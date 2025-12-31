-- ============================================================================
-- A/B Testing System
-- Sistema completo per test scientifici di prezzi, offerte, comunicazioni
-- Include: traffic splitting, statistical significance, conversion tracking
-- ============================================================================

-- Tabella tests principali
CREATE TABLE IF NOT EXISTS ab_tests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Metadata
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,

    -- Type of test
    test_type ENUM(
        'pricing',           -- Test prezzi
        'offer',             -- Test offerte/promozioni
        'email',             -- Test email subject/content
        'cta',               -- Test call-to-action
        'landing_page',      -- Test landing page
        'feature',           -- Test nuove feature
        'onboarding',        -- Test flusso onboarding
        'other'
    ) NOT NULL,

    -- Success metric
    success_metric VARCHAR(100) NOT NULL COMMENT 'conversion_rate, revenue_per_visitor, etc',

    -- Targeting
    target_audience TEXT NULL COMMENT 'JSON rules for targeting',
    sample_size_target INT DEFAULT 1000,

    -- Statistical settings
    confidence_level INT DEFAULT 95 COMMENT '90, 95, or 99',

    -- Status
    status ENUM('draft', 'running', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',

    -- Timeline
    start_date DATE NULL,
    end_date DATE NULL,

    -- Results
    winner_variant_id BIGINT NULL,

    -- Audit
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_test_type (test_type),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date),

    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella varianti
CREATE TABLE IF NOT EXISTS ab_variants (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    test_id BIGINT NOT NULL,

    -- Variant info
    variant_name VARCHAR(100) NOT NULL,
    variant_key VARCHAR(50) NOT NULL COMMENT 'Unique key: control, variant_a, variant_b',
    is_control BOOLEAN NOT NULL DEFAULT FALSE,

    -- Traffic allocation
    traffic_allocation DECIMAL(5,2) NOT NULL DEFAULT 50.00 COMMENT 'Percentage 0-100',

    -- Configuration (JSON)
    config_json JSON NULL COMMENT 'Variant-specific config: price, copy, color, etc',

    -- Statistics (updated in real-time)
    total_views INT DEFAULT 0,
    total_clicks INT DEFAULT 0,
    total_conversions INT DEFAULT 0,
    total_purchases INT DEFAULT 0,
    total_revenue DECIMAL(12,2) DEFAULT 0.00,

    -- Calculated metrics
    conversion_rate DECIMAL(6,4) DEFAULT 0.0000 COMMENT 'conversions / views',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY idx_test_variant (test_id, variant_key),
    INDEX idx_test (test_id),

    FOREIGN KEY (test_id) REFERENCES ab_tests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella assegnazioni utenti → varianti
CREATE TABLE IF NOT EXISTS ab_assignments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    test_id BIGINT NOT NULL,
    variant_id BIGINT NOT NULL,
    user_id INT NOT NULL,

    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY idx_test_user (test_id, user_id),
    INDEX idx_variant (variant_id),
    INDEX idx_user (user_id),

    FOREIGN KEY (test_id) REFERENCES ab_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES ab_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella eventi tracking
CREATE TABLE IF NOT EXISTS ab_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    test_id BIGINT NOT NULL,
    variant_id BIGINT NOT NULL,
    user_id INT NOT NULL,

    -- Event data
    event_type ENUM('view', 'click', 'conversion', 'purchase', 'custom') NOT NULL,
    event_value DECIMAL(10,2) NULL COMMENT 'Monetary value if applicable',
    metadata JSON NULL,

    event_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_test (test_id),
    INDEX idx_variant (variant_id),
    INDEX idx_user (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_timestamp (event_timestamp),

    FOREIGN KEY (test_id) REFERENCES ab_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES ab_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Viste
-- ============================================================================

-- Vista risultati test in tempo reale
CREATE OR REPLACE VIEW v_ab_test_results AS
SELECT
    t.id as test_id,
    t.name as test_name,
    t.test_type,
    t.status,
    t.start_date,

    v.id as variant_id,
    v.variant_name,
    v.variant_key,
    v.is_control,
    v.traffic_allocation,

    -- Metrics
    v.total_views,
    v.total_clicks,
    v.total_conversions,
    v.total_purchases,
    v.total_revenue,
    v.conversion_rate,

    -- Calculated
    CASE WHEN v.total_views > 0
        THEN ROUND(v.total_revenue / v.total_views, 2)
        ELSE 0
    END as revenue_per_visitor,

    CASE WHEN v.total_conversions > 0
        THEN ROUND(v.total_revenue / v.total_conversions, 2)
        ELSE 0
    END as revenue_per_conversion,

    -- Sample size
    (SELECT COUNT(DISTINCT user_id)
     FROM ab_assignments
     WHERE variant_id = v.id
    ) as sample_size

FROM ab_tests t
JOIN ab_variants v ON t.id = v.test_id
WHERE t.status IN ('running', 'paused', 'completed');

-- Vista active tests
CREATE OR REPLACE VIEW v_ab_active_tests AS
SELECT
    t.*,
    COUNT(DISTINCT aa.user_id) as total_participants,
    SUM(v.total_conversions) as total_conversions,
    SUM(v.total_revenue) as total_revenue
FROM ab_tests t
LEFT JOIN ab_assignments aa ON t.id = aa.test_id
LEFT JOIN ab_variants v ON t.id = v.test_id
WHERE t.status = 'running'
GROUP BY t.id;

-- ============================================================================
-- Stored Procedures
-- ============================================================================

DELIMITER $$

-- Ottieni risultati test con statistical significance
CREATE PROCEDURE IF NOT EXISTS get_ab_test_results(
    IN p_test_id BIGINT
)
BEGIN
    -- Varianti con metriche
    SELECT
        v.*,
        (SELECT COUNT(DISTINCT user_id) FROM ab_assignments WHERE variant_id = v.id) as sample_size,
        v.config_json
    FROM ab_variants v
    WHERE v.test_id = p_test_id
    ORDER BY v.is_control DESC, v.id ASC;
END$$

-- Ricalcola stats variante da eventi
CREATE PROCEDURE IF NOT EXISTS recalculate_variant_stats(
    IN p_variant_id BIGINT
)
BEGIN
    UPDATE ab_variants SET
        total_views = (
            SELECT COUNT(*)
            FROM ab_events
            WHERE variant_id = p_variant_id
            AND event_type = 'view'
        ),
        total_clicks = (
            SELECT COUNT(*)
            FROM ab_events
            WHERE variant_id = p_variant_id
            AND event_type = 'click'
        ),
        total_conversions = (
            SELECT COUNT(*)
            FROM ab_events
            WHERE variant_id = p_variant_id
            AND event_type = 'conversion'
        ),
        total_purchases = (
            SELECT COUNT(*)
            FROM ab_events
            WHERE variant_id = p_variant_id
            AND event_type = 'purchase'
        ),
        total_revenue = (
            SELECT COALESCE(SUM(event_value), 0)
            FROM ab_events
            WHERE variant_id = p_variant_id
            AND event_type = 'purchase'
        )
    WHERE id = p_variant_id;

    -- Ricalcola conversion rate
    UPDATE ab_variants
    SET conversion_rate = CASE WHEN total_views > 0
        THEN total_conversions / total_views
        ELSE 0
    END
    WHERE id = p_variant_id;
END$$

DELIMITER ;

-- ============================================================================
-- Trigger
-- ============================================================================

DELIMITER $$

-- Auto-update conversion_rate quando cambiano stats
CREATE TRIGGER IF NOT EXISTS update_conversion_rate_on_stats
BEFORE UPDATE ON ab_variants
FOR EACH ROW
BEGIN
    IF NEW.total_views > 0 THEN
        SET NEW.conversion_rate = NEW.total_conversions / NEW.total_views;
    ELSE
        SET NEW.conversion_rate = 0;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- Query di verifica
-- ============================================================================

SELECT 'A/B Testing System installato!' as status;

SHOW TABLES LIKE 'ab_%';

-- ============================================================================
-- Dati di esempio (opzionale)
-- ============================================================================

-- Esempio test pricing
/*
INSERT INTO ab_tests (
    name,
    description,
    test_type,
    success_metric,
    confidence_level,
    status
) VALUES (
    'Premium Plan Pricing Test',
    'Test 3 pricing tiers per piano Premium',
    'pricing',
    'conversion_rate',
    95,
    'draft'
);

SET @test_id = LAST_INSERT_ID();

-- Varianti pricing
INSERT INTO ab_variants (test_id, variant_name, variant_key, is_control, traffic_allocation, config_json) VALUES
(@test_id, 'Control - €99/mese', 'control', TRUE, 33.33, JSON_OBJECT('price', 99.00, 'currency', 'EUR', 'billing', 'monthly')),
(@test_id, 'Variant A - €149/mese', 'variant_a', FALSE, 33.33, JSON_OBJECT('price', 149.00, 'currency', 'EUR', 'billing', 'monthly')),
(@test_id, 'Variant B - €119/mese', 'variant_b', FALSE, 33.34, JSON_OBJECT('price', 119.00, 'currency', 'EUR', 'billing', 'monthly'));
*/

-- Esempio test email subject
/*
INSERT INTO ab_tests (
    name,
    description,
    test_type,
    success_metric,
    confidence_level,
    status
) VALUES (
    'Welcome Email Subject Test',
    'Test 2 subject lines per email benvenuto',
    'email',
    'conversion_rate',
    95,
    'draft'
);

SET @test_id = LAST_INSERT_ID();

INSERT INTO ab_variants (test_id, variant_name, variant_key, is_control, traffic_allocation, config_json) VALUES
(@test_id, 'Control - Benvenuto su Finch AI', 'control', TRUE, 50.00, JSON_OBJECT('subject', 'Benvenuto su Finch AI', 'emoji', FALSE)),
(@test_id, 'Variant A - 🚀 Inizia con Finch AI', 'variant_a', FALSE, 50.00, JSON_OBJECT('subject', '🚀 Inizia con Finch AI', 'emoji', TRUE));
*/

-- ============================================================================
-- Note Implementazione
-- ============================================================================

/*
WORKFLOW A/B TESTING:

1. CREATE TEST:
   - Admin crea test con varianti
   - Definisce success metric (conversion_rate, revenue, etc)
   - Imposta traffic allocation (es: 50/50 o 33/33/33)
   - Definisce target audience (opzionale: solo segmento X)

2. START TEST:
   - Status → 'running'
   - Sistema inizia ad assegnare utenti a varianti

3. ASSIGNMENT:
   - Utente arriva su pagina/feature testata
   - Sistema assegna a variante con hash-based splitting
   - Deterministic: stesso utente = sempre stessa variante
   - Salva in ab_assignments

4. TRACKING:
   - Eventi tracciati: view, click, conversion, purchase
   - Real-time stats update in ab_variants
   - Salvati eventi in ab_events per audit

5. ANALYSIS:
   - Calcolo statistical significance (Z-test)
   - Confidence intervals (Wilson score)
   - P-value, lift, winner detection

6. COMPLETION:
   - Admin dichiara winner
   - Status → 'completed'
   - Deploy winning variant a tutti

TEST TYPES COMUNI:

1. PRICING TEST:
   config_json: {"price": 149.00, "currency": "EUR"}

2. EMAIL SUBJECT TEST:
   config_json: {"subject": "🚀 Offer!", "emoji": true}

3. CTA BUTTON TEST:
   config_json: {"text": "Get Started", "color": "#FF5733"}

4. LANDING PAGE TEST:
   config_json: {"headline": "...", "image": "variant_a.jpg"}

STATISTICAL FORMULAS:

Z-test per proporzioni:
  z = (p2 - p1) / SE
  SE = √(p_pool × (1 - p_pool) × (1/n1 + 1/n2))

Wilson confidence interval:
  CI = (p + z²/2n ± z√(p(1-p)/n + z²/4n²)) / (1 + z²/n)

Significance:
  |z| >= 1.960 → 95% confidence
  |z| >= 2.576 → 99% confidence

BEST PRACTICES:

- Minimum sample size: 100 per variante
- Test duration: 1-4 settimane (full business cycle)
- Evita "peeking" (controllo risultati prematuro)
- Test 1 variabile alla volta (isolamento)
- Documentare hypothesis prima del test
- Archivio risultati per learnings futuri

*/
