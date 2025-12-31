<?php
/**
 * Webhook Handler per PayPal
 *
 * Riceve notifiche da PayPal per eventi di pagamento
 * URL da configurare su PayPal Developer Dashboard: https://tuosito.it/area-clienti/api/webhook-paypal.php
 */

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/payment-gateways.php';

// Leggi il payload
$payload = @file_get_contents('php://input');
$headers = getallheaders();

// Log webhook ricevuto
$logDir = __DIR__ . '/../cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/webhooks-paypal-' . date('Y-m') . '.log';

try {
    // Inizializza gateway PayPal
    $paypal = new PayPalGateway($pdo);

    // Verifica signature webhook (in produzione, decommentare questa parte)
    /*
    $webhookId = $paypal->getWebhookId();
    $isValid = $paypal->verificaWebhookSignature(
        $headers,
        $payload,
        $webhookId
    );

    if (!$isValid) {
        http_response_code(400);
        exit();
    }
    */

    // Decodifica payload
    $event = json_decode($payload, true);

    if (!$event || !isset($event['event_type'])) {
        throw new Exception('Evento webhook non valido');
    }

    // Log evento
    $logEntry = sprintf(
        "[%s] Webhook ricevuto: %s - ID: %s\n",
        date('Y-m-d H:i:s'),
        $event['event_type'],
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
            "paypal",
            :event_type,
            :event_id,
            :payload,
            CURRENT_TIMESTAMP
        )
    ');
    $stmt->execute([
        'event_type' => $event['event_type'],
        'event_id' => $event['id'] ?? null,
        'payload' => $payload
    ]);

    $webhookLogId = $pdo->lastInsertId();

    // Gestisci evento
    switch ($event['event_type']) {
        case 'PAYMENT.CAPTURE.COMPLETED':
            handlePaymentCaptureCompleted($pdo, $event['resource'], $webhookLogId);
            break;

        case 'PAYMENT.CAPTURE.DENIED':
        case 'PAYMENT.CAPTURE.DECLINED':
            handlePaymentCaptureFailed($pdo, $event['resource'], $webhookLogId);
            break;

        case 'PAYMENT.CAPTURE.REFUNDED':
            handlePaymentCaptureRefunded($pdo, $event['resource'], $webhookLogId);
            break;

        default:
            // Altri eventi non gestiti
            $logEntry = sprintf(
                "[%s] Evento non gestito: %s\n",
                date('Y-m-d H:i:s'),
                $event['event_type']
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
function handlePaymentCaptureCompleted($pdo, $capture, $webhookLogId) {
    $captureId = $capture['id'];
    $orderId = $capture['supplementary_data']['related_ids']['order_id'] ?? null;
    $importo = (float)$capture['amount']['value'];

    // Cerca transazione tramite order_id
    $stmt = $pdo->prepare('
        SELECT * FROM payment_transactions
        WHERE gateway_transaction_id = :order_id
    ');
    $stmt->execute(['order_id' => $orderId]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        throw new Exception('Transazione non trovata per order_id: ' . $orderId);
    }

    $fatturaId = $transaction['fattura_id'];

    $pdo->beginTransaction();

    try {
        // Aggiorna transazione
        $stmt = $pdo->prepare('
            UPDATE payment_transactions
            SET
                stato = "completed",
                importo_ricevuto = :importo,
                data_completamento = CURRENT_TIMESTAMP,
                webhook_log_id = :webhook_log_id,
                dettagli_gateway = :dettagli
            WHERE id = :id
        ');
        $stmt->execute([
            'importo' => $importo,
            'webhook_log_id' => $webhookLogId,
            'dettagli' => json_encode(['capture_id' => $captureId]),
            'id' => $transaction['id']
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
                "paypal",
                CURRENT_TIMESTAMP,
                :capture_id,
                "Pagamento ricevuto via PayPal"
            )
        ');
        $stmt->execute([
            'fattura_id' => $fatturaId,
            'importo' => $importo,
            'capture_id' => $captureId
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
function handlePaymentCaptureFailed($pdo, $capture, $webhookLogId) {
    $captureId = $capture['id'];
    $orderId = $capture['supplementary_data']['related_ids']['order_id'] ?? null;
    $errorMessage = $capture['status_details']['reason'] ?? 'Errore sconosciuto';

    // Cerca transazione
    $stmt = $pdo->prepare('
        SELECT id FROM payment_transactions
        WHERE gateway_transaction_id = :order_id
    ');
    $stmt->execute(['order_id' => $orderId]);
    $transaction = $stmt->fetch();

    if ($transaction) {
        // Aggiorna transazione
        $stmt = $pdo->prepare('
            UPDATE payment_transactions
            SET
                stato = "failed",
                errore = :errore,
                webhook_log_id = :webhook_log_id
            WHERE id = :id
        ');
        $stmt->execute([
            'errore' => $errorMessage,
            'webhook_log_id' => $webhookLogId,
            'id' => $transaction['id']
        ]);
    }

    // Log fallimento
    global $logFile;
    $logEntry = sprintf(
        "[%s] ✗ Pagamento fallito - Order ID: %s - Errore: %s\n",
        date('Y-m-d H:i:s'),
        $orderId,
        $errorMessage
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Gestisce rimborso
 */
function handlePaymentCaptureRefunded($pdo, $refund, $webhookLogId) {
    $refundId = $refund['id'];
    $captureId = $refund['links'][0]['href'] ?? null;
    $importoRimborsato = (float)$refund['amount']['value'];

    // Cerca transazione tramite capture_id nei dettagli
    $stmt = $pdo->prepare('
        SELECT * FROM payment_transactions
        WHERE JSON_EXTRACT(dettagli_gateway, "$.capture_id") = :capture_id
    ');
    $stmt->execute(['capture_id' => $captureId]);
    $transaction = $stmt->fetch();

    if ($transaction) {
        // Aggiorna transazione
        $stmt = $pdo->prepare('
            UPDATE payment_transactions
            SET
                stato = "refunded",
                importo_rimborsato = :importo_rimborsato,
                webhook_log_id = :webhook_log_id
            WHERE id = :id
        ');
        $stmt->execute([
            'importo_rimborsato' => $importoRimborsato,
            'webhook_log_id' => $webhookLogId,
            'id' => $transaction['id']
        ]);
    }

    // Log rimborso
    global $logFile;
    $logEntry = sprintf(
        "[%s] ↩ Rimborso - ID: %s - Importo: €%.2f\n",
        date('Y-m-d H:i:s'),
        $refundId,
        $importoRimborsato
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
