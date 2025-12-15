<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';

$clienteId = $_SESSION['cliente_id'];

// Recupera dati utente
$stmt = $pdo->prepare('SELECT id, email, nome, cognome, azienda, telefono, ruolo, created_at, last_login, mfa_enabled FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$utente = $stmt->fetch();

$success = $error = '';

// Aggiornamento dati profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $cognome = trim($_POST['cognome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $azienda = trim($_POST['azienda'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        // Validazione
        $nomeValidation = Security::sanitizeString($nome, 100);
        $cognomeValidation = Security::sanitizeString($cognome, 100);
        $emailValidation = Security::validateEmail($email);

        if (!$nomeValidation['valid']) {
            $error = 'Nome: ' . $nomeValidation['error'];
        } elseif (!$cognomeValidation['valid']) {
            $error = 'Cognome: ' . $cognomeValidation['error'];
        } elseif (!$emailValidation['valid']) {
            $error = $emailValidation['error'];
        } else {
            if (!empty($telefono)) {
                $telefonoValidation = Security::validatePhone($telefono);
                if (!$telefonoValidation['valid']) {
                    $error = $telefonoValidation['error'];
                    $telefono = '';
                } else {
                    $telefono = $telefonoValidation['value'];
                }
            }

            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare('UPDATE utenti SET nome = :nome, cognome = :cognome, email = :email, azienda = :azienda, telefono = :telefono WHERE id = :id');
                    $stmt->execute([
                        'nome' => $nomeValidation['value'],
                        'cognome' => $cognomeValidation['value'],
                        'email' => $emailValidation['value'],
                        'azienda' => $azienda,
                        'telefono' => $telefono,
                        'id' => $clienteId,
                    ]);

                    $success = 'Profilo aggiornato con successo!';
                    $_SESSION['cliente_email'] = $emailValidation['value'];
                    $_SESSION['cliente_nome_completo'] = trim($nomeValidation['value'] . ' ' . $cognomeValidation['value']);

                    // Aggiorna dati locali
                    $utente['nome'] = $nomeValidation['value'];
                    $utente['cognome'] = $cognomeValidation['value'];
                    $utente['email'] = $emailValidation['value'];
                    $utente['azienda'] = $azienda;
                    $utente['telefono'] = $telefono;

                    ErrorHandler::logAccess('Profile updated', ['user_id' => $clienteId]);

                } catch (Exception $e) {
                    ErrorHandler::logError('Profile update error: ' . $e->getMessage());
                    $error = 'Errore durante il salvataggio. Riprova.';
                }
            }
        }
    }
}

// Cambio password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Recupera hash password attuale
        $stmt = $pdo->prepare('SELECT password_hash FROM utenti WHERE id = :id');
        $stmt->execute(['id' => $clienteId]);
        $user = $stmt->fetch();

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $error = 'Password attuale non corretta';
        } else {
            $passwordValidation = Security::validatePassword($newPassword);

            if (!$passwordValidation['valid']) {
                $error = $passwordValidation['error'];
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Le password non coincidono';
            } else {
                try {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE utenti SET password_hash = :hash WHERE id = :id');
                    $stmt->execute(['hash' => $newHash, 'id' => $clienteId]);

                    $success = 'Password modificata con successo!';

                    ErrorHandler::logAccess('Password changed', ['user_id' => $clienteId]);

                } catch (Exception $e) {
                    ErrorHandler::logError('Password change error: ' . $e->getMessage());
                    $error = 'Errore durante il cambio password';
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
  <title>Il mio Profilo - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <?php if ($success): ?>
    <div class="alert success" style="margin-bottom: 20px;"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <!-- Informazioni Account -->
  <section class="card">
    <h2>Informazioni Account</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-top: 16px;">
      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Ruolo</p>
        <p style="margin: 4px 0 0; font-weight: 600; text-transform: capitalize;">
          <?= htmlspecialchars($utente['ruolo']) ?>
        </p>
      </div>

      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Membro dal</p>
        <p style="margin: 4px 0 0; font-weight: 600;">
          <?= date('d/m/Y', strtotime($utente['created_at'])) ?>
        </p>
      </div>

      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Ultimo accesso</p>
        <p style="margin: 4px 0 0; font-weight: 600;">
          <?= $utente['last_login'] ? date('d/m/Y H:i', strtotime($utente['last_login'])) : 'Mai' ?>
        </p>
      </div>
    </div>
  </section>

  <!-- Modifica Dati Personali -->
  <section class="card">
    <h2>Dati Personali</h2>
    <form method="post" style="margin-top: 16px;">
      <?php echo Security::csrfField(); ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">

        <label>
          Nome *
          <input type="text" name="nome" value="<?php echo htmlspecialchars($utente['nome'] ?? ''); ?>" required>
        </label>

        <label>
          Cognome *
          <input type="text" name="cognome" value="<?php echo htmlspecialchars($utente['cognome'] ?? ''); ?>" required>
        </label>

        <label style="grid-column: 1 / -1;">
          Email *
          <input type="email" name="email" value="<?php echo htmlspecialchars($utente['email'] ?? ''); ?>" required>
        </label>

        <label style="grid-column: 1 / -1;">
          Azienda
          <input type="text" name="azienda" value="<?php echo htmlspecialchars($utente['azienda'] ?? ''); ?>">
        </label>

        <label>
          Telefono
          <input type="tel" name="telefono" value="<?php echo htmlspecialchars($utente['telefono'] ?? ''); ?>">
        </label>

      </div>

      <div style="margin-top: 20px;">
        <button class="btn primary" type="submit" name="update_profile">Salva Modifiche</button>
      </div>
    </form>
  </section>

  <!-- Sicurezza -->
  <section class="card">
    <h2>🔐 Sicurezza</h2>

    <!-- MFA Status -->
    <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border); margin: 20px 0;">
      <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
        <div>
          <p style="margin: 0; font-weight: 600;">Autenticazione a Due Fattori (MFA)</p>
          <p class="muted small" style="margin: 6px 0 0;">
            <?php if ($utente['mfa_enabled']): ?>
              <span style="color: #10b981;">✓ Attiva</span> - Il tuo account è protetto
            <?php else: ?>
              <span style="color: #fbbf24;">! Non attiva</span> - Consigliamo di attivarla per maggiore sicurezza
            <?php endif; ?>
          </p>
        </div>
        <a href="/area-clienti/mfa-setup.php" class="btn <?php echo $utente['mfa_enabled'] ? 'ghost' : 'primary'; ?>">
          <?php echo $utente['mfa_enabled'] ? 'Gestisci MFA' : 'Attiva MFA'; ?>
        </a>
      </div>
    </div>

    <!-- Cambio Password -->
    <h3 style="margin-top: 30px; font-size: 18px;">Cambia Password</h3>
    <p class="muted small">La password deve essere di almeno 8 caratteri, contenere lettere e numeri</p>

    <form method="post" style="margin-top: 16px;">
      <?php echo Security::csrfField(); ?>

      <div style="display: grid; gap: 16px; max-width: 500px;">

        <label>
          Password Attuale *
          <input type="password" name="current_password" required autocomplete="current-password">
        </label>

        <label>
          Nuova Password *
          <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
        </label>

        <label>
          Conferma Nuova Password *
          <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
        </label>

      </div>

      <div style="margin-top: 20px;">
        <button class="btn primary" type="submit" name="change_password">Cambia Password</button>
      </div>
    </form>
  </section>

  <!-- Azioni Account -->
  <section class="card">
    <h2>Azioni</h2>
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px;">
      <a href="/area-clienti/dashboard.php" class="btn ghost">← Torna alla Dashboard</a>
      <a href="mailto:supporto@finch-ai.it?subject=Richiesta%20supporto%20account" class="btn ghost">Contatta Supporto</a>
    </div>
  </section>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
