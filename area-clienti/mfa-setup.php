<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/totp.php';

$clienteId = $_SESSION['cliente_id'];

// Recupera dati utente
$stmt = $pdo->prepare('SELECT id, email, nome, cognome, mfa_enabled, mfa_secret FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$utente = $stmt->fetch();

$success = $error = '';
$qrCodeURL = '';
$secret = '';

// Abilita MFA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_mfa'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        // Genera nuovo secret
        $secret = TOTP::generateSecret();
        $qrCodeURL = TOTP::getQRCodeURL($secret, $utente['email']);

        // Salva secret (ma non abilita ancora MFA)
        $stmt = $pdo->prepare('UPDATE utenti SET mfa_secret = :secret WHERE id = :id');
        $stmt->execute(['secret' => $secret, 'id' => $clienteId]);

        $_SESSION['mfa_setup_secret'] = $secret;
        $success = 'Scansiona il QR Code con la tua app Authenticator';
    }
}

// Verifica e attiva MFA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_mfa'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $verifyCode = trim($_POST['verify_code'] ?? '');
        $setupSecret = $_SESSION['mfa_setup_secret'] ?? $utente['mfa_secret'];

        $codeValidation = Security::validateTOTPCode($verifyCode);

        if (!$codeValidation['valid']) {
            $error = $codeValidation['error'];
        } elseif (!$setupSecret) {
            $error = 'Nessun secret MFA trovato. Riavvia la configurazione.';
        } elseif (!TOTP::verifyCode($setupSecret, $codeValidation['value'])) {
            $error = 'Codice non corretto. Riprova.';
        } else {
            // Attiva MFA
            $stmt = $pdo->prepare('UPDATE utenti SET mfa_enabled = TRUE, mfa_secret = :secret WHERE id = :id');
            $stmt->execute(['secret' => $setupSecret, 'id' => $clienteId]);

            unset($_SESSION['mfa_setup_secret']);

            ErrorHandler::logAccess('MFA enabled', ['user_id' => $clienteId]);

            $success = 'Autenticazione a due fattori attivata con successo!';
            $utente['mfa_enabled'] = true;
        }
    }
}

// Disabilita MFA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disable_mfa'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $password = $_POST['password'] ?? '';

        // Verifica password
        $stmt = $pdo->prepare('SELECT password_hash FROM utenti WHERE id = :id');
        $stmt->execute(['id' => $clienteId]);
        $user = $stmt->fetch();

        if (!password_verify($password, $user['password_hash'])) {
            $error = 'Password non corretta';
        } else {
            // Disabilita MFA
            $stmt = $pdo->prepare('UPDATE utenti SET mfa_enabled = FALSE, mfa_secret = NULL WHERE id = :id');
            $stmt->execute(['id' => $clienteId]);

            ErrorHandler::logAccess('MFA disabled', ['user_id' => $clienteId]);

            $success = 'Autenticazione a due fattori disattivata';
            $utente['mfa_enabled'] = false;
            $utente['mfa_secret'] = null;
        }
    }
}

// Mostra QR se in setup
if (isset($_SESSION['mfa_setup_secret']) && !$utente['mfa_enabled']) {
    $secret = $_SESSION['mfa_setup_secret'];
    $qrCodeURL = TOTP::getQRCodeURL($secret, $utente['email']);
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Autenticazione a Due Fattori - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <div style="margin-bottom: 20px;">
    <a href="/area-clienti/profilo.php" style="color: var(--accent1);">← Torna al Profilo</a>
  </div>

  <?php if ($success): ?>
    <div class="alert success" style="margin-bottom: 20px;"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <section class="card">
    <h2>🔐 Autenticazione a Due Fattori (MFA)</h2>

    <?php if ($utente['mfa_enabled']): ?>
      <!-- MFA già abilitato -->
      <div style="padding: 20px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; margin-top: 20px;">
        <p style="margin: 0; color: #10b981; font-weight: 600;">✓ L'autenticazione a due fattori è attiva</p>
        <p class="muted small" style="margin: 8px 0 0 0;">Il tuo account è protetto da un ulteriore livello di sicurezza</p>
      </div>

      <div style="margin-top: 30px;">
        <h3>Disabilita MFA</h3>
        <p class="muted small">Inserisci la password per disabilitare l'autenticazione a due fattori</p>

        <form method="post" style="max-width: 400px; margin-top: 16px;">
          <?php echo Security::csrfField(); ?>
          <label>
            Password
            <input type="password" name="password" required>
          </label>
          <button type="submit" name="disable_mfa" class="btn ghost" style="margin-top: 12px; border-color: #ef4444; color: #ef4444;">
            Disabilita MFA
          </button>
        </form>
      </div>

    <?php elseif ($qrCodeURL): ?>
      <!-- Step 2: Scansiona QR Code -->
      <div style="margin-top: 20px;">
        <h3>Step 2: Scansiona il QR Code</h3>
        <p class="muted">Usa un'app di autenticazione (Google Authenticator, Microsoft Authenticator, Authy, ecc.)</p>

        <div style="text-align: center; margin: 30px 0;">
          <img src="<?php echo htmlspecialchars($qrCodeURL); ?>" alt="QR Code MFA" style="border: 4px solid var(--border); border-radius: 12px; padding: 10px; background: white;">
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 10px; margin-bottom: 20px;">
          <p class="muted small" style="margin: 0 0 8px 0;">Secret Key (inserimento manuale):</p>
          <code style="font-size: 16px; color: var(--accent1); word-break: break-all;"><?php echo htmlspecialchars($secret); ?></code>
        </div>

        <h3>Step 3: Verifica il codice</h3>
        <p class="muted small">Inserisci il codice a 6 cifre dall'app per completare l'attivazione</p>

        <form method="post" style="max-width: 300px; margin-top: 16px;">
          <?php echo Security::csrfField(); ?>
          <label>
            Codice di verifica
            <input type="text" name="verify_code" placeholder="000000" pattern="[0-9]{6}" maxlength="6" required autofocus>
          </label>
          <button type="submit" name="verify_mfa" class="btn primary" style="margin-top: 12px;">
            Verifica e Attiva MFA
          </button>
        </form>
      </div>

    <?php else: ?>
      <!-- Step 1: Abilita MFA -->
      <div style="margin-top: 20px;">
        <p>L'autenticazione a due fattori (MFA) aggiunge un ulteriore livello di sicurezza al tuo account.</p>
        <p class="muted">Avrai bisogno di un'app di autenticazione sul tuo smartphone.</p>

        <div style="margin: 30px 0;">
          <h3>Come funziona?</h3>
          <ol style="line-height: 1.8; color: var(--muted);">
            <li>Installa un'app di autenticazione (Google Authenticator, Microsoft Authenticator, Authy)</li>
            <li>Scansiona il QR code che ti mostreremo</li>
            <li>Inserisci il codice a 6 cifre per verificare</li>
            <li>Da ora in poi, oltre alla password, ti servirà il codice dall'app per accedere</li>
          </ol>
        </div>

        <form method="post">
          <?php echo Security::csrfField(); ?>
          <button type="submit" name="enable_mfa" class="btn primary">
            Attiva Autenticazione a Due Fattori
          </button>
        </form>
      </div>

    <?php endif; ?>
  </section>

  <section class="card">
    <h3>App Consigliate</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 16px;">
      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p style="margin: 0; font-weight: 600;">Google Authenticator</p>
        <p class="muted small" style="margin: 4px 0 0;">iOS, Android</p>
      </div>
      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p style="margin: 0; font-weight: 600;">Microsoft Authenticator</p>
        <p class="muted small" style="margin: 4px 0 0;">iOS, Android</p>
      </div>
      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p style="margin: 0; font-weight: 600;">Authy</p>
        <p class="muted small" style="margin: 4px 0 0;">iOS, Android, Desktop</p>
      </div>
    </div>
  </section>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
