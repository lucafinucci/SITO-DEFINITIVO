-- ===============================================
-- FINCH-AI Area Clienti - Database Schema
-- ===============================================

-- Tabella Utenti
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

-- Tabella Sessioni (per tracking accessi)
CREATE TABLE IF NOT EXISTS sessioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash),
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Servizi
CREATE TABLE IF NOT EXISTS servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    codice VARCHAR(50) UNIQUE NOT NULL,
    prezzo_mensile DECIMAL(10, 2),
    costo_per_pagina DECIMAL(10, 4) NOT NULL DEFAULT 0.0000,
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codice (codice)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Servizi Attivi per Cliente
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

-- Tabella Fatture
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

-- Tabella Scadenze
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

-- Tabella Logs Accessi
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
