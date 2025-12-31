<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$clienteId = $_SESSION['cliente_id'];

// Verifica che sia admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

// Leggi input JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

function getFattureColumns($pdo) {
    static $cols = null;
    if ($cols !== null) {
        return $cols;
    }
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fatture'
          AND COLUMN_NAME IN ('stato', 'data_pagamento')
    ");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $cols;
}

function fatturePagamentiTableExists($pdo) {
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fatture_pagamenti'
    ");
    $stmt->execute();
    $exists = (int)$stmt->fetchColumn() > 0;
    return $exists;
}

function fatturaHaPagamenti($pdo, $fatturaId) {
    if (!fatturePagamentiTableExists($pdo)) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM fatture_pagamenti WHERE fattura_id = :id');
    $stmt->execute(['id' => $fatturaId]);
    return (int)$stmt->fetchColumn() > 0;
}

function verificaFatturaModificabile($pdo, $fatturaId) {
    $cols = getFattureColumns($pdo);
    $selectDataPag = in_array('data_pagamento', $cols, true) ? ', data_pagamento' : '';
    $stmt = $pdo->prepare('SELECT stato' . $selectDataPag . ' FROM fatture WHERE id = :id');
    $stmt->execute(['id' => $fatturaId]);
    $fattura = $stmt->fetch();

    if (!$fattura) {
        throw new Exception('Fattura non trovata');
    }

    $stato = strtolower(trim((string)$fattura['stato']));
    $pagataDaStato = $stato === 'pagata';
    $pagataDaData = false;
    if (array_key_exists('data_pagamento', $fattura)) {
        $dp = trim((string)$fattura['data_pagamento']);
        $pagataDaData = $dp !== '' && $dp !== '0000-00-00' && $dp !== '0000-00-00 00:00:00';
    }

    if ($pagataDaStato || $pagataDaData || fatturaHaPagamenti($pdo, $fatturaId)) {
        throw new Exception('Fattura pagata: modifiche non consentite');
    }
}

try {
    switch ($action) {
        case 'create':
            // Crea nuova riga
            $fatturaId = (int)($input['fattura_id'] ?? 0);
            $servizioId = isset($input['servizio_id']) && $input['servizio_id'] ? (int)$input['servizio_id'] : null;
            $descrizione = trim($input['descrizione'] ?? '');
            $quantita = (float)($input['quantita'] ?? 1);
            $prezzoUnitario = (float)($input['prezzo_unitario'] ?? 0);
            $ivaPercentuale = (float)($input['iva_percentuale'] ?? 22);
            $ordine = (int)($input['ordine'] ?? 0);

            if ($fatturaId <= 0) {
                throw new Exception('ID fattura non valido');
            }

            if (empty($descrizione)) {
                throw new Exception('Descrizione obbligatoria');
            }

            verificaFatturaModificabile($pdo, $fatturaId);

            // Calcola importi
            $imponibile = $quantita * $prezzoUnitario;
            $ivaImporto = $imponibile * ($ivaPercentuale / 100);
            $totale = $imponibile + $ivaImporto;

            // Inserisci riga (gestisci compatibilità NULL/NOT NULL)
            $stmt = $pdo->prepare("
                INSERT INTO fatture_righe (
                    fattura_id, servizio_id, descrizione, quantita, prezzo_unitario,
                    imponibile, iva_percentuale, iva_importo, totale, ordine
                ) VALUES (
                    :fattura_id, :servizio_id, :descrizione, :quantita, :prezzo_unitario,
                    :imponibile, :iva_percentuale, :iva_importo, :totale, :ordine
                )
            ");

            $stmt->execute([
                'fattura_id' => $fatturaId,
                'servizio_id' => $servizioId,
                'descrizione' => $descrizione,
                'quantita' => $quantita,
                'prezzo_unitario' => $prezzoUnitario,
                'imponibile' => $imponibile,
                'iva_percentuale' => $ivaPercentuale,
                'iva_importo' => $ivaImporto,
                'totale' => $totale,
                'ordine' => $ordine
            ]);

            $rigaId = $pdo->lastInsertId();

            // Ricalcola totali fattura
            ricalcolaTotaliFattura($pdo, $fatturaId);

            echo json_encode([
                'success' => true,
                'message' => 'Voce aggiunta con successo',
                'riga_id' => $rigaId
            ]);
            break;

        case 'update':
            // Aggiorna riga esistente
            $rigaId = (int)($input['riga_id'] ?? 0);
            $servizioId = isset($input['servizio_id']) && $input['servizio_id'] ? (int)$input['servizio_id'] : null;
            $descrizione = trim($input['descrizione'] ?? '');
            $quantita = (float)($input['quantita'] ?? 1);
            $prezzoUnitario = (float)($input['prezzo_unitario'] ?? 0);
            $ivaPercentuale = (float)($input['iva_percentuale'] ?? 22);
            $ordine = (int)($input['ordine'] ?? 0);

            if ($rigaId <= 0) {
                throw new Exception('ID riga non valido');
            }

            if (empty($descrizione)) {
                throw new Exception('Descrizione obbligatoria');
            }

            // Verifica che la riga esista e recupera fattura_id
            $stmt = $pdo->prepare('SELECT fattura_id FROM fatture_righe WHERE id = :id');
            $stmt->execute(['id' => $rigaId]);
            $riga = $stmt->fetch();

            if (!$riga) {
                throw new Exception('Riga non trovata');
            }

            $fatturaId = $riga['fattura_id'];
            verificaFatturaModificabile($pdo, $fatturaId);

            // Calcola importi
            $imponibile = $quantita * $prezzoUnitario;
            $ivaImporto = $imponibile * ($ivaPercentuale / 100);
            $totale = $imponibile + $ivaImporto;

            // Aggiorna riga
            $stmt = $pdo->prepare("
                UPDATE fatture_righe
                SET servizio_id = :servizio_id,
                    descrizione = :descrizione,
                    quantita = :quantita,
                    prezzo_unitario = :prezzo_unitario,
                    imponibile = :imponibile,
                    iva_percentuale = :iva_percentuale,
                    iva_importo = :iva_importo,
                    totale = :totale,
                    ordine = :ordine
                WHERE id = :id
            ");

            $stmt->execute([
                'servizio_id' => $servizioId,
                'descrizione' => $descrizione,
                'quantita' => $quantita,
                'prezzo_unitario' => $prezzoUnitario,
                'imponibile' => $imponibile,
                'iva_percentuale' => $ivaPercentuale,
                'iva_importo' => $ivaImporto,
                'totale' => $totale,
                'ordine' => $ordine,
                'id' => $rigaId
            ]);

            // Ricalcola totali fattura
            ricalcolaTotaliFattura($pdo, $fatturaId);

            echo json_encode([
                'success' => true,
                'message' => 'Voce aggiornata con successo'
            ]);
            break;

        case 'delete':
            // Elimina riga
            $rigaId = (int)($input['riga_id'] ?? 0);

            if ($rigaId <= 0) {
                throw new Exception('ID riga non valido');
            }

            // Verifica che la riga esista e recupera fattura_id
            $stmt = $pdo->prepare('SELECT fattura_id FROM fatture_righe WHERE id = :id');
            $stmt->execute(['id' => $rigaId]);
            $riga = $stmt->fetch();

            if (!$riga) {
                throw new Exception('Riga non trovata');
            }

            $fatturaId = $riga['fattura_id'];
            verificaFatturaModificabile($pdo, $fatturaId);

            // Elimina riga
            $stmt = $pdo->prepare('DELETE FROM fatture_righe WHERE id = :id');
            $stmt->execute(['id' => $rigaId]);

            // Ricalcola totali fattura
            ricalcolaTotaliFattura($pdo, $fatturaId);

            echo json_encode([
                'success' => true,
                'message' => 'Voce eliminata con successo'
            ]);
            break;

        default:
            throw new Exception('Azione non valida');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Ricalcola i totali della fattura sommando le righe
 */
function ricalcolaTotaliFattura($pdo, $fatturaId) {
    // Verifica quali colonne esistono nella tabella fatture
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fatture'
          AND COLUMN_NAME IN ('imponibile', 'importo_netto', 'iva_importo', 'iva', 'totale', 'importo_totale', 'iva_percentuale')
    ");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $hasImponibile = in_array('imponibile', $cols, true);
    $hasImportoNetto = in_array('importo_netto', $cols, true);
    $hasIvaImporto = in_array('iva_importo', $cols, true);
    $hasIva = in_array('iva', $cols, true);
    $hasTotale = in_array('totale', $cols, true);
    $hasImportoTotale = in_array('importo_totale', $cols, true);
    $hasIvaPercentuale = in_array('iva_percentuale', $cols, true);

    // Determina i nomi delle colonne da usare
    $colImponibile = $hasImponibile ? 'imponibile' : 'importo_netto';
    $colIva = $hasIvaImporto ? 'iva_importo' : 'iva';
    $colTotale = $hasTotale ? 'totale' : 'importo_totale';

    // Somma importi dalle righe
    $stmt = $pdo->prepare("
        SELECT
            SUM(imponibile) AS tot_imponibile,
            SUM(iva_importo) AS tot_iva,
            SUM(totale) AS tot_totale,
            AVG(iva_percentuale) AS avg_iva_perc
        FROM fatture_righe
        WHERE fattura_id = :fattura_id
    ");
    $stmt->execute(['fattura_id' => $fatturaId]);
    $totali = $stmt->fetch();

    $imponibile = (float)($totali['tot_imponibile'] ?? 0);
    $ivaImporto = (float)($totali['tot_iva'] ?? 0);
    $totale = (float)($totali['tot_totale'] ?? 0);
    $ivaPercentuale = (float)($totali['avg_iva_perc'] ?? 22);

    // Aggiorna fattura con i nomi di colonna corretti
    if ($hasIvaPercentuale) {
        $stmt = $pdo->prepare("
            UPDATE fatture
            SET $colImponibile = :imponibile,
                iva_percentuale = :iva_percentuale,
                $colIva = :iva_importo,
                $colTotale = :totale
            WHERE id = :id
        ");

        $stmt->execute([
            'imponibile' => $imponibile,
            'iva_percentuale' => $ivaPercentuale,
            'iva_importo' => $ivaImporto,
            'totale' => $totale,
            'id' => $fatturaId
        ]);
    } else {
        // Schema vecchio senza iva_percentuale
        $stmt = $pdo->prepare("
            UPDATE fatture
            SET $colImponibile = :imponibile,
                $colIva = :iva_importo,
                $colTotale = :totale
            WHERE id = :id
        ");

        $stmt->execute([
            'imponibile' => $imponibile,
            'iva_importo' => $ivaImporto,
            'totale' => $totale,
            'id' => $fatturaId
        ]);
    }
}
