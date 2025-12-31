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

if ($action === 'toggle') {
    $couponId = $input['coupon_id'] ?? null;
    $attivo = $input['attivo'] ?? null;
    if (!$couponId || !in_array((int)$attivo, [0, 1], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dati non validi']);
        exit;
    }
    $stmt = $pdo->prepare('UPDATE coupon SET attivo = :attivo WHERE id = :id');
    $stmt->execute(['attivo' => (int)$attivo, 'id' => $couponId]);
    echo json_encode(['success' => true]);
    exit;
}

$codice = strtoupper(trim($input['codice'] ?? ''));
$tipo = $input['tipo'] ?? 'percentuale';
$valore = $input['valore'] ?? null;
$dataInizio = $input['data_inizio'] ?? null;
$dataFine = $input['data_fine'] ?? null;
$maxUsi = $input['max_usi'] ?? null;

if (!$codice || !is_numeric($valore)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

if (!in_array($tipo, ['percentuale', 'fisso'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tipo coupon non valido']);
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO coupon (codice, tipo, valore, data_inizio, data_fine, max_usi, attivo)
    VALUES (:codice, :tipo, :valore, :data_inizio, :data_fine, :max_usi, 1)
');

try {
    $stmt->execute([
        'codice' => $codice,
        'tipo' => $tipo,
        'valore' => $valore,
        'data_inizio' => $dataInizio ?: null,
        'data_fine' => $dataFine ?: null,
        'max_usi' => $maxUsi !== '' ? $maxUsi : null
    ]);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Codice già esistente']);
    exit;
}

echo json_encode(['success' => true]);
