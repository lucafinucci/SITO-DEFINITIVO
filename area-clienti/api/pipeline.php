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
$statiValidi = ['proposta', 'negoziazione', 'vinto', 'perso'];

try {
    if ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $stato = $input['stato'] ?? '';
        if ($id <= 0 || !in_array($stato, $statiValidi, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dati non validi']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE pipeline_trattative SET stato = :stato WHERE id = :id');
        $stmt->execute(['stato' => $stato, 'id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID mancante']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM pipeline_trattative WHERE id = :id');
        $stmt->execute(['id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    $nomeAzienda = trim((string)($input['nome_azienda'] ?? ''));
    $referente = trim((string)($input['referente'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $valore = (float)($input['valore_previsto'] ?? 0);
    $note = trim((string)($input['note'] ?? ''));
    $stato = $input['stato'] ?? 'proposta';

    if ($nomeAzienda === '' || !in_array($stato, $statiValidi, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
        exit;
    }

    $stmt = $pdo->prepare('
        INSERT INTO pipeline_trattative
        (nome_azienda, referente, email, valore_previsto, stato, note, created_by)
        VALUES (:nome_azienda, :referente, :email, :valore_previsto, :stato, :note, :created_by)
    ');
    $stmt->execute([
        'nome_azienda' => $nomeAzienda,
        'referente' => $referente,
        'email' => $email,
        'valore_previsto' => $valore,
        'stato' => $stato,
        'note' => $note,
        'created_by' => $_SESSION['cliente_id']
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
