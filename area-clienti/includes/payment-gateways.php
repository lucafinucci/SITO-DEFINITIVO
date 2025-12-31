<?php
/**
 * Configurazione Payment Gateways
 */

class PaymentGateway {
    private $pdo;
    private $config;

    public function __construct($pdo, $gateway = 'stripe') {
        $this->pdo = $pdo;
        $this->loadConfig($gateway);
    }

    private function loadConfig($gateway) {
        $stmt = $this->pdo->prepare('SELECT * FROM payment_gateways_config WHERE gateway = :gateway');
        $stmt->execute(['gateway' => $gateway]);
        $this->config = $stmt->fetch();

        if (!$this->config) {
            throw new Exception("Gateway $gateway non configurato");
        }

        if (!$this->config['attivo']) {
            throw new Exception("Gateway $gateway non attivo");
        }
    }

    public function getConfig() {
        return $this->config;
    }

    public function isTestMode() {
        return $this->config['modalita'] === 'test';
    }

    public function getPublicKey() {
        return $this->config['api_key_public'];
    }

    public function getSecretKey() {
        return $this->config['api_key_secret'];
    }

    public function getWebhookSecret() {
        return $this->config['webhook_secret'];
    }

    public function calcolaCommissione($importo) {
        $percentuale = (float)$this->config['commissione_percentuale'];
        $fissa = (float)$this->config['commissione_fissa'];

        $commissionePerc = $importo * ($percentuale / 100);
        $commissioneTotale = $commissionePerc + $fissa;

        return round($commissioneTotale, 2);
    }

    public function registraTransazione($fatturaId, $dati) {
        $stmt = $this->pdo->prepare('
            INSERT INTO payment_transactions (
                fattura_id,
                gateway,
                transaction_id,
                payment_intent_id,
                importo,
                commissione,
                importo_netto,
                stato,
                currency,
                metodo_pagamento,
                card_last4,
                card_brand,
                metadata
            ) VALUES (
                :fattura_id,
                :gateway,
                :transaction_id,
                :payment_intent_id,
                :importo,
                :commissione,
                :importo_netto,
                :stato,
                :currency,
                :metodo_pagamento,
                :card_last4,
                :card_brand,
                :metadata
            )
        ');

        $stmt->execute([
            'fattura_id' => $fatturaId,
            'gateway' => $this->config['gateway'],
            'transaction_id' => $dati['transaction_id'] ?? null,
            'payment_intent_id' => $dati['payment_intent_id'] ?? null,
            'importo' => $dati['importo'],
            'commissione' => $dati['commissione'] ?? 0,
            'importo_netto' => $dati['importo_netto'],
            'stato' => $dati['stato'] ?? 'pending',
            'currency' => $dati['currency'] ?? 'EUR',
            'metodo_pagamento' => $dati['metodo_pagamento'] ?? null,
            'card_last4' => $dati['card_last4'] ?? null,
            'card_brand' => $dati['card_brand'] ?? null,
            'metadata' => json_encode($dati['metadata'] ?? [])
        ]);

        return $this->pdo->lastInsertId();
    }

    public function aggiornaTransazione($transactionId, $stato, $dati = []) {
        $sets = ['stato = :stato'];
        $params = ['stato' => $stato, 'id' => $transactionId];

        if (isset($dati['transaction_id'])) {
            $sets[] = 'transaction_id = :transaction_id';
            $params['transaction_id'] = $dati['transaction_id'];
        }

        if ($stato === 'completed') {
            $sets[] = 'completed_at = CURRENT_TIMESTAMP';
        }

        if (isset($dati['messaggio_errore'])) {
            $sets[] = 'messaggio_errore = :messaggio_errore';
            $params['messaggio_errore'] = $dati['messaggio_errore'];
        }

        $sql = 'UPDATE payment_transactions SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function logWebhook($gateway, $eventType, $eventId, $payload) {
        $stmt = $this->pdo->prepare('
            INSERT INTO payment_webhooks_log (
                gateway,
                event_type,
                event_id,
                payload,
                ip_address
            ) VALUES (
                :gateway,
                :event_type,
                :event_id,
                :payload,
                :ip_address
            )
        ');

        $stmt->execute([
            'gateway' => $gateway,
            'event_type' => $eventType,
            'event_id' => $eventId,
            'payload' => json_encode($payload),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    public function marcaWebhookProcessato($webhookId) {
        $stmt = $this->pdo->prepare('
            UPDATE payment_webhooks_log
            SET processed = TRUE, processed_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
        $stmt->execute(['id' => $webhookId]);
    }
}

class StripeGateway extends PaymentGateway {
    private $stripe;

    public function __construct($pdo) {
        parent::__construct($pdo, 'stripe');

        // Carica Stripe SDK (se installato via Composer)
        // require_once 'vendor/autoload.php';
        // \Stripe\Stripe::setApiKey($this->getSecretKey());
    }

    public function creaPaymentIntent($fatturaId, $importo, $metadata = []) {
        // Esempio implementazione con Stripe SDK
        // In produzione, decommentare dopo installazione SDK

        /*
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $importo * 100, // Stripe usa centesimi
            'currency' => 'eur',
            'metadata' => array_merge($metadata, [
                'fattura_id' => $fatturaId
            ]),
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        $commissione = $this->calcolaCommissione($importo);

        $this->registraTransazione($fatturaId, [
            'payment_intent_id' => $paymentIntent->id,
            'importo' => $importo,
            'commissione' => $commissione,
            'importo_netto' => $importo - $commissione,
            'stato' => 'pending',
            'metadata' => $metadata
        ]);

        return $paymentIntent;
        */

        // Placeholder per test senza SDK
        $fakePaymentIntent = [
            'id' => 'pi_test_' . uniqid(),
            'client_secret' => 'pi_test_secret_' . uniqid(),
            'amount' => $importo * 100,
            'currency' => 'eur',
            'status' => 'requires_payment_method'
        ];

        $commissione = $this->calcolaCommissione($importo);

        $this->registraTransazione($fatturaId, [
            'payment_intent_id' => $fakePaymentIntent['id'],
            'importo' => $importo,
            'commissione' => $commissione,
            'importo_netto' => $importo - $commissione,
            'stato' => 'pending',
            'metadata' => $metadata
        ]);

        return $fakePaymentIntent;
    }
}

class PayPalGateway extends PaymentGateway {
    public function __construct($pdo) {
        parent::__construct($pdo, 'paypal');
    }

    public function creaOrdine($fatturaId, $importo, $metadata = []) {
        // Implementazione PayPal SDK
        // In produzione, usare PayPal REST SDK

        /*
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "fattura_$fatturaId",
                "amount" => [
                    "currency_code" => "EUR",
                    "value" => number_format($importo, 2, '.', '')
                ]
            ]],
            "application_context" => [
                "return_url" => "https://yourdomain.com/payment/success",
                "cancel_url" => "https://yourdomain.com/payment/cancel"
            ]
        ];

        $client = PayPalClient::client();
        $response = $client->execute($request);

        $commissione = $this->calcolaCommissione($importo);

        $this->registraTransazione($fatturaId, [
            'transaction_id' => $response->result->id,
            'importo' => $importo,
            'commissione' => $commissione,
            'importo_netto' => $importo - $commissione,
            'stato' => 'pending',
            'metadata' => $metadata
        ]);

        return $response->result;
        */

        // Placeholder
        $fakeOrder = [
            'id' => 'ORDER-' . uniqid(),
            'status' => 'CREATED',
            'links' => [
                ['rel' => 'approve', 'href' => 'https://www.paypal.com/checkoutnow?token=fake']
            ]
        ];

        $commissione = $this->calcolaCommissione($importo);

        $this->registraTransazione($fatturaId, [
            'transaction_id' => $fakeOrder['id'],
            'importo' => $importo,
            'commissione' => $commissione,
            'importo_netto' => $importo - $commissione,
            'stato' => 'pending',
            'metadata' => $metadata
        ]);

        return $fakeOrder;
    }
}
