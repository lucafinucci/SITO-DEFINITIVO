<?php
require __DIR__ . '/area-clienti/includes/db.php';

echo "=== AGGIORNAMENTO PASSWORD ===\n\n";

$passwords = [
    'admin@finch-ai.it' => 'Admin123!',
    'demo@finch-ai.it' => 'Demo123!',
    'cliente@example.com' => 'Cliente123!'
];

foreach ($passwords as $email => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('UPDATE utenti SET password_hash = ? WHERE email = ?');
    $result = $stmt->execute([$hash, $email]);

    if ($result) {
        // Verifica
        $checkStmt = $pdo->prepare('SELECT password_hash FROM utenti WHERE email = ?');
        $checkStmt->execute([$email]);
        $user = $checkStmt->fetch();

        $verify = password_verify($password, $user['password_hash']);

        echo "Email: $email\n";
        echo "Password: $password\n";
        echo "Hash: " . substr($hash, 0, 40) . "...\n";
        echo "Verifica: " . ($verify ? "✓ OK" : "✗ FAIL") . "\n";
        echo "\n";
    } else {
        echo "✗ ERRORE aggiornamento $email\n\n";
    }
}

echo "=== COMPLETATO ===\n";
