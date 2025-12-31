-- Sistema Notifiche Smart per Admin

-- Tabella notifiche
CREATE TABLE IF NOT EXISTS notifiche (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Destinatario
    utente_id INT NOT NULL,

    -- Tipo e contenuto
    tipo ENUM(
        'nuovo_cliente',
        'pagamento_ricevuto',
        'richiesta_addestramento',
        'fattura_scaduta',
        'servizio_attivato',
        'servizio_disattivato',
        'sollecito_inviato',
        'errore_sistema',
        'altro'
    ) NOT NULL DEFAULT 'altro',

    titolo VARCHAR(255) NOT NULL,
    messaggio TEXT NOT NULL,
    icona VARCHAR(50) NULL COMMENT 'Emoji o nome icona',

    -- Priorità e stato
    priorita ENUM('bassa', 'normale', 'alta', 'urgente') NOT NULL DEFAULT 'normale',
    letta BOOLEAN NOT NULL DEFAULT FALSE,
    archiviata BOOLEAN NOT NULL DEFAULT FALSE,

    -- Link azione
    link_azione VARCHAR(500) NULL,
    label_azione VARCHAR(100) NULL,

    -- Riferimenti
    cliente_id INT NULL,
    fattura_id INT NULL,
    richiesta_id INT NULL,

    -- Metadata
    dati_extra JSON NULL COMMENT 'Dati aggiuntivi contestuali',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    letta_at TIMESTAMP NULL,

    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE CASCADE,

    INDEX idx_utente (utente_id),
    INDEX idx_tipo (tipo),
    INDEX idx_letta (letta),
    INDEX idx_priorita (priorita),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella preferenze notifiche utente
CREATE TABLE IF NOT EXISTS notifiche_preferenze (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,

    -- Canali attivi
    email_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    browser_enabled BOOLEAN NOT NULL DEFAULT TRUE,

    -- Tipi notifiche
    nuovo_cliente_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    pagamento_ricevuto_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    richiesta_addestramento_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    fattura_scaduta_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    servizio_attivato_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    servizio_disattivato_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    sollecito_inviato_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    errore_sistema_enabled BOOLEAN NOT NULL DEFAULT TRUE,

    -- Raggruppamento
    raggruppa_email BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Invia digest invece di email singole',
    frequenza_digest ENUM('immediato', 'orario', 'giornaliero') NOT NULL DEFAULT 'orario',

    -- Orari silenzio
    silenzio_notturno BOOLEAN NOT NULL DEFAULT TRUE,
    silenzio_inizio TIME NULL DEFAULT '22:00:00',
    silenzio_fine TIME NULL DEFAULT '08:00:00',

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    UNIQUE KEY unique_utente (utente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vista notifiche non lette
CREATE OR REPLACE VIEW v_notifiche_non_lette AS
SELECT
    n.id,
    n.utente_id,
    n.tipo,
    n.titolo,
    n.messaggio,
    n.icona,
    n.priorita,
    n.link_azione,
    n.label_azione,
    n.created_at,
    TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) AS minuti_fa,
    CASE
        WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN
            CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' min fa')
        WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN
            CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' ore fa')
        ELSE
            CONCAT(TIMESTAMPDIFF(DAY, n.created_at, NOW()), ' giorni fa')
    END AS tempo_relativo
FROM notifiche n
WHERE n.letta = FALSE AND n.archiviata = FALSE
ORDER BY
    CASE n.priorita
        WHEN 'urgente' THEN 1
        WHEN 'alta' THEN 2
        WHEN 'normale' THEN 3
        WHEN 'bassa' THEN 4
    END,
    n.created_at DESC;

-- Vista statistiche notifiche
CREATE OR REPLACE VIEW v_notifiche_statistiche AS
SELECT
    utente_id,
    COUNT(*) AS totale_notifiche,
    SUM(CASE WHEN letta = FALSE THEN 1 ELSE 0 END) AS non_lette,
    SUM(CASE WHEN letta = TRUE THEN 1 ELSE 0 END) AS lette,
    SUM(CASE WHEN priorita = 'urgente' AND letta = FALSE THEN 1 ELSE 0 END) AS urgenti_non_lette,
    MAX(created_at) AS ultima_notifica
FROM notifiche
WHERE archiviata = FALSE
GROUP BY utente_id;

-- Inserisci preferenze di default per admin esistenti
INSERT INTO notifiche_preferenze (utente_id)
SELECT id FROM utenti WHERE ruolo = 'admin'
ON DUPLICATE KEY UPDATE utente_id = utente_id;

-- Funzione per pulire vecchie notifiche (30 giorni)
DELIMITER //
CREATE EVENT IF NOT EXISTS pulisci_notifiche_vecchie
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Archivia notifiche lette più vecchie di 30 giorni
    UPDATE notifiche
    SET archiviata = TRUE
    WHERE letta = TRUE
      AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND archiviata = FALSE;

    -- Elimina notifiche archiviate più vecchie di 90 giorni
    DELETE FROM notifiche
    WHERE archiviata = TRUE
      AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
END//
DELIMITER ;
