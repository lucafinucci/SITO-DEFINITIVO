<?php
// RESET COMPLETO DATABASE - ESEGUI QUESTO
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>RESET DATABASE</h1>";

// Prova tutte le possibili connessioni
$hosts = ['localhost', '127.0.0.1', 'localhost:3306', '127.0.0.1:3306'];
$pdo = null;

foreach ($hosts as $host) {
    try {
        echo "Provo connessione a: {$host}...<br>";
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "✅ CONNESSO a {$host}!<br><br>";
        break;
    } catch (PDOException $e) {
        echo "❌ Errore: " . $e->getMessage() . "<br><br>";
    }
}

if (!$pdo) {
    die("IMPOSSIBILE CONNETTERSI A MYSQL");
}

// DROP E CREA DATABASE
try {
    echo "Elimino database esistente...<br>";
    $pdo->exec("DROP DATABASE IF EXISTS finch_ai_clienti");
    echo "✅ Database eliminato<br><br>";

    echo "Creo nuovo database...<br>";
    $pdo->exec("CREATE DATABASE finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database creato<br><br>";

    $pdo->exec("USE finch_ai_clienti");

    // Importa schema
    echo "Importo tabelle...<br>";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');

    $statements = explode(';', $schema);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt) && !preg_match('/^--/', $stmt)) {
            $pdo->exec($stmt);
        }
    }
    echo "✅ Tabelle create<br><br>";

    // Crea utente demo
    echo "Creo utente demo...<br>";
    $pdo->exec("INSERT INTO utenti (email, password_hash, nome, cognome, azienda, attivo) VALUES
        ('demo@finch-ai.it', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'User', 'Demo Company', TRUE)");
    echo "✅ Utente demo creato<br><br>";

    echo "<h2 style='color: green;'>✅ FATTO!</h2>";
    echo "<p>Vai su: <a href='/area-clienti/login.php' style='font-size: 20px;'>LOGIN</a></p>";
    echo "<p>Email: demo@finch-ai.it<br>Password: password</p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>ERRORE:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
