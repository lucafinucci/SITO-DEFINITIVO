<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Verifica admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();
if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID mancante']);
    exit;
}

$stmt = $pdo->prepare('
    SELECT id, nome_azienda, referente, email, stato, scadenza, sconto_percentuale, note
    FROM preventivi
    WHERE id = :id
');
$stmt->execute(['id' => $id]);
$preventivo = $stmt->fetch();
if (!$preventivo) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Preventivo non trovato']);
    exit;
}

$stmt = $pdo->prepare('
    SELECT descrizione, quantita, prezzo_unitario
    FROM preventivi_voci
    WHERE preventivo_id = :id
');
$stmt->execute(['id' => $id]);
$voci = $stmt->fetchAll();

echo json_encode(['success' => true, 'preventivo' => $preventivo, 'voci' => $voci]);
