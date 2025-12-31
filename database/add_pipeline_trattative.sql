-- ===============================================
-- Tabella pipeline vendite (trattative commerciali)
-- ===============================================

CREATE TABLE IF NOT EXISTS pipeline_trattative (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_azienda VARCHAR(150) NOT NULL,
    referente VARCHAR(150) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    valore_previsto DECIMAL(10,2) DEFAULT 0.00,
    stato ENUM('proposta', 'negoziazione', 'vinto', 'perso') DEFAULT 'proposta',
    note TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_stato (stato),
    INDEX idx_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
