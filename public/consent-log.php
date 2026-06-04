<?php
// Registro consensi cookie (GDPR - prova del consenso).
// Riceve una scelta JSON dal banner e la salva nella tabella `consensi_cookie`.
// Best-effort: il banner non dipende dalla risposta. Gira su hosting Aruba.

header('Content-Type: application/json; charset=utf-8');

// CORS: consenti solo le origini del sito (allineato a contact.php).
$allowedOrigins = [
  'https://finch-ai.it',
  'https://www.finch-ai.it',
  'http://localhost:5173',
  'http://127.0.0.1:5173',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
  header('Access-Control-Allow-Origin: ' . $origin);
  header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid JSON']);
  exit;
}

// ---- Validazione / normalizzazione input ----
$consentId = (string)($data['consent_id'] ?? '');
if (!preg_match('/^[0-9a-fA-F-]{8,36}$/', $consentId)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid consent_id']);
  exit;
}

$azione = (string)($data['azione'] ?? '');
if (!in_array($azione, ['accept_all', 'reject_all', 'custom'], true)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid azione']);
  exit;
}

$statistici = !empty($data['statistici']) ? 1 : 0;
$marketing  = !empty($data['marketing']) ? 1 : 0;
$version    = substr((string)($data['version'] ?? ''), 0, 20);
$lang       = (($data['lang'] ?? 'it') === 'en') ? 'en' : 'it';
$pageUrl    = substr((string)($data['page_url'] ?? ''), 0, 500);

// IP client (gestione proxy come audit-logger.php).
function clientIp() {
  $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
  if (strpos($ip, ',') !== false) { $ip = trim(explode(',', $ip)[0]); }
  return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
}
$ip = clientIp();
$userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000);

// ---- Connessione DB (riusa le credenziali da Config; PDO proprio in try/catch
//      per restituire sempre JSON, senza la pagina HTML di db.php). ----
require_once __DIR__ . '/area-clienti/includes/config.php';

try {
  $dbHost = Config::get('DB_HOST', 'localhost');
  $dbName = Config::get('DB_NAME', 'finch_ai_clienti');
  $dbUser = Config::get('DB_USER', 'root');
  $dbPass = Config::get('DB_PASS', '');

  $pdo = new PDO(
    "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
    $dbUser,
    $dbPass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );

  $stmt = $pdo->prepare('
    INSERT INTO consensi_cookie
      (consent_id, ip_address, user_agent, consent_version, necessari, statistici, marketing, azione, lingua, page_url)
    VALUES
      (:consent_id, :ip, :ua, :version, 1, :statistici, :marketing, :azione, :lingua, :page_url)
  ');
  $stmt->execute([
    'consent_id' => $consentId,
    'ip'         => $ip,
    'ua'         => $userAgent,
    'version'    => $version,
    'statistici' => $statistici,
    'marketing'  => $marketing,
    'azione'     => $azione,
    'lingua'     => $lang,
    'page_url'   => $pageUrl,
  ]);

  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  error_log('consent-log error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'Log failed']);
}
