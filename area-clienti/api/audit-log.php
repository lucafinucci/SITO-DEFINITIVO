<?php
/**
 * API Audit Log
 * Gestione e recupero log attività
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = getRBAC($pdo);
$audit = new AuditLogger($pdo);

try {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'list':
            // Lista log con filtri
            $rbac->requirePermission('can_view_audit_log');

            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 50;
            $offset = ($page - 1) * $perPage;

            // Costruisci filtri
            $filtri = [];
            $params = [];

            if (!empty($_GET['user_id'])) {
                $filtri[] = 'user_id = :user_id';
                $params['user_id'] = $_GET['user_id'];
            }

            if (!empty($_GET['azione'])) {
                $filtri[] = 'azione = :azione';
                $params['azione'] = $_GET['azione'];
            }

            if (!empty($_GET['entita'])) {
                $filtri[] = 'entita = :entita';
                $params['entita'] = $_GET['entita'];
            }

            if (!empty($_GET['livello'])) {
                $filtri[] = 'livello = :livello';
                $params['livello'] = $_GET['livello'];
            }

            if (!empty($_GET['categoria'])) {
                $filtri[] = 'categoria = :categoria';
                $params['categoria'] = $_GET['categoria'];
            }

            if (!empty($_GET['richiede_review'])) {
                $filtri[] = 'richiede_review = TRUE';
            }

            // Filtro periodo
            $periodo = $_GET['periodo'] ?? '30d';
            switch ($periodo) {
                case '24h':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                    break;
                case '7d':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case '30d':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case '90d':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'custom':
                    if (!empty($_GET['data_inizio'])) {
                        $filtri[] = 'DATE(created_at) >= :data_inizio';
                        $params['data_inizio'] = $_GET['data_inizio'];
                    }
                    if (!empty($_GET['data_fine'])) {
                        $filtri[] = 'DATE(created_at) <= :data_fine';
                        $params['data_fine'] = $_GET['data_fine'];
                    }
                    break;
            }

            $whereClause = !empty($filtri) ? 'WHERE ' . implode(' AND ', $filtri) : '';

            // Conta totale
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_log $whereClause");
            foreach ($params as $key => $value) {
                $countStmt->bindValue(':' . $key, $value);
            }
            $countStmt->execute();
            $total = $countStmt->fetchColumn();

            // Recupera log
            $stmt = $pdo->prepare("
                SELECT
                    id,
                    user_id,
                    user_email,
                    user_ruolo,
                    user_ip,
                    azione,
                    entita,
                    entita_id,
                    descrizione,
                    livello,
                    categoria,
                    successo,
                    richiede_review,
                    created_at
                FROM audit_log
                $whereClause
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $logs = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'logs' => $logs,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);
            break;

        case 'details':
            // Dettagli singolo log
            $rbac->requirePermission('can_view_audit_log');

            $logId = (int)($_GET['id'] ?? 0);
            if (!$logId) {
                throw new Exception('ID log mancante');
            }

            $stmt = $pdo->prepare('SELECT * FROM audit_log WHERE id = :id');
            $stmt->execute(['id' => $logId]);
            $log = $stmt->fetch();

            if (!$log) {
                throw new Exception('Log non trovato');
            }

            echo json_encode([
                'success' => true,
                'log' => $log
            ]);
            break;

        case 'export':
            // Export log
            $rbac->requirePermission('can_export_data');

            $formato = $_GET['formato'] ?? 'csv';

            // Costruisci filtri (stessa logica di 'list')
            $filtri = [];
            $params = [];

            if (!empty($_GET['user_id'])) {
                $filtri[] = 'user_id = :user_id';
                $params['user_id'] = $_GET['user_id'];
            }

            if (!empty($_GET['azione'])) {
                $filtri[] = 'azione = :azione';
                $params['azione'] = $_GET['azione'];
            }

            if (!empty($_GET['entita'])) {
                $filtri[] = 'entita = :entita';
                $params['entita'] = $_GET['entita'];
            }

            if (!empty($_GET['livello'])) {
                $filtri[] = 'livello = :livello';
                $params['livello'] = $_GET['livello'];
            }

            if (!empty($_GET['categoria'])) {
                $filtri[] = 'categoria = :categoria';
                $params['categoria'] = $_GET['categoria'];
            }

            if (!empty($_GET['richiede_review'])) {
                $filtri[] = 'richiede_review = TRUE';
            }

            // Filtro periodo
            $periodo = $_GET['periodo'] ?? '30d';
            switch ($periodo) {
                case '24h':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                    break;
                case '7d':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case '30d':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case '90d':
                    $filtri[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'custom':
                    if (!empty($_GET['data_inizio'])) {
                        $filtri[] = 'DATE(created_at) >= :data_inizio';
                        $params['data_inizio'] = $_GET['data_inizio'];
                    }
                    if (!empty($_GET['data_fine'])) {
                        $filtri[] = 'DATE(created_at) <= :data_fine';
                        $params['data_fine'] = $_GET['data_fine'];
                    }
                    break;
            }

            $whereClause = !empty($filtri) ? 'WHERE ' . implode(' AND ', $filtri) : '';

            // Recupera tutti i log (limite 10000 per sicurezza)
            $stmt = $pdo->prepare("
                SELECT * FROM audit_log
                $whereClause
                ORDER BY created_at DESC
                LIMIT 10000
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();

            $logs = $stmt->fetchAll();

            // Log export
            $audit->logExport('audit_log', $formato, $_GET, count($logs));

            if ($formato === 'csv') {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d_H-i-s') . '.csv"');

                $output = fopen('php://output', 'w');

                // BOM UTF-8
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header
                fputcsv($output, [
                    'ID',
                    'Data/Ora',
                    'Admin Email',
                    'Ruolo',
                    'IP',
                    'Azione',
                    'Entità',
                    'ID Entità',
                    'Descrizione',
                    'Livello',
                    'Categoria',
                    'Successo',
                    'Richiede Review',
                    'URL',
                    'Metodo'
                ]);

                // Dati
                foreach ($logs as $log) {
                    fputcsv($output, [
                        $log['id'],
                        $log['created_at'],
                        $log['user_email'],
                        $log['user_ruolo'],
                        $log['user_ip'],
                        $log['azione'],
                        $log['entita'],
                        $log['entita_id'],
                        $log['descrizione'],
                        $log['livello'],
                        $log['categoria'],
                        $log['successo'] ? 'Sì' : 'No',
                        $log['richiede_review'] ? 'Sì' : 'No',
                        $log['request_url'],
                        $log['request_method']
                    ]);
                }

                fclose($output);
                exit;

            } elseif ($formato === 'json') {
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d_H-i-s') . '.json"');

                echo json_encode([
                    'exported_at' => date('Y-m-d H:i:s'),
                    'total_records' => count($logs),
                    'filters' => $_GET,
                    'logs' => $logs
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }

            throw new Exception('Formato non supportato');

        case 'stats':
            // Statistiche audit
            $rbac->requirePermission('can_view_audit_log');

            $giorni = (int)($_GET['giorni'] ?? 30);

            $stats = $pdo->prepare('
                SELECT
                    COUNT(*) as totale,
                    SUM(CASE WHEN successo = TRUE THEN 1 ELSE 0 END) as successi,
                    SUM(CASE WHEN successo = FALSE THEN 1 ELSE 0 END) as fallimenti,
                    SUM(CASE WHEN livello = "info" THEN 1 ELSE 0 END) as info,
                    SUM(CASE WHEN livello = "warning" THEN 1 ELSE 0 END) as warning,
                    SUM(CASE WHEN livello = "error" THEN 1 ELSE 0 END) as errori,
                    SUM(CASE WHEN livello = "critical" THEN 1 ELSE 0 END) as critici,
                    COUNT(DISTINCT user_id) as admin_attivi,
                    COUNT(DISTINCT DATE(created_at)) as giorni_attivi
                FROM audit_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :giorni DAY)
            ');
            $stats->execute(['giorni' => $giorni]);
            $statistiche = $stats->fetch();

            // Azioni per categoria
            $perCategoria = $pdo->prepare('
                SELECT
                    categoria,
                    COUNT(*) as count
                FROM audit_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :giorni DAY)
                GROUP BY categoria
                ORDER BY count DESC
            ');
            $perCategoria->execute(['giorni' => $giorni]);
            $categorie = $perCategoria->fetchAll();

            // Top admin attivi
            $topAdmin = $pdo->prepare('
                SELECT
                    user_email,
                    COUNT(*) as azioni
                FROM audit_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :giorni DAY)
                    AND user_id IS NOT NULL
                GROUP BY user_id, user_email
                ORDER BY azioni DESC
                LIMIT 10
            ');
            $topAdmin->execute(['giorni' => $giorni]);
            $adminAttivi = $topAdmin->fetchAll();

            // Timeline attività (per giorno)
            $timeline = $pdo->prepare('
                SELECT
                    DATE(created_at) as data,
                    COUNT(*) as azioni,
                    SUM(CASE WHEN successo = FALSE THEN 1 ELSE 0 END) as fallimenti
                FROM audit_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :giorni DAY)
                GROUP BY DATE(created_at)
                ORDER BY data ASC
            ');
            $timeline->execute(['giorni' => $giorni]);
            $attivitaGiornaliera = $timeline->fetchAll();

            echo json_encode([
                'success' => true,
                'periodo_giorni' => $giorni,
                'statistiche_generali' => $statistiche,
                'per_categoria' => $categorie,
                'top_admin' => $adminAttivi,
                'timeline' => $attivitaGiornaliera
            ]);
            break;

        case 'review':
            // Segna log come revisionato
            $rbac->requirePermission('can_view_audit_log');

            $logId = (int)($_POST['log_id'] ?? 0);
            if (!$logId) {
                throw new Exception('ID log mancante');
            }

            $stmt = $pdo->prepare('
                UPDATE audit_log
                SET richiede_review = FALSE
                WHERE id = :id
            ');
            $stmt->execute(['id' => $logId]);

            // Log della review
            $audit->log([
                'azione' => 'review_audit_log',
                'entita' => 'audit_log',
                'entita_id' => $logId,
                'descrizione' => "Log #$logId revisionato",
                'categoria' => 'sistema',
                'livello' => 'info'
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Log segnato come revisionato'
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
