<?php
/**
 * Setup Database Automatico
 * Crea database e tabelle per Area Clienti
 */

echo "<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='utf-8'>
    <title>Setup Database - Finch-AI</title>
    <style>
        body { font-family: system-ui; max-width: 800px; margin: 40px auto; padding: 20px; background: #0b1220; color: #e5e7eb; }
        .step { margin: 20px 0; padding: 15px; background: #1f2937; border-radius: 8px; }
        .success { border-left: 4px solid #10b981; }
        .error { border-left: 4px solid #ef4444; }
        .warning { border-left: 4px solid #fbbf24; }
        h1 { color: #22d3ee; }
        pre { background: #0f172a; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: linear-gradient(90deg, #22d3ee, #3b82f6); color: #0b1220; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 10px 5px; }
    </style>
</head>
<body>
    <h1>🔧 Setup Database Area Clienti</h1>
";

// Carica configurazione
require_once __DIR__ . '/area-clienti/includes/config.php';

$dbHost = Config::get('DB_HOST', 'localhost');
$dbName = Config::get('DB_NAME', 'finch_ai_clienti');
$dbUser = Config::get('DB_USER', 'root');
$dbPass = Config::get('DB_PASS', '');

echo "<div class='step'>
    <strong>Configurazione da .env:</strong><br>
    Host: <code>{$dbHost}</code><br>
    Database: <code>{$dbName}</code><br>
    User: <code>{$dbUser}</code><br>
    Password: <code>" . (empty($dbPass) ? '(vuota)' : '***') . "</code>
</div>";

// Step 1: Connessione a MySQL (senza specificare database)
echo "<div class='step'>
    <h3>Step 1: Connessione a MySQL</h3>";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='success'>✅ Connesso a MySQL</p>";
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "❌ Errore connessione MySQL:<br>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<h4>💡 Soluzioni possibili:</h4>";
    echo "<ul>";
    echo "<li>Verifica che MySQL/MariaDB sia avviato in XAMPP</li>";
    echo "<li>Se l'errore è 'Host not allowed', prova a cambiare DB_HOST in .env da 'localhost' a '127.0.0.1'</li>";
    echo "<li>Se l'errore è 'Access denied', verifica user e password in .env</li>";
    echo "</ul>";
    echo "</div></div></body></html>";
    exit;
}
echo "</div>";

// Step 2: Crea database
echo "<div class='step'>
    <h3>Step 2: Creazione Database</h3>";

try {
    // Verifica se esiste
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
        echo "<p class='warning'>⚠️ Database '{$dbName}' già esistente</p>";
    } else {
        $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p class='success'>✅ Database '{$dbName}' creato</p>";
    }

    // Seleziona database
    $pdo->exec("USE `{$dbName}`");

} catch (PDOException $e) {
    echo "<div class='error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// Step 3: Importa schema
echo "<div class='step'>
    <h3>Step 3: Creazione Tabelle</h3>";

$schemaFile = __DIR__ . '/database/schema.sql';

if (!file_exists($schemaFile)) {
    echo "<div class='error'>❌ File schema.sql non trovato in: {$schemaFile}</div>";
    echo "</div></body></html>";
    exit;
}

try {
    $sql = file_get_contents($schemaFile);

    // Esegui ogni statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) { return !empty($s) && !preg_match('/^--/', $s); }
    );

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "<p class='success'>✅ Tabelle create correttamente</p>";

    // Verifica tabelle
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<p>Tabelle create (" . count($tables) . "):</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li><code>{$table}</code></li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<div class='error'>❌ Errore creazione tabelle: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// Step 4: Verifica utenti demo
echo "<div class='step'>
    <h3>Step 4: Utenti Demo</h3>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utenti");
    $count = $stmt->fetch()['count'];

    if ($count > 0) {
        echo "<p class='success'>✅ Trovati {$count} utenti esistenti</p>";

        $stmt = $pdo->query("SELECT email, nome, cognome, mfa_enabled FROM utenti LIMIT 5");
        $users = $stmt->fetchAll();

        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #0f172a;'><th>Email</th><th>Nome</th><th>MFA</th></tr>";
        foreach ($users as $user) {
            echo "<tr style='border-bottom: 1px solid #1f2937;'>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['nome']} {$user['cognome']}</td>";
            echo "<td>" . ($user['mfa_enabled'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

    } else {
        echo "<p class='warning'>⚠️ Nessun utente trovato. Devi importare i dati demo o crearli manualmente.</p>";
        echo "<p>Per creare un utente demo:</p>";
        echo "<pre>INSERT INTO utenti (email, password_hash, nome, cognome, azienda, attivo)
VALUES (
  'demo@finch-ai.it',
  '\$2y\$10\$sy1aBPONuwKREhutPj7BFeX4jMCdRpMOAYHrFTjEn3fI3bERIpJ4q', -- password: \"Demo123!\"
  'Demo',
  'User',
  'Demo Company',
  TRUE
);</pre>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Success
echo "<div class='step success'>
    <h3>🎉 Setup Completato!</h3>
    <p>Il database è stato configurato correttamente.</p>
    <p><strong>Prossimi passi:</strong></p>
    <ol>
        <li>Elimina questo file per sicurezza: <code>SETUP_DATABASE.php</code></li>
        <li>Vai su Area Clienti: <a href='/area-clienti/login.php' class='btn'>Vai al Login</a></li>
        <li>Usa credenziali demo se disponibili</li>
    </ol>
</div>";

echo "<p style='text-align: center; color: #9ca3af; margin-top: 40px;'>
    © 2024 Finch-AI - Database Setup v1.0
</p>";

echo "</body></html>";
