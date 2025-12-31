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
$nome = trim((string)($input['nome'] ?? ''));
$cognome = trim((string)($input['cognome'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$azienda = trim((string)($input['azienda'] ?? ''));
$password = (string)($input['password'] ?? '');
$webappUrl = trim((string)($input['webapp_url'] ?? ''));
$attivaDocint = ($input['attiva_docint'] ?? '') === '1';

if ($nome === '' || $cognome === '' || $email === '' || $azienda === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email non valida']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password troppo corta (min 8 caratteri)']);
    exit;
}

if ($webappUrl !== '' && !filter_var($webappUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'URL WebApp non valido']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM utenti WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email già registrata']);
        exit;
    }

    $hasWebappUrl = false;
    $stmtCols = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'utenti'
          AND COLUMN_NAME = 'webapp_url'
    ");
    $stmtCols->execute();
    $hasWebappUrl = (bool)$stmtCols->fetchColumn();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($hasWebappUrl) {
        $stmt = $pdo->prepare('
            INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo, webapp_url)
            VALUES (:email, :password_hash, :nome, :cognome, :azienda, "cliente", :webapp_url)
        ');
        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'nome' => $nome,
            'cognome' => $cognome,
            'azienda' => $azienda,
            'webapp_url' => $webappUrl !== '' ? $webappUrl : null
        ]);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo)
            VALUES (:email, :password_hash, :nome, :cognome, :azienda, "cliente")
        ');
        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'nome' => $nome,
            'cognome' => $cognome,
            'azienda' => $azienda
        ]);
    }

    $clienteId = (int)$pdo->lastInsertId();

    if ($attivaDocint) {
        $stmt = $pdo->prepare('SELECT id FROM servizi WHERE codice = "DOC-INT" LIMIT 1');
        $stmt->execute();
        $servizioId = (int)$stmt->fetchColumn();
        if ($servizioId > 0) {
            $stmt = $pdo->prepare('
                INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
                VALUES (:user_id, :servizio_id, CURDATE(), "attivo")
            ');
            $stmt->execute([
                'user_id' => $clienteId,
                'servizio_id' => $servizioId
            ]);
        }
    }

    echo json_encode(['success' => true, 'cliente_id' => $clienteId]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore database']);
}
