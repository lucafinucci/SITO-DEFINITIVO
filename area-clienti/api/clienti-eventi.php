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
$clienteId = (int)($input['cliente_id'] ?? 0);
$titolo = trim((string)($input['titolo'] ?? ''));
$dettagli = trim((string)($input['dettagli'] ?? ''));
$tipo = trim((string)($input['tipo'] ?? 'manuale'));

if ($clienteId <= 0 || $titolo === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$allowedTypes = ['manuale', 'email', 'sistema'];
if (!in_array($tipo, $allowedTypes, true)) {
    $tipo = 'manuale';
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO clienti_eventi (cliente_id, tipo, titolo, dettagli, created_by)
        VALUES (:cliente_id, :tipo, :titolo, :dettagli, :created_by)
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'tipo' => $tipo,
        'titolo' => $titolo,
        'dettagli' => $dettagli,
        'created_by' => $_SESSION['cliente_id']
    ]);

    echo json_encode(['success' => true, 'evento_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
