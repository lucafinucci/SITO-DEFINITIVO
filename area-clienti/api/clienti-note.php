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
$clienteId = (int)($input['cliente_id'] ?? 0);
$noteId = (int)($input['note_id'] ?? 0);
$note = trim((string)($input['note'] ?? ''));

try {
    if ($action === 'delete' && $noteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM clienti_note WHERE id = :id');
        $stmt->execute(['id' => $noteId]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'update' && $noteId > 0) {
        if ($note === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nota vuota']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE clienti_note SET note = :note WHERE id = :id');
        $stmt->execute(['note' => $note, 'id' => $noteId]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($clienteId <= 0 || $note === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
        exit;
    }

    $stmt = $pdo->prepare('
        INSERT INTO clienti_note (cliente_id, note, created_by)
        VALUES (:cliente_id, :note, :created_by)
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'note' => $note,
        'created_by' => $_SESSION['cliente_id']
    ]);

    echo json_encode(['success' => true, 'note_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
