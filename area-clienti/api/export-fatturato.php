<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

// Verifica admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();
if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    exit('Accesso negato');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="fatturato_mensile.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['mese', 'fatture', 'totale_netto', 'iva', 'totale_lordo']);

$stmt = $pdo->prepare('
    SELECT
        DATE_FORMAT(data_emissione, "%Y-%m") AS mese,
        COUNT(*) AS fatture,
        SUM(importo_netto) AS totale_netto,
        SUM(iva) AS iva,
        SUM(importo_totale) AS totale_lordo
    FROM fatture
    GROUP BY mese
    ORDER BY mese DESC
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    fputcsv($out, [
        $row['mese'],
        $row['fatture'],
        number_format((float)$row['totale_netto'], 2, '.', ''),
        number_format((float)$row['iva'], 2, '.', ''),
        number_format((float)$row['totale_lordo'], 2, '.', '')
    ]);
}
fclose($out);
