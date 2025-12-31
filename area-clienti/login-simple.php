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
            SELECT id, email, password_hash, nome, cognome, ruolo
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
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body class="auth-body">
  <div class="card auth-card">
    <div class="brand-inline">
      <span class="logo-dot"></span>
      <span class="brand-name">Finch-AI · Area Clienti</span>
    </div>
    <h1>Accedi</h1>
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
      Problemi di accesso? <a href="mailto:supporto@finch-ai.it" style="color: var(--accent1);">Contatta il supporto</a>
    </p>
  </div>
</body>
</html>
