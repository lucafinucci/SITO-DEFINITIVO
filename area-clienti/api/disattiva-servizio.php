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

$utenteServizioId = $input['utente_servizio_id'] ?? null;

// Validazione
if (!$utenteServizioId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID servizio mancante']);
    exit;
}

// Verifica che il record esista (ora a livello aziendale)
$stmt = $pdo->prepare('
    SELECT ase.id, s.nome as servizio_nome, a.nome as cliente_nome
    FROM aziende_servizi ase
    JOIN servizi s ON ase.servizio_id = s.id
    JOIN aziende a ON ase.azienda_id = a.id
    WHERE ase.id = :id AND ase.stato = "attivo"
');
$stmt->execute(['id' => $utenteServizioId]);
$record = $stmt->fetch();

if (!$record) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Servizio non trovato o già disattivato']);
    exit;
}

// Disattiva il servizio a livello aziendale
try {
    $stmt = $pdo->prepare('
        UPDATE aziende_servizi
        SET stato = "disattivato",
            data_disattivazione = CURDATE()
        WHERE id = :id
    ');

    $stmt->execute(['id' => $utenteServizioId]);

    echo json_encode([
        'success' => true,
        'message' => 'Servizio disattivato con successo',
        'servizio_nome' => $record['servizio_nome'],
        'cliente_nome' => $record['cliente_nome']
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
