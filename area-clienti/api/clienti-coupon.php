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
    $stmt = $pdo->prepare('DELETE FROM clienti_coupon WHERE id = :id');
    $stmt->execute(['id' => $assignmentId]);
    echo json_encode(['success' => true]);
    exit;
}

$clienteId = $input['cliente_id'] ?? null;
$couponId = $input['coupon_id'] ?? null;

if (!$clienteId || !$couponId) {
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

$stmt = $pdo->prepare('SELECT id, attivo FROM coupon WHERE id = :id');
$stmt->execute(['id' => $couponId]);
$coupon = $stmt->fetch();
if (!$coupon) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Coupon non valido']);
    exit;
}
if (!(int)$coupon['attivo']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Coupon disattivato']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO clienti_coupon (cliente_id, coupon_id)
        VALUES (:cliente_id, :coupon_id)
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'coupon_id' => $couponId
    ]);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Coupon già assegnato']);
    exit;
}

echo json_encode(['success' => true]);
