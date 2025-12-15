<?php
/**
 * Inizializzazione database - Usa connessione senza TCP
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Init DB</title>";
echo "<style>body{font-family:monospace;max-width:900px;margin:30px auto;padding:20px;background:#0b1220;color:#e2e8f0;}";
echo ".success{color:#10b981;padding:10px;background:rgba(16,185,129,0.1);border-left:4px solid #10b981;margin:10px 0;}";
echo ".error{color:#f87171;padding:10px;background:rgba(248,113,113,0.1);border-left:4px solid #f87171;margin:10px 0;}";
echo ".info{color:#22d3ee;padding:10px;background:rgba(34,211,238,0.1);border-left:4px solid #22d3ee;margin:10px 0;}";
echo "pre{background:#1f2937;padding:15px;border-radius:8px;overflow-x:auto;} h1{color:#22d3ee;}</style>";
echo "</head><body>";

echo "<h1>🚀 Inizializzazione Database Finch-AI</h1>";

try {
    // Usa localhost senza forzare TCP (usa socket Unix su Windows)
    echo "<div class='info'><strong>📡 Connessione MySQL (via socket)...</strong></div>";

    $pdo = new PDO(
        "mysql:host=localhost;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "<div class='success'>✓ Connessione riuscita!</div>";

    // Crea database
    echo "<div class='info'><strong>📁 Creazione database...</strong></div>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<div class='success'>✓ Database 'finch_ai_clienti' creato</div>";

    // Seleziona database
    $pdo->exec("USE finch_ai_clienti");

    // Leggi e esegui schema
    echo "<div class='info'><strong>📋 Creazione tabelle...</strong></div>";
    $schemaPath = __DIR__ . '/database/schema.sql';

    if (!file_exists($schemaPath)) {
        throw new Exception("File schema.sql non trovato: $schemaPath");
    }

    $schemaSql = file_get_contents($schemaPath);
    $pdo->exec($schemaSql);
    echo "<div class='success'>✓ 7 tabelle create con successo</div>";

    // Leggi e esegui seed (utenti demo)
    echo "<div class='info'><strong>👥 Inserimento utenti demo...</strong></div>";
    $seedPath = __DIR__ . '/database/seed.sql';

    if (file_exists($seedPath)) {
        $seedSql = file_get_contents($seedPath);
        $pdo->exec($seedSql);
        echo "<div class='success'>✓ Utenti demo inseriti</div>";
    } else {
        echo "<div class='error'>⚠ File seed.sql non trovato, nessun utente demo caricato</div>";
    }

    // Mostra riepilogo
    echo "<div class='success'><h2>✅ SETUP COMPLETATO!</h2>";
    echo "<p><strong>Database:</strong> finch_ai_clienti</p>";
    echo "<p><strong>Tabelle create:</strong> 7</p>";
    echo "<p><strong>Host connessione:</strong> localhost (socket)</p>";
    echo "</div>";

    echo "<div class='info'><h3>📝 Credenziali Test</h3>";
    echo "<p><strong>Email:</strong> demo@finch-ai.it</p>";
    echo "<p><strong>Password:</strong> Demo123!</p>";
    echo "</div>";

    echo "<div class='info'><h3>🔗 Prossimi Passi</h3>";
    echo "<p>1. Vai su: <a href='http://localhost:5173' style='color:#22d3ee'>http://localhost:5173</a></p>";
    echo "<p>2. Clicca su 'Area Clienti'</p>";
    echo "<p>3. Accedi con le credenziali sopra</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'><strong>❌ ERRORE:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";

    echo "<div class='info'><h3>💡 Soluzioni:</h3>";
    echo "<ol>";
    echo "<li>Verifica che MySQL sia avviato in XAMPP Control Panel</li>";
    echo "<li>Prova ad accedere a phpMyAdmin: <a href='http://localhost/phpmyadmin' style='color:#22d3ee'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Se phpMyAdmin funziona, esegui manualmente il file database/schema.sql da lì</li>";
    echo "</ol></div>";
}

echo "</body></html>";
