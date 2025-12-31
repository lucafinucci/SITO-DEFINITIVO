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
<nav style="position: sticky; top: 0; left: 0; right: 0; z-index: 50; border-bottom: 1px solid rgba(30, 41, 59, 0.5); background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(24px);">
  <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
    <div style="display: flex; height: 7rem; align-items: center; justify-content: space-between;">
      <!-- Logo GRANDE come pagina principale -->
      <a href="/area-clienti/dashboard.php" style="display: block; width: 100%; max-width: 300px; text-decoration: none;">
        <div style="position: relative; width: 100%;">
          <!-- Glow effect espanso -->
          <div style="position: absolute; inset: 0; border-radius: 24px; background: linear-gradient(135deg, #22d3ee, #3b82f6); opacity: 0.5; filter: blur(60px); transition: all 0.3s;"></div>
          <div style="position: absolute; inset: 0; border-radius: 24px; background: #22d3ee; opacity: 0.3; filter: blur(48px); animation: pulse 2s infinite;"></div>

          <!-- Logo container FULL WIDTH -->
          <div style="position: relative; display: flex; height: 96px; width: 100%; align-items: center; justify-content: center; border-radius: 24px; background: white; box-shadow: 0 0 60px rgba(34,211,238,0.6), 0 0 120px rgba(34,211,238,0.4), 0 20px 50px rgba(0,0,0,0.3); transition: all 0.3s; overflow: hidden; border: 4px solid rgba(34,211,238,0.5);">
            <img src="/assets/images/LOGO.png" alt="Finch-AI" style="height: 80px; width: auto; object-fit: contain; transition: transform 0.3s;">

            <!-- Ring pulsante -->
            <div style="position: absolute; inset: 0; border-radius: 24px; border: 2px solid #22d3ee; opacity: 0; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
          </div>

          <!-- Riflessione sotto -->
          <div style="position: absolute; bottom: -8px; left: 0; right: 0; height: 32px; background: linear-gradient(to bottom, rgba(34,211,238,0.2), transparent); filter: blur(16px); opacity: 0.6;"></div>
        </div>
      </a>

      <!-- Desktop Nav Links e Bottone -->
      <div style="display: flex; align-items: center; gap: 1rem;">
        <?php if (!empty($_SESSION['cliente_email'])): ?>
          <span style="color: #94a3b8; font-size: 0.875rem; display: none;" class="desktop-only">
            <?php echo htmlspecialchars($_SESSION['cliente_nome_completo'] ?? $_SESSION['cliente_email']); ?>
          </span>
          <a href="/area-clienti/dashboard.php" style="position: relative; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #94a3b8; text-decoration: none; transition: color 0.2s;">
            Dashboard
          </a>
          <a href="/area-clienti/profilo.php" style="position: relative; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #94a3b8; text-decoration: none; transition: color 0.2s;">
            Profilo
          </a>
          <a href="/area-clienti/logout.php" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; background: linear-gradient(135deg, #22d3ee, #3b82f6); padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; color: white; box-shadow: 0 4px 6px rgba(34,211,238,0.2); text-decoration: none; transition: filter 0.2s;">
            Esci
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<style>
@keyframes pulse {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 0.5; }
}

@keyframes ping {
  75%, 100% {
    transform: scale(1.05);
    opacity: 0;
  }
}

nav a:hover {
  color: #22d3ee !important;
}

nav a[href*="logout"]:hover {
  filter: brightness(1.1);
}

@media (min-width: 768px) {
  .desktop-only {
    display: inline !important;
  }
}

@media (max-width: 768px) {
  nav > div > div {
    height: 5rem !important;
  }
  nav a[href*="dashboard.php"] > div > div:nth-child(3) {
    height: 64px !important;
  }
  nav a[href*="dashboard.php"] > div > div:nth-child(3) img {
    height: 56px !important;
  }
  nav > div > div > div:last-child {
    gap: 0.5rem !important;
  }
  nav > div > div > div:last-child a {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.75rem !important;
  }
}
</style>
