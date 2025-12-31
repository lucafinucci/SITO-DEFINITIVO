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
header('Content-Disposition: attachment; filename="clienti.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['id', 'nome', 'cognome', 'email', 'azienda', 'created_at', 'servizi_attivi', 'mrr']);

$stmt = $pdo->prepare('
    SELECT
        u.id,
        u.nome,
        u.cognome,
        u.email,
        u.azienda,
        u.created_at,
        COUNT(CASE WHEN us.stato = "attivo" THEN 1 END) AS servizi_attivi,
        COALESCE(SUM(CASE WHEN us.stato = "attivo" THEN COALESCE(pp.prezzo_mensile, s.prezzo_mensile) END), 0) AS mrr
    FROM utenti u
    LEFT JOIN utenti_servizi us ON us.user_id = u.id
    LEFT JOIN servizi s ON s.id = us.servizio_id
    LEFT JOIN clienti_prezzi_personalizzati pp
        ON pp.cliente_id = u.id AND pp.servizio_id = s.id
    WHERE u.ruolo != "admin"
    GROUP BY u.id
    ORDER BY u.azienda ASC, u.cognome ASC
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    fputcsv($out, [
        $row['id'],
        $row['nome'],
        $row['cognome'],
        $row['email'],
        $row['azienda'],
        $row['created_at'],
        $row['servizi_attivi'],
        number_format((float)$row['mrr'], 2, '.', '')
    ]);
}
fclose($out);
