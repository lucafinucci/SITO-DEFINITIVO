<?php
/**
 * Script CRON per email automatiche scadenze
 *
 * Invia email automatiche per:
 * - Fatture in scadenza (3 giorni prima)
 * - Fatture scadute (solleciti)
 *
 * Configurazione CRON consigliata:
 * 0 9 * * * php /path/to/invia-email-scadenze.php
 * (Esegui ogni giorno alle 09:00)
 */

// Verifica che lo script sia eseguito da CLI
if (php_sapi_name() !== 'cli') {
    die('Questo script può essere eseguito solo da command line');
}

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/email-manager.php';

echo "[" . date('Y-m-d H:i:s') . "] Avvio invio email scadenze\n";

try {
    $emailManager = new EmailManager($pdo);
    $emailInviate = 0;

    // 1. Email promemoria fatture in scadenza (3 giorni)
    echo "\n=== Promemoria Fatture in Scadenza ===\n";

    $stmt = $pdo->prepare('
        SELECT
            f.id,
            f.numero_fattura,
            f.data_emissione,
            f.data_scadenza,
            f.imponibile,
            f.iva_percentuale,
            f.iva_importo,
            f.totale,
            u.id AS cliente_id,
            u.nome,
            u.cognome,
            u.email,
            u.azienda,
            DATEDIFF(f.data_scadenza, CURDATE()) AS giorni_a_scadenza
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.stato IN ("emessa", "inviata")
          AND f.data_scadenza = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ');
    $stmt->execute();
    $fattureInScadenza = $stmt->fetchAll();

    echo "Trovate " . count($fattureInScadenza) . " fatture in scadenza tra 3 giorni\n";

    foreach ($fattureInScadenza as $fattura) {
        try {
            $variabili = [
                'azienda' => $fattura['azienda'],
                'nome_cliente' => $fattura['nome'] . ' ' . $fattura['cognome'],
                'numero_fattura' => $fattura['numero_fattura'],
                'data_emissione' => date('d/m/Y', strtotime($fattura['data_emissione'])),
                'data_scadenza' => date('d/m/Y', strtotime($fattura['data_scadenza'])),
                'imponibile' => number_format($fattura['imponibile'], 2, ',', '.'),
                'iva_percentuale' => number_format($fattura['iva_percentuale'], 0),
                'iva_importo' => number_format($fattura['iva_importo'], 2, ',', '.'),
                'totale' => number_format($fattura['totale'], 2, ',', '.'),
                'giorni_a_scadenza' => $fattura['giorni_a_scadenza'],
                'link_paga' => 'https://finch-ai.it/area-clienti/paga-fattura.php?id=' . $fattura['id'],
                'link_pdf' => 'https://finch-ai.it/area-clienti/api/genera-pdf-fattura.php?id=' . $fattura['id']
            ];

            $emailManager->sendFromTemplate(
                'fattura-emessa', // Riutilizza template fattura
                ['email' => $fattura['email'], 'nome' => $fattura['azienda']],
                $variabili,
                [
                    'cliente_id' => $fattura['cliente_id'],
                    'fattura_id' => $fattura['id'],
                    'priorita' => 'normale'
                ]
            );

            $emailInviate++;
            echo "  ✓ Email inviata: {$fattura['azienda']} - {$fattura['numero_fattura']}\n";

        } catch (Exception $e) {
            echo "  ✗ Errore: {$fattura['numero_fattura']} - {$e->getMessage()}\n";
        }

        sleep(1); // Pausa tra invii
    }

    // 2. Solleciti fatture scadute da 7 giorni
    echo "\n=== Solleciti Primo Livello (7 giorni) ===\n";

    $stmt = $pdo->prepare('
        SELECT
            f.id,
            f.numero_fattura,
            f.data_emissione,
            f.data_scadenza,
            f.totale,
            u.id AS cliente_id,
            u.nome,
            u.cognome,
            u.email,
            u.azienda,
            DATEDIFF(CURDATE(), f.data_scadenza) AS giorni_ritardo
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.stato = "scaduta"
          AND f.data_scadenza = DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          AND NOT EXISTS (
              SELECT 1 FROM email_log el
              WHERE el.fattura_id = f.id
                AND el.oggetto LIKE "%sollecito%"
                AND DATE(el.created_at) = CURDATE()
          )
    ');
    $stmt->execute();
    $solleciti7giorni = $stmt->fetchAll();

    echo "Trovate " . count($solleciti7giorni) . " fatture per sollecito (7 giorni)\n";

    foreach ($solleciti7giorni as $fattura) {
        try {
            $variabili = [
                'azienda' => $fattura['azienda'],
                'numero_fattura' => $fattura['numero_fattura'],
                'data_emissione' => date('d/m/Y', strtotime($fattura['data_emissione'])),
                'data_scadenza' => date('d/m/Y', strtotime($fattura['data_scadenza'])),
                'totale' => number_format($fattura['totale'], 2, ',', '.'),
                'giorni_ritardo' => $fattura['giorni_ritardo'],
                'link_paga' => 'https://finch-ai.it/area-clienti/paga-fattura.php?id=' . $fattura['id']
            ];

            $emailManager->sendFromTemplate(
                'sollecito-primo',
                ['email' => $fattura['email'], 'nome' => $fattura['azienda']],
                $variabili,
                [
                    'cliente_id' => $fattura['cliente_id'],
                    'fattura_id' => $fattura['id'],
                    'priorita' => 'alta'
                ]
            );

            $emailInviate++;
            echo "  ✓ Sollecito inviato: {$fattura['azienda']} - {$fattura['numero_fattura']}\n";

        } catch (Exception $e) {
            echo "  ✗ Errore: {$fattura['numero_fattura']} - {$e->getMessage()}\n";
        }

        sleep(1);
    }

    // 3. Conferme pagamento ricevuto (oggi)
    echo "\n=== Conferme Pagamento Ricevuto ===\n";

    $stmt = $pdo->prepare('
        SELECT
            fp.id,
            fp.fattura_id,
            fp.importo,
            fp.metodo_pagamento,
            fp.riferimento_transazione,
            fp.data_pagamento,
            f.numero_fattura,
            u.id AS cliente_id,
            u.email,
            u.azienda
        FROM fatture_pagamenti fp
        JOIN fatture f ON fp.fattura_id = f.id
        JOIN utenti u ON f.cliente_id = u.id
        WHERE DATE(fp.data_pagamento) = CURDATE()
          AND NOT EXISTS (
              SELECT 1 FROM email_log el
              WHERE el.fattura_id = f.id
                AND el.oggetto LIKE "%Pagamento Ricevuto%"
                AND DATE(el.created_at) = CURDATE()
          )
    ');
    $stmt->execute();
    $pagamenti = $stmt->fetchAll();

    echo "Trovati " . count($pagamenti) . " pagamenti da confermare\n";

    foreach ($pagamenti as $pagamento) {
        try {
            $variabili = [
                'azienda' => $pagamento['azienda'],
                'numero_fattura' => $pagamento['numero_fattura'],
                'importo_pagato' => number_format($pagamento['importo'], 2, ',', '.'),
                'data_pagamento' => date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])),
                'metodo_pagamento' => ucfirst($pagamento['metodo_pagamento']),
                'riferimento_transazione' => $pagamento['riferimento_transazione'] ?? 'N/A'
            ];

            $emailManager->sendFromTemplate(
                'pagamento-ricevuto',
                ['email' => $pagamento['email'], 'nome' => $pagamento['azienda']],
                $variabili,
                [
                    'cliente_id' => $pagamento['cliente_id'],
                    'fattura_id' => $pagamento['fattura_id'],
                    'priorita' => 'alta'
                ]
            );

            $emailInviate++;
            echo "  ✓ Conferma inviata: {$pagamento['azienda']} - {$pagamento['numero_fattura']}\n";

        } catch (Exception $e) {
            echo "  ✗ Errore: {$pagamento['numero_fattura']} - {$e->getMessage()}\n";
        }

        sleep(1);
    }

    echo "\n=== Riepilogo ===\n";
    echo "Email totali inviate: $emailInviate\n";
    echo "  - Promemoria scadenza: " . count($fattureInScadenza) . "\n";
    echo "  - Solleciti: " . count($solleciti7giorni) . "\n";
    echo "  - Conferme pagamento: " . count($pagamenti) . "\n";

    echo "\n[" . date('Y-m-d H:i:s') . "] Completato\n";

    // Log su file
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/email-scadenze-' . date('Y-m') . '.log';
    $logContent = sprintf(
        "[%s] Email inviate: %d (Promemoria: %d, Solleciti: %d, Conferme: %d)\n",
        date('Y-m-d H:i:s'),
        $emailInviate,
        count($fattureInScadenza),
        count($solleciti7giorni),
        count($pagamenti)
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);

} catch (Exception $e) {
    echo "ERRORE CRITICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
