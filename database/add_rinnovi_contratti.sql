-- ===============================================
-- Tabelle per rinnovi contratti (Admin)
-- ===============================================

CREATE TABLE IF NOT EXISTS clienti_contratti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    titolo VARCHAR(150) NOT NULL,
    data_inizio DATE DEFAULT NULL,
    data_scadenza DATE NOT NULL,
    valore_annuo DECIMAL(10,2) DEFAULT 0.00,
    stato ENUM('attivo', 'in_rinnovo', 'rinnovato', 'scaduto') DEFAULT 'attivo',
    note TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_scadenza (data_scadenza),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
