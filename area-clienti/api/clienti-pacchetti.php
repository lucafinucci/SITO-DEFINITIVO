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

// Verifica CSRF
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (!$csrfToken || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token non valido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'assign';

if ($action === 'remove') {
    $assignmentId = $input['assignment_id'] ?? null;
    if (!$assignmentId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID assegnazione mancante']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM clienti_pacchetti WHERE id = :id');
    $stmt->execute(['id' => $assignmentId]);
    echo json_encode(['success' => true]);
    exit;
}

$clienteId = $input['cliente_id'] ?? null;
$pacchettoId = $input['pacchetto_id'] ?? null;
$dataInizio = $input['data_inizio'] ?? null;
$dataFine = $input['data_fine'] ?? null;

if (!$clienteId || !$pacchettoId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$cliente = $stmt->fetch();
if (!$cliente || $cliente['ruolo'] === 'admin') {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Cliente non valido']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, attivo FROM pacchetti WHERE id = :id');
$stmt->execute(['id' => $pacchettoId]);
$pacchetto = $stmt->fetch();
if (!$pacchetto) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Pacchetto non valido']);
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO clienti_pacchetti (cliente_id, pacchetto_id, data_inizio, data_fine, attivo)
    VALUES (:cliente_id, :pacchetto_id, :data_inizio, :data_fine, 1)
');
$stmt->execute([
    'cliente_id' => $clienteId,
    'pacchetto_id' => $pacchettoId,
    'data_inizio' => $dataInizio ?: null,
    'data_fine' => $dataFine ?: null
]);

echo json_encode(['success' => true]);
