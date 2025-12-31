<?php
/**
 * API Churn Prediction
 * Endpoint per operazioni di churn prediction e retention
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/churn-predictor.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

$audit = new AuditLogger($pdo);
$churn = new ChurnPredictor($pdo);

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'predict_single':
            // Predizione singolo cliente
            $clienteId = (int)($_GET['cliente_id'] ?? $_POST['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $prediction = $churn->predictChurn($clienteId);

            // Salva nel database
            $churn->savePrediction($prediction);

            // Log audit
            $audit->log([
                'azione' => 'churn_prediction',
                'entita' => 'cliente',
                'entita_id' => $clienteId,
                'descrizione' => "Churn prediction calcolata: {$prediction['risk_level']} ({$prediction['churn_percentage']}%)",
                'categoria' => 'analytics',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'prediction' => $prediction
            ]);
            break;

        case 'recalculate_all':
            // Ricalcola predizioni per tutti i clienti
            set_time_limit(300); // 5 minuti

            $limit = (int)($_GET['limit'] ?? 1000);
            $predictions = $churn->predictBatch($limit);

            $saved = 0;
            foreach ($predictions as $prediction) {
                if ($churn->savePrediction($prediction)) {
                    $saved++;
                }
            }

            // Log audit
            $audit->log([
                'azione' => 'churn_batch_calculation',
                'entita' => 'analytics',
                'descrizione' => "Calcolate {$saved} predizioni churn",
                'categoria' => 'analytics',
                'livello' => 'info',
                'metadata' => json_encode(['processed' => $saved])
            ]);

            echo json_encode([
                'success' => true,
                'processed' => $saved,
                'high_risk' => count(array_filter($predictions, fn($p) => $p['risk_level'] === 'high')),
                'medium_risk' => count(array_filter($predictions, fn($p) => $p['risk_level'] === 'medium')),
                'low_risk' => count(array_filter($predictions, fn($p) => $p['risk_level'] === 'low'))
            ]);
            break;

        case 'get_details':
            // Dettagli predizione cliente
            $clienteId = (int)($_GET['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $stmt = $pdo->prepare('
                SELECT * FROM churn_predictions
                WHERE cliente_id = :id
            ');
            $stmt->execute(['id' => $clienteId]);
            $prediction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prediction) {
                // Calcola se non esiste
                $newPrediction = $churn->predictChurn($clienteId);
                $churn->savePrediction($newPrediction);
                $prediction = $newPrediction;
            } else {
                // Decodifica JSON
                $prediction['scores'] = json_decode($prediction['scores_json'], true);
                $prediction['recommendations'] = json_decode($prediction['recommendations_json'], true);
                $prediction['features'] = json_decode($prediction['features_json'], true);
            }

            echo json_encode([
                'success' => true,
                'prediction' => $prediction
            ]);
            break;

        case 'get_history':
            // Storico predizioni cliente
            $clienteId = (int)($_GET['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $stmt = $pdo->prepare('
                SELECT *
                FROM churn_history
                WHERE cliente_id = :id
                ORDER BY snapshot_date DESC
                LIMIT 30
            ');
            $stmt->execute(['id' => $clienteId]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'history' => $history
            ]);
            break;

        case 'create_action':
            // Crea azione retention
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);

            $required = ['cliente_id', 'action_type', 'category', 'priority', 'description'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo $field richiesto");
                }
            }

            $stmt = $pdo->prepare('
                INSERT INTO churn_retention_actions (
                    cliente_id,
                    prediction_id,
                    action_type,
                    category,
                    priority,
                    description,
                    assigned_to,
                    scheduled_date,
                    status
                ) VALUES (
                    :cliente_id,
                    (SELECT id FROM churn_predictions WHERE cliente_id = :cliente_id2),
                    :action_type,
                    :category,
                    :priority,
                    :description,
                    :assigned_to,
                    :scheduled_date,
                    :status
                )
            ');

            $stmt->execute([
                'cliente_id' => $data['cliente_id'],
                'cliente_id2' => $data['cliente_id'],
                'action_type' => $data['action_type'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'description' => $data['description'],
                'assigned_to' => $data['assigned_to'] ?? $_SESSION['cliente_id'],
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'status' => $data['status'] ?? 'planned'
            ]);

            $actionId = $pdo->lastInsertId();

            // Log audit
            $audit->log([
                'azione' => 'create_retention_action',
                'entita' => 'retention_action',
                'entita_id' => $actionId,
                'descrizione' => "Azione retention creata per cliente {$data['cliente_id']}",
                'categoria' => 'cliente',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'action_id' => $actionId
            ]);
            break;

        case 'update_action':
            // Aggiorna azione retention
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $actionId = (int)($data['action_id'] ?? 0);

            if (!$actionId) {
                throw new Exception('action_id richiesto');
            }

            $updates = [];
            $params = ['id' => $actionId];

            $allowedFields = ['status', 'outcome', 'effectiveness_score', 'completed_date'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }

            if (empty($updates)) {
                throw new Exception('Nessun campo da aggiornare');
            }

            $sql = 'UPDATE churn_retention_actions SET ' . implode(', ', $updates) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'success' => true,
                'updated' => $stmt->rowCount()
            ]);
            break;

        case 'get_actions':
            // Lista azioni retention
            $clienteId = (int)($_GET['cliente_id'] ?? 0);

            $sql = '
                SELECT ra.*, u.nome, u.cognome
                FROM churn_retention_actions ra
                LEFT JOIN utenti u ON ra.assigned_to = u.id
            ';

            $params = [];

            if ($clienteId) {
                $sql .= ' WHERE ra.cliente_id = :cliente_id';
                $params['cliente_id'] = $clienteId;
            }

            $sql .= ' ORDER BY ra.created_at DESC LIMIT 100';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'actions' => $actions
            ]);
            break;

        case 'bulk_contact':
            // Crea azioni bulk per clienti a rischio
            $rbac->requirePermission('can_send_emails');

            $riskLevel = $_GET['risk'] ?? 'high';

            $stmt = $pdo->prepare('
                SELECT cliente_id
                FROM churn_predictions
                WHERE risk_level = :risk
            ');
            $stmt->execute(['risk' => $riskLevel]);
            $clienti = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $created = 0;
            foreach ($clienti as $clienteId) {
                $stmt = $pdo->prepare('
                    INSERT INTO churn_retention_actions (
                        cliente_id,
                        prediction_id,
                        action_type,
                        category,
                        priority,
                        description,
                        assigned_to,
                        status
                    ) VALUES (
                        :cliente_id,
                        (SELECT id FROM churn_predictions WHERE cliente_id = :cliente_id2),
                        :action_type,
                        :category,
                        :priority,
                        :description,
                        :assigned_to,
                        :status
                    )
                ');

                if ($stmt->execute([
                    'cliente_id' => $clienteId,
                    'cliente_id2' => $clienteId,
                    'action_type' => 'call',
                    'category' => 'retention',
                    'priority' => 'high',
                    'description' => "Contatto urgente per cliente a rischio {$riskLevel}",
                    'assigned_to' => $_SESSION['cliente_id'],
                    'status' => 'planned'
                ])) {
                    $created++;
                }
            }

            echo json_encode([
                'success' => true,
                'created' => $created,
                'message' => "Creati $created task di contatto"
            ]);
            break;

        case 'export':
            // Export predizioni
            $format = $_GET['format'] ?? 'csv';

            $stmt = $pdo->query('
                SELECT
                    u.email,
                    u.nome,
                    u.cognome,
                    u.azienda,
                    cp.churn_probability,
                    cp.risk_level,
                    cp.top_risk_factors,
                    cp.updated_at
                FROM churn_predictions cp
                JOIN utenti u ON cp.cliente_id = u.id
                ORDER BY cp.churn_probability DESC
            ');

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="churn_predictions_' . date('Y-m-d') . '.csv"');

                $output = fopen('php://output', 'w');
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

                // Header
                fputcsv($output, [
                    'Email',
                    'Nome',
                    'Cognome',
                    'Azienda',
                    'Probabilità Churn (%)',
                    'Livello Rischio',
                    'Fattori Rischio',
                    'Aggiornato'
                ]);

                // Data
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['email'],
                        $row['nome'],
                        $row['cognome'],
                        $row['azienda'],
                        round($row['churn_probability'] * 100, 2),
                        strtoupper($row['risk_level']),
                        $row['top_risk_factors'],
                        $row['updated_at']
                    ]);
                }

                fclose($output);
                exit;
            } else {
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            }
            break;

        case 'stats':
            // Statistiche aggregate
            $stats = $churn->getChurnStats();

            // Trend ultimi 30 giorni
            $trend = $pdo->query('
                SELECT
                    DATE(snapshot_date) as date,
                    AVG(churn_probability) as avg_probability,
                    COUNT(DISTINCT cliente_id) as clienti
                FROM churn_history
                WHERE snapshot_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(snapshot_date)
                ORDER BY date ASC
            ')->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'trend' => $trend
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
