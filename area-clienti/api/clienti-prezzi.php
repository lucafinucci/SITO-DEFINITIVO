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
$clienteId = $input['cliente_id'] ?? null;
$servizioId = $input['servizio_id'] ?? null;
$action = $input['action'] ?? 'set';

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

$stmt = $pdo->prepare('SELECT id FROM servizi WHERE id = :id');
$stmt->execute(['id' => $servizioId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Servizio non valido']);
    exit;
}

if ($action === 'delete') {
    $stmt = $pdo->prepare('
        DELETE FROM clienti_prezzi_personalizzati
        WHERE cliente_id = :cliente_id AND servizio_id = :servizio_id
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'servizio_id' => $servizioId
    ]);
    echo json_encode(['success' => true]);
    exit;
}

$prezzo = $input['prezzo_mensile'] ?? null;
$costoPagina = $input['costo_per_pagina'] ?? null;
$hasPrezzo = $prezzo !== null && $prezzo !== '';
$hasCosto = $costoPagina !== null && $costoPagina !== '';

if (!$hasPrezzo && !$hasCosto) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Inserisci un prezzo o un costo per pagina']);
    exit;
}

if ($hasPrezzo && (!is_numeric($prezzo) || $prezzo < 0)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Prezzo non valido']);
    exit;
}

if ($hasCosto && (!is_numeric($costoPagina) || $costoPagina < 0)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Costo per pagina non valido']);
    exit;
}

$stmt = $pdo->prepare('
    SELECT prezzo_mensile, costo_per_pagina
    FROM clienti_prezzi_personalizzati
    WHERE cliente_id = :cliente_id AND servizio_id = :servizio_id
');
$stmt->execute([
    'cliente_id' => $clienteId,
    'servizio_id' => $servizioId
]);
$current = $stmt->fetch();

if (!$current && !$hasPrezzo) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Inserisci un prezzo mensile per creare il record']);
    exit;
}

$finalPrezzo = $hasPrezzo ? (float)$prezzo : (float)$current['prezzo_mensile'];
$finalCosto = $hasCosto ? (float)$costoPagina : ($current['costo_per_pagina'] ?? null);

$stmt = $pdo->prepare('
    INSERT INTO clienti_prezzi_personalizzati (cliente_id, servizio_id, prezzo_mensile, costo_per_pagina)
    VALUES (:cliente_id, :servizio_id, :prezzo, :costo_pagina)
    ON DUPLICATE KEY UPDATE
        prezzo_mensile = VALUES(prezzo_mensile),
        costo_per_pagina = VALUES(costo_per_pagina)
');
$stmt->execute([
    'cliente_id' => $clienteId,
    'servizio_id' => $servizioId,
    'prezzo' => $finalPrezzo,
    'costo_pagina' => $finalCosto
]);

echo json_encode(['success' => true]);
