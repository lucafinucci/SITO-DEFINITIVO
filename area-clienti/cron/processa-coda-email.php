<?php
/**
 * Script CRON per processare coda email
 *
 * Processa email in coda e le invia
 *
 * Configurazione CRON consigliata:
 * */5 * * * * php /path/to/processa-coda-email.php
 * (Esegui ogni 5 minuti)
 */

// Verifica che lo script sia eseguito da CLI
if (php_sapi_name() !== 'cli') {
    die('Questo script può essere eseguito solo da command line');
}

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/email-manager.php';

echo "[" . date('Y-m-d H:i:s') . "] Avvio processamento coda email\n";

try {
    $emailManager = new EmailManager($pdo);

    // Processa massimo 50 email per esecuzione
    $risultato = $emailManager->processQueue(50);

    echo "  - Email processate: {$risultato['processati']}\n";
    echo "  - Inviate con successo: {$risultato['inviati']}\n";
    echo "  - Fallite: {$risultato['falliti']}\n";

    // Statistiche generali
    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) AS totale,
            SUM(CASE WHEN stato = "in_coda" THEN 1 ELSE 0 END) AS in_coda,
            SUM(CASE WHEN stato = "processing" THEN 1 ELSE 0 END) AS processing,
            SUM(CASE WHEN stato = "fallita" THEN 1 ELSE 0 END) AS fallite
        FROM email_queue
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ');
    $stmt->execute();
    $stats = $stmt->fetch();

    echo "\n=== Stato Coda (ultime 24h) ===\n";
    echo "Totale: {$stats['totale']}\n";
    echo "In coda: {$stats['in_coda']}\n";
    echo "In elaborazione: {$stats['processing']}\n";
    echo "Fallite: {$stats['fallite']}\n";

    // Alert se troppe email in coda
    if ($stats['in_coda'] > 100) {
        echo "\n⚠️ ATTENZIONE: Più di 100 email in coda!\n";
    }

    // Alert se troppe fallite
    if ($stats['fallite'] > 10) {
        echo "\n⚠️ ATTENZIONE: Più di 10 email fallite nelle ultime 24h!\n";
    }

    echo "\n[" . date('Y-m-d H:i:s') . "] Completato\n";

    // Log su file
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/email-queue-' . date('Y-m') . '.log';
    $logContent = sprintf(
        "[%s] Processate: %d - Inviate: %d - Fallite: %d - In coda: %d\n",
        date('Y-m-d H:i:s'),
        $risultato['processati'],
        $risultato['inviati'],
        $risultato['falliti'],
        $stats['in_coda']
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);

} catch (Exception $e) {
    echo "ERRORE CRITICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
