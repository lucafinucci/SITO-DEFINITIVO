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
$action = $input['action'] ?? 'add';
$tagName = trim((string)($input['tag'] ?? ''));
$tagId = (int)($input['tag_id'] ?? 0);
$color = trim((string)($input['color'] ?? ''));

if ($clienteId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cliente mancante']);
    exit;
}

try {
    if ($action === 'remove' && $tagId > 0) {
        $stmt = $pdo->prepare('
            DELETE FROM clienti_tag_rel WHERE cliente_id = :cliente_id AND tag_id = :tag_id
        ');
        $stmt->execute(['cliente_id' => $clienteId, 'tag_id' => $tagId]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'color' && $tagId > 0) {
        $stmt = $pdo->prepare('UPDATE clienti_tag SET colore = :colore WHERE id = :id');
        $stmt->execute([
            'colore' => $color !== '' ? $color : null,
            'id' => $tagId
        ]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($tagId <= 0 && $tagName === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Tag mancante']);
        exit;
    }

    if ($tagId <= 0) {
        // Crea tag se non esiste
        $stmt = $pdo->prepare('SELECT id FROM clienti_tag WHERE nome = :nome');
        $stmt->execute(['nome' => $tagName]);
        $existing = $stmt->fetch();
        if ($existing) {
            $tagId = (int)$existing['id'];
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO clienti_tag (nome, colore)
                VALUES (:nome, :colore)
            ');
            $stmt->execute([
                'nome' => $tagName,
                'colore' => $color !== '' ? $color : null
            ]);
            $tagId = (int)$pdo->lastInsertId();
        }
    } elseif ($color !== '') {
        $stmt = $pdo->prepare('UPDATE clienti_tag SET colore = :colore WHERE id = :id');
        $stmt->execute([
            'colore' => $color,
            'id' => $tagId
        ]);
    }

    // Associa tag al cliente
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO clienti_tag_rel (cliente_id, tag_id)
        VALUES (:cliente_id, :tag_id)
    ');
    $stmt->execute(['cliente_id' => $clienteId, 'tag_id' => $tagId]);

    echo json_encode(['success' => true, 'tag_id' => $tagId, 'tag' => $tagName]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
