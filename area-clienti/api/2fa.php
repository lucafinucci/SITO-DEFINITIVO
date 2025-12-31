<?php
/**
 * API 2FA (Two-Factor Authentication)
 * Gestione autenticazione a due fattori
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/totp.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = getRBAC($pdo);
$audit = new AuditLogger($pdo);
$userId = $_SESSION['cliente_id'];

// Helper: Log 2FA
function log2FAAttempt($pdo, $userId, $type, $success, $reason = null) {
    $stmt = $pdo->prepare('
        INSERT INTO auth_2fa_log (
            user_id,
            user_email,
            ip_address,
            user_agent,
            verification_type,
            success,
            failure_reason
        )
        SELECT
            :user_id,
            email,
            :ip,
            :user_agent,
            :type,
            :success,
            :reason
        FROM utenti
        WHERE id = :user_id2
    ');

    $stmt->execute([
        'user_id' => $userId,
        'user_id2' => $userId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'type' => $type,
        'success' => $success,
        'reason' => $reason
    ]);
}

try {
    // Leggi JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'setup':
            // Genera secret e QR code per setup iniziale
            $userStmt = $pdo->prepare('SELECT email FROM utenti WHERE id = :id');
            $userStmt->execute(['id' => $userId]);
            $user = $userStmt->fetch();

            if (!$user) {
                throw new Exception('Utente non trovato');
            }

            // Genera secret
            $secret = TOTP::generateSecret(16);

            // Genera QR Code URL
            $qrCodeUrl = TOTP::getQRCodeURL($secret, $user['email'], 'Finch-AI');

            echo json_encode([
                'success' => true,
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl,
                'provisioning_uri' => TOTP::getProvisioningURI($secret, $user['email'], 'Finch-AI')
            ]);
            break;

        case 'enable':
            // Abilita 2FA dopo verifica codice
            $secret = $input['secret'] ?? '';
            $code = $input['code'] ?? '';

            if (!$secret || !$code) {
                throw new Exception('Secret e codice richiesti');
            }

            // Verifica codice TOTP
            if (!TOTP::verifyCode($secret, $code)) {
                log2FAAttempt($pdo, $userId, 'totp', false, 'Codice non valido durante setup');
                throw new Exception('Codice non valido');
            }

            // Genera backup codes
            $backupCodes = [];
            for ($i = 0; $i < 10; $i++) {
                $code = '';
                for ($j = 0; $j < 8; $j++) {
                    $code .= random_int(0, 9);
                }
                $backupCodes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            }

            // Hash backup codes
            $hashedBackupCodes = array_map(function($code) {
                return password_hash(str_replace('-', '', $code), PASSWORD_DEFAULT);
            }, $backupCodes);

            // Salva nel database
            $stmt = $pdo->prepare('
                UPDATE utenti
                SET
                    auth_2fa_enabled = TRUE,
                    auth_2fa_secret = :secret,
                    auth_2fa_backup_codes = :backup_codes,
                    auth_2fa_enabled_at = NOW()
                WHERE id = :user_id
            ');

            $stmt->execute([
                'user_id' => $userId,
                'secret' => $secret,
                'backup_codes' => json_encode($hashedBackupCodes)
            ]);

            // Log audit
            $audit->log([
                'azione' => 'enable_2fa',
                'entita' => 'security',
                'descrizione' => '2FA abilitato',
                'categoria' => 'auth',
                'livello' => 'warning',
                'richiede_review' => true
            ]);

            log2FAAttempt($pdo, $userId, 'totp', true);

            echo json_encode([
                'success' => true,
                'backup_codes' => $backupCodes,
                'message' => '2FA abilitato con successo'
            ]);
            break;

        case 'disable':
            // Disabilita 2FA (richiede verifica codice)
            $code = $input['code'] ?? '';

            if (!$code) {
                throw new Exception('Codice richiesto');
            }

            // Recupera secret corrente
            $stmt = $pdo->prepare('
                SELECT auth_2fa_secret, auth_2fa_backup_codes
                FROM utenti
                WHERE id = :id AND auth_2fa_enabled = TRUE
            ');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('2FA non abilitato');
            }

            // Verifica codice TOTP
            $validCode = TOTP::verifyCode($user['auth_2fa_secret'], $code);

            // Se non è TOTP, verifica backup code
            if (!$validCode && $user['auth_2fa_backup_codes']) {
                $backupCodes = json_decode($user['auth_2fa_backup_codes'], true);
                $codeNoSpaces = str_replace('-', '', $code);

                foreach ($backupCodes as $hashedCode) {
                    if (password_verify($codeNoSpaces, $hashedCode)) {
                        $validCode = true;
                        break;
                    }
                }
            }

            if (!$validCode) {
                log2FAAttempt($pdo, $userId, 'totp', false, 'Codice non valido per disabilitazione');
                throw new Exception('Codice non valido');
            }

            // Disabilita 2FA
            $stmt = $pdo->prepare('
                UPDATE utenti
                SET
                    auth_2fa_enabled = FALSE,
                    auth_2fa_secret = NULL,
                    auth_2fa_backup_codes = NULL,
                    auth_2fa_enabled_at = NULL
                WHERE id = :user_id
            ');

            $stmt->execute(['user_id' => $userId]);

            // Log audit
            $audit->log([
                'azione' => 'disable_2fa',
                'entita' => 'security',
                'descrizione' => '2FA disabilitato',
                'categoria' => 'auth',
                'livello' => 'critical',
                'richiede_review' => true
            ]);

            log2FAAttempt($pdo, $userId, 'totp', true);

            echo json_encode([
                'success' => true,
                'message' => '2FA disabilitato'
            ]);
            break;

        case 'regenerate_backup_codes':
            // Rigenera backup codes
            $stmt = $pdo->prepare('
                SELECT auth_2fa_enabled
                FROM utenti
                WHERE id = :id
            ');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user || !$user['auth_2fa_enabled']) {
                throw new Exception('2FA non abilitato');
            }

            // Genera nuovi backup codes
            $backupCodes = [];
            for ($i = 0; $i < 10; $i++) {
                $code = '';
                for ($j = 0; $j < 8; $j++) {
                    $code .= random_int(0, 9);
                }
                $backupCodes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            }

            // Hash backup codes
            $hashedBackupCodes = array_map(function($code) {
                return password_hash(str_replace('-', '', $code), PASSWORD_DEFAULT);
            }, $backupCodes);

            // Aggiorna database
            $stmt = $pdo->prepare('
                UPDATE utenti
                SET auth_2fa_backup_codes = :codes
                WHERE id = :user_id
            ');

            $stmt->execute([
                'user_id' => $userId,
                'codes' => json_encode($hashedBackupCodes)
            ]);

            // Log audit
            $audit->log([
                'azione' => 'regenerate_2fa_backup_codes',
                'entita' => 'security',
                'descrizione' => 'Backup codes rigenerati',
                'categoria' => 'auth',
                'livello' => 'warning',
                'richiede_review' => true
            ]);

            echo json_encode([
                'success' => true,
                'backup_codes' => $backupCodes,
                'message' => 'Backup codes rigenerati'
            ]);
            break;

        case 'verify':
            // Verifica codice 2FA (usato al login)
            $code = $input['code'] ?? '';
            $trustDevice = (bool)($input['trust_device'] ?? false);

            if (!$code) {
                throw new Exception('Codice richiesto');
            }

            // Recupera dati utente
            $stmt = $pdo->prepare('
                SELECT auth_2fa_secret, auth_2fa_backup_codes
                FROM utenti
                WHERE id = :id AND auth_2fa_enabled = TRUE
            ');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('2FA non abilitato');
            }

            $verifiedVia = null;

            // Verifica codice TOTP
            if (TOTP::verifyCode($user['auth_2fa_secret'], $code)) {
                $verifiedVia = 'totp';
            }
            // Verifica backup code
            elseif ($user['auth_2fa_backup_codes']) {
                $backupCodes = json_decode($user['auth_2fa_backup_codes'], true);
                $codeNoSpaces = str_replace('-', '', $code);

                foreach ($backupCodes as $index => $hashedCode) {
                    if (password_verify($codeNoSpaces, $hashedCode)) {
                        $verifiedVia = 'backup_code';

                        // Rimuovi backup code usato
                        unset($backupCodes[$index]);

                        $stmt = $pdo->prepare('
                            UPDATE utenti
                            SET auth_2fa_backup_codes = :codes
                            WHERE id = :user_id
                        ');

                        $stmt->execute([
                            'user_id' => $userId,
                            'codes' => json_encode(array_values($backupCodes))
                        ]);

                        break;
                    }
                }
            }

            if (!$verifiedVia) {
                log2FAAttempt($pdo, $userId, 'totp', false, 'Codice non valido');
                throw new Exception('Codice non valido');
            }

            // Marca sessione come verificata
            $_SESSION['2fa_verified'] = true;
            $_SESSION['2fa_verified_at'] = time();

            // Se richiesto, salva dispositivo fidato
            if ($trustDevice) {
                $deviceToken = bin2hex(random_bytes(32));
                $deviceFingerprint = md5($_SERVER['HTTP_USER_AGENT'] ?? '');

                $stmt = $pdo->prepare('
                    INSERT INTO auth_trusted_devices (
                        user_id,
                        device_token,
                        device_name,
                        device_fingerprint,
                        ip_address,
                        user_agent,
                        expires_at
                    ) VALUES (
                        :user_id,
                        :token,
                        :name,
                        :fingerprint,
                        :ip,
                        :user_agent,
                        DATE_ADD(NOW(), INTERVAL 30 DAY)
                    )
                ');

                $stmt->execute([
                    'user_id' => $userId,
                    'token' => $deviceToken,
                    'name' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'fingerprint' => $deviceFingerprint,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);

                // Salva token in cookie
                setcookie('2fa_device', $deviceToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }

            log2FAAttempt($pdo, $userId, $verifiedVia, true);

            echo json_encode([
                'success' => true,
                'verified_via' => $verifiedVia,
                'message' => 'Codice verificato'
            ]);
            break;

        case 'remove_device':
            // Rimuovi dispositivo fidato
            $deviceId = (int)($input['device_id'] ?? 0);

            if (!$deviceId) {
                throw new Exception('ID dispositivo mancante');
            }

            $stmt = $pdo->prepare('
                UPDATE auth_trusted_devices
                SET is_active = FALSE
                WHERE id = :id AND user_id = :user_id
            ');

            $stmt->execute([
                'id' => $deviceId,
                'user_id' => $userId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Dispositivo rimosso'
            ]);
            break;

        case 'check_trusted_device':
            // Verifica se il dispositivo corrente è fidato
            $deviceToken = $_COOKIE['2fa_device'] ?? null;

            if (!$deviceToken) {
                echo json_encode([
                    'success' => true,
                    'is_trusted' => false
                ]);
                break;
            }

            $stmt = $pdo->prepare('
                SELECT id, last_used_at
                FROM auth_trusted_devices
                WHERE user_id = :user_id
                AND device_token = :token
                AND is_active = TRUE
                AND expires_at > NOW()
            ');

            $stmt->execute([
                'user_id' => $userId,
                'token' => $deviceToken
            ]);

            $device = $stmt->fetch();

            if ($device) {
                // Aggiorna ultimo utilizzo
                $updateStmt = $pdo->prepare('
                    UPDATE auth_trusted_devices
                    SET last_used_at = NOW()
                    WHERE id = :id
                ');
                $updateStmt->execute(['id' => $device['id']]);

                echo json_encode([
                    'success' => true,
                    'is_trusted' => true
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'is_trusted' => false
                ]);
            }
            break;

        default:
            throw new Exception('Azione non valida');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
