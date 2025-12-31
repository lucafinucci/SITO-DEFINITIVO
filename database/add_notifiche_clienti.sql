-- Estensione Sistema Notifiche per Clienti
-- Aggiunge supporto notifiche multi-canale (Email/SMS) per clienti

-- Aggiungi colonna canale_invio alla tabella notifiche
ALTER TABLE notifiche
ADD COLUMN canale ENUM('browser', 'email', 'sms', 'push') NOT NULL DEFAULT 'browser' COMMENT 'Canale di invio notifica',
ADD COLUMN stato_invio ENUM('pending', 'sent', 'failed', 'delivered') NOT NULL DEFAULT 'pending',
ADD COLUMN inviato_at TIMESTAMP NULL,
ADD COLUMN errore_invio TEXT NULL,
ADD COLUMN tentativi_invio INT NOT NULL DEFAULT 0;

-- Estendi preferenze notifiche per clienti
ALTER TABLE notifiche_preferenze
ADD COLUMN telefono_sms VARCHAR(20) NULL COMMENT 'Numero telefono per SMS',
ADD COLUMN sms_enabled BOOLEAN NOT NULL DEFAULT FALSE,
ADD COLUMN push_enabled BOOLEAN NOT NULL DEFAULT FALSE,

-- Preferenze canale per tipo notifica
ADD COLUMN servizio_attivato_canale ENUM('email', 'sms', 'entrambi', 'nessuno') NOT NULL DEFAULT 'email',
ADD COLUMN servizio_disattivato_canale ENUM('email', 'sms', 'entrambi', 'nessuno') NOT NULL DEFAULT 'email',
ADD COLUMN fattura_emessa_canale ENUM('email', 'sms', 'entrambi', 'nessuno') NOT NULL DEFAULT 'email',
ADD COLUMN fattura_scadenza_canale ENUM('email', 'sms', 'entrambi', 'nessuno') NOT NULL DEFAULT 'entrambi',
ADD COLUMN pagamento_confermato_canale ENUM('email', 'sms', 'entrambi', 'nessuno') NOT NULL DEFAULT 'email',
ADD COLUMN aggiornamento_sistema_canale ENUM('email', 'sms', 'entrambi', 'nessuno') NOT NULL DEFAULT 'email';

-- Aggiungi tipi notifica specifici per clienti
ALTER TABLE notifiche
MODIFY COLUMN tipo ENUM(
    'nuovo_cliente',
    'pagamento_ricevuto',
    'richiesta_addestramento',
    'fattura_scaduta',
    'servizio_attivato',
    'servizio_disattivato',
    'sollecito_inviato',
    'errore_sistema',
    'fattura_emessa',           -- NUOVO: Cliente riceve fattura
    'fattura_in_scadenza',      -- NUOVO: Promemoria scadenza
    'pagamento_confermato',     -- NUOVO: Pagamento ricevuto
    'aggiornamento_servizio',   -- NUOVO: Aggiornamenti servizi
    'manutenzione_sistema',     -- NUOVO: Manutenzione programmata
    'altro'
) NOT NULL DEFAULT 'altro';

-- Tabella configurazione SMS Gateway
CREATE TABLE IF NOT EXISTS sms_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('twilio', 'vonage', 'aws_sns', 'custom') NOT NULL DEFAULT 'twilio',
    api_key VARCHAR(255) NOT NULL,
    api_secret VARCHAR(255) NOT NULL,
    sender_number VARCHAR(20) NOT NULL COMMENT 'Numero mittente SMS',
    attivo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella log SMS
CREATE TABLE IF NOT EXISTS sms_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notifica_id INT NULL,
    destinatario_numero VARCHAR(20) NOT NULL,
    destinatario_nome VARCHAR(255) NULL,
    messaggio TEXT NOT NULL,

    -- Tracking invio
    stato ENUM('pending', 'sent', 'delivered', 'failed', 'undelivered') NOT NULL DEFAULT 'pending',
    provider VARCHAR(50) NULL,
    message_id VARCHAR(255) NULL COMMENT 'ID messaggio dal provider',
    costo DECIMAL(10, 4) NULL COMMENT 'Costo SMS in euro',

    -- Metadata
    cliente_id INT NULL,
    fattura_id INT NULL,
    errore TEXT NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (notifica_id) REFERENCES notifiche(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE CASCADE,

    INDEX idx_destinatario (destinatario_numero),
    INDEX idx_stato (stato),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Template SMS predefiniti
CREATE TABLE IF NOT EXISTS sms_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codice VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT NULL,
    messaggio TEXT NOT NULL COMMENT 'Max 160 caratteri per SMS singolo',

    -- Variabili supportate (JSON array)
    variabili_disponibili JSON NULL,

    -- Configurazione
    attivo BOOLEAN NOT NULL DEFAULT TRUE,
    tipo_notifica VARCHAR(100) NULL COMMENT 'Collegamento a tipo notifica',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_codice (codice),
    INDEX idx_tipo (tipo_notifica)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci template SMS predefiniti
INSERT INTO sms_templates (codice, nome, descrizione, messaggio, variabili_disponibili, tipo_notifica) VALUES
('servizio-attivato-sms', 'Servizio Attivato', 'SMS conferma attivazione servizio',
 'Finch-AI: Servizio {nome_servizio} attivato con successo! Accedi all''area clienti per i dettagli. Info: finch-ai.it',
 JSON_ARRAY('nome_servizio', 'data_attivazione'),
 'servizio_attivato'),

('fattura-emessa-sms', 'Fattura Emessa', 'SMS notifica nuova fattura',
 'Finch-AI: Nuova fattura {numero_fattura} emessa per €{importo}. Scadenza: {data_scadenza}. Paga online: {link_pagamento}',
 JSON_ARRAY('numero_fattura', 'importo', 'data_scadenza', 'link_pagamento'),
 'fattura_emessa'),

('fattura-scadenza-sms', 'Promemoria Scadenza', 'SMS promemoria scadenza fattura',
 'Finch-AI: Promemoria! Fattura {numero_fattura} (€{importo}) scade il {data_scadenza}. Paga ora: {link_pagamento}',
 JSON_ARRAY('numero_fattura', 'importo', 'data_scadenza', 'link_pagamento'),
 'fattura_in_scadenza'),

('pagamento-confermato-sms', 'Pagamento Confermato', 'SMS conferma pagamento ricevuto',
 'Finch-AI: Pagamento di €{importo} per fattura {numero_fattura} confermato. Grazie! Ricevuta disponibile in area clienti.',
 JSON_ARRAY('importo', 'numero_fattura', 'data_pagamento'),
 'pagamento_confermato'),

('servizio-disattivato-sms', 'Servizio Disattivato', 'SMS notifica disattivazione servizio',
 'Finch-AI: Servizio {nome_servizio} disattivato. Per assistenza contattaci: support@finch-ai.it',
 JSON_ARRAY('nome_servizio', 'data_disattivazione'),
 'servizio_disattivato'),

('manutenzione-sms', 'Manutenzione Programmata', 'SMS avviso manutenzione',
 'Finch-AI: Manutenzione programmata {data_manutenzione} dalle {ora_inizio} alle {ora_fine}. Servizi temporaneamente non disponibili.',
 JSON_ARRAY('data_manutenzione', 'ora_inizio', 'ora_fine'),
 'manutenzione_sistema');

-- Vista notifiche clienti (esclude notifiche solo admin)
CREATE OR REPLACE VIEW v_notifiche_clienti AS
SELECT
    n.id,
    n.utente_id,
    n.tipo,
    n.titolo,
    n.messaggio,
    n.icona,
    n.priorita,
    n.letta,
    n.canale,
    n.stato_invio,
    n.link_azione,
    n.label_azione,
    n.created_at,
    u.email,
    u.azienda,
    u.nome,
    u.cognome,
    np.telefono_sms,
    np.sms_enabled,
    np.email_enabled,
    CASE
        WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN
            CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' min fa')
        WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN
            CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' ore fa')
        ELSE
            CONCAT(TIMESTAMPDIFF(DAY, n.created_at, NOW()), ' giorni fa')
    END AS tempo_relativo
FROM notifiche n
JOIN utenti u ON n.utente_id = u.id
LEFT JOIN notifiche_preferenze np ON u.id = np.utente_id
WHERE u.ruolo = 'cliente'
  AND n.tipo NOT IN ('nuovo_cliente', 'pagamento_ricevuto', 'richiesta_addestramento', 'errore_sistema')
ORDER BY
    CASE n.priorita
        WHEN 'urgente' THEN 1
        WHEN 'alta' THEN 2
        WHEN 'normale' THEN 3
        WHEN 'bassa' THEN 4
    END,
    n.created_at DESC;

-- Vista statistiche SMS
CREATE OR REPLACE VIEW v_sms_statistiche AS
SELECT
    DATE(created_at) AS data,
    COUNT(*) AS totale_inviati,
    SUM(CASE WHEN stato = 'delivered' THEN 1 ELSE 0 END) AS consegnati,
    SUM(CASE WHEN stato = 'failed' THEN 1 ELSE 0 END) AS falliti,
    SUM(CASE WHEN stato = 'pending' THEN 1 ELSE 0 END) AS in_coda,
    ROUND(SUM(CASE WHEN stato = 'delivered' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS tasso_consegna,
    SUM(costo) AS costo_totale,
    COUNT(DISTINCT cliente_id) AS clienti_unici
FROM sms_log
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY data DESC;

-- Crea preferenze di default per clienti esistenti
INSERT INTO notifiche_preferenze (utente_id, sms_enabled, email_enabled)
SELECT id, FALSE, TRUE FROM utenti WHERE ruolo = 'cliente'
ON DUPLICATE KEY UPDATE utente_id = utente_id;

-- Indici aggiuntivi per performance
ALTER TABLE notifiche ADD INDEX idx_canale_stato (canale, stato_invio);
ALTER TABLE notifiche ADD INDEX idx_tipo_utente (tipo, utente_id);

-- Event per pulire log SMS vecchi (90 giorni)
DELIMITER //
CREATE EVENT IF NOT EXISTS pulisci_sms_log
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM sms_log
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
      AND stato IN ('delivered', 'failed');
END//
DELIMITER ;
