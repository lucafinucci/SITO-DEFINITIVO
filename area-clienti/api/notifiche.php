<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/notifiche-manager.php';

header('Content-Type: application/json; charset=utf-8');

$nm = new NotificheManager($pdo);
$utenteId = $_SESSION['cliente_id'];

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    switch ($action) {
        case 'list':
            // Recupera lista notifiche
            $soloNonLette = isset($_GET['solo_non_lette']) && $_GET['solo_non_lette'] === 'true';
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
            $tipo = $_GET['tipo'] ?? null;

            $notifiche = $nm->getNotifiche($utenteId, [
                'solo_non_lette' => $soloNonLette,
                'limit' => $limit,
                'tipo' => $tipo
            ]);

            echo json_encode([
                'success' => true,
                'notifiche' => $notifiche
            ]);
            break;

        case 'count':
            // Conta notifiche non lette
            $count = $nm->contaNonLette($utenteId);

            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
            break;

        case 'stats':
            // Statistiche notifiche
            $stats = $nm->getStatistiche($utenteId);

            echo json_encode([
                'success' => true,
                'statistiche' => $stats
            ]);
            break;

        case 'mark-read':
            // Segna come letta
            $notificaId = (int)($_POST['id'] ?? 0);

            if (!$notificaId) {
                throw new Exception('ID notifica mancante');
            }

            $success = $nm->marcaComeLetta($notificaId, $utenteId);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notifica segnata come letta' : 'Notifica non trovata'
            ]);
            break;

        case 'mark-all-read':
            // Segna tutte come lette
            $count = $nm->marcaTutteComeLette($utenteId);

            echo json_encode([
                'success' => true,
                'count' => $count,
                'message' => "$count notifiche segnate come lette"
            ]);
            break;

        case 'archive':
            // Archivia notifica
            $notificaId = (int)($_POST['id'] ?? 0);

            if (!$notificaId) {
                throw new Exception('ID notifica mancante');
            }

            $success = $nm->archivia($notificaId, $utenteId);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notifica archiviata' : 'Notifica non trovata'
            ]);
            break;

        case 'poll':
            // Polling per nuove notifiche (long-polling)
            $ultimoId = (int)($_GET['ultimo_id'] ?? 0);
            $timeout = min(30, max(5, (int)($_GET['timeout'] ?? 15))); // Max 30 sec

            $startTime = time();
            $nuove = [];

            while (time() - $startTime < $timeout) {
                // Cerca nuove notifiche
                $stmt = $pdo->prepare('
                    SELECT * FROM notifiche
                    WHERE utente_id = :utente_id
                      AND id > :ultimo_id
                      AND archiviata = FALSE
                    ORDER BY id ASC
                    LIMIT 10
                ');
                $stmt->execute([
                    'utente_id' => $utenteId,
                    'ultimo_id' => $ultimoId
                ]);
                $nuove = $stmt->fetchAll();

                if (!empty($nuove)) {
                    break;
                }

                // Attendi 2 secondi prima di riprovare
                sleep(2);
            }

            echo json_encode([
                'success' => true,
                'notifiche' => $nuove,
                'count' => count($nuove)
            ]);
            break;

        default:
            throw new Exception('Azione non riconosciuta');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
