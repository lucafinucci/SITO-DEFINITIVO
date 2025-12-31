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

$userId = $input['user_id'] ?? null;
$servizioId = $input['servizio_id'] ?? null;
$dataAttivazione = $input['data_attivazione'] ?? date('Y-m-d');
$note = $input['note'] ?? null;

// Validazione
if (!$userId || !$servizioId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

// Verifica che l'utente esista
$stmt = $pdo->prepare('SELECT id FROM utenti WHERE id = :id');
$stmt->execute(['id' => $userId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
    exit;
}

// Verifica che il servizio esista
$stmt = $pdo->prepare('SELECT id, nome FROM servizi WHERE id = :id AND attivo = 1');
$stmt->execute(['id' => $servizioId]);
$servizio = $stmt->fetch();
if (!$servizio) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Servizio non trovato']);
    exit;
}

// Ottieni azienda_id dell'utente
$stmt = $pdo->prepare('SELECT azienda_id FROM utenti WHERE id = :id');
$stmt->execute(['id' => $userId]);
$aziendaId = $stmt->fetchColumn();

if (!$aziendaId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Utente non associato a nessuna azienda']);
    exit;
}

// Verifica che il servizio non sia già attivo per questa azienda
$stmt = $pdo->prepare('
    SELECT id FROM aziende_servizi
    WHERE azienda_id = :azienda_id AND servizio_id = :servizio_id AND stato = "attivo"
');
$stmt->execute([
    'azienda_id' => $aziendaId,
    'servizio_id' => $servizioId
]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Servizio già attivo per questa azienda']);
    exit;
}

// Attiva il servizio a livello aziendale
try {
    $stmt = $pdo->prepare('
        INSERT INTO aziende_servizi (azienda_id, servizio_id, data_attivazione, stato, note)
        VALUES (:azienda_id, :servizio_id, :data_attivazione, "attivo", :note)
    ');

    $stmt->execute([
        'azienda_id' => $aziendaId,
        'servizio_id' => $servizioId,
        'data_attivazione' => $dataAttivazione,
        'note' => $note
    ]);

    // Invia email automatica di conferma attivazione
    require_once __DIR__ . '/../includes/email-hooks.php';
    onServizioAttivato($pdo, $userId, $servizioId);

    echo json_encode([
        'success' => true,
        'message' => 'Servizio attivato con successo',
        'servizio_nome' => $servizio['nome']
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
