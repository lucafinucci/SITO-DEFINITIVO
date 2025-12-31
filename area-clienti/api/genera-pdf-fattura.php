<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

// Verifica utente e ruolo
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(403);
    die('Accesso negato');
}

$isAdmin = ($user['ruolo'] === 'admin');

$fatturaId = (int)($_GET['id'] ?? 0);

if (!$fatturaId) {
    http_response_code(400);
    die('ID fattura mancante');
}

// Verifica compatibilità schema
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

// Recupera fattura con dettagli
$whereClause = "f.id = :id";
$params = ['id' => $fatturaId];
if (!$isAdmin) {
    $whereClause .= " AND f.$colCliente = :user_id";
    $params['user_id'] = $_SESSION['cliente_id'];
}

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
    WHERE $whereClause
");
$stmt->execute($params);
$fattura = $stmt->fetch();

if (!$fattura) {
    http_response_code(404);
    die('Fattura non trovata');
}

// Recupera righe fattura
$stmt = $pdo->prepare('
    SELECT * FROM fatture_righe
    WHERE fattura_id = :fattura_id
    ORDER BY ordine ASC
');
$stmt->execute(['fattura_id' => $fatturaId]);
$righe = $stmt->fetchAll();

// Genera HTML della fattura
$html = generaHTMLFattura($fattura, $righe);

$format = $_GET['format'] ?? 'pdf';
if ($format == 'html') {
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}

$pdf = generaPDFFattura($fattura, $righe);
$filename = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$fattura['numero_fattura']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;

function generaHTMLFattura($fattura, $righe) {
    $company = require __DIR__ . '/../includes/company-info.php';
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
                ' . htmlspecialchars($company['indirizzo']) . '<br>
                ' . htmlspecialchars($company['piva']) . ' • ' . htmlspecialchars($company['telefono']) . '<br>
                ' . htmlspecialchars($company['email']) . ' • ' . htmlspecialchars($company['web']) . '
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
        ' . htmlspecialchars($company['iban']) . ' • ' . htmlspecialchars($company['bic']) . '<br>
        <br>
        Documento generato elettronicamente • ' . htmlspecialchars($company['ragione_sociale']) . ' • ' . htmlspecialchars($company['piva']) . '
    </div>
</body>
</html>';

    return $html;
}

function generaPDFFattura($fattura, $righe) {
    $company = require __DIR__ . '/../includes/company-info.php';

    $lines = [];
    $lines[] = $company['ragione_sociale'] ?? 'Finch-AI';
    $lines[] = $company['indirizzo'] ?? '';
    $lines[] = $company['piva'] ?? '';
    $lines[] = $company['telefono'] ?? '';
    $lines[] = $company['email'] ?? '';
    $lines[] = $company['web'] ?? '';
    $lines[] = '';

    $numero = (string)$fattura['numero_fattura'];
    $lines[] = 'Fattura ' . $numero;
    $lines[] = 'Data emissione: ' . date('d/m/Y', strtotime($fattura['data_emissione']));
    $lines[] = 'Data scadenza: ' . date('d/m/Y', strtotime($fattura['data_scadenza']));
    $lines[] = 'Periodo: ' . sprintf('%02d/%d', $fattura['mese'], $fattura['anno']);
    $lines[] = '';

    $clienteNome = trim($fattura['cliente_nome'] . ' ' . $fattura['cliente_cognome']);
    $lines[] = 'Cliente: ' . $clienteNome;
    if (!empty($fattura['azienda'])) {
        $lines[] = 'Azienda: ' . $fattura['azienda'];
    }
    if (!empty($fattura['cliente_email'])) {
        $lines[] = 'Email: ' . $fattura['cliente_email'];
    }
    if (!empty($fattura['telefono'])) {
        $lines[] = 'Telefono: ' . $fattura['telefono'];
    }
    $lines[] = '';

    $lines[] = 'Righe fattura:';
    if (!empty($righe)) {
        foreach ($righe as $idx => $riga) {
            $descrizione = (string)($riga['descrizione'] ?? 'Servizio');
            $quantita = (float)($riga['quantita'] ?? 1);
            $prezzo = (float)($riga['prezzo_unitario'] ?? 0);
            $totale = (float)($riga['totale'] ?? ($quantita * $prezzo));
            $lines[] = sprintf(
                '%d) %s | Qta %s x %s = %s',
                $idx + 1,
                $descrizione,
                number_format($quantita, 2, ',', '.'),
                number_format($prezzo, 2, ',', '.'),
                number_format($totale, 2, ',', '.')
            );
        }
    } else {
        $lines[] = '- Nessuna riga';
    }

    $lines[] = '';
    $lines[] = 'Imponibile: ' . number_format((float)$fattura['imponibile'], 2, ',', '.');
    $lines[] = 'IVA (' . number_format((float)$fattura['iva_percentuale'], 0, ',', '.') . '%): ' . number_format((float)$fattura['iva_importo'], 2, ',', '.');
    $lines[] = 'Totale: ' . number_format((float)$fattura['totale'], 2, ',', '.');

    return buildSimplePdf($lines);
}

function buildSimplePdf(array $lines) {
    $lineHeight = 14;
    $contentLines = [
        'BT',
        '/F1 12 Tf',
        '50 770 Td'
    ];

    foreach ($lines as $line) {
        $contentLines[] = '(' . pdfEscape((string)$line) . ') Tj';
        $contentLines[] = '0 -' . $lineHeight . ' Td';
    }

    $contentLines[] = 'ET';
    $content = implode("
", $contentLines);
    $length = strlen($content);

    $objects = [];
    $objects[] = "1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
";
    $objects[] = "2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
";
    $objects[] = "3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>
endobj
";
    $objects[] = "4 0 obj
<< /Length {$length} >>
stream
{$content}
endstream
endobj
";
    $objects[] = "5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
";

    $pdf = "%PDF-1.4
";
    $offsets = [0];
    foreach ($objects as $obj) {
        $offsets[] = strlen($pdf);
        $pdf .= $obj;
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref
0 " . (count($objects) + 1) . "
";
    $pdf .= "0000000000 65535 f 
";
    foreach (array_slice($offsets, 1) as $offset) {
        $pdf .= sprintf("%010d 00000 n 
", $offset);
    }

    $pdf .= "trailer
<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>
";
    $pdf .= "startxref
" . $xrefOffset . "
%%EOF";

    return $pdf;
}

function pdfEscape($text) {
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);
    $text = str_replace(')', '\\)', $text);
    return $text;
}
