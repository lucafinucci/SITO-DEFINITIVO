<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

$clienteId = $_SESSION['cliente_id'];

// Verifica che sia admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    header('Location: /area-clienti/denied.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$csrfToken = $_SESSION['csrf_token'] ?? '';

// Filtri e paginazione
$statiValidi = ['in_attesa', 'in_lavorazione', 'completato', 'annullato'];
$statoFilter = $_GET['stato'] ?? '';
if (!in_array($statoFilter, $statiValidi, true)) {
    $statoFilter = '';
}

$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($_GET['per_page'] ?? 10);
if ($perPage < 5) {
    $perPage = 5;
} elseif ($perPage > 50) {
    $perPage = 50;
}

$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($statoFilter) {
    $where[] = 'r.stato = :stato';
    $params['stato'] = $statoFilter;
}

if ($search !== '') {
    $where[] = '(u.azienda LIKE :search OR u.nome LIKE :search OR u.cognome LIKE :search OR u.email LIKE :search OR r.tipo_modello LIKE :search OR r.id = :search_id)';
    $params['search'] = '%' . $search . '%';
    $params['search_id'] = ctype_digit($search) ? (int)$search : 0;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Totale richieste per paginazione
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT r.id)
    FROM richieste_addestramento r
    JOIN utenti u ON r.user_id = u.id
    $whereSql
");
$stmt->execute($params);
$totaleRichieste = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($totaleRichieste / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Statistiche (rispettano i filtri)
$stats = [
  'in_attesa' => 0,
  'in_lavorazione' => 0,
  'completato' => 0,
  'annullato' => 0
];
$stmt = $pdo->prepare("
    SELECT r.stato, COUNT(*) as totale
    FROM richieste_addestramento r
    JOIN utenti u ON r.user_id = u.id
    $whereSql
    GROUP BY r.stato
");
$stmt->execute($params);
foreach ($stmt->fetchAll() as $row) {
    $stats[$row['stato']] = (int)$row['totale'];
}

// Recupera richieste paginando
$stmt = $pdo->prepare("
    SELECT
        r.id,
        r.tipo_modello,
        r.descrizione,
        r.num_documenti_stimati,
        r.note,
        r.stato,
        r.created_at,
        u.nome,
        u.cognome,
        u.email,
        u.azienda,
        COUNT(f.id) as num_files
    FROM richieste_addestramento r
    JOIN utenti u ON r.user_id = u.id
    LEFT JOIN richieste_addestramento_files f ON r.id = f.richiesta_id
    $whereSql
    GROUP BY r.id
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue(':' . $key, $value, $type);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$richieste = $stmt->fetchAll();

$baseParams = [
    'q' => $search,
    'stato' => $statoFilter,
    'per_page' => $perPage
];


// Recupera file per tutte le richieste in un'unica query
$filesByRichiesta = [];
if (!empty($richieste)) {
    $richiestaIds = array_column($richieste, 'id');
    $placeholders = implode(',', array_fill(0, count($richiestaIds), '?'));
    $stmt = $pdo->prepare("
        SELECT richiesta_id, id, filename_originale, file_size
        FROM richieste_addestramento_files
        WHERE richiesta_id IN ($placeholders)
        ORDER BY uploaded_at ASC
    ");
    $stmt->execute($richiestaIds);
    foreach ($stmt->fetchAll() as $file) {
        $filesByRichiesta[$file['richiesta_id']][] = $file;
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Gestione Richieste Addestramento - Admin</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .richiesta-card {
      padding: 20px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      margin-bottom: 16px;
    }
    .richiesta-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 16px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border);
    }
    .badge-stato {
      padding: 4px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge-in_attesa { background: #fbbf24; color: #000; }
    .badge-in_lavorazione { background: #3b82f6; color: #fff; }
    .badge-completato { background: #10b981; color: #fff; }
    .badge-annullato { background: #6b7280; color: #fff; }
    .file-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: 12px;
    }
    .file-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 12px;
      background: rgba(255,255,255,0.03);
      border-radius: 6px;
      font-size: 14px;
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
    .modal.show {
      display: flex;
    }
    .modal-content {
      background: #1e293b;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 30px;
      max-width: 520px;
      width: 90%;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .modal-header h3 {
      margin: 0;
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
    .close-modal:hover {
      color: white;
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 600;
    }
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .form-group textarea {
      min-height: 120px;
      resize: vertical;
    }
    .modal-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 24px;
    }

    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }
    .filters input,
    .filters select {
      padding: 8px 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .filters input {
      min-width: 260px;
      flex: 1 1 260px;
    }
    .pagination {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-top: 16px;
      flex-wrap: wrap;
    }
    .pagination-links {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }
    .pagination-links .active {
      background: #8b5cf6;
      border-color: #8b5cf6;
      color: white;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid var(--border);">
    <div>
      <h1 style="margin: 0 0 8px 0;">🛠️ Gestione Richieste Addestramento</h1>
      <p class="muted">Pannello amministratore</p>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div style="display: flex; gap: 12px;">
        <a href="/area-clienti/admin/gestione-servizi.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          Servizi Clienti
        </a>
        <a href="/area-clienti/admin/richieste-addestramento.php"
           style="padding: 8px 16px; background: #8b5cf6; border: 1px solid #8b5cf6; border-radius: 8px; color: white; text-decoration: none; font-size: 14px;">
          Richieste Addestramento
        </a>
        <a href="/area-clienti/admin/fatture.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          Fatture
        </a>
        <a href="/area-clienti/admin/scadenzario.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          📅 Scadenzario
        </a>
        <a href="/area-clienti/admin/pipeline.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          Pipeline Vendite
        </a>
        <a href="/area-clienti/admin/preventivi.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          Preventivi
        </a>
      </div>
      <span class="badge" style="background: #8b5cf6;">Admin</span>
    </div>
  </div>

  <!-- Statistiche -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
        <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">In Attesa</p>
      <h2 style="margin: 0; color: #fbbf24;"><?= $stats['in_attesa'] ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">In Lavorazione</p>
      <h2 style="margin: 0; color: #3b82f6;"><?= $stats['in_lavorazione'] ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Completato</p>
      <h2 style="margin: 0; color: #10b981;"><?= $stats['completato'] ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Totale Richieste</p>
      <h2 style="margin: 0;"><?= $totaleRichieste ?></h2>
    </div>
  </div>


  <!-- Filtri -->
  <form method="get" class="filters" style="margin-bottom: 16px;">
    <input type="text" name="q" placeholder="Cerca azienda, email, modello o ID" value="<?= htmlspecialchars($search) ?>">
    <select name="stato">
      <option value="" <?= $statoFilter === '' ? 'selected' : '' ?>>Tutti gli stati</option>
      <option value="in_attesa" <?= $statoFilter === 'in_attesa' ? 'selected' : '' ?>>In attesa</option>
      <option value="in_lavorazione" <?= $statoFilter === 'in_lavorazione' ? 'selected' : '' ?>>In lavorazione</option>
      <option value="completato" <?= $statoFilter === 'completato' ? 'selected' : '' ?>>Completato</option>
      <option value="annullato" <?= $statoFilter === 'annullato' ? 'selected' : '' ?>>Annullato</option>
    </select>
    <select name="per_page">
      <?php foreach ([10, 20, 50] as $size): ?>
        <option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?> / pagina</option>
      <?php endforeach; ?>
    </select>
    <button class="btn ghost small" type="submit">Filtra</button>
    <a class="btn ghost small" href="/area-clienti/admin/richieste-addestramento.php">Reset</a>
  </form>

  <!-- Lista Richieste -->
  <section class="card">
    <h3>📋 Richieste</h3>

    <?php if (empty($richieste)): ?>
      <p class="muted" style="text-align: center; padding: 40px 0;">Nessuna richiesta presente</p>
    <?php else: ?>
      <?php foreach ($richieste as $richiesta): ?>
        <div class="richiesta-card">
          <div class="richiesta-header">
            <div style="flex: 1;">
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <h4 style="margin: 0;">Richiesta #<?= $richiesta['id'] ?></h4>
                <span class="badge-stato badge-<?= $richiesta['stato'] ?>">
                  <?= ucfirst(str_replace('_', ' ', $richiesta['stato'])) ?>
                </span>
              </div>
              <p class="muted small" style="margin: 0;">
                <strong><?= htmlspecialchars($richiesta['azienda']) ?></strong> •
                <?= htmlspecialchars($richiesta['nome'] . ' ' . $richiesta['cognome']) ?> •
                <?= htmlspecialchars($richiesta['email']) ?>
              </p>
            </div>
            <p class="muted small" style="margin: 0;">
              <?= date('d/m/Y H:i', strtotime($richiesta['created_at'])) ?>
            </p>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Tipo Modello</p>
              <p style="margin: 0; font-weight: 600;"><?= htmlspecialchars($richiesta['tipo_modello']) ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Documenti Stimati</p>
              <p style="margin: 0; font-weight: 600;"><?= $richiesta['num_documenti_stimati'] ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">File Caricati</p>
              <p style="margin: 0; font-weight: 600;"><?= $richiesta['num_files'] ?> file</p>
            </div>
          </div>

          <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
            <p class="muted small" style="margin: 0 0 8px 0;">Descrizione:</p>
            <p style="margin: 0;"><?= nl2br(htmlspecialchars($richiesta['descrizione'])) ?></p>

            <?php if ($richiesta['note']): ?>
              <p class="muted small" style="margin: 12px 0 4px 0;">Note:</p>
              <p style="margin: 0; color: var(--muted);"><?= nl2br(htmlspecialchars($richiesta['note'])) ?></p>
            <?php endif; ?>
          </div>

          <?php $files = $filesByRichiesta[$richiesta['id']] ?? []; ?>

          <?php if (!empty($files)): ?>
            <div class="file-list">
              <p class="muted small" style="margin: 12px 0 4px 0;">File caricati:</p>
              <?php foreach ($files as $file): ?>
                <div class="file-item">
                  <span>📄 <?= htmlspecialchars($file['filename_originale']) ?></span>
                  <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="muted small"><?= round($file['file_size'] / 1024, 1) ?> KB</span>
                    <a href="/area-clienti/api/download-training-files.php?file_id=<?= $file['id'] ?>"
                       class="btn ghost small">
                      ⬇️ Download
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="actions" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
            <?php if ($richiesta['num_files'] > 0): ?>
              <a href="/area-clienti/api/download-training-files.php?richiesta_id=<?= $richiesta['id'] ?>"
                 class="btn primary small">
                📦 Scarica Tutti (ZIP)
              </a>
            <?php endif; ?>

            <?php if ($richiesta['stato'] === 'in_attesa'): ?>
              <button class="btn ghost small" onclick="aggiornaStato(<?= $richiesta['id'] ?>, 'in_lavorazione')">
                ▶️ Inizia Lavorazione
              </button>
            <?php endif; ?>

            <?php if ($richiesta['stato'] === 'in_lavorazione'): ?>
              <button class="btn ghost small" onclick="aggiornaStato(<?= $richiesta['id'] ?>, 'completato')">
                ✅ Segna Completato
              </button>
            <?php endif; ?>

            <button class="btn ghost small" onclick="apriEmailModal(<?= $richiesta['id'] ?>, '<?= htmlspecialchars($richiesta['email']) ?>')">
              ✉️ Invia Email
            </button>

            <button class="btn ghost small" onclick="cancellaRichiesta(<?= $richiesta['id'] ?>, '<?= htmlspecialchars($richiesta['azienda']) ?>')" style="color: #ef4444; border-color: #ef4444;">
              🗑️ Cancella
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>


  <!-- Paginazione -->
  <div class="pagination">
    <span class="muted small">Pagina <?= $page ?> di <?= $totalPages ?> / Totale: <?= $totaleRichieste ?></span>
    <div class="pagination-links">
      <?php
      $params = $baseParams;
      if ($page > 1) {
        $params['page'] = $page - 1;
        echo '<a class="btn ghost small" href="/area-clienti/admin/richieste-addestramento.php?' . http_build_query($params) . '">< Prec</a>';
      }
      $startPage = max(1, $page - 2);
      $endPage = min($totalPages, $page + 2);
      for ($p = $startPage; $p <= $endPage; $p++) {
        $params['page'] = $p;
        $active = $p === $page ? ' active' : '';
        echo '<a class="btn ghost small' . $active . '" href="/area-clienti/admin/richieste-addestramento.php?' . http_build_query($params) . '">' . $p . '</a>';
      }
      if ($page < $totalPages) {
        $params['page'] = $page + 1;
        echo '<a class="btn ghost small" href="/area-clienti/admin/richieste-addestramento.php?' . http_build_query($params) . '">Succ ></a>';
      }
      ?>
    </div>
  </div>

</main>

<!-- Modal invio email -->
<div id="modalEmail" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Invia Email al Cliente</h3>
      <button class="close-modal" onclick="chiudiEmailModal()">✕</button>
    </div>

    <form id="formEmail">
      <input type="hidden" id="emailRichiestaId" name="richiesta_id">

      <div class="form-group">
        <label>Destinatario</label>
        <input type="text" id="emailDestinatario" readonly>
      </div>

      <div class="form-group">
        <label>Oggetto</label>
        <input type="text" id="emailOggetto" name="subject" value="Aggiornamento richiesta addestramento">
      </div>

      <div class="form-group">
        <label>Messaggio</label>
        <textarea id="emailMessaggio" name="message" placeholder="Scrivi il messaggio da inviare al cliente..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiEmailModal()">Annulla</button>
        <button type="submit" class="btn primary">✉️ Invia Email</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';
const emailModal = document.getElementById('modalEmail');
function aggiornaStato(richiestaId, nuovoStato) {
  if (!confirm(`Cambiare stato in "${nuovoStato}"?`)) return;

  fetch('/area-clienti/api/update-richiesta.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify({ richiesta_id: richiestaId, stato: nuovoStato })
  })
    .then(async (response) => {
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.error || 'Impossibile aggiornare lo stato');
      }
      alert('✅ Stato aggiornato con successo!');
      window.location.reload();
    })
    .catch((error) => {
      alert('⚠️ Errore: ' + error.message);
    });
}

function apriEmailModal(richiestaId, email) {
  document.getElementById('emailRichiestaId').value = richiestaId;
  document.getElementById('emailDestinatario').value = email;
  document.getElementById('emailMessaggio').value = '';
  emailModal.classList.add('show');
}

function chiudiEmailModal() {
  emailModal.classList.remove('show');
}

async function cancellaRichiesta(richiestaId, azienda) {
  if (!confirm(`Sei sicuro di voler cancellare la richiesta #${richiestaId} di ${azienda}?\n\nQuesta azione è irreversibile e cancellerà anche tutti i file caricati.`)) {
    return;
  }

  try {
    console.log('Cancellazione richiesta:', richiestaId);

    const response = await fetch('/area-clienti/api/cancella-richiesta.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ richiesta_id: richiestaId })
    });

    console.log('Response status:', response.status);
    const result = await response.json();
    console.log('Result:', result);

    if (result.success) {
      alert('✓ Richiesta #' + richiestaId + ' cancellata con successo!');
      // Hard reload per evitare cache
      window.location.href = window.location.href + '?t=' + Date.now();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile cancellare la richiesta'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione: ' + error.message);
  }
}

emailModal.addEventListener('click', function(e) {
  if (e.target === emailModal) {
    chiudiEmailModal();
  }
});

document.getElementById('formEmail').addEventListener('submit', async function(e) {
  e.preventDefault();

  const richiestaId = document.getElementById('emailRichiestaId').value;
  const subject = document.getElementById('emailOggetto').value.trim();
  const message = document.getElementById('emailMessaggio').value.trim();

  if (!message) {
    alert('⚠️ Inserisci un messaggio da inviare.');
    return;
  }

  try {
    const response = await fetch('/area-clienti/api/invia-email.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        richiesta_id: richiestaId,
        subject: subject,
        message: message
      })
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.error || 'Impossibile inviare l'email');
    }

    alert('✅ Email inviata con successo!');
    chiudiEmailModal();
  } catch (error) {
    alert('⚠️ Errore: ' + error.message);
  }
});

</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
