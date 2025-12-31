-- ============================================================================
-- MIGRAZIONE: Sistema Multi-Utente per Azienda
-- ============================================================================
-- Questo script crea una tabella aziende separata e migra i dati esistenti
-- per supportare più utenti per la stessa azienda con servizi condivisi.
-- ============================================================================

-- 1. Crea tabella aziende
CREATE TABLE IF NOT EXISTS aziende (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    partita_iva VARCHAR(50) NULL,
    codice_fiscale VARCHAR(50) NULL,
    indirizzo VARCHAR(255) NULL,
    citta VARCHAR(100) NULL,
    cap VARCHAR(10) NULL,
    provincia VARCHAR(2) NULL,
    nazione VARCHAR(100) DEFAULT 'Italia',
    telefono VARCHAR(20) NULL,
    pec VARCHAR(255) NULL,
    codice_sdi VARCHAR(20) NULL,
    webapp_url VARCHAR(255) NULL COMMENT 'URL WebApp condiviso per tutta lazienda',
    cliente_dal DATE NULL COMMENT 'Data di inizio rapporto commerciale',
    note TEXT NULL,
    attivo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_attivo (attivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Anagrafica aziende clienti';

-- 2. Migra le aziende esistenti dalla tabella utenti
INSERT INTO aziende (nome, webapp_url, cliente_dal, created_at)
SELECT DISTINCT
    u.azienda,
    (SELECT webapp_url FROM utenti WHERE azienda = u.azienda AND webapp_url IS NOT NULL LIMIT 1),
    (SELECT MIN(cliente_dal) FROM utenti WHERE azienda = u.azienda),
    (SELECT MIN(created_at) FROM utenti WHERE azienda = u.azienda)
FROM utenti u
WHERE u.azienda IS NOT NULL
  AND u.azienda != ''
  AND u.ruolo != 'admin'
ON DUPLICATE KEY UPDATE aziende.nome = aziende.nome;

-- 3. Aggiungi colonna azienda_id alla tabella utenti
ALTER TABLE utenti
    ADD COLUMN azienda_id INT NULL AFTER azienda,
    ADD INDEX idx_azienda_id (azienda_id),
    ADD CONSTRAINT fk_utenti_azienda
        FOREIGN KEY (azienda_id) REFERENCES aziende(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

-- 4. Popola azienda_id negli utenti esistenti
UPDATE utenti u
INNER JOIN aziende a ON u.azienda = a.nome
SET u.azienda_id = a.id
WHERE u.ruolo != 'admin';

-- 5. Crea nuova tabella aziende_servizi (servizi a livello aziendale)
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
    UNIQUE KEY unique_azienda_servizio (azienda_id, servizio_id, stato),
    INDEX idx_azienda_servizi (azienda_id, stato),
    INDEX idx_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Servizi attivi per azienda';

-- 6. Migra i servizi da utenti_servizi a aziende_servizi
-- (raggruppa per azienda, prendendo la data di attivazione più vecchia)
INSERT INTO aziende_servizi (azienda_id, servizio_id, data_attivazione, data_disattivazione, stato, note)
SELECT
    u.azienda_id,
    us.servizio_id,
    MIN(us.data_attivazione) as data_attivazione,
    us.data_disattivazione,
    us.stato,
    GROUP_CONCAT(DISTINCT us.note SEPARATOR '; ') as note
FROM utenti_servizi us
INNER JOIN utenti u ON us.user_id = u.id
WHERE u.azienda_id IS NOT NULL
  AND us.stato = 'attivo'
GROUP BY u.azienda_id, us.servizio_id, us.stato, us.data_disattivazione
ON DUPLICATE KEY UPDATE
    data_attivazione = VALUES(data_attivazione),
    note = VALUES(note);

-- 7. Crea nuova tabella aziende_prezzi_personalizzati
CREATE TABLE IF NOT EXISTS aziende_prezzi_personalizzati (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    prezzo_mensile DECIMAL(10,2) NULL COMMENT 'Prezzo mensile personalizzato per questa azienda',
    costo_per_pagina DECIMAL(10,4) NULL COMMENT 'Costo per pagina personalizzato',
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_azienda_servizio_prezzo (azienda_id, servizio_id),
    INDEX idx_azienda_prezzi (azienda_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prezzi personalizzati per azienda';

-- 8. Migra i prezzi da clienti_prezzi_personalizzati a aziende_prezzi_personalizzati
INSERT INTO aziende_prezzi_personalizzati (azienda_id, servizio_id, prezzo_mensile, costo_per_pagina, created_at)
SELECT
    u.azienda_id,
    cpp.servizio_id,
    cpp.prezzo_mensile,
    cpp.costo_per_pagina,
    MIN(cpp.created_at) as created_at
FROM clienti_prezzi_personalizzati cpp
INNER JOIN utenti u ON cpp.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, cpp.servizio_id, cpp.prezzo_mensile, cpp.costo_per_pagina
ON DUPLICATE KEY UPDATE
    prezzo_mensile = VALUES(prezzo_mensile),
    costo_per_pagina = VALUES(costo_per_pagina);

-- 9. Crea nuova tabella aziende_quote
CREATE TABLE IF NOT EXISTS aziende_quote (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    quota_documenti_mese INT NULL COMMENT 'Quota documenti mensile per azienda',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_azienda_servizio_quota (azienda_id, servizio_id),
    INDEX idx_azienda_quote (azienda_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quote personalizzate per azienda';

-- 10. Migra le quote da clienti_quote a aziende_quote
INSERT INTO aziende_quote (azienda_id, servizio_id, quota_documenti_mese, created_at)
SELECT
    u.azienda_id,
    cq.servizio_id,
    MAX(cq.quota_documenti_mese) as quota_documenti_mese,
    MIN(cq.created_at) as created_at
FROM clienti_quote cq
INNER JOIN utenti u ON cq.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, cq.servizio_id
ON DUPLICATE KEY UPDATE
    quota_documenti_mese = VALUES(quota_documenti_mese);

-- 11. Aggiungi campo ruolo_aziendale per distinguere admin aziendali da utenti normali
ALTER TABLE utenti
    ADD COLUMN ruolo_aziendale ENUM('admin', 'utente') DEFAULT 'utente' AFTER ruolo;

-- 12. Imposta il primo utente di ogni azienda come admin aziendale
UPDATE utenti u
INNER JOIN (
    SELECT azienda_id, MIN(id) as first_user_id
    FROM utenti
    WHERE azienda_id IS NOT NULL AND ruolo = 'cliente'
    GROUP BY azienda_id
) first_users ON u.id = first_users.first_user_id
SET u.ruolo_aziendale = 'admin'
WHERE u.azienda_id IS NOT NULL;

-- 13. Crea tabella per tracking uso servizi a livello aziendale
CREATE TABLE IF NOT EXISTS aziende_servizi_uso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    user_id INT NULL COMMENT 'Utente che ha effettuato loperazione (opzionale)',
    periodo VARCHAR(7) NOT NULL COMMENT 'Periodo YYYY-MM',
    documenti_processati INT DEFAULT 0,
    pagine_processate INT DEFAULT 0,
    costo_calcolato DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL,
    UNIQUE KEY unique_azienda_servizio_periodo (azienda_id, servizio_id, periodo),
    INDEX idx_azienda_periodo (azienda_id, periodo),
    INDEX idx_user_periodo (user_id, periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracking uso servizi per azienda e periodo';

-- 14. Migra i dati da servizi_quota_uso a aziende_servizi_uso (aggregando per azienda)
INSERT INTO aziende_servizi_uso (azienda_id, servizio_id, user_id, periodo, documenti_processati, updated_at)
SELECT
    u.azienda_id,
    squ.servizio_id,
    squ.cliente_id,
    squ.periodo,
    SUM(squ.documenti_usati) as documenti_processati,
    MAX(squ.updated_at) as updated_at
FROM servizi_quota_uso squ
INNER JOIN utenti u ON squ.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, squ.servizio_id, squ.cliente_id, squ.periodo
ON DUPLICATE KEY UPDATE
    documenti_processati = documenti_processati + VALUES(documenti_processati),
    updated_at = VALUES(updated_at);

-- 15. Vista per compatibilità con query esistenti
CREATE OR REPLACE VIEW v_utenti_servizi_legacy AS
SELECT
    u.id as user_id,
    ase.servizio_id,
    ase.data_attivazione,
    ase.data_disattivazione,
    ase.stato,
    ase.note,
    s.nome as servizio_nome,
    s.codice as servizio_codice,
    s.prezzo_mensile,
    s.costo_per_pagina,
    app.prezzo_mensile AS prezzo_personalizzato,
    app.costo_per_pagina AS costo_per_pagina_personalizzato,
    COALESCE(app.prezzo_mensile, s.prezzo_mensile) AS prezzo_finale,
    COALESCE(app.costo_per_pagina, s.costo_per_pagina) AS costo_per_pagina_finale
FROM utenti u
INNER JOIN aziende_servizi ase ON u.azienda_id = ase.azienda_id
INNER JOIN servizi s ON ase.servizio_id = s.id
LEFT JOIN aziende_prezzi_personalizzati app ON app.azienda_id = u.azienda_id AND app.servizio_id = s.id
WHERE u.azienda_id IS NOT NULL AND ase.stato = 'attivo';

-- ============================================================================
-- RIEPILOGO MODIFICHE
-- ============================================================================
-- ✓ Creata tabella aziende con dati anagrafici completi
-- ✓ Migrati dati aziende esistenti da utenti.azienda
-- ✓ Aggiunta FK azienda_id alla tabella utenti
-- ✓ Creata tabella aziende_servizi (servizi a livello aziendale)
-- ✓ Migrati servizi da utenti_servizi a aziende_servizi
-- ✓ Creata tabella aziende_prezzi_personalizzati
-- ✓ Migrati prezzi personalizzati a livello aziendale
-- ✓ Creata tabella aziende_quote
-- ✓ Migrate quote a livello aziendale
-- ✓ Aggiunto campo ruolo_aziendale agli utenti
-- ✓ Impostato primo utente come admin aziendale
-- ✓ Creata tabella aziende_servizi_uso per tracking
-- ✓ Migrati dati uso servizi a livello aziendale
-- ✓ Creata vista di compatibilità v_utenti_servizi_legacy
-- ============================================================================

-- NOTE IMPORTANTI:
-- 1. Le vecchie tabelle (utenti_servizi, clienti_prezzi_personalizzati, clienti_quote)
--    NON vengono eliminate per sicurezza. Puoi eliminarle manualmente dopo aver verificato
--    che tutto funzioni correttamente.
-- 2. Il campo utenti.azienda rimane per compatibilità ma non viene più utilizzato.
-- 3. Tutti i servizi, prezzi e quote sono ora a livello aziendale e condivisi tra utenti.
-- ============================================================================
