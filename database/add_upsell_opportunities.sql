-- ============================================================================
-- Upselling & Cross-selling Intelligence System
-- Sistema di raccomandazioni automatiche per opportunità di vendita
-- ============================================================================

-- Tabella catalogo servizi (se non esiste già)
CREATE TABLE IF NOT EXISTS servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    tipo_servizio VARCHAR(100) NOT NULL,
    categoria ENUM('base', 'premium', 'enterprise', 'addon') NOT NULL DEFAULT 'base',
    prezzo_mensile DECIMAL(10,2) NOT NULL,
    prezzo_annuale DECIMAL(10,2) NULL,
    target_cliente VARCHAR(255) NULL COMMENT 'PMI, Enterprise, etc',
    features JSON NULL,
    attivo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tipo (tipo_servizio),
    INDEX idx_categoria (categoria),
    INDEX idx_attivo (attivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella opportunità upselling
CREATE TABLE IF NOT EXISTS upsell_opportunities (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servizio_id INT NOT NULL,

    -- Scoring
    opportunity_score DECIMAL(5,4) NOT NULL COMMENT '0.0000 - 1.0000',
    opportunity_level ENUM('low', 'medium', 'high') NOT NULL,
    expected_value DECIMAL(10,2) NOT NULL COMMENT 'Expected revenue (score × price × 12m)',

    -- AI reasoning
    reasoning JSON NULL COMMENT 'Array di motivazioni',
    scores_breakdown JSON NULL COMMENT 'Score per componente',

    -- Pitch automation
    suggested_pitch TEXT NULL,
    best_time_to_contact ENUM('now', 'this_week', 'this_month', 'after_reengagement') NOT NULL DEFAULT 'this_week',

    -- Status tracking
    status ENUM('identified', 'contacted', 'demo_scheduled', 'proposal_sent', 'won', 'lost', 'on_hold') NOT NULL DEFAULT 'identified',
    assigned_to INT NULL COMMENT 'Admin/Sales rep',

    -- Outcome
    contacted_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    won_value DECIMAL(10,2) NULL,
    lost_reason TEXT NULL,

    -- Metadata
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE KEY idx_cliente_servizio (cliente_id, servizio_id),
    INDEX idx_opportunity_level (opportunity_level),
    INDEX idx_score (opportunity_score),
    INDEX idx_status (status),
    INDEX idx_expected_value (expected_value),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella servizi complementari (mapping)
CREATE TABLE IF NOT EXISTS servizi_complementari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servizio_base_id INT NOT NULL,
    servizio_complementare_id INT NOT NULL,
    relevance_score DECIMAL(3,2) NOT NULL DEFAULT 1.00 COMMENT '0.00 - 1.00',

    UNIQUE KEY idx_combo (servizio_base_id, servizio_complementare_id),
    FOREIGN KEY (servizio_base_id) REFERENCES servizi(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_complementare_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella bundles/pacchetti
CREATE TABLE IF NOT EXISTS servizi_bundles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    servizi_ids JSON NOT NULL COMMENT 'Array di servizio IDs',
    prezzo_bundle DECIMAL(10,2) NOT NULL,
    sconto_percentuale DECIMAL(5,2) NULL,
    attivo BOOLEAN NOT NULL DEFAULT TRUE,

    INDEX idx_attivo (attivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella conversioni upselling (tracking)
CREATE TABLE IF NOT EXISTS upsell_conversions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    opportunity_id BIGINT NOT NULL,
    cliente_id INT NOT NULL,
    servizio_id INT NOT NULL,

    -- Conversion details
    converted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    contract_value DECIMAL(10,2) NOT NULL,
    contract_duration_months INT NOT NULL DEFAULT 12,

    -- Attribution
    conversion_source ENUM('sales_call', 'email_campaign', 'automated_pitch', 'self_serve', 'other') NOT NULL,
    sales_rep_id INT NULL,

    -- Metadata
    notes TEXT NULL,

    INDEX idx_opportunity (opportunity_id),
    INDEX idx_converted_at (converted_at),
    FOREIGN KEY (opportunity_id) REFERENCES upsell_opportunities(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    FOREIGN KEY (sales_rep_id) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Viste per dashboard
-- ============================================================================

CREATE OR REPLACE VIEW v_upsell_dashboard AS
SELECT
    u.id as cliente_id,
    u.email,
    u.nome,
    u.cognome,
    u.azienda,

    -- Opportunità
    uo.id as opportunity_id,
    uo.servizio_id,
    s.nome as servizio_nome,
    s.prezzo_mensile,
    uo.opportunity_score,
    uo.opportunity_level,
    uo.expected_value,
    uo.status,
    uo.best_time_to_contact,

    -- Servizi attuali
    (SELECT COUNT(*) FROM servizi_attivi WHERE cliente_id = u.id AND stato = 'attivo') as servizi_attivi,

    -- Revenue
    (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata') as lifetime_value,

    -- Churn risk
    (SELECT churn_probability FROM churn_predictions WHERE cliente_id = u.id) as churn_risk,

    -- Timing
    uo.created_at as opportunita_identificata,
    uo.updated_at as ultima_modifica

FROM utenti u
JOIN upsell_opportunities uo ON u.id = uo.cliente_id
JOIN servizi s ON uo.servizio_id = s.id
WHERE u.ruolo = 'cliente'
AND u.attivo = TRUE
AND uo.status IN ('identified', 'contacted', 'demo_scheduled', 'proposal_sent');

-- Vista performance upselling
CREATE OR REPLACE VIEW v_upsell_performance AS
SELECT
    DATE(uo.created_at) as data,
    COUNT(*) as opportunities_identified,
    SUM(CASE WHEN uo.status = 'won' THEN 1 ELSE 0 END) as opportunities_won,
    SUM(CASE WHEN uo.status = 'won' THEN uo.won_value ELSE 0 END) as revenue_generated,
    AVG(uo.opportunity_score) as avg_score,
    AVG(CASE WHEN uo.status IN ('won', 'lost')
        THEN DATEDIFF(uo.closed_at, uo.created_at)
        ELSE NULL
    END) as avg_days_to_close
FROM upsell_opportunities uo
WHERE uo.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY DATE(uo.created_at)
ORDER BY data DESC;

-- ============================================================================
-- Stored Procedures
-- ============================================================================

DELIMITER $$

-- Calcola ROI campagne upselling
CREATE PROCEDURE IF NOT EXISTS calculate_upsell_roi(
    IN p_days INT,
    OUT p_roi DECIMAL(10,2)
)
BEGIN
    DECLARE total_revenue DECIMAL(10,2);
    DECLARE total_cost DECIMAL(10,2);

    -- Revenue da conversioni
    SELECT COALESCE(SUM(won_value), 0) INTO total_revenue
    FROM upsell_opportunities
    WHERE status = 'won'
    AND closed_at >= DATE_SUB(NOW(), INTERVAL p_days DAY);

    -- Costo stimato (es: 10€ per opportunity contacted)
    SELECT COUNT(*) * 10 INTO total_cost
    FROM upsell_opportunities
    WHERE contacted_at >= DATE_SUB(NOW(), INTERVAL p_days DAY);

    -- ROI %
    IF total_cost > 0 THEN
        SET p_roi = ((total_revenue - total_cost) / total_cost) * 100;
    ELSE
        SET p_roi = 0;
    END IF;
END$$

-- Top performing servizi per upselling
CREATE PROCEDURE IF NOT EXISTS top_upsell_services(IN p_limit INT)
BEGIN
    SELECT
        s.id,
        s.nome,
        s.tipo_servizio,
        s.prezzo_mensile,
        COUNT(uo.id) as total_opportunities,
        SUM(CASE WHEN uo.status = 'won' THEN 1 ELSE 0 END) as conversions,
        SUM(CASE WHEN uo.status = 'won' THEN 1 ELSE 0 END) / COUNT(uo.id) * 100 as conversion_rate,
        SUM(uo.won_value) as total_revenue
    FROM servizi s
    LEFT JOIN upsell_opportunities uo ON s.id = uo.servizio_id
    WHERE uo.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
    GROUP BY s.id
    ORDER BY conversion_rate DESC, total_revenue DESC
    LIMIT p_limit;
END$$

DELIMITER ;

-- ============================================================================
-- Trigger
-- ============================================================================

DELIMITER $$

-- Audit log quando opportunity convertita
CREATE TRIGGER IF NOT EXISTS upsell_opportunity_won
AFTER UPDATE ON upsell_opportunities
FOR EACH ROW
BEGIN
    IF NEW.status = 'won' AND OLD.status != 'won' THEN
        INSERT INTO audit_log (
            user_id,
            azione,
            entita,
            entita_id,
            descrizione,
            metadata,
            categoria,
            livello
        ) VALUES (
            @current_admin_id,
            'upsell_won',
            'upsell_opportunity',
            NEW.id,
            CONCAT('Upsell vinto: servizio ', NEW.servizio_id, ' per €', NEW.won_value),
            JSON_OBJECT(
                'cliente_id', NEW.cliente_id,
                'servizio_id', NEW.servizio_id,
                'value', NEW.won_value
            ),
            'sales',
            'info'
        );
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- Eventi automatici
-- ============================================================================

-- Pulizia opportunità vecchie/perdute
CREATE EVENT IF NOT EXISTS cleanup_old_opportunities
ON SCHEDULE EVERY 1 MONTH
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 03:00:00')
DO
    DELETE FROM upsell_opportunities
    WHERE status = 'lost'
    AND closed_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- ============================================================================
-- Dati di esempio servizi (opzionale)
-- ============================================================================

-- Inserisci servizi di esempio
INSERT INTO servizi (nome, descrizione, tipo_servizio, categoria, prezzo_mensile, prezzo_annuale, target_cliente) VALUES
('Document Intelligence Basic', 'OCR e estrazione dati base', 'document_intelligence', 'base', 49.00, 490.00, 'PMI'),
('Document Intelligence Premium', 'OCR avanzato + AI training', 'document_intelligence', 'premium', 149.00, 1490.00, 'PMI/Enterprise'),
('Document Intelligence Enterprise', 'Soluzione completa con SLA', 'document_intelligence', 'enterprise', 499.00, 4990.00, 'Enterprise'),
('AI Training Custom', 'Addestramento modelli personalizzati', 'ai_training', 'addon', 299.00, 2990.00, 'Enterprise'),
('Premium Support', 'Supporto prioritario 24/7', 'support', 'addon', 99.00, 990.00, 'Tutti'),
('API Access Enterprise', 'Accesso API illimitato', 'api_access', 'addon', 199.00, 1990.00, 'Developers')
ON DUPLICATE KEY UPDATE nome=nome;

-- Mapping servizi complementari
INSERT INTO servizi_complementari (servizio_base_id, servizio_complementare_id, relevance_score) VALUES
(1, 2, 0.90),  -- Basic → Premium
(1, 4, 0.70),  -- Basic → AI Training
(2, 3, 0.85),  -- Premium → Enterprise
(2, 5, 0.75),  -- Premium → Support
(3, 4, 0.95),  -- Enterprise → AI Training
(3, 5, 0.90),  -- Enterprise → Support
(3, 6, 0.80)   -- Enterprise → API Access
ON DUPLICATE KEY UPDATE relevance_score=relevance_score;

-- ============================================================================
-- Query di verifica
-- ============================================================================

SELECT 'Upselling System installato!' as status;

-- Mostra tabelle create
SHOW TABLES LIKE '%upsell%';
SHOW TABLES LIKE '%servizi%';

-- Test vista
SELECT COUNT(*) FROM v_upsell_dashboard;
