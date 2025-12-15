<?php
/**
 * Script di debug per verificare la connessione
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DEBUG AREA CLIENTI</h1>";
echo "<hr>";

// 1. Verifica file .env
echo "<h2>1. File .env</h2>";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "✅ File .env trovato<br>";
    $env = file_get_contents($envFile);
    echo "<pre>" . htmlspecialchars($env) . "</pre>";
} else {
    echo "❌ File .env NON trovato in: " . htmlspecialchars($envFile) . "<br>";
}

echo "<hr>";

// 2. Carica configurazione manualmente (senza dipendenze)
echo "<h2>2. Configurazione</h2>";

// Leggi .env manualmente
$config = [
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'finch_ai_clienti',
    'DB_USER' => 'root',
    'DB_PASS' => ''
];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $config[trim($key)] = trim($value);
        }
    }
}

$dbHost = $config['DB_HOST'];
$dbName = $config['DB_NAME'];
$dbUser = $config['DB_USER'];
$dbPass = $config['DB_PASS'];

echo "DB_HOST: <strong>" . htmlspecialchars($dbHost) . "</strong><br>";
echo "DB_NAME: <strong>" . htmlspecialchars($dbName) . "</strong><br>";
echo "DB_USER: <strong>" . htmlspecialchars($dbUser) . "</strong><br>";
echo "DB_PASS: <strong>" . (empty($dbPass) ? '(vuota)' : '***') . "</strong><br>";

echo "<hr>";

// 3. Test connessione
echo "<h2>3. Test Connessione Database</h2>";

try {
    echo "Tentativo di connessione a: mysql:host={$dbHost};charset=utf8mb4<br><br>";

    // Prima prova senza database specifico
    $pdoTest = new PDO(
        "mysql:host={$dbHost};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✅ Connessione MySQL riuscita!<br><br>";

    // Verifica se esiste il database
    $stmt = $pdoTest->query("SHOW DATABASES LIKE '{$dbName}'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "✅ Database '{$dbName}' trovato<br><br>";

        // Connetti al database specifico
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        echo "✅ Connessione al database '{$dbName}' riuscita!<br><br>";

        // Verifica tabelle
        echo "<h3>Tabelle presenti:</h3>";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tables)) {
            echo "⚠️ Il database esiste ma non contiene tabelle<br>";
            echo "<p><a href='/RESET_DB_NOW.php' style='background: #22d3ee; color: #0b1220; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 10px;'>CREA TABELLE</a></p>";
        } else {
            echo "<ul>";
            foreach ($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                echo "<li><strong>{$table}</strong>: {$count} record</li>";
            }
            echo "</ul>";

            echo "<br><strong style='color: green;'>✅ TUTTO OK! Puoi usare l'Area Clienti</strong><br>";
            echo "<p><a href='/area-clienti/login.php' style='background: #22d3ee; color: #0b1220; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 10px;'>VAI AL LOGIN</a></p>";
        }

    } else {
        echo "❌ Database '{$dbName}' NON TROVATO<br><br>";
        echo "<p>Il database deve essere creato. Clicca qui:</p>";
        echo "<p><a href='/RESET_DB_NOW.php' style='background: #22d3ee; color: #0b1220; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block;'>CREA DATABASE</a></p>";
    }

} catch (PDOException $e) {
    echo "❌ <strong>ERRORE:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";

    echo "<h3>Soluzioni possibili:</h3>";
    echo "<ol>";
    echo "<li>Verifica che MySQL sia avviato in XAMPP</li>";
    echo "<li>Verifica che DB_HOST in .env sia '127.0.0.1' (non 'localhost')</li>";
    echo "<li>Verifica che la porta 3306 sia aperta</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p style='color: #9ca3af; font-size: 12px;'>Debug script - " . date('Y-m-d H:i:s') . "</p>";
?>
<style>
body {
    font-family: system-ui, -apple-system, sans-serif;
    padding: 40px;
    background: #0b1220;
    color: #e5e7eb;
    max-width: 900px;
    margin: 0 auto;
}
h1, h2, h3 { color: #22d3ee; }
pre {
    background: #1f2937;
    padding: 15px;
    border-radius: 8px;
    overflow-x: auto;
}
hr {
    border: none;
    border-top: 1px solid #374151;
    margin: 30px 0;
}
ul, ol {
    line-height: 1.8;
}
</style>
