<?php
/**
 * Email Hooks - Integrazione email automatiche con eventi sistema
 *
 * Funzioni hook da chiamare quando avvengono eventi nel sistema
 */

require_once __DIR__ . '/email-manager.php';
require_once __DIR__ . '/notifiche-manager.php';

/**
 * Hook: Nuovo cliente registrato
 */
function onClienteRegistrato($pdo, $clienteId) {
    try {
        // Recupera dati cliente
        $stmt = $pdo->prepare('SELECT * FROM utenti WHERE id = :id');
        $stmt->execute(['id' => $clienteId]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            return false;
        }

        // Invia email benvenuto
        sendWelcomeEmail($pdo, $cliente);

        // Notifica admin
        notificaNuovoCliente($pdo, $cliente);

        return true;

    } catch (Exception $e) {
        error_log("Errore invio email benvenuto: " . $e->getMessage());
        return false;
    }
}

/**
 * Hook: Servizio attivato per cliente
 */
function onServizioAttivato($pdo, $userId, $servizioId) {
    try {
        // Recupera dati
        $stmt = $pdo->prepare('
            SELECT
                u.id AS cliente_id,
                u.nome,
                u.cognome,
                u.email,
                u.azienda,
                s.nome AS servizio_nome,
                s.descrizione AS servizio_descrizione,
                s.prezzo_mensile,
                us.data_attivazione
            FROM utenti u
            JOIN utenti_servizi us ON u.id = us.user_id
            JOIN servizi s ON us.servizio_id = s.id
            WHERE u.id = :user_id AND s.id = :servizio_id
            ORDER BY us.data_attivazione DESC
            LIMIT 1
        ');
        $stmt->execute(['user_id' => $userId, 'servizio_id' => $servizioId]);
        $dati = $stmt->fetch();

        if (!$dati) {
            return false;
        }

        $emailManager = new EmailManager($pdo);

        $variabili = [
            'nome_cliente' => $dati['nome'] . ' ' . $dati['cognome'],
            'nome_servizio' => $dati['servizio_nome'],
            'descrizione_servizio' => $dati['servizio_descrizione'],
            'data_attivazione' => date('d/m/Y', strtotime($dati['data_attivazione'])),
            'prezzo_mensile' => number_format($dati['prezzo_mensile'], 2, ',', '.'),
            'link_servizio' => 'https://finch-ai.it/area-clienti/servizio-dettaglio.php?id=' . $servizioId
        ];

        return $emailManager->sendFromTemplate(
            'servizio-attivato',
            ['email' => $dati['email'], 'nome' => $dati['azienda']],
            $variabili,
            ['cliente_id' => $dati['cliente_id']]
        );

    } catch (Exception $e) {
        error_log("Errore invio email attivazione servizio: " . $e->getMessage());
        return false;
    }
}

/**
 * Hook: Fattura emessa
 */
function onFatturaEmessa($pdo, $fatturaId) {
    try {
        // Recupera dati fattura
        $stmt = $pdo->prepare('
            SELECT
                f.*,
                u.id AS cliente_id,
                u.nome,
                u.cognome,
                u.email,
                u.azienda
            FROM fatture f
            JOIN utenti u ON f.cliente_id = u.id
            WHERE f.id = :id
        ');
        $stmt->execute(['id' => $fatturaId]);
        $dati = $stmt->fetch();

        if (!$dati) {
            return false;
        }

        // Invia email
        return sendInvoiceEmail($pdo, $dati, $dati);

    } catch (Exception $e) {
        error_log("Errore invio email fattura: " . $e->getMessage());
        return false;
    }
}

/**
 * Hook: Pagamento ricevuto
 */
function onPagamentoRicevuto($pdo, $fatturaId, $importo, $metodo, $riferimento = null) {
    try {
        // Recupera dati
        $stmt = $pdo->prepare('
            SELECT
                f.*,
                u.id AS cliente_id,
                u.email,
                u.azienda
            FROM fatture f
            JOIN utenti u ON f.cliente_id = u.id
            WHERE f.id = :id
        ');
        $stmt->execute(['id' => $fatturaId]);
        $dati = $stmt->fetch();

        if (!$dati) {
            return false;
        }

        $emailManager = new EmailManager($pdo);

        $variabili = [
            'azienda' => $dati['azienda'],
            'numero_fattura' => $dati['numero_fattura'],
            'importo_pagato' => number_format($importo, 2, ',', '.'),
            'data_pagamento' => date('d/m/Y H:i'),
            'metodo_pagamento' => ucfirst($metodo),
            'riferimento_transazione' => $riferimento ?? 'N/A'
        ];

        // Invia email conferma
        $emailManager->sendFromTemplate(
            'pagamento-ricevuto',
            ['email' => $dati['email'], 'nome' => $dati['azienda']],
            $variabili,
            [
                'cliente_id' => $dati['cliente_id'],
                'fattura_id' => $fatturaId,
                'priorita' => 'alta'
            ]
        );

        // Notifica admin
        notificaPagamentoRicevuto($pdo, $dati, $importo, $metodo);

        return true;

    } catch (Exception $e) {
        error_log("Errore invio conferma pagamento: " . $e->getMessage());
        return false;
    }
}

/**
 * Hook: Richiesta addestramento ricevuta
 */
function onRichiestaAddestramentoRicevuta($pdo, $richiestaId) {
    try {
        // Recupera dati richiesta
        $stmt = $pdo->prepare('
            SELECT
                r.id,
                r.user_id,
                r.tipo_documento,
                r.numero_documenti,
                r.note,
                r.created_at,
                u.id AS cliente_id,
                u.nome,
                u.cognome,
                u.email,
                u.azienda
            FROM richieste_addestramento r
            JOIN utenti u ON r.user_id = u.id
            WHERE r.id = :id
        ');
        $stmt->execute(['id' => $richiestaId]);
        $richiesta = $stmt->fetch();

        if (!$richiesta) {
            return false;
        }

        // Crea template custom per questa email (non predefinito)
        $emailManager = new EmailManager($pdo);

        $oggetto = "Richiesta Addestramento Ricevuta - #{$richiestaId}";

        $corpo = '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #8b5cf6; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
        .info-box { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 Richiesta Addestramento Ricevuta</h1>
        </div>
        <div class="content">
            <p>Gentile <strong>' . htmlspecialchars($richiesta['azienda']) . '</strong>,</p>

            <p>Abbiamo ricevuto la tua richiesta di addestramento AI.</p>

            <div class="info-box">
                <p><strong>Dettagli Richiesta:</strong></p>
                <p>ID Richiesta: #' . $richiesta['id'] . '</p>
                <p>Tipo Documento: ' . htmlspecialchars($richiesta['tipo_documento']) . '</p>
                <p>Numero Documenti: ' . $richiesta['numero_documenti'] . '</p>
                <p>Data Richiesta: ' . date('d/m/Y H:i', strtotime($richiesta['created_at'])) . '</p>
            </div>

            <p>Il nostro team sta esaminando la tua richiesta e ti contatterà entro 24-48 ore per confermare i dettagli e fornire un preventivo.</p>

            <p>Grazie per aver scelto Finch-AI!</p>

            <p>Cordiali saluti,<br>
            <strong>Il Team Finch-AI</strong></p>
        </div>
    </div>
</body>
</html>';

        // Aggiungi direttamente alla coda
        $emailManager->addToQueue([
            'template_id' => null,
            'destinatario_email' => $richiesta['email'],
            'destinatario_nome' => $richiesta['azienda'],
            'oggetto' => $oggetto,
            'corpo_html' => $corpo,
            'corpo_testo' => strip_tags($corpo),
            'mittente_email' => 'noreply@finch-ai.it',
            'mittente_nome' => 'Finch-AI',
            'reply_to' => 'support@finch-ai.it',
            'cliente_id' => $richiesta['cliente_id'],
            'fattura_id' => null,
            'variabili' => null,
            'priorita' => 'alta',
            'data_pianificazione' => null
        ]);

        // Notifica admin
        notificaRichiestaAddestramento($pdo, $richiesta);

        return true;

    } catch (Exception $e) {
        error_log("Errore invio conferma richiesta addestramento: " . $e->getMessage());
        return false;
    }
}

/**
 * Hook: Servizio disattivato
 */
function onServizioDisattivato($pdo, $userId, $servizioId, $motivazione = null) {
    try {
        // Recupera dati
        $stmt = $pdo->prepare('
            SELECT
                u.id AS cliente_id,
                u.email,
                u.azienda,
                s.nome AS servizio_nome
            FROM utenti u
            CROSS JOIN servizi s
            WHERE u.id = :user_id AND s.id = :servizio_id
        ');
        $stmt->execute(['user_id' => $userId, 'servizio_id' => $servizioId]);
        $dati = $stmt->fetch();

        if (!$dati) {
            return false;
        }

        $emailManager = new EmailManager($pdo);

        $corpo = '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ef4444; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Servizio Disattivato</h1>
        </div>
        <div class="content">
            <p>Gentile <strong>' . htmlspecialchars($dati['azienda']) . '</strong>,</p>

            <p>Ti confermiamo che il servizio <strong>' . htmlspecialchars($dati['servizio_nome']) . '</strong> è stato disattivato.</p>

            ' . ($motivazione ? '<p>Motivazione: ' . htmlspecialchars($motivazione) . '</p>' : '') . '

            <p>Se desideri riattivare il servizio o hai domande, non esitare a contattarci.</p>

            <p>Cordiali saluti,<br>
            <strong>Il Team Finch-AI</strong></p>
        </div>
    </div>
</body>
</html>';

        return $emailManager->addToQueue([
            'template_id' => null,
            'destinatario_email' => $dati['email'],
            'destinatario_nome' => $dati['azienda'],
            'oggetto' => "Servizio {$dati['servizio_nome']} Disattivato",
            'corpo_html' => $corpo,
            'corpo_testo' => strip_tags($corpo),
            'mittente_email' => 'noreply@finch-ai.it',
            'mittente_nome' => 'Finch-AI',
            'reply_to' => null,
            'cliente_id' => $dati['cliente_id'],
            'fattura_id' => null,
            'variabili' => null,
            'priorita' => 'normale',
            'data_pianificazione' => null
        ]);

    } catch (Exception $e) {
        error_log("Errore invio email disattivazione servizio: " . $e->getMessage());
        return false;
    }
}
