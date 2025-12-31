<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

$clienteId = $_SESSION['cliente_id'];

// Verifica admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    header('Location: /area-clienti/denied.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');
$csrfToken = $_SESSION['csrf_token'] ?? '';

$stmt = $pdo->prepare('
    SELECT id, nome_azienda, referente, email, valore_previsto, stato, note, updated_at
    FROM pipeline_trattative
    ORDER BY updated_at DESC
');
$stmt->execute();
$trattative = $stmt->fetchAll();

$columns = [
    'proposta' => 'Da fare proposta',
    'negoziazione' => 'Negoziazione',
    'vinto' => 'Chiuso vinto',
    'perso' => 'Chiuso perso'
];

$grouped = [
    'proposta' => [],
    'negoziazione' => [],
    'vinto' => [],
    'perso' => []
];

foreach ($trattative as $t) {
    $grouped[$t['stato']][] = $t;
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Pipeline Vendite - Admin</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid var(--border);
    }
    .admin-nav {
      display: flex;
      gap: 12px;
    }
    .admin-nav a {
      padding: 8px 16px;
      background: rgba(139, 92, 246, 0.1);
      border: 1px solid rgba(139, 92, 246, 0.3);
      border-radius: 8px;
      color: #a78bfa;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.2s;
    }
    .admin-nav a:hover {
      background: rgba(139, 92, 246, 0.2);
      border-color: #8b5cf6;
    }
    .admin-nav a.active {
      background: #8b5cf6;
      color: white;
      border-color: #8b5cf6;
    }
    .pipeline-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 16px;
    }
    .pipeline-col {
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 12px;
      min-height: 300px;
    }
    .pipeline-col h3 {
      margin: 0 0 12px 0;
      font-size: 16px;
    }
    .deal-card {
      background: rgba(255,255,255,0.03);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 10px;
    }
    .deal-title {
      font-weight: 600;
      margin-bottom: 6px;
    }
    .deal-meta {
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 8px;
    }
    .deal-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center;
    }
    .deal-actions select {
      padding: 6px 8px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 12px;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .modal.show {
      display: flex;
    }
    .modal-content {
      background: #1e293b;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      max-width: 520px;
      width: 90%;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .close-modal {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 24px;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
    }
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
    }
    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-size: 12px;
      font-weight: 600;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 16px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <div class="admin-header">
    <div>
      <h1 style="margin: 0 0 8px 0;">📈 Pipeline Vendite</h1>
      <p class="muted">Pannello amministratore</p>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div class="admin-nav">
        <a href="/area-clienti/admin/gestione-servizi.php">Servizi Clienti</a>
        <a href="/area-clienti/admin/richieste-addestramento.php">Richieste Addestramento</a>
        <a href="/area-clienti/admin/pipeline.php" class="active">Pipeline Vendite</a>
        <a href="/area-clienti/admin/preventivi.php">Preventivi</a>
        <a href="/area-clienti/admin/ticket.php">Ticket</a>
      </div>
      <button class="btn primary small" onclick="apriModal()">+ Nuova Trattativa</button>
      <span class="badge" style="background: #8b5cf6;">Admin</span>
    </div>
  </div>

  <div class="pipeline-grid">
    <?php foreach ($columns as $key => $label): ?>
      <div class="pipeline-col">
        <h3><?= htmlspecialchars($label) ?> (<?= count($grouped[$key]) ?>)</h3>
        <?php if (empty($grouped[$key])): ?>
          <p class="muted small">Nessuna trattativa</p>
        <?php else: ?>
          <?php foreach ($grouped[$key] as $t): ?>
            <div class="deal-card" data-id="<?= $t['id'] ?>">
              <div class="deal-title"><?= htmlspecialchars($t['nome_azienda']) ?></div>
              <div class="deal-meta">
                <?= htmlspecialchars($t['referente'] ?: 'N/D') ?> ·
                <?= htmlspecialchars($t['email'] ?: 'N/D') ?>
              </div>
              <div class="deal-meta">
                Valore previsto: €<?= number_format((float)$t['valore_previsto'], 0, ',', '.') ?> ·
                Aggiornato: <?= date('d/m/Y', strtotime($t['updated_at'])) ?>
              </div>
              <?php if (!empty($t['note'])): ?>
                <div class="muted small" style="margin-bottom: 8px;">
                  <?= nl2br(htmlspecialchars($t['note'])) ?>
                </div>
              <?php endif; ?>
              <div class="deal-actions">
                <select onchange="aggiornaStato(<?= $t['id'] ?>, this.value)">
                  <?php foreach ($columns as $status => $labelStatus): ?>
                    <option value="<?= $status ?>" <?= $t['stato'] === $status ? 'selected' : '' ?>>
                      <?= htmlspecialchars($labelStatus) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button class="btn ghost small" onclick="eliminaTrattativa(<?= $t['id'] ?>)">Elimina</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<div id="modalTrattativa" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Nuova trattativa</h3>
      <button class="close-modal" onclick="chiudiModal()">×</button>
    </div>
    <form id="formTrattativa">
      <div class="form-grid">
        <div class="form-group">
          <label>Azienda</label>
          <input name="nome_azienda" required>
        </div>
        <div class="form-group">
          <label>Referente</label>
          <input name="referente">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input name="email" type="email">
        </div>
        <div class="form-group">
          <label>Valore previsto (€)</label>
          <input name="valore_previsto" type="number" step="0.01" min="0">
        </div>
        <div class="form-group">
          <label>Stato</label>
          <select name="stato">
            <option value="proposta">Da fare proposta</option>
            <option value="negoziazione">Negoziazione</option>
            <option value="vinto">Chiuso vinto</option>
            <option value="perso">Chiuso perso</option>
          </select>
        </div>
      </div>
      <div class="form-group" style="margin-top: 12px;">
        <label>Note</label>
        <textarea name="note" rows="3"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiModal()">Annulla</button>
        <button type="submit" class="btn primary">Salva</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';
const modal = document.getElementById('modalTrattativa');

function apriModal() {
  modal.classList.add('show');
}

function chiudiModal() {
  modal.classList.remove('show');
}

modal.addEventListener('click', function(e) {
  if (e.target === modal) {
    chiudiModal();
  }
});

async function postJson(payload) {
  const response = await fetch('/area-clienti/api/pipeline.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify(payload)
  });
  const result = await response.json();
  if (!response.ok || !result.success) {
    throw new Error(result.error || 'Operazione non riuscita');
  }
  return result;
}

document.getElementById('formTrattativa').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const payload = Object.fromEntries(formData.entries());
  payload.action = 'create';
  try {
    await postJson(payload);
    location.reload();
  } catch (err) {
    alert('❌ ' + err.message);
  }
});

async function aggiornaStato(id, stato) {
  try {
    await postJson({ action: 'update', id, stato });
  } catch (err) {
    alert('❌ ' + err.message);
  }
}

async function eliminaTrattativa(id) {
  if (!confirm('Eliminare questa trattativa?')) return;
  try {
    await postJson({ action: 'delete', id });
    location.reload();
  } catch (err) {
    alert('❌ ' + err.message);
  }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
