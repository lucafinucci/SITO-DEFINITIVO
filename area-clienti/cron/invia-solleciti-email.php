<?php
/**
 * Script CRON per invio automatico solleciti email
 *
 * Invia email di sollecito per fatture scadute
 *
 * Configurazione CRON consigliata:
 * 0 9 * * * php /path/to/invia-solleciti-email.php
 * (Esegui ogni giorno alle 09:00)
 */

// Verifica che lo script sia eseguito da CLI
if (php_sapi_name() !== 'cli') {
    die('Questo script può essere eseguito solo da command line');
}

require __DIR__ . '/../includes/db.php';

echo "[" . date('Y-m-d H:i:s') . "] Avvio invio solleciti email\n";

try {
    // Recupera configurazione
    $stmt = $pdo->prepare('SELECT * FROM solleciti_config LIMIT 1');
    $stmt->execute();
    $config = $stmt->fetch();

    if (!$config || !$config['solleciti_automatici_attivi']) {
        echo "Solleciti automatici disattivati. Uscita.\n";
        exit(0);
    }

    // Recupera solleciti da inviare
    $stmt = $pdo->prepare('
        SELECT
            s.id AS sollecito_id,
            s.tipo,
            s.numero_sollecito,
            s.oggetto,
            s.messaggio,
            f.id AS fattura_id,
            f.numero_fattura,
            f.totale,
            f.data_emissione,
            f.data_scadenza,
            DATEDIFF(CURDATE(), f.data_scadenza) AS giorni_ritardo,
            u.id AS cliente_id,
            u.azienda,
            u.nome AS cliente_nome,
            u.cognome AS cliente_cognome,
            u.email AS cliente_email
        FROM fatture_solleciti s
        JOIN fatture f ON s.fattura_id = f.id
        JOIN utenti u ON f.cliente_id = u.id
        WHERE s.stato = "da_inviare"
          AND f.stato = "scaduta"
        ORDER BY s.numero_sollecito ASC, s.data_creazione ASC
        LIMIT 50
    ');
    $stmt->execute();
    $solleciti = $stmt->fetchAll();

    echo "Trovati " . count($solleciti) . " solleciti da inviare\n\n";

    $inviati = 0;
    $errori = [];

    foreach ($solleciti as $sollecito) {
        try {
            // Seleziona template appropriato
            $template = null;
            switch ($sollecito['tipo']) {
                case 'primo_sollecito':
                    $template = $config['template_primo_sollecito'];
                    break;
                case 'secondo_sollecito':
                    $template = $config['template_secondo_sollecito'];
                    break;
                case 'sollecito_urgente':
                case 'ultimo_avviso':
                    $template = $config['template_sollecito_urgente'];
                    break;
            }

            // Personalizza messaggio
            $messaggio = $sollecito['messaggio'] ?: $template;
            $messaggio = personalizzaMessaggio($messaggio, $sollecito);

            // Invia email
            $oggetto = personalizzaMessaggio($sollecito['oggetto'], $sollecito);
            $successo = inviaEmailSollecito(
                $sollecito['cliente_email'],
                $oggetto,
                $messaggio,
                $config['email_mittente'],
                $config['nome_mittente']
            );

            if ($successo) {
                // Aggiorna stato sollecito
                $stmt = $pdo->prepare('
                    UPDATE fatture_solleciti
                    SET stato = "inviato",
                        data_invio = CURRENT_TIMESTAMP,
                        metodo_invio = "email"
                    WHERE id = :id
                ');
                $stmt->execute(['id' => $sollecito['sollecito_id']]);

                $inviati++;
                echo "  ✓ Sollecito #{$sollecito['numero_sollecito']} inviato: " .
                     "{$sollecito['azienda']} - Fattura {$sollecito['numero_fattura']}\n";
            } else {
                throw new Exception("Invio email fallito");
            }

        } catch (Exception $e) {
            $errore = "Errore sollecito #{$sollecito['sollecito_id']}: " . $e->getMessage();
            $errori[] = $errore;
            echo "  ✗ $errore\n";
        }

        // Pausa di 1 secondo tra invii per evitare problemi SMTP
        sleep(1);
    }

    echo "\n=== Riepilogo ===\n";
    echo "Solleciti processati: " . count($solleciti) . "\n";
    echo "Inviati con successo: $inviati\n";
    echo "Errori: " . count($errori) . "\n";

    if (!empty($errori)) {
        echo "\nErrori rilevati:\n";
        foreach ($errori as $errore) {
            echo "  - $errore\n";
        }
    }

    echo "\n[" . date('Y-m-d H:i:s') . "] Completato\n";

    // Log su file
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/solleciti-email-' . date('Y-m') . '.log';
    $logContent = sprintf(
        "[%s] Invio completato - Processati: %d - Inviati: %d - Errori: %d\n",
        date('Y-m-d H:i:s'),
        count($solleciti),
        $inviati,
        count($errori)
    );
    file_put_contents($logFile, $logContent, FILE_APPEND);

} catch (Exception $e) {
    echo "ERRORE CRITICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

function personalizzaMessaggio($template, $dati) {
    $replacements = [
        '{numero_fattura}' => $dati['numero_fattura'],
        '{totale}' => '€ ' . number_format($dati['totale'], 2, ',', '.'),
        '{data_emissione}' => date('d/m/Y', strtotime($dati['data_emissione'])),
        '{data_scadenza}' => date('d/m/Y', strtotime($dati['data_scadenza'])),
        '{giorni_ritardo}' => $dati['giorni_ritardo'],
        '{azienda}' => $dati['azienda'],
        '{cliente_nome}' => $dati['cliente_nome'] . ' ' . $dati['cliente_cognome']
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $template);
}

function inviaEmailSollecito($destinatario, $oggetto, $messaggio, $mittente, $nomeMittente) {
    // Header email
    $headers = [
        'From' => "$nomeMittente <$mittente>",
        'Reply-To' => $mittente,
        'X-Mailer' => 'PHP/' . phpversion(),
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=UTF-8'
    ];

    // Converti messaggio in HTML
    $messaggioHtml = '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #8b5cf6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background: #e9ecef;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-radius: 0 0 8px 8px;
        }
        .highlight {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #8b5cf6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Finch-AI</h2>
        <p>Amministrazione</p>
    </div>
    <div class="content">
        ' . nl2br(htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8')) . '
    </div>
    <div class="footer">
        <p>Questa è una comunicazione automatica. Per assistenza: fatturazione@finch-ai.it</p>
        <p>© ' . date('Y') . ' Finch-AI - Tutti i diritti riservati</p>
    </div>
</body>
</html>';

    // Converti array headers in stringa
    $headersString = '';
    foreach ($headers as $key => $value) {
        $headersString .= "$key: $value\r\n";
    }

    // Invia email
    $result = mail($destinatario, $oggetto, $messaggioHtml, $headersString);

    // Per produzione, usa una libreria SMTP come PHPMailer:
    /*
    require 'vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';
    $mail->Password = 'your-password';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom($mittente, $nomeMittente);
    $mail->addAddress($destinatario);
    $mail->Subject = $oggetto;
    $mail->Body = $messaggioHtml;
    $mail->isHTML(true);
    return $mail->send();
    */

    return $result;
}
