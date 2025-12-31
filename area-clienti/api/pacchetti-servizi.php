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
$action = $input['action'] ?? 'add';
$pacchettoId = $input['pacchetto_id'] ?? null;
$servizioId = $input['servizio_id'] ?? null;

if (!$pacchettoId || !$servizioId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM pacchetti WHERE id = :id');
$stmt->execute(['id' => $pacchettoId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Pacchetto non valido']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM servizi WHERE id = :id');
$stmt->execute(['id' => $servizioId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Servizio non valido']);
    exit;
}

if ($action === 'remove') {
    $stmt = $pdo->prepare('
        DELETE FROM pacchetti_servizi
        WHERE pacchetto_id = :pacchetto_id AND servizio_id = :servizio_id
    ');
    $stmt->execute([
        'pacchetto_id' => $pacchettoId,
        'servizio_id' => $servizioId
    ]);
    echo json_encode(['success' => true]);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO pacchetti_servizi (pacchetto_id, servizio_id)
        VALUES (:pacchetto_id, :servizio_id)
    ');
    $stmt->execute([
        'pacchetto_id' => $pacchettoId,
        'servizio_id' => $servizioId
    ]);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Servizio già nel pacchetto']);
    exit;
}

echo json_encode(['success' => true]);
