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
$statiValidi = ['bozza', 'inviato', 'accettato'];

try {
    if ($action === 'update-status') {
        $id = (int)($input['id'] ?? 0);
        $stato = $input['stato'] ?? '';
        if ($id <= 0 || !in_array($stato, $statiValidi, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dati non validi']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE preventivi SET stato = :stato WHERE id = :id');
        $stmt->execute(['stato' => $stato, 'id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID mancante']);
            exit;
        }
        // fallthrough to create logic with id
    }

    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID mancante']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM preventivi WHERE id = :id');
        $stmt->execute(['id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // Create/Update
    $nomeAzienda = trim((string)($input['nome_azienda'] ?? ''));
    $referente = trim((string)($input['referente'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $note = trim((string)($input['note'] ?? ''));
    $scadenza = trim((string)($input['scadenza'] ?? ''));
    $sconto = (float)($input['sconto_percentuale'] ?? 0);
    $stato = $input['stato'] ?? 'bozza';
    $voci = $input['voci'] ?? [];

    if ($nomeAzienda === '' || !in_array($stato, $statiValidi, true) || !is_array($voci)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
        exit;
    }

    $subtotale = 0;
    $cleanVoci = [];
    foreach ($voci as $v) {
        $descrizione = trim((string)($v['descrizione'] ?? ''));
        $quantita = (float)($v['quantita'] ?? 1);
        $prezzo = (float)($v['prezzo_unitario'] ?? 0);
        if ($descrizione === '' || $quantita <= 0) {
            continue;
        }
        $totaleRiga = $quantita * $prezzo;
        $subtotale += $totaleRiga;
        $cleanVoci[] = [
            'descrizione' => $descrizione,
            'quantita' => $quantita,
            'prezzo_unitario' => $prezzo,
            'totale' => $totaleRiga
        ];
    }

    if (empty($cleanVoci)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Inserire almeno una voce valida']);
        exit;
    }

    $sconto = max(0, min(100, $sconto));
    $totale = $subtotale * (1 - ($sconto / 100));

    $pdo->beginTransaction();

    if ($action === 'update') {
        $stmt = $pdo->prepare('
            UPDATE preventivi
            SET nome_azienda = :nome_azienda,
                referente = :referente,
                email = :email,
                stato = :stato,
                sconto_percentuale = :sconto,
                note = :note,
                scadenza = :scadenza,
                subtotale = :subtotale,
                totale = :totale
            WHERE id = :id
        ');
        $stmt->execute([
            'nome_azienda' => $nomeAzienda,
            'referente' => $referente,
            'email' => $email,
            'stato' => $stato,
            'sconto' => $sconto,
            'note' => $note,
            'scadenza' => $scadenza !== '' ? $scadenza : null,
            'subtotale' => $subtotale,
            'totale' => $totale,
            'id' => (int)$input['id']
        ]);

        $stmt = $pdo->prepare('DELETE FROM preventivi_voci WHERE preventivo_id = :id');
        $stmt->execute(['id' => (int)$input['id']]);
        $preventivoId = (int)$input['id'];
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO preventivi (nome_azienda, referente, email, stato, sconto_percentuale, note, scadenza, subtotale, totale, created_by)
            VALUES (:nome_azienda, :referente, :email, :stato, :sconto, :note, :scadenza, :subtotale, :totale, :created_by)
        ');
        $stmt->execute([
            'nome_azienda' => $nomeAzienda,
            'referente' => $referente,
            'email' => $email,
            'stato' => $stato,
            'sconto' => $sconto,
            'note' => $note,
            'scadenza' => $scadenza !== '' ? $scadenza : null,
            'subtotale' => $subtotale,
            'totale' => $totale,
            'created_by' => $_SESSION['cliente_id']
        ]);
        $preventivoId = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('
        INSERT INTO preventivi_voci (preventivo_id, descrizione, quantita, prezzo_unitario, totale)
        VALUES (:preventivo_id, :descrizione, :quantita, :prezzo_unitario, :totale)
    ');
    foreach ($cleanVoci as $riga) {
        $stmt->execute([
            'preventivo_id' => $preventivoId,
            'descrizione' => $riga['descrizione'],
            'quantita' => $riga['quantita'],
            'prezzo_unitario' => $riga['prezzo_unitario'],
            'totale' => $riga['totale']
        ]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'id' => $preventivoId]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
}
