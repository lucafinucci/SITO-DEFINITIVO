<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/fatture-email.php';

header('Content-Type: application/json; charset=utf-8');

$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$fatturaId = (int)($input['fattura_id'] ?? 0);

if ($fatturaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID fattura non valido']);
    exit;
}

$result = sendFatturaEmail($pdo, $fatturaId);

if (!$result['success']) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Errore invio email']);
    exit;
}

$stmt = $pdo->prepare('UPDATE fatture SET stato = "inviata" WHERE id = :id AND stato IN ("bozza", "emessa")');
$stmt->execute(['id' => $fatturaId]);

echo json_encode([
    'success' => true,
    'message' => $result['message'] ?? 'Email inviata con successo'
]);
