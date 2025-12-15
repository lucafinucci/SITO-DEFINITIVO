<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Test Database</title>
    <style>
        body {
            font-family: system-ui;
            padding: 40px;
            background: #0b1220;
            color: #e5e7eb;
            max-width: 800px;
            margin: 0 auto;
        }
        h1 { color: #22d3ee; }
        .box {
            background: #1f2937;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .ok { color: #10b981; }
        .error { color: #ef4444; }
        pre {
            background: #0f172a;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        a.btn {
            display: inline-block;
            background: #22d3ee;
            color: #0b1220;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>🔍 Test Database Finch-AI</h1>

    <?php
    // Test 1: File .env
    echo '<div class="box">';
    echo '<h2>1. File .env</h2>';
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        echo '<p class="ok">✅ File .env trovato</p>';

        // Leggi configurazione
        $config = [];
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $config[trim($key)] = trim($value);
            }
        }

        echo '<pre>';
        echo "DB_HOST: " . ($config['DB_HOST'] ?? 'non impostato') . "\n";
        echo "DB_NAME: " . ($config['DB_NAME'] ?? 'non impostato') . "\n";
        echo "DB_USER: " . ($config['DB_USER'] ?? 'non impostato') . "\n";
        echo '</pre>';
    } else {
        echo '<p class="error">❌ File .env NON trovato</p>';
        $config = [
            'DB_HOST' => '127.0.0.1',
            'DB_NAME' => 'finch_ai_clienti',
            'DB_USER' => 'root',
            'DB_PASS' => ''
        ];
    }
    echo '</div>';

    // Test 2: Connessione MySQL
    echo '<div class="box">';
    echo '<h2>2. Connessione MySQL</h2>';

    $dbHost = $config['DB_HOST'] ?? '127.0.0.1';
    $dbName = $config['DB_NAME'] ?? 'finch_ai_clienti';
    $dbUser = $config['DB_USER'] ?? 'root';
    $dbPass = $config['DB_PASS'] ?? '';

    try {
        // Connessione base
        $pdo = new PDO(
            "mysql:host={$dbHost};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        echo '<p class="ok">✅ Connesso a MySQL su ' . htmlspecialchars($dbHost) . '</p>';

        // Verifica database
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
        if ($stmt->fetch()) {
            echo '<p class="ok">✅ Database "' . htmlspecialchars($dbName) . '" esiste</p>';

            // Connetti al database
            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Lista tabelle
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            if (empty($tables)) {
                echo '<p class="error">⚠️ Database vuoto - nessuna tabella trovata</p>';
                echo '<a href="/RESET_DB_NOW.php" class="btn">Crea Tabelle</a>';
            } else {
                echo '<p class="ok">✅ Trovate ' . count($tables) . ' tabelle:</p>';
                echo '<ul>';
                foreach ($tables as $table) {
                    $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                    echo '<li><strong>' . htmlspecialchars($table) . '</strong>: ' . $count . ' record</li>';
                }
                echo '</ul>';

                // Verifica utente demo
                $user = $pdo->query("SELECT * FROM utenti WHERE email='demo@finch-ai.it' LIMIT 1")->fetch();
                if ($user) {
                    echo '<p class="ok">✅ Utente demo trovato</p>';
                    echo '<p><strong>Credenziali:</strong><br>';
                    echo 'Email: demo@finch-ai.it<br>';
                    echo 'Password: password</p>';
                    echo '<a href="/area-clienti/login.php" class="btn">Vai al Login</a>';
                } else {
                    echo '<p class="error">⚠️ Utente demo non trovato</p>';
                    echo '<a href="/RESET_DB_NOW.php" class="btn">Ricrea Database</a>';
                }
            }

        } else {
            echo '<p class="error">❌ Database "' . htmlspecialchars($dbName) . '" NON esiste</p>';
            echo '<a href="/RESET_DB_NOW.php" class="btn">Crea Database</a>';
        }

    } catch (PDOException $e) {
        echo '<p class="error">❌ Errore: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<h3>Possibili soluzioni:</h3>';
        echo '<ul>';
        echo '<li>Verifica che MySQL sia avviato in XAMPP</li>';
        echo '<li>Verifica che DB_HOST sia "127.0.0.1" nel file .env</li>';
        echo '<li>Verifica username e password</li>';
        echo '</ul>';
    }
    echo '</div>';
    ?>

    <div class="box">
        <h2>3. Informazioni Server</h2>
        <pre><?php
        echo "PHP Version: " . phpversion() . "\n";
        echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
        echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
        echo "Current File: " . __FILE__ . "\n";
        ?></pre>
    </div>
</body>
</html>
