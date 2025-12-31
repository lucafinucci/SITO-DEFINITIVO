<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/fatture-settings.php';

header('Content-Type: application/json; charset=utf-8');

$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$invioModalita = $input['invio_modalita'] ?? 'manuale';
$mostraSoloInviate = !empty($input['mostra_cliente_solo_inviate']) ? 1 : 0;

$modalitaValide = ['manuale', 'automatico'];
if (!in_array($invioModalita, $modalitaValide, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Modalita non valida']);
    exit;
}

try {
    ensureFattureSettingsTable($pdo);

    $stmt = $pdo->prepare('
        INSERT INTO fatture_impostazioni (invio_modalita, mostra_cliente_solo_inviate)
        VALUES (:invio_modalita, :mostra_cliente_solo_inviate)
    ');
    $stmt->execute([
        'invio_modalita' => $invioModalita,
        'mostra_cliente_solo_inviate' => $mostraSoloInviate
    ]);

    echo json_encode(['success' => true, 'message' => 'Impostazioni salvate']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Impossibile salvare le impostazioni']);
}
