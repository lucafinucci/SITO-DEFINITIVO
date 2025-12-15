<?php
/**
 * Database Connection
 * Usa environment variables per sicurezza
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/error-handler.php';

try {
    $dbHost = Config::get('DB_HOST', 'localhost');
    $dbName = Config::get('DB_NAME', 'finch_ai_clienti');
    $dbUser = Config::get('DB_USER', 'root');
    $dbPass = Config::get('DB_PASS', '');

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ]
    );

    ErrorHandler::logAccess('Database connection established');

} catch (PDOException $e) {
    ErrorHandler::logError('Database connection failed: ' . $e->getMessage());

    if (Config::isDebug()) {
        throw $e;
    }

    http_response_code(500);
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Errore Database - Finch-AI</title>
    <style>
        body { font-family: system-ui; padding: 40px; background: #0b1220; color: #e5e7eb; text-align: center; }
        .error-box { max-width: 500px; margin: 0 auto; background: #1f2937; padding: 40px; border-radius: 16px; }
        h1 { color: #ef4444; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⚠️ Errore di connessione al database</h1>
        <p>Impossibile connettersi al database. Riprova più tardi.</p>
    </div>
</body>
</html>';
    exit;
}
