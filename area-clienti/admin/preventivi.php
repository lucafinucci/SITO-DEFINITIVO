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
    SELECT id, nome_azienda, referente, email, stato, scadenza, subtotale, totale, created_at
    FROM preventivi
    ORDER BY created_at DESC
');
$stmt->execute();
$preventivi = $stmt->fetchAll();
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Preventivi - Admin</title>
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
    .table {
      width: 100%;
      border-collapse: collapse;
    }
    .table th, .table td {
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      text-align: left;
      font-size: 14px;
    }
    .table th {
      font-size: 12px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .table td.num {
      text-align: right;
      white-space: nowrap;
    }
    .actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
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
    .modal.show { display: flex; }
    .modal-content {
      background: #1e293b;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      max-width: 720px;
      width: 95%;
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
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
    }
    .items-table th, .items-table td {
      padding: 8px 10px;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
    }
    .items-table th { color: var(--muted); }
    .items-table input {
      width: 100%;
      padding: 6px 8px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 13px;
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
      <h1 style="margin: 0 0 8px 0;">🧾 Preventivi</h1>
      <p class="muted">Pannello amministratore</p>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div class="admin-nav">
        <a href="/area-clienti/admin/gestione-servizi.php">Servizi Clienti</a>
        <a href="/area-clienti/admin/richieste-addestramento.php">Richieste Addestramento</a>
        <a href="/area-clienti/admin/pipeline.php">Pipeline Vendite</a>
        <a href="/area-clienti/admin/preventivi.php" class="active">Preventivi</a>
        <a href="/area-clienti/admin/ticket.php">Ticket</a>
      </div>
      <button class="btn primary small" onclick="apriModal()">+ Nuovo preventivo</button>
      <span class="badge" style="background: #8b5cf6;">Admin</span>
    </div>
  </div>

  <section class="card">
    <h3>Elenco preventivi</h3>
    <?php if (empty($preventivi)): ?>
      <p class="muted" style="text-align: center; padding: 30px 0;">Nessun preventivo</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Azienda</th>
            <th>Referente</th>
            <th>Stato</th>
            <th>Scadenza</th>
            <th class="num">Totale</th>
            <th>Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($preventivi as $p): ?>
            <tr>
              <td>#<?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['nome_azienda']) ?></td>
              <td><?= htmlspecialchars($p['referente'] ?: 'N/D') ?></td>
              <td>
                <select onchange="aggiornaStato(<?= $p['id'] ?>, this.value)">
                  <?php foreach (['bozza' => 'Bozza', 'inviato' => 'Inviato', 'accettato' => 'Accettato'] as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $p['stato'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><?= $p['scadenza'] ?: 'N/D' ?></td>
              <td class="num">€<?= number_format((float)$p['totale'], 0, ',', '.') ?></td>
              <td>
                <div class="actions">
                  <button class="btn ghost small" onclick="modificaPreventivo(<?= $p['id'] ?>)">Modifica</button>
                  <a class="btn ghost small" href="/area-clienti/api/genera-pdf-preventivo.php?id=<?= $p['id'] ?>" target="_blank">PDF</a>
                  <button class="btn ghost small" onclick="inviaEmail(<?= $p['id'] ?>)">Invia</button>
                  <button class="btn ghost small" onclick="eliminaPreventivo(<?= $p['id'] ?>)">Elimina</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</main>

<div id="modalPreventivo" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Nuovo preventivo</h3>
      <button class="close-modal" onclick="chiudiModal()">×</button>
    </div>
    <form id="formPreventivo">
      <input type="hidden" name="id" id="preventivoId">
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
          <label>Scadenza</label>
          <input name="scadenza" type="date">
        </div>
        <div class="form-group">
          <label>Sconto (%)</label>
          <input name="sconto_percentuale" type="number" step="0.01" min="0" max="100" value="0">
        </div>
        <div class="form-group">
          <label>Stato</label>
          <select name="stato">
            <option value="bozza">Bozza</option>
            <option value="inviato">Inviato</option>
            <option value="accettato">Accettato</option>
          </select>
        </div>
      </div>
      <div class="form-group" style="margin-top: 12px;">
        <label>Note</label>
        <textarea name="note" rows="3"></textarea>
      </div>

      <table class="items-table" id="itemsTable">
        <thead>
          <tr>
            <th>Servizio</th>
            <th>Qta</th>
            <th>Prezzo</th>
            <th></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <button type="button" class="btn ghost small" onclick="aggiungiRiga()">+ Aggiungi voce</button>

      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiModal()">Annulla</button>
        <button type="submit" class="btn primary">Salva</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';
const modal = document.getElementById('modalPreventivo');
const itemsBody = document.querySelector('#itemsTable tbody');

function apriModal() {
  modal.classList.add('show');
  document.getElementById('formPreventivo').reset();
  document.getElementById('preventivoId').value = '';
  itemsBody.innerHTML = '';
  if (!itemsBody.children.length) {
    aggiungiRiga();
  }
}

function chiudiModal() {
  modal.classList.remove('show');
}

modal.addEventListener('click', function(e) {
  if (e.target === modal) {
    chiudiModal();
  }
});

function aggiungiRiga() {
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><input name="descrizione" placeholder="Servizio"></td>
    <td><input name="quantita" type="number" step="0.01" min="0.01" value="1"></td>
    <td><input name="prezzo" type="number" step="0.01" min="0" value="0"></td>
    <td><button type="button" class="btn ghost small">Rimuovi</button></td>
  `;
  row.querySelector('button').addEventListener('click', () => row.remove());
  itemsBody.appendChild(row);
}

async function postJson(payload) {
  const response = await fetch('/area-clienti/api/preventivi.php', {
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

document.getElementById('formPreventivo').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const payload = Object.fromEntries(formData.entries());
  const voci = [];
  itemsBody.querySelectorAll('tr').forEach((row) => {
    voci.push({
      descrizione: row.querySelector('input[name="descrizione"]').value,
      quantita: row.querySelector('input[name="quantita"]').value,
      prezzo_unitario: row.querySelector('input[name="prezzo"]').value
    });
  });
  payload.voci = voci;
  payload.action = payload.id ? 'update' : 'create';
  try {
    await postJson(payload);
    location.reload();
  } catch (err) {
    alert('❌ ' + err.message);
  }
});

async function aggiornaStato(id, stato) {
  try {
    await postJson({ action: 'update-status', id, stato });
  } catch (err) {
    alert('❌ ' + err.message);
  }
}

async function eliminaPreventivo(id) {
  if (!confirm('Eliminare questo preventivo?')) return;
  try {
    await postJson({ action: 'delete', id });
    location.reload();
  } catch (err) {
    alert('❌ ' + err.message);
  }
}

async function inviaEmail(id) {
  try {
    const response = await fetch('/area-clienti/api/preventivo-email.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ id, csrf_token: csrfToken })
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.error || 'Invio fallito');
    }
    alert('✅ Email inviata');
    location.reload();
  } catch (err) {
    alert('❌ ' + err.message);
  }
}

async function modificaPreventivo(id) {
  try {
    const response = await fetch(`/area-clienti/api/preventivo-get.php?id=${id}`);
    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.error || 'Impossibile caricare il preventivo');
    }

    const p = result.preventivo;
    document.getElementById('preventivoId').value = p.id;
    document.querySelector('input[name="nome_azienda"]').value = p.nome_azienda || '';
    document.querySelector('input[name="referente"]').value = p.referente || '';
    document.querySelector('input[name="email"]').value = p.email || '';
    document.querySelector('input[name="scadenza"]').value = p.scadenza || '';
    document.querySelector('input[name="sconto_percentuale"]').value = p.sconto_percentuale || 0;
    document.querySelector('select[name="stato"]').value = p.stato || 'bozza';
    document.querySelector('textarea[name="note"]').value = p.note || '';

    itemsBody.innerHTML = '';
    result.voci.forEach((row) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input name="descrizione" value="${row.descrizione || ''}"></td>
        <td><input name="quantita" type="number" step="0.01" min="0.01" value="${row.quantita || 1}"></td>
        <td><input name="prezzo" type="number" step="0.01" min="0" value="${row.prezzo_unitario || 0}"></td>
        <td><button type="button" class="btn ghost small">Rimuovi</button></td>
      `;
      tr.querySelector('button').addEventListener('click', () => tr.remove());
      itemsBody.appendChild(tr);
    });
    if (!itemsBody.children.length) {
      aggiungiRiga();
    }
    modal.classList.add('show');
  } catch (err) {
    alert('❌ ' + err.message);
  }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
