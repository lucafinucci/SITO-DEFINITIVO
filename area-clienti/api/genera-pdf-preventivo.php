<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

// Verifica utente e ruolo
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    die('Accesso negato');
}

$preventivoId = (int)($_GET['id'] ?? 0);
if (!$preventivoId) {
    http_response_code(400);
    die('ID preventivo mancante');
}

$stmt = $pdo->prepare('
    SELECT id, nome_azienda, referente, email, stato, scadenza, note, subtotale, sconto_percentuale, totale, created_at
    FROM preventivi
    WHERE id = :id
');
$stmt->execute(['id' => $preventivoId]);
$preventivo = $stmt->fetch();

if (!$preventivo) {
    http_response_code(404);
    die('Preventivo non trovato');
}

$stmt = $pdo->prepare('
    SELECT descrizione, quantita, prezzo_unitario, totale
    FROM preventivi_voci
    WHERE preventivo_id = :id
    ORDER BY id ASC
');
$stmt->execute(['id' => $preventivoId]);
$voci = $stmt->fetchAll();

$html = generaHTMLPreventivo($preventivo, $voci);

header('Content-Type: text/html; charset=utf-8');
echo $html;

function generaHTMLPreventivo($preventivo, $voci) {
    $company = require __DIR__ . '/../includes/company-info.php';

    $numeroPreventivo = htmlspecialchars((string)$preventivo['id']);
    $dataCreazione = $preventivo['created_at'] ? date('d/m/Y', strtotime($preventivo['created_at'])) : 'N/D';
    $dataScadenza = $preventivo['scadenza'] ? date('d/m/Y', strtotime($preventivo['scadenza'])) : 'N/D';

    $clienteAzienda = htmlspecialchars((string)$preventivo['nome_azienda']);
    $clienteReferente = htmlspecialchars((string)($preventivo['referente'] ?: 'N/D'));
    $clienteEmail = htmlspecialchars((string)($preventivo['email'] ?: 'N/D'));
    $stato = htmlspecialchars(ucfirst((string)$preventivo['stato']));

    $subtotale = number_format((float)$preventivo['subtotale'], 2, ',', '.');
    $sconto = number_format((float)$preventivo['sconto_percentuale'], 2, ',', '.');
    $totale = number_format((float)$preventivo['totale'], 2, ',', '.');

    $html = '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Preventivo ' . $numeroPreventivo . '</title>
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
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-info {
            width: 55%;
        }
        .company-details {
            font-size: 10pt;
            line-height: 1.4;
        }
        .doc-info {
            width: 40%;
            text-align: right;
        }
        .doc-title {
            font-size: 18pt;
            font-weight: 700;
            color: #8b5cf6;
            margin-bottom: 10px;
        }
        .doc-line {
            font-size: 10pt;
            margin-bottom: 4px;
        }
        .cliente-box {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .cliente-label {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 6px;
        }
        .cliente-nome {
            font-size: 13pt;
            font-weight: 700;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead th {
            background: #f3f4f6;
            padding: 8px;
            border: 1px solid #dee2e6;
            font-weight: 700;
            text-align: left;
            font-size: 10pt;
        }
        tbody td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10pt;
        }
        .align-right { text-align: right; }
        .align-center { text-align: center; }
        .totali {
            width: 100%;
            max-width: 280px;
            margin-left: auto;
            font-size: 10pt;
        }
        .totali-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .totali-row.totale {
            font-size: 14pt;
            font-weight: 700;
            color: #8b5cf6;
            border-top: 2px solid #8b5cf6;
            border-bottom: 2px solid #8b5cf6;
            margin-top: 8px;
            padding: 8px 0;
        }
        .note {
            margin-top: 20px;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 10pt;
        }
        .note-label { font-weight: 700; margin-bottom: 6px; }
        .footer {
            margin-top: 60px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <img src="/assets/images/LOGO.png" alt="Finch-AI" style="height: 60px; margin-bottom: 10px;">
            <div class="company-details">
                ' . htmlspecialchars($company['indirizzo']) . '<br>
                ' . htmlspecialchars($company['piva']) . ' - ' . htmlspecialchars($company['telefono']) . '<br>
                ' . htmlspecialchars($company['email']) . ' - ' . htmlspecialchars($company['web']) . '
            </div>
        </div>
        <div class="doc-info">
            <div class="doc-title">PREVENTIVO #' . $numeroPreventivo . '</div>
            <div class="doc-line">Stato: ' . $stato . '</div>
            <div class="doc-line">Data creazione: ' . $dataCreazione . '</div>
            <div class="doc-line">Scadenza: ' . $dataScadenza . '</div>
        </div>
    </div>

    <div class="cliente-box">
        <div class="cliente-label">Preventivo per</div>
        <div class="cliente-nome">' . $clienteAzienda . '</div>
        <div class="cliente-dettagli">Referente: ' . $clienteReferente . '<br>Email: ' . $clienteEmail . '</div>
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

    foreach ($voci as $riga) {
        $descrizione = htmlspecialchars((string)$riga['descrizione']);
        $quantita = number_format((float)$riga['quantita'], 2, ',', '.');
        $prezzoUnitario = '&euro; ' . number_format((float)$riga['prezzo_unitario'], 2, ',', '.');
        $totaleRiga = '&euro; ' . number_format((float)$riga['totale'], 2, ',', '.');

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
        <div class="totali-row">
            <span>Subtotale:</span>
            <span>&euro; ' . $subtotale . '</span>
        </div>
        <div class="totali-row">
            <span>Sconto:</span>
            <span>' . $sconto . '%</span>
        </div>
        <div class="totali-row totale">
            <span>TOTALE:</span>
            <span>&euro; ' . $totale . '</span>
        </div>
    </div>';

    if (!empty($preventivo['note'])) {
        $note = nl2br(htmlspecialchars((string)$preventivo['note']));
        $html .= '
    <div class="note">
        <div class="note-label">Note</div>
        <div>' . $note . '</div>
    </div>';
    }

    $html .= '
    <div class="footer">
        Documento generato elettronicamente - ' . htmlspecialchars($company['ragione_sociale']) . ' - ' . htmlspecialchars($company['piva']) . '
    </div>
</body>
</html>';

    return $html;
}
