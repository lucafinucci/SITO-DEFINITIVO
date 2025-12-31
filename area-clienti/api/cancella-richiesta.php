<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Verifica che sia admin
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

// Leggi dati POST
$input = json_decode(file_get_contents('php://input'), true);
$richiestaId = $input['richiesta_id'] ?? null;

// Validazione
if (!$richiestaId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID richiesta mancante']);
    exit;
}

// Verifica che la richiesta esista
$stmt = $pdo->prepare('
    SELECT id, user_id
    FROM richieste_addestramento
    WHERE id = :id
');
$stmt->execute(['id' => $richiestaId]);
$richiesta = $stmt->fetch();

if (!$richiesta) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Richiesta non trovata']);
    exit;
}

try {
    // Inizia transazione
    $pdo->beginTransaction();

    // Recupera i file per cancellarli fisicamente
    $stmt = $pdo->prepare('
        SELECT file_path
        FROM richieste_addestramento_files
        WHERE richiesta_id = :richiesta_id
    ');
    $stmt->execute(['richiesta_id' => $richiestaId]);
    $files = $stmt->fetchAll();

    // Cancella i file dal filesystem
    foreach ($files as $file) {
        $filePath = __DIR__ . '/../' . $file['file_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // Cancella i record dei file dal database
    $stmt = $pdo->prepare('
        DELETE FROM richieste_addestramento_files
        WHERE richiesta_id = :richiesta_id
    ');
    $stmt->execute(['richiesta_id' => $richiestaId]);

    // Cancella la richiesta
    $stmt = $pdo->prepare('
        DELETE FROM richieste_addestramento
        WHERE id = :id
    ');
    $stmt->execute(['id' => $richiestaId]);

    // Commit transazione
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Richiesta cancellata con successo'
    ]);

} catch (PDOException $e) {
    // Rollback in caso di errore
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
