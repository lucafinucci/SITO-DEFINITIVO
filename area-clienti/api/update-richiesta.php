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
$richiestaId = (int)($input['richiesta_id'] ?? 0);
$stato = $input['stato'] ?? '';

$statiValidi = ['in_attesa', 'in_lavorazione', 'completato', 'annullato'];
if ($richiestaId <= 0 || !in_array($stato, $statiValidi, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati non validi']);
    exit;
}

// Verifica esistenza richiesta
$stmt = $pdo->prepare('SELECT id, stato FROM richieste_addestramento WHERE id = :id');
$stmt->execute(['id' => $richiestaId]);
$richiesta = $stmt->fetch();

if (!$richiesta) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Richiesta non trovata']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE richieste_addestramento SET stato = :stato WHERE id = :id');
    $stmt->execute(['stato' => $stato, 'id' => $richiestaId]);

    echo json_encode([
        'success' => true,
        'message' => 'Stato aggiornato',
        'richiesta_id' => $richiestaId,
        'stato' => $stato
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
