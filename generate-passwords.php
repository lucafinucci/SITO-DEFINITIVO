<?php
/**
 * Genera password hash per gli utenti demo
 * Eseguire da command line: C:\xampp\php\php.exe generate-passwords.php
 */

echo "\n=== GENERAZIONE PASSWORD HASH ===\n\n";

$passwords = [
    'Admin123!' => 'admin@finch-ai.it',
    'Demo123!' => 'demo@finch-ai.it',
    'Cliente123!' => 'cliente@example.com',
];

foreach ($passwords as $password => $email) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Email:    $email\n";
    echo "Password: $password\n";
    echo "Hash:     $hash\n\n";
}

echo "=== SQL UPDATE STATEMENTS ===\n\n";

foreach ($passwords as $password => $email) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "UPDATE utenti SET password_hash = '$hash' WHERE email = '$email';\n";
}

echo "\n✅ Copia gli statement SQL e eseguili in phpMyAdmin!\n\n";
