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

$clienteId = (int)($_POST['cliente_id'] ?? 0);
if ($clienteId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cliente mancante']);
    exit;
}

if (empty($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Upload non valido']);
    exit;
}

$file = $_FILES['documento'];
$originalName = basename($file['name']);
$safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
$ext = pathinfo($safeName, PATHINFO_EXTENSION);
$uniqueName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');

$baseDir = __DIR__ . '/../uploads/clienti/' . $clienteId;
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0775, true);
}

$destPath = $baseDir . '/' . $uniqueName;
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Salvataggio file fallito']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO clienti_documenti (cliente_id, nome, file_path, mime_type, file_size, created_by)
        VALUES (:cliente_id, :nome, :file_path, :mime_type, :file_size, :created_by)
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'nome' => $originalName,
        'file_path' => '/area-clienti/uploads/clienti/' . $clienteId . '/' . $uniqueName,
        'mime_type' => $file['type'] ?? 'application/octet-stream',
        'file_size' => (int)$file['size'],
        'created_by' => $_SESSION['cliente_id']
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
