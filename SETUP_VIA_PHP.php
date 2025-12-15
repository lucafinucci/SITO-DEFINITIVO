<?php
/**
 * Setup database via PHP - Soluzione definitiva
 * Usa PDO che supporta meglio TCP/IP
 */

echo "\n================================================\n";
echo "SETUP DATABASE FINCH-AI VIA PHP\n";
echo "================================================\n\n";

try {
    echo "Step 1: Connessione a MySQL (127.0.0.1)...\n";

    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✓ Connessione riuscita!\n\n";

    echo "Step 2: Lettura script SQL...\n";

    $sqlFile = __DIR__ . '/database/ESEGUI_IN_PHPMYADMIN.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File SQL non trovato: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    echo "✓ Script SQL caricato (" . strlen($sql) . " bytes)\n\n";

    echo "Step 3: Esecuzione script...\n";
    echo "(Questo può richiedere alcuni secondi)\n\n";

    // Esegui lo script
    $pdo->exec($sql);

    echo "✓ Script eseguito con successo!\n\n";

    echo "Step 4: Verifica database creato...\n";

    // Verifica database
    $stmt = $pdo->query("SHOW DATABASES LIKE 'finch_ai_clienti'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Database non creato!");
    }
    echo "✓ Database 'finch_ai_clienti' trovato\n\n";

    // Seleziona database
    $pdo->exec("USE finch_ai_clienti");

    // Verifica tabelle
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Step 5: Verifica tabelle create...\n";
    echo "Trovate " . count($tables) . " tabelle:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";

    // Verifica utenti
    $stmt = $pdo->query("SELECT email, nome, cognome, ruolo FROM utenti");
    $users = $stmt->fetchAll();

    echo "Step 6: Verifica utenti inseriti...\n";
    echo "Trovati " . count($users) . " utenti:\n";
    foreach ($users as $user) {
        echo "  - {$user['email']} ({$user['nome']} {$user['cognome']}) - Ruolo: {$user['ruolo']}\n";
    }
    echo "\n";

    echo "================================================\n";
    echo "SUCCESS! Setup completato con successo!\n";
    echo "================================================\n\n";

    echo "CREDENZIALI DI TEST:\n";
    echo "Email:    demo@finch-ai.it\n";
    echo "Password: Demo123!\n\n";

    echo "PROSSIMO PASSO:\n";
    echo "Apri il browser e vai a:\n";
    echo "http://localhost/area-clienti/login.php\n\n";

} catch (PDOException $e) {
    echo "\n❌ ERRORE DATABASE:\n";
    echo $e->getMessage() . "\n\n";

    echo "POSSIBILI SOLUZIONI:\n";
    echo "1. Verifica che MySQL sia avviato in XAMPP\n";
    echo "2. Riavvia MySQL dal Control Panel XAMPP\n";
    echo "3. Controlla che la porta 3306 sia libera\n\n";

    exit(1);

} catch (Exception $e) {
    echo "\n❌ ERRORE:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
