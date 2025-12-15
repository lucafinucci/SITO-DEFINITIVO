<?php
require __DIR__ . '/area-clienti/includes/db.php';

$email = 'admin';  // Come nell'immagine
$password = 'Demo123!';

echo "Test Login\n";
echo "================\n\n";

// Cerca utente
$stmt = $pdo->prepare('SELECT id, email, password_hash, nome, cognome FROM utenti WHERE email LIKE :email AND attivo = TRUE LIMIT 1');
$stmt->execute(['email' => '%' . $email . '%']);
$user = $stmt->fetch();

if (!$user) {
    echo "❌ UTENTE NON TROVATO con email: $email\n\n";

    // Mostra tutti gli utenti
    echo "Utenti disponibili:\n";
    $all = $pdo->query('SELECT id, email FROM utenti')->fetchAll();
    foreach ($all as $u) {
        echo "  - {$u['email']}\n";
    }
    exit;
}

echo "✓ Utente trovato: {$user['email']}\n";
echo "Nome: {$user['nome']} {$user['cognome']}\n";
echo "Hash DB: {$user['password_hash']}\n\n";

// Verifica password
if (password_verify($password, $user['password_hash'])) {
    echo "✅ PASSWORD CORRETTA!\n";
} else {
    echo "❌ PASSWORD ERRATA!\n";

    // Genera hash corretto
    $correct_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "\nHash corretto per '$password':\n";
    echo "$correct_hash\n";
}
