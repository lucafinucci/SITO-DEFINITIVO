<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../lib/fpdf.php';

// Verifica admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();
if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    exit('Accesso negato');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('ID mancante');
}

$stmt = $pdo->prepare('
    SELECT id, nome_azienda, referente, email, stato, sconto_percentuale, note, scadenza, subtotale, totale, created_at
    FROM preventivi
    WHERE id = :id
');
$stmt->execute(['id' => $id]);
$preventivo = $stmt->fetch();
if (!$preventivo) {
    http_response_code(404);
    exit('Preventivo non trovato');
}

$stmt = $pdo->prepare('
    SELECT descrizione, quantita, prezzo_unitario, totale
    FROM preventivi_voci
    WHERE preventivo_id = :id
');
$stmt->execute(['id' => $id]);
$voci = $stmt->fetchAll();

function pdfText($text) {
    return iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
}

$pdf = new FPDF();
$pdf->AddPage();
$logoPath = __DIR__ . '/../../assets/images/LOGO.png';
if (file_exists($logoPath)) {
    $logoW = 40;
    $x = ($pdf->w - $logoW) / 2;
    $pdf->Image($logoPath, $x, 10, $logoW);
    $pdf->y = 30;
    $pdf->x = $pdf->lMargin;
}

$pdf->SetFont('Helvetica', 'B', 18);
$pdf->Cell(0, 10, pdfText('Preventivo #' . $preventivo['id']), 0, 1, 'C');

$pdf->SetFont('Helvetica', '', 11);
$pdf->Cell(0, 6, pdfText('Azienda: ' . $preventivo['nome_azienda']), 0, 1);
$pdf->Cell(0, 6, pdfText('Referente: ' . ($preventivo['referente'] ?: 'N/D')), 0, 1);
$pdf->Cell(0, 6, pdfText('Email: ' . ($preventivo['email'] ?: 'N/D')), 0, 1);
$pdf->Cell(0, 6, pdfText('Stato: ' . ucfirst($preventivo['stato'])), 0, 1);
$pdf->Cell(0, 6, pdfText('Scadenza: ' . ($preventivo['scadenza'] ?: 'N/D')), 0, 1);
$pdf->Ln(6);

// Tabella voci
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetFillColor(230, 235, 243);
$pdf->Cell(90, 7, pdfText('Servizio'), 1, 0, 'L', true);
$pdf->Cell(20, 7, pdfText('Qta'), 1, 0, 'R', true);
$pdf->Cell(35, 7, pdfText('Prezzo'), 1, 0, 'R', true);
$pdf->Cell(35, 7, pdfText('Totale'), 1, 1, 'R', true);

$pdf->SetFont('Helvetica', '', 11);
foreach ($voci as $riga) {
    $pdf->Cell(90, 7, pdfText($riga['descrizione']), 1, 0);
    $pdf->Cell(20, 7, number_format((float)$riga['quantita'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(35, 7, pdfText('€' . number_format((float)$riga['prezzo_unitario'], 2, ',', '.')), 1, 0, 'R');
    $pdf->Cell(35, 7, pdfText('€' . number_format((float)$riga['totale'], 2, ',', '.')), 1, 1, 'R');
}

$pdf->Ln(4);
$pdf->SetFont('Helvetica', '', 11);
$pdf->Cell(0, 6, pdfText('Subtotale: €' . number_format((float)$preventivo['subtotale'], 2, ',', '.')), 0, 1, 'R');
$pdf->Cell(0, 6, pdfText('Sconto: ' . number_format((float)$preventivo['sconto_percentuale'], 2, ',', '.') . '%'), 0, 1, 'R');
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(0, 8, pdfText('Totale: €' . number_format((float)$preventivo['totale'], 2, ',', '.')), 0, 1, 'R');

if (!empty($preventivo['note'])) {
    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(0, 6, pdfText('Note'), 0, 1);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->MultiCell(0, 6, pdfText($preventivo['note']));
}

$fileName = 'preventivo_' . $preventivo['id'] . '.pdf';
$pdf->Output('D', $fileName);
