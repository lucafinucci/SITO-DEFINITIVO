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
$action = $input['action'] ?? 'set';
$clienteId = $input['cliente_id'] ?? null;
$servizioId = $input['servizio_id'] ?? null;

if (!$clienteId || !$servizioId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

if ($action === 'delete') {
    $stmt = $pdo->prepare('
        DELETE FROM clienti_quote
        WHERE cliente_id = :cliente_id AND servizio_id = :servizio_id
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'servizio_id' => $servizioId
    ]);
    echo json_encode(['success' => true]);
    exit;
}

$quota = $input['quota_documenti_mese'] ?? null;
if ($quota !== null && (!is_numeric($quota) || (int)$quota < 0)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Quota non valida']);
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO clienti_quote (cliente_id, servizio_id, quota_documenti_mese)
    VALUES (:cliente_id, :servizio_id, :quota)
    ON DUPLICATE KEY UPDATE quota_documenti_mese = VALUES(quota_documenti_mese)
');
$stmt->execute([
    'cliente_id' => $clienteId,
    'servizio_id' => $servizioId,
    'quota' => $quota
]);

echo json_encode(['success' => true]);
