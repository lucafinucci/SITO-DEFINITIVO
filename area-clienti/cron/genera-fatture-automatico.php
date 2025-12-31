<?php
/**
 * Script CRON per generazione automatica fatture mensili
 *
 * Esegue automaticamente la generazione delle fatture per il mese precedente
 * il primo giorno di ogni mese.
 *
 * Configurazione CRON consigliata (Linux):
 * 0 2 1 * * php /path/to/genera-fatture-automatico.php
 *
 * Windows Task Scheduler:
 * Trigger: Il primo giorno di ogni mese alle 02:00
 * Azione: php.exe "C:\path\to\genera-fatture-automatico.php"
 */

// Verifica che lo script sia eseguito da CLI
if (php_sapi_name() !== 'cli') {
    die('Questo script può essere eseguito solo da command line');
}

require __DIR__ . '/../includes/db.php';

echo "[" . date('Y-m-d H:i:s') . "] Avvio generazione automatica fatture mensili\n";

try {
    // Calcola mese precedente
    $dataPrecedente = new DateTime('first day of last month');
    $anno = (int)$dataPrecedente->format('Y');
    $mese = (int)$dataPrecedente->format('n');

    echo "Generazione fatture per periodo: $mese/$anno\n";

    // Calcola periodo di riferimento
    $primoGiorno = new DateTime("$anno-$mese-01");
    $ultimoGiorno = clone $primoGiorno;
    $ultimoGiorno->modify('last day of this month');

    // IVA di default
    $ivaPercentuale = 22.00;

    $pdo->beginTransaction();

    // Trova tutti i clienti con servizi attivi nel mese
    $stmt = $pdo->prepare('
        SELECT DISTINCT
            u.id,
            u.nome,
            u.cognome,
            u.email,
            u.azienda
        FROM utenti u
        JOIN utenti_servizi us ON u.id = us.user_id
        WHERE u.ruolo != "admin"
          AND us.stato = "attivo"
          AND us.data_attivazione <= :ultimo_giorno
          AND (us.data_disattivazione IS NULL OR us.data_disattivazione > :primo_giorno)
        ORDER BY u.azienda ASC
    ');
    $stmt->execute([
        'primo_giorno' => $primoGiorno->format('Y-m-d'),
        'ultimo_giorno' => $ultimoGiorno->format('Y-m-d')
    ]);
    $clienti = $stmt->fetchAll();

    echo "Trovati " . count($clienti) . " clienti con servizi attivi\n";

    $fattureGenerate = 0;
    $fattureSkippate = 0;
    $errori = [];

    foreach ($clienti as $cliente) {
        // Verifica se esiste già una fattura per questo cliente/periodo
        $stmt = $pdo->prepare('
            SELECT id FROM fatture
            WHERE cliente_id = :cliente_id AND anno = :anno AND mese = :mese
            LIMIT 1
        ');
        $stmt->execute([
            'cliente_id' => $cliente['id'],
            'anno' => $anno,
            'mese' => $mese
        ]);

        if ($stmt->fetch()) {
            $fattureSkippate++;
            echo "  - Skipping {$cliente['azienda']} (fattura già esistente)\n";
            continue;
        }

        // Recupera servizi attivi per il cliente in questo mese
        $stmt = $pdo->prepare('
            SELECT
                us.id AS utente_servizio_id,
                s.id AS servizio_id,
                s.nome AS servizio_nome,
                s.descrizione,
                s.prezzo_mensile,
                COALESCE(pp.prezzo_mensile, s.prezzo_mensile) AS prezzo_finale,
                us.data_attivazione,
                us.data_disattivazione
            FROM utenti_servizi us
            JOIN servizi s ON us.servizio_id = s.id
            LEFT JOIN clienti_prezzi_personalizzati pp
              ON pp.cliente_id = us.user_id AND pp.servizio_id = s.id
            WHERE us.user_id = :user_id
              AND us.stato = "attivo"
              AND us.data_attivazione <= :ultimo_giorno
              AND (us.data_disattivazione IS NULL OR us.data_disattivazione > :primo_giorno)
            ORDER BY s.nome ASC
        ');
        $stmt->execute([
            'user_id' => $cliente['id'],
            'primo_giorno' => $primoGiorno->format('Y-m-d'),
            'ultimo_giorno' => $ultimoGiorno->format('Y-m-d')
        ]);
        $servizi = $stmt->fetchAll();

        // Pacchetti attivi nel periodo
        $stmt = $pdo->prepare('
            SELECT
                cp.id AS cliente_pacchetto_id,
                cp.pacchetto_id,
                cp.data_inizio,
                cp.data_fine,
                p.nome,
                p.prezzo_mensile
            FROM clienti_pacchetti cp
            JOIN pacchetti p ON cp.pacchetto_id = p.id
            WHERE cp.cliente_id = :user_id
              AND cp.attivo = 1
              AND (cp.data_inizio IS NULL OR cp.data_inizio <= :ultimo_giorno)
              AND (cp.data_fine IS NULL OR cp.data_fine >= :primo_giorno)
        ');
        $stmt->execute([
            'user_id' => $cliente['id'],
            'primo_giorno' => $primoGiorno->format('Y-m-d'),
            'ultimo_giorno' => $ultimoGiorno->format('Y-m-d')
        ]);
        $pacchetti = $stmt->fetchAll();

        // Servizi coperti dai pacchetti
        $bundleServiceIds = [];
        if (!empty($pacchetti)) {
            $bundleIds = array_column($pacchetti, 'pacchetto_id');
            $bundlePlaceholders = implode(',', array_fill(0, count($bundleIds), '?'));
            $stmt = $pdo->prepare("
                SELECT pacchetto_id, servizio_id
                FROM pacchetti_servizi
                WHERE pacchetto_id IN ($bundlePlaceholders)
            ");
            $stmt->execute($bundleIds);
            foreach ($stmt->fetchAll() as $row) {
                $bundleServiceIds[(int)$row['servizio_id']] = true;
            }
        }

        if (empty($servizi)) {
            if (empty($pacchetti)) {
                echo "  - Skipping {$cliente['azienda']} (nessun servizio o pacchetto attivo)\n";
                continue;
            }
        }

        try {
            // Genera numero fattura
            $numeroFattura = generaNumeroProssimoFattura($pdo, $anno);

            // Prepara righe fattura e imponibile
            $lineItems = [];
            $ordine = 0;
            foreach ($servizi as $servizio) {
                if (!empty($bundleServiceIds[(int)$servizio['servizio_id']])) {
                    continue;
                }
                $ordine++;
                $prezzoUnitario = (float)$servizio['prezzo_finale'];
                $descrizione = $servizio['servizio_nome'];

                // Pro-rata se necessario
                $giorniMese = (int)$ultimoGiorno->format('d');
                $dataInizio = max($primoGiorno, new DateTime($servizio['data_attivazione']));
                $dataFine = $servizio['data_disattivazione']
                    ? min($ultimoGiorno, new DateTime($servizio['data_disattivazione']))
                    : $ultimoGiorno;

                $intervalloGiorni = $dataInizio->diff($dataFine)->days + 1;
                if ($intervalloGiorni < $giorniMese) {
                    $percentuale = ($intervalloGiorni / $giorniMese);
                    $prezzoUnitario = round($prezzoUnitario * $percentuale, 2);
                    $descrizione .= " (pro-rata $intervalloGiorni/$giorniMese giorni)";
                }

                $lineItems[] = [
                    'servizio_id' => $servizio['servizio_id'],
                    'utente_servizio_id' => $servizio['utente_servizio_id'],
                    'descrizione' => $descrizione,
                    'prezzo_unitario' => $prezzoUnitario,
                    'imponibile' => $prezzoUnitario,
                    'ordine' => $ordine
                ];
            }

            foreach ($pacchetti as $bundle) {
                $ordine++;
                $prezzoUnitario = (float)$bundle['prezzo_mensile'];
                $descrizione = 'Pacchetto: ' . $bundle['nome'];

                $giorniMese = (int)$ultimoGiorno->format('d');
                $dataInizio = $bundle['data_inizio']
                    ? max($primoGiorno, new DateTime($bundle['data_inizio']))
                    : $primoGiorno;
                $dataFine = $bundle['data_fine']
                    ? min($ultimoGiorno, new DateTime($bundle['data_fine']))
                    : $ultimoGiorno;
                $intervalloGiorni = $dataInizio->diff($dataFine)->days + 1;
                if ($intervalloGiorni < $giorniMese) {
                    $percentuale = ($intervalloGiorni / $giorniMese);
                    $prezzoUnitario = round($prezzoUnitario * $percentuale, 2);
                    $descrizione .= " (pro-rata $intervalloGiorni/$giorniMese giorni)";
                }

                $lineItems[] = [
                    'servizio_id' => null,
                    'utente_servizio_id' => null,
                    'descrizione' => $descrizione,
                    'prezzo_unitario' => $prezzoUnitario,
                    'imponibile' => $prezzoUnitario,
                    'ordine' => $ordine
                ];
            }

            // Acquisti on-demand del mese (non fatturati)
            $stmt = $pdo->prepare('
                SELECT a.id, a.quantita, a.prezzo_unitario, a.totale, a.data_acquisto, s.nome
                FROM clienti_acquisti_onetime a
                JOIN servizi_on_demand s ON a.servizio_id = s.id
                WHERE a.cliente_id = :cliente_id
                  AND a.stato = "da_fatturare"
                  AND a.data_acquisto BETWEEN :primo AND :ultimo
                ORDER BY a.data_acquisto ASC
            ');
            $stmt->execute([
                'cliente_id' => $cliente['id'],
                'primo' => $primoGiorno->format('Y-m-d'),
                'ultimo' => $ultimoGiorno->format('Y-m-d')
            ]);
            $acquistiOnetime = $stmt->fetchAll();
            $onetimeIds = [];
            foreach ($acquistiOnetime as $acq) {
                $ordine++;
                $descrizione = 'On-demand: ' . $acq['nome'];
                $prezzoUnitario = (float)$acq['prezzo_unitario'];
                $imponibileRiga = (float)$acq['totale'];
                $lineItems[] = [
                    'servizio_id' => null,
                    'utente_servizio_id' => null,
                    'descrizione' => $descrizione,
                    'prezzo_unitario' => $prezzoUnitario,
                    'imponibile' => $imponibileRiga,
                    'ordine' => $ordine
                ];
                $onetimeIds[] = (int)$acq['id'];
            }

            $imponibile = 0;
            foreach ($lineItems as $item) {
                $imponibile += (float)$item['imponibile'];
            }

            // Applica sconti temporanei
            $stmt = $pdo->prepare('
                SELECT id, servizio_id, tipo, valore, data_inizio, data_fine, note
                FROM clienti_sconti
                WHERE cliente_id = :cliente_id
                  AND attivo = 1
                  AND (data_inizio IS NULL OR data_inizio <= :ultimo_giorno)
                  AND (data_fine IS NULL OR data_fine >= :primo_giorno)
                ORDER BY created_at ASC
            ');
            $stmt->execute([
                'cliente_id' => $cliente['id'],
                'primo_giorno' => $primoGiorno->format('Y-m-d'),
                'ultimo_giorno' => $ultimoGiorno->format('Y-m-d')
            ]);
            $sconti = $stmt->fetchAll();

            foreach ($sconti as $sconto) {
                if ($imponibile <= 0) {
                    break;
                }
                $importoSconto = 0;
                $label = 'Sconto promozionale';
                if (!empty($sconto['servizio_id'])) {
                    foreach ($lineItems as $item) {
                        if ((int)$item['servizio_id'] !== (int)$sconto['servizio_id']) {
                            continue;
                        }
                        $base = (float)$item['imponibile'];
                        if ($sconto['tipo'] === 'percentuale') {
                            $importoSconto = round($base * ((float)$sconto['valore'] / 100), 2);
                        } else {
                            $importoSconto = min((float)$sconto['valore'], $base);
                        }
                        $label = 'Sconto promozionale - ' . $item['descrizione'];
                        break;
                    }
                } else {
                    $base = $imponibile;
                    if ($sconto['tipo'] === 'percentuale') {
                        $importoSconto = round($base * ((float)$sconto['valore'] / 100), 2);
                    } else {
                        $importoSconto = min((float)$sconto['valore'], $base);
                    }
                    $label = 'Sconto promozionale (globale)';
                }

                if ($importoSconto > 0) {
                    $ordine++;
                    $lineItems[] = [
                        'servizio_id' => null,
                        'utente_servizio_id' => null,
                        'descrizione' => $label,
                        'prezzo_unitario' => -$importoSconto,
                        'imponibile' => -$importoSconto,
                        'ordine' => $ordine
                    ];
                    $imponibile -= $importoSconto;
                }
            }

            // Applica coupon assegnati
            $stmt = $pdo->prepare('
                SELECT
                    cc.id AS assignment_id,
                    c.id AS coupon_id,
                    c.codice,
                    c.tipo,
                    c.valore,
                    c.max_usi,
                    c.usi
                FROM clienti_coupon cc
                JOIN coupon c ON cc.coupon_id = c.id
                WHERE cc.cliente_id = :cliente_id
                  AND cc.usato = 0
                  AND c.attivo = 1
                  AND (c.data_inizio IS NULL OR c.data_inizio <= :ultimo_giorno)
                  AND (c.data_fine IS NULL OR c.data_fine >= :primo_giorno)
                  AND (c.max_usi IS NULL OR c.usi < c.max_usi)
                ORDER BY cc.assegnato_il ASC
            ');
            $stmt->execute([
                'cliente_id' => $cliente['id'],
                'primo_giorno' => $primoGiorno->format('Y-m-d'),
                'ultimo_giorno' => $ultimoGiorno->format('Y-m-d')
            ]);
            $coupons = $stmt->fetchAll();

            $couponAssignments = [];
            $couponUsage = [];
            foreach ($coupons as $coupon) {
                if ($imponibile <= 0) {
                    break;
                }
                $base = $imponibile;
                if ($coupon['tipo'] === 'percentuale') {
                    $importoCoupon = round($base * ((float)$coupon['valore'] / 100), 2);
                } else {
                    $importoCoupon = min((float)$coupon['valore'], $base);
                }
                if ($importoCoupon > 0) {
                    $ordine++;
                    $lineItems[] = [
                        'servizio_id' => null,
                        'utente_servizio_id' => null,
                        'descrizione' => 'Coupon ' . $coupon['codice'],
                        'prezzo_unitario' => -$importoCoupon,
                        'imponibile' => -$importoCoupon,
                        'ordine' => $ordine
                    ];
                    $imponibile -= $importoCoupon;
                    $couponAssignments[] = (int)$coupon['assignment_id'];
                    $couponUsage[(int)$coupon['coupon_id']] = ($couponUsage[(int)$coupon['coupon_id']] ?? 0) + 1;
                }
            }

            if ($imponibile < 0) {
                $imponibile = 0;
            }

            $ivaImporto = round($imponibile * ($ivaPercentuale / 100), 2);
            $totale = $imponibile + $ivaImporto;

            // Data emissione: primo giorno del mese successivo
            $dataEmissione = clone $ultimoGiorno;
            $dataEmissione->modify('+1 day');

            // Data scadenza: 30 giorni dalla data emissione
            $dataScadenza = clone $dataEmissione;
            $dataScadenza->modify('+30 days');

            // Crea fattura
            $stmt = $pdo->prepare('
                INSERT INTO fatture (
                    numero_fattura,
                    cliente_id,
                    data_emissione,
                    data_scadenza,
                    anno,
                    mese,
                    imponibile,
                    iva_percentuale,
                    iva_importo,
                    totale,
                    stato
                ) VALUES (
                    :numero_fattura,
                    :cliente_id,
                    :data_emissione,
                    :data_scadenza,
                    :anno,
                    :mese,
                    :imponibile,
                    :iva_percentuale,
                    :iva_importo,
                    :totale,
                    "emessa"
                )
            ');

            $stmt->execute([
                'numero_fattura' => $numeroFattura,
                'cliente_id' => $cliente['id'],
                'data_emissione' => $dataEmissione->format('Y-m-d'),
                'data_scadenza' => $dataScadenza->format('Y-m-d'),
                'anno' => $anno,
                'mese' => $mese,
                'imponibile' => $imponibile,
                'iva_percentuale' => $ivaPercentuale,
                'iva_importo' => $ivaImporto,
                'totale' => $totale
            ]);

            $fatturaId = $pdo->lastInsertId();

            // Crea righe fattura
            foreach ($lineItems as $item) {
                $imponibileRiga = (float)$item['imponibile'];
                $ivaRiga = round($imponibileRiga * ($ivaPercentuale / 100), 2);
                $totaleRiga = $imponibileRiga + $ivaRiga;

                $stmt = $pdo->prepare('
                    INSERT INTO fatture_righe (
                        fattura_id,
                        servizio_id,
                        descrizione,
                        quantita,
                        prezzo_unitario,
                        imponibile,
                        iva_percentuale,
                        iva_importo,
                        totale,
                        utente_servizio_id,
                        ordine
                    ) VALUES (
                        :fattura_id,
                        :servizio_id,
                        :descrizione,
                        1.00,
                        :prezzo_unitario,
                        :imponibile,
                        :iva_percentuale,
                        :iva_importo,
                        :totale,
                        :utente_servizio_id,
                        :ordine
                    )
                ');

                $stmt->execute([
                    'fattura_id' => $fatturaId,
                    'servizio_id' => $item['servizio_id'],
                    'descrizione' => $item['descrizione'],
                    'prezzo_unitario' => $item['prezzo_unitario'],
                    'imponibile' => $imponibileRiga,
                    'iva_percentuale' => $ivaPercentuale,
                    'iva_importo' => $ivaRiga,
                    'totale' => $totaleRiga,
                    'utente_servizio_id' => $item['utente_servizio_id'],
                    'ordine' => $item['ordine']
                ]);
            }

            // Aggiorna uso coupon
            if (!empty($couponAssignments)) {
                $placeholders = implode(',', array_fill(0, count($couponAssignments), '?'));
                $stmt = $pdo->prepare("UPDATE clienti_coupon SET usato = 1, usato_il = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($couponAssignments);
            }
            if (!empty($couponUsage)) {
                foreach ($couponUsage as $couponId => $count) {
                    $stmt = $pdo->prepare('UPDATE coupon SET usi = usi + :count WHERE id = :id');
                    $stmt->execute(['count' => $count, 'id' => $couponId]);
                }
            }

            if (!empty($onetimeIds)) {
                $placeholders = implode(',', array_fill(0, count($onetimeIds), '?'));
                $stmt = $pdo->prepare("UPDATE clienti_acquisti_onetime SET stato = 'fatturato', fattura_id = ? WHERE id IN ($placeholders)");
                $params = array_merge([$fatturaId], $onetimeIds);
                $stmt->execute($params);
            }

            $fattureGenerate++;
            echo "  ✓ Generata fattura $numeroFattura per {$cliente['azienda']} (€" . number_format($totale, 2) . ")\n";

        } catch (Exception $e) {
            $errori[] = "Errore per {$cliente['azienda']}: " . $e->getMessage();
            echo "  ✗ Errore per {$cliente['azienda']}: " . $e->getMessage() . "\n";
        }
    }

    $pdo->commit();

    echo "\n=== Riepilogo ===\n";
    echo "Periodo: $mese/$anno\n";
    echo "Clienti elaborati: " . count($clienti) . "\n";
    echo "Fatture generate: $fattureGenerate\n";
    echo "Fatture skippate: $fattureSkippate\n";
    echo "Errori: " . count($errori) . "\n";

    if (!empty($errori)) {
        echo "\nErrori rilevati:\n";
        foreach ($errori as $errore) {
            echo "  - $errore\n";
        }
    }

    echo "\n[" . date('Y-m-d H:i:s') . "] Completato\n";

    // Log su file
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/fatture-' . date('Y-m') . '.log';
    $logContent = sprintf(
        "[%s] Generazione completata - Periodo: %02d/%d - Generate: %d - Skippate: %d - Errori: %d\n",
        date('Y-m-d H:i:s'),
        $mese,
        $anno,
        $fattureGenerate,
        $fattureSkippate,
        count($errori)
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERRORE CRITICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

function generaNumeroProssimoFattura($pdo, $anno) {
    $stmt = $pdo->prepare('SELECT * FROM fatture_config WHERE anno = :anno');
    $stmt->execute(['anno' => $anno]);
    $config = $stmt->fetch();

    if (!$config) {
        $stmt = $pdo->prepare('
            INSERT INTO fatture_config (anno, ultimo_numero, prefisso, formato)
            VALUES (:anno, 0, "FT", "{prefisso}-{anno}-{numero}")
        ');
        $stmt->execute(['anno' => $anno]);

        $stmt = $pdo->prepare('SELECT * FROM fatture_config WHERE anno = :anno');
        $stmt->execute(['anno' => $anno]);
        $config = $stmt->fetch();
    }

    $nuovoNumero = (int)$config['ultimo_numero'] + 1;

    $stmt = $pdo->prepare('UPDATE fatture_config SET ultimo_numero = :numero WHERE anno = :anno');
    $stmt->execute(['numero' => $nuovoNumero, 'anno' => $anno]);

    $formato = $config['formato'];
    $numeroFormattato = str_replace(
        ['{prefisso}', '{anno}', '{numero}'],
        [$config['prefisso'], $anno, str_pad($nuovoNumero, 5, '0', STR_PAD_LEFT)],
        $formato
    );

    return $numeroFormattato;
}
