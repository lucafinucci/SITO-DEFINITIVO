<?php
// Avvia la sessione con impostazioni più restrittive
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax',
    ]);
    session_start();
}

// CSRF token per richieste POST
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verifica autenticazione
if (!isset($_SESSION['cliente_id'])) {
    header('Location: /area-clienti/login.php');
    exit;
}
