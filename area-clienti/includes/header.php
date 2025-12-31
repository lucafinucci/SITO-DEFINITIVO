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
<header>
  <div class="header-content">
    <a href="/area-clienti/dashboard.php" class="brand">
      <span class="logo-dot"></span>
      <span class="brand-name">Finch-AI</span>
    </a>
    <div class="nav-links">
      <?php if (!empty($_SESSION['cliente_email'])): ?>
        <span class="muted small"><?php echo htmlspecialchars($_SESSION['cliente_nome_completo'] ?? $_SESSION['cliente_email']); ?></span>
        <a href="/area-clienti/profilo.php" class="btn ghost small">👤 Profilo</a>
        <a href="/area-clienti/logout.php" class="btn ghost small">Esci</a>
      <?php endif; ?>
    </div>
  </div>
</header>
