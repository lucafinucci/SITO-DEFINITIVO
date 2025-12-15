-- ===============================================
-- SETUP COMPLETO DATABASE FINCH-AI
-- Copia TUTTO questo codice e incollalo in phpMyAdmin > SQL
-- ===============================================

-- STEP 1: Crea database
CREATE DATABASE IF NOT EXISTS finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finch_ai_clienti;

-- STEP 2: Crea tabelle
CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    azienda VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    ruolo ENUM('admin', 'cliente', 'viewer') DEFAULT 'cliente',
    mfa_secret VARCHAR(32) DEFAULT NULL,
    mfa_enabled BOOLEAN DEFAULT FALSE,
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_azienda (azienda)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash),
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    codice VARCHAR(50) UNIQUE NOT NULL,
    prezzo_mensile DECIMAL(10, 2),
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codice (codice)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS utenti_servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    servizio_id INT NOT NULL,
    data_attivazione DATE NOT NULL,
    data_disattivazione DATE NULL,
    stato ENUM('attivo', 'sospeso', 'disattivato') DEFAULT 'attivo',
    note TEXT,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    INDEX idx_user_servizio (user_id, servizio_id),
    INDEX idx_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fatture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    numero_fattura VARCHAR(50) NOT NULL UNIQUE,
    data_emissione DATE NOT NULL,
    data_scadenza DATE NOT NULL,
    importo_netto DECIMAL(10, 2) NOT NULL,
    iva DECIMAL(10, 2) NOT NULL,
    importo_totale DECIMAL(10, 2) NOT NULL,
    stato ENUM('emessa', 'pagata', 'scaduta', 'annullata') DEFAULT 'emessa',
    file_path VARCHAR(500),
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_numero (numero_fattura),
    INDEX idx_user (user_id),
    INDEX idx_data (data_emissione),
    INDEX idx_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scadenze (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    descrizione TEXT NOT NULL,
    data_scadenza DATE NOT NULL,
    urgente BOOLEAN DEFAULT FALSE,
    completata BOOLEAN DEFAULT FALSE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_data (data_scadenza),
    INDEX idx_urgente (urgente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    email_tentativo VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    successo BOOLEAN DEFAULT FALSE,
    motivo_fallimento VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_ip (ip_address),
    INDEX idx_data (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 3: Inserisci dati demo
-- Password per tutti gli utenti: Demo123!
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, telefono, ruolo, mfa_enabled, attivo) VALUES
('admin@finch-ai.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mario', 'Rossi', 'Finch-AI Srl', '+39 02 1234567', 'admin', FALSE, TRUE),
('demo@finch-ai.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luigi', 'Verdi', 'Azienda Demo Srl', '+39 06 7654321', 'cliente', FALSE, TRUE),
('cliente@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paolo', 'Bianchi', 'Example Corp', '+39 011 9876543', 'cliente', FALSE, TRUE);

INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
('Document Intelligence', 'OCR e validazione documenti automatica con AI', 'DOC-INT', 1500.00, TRUE),
('Production Analytics', 'Dashboard KPI e monitoraggio real-time', 'PROD-ANA', 1200.00, TRUE),
('Financial Control', 'Integrazione ERP e forecast economico', 'FIN-CTR', 1800.00, TRUE),
('Supply Chain Optimizer', 'Ottimizzazione logistica e inventario', 'SUP-OPT', 2000.00, TRUE),
('Quality Assurance AI', 'Controllo qualità automatizzato', 'QA-AI', 1600.00, TRUE);

INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
(1, 1, '2024-01-01', 'attivo'),
(1, 2, '2024-01-01', 'attivo'),
(1, 3, '2024-01-15', 'attivo'),
(2, 1, '2024-01-01', 'attivo'),
(2, 2, '2024-01-01', 'attivo'),
(2, 3, '2024-02-15', 'attivo'),
(3, 1, '2024-03-01', 'attivo'),
(3, 4, '2024-03-15', 'attivo');

INSERT INTO fatture (user_id, numero_fattura, data_emissione, data_scadenza, importo_netto, iva, importo_totale, stato, file_path) VALUES
(1, 'FT-2024-001', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-001.pdf'),
(1, 'FT-2024-002', '2024-02-15', '2024-03-16', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-002.pdf'),
(2, 'FT-2024-004', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-004.pdf'),
(2, 'FT-2024-005', '2024-02-15', '2024-03-16', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-005.pdf');

INSERT INTO scadenze (user_id, tipo, descrizione, data_scadenza, urgente, completata) VALUES
(1, 'Rinnovo', 'Rinnovo annuale servizi', '2024-12-31', FALSE, FALSE),
(2, 'Contratto', 'Rinnovo contratto servizi', '2024-12-31', FALSE, FALSE);

-- ===============================================
-- SETUP COMPLETATO!
-- Ora puoi accedere all'area clienti con:
-- Email: demo@finch-ai.it
-- Password: Demo123!
-- ===============================================
