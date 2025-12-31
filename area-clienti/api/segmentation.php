<?php
/**
 * API Customer Segmentation
 * Endpoint per clustering e analisi segmenti clienti
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/customer-segmentation.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

$audit = new AuditLogger($pdo);
$segmentation = new CustomerSegmentation($pdo);

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'recalculate':
            // Ricalcola segmentazione completa
            $rbac->requirePermission('can_edit_clienti'); // Admin only

            set_time_limit(600); // 10 minuti

            $numClusters = isset($_GET['clusters']) ? (int)$_GET['clusters'] : null;

            $result = $segmentation->performClustering($numClusters);

            // Log audit
            $audit->log([
                'azione' => 'segmentation_recalculated',
                'entita' => 'analytics',
                'descrizione' => "Segmentazione ricalcolata: {$result['num_clusters']} cluster, {$result['total_customers']} clienti",
                'categoria' => 'analytics',
                'livello' => 'info',
                'metadata' => json_encode([
                    'clusters' => $result['num_clusters'],
                    'customers' => $result['total_customers'],
                    'iterations' => $result['iterations']
                ])
            ]);

            echo json_encode([
                'success' => true,
                'num_clusters' => $result['num_clusters'],
                'total_customers' => $result['total_customers'],
                'iterations' => $result['iterations'],
                'profiles' => $result['profiles']
            ]);
            break;

        case 'assign_customer':
            // Assegna nuovo cliente a segmento
            $clienteId = (int)($_GET['cliente_id'] ?? $_POST['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $segment = $segmentation->assignCustomerToSegment($clienteId);

            echo json_encode([
                'success' => true,
                'segment' => $segment
            ]);
            break;

        case 'get_stats':
            // Statistiche segmentazione
            $stats = $segmentation->getSegmentationStats();

            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;

        case 'get_all_segments':
            // Lista tutti i segmenti con profili
            $stmt = $pdo->query("
                SELECT * FROM segment_profiles
                ORDER BY size DESC
            ");

            $segments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decodifica JSON
            foreach ($segments as &$segment) {
                $segment['characteristics'] = json_decode($segment['characteristics'], true);
                $segment['recommendations'] = json_decode($segment['recommendations'], true);
                $segment['centroid_data'] = json_decode($segment['centroid_data'], true);
            }

            echo json_encode([
                'success' => true,
                'segments' => $segments
            ]);
            break;

        case 'get_segment_details':
            // Dettagli segmento specifico
            $segmentId = (int)($_GET['segment_id'] ?? 0);

            if ($segmentId === '') {
                throw new Exception('segment_id richiesto');
            }

            $stmt = $pdo->prepare("
                SELECT * FROM segment_profiles
                WHERE segment_id = :segment_id
            ");

            $stmt->execute(['segment_id' => $segmentId]);
            $segment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$segment) {
                throw new Exception('Segmento non trovato');
            }

            echo json_encode([
                'success' => true,
                'segment' => $segment
            ]);
            break;

        case 'get_segment_customers':
            // Clienti di un segmento
            $segmentId = (int)($_GET['segment_id'] ?? 0);

            if ($segmentId === '') {
                throw new Exception('segment_id richiesto');
            }

            $stmt = $pdo->prepare("
                SELECT
                    u.id as cliente_id,
                    u.email,
                    u.nome,
                    u.cognome,
                    u.azienda,
                    COALESCE(
                        (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata'),
                        0
                    ) as lifetime_value,
                    DATEDIFF(NOW(), u.last_login) as days_since_last_login,
                    cs.assignment_date
                FROM utenti u
                JOIN customer_segments cs ON u.id = cs.cliente_id
                WHERE cs.segment_id = :segment_id
                AND u.ruolo = 'cliente'
                AND u.attivo = TRUE
                ORDER BY lifetime_value DESC
            ");

            $stmt->execute(['segment_id' => $segmentId]);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'customers' => $customers
            ]);
            break;

        case 'get_customer_segment':
            // Segmento di un cliente
            $clienteId = (int)($_GET['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $stmt = $pdo->prepare("
                SELECT
                    cs.segment_id,
                    sp.persona_name,
                    sp.persona_icon,
                    sp.persona_description,
                    sp.characteristics,
                    sp.recommendations,
                    cs.assignment_date
                FROM customer_segments cs
                JOIN segment_profiles sp ON cs.segment_id = sp.segment_id
                WHERE cs.cliente_id = :cliente_id
            ");

            $stmt->execute(['cliente_id' => $clienteId]);
            $segment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$segment) {
                // Cliente non ancora segmentato - assegna ora
                $segment = $segmentation->assignCustomerToSegment($clienteId);
            }

            echo json_encode([
                'success' => true,
                'segment' => $segment
            ]);
            break;

        case 'segment_migrations':
            // Storico migrazioni segmenti
            $clienteId = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;

            $sql = "
                SELECT
                    sh.*,
                    u.email,
                    u.nome,
                    u.cognome,
                    sp1.persona_name as old_persona,
                    sp2.persona_name as new_persona
                FROM segment_history sh
                JOIN utenti u ON sh.cliente_id = u.id
                LEFT JOIN segment_profiles sp1 ON sh.old_segment_id = sp1.segment_id
                JOIN segment_profiles sp2 ON sh.new_segment_id = sp2.segment_id
            ";

            $params = [];

            if ($clienteId) {
                $sql .= " WHERE sh.cliente_id = :cliente_id";
                $params['cliente_id'] = $clienteId;
            }

            $sql .= " ORDER BY sh.migration_date DESC LIMIT 100";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'migrations' => $migrations
            ]);
            break;

        case 'distribution_stats':
            // Statistiche distribuzione segmenti
            $stmt = $pdo->query("CALL segment_distribution_stats()");
            $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'distribution' => $distribution
            ]);
            break;

        case 'segment_recommendations':
            // Raccomandazioni per un segmento
            $segmentId = (int)($_GET['segment_id'] ?? 0);

            if ($segmentId === '') {
                throw new Exception('segment_id richiesto');
            }

            $stmt = $pdo->prepare("CALL get_segment_recommendations(:segment_id)");
            $stmt->execute(['segment_id' => $segmentId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new Exception('Segmento non trovato');
            }

            $recommendations = json_decode($data['recommendations'], true);
            $characteristics = json_decode($data['characteristics'], true);

            echo json_encode([
                'success' => true,
                'persona_name' => $data['persona_name'],
                'persona_description' => $data['persona_description'],
                'size' => $data['size'],
                'avg_ltv' => $data['avg_ltv'],
                'avg_churn_risk' => $data['avg_churn_risk'],
                'characteristics' => $characteristics,
                'recommendations' => $recommendations
            ]);
            break;

        case 'create_campaign':
            // Crea campagna targeted per segmento
            $rbac->requirePermission('can_send_emails');

            $data = json_decode(file_get_contents('php://input'), true);

            $required = ['segment_id', 'campaign_name', 'campaign_type', 'target_action'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo $field richiesto");
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO segment_campaigns (
                    segment_id,
                    campaign_name,
                    campaign_type,
                    target_action,
                    subject,
                    message,
                    scheduled_date,
                    status,
                    created_by
                ) VALUES (
                    :segment_id,
                    :campaign_name,
                    :campaign_type,
                    :target_action,
                    :subject,
                    :message,
                    :scheduled_date,
                    :status,
                    :created_by
                )
            ");

            $stmt->execute([
                'segment_id' => $data['segment_id'],
                'campaign_name' => $data['campaign_name'],
                'campaign_type' => $data['campaign_type'],
                'target_action' => $data['target_action'],
                'subject' => $data['subject'] ?? null,
                'message' => $data['message'] ?? null,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $_SESSION['cliente_id']
            ]);

            $campaignId = $pdo->lastInsertId();

            // Log audit
            $audit->log([
                'azione' => 'campaign_created',
                'entita' => 'segment_campaign',
                'entita_id' => $campaignId,
                'descrizione' => "Campagna '{$data['campaign_name']}' creata per segmento {$data['segment_id']}",
                'categoria' => 'marketing',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'campaign_id' => $campaignId
            ]);
            break;

        case 'get_campaigns':
            // Lista campagne
            $segmentId = isset($_GET['segment_id']) ? (int)$_GET['segment_id'] : null;

            $sql = "
                SELECT
                    sc.*,
                    sp.persona_name,
                    u.nome as created_by_name
                FROM segment_campaigns sc
                JOIN segment_profiles sp ON sc.segment_id = sp.segment_id
                LEFT JOIN utenti u ON sc.created_by = u.id
            ";

            $params = [];

            if ($segmentId) {
                $sql .= " WHERE sc.segment_id = :segment_id";
                $params['segment_id'] = $segmentId;
            }

            $sql .= " ORDER BY sc.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'campaigns' => $campaigns
            ]);
            break;

        case 'campaign_performance':
            // Performance campagne
            $stmt = $pdo->query("SELECT * FROM v_segment_campaign_performance");
            $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'performance' => $performance
            ]);
            break;

        case 'export':
            // Export segmentazione CSV
            $format = $_GET['format'] ?? 'csv';

            $stmt = $pdo->query("
                SELECT
                    u.email,
                    u.nome,
                    u.cognome,
                    u.azienda,
                    sp.persona_name as segmento,
                    sp.persona_icon,
                    COALESCE(
                        (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata'),
                        0
                    ) as lifetime_value,
                    (SELECT COUNT(*) FROM servizi_attivi WHERE cliente_id = u.id AND stato = 'attivo') as servizi_attivi,
                    DATEDIFF(NOW(), u.last_login) as giorni_inattivo,
                    cs.assignment_date
                FROM utenti u
                JOIN customer_segments cs ON u.id = cs.cliente_id
                JOIN segment_profiles sp ON cs.segment_id = sp.segment_id
                WHERE u.ruolo = 'cliente'
                ORDER BY sp.segment_id, lifetime_value DESC
            ");

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="customer_segments_' . date('Y-m-d') . '.csv"');

                $output = fopen('php://output', 'w');
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

                // Header
                fputcsv($output, [
                    'Email',
                    'Nome',
                    'Cognome',
                    'Azienda',
                    'Segmento',
                    'Icon',
                    'Lifetime Value (€)',
                    'Servizi Attivi',
                    'Giorni Inattivo',
                    'Data Assegnazione'
                ]);

                // Data
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['email'],
                        $row['nome'],
                        $row['cognome'],
                        $row['azienda'],
                        $row['segmento'],
                        $row['persona_icon'],
                        round($row['lifetime_value'], 2),
                        $row['servizi_attivi'],
                        $row['giorni_inattivo'],
                        $row['assignment_date']
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

        case 'compare_segments':
            // Confronta due segmenti
            $segment1 = (int)($_GET['segment1'] ?? 0);
            $segment2 = (int)($_GET['segment2'] ?? 0);

            if (!$segment1 || !$segment2) {
                throw new Exception('segment1 e segment2 richiesti');
            }

            $stmt = $pdo->prepare("
                SELECT * FROM segment_profiles
                WHERE segment_id IN (:seg1, :seg2)
            ");

            $stmt->execute(['seg1' => $segment1, 'seg2' => $segment2]);
            $segments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($segments) !== 2) {
                throw new Exception('Uno o entrambi i segmenti non trovati');
            }

            $comparison = [
                'segment1' => $segments[0],
                'segment2' => $segments[1],
                'differences' => [
                    'size_diff' => abs($segments[0]['size'] - $segments[1]['size']),
                    'ltv_diff' => abs($segments[0]['avg_ltv'] - $segments[1]['avg_ltv']),
                    'engagement_diff' => abs($segments[0]['avg_engagement'] - $segments[1]['avg_engagement']),
                    'churn_diff' => abs($segments[0]['avg_churn_risk'] - $segments[1]['avg_churn_risk'])
                ]
            ];

            echo json_encode([
                'success' => true,
                'comparison' => $comparison
            ]);
            break;

        case 'segment_value_analysis':
            // Analisi valore per segmento
            $stmt = $pdo->query("
                SELECT
                    sp.segment_id,
                    sp.persona_name,
                    sp.size,
                    sp.avg_ltv,

                    -- Revenue totale segmento
                    sp.size * sp.avg_ltv as total_segment_value,

                    -- Potenziale upselling
                    (SELECT COUNT(*)
                     FROM customer_segments cs
                     JOIN upsell_opportunities uo ON cs.cliente_id = uo.cliente_id
                     WHERE cs.segment_id = sp.segment_id
                     AND uo.status = 'identified'
                     AND uo.opportunity_level = 'high'
                    ) as high_upsell_opportunities,

                    -- At-risk count
                    (SELECT COUNT(*)
                     FROM customer_segments cs
                     JOIN churn_predictions cp ON cs.cliente_id = cp.cliente_id
                     WHERE cs.segment_id = sp.segment_id
                     AND cp.risk_level = 'high'
                    ) as high_churn_risk_count

                FROM segment_profiles sp
                ORDER BY total_segment_value DESC
            ");

            $analysis = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'value_analysis' => $analysis
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
