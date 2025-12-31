-- Tabella per configurazione gateway di pagamento
CREATE TABLE IF NOT EXISTS payment_gateways_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gateway ENUM('stripe', 'paypal') NOT NULL UNIQUE,

    -- Configurazione
    attivo BOOLEAN NOT NULL DEFAULT FALSE,
    modalita ENUM('test', 'live') NOT NULL DEFAULT 'test',

    -- Chiavi API
    api_key_public VARCHAR(255) NULL,
    api_key_secret VARCHAR(255) NULL,
    webhook_secret VARCHAR(255) NULL,

    -- Configurazione PayPal
    client_id VARCHAR(255) NULL,
    client_secret VARCHAR(255) NULL,

    -- Opzioni
    commissione_percentuale DECIMAL(5,2) DEFAULT 0.00,
    commissione_fissa DECIMAL(10,2) DEFAULT 0.00,

    -- Metadati
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per transazioni pagamento
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fattura_id INT NOT NULL,

    -- Gateway utilizzato
    gateway ENUM('stripe', 'paypal', 'bonifico', 'altro') NOT NULL,

    -- ID transazione gateway
    transaction_id VARCHAR(255) NULL,
    payment_intent_id VARCHAR(255) NULL,

    -- Importi
    importo DECIMAL(10,2) NOT NULL,
    commissione DECIMAL(10,2) DEFAULT 0.00,
    importo_netto DECIMAL(10,2) NOT NULL,

    -- Stato
    stato ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending',

    -- Dettagli
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    metodo_pagamento VARCHAR(50) NULL, -- card, bank_transfer, paypal, etc.

    -- Dati carta (ultimi 4 cifre)
    card_last4 VARCHAR(4) NULL,
    card_brand VARCHAR(20) NULL,

    -- Messaggi
    messaggio_errore TEXT NULL,

    -- Metadata JSON
    metadata JSON NULL,

    -- Date
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,

    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE CASCADE,

    INDEX idx_fattura (fattura_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_payment_intent (payment_intent_id),
    INDEX idx_stato (stato),
    INDEX idx_gateway (gateway)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per log webhook
CREATE TABLE IF NOT EXISTS payment_webhooks_log (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Gateway
    gateway ENUM('stripe', 'paypal') NOT NULL,

    -- Evento
    event_type VARCHAR(100) NOT NULL,
    event_id VARCHAR(255) NULL,

    -- Payload
    payload JSON NULL,

    -- Stato elaborazione
    processed BOOLEAN NOT NULL DEFAULT FALSE,
    processed_at TIMESTAMP NULL,

    -- Errori
    error_message TEXT NULL,

    -- Metadati
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_gateway (gateway),
    INDEX idx_event_type (event_type),
    INDEX idx_event_id (event_id),
    INDEX idx_processed (processed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci configurazioni di default
INSERT INTO payment_gateways_config (gateway, attivo, modalita) VALUES
('stripe', FALSE, 'test'),
('paypal', FALSE, 'test')
ON DUPLICATE KEY UPDATE gateway = gateway;

-- Vista per transazioni complete
CREATE OR REPLACE VIEW v_payment_transactions AS
SELECT
    t.id,
    t.fattura_id,
    f.numero_fattura,
    f.cliente_id,
    u.azienda,
    u.email AS cliente_email,
    t.gateway,
    t.transaction_id,
    t.importo,
    t.commissione,
    t.importo_netto,
    t.stato,
    t.currency,
    t.metodo_pagamento,
    t.card_last4,
    t.card_brand,
    t.created_at,
    t.completed_at
FROM payment_transactions t
JOIN fatture f ON t.fattura_id = f.id
JOIN utenti u ON f.cliente_id = u.id;
