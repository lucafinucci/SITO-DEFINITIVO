<?php
/**
 * SMS Manager - Gestione invio SMS multi-provider
 * Supporta: Twilio, Vonage (Nexmo), AWS SNS
 */

class SMSManager {
    private $pdo;
    private $config;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadConfig();
    }

    /**
     * Carica configurazione SMS gateway
     */
    private function loadConfig() {
        $stmt = $this->pdo->prepare('
            SELECT * FROM sms_config WHERE attivo = TRUE LIMIT 1
        ');
        $stmt->execute();
        $this->config = $stmt->fetch();

        if (!$this->config) {
            throw new Exception('Nessuna configurazione SMS attiva trovata');
        }
    }

    /**
     * Invia SMS usando template
     */
    public function sendFromTemplate($codiceTemplate, $destinatario, $variabili, $opzioni = []) {
        try {
            // Carica template
            $template = $this->getTemplate($codiceTemplate);

            if (!$template || !$template['attivo']) {
                throw new Exception("Template SMS '$codiceTemplate' non trovato o non attivo");
            }

            // Render messaggio
            $messaggio = $this->renderTemplate($template, $variabili);

            // Valida numero telefono
            $numeroValidato = $this->validaTelefono($destinatario['telefono']);

            if (!$numeroValidato) {
                throw new Exception("Numero telefono non valido: {$destinatario['telefono']}");
            }

            // Invia SMS
            $result = $this->send(
                $numeroValidato,
                $messaggio,
                array_merge($opzioni, [
                    'template_id' => $template['id'],
                    'destinatario_nome' => $destinatario['nome'] ?? null
                ])
            );

            return $result;

        } catch (Exception $e) {
            error_log("Errore invio SMS da template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invia SMS diretto
     */
    public function send($numeroDestinatario, $messaggio, $opzioni = []) {
        try {
            // Valida messaggio (max 160 caratteri per SMS singolo)
            if (strlen($messaggio) > 160) {
                error_log("Attenzione: SMS supera 160 caratteri, verrà diviso in parti multiple");
            }

            // Log in database
            $logId = $this->logSMS([
                'notifica_id' => $opzioni['notifica_id'] ?? null,
                'destinatario_numero' => $numeroDestinatario,
                'destinatario_nome' => $opzioni['destinatario_nome'] ?? null,
                'messaggio' => $messaggio,
                'cliente_id' => $opzioni['cliente_id'] ?? null,
                'fattura_id' => $opzioni['fattura_id'] ?? null,
                'provider' => $this->config['provider']
            ]);

            // Invia tramite provider
            $result = $this->sendViaProvider($numeroDestinatario, $messaggio);

            // Aggiorna log con risultato
            $this->updateLogStatus($logId, [
                'stato' => $result['success'] ? 'sent' : 'failed',
                'message_id' => $result['message_id'] ?? null,
                'errore' => $result['error'] ?? null,
                'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null
            ]);

            return $result['success'];

        } catch (Exception $e) {
            error_log("Errore invio SMS: " . $e->getMessage());

            // Aggiorna log con errore
            if (isset($logId)) {
                $this->updateLogStatus($logId, [
                    'stato' => 'failed',
                    'errore' => $e->getMessage()
                ]);
            }

            return false;
        }
    }

    /**
     * Invia SMS tramite provider configurato
     */
    private function sendViaProvider($numero, $messaggio) {
        switch ($this->config['provider']) {
            case 'twilio':
                return $this->sendViaTwilio($numero, $messaggio);

            case 'vonage':
                return $this->sendViaVonage($numero, $messaggio);

            case 'aws_sns':
                return $this->sendViaAWS($numero, $messaggio);

            default:
                throw new Exception("Provider SMS non supportato: {$this->config['provider']}");
        }
    }

    /**
     * Invio tramite Twilio
     */
    private function sendViaTwilio($numero, $messaggio) {
        $accountSid = $this->config['api_key'];
        $authToken = $this->config['api_secret'];
        $from = $this->config['sender_number'];

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $data = [
            'From' => $from,
            'To' => $numero,
            'Body' => $messaggio
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 201 && isset($result['sid'])) {
            return [
                'success' => true,
                'message_id' => $result['sid'],
                'provider_response' => $result
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['message'] ?? 'Errore sconosciuto Twilio',
                'provider_response' => $result
            ];
        }
    }

    /**
     * Invio tramite Vonage (Nexmo)
     */
    private function sendViaVonage($numero, $messaggio) {
        $apiKey = $this->config['api_key'];
        $apiSecret = $this->config['api_secret'];
        $from = $this->config['sender_number'];

        $url = 'https://rest.nexmo.com/sms/json';

        $data = [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'from' => $from,
            'to' => $numero,
            'text' => $messaggio
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['messages'][0]['status']) && $result['messages'][0]['status'] === '0') {
            return [
                'success' => true,
                'message_id' => $result['messages'][0]['message-id'],
                'provider_response' => $result
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['messages'][0]['error-text'] ?? 'Errore sconosciuto Vonage',
                'provider_response' => $result
            ];
        }
    }

    /**
     * Invio tramite AWS SNS
     */
    private function sendViaAWS($numero, $messaggio) {
        // Richiede AWS SDK for PHP
        // composer require aws/aws-sdk-php

        if (!class_exists('Aws\Sns\SnsClient')) {
            return [
                'success' => false,
                'error' => 'AWS SDK non installato. Eseguire: composer require aws/aws-sdk-php'
            ];
        }

        try {
            $sns = new \Aws\Sns\SnsClient([
                'region' => 'eu-west-1', // Modifica in base alla tua region
                'version' => 'latest',
                'credentials' => [
                    'key' => $this->config['api_key'],
                    'secret' => $this->config['api_secret']
                ]
            ]);

            $result = $sns->publish([
                'PhoneNumber' => $numero,
                'Message' => $messaggio
            ]);

            return [
                'success' => true,
                'message_id' => $result['MessageId'],
                'provider_response' => $result->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Recupera template SMS
     */
    public function getTemplate($codice) {
        $stmt = $this->pdo->prepare('
            SELECT * FROM sms_templates WHERE codice = :codice
        ');
        $stmt->execute(['codice' => $codice]);
        return $stmt->fetch();
    }

    /**
     * Render template con variabili
     */
    public function renderTemplate($template, $variabili) {
        $messaggio = $template['messaggio'];

        foreach ($variabili as $chiave => $valore) {
            $messaggio = str_replace('{' . $chiave . '}', $valore, $messaggio);
        }

        return $messaggio;
    }

    /**
     * Valida e formatta numero telefono (formato internazionale)
     */
    public function validaTelefono($numero) {
        // Rimuovi spazi, trattini, parentesi
        $numero = preg_replace('/[\s\-\(\)]/','', $numero);

        // Se non inizia con +, aggiungi prefisso Italia
        if (!str_starts_with($numero, '+')) {
            // Se inizia con 39, aggiungi solo +
            if (str_starts_with($numero, '39')) {
                $numero = '+' . $numero;
            } else {
                // Altrimenti aggiungi +39
                $numero = '+39' . $numero;
            }
        }

        // Valida formato base (minimo 10 cifre dopo il +)
        if (!preg_match('/^\+\d{10,15}$/', $numero)) {
            return false;
        }

        return $numero;
    }

    /**
     * Log SMS nel database
     */
    private function logSMS($dati) {
        $stmt = $this->pdo->prepare('
            INSERT INTO sms_log (
                notifica_id,
                destinatario_numero,
                destinatario_nome,
                messaggio,
                stato,
                provider,
                cliente_id,
                fattura_id
            ) VALUES (
                :notifica_id,
                :destinatario_numero,
                :destinatario_nome,
                :messaggio,
                :stato,
                :provider,
                :cliente_id,
                :fattura_id
            )
        ');

        $stmt->execute([
            'notifica_id' => $dati['notifica_id'],
            'destinatario_numero' => $dati['destinatario_numero'],
            'destinatario_nome' => $dati['destinatario_nome'],
            'messaggio' => $dati['messaggio'],
            'stato' => 'pending',
            'provider' => $dati['provider'],
            'cliente_id' => $dati['cliente_id'],
            'fattura_id' => $dati['fattura_id']
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Aggiorna stato log SMS
     */
    private function updateLogStatus($logId, $dati) {
        $fields = [];
        $params = ['id' => $logId];

        foreach ($dati as $field => $value) {
            $fields[] = "$field = :$field";
            $params[$field] = $value;
        }

        $sql = 'UPDATE sms_log SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Ottieni statistiche SMS
     */
    public function getStatistiche($periodo = 30) {
        $stmt = $this->pdo->prepare('
            SELECT * FROM v_sms_statistiche
            WHERE data >= DATE_SUB(CURDATE(), INTERVAL :periodo DAY)
            ORDER BY data DESC
        ');
        $stmt->execute(['periodo' => $periodo]);
        return $stmt->fetchAll();
    }

    /**
     * Conta SMS in coda
     */
    public function contaInCoda() {
        $stmt = $this->pdo->query('
            SELECT COUNT(*) FROM sms_log WHERE stato = "pending"
        ');
        return (int)$stmt->fetchColumn();
    }
}

/**
 * Helper function per invio rapido SMS
 */
function sendSMS($pdo, $numero, $messaggio, $opzioni = []) {
    try {
        $smsManager = new SMSManager($pdo);
        return $smsManager->send($numero, $messaggio, $opzioni);
    } catch (Exception $e) {
        error_log("Errore helper sendSMS: " . $e->getMessage());
        return false;
    }
}
