<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

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
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'invia-manuale':
            // Invia sollecito manuale
            $fatturaId = (int)($input['fattura_id'] ?? 0);

            if (!$fatturaId) {
                throw new Exception('ID fattura mancante');
            }

            // Recupera fattura
            $stmt = $pdo->prepare('
                SELECT
                    f.*,
                    u.azienda,
                    u.nome,
                    u.cognome,
                    u.email,
                    DATEDIFF(CURDATE(), f.data_scadenza) AS giorni_ritardo
                FROM fatture f
                JOIN utenti u ON f.cliente_id = u.id
                WHERE f.id = :id
            ');
            $stmt->execute(['id' => $fatturaId]);
            $fattura = $stmt->fetch();

            if (!$fattura) {
                throw new Exception('Fattura non trovata');
            }

            if ($fattura['stato'] !== 'scaduta') {
                throw new Exception('La fattura non è scaduta');
            }

            // Conta solleciti già inviati
            $stmt = $pdo->prepare('
                SELECT COUNT(*) FROM fatture_solleciti
                WHERE fattura_id = :fattura_id AND stato = "inviato"
            ');
            $stmt->execute(['fattura_id' => $fatturaId]);
            $numeroSollecito = (int)$stmt->fetchColumn() + 1;

            // Determina tipo
            $tipo = 'primo_sollecito';
            if ($numeroSollecito == 2) $tipo = 'secondo_sollecito';
            elseif ($numeroSollecito >= 3) $tipo = 'sollecito_urgente';

            // Crea sollecito
            $stmt = $pdo->prepare('
                INSERT INTO fatture_solleciti (
                    fattura_id,
                    tipo,
                    numero_sollecito,
                    oggetto,
                    stato,
                    data_invio,
                    inviato_da,
                    metodo_invio
                ) VALUES (
                    :fattura_id,
                    :tipo,
                    :numero,
                    :oggetto,
                    "inviato",
                    CURRENT_TIMESTAMP,
                    :inviato_da,
                    "email_manuale"
                )
            ');

            $oggetto = "Sollecito pagamento fattura {$fattura['numero_fattura']}";

            $stmt->execute([
                'fattura_id' => $fatturaId,
                'tipo' => $tipo,
                'numero' => $numeroSollecito,
                'oggetto' => $oggetto,
                'inviato_da' => $_SESSION['cliente_id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Sollecito registrato. Email da inviare manualmente a: ' . $fattura['email']
            ]);
            break;

        case 'annulla':
            // Annulla sollecito
            $sollecitoId = (int)($input['sollecito_id'] ?? 0);

            if (!$sollecitoId) {
                throw new Exception('ID sollecito mancante');
            }

            $stmt = $pdo->prepare('
                UPDATE fatture_solleciti
                SET stato = "annullato"
                WHERE id = :id AND stato = "da_inviare"
            ');
            $stmt->execute(['id' => $sollecitoId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Sollecito non trovato o già inviato');
            }

            echo json_encode(['success' => true, 'message' => 'Sollecito annullato']);
            break;

        default:
            throw new Exception('Azione non riconosciuta');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
