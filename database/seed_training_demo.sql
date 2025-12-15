-- ===============================================
-- Dati DEMO per Sistema Training AI
-- Esegui DOPO aver creato le tabelle con add_training_tables.sql
-- ===============================================

-- IMPORTANTE: Sostituisci user_id=2 con l'ID del tuo utente test
-- Per trovarlo: SELECT id, email FROM utenti WHERE email = 'tuaemail@test.it';

-- Inserisci modelli addestrati di esempio
INSERT INTO modelli_addestrati
(richiesta_id, user_id, nome_modello, tipo_modello, versione, accuratezza, num_documenti_addestramento, attivo, created_at)
VALUES
-- Modello 1: Fatture Elettroniche (ATTIVO)
(1, 2, 'Fatture Elettroniche', 'Fatture', '1.0', 98.5, 4521, TRUE, DATE_SUB(NOW(), INTERVAL 28 DAY)),

-- Modello 2: Contratti Commerciali (ATTIVO)
(2, 2, 'Contratti Commerciali', 'Contratti', '1.2', 96.4, 1834, TRUE, DATE_SUB(NOW(), INTERVAL 15 DAY)),

-- Modello 3: Bolle di Trasporto (ATTIVO)
(3, 2, 'Bolle di Trasporto', 'DDT', '2.0', 97.8, 2756, TRUE, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Inserisci richieste di addestramento di esempio
INSERT INTO richieste_addestramento
(user_id, tipo_modello, descrizione, num_documenti_stimati, note, stato, created_at)
VALUES
-- Richiesta 1: Completata (per modello 1)
(
  2,
  'fatture',
  'Addestramento modello per fatture elettroniche fornitori con estrazione automatica di: codice fornitore, data emissione, importo totale, IVA, codici articolo e quantità.',
  50,
  'Fatture formato XML e PDF',
  'completato',
  DATE_SUB(NOW(), INTERVAL 30 DAY)
),

-- Richiesta 2: Completata (per modello 2)
(
  2,
  'contratti',
  'Modello per contratti commerciali con estrazione di: parti contraenti, data stipula, durata, clausole principali, importi e scadenze rinnovo.',
  35,
  'Contratti di fornitura e servizi',
  'completato',
  DATE_SUB(NOW(), INTERVAL 17 DAY)
),

-- Richiesta 3: Completata (per modello 3)
(
  2,
  'ddt',
  'DDT con estrazione: numero documento, data, destinatario, articoli, quantità, causale trasporto, vettore.',
  80,
  NULL,
  'completato',
  DATE_SUB(NOW(), INTERVAL 22 DAY)
),

-- Richiesta 4: In Lavorazione
(
  2,
  'preventivi',
  'Modello per preventivi clienti con estrazione di: numero preventivo, cliente, lista articoli, prezzi unitari, sconti, totale.',
  40,
  'Preventivi in formato PDF con loghi aziendali',
  'in_lavorazione',
  DATE_SUB(NOW(), INTERVAL 3 DAY)
),

-- Richiesta 5: In Attesa
(
  2,
  'ordini',
  'Ordini di acquisto fornitori con estrazione: numero ordine, fornitore, data, articoli ordinati, quantità, prezzi, consegna prevista.',
  25,
  'Ordini ricevuti via email in PDF',
  'in_attesa',
  DATE_SUB(NOW(), INTERVAL 1 DAY)
);

-- Aggiorna richiesta_id nei modelli completati
UPDATE modelli_addestrati SET richiesta_id = 1 WHERE id = 1;
UPDATE modelli_addestrati SET richiesta_id = 2 WHERE id = 2;
UPDATE modelli_addestrati SET richiesta_id = 3 WHERE id = 3;

-- Aggiorna last_used per alcuni modelli
UPDATE modelli_addestrati
SET last_used = DATE_SUB(NOW(), INTERVAL 2 DAY)
WHERE id = 1;

UPDATE modelli_addestrati
SET last_used = DATE_SUB(NOW(), INTERVAL 5 DAY)
WHERE id = 3;

-- ===============================================
-- VERIFICA DATI INSERITI
-- ===============================================

-- Conta modelli attivi per utente
SELECT
  u.email,
  COUNT(m.id) as num_modelli_attivi
FROM utenti u
LEFT JOIN modelli_addestrati m ON u.id = m.user_id AND m.attivo = TRUE
WHERE u.id = 2
GROUP BY u.email;

-- Mostra tutti i modelli
SELECT
  m.id,
  m.nome_modello,
  m.tipo_modello,
  m.accuratezza,
  m.num_documenti_addestramento,
  m.attivo,
  DATE_FORMAT(m.created_at, '%d/%m/%Y') as creato
FROM modelli_addestrati m
WHERE m.user_id = 2
ORDER BY m.created_at DESC;

-- Mostra richieste in corso
SELECT
  r.id,
  r.tipo_modello,
  r.stato,
  r.num_documenti_stimati,
  DATE_FORMAT(r.created_at, '%d/%m/%Y %H:%i') as richiesto
FROM richieste_addestramento r
WHERE r.user_id = 2 AND r.stato IN ('in_attesa', 'in_lavorazione')
ORDER BY r.created_at DESC;
