<?php
/**
 * KPI API Proxy
 * Proxy interno per chiamare API KPI esterna ed evitare problemi CORS
 * Include caching per ridurre chiamate API
 */

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/cache.php';
require __DIR__ . '/../includes/error-handler.php';

header('Content-Type: application/json');

// Verifica autenticazione
if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autenticato']);
    exit;
}

$clienteId = $_SESSION['cliente_id'];

try {
    // Check cache (5 minuti)
    $cacheKey = Cache::userKey($clienteId, 'kpi_data');
    $cachedData = Cache::get($cacheKey);

    if ($cachedData !== null) {
        echo json_encode([
            'success' => true,
            'data' => $cachedData,
            'cached' => true,
        ]);
        exit;
    }

    // Chiamata API esterna
    $apiEndpoint = Config::get('KPI_API_ENDPOINT');
    $apiKey = Config::get('KPI_API_KEY');

    if (empty($apiEndpoint)) {
        // Fallback a dati mockati se API non configurata
        $mockData = [
            'documenti' => 12847,
            'tempo_risparmiato' => '427h',
            'costo_risparmiato' => '€8.450',
            'automazione' => '94.2%',
            'errori_evitati' => 312,
            'roi' => '340%',
            'trend' => [
                'mesi' => ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
                'documenti' => [1850, 2100, 2350, 2450, 2600, 2847],
                'automazione' => [88, 90, 91.5, 92.8, 93.5, 94.2],
                'ore' => [58, 65, 72, 75, 80, 85],
            ],
        ];

        Cache::set($cacheKey, $mockData, 300);

        echo json_encode([
            'success' => true,
            'data' => $mockData,
            'mock' => true,
        ]);
        exit;
    }

    // Costruisci URL con parametri
    $url = $apiEndpoint . '?' . http_build_query([
        'cliente_id' => $clienteId,
        'timestamp' => time(),
    ]);

    // Inizializza cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Finch-AI Area Clienti/1.0',
    ]);

    // Aggiungi header API key se presente
    if (!empty($apiKey)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $apiKey,
            'Accept: application/json',
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        ErrorHandler::logError('KPI API cURL error: ' . $curlError);
        throw new Exception('Errore di connessione API');
    }

    if ($httpCode !== 200) {
        ErrorHandler::logError('KPI API HTTP error: ' . $httpCode);
        throw new Exception('API non disponibile');
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        ErrorHandler::logError('KPI API JSON error: ' . json_last_error_msg());
        throw new Exception('Risposta API non valida');
    }

    // Salva in cache
    Cache::set($cacheKey, $data, 300);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'cached' => false,
    ]);

} catch (Exception $e) {
    ErrorHandler::logError('KPI Proxy error: ' . $e->getMessage());

    // Fallback a dati mockati in caso di errore
    $mockData = [
        'documenti' => 12847,
        'tempo_risparmiato' => '427h',
        'costo_risparmiato' => '€8.450',
        'automazione' => '94.2%',
        'errori_evitati' => 312,
        'roi' => '340%',
        'trend' => [
            'mesi' => ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
            'documenti' => [1850, 2100, 2350, 2450, 2600, 2847],
            'automazione' => [88, 90, 91.5, 92.8, 93.5, 94.2],
            'ore' => [58, 65, 72, 75, 80, 85],
        ],
    ];

    echo json_encode([
        'success' => true,
        'data' => $mockData,
        'fallback' => true,
        'error' => Config::isDebug() ? $e->getMessage() : null,
    ]);
}
