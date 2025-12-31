<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/fatture-settings.php';

$clienteId = $_SESSION['cliente_id'];
$fattureSettings = getFattureSettings($pdo);
$mostraSoloInviate = (int)$fattureSettings['mostra_cliente_solo_inviate'] === 1;
$colCliente = 'user_id';
$hasClienteId = false;
$hasUserId = false;
$gatewayAttivo = false;
try {
  $stmtCols = $pdo->prepare("
      SELECT COLUMN_NAME
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'fatture'
        AND COLUMN_NAME IN ('cliente_id', 'user_id')
  ");
  $stmtCols->execute();
  $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
  $hasClienteId = in_array('cliente_id', $cols, true);
  $hasUserId = in_array('user_id', $cols, true);
  if ($hasClienteId && $hasUserId) {
    $colCliente = 'COALESCE(cliente_id, user_id)';
  } elseif ($hasClienteId) {
    $colCliente = 'cliente_id';
  } elseif ($hasUserId) {
    $colCliente = 'user_id';
  }
} catch (PDOException $e) {
  $colCliente = 'user_id';
}

try {
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM payment_gateways_config WHERE attivo = TRUE');
  $stmt->execute();
  $gatewayAttivo = ((int)$stmt->fetchColumn()) > 0;
} catch (PDOException $e) {
  $gatewayAttivo = false;
}

$ticketSuccess = null;
$ticketError = null;

if (isset($_GET['ticket']) && $_GET['ticket'] === 'success') {
  $ticketSuccess = 'Ticket creato correttamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_ticket') {
  $csrfToken = $_POST['csrf_token'] ?? '';
  $sessionToken = $_SESSION['csrf_token'] ?? '';

  if (!$csrfToken || !hash_equals($sessionToken, $csrfToken)) {
    $ticketError = 'Token non valido. Riprova.';
  } else {
    $oggetto = trim($_POST['oggetto'] ?? '');
    $messaggio = trim($_POST['messaggio'] ?? '');
    $priorita = $_POST['priorita'] ?? 'media';
    $prioritaValide = ['normale', 'urgente'];

    if ($oggetto === '' || $messaggio === '') {
      $ticketError = 'Oggetto e messaggio sono obbligatori.';
    } elseif (strlen($oggetto) > 200) {
      $ticketError = 'Oggetto troppo lungo (max 200 caratteri).';
    } elseif (strlen($messaggio) > 4000) {
      $ticketError = 'Messaggio troppo lungo (max 4000 caratteri).';
    } elseif (!in_array($priorita, $prioritaValide, true)) {
      $ticketError = 'Priorita non valida.';
    } else {
      try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('
          INSERT INTO support_tickets (cliente_id, oggetto, priorita, stato, ultimo_messaggio_at)
          VALUES (:cliente_id, :oggetto, :priorita, "aperto", NOW())
        ');
        $stmt->execute([
          'cliente_id' => $clienteId,
          'oggetto' => $oggetto,
          'priorita' => $priorita
        ]);

        $ticketId = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare('
          INSERT INTO support_ticket_messaggi (ticket_id, mittente_tipo, mittente_id, messaggio)
          VALUES (:ticket_id, "cliente", :mittente_id, :messaggio)
        ');
        $stmt->execute([
          'ticket_id' => $ticketId,
          'mittente_id' => $clienteId,
          'messaggio' => $messaggio
        ]);

        $pdo->commit();

        header('Location: /area-clienti/dashboard.php?ticket=success');
        exit;
      } catch (Exception $e) {
        $pdo->rollBack();
        $ticketError = 'Errore durante la creazione del ticket.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Area Clienti Finch-AI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .service-item.clickable {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .service-item.clickable:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(34, 211, 238, 0.3);
      border-color: #22d3ee !important;
    }
  </style>
</head>
<body>
<!-- Canvas della rete neurale -->
<canvas id="neural-canvas"></canvas>

<!-- Layer decorativi aggiuntivi -->
<div class="bg-layers">
  <div class="bg-diagonal-gradient"></div>
  <div class="bg-scanlines"></div>
</div>

<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <?php
  // Recupera servizi attivi dell'utente (ora a livello aziendale)
  $stmt = $pdo->prepare('
    SELECT s.id, s.nome, s.descrizione, s.codice, s.prezzo_mensile, s.costo_per_pagina,
           app.prezzo_mensile AS prezzo_personalizzato,
           app.costo_per_pagina AS costo_per_pagina_personalizzato,
           COALESCE(app.prezzo_mensile, s.prezzo_mensile) AS prezzo_finale,
           COALESCE(app.costo_per_pagina, s.costo_per_pagina) AS costo_per_pagina_finale,
           ase.data_attivazione, ase.stato
    FROM utenti u
    INNER JOIN aziende_servizi ase ON u.azienda_id = ase.azienda_id
    JOIN servizi s ON ase.servizio_id = s.id
    LEFT JOIN aziende_prezzi_personalizzati app
      ON app.azienda_id = u.azienda_id AND app.servizio_id = s.id
    WHERE u.id = :user_id AND ase.stato = "attivo"
    ORDER BY ase.data_attivazione DESC
  ');
  $stmt->execute(['user_id' => $clienteId]);
  $serviziAttivi = $stmt->fetchAll();

  $currentPeriod = date('Y-m');
  $usageByService = [];
  // TODO: Aggiornare per usare aziende_servizi_uso
  /*
  $stmt = $pdo->prepare('
    SELECT servizio_id, documenti_usati
    FROM servizi_quota_uso
    WHERE cliente_id = :cliente_id AND periodo = :periodo
  ');
  $stmt->execute([
    'cliente_id' => $clienteId,
    'periodo' => $currentPeriod
  ]);
  foreach ($stmt->fetchAll() as $row) {
    $usageByService[(int)$row['servizio_id']] = (int)$row['documenti_usati'];
  }
  */

  // Recupera fatture recenti
  $whereFatture = "$colCliente = :cliente_id";
  if ($mostraSoloInviate) {
    $whereFatture .= " AND stato IN ('inviata', 'pagata', 'scaduta', 'annullata')";
  }

  $stmt = $pdo->prepare("
    SELECT id, numero_fattura, data_emissione, data_scadenza, importo_totale AS totale, stato
    FROM fatture
    WHERE $whereFatture
    ORDER BY data_emissione DESC
    LIMIT 5
  ");
  $stmt->execute(['cliente_id' => $clienteId]);
  $fattureRecenti = $stmt->fetchAll();

  // Conta fatture da pagare
  $stmt = $pdo->prepare("
    SELECT COUNT(*) AS totale, SUM(importo_totale) AS importo
    FROM fatture
    WHERE $colCliente = :cliente_id AND stato IN ('inviata', 'scaduta')
  ");
  $stmt->execute(['cliente_id' => $clienteId]);
  $fattureDaPagare = $stmt->fetch();

  $stmt = $pdo->prepare('
    SELECT t.id, t.oggetto, t.priorita, t.stato, t.ultimo_messaggio_at, t.updated_at,
           m.messaggio AS ultimo_messaggio
    FROM support_tickets t
    LEFT JOIN support_ticket_messaggi m
      ON m.id = (
        SELECT id
        FROM support_ticket_messaggi
        WHERE ticket_id = t.id
        ORDER BY created_at DESC
        LIMIT 1
      )
    WHERE t.cliente_id = :cliente_id
    ORDER BY COALESCE(t.ultimo_messaggio_at, t.updated_at) DESC
    LIMIT 5
  ');
  $stmt->execute(['cliente_id' => $clienteId]);
  $ticketRecenti = $stmt->fetchAll();

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

  <section class="card">
    <div class="card-header">
      <h3>🚀 I tuoi servizi attivi</h3>
      <a href="/area-clienti/servizi.php" class="btn ghost small">Vedi tutti</a>
    </div>

    <?php if (empty($serviziAttivi)): ?>
      <p class="muted">Nessun servizio attivo al momento.</p>
    <?php else: ?>
      <div class="services-list">
        <?php foreach ($serviziAttivi as $servizio): ?>
          <a href="/area-clienti/servizio-dettaglio.php?id=<?= $servizio['id'] ?>" class="service-item">
            <div class="service-info">
              <h4><?= htmlspecialchars($servizio['nome']) ?></h4>
              <p class="muted small"><?= htmlspecialchars($servizio['descrizione']) ?></p>
              <span class="badge success">Attivo dal <?= date('d/m/Y', strtotime($servizio['data_attivazione'])) ?></span>
              <?php
              $costoPagina = (float)$servizio['costo_per_pagina_finale'];
              $docsUsati = $usageByService[(int)$servizio['id']] ?? 0;
              $costoVariabile = $docsUsati * $costoPagina;
              ?>
              <?php if ($costoPagina > 0): ?>
                <div class="muted small" style="margin-top: 6px;">
                  Costo pagina: €<?= number_format($costoPagina, 4, ',', '.') ?>
                  • Consumo mese: <?= (int)$docsUsati ?> doc
                  • Variabile stimata: €<?= number_format($costoVariabile, 2, ',', '.') ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="service-price">
              <span class="price">€<?= number_format($servizio['prezzo_finale'], 2, ',', '.') ?></span>
              <span class="muted small">/mese</span>
              <?php if ($servizio['prezzo_personalizzato'] !== null): ?>
                <span class="badge" style="margin-left: 6px;">Personalizzato</span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Sezione Fatture -->
  <?php if (!empty($fattureRecenti)): ?>
  <section class="card" style="margin-top: 30px;">
    <div class="card-header">
      <h3>📊 Le tue Fatture</h3>
      <a href="/area-clienti/fatture.php" class="btn ghost small">Vedi tutte</a>
    </div>
    <?php if ($fattureDaPagare['totale'] > 0): ?>
      <div class="alert" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; border-left-color: #f59e0b; margin-bottom: 24px;">
        ⚠️ <strong><?= $fattureDaPagare['totale'] ?> fatture da pagare</strong> - Totale: €<?= number_format($fattureDaPagare['importo'], 2, ',', '.') ?>
      </div>
    <?php endif; ?>

    <div class="services-list">
      <?php foreach ($fattureRecenti as $fattura): ?>
        <?php
        $statoClass = match($fattura['stato']) {
          'pagata' => 'success',
          'scaduta' => 'danger',
          'inviata' => 'info',
          default => 'default'
        };
        $statoLabel = match($fattura['stato']) {
          'bozza' => 'Bozza',
          'emessa' => 'Emessa',
          'inviata' => 'Inviata',
          'pagata' => 'Pagata',
          'scaduta' => 'Scaduta',
          'annullata' => 'Annullata',
          default => $fattura['stato']
        };
        ?>
        <div class="service-item clickable" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 10px;" onclick="console.log('Click fattura ID: <?= $fattura['id'] ?>'); window.location.href='/area-clienti/fattura-dettaglio.php?id=<?= $fattura['id'] ?>'">
          <div class="service-info">
            <h4 style="margin: 0 0 5px 0;">Fattura <?= htmlspecialchars($fattura['numero_fattura']) ?></h4>
            <p class="muted small" style="margin: 0;">
              Emessa il <?= date('d/m/Y', strtotime($fattura['data_emissione'])) ?> -
              Scadenza: <?= date('d/m/Y', strtotime($fattura['data_scadenza'])) ?>
            </p>
            <span class="badge <?= $statoClass ?>" style="margin-top: 8px; display: inline-block;">
              <?= $statoLabel ?>
            </span>
          </div>
          <div style="display: flex; align-items: center; gap: 15px;">
            <div class="service-price">
              <span class="price">€<?= number_format($fattura['totale'], 2, ',', '.') ?></span>
            </div>
            <a href="/area-clienti/api/download-fattura.php?id=<?= $fattura['id'] ?>" class="btn small" style="background: #6366f1; color: white; border-color: #6366f1; text-decoration: none; padding: 8px 16px; border-radius: 6px;" onclick="event.stopPropagation();" title="Scarica PDF">
              📥 PDF
            </a>
            <?php if (in_array($fattura['stato'], ['emessa', 'inviata', 'scaduta'], true)): ?>
              <?php if ($gatewayAttivo): ?>
                <a href="/area-clienti/paga-fattura.php?id=<?= $fattura['id'] ?>" class="btn small" style="background: #10b981; color: white; border-color: #10b981; text-decoration: none; padding: 8px 16px; border-radius: 6px;" onclick="event.stopPropagation();">
                  💳 Paga Ora
                </a>
              <?php else: ?>
                <button class="btn small" type="button" style="background: #10b981; color: white; border-color: #10b981; text-decoration: none; padding: 8px 16px; border-radius: 6px; opacity: 0.5; cursor: not-allowed;" title="Gateway pagamento non configurato" disabled onclick="event.stopPropagation();">
                  💳 Paga Ora
                </button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="card" style="margin-top: 30px;">
    <div class="card-header">
      <h3>🎟️ Supporto e Ticket</h3>
      <a class="btn ghost small" href="/area-clienti/ticket.php">📁 Archivio Ticket</a>
    </div>

    <?php if ($ticketSuccess): ?>
      <div class="alert success"><?= htmlspecialchars($ticketSuccess) ?></div>
    <?php endif; ?>
    <?php if ($ticketError): ?>
      <div class="alert error"><?= htmlspecialchars($ticketError) ?></div>
    <?php endif; ?>

    <div class="ticket-grid">
      <div>
        <h4 style="margin-top: 0;">Ticket recenti</h4>
        <?php if (empty($ticketRecenti)): ?>
          <p class="muted">Non ci sono ticket aperti o chiusi.</p>
        <?php else: ?>
          <div class="services-list">
            <?php foreach ($ticketRecenti as $ticket): ?>
              <?php
              $statusInfo = $ticketStatusMap[$ticket['stato']] ?? [$ticket['stato'], 'neutral'];
              $statusLabel = $statusInfo[0];
              $statusClass = $statusInfo[1];
              $priorityLabel = $ticketPriorityMap[$ticket['priorita']] ?? $ticket['priorita'];
              $ultimoMessaggio = trim($ticket['ultimo_messaggio'] ?? '');
              if ($ultimoMessaggio === '') {
                $ultimoMessaggio = 'Nessun messaggio';
              } elseif (strlen($ultimoMessaggio) > 140) {
                $ultimoMessaggio = substr($ultimoMessaggio, 0, 140) . '...';
              }
              $lastUpdate = $ticket['ultimo_messaggio_at'] ?? $ticket['updated_at'];
              ?>
              <a class="ticket-item" href="/area-clienti/ticket.php?id=<?= (int)$ticket['id'] ?>">
                <div>
                  <p class="ticket-title"><?= htmlspecialchars($ticket['oggetto']) ?></p>
                  <div class="ticket-meta">
                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                    <span class="muted small">Aggiornato: <?= date('d/m/Y H:i', strtotime($lastUpdate)) ?></span>
                  </div>
                  <p class="muted small" style="margin: 8px 0 0 0;">Ultimo messaggio: <?= htmlspecialchars($ultimoMessaggio) ?></p>
                </div>
                <div class="muted small">Priorita: <?= htmlspecialchars($priorityLabel) ?></div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div>
        <h4 style="margin-top: 0;">Apri un nuovo ticket</h4>
        <form method="post" class="form-grid">
          <input type="hidden" name="action" value="create_ticket">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <label>
            Oggetto
            <input type="text" name="oggetto" maxlength="200" required>
          </label>
          <label>
            Priorita
            <select name="priorita">
              <option value="normale" selected>Normale</option>
              <option value="urgente">Urgente</option>
            </select>
          </label>
          <label>
            Messaggio
            <textarea name="messaggio" rows="5" maxlength="4000" required></textarea>
          </label>
          <button class="btn primary" type="submit">Invia Ticket</button>
        </form>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Script per animazione rete neurale -->
<script src="/area-clienti/js/neural-network.js"></script>
</body>
</html>
