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
    $servizioId = $input['servizio_id'] ?? null;
    $attivo = $input['attivo'] ?? null;
    if (!$servizioId || !in_array((int)$attivo, [0, 1], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dati non validi']);
        exit;
    }
    $stmt = $pdo->prepare('UPDATE servizi_on_demand SET attivo = :attivo WHERE id = :id');
    $stmt->execute(['attivo' => (int)$attivo, 'id' => $servizioId]);
    echo json_encode(['success' => true]);
    exit;
}

$nome = trim($input['nome'] ?? '');
$prezzo = $input['prezzo_unitario'] ?? null;
$descrizione = $input['descrizione'] ?? null;

if (!$nome || !is_numeric($prezzo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO servizi_on_demand (nome, descrizione, prezzo_unitario, attivo)
    VALUES (:nome, :descrizione, :prezzo, 1)
');
$stmt->execute([
    'nome' => $nome,
    'descrizione' => $descrizione ?: null,
    'prezzo' => $prezzo
]);

echo json_encode(['success' => true]);
