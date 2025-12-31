-- ===============================================
-- FINCH-AI Area Clienti - Dati Demo
-- ===============================================

-- NOTA: Le password sono hashate con password_hash() di PHP
-- Password in chiaro per testing:
-- admin@finch-ai.it -> Admin123!
-- demo@finch-ai.it -> Demo123!
-- cliente@example.com -> Cliente123!

-- Inserimento Utenti Demo
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, telefono, ruolo, mfa_enabled, attivo) VALUES
('admin@finch-ai.it', '$2y$10$SdqzQ57mArz7PS0vrXXO1uzyQXLNfkAYmKeaN1UNcoA5vwzqCuO2m', 'Mario', 'Rossi', 'Finch-AI Srl', '+39 02 1234567', 'admin', FALSE, TRUE),
('demo@finch-ai.it', '$2y$10$sy1aBPONuwKREhutPj7BFeX4jMCdRpMOAYHrFTjEn3fI3bERIpJ4q', 'Luigi', 'Verdi', 'Azienda Demo Srl', '+39 06 7654321', 'cliente', FALSE, TRUE),
('cliente@example.com', '$2y$10$8LvP.M5bfHJ/2KYyNgTyZ.RrFuvV/2JQwcQ9yeNLmzHF7tasU2ulW', 'Paolo', 'Bianchi', 'Example Corp', '+39 011 9876543', 'cliente', FALSE, TRUE);

-- Inserimento Servizi
INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
('Document Intelligence', 'OCR e validazione documenti automatica con AI', 'DOC-INT', 1500.00, TRUE),
('Production Analytics', 'Dashboard KPI e monitoraggio real-time', 'PROD-ANA', 1200.00, TRUE),
('Financial Control', 'Integrazione ERP e forecast economico', 'FIN-CTR', 1800.00, TRUE),
('Supply Chain Optimizer', 'Ottimizzazione logistica e inventario', 'SUP-OPT', 2000.00, TRUE),
('Quality Assurance AI', 'Controllo qualità automatizzato', 'QA-AI', 1600.00, TRUE);

-- Assegnazione Servizi agli Utenti
-- Admin ha tutti i servizi
INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
(1, 1, '2024-01-01', 'attivo'),
(1, 2, '2024-01-01', 'attivo'),
(1, 3, '2024-01-15', 'attivo');

-- Cliente Demo ha 3 servizi
INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
(2, 1, '2024-01-01', 'attivo'),
(2, 2, '2024-01-01', 'attivo'),
(2, 3, '2024-02-15', 'attivo');

-- Cliente Example ha 2 servizi
INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
(3, 1, '2024-03-01', 'attivo'),
(3, 4, '2024-03-15', 'attivo');

-- Inserimento Fatture
INSERT INTO fatture (user_id, numero_fattura, data_emissione, data_scadenza, importo_netto, iva, importo_totale, stato, file_path) VALUES
-- Fatture Admin
(1, 'FT-2024-001', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-001.pdf'),
(1, 'FT-2024-002', '2024-02-15', '2024-03-16', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-002.pdf'),
(1, 'FT-2024-003', '2024-03-15', '2024-04-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-003.pdf'),
(1, 'FT-2024-010', '2024-10-15', '2024-11-14', 4100.00, 902.00, 5002.00, 'emessa', '/fatture/2024/FT-2024-010.pdf'),

-- Fatture Demo
(2, 'FT-2024-004', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-004.pdf'),
(2, 'FT-2024-005', '2024-02-15', '2024-03-16', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-005.pdf'),
(2, 'FT-2024-006', '2024-03-15', '2024-04-14', 4500.00, 990.00, 5490.00, 'pagata', '/fatture/2024/FT-2024-006.pdf'),
(2, 'FT-2024-011', '2024-10-15', '2024-11-14', 4500.00, 990.00, 5490.00, 'emessa', '/fatture/2024/FT-2024-011.pdf'),

-- Fatture Cliente Example
(3, 'FT-2024-007', '2024-03-15', '2024-04-14', 3500.00, 770.00, 4270.00, 'pagata', '/fatture/2024/FT-2024-007.pdf'),
(3, 'FT-2024-008', '2024-04-15', '2024-05-15', 3500.00, 770.00, 4270.00, 'pagata', '/fatture/2024/FT-2024-008.pdf'),
(3, 'FT-2024-012', '2024-10-15', '2024-11-14', 3500.00, 770.00, 4270.00, 'emessa', '/fatture/2024/FT-2024-012.pdf');

-- Inserimento Scadenze
INSERT INTO scadenze (user_id, tipo, descrizione, data_scadenza, urgente, completata) VALUES
-- Admin
(1, 'Pagamento', 'Fattura FT-2024-010', '2024-11-14', TRUE, FALSE),
(1, 'Rinnovo', 'Rinnovo annuale servizi', '2024-12-31', FALSE, FALSE),
(1, 'Documentazione', 'Invio documentazione fiscale', '2024-12-15', FALSE, FALSE),

-- Demo
(2, 'Pagamento', 'Fattura FT-2024-011', '2024-11-14', TRUE, FALSE),
(2, 'Contratto', 'Rinnovo contratto servizi', '2024-12-31', FALSE, FALSE),
(2, 'Report', 'Invio report mensile utilizzo', '2024-11-30', FALSE, FALSE),

-- Cliente Example
(3, 'Pagamento', 'Fattura FT-2024-012', '2024-11-14', TRUE, FALSE),
(3, 'Documentazione', 'Documentazione fine anno', '2024-12-20', FALSE, FALSE);
