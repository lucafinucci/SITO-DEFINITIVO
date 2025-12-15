<?php
// Test password verification

require __DIR__ . '/area-clienti/includes/db.php';

echo "=== TEST PASSWORD VERIFICATION ===\n\n";

// Prendi utenti dal database
$stmt = $pdo->query('SELECT id, email, password_hash FROM utenti');
$users = $stmt->fetchAll();

$passwords = [
    'admin@finch-ai.it' => 'Admin123!',
    'demo@finch-ai.it' => 'Demo123!',
    'cliente@example.com' => 'Cliente123!'
];

foreach ($users as $user) {
    $email = $user['email'];
    $dbHash = $user['password_hash'];
    $testPassword = $passwords[$email] ?? '';

    echo "Email: $email\n";
    echo "Hash DB (primi 30 char): " . substr($dbHash, 0, 30) . "...\n";
    echo "Password test: $testPassword\n";

    if ($testPassword) {
        $verify = password_verify($testPassword, $dbHash);
        echo "Verifica: " . ($verify ? "✓ OK" : "✗ FAIL") . "\n";

        if (!$verify) {
            // Prova a generare nuovo hash e confrontare
            $newHash = password_hash($testPassword, PASSWORD_BCRYPT);
            echo "Nuovo hash generato: " . substr($newHash, 0, 30) . "...\n";
            echo "SQL per aggiornare:\n";
            echo "UPDATE utenti SET password_hash = '$newHash' WHERE email = '$email';\n";
        }
    }

    echo "\n";
}

echo "\n=== TEST MANUALE ===\n";
echo "Inserisci email admin e verifica:\n";
$adminStmt = $pdo->prepare('SELECT * FROM utenti WHERE email = ?');
$adminStmt->execute(['admin@finch-ai.it']);
$admin = $adminStmt->fetch();

if ($admin) {
    echo "Utente trovato: {$admin['email']}\n";
    echo "Attivo: " . ($admin['attivo'] ? 'SI' : 'NO') . "\n";
    echo "MFA enabled: " . ($admin['mfa_enabled'] ? 'SI' : 'NO') . "\n";
    echo "Password hash: " . substr($admin['password_hash'], 0, 40) . "...\n";

    $testPwd = 'Admin123!';
    $result = password_verify($testPwd, $admin['password_hash']);
    echo "\nTest password_verify('Admin123!', hash): " . ($result ? 'TRUE ✓' : 'FALSE ✗') . "\n";
}
