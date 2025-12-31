DROP TABLE IF EXISTS aziende_servizi;
DROP TABLE IF EXISTS aziende_prezzi_personalizzati;
DROP TABLE IF EXISTS aziende_quote;

CREATE TABLE aziende_servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    data_attivazione DATE NOT NULL,
    stato VARCHAR(20) DEFAULT 'attivo',
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE aziende_prezzi_personalizzati (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    prezzo_mensile DECIMAL(10,2) NULL,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    UNIQUE KEY (azienda_id, servizio_id)
) ENGINE=InnoDB;

CREATE TABLE aziende_quote (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    servizio_id INT NOT NULL,
    quota_documenti_mese INT NULL,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
    UNIQUE KEY (azienda_id, servizio_id)
) ENGINE=InnoDB;

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
