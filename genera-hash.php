<?php
// Genera hash password per Demo123!
$password = 'Demo123!';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\n";

// Verifica che funzioni
if (password_verify($password, $hash)) {
    echo "✓ Verifica OK!\n";
} else {
    echo "✗ Verifica FALLITA!\n";
}
