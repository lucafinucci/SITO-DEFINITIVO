<?php
/**
 * Script di test connessione database
 * Testa la connessione e crea il database se necessario
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test DB</title>";
echo "<style>body{font-family:monospace;max-width:800px;margin:50px auto;padding:20px;background:#0b1220;color:#e2e8f0;}";
echo ".success{color:#10b981;} .error{color:#f87171;} .info{color:#22d3ee;} pre{background:#1f2937;padding:15px;border-radius:8px;}</style>";
echo "</head><body><h1>Test Connessione Database</h1>";

// Test 1: Connessione senza database
echo "<h2>Test 1: Connessione MySQL</h2>";
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "<p class='success'>✓ Connessione MySQL riuscita!</p>";

    // Test 2: Verifica se il database esiste
    echo "<h2>Test 2: Verifica Database</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'finch_ai_clienti'");
    $dbExists = $stmt->fetch();

    if ($dbExists) {
        echo "<p class='info'>Database 'finch_ai_clienti' già esistente</p>";
    } else {
        echo "<p class='info'>Database 'finch_ai_clienti' non trovato. Creazione in corso...</p>";

        // Crea database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p class='success'>✓ Database 'finch_ai_clienti' creato!</p>";
    }

    // Test 3: Connessione al database specifico
    echo "<h2>Test 3: Connessione al Database</h2>";
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=finch_ai_clienti;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "<p class='success'>✓ Connessione a 'finch_ai_clienti' riuscita!</p>";

    // Test 4: Verifica tabelle
    echo "<h2>Test 4: Verifica Tabelle</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "<p class='info'>Nessuna tabella trovata. Inizializzazione necessaria.</p>";
        echo "<p><a href='/database/init.php' style='color:#22d3ee;'>→ Clicca qui per inizializzare il database</a></p>";
    } else {
        echo "<p class='success'>✓ Trovate " . count($tables) . " tabelle:</p>";
        echo "<pre>" . implode("\n", $tables) . "</pre>";
    }

    // Test 5: Configurazione file
    echo "<h2>Test 5: Configurazione File</h2>";

    $configs = [
        'area-clienti/includes/db.php' => 'c:\Users\oneno\Desktop\SITO\area-clienti\includes\db.php',
        'public/api/config/database.php' => 'c:\Users\oneno\Desktop\SITO\public\api\config\database.php'
    ];

    foreach ($configs as $label => $path) {
        if (file_exists($path)) {
            echo "<p class='success'>✓ $label esistente</p>";
        } else {
            echo "<p class='error'>✗ $label non trovato</p>";
        }
    }

    echo "<h2 class='success'>✅ Test Completato!</h2>";
    echo "<p>Il database è pronto. Prossimi step:</p>";
    echo "<ol>";
    echo "<li>Visitare <a href='/database/init.php' style='color:#22d3ee;'>/database/init.php</a> per creare tabelle e dati demo</li>";
    echo "<li>Testare login su <a href='/area-clienti/login.php' style='color:#22d3ee;'>/area-clienti/login.php</a></li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<p class='error'>✗ Errore: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Possibili soluzioni:</h3>";
    echo "<ul>";
    echo "<li>Verificare che MySQL/MariaDB sia in esecuzione (XAMPP Control Panel)</li>";
    echo "<li>Controllare che la porta 3306 sia aperta</li>";
    echo "<li>Provare a riavviare MySQL da XAMPP</li>";
    echo "</ul>";
}

echo "</body></html>";
