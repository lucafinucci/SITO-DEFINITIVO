-- Vista per Scadenzario Fatture
-- Mostra tutte le scadenze fatture con colori e priorità

CREATE OR REPLACE VIEW v_scadenzario_fatture AS
SELECT
    f.id AS fattura_id,
    f.numero_fattura,
    f.cliente_id,
    u.azienda,
    u.nome AS cliente_nome,
    u.cognome AS cliente_cognome,
    u.email AS cliente_email,
    f.data_emissione,
    f.data_scadenza,
    f.data_pagamento,
    f.totale,
    f.stato,

    -- Calcola giorni alla scadenza (negativo = scaduta)
    DATEDIFF(f.data_scadenza, CURDATE()) AS giorni_a_scadenza,

    -- Calcola giorni di ritardo (se scaduta)
    CASE
        WHEN f.data_scadenza < CURDATE() AND f.stato != 'pagata'
        THEN DATEDIFF(CURDATE(), f.data_scadenza)
        ELSE 0
    END AS giorni_ritardo,

    -- Priorità (1=urgente, 2=warning, 3=normale)
    CASE
        WHEN f.stato = 'scaduta' THEN 1
        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF(f.data_scadenza, CURDATE()) <= 7 THEN 2
        WHEN f.stato IN ('emessa', 'inviata') THEN 3
        ELSE 4
    END AS priorita,

    -- Colore per calendario
    CASE
        WHEN f.stato = 'pagata' THEN 'success'
        WHEN f.stato = 'scaduta' THEN 'danger'
        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF(f.data_scadenza, CURDATE()) <= 7 THEN 'warning'
        WHEN f.stato IN ('emessa', 'inviata') THEN 'info'
        ELSE 'default'
    END AS colore,

    -- Tipo evento per calendario
    CASE
        WHEN f.stato = 'pagata' THEN 'pagamento'
        ELSE 'scadenza'
    END AS tipo_evento

FROM fatture f
JOIN utenti u ON f.cliente_id = u.id
WHERE f.stato IN ('emessa', 'inviata', 'scaduta', 'pagata')
ORDER BY f.data_scadenza ASC;

-- Vista per Dashboard Scadenze (solo fatture attive non pagate)
CREATE OR REPLACE VIEW v_dashboard_scadenze AS
SELECT
    DATE(data_scadenza) AS data,
    COUNT(*) AS num_fatture,
    SUM(totale) AS importo_totale,
    SUM(CASE WHEN stato = 'scaduta' THEN 1 ELSE 0 END) AS num_scadute,
    SUM(CASE WHEN stato = 'scaduta' THEN totale ELSE 0 END) AS importo_scaduto,
    MIN(giorni_a_scadenza) AS giorni_minimo,
    MAX(priorita) AS priorita_max
FROM v_scadenzario_fatture
WHERE stato IN ('emessa', 'inviata', 'scaduta')
GROUP BY DATE(data_scadenza)
ORDER BY data ASC;

-- Vista per Statistiche Scadenzario
CREATE OR REPLACE VIEW v_statistiche_scadenzario AS
SELECT
    -- Scadenze oggi
    SUM(CASE WHEN DATE(data_scadenza) = CURDATE() AND stato != 'pagata' THEN 1 ELSE 0 END) AS scadenze_oggi,
    SUM(CASE WHEN DATE(data_scadenza) = CURDATE() AND stato != 'pagata' THEN totale ELSE 0 END) AS importo_oggi,

    -- Scadenze questa settimana
    SUM(CASE
        WHEN data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND stato != 'pagata'
        THEN 1 ELSE 0
    END) AS scadenze_settimana,
    SUM(CASE
        WHEN data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND stato != 'pagata'
        THEN totale ELSE 0
    END) AS importo_settimana,

    -- Scadenze questo mese
    SUM(CASE
        WHEN MONTH(data_scadenza) = MONTH(CURDATE())
        AND YEAR(data_scadenza) = YEAR(CURDATE())
        AND stato != 'pagata'
        THEN 1 ELSE 0
    END) AS scadenze_mese,
    SUM(CASE
        WHEN MONTH(data_scadenza) = MONTH(CURDATE())
        AND YEAR(data_scadenza) = YEAR(CURDATE())
        AND stato != 'pagata'
        THEN totale ELSE 0
    END) AS importo_mese,

    -- Fatture scadute
    SUM(CASE WHEN stato = 'scaduta' THEN 1 ELSE 0 END) AS fatture_scadute,
    SUM(CASE WHEN stato = 'scaduta' THEN totale ELSE 0 END) AS importo_scaduto,

    -- Fatture pagate questo mese
    SUM(CASE
        WHEN stato = 'pagata'
        AND MONTH(data_pagamento) = MONTH(CURDATE())
        AND YEAR(data_pagamento) = YEAR(CURDATE())
        THEN 1 ELSE 0
    END) AS pagate_mese,
    SUM(CASE
        WHEN stato = 'pagata'
        AND MONTH(data_pagamento) = MONTH(CURDATE())
        AND YEAR(data_pagamento) = YEAR(CURDATE())
        THEN totale ELSE 0
    END) AS importo_pagato_mese

FROM v_scadenzario_fatture;

-- Indici per performance
ALTER TABLE fatture ADD INDEX idx_data_scadenza (data_scadenza);
ALTER TABLE fatture ADD INDEX idx_data_pagamento (data_pagamento);
ALTER TABLE fatture ADD INDEX idx_stato_scadenza (stato, data_scadenza);
