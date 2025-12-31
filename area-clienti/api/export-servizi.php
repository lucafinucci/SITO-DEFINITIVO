<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/config.php';

// Verifica admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();
if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    exit('Accesso negato');
}

$marginPct = (float)Config::get('SERVICE_MARGIN_PCT', 35);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="servizi.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['id', 'nome', 'codice', 'prezzo_mensile', 'attivi', 'totali', 'mrr', 'margine_stimato']);

$stmt = $pdo->prepare('
    SELECT
        s.id,
        s.nome,
        s.codice,
        s.prezzo_mensile,
        COALESCE(SUM(CASE WHEN us.stato = "attivo" THEN COALESCE(pp.prezzo_mensile, s.prezzo_mensile) END), 0) AS mrr,
        SUM(CASE WHEN us.stato = "attivo" THEN 1 ELSE 0 END) AS attivi,
        COUNT(us.id) AS totali
    FROM servizi s
    LEFT JOIN utenti_servizi us ON us.servizio_id = s.id
    LEFT JOIN clienti_prezzi_personalizzati pp
        ON pp.cliente_id = us.user_id AND pp.servizio_id = s.id
    GROUP BY s.id
    ORDER BY totali DESC, attivi DESC, s.nome ASC
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    $mrr = (float)$row['mrr'];
    $margin = $mrr * ($marginPct / 100);
    fputcsv($out, [
        $row['id'],
        $row['nome'],
        $row['codice'],
        number_format((float)$row['prezzo_mensile'], 2, '.', ''),
        $row['attivi'],
        $row['totali'],
        number_format($mrr, 2, '.', ''),
        number_format($margin, 2, '.', '')
    ]);
}
fclose($out);
