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
$servizioId = $input['servizio_id'] ?? null;
$nome = trim($input['nome'] ?? '');
$codice = trim($input['codice'] ?? '');
$prezzo = $input['prezzo_mensile'] ?? null;
$costoPagina = $input['costo_per_pagina'] ?? null;
$quota = $input['quota_documenti_mese'] ?? null;
$attivo = isset($input['attivo']) ? (int)$input['attivo'] : 1;
$descrizione = $input['descrizione'] ?? null;

if (!$servizioId || !$nome || !$codice) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM servizi WHERE id = :id');
$stmt->execute(['id' => $servizioId]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$current) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Servizio non trovato']);
    exit;
}

$stmt = $pdo->prepare('SELECT quota_documenti_mese FROM servizi_quote WHERE servizio_id = :id');
$stmt->execute(['id' => $servizioId]);
$currentQuota = $stmt->fetchColumn();
$currentQuota = $currentQuota !== false ? (int)$currentQuota : null;

$quotaValue = null;
if ($quota !== '' && $quota !== null) {
    if (!is_numeric($quota) || (int)$quota < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Quota non valida']);
        exit;
    }
    $quotaValue = (int)$quota;
}

$costoPaginaValue = (float)$current['costo_per_pagina'];
if ($costoPagina !== '' && $costoPagina !== null) {
    if (!is_numeric($costoPagina) || (float)$costoPagina < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Costo per pagina non valido']);
        exit;
    }
    $costoPaginaValue = (float)$costoPagina;
}

$newData = [
    'nome' => $nome,
    'codice' => $codice,
    'descrizione' => $descrizione,
    'prezzo_mensile' => $prezzo !== '' ? (float)$prezzo : null,
    'costo_per_pagina' => $costoPaginaValue,
    'attivo' => $attivo ? 1 : 0,
    'quota_documenti_mese' => $quotaValue
];

$oldData = [
    'nome' => $current['nome'],
    'codice' => $current['codice'],
    'descrizione' => $current['descrizione'],
    'prezzo_mensile' => $current['prezzo_mensile'],
    'costo_per_pagina' => $current['costo_per_pagina'],
    'attivo' => (int)$current['attivo'],
    'quota_documenti_mese' => $currentQuota
];

$changed = [];
foreach ($newData as $field => $value) {
    $oldValue = $oldData[$field];
    if ((string)$value !== (string)$oldValue) {
        $changed[$field] = ['old' => $oldValue, 'new' => $value];
    }
}

if (empty($changed)) {
    echo json_encode(['success' => true, 'message' => 'Nessuna modifica']);
    exit;
}

// Verifica codice univoco se cambiato
if ($codice !== $current['codice']) {
    $stmt = $pdo->prepare('SELECT id FROM servizi WHERE codice = :codice AND id != :id');
    $stmt->execute(['codice' => $codice, 'id' => $servizioId]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Codice servizio già esistente']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare('
        UPDATE servizi
        SET nome = :nome,
            codice = :codice,
            descrizione = :descrizione,
            prezzo_mensile = :prezzo_mensile,
            costo_per_pagina = :costo_per_pagina,
            attivo = :attivo
        WHERE id = :id
    ');
    $stmt->execute([
        'nome' => $newData['nome'],
        'codice' => $newData['codice'],
        'descrizione' => $newData['descrizione'],
        'prezzo_mensile' => $newData['prezzo_mensile'],
        'costo_per_pagina' => $newData['costo_per_pagina'],
        'attivo' => $newData['attivo'],
        'id' => $servizioId
    ]);

    $stmt = $pdo->prepare('
        INSERT INTO servizi_quote (servizio_id, quota_documenti_mese)
        VALUES (:servizio_id, :quota)
        ON DUPLICATE KEY UPDATE quota_documenti_mese = VALUES(quota_documenti_mese)
    ');
    $stmt->execute([
        'servizio_id' => $servizioId,
        'quota' => $newData['quota_documenti_mese']
    ]);

    $action = 'update';
    if (count($changed) === 1 && isset($changed['attivo'])) {
        $action = $newData['attivo'] ? 'activate' : 'deactivate';
    }

    $stmt = $pdo->prepare('
        INSERT INTO servizi_versioni (servizio_id, action, changed_fields, old_data, new_data, changed_by)
        VALUES (:servizio_id, :action, :changed_fields, :old_data, :new_data, :changed_by)
    ');
    $stmt->execute([
        'servizio_id' => $servizioId,
        'action' => $action,
        'changed_fields' => json_encode($changed, JSON_UNESCAPED_UNICODE),
        'old_data' => json_encode($oldData, JSON_UNESCAPED_UNICODE),
        'new_data' => json_encode($newData, JSON_UNESCAPED_UNICODE),
        'changed_by' => $_SESSION['cliente_id']
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database']);
}
