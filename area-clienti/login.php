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

            // 2FA TEMPORANEAMENTE DISABILITATO - tabella auth_trusted_devices non esiste
            // TODO: riabilitare quando la tabella sarà creata

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
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
<?php include __DIR__ . '/includes/layout-start.php'; ?>

  <div class="card auth-card">
    <!-- Logo GRANDE -->
    <div style="margin-bottom: 2rem;">
      <div style="position: relative; width: 100%; max-width: 280px; margin: 0 auto;">
        <!-- Glow effect espanso -->
        <div style="position: absolute; inset: 0; border-radius: 24px; background: linear-gradient(135deg, #22d3ee, #3b82f6); opacity: 0.5; filter: blur(50px);"></div>
        <div style="position: absolute; inset: 0; border-radius: 24px; background: #22d3ee; opacity: 0.3; filter: blur(40px); animation: pulse 2s infinite;"></div>

        <!-- Logo container FULL WIDTH -->
        <div style="position: relative; display: flex; height: 110px; width: 100%; align-items: center; justify-content: center; border-radius: 24px; background: white; box-shadow: 0 0 60px rgba(34,211,238,0.7), 0 0 100px rgba(34,211,238,0.5), 0 20px 40px rgba(0,0,0,0.4); overflow: hidden; border: 4px solid rgba(34,211,238,0.6);">
          <img src="/assets/images/LOGO.png" alt="Finch-AI" style="height: 90px; width: auto; object-fit: contain;">

          <!-- Ring pulsante -->
          <div style="position: absolute; inset: 0; border-radius: 24px; border: 2px solid #22d3ee; opacity: 0; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
        </div>

        <!-- Riflessione sotto -->
        <div style="position: absolute; bottom: -10px; left: 0; right: 0; height: 28px; background: linear-gradient(to bottom, rgba(34,211,238,0.25), transparent); filter: blur(14px); opacity: 0.7;"></div>
      </div>
      <h2 style="text-align: center; margin-top: 1.5rem; font-size: 1.5rem; font-weight: 700; color: transparent; background: linear-gradient(135deg, #22d3ee, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        Finch-AI · Area Clienti
      </h2>
    </div>
    <h1 style="margin-top: 1rem;">Accedi</h1>
    <?php if ($error): ?>
      <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" class="form-grid">
      <label>Email
        <input type="email" name="email" placeholder="nome@azienda.it" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
      </label>

      <label>Password
        <input type="password" name="password" placeholder="••••••••" required>
      </label>

      <button type="submit" class="btn primary">Accedi</button>
    </form>

    <p class="muted small" style="text-align: center; margin-top: 20px;">
      Problemi di accesso? <a href="mailto:supporto@finch-ai.it" style="color: #22d3ee;">Contatta il supporto</a>
    </p>
  </div>

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
</style>

<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
