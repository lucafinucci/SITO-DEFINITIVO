<?php
/**
 * API Upselling Opportunities
 * Endpoint per operazioni di upselling e cross-selling
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/upsell-engine.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

$audit = new AuditLogger($pdo);
$upsell = new UpsellEngine($pdo);

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'find_single':
            // Trova opportunità per un singolo cliente
            $clienteId = (int)($_GET['cliente_id'] ?? $_POST['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $opportunities = $upsell->findOpportunities($clienteId);

            // Salva nel database
            foreach ($opportunities as $opp) {
                $upsell->saveOpportunity($opp);
            }

            // Log audit
            $audit->log([
                'azione' => 'upsell_analysis',
                'entita' => 'cliente',
                'entita_id' => $clienteId,
                'descrizione' => "Trovate " . count($opportunities) . " opportunità upselling",
                'categoria' => 'sales',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'opportunities' => $opportunities
            ]);
            break;

        case 'recalculate_all':
            // Ricalcola opportunità per tutti i clienti
            set_time_limit(300); // 5 minuti

            $limit = (int)($_GET['limit'] ?? 1000);
            $allOpportunities = $upsell->findOpportunitiesBatch($limit);

            $saved = 0;
            foreach ($allOpportunities as $clienteId => $opportunities) {
                foreach ($opportunities as $opp) {
                    if ($upsell->saveOpportunity($opp)) {
                        $saved++;
                    }
                }
            }

            // Log audit
            $audit->log([
                'azione' => 'upsell_batch_calculation',
                'entita' => 'analytics',
                'descrizione' => "Calcolate {$saved} opportunità upselling",
                'categoria' => 'sales',
                'livello' => 'info',
                'metadata' => json_encode(['processed' => $saved])
            ]);

            // Conta per priorità
            $stmt = $pdo->query("
                SELECT
                    opportunity_level,
                    COUNT(*) as count
                FROM upsell_opportunities
                WHERE status = 'identified'
                GROUP BY opportunity_level
            ");

            $breakdown = [
                'high_priority' => 0,
                'medium_priority' => 0,
                'low_priority' => 0
            ];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $breakdown[$row['opportunity_level'] . '_priority'] = (int)$row['count'];
            }

            echo json_encode([
                'success' => true,
                'opportunities_found' => $saved,
                'high_priority' => $breakdown['high_priority'],
                'medium_priority' => $breakdown['medium_priority'],
                'low_priority' => $breakdown['low_priority']
            ]);
            break;

        case 'get_all':
            // Lista tutte le opportunità
            $status = $_GET['status'] ?? 'identified';
            $level = $_GET['level'] ?? '';

            $sql = "
                SELECT
                    uo.*,
                    u.nome,
                    u.cognome,
                    u.email,
                    u.azienda,
                    s.nome as servizio_nome,
                    s.prezzo_mensile,
                    cp.churn_probability as churn_risk
                FROM upsell_opportunities uo
                JOIN utenti u ON uo.cliente_id = u.id
                JOIN servizi s ON uo.servizio_id = s.id
                LEFT JOIN churn_predictions cp ON u.id = cp.cliente_id
                WHERE 1=1
            ";

            $params = [];

            if ($status) {
                $sql .= " AND uo.status = :status";
                $params['status'] = $status;
            }

            if ($level) {
                $sql .= " AND uo.opportunity_level = :level";
                $params['level'] = $level;
            }

            $sql .= " ORDER BY uo.opportunity_score DESC, uo.expected_value DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $opportunities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'opportunities' => $opportunities
            ]);
            break;

        case 'get_details':
            // Dettagli opportunità
            $opportunityId = (int)($_GET['id'] ?? 0);

            if (!$opportunityId) {
                throw new Exception('id richiesto');
            }

            $stmt = $pdo->prepare("
                SELECT
                    uo.*,
                    u.nome,
                    u.cognome,
                    u.email,
                    u.azienda,
                    s.nome as servizio_nome,
                    s.descrizione as servizio_descrizione,
                    s.prezzo_mensile,
                    s.prezzo_annuale,
                    cp.churn_probability as churn_risk
                FROM upsell_opportunities uo
                JOIN utenti u ON uo.cliente_id = u.id
                JOIN servizi s ON uo.servizio_id = s.id
                LEFT JOIN churn_predictions cp ON u.id = cp.cliente_id
                WHERE uo.id = :id
            ");

            $stmt->execute(['id' => $opportunityId]);
            $opportunity = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$opportunity) {
                throw new Exception('Opportunità non trovata');
            }

            echo json_encode([
                'success' => true,
                'opportunity' => $opportunity
            ]);
            break;

        case 'get_by_customer':
            // Opportunità per cliente specifico
            $clienteId = (int)($_GET['cliente_id'] ?? 0);

            if (!$clienteId) {
                throw new Exception('cliente_id richiesto');
            }

            $stmt = $pdo->prepare("
                SELECT
                    uo.*,
                    s.nome as servizio_nome,
                    s.prezzo_mensile
                FROM upsell_opportunities uo
                JOIN servizi s ON uo.servizio_id = s.id
                WHERE uo.cliente_id = :cliente_id
                ORDER BY uo.opportunity_score DESC
            ");

            $stmt->execute(['cliente_id' => $clienteId]);
            $opportunities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'opportunities' => $opportunities
            ]);
            break;

        case 'update_status':
            // Aggiorna stato opportunità
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $opportunityId = (int)($data['opportunity_id'] ?? 0);
            $newStatus = $data['status'] ?? '';

            if (!$opportunityId || !$newStatus) {
                throw new Exception('opportunity_id e status richiesti');
            }

            $validStatuses = ['identified', 'contacted', 'demo_scheduled', 'proposal_sent', 'won', 'lost', 'on_hold'];
            if (!in_array($newStatus, $validStatuses)) {
                throw new Exception('Status non valido');
            }

            $updates = ['status = :status'];
            $params = [
                'id' => $opportunityId,
                'status' => $newStatus
            ];

            if ($newStatus === 'contacted' && !isset($data['skip_contacted_at'])) {
                $updates[] = 'contacted_at = NOW()';
            }

            if (in_array($newStatus, ['won', 'lost'])) {
                $updates[] = 'closed_at = NOW()';
            }

            $sql = "UPDATE upsell_opportunities SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Log audit
            $audit->log([
                'azione' => 'upsell_status_update',
                'entita' => 'upsell_opportunity',
                'entita_id' => $opportunityId,
                'descrizione' => "Status cambiato in: {$newStatus}",
                'categoria' => 'sales',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'updated' => $stmt->rowCount()
            ]);
            break;

        case 'mark_won':
            // Segna opportunità come vinta
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $opportunityId = (int)($data['opportunity_id'] ?? 0);
            $wonValue = (float)($data['won_value'] ?? 0);

            if (!$opportunityId || $wonValue <= 0) {
                throw new Exception('opportunity_id e won_value richiesti');
            }

            // Prendi dettagli opportunità
            $stmt = $pdo->prepare("SELECT * FROM upsell_opportunities WHERE id = :id");
            $stmt->execute(['id' => $opportunityId]);
            $opportunity = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$opportunity) {
                throw new Exception('Opportunità non trovata');
            }

            // Aggiorna opportunità
            $stmt = $pdo->prepare("
                UPDATE upsell_opportunities
                SET status = 'won',
                    won_value = :won_value,
                    closed_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $opportunityId,
                'won_value' => $wonValue
            ]);

            // Inserisci nella tabella conversioni
            $stmt = $pdo->prepare("
                INSERT INTO upsell_conversions (
                    opportunity_id,
                    cliente_id,
                    servizio_id,
                    contract_value,
                    conversion_source,
                    sales_rep_id
                ) VALUES (
                    :opportunity_id,
                    :cliente_id,
                    :servizio_id,
                    :contract_value,
                    :conversion_source,
                    :sales_rep_id
                )
            ");

            $stmt->execute([
                'opportunity_id' => $opportunityId,
                'cliente_id' => $opportunity['cliente_id'],
                'servizio_id' => $opportunity['servizio_id'],
                'contract_value' => $wonValue,
                'conversion_source' => $data['source'] ?? 'sales_call',
                'sales_rep_id' => $_SESSION['cliente_id']
            ]);

            // Log audit
            $audit->log([
                'azione' => 'upsell_won',
                'entita' => 'upsell_opportunity',
                'entita_id' => $opportunityId,
                'descrizione' => "Opportunità vinta! Valore: €{$wonValue}",
                'categoria' => 'sales',
                'livello' => 'info',
                'metadata' => json_encode([
                    'cliente_id' => $opportunity['cliente_id'],
                    'servizio_id' => $opportunity['servizio_id'],
                    'value' => $wonValue
                ])
            ]);

            echo json_encode([
                'success' => true,
                'conversion_id' => $pdo->lastInsertId()
            ]);
            break;

        case 'mark_lost':
            // Segna opportunità come persa
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $opportunityId = (int)($data['opportunity_id'] ?? 0);
            $reason = $data['lost_reason'] ?? '';

            if (!$opportunityId) {
                throw new Exception('opportunity_id richiesto');
            }

            $stmt = $pdo->prepare("
                UPDATE upsell_opportunities
                SET status = 'lost',
                    lost_reason = :reason,
                    closed_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $opportunityId,
                'reason' => $reason
            ]);

            echo json_encode([
                'success' => true,
                'updated' => $stmt->rowCount()
            ]);
            break;

        case 'assign':
            // Assegna opportunità a un sales rep
            $rbac->requirePermission('can_edit_clienti');

            $data = json_decode(file_get_contents('php://input'), true);
            $opportunityId = (int)($data['opportunity_id'] ?? 0);
            $assignedTo = (int)($data['assigned_to'] ?? 0);

            if (!$opportunityId || !$assignedTo) {
                throw new Exception('opportunity_id e assigned_to richiesti');
            }

            $stmt = $pdo->prepare("
                UPDATE upsell_opportunities
                SET assigned_to = :assigned_to
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $opportunityId,
                'assigned_to' => $assignedTo
            ]);

            echo json_encode([
                'success' => true,
                'updated' => $stmt->rowCount()
            ]);
            break;

        case 'stats':
            // Statistiche aggregate
            $days = (int)($_GET['days'] ?? 30);

            $stats = $pdo->prepare("
                SELECT
                    COUNT(*) as total_opportunities,
                    SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as total_won,
                    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as total_lost,
                    SUM(CASE WHEN status = 'won' THEN won_value ELSE 0 END) as total_revenue,
                    AVG(opportunity_score) as avg_score,
                    AVG(CASE WHEN status IN ('won', 'lost')
                        THEN DATEDIFF(closed_at, created_at)
                        ELSE NULL
                    END) as avg_days_to_close
                FROM upsell_opportunities
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            ");

            $stats->execute(['days' => $days]);
            $statsData = $stats->fetch(PDO::FETCH_ASSOC);

            // Conversione rate
            $statsData['conversion_rate'] = $statsData['total_opportunities'] > 0
                ? round(($statsData['total_won'] / $statsData['total_opportunities']) * 100, 2)
                : 0;

            // Top servizi
            $topServices = $pdo->prepare("
                SELECT
                    s.nome,
                    COUNT(*) as opportunities,
                    SUM(CASE WHEN uo.status = 'won' THEN 1 ELSE 0 END) as conversions,
                    SUM(CASE WHEN uo.status = 'won' THEN uo.won_value ELSE 0 END) as revenue
                FROM upsell_opportunities uo
                JOIN servizi s ON uo.servizio_id = s.id
                WHERE uo.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY s.id
                ORDER BY revenue DESC
                LIMIT 5
            ");

            $topServices->execute(['days' => $days]);
            $topServicesData = $topServices->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'stats' => $statsData,
                'top_services' => $topServicesData
            ]);
            break;

        case 'performance':
            // Performance trend
            $days = (int)($_GET['days'] ?? 30);

            $stmt = $pdo->prepare("
                SELECT
                    DATE(created_at) as date,
                    COUNT(*) as opportunities_created,
                    SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as opportunities_won,
                    SUM(expected_value) as expected_value,
                    SUM(CASE WHEN status = 'won' THEN won_value ELSE 0 END) as actual_revenue
                FROM upsell_opportunities
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");

            $stmt->execute(['days' => $days]);
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'trend' => $trend
            ]);
            break;

        case 'export':
            // Export CSV
            $format = $_GET['format'] ?? 'csv';

            $stmt = $pdo->query("
                SELECT
                    u.email,
                    u.nome,
                    u.cognome,
                    u.azienda,
                    s.nome as servizio,
                    uo.opportunity_score,
                    uo.opportunity_level,
                    uo.expected_value,
                    uo.status,
                    uo.best_time_to_contact,
                    uo.created_at
                FROM upsell_opportunities uo
                JOIN utenti u ON uo.cliente_id = u.id
                JOIN servizi s ON uo.servizio_id = s.id
                ORDER BY uo.opportunity_score DESC
            ");

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="upsell_opportunities_' . date('Y-m-d') . '.csv"');

                $output = fopen('php://output', 'w');
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

                // Header
                fputcsv($output, [
                    'Email',
                    'Nome',
                    'Cognome',
                    'Azienda',
                    'Servizio',
                    'Score (%)',
                    'Priorità',
                    'Expected Value (€)',
                    'Stato',
                    'Timing',
                    'Data Creazione'
                ]);

                // Data
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['email'],
                        $row['nome'],
                        $row['cognome'],
                        $row['azienda'],
                        $row['servizio'],
                        round($row['opportunity_score'] * 100, 2),
                        strtoupper($row['opportunity_level']),
                        round($row['expected_value'], 2),
                        $row['status'],
                        $row['best_time_to_contact'],
                        $row['created_at']
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

        case 'roi':
            // Calcola ROI
            $days = (int)($_GET['days'] ?? 30);

            $stmt = $pdo->prepare("CALL calculate_upsell_roi(:days, @roi)");
            $stmt->execute(['days' => $days]);

            $result = $pdo->query("SELECT @roi as roi")->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'roi_percentage' => round($result['roi'], 2),
                'period_days' => $days
            ]);
            break;

        case 'top_services':
            // Servizi top performing
            $limit = (int)($_GET['limit'] ?? 10);

            $stmt = $pdo->prepare("CALL top_upsell_services(:limit)");
            $stmt->execute(['limit' => $limit]);

            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'services' => $services
            ]);
            break;

        case 'complementary':
            // Servizi complementari per un servizio
            $servizioId = (int)($_GET['servizio_id'] ?? 0);

            if (!$servizioId) {
                throw new Exception('servizio_id richiesto');
            }

            $stmt = $pdo->prepare("
                SELECT
                    s.*,
                    sc.relevance_score
                FROM servizi_complementari sc
                JOIN servizi s ON sc.servizio_complementare_id = s.id
                WHERE sc.servizio_base_id = :servizio_id
                ORDER BY sc.relevance_score DESC
            ");

            $stmt->execute(['servizio_id' => $servizioId]);
            $complementary = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'complementary_services' => $complementary
            ]);
            break;

        case 'bundles':
            // Lista bundles disponibili
            $stmt = $pdo->query("
                SELECT *
                FROM servizi_bundles
                WHERE attivo = TRUE
                ORDER BY sconto_percentuale DESC
            ");

            $bundles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decodifica servizi_ids
            foreach ($bundles as &$bundle) {
                $bundle['servizi_ids'] = json_decode($bundle['servizi_ids'], true);
            }

            echo json_encode([
                'success' => true,
                'bundles' => $bundles
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
