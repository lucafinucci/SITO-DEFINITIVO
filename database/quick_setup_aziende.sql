-- Setup rapido tabelle aziende

-- 1. Crea tabella aziende
CREATE TABLE IF NOT EXISTS aziende (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    webapp_url VARCHAR(255) NULL,
    cliente_dal DATE NULL,
    attivo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migra aziende
INSERT INTO aziende (nome, created_at)
SELECT DISTINCT azienda, MIN(created_at)
FROM utenti
WHERE azienda IS NOT NULL AND azienda != '' AND ruolo != 'admin'
GROUP BY azienda
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- 3. Aggiungi azienda_id e ruolo_aziendale agli utenti (se non esistono)
SET @exist_azienda_id = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'finch_ai_clienti' AND TABLE_NAME = 'utenti' AND COLUMN_NAME = 'azienda_id');
SET @exist_ruolo = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'finch_ai_clienti' AND TABLE_NAME = 'utenti' AND COLUMN_NAME = 'ruolo_aziendale');

SET @sql_azienda_id = IF(@exist_azienda_id = 0, 'ALTER TABLE utenti ADD COLUMN azienda_id INT NULL', 'SELECT 1');
PREPARE stmt FROM @sql_azienda_id;
EXECUTE stmt;

SET @sql_ruolo = IF(@exist_ruolo = 0, 'ALTER TABLE utenti ADD COLUMN ruolo_aziendale ENUM(\'admin\', \'utente\') DEFAULT \'utente\'', 'SELECT 1');
PREPARE stmt FROM @sql_ruolo;
EXECUTE stmt;

-- 4. Crea FK (se non esiste)
SET @exist_fk = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'finch_ai_clienti' AND TABLE_NAME = 'utenti' AND CONSTRAINT_NAME = 'fk_utenti_azienda');
SET @sql_fk = IF(@exist_fk = 0, 'ALTER TABLE utenti ADD CONSTRAINT fk_utenti_azienda FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;

-- 5. Popola azienda_id
UPDATE utenti u
INNER JOIN aziende a ON u.azienda = a.nome
SET u.azienda_id = a.id
WHERE u.ruolo != 'admin';

-- 6. Imposta admin aziendale
UPDATE utenti u
INNER JOIN (
    SELECT azienda_id, MIN(id) as first_user_id
    FROM utenti
    WHERE azienda_id IS NOT NULL AND ruolo = 'cliente'
    GROUP BY azienda_id
) first_users ON u.id = first_users.first_user_id
SET u.ruolo_aziendale = 'admin'
WHERE u.azienda_id IS NOT NULL;

-- 7. Crea aziende_servizi
CREATE TABLE IF NOT EXISTS aziende_servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    data_attivazione DATE NOT NULL,
    data_disattivazione DATE NULL,
    stato ENUM('attivo', 'disattivato', 'sospeso') DEFAULT 'attivo',
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    INDEX idx_azienda_servizi (azienda_id, servizio_id, stato),
    INDEX idx_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Migra servizi
INSERT INTO aziende_servizi (azienda_id, servizio_id, data_attivazione, stato, note)
SELECT
    u.azienda_id,
    us.servizio_id,
    MIN(us.data_attivazione) as data_attivazione,
    'attivo' as stato,
    GROUP_CONCAT(DISTINCT us.note SEPARATOR '; ') as note
FROM utenti_servizi us
INNER JOIN utenti u ON us.user_id = u.id
WHERE u.azienda_id IS NOT NULL AND us.stato = 'attivo'
GROUP BY u.azienda_id, us.servizio_id
ON DUPLICATE KEY UPDATE note = VALUES(note);

-- 9. Crea aziende_prezzi_personalizzati
CREATE TABLE IF NOT EXISTS aziende_prezzi_personalizzati (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    prezzo_mensile DECIMAL(10,2) NULL,
    costo_per_pagina DECIMAL(10,4) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_azienda_servizio_prezzo (azienda_id, servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Migra prezzi
INSERT INTO aziende_prezzi_personalizzati (azienda_id, servizio_id, prezzo_mensile, costo_per_pagina)
SELECT
    u.azienda_id,
    cpp.servizio_id,
    cpp.prezzo_mensile,
    cpp.costo_per_pagina
FROM clienti_prezzi_personalizzati cpp
INNER JOIN utenti u ON cpp.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, cpp.servizio_id, cpp.prezzo_mensile, cpp.costo_per_pagina
ON DUPLICATE KEY UPDATE prezzo_mensile = VALUES(prezzo_mensile);

-- 11. Crea aziende_quote
CREATE TABLE IF NOT EXISTS aziende_quote (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    quota_documenti_mese INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_azienda_servizio_quota (azienda_id, servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Migra quote
INSERT INTO aziende_quote (azienda_id, servizio_id, quota_documenti_mese)
SELECT
    u.azienda_id,
    cq.servizio_id,
    MAX(cq.quota_documenti_mese) as quota_documenti_mese
FROM clienti_quote cq
INNER JOIN utenti u ON cq.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, cq.servizio_id
ON DUPLICATE KEY UPDATE quota_documenti_mese = VALUES(quota_documenti_mese);
