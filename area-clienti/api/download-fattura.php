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

try {
    $stmtCols = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fatture'
          AND COLUMN_NAME IN ('file_pdf_path', 'file_path', 'user_id', 'cliente_id')
    ");
    $stmtCols->execute();
    $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

    $hasFilePdf = in_array('file_pdf_path', $cols, true);
    $hasFilePath = in_array('file_path', $cols, true);
    $colFile = $hasFilePdf ? 'file_pdf_path' : ($hasFilePath ? 'file_path' : null);

    $userCol = null;
    if (in_array('user_id', $cols, true)) {
        $userCol = 'user_id';
    } elseif (in_array('cliente_id', $cols, true)) {
        $userCol = 'cliente_id';
    }

    if (!$colFile || !$userCol) {
        http_response_code(500);
        echo 'Schema fatture non compatibile';
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id, numero_fattura, data_emissione, {$colFile} AS file_pdf_path
        FROM fatture
        WHERE id = :id AND {$userCol} = :user_id
    ");
    $stmt->execute([
        'id' => $fatturaId,
        'user_id' => $_SESSION['cliente_id']
    ]);
    $fattura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fattura) {
        http_response_code(404);
        echo 'Fattura non trovata';
        exit;
    }

    $rawPath = trim((string)($fattura['file_pdf_path'] ?? ''));
    if ($rawPath === '') {
        $stmtServizi = $pdo->prepare("
            SELECT COALESCE(s.nome, fr.descrizione) AS servizio_nome
            FROM fatture_righe fr
            LEFT JOIN servizi s ON fr.servizio_id = s.id
            WHERE fr.fattura_id = :fattura_id
            ORDER BY fr.id ASC
        ");
        $stmtServizi->execute(['fattura_id' => $fatturaId]);
        $servizi = $stmtServizi->fetchAll(PDO::FETCH_COLUMN);

        $numero = (string)$fattura['numero_fattura'];
        $data = date('d/m/Y', strtotime($fattura['data_emissione']));
        $linee = [
            'FATTURA',
            'Numero: ' . $numero,
            'Data: ' . $data,
        ];
        if (!empty($servizi)) {
            $linee[] = 'Servizi:';
            foreach ($servizi as $servizio) {
                $linee[] = '- ' . (string)$servizio;
            }
        } else {
            $linee[] = 'Servizi: nessuno';
        }

        $pdfLines = [];
        foreach ($linee as $line) {
            $line = str_replace('\\', '\\\\', $line);
            $line = str_replace('(', '\\(', $line);
            $line = str_replace(')', '\\)', $line);
            $pdfLines[] = '(' . $line . ') Tj';
        }

        $content = "BT\n/F1 14 Tf\n50 760 Td\n" . implode("\n0 -18 Td\n", $pdfLines) . "\nET\n";
        $length = strlen($content);
        $pdf = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> >>
endobj
4 0 obj
<< /Length {$length} >>
stream
{$content}
endstream
endobj
xref
0 5
0000000000 65535 f
0000000010 00000 n
0000000060 00000 n
0000000117 00000 n
0000000268 00000 n
trailer
<< /Size 5 /Root 1 0 R >>
startxref
{$length}
%%EOF";

        $filename = $numero . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }

    if (preg_match('~^https?://~i', $rawPath)) {
        header('Location: ' . $rawPath);
        exit;
    }

    $projectRoot = dirname(__DIR__, 2);
    $candidate = $rawPath;

    if (!file_exists($candidate)) {
        if ($rawPath[0] === '/') {
            $candidate = $projectRoot . $rawPath;
        } else {
            $candidate = $projectRoot . '/' . $rawPath;
        }
    }

    if (!file_exists($candidate) || !is_readable($candidate)) {
        http_response_code(404);
        echo 'File PDF non trovato';
        exit;
    }

    $filename = basename($candidate);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($candidate));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    readfile($candidate);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Errore durante il download';
}
