-- ============================================================================
-- Churn Prediction System
-- Sistema di analisi predittiva per identificare clienti a rischio abbandono
-- ============================================================================

-- Tabella predizioni churn
CREATE TABLE IF NOT EXISTS churn_predictions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,

    -- Prediction scores
    churn_probability DECIMAL(5,4) NOT NULL COMMENT '0.0000 - 1.0000',
    risk_level ENUM('low', 'medium', 'high') NOT NULL,

    -- Component scores (JSON)
    scores_json JSON NOT NULL COMMENT 'Scores per ogni componente',

    -- Top risk factors
    top_risk_factors VARCHAR(255) NULL COMMENT 'Comma-separated',

    -- Recommendations (JSON array)
    recommendations_json JSON NULL,

    -- Features utilizzate (JSON)
    features_json JSON NULL COMMENT 'Features per debugging/audit',

    -- Metadata
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE KEY idx_cliente (cliente_id),
    INDEX idx_risk_level (risk_level),
    INDEX idx_probability (churn_probability),
    INDEX idx_updated (updated_at),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella azioni retention intraprese
CREATE TABLE IF NOT EXISTS churn_retention_actions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    prediction_id BIGINT NULL,

    -- Action details
    action_type ENUM(
        'call',
        'email',
        'meeting',
        'discount_offer',
        'upgrade_offer',
        'training',
        'support_escalation',
        'other'
    ) NOT NULL,

    category ENUM('engagement', 'payment', 'retention', 'support', 'onboarding', 'monitoring') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') NOT NULL,

    description TEXT NOT NULL,
    assigned_to INT NULL COMMENT 'Admin user ID',

    -- Status
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',

    -- Outcome
    outcome TEXT NULL,
    effectiveness_score INT NULL COMMENT '1-5 rating',

    -- Dates
    scheduled_date DATE NULL,
    completed_date DATE NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_scheduled (scheduled_date),
    INDEX idx_assigned (assigned_to),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (prediction_id) REFERENCES churn_predictions(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella storico churn (track changes over time)
CREATE TABLE IF NOT EXISTS churn_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,

    churn_probability DECIMAL(5,4) NOT NULL,
    risk_level ENUM('low', 'medium', 'high') NOT NULL,

    scores_json JSON NOT NULL,

    snapshot_date DATE NOT NULL,

    INDEX idx_cliente_date (cliente_id, snapshot_date),
    INDEX idx_snapshot_date (snapshot_date),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Vista per dashboard churn
-- ============================================================================

CREATE OR REPLACE VIEW v_churn_dashboard AS
SELECT
    u.id as cliente_id,
    u.email,
    u.nome,
    u.cognome,
    u.azienda,
    u.created_at as cliente_dal,
    DATEDIFF(NOW(), u.created_at) as giorni_cliente,

    -- Churn prediction
    cp.churn_probability,
    cp.risk_level,
    cp.top_risk_factors,
    cp.updated_at as ultima_predizione,

    -- Services
    (SELECT COUNT(*) FROM servizi_attivi WHERE cliente_id = u.id AND stato = 'attivo') as servizi_attivi,
    (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata') as lifetime_value,

    -- Last activity
    u.last_login,
    DATEDIFF(NOW(), u.last_login) as giorni_inattivo,

    -- Pending actions
    (SELECT COUNT(*)
     FROM churn_retention_actions
     WHERE cliente_id = u.id
     AND status IN ('planned', 'in_progress')
    ) as azioni_pending

FROM utenti u
LEFT JOIN churn_predictions cp ON u.id = cp.cliente_id
WHERE u.ruolo = 'cliente'
AND u.attivo = TRUE;

-- ============================================================================
-- Stored Procedure per calcolo batch
-- ============================================================================

DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS calculate_churn_batch(
    IN p_limit INT
)
BEGIN
    -- Questa procedure sarà chiamata da PHP
    -- per calcolare churn prediction per tutti i clienti

    SELECT
        id,
        email,
        nome,
        cognome
    FROM utenti
    WHERE ruolo = 'cliente'
    AND attivo = TRUE
    LIMIT p_limit;
END$$

DELIMITER ;

-- ============================================================================
-- Trigger per storicizzare predizioni
-- ============================================================================

DELIMITER $$

CREATE TRIGGER IF NOT EXISTS churn_prediction_history
AFTER UPDATE ON churn_predictions
FOR EACH ROW
BEGIN
    -- Salva snapshot solo se probabilità cambia significativamente (> 5%)
    IF ABS(NEW.churn_probability - OLD.churn_probability) > 0.05 THEN
        INSERT INTO churn_history (
            cliente_id,
            churn_probability,
            risk_level,
            scores_json,
            snapshot_date
        ) VALUES (
            NEW.cliente_id,
            NEW.churn_probability,
            NEW.risk_level,
            NEW.scores_json,
            CURDATE()
        );
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- Eventi automatici
-- ============================================================================

-- Calcolo churn giornaliero (alle 04:00)
-- CREATE EVENT IF NOT EXISTS daily_churn_calculation
-- ON SCHEDULE EVERY 1 DAY
-- STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 04:00:00')
-- DO
--     CALL calculate_churn_batch(1000);

-- Pulizia storico vecchio (conserva 1 anno)
CREATE EVENT IF NOT EXISTS cleanup_churn_history
ON SCHEDULE EVERY 1 MONTH
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 05:00:00')
DO
    DELETE FROM churn_history
    WHERE snapshot_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- ============================================================================
-- Query di verifica
-- ============================================================================

-- Verifica installazione
SELECT 'Churn Prediction System installato!' as status;

-- Mostra struttura
SHOW TABLES LIKE 'churn_%';

-- Test vista
SELECT COUNT(*) as total_clienti FROM v_churn_dashboard;

-- ============================================================================
-- Dati di esempio (opzionale - per testing)
-- ============================================================================

-- Inserisci prediction di test
-- INSERT INTO churn_predictions (
--     cliente_id,
--     churn_probability,
--     risk_level,
--     scores_json,
--     top_risk_factors
-- )
-- SELECT
--     id,
--     0.75,
--     'high',
--     JSON_OBJECT(
--         'engagement', 0.8,
--         'payment_behavior', 0.7,
--         'usage_trend', 0.6,
--         'support_tickets', 0.5,
--         'contract_status', 0.4
--     ),
--     'engagement,payment_behavior,usage_trend'
-- FROM utenti
-- WHERE ruolo = 'cliente'
-- LIMIT 1;
