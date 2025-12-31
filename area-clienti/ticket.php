<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];
$ticketId = (int)($_GET['id'] ?? 0);


$hasAssignedAdmin = false;
try {
    $stmtCols = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'support_tickets'
          AND COLUMN_NAME = 'assigned_admin_id'
    ");
    $stmtCols->execute();
    $hasAssignedAdmin = (bool)$stmtCols->fetchColumn();
} catch (PDOException $e) {
    $hasAssignedAdmin = false;
}

$assignedSelect = 'NULL AS assigned_nome, NULL AS assigned_cognome';
$assignedJoin = '';
if ($hasAssignedAdmin) {
    $assignedSelect = 'a.nome AS assigned_nome, a.cognome AS assigned_cognome';
    $assignedJoin = 'LEFT JOIN utenti a ON a.id = t.assigned_admin_id';
}

if ($ticketId <= 0) {
    header('Location: /area-clienti/dashboard.php');
    exit;
}

$sql = "
    SELECT t.*, u.azienda, u.email, {$assignedSelect}
    FROM support_tickets t
    JOIN utenti u ON u.id = t.cliente_id
    {$assignedJoin}
    WHERE t.id = :id AND t.cliente_id = :cliente_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $ticketId,
    'cliente_id' => $clienteId
]);
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    $ticketNotFound = true;
} else {
    $ticketNotFound = false;
}

$ticketSuccess = null;
$ticketError = null;

if (!$ticketNotFound && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reply_ticket') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $messaggio = trim($_POST['messaggio'] ?? '');

    if (!$csrfToken || !hash_equals($sessionToken, $csrfToken)) {
        $ticketError = 'Token non valido. Riprova.';
    } elseif ($messaggio === '') {
        $ticketError = 'Il messaggio non puo essere vuoto.';
    } elseif (strlen($messaggio) > 4000) {
        $ticketError = 'Messaggio troppo lungo (max 4000 caratteri).';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('
                INSERT INTO support_ticket_messaggi (ticket_id, mittente_tipo, mittente_id, messaggio)
                VALUES (:ticket_id, "cliente", :mittente_id, :messaggio)
            ');
            $stmt->execute([
                'ticket_id' => $ticketId,
                'mittente_id' => $clienteId,
                'messaggio' => $messaggio
            ]);

            $stmt = $pdo->prepare('
                UPDATE support_tickets
                SET stato = IF(stato = "chiuso", "aperto", stato),
                    ultimo_messaggio_at = NOW()
                WHERE id = :id
            ');
            $stmt->execute(['id' => $ticketId]);

            $pdo->commit();

            header('Location: /area-clienti/ticket.php?id=' . $ticketId . '&sent=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $ticketError = 'Errore durante l invio della risposta.';
        }
    }
}

if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $ticketSuccess = 'Messaggio inviato.';
}

$ticketStatusMap = [
    'aperto' => ['Aperto', 'warning'],
    'in_corso' => ['In corso', 'info'],
    'chiuso' => ['Chiuso', 'success']
];

$messaggi = [];
if (!$ticketNotFound) {
    $stmt = $pdo->prepare('
        SELECT m.*, u.nome, u.cognome
        FROM support_ticket_messaggi m
        LEFT JOIN utenti u ON u.id = m.mittente_id
        WHERE m.ticket_id = :ticket_id
        ORDER BY m.created_at ASC
    ');
    $stmt->execute(['ticket_id' => $ticketId]);
    $messaggi = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket Supporto - Area Clienti</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <?php if ($ticketNotFound): ?>
    <section class="card">
      <h3>Ticket non trovato</h3>
      <p class="muted">Il ticket richiesto non esiste o non hai accesso.</p>
      <a class="btn ghost" href="/area-clienti/dashboard.php">Torna alla Dashboard</a>
    </section>
  <?php else: ?>
    <?php
    $statusInfo = $ticketStatusMap[$ticket['stato']] ?? [$ticket['stato'], 'neutral'];
    $statusLabel = $statusInfo[0];
    $statusClass = $statusInfo[1];
    ?>
    <section class="card">
      <div class="card-header">
        <div>
          <h3 style="margin: 0 0 8px 0;"><?= htmlspecialchars($ticket['oggetto']) ?></h3>
          <div class="ticket-meta">
            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
            <span class="muted small">Creato: <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
            <span class="muted small">
              In carico a:
              <?php if (!empty($ticket['assigned_nome'])): ?>
                <?= htmlspecialchars(trim($ticket['assigned_nome'] . ' ' . $ticket['assigned_cognome'])) ?>
              <?php else: ?>
                Team Supporto
              <?php endif; ?>
            </span>
          </div>
        </div>
        <a class="btn ghost small" href="/area-clienti/dashboard.php">Torna alla Dashboard</a>
      </div>

      <?php if ($ticketSuccess): ?>
        <div class="alert success"><?= htmlspecialchars($ticketSuccess) ?></div>
      <?php endif; ?>
      <?php if ($ticketError): ?>
        <div class="alert error"><?= htmlspecialchars($ticketError) ?></div>
      <?php endif; ?>

      <div class="ticket-thread">
        <?php if (empty($messaggi)): ?>
          <p class="muted">Nessun messaggio disponibile.</p>
        <?php else: ?>
          <?php foreach ($messaggi as $msg): ?>
            <?php
              $isAdmin = $msg['mittente_tipo'] === 'admin';
              $metaName = $isAdmin ? 'Supporto Finch-AI' : 'Tu';
              if (!$isAdmin && $msg['nome']) {
                  $metaName = $msg['nome'] . ' ' . $msg['cognome'];
              }
            ?>
            <div class="ticket-message <?= $isAdmin ? 'admin' : 'cliente' ?>">
              <div class="meta">
                <strong><?= htmlspecialchars(trim($metaName)) ?></strong>
                <span class="muted small"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
              </div>
              <div><?= nl2br(htmlspecialchars($msg['messaggio'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div style="margin-top: 20px;">
        <?php if ($ticket['stato'] === 'chiuso'): ?>
          <p class="muted">Il ticket e chiuso. Puoi inviare un messaggio per riaprirlo.</p>
        <?php endif; ?>
        <form method="post" class="form-grid">
          <input type="hidden" name="action" value="reply_ticket">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <label>
            Nuovo messaggio
            <textarea name="messaggio" rows="5" maxlength="4000" required></textarea>
          </label>
          <button class="btn primary" type="submit">Invia Risposta</button>
        </form>
      </div>
    </section>
  <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
