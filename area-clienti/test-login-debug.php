<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Login Debug</h2>";

// Test 1: Connessione DB diretta
echo "<h3>1. Test Connessione Diretta</h3>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=finch_ai_clienti;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Connessione DB OK</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore DB: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Verifica utenti
echo "<h3>2. Verifica Utenti</h3>";
$stmt = $pdo->query("SELECT id, email, ruolo, attivo FROM utenti");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "<p style='color: red;'>⚠ PROBLEMA: Nessun utente nel database!</p>";
    echo "<p>Devo creare utenti di test?</p>";

    // Crea utenti di test
    echo "<h4>Creazione utenti di test...</h4>";

    // Admin
    $stmt = $pdo->prepare("
        INSERT INTO utenti (email, password_hash, nome, cognome, ruolo, attivo)
        VALUES (?, ?, ?, ?, ?, TRUE)
    ");

    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt->execute(['admin@finch-ai.it', $adminHash, 'Admin', 'Finch-AI', 'admin']);
    echo "<p style='color: green;'>✓ Utente admin creato: admin@finch-ai.it / admin123</p>";

    // Cliente
    $clientHash = password_hash('cliente123', PASSWORD_DEFAULT);
    $stmt->execute(['cliente@test.it', $clientHash, 'Mario', 'Rossi', 'cliente']);
    echo "<p style='color: green;'>✓ Utente cliente creato: cliente@test.it / cliente123</p>";

    echo "<p><a href='login.php'>Vai al Login</a></p>";

} else {
    echo "<p style='color: green;'>✓ Trovati " . count($users) . " utenti</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Email</th><th>Ruolo</th><th>Attivo</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['ruolo']}</td>";
        echo "<td>" . ($user['attivo'] ? 'Sì' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test 3: Verifica include
echo "<h3>3. Test Include Files</h3>";
echo "<ul>";
echo "<li>config.php: " . (file_exists(__DIR__ . '/includes/config.php') ? '✓' : '✗') . "</li>";
echo "<li>auth.php: " . (file_exists(__DIR__ . '/includes/auth.php') ? '✓' : '✗') . "</li>";
echo "<li>db.php: " . (file_exists(__DIR__ . '/includes/db.php') ? '✓' : '✗') . "</li>";
echo "<li>error-handler.php: " . (file_exists(__DIR__ . '/includes/error-handler.php') ? '✓' : '✗') . "</li>";
echo "</ul>";

// Test 4: Prova caricamento include
echo "<h3>4. Test Caricamento Include</h3>";
try {
    require_once __DIR__ . '/includes/config.php';
    echo "<p style='color: green;'>✓ Config caricato</p>";

    require_once __DIR__ . '/includes/error-handler.php';
    echo "<p style='color: green;'>✓ ErrorHandler caricato</p>";

    // Verifica che i metodi siano pubblici
    if (method_exists('ErrorHandler', 'logError')) {
        $reflection = new ReflectionMethod('ErrorHandler', 'logError');
        echo "<p>logError è: " . ($reflection->isPublic() ? '<span style="color: green;">PUBLIC ✓</span>' : '<span style="color: red;">PRIVATE ✗</span>') . "</p>";
    }

    if (method_exists('ErrorHandler', 'logAccess')) {
        $reflection = new ReflectionMethod('ErrorHandler', 'logAccess');
        echo "<p>logAccess è: " . ($reflection->isPublic() ? '<span style="color: green;">PUBLIC ✓</span>' : '<span style="color: red;">PRIVATE ✗</span>') . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Errore: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Vai al Login</a> | <a href='dashboard.php'>Vai alla Dashboard</a></p>";
