-- Tabella per solleciti pagamento fatture
CREATE TABLE IF NOT EXISTS fatture_solleciti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fattura_id INT NOT NULL,

    -- Tipo e numero sollecito
    tipo ENUM('primo_sollecito', 'secondo_sollecito', 'sollecito_urgente', 'ultimo_avviso') NOT NULL,
    numero_sollecito INT NOT NULL DEFAULT 1,

    -- Contenuto sollecito
    oggetto VARCHAR(255) NOT NULL,
    messaggio TEXT NULL,

    -- Stato
    stato ENUM('da_inviare', 'inviato', 'annullato') NOT NULL DEFAULT 'da_inviare',

    -- Date
    data_creazione TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_invio TIMESTAMP NULL,

    -- Metadati
    inviato_da INT NULL,
    metodo_invio VARCHAR(50) NULL,

    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE CASCADE,
    FOREIGN KEY (inviato_da) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_fattura (fattura_id),
    INDEX idx_stato (stato),
    INDEX idx_tipo (tipo),
    INDEX idx_data_creazione (data_creazione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per configurazione solleciti
CREATE TABLE IF NOT EXISTS solleciti_config (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Configurazione giorni
    giorni_primo_sollecito INT NOT NULL DEFAULT 7,
    giorni_secondo_sollecito INT NOT NULL DEFAULT 15,
    giorni_sollecito_urgente INT NOT NULL DEFAULT 30,

    -- Abilitazione solleciti automatici
    solleciti_automatici_attivi BOOLEAN NOT NULL DEFAULT TRUE,

    -- Email mittente
    email_mittente VARCHAR(255) NOT NULL DEFAULT 'fatturazione@finch-ai.it',
    nome_mittente VARCHAR(255) NOT NULL DEFAULT 'Finch-AI Amministrazione',

    -- Template email
    template_primo_sollecito TEXT NULL,
    template_secondo_sollecito TEXT NULL,
    template_sollecito_urgente TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci configurazione di default
INSERT INTO solleciti_config (
    giorni_primo_sollecito,
    giorni_secondo_sollecito,
    giorni_sollecito_urgente,
    solleciti_automatici_attivi,
    template_primo_sollecito,
    template_secondo_sollecito,
    template_sollecito_urgente
) VALUES (
    7,
    15,
    30,
    TRUE,
    'Gentile Cliente,

Con la presente desideriamo cortesemente ricordarLe che la fattura n. {numero_fattura} del {data_emissione}, per un importo di € {totale}, è scaduta in data {data_scadenza}.

Le chiediamo cortesemente di provvedere al pagamento con sollecitudine.

Per qualsiasi chiarimento, non esiti a contattarci.

Cordiali saluti,
Finch-AI Amministrazione',

    'Gentile Cliente,

Con riferimento alla nostra precedente comunicazione, ci permettiamo di sollecitare nuovamente il pagamento della fattura n. {numero_fattura} del {data_emissione}, scaduta il {data_scadenza}.

Importo: € {totale}
Giorni di ritardo: {giorni_ritardo}

Qualora il pagamento sia già stato effettuato, La preghiamo di comunicarcelo tempestivamente.

In caso contrario, La invitiamo a regolarizzare la Sua posizione quanto prima.

Cordiali saluti,
Finch-AI Amministrazione',

    'URGENTE - Ultimo Sollecito

Gentile Cliente,

Nonostante i nostri precedenti solleciti, il pagamento della fattura n. {numero_fattura} risulta ancora inevaso.

Dettagli fattura:
- Data emissione: {data_emissione}
- Data scadenza: {data_scadenza}
- Importo: € {totale}
- Giorni di ritardo: {giorni_ritardo}

La invitiamo URGENTEMENTE a provvedere al saldo entro 7 giorni dalla ricezione della presente, per evitare l''avvio delle procedure di recupero crediti.

Per concordare eventuali piani di rateizzazione, può contattarci ai nostri riferimenti.

Distinti saluti,
Finch-AI Amministrazione'
);

-- Vista per solleciti con dettagli fattura
CREATE OR REPLACE VIEW v_solleciti_pending AS
SELECT
    s.id AS sollecito_id,
    s.tipo,
    s.numero_sollecito,
    s.oggetto,
    s.stato AS stato_sollecito,
    s.data_creazione,
    f.id AS fattura_id,
    f.numero_fattura,
    f.totale,
    f.data_emissione,
    f.data_scadenza,
    f.stato AS stato_fattura,
    DATEDIFF(CURDATE(), f.data_scadenza) AS giorni_ritardo,
    u.id AS cliente_id,
    u.azienda,
    u.nome AS cliente_nome,
    u.cognome AS cliente_cognome,
    u.email AS cliente_email
FROM fatture_solleciti s
JOIN fatture f ON s.fattura_id = f.id
JOIN utenti u ON f.cliente_id = u.id
WHERE s.stato = 'da_inviare'
ORDER BY s.numero_sollecito DESC, s.data_creazione ASC;
