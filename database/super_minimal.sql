CREATE TABLE IF NOT EXISTS aziende_servizi (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT(11) NOT NULL,
    servizio_id INT(11) NOT NULL,
    data_attivazione DATE NOT NULL,
    stato VARCHAR(20) DEFAULT 'attivo',
    INDEX (azienda_id),
    INDEX (servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aziende_prezzi_personalizzati (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT(11) NOT NULL,
    servizio_id INT(11) NOT NULL,
    prezzo_mensile DECIMAL(10,2) NULL,
    UNIQUE KEY (azienda_id, servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aziende_quote (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT(11) NOT NULL,
    servizio_id INT(11) NOT NULL,
    quota_documenti_mese INT(11) NULL,
    UNIQUE KEY (azienda_id, servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO aziende_servizi (azienda_id, servizio_id, data_attivazione, stato)
SELECT u.azienda_id, us.servizio_id, MIN(us.data_attivazione), 'attivo'
FROM utenti_servizi us
INNER JOIN utenti u ON us.user_id = u.id
WHERE u.azienda_id IS NOT NULL AND us.stato = 'attivo'
GROUP BY u.azienda_id, us.servizio_id;

INSERT INTO aziende_prezzi_personalizzati (azienda_id, servizio_id, prezzo_mensile)
SELECT u.azienda_id, cpp.servizio_id, cpp.prezzo_mensile
FROM clienti_prezzi_personalizzati cpp
INNER JOIN utenti u ON cpp.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, cpp.servizio_id, cpp.prezzo_mensile
ON DUPLICATE KEY UPDATE prezzo_mensile = VALUES(prezzo_mensile);

INSERT INTO aziende_quote (azienda_id, servizio_id, quota_documenti_mese)
SELECT u.azienda_id, cq.servizio_id, MAX(cq.quota_documenti_mese)
FROM clienti_quote cq
INNER JOIN utenti u ON cq.cliente_id = u.id
WHERE u.azienda_id IS NOT NULL
GROUP BY u.azienda_id, cq.servizio_id
ON DUPLICATE KEY UPDATE quota_documenti_mese = VALUES(quota_documenti_mese);
