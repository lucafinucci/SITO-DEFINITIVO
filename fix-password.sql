USE finch_ai_clienti;

-- Aggiorna password per tutti gli utenti
-- Password: Demo123!
UPDATE utenti SET password_hash = '$2y$10$ZCrT.LYKMXCWvfMcugLi/Oje/DH3muqxAl9XLe.qwnFQFezmbP84u';

SELECT email, password_hash FROM utenti;
