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
header('Content-Disposition: attachment; filename="uso_servizi_clienti.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['cliente_id', 'azienda', 'email', 'servizi_attivi', 'servizi_totali', 'mrr']);

$stmt = $pdo->prepare('
    SELECT
        u.id AS cliente_id,
        u.azienda,
        u.email,
        SUM(CASE WHEN us.stato = "attivo" THEN 1 ELSE 0 END) AS servizi_attivi,
        COUNT(us.id) AS servizi_totali,
        COALESCE(SUM(CASE WHEN us.stato = "attivo" THEN COALESCE(pp.prezzo_mensile, s.prezzo_mensile) END), 0) AS mrr
    FROM utenti u
    LEFT JOIN utenti_servizi us ON us.user_id = u.id
    LEFT JOIN servizi s ON s.id = us.servizio_id
    LEFT JOIN clienti_prezzi_personalizzati pp
        ON pp.cliente_id = u.id AND pp.servizio_id = s.id
    WHERE u.ruolo != "admin"
    GROUP BY u.id
    ORDER BY u.azienda ASC, u.email ASC
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    fputcsv($out, [
        $row['cliente_id'],
        $row['azienda'],
        $row['email'],
        $row['servizi_attivi'],
        $row['servizi_totali'],
        number_format((float)$row['mrr'], 2, '.', '')
    ]);
}
fclose($out);
