<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

$adminId = $_SESSION['cliente_id'];

// Verifica che sia admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $adminId]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    header('Location: /area-clienti/denied.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$ticketSuccess = null;
$ticketError = null;

$stmt = $pdo->query('
    SELECT id, nome, cognome, email
    FROM utenti
    WHERE ruolo = "admin" AND attivo = 1
    ORDER BY nome, cognome
');
$adminList = $stmt->fetchAll();
$adminIds = array_map('intval', array_column($adminList, 'id'));

if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $ticketSuccess = 'Risposta inviata.';
}
if (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $ticketSuccess = 'Stato aggiornato.';
}

$ticketId = (int)($_GET['id'] ?? 0);

$validStatus = ['aperto', 'in_corso', 'chiuso'];
$validPriorita = ['normale', 'urgente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!$csrfToken || !hash_equals($sessionToken, $csrfToken)) {
        $ticketError = 'Token non valido. Riprova.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'reply_admin') {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $messaggio = trim($_POST['messaggio'] ?? '');
            $newStatus = $_POST['stato'] ?? '';

            if ($ticketId <= 0 || $messaggio === '') {
                $ticketError = 'Dati non validi.';
            } elseif (strlen($messaggio) > 4000) {
                $ticketError = 'Messaggio troppo lungo (max 4000 caratteri).';
            } elseif ($newStatus !== '' && !in_array($newStatus, $validStatus, true)) {
                $ticketError = 'Stato non valido.';
            } else {
                $stmt = $pdo->prepare('SELECT id, stato, assigned_admin_id FROM support_tickets WHERE id = :id');
                $stmt->execute(['id' => $ticketId]);
                $ticketRow = $stmt->fetch();

                if (!$ticketRow) {
                    $ticketError = 'Ticket non trovato.';
                } else {
                    try {
                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare('
                            INSERT INTO support_ticket_messaggi (ticket_id, mittente_tipo, mittente_id, messaggio)
                            VALUES (:ticket_id, "admin", :mittente_id, :messaggio)
                        ');
                        $stmt->execute([
                            'ticket_id' => $ticketId,
                            'mittente_id' => $adminId,
                            'messaggio' => $messaggio
                        ]);

                        $finalStatus = $ticketRow['stato'];
                        if ($newStatus !== '') {
                            $finalStatus = $newStatus;
                        } elseif ($ticketRow['stato'] === 'aperto') {
                            $finalStatus = 'in_corso';
                        }

                        $stmt = $pdo->prepare('
                            UPDATE support_tickets
                            SET stato = :stato,
                                ultimo_messaggio_at = NOW(),
                                assigned_admin_id = COALESCE(assigned_admin_id, :admin_id),
                                assigned_at = IF(assigned_admin_id IS NULL, NOW(), assigned_at)
                            WHERE id = :id
                        ');
                        $stmt->execute([
                            'stato' => $finalStatus,
                            'admin_id' => $adminId,
                            'id' => $ticketId
                        ]);

                        $pdo->commit();

                        header('Location: /area-clienti/admin/ticket.php?id=' . $ticketId . '&sent=1');
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $ticketError = 'Errore durante la risposta.';
                    }
                }
            }
        } elseif ($action === 'update_status') {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $newStatus = $_POST['stato'] ?? '';

            if ($ticketId <= 0 || !in_array($newStatus, $validStatus, true)) {
                $ticketError = 'Stato non valido.';
            } else {
                $stmt = $pdo->prepare('UPDATE support_tickets SET stato = :stato WHERE id = :id');
                $stmt->execute([
                    'stato' => $newStatus,
                    'id' => $ticketId
                ]);

                header('Location: /area-clienti/admin/ticket.php?id=' . $ticketId . '&status=updated');
                exit;
            }
        } elseif ($action === 'assign_ticket') {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $assignedId = (int)($_POST['assigned_admin_id'] ?? 0);
            $assignedValue = $assignedId > 0 ? $assignedId : null;

            if ($ticketId <= 0) {
                $ticketError = 'Ticket non valido.';
            } elseif ($assignedValue !== null && !in_array($assignedId, $adminIds, true)) {
                $ticketError = 'Admin non valido.';
            } else {
                $stmt = $pdo->prepare('
                    UPDATE support_tickets
                    SET assigned_admin_id = :assigned,
                        assigned_at = IF(:assigned IS NULL, NULL, NOW())
                    WHERE id = :id
                ');
                $stmt->execute([
                    'assigned' => $assignedValue,
                    'id' => $ticketId
                ]);

                header('Location: /area-clienti/admin/ticket.php?id=' . $ticketId . '&status=updated');
                exit;
            }
        }
    }
}

$statoFilter = $_GET['stato'] ?? '';
$prioritaFilter = $_GET['priorita'] ?? '';
$search = trim($_GET['q'] ?? '');

if (!in_array($statoFilter, $validStatus, true)) {
    $statoFilter = '';
}
if (!in_array($prioritaFilter, $validPriorita, true)) {
    $prioritaFilter = '';
}

$where = [];
$params = [];

if ($statoFilter !== '') {
    $where[] = 't.stato = :stato';
    $params['stato'] = $statoFilter;
}
if ($prioritaFilter !== '') {
    $where[] = 't.priorita = :priorita';
    $params['priorita'] = $prioritaFilter;
}
if ($search !== '') {
    $where[] = '(t.oggetto LIKE :q OR u.email LIKE :q OR u.azienda LIKE :q)';
    $params['q'] = '%' . $search . '%';
}

$sql = '
    SELECT t.id, t.oggetto, t.priorita, t.stato, t.ultimo_messaggio_at, t.updated_at,
           u.azienda, u.email,
           a.nome AS assigned_nome, a.cognome AS assigned_cognome
    FROM support_tickets t
    JOIN utenti u ON u.id = t.cliente_id
    LEFT JOIN utenti a ON a.id = t.assigned_admin_id
';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY COALESCE(t.ultimo_messaggio_at, t.updated_at) DESC LIMIT 30';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ticketList = $stmt->fetchAll();

$ticketDetail = null;
$messaggi = [];

if ($ticketId > 0) {
    $stmt = $pdo->prepare('
        SELECT t.*, u.azienda, u.email, u.nome, u.cognome,
               a.nome AS assigned_nome, a.cognome AS assigned_cognome, a.email AS assigned_email
        FROM support_tickets t
        JOIN utenti u ON u.id = t.cliente_id
        LEFT JOIN utenti a ON a.id = t.assigned_admin_id
        WHERE t.id = :id
    ');
    $stmt->execute(['id' => $ticketId]);
    $ticketDetail = $stmt->fetch();

    if ($ticketDetail) {
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
}

$ticketStatusMap = [
    'aperto' => ['Aperto', 'warning'],
    'in_corso' => ['In corso', 'info'],
    'chiuso' => ['Chiuso', 'success']
];
$ticketPriorityMap = [
    'normale' => 'Normale',
    'urgente' => 'Urgente'
];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Ticket Supporto - Admin</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }
    .admin-nav {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    .admin-nav a {
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: #0f172a;
    }
    .admin-nav a.active {
      border-color: var(--accent1);
      color: var(--accent1);
    }
    .ticket-columns {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
      gap: 20px;
    }
    @media (max-width: 1000px) {
      .ticket-columns { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">

  <div class="admin-header">
    <div>
      <h1 style="margin: 0 0 8px 0;">Ticket Supporto</h1>
      <p class="muted">Gestione richieste clienti</p>
    </div>
    <div class="admin-nav">
      <a href="/area-clienti/admin/gestione-servizi.php">Servizi Clienti</a>
      <a href="/area-clienti/admin/richieste-addestramento.php">Richieste Addestramento</a>
      <a href="/area-clienti/admin/fatture.php">Fatture</a>
      <a href="/area-clienti/admin/scadenzario.php">Scadenzario</a>
      <a href="/area-clienti/admin/pipeline.php">Pipeline Vendite</a>
      <a href="/area-clienti/admin/preventivi.php">Preventivi</a>
      <a href="/area-clienti/admin/ticket.php" class="active">Ticket</a>
    </div>
  </div>

  <?php if ($ticketSuccess): ?>
    <div class="alert success"><?= htmlspecialchars($ticketSuccess) ?></div>
  <?php endif; ?>
  <?php if ($ticketError): ?>
    <div class="alert error"><?= htmlspecialchars($ticketError) ?></div>
  <?php endif; ?>

  <div class="ticket-columns">
    <section class="card">
      <h3 style="margin-top: 0;">Elenco ticket</h3>
      <form method="get" class="form-grid" style="margin-bottom: 12px;">
        <label>
          Cerca
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Oggetto, email, azienda">
        </label>
        <label>
          Stato
          <select name="stato">
            <option value="">Tutti</option>
            <?php foreach ($validStatus as $s): ?>
              <option value="<?= $s ?>" <?= $s === $statoFilter ? 'selected' : '' ?>>
                <?= htmlspecialchars($ticketStatusMap[$s][0]) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Priorita
          <select name="priorita">
            <option value="">Tutte</option>
            <?php foreach ($validPriorita as $p): ?>
              <option value="<?= $p ?>" <?= $p === $prioritaFilter ? 'selected' : '' ?>>
                <?= htmlspecialchars($ticketPriorityMap[$p]) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <button class="btn ghost" type="submit">Filtra</button>
      </form>

      <?php if (empty($ticketList)): ?>
        <p class="muted">Nessun ticket trovato.</p>
      <?php else: ?>
        <div class="services-list">
          <?php foreach ($ticketList as $row): ?>
            <?php
              $statusInfo = $ticketStatusMap[$row['stato']] ?? [$row['stato'], 'neutral'];
              $priorityLabel = $ticketPriorityMap[$row['priorita']] ?? $row['priorita'];
              $lastUpdate = $row['ultimo_messaggio_at'] ?? $row['updated_at'];
            ?>
            <a class="ticket-item" href="/area-clienti/admin/ticket.php?id=<?= (int)$row['id'] ?>">
              <div>
                <p class="ticket-title"><?= htmlspecialchars($row['oggetto']) ?></p>
                <div class="ticket-meta">
                  <span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0]) ?></span>
                  <span class="muted small"><?= htmlspecialchars($row['azienda']) ?></span>
                  <span class="muted small"><?= htmlspecialchars($row['email']) ?></span>
                  <?php if (!empty($row['assigned_nome'])): ?>
                    <span class="muted small">Assegnato a: <?= htmlspecialchars(trim($row['assigned_nome'] . ' ' . $row['assigned_cognome'])) ?></span>
                  <?php endif; ?>
                </div>
                <p class="muted small" style="margin: 8px 0 0 0;">Aggiornato: <?= date('d/m/Y H:i', strtotime($lastUpdate)) ?></p>
              </div>
              <div class="muted small">Priorita: <?= htmlspecialchars($priorityLabel) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="card">
      <?php if (!$ticketDetail): ?>
        <h3 style="margin-top: 0;">Dettaglio ticket</h3>
        <p class="muted">Seleziona un ticket per visualizzare la conversazione.</p>
      <?php else: ?>
        <?php
          $statusInfo = $ticketStatusMap[$ticketDetail['stato']] ?? [$ticketDetail['stato'], 'neutral'];
          $priorityLabel = $ticketPriorityMap[$ticketDetail['priorita']] ?? $ticketDetail['priorita'];
        ?>
        <div class="card-header">
          <div>
            <h3 style="margin: 0 0 8px 0;"><?= htmlspecialchars($ticketDetail['oggetto']) ?></h3>
            <div class="ticket-meta">
              <span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0]) ?></span>
              <span class="muted small">Priorita: <?= htmlspecialchars($priorityLabel) ?></span>
              <span class="muted small">
                Assegnato a:
                <?php if (!empty($ticketDetail['assigned_nome'])): ?>
                  <?= htmlspecialchars(trim($ticketDetail['assigned_nome'] . ' ' . $ticketDetail['assigned_cognome'])) ?>
                <?php else: ?>
                  Non assegnato
                <?php endif; ?>
              </span>
            </div>
          </div>
          <div class="muted small">
            <?= htmlspecialchars($ticketDetail['azienda']) ?><br>
            <?= htmlspecialchars($ticketDetail['email']) ?>
          </div>
        </div>

        <div class="ticket-thread">
          <?php if (empty($messaggi)): ?>
            <p class="muted">Nessun messaggio disponibile.</p>
          <?php else: ?>
            <?php foreach ($messaggi as $msg): ?>
              <?php
                $isAdmin = $msg['mittente_tipo'] === 'admin';
                $metaName = $isAdmin ? 'Team Supporto' : 'Cliente';
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

        <div style="margin-top: 16px;">
          <form method="post" class="form-grid" style="margin-bottom: 10px;">
            <input type="hidden" name="action" value="assign_ticket">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticketDetail['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <label>
              Assegna a
              <select name="assigned_admin_id">
                <option value="0">Non assegnato</option>
                <?php foreach ($adminList as $admin): ?>
                  <?php
                    $adminLabel = trim($admin['nome'] . ' ' . $admin['cognome']);
                    if ($adminLabel === '') {
                      $adminLabel = $admin['email'];
                    }
                  ?>
                  <option value="<?= (int)$admin['id'] ?>" <?= (int)$ticketDetail['assigned_admin_id'] === (int)$admin['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($adminLabel) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn ghost" type="submit">Aggiorna Assegnazione</button>
          </form>

          <form method="post" class="form-grid">
            <input type="hidden" name="action" value="reply_admin">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticketDetail['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <label>
              Risposta
              <textarea name="messaggio" rows="5" maxlength="4000" required></textarea>
            </label>
            <label>
              Stato ticket
              <select name="stato">
                <option value="">Nessuna modifica</option>
                <?php foreach ($validStatus as $s): ?>
                  <option value="<?= $s ?>"><?= htmlspecialchars($ticketStatusMap[$s][0]) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn primary" type="submit">Invia Risposta</button>
          </form>

          <form method="post" style="margin-top: 10px;">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticketDetail['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div style="display: flex; gap: 10px; align-items: center;">
              <select name="stato">
                <?php foreach ($validStatus as $s): ?>
                  <option value="<?= $s ?>" <?= $s === $ticketDetail['stato'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ticketStatusMap[$s][0]) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn ghost" type="submit">Aggiorna Stato</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </section>
  </div>

</main>
</body>
</html>
