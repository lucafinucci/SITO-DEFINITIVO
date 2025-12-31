<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Connessione Database</h2>";

// Test connessione
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=finch_ai_clienti;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "<p style='color: green;'>✓ Connessione al database OK</p>";

    // Verifica tabelle
    echo "<h3>Tabelle presenti:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Verifica utenti
    echo "<h3>Utenti nel database:</h3>";
    $stmt = $pdo->query("SELECT id, email, ruolo, attivo, created_at FROM utenti ORDER BY id");
    $utenti = $stmt->fetchAll();

    if (empty($utenti)) {
        echo "<p style='color: red;'>⚠ Nessun utente trovato nel database!</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Email</th><th>Ruolo</th><th>Attivo</th><th>Creato</th></tr>";
        foreach ($utenti as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['ruolo']}</td>";
            echo "<td>" . ($user['attivo'] ? 'Sì' : 'No') . "</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore connessione database: " . $e->getMessage() . "</p>";
}
