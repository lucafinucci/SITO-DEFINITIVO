-- =====================================================
-- Setup Test KPI Dashboard - Dati di Test
-- =====================================================
-- Questo script crea utenti e dati di test per testare
-- la dashboard KPI admin in locale
-- =====================================================

-- 1. CREA UTENTE ADMIN (se non esiste)
INSERT IGNORE INTO utenti (id, email, password_hash, nome, cognome, azienda, ruolo, created_at)
VALUES (
    999,
    'admin@finch-ai.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'Admin',
    'Sistema',
    'Finch-AI',
    'admin',
    NOW()
);

-- 2. VERIFICA CHE ESISTA IL SERVIZIO DOCUMENT INTELLIGENCE
SELECT @servizio_id := id FROM servizi WHERE codice = 'DOC-INT' LIMIT 1;

-- Se non esiste, crealo
INSERT IGNORE INTO servizi (codice, nome, descrizione, prezzo_mensile, costo_per_pagina, attivo)
VALUES (
    'DOC-INT',
    'Document Intelligence',
    'Lettura automatica documenti con AI',
    99.00,
    0.02,
    1
);

-- Riprendi l'ID
SELECT @servizio_id := id FROM servizi WHERE codice = 'DOC-INT' LIMIT 1;

-- 3. CREA CLIENTI DI TEST
-- Cliente 1: Mario Rossi - Azienda Test SRL
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo, created_at)
VALUES (
    'mario.rossi@aztest.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Mario',
    'Rossi',
    'Azienda Test SRL',
    'cliente',
    DATE_SUB(NOW(), INTERVAL 6 MONTH)
)
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @cliente1_id = LAST_INSERT_ID();

-- Attiva servizio per cliente 1
INSERT IGNORE INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
VALUES (@cliente1_id, @servizio_id, DATE_SUB(NOW(), INTERVAL 6 MONTH), 'attivo');

-- Dati utilizzo cliente 1
INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
VALUES
    (@cliente1_id, @servizio_id, DATE_FORMAT(NOW(), '%Y-%m'), 1250)
ON DUPLICATE KEY UPDATE
    documenti_usati = 1250;

-- Cliente 2: Luigi Verdi - Innovazione SPA
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo, created_at)
VALUES (
    'luigi.verdi@innovazione.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Luigi',
    'Verdi',
    'Innovazione SPA',
    'cliente',
    DATE_SUB(NOW(), INTERVAL 4 MONTH)
)
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @cliente2_id = LAST_INSERT_ID();

INSERT IGNORE INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
VALUES (@cliente2_id, @servizio_id, DATE_SUB(NOW(), INTERVAL 4 MONTH), 'attivo');

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
VALUES
    (@cliente2_id, @servizio_id, DATE_FORMAT(NOW(), '%Y-%m'), 850)
ON DUPLICATE KEY UPDATE
    documenti_usati = 850;

-- Cliente 3: Anna Bianchi - Digital Solutions
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo, created_at)
VALUES (
    'anna.bianchi@digitalsol.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Anna',
    'Bianchi',
    'Digital Solutions',
    'cliente',
    DATE_SUB(NOW(), INTERVAL 3 MONTH)
)
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @cliente3_id = LAST_INSERT_ID();

INSERT IGNORE INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
VALUES (@cliente3_id, @servizio_id, DATE_SUB(NOW(), INTERVAL 3 MONTH), 'attivo');

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
VALUES
    (@cliente3_id, @servizio_id, DATE_FORMAT(NOW(), '%Y-%m'), 2100)
ON DUPLICATE KEY UPDATE
    documenti_usati = 2100;

-- Cliente 4: Francesco Neri - Tech Consulting
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo, created_at)
VALUES (
    'francesco.neri@techcons.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Francesco',
    'Neri',
    'Tech Consulting',
    'cliente',
    DATE_SUB(NOW(), INTERVAL 8 MONTH)
)
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @cliente4_id = LAST_INSERT_ID();

INSERT IGNORE INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
VALUES (@cliente4_id, @servizio_id, DATE_SUB(NOW(), INTERVAL 8 MONTH), 'attivo');

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
VALUES
    (@cliente4_id, @servizio_id, DATE_FORMAT(NOW(), '%Y-%m'), 3200)
ON DUPLICATE KEY UPDATE
    documenti_usati = 3200;

-- Cliente 5: Giulia Russo - Smart Business
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo, created_at)
VALUES (
    'giulia.russo@smartbiz.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Giulia',
    'Russo',
    'Smart Business',
    'cliente',
    DATE_SUB(NOW(), INTERVAL 2 MONTH)
)
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @cliente5_id = LAST_INSERT_ID();

INSERT IGNORE INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
VALUES (@cliente5_id, @servizio_id, DATE_SUB(NOW(), INTERVAL 2 MONTH), 'attivo');

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
VALUES
    (@cliente5_id, @servizio_id, DATE_FORMAT(NOW(), '%Y-%m'), 620)
ON DUPLICATE KEY UPDATE
    documenti_usati = 620;

-- 4. AGGIUNGI DATI STORICI (ultimi 3 mesi)
-- Mese -1
INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
SELECT
    us.user_id,
    @servizio_id,
    DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m'),
    FLOOR(RAND() * 1500) + 500
FROM utenti_servizi us
WHERE us.servizio_id = @servizio_id AND us.stato = 'attivo'
ON DUPLICATE KEY UPDATE
    documenti_usati = VALUES(documenti_usati);

-- Mese -2
INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
SELECT
    us.user_id,
    @servizio_id,
    DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 MONTH), '%Y-%m'),
    FLOOR(RAND() * 1200) + 400
FROM utenti_servizi us
WHERE us.servizio_id = @servizio_id AND us.stato = 'attivo'
ON DUPLICATE KEY UPDATE
    documenti_usati = VALUES(documenti_usati);

-- Mese -3
INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
SELECT
    us.user_id,
    @servizio_id,
    DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3 MONTH), '%Y-%m'),
    FLOOR(RAND() * 1000) + 300
FROM utenti_servizi us
WHERE us.servizio_id = @servizio_id AND us.stato = 'attivo'
ON DUPLICATE KEY UPDATE
    documenti_usati = VALUES(documenti_usati);

-- =====================================================
-- RIEPILOGO DATI CREATI
-- =====================================================

SELECT '=== RIEPILOGO SETUP ===' AS '';

SELECT
    'Utente Admin Creato' AS tipo,
    email,
    'password: password' AS credenziali
FROM utenti
WHERE ruolo = 'admin' AND id = 999;

SELECT
    'Clienti con Document Intelligence' AS tipo,
    COUNT(*) AS totale
FROM utenti u
JOIN utenti_servizi us ON u.id = us.user_id
WHERE us.servizio_id = @servizio_id AND us.stato = 'attivo';

SELECT
    u.nome,
    u.cognome,
    u.azienda,
    u.email,
    squo.documenti_usati,
    squo.periodo
FROM utenti u
JOIN utenti_servizi us ON u.id = us.user_id
JOIN servizi_quota_uso squo ON u.id = squo.cliente_id
WHERE us.servizio_id = @servizio_id
    AND us.stato = 'attivo'
    AND squo.periodo = DATE_FORMAT(NOW(), '%Y-%m')
ORDER BY u.azienda;

SELECT '=== ISTRUZIONI ===' AS '';
SELECT 'Login Admin:' AS azione, 'http://localhost/area-clienti/login.php' AS url;
SELECT 'Email:' AS campo, 'admin@finch-ai.it' AS valore UNION ALL
SELECT 'Password:' AS campo, 'password' AS valore;
SELECT '' AS '', '' AS '';
SELECT 'Dashboard KPI:' AS azione, 'http://localhost/area-clienti/admin/kpi-clienti.php' AS url;

-- =====================================================
-- CLEANUP (opzionale - decommenta per rimuovere i dati di test)
-- =====================================================

/*
-- Rimuovi dati di test
DELETE FROM servizi_quota_uso WHERE cliente_id IN (
    SELECT id FROM utenti WHERE email LIKE '%@aztest.it'
        OR email LIKE '%@innovazione.it'
        OR email LIKE '%@digitalsol.it'
        OR email LIKE '%@techcons.it'
        OR email LIKE '%@smartbiz.it'
);

DELETE FROM utenti_servizi WHERE user_id IN (
    SELECT id FROM utenti WHERE email LIKE '%@aztest.it'
        OR email LIKE '%@innovazione.it'
        OR email LIKE '%@digitalsol.it'
        OR email LIKE '%@techcons.it'
        OR email LIKE '%@smartbiz.it'
);

DELETE FROM utenti WHERE email LIKE '%@aztest.it'
    OR email LIKE '%@innovazione.it'
    OR email LIKE '%@digitalsol.it'
    OR email LIKE '%@techcons.it'
    OR email LIKE '%@smartbiz.it';

DELETE FROM utenti WHERE id = 999; -- Admin di test
*/
