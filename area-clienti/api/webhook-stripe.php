<?php
/**
 * Webhook Handler per Stripe
 *
 * Riceve notifiche da Stripe per eventi di pagamento
 * URL da configurare su Stripe Dashboard: https://tuosito.it/area-clienti/api/webhook-stripe.php
 */

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/payment-gateways.php';

// Leggi il payload
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Log webhook ricevuto
$logDir = __DIR__ . '/../cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/webhooks-stripe-' . date('Y-m') . '.log';

try {
    // Inizializza gateway Stripe
    $stripe = new StripeGateway($pdo);

    // Verifica signature webhook (in produzione, decommentare questa parte)
    /*
    $webhookSecret = $stripe->getWebhookSecret();

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );
    } catch (\UnexpectedValueException $e) {
        // Payload non valido
        http_response_code(400);
        exit();
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Signature non valida
        http_response_code(400);
        exit();
    }
    */

    // Per testing, decodifica direttamente il payload
    $event = json_decode($payload, true);

    if (!$event || !isset($event['type'])) {
        throw new Exception('Evento webhook non valido');
    }

    // Log evento
    $logEntry = sprintf(
        "[%s] Webhook ricevuto: %s - ID: %s\n",
        date('Y-m-d H:i:s'),
        $event['type'],
        $event['id'] ?? 'N/A'
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Registra webhook nel database
    $stmt = $pdo->prepare('
        INSERT INTO payment_webhooks_log (
            gateway,
            event_type,
            event_id,
            payload,
            created_at
        ) VALUES (
            "stripe",
            :event_type,
            :event_id,
            :payload,
            CURRENT_TIMESTAMP
        )
    ');
    $stmt->execute([
        'event_type' => $event['type'],
        'event_id' => $event['id'] ?? null,
        'payload' => $payload
    ]);

    $webhookLogId = $pdo->lastInsertId();

    // Gestisci evento
    switch ($event['type']) {
        case 'payment_intent.succeeded':
            handlePaymentIntentSucceeded($pdo, $event['data']['object'], $webhookLogId);
            break;

        case 'payment_intent.payment_failed':
            handlePaymentIntentFailed($pdo, $event['data']['object'], $webhookLogId);
            break;

        case 'charge.refunded':
            handleChargeRefunded($pdo, $event['data']['object'], $webhookLogId);
            break;

        default:
            // Altri eventi non gestiti
            $logEntry = sprintf(
                "[%s] Evento non gestito: %s\n",
                date('Y-m-d H:i:s'),
                $event['type']
            );
            file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    // Aggiorna stato webhook
    $stmt = $pdo->prepare('
        UPDATE payment_webhooks_log
        SET processed = TRUE, processed_at = CURRENT_TIMESTAMP
        WHERE id = :id
    ');
    $stmt->execute(['id' => $webhookLogId]);

    http_response_code(200);
    echo json_encode(['received' => true]);

} catch (Exception $e) {
    $errorLog = sprintf(
        "[%s] ERRORE: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    file_put_contents($logFile, $errorLog, FILE_APPEND);

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Gestisce pagamento completato con successo
 */
function handlePaymentIntentSucceeded($pdo, $paymentIntent, $webhookLogId) {
    $paymentIntentId = $paymentIntent['id'];
    $importo = $paymentIntent['amount'] / 100; // Stripe usa centesimi
    $metadata = $paymentIntent['metadata'] ?? [];
    $fatturaId = (int)($metadata['fattura_id'] ?? 0);

    if (!$fatturaId) {
        throw new Exception('ID fattura mancante nei metadata');
    }

    $pdo->beginTransaction();

    try {
        // Aggiorna transazione
        $stmt = $pdo->prepare('
            UPDATE payment_transactions
            SET
                stato = "completed",
                importo_ricevuto = :importo,
                data_completamento = CURRENT_TIMESTAMP,
                webhook_log_id = :webhook_log_id
            WHERE gateway_transaction_id = :transaction_id
        ');
        $stmt->execute([
            'importo' => $importo,
            'webhook_log_id' => $webhookLogId,
            'transaction_id' => $paymentIntentId
        ]);

        // Registra pagamento fattura
        $stmt = $pdo->prepare('
            INSERT INTO fatture_pagamenti (
                fattura_id,
                importo,
                metodo_pagamento,
                data_pagamento,
                riferimento_transazione,
                note
            ) VALUES (
                :fattura_id,
                :importo,
                "stripe",
                CURRENT_TIMESTAMP,
                :transaction_id,
                "Pagamento ricevuto via Stripe"
            )
        ');
        $stmt->execute([
            'fattura_id' => $fatturaId,
            'importo' => $importo,
            'transaction_id' => $paymentIntentId
        ]);

        // Aggiorna stato fattura
        $stmt = $pdo->prepare('
            UPDATE fatture
            SET stato = "pagata", data_pagamento = CURRENT_TIMESTAMP
            WHERE id = :id AND stato != "pagata"
        ');
        $stmt->execute(['id' => $fatturaId]);

        $pdo->commit();

        // Log successo
        global $logFile;
        $logEntry = sprintf(
            "[%s] ✓ Pagamento completato - Fattura #%d - Importo: €%.2f\n",
            date('Y-m-d H:i:s'),
            $fatturaId,
            $importo
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Gestisce pagamento fallito
 */
function handlePaymentIntentFailed($pdo, $paymentIntent, $webhookLogId) {
    $paymentIntentId = $paymentIntent['id'];
    $errorMessage = $paymentIntent['last_payment_error']['message'] ?? 'Errore sconosciuto';

    // Aggiorna transazione
    $stmt = $pdo->prepare('
        UPDATE payment_transactions
        SET
            stato = "failed",
            errore = :errore,
            webhook_log_id = :webhook_log_id
        WHERE gateway_transaction_id = :transaction_id
    ');
    $stmt->execute([
        'errore' => $errorMessage,
        'webhook_log_id' => $webhookLogId,
        'transaction_id' => $paymentIntentId
    ]);

    // Log fallimento
    global $logFile;
    $logEntry = sprintf(
        "[%s] ✗ Pagamento fallito - ID: %s - Errore: %s\n",
        date('Y-m-d H:i:s'),
        $paymentIntentId,
        $errorMessage
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Gestisce rimborso
 */
function handleChargeRefunded($pdo, $charge, $webhookLogId) {
    $chargeId = $charge['id'];
    $importoRimborsato = $charge['amount_refunded'] / 100;
    $paymentIntentId = $charge['payment_intent'] ?? null;

    if (!$paymentIntentId) {
        return;
    }

    // Aggiorna transazione
    $stmt = $pdo->prepare('
        UPDATE payment_transactions
        SET
            stato = "refunded",
            importo_rimborsato = :importo_rimborsato,
            webhook_log_id = :webhook_log_id
        WHERE gateway_transaction_id = :transaction_id
    ');
    $stmt->execute([
        'importo_rimborsato' => $importoRimborsato,
        'webhook_log_id' => $webhookLogId,
        'transaction_id' => $paymentIntentId
    ]);

    // Log rimborso
    global $logFile;
    $logEntry = sprintf(
        "[%s] ↩ Rimborso - ID: %s - Importo: €%.2f\n",
        date('Y-m-d H:i:s'),
        $chargeId,
        $importoRimborsato
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
