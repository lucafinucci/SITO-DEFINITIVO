-- Sistema Template Email e Comunicazioni

-- Tabella template email
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Identificazione template
    codice VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    categoria ENUM('fatturazione', 'solleciti', 'servizi', 'marketing', 'supporto', 'onboarding', 'altro') NOT NULL DEFAULT 'altro',

    -- Contenuto template
    oggetto VARCHAR(500) NOT NULL,
    corpo_html TEXT NOT NULL,
    corpo_testo TEXT NULL,

    -- Variabili disponibili
    variabili_disponibili JSON NULL COMMENT 'Lista variabili utilizzabili nel template',

    -- Configurazione
    attivo BOOLEAN NOT NULL DEFAULT TRUE,
    predefinito BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Template di sistema non modificabile',

    -- Email mittente
    mittente_nome VARCHAR(255) NULL,
    mittente_email VARCHAR(255) NULL,
    reply_to VARCHAR(255) NULL,

    -- Allegati
    allegati_automatici JSON NULL COMMENT 'Lista allegati da includere automaticamente',

    -- Metadata
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,

    FOREIGN KEY (created_by) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_codice (codice),
    INDEX idx_categoria (categoria),
    INDEX idx_attivo (attivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella storico email inviate
CREATE TABLE IF NOT EXISTS email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Riferimenti
    template_id INT NULL,
    cliente_id INT NULL,
    fattura_id INT NULL,

    -- Destinatari
    destinatario_email VARCHAR(255) NOT NULL,
    destinatario_nome VARCHAR(255) NULL,
    cc TEXT NULL,
    bcc TEXT NULL,

    -- Contenuto email
    oggetto VARCHAR(500) NOT NULL,
    corpo_html TEXT NOT NULL,
    corpo_testo TEXT NULL,

    -- Mittente
    mittente_nome VARCHAR(255) NULL,
    mittente_email VARCHAR(255) NOT NULL,

    -- Allegati
    allegati JSON NULL,

    -- Stato invio
    stato ENUM('in_coda', 'inviata', 'fallita', 'bounce', 'aperta', 'click') NOT NULL DEFAULT 'in_coda',
    errore TEXT NULL,

    -- Tracking
    data_invio TIMESTAMP NULL,
    data_apertura TIMESTAMP NULL,
    data_click TIMESTAMP NULL,
    ip_apertura VARCHAR(50) NULL,
    user_agent TEXT NULL,

    -- Metadata
    variabili_utilizzate JSON NULL COMMENT 'Variabili e valori usati',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    inviato_da INT NULL,

    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE SET NULL,
    FOREIGN KEY (inviato_da) REFERENCES utenti(id) ON DELETE SET NULL,

    INDEX idx_destinatario (destinatario_email),
    INDEX idx_stato (stato),
    INDEX idx_data_invio (data_invio),
    INDEX idx_template (template_id),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella coda email
CREATE TABLE IF NOT EXISTS email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Email da inviare
    template_id INT NULL,
    destinatario_email VARCHAR(255) NOT NULL,
    destinatario_nome VARCHAR(255) NULL,
    oggetto VARCHAR(500) NOT NULL,
    corpo_html TEXT NOT NULL,
    corpo_testo TEXT NULL,

    -- Configurazione
    mittente_email VARCHAR(255) NOT NULL,
    mittente_nome VARCHAR(255) NULL,
    reply_to VARCHAR(255) NULL,

    -- Allegati
    allegati JSON NULL,

    -- Variabili
    variabili JSON NULL,

    -- Riferimenti
    cliente_id INT NULL,
    fattura_id INT NULL,

    -- Scheduling
    priorita ENUM('bassa', 'normale', 'alta', 'urgente') NOT NULL DEFAULT 'normale',
    data_pianificazione TIMESTAMP NULL,
    tentativi INT NOT NULL DEFAULT 0,
    max_tentativi INT NOT NULL DEFAULT 3,

    -- Stato
    stato ENUM('in_coda', 'processing', 'completata', 'fallita', 'annullata') NOT NULL DEFAULT 'in_coda',
    errore TEXT NULL,

    -- Timestamp
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,

    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (fattura_id) REFERENCES fatture(id) ON DELETE SET NULL,

    INDEX idx_stato (stato),
    INDEX idx_priorita (priorita),
    INDEX idx_data_pianificazione (data_pianificazione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci template predefiniti

-- 1. Benvenuto nuovo cliente
INSERT INTO email_templates (
    codice, nome, descrizione, categoria, oggetto, corpo_html, corpo_testo,
    variabili_disponibili, predefinito, mittente_nome, mittente_email
) VALUES (
    'benvenuto-cliente',
    'Benvenuto Nuovo Cliente',
    'Email di benvenuto automatica per nuovi clienti',
    'onboarding',
    'Benvenuto in Finch-AI, {nome_cliente}!',
    '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 24px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .highlight { background: #f3f4f6; padding: 15px; border-left: 4px solid #8b5cf6; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Benvenuto in Finch-AI!</h1>
        </div>
        <div class="content">
            <p>Ciao <strong>{nome_cliente}</strong>,</p>

            <p>Siamo entusiasti di darti il benvenuto in Finch-AI! Il tuo account è stato creato con successo.</p>

            <div class="highlight">
                <p><strong>I tuoi dati di accesso:</strong></p>
                <p>Email: <strong>{email}</strong><br>
                Azienda: <strong>{azienda}</strong></p>
            </div>

            <p>Puoi accedere alla tua Area Clienti per:</p>
            <ul>
                <li>Gestire i tuoi servizi attivi</li>
                <li>Visualizzare e pagare le fatture</li>
                <li>Richiedere addestramenti AI personalizzati</li>
                <li>Accedere al supporto tecnico</li>
            </ul>

            <p style="text-align: center;">
                <a href="{link_area_clienti}" class="button">Accedi all\'Area Clienti</a>
            </p>

            <p>Se hai domande o hai bisogno di assistenza, non esitare a contattarci.</p>

            <p>A presto,<br>
            <strong>Il Team Finch-AI</strong></p>
        </div>
        <div class="footer">
            <p>© 2025 Finch-AI - Tutti i diritti riservati</p>
            <p>Questa è una comunicazione automatica. Per assistenza: <a href="mailto:support@finch-ai.it">support@finch-ai.it</a></p>
        </div>
    </div>
</body>
</html>',
    'Benvenuto in Finch-AI!\n\nCiao {nome_cliente},\n\nSiamo entusiasti di darti il benvenuto! Il tuo account è stato creato con successo.\n\nEmail: {email}\nAzienda: {azienda}\n\nAccedi all\'Area Clienti: {link_area_clienti}\n\nIl Team Finch-AI',
    '["nome_cliente", "email", "azienda", "link_area_clienti"]',
    TRUE,
    'Finch-AI',
    'noreply@finch-ai.it'
);

-- 2. Attivazione servizio
INSERT INTO email_templates (
    codice, nome, descrizione, categoria, oggetto, corpo_html, corpo_testo,
    variabili_disponibili, predefinito
) VALUES (
    'servizio-attivato',
    'Servizio Attivato',
    'Conferma attivazione servizio',
    'servizi',
    'Il tuo servizio {nome_servizio} è stato attivato',
    '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
        .service-box { background: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #10b981; }
        .button { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Servizio Attivato</h1>
        </div>
        <div class="content">
            <p>Ciao <strong>{nome_cliente}</strong>,</p>

            <p>Il tuo servizio è stato attivato con successo!</p>

            <div class="service-box">
                <h3 style="margin-top: 0;">{nome_servizio}</h3>
                <p>{descrizione_servizio}</p>
                <p><strong>Data attivazione:</strong> {data_attivazione}</p>
                <p><strong>Costo mensile:</strong> €{prezzo_mensile}</p>
            </div>

            <p>Il servizio è ora disponibile nella tua Area Clienti.</p>

            <p style="text-align: center;">
                <a href="{link_servizio}" class="button">Vai al Servizio</a>
            </p>

            <p>Grazie per aver scelto Finch-AI!</p>
        </div>
    </div>
</body>
</html>',
    'Servizio Attivato\n\nIl tuo servizio {nome_servizio} è stato attivato!\n\nData: {data_attivazione}\nCosto: €{prezzo_mensile}/mese\n\nVai al servizio: {link_servizio}',
    '["nome_cliente", "nome_servizio", "descrizione_servizio", "data_attivazione", "prezzo_mensile", "link_servizio"]',
    TRUE
);

-- 3. Nuova fattura emessa
INSERT INTO email_templates (
    codice, nome, descrizione, categoria, oggetto, corpo_html, corpo_testo,
    variabili_disponibili, predefinito
) VALUES (
    'fattura-emessa',
    'Fattura Emessa',
    'Notifica nuova fattura emessa',
    'fatturazione',
    'Nuova Fattura {numero_fattura} - €{totale}',
    '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
        .invoice-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .total { font-size: 24px; font-weight: bold; color: #8b5cf6; text-align: center; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
        .button-secondary { background: #3b82f6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Nuova Fattura</h1>
        </div>
        <div class="content">
            <p>Gentile <strong>{azienda}</strong>,</p>

            <p>È stata emessa una nuova fattura a tuo nome.</p>

            <div class="invoice-details">
                <p><strong>Numero Fattura:</strong> {numero_fattura}</p>
                <p><strong>Data Emissione:</strong> {data_emissione}</p>
                <p><strong>Data Scadenza:</strong> {data_scadenza}</p>
                <p><strong>Imponibile:</strong> €{imponibile}</p>
                <p><strong>IVA ({iva_percentuale}%):</strong> €{iva_importo}</p>
            </div>

            <div class="total">
                Totale: €{totale}
            </div>

            <p style="text-align: center;">
                <a href="{link_paga}" class="button">💳 Paga Ora</a>
                <a href="{link_pdf}" class="button button-secondary">📄 Scarica PDF</a>
            </p>

            <p>Ti ricordiamo che il pagamento deve essere effettuato entro il <strong>{data_scadenza}</strong>.</p>

            <p>Per qualsiasi domanda, non esitare a contattarci.</p>

            <p>Cordiali saluti,<br>
            <strong>Finch-AI Amministrazione</strong></p>
        </div>
    </div>
</body>
</html>',
    'Nuova Fattura {numero_fattura}\n\nImporto: €{totale}\nScadenza: {data_scadenza}\n\nPaga ora: {link_paga}\nScarica PDF: {link_pdf}',
    '["azienda", "numero_fattura", "data_emissione", "data_scadenza", "imponibile", "iva_percentuale", "iva_importo", "totale", "link_paga", "link_pdf"]',
    TRUE
);

-- 4. Pagamento ricevuto
INSERT INTO email_templates (
    codice, nome, descrizione, categoria, oggetto, corpo_html, corpo_testo,
    variabili_disponibili, predefinito
) VALUES (
    'pagamento-ricevuto',
    'Conferma Pagamento',
    'Conferma ricezione pagamento',
    'fatturazione',
    'Pagamento Ricevuto - Fattura {numero_fattura}',
    '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
        .success-box { background: #d1fae5; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; border: 2px solid #10b981; }
        .checkmark { font-size: 48px; color: #10b981; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Pagamento Confermato</h1>
        </div>
        <div class="content">
            <p>Gentile <strong>{azienda}</strong>,</p>

            <div class="success-box">
                <div class="checkmark">✓</div>
                <h2 style="margin: 10px 0;">Pagamento Ricevuto</h2>
                <p style="font-size: 20px; font-weight: bold; color: #10b981;">€{importo_pagato}</p>
            </div>

            <p>Confermiamo di aver ricevuto il pagamento per la fattura <strong>{numero_fattura}</strong>.</p>

            <p><strong>Dettagli pagamento:</strong></p>
            <ul>
                <li>Data: {data_pagamento}</li>
                <li>Metodo: {metodo_pagamento}</li>
                <li>Importo: €{importo_pagato}</li>
                <li>Riferimento: {riferimento_transazione}</li>
            </ul>

            <p>La fattura è stata aggiornata allo stato "Pagata".</p>

            <p>Grazie per la tua puntualità!</p>

            <p>Cordiali saluti,<br>
            <strong>Finch-AI Amministrazione</strong></p>
        </div>
    </div>
</body>
</html>',
    'Pagamento Ricevuto\n\nFattura: {numero_fattura}\nImporto: €{importo_pagato}\nData: {data_pagamento}\nMetodo: {metodo_pagamento}\n\nGrazie!',
    '["azienda", "numero_fattura", "importo_pagato", "data_pagamento", "metodo_pagamento", "riferimento_transazione"]',
    TRUE
);

-- 5. Sollecito primo (7 giorni)
INSERT INTO email_templates (
    codice, nome, descrizione, categoria, oggetto, corpo_html, corpo_testo,
    variabili_disponibili, predefinito
) VALUES (
    'sollecito-primo',
    'Primo Sollecito Pagamento',
    'Sollecito cortese dopo 7 giorni dalla scadenza',
    'solleciti',
    'Gentile Sollecito - Fattura {numero_fattura}',
    '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f59e0b; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
        .warning-box { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b; }
        .button { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Sollecito Pagamento</h1>
        </div>
        <div class="content">
            <p>Gentile <strong>{azienda}</strong>,</p>

            <p>Con la presente desideriamo cortesemente ricordarLe che la fattura <strong>{numero_fattura}</strong> risulta scaduta.</p>

            <div class="warning-box">
                <p><strong>Dettagli Fattura:</strong></p>
                <p>Numero: {numero_fattura}</p>
                <p>Data Emissione: {data_emissione}</p>
                <p>Data Scadenza: {data_scadenza}</p>
                <p>Importo: <strong>€{totale}</strong></p>
                <p>Giorni di ritardo: <strong>{giorni_ritardo}</strong></p>
            </div>

            <p>Le chiediamo cortesemente di provvedere al pagamento con sollecitudine.</p>

            <p style="text-align: center;">
                <a href="{link_paga}" class="button">Paga Ora</a>
            </p>

            <p>Qualora il pagamento sia già stato effettuato, La preghiamo di comunicarcelo tempestivamente.</p>

            <p>Per qualsiasi chiarimento, non esiti a contattarci.</p>

            <p>Cordiali saluti,<br>
            <strong>Finch-AI Amministrazione</strong></p>
        </div>
    </div>
</body>
</html>',
    'Sollecito Pagamento\n\nFattura: {numero_fattura}\nScadenza: {data_scadenza}\nImporto: €{totale}\nRitardo: {giorni_ritardo} giorni\n\nPaga ora: {link_paga}',
    '["azienda", "numero_fattura", "data_emissione", "data_scadenza", "totale", "giorni_ritardo", "link_paga"]',
    TRUE
);

-- Vista statistiche email
CREATE OR REPLACE VIEW v_email_statistics AS
SELECT
    DATE(created_at) AS data,
    COUNT(*) AS totale_inviate,
    SUM(CASE WHEN stato = 'inviata' THEN 1 ELSE 0 END) AS inviate_successo,
    SUM(CASE WHEN stato = 'fallita' THEN 1 ELSE 0 END) AS fallite,
    SUM(CASE WHEN stato = 'aperta' THEN 1 ELSE 0 END) AS aperte,
    SUM(CASE WHEN stato = 'click' THEN 1 ELSE 0 END) AS click,
    ROUND(SUM(CASE WHEN stato = 'aperta' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 2) AS tasso_apertura,
    ROUND(SUM(CASE WHEN stato = 'click' THEN 1 ELSE 0 END) * 100.0 / NULLIF(SUM(CASE WHEN stato = 'aperta' THEN 1 ELSE 0 END), 0), 2) AS tasso_click
FROM email_log
GROUP BY DATE(created_at)
ORDER BY data DESC;
