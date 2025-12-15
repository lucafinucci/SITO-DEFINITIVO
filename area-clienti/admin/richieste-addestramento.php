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

// Recupera tutte le richieste
$stmt = $pdo->prepare('
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
    GROUP BY r.id
    ORDER BY r.created_at DESC
');
$stmt->execute();
$richieste = $stmt->fetchAll();
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
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
      <h1 style="margin: 0 0 8px 0;">🛠️ Gestione Richieste Addestramento</h1>
      <p class="muted">Pannello amministratore</p>
    </div>
    <span class="badge" style="background: #8b5cf6;">Admin</span>
  </div>

  <!-- Statistiche -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
    <?php
    $stats = [
      'in_attesa' => 0,
      'in_lavorazione' => 0,
      'completato' => 0,
      'annullato' => 0
    ];
    foreach ($richieste as $r) {
      $stats[$r['stato']]++;
    }
    ?>
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
      <h2 style="margin: 0;"><?= count($richieste) ?></h2>
    </div>
  </div>

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

          <?php
          // Recupera file della richiesta
          $stmt = $pdo->prepare('
            SELECT id, filename_originale, file_size
            FROM richieste_addestramento_files
            WHERE richiesta_id = :richiesta_id
            ORDER BY uploaded_at ASC
          ');
          $stmt->execute(['richiesta_id' => $richiesta['id']]);
          $files = $stmt->fetchAll();
          ?>

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

            <button class="btn ghost small" onclick="inviaEmail(<?= $richiesta['id'] ?>, '<?= htmlspecialchars($richiesta['email']) ?>')">
              ✉️ Invia Email
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

</main>

<script>
function aggiornaStato(richiestaId, nuovoStato) {
  if (!confirm(`Cambiare stato in "${nuovoStato}"?`)) return;

  // TODO: Implementare API per aggiornare stato
  alert(`Funzionalità in arrivo: aggiornamento stato richiesta ${richiestaId} a "${nuovoStato}"`);

  // Esempio implementazione:
  // fetch('/area-clienti/api/update-richiesta.php', {
  //   method: 'POST',
  //   headers: { 'Content-Type': 'application/json' },
  //   body: JSON.stringify({ richiesta_id: richiestaId, stato: nuovoStato })
  // }).then(() => location.reload());
}

function inviaEmail(richiestaId, email) {
  const messaggio = prompt(`Messaggio da inviare a ${email}:`);
  if (!messaggio) return;

  alert(`Email che verrà inviata:\n\nA: ${email}\nMessaggio: ${messaggio}\n\n(Funzionalità in arrivo)`);

  // TODO: Implementare invio email
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
