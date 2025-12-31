<?php
/**
 * Script CRON per verifica scadenze fatture
 *
 * Esegue le seguenti operazioni:
 * 1. Aggiorna stato fatture scadute
 * 2. Identifica fatture da sollecitare
 * 3. Genera report scadenze
 *
 * Configurazione CRON consigliata:
 * 0 6 * * * php /path/to/verifica-scadenze-fatture.php
 * (Esegui ogni giorno alle 06:00)
 */

// Verifica che lo script sia eseguito da CLI
if (php_sapi_name() !== 'cli') {
    die('Questo script può essere eseguito solo da command line');
}

require __DIR__ . '/../includes/db.php';

echo "[" . date('Y-m-d H:i:s') . "] Avvio verifica scadenze fatture\n";

try {
    $oggi = date('Y-m-d');

    // 1. Aggiorna fatture scadute (non pagate e oltre scadenza)
    echo "Step 1: Aggiornamento fatture scadute...\n";

    $stmt = $pdo->prepare('
        UPDATE fatture
        SET stato = "scaduta"
        WHERE stato IN ("emessa", "inviata")
          AND data_scadenza < :oggi
    ');
    $stmt->execute(['oggi' => $oggi]);
    $fattureScadute = $stmt->rowCount();

    echo "  - Fatture marcate come scadute: $fattureScadute\n";

    // 2. Identifica fatture da sollecitare
    echo "\nStep 2: Identificazione fatture da sollecitare...\n";

    // Sollecito 1: 7 giorni dopo scadenza
    $stmt = $pdo->prepare('
        SELECT
            f.id,
            f.numero_fattura,
            f.totale,
            f.data_scadenza,
            f.data_emissione,
            u.id AS cliente_id,
            u.azienda,
            u.nome,
            u.cognome,
            u.email,
            DATEDIFF(:oggi, f.data_scadenza) AS giorni_scadenza
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.stato = "scaduta"
          AND f.data_scadenza = DATE_SUB(:oggi, INTERVAL 7 DAY)
    ');
    $stmt->execute(['oggi' => $oggi]);
    $solleciti7giorni = $stmt->fetchAll();

    echo "  - Fatture per sollecito 7 giorni: " . count($solleciti7giorni) . "\n";

    // Sollecito 2: 15 giorni dopo scadenza
    $stmt = $pdo->prepare('
        SELECT
            f.id,
            f.numero_fattura,
            f.totale,
            f.data_scadenza,
            f.data_emissione,
            u.id AS cliente_id,
            u.azienda,
            u.nome,
            u.cognome,
            u.email,
            DATEDIFF(:oggi, f.data_scadenza) AS giorni_scadenza
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.stato = "scaduta"
          AND f.data_scadenza = DATE_SUB(:oggi, INTERVAL 15 DAY)
    ');
    $stmt->execute(['oggi' => $oggi]);
    $solleciti15giorni = $stmt->fetchAll();

    echo "  - Fatture per sollecito 15 giorni: " . count($solleciti15giorni) . "\n";

    // Sollecito 3: 30 giorni dopo scadenza (URGENTE)
    $stmt = $pdo->prepare('
        SELECT
            f.id,
            f.numero_fattura,
            f.totale,
            f.data_scadenza,
            f.data_emissione,
            u.id AS cliente_id,
            u.azienda,
            u.nome,
            u.cognome,
            u.email,
            DATEDIFF(:oggi, f.data_scadenza) AS giorni_scadenza
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.stato = "scaduta"
          AND f.data_scadenza = DATE_SUB(:oggi, INTERVAL 30 DAY)
    ');
    $stmt->execute(['oggi' => $oggi]);
    $solleciti30giorni = $stmt->fetchAll();

    echo "  - Fatture per sollecito 30 giorni (URGENTE): " . count($solleciti30giorni) . "\n";

    // 3. Registra solleciti da inviare
    echo "\nStep 3: Registrazione solleciti...\n";

    $pdo->beginTransaction();

    $totaliSolleciti = 0;

    foreach ($solleciti7giorni as $fattura) {
        registraSollecito($pdo, $fattura['id'], 1, 'primo_sollecito',
            "Gentile sollecito di pagamento - 7 giorni dalla scadenza");
        $totaliSolleciti++;
    }

    foreach ($solleciti15giorni as $fattura) {
        registraSollecito($pdo, $fattura['id'], 2, 'secondo_sollecito',
            "Secondo sollecito di pagamento - 15 giorni dalla scadenza");
        $totaliSolleciti++;
    }

    foreach ($solleciti30giorni as $fattura) {
        registraSollecito($pdo, $fattura['id'], 3, 'sollecito_urgente',
            "URGENTE: Ultimo sollecito di pagamento - 30 giorni dalla scadenza");
        $totaliSolleciti++;
    }

    $pdo->commit();

    echo "  - Solleciti registrati: $totaliSolleciti\n";

    // 4. Statistiche generali
    echo "\nStep 4: Statistiche generali...\n";

    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) AS totale,
            SUM(CASE WHEN stato = "scaduta" THEN 1 ELSE 0 END) AS scadute,
            SUM(CASE WHEN stato IN ("emessa", "inviata") AND data_scadenza < :oggi THEN totale ELSE 0 END) AS importo_scaduto,
            SUM(CASE WHEN stato = "scaduta" THEN totale ELSE 0 END) AS importo_da_recuperare
        FROM fatture
        WHERE stato IN ("emessa", "inviata", "scaduta")
    ');
    $stmt->execute(['oggi' => $oggi]);
    $stats = $stmt->fetch();

    echo "  - Fatture non pagate totali: " . $stats['totale'] . "\n";
    echo "  - Fatture scadute: " . $stats['scadute'] . "\n";
    echo "  - Importo scaduto: €" . number_format($stats['importo_scaduto'], 2) . "\n";
    echo "  - Importo da recuperare: €" . number_format($stats['importo_da_recuperare'], 2) . "\n";

    // 5. Report clienti con più fatture scadute
    echo "\nStep 5: Clienti con maggiori debiti...\n";

    $stmt = $pdo->prepare('
        SELECT
            u.id,
            u.azienda,
            u.email,
            COUNT(f.id) AS num_fatture_scadute,
            SUM(f.totale) AS totale_debito
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.stato = "scaduta"
        GROUP BY u.id
        HAVING num_fatture_scadute >= 2
        ORDER BY totale_debito DESC
        LIMIT 10
    ');
    $stmt->execute();
    $clientiDebito = $stmt->fetchAll();

    if (!empty($clientiDebito)) {
        foreach ($clientiDebito as $cliente) {
            echo "  - {$cliente['azienda']}: {$cliente['num_fatture_scadute']} fatture, €" .
                 number_format($cliente['totale_debito'], 2) . "\n";
        }
    } else {
        echo "  - Nessun cliente con debiti multipli\n";
    }

    echo "\n=== Riepilogo ===\n";
    echo "Fatture scadute aggiornate: $fattureScadute\n";
    echo "Solleciti registrati: $totaliSolleciti\n";
    echo "  - Primo sollecito (7g): " . count($solleciti7giorni) . "\n";
    echo "  - Secondo sollecito (15g): " . count($solleciti15giorni) . "\n";
    echo "  - Sollecito urgente (30g): " . count($solleciti30giorni) . "\n";

    echo "\n[" . date('Y-m-d H:i:s') . "] Completato\n";

    // Log su file
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/scadenze-' . date('Y-m') . '.log';
    $logContent = sprintf(
        "[%s] Verifica completata - Scadute: %d - Solleciti: %d (7g:%d, 15g:%d, 30g:%d)\n",
        date('Y-m-d H:i:s'),
        $fattureScadute,
        $totaliSolleciti,
        count($solleciti7giorni),
        count($solleciti15giorni),
        count($solleciti30giorni)
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

function registraSollecito($pdo, $fatturaId, $numero, $tipo, $oggetto) {
    // Verifica se sollecito già inviato
    $stmt = $pdo->prepare('
        SELECT id FROM fatture_solleciti
        WHERE fattura_id = :fattura_id AND tipo = :tipo
        LIMIT 1
    ');
    $stmt->execute(['fattura_id' => $fatturaId, 'tipo' => $tipo]);

    if ($stmt->fetch()) {
        return; // Sollecito già registrato
    }

    // Registra nuovo sollecito
    $stmt = $pdo->prepare('
        INSERT INTO fatture_solleciti (
            fattura_id,
            tipo,
            numero_sollecito,
            oggetto,
            stato,
            data_creazione
        ) VALUES (
            :fattura_id,
            :tipo,
            :numero,
            :oggetto,
            "da_inviare",
            CURRENT_TIMESTAMP
        )
    ');

    $stmt->execute([
        'fattura_id' => $fatturaId,
        'tipo' => $tipo,
        'numero' => $numero,
        'oggetto' => $oggetto
    ]);
}
