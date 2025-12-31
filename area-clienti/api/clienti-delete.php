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

if ($clienteId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cliente non valido']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM utenti WHERE id = :id AND ruolo != "admin"');
    $stmt->execute(['id' => $clienteId]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Cliente non trovato']);
        exit;
    }

    $deleteStmt = $pdo->prepare('DELETE FROM utenti WHERE id = :id AND ruolo != "admin"');
    $deleteStmt->execute(['id' => $clienteId]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database']);
}
