-- Sistema RBAC (Role-Based Access Control) e Audit Trail
-- Gestione multi-admin con permessi granulari e log completo azioni

-- ========================================
-- RUOLI E PERMESSI
-- ========================================

-- Tabella ruoli admin
CREATE TABLE IF NOT EXISTS admin_ruoli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    descrizione TEXT NULL,
    livello_accesso INT NOT NULL DEFAULT 1 COMMENT '1=Base, 2=Medio, 3=Alto, 4=Super Admin',

    -- Permessi generali
    can_view_dashboard BOOLEAN NOT NULL DEFAULT TRUE,
    can_view_analytics BOOLEAN NOT NULL DEFAULT TRUE,

    -- Gestione clienti
    can_view_clienti BOOLEAN NOT NULL DEFAULT TRUE,
    can_edit_clienti BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete_clienti BOOLEAN NOT NULL DEFAULT FALSE,
    can_impersonate_clienti BOOLEAN NOT NULL DEFAULT FALSE,

    -- Gestione servizi
    can_view_servizi BOOLEAN NOT NULL DEFAULT TRUE,
    can_edit_servizi BOOLEAN NOT NULL DEFAULT FALSE,
    can_activate_servizi BOOLEAN NOT NULL DEFAULT FALSE,
    can_deactivate_servizi BOOLEAN NOT NULL DEFAULT FALSE,

    -- Gestione fatture
    can_view_fatture BOOLEAN NOT NULL DEFAULT TRUE,
    can_create_fatture BOOLEAN NOT NULL DEFAULT FALSE,
    can_edit_fatture BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete_fatture BOOLEAN NOT NULL DEFAULT FALSE,
    can_mark_paid BOOLEAN NOT NULL DEFAULT FALSE,

    -- Gestione pagamenti
    can_view_pagamenti BOOLEAN NOT NULL DEFAULT TRUE,
    can_process_pagamenti BOOLEAN NOT NULL DEFAULT FALSE,
    can_refund BOOLEAN NOT NULL DEFAULT FALSE,

    -- Richieste addestramento
    can_view_training BOOLEAN NOT NULL DEFAULT TRUE,
    can_approve_training BOOLEAN NOT NULL DEFAULT FALSE,
    can_reject_training BOOLEAN NOT NULL DEFAULT FALSE,

    -- Comunicazioni
    can_send_emails BOOLEAN NOT NULL DEFAULT FALSE,
    can_send_sms BOOLEAN NOT NULL DEFAULT FALSE,
    can_broadcast BOOLEAN NOT NULL DEFAULT FALSE,

    -- Configurazione sistema
    can_view_settings BOOLEAN NOT NULL DEFAULT FALSE,
    can_edit_settings BOOLEAN NOT NULL DEFAULT FALSE,
    can_manage_templates BOOLEAN NOT NULL DEFAULT FALSE,

    -- Gestione team
    can_view_team BOOLEAN NOT NULL DEFAULT FALSE,
    can_invite_admin BOOLEAN NOT NULL DEFAULT FALSE,
    can_edit_admin BOOLEAN NOT NULL DEFAULT FALSE,
    can_delete_admin BOOLEAN NOT NULL DEFAULT FALSE,
    can_assign_roles BOOLEAN NOT NULL DEFAULT FALSE,

    -- Audit e log
    can_view_audit_log BOOLEAN NOT NULL DEFAULT FALSE,
    can_export_data BOOLEAN NOT NULL DEFAULT FALSE,

    -- Metadata
    attivo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_livello (livello_accesso),
    INDEX idx_attivo (attivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci ruoli predefiniti
INSERT INTO admin_ruoli (
    nome, display_name, descrizione, livello_accesso,
    can_view_clienti, can_edit_clienti, can_delete_clienti,
    can_edit_servizi, can_activate_servizi, can_deactivate_servizi,
    can_create_fatture, can_edit_fatture, can_delete_fatture, can_mark_paid,
    can_process_pagamenti, can_refund,
    can_approve_training, can_reject_training,
    can_send_emails, can_send_sms, can_broadcast,
    can_view_settings, can_edit_settings, can_manage_templates,
    can_view_team, can_invite_admin, can_edit_admin, can_delete_admin, can_assign_roles,
    can_view_audit_log, can_export_data
) VALUES
-- Super Admin: Accesso completo
('super_admin', 'Super Amministratore', 'Accesso completo a tutte le funzionalità', 4,
 TRUE, TRUE, TRUE,
 TRUE, TRUE, TRUE,
 TRUE, TRUE, TRUE, TRUE,
 TRUE, TRUE,
 TRUE, TRUE,
 TRUE, TRUE, TRUE,
 TRUE, TRUE, TRUE,
 TRUE, TRUE, TRUE, TRUE, TRUE,
 TRUE, TRUE),

-- Admin: Gestione operativa completa
('admin', 'Amministratore', 'Gestione operativa clienti, servizi e fatture', 3,
 TRUE, TRUE, FALSE,
 TRUE, TRUE, TRUE,
 TRUE, TRUE, FALSE, TRUE,
 TRUE, FALSE,
 TRUE, TRUE,
 TRUE, TRUE, FALSE,
 TRUE, FALSE, TRUE,
 TRUE, FALSE, FALSE, FALSE, FALSE,
 TRUE, TRUE),

-- Manager: Supervisione e approvazioni
('manager', 'Manager', 'Supervisione attività e approvazione richieste', 2,
 TRUE, TRUE, FALSE,
 FALSE, TRUE, TRUE,
 TRUE, TRUE, FALSE, TRUE,
 TRUE, FALSE,
 TRUE, TRUE,
 TRUE, FALSE, FALSE,
 FALSE, FALSE, FALSE,
 TRUE, FALSE, FALSE, FALSE, FALSE,
 TRUE, FALSE),

-- Supporto: Visualizzazione e assistenza clienti
('supporto', 'Supporto Clienti', 'Visualizzazione dati e assistenza base', 1,
 TRUE, FALSE, FALSE,
 FALSE, FALSE, FALSE,
 FALSE, FALSE, FALSE, FALSE,
 FALSE, FALSE,
 FALSE, FALSE,
 TRUE, FALSE, FALSE,
 FALSE, FALSE, FALSE,
 FALSE, FALSE, FALSE, FALSE, FALSE,
 FALSE, FALSE),

-- Contabile: Gestione fatture e pagamenti
('contabile', 'Contabile', 'Gestione fatturazione e contabilità', 2,
 TRUE, FALSE, FALSE,
 FALSE, FALSE, FALSE,
 TRUE, TRUE, TRUE, TRUE,
 TRUE, TRUE,
 FALSE, FALSE,
 TRUE, FALSE, FALSE,
 FALSE, FALSE, FALSE,
 FALSE, FALSE, FALSE, FALSE, FALSE,
 TRUE, TRUE);

-- Estendi tabella utenti con ruolo admin
ALTER TABLE utenti
ADD COLUMN admin_ruolo_id INT NULL COMMENT 'FK a admin_ruoli per admin',
ADD COLUMN is_super_admin BOOLEAN NOT NULL DEFAULT FALSE,
ADD COLUMN can_login BOOLEAN NOT NULL DEFAULT TRUE,
ADD COLUMN ultimo_accesso TIMESTAMP NULL,
ADD COLUMN ip_ultimo_accesso VARCHAR(45) NULL,
ADD COLUMN tentativi_login_falliti INT NOT NULL DEFAULT 0,
ADD COLUMN account_bloccato_fino TIMESTAMP NULL,
ADD COLUMN auth_2fa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
ADD COLUMN auth_2fa_secret VARCHAR(255) NULL,
ADD COLUMN session_token VARCHAR(255) NULL,
ADD COLUMN session_expires_at TIMESTAMP NULL,
ADD FOREIGN KEY (admin_ruolo_id) REFERENCES admin_ruoli(id) ON DELETE SET NULL;

-- Indici per performance
ALTER TABLE utenti ADD INDEX idx_ruolo_admin (admin_ruolo_id, ruolo);
ALTER TABLE utenti ADD INDEX idx_session (session_token, session_expires_at);
ALTER TABLE utenti ADD INDEX idx_ultimo_accesso (ultimo_accesso);

-- ========================================
-- AUDIT LOG
-- ========================================

-- Tabella audit trail completo
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Chi
    user_id INT NULL COMMENT 'ID utente che ha eseguito azione',
    user_email VARCHAR(255) NULL,
    user_ruolo VARCHAR(50) NULL,
    user_ip VARCHAR(45) NULL,
    user_agent TEXT NULL,

    -- Cosa
    azione VARCHAR(100) NOT NULL COMMENT 'Tipo azione: create, read, update, delete, login, etc',
    entita VARCHAR(100) NOT NULL COMMENT 'Tipo entità: cliente, fattura, servizio, etc',
    entita_id INT NULL COMMENT 'ID record modificato',

    -- Dettagli
    descrizione TEXT NULL,
    dati_prima JSON NULL COMMENT 'Stato prima della modifica',
    dati_dopo JSON NULL COMMENT 'Stato dopo la modifica',
    metadata JSON NULL COMMENT 'Dati aggiuntivi contestuali',

    -- Quando e dove
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    request_url VARCHAR(500) NULL,
    request_method VARCHAR(10) NULL,

    -- Severità e categoria
    livello ENUM('info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    categoria ENUM(
        'auth', 'cliente', 'servizio', 'fattura', 'pagamento',
        'email', 'sms', 'config', 'team', 'training', 'altro'
    ) NOT NULL DEFAULT 'altro',

    -- Flag
    successo BOOLEAN NOT NULL DEFAULT TRUE,
    richiede_review BOOLEAN NOT NULL DEFAULT FALSE,

    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_user (user_id, created_at),
    INDEX idx_azione (azione, created_at),
    INDEX idx_entita (entita, entita_id),
    INDEX idx_created (created_at),
    INDEX idx_categoria (categoria, livello),
    INDEX idx_review (richiede_review, livello)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella sessioni admin attive
CREATE TABLE IF NOT EXISTS admin_sessions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,

    attiva BOOLEAN NOT NULL DEFAULT TRUE,

    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,

    INDEX idx_token (session_token, attiva),
    INDEX idx_user_attiva (user_id, attiva),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella inviti team admin
CREATE TABLE IF NOT EXISTS admin_inviti (
    id INT AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NULL,
    cognome VARCHAR(255) NULL,
    ruolo_id INT NOT NULL,

    -- Invito
    invited_by INT NOT NULL COMMENT 'Admin che ha inviato invito',
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,

    -- Stato
    stato ENUM('pending', 'accepted', 'expired', 'cancelled') NOT NULL DEFAULT 'pending',
    accepted_at TIMESTAMP NULL,
    created_user_id INT NULL COMMENT 'ID utente creato dopo accettazione',

    -- Metadata
    messaggio_personale TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ruolo_id) REFERENCES admin_ruoli(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (created_user_id) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_token (token, stato),
    INDEX idx_email (email),
    INDEX idx_stato (stato, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- VISTE
-- ========================================

-- Vista admin con ruoli
CREATE OR REPLACE VIEW v_admin_team AS
SELECT
    u.id,
    u.nome,
    u.cognome,
    u.email,
    u.azienda,
    u.is_super_admin,
    u.can_login,
    u.auth_2fa_enabled,
    u.ultimo_accesso,
    u.created_at AS data_registrazione,

    ar.id AS ruolo_id,
    ar.nome AS ruolo_code,
    ar.display_name AS ruolo_nome,
    ar.livello_accesso,
    ar.descrizione AS ruolo_descrizione,

    -- Conta sessioni attive
    (SELECT COUNT(*) FROM admin_sessions s
     WHERE s.user_id = u.id AND s.attiva = TRUE AND s.expires_at > NOW()) AS sessioni_attive,

    -- Ultima attività
    (SELECT MAX(last_activity) FROM admin_sessions s
     WHERE s.user_id = u.id) AS ultima_attivita,

    -- Azioni recenti (ultimi 7 giorni)
    (SELECT COUNT(*) FROM audit_log al
     WHERE al.user_id = u.id AND al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS azioni_settimana

FROM utenti u
LEFT JOIN admin_ruoli ar ON u.admin_ruolo_id = ar.id
WHERE u.ruolo = 'admin'
ORDER BY ar.livello_accesso DESC, u.created_at DESC;

-- Vista audit log arricchita
CREATE OR REPLACE VIEW v_audit_log_dettagliato AS
SELECT
    al.id,
    al.azione,
    al.entita,
    al.entita_id,
    al.descrizione,
    al.created_at,
    al.livello,
    al.categoria,
    al.successo,
    al.richiede_review,

    u.id AS user_id,
    u.nome AS user_nome,
    u.cognome AS user_cognome,
    u.email AS user_email,

    ar.display_name AS user_ruolo_nome,
    ar.livello_accesso AS user_livello,

    al.user_ip,
    al.request_url,
    al.request_method,

    -- Descrizione entità modificata
    CASE al.entita
        WHEN 'cliente' THEN (SELECT azienda FROM utenti WHERE id = al.entita_id)
        WHEN 'fattura' THEN (SELECT numero_fattura FROM fatture WHERE id = al.entita_id)
        WHEN 'servizio' THEN (SELECT nome FROM servizi WHERE id = al.entita_id)
        ELSE NULL
    END AS entita_descrizione,

    CASE
        WHEN TIMESTAMPDIFF(MINUTE, al.created_at, NOW()) < 60 THEN
            CONCAT(TIMESTAMPDIFF(MINUTE, al.created_at, NOW()), ' min fa')
        WHEN TIMESTAMPDIFF(HOUR, al.created_at, NOW()) < 24 THEN
            CONCAT(TIMESTAMPDIFF(HOUR, al.created_at, NOW()), ' ore fa')
        WHEN TIMESTAMPDIFF(DAY, al.created_at, NOW()) < 7 THEN
            CONCAT(TIMESTAMPDIFF(DAY, al.created_at, NOW()), ' giorni fa')
        ELSE
            DATE_FORMAT(al.created_at, '%d/%m/%Y %H:%i')
    END AS tempo_relativo

FROM audit_log al
LEFT JOIN utenti u ON al.user_id = u.id
LEFT JOIN admin_ruoli ar ON u.admin_ruolo_id = ar.id
ORDER BY al.created_at DESC;

-- Vista statistiche audit per admin
CREATE OR REPLACE VIEW v_audit_statistiche_admin AS
SELECT
    user_id,
    user_email,
    DATE(created_at) AS data,
    COUNT(*) AS totale_azioni,
    SUM(CASE WHEN successo = TRUE THEN 1 ELSE 0 END) AS azioni_successo,
    SUM(CASE WHEN successo = FALSE THEN 1 ELSE 0 END) AS azioni_fallite,
    SUM(CASE WHEN livello = 'critical' THEN 1 ELSE 0 END) AS azioni_critiche,
    SUM(CASE WHEN categoria = 'auth' THEN 1 ELSE 0 END) AS login_logout,
    SUM(CASE WHEN categoria = 'cliente' THEN 1 ELSE 0 END) AS gestione_clienti,
    SUM(CASE WHEN categoria = 'fattura' THEN 1 ELSE 0 END) AS gestione_fatture,
    COUNT(DISTINCT entita) AS tipi_entita_modificate,
    MIN(created_at) AS prima_azione,
    MAX(created_at) AS ultima_azione
FROM audit_log
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY user_id, user_email, DATE(created_at);

-- ========================================
-- EVENTI AUTOMATICI
-- ========================================

-- Pulisci sessioni scadute (ogni ora)
DELIMITER //
CREATE EVENT IF NOT EXISTS pulisci_sessioni_scadute
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Disattiva sessioni scadute
    UPDATE admin_sessions
    SET attiva = FALSE
    WHERE expires_at < NOW()
      AND attiva = TRUE;

    -- Elimina sessioni vecchie (>30 giorni)
    DELETE FROM admin_sessions
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
END//
DELIMITER ;

-- Pulisci inviti scaduti (giornaliero)
DELIMITER //
CREATE EVENT IF NOT EXISTS pulisci_inviti_scaduti
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE admin_inviti
    SET stato = 'expired'
    WHERE stato = 'pending'
      AND expires_at < NOW();
END//
DELIMITER ;

-- Archivia audit log vecchi (settimanale)
DELIMITER //
CREATE EVENT IF NOT EXISTS archivia_audit_log
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Elimina log info/warning più vecchi di 90 giorni
    DELETE FROM audit_log
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
      AND livello IN ('info', 'warning')
      AND richiede_review = FALSE;

    -- Elimina log error più vecchi di 180 giorni
    DELETE FROM audit_log
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY)
      AND livello = 'error'
      AND richiede_review = FALSE;

    -- Log critical conservati per 1 anno
    DELETE FROM audit_log
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)
      AND livello = 'critical'
      AND richiede_review = FALSE;
END//
DELIMITER ;

-- Resetta tentativi login falliti (giornaliero)
DELIMITER //
CREATE EVENT IF NOT EXISTS reset_login_falliti
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Resetta contatori
    UPDATE utenti
    SET tentativi_login_falliti = 0
    WHERE tentativi_login_falliti > 0
      AND ultimo_accesso < DATE_SUB(NOW(), INTERVAL 1 DAY);

    -- Sblocca account
    UPDATE utenti
    SET account_bloccato_fino = NULL
    WHERE account_bloccato_fino IS NOT NULL
      AND account_bloccato_fino < NOW();
END//
DELIMITER ;

-- ========================================
-- TRIGGER AUDIT AUTOMATICI
-- ========================================

-- Trigger: Log creazione cliente
DELIMITER //
CREATE TRIGGER IF NOT EXISTS audit_cliente_insert
AFTER INSERT ON utenti
FOR EACH ROW
BEGIN
    IF NEW.ruolo = 'cliente' THEN
        INSERT INTO audit_log (
            user_id,
            azione,
            entita,
            entita_id,
            descrizione,
            categoria,
            dati_dopo,
            successo
        ) VALUES (
            @current_admin_id,
            'create',
            'cliente',
            NEW.id,
            CONCAT('Nuovo cliente creato: ', NEW.azienda, ' (', NEW.email, ')'),
            'cliente',
            JSON_OBJECT(
                'id', NEW.id,
                'email', NEW.email,
                'azienda', NEW.azienda,
                'nome', NEW.nome,
                'cognome', NEW.cognome
            ),
            TRUE
        );
    END IF;
END//
DELIMITER ;

-- Trigger: Log modifica cliente
DELIMITER //
CREATE TRIGGER IF NOT EXISTS audit_cliente_update
AFTER UPDATE ON utenti
FOR EACH ROW
BEGIN
    IF NEW.ruolo = 'cliente' THEN
        INSERT INTO audit_log (
            user_id,
            azione,
            entita,
            entita_id,
            descrizione,
            categoria,
            dati_prima,
            dati_dopo,
            successo
        ) VALUES (
            @current_admin_id,
            'update',
            'cliente',
            NEW.id,
            CONCAT('Cliente modificato: ', NEW.azienda),
            'cliente',
            JSON_OBJECT(
                'email', OLD.email,
                'azienda', OLD.azienda,
                'can_login', OLD.can_login
            ),
            JSON_OBJECT(
                'email', NEW.email,
                'azienda', NEW.azienda,
                'can_login', NEW.can_login
            ),
            TRUE
        );
    END IF;
END//
DELIMITER ;

-- Trigger: Log creazione fattura
DELIMITER //
CREATE TRIGGER IF NOT EXISTS audit_fattura_insert
AFTER INSERT ON fatture
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (
        user_id,
        azione,
        entita,
        entita_id,
        descrizione,
        categoria,
        dati_dopo,
        successo
    ) VALUES (
        @current_admin_id,
        'create',
        'fattura',
        NEW.id,
        CONCAT('Fattura creata: ', NEW.numero_fattura, ' - €', FORMAT(NEW.totale, 2)),
        'fattura',
        JSON_OBJECT(
            'numero_fattura', NEW.numero_fattura,
            'cliente_id', NEW.cliente_id,
            'totale', NEW.totale,
            'data_scadenza', NEW.data_scadenza
        ),
        TRUE
    );
END//
DELIMITER ;

-- Trigger: Log cambio stato fattura
DELIMITER //
CREATE TRIGGER IF NOT EXISTS audit_fattura_stato
AFTER UPDATE ON fatture
FOR EACH ROW
BEGIN
    IF OLD.stato != NEW.stato THEN
        INSERT INTO audit_log (
            user_id,
            azione,
            entita,
            entita_id,
            descrizione,
            categoria,
            dati_prima,
            dati_dopo,
            livello,
            successo
        ) VALUES (
            @current_admin_id,
            'update_stato',
            'fattura',
            NEW.id,
            CONCAT('Stato fattura ', NEW.numero_fattura, ' cambiato: ', OLD.stato, ' → ', NEW.stato),
            'fattura',
            JSON_OBJECT('stato', OLD.stato),
            JSON_OBJECT('stato', NEW.stato),
            IF(NEW.stato = 'pagata', 'info', 'warning'),
            TRUE
        );
    END IF;
END//
DELIMITER ;

-- Assegna ruolo super_admin al primo admin esistente
UPDATE utenti
SET admin_ruolo_id = (SELECT id FROM admin_ruoli WHERE nome = 'super_admin'),
    is_super_admin = TRUE
WHERE ruolo = 'admin'
ORDER BY id ASC
LIMIT 1;
