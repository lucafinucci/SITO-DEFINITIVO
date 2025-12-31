<?php
/**
 * API Admin - KPI Document Intelligence per Cliente
 * Recupera i KPI dalla webapp esterna per tutti i clienti o un cliente specifico
 */

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Verifica che sia admin
if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autenticato']);
    exit;
}

$adminId = $_SESSION['cliente_id'];
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $adminId]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

try {
    $clienteIdFilter = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;

    // Recupera lista clienti con servizio Document Intelligence attivo
    $where = "s.codice = 'DOC-INT' AND us.stato = 'attivo'";
    if ($clienteIdFilter) {
        $where .= " AND u.id = :cliente_id";
    }

    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.id,
            u.nome,
            u.cognome,
            u.email,
            u.azienda,
            us.data_attivazione,
            s.nome AS servizio_nome,
            s.codice AS servizio_codice
        FROM utenti u
        JOIN utenti_servizi us ON u.id = us.user_id
        JOIN servizi s ON us.servizio_id = s.id
        WHERE $where
        ORDER BY u.azienda ASC, u.cognome ASC
    ");

    if ($clienteIdFilter) {
        $stmt->execute(['cliente_id' => $clienteIdFilter]);
    } else {
        $stmt->execute();
    }

    $clienti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clienti)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'Nessun cliente con Document Intelligence attivo'
        ]);
        exit;
    }

    // Configurazione API esterna
    $apiEndpoint = Config::get('WEBAPP_API_URL', '');
    $apiToken = Config::get('WEBAPP_API_TOKEN', '');

    if (empty($apiEndpoint)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $apiEndpoint = $protocol . '://' . $host . '/area-clienti/api/mock-kpi-webapp.php';
    }

    if (empty($apiToken) && strpos($apiEndpoint, 'mock-kpi-webapp.php') !== false) {
        $apiToken = 'test_token_locale_123456';
    }

    $risultati = [];

    foreach ($clienti as $cliente) {
        $clienteId = (int)$cliente['id'];

        // 1. Recupera dati locali (quota uso)
        $currentPeriod = date('Y-m');
        $stmt = $pdo->prepare('
            SELECT documenti_usati, pagine_analizzate
            FROM servizi_quota_uso
            WHERE cliente_id = :cliente_id
                AND servizio_id = (SELECT id FROM servizi WHERE codice = "DOC-INT" LIMIT 1)
                AND periodo = :periodo
            LIMIT 1
        ');
        $stmt->execute([
            'cliente_id' => $clienteId,
            'periodo' => $currentPeriod
        ]);
        $quotaUso = $stmt->fetch(PDO::FETCH_ASSOC);

        $documentiLocali = $quotaUso ? (int)$quotaUso['documenti_usati'] : 0;
        $pagineLocali = $quotaUso ? (int)$quotaUso['pagine_analizzate'] : 0;

        // 2. Tenta di recuperare dati dalla webapp esterna
        $kpiWebapp = null;
        $url = $apiEndpoint . '?' . http_build_query([
            'cliente_id' => $clienteId,
            'token' => $apiToken,
            'timestamp' => time()
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: Finch-AI Admin/1.0'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Parsing risposta API esterna
        if (!$curlError && $httpCode === 200) {
            $webappData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($webappData['success']) && $webappData['success']) {
                $kpiWebapp = $webappData['data'] ?? null;
            }
        }

        // 3. Combina dati locali + webapp
        $risultati[] = [
            'cliente' => [
                'id' => $clienteId,
                'nome' => $cliente['nome'],
                'cognome' => $cliente['cognome'],
                'email' => $cliente['email'],
                'azienda' => $cliente['azienda'],
                'data_attivazione' => $cliente['data_attivazione']
            ],
            'kpi_locali' => [
                'documenti_mese' => $documentiLocali,
                'pagine_mese' => $pagineLocali,
                'periodo' => $currentPeriod
            ],
            'kpi_webapp' => $kpiWebapp,
            'api_status' => [
                'disponibile' => $kpiWebapp !== null,
                'http_code' => $httpCode ?? null,
                'error' => $curlError ?: null
            ]
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $risultati,
        'totale_clienti' => count($risultati),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore durante il recupero dei KPI',
        'details' => $e->getMessage()
    ]);
}
