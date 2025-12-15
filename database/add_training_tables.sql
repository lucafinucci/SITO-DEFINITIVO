-- ===============================================
-- Tabelle per Sistema Upload Addestramento AI
-- ===============================================

-- Tabella richieste di addestramento
CREATE TABLE IF NOT EXISTS richieste_addestramento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tipo_modello VARCHAR(100) NOT NULL COMMENT 'Tipo di documento (Fatture, DDT, Contratti, etc)',
    descrizione TEXT NOT NULL COMMENT 'Descrizione dettagliata del modello richiesto',
    num_documenti_stimati INT DEFAULT 0 COMMENT 'Numero documenti che il cliente caricherà',
    stato ENUM('in_attesa', 'in_lavorazione', 'completato', 'annullato') DEFAULT 'in_attesa',
    note_admin TEXT COMMENT 'Note interne per il team',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_user_stato (user_id, stato),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella file caricati per addestramento
CREATE TABLE IF NOT EXISTS richieste_addestramento_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    richiesta_id INT NOT NULL,
    filename_originale VARCHAR(255) NOT NULL,
    filename_storage VARCHAR(255) NOT NULL COMMENT 'Nome file salvato sul server',
    file_path VARCHAR(500) NOT NULL COMMENT 'Path completo file',
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL COMMENT 'Dimensione in bytes',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (richiesta_id) REFERENCES richieste_addestramento(id) ON DELETE CASCADE,
    INDEX idx_richiesta (richiesta_id),
    INDEX idx_uploaded (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella modelli addestrati (risultati)
CREATE TABLE IF NOT EXISTS modelli_addestrati (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    richiesta_id INT DEFAULT NULL COMMENT 'Richiesta originale che ha generato questo modello',
    nome_modello VARCHAR(150) NOT NULL,
    tipo_modello VARCHAR(100) NOT NULL,
    descrizione TEXT,
    accuratezza DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Accuratezza % del modello (es: 98.50)',
    num_documenti_addestramento INT DEFAULT 0,
    modello_file_path VARCHAR(500) COMMENT 'Path al file .pkl o .h5 del modello',
    attivo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (richiesta_id) REFERENCES richieste_addestramento(id) ON DELETE SET NULL,
    INDEX idx_user_attivo (user_id, attivo),
    INDEX idx_tipo (tipo_modello)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dati demo: Inserisci un modello addestrato per utente demo
INSERT INTO modelli_addestrati (user_id, nome_modello, tipo_modello, descrizione, accuratezza, num_documenti_addestramento, attivo) VALUES
(2, 'Fatture Elettroniche v2.1', 'Fatture Elettroniche', 'Modello ottimizzato per fatture elettroniche XML e PDF', 98.5, 4521, TRUE),
(2, 'DDT & Bolle di Trasporto', 'DDT & Bolle', 'Riconoscimento automatico DDT con estrazione dati logistici', 96.2, 2756, TRUE),
(2, 'Contratti Commerciali', 'Contratti', 'Analisi contratti commerciali con estrazione clausole chiave', 97.8, 1834, TRUE);

-- Inserisci modello anche per admin
INSERT INTO modelli_addestrati (user_id, nome_modello, tipo_modello, descrizione, accuratezza, num_documenti_addestramento, attivo) VALUES
(1, 'Fatture Fornitori', 'Fatture', 'Modello per fatture fornitori con validazione automatica', 99.1, 5234, TRUE);
