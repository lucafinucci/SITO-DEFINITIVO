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
$statiValidi = ['attivo', 'in_rinnovo', 'rinnovato', 'scaduto'];

$hasContrattoServizio = false;
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM clienti_contratti LIKE 'servizio_id'");
    $stmt->execute();
    $hasContrattoServizio = (bool)$stmt->fetch();
} catch (PDOException $e) {
    $hasContrattoServizio = false;
}

try {
    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID mancante']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM clienti_contratti WHERE id = :id');
        $stmt->execute(['id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'update-status') {
        $id = (int)($input['id'] ?? 0);
        $stato = $input['stato'] ?? '';
        if ($id <= 0 || !in_array($stato, $statiValidi, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dati non validi']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE clienti_contratti SET stato = :stato WHERE id = :id');
        $stmt->execute(['stato' => $stato, 'id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    $clienteId = (int)($input['cliente_id'] ?? 0);
    $servizioId = (int)($input['servizio_id'] ?? 0);
    $titolo = trim((string)($input['titolo'] ?? ''));
    $dataInizio = trim((string)($input['data_inizio'] ?? ''));
    $dataScadenza = trim((string)($input['data_scadenza'] ?? ''));
    $valoreAnn = (float)($input['valore_annuo'] ?? 0);
    $stato = $input['stato'] ?? 'attivo';
    $note = trim((string)($input['note'] ?? ''));

    if ($clienteId <= 0 || $titolo === '' || $dataScadenza === '' || !in_array($stato, $statiValidi, true) || ($hasContrattoServizio && $servizioId <= 0)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
        exit;
    }

    if ($hasContrattoServizio) {
        $stmt = $pdo->prepare('
            INSERT INTO clienti_contratti
            (cliente_id, servizio_id, titolo, data_inizio, data_scadenza, valore_annuo, stato, note, created_by)
            VALUES (:cliente_id, :servizio_id, :titolo, :data_inizio, :data_scadenza, :valore_annuo, :stato, :note, :created_by)
        ');
        $stmt->execute([
            'cliente_id' => $clienteId,
            'servizio_id' => $servizioId,
            'titolo' => $titolo,
            'data_inizio' => $dataInizio !== '' ? $dataInizio : null,
            'data_scadenza' => $dataScadenza,
            'valore_annuo' => $valoreAnn,
            'stato' => $stato,
            'note' => $note,
            'created_by' => $_SESSION['cliente_id']
        ]);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO clienti_contratti
            (cliente_id, titolo, data_inizio, data_scadenza, valore_annuo, stato, note, created_by)
            VALUES (:cliente_id, :titolo, :data_inizio, :data_scadenza, :valore_annuo, :stato, :note, :created_by)
        ');
        $stmt->execute([
            'cliente_id' => $clienteId,
            'titolo' => $titolo,
            'data_inizio' => $dataInizio !== '' ? $dataInizio : null,
            'data_scadenza' => $dataScadenza,
            'valore_annuo' => $valoreAnn,
            'stato' => $stato,
            'note' => $note,
            'created_by' => $_SESSION['cliente_id']
        ]);
    }

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
