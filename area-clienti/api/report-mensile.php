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

$month = $_GET['month'] ?? '';
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = (new DateTime('first day of last month 00:00:00'))->format('Y-m');
}

$start = new DateTime($month . '-01 00:00:00');
$end = (clone $start)->modify('+1 month');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="report-contabilita-' . $month . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['mese', $month]);
fputcsv($out, []);

// Riepilogo fatture
fputcsv($out, ['riepilogo_fatture', 'valore']);

$stmt = $pdo->prepare('
    SELECT
        COUNT(*) AS fatture,
        SUM(importo_netto) AS totale_netto,
        SUM(iva) AS iva,
        SUM(importo_totale) AS totale_lordo,
        SUM(CASE WHEN stato = "pagata" THEN 1 ELSE 0 END) AS fatture_pagate,
        SUM(CASE WHEN stato = "emessa" THEN 1 ELSE 0 END) AS fatture_emesse
    FROM fatture
    WHERE data_emissione >= :start AND data_emissione < :end
');
$stmt->execute([
    'start' => $start->format('Y-m-d'),
    'end' => $end->format('Y-m-d')
]);
$row = $stmt->fetch();

fputcsv($out, ['numero_fatture', (int)$row['fatture']]);
fputcsv($out, ['fatture_pagate', (int)$row['fatture_pagate']]);
fputcsv($out, ['fatture_emesse', (int)$row['fatture_emesse']]);
fputcsv($out, ['totale_netto', number_format((float)$row['totale_netto'], 2, '.', '')]);
fputcsv($out, ['iva', number_format((float)$row['iva'], 2, '.', '')]);
fputcsv($out, ['totale_lordo', number_format((float)$row['totale_lordo'], 2, '.', '')]);

fputcsv($out, []);

// Dettaglio fatture
fputcsv($out, ['id', 'cliente_id', 'numero_fattura', 'data_emissione', 'importo_totale', 'stato']);

$stmt = $pdo->prepare('
    SELECT id, user_id, numero_fattura, data_emissione, importo_totale, stato
    FROM fatture
    WHERE data_emissione >= :start AND data_emissione < :end
    ORDER BY data_emissione ASC
');
$stmt->execute([
    'start' => $start->format('Y-m-d'),
    'end' => $end->format('Y-m-d')
]);
foreach ($stmt->fetchAll() as $fattura) {
    fputcsv($out, [
        $fattura['id'],
        $fattura['user_id'],
        $fattura['numero_fattura'],
        $fattura['data_emissione'],
        number_format((float)$fattura['importo_totale'], 2, '.', ''),
        $fattura['stato']
    ]);
}

fclose($out);
