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

if ($action === 'cancel') {
    $acquistoId = $input['acquisto_id'] ?? null;
    if (!$acquistoId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID acquisto mancante']);
        exit;
    }
    $stmt = $pdo->prepare('
        UPDATE clienti_acquisti_onetime
        SET stato = "annullato"
        WHERE id = :id AND stato = "da_fatturare"
    ');
    $stmt->execute(['id' => $acquistoId]);
    echo json_encode(['success' => true]);
    exit;
}

$clienteId = $input['cliente_id'] ?? null;
$servizioId = $input['servizio_id'] ?? null;
$quantita = $input['quantita'] ?? 1;
$dataAcquisto = $input['data_acquisto'] ?? date('Y-m-d');
$prezzoOverride = $input['prezzo_unitario'] ?? null;

if (!$clienteId || !$servizioId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$cliente = $stmt->fetch();
if (!$cliente || $cliente['ruolo'] === 'admin') {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Cliente non valido']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, prezzo_unitario FROM servizi_on_demand WHERE id = :id AND attivo = 1');
$stmt->execute(['id' => $servizioId]);
$servizio = $stmt->fetch();
if (!$servizio) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Servizio on-demand non valido']);
    exit;
}

if (!is_numeric($quantita) || (float)$quantita <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Quantita non valida']);
    exit;
}

$prezzo = $servizio['prezzo_unitario'];
if ($prezzoOverride !== null && $prezzoOverride !== '') {
    if (!is_numeric($prezzoOverride) || (float)$prezzoOverride < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Prezzo non valido']);
        exit;
    }
    $prezzo = (float)$prezzoOverride;
}

$totale = round(((float)$quantita) * ((float)$prezzo), 2);

$stmt = $pdo->prepare('
    INSERT INTO clienti_acquisti_onetime (cliente_id, servizio_id, quantita, prezzo_unitario, totale, data_acquisto, stato)
    VALUES (:cliente_id, :servizio_id, :quantita, :prezzo_unitario, :totale, :data_acquisto, "da_fatturare")
');
$stmt->execute([
    'cliente_id' => $clienteId,
    'servizio_id' => $servizioId,
    'quantita' => $quantita,
    'prezzo_unitario' => $prezzo,
    'totale' => $totale,
    'data_acquisto' => $dataAcquisto
]);

echo json_encode(['success' => true]);
