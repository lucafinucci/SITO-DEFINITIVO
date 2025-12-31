<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/error-handler.php';
require __DIR__ . '/../includes/security.php';

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
$subject = trim((string)($input['subject'] ?? ''));
$message = trim((string)($input['message'] ?? ''));
$message = str_replace("\0", '', $message);

if ($richiestaId <= 0 || $message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Messaggio o richiesta mancanti']);
    exit;
}

if (strlen($message) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Messaggio troppo lungo']);
    exit;
}

// Recupera dati richiesta + utente
$stmt = $pdo->prepare('
    SELECT
        r.id,
        r.user_id,
        r.tipo_modello,
        r.stato,
        r.created_at,
        u.email,
        u.nome,
        u.cognome,
        u.azienda
    FROM richieste_addestramento r
    JOIN utenti u ON r.user_id = u.id
    WHERE r.id = :id
');
$stmt->execute(['id' => $richiestaId]);
$richiesta = $stmt->fetch();

if (!$richiesta) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Richiesta non trovata']);
    exit;
}

$emailValidation = Security::validateEmail($richiesta['email']);
if (!$emailValidation['valid']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email destinatario non valida']);
    exit;
}

if ($subject === '') {
    $subject = 'Aggiornamento richiesta addestramento #' . $richiesta['id'];
}

// Previeni header injection
$subject = preg_replace("/[\\r\\n]+/", ' ', $subject);

$fromEmail = Config::get('MAIL_FROM', 'noreply@finch-ai.it');
$replyTo = Config::get('TRAINING_EMAIL', 'ai-training@finch-ai.it');

$body = "Ciao {$richiesta['nome']} {$richiesta['cognome']},\n\n";
$body .= "Di seguito un aggiornamento sulla tua richiesta di addestramento.\n\n";
$body .= "Messaggio:\n{$message}\n\n";
$body .= "Dettagli richiesta:\n";
$body .= "- ID: {$richiesta['id']}\n";
$body .= "- Azienda: {$richiesta['azienda']}\n";
$body .= "- Tipo modello: {$richiesta['tipo_modello']}\n";
$body .= "- Stato attuale: {$richiesta['stato']}\n";
$body .= "- Data richiesta: " . date('d/m/Y H:i', strtotime($richiesta['created_at'])) . "\n\n";
$body .= "Cordiali saluti,\nTeam Finch-AI\n";

$headers = "From: Area Clienti <{$fromEmail}>\r\n";
$headers .= "Reply-To: {$replyTo}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$mailSent = @mail($emailValidation['value'], $subject, $body, $headers);

if (!$mailSent) {
    ErrorHandler::logError('Failed to send admin email', [
        'richiesta_id' => $richiestaId,
        'email_to' => $emailValidation['value']
    ]);

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Invio email fallito']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO clienti_eventi (cliente_id, tipo, titolo, dettagli, created_by)
        VALUES (:cliente_id, "email", :titolo, :dettagli, :created_by)
    ');
    $stmt->execute([
        'cliente_id' => $richiesta['user_id'] ?? null,
        'titolo' => 'Email inviata al cliente',
        'dettagli' => "Oggetto: {$subject}\n\n{$message}",
        'created_by' => $_SESSION['cliente_id']
    ]);
} catch (PDOException $e) {
    ErrorHandler::logError('Failed to log admin email event', [
        'richiesta_id' => $richiestaId,
        'error' => $e->getMessage()
    ]);
}

ErrorHandler::logAccess('Admin email sent', [
    'richiesta_id' => $richiestaId,
    'email_to' => $emailValidation['value']
]);

echo json_encode([
    'success' => true,
    'message' => 'Email inviata con successo'
]);
