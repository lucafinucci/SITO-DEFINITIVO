<?php
// Genera hash password corretti per l'area clienti

$passwords = [
    'Admin123!' => 'admin@finch-ai.it',
    'Demo123!' => 'demo@finch-ai.it',
    'Cliente123!' => 'cliente@example.com'
];

echo "=== PASSWORD HASH GENERATI ===\n\n";

foreach ($passwords as $password => $email) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n\n";
}

echo "\n=== SQL UPDATE STATEMENTS ===\n\n";

echo "UPDATE utenti SET password_hash = '" . password_hash('Admin123!', PASSWORD_BCRYPT) . "' WHERE email = 'admin@finch-ai.it';\n";
echo "UPDATE utenti SET password_hash = '" . password_hash('Demo123!', PASSWORD_BCRYPT) . "' WHERE email = 'demo@finch-ai.it';\n";
echo "UPDATE utenti SET password_hash = '" . password_hash('Cliente123!', PASSWORD_BCRYPT) . "' WHERE email = 'cliente@example.com';\n";
