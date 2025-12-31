-- ===============================================
-- Tabelle per Preventivi (Admin)
-- ===============================================

CREATE TABLE IF NOT EXISTS preventivi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_azienda VARCHAR(150) NOT NULL,
    referente VARCHAR(150) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    stato ENUM('bozza', 'inviato', 'accettato') DEFAULT 'bozza',
    sconto_percentuale DECIMAL(5,2) DEFAULT 0.00,
    note TEXT,
    scadenza DATE DEFAULT NULL,
    subtotale DECIMAL(10,2) DEFAULT 0.00,
    totale DECIMAL(10,2) DEFAULT 0.00,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_stato (stato),
    INDEX idx_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS preventivi_voci (
    id INT AUTO_INCREMENT PRIMARY KEY,
    preventivo_id INT NOT NULL,
    descrizione VARCHAR(255) NOT NULL,
    quantita DECIMAL(10,2) DEFAULT 1.00,
    prezzo_unitario DECIMAL(10,2) DEFAULT 0.00,
    totale DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (preventivo_id) REFERENCES preventivi(id) ON DELETE CASCADE,
    INDEX idx_preventivo (preventivo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
