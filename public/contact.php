<?php
// Simple mail relay for the contact form.
// Runs on shared hosting (Aruba). Expects JSON POST.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

if (!$data) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid JSON']);
  exit;
}

function field($key, $default = '') {
  return isset($GLOBALS['data'][$key]) ? trim($GLOBALS['data'][$key]) : $default;
}

// Basic validation
$name = field('name');
$email = field('email');
$message = field('message');
$phone = field('phone');
$company = field('company');
$need = field('need');
$source = field('source');

if ($name === '' || $email === '' || $message === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

// Configure recipient
$to = getenv('CONTACT_TO') ?: 'info@finch-ai.it';
$subject = 'Richiesta contatto Finch-AI';

$body = "Nome: $name\n"
      . "Email: $email\n"
      . "Telefono: $phone\n"
      . "Azienda: $company\n"
      . "Esigenza: $need\n"
      . "Messaggio: $message\n"
      . "Fonte: $source\n";

$headers = [];
$headers[] = 'From: '.$to;
$headers[] = 'Reply-To: '.$email;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$ok = mail($to, $subject, $body, implode("\r\n", $headers));

if ($ok) {
  echo json_encode(['ok' => true]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Mail send failed']);
}
