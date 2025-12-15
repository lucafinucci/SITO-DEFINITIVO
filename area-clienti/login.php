<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/totp.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Se già autenticato
if (!empty($_SESSION['cliente_id'])) {
    header('Location: /area-clienti/dashboard.php');
    exit;
}

$error = '';
$showMFAInput = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCSRFToken($csrfToken)) {
        $error = 'Token di sicurezza non valido. Riprova.';
        ErrorHandler::logAccess('CSRF token validation failed', ['ip' => Security::getClientIP()]);
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $otpCode = trim($_POST['otp_code'] ?? '');

        // Valida input
        $emailValidation = Security::validateEmail($email);
        if (!$emailValidation['valid']) {
            $error = $emailValidation['error'];
        } else {
            $email = $emailValidation['value'];

            // Rate limiting
            $rateLimit = Security::checkRateLimit($email);

            if (!$rateLimit['allowed']) {
                $minutes = ceil($rateLimit['remaining_time'] / 60);
                $error = "Troppi tentativi falliti. Riprova tra {$minutes} minuti.";
                ErrorHandler::logAccess('Login rate limit exceeded', [
                    'email' => $email,
                    'ip' => Security::getClientIP(),
                    'remaining_time' => $rateLimit['remaining_time']
                ]);
            } else {
                // Query utente
                $stmt = $pdo->prepare('
                    SELECT id, email, password_hash, nome, cognome, azienda, mfa_enabled, mfa_secret
                    FROM utenti
                    WHERE email = :email AND attivo = TRUE
                    LIMIT 1
                ');
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                $logSuccess = false;
                $logReason = '';
                $logUserId = null;

                if ($user) {
                    $logUserId = $user['id'];

                    // Verifica password
                    if (!password_verify($password, $user['password_hash'])) {
                        $error = 'Credenziali non valide';
                        $logReason = 'Password errata';
                        Security::recordFailedAttempt($email);
                    } else {
                        // Password corretta - verifica MFA se abilitato
                        if ($user['mfa_enabled'] && $user['mfa_secret']) {
                            if (empty($otpCode)) {
                                // Mostra campo OTP
                                $showMFAInput = true;
                                $_SESSION['mfa_user_id'] = $user['id'];
                            } else {
                                // Valida OTP
                                $otpValidation = Security::validateTOTPCode($otpCode);
                                if (!$otpValidation['valid']) {
                                    $error = $otpValidation['error'];
                                    $logReason = 'Codice OTP non valido';
                                    Security::recordFailedAttempt($email);
                                    $showMFAInput = true;
                                } elseif (!TOTP::verifyCode($user['mfa_secret'], $otpValidation['value'])) {
                                    $error = 'Codice di verifica non corretto';
                                    $logReason = 'Codice OTP errato';
                                    Security::recordFailedAttempt($email);
                                    $showMFAInput = true;
                                } else {
                                    // MFA verificato
                                    $logSuccess = true;
                                }
                            }
                        } else {
                            // Nessun MFA richiesto
                            $logSuccess = true;
                        }

                        // Login riuscito
                        if ($logSuccess) {
                            $_SESSION['cliente_id'] = $user['id'];
                            $_SESSION['cliente_email'] = $user['email'];
                            $_SESSION['cliente_nome_completo'] = trim($user['nome'] . ' ' . $user['cognome']);

                            // Reset rate limiting
                            Security::resetRateLimit($email);

                            // Aggiorna last_login
                            $pdo->prepare('UPDATE utenti SET last_login = CURRENT_TIMESTAMP WHERE id = ?')->execute([$user['id']]);

                            ErrorHandler::logAccess('Successful login', ['user_id' => $user['id'], 'email' => $email]);

                            header('Location: /area-clienti/dashboard.php');
                            exit;
                        }
                    }
                } else {
                    $error = 'Credenziali non valide';
                    $logReason = 'Email non trovata';
                    Security::recordFailedAttempt($email);
                }

                // Logga il tentativo di accesso
                try {
                    $logStmt = $pdo->prepare('
                        INSERT INTO access_logs (user_id, email_tentativo, ip_address, user_agent, successo, motivo_fallimento)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ');
                    $logStmt->execute([
                        $logUserId,
                        $email,
                        Security::getClientIP(),
                        Security::getUserAgent(),
                        $logSuccess,
                        $logReason
                    ]);
                } catch (PDOException $e) {
                    ErrorHandler::logError('Failed to write to access_logs: ' . $e->getMessage());
                }
            }
        }
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
      <?php echo Security::csrfField(); ?>

      <label>Email
        <input type="email" name="email" placeholder="nome@azienda.it" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
      </label>

      <label>Password
        <input type="password" name="password" placeholder="••••••••" required>
      </label>

      <?php if ($showMFAInput): ?>
        <label>Codice di verifica (6 cifre)
          <input type="text" name="otp_code" placeholder="000000" pattern="[0-9]{6}" maxlength="6" required autofocus>
          <span class="muted small" style="display: block; margin-top: 4px;">
            Inserisci il codice dall'app Authenticator
          </span>
        </label>
      <?php endif; ?>

      <button type="submit" class="btn primary">Accedi</button>
    </form>

    <p class="muted small" style="text-align: center; margin-top: 20px;">
      Problemi di accesso? <a href="mailto:supporto@finch-ai.it" style="color: var(--accent1);">Contatta il supporto</a>
    </p>
  </div>
</body>
</html>
