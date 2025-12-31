<?php

function sendFatturaEmail(PDO $pdo, $fatturaId) {
    $fatturaId = (int)$fatturaId;
    if ($fatturaId <= 0) {
        return ['success' => false, 'error' => 'ID fattura non valido'];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'fatture'
              AND COLUMN_NAME IN ('anno', 'mese', 'cliente_id', 'user_id', 'imponibile', 'importo_netto', 'iva_importo', 'iva', 'iva_percentuale', 'totale', 'importo_totale')
        ");
        $stmt->execute();
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $hasAnno = in_array('anno', $cols, true);
        $hasMese = in_array('mese', $cols, true);
        $hasClienteId = in_array('cliente_id', $cols, true);
        $hasImponibile = in_array('imponibile', $cols, true);
        $hasIvaImporto = in_array('iva_importo', $cols, true);
        $hasIvaPerc = in_array('iva_percentuale', $cols, true);
        $hasTotale = in_array('totale', $cols, true);

        $colCliente = $hasClienteId ? 'cliente_id' : 'user_id';
        $colImponibile = $hasImponibile ? 'imponibile' : 'importo_netto';
        $colIva = $hasIvaImporto ? 'iva_importo' : 'iva';
        $colTotale = $hasTotale ? 'totale' : 'importo_totale';

        $stmt = $pdo->prepare("
            SELECT
                f.*,
                " . ($hasAnno ? 'f.anno' : 'YEAR(f.data_emissione) AS anno') . ",
                " . ($hasMese ? 'f.mese' : 'MONTH(f.data_emissione) AS mese') . ",
                f.$colImponibile AS imponibile,
                " . ($hasIvaPerc ? 'f.iva_percentuale' : '22.00 AS iva_percentuale') . ",
                f.$colIva AS iva_importo,
                f.$colTotale AS totale,
                u.azienda,
                u.nome AS cliente_nome,
                u.cognome AS cliente_cognome,
                u.email AS cliente_email,
                u.telefono
            FROM fatture f
            JOIN utenti u ON f.$colCliente = u.id
            WHERE f.id = :id
        ");
        $stmt->execute(['id' => $fatturaId]);
        $fattura = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fattura) {
            return ['success' => false, 'error' => 'Fattura non trovata'];
        }

        $stmt = $pdo->prepare('
            SELECT * FROM fatture_righe
            WHERE fattura_id = :fattura_id
            ORDER BY ordine ASC
        ');
        $stmt->execute(['fattura_id' => $fatturaId]);
        $righe = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $htmlContent = generaHTMLFatturaEmail($fattura, $righe);

        $destinatario = $fattura['cliente_email'];
        $destinatarioNome = $fattura['azienda'];
        $numeroFattura = $fattura['numero_fattura'];
        $periodo = sprintf('%02d/%d', $fattura['mese'], $fattura['anno']);
        $totale = number_format((float)$fattura['totale'], 2, ',', '.');

        $oggetto = "Fattura N. $numeroFattura - Periodo $periodo";

        $corpoEmail = "
<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #8b5cf6; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 12px 24px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Finch-AI</h1>
        </div>
        <div class='content'>
            <p>Gentile <strong>$destinatarioNome</strong>,</p>
            <p>In allegato trova la fattura <strong>N. $numeroFattura</strong> relativa al periodo <strong>$periodo</strong>.</p>
            <p><strong>Importo totale:</strong> € $totale</p>
            <p>Per qualsiasi chiarimento non esiti a contattarci.</p>
            <p>Cordiali saluti,<br>Il team Finch-AI</p>
        </div>
        <div class='footer'>
            <p>Finch-AI S.r.l. | Via Example 123, 00100 Roma (RM)<br>
            P.IVA: IT12345678901 | Email: fatturazione@finch-ai.it</p>
        </div>
    </div>
</body>
</html>
        ";

        $tempDir = sys_get_temp_dir();
        $pdfFileName = "Fattura_" . $numeroFattura . ".html";
        $pdfFilePath = $tempDir . DIRECTORY_SEPARATOR . $pdfFileName;
        file_put_contents($pdfFilePath, $htmlContent);

        $mittente = "fatturazione@finch-ai.it";
        $mittenteNome = "Finch-AI - Fatturazione";
        $boundary = md5((string)time());

        $headers = "From: $mittenteNome <$mittente>\r\n";
        $headers .= "Reply-To: $mittente\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $corpoEmail . "\r\n";

        $fileContent = chunk_split(base64_encode(file_get_contents($pdfFilePath)));
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/html; name=\"$pdfFileName\"\r\n";
        $message .= "Content-Disposition: attachment; filename=\"$pdfFileName\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= $fileContent . "\r\n";
        $message .= "--$boundary--";

        $emailInviata = mail($destinatario, $oggetto, $message, $headers);
        @unlink($pdfFilePath);

        if (!$emailInviata) {
            return ['success' => false, 'error' => 'Errore durante l\'invio dell\'email'];
        }

        return [
            'success' => true,
            'message' => "Email inviata con successo a $destinatario"
        ];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function generaHTMLFatturaEmail($fattura, $righe) {
    $numeroFattura = htmlspecialchars($fattura['numero_fattura']);
    $dataEmissione = date('d/m/Y', strtotime($fattura['data_emissione']));
    $dataScadenza = date('d/m/Y', strtotime($fattura['data_scadenza']));

    $clienteAzienda = htmlspecialchars($fattura['azienda']);
    $clienteNome = htmlspecialchars($fattura['cliente_nome'] . ' ' . $fattura['cliente_cognome']);
    $clienteEmail = htmlspecialchars($fattura['cliente_email']);
    $clienteTelefono = htmlspecialchars($fattura['telefono'] ?? '');
    $periodo = sprintf('%02d/%d', $fattura['mese'], $fattura['anno']);

    $imponibile = number_format((float)$fattura['imponibile'], 2, ',', '.');
    $ivaPerc = number_format((float)$fattura['iva_percentuale'], 0, ',', '.');
    $ivaImporto = number_format((float)$fattura['iva_importo'], 2, ',', '.');
    $totale = number_format((float)$fattura['totale'], 2, ',', '.');

    $html = '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Fattura ' . $numeroFattura . '</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #8b5cf6;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 24pt;
            font-weight: 700;
            color: #8b5cf6;
            margin: 0 0 5px 0;
        }
        .company-details {
            font-size: 9pt;
            color: #666;
        }
        .fattura-info {
            text-align: right;
            flex-shrink: 0;
        }
        .fattura-numero {
            font-size: 20pt;
            font-weight: 700;
            color: #000;
            margin: 0 0 10px 0;
        }
        .fattura-data {
            font-size: 10pt;
            color: #666;
            margin: 3px 0;
        }
        .cliente-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .cliente-label {
            font-size: 9pt;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 10px 0;
        }
        .cliente-nome {
            font-size: 14pt;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        .cliente-dettagli {
            font-size: 10pt;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #8b5cf6;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        th.align-right {
            text-align: right;
        }
        th.align-center {
            text-align: center;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10pt;
        }
        td.align-right {
            text-align: right;
        }
        td.align-center {
            text-align: center;
        }
        .totali {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .totali-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
            font-size: 11pt;
        }
        .totali-row.imponibile {
            font-weight: 600;
        }
        .totali-row.totale {
            font-size: 16pt;
            font-weight: 700;
            color: #8b5cf6;
            border-top: 3px solid #8b5cf6;
            border-bottom: 3px solid #8b5cf6;
            margin-top: 10px;
            padding: 15px 0;
        }
        .footer {
            clear: both;
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
        .note {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            font-size: 10pt;
        }
        .note-label {
            font-weight: 700;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <img src="/assets/images/LOGO.png" alt="Finch-AI" style="height: 60px; margin-bottom: 10px;">
            <div class="company-details">
                Via Example 123, 00100 Roma (RM)<br>
                P.IVA: IT12345678901 • Tel: +39 06 1234567<br>
                Email: fatturazione@finch-ai.it • Web: www.finch-ai.it
            </div>
        </div>
        <div class="fattura-info">
            <div class="fattura-numero">FATTURA N. ' . $numeroFattura . '</div>
            <div class="fattura-data">Periodo: ' . $periodo . '</div>
            <div class="fattura-data">Data Emissione: ' . $dataEmissione . '</div>
            <div class="fattura-data">Data Scadenza: ' . $dataScadenza . '</div>
        </div>
    </div>

    <div class="cliente-box">
        <div class="cliente-label">Fattura intestata a:</div>
        <div class="cliente-nome">' . $clienteAzienda . '</div>
        <div class="cliente-dettagli">
            ' . $clienteNome . '<br>
            Email: ' . $clienteEmail;

    if ($clienteTelefono) {
        $html .= '<br>Tel: ' . $clienteTelefono;
    }

    $html .= '
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60%;">Descrizione</th>
                <th class="align-center" style="width: 10%;">Qta</th>
                <th class="align-right" style="width: 15%;">Prezzo Unit.</th>
                <th class="align-right" style="width: 15%;">Totale</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($righe as $riga) {
        $descrizione = htmlspecialchars($riga['descrizione']);
        $quantita = number_format((float)$riga['quantita'], 2, ',', '.');
        $prezzoUnitario = '€ ' . number_format((float)$riga['prezzo_unitario'], 2, ',', '.');
        $totaleRiga = '€ ' . number_format((float)$riga['totale'], 2, ',', '.');

        $html .= '
            <tr>
                <td>' . $descrizione . '</td>
                <td class="align-center">' . $quantita . '</td>
                <td class="align-right">' . $prezzoUnitario . '</td>
                <td class="align-right">' . $totaleRiga . '</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>

    <div class="totali">
        <div class="totali-row imponibile">
            <span>Imponibile:</span>
            <span>€ ' . $imponibile . '</span>
        </div>
        <div class="totali-row">
            <span>IVA (' . $ivaPerc . '%):</span>
            <span>€ ' . $ivaImporto . '</span>
        </div>
        <div class="totali-row totale">
            <span>TOTALE:</span>
            <span>€ ' . $totale . '</span>
        </div>
    </div>';

    if ($fattura['note']) {
        $note = nl2br(htmlspecialchars($fattura['note']));
        $html .= '
    <div class="note">
        <div class="note-label">Note:</div>
        ' . $note . '
    </div>';
    }

    $html .= '
    <div class="footer">
        Pagamento da effettuarsi entro il ' . $dataScadenza . ' tramite bonifico bancario<br>
        IBAN: IT00 A000 0000 0000 0000 0000 000 • BIC: EXAMPLE<br>
        <br>
        Documento generato elettronicamente • Finch-AI S.r.l. • P.IVA IT12345678901
    </div>
</body>
</html>';

    return $html;
}
