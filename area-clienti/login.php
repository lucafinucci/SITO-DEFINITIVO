<?php
require __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se già autenticato, reindirizza
if (!empty($_SESSION['cliente_id'])) {
    $stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['cliente_id']]);
    $user = $stmt->fetch();

    if ($user && $user['ruolo'] === 'admin') {
        header('Location: /area-clienti/admin/gestione-servizi.php');
    } else {
        header('Location: /area-clienti/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        // Query utente
        $stmt = $pdo->prepare('
            SELECT id, email, password_hash, nome, cognome, ruolo, mfa_enabled AS auth_2fa_enabled
            FROM utenti
            WHERE email = :email AND attivo = TRUE
            LIMIT 1
        ');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login riuscito
            $_SESSION['cliente_id'] = $user['id'];
            $_SESSION['cliente_email'] = $user['email'];
            $_SESSION['cliente_nome_completo'] = trim($user['nome'] . ' ' . $user['cognome']);

            // Aggiorna last_login
            $pdo->prepare('UPDATE utenti SET last_login = CURRENT_TIMESTAMP WHERE id = ?')->execute([$user['id']]);

            // Reindirizza in base al ruolo
            if ($user['ruolo'] === 'admin') {
                header('Location: /area-clienti/admin/gestione-servizi.php');
            } else {
                header('Location: /area-clienti/dashboard.php');
            }
            exit;
        } else {
            $error = 'Credenziali non valide';
        }
    } else {
        $error = 'Inserisci email e password';
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Login Area Clienti - Finch-AI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #0b1220;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
      color: #e2e8f0;
    }

    /* Canvas rete neurale */
    #neural-canvas {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -10;
      pointer-events: none;
    }

    /* Layer decorativi */
    .bg-layers {
      position: fixed;
      inset: 0;
      z-index: -10;
      pointer-events: none;
    }

    .bg-diagonal {
      position: absolute;
      inset: 0;
      opacity: 0.65;
      background: linear-gradient(120deg, #040a14 20%, #08101f 60%, #050c18 90%);
    }

    .bg-scanlines {
      position: absolute;
      inset: 0;
      background: linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
      background-size: 100% 24px;
      mix-blend-mode: overlay;
      opacity: 0.55;
    }

    /* Card principale */
    .login-card {
      width: 100%;
      max-width: 28rem;
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(40px);
      border: 1px solid rgba(51, 65, 85, 0.6);
      border-radius: 1.5rem;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      padding: 2rem;
      position: relative;
      z-index: 1;
    }

    /* Logo container */
    .logo-container {
      text-align: center;
      margin-bottom: 2rem;
    }

    .logo-wrapper {
      display: inline-block;
      margin-bottom: 1.5rem;
    }

    .logo-box {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(34, 211, 238, 0.3);
      transition: all 0.3s ease;
    }

    .logo-box:hover {
      transform: scale(1.05);
      box-shadow: 0 15px 35px rgba(34, 211, 238, 0.5);
    }

    .logo-box img {
      height: 64px;
      width: auto;
    }

    /* Badge */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.375rem 1rem;
      border-radius: 9999px;
      border: 1px solid rgba(34, 211, 238, 0.5);
      background: rgba(34, 211, 238, 0.1);
      margin-bottom: 1rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: #22d3ee;
    }

    .badge svg {
      width: 1rem;
      height: 1rem;
    }

    h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
      margin-bottom: 0.5rem;
    }

    .subtitle {
      color: #94a3b8;
      font-size: 0.875rem;
    }

    /* Alert errore */
    .alert-error {
      padding: 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fca5a5;
      margin-bottom: 1rem;
    }

    /* Form */
    .form-group {
      margin-bottom: 1rem;
    }

    label {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      color: #cbd5e1;
      margin-bottom: 0.5rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem 1rem;
      background: rgba(30, 41, 59, 0.5);
      border: 1px solid #334155;
      border-radius: 0.75rem;
      color: white;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    input:focus {
      outline: none;
      border-color: #22d3ee;
      box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.2);
    }

    input::placeholder {
      color: #64748b;
    }

    /* Pulsante */
    .btn-primary {
      width: 100%;
      padding: 0.75rem 1rem;
      background: linear-gradient(135deg, #22d3ee, #3b82f6);
      color: white;
      font-weight: 600;
      border-radius: 0.75rem;
      box-shadow: 0 10px 25px rgba(34, 211, 238, 0.3);
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      font-size: 0.875rem;
      margin-top: 0.5rem;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px rgba(34, 211, 238, 0.5);
    }

    /* Sezione recupero */
    .recovery-box {
      margin-top: 1.5rem;
      padding: 0.75rem;
      border-radius: 0.75rem;
      background: rgba(30, 41, 59, 0.5);
      border: 1px solid rgba(51, 65, 85, 0.6);
    }

    .recovery-box h3 {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: white;
      font-size: 0.875rem;
    }

    .recovery-links {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .recovery-link {
      display: block;
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid #334155;
      color: #22d3ee;
      text-decoration: none;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    .recovery-link:hover {
      border-color: #22d3ee;
      background: rgba(34, 211, 238, 0.1);
    }

    /* Pulsante aiuto */
    .btn-help {
      display: block;
      width: 100%;
      padding: 0.75rem 1rem;
      text-align: center;
      border: 1px solid #334155;
      color: #cbd5e1;
      font-weight: 500;
      border-radius: 0.75rem;
      text-decoration: none;
      margin-top: 0.75rem;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    .btn-help:hover {
      border-color: #22d3ee;
      background: rgba(34, 211, 238, 0.05);
    }

    /* Footer note */
    .footer-note {
      margin-top: 1.5rem;
      padding: 0.75rem;
      background: rgba(30, 41, 59, 0.3);
      border: 1px solid rgba(51, 65, 85, 0.5);
      border-radius: 0.5rem;
    }

    .footer-note p {
      font-size: 0.75rem;
      color: #94a3b8;
      line-height: 1.5;
    }

    .footer-note strong {
      color: #cbd5e1;
    }

    .footer-note a {
      color: #22d3ee;
      text-decoration: none;
    }

    /* Link home */
    .home-link {
      margin-top: 1.5rem;
      text-align: center;
    }

    .home-link a {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: #94a3b8;
      text-decoration: none;
      transition: color 0.2s;
    }

    .home-link a:hover {
      color: #cbd5e1;
    }

    .home-link svg {
      width: 1rem;
      height: 1rem;
    }
  </style>
</head>
<body>
  <!-- Canvas rete neurale -->
  <canvas id="neural-canvas"></canvas>

  <!-- Layer decorativi -->
  <div class="bg-layers">
    <div class="bg-diagonal"></div>
    <div class="bg-scanlines"></div>
  </div>

  <!-- Card Login -->
  <div class="login-card">
    <!-- Logo -->
    <div class="logo-container">
      <a href="/SITO/" class="logo-wrapper">
        <div class="logo-box">
          <img src="/SITO/assets/images/LOGO.png" alt="Finch-AI">
        </div>
      </a>

      <div class="badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <span>Accesso riservato</span>
      </div>

      <h1>Area Clienti Finch-AI</h1>
      <p class="subtitle">Inserisci le credenziali per accedere all'area riservata.</p>
    </div>

    <!-- Errore -->
    <?php if ($error): ?>
      <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post">
      <div class="form-group">
        <label for="email">Email aziendale o Username</label>
        <input
          type="text"
          id="email"
          name="email"
          placeholder="nome@azienda.it"
          required
          autofocus
          value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>"
        >
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="••••••••"
          required
        >
      </div>

      <button type="submit" class="btn-primary">Accedi</button>
    </form>

    <!-- Recupero credenziali -->
    <div class="recovery-box">
      <h3>Recupero credenziali</h3>
      <div class="recovery-links">
        <a href="mailto:info@finch-ai.it?subject=Recupero%20credenziali" class="recovery-link">
          Recupera via email
        </a>
        <a href="mailto:info@finch-ai.it?subject=Reset%20password" class="recovery-link">
          Reset password
        </a>
      </div>
    </div>

    <a href="mailto:info@finch-ai.it?subject=Supporto%20Area%20Clienti" class="btn-help">
      Hai bisogno di aiuto?
    </a>

    <!-- Footer -->
    <div class="footer-note">
      <p>
        <strong>Suggerimento:</strong> Per problemi di accesso contattaci a
        <a href="mailto:info@finch-ai.it">info@finch-ai.it</a>
      </p>
    </div>
  </div>

  <!-- Link home -->
  <div class="home-link">
    <a href="/SITO/">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Torna alla home
    </a>
  </div>

  <!-- Script rete neurale -->
  <script>
    (function() {
      const canvas = document.getElementById('neural-canvas');
      if (!canvas) return;

      const ctx = canvas.getContext('2d', { alpha: true });
      let w = canvas.width = window.innerWidth;
      let h = canvas.height = window.innerHeight;

      const onResize = () => {
        w = canvas.width = window.innerWidth;
        h = canvas.height = window.innerHeight;
      };
      window.addEventListener('resize', onResize);

      const PARTICLES = Math.min(90, Math.floor((w * h) / 18000));
      const MAX_SPEED = 0.4;
      const LINK_DIST = Math.min(180, Math.max(110, Math.min(w, h) * 0.22));

      const rnd = (min, max) => Math.random() * (max - min) + min;

      const nodes = Array.from({ length: PARTICLES }).map(() => ({
        x: rnd(0, w),
        y: rnd(0, h),
        vx: rnd(-MAX_SPEED, MAX_SPEED),
        vy: rnd(-MAX_SPEED, MAX_SPEED),
        r: rnd(0.6, 1.8),
      }));

      let rafId;

      const gradientStroke = () => {
        const g = ctx.createLinearGradient(0, 0, w, h);
        g.addColorStop(0, 'rgba(0,224,255,0.85)');
        g.addColorStop(1, 'rgba(59,130,246,0.85)');
        return g;
      };

      const draw = () => {
        ctx.clearRect(0, 0, w, h);

        ctx.fillStyle = 'rgba(7,12,22,0.75)';
        ctx.fillRect(0, 0, w, h);

        const rg = ctx.createRadialGradient(w * 0.5, h * 0.3, 0, w * 0.5, h * 0.3, Math.max(w, h) * 0.7);
        rg.addColorStop(0, 'rgba(23,162,255,0.10)');
        rg.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.fillStyle = rg;
        ctx.fillRect(0, 0, w, h);

        ctx.globalCompositeOperation = 'lighter';
        for (let i = 0; i < nodes.length; i++) {
          const n = nodes[i];
          n.x += n.vx;
          n.y += n.vy;

          if (n.x < 0 || n.x > w) n.vx *= -1;
          if (n.y < 0 || n.y > h) n.vy *= -1;

          ctx.beginPath();
          ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
          ctx.fillStyle = 'rgba(56,189,248,0.65)';
          ctx.fill();
        }

        ctx.lineWidth = 0.7;
        ctx.strokeStyle = gradientStroke();
        for (let i = 0; i < nodes.length; i++) {
          for (let j = i + 1; j < nodes.length; j++) {
            const dx = nodes[i].x - nodes[j].x;
            const dy = nodes[i].y - nodes[j].y;
            const dist = Math.hypot(dx, dy);
            if (dist < LINK_DIST) {
              const alpha = 1 - dist / LINK_DIST;
              ctx.globalAlpha = alpha * 0.6;
              ctx.beginPath();
              ctx.moveTo(nodes[i].x, nodes[i].y);
              ctx.lineTo(nodes[j].x, nodes[j].y);
              ctx.stroke();
            }
          }
        }
        ctx.globalAlpha = 1;

        rafId = requestAnimationFrame(draw);
      };

      draw();
    })();
  </script>
</body>
</html>
