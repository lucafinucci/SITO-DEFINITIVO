<?php
require __DIR__ . '/includes/db.php';

// Nuova password per admin
$nuovaPassword = 'admin123';
$passwordHash = password_hash($nuovaPassword, PASSWORD_BCRYPT);

// Aggiorna password admin
$stmt = $pdo->prepare('UPDATE utenti SET password_hash = :password WHERE email = :email');
$stmt->execute([
    'password' => $passwordHash,
    'email' => 'admin@finch-ai.it'
]);

echo "✓ Password amministratore aggiornata con successo!\n\n";
echo "Email: admin@finch-ai.it\n";
echo "Password: {$nuovaPassword}\n";
?>
