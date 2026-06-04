-- ===============================================
-- FINCH-AI - Registro consensi cookie (GDPR)
-- ===============================================
-- Tiene traccia di ogni scelta dell'utente sul banner cookie, come prova
-- del consenso richiesta dal GDPR (accountability). Una riga per scelta.
-- Eseguire una sola volta sul database (es. phpMyAdmin su Aruba).

CREATE TABLE IF NOT EXISTS consensi_cookie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consent_id VARCHAR(36) NOT NULL,            -- UUID generato lato client
    ip_address VARCHAR(45),                     -- IPv4/IPv6
    user_agent TEXT,
    consent_version VARCHAR(20) NOT NULL,       -- versione del banner al momento della scelta
    necessari TINYINT(1) NOT NULL DEFAULT 1,    -- sempre attivi
    statistici TINYINT(1) NOT NULL DEFAULT 0,   -- analytics (GA4)
    marketing TINYINT(1) NOT NULL DEFAULT 0,    -- Google Ads / remarketing
    azione VARCHAR(20) NOT NULL,                -- accept_all | reject_all | custom
    lingua VARCHAR(5) DEFAULT 'it',
    page_url VARCHAR(500),                      -- pagina in cui è avvenuta la scelta
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_consent (consent_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
