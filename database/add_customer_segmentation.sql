-- ============================================================================
-- Customer Segmentation System
-- Sistema di clustering automatico basato su comportamento clienti
-- Algoritmo: K-means con feature engineering avanzato
-- ============================================================================

-- Tabella assegnazioni clienti a segmenti
CREATE TABLE IF NOT EXISTS customer_segments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    segment_id INT NOT NULL,

    assignment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY idx_cliente (cliente_id),
    INDEX idx_segment (segment_id),
    INDEX idx_assignment_date (assignment_date),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella profili segmenti (personas)
CREATE TABLE IF NOT EXISTS segment_profiles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    segment_id INT NOT NULL,

    -- Persona
    persona_name VARCHAR(100) NOT NULL,
    persona_description TEXT,
    persona_icon VARCHAR(10) DEFAULT '👤',

    -- Statistiche
    size INT NOT NULL DEFAULT 0,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,

    -- Metriche medie
    avg_ltv DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    avg_engagement DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    avg_usage DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    avg_churn_risk DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    avg_tenure_days INT NOT NULL DEFAULT 0,

    -- Caratteristiche (JSON array)
    characteristics JSON NULL COMMENT 'Array: high_value, power_user, at_risk, etc',

    -- Raccomandazioni strategiche (JSON array)
    recommendations JSON NULL COMMENT 'Array di oggetti {priority, action, message}',

    -- Centroid data (per assignment nuovi clienti)
    centroid_data JSON NULL COMMENT 'Feature values del centroid',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY idx_segment (segment_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella storico segmentazione (track segment migrations)
CREATE TABLE IF NOT EXISTS segment_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,

    old_segment_id INT NULL,
    new_segment_id INT NOT NULL,

    migration_date DATE NOT NULL,

    INDEX idx_cliente (cliente_id),
    INDEX idx_migration_date (migration_date),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella campagne per segmento
CREATE TABLE IF NOT EXISTS segment_campaigns (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    segment_id INT NOT NULL,

    campaign_name VARCHAR(255) NOT NULL,
    campaign_type ENUM('email', 'sms', 'call', 'in_app', 'mixed') NOT NULL,

    -- Targeting
    target_action ENUM('retention', 'upsell', 'reengagement', 'onboarding', 'advocacy') NOT NULL,

    -- Message
    subject VARCHAR(255) NULL,
    message TEXT NULL,

    -- Scheduling
    scheduled_date DATE NULL,
    sent_date DATE NULL,

    -- Performance
    sent_count INT DEFAULT 0,
    opened_count INT DEFAULT 0,
    clicked_count INT DEFAULT 0,
    converted_count INT DEFAULT 0,

    status ENUM('draft', 'scheduled', 'sent', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',

    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_segment (segment_id),
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_date),

    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Viste
-- ============================================================================

-- Vista segmenti con dettagli completi
CREATE OR REPLACE VIEW v_customer_segments AS
SELECT
    u.id as cliente_id,
    u.email,
    u.nome,
    u.cognome,
    u.azienda,

    cs.segment_id,
    sp.persona_name,
    sp.persona_icon,
    sp.persona_description,

    -- Metriche cliente
    COALESCE(
        (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata'),
        0
    ) as lifetime_value,

    (SELECT COUNT(*) FROM servizi_attivi WHERE cliente_id = u.id AND stato = 'attivo') as active_services,

    DATEDIFF(NOW(), u.last_login) as days_since_last_login,
    DATEDIFF(NOW(), u.created_at) as days_as_customer,

    -- Segment info
    sp.avg_ltv as segment_avg_ltv,
    sp.characteristics as segment_characteristics,

    cs.assignment_date

FROM utenti u
JOIN customer_segments cs ON u.id = cs.cliente_id
JOIN segment_profiles sp ON cs.segment_id = sp.segment_id
WHERE u.ruolo = 'cliente'
AND u.attivo = TRUE;

-- Vista performance campagne per segmento
CREATE OR REPLACE VIEW v_segment_campaign_performance AS
SELECT
    sc.segment_id,
    sp.persona_name,

    COUNT(*) as total_campaigns,
    SUM(sc.sent_count) as total_sent,
    SUM(sc.opened_count) as total_opened,
    SUM(sc.clicked_count) as total_clicked,
    SUM(sc.converted_count) as total_converted,

    CASE WHEN SUM(sc.sent_count) > 0
        THEN ROUND((SUM(sc.opened_count) / SUM(sc.sent_count)) * 100, 2)
        ELSE 0
    END as open_rate,

    CASE WHEN SUM(sc.sent_count) > 0
        THEN ROUND((SUM(sc.clicked_count) / SUM(sc.sent_count)) * 100, 2)
        ELSE 0
    END as click_rate,

    CASE WHEN SUM(sc.sent_count) > 0
        THEN ROUND((SUM(sc.converted_count) / SUM(sc.sent_count)) * 100, 2)
        ELSE 0
    END as conversion_rate

FROM segment_campaigns sc
JOIN segment_profiles sp ON sc.segment_id = sp.segment_id
WHERE sc.status IN ('sent', 'completed')
GROUP BY sc.segment_id, sp.persona_name;

-- Vista migrazioni segmenti
CREATE OR REPLACE VIEW v_segment_migrations AS
SELECT
    u.email,
    u.nome,
    u.cognome,

    sp1.persona_name as old_segment,
    sp2.persona_name as new_segment,

    sh.migration_date,

    DATEDIFF(NOW(), sh.migration_date) as days_since_migration

FROM segment_history sh
JOIN utenti u ON sh.cliente_id = u.id
LEFT JOIN segment_profiles sp1 ON sh.old_segment_id = sp1.segment_id
JOIN segment_profiles sp2 ON sh.new_segment_id = sp2.segment_id
ORDER BY sh.migration_date DESC;

-- ============================================================================
-- Stored Procedures
-- ============================================================================

DELIMITER $$

-- Ottieni clienti di un segmento
CREATE PROCEDURE IF NOT EXISTS get_segment_customers(
    IN p_segment_id INT
)
BEGIN
    SELECT
        u.id,
        u.email,
        u.nome,
        u.cognome,
        u.azienda,
        COALESCE(
            (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata'),
            0
        ) as lifetime_value,
        DATEDIFF(NOW(), u.last_login) as days_since_last_login
    FROM utenti u
    JOIN customer_segments cs ON u.id = cs.cliente_id
    WHERE cs.segment_id = p_segment_id
    AND u.ruolo = 'cliente'
    AND u.attivo = TRUE
    ORDER BY lifetime_value DESC;
END$$

-- Statistiche distribuzione segmenti
CREATE PROCEDURE IF NOT EXISTS segment_distribution_stats()
BEGIN
    SELECT
        sp.segment_id,
        sp.persona_name,
        sp.persona_icon,
        sp.size,
        sp.percentage,
        sp.avg_ltv,
        sp.avg_churn_risk,

        -- Revenue potential
        sp.size * sp.avg_ltv as total_segment_value,

        -- At-risk count
        (SELECT COUNT(*)
         FROM customer_segments cs
         JOIN churn_predictions cp ON cs.cliente_id = cp.cliente_id
         WHERE cs.segment_id = sp.segment_id
         AND cp.risk_level = 'high'
        ) as high_risk_count

    FROM segment_profiles sp
    ORDER BY sp.size DESC;
END$$

-- Raccomandazioni per segmento
CREATE PROCEDURE IF NOT EXISTS get_segment_recommendations(
    IN p_segment_id INT
)
BEGIN
    SELECT
        sp.persona_name,
        sp.persona_description,
        sp.recommendations,
        sp.characteristics,
        sp.size,
        sp.avg_ltv,
        sp.avg_churn_risk
    FROM segment_profiles sp
    WHERE sp.segment_id = p_segment_id;
END$$

DELIMITER ;

-- ============================================================================
-- Trigger
-- ============================================================================

DELIMITER $$

-- Log segment migration quando cliente cambia segmento
CREATE TRIGGER IF NOT EXISTS segment_migration_tracker
AFTER UPDATE ON customer_segments
FOR EACH ROW
BEGIN
    IF NEW.segment_id != OLD.segment_id THEN
        INSERT INTO segment_history (
            cliente_id,
            old_segment_id,
            new_segment_id,
            migration_date
        ) VALUES (
            NEW.cliente_id,
            OLD.segment_id,
            NEW.segment_id,
            CURDATE()
        );
    END IF;
END$$

-- Aggiorna size segmento quando cliente assegnato
CREATE TRIGGER IF NOT EXISTS update_segment_size_insert
AFTER INSERT ON customer_segments
FOR EACH ROW
BEGIN
    UPDATE segment_profiles
    SET size = (
        SELECT COUNT(*)
        FROM customer_segments
        WHERE segment_id = NEW.segment_id
    )
    WHERE segment_id = NEW.segment_id;
END$$

CREATE TRIGGER IF NOT EXISTS update_segment_size_delete
AFTER DELETE ON customer_segments
FOR EACH ROW
BEGIN
    UPDATE segment_profiles
    SET size = (
        SELECT COUNT(*)
        FROM customer_segments
        WHERE segment_id = OLD.segment_id
    )
    WHERE segment_id = OLD.segment_id;
END$$

DELIMITER ;

-- ============================================================================
-- Eventi automatici
-- ============================================================================

-- Ricalcola segmentazione settimanalmente (domenica alle 02:00)
-- Nota: eseguito via PHP per controllo completo
-- CREATE EVENT IF NOT EXISTS weekly_segmentation
-- ON SCHEDULE EVERY 1 WEEK
-- STARTS '2025-12-22 02:00:00'
-- DO
--     -- Chiamata a PHP script esterno
--     CALL execute_segmentation_refresh();

-- Pulizia storico migrazioni (conserva 1 anno)
CREATE EVENT IF NOT EXISTS cleanup_segment_history
ON SCHEDULE EVERY 1 MONTH
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 04:00:00')
DO
    DELETE FROM segment_history
    WHERE migration_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- ============================================================================
-- Utility table: Service Usage (per usage intensity calculation)
-- ============================================================================

CREATE TABLE IF NOT EXISTS service_usage (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servizio_id INT NOT NULL,

    usage_count INT NOT NULL DEFAULT 1,
    date DATE NOT NULL,

    INDEX idx_cliente_date (cliente_id, date),
    INDEX idx_servizio (servizio_id),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Query di verifica
-- ============================================================================

SELECT 'Customer Segmentation System installato!' as status;

-- Mostra tabelle create
SHOW TABLES LIKE '%segment%';

-- Test viste
SELECT COUNT(*) as segment_count FROM segment_profiles;

-- ============================================================================
-- Note implementazione
-- ============================================================================

/*
WORKFLOW SEGMENTAZIONE:

1. INITIAL CLUSTERING (una volta):
   - PHP: $segmentation->performClustering()
   - Analizza tutti i clienti
   - Identifica cluster ottimali (Elbow method)
   - Esegue K-means
   - Crea personas
   - Salva in segment_profiles e customer_segments

2. ASSIGNMENT NUOVI CLIENTI (automatico):
   - Nuovo cliente registrato
   - PHP: $segmentation->assignCustomerToSegment($clienteId)
   - Calcola distanza da centroids esistenti
   - Assegna a segmento più vicino

3. RE-SEGMENTAZIONE (settimanale):
   - CRON job domenica 02:00
   - PHP: $segmentation->performClustering()
   - Ricalcola tutti i segmenti
   - Trigger traccia migrations in segment_history

4. CAMPAGNE TARGETED:
   - Admin crea campagna per segmento
   - Es: Email "retention" per segmento "At-Risk VIPs"
   - Track performance (open rate, conversion rate)

PERSONAS TIPICHE GENERATE:

👑 VIP Champions
   - High LTV + High Engagement
   - Azione: Upselling, Advocacy

⚠️ At-Risk VIPs
   - High LTV + High Churn Risk
   - Azione: Retention urgente

🚀 Power Users Budget
   - High Usage + Low LTV
   - Azione: Pricing optimization

🌱 New Explorers
   - Tenure < 60 giorni
   - Azione: Onboarding, Training

💎 Loyal Advocates
   - Tenure > 180 giorni + Good engagement
   - Azione: Referral, Case study

😴 Hibernating
   - Low Engagement
   - Azione: Re-engagement campaign

👤 Standard Users
   - Medie su tutte le metriche
   - Azione: Monitoring

METRICHE CHIAVE:

- LTV Score: Lifetime value normalizzato
- Engagement Score: Login frequency + Actions
- Usage Intensity: API calls + Active services
- Service Diversity: Numero tipi servizi diversi
- Payment Reliability: On-time payment ratio
- Tenure Score: Anzianità cliente

ALGORITMO K-MEANS:

1. Feature extraction (6 metriche)
2. Normalization (min-max 0-1)
3. K-means++ initialization
4. Iteration until convergence
5. Elbow method for optimal K
6. Cluster profiling

*/
