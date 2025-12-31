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
$action = $input['action'] ?? 'create';

if ($action === 'delete') {
    $discountId = $input['discount_id'] ?? null;
    if (!$discountId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID sconto mancante']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM clienti_sconti WHERE id = :id');
    $stmt->execute(['id' => $discountId]);
    echo json_encode(['success' => true]);
    exit;
}

$clienteId = $input['cliente_id'] ?? null;
$servizioId = $input['servizio_id'] ?? null;
$tipo = $input['tipo'] ?? 'percentuale';
$valore = $input['valore'] ?? null;
$dataInizio = $input['data_inizio'] ?? null;
$dataFine = $input['data_fine'] ?? null;
$note = $input['note'] ?? null;

if (!$clienteId || !is_numeric($valore)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

if (!in_array($tipo, ['percentuale', 'fisso'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tipo sconto non valido']);
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

if ($servizioId) {
    $stmt = $pdo->prepare('SELECT id FROM servizi WHERE id = :id');
    $stmt->execute(['id' => $servizioId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Servizio non valido']);
        exit;
    }
} else {
    $servizioId = null;
}

$stmt = $pdo->prepare('
    INSERT INTO clienti_sconti (cliente_id, servizio_id, tipo, valore, data_inizio, data_fine, note, attivo)
    VALUES (:cliente_id, :servizio_id, :tipo, :valore, :data_inizio, :data_fine, :note, 1)
');
$stmt->execute([
    'cliente_id' => $clienteId,
    'servizio_id' => $servizioId,
    'tipo' => $tipo,
    'valore' => $valore,
    'data_inizio' => $dataInizio ?: null,
    'data_fine' => $dataFine ?: null,
    'note' => $note ?: null
]);

echo json_encode(['success' => true]);
