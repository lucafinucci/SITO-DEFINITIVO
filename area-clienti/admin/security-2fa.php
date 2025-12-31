<?php
/**
 * Gestione 2FA (Two-Factor Authentication)
 * Abilita/Disabilita autenticazione a due fattori
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/totp.php';

$rbac = getRBAC($pdo);
$userId = $_SESSION['cliente_id'];

// Recupera stato 2FA corrente
$stmt = $pdo->prepare('
    SELECT
        auth_2fa_enabled,
        auth_2fa_enabled_at,
        auth_2fa_backup_codes
    FROM utenti
    WHERE id = :id
');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

$is2FAEnabled = (bool)$user['auth_2fa_enabled'];
$backupCodesRemaining = $user['auth_2fa_backup_codes']
    ? count(json_decode($user['auth_2fa_backup_codes'], true))
    : 0;

// Statistiche 2FA
$stats = $pdo->prepare('
    SELECT
        SUM(CASE WHEN success = TRUE THEN 1 ELSE 0 END) as successi,
        SUM(CASE WHEN success = FALSE THEN 1 ELSE 0 END) as fallimenti,
        MAX(created_at) as ultimo_accesso
    FROM auth_2fa_log
    WHERE user_id = :user_id
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
');
$stats->execute(['user_id' => $userId]);
$stats2FA = $stats->fetch();

// Dispositivi fidati
$trustedDevices = $pdo->prepare('
    SELECT *
    FROM auth_trusted_devices
    WHERE user_id = :user_id
    AND is_active = TRUE
    AND expires_at > NOW()
    ORDER BY last_used_at DESC
');
$trustedDevices->execute(['user_id' => $userId]);
$devices = $trustedDevices->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticazione a Due Fattori (2FA)</title>
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
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2rem;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #718096;
        }

        .status-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .status-badge.enabled {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-badge.disabled {
            background: #fed7d7;
            color: #742a2a;
        }

        .status-badge::before {
            content: '●';
            font-size: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }

        .setup-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .setup-card h2 {
            color: #1a202c;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .setup-steps {
            counter-reset: step;
        }

        .step {
            margin-bottom: 2rem;
            padding-left: 3rem;
            position: relative;
        }

        .step::before {
            counter-increment: step;
            content: counter(step);
            position: absolute;
            left: 0;
            top: 0;
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .step h3 {
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }

        .step p {
            color: #718096;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .qr-container {
            text-align: center;
            padding: 2rem;
            background: #f7fafc;
            border-radius: 12px;
            margin: 1rem 0;
        }

        .qr-container img {
            max-width: 250px;
            border: 4px solid white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .secret-code {
            background: #2d3748;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 1.25rem;
            text-align: center;
            letter-spacing: 0.2em;
            margin: 1rem 0;
            cursor: pointer;
            transition: all 0.2s;
        }

        .secret-code:hover {
            background: #1a202c;
        }

        .backup-codes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .backup-code {
            background: #f7fafc;
            padding: 0.75rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            text-align: center;
            border: 2px solid #e2e8f0;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-danger {
            background: #f56565;
            color: white;
        }

        .btn-danger:hover {
            background: #e53e3e;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-success {
            background: #48bb78;
            color: white;
        }

        .btn-success:hover {
            background: #38a169;
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

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .code-input {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.5em;
            max-width: 300px;
            margin: 0 auto;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .alert-warning {
            background: #feebc8;
            color: #7c2d12;
            border-left: 4px solid #ed8936;
        }

        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid #4299e1;
        }

        .devices-list {
            margin-top: 1rem;
        }

        .device-item {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .device-info {
            flex: 1;
        }

        .device-name {
            font-weight: 600;
            color: #1a202c;
        }

        .device-meta {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .backup-codes {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Autenticazione a Due Fattori (2FA)</h1>
            <p>Proteggi il tuo account con un livello aggiuntivo di sicurezza</p>
        </div>

        <!-- Status Card -->
        <div class="status-card">
            <h2 style="margin-bottom: 1rem;">Stato 2FA</h2>

            <?php if ($is2FAEnabled): ?>
                <div class="status-badge enabled">
                    2FA Abilitato
                </div>
                <p style="color: #718096; margin-bottom: 1rem;">
                    Attivato il <?= date('d/m/Y H:i', strtotime($user['auth_2fa_enabled_at'])) ?>
                </p>
            <?php else: ?>
                <div class="status-badge disabled">
                    2FA Disabilitato
                </div>
                <p style="color: #718096; margin-bottom: 1rem;">
                    Il tuo account non è protetto da autenticazione a due fattori
                </p>
            <?php endif; ?>

            <?php if ($is2FAEnabled): ?>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats2FA['successi'] ?? 0 ?></div>
                        <div class="stat-label">Accessi riusciti (30gg)</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value"><?= $stats2FA['fallimenti'] ?? 0 ?></div>
                        <div class="stat-label">Tentativi falliti (30gg)</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value"><?= $backupCodesRemaining ?></div>
                        <div class="stat-label">Backup codes rimanenti</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value"><?= count($devices) ?></div>
                        <div class="stat-label">Dispositivi fidati</div>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button class="btn btn-secondary" onclick="showRegenerateBackupCodes()">
                        🔄 Rigenera Backup Codes
                    </button>
                    <button class="btn btn-danger" onclick="disable2FA()">
                        ❌ Disabilita 2FA
                    </button>
                </div>
            <?php else: ?>
                <div style="margin-top: 1rem;">
                    <button class="btn btn-primary" onclick="showSetup2FA()">
                        🔒 Abilita 2FA
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Setup 2FA (nascosto per default) -->
        <div id="setup2FACard" class="setup-card hidden">
            <h2>📱 Configura 2FA</h2>

            <div class="setup-steps">
                <div class="step">
                    <h3>Scarica un'app Authenticator</h3>
                    <p>
                        Scarica una delle seguenti app sul tuo smartphone:
                    </p>
                    <ul style="margin-left: 1rem; color: #718096;">
                        <li>Google Authenticator (iOS / Android)</li>
                        <li>Microsoft Authenticator (iOS / Android)</li>
                        <li>Authy (iOS / Android / Desktop)</li>
                    </ul>
                </div>

                <div class="step">
                    <h3>Scansiona il QR Code</h3>
                    <p>
                        Apri l'app e scansiona questo QR code, oppure inserisci manualmente il codice segreto.
                    </p>

                    <div class="qr-container" id="qrCodeContainer">
                        <p style="color: #718096;">Genera il QR code cliccando "Genera QR Code"</p>
                    </div>

                    <div style="text-align: center; margin-top: 1rem;">
                        <button class="btn btn-primary" onclick="generateQRCode()" id="generateBtn">
                            📷 Genera QR Code
                        </button>
                    </div>

                    <div id="secretContainer" class="hidden">
                        <p style="text-align: center; color: #718096; margin-top: 1rem;">
                            <strong>Oppure inserisci manualmente:</strong>
                        </p>
                        <div class="secret-code" id="secretCode" onclick="copySecret()">
                            <!-- Secret sarà inserito qui -->
                        </div>
                        <p style="text-align: center; font-size: 0.75rem; color: #718096;">
                            Clicca per copiare
                        </p>
                    </div>
                </div>

                <div class="step">
                    <h3>Verifica il codice</h3>
                    <p>
                        Inserisci il codice a 6 cifre generato dall'app per completare la configurazione.
                    </p>

                    <form id="verify2FAForm" onsubmit="verify2FA(event)">
                        <div class="form-group">
                            <input
                                type="text"
                                id="verificationCode"
                                class="code-input"
                                placeholder="000000"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                required
                            >
                        </div>

                        <div style="text-align: center;">
                            <button type="submit" class="btn btn-success">
                                ✓ Verifica e Abilita 2FA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backup Codes Modal -->
        <div id="backupCodesCard" class="setup-card hidden">
            <h2>🔑 Backup Codes</h2>

            <div class="alert alert-warning">
                <strong>⚠️ Importante:</strong> Salva questi codici in un posto sicuro.
                Ogni codice può essere usato una sola volta se perdi l'accesso al tuo dispositivo.
            </div>

            <div id="backupCodesList" class="backup-codes">
                <!-- Backup codes saranno inseriti qui -->
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                <button class="btn btn-primary" onclick="downloadBackupCodes()">
                    📥 Scarica Backup Codes
                </button>
                <button class="btn btn-secondary" onclick="printBackupCodes()">
                    🖨️ Stampa
                </button>
                <button class="btn btn-secondary" onclick="closeBackupCodes()">
                    ✓ Ho salvato i codici
                </button>
            </div>
        </div>

        <!-- Dispositivi Fidati -->
        <?php if ($is2FAEnabled && count($devices) > 0): ?>
        <div class="setup-card">
            <h2>📱 Dispositivi Fidati</h2>
            <p style="color: #718096; margin-bottom: 1rem;">
                Dispositivi in cui hai scelto "Ricorda questo dispositivo"
            </p>

            <div class="devices-list">
                <?php foreach ($devices as $device): ?>
                <div class="device-item">
                    <div class="device-info">
                        <div class="device-name">
                            <?= htmlspecialchars($device['device_name'] ?? 'Dispositivo sconosciuto') ?>
                        </div>
                        <div class="device-meta">
                            IP: <?= htmlspecialchars($device['ip_address']) ?> •
                            Ultimo uso: <?= date('d/m/Y H:i', strtotime($device['last_used_at'])) ?> •
                            Scade: <?= date('d/m/Y', strtotime($device['expires_at'])) ?>
                        </div>
                    </div>
                    <button class="btn btn-danger" onclick="removeDevice(<?= $device['id'] ?>)">
                        Rimuovi
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        let currentSecret = null;
        let currentBackupCodes = [];

        function showSetup2FA() {
            document.getElementById('setup2FACard').classList.remove('hidden');
            document.getElementById('setup2FACard').scrollIntoView({ behavior: 'smooth' });
        }

        async function generateQRCode() {
            try {
                const response = await fetch('../api/2fa.php?action=setup');
                const data = await response.json();

                if (data.success) {
                    currentSecret = data.secret;

                    // Mostra QR Code
                    document.getElementById('qrCodeContainer').innerHTML = `
                        <img src="${data.qr_code_url}" alt="QR Code 2FA">
                    `;

                    // Mostra secret
                    document.getElementById('secretCode').textContent = data.secret;
                    document.getElementById('secretContainer').classList.remove('hidden');

                    // Nascondi bottone
                    document.getElementById('generateBtn').style.display = 'none';
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nella generazione del QR code');
            }
        }

        function copySecret() {
            const secretCode = document.getElementById('secretCode').textContent;
            navigator.clipboard.writeText(secretCode).then(() => {
                const originalText = document.getElementById('secretCode').textContent;
                document.getElementById('secretCode').textContent = '✓ Copiato!';
                setTimeout(() => {
                    document.getElementById('secretCode').textContent = originalText;
                }, 2000);
            });
        }

        async function verify2FA(event) {
            event.preventDefault();

            const code = document.getElementById('verificationCode').value;

            if (!currentSecret) {
                alert('Devi prima generare il QR code');
                return;
            }

            try {
                const response = await fetch('../api/2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'enable',
                        secret: currentSecret,
                        code: code
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Mostra backup codes
                    currentBackupCodes = data.backup_codes;
                    showBackupCodes(data.backup_codes);

                    // Nascondi setup
                    document.getElementById('setup2FACard').classList.add('hidden');
                } else {
                    alert('Codice non valido: ' + data.error);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nella verifica del codice');
            }
        }

        function showBackupCodes(codes) {
            const list = document.getElementById('backupCodesList');
            list.innerHTML = codes.map(code => `
                <div class="backup-code">${code}</div>
            `).join('');

            document.getElementById('backupCodesCard').classList.remove('hidden');
            document.getElementById('backupCodesCard').scrollIntoView({ behavior: 'smooth' });
        }

        function downloadBackupCodes() {
            const text = currentBackupCodes.join('\n');
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'finch-ai-backup-codes.txt';
            a.click();
        }

        function printBackupCodes() {
            window.print();
        }

        function closeBackupCodes() {
            location.reload();
        }

        async function showRegenerateBackupCodes() {
            if (!confirm('Sei sicuro di voler rigenerare i backup codes? I vecchi codici non funzioneranno più.')) {
                return;
            }

            try {
                const response = await fetch('../api/2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'regenerate_backup_codes' })
                });

                const data = await response.json();

                if (data.success) {
                    currentBackupCodes = data.backup_codes;
                    showBackupCodes(data.backup_codes);
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nella rigenerazione dei backup codes');
            }
        }

        async function disable2FA() {
            if (!confirm('Sei sicuro di voler disabilitare il 2FA? Il tuo account sarà meno sicuro.')) {
                return;
            }

            const code = prompt('Inserisci un codice 2FA per confermare:');
            if (!code) return;

            try {
                const response = await fetch('../api/2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'disable',
                        code: code
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('2FA disabilitato con successo');
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nella disabilitazione del 2FA');
            }
        }

        async function removeDevice(deviceId) {
            if (!confirm('Rimuovere questo dispositivo dalla lista dei fidati?')) {
                return;
            }

            try {
                const response = await fetch('../api/2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'remove_device',
                        device_id: deviceId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nella rimozione del dispositivo');
            }
        }
    </script>
</body>
</html>
