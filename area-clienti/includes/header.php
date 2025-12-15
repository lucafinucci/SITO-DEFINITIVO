<?php
// Assicura la sessione (non forza autenticazione qui per riuso in login)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax',
    ]);
    session_start();
}
?>
<header class="topbar">
  <div class="container topbar-inner">
    <div class="brand">
      <span class="logo-dot"></span>
      <span class="brand-name">Finch-AI · Area Clienti</span>
    </div>
    <div class="topbar-actions">
      <?php if (!empty($_SESSION['cliente_email'])): ?>
        <span class="user-email"><?php echo htmlspecialchars($_SESSION['cliente_email']); ?></span>
      <?php endif; ?>
      <a class="btn ghost" href="/area-clienti/logout.php">Esci</a>
    </div>
  </div>
</header>
