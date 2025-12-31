<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo 'Accesso negato';
    exit;
}

$fatturaId = (int)($_GET['id'] ?? 0);
if ($fatturaId <= 0) {
    http_response_code(400);
    echo 'ID fattura mancante';
    exit;
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

// Recupera fattura con dettagli (solo se appartiene al cliente loggato)
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
    WHERE f.id = :id AND f.$colCliente = :user_id
");
$stmt->execute(['id' => $fatturaId, 'user_id' => $_SESSION['cliente_id']]);
$fattura = $stmt->fetch();

if (!$fattura) {
    http_response_code(404);
    echo 'Fattura non trovata';
    exit;
}

// Recupera righe fattura
$stmt = $pdo->prepare('
    SELECT fr.*, s.nome AS servizio_nome
    FROM fatture_righe fr
    LEFT JOIN servizi s ON fr.servizio_id = s.id
    WHERE fr.fattura_id = :fattura_id
    ORDER BY fr.ordine ASC, fr.id ASC
');
$stmt->execute(['fattura_id' => $fatturaId]);
$righe = $stmt->fetchAll();

// Include le funzioni di generazione PDF
require_once __DIR__ . '/../includes/company-info.php';

// Genera PDF usando la funzione esistente
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

    // Costruisci righe tabella
    $righeHTML = '';
    foreach ($righe as $riga) {
        $descrizione = htmlspecialchars($riga['servizio_nome'] ?? $riga['descrizione']);
        $qta = number_format((float)$riga['quantita'], 2, ',', '.');
        $prezzo = number_format((float)$riga['prezzo_unitario'], 2, ',', '.');
        $imp = number_format((float)$riga['imponibile'], 2, ',', '.');
        $iva = number_format((float)$riga['iva_percentuale'], 0);
        $ivaImp = number_format((float)$riga['iva_importo'], 2, ',', '.');
        $tot = number_format((float)$riga['totale'], 2, ',', '.');

        $righeHTML .= "
        <tr>
            <td>$descrizione</td>
            <td class=\"align-center\">$qta</td>
            <td class=\"align-right\">€ $prezzo</td>
            <td class=\"align-right\">€ $imp</td>
            <td class=\"align-center\">$iva%</td>
            <td class=\"align-right\">€ $ivaImp</td>
            <td class=\"align-right\"><strong>€ $tot</strong></td>
        </tr>";
    }

    $logoPath = realpath(__DIR__ . '/../../public/assets/images/LOGO.png');
    $logoData = '';
    if ($logoPath && file_exists($logoPath)) {
        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }

    return '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Fattura ' . $numeroFattura . '</title>
    <style>
        @page { margin: 20mm; }
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
        .company-info { flex: 1; }
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
        }
        th.align-right, td.align-right { text-align: right; }
        th.align-center, td.align-center { text-align: center; }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10pt;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            ' . ($logoData ? '<img src="' . $logoData . '" alt="Finch-AI" style="height: 60px; margin-bottom: 10px;">' : '<div class="company-name">Finch-AI</div>') . '
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
        <div class="cliente-label">Fatturato a:</div>
        <div class="cliente-nome">' . $clienteAzienda . '</div>
        <div class="cliente-dettagli">
            ' . $clienteNome . '<br>
            ' . $clienteEmail . ($clienteTelefono ? '<br>' . $clienteTelefono : '') . '
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descrizione</th>
                <th class="align-center">Qta</th>
                <th class="align-right">Prezzo</th>
                <th class="align-right">Imponibile</th>
                <th class="align-center">IVA</th>
                <th class="align-right">IVA €</th>
                <th class="align-right">Totale</th>
            </tr>
        </thead>
        <tbody>
            ' . $righeHTML . '
        </tbody>
    </table>

    <div class="totali">
        <div class="totali-row"><span>Imponibile:</span><span>€ ' . $imponibile . '</span></div>
        <div class="totali-row"><span>IVA (' . $ivaPerc . '%):</span><span>€ ' . $ivaImporto . '</span></div>
        <div class="totali-row totale"><span>TOTALE:</span><span>€ ' . $totale . '</span></div>
    </div>

    <div class="footer">
        Finch-AI S.r.l. - Documento generato automaticamente
    </div>
</body>
</html>';
}

// Usa DomPDF o altra libreria se disponibile, altrimenti PDF semplice
$html = generaHTMLFattura($fattura, $righe);

// Filename per il download
$filename = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$fattura['numero_fattura']) . '.pdf';

// Per ora genera un PDF base - in futuro si può usare DomPDF
// Crea un PDF semplice con wkhtmltopdf se disponibile, altrimenti HTML
$usePdfConversion = false; // Cambia a true se hai wkhtmltopdf installato

if ($usePdfConversion && function_exists('shell_exec')) {
    // Usa wkhtmltopdf o altra conversione
    $tmpHtml = tempnam(sys_get_temp_dir(), 'invoice_') . '.html';
    $tmpPdf = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
    file_put_contents($tmpHtml, $html);
    shell_exec("wkhtmltopdf $tmpHtml $tmpPdf");

    if (file_exists($tmpPdf)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpPdf));
        readfile($tmpPdf);
        unlink($tmpHtml);
        unlink($tmpPdf);
        exit;
    }
}

// Fallback: mostra HTML che il browser può stampare come PDF
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="' . str_replace('.pdf', '.html', $filename) . '"');
echo $html;
echo '<script>window.print();</script>';
exit;
