-- ============================================================================
-- Sistema 2FA (Two-Factor Authentication) per Admin
-- Autenticazione a due fattori con TOTP (Google Authenticator compatible)
-- ============================================================================

-- Aggiungi colonne 2FA alla tabella utenti (se non esistono già)
ALTER TABLE utenti
ADD COLUMN IF NOT EXISTS auth_2fa_enabled BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Se 2FA è abilitato',
ADD COLUMN IF NOT EXISTS auth_2fa_secret VARCHAR(255) NULL COMMENT 'Secret TOTP (base32)',
ADD COLUMN IF NOT EXISTS auth_2fa_backup_codes JSON NULL COMMENT 'Backup codes (hashed)',
ADD COLUMN IF NOT EXISTS auth_2fa_enabled_at TIMESTAMP NULL COMMENT 'Quando è stato abilitato 2FA',
ADD INDEX idx_2fa_enabled (auth_2fa_enabled);

-- Tabella log accessi 2FA
CREATE TABLE IF NOT EXISTS auth_2fa_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,

    -- Tipo di verifica
    verification_type ENUM('totp', 'backup_code') NOT NULL,

    -- Risultato
    success BOOLEAN NOT NULL,
    failure_reason VARCHAR(255) NULL,

    -- Metadata
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_success (success),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella dispositivi fidati (opzionale - per "Trust this device")
CREATE TABLE IF NOT EXISTS auth_trusted_devices (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,

    -- Identificazione dispositivo
    device_token VARCHAR(255) NOT NULL UNIQUE,
    device_name VARCHAR(255) NULL,
    device_fingerprint VARCHAR(255) NULL,

    -- Info dispositivo
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,

    -- Metadata
    trusted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    INDEX idx_user_id (user_id),
    INDEX idx_device_token (device_token),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Vista per monitoring 2FA
-- ============================================================================

CREATE OR REPLACE VIEW v_2fa_status AS
SELECT
    u.id,
    u.email,
    u.nome,
    u.cognome,
    u.ruolo,

    -- Status 2FA
    u.auth_2fa_enabled,
    u.auth_2fa_enabled_at,

    -- Backup codes
    JSON_LENGTH(u.auth_2fa_backup_codes) as backup_codes_remaining,

    -- Statistiche accessi
    (SELECT COUNT(*)
     FROM auth_2fa_log l
     WHERE l.user_id = u.id
     AND l.success = TRUE
     AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ) as successful_logins_30d,

    (SELECT COUNT(*)
     FROM auth_2fa_log l
     WHERE l.user_id = u.id
     AND l.success = FALSE
     AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ) as failed_logins_30d,

    -- Dispositivi fidati
    (SELECT COUNT(*)
     FROM auth_trusted_devices td
     WHERE td.user_id = u.id
     AND td.is_active = TRUE
     AND td.expires_at > NOW()
    ) as trusted_devices_count,

    -- Ultimo accesso
    (SELECT MAX(created_at)
     FROM auth_2fa_log l
     WHERE l.user_id = u.id
     AND l.success = TRUE
    ) as last_successful_login

FROM utenti u
WHERE u.ruolo = 'admin';

-- ============================================================================
-- Eventi automatici per manutenzione
-- ============================================================================

-- Pulizia log 2FA vecchi (conserva 90 giorni)
CREATE EVENT IF NOT EXISTS cleanup_2fa_logs
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
    DELETE FROM auth_2fa_log
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Rimuovi dispositivi fidati scaduti
CREATE EVENT IF NOT EXISTS cleanup_trusted_devices
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
    UPDATE auth_trusted_devices
    SET is_active = FALSE
    WHERE expires_at < NOW() AND is_active = TRUE;

-- ============================================================================
-- Trigger per audit automatico
-- ============================================================================

DELIMITER $$

-- Trigger quando 2FA viene abilitato
CREATE TRIGGER IF NOT EXISTS audit_2fa_enabled
AFTER UPDATE ON utenti
FOR EACH ROW
BEGIN
    IF NEW.auth_2fa_enabled = TRUE AND OLD.auth_2fa_enabled = FALSE THEN
        INSERT INTO audit_log (
            user_id,
            user_email,
            user_ruolo,
            azione,
            entita,
            entita_id,
            descrizione,
            categoria,
            livello,
            richiede_review
        ) VALUES (
            NEW.id,
            NEW.email,
            NEW.ruolo,
            'enable_2fa',
            'security',
            NEW.id,
            CONCAT('2FA abilitato per ', NEW.email),
            'auth',
            'warning',
            TRUE
        );
    END IF;
END$$

-- Trigger quando 2FA viene disabilitato
CREATE TRIGGER IF NOT EXISTS audit_2fa_disabled
AFTER UPDATE ON utenti
FOR EACH ROW
BEGIN
    IF NEW.auth_2fa_enabled = FALSE AND OLD.auth_2fa_enabled = TRUE THEN
        INSERT INTO audit_log (
            user_id,
            user_email,
            user_ruolo,
            azione,
            entita,
            entita_id,
            descrizione,
            categoria,
            livello,
            richiede_review
        ) VALUES (
            NEW.id,
            NEW.email,
            NEW.ruolo,
            'disable_2fa',
            'security',
            NEW.id,
            CONCAT('2FA disabilitato per ', NEW.email),
            'auth',
            'critical',
            TRUE
        );
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- Stored Procedure per operazioni comuni
-- ============================================================================

DELIMITER $$

-- Procedure: Verifica se troppi tentativi falliti
CREATE PROCEDURE IF NOT EXISTS check_2fa_attempts(
    IN p_user_id INT,
    IN p_minutes INT,
    IN p_max_attempts INT,
    OUT p_is_locked BOOLEAN
)
BEGIN
    DECLARE attempts INT;

    SELECT COUNT(*) INTO attempts
    FROM auth_2fa_log
    WHERE user_id = p_user_id
        AND success = FALSE
        AND created_at >= DATE_SUB(NOW(), INTERVAL p_minutes MINUTE);

    SET p_is_locked = (attempts >= p_max_attempts);
END$$

-- Procedure: Statistiche 2FA per admin
CREATE PROCEDURE IF NOT EXISTS get_2fa_stats(IN p_days INT)
BEGIN
    SELECT
        COUNT(DISTINCT user_id) as total_users_with_2fa,
        SUM(CASE WHEN success = TRUE THEN 1 ELSE 0 END) as successful_verifications,
        SUM(CASE WHEN success = FALSE THEN 1 ELSE 0 END) as failed_verifications,
        SUM(CASE WHEN verification_type = 'totp' THEN 1 ELSE 0 END) as totp_usage,
        SUM(CASE WHEN verification_type = 'backup_code' THEN 1 ELSE 0 END) as backup_code_usage,
        COUNT(DISTINCT DATE(created_at)) as active_days
    FROM auth_2fa_log
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL p_days DAY);
END$$

DELIMITER ;

-- ============================================================================
-- Configurazione iniziale
-- ============================================================================

-- Imposta super admin esistente a richiedere 2FA (opzionale)
-- UPDATE utenti
-- SET auth_2fa_enabled = FALSE
-- WHERE ruolo = 'admin' AND is_super_admin = TRUE;

-- ============================================================================
-- Query di verifica
-- ============================================================================

-- Verifica installazione
SELECT 'Installazione 2FA completata!' as status;

-- Mostra admin con 2FA
SELECT
    email,
    auth_2fa_enabled,
    auth_2fa_enabled_at,
    JSON_LENGTH(auth_2fa_backup_codes) as backup_codes
FROM utenti
WHERE ruolo = 'admin';

-- Mostra statistiche dalla vista
SELECT * FROM v_2fa_status;
