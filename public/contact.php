<?php
// Simple mail relay for the contact form.
// Runs on shared hosting (Aruba). Expects JSON POST.

header('Content-Type: application/json');

// CORS: consenti solo le origini del sito (no wildcard).
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

// Rate-limit basilare per IP (anti-spam): max 5 invii / 10 min.
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rlFile = sys_get_temp_dir() . '/contact_rl_' . md5($ip) . '.txt';
$now = time();
$hits = [];
if (is_file($rlFile)) {
  $hits = array_filter(
    array_map('intval', explode(',', (string)@file_get_contents($rlFile))),
    function ($t) use ($now) { return $t > $now - 600; }
  );
}
if (count($hits) >= 5) {
  http_response_code(429);
  echo json_encode(['error' => 'Too many requests']);
  exit;
}
$hits[] = $now;
@file_put_contents($rlFile, implode(',', $hits), LOCK_EX);

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
// Campi strutturati (inviati dal form chatbot; opzionali per gli altri form).
$problem = field('problem');
$day = field('day');
$slot = field('slot');
$transcript = field('transcript');
$lang = field('lang') === 'en' ? 'en' : 'it';

if ($name === '' || $email === '' || $message === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

// Anti email-header-injection: rifiuta CR/LF in ogni campo che finisce negli header.
foreach ([$email, $name, $phone, $company, $need, $source] as $headerField) {
  if (preg_match('/[\r\n]/', (string)$headerField)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
  }
}

// Valida il formato email (usato in Reply-To).
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid email']);
  exit;
}

// Configurazione
$to = getenv('CONTACT_TO') ?: 'info@finch-ai.it';
// Link prenotazione (Calendly): il pulsante compare solo se questa env è impostata.
// Lasciare vuoto disattiva il CTA finché l'account non è configurato.
$bookingUrl = getenv('CONTACT_BOOKING_URL') ?: '';
$brand = '#0d9488';
$logo = 'https://finch-ai.it/favicon-192.png';

// Escape per l'HTML.
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Data yyyy-mm-dd -> dd/mm/yyyy (se valorizzata e valida).
function fmtDay($day) {
  if ($day === '' ) return '';
  $d = DateTime::createFromFormat('Y-m-d', $day);
  return $d ? $d->format('d/m/Y') : $day;
}

// Fascia preferita leggibile: "12/06/2026 — Pomeriggio (14-18)".
function preferredSlot($day, $slot) {
  $parts = array_filter([fmtDay($day), $slot], function ($p) { return $p !== ''; });
  return implode(' — ', $parts);
}

// Riga di tabella per l'email del team.
function row($label, $value) {
  if ($value === '' || $value === null) return '';
  return '<tr>'
    . '<td style="padding:8px 12px;background:#f1f5f9;font-weight:600;color:#0f172a;white-space:nowrap;vertical-align:top;border-bottom:1px solid #e2e8f0;">' . e($label) . '</td>'
    . '<td style="padding:8px 12px;color:#0f172a;border-bottom:1px solid #e2e8f0;">' . nl2br(e($value)) . '</td>'
    . '</tr>';
}

// Invio email HTML. Ritorna bool.
function sendHtml($to, $subject, $html, $extraHeaders = []) {
  $headers = array_merge([
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
  ], $extraHeaders);
  // Subject codificato per supportare accenti/emoji.
  $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
  return mail($to, $encodedSubject, $html, implode("\r\n", $headers));
}

// Shell HTML brandizzata condivisa.
function shell($title, $inner) {
  global $brand, $logo;
  return '<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8">'
    . '<meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
    . '<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">'
    . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 0;"><tr><td align="center">'
    . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">'
    . '<tr><td style="background:' . $brand . ';padding:20px 28px;">'
    . '<table role="presentation" cellpadding="0" cellspacing="0"><tr>'
    . '<td style="vertical-align:middle;"><img src="' . $logo . '" width="36" height="36" alt="Finch-AI" style="display:block;border-radius:8px;"></td>'
    . '<td style="vertical-align:middle;padding-left:12px;color:#ffffff;font-size:18px;font-weight:700;">Finch-AI</td>'
    . '</tr></table></td></tr>'
    . '<tr><td style="padding:28px;">'
    . '<h1 style="margin:0 0 16px;font-size:20px;color:#0f172a;">' . e($title) . '</h1>'
    . $inner
    . '</td></tr>'
    . '<tr><td style="padding:18px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;text-align:center;">'
    . 'Finch-AI · <a href="mailto:info@finch-ai.it" style="color:' . $brand . ';text-decoration:none;">info@finch-ai.it</a> · '
    . '<a href="https://finch-ai.it" style="color:' . $brand . ';text-decoration:none;">finch-ai.it</a>'
    . '</td></tr>'
    . '</table></td></tr></table></body></html>';
}

// Pulsante CTA.
function button($url, $label) {
  global $brand;
  return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 4px;"><tr>'
    . '<td style="border-radius:10px;background:' . $brand . ';">'
    . '<a href="' . e($url) . '" target="_blank" style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">'
    . e($label) . '</a></td></tr></table>';
}

$preferred = preferredSlot($day, $slot);

// ---------- Email per il TEAM ----------
$teamRows = row('Nome', $name)
  . row('Email', $email)
  . row('Telefono', $phone)
  . row('Azienda', $company)
  . row('Esigenza', $need)
  . row('Descrizione', $problem !== '' ? $problem : $message)
  . row('Fascia preferita', $preferred)
  . row('Fonte', $source);

$teamInner = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" '
  . 'style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;font-size:14px;">'
  . $teamRows . '</table>';

if ($transcript !== '') {
  $teamInner .= '<h2 style="margin:24px 0 8px;font-size:15px;color:#0f172a;">Conversazione</h2>'
    . '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;'
    . 'font-size:13px;color:#334155;line-height:1.6;white-space:normal;">'
    . nl2br(e($transcript)) . '</div>';
}

$teamHtml = shell('Nuova richiesta dal sito', $teamInner);
$teamSubject = 'Nuova richiesta valutazione (chatbot) — ' . $name;

$ok = sendHtml($to, $teamSubject, $teamHtml, [
  'From: ' . $to,
  'Reply-To: ' . $email,
]);

// ---------- Email di conferma all'UTENTE (best-effort, versione ridotta) ----------
$copy = [
  'it' => [
    'subject'      => 'Abbiamo ricevuto la tua richiesta — Finch-AI',
    'title'        => 'Grazie, ' . $name . '!',
    'intro'        => 'Abbiamo ricevuto la tua richiesta di valutazione. Il team Finch-AI la esaminerà e ti ricontatterà a breve. Se preferisci, puoi già scegliere un momento per la riunione:',
    'introNoBook'  => 'Abbiamo ricevuto la tua richiesta di valutazione. Il team Finch-AI la esaminerà e ti ricontatterà a breve per fissare un appuntamento.',
    'summary'      => 'Riepilogo della tua richiesta',
    'needLbl'      => 'Esigenza',
    'slotLbl'      => 'Fascia preferita',
    'cta'          => 'Pianifica la riunione',
    'outro'        => 'Per qualsiasi cosa, rispondi pure a questa email o scrivici a info@finch-ai.it.',
  ],
  'en' => [
    'subject'      => 'We received your request — Finch-AI',
    'title'        => 'Thank you, ' . $name . '!',
    'intro'        => 'We received your evaluation request. The Finch-AI team will review it and get back to you shortly. If you prefer, you can already pick a time for the meeting:',
    'introNoBook'  => 'We received your evaluation request. The Finch-AI team will review it and get back to you shortly to schedule a meeting.',
    'summary'      => 'Summary of your request',
    'needLbl'      => 'Need',
    'slotLbl'      => 'Preferred time',
    'cta'          => 'Schedule the meeting',
    'outro'        => 'For anything else, just reply to this email or write to info@finch-ai.it.',
  ],
];
$c = $copy[$lang];

$userSummary = '';
$desc = $problem !== '' ? $problem : '';
if ($desc !== '') {
  $userSummary .= '<p style="margin:4px 0;font-size:14px;color:#334155;"><strong>' . e($c['needLbl']) . ':</strong> ' . nl2br(e($desc)) . '</p>';
}
if ($preferred !== '') {
  $userSummary .= '<p style="margin:4px 0;font-size:14px;color:#334155;"><strong>' . e($c['slotLbl']) . ':</strong> ' . e($preferred) . '</p>';
}

$userIntro = $bookingUrl !== '' ? $c['intro'] : $c['introNoBook'];
$userInner = '<p style="margin:0 0 16px;font-size:15px;color:#334155;line-height:1.6;">' . e($userIntro) . '</p>'
  . ($bookingUrl !== '' ? button($bookingUrl, $c['cta']) : '');

if ($userSummary !== '') {
  $userInner .= '<div style="margin:20px 0 4px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">'
    . '<p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#0f172a;text-transform:uppercase;letter-spacing:0.03em;">' . e($c['summary']) . '</p>'
    . $userSummary . '</div>';
}

$userInner .= '<p style="margin:20px 0 0;font-size:13px;color:#64748b;line-height:1.6;">' . e($c['outro']) . '</p>';

$userHtml = shell($c['title'], $userInner);

@sendHtml($email, $c['subject'], $userHtml, [
  'From: ' . $to,
  'Reply-To: ' . $to,
]);

// La risposta è OK se l'email al team è partita; la conferma all'utente è best-effort.
if ($ok) {
  echo json_encode(['ok' => true]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Mail send failed']);
}
