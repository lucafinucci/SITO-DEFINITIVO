<?php
ini_set('display_errors', '0');
error_reporting(0);
ob_start();
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
require __DIR__ . '/../lib/fpdf.php';

header('Content-Type: application/json; charset=utf-8');


register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Errore server: ' . $err['message']
        ]);
    }
});

function respondJson(array $payload, int $statusCode = 200) {
    static $responded = false;
    if ($responded) {
        return;
    }
    $responded = true;

    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

// Verifica admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();
if (!$user || $user['ruolo'] !== 'admin') {
    respondJson(['success' => false, 'error' => 'Accesso negato'], 403);
}

// Verifica CSRF
$input = json_decode(file_get_contents('php://input'), true);

function startPdf() {
    if (ob_get_length()) {
        ob_clean();
    }
    ini_set('display_errors', '0');
    error_reporting(0);
}

$csrfToken = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (!$csrfToken || !hash_equals($sessionToken, $csrfToken)) {
    respondJson(['success' => false, 'error' => 'CSRF token non valido'], 403);
}

$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    respondJson(['success' => false, 'error' => 'ID mancante'], 400);
}

$stmt = $pdo->prepare('
    SELECT id, nome_azienda, referente, email, stato, sconto_percentuale, note, scadenza, subtotale, totale
    FROM preventivi
    WHERE id = :id
');
$stmt->execute(['id' => $id]);
$preventivo = $stmt->fetch();
if (!$preventivo) {
    respondJson(['success' => false, 'error' => 'Preventivo non trovato'], 404);
}

if (empty($preventivo['email'])) {
    respondJson(['success' => false, 'error' => 'Email cliente mancante'], 400);
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

startPdf();
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
$pdf->Cell(0, 6, pdfText('Scadenza: ' . ($preventivo['scadenza'] ?: 'N/D')), 0, 1);
$pdf->Ln(4);

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

$pdfData = $pdf->Output('S');
$boundary = md5((string)time());

$to = $preventivo['email'];
$subject = 'Preventivo #' . $preventivo['id'];
$from = Config::get('MAIL_FROM', 'noreply@finch-ai.it');

$headers = "From: Area Clienti <{$from}>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

$body = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=\"utf-8\"\r\n\r\n";
$body .= "Ciao,\n";
$body .= "in allegato trovi il preventivo #{$preventivo['id']}.\n\n";
$body .= "Cordiali saluti,\nTeam Finch-AI\n\r\n";

$body .= "--{$boundary}\r\n";
$body .= "Content-Type: application/pdf; name=\"preventivo_{$preventivo['id']}.pdf\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"preventivo_{$preventivo['id']}.pdf\"\r\n\r\n";
$body .= chunk_split(base64_encode($pdfData)) . "\r\n";
$body .= "--{$boundary}--";

$mailSent = @mail($to, $subject, $body, $headers);
if (!$mailSent) {
    respondJson(['success' => false, 'error' => 'Invio email fallito'], 500);
}

// aggiorna stato a inviato
$stmt = $pdo->prepare('UPDATE preventivi SET stato = "inviato" WHERE id = :id');
$stmt->execute(['id' => $id]);

respondJson(['success' => true]);
