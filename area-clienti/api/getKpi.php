<?php
// Proxy opzionale verso l'API Document Intelligence
// Protezione API-key
$apiKeyConfigured = 'INSERISCI_API_KEY';
$incomingKey = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');

require __DIR__ . '/../includes/auth.php';

if ($incomingKey !== $apiKeyConfigured) {
    http_response_code(401);
    echo json_encode(['error' => 'API key non valida']);
    exit;
}

$clienteId = $_SESSION['cliente_id'];

$token = 'INSERISCI_TOKEN_SICURO';
$endpoint = 'https://app.finch-ai.it/api/kpi/documenti';
$url = $endpoint . '?cliente_id=' . urlencode($clienteId) . '&token=' . urlencode($token);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

header('Content-Type: application/json');

if ($response === false || $code >= 400) {
    http_response_code(502);
    echo json_encode(['error' => 'Impossibile recuperare KPI']);
    exit;
}

echo $response;
