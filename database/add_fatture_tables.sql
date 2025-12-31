-- Tabella per le fatture
CREATE TABLE IF NOT EXISTS fatture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_fattura VARCHAR(50) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,

    -- Dettagli fattura
    data_emissione DATE NOT NULL,
    data_scadenza DATE NOT NULL,
    anno INT NOT NULL,
    mese INT NOT NULL,

    -- Importi
    imponibile DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    iva_percentuale DECIMAL(5,2) NOT NULL DEFAULT 22.00,
    iva_importo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    totale DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Stato fattura
    stato ENUM('bozza', 'emessa', 'inviata', 'pagata', 'scaduta', 'annullata') NOT NULL DEFAULT 'bozza',
    data_pagamento DATE NULL,
    metodo_pagamento VARCHAR(50) NULL,

    -- Note e riferimenti
    note TEXT NULL,
    riferimento_interno VARCHAR(100) NULL,

    -- File PDF
    file_pdf_path VARCHAR(255) NULL,

    -- Metadati
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,

    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_cliente (cliente_id),
    INDEX idx_data_emissione (data_emissione),
    INDEX idx_stato (stato),
    INDEX idx_anno_mese (anno, mese),
    INDEX idx_numero (numero_fattura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per le righe di fattura (dettaglio servizi)
CREATE TABLE IF NOT EXISTS fatture_righe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fattura_id INT NOT NULL,

    -- Dettagli riga
    servizio_id INT NULL,
    descrizione VARCHAR(500) NOT NULL,
    quantita DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    prezzo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Importi calcolati
    imponibile DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    iva_percentuale DECIMAL(5,2) NOT NULL DEFAULT 22.00,
    iva_importo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    totale DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Riferimenti
    utente_servizio_id INT NULL,

    -- Ordinamento
    ordine INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE SET NULL,
    FOREIGN KEY (utente_servizio_id) REFERENCES utenti_servizi(id) ON DELETE SET NULL,

    INDEX idx_fattura (fattura_id),
    INDEX idx_servizio (servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per lo storico pagamenti
CREATE TABLE IF NOT EXISTS fatture_pagamenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fattura_id INT NOT NULL,

    -- Dettagli pagamento
    importo DECIMAL(10,2) NOT NULL,
    data_pagamento DATE NOT NULL,
    metodo_pagamento VARCHAR(50) NOT NULL,

    -- Riferimenti esterni (es. transazione Stripe)
    riferimento_esterno VARCHAR(255) NULL,

    -- Note
    note TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,

    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_fattura (fattura_id),
    INDEX idx_data_pagamento (data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per configurazione numerazione fatture
CREATE TABLE IF NOT EXISTS fatture_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anno INT NOT NULL UNIQUE,
    ultimo_numero INT NOT NULL DEFAULT 0,
    prefisso VARCHAR(20) NOT NULL DEFAULT 'FT',
    formato VARCHAR(50) NOT NULL DEFAULT '{prefisso}-{anno}-{numero}',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci configurazione per anno corrente
INSERT INTO fatture_config (anno, ultimo_numero, prefisso, formato)
VALUES (YEAR(CURDATE()), 0, 'FT', '{prefisso}-{anno}-{numero}')
ON DUPLICATE KEY UPDATE anno = anno;

-- Vista per riepilogo fatture
CREATE OR REPLACE VIEW v_fatture_riepilogo AS
SELECT
    f.id,
    f.numero_fattura,
    f.data_emissione,
    f.data_scadenza,
    f.anno,
    f.mese,
    f.imponibile,
    f.iva_importo,
    f.totale,
    f.stato,
    f.data_pagamento,
    f.metodo_pagamento,
    u.id AS cliente_id,
    u.azienda,
    u.nome AS cliente_nome,
    u.cognome AS cliente_cognome,
    u.email AS cliente_email,
    COUNT(DISTINCT fr.id) AS num_righe,
    SUM(CASE WHEN fp.id IS NOT NULL THEN fp.importo ELSE 0 END) AS totale_pagato
FROM fatture f
JOIN utenti u ON f.cliente_id = u.id
LEFT JOIN fatture_righe fr ON f.id = fr.fattura_id
LEFT JOIN fatture_pagamenti fp ON f.id = fp.fattura_id
GROUP BY f.id, u.id;
