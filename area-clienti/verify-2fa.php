<?php
/**
 * Verifica 2FA al Login
 * Pagina intermedia dopo il login se l'utente ha 2FA abilitato
 */

session_start();
require 'includes/db.php';

// Verifica che l'utente abbia fatto login ma non ancora 2FA
if (!isset($_SESSION['2fa_required']) || !isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

// Se già verificato, redirect
if (isset($_SESSION['2fa_verified'])) {
    header('Location: dashboard.php');
    exit;
}

$userId = $_SESSION['cliente_id'];

// Recupera info utente
$stmt = $pdo->prepare('
    SELECT email, nome, cognome, auth_2fa_backup_codes
    FROM utenti
    WHERE id = :id
');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

$backupCodesRemaining = $user['auth_2fa_backup_codes']
    ? count(json_decode($user['auth_2fa_backup_codes'], true))
    : 0;

// Controlla se ha troppi tentativi falliti
$stmt = $pdo->prepare('
    SELECT COUNT(*) as attempts
    FROM auth_2fa_log
    WHERE user_id = :user_id
    AND success = FALSE
    AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
');
$stmt->execute(['user_id' => $userId]);
$result = $stmt->fetch();
$failedAttempts = $result['attempts'];

$isLocked = $failedAttempts >= 5;

if ($isLocked) {
    $lockMessage = "Troppi tentativi falliti. Account temporaneamente bloccato per 15 minuti.";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica 2FA - Finch-AI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .verify-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 2rem;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #718096;
            font-size: 0.875rem;
        }

        .security-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        h2 {
            color: #1a202c;
            text-align: center;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .subtitle {
            color: #718096;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .user-info {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .user-info strong {
            color: #1a202c;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left-color: #f56565;
        }

        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left-color: #4299e1;
        }

        .alert-warning {
            background: #feebc8;
            color: #7c2d12;
            border-left-color: #ed8936;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        .code-input-container {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 1.5rem 0;
        }

        .code-digit {
            width: 50px;
            height: 60px;
            font-size: 2rem;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            transition: all 0.2s;
        }

        .code-digit:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
            margin-top: 0.5rem;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .options {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .option-btn {
            display: block;
            width: 100%;
            padding: 0.75rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #4a5568;
            text-decoration: none;
            text-align: center;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .option-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .backup-code-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1.25rem;
            text-align: center;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.2em;
            margin: 1rem 0;
        }

        .backup-code-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .trust-device {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            padding: 0.75rem;
            background: #f7fafc;
            border-radius: 8px;
        }

        .trust-device input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .trust-device label {
            flex: 1;
            cursor: pointer;
            margin: 0;
            font-weight: normal;
        }

        .attempts-remaining {
            text-align: center;
            color: #ed8936;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .hidden {
            display: none !important;
        }

        @media (max-width: 768px) {
            .verify-container {
                padding: 2rem 1.5rem;
            }

            .code-digit {
                width: 40px;
                height: 50px;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="logo">
            <div class="security-icon">🔐</div>
            <h1>Finch-AI</h1>
            <p>Area Clienti</p>
        </div>

        <h2>Verifica Autenticazione</h2>
        <p class="subtitle">
            Inserisci il codice a 6 cifre dall'app Authenticator
        </p>

        <div class="user-info">
            Accesso come: <strong><?= htmlspecialchars($user['email']) ?></strong>
        </div>

        <?php if ($isLocked): ?>
            <div class="alert alert-error">
                <strong>⚠️ Account Bloccato</strong><br>
                <?= htmlspecialchars($lockMessage) ?>
            </div>
        <?php endif; ?>

        <div id="errorMessage" class="alert alert-error hidden"></div>

        <!-- Form codice TOTP -->
        <form id="verifyForm" onsubmit="verify2FA(event)" <?= $isLocked ? 'style="display:none;"' : '' ?>>
            <div class="code-input-container">
                <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" id="digit1" autocomplete="off">
                <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" id="digit2" autocomplete="off">
                <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" id="digit3" autocomplete="off">
                <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" id="digit4" autocomplete="off">
                <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" id="digit5" autocomplete="off">
                <input type="text" class="code-digit" maxlength="1" pattern="[0-9]" id="digit6" autocomplete="off">
            </div>

            <div class="trust-device">
                <input type="checkbox" id="trustDevice">
                <label for="trustDevice">Ricorda questo dispositivo per 30 giorni</label>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">
                ✓ Verifica
            </button>

            <?php if ($failedAttempts > 0): ?>
            <div class="attempts-remaining">
                ⚠️ <?= 5 - $failedAttempts ?> tentativi rimasti
            </div>
            <?php endif; ?>
        </form>

        <!-- Opzioni alternative -->
        <div class="options" <?= $isLocked ? 'style="display:none;"' : '' ?>>
            <button class="option-btn" onclick="showBackupCode()">
                🔑 Usa un backup code
                <?php if ($backupCodesRemaining > 0): ?>
                    (<?= $backupCodesRemaining ?> rimanenti)
                <?php endif; ?>
            </button>

            <a href="logout.php" class="option-btn">
                ← Torna al login
            </a>
        </div>

        <!-- Form backup code (nascosto) -->
        <div id="backupCodeForm" class="hidden">
            <form onsubmit="verifyBackupCode(event)">
                <p style="text-align: center; color: #718096; margin-bottom: 1rem;">
                    Inserisci uno dei tuoi backup codes (formato: XXXX-XXXX)
                </p>

                <input
                    type="text"
                    id="backupCodeInput"
                    class="backup-code-input"
                    placeholder="0000-0000"
                    pattern="[0-9]{4}-[0-9]{4}"
                    maxlength="9"
                    autocomplete="off"
                >

                <button type="submit" class="btn btn-primary">
                    ✓ Verifica Backup Code
                </button>

                <button type="button" class="btn btn-secondary" onclick="showTOTPForm()">
                    ← Torna al codice normale
                </button>
            </form>
        </div>

        <?php if ($isLocked): ?>
            <a href="logout.php" class="btn btn-secondary" style="margin-top: 1rem;">
                ← Torna al login
            </a>
        <?php endif; ?>
    </div>

    <script>
        // Auto-focus e navigazione tra input
        document.addEventListener('DOMContentLoaded', () => {
            const digits = document.querySelectorAll('.code-digit');

            digits.forEach((digit, index) => {
                digit.addEventListener('input', (e) => {
                    if (e.target.value.length === 1) {
                        // Vai al prossimo input
                        if (index < digits.length - 1) {
                            digits[index + 1].focus();
                        } else {
                            // Ultimo digit, submit automatico
                            document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
                        }
                    }
                });

                digit.addEventListener('keydown', (e) => {
                    // Backspace: torna indietro
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        digits[index - 1].focus();
                    }

                    // Paste: distribuisci le cifre
                    if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                        setTimeout(() => {
                            const pastedValue = e.target.value;
                            if (pastedValue.length === 6) {
                                pastedValue.split('').forEach((char, i) => {
                                    if (digits[i]) {
                                        digits[i].value = char;
                                    }
                                });
                                document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
                            }
                        }, 10);
                    }
                });

                // Solo numeri
                digit.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                });
            });

            // Focus sul primo input
            digits[0].focus();

            // Auto-format backup code
            const backupInput = document.getElementById('backupCodeInput');
            if (backupInput) {
                backupInput.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/[^0-9]/g, '');
                    if (value.length > 4) {
                        value = value.slice(0, 4) + '-' + value.slice(4, 8);
                    }
                    e.target.value = value;
                });
            }
        });

        async function verify2FA(event) {
            event.preventDefault();

            const digits = document.querySelectorAll('.code-digit');
            const code = Array.from(digits).map(d => d.value).join('');

            if (code.length !== 6) {
                showError('Inserisci tutte le 6 cifre');
                return;
            }

            const trustDevice = document.getElementById('trustDevice').checked;
            const submitBtn = document.getElementById('submitBtn');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Verifica in corso...';

            try {
                const response = await fetch('api/2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'verify',
                        code: code,
                        trust_device: trustDevice
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Redirect a dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    showError(data.error || 'Codice non valido');
                    // Reset input
                    digits.forEach(d => d.value = '');
                    digits[0].focus();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '✓ Verifica';
                }
            } catch (error) {
                console.error('Errore:', error);
                showError('Errore di connessione. Riprova.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✓ Verifica';
            }
        }

        async function verifyBackupCode(event) {
            event.preventDefault();

            const code = document.getElementById('backupCodeInput').value;

            if (!code || code.length !== 9) {
                showError('Formato backup code non valido');
                return;
            }

            try {
                const response = await fetch('api/2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'verify',
                        code: code,
                        trust_device: false
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    showError(data.error || 'Backup code non valido');
                    document.getElementById('backupCodeInput').value = '';
                }
            } catch (error) {
                console.error('Errore:', error);
                showError('Errore di connessione. Riprova.');
            }
        }

        function showBackupCode() {
            document.getElementById('verifyForm').classList.add('hidden');
            document.querySelector('.options').classList.add('hidden');
            document.getElementById('backupCodeForm').classList.remove('hidden');
            document.getElementById('backupCodeInput').focus();
        }

        function showTOTPForm() {
            document.getElementById('backupCodeForm').classList.add('hidden');
            document.getElementById('verifyForm').classList.remove('hidden');
            document.querySelector('.options').classList.remove('hidden');
            document.getElementById('digit1').focus();
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = '⚠️ ' + message;
            errorDiv.classList.remove('hidden');

            setTimeout(() => {
                errorDiv.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>
</html>
