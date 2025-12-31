<?php
/**
 * Email Manager - Sistema di gestione email e template
 */

class EmailManager {
    private $pdo;
    private $defaultFrom = 'noreply@finch-ai.it';
    private $defaultFromName = 'Finch-AI';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Carica template per codice
     */
    public function getTemplate($codice) {
        $stmt = $this->pdo->prepare('
            SELECT * FROM email_templates
            WHERE codice = :codice AND attivo = TRUE
            LIMIT 1
        ');
        $stmt->execute(['codice' => $codice]);
        return $stmt->fetch();
    }

    /**
     * Sostituisce variabili nel template
     */
    public function renderTemplate($template, $variabili) {
        $oggetto = $template['oggetto'];
        $corpoHtml = $template['corpo_html'];
        $corpoTesto = $template['corpo_testo'];

        // Sostituisci variabili
        foreach ($variabili as $chiave => $valore) {
            $placeholder = '{' . $chiave . '}';
            $oggetto = str_replace($placeholder, $valore, $oggetto);
            $corpoHtml = str_replace($placeholder, $valore, $corpoHtml);
            if ($corpoTesto) {
                $corpoTesto = str_replace($placeholder, $valore, $corpoTesto);
            }
        }

        return [
            'oggetto' => $oggetto,
            'corpo_html' => $corpoHtml,
            'corpo_testo' => $corpoTesto
        ];
    }

    /**
     * Invia email usando template
     */
    public function sendFromTemplate($codiceTemplate, $destinatario, $variabili, $opzioni = []) {
        // Carica template
        $template = $this->getTemplate($codiceTemplate);

        if (!$template) {
            throw new Exception("Template '$codiceTemplate' non trovato");
        }

        // Render template con variabili
        $rendered = $this->renderTemplate($template, $variabili);

        // Prepara dati email
        $emailData = [
            'template_id' => $template['id'],
            'destinatario_email' => $destinatario['email'],
            'destinatario_nome' => $destinatario['nome'] ?? null,
            'oggetto' => $rendered['oggetto'],
            'corpo_html' => $rendered['corpo_html'],
            'corpo_testo' => $rendered['corpo_testo'],
            'mittente_email' => $template['mittente_email'] ?? $this->defaultFrom,
            'mittente_nome' => $template['mittente_nome'] ?? $this->defaultFromName,
            'reply_to' => $template['reply_to'],
            'cliente_id' => $opzioni['cliente_id'] ?? null,
            'fattura_id' => $opzioni['fattura_id'] ?? null,
            'variabili' => json_encode($variabili),
            'priorita' => $opzioni['priorita'] ?? 'normale',
            'data_pianificazione' => $opzioni['data_pianificazione'] ?? null
        ];

        // Aggiungi a coda
        return $this->addToQueue($emailData);
    }

    /**
     * Aggiungi email alla coda
     */
    public function addToQueue($emailData) {
        $stmt = $this->pdo->prepare('
            INSERT INTO email_queue (
                template_id,
                destinatario_email,
                destinatario_nome,
                oggetto,
                corpo_html,
                corpo_testo,
                mittente_email,
                mittente_nome,
                reply_to,
                cliente_id,
                fattura_id,
                variabili,
                priorita,
                data_pianificazione
            ) VALUES (
                :template_id,
                :destinatario_email,
                :destinatario_nome,
                :oggetto,
                :corpo_html,
                :corpo_testo,
                :mittente_email,
                :mittente_nome,
                :reply_to,
                :cliente_id,
                :fattura_id,
                :variabili,
                :priorita,
                :data_pianificazione
            )
        ');

        $stmt->execute($emailData);
        return $this->pdo->lastInsertId();
    }

    /**
     * Processa coda email (da usare in CRON)
     */
    public function processQueue($limite = 50) {
        // Recupera email da inviare
        $stmt = $this->pdo->prepare('
            SELECT * FROM email_queue
            WHERE stato = "in_coda"
              AND tentativi < max_tentativi
              AND (data_pianificazione IS NULL OR data_pianificazione <= NOW())
            ORDER BY
                CASE priorita
                    WHEN "urgente" THEN 1
                    WHEN "alta" THEN 2
                    WHEN "normale" THEN 3
                    WHEN "bassa" THEN 4
                END,
                created_at ASC
            LIMIT :limite
        ');
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $emails = $stmt->fetchAll();

        $inviati = 0;
        $falliti = 0;

        foreach ($emails as $email) {
            // Marca come processing
            $this->updateQueueStatus($email['id'], 'processing');

            try {
                // Invia email
                $successo = $this->sendEmail([
                    'to' => $email['destinatario_email'],
                    'to_name' => $email['destinatario_nome'],
                    'from' => $email['mittente_email'],
                    'from_name' => $email['mittente_nome'],
                    'reply_to' => $email['reply_to'],
                    'subject' => $email['oggetto'],
                    'html' => $email['corpo_html'],
                    'text' => $email['corpo_testo']
                ]);

                if ($successo) {
                    // Marca come completata
                    $this->updateQueueStatus($email['id'], 'completata');

                    // Registra in log
                    $this->logEmail([
                        'template_id' => $email['template_id'],
                        'cliente_id' => $email['cliente_id'],
                        'fattura_id' => $email['fattura_id'],
                        'destinatario_email' => $email['destinatario_email'],
                        'destinatario_nome' => $email['destinatario_nome'],
                        'oggetto' => $email['oggetto'],
                        'corpo_html' => $email['corpo_html'],
                        'corpo_testo' => $email['corpo_testo'],
                        'mittente_email' => $email['mittente_email'],
                        'mittente_nome' => $email['mittente_nome'],
                        'variabili_utilizzate' => $email['variabili'],
                        'stato' => 'inviata'
                    ]);

                    $inviati++;
                } else {
                    throw new Exception('Invio fallito');
                }

            } catch (Exception $e) {
                // Incrementa tentativi
                $nuoviTentativi = $email['tentativi'] + 1;

                if ($nuoviTentativi >= $email['max_tentativi']) {
                    // Troppi tentativi, marca come fallita
                    $this->updateQueueStatus($email['id'], 'fallita', $e->getMessage());
                    $this->logEmail([
                        'template_id' => $email['template_id'],
                        'cliente_id' => $email['cliente_id'],
                        'destinatario_email' => $email['destinatario_email'],
                        'oggetto' => $email['oggetto'],
                        'corpo_html' => $email['corpo_html'],
                        'mittente_email' => $email['mittente_email'],
                        'stato' => 'fallita',
                        'errore' => $e->getMessage()
                    ]);
                } else {
                    // Riprova dopo
                    $this->updateQueueTentativi($email['id'], $nuoviTentativi, $e->getMessage());
                }

                $falliti++;
            }

            // Pausa tra invii
            sleep(1);
        }

        return [
            'processati' => count($emails),
            'inviati' => $inviati,
            'falliti' => $falliti
        ];
    }

    /**
     * Invia email effettiva
     */
    private function sendEmail($data) {
        // Header email
        $headers = [
            'From' => sprintf('%s <%s>', $data['from_name'] ?? 'Finch-AI', $data['from']),
            'Reply-To' => $data['reply_to'] ?? $data['from'],
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];

        $headersString = '';
        foreach ($headers as $key => $value) {
            $headersString .= "$key: $value\r\n";
        }

        // Invia con PHP mail() (per produzione usa PHPMailer/SMTP)
        return mail(
            $data['to'],
            $data['subject'],
            $data['html'],
            $headersString
        );

        /* Produzione con PHPMailer:
        require 'vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-password';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom($data['from'], $data['from_name']);
        $mail->addAddress($data['to'], $data['to_name']);
        if ($data['reply_to']) {
            $mail->addReplyTo($data['reply_to']);
        }
        $mail->Subject = $data['subject'];
        $mail->Body = $data['html'];
        $mail->AltBody = $data['text'];
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        return $mail->send();
        */
    }

    /**
     * Registra email nel log
     */
    private function logEmail($data) {
        $stmt = $this->pdo->prepare('
            INSERT INTO email_log (
                template_id,
                cliente_id,
                fattura_id,
                destinatario_email,
                destinatario_nome,
                oggetto,
                corpo_html,
                corpo_testo,
                mittente_email,
                mittente_nome,
                stato,
                errore,
                data_invio,
                variabili_utilizzate
            ) VALUES (
                :template_id,
                :cliente_id,
                :fattura_id,
                :destinatario_email,
                :destinatario_nome,
                :oggetto,
                :corpo_html,
                :corpo_testo,
                :mittente_email,
                :mittente_nome,
                :stato,
                :errore,
                CURRENT_TIMESTAMP,
                :variabili_utilizzate
            )
        ');

        $stmt->execute([
            'template_id' => $data['template_id'] ?? null,
            'cliente_id' => $data['cliente_id'] ?? null,
            'fattura_id' => $data['fattura_id'] ?? null,
            'destinatario_email' => $data['destinatario_email'],
            'destinatario_nome' => $data['destinatario_nome'] ?? null,
            'oggetto' => $data['oggetto'],
            'corpo_html' => $data['corpo_html'],
            'corpo_testo' => $data['corpo_testo'] ?? null,
            'mittente_email' => $data['mittente_email'],
            'mittente_nome' => $data['mittente_nome'] ?? null,
            'stato' => $data['stato'],
            'errore' => $data['errore'] ?? null,
            'variabili_utilizzate' => $data['variabili_utilizzate'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Aggiorna stato coda
     */
    private function updateQueueStatus($id, $stato, $errore = null) {
        $stmt = $this->pdo->prepare('
            UPDATE email_queue
            SET stato = :stato,
                errore = :errore,
                processed_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
        $stmt->execute([
            'id' => $id,
            'stato' => $stato,
            'errore' => $errore
        ]);
    }

    /**
     * Aggiorna tentativi coda
     */
    private function updateQueueTentativi($id, $tentativi, $errore = null) {
        $stmt = $this->pdo->prepare('
            UPDATE email_queue
            SET tentativi = :tentativi,
                errore = :errore,
                stato = "in_coda"
            WHERE id = :id
        ');
        $stmt->execute([
            'id' => $id,
            'tentativi' => $tentativi,
            'errore' => $errore
        ]);
    }

    /**
     * Ottieni statistiche email
     */
    public function getStatistics($periodo = 30) {
        $stmt = $this->pdo->prepare('
            SELECT
                COUNT(*) AS totale,
                SUM(CASE WHEN stato = "inviata" THEN 1 ELSE 0 END) AS inviate,
                SUM(CASE WHEN stato = "fallita" THEN 1 ELSE 0 END) AS fallite,
                SUM(CASE WHEN stato = "aperta" THEN 1 ELSE 0 END) AS aperte,
                SUM(CASE WHEN stato = "click" THEN 1 ELSE 0 END) AS click,
                ROUND(SUM(CASE WHEN stato = "aperta" THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 2) AS tasso_apertura,
                ROUND(SUM(CASE WHEN stato = "click" THEN 1 ELSE 0 END) * 100.0 / NULLIF(SUM(CASE WHEN stato = "aperta" THEN 1 ELSE 0 END), 0), 2) AS tasso_click
            FROM email_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :periodo DAY)
        ');
        $stmt->execute(['periodo' => $periodo]);
        return $stmt->fetch();
    }
}

/**
 * Shortcut per invio email benvenuto
 */
function sendWelcomeEmail($pdo, $cliente) {
    $emailManager = new EmailManager($pdo);

    $variabili = [
        'nome_cliente' => $cliente['nome'] . ' ' . $cliente['cognome'],
        'email' => $cliente['email'],
        'azienda' => $cliente['azienda'],
        'link_area_clienti' => 'https://finch-ai.it/area-clienti/login.php'
    ];

    return $emailManager->sendFromTemplate(
        'benvenuto-cliente',
        ['email' => $cliente['email'], 'nome' => $cliente['nome']],
        $variabili,
        ['cliente_id' => $cliente['id']]
    );
}

/**
 * Shortcut per invio notifica fattura
 */
function sendInvoiceEmail($pdo, $fattura, $cliente) {
    $emailManager = new EmailManager($pdo);

    $variabili = [
        'azienda' => $cliente['azienda'],
        'numero_fattura' => $fattura['numero_fattura'],
        'data_emissione' => date('d/m/Y', strtotime($fattura['data_emissione'])),
        'data_scadenza' => date('d/m/Y', strtotime($fattura['data_scadenza'])),
        'imponibile' => number_format($fattura['imponibile'], 2, ',', '.'),
        'iva_percentuale' => number_format($fattura['iva_percentuale'], 0),
        'iva_importo' => number_format($fattura['iva_importo'], 2, ',', '.'),
        'totale' => number_format($fattura['totale'], 2, ',', '.'),
        'link_paga' => 'https://finch-ai.it/area-clienti/paga-fattura.php?id=' . $fattura['id'],
        'link_pdf' => 'https://finch-ai.it/area-clienti/api/genera-pdf-fattura.php?id=' . $fattura['id']
    ];

    return $emailManager->sendFromTemplate(
        'fattura-emessa',
        ['email' => $cliente['email'], 'nome' => $cliente['azienda']],
        $variabili,
        [
            'cliente_id' => $cliente['id'],
            'fattura_id' => $fattura['id']
        ]
    );
}
