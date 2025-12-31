<?php
/**
 * API A/B Testing
 * Endpoint per gestione test, assignment, tracking, analytics
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/ab-testing-engine.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = getRBAC($pdo);
$audit = new AuditLogger($pdo);
$abTesting = new ABTestingEngine($pdo);

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'create_test':
            // Crea nuovo test
            $rbac->requirePermission('can_edit_clienti'); // Admin only

            $data = json_decode(file_get_contents('php://input'), true);

            $testId = $abTesting->createTest($data);

            $audit->log([
                'azione' => 'ab_test_created',
                'entita' => 'ab_test',
                'entita_id' => $testId,
                'descrizione' => "Test A/B creato: {$data['name']}",
                'categoria' => 'testing',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'test_id' => $testId
            ]);
            break;

        case 'start_test':
            // Avvia test
            $rbac->requirePermission('can_edit_clienti');

            $testId = (int)($_GET['test_id'] ?? $_POST['test_id'] ?? 0);

            if (!$testId) {
                throw new Exception('test_id richiesto');
            }

            $abTesting->startTest($testId);

            echo json_encode([
                'success' => true,
                'message' => 'Test avviato'
            ]);
            break;

        case 'pause_test':
            // Pausa test
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $testId = (int)($data['test_id'] ?? 0);

            if (!$testId) {
                throw new Exception('test_id richiesto');
            }

            $abTesting->pauseTest($testId);

            echo json_encode([
                'success' => true,
                'message' => 'Test in pausa'
            ]);
            break;

        case 'complete_test':
            // Completa test
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $testId = (int)($data['test_id'] ?? 0);
            $winnerVariantId = isset($data['winner_variant_id']) ? (int)$data['winner_variant_id'] : null;

            if (!$testId) {
                throw new Exception('test_id richiesto');
            }

            $abTesting->completeTest($testId, $winnerVariantId);

            echo json_encode([
                'success' => true,
                'message' => 'Test completato'
            ]);
            break;

        case 'get_variant':
            // Ottieni variante per utente (assignment)
            $testId = (int)($_GET['test_id'] ?? 0);
            $userId = (int)($_GET['user_id'] ?? $_SESSION['cliente_id'] ?? 0);

            if (!$testId || !$userId) {
                throw new Exception('test_id e user_id richiesti');
            }

            $variant = $abTesting->assignVariant($testId, $userId);

            echo json_encode([
                'success' => true,
                'variant' => $variant
            ]);
            break;

        case 'track_event':
            // Traccia evento
            $data = json_decode(file_get_contents('php://input'), true);

            $required = ['test_id', 'user_id', 'event_type'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("$field richiesto");
                }
            }

            $abTesting->trackEvent(
                $data['test_id'],
                $data['user_id'],
                $data['event_type'],
                $data['event_value'] ?? null,
                $data['metadata'] ?? []
            );

            echo json_encode([
                'success' => true,
                'message' => 'Evento tracciato'
            ]);
            break;

        case 'get_results':
            // Ottieni risultati test
            $rbac->requirePermission('can_view_analytics');

            $testId = (int)($_GET['test_id'] ?? 0);

            if (!$testId) {
                throw new Exception('test_id richiesto');
            }

            $results = $abTesting->calculateResults($testId);

            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
            break;

        case 'get_all_tests':
            // Lista tutti test
            $rbac->requirePermission('can_view_analytics');

            $status = $_GET['status'] ?? null;

            $sql = "SELECT * FROM ab_tests";
            $params = [];

            if ($status) {
                $sql .= " WHERE status = :status";
                $params['status'] = $status;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'tests' => $tests
            ]);
            break;

        case 'get_test_details':
            // Dettagli test
            $rbac->requirePermission('can_view_analytics');

            $testId = (int)($_GET['test_id'] ?? 0);

            if (!$testId) {
                throw new Exception('test_id richiesto');
            }

            $stmt = $pdo->prepare("SELECT * FROM ab_tests WHERE id = :test_id");
            $stmt->execute(['test_id' => $testId]);
            $test = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$test) {
                throw new Exception('Test non trovato');
            }

            // Varianti
            $stmt = $pdo->prepare("SELECT * FROM ab_variants WHERE test_id = :test_id");
            $stmt->execute(['test_id' => $testId]);
            $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'test' => $test,
                'variants' => $variants
            ]);
            break;

        case 'get_variant_config':
            // Get config variante per utente
            $testId = (int)($_GET['test_id'] ?? 0);
            $userId = (int)($_GET['user_id'] ?? $_SESSION['cliente_id'] ?? 0);
            $configKey = $_GET['config_key'] ?? null;

            if (!$testId || !$userId) {
                throw new Exception('test_id e user_id richiesti');
            }

            $config = $abTesting->getVariantConfig($testId, $userId, $configKey);

            echo json_encode([
                'success' => true,
                'config' => $config
            ]);
            break;

        default:
            throw new Exception('Azione non valida');
    }

} catch (PermissionDeniedException $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
