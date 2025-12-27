<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];

// Verifica accesso a Document Intelligence
$stmt = $pdo->prepare('
  SELECT s.*, us.data_attivazione, us.stato
  FROM servizi s
  JOIN utenti_servizi us ON s.id = us.servizio_id
  WHERE s.codice = "DOC-INT" AND us.user_id = :user_id AND us.stato = "attivo"
  LIMIT 1
');
$stmt->execute(['user_id' => $clienteId]);
$servizio = $stmt->fetch();

if (!$servizio) {
  header('Location: /area-clienti/servizi.php');
  exit;
}

// Recupera modelli addestrati dell'utente
$stmt = $pdo->prepare('
  SELECT id, nome_modello, tipo_modello, versione, accuratezza,
         num_documenti_addestramento, attivo, created_at, last_used
  FROM modelli_addestrati
  WHERE user_id = :user_id
  ORDER BY created_at DESC
');
$stmt->execute(['user_id' => $clienteId]);
$modelliAddestrati = $stmt->fetchAll();

// Recupera richieste in corso
$stmt = $pdo->prepare('
  SELECT id, tipo_modello, descrizione, num_documenti_stimati, stato, created_at
  FROM richieste_addestramento
  WHERE user_id = :user_id AND stato IN ("in_attesa", "in_lavorazione")
  ORDER BY created_at DESC
');
$stmt->execute(['user_id' => $clienteId]);
$richiesteInCorso = $stmt->fetchAll();
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Modelli AI Addestrati - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .model-card {
      padding: 20px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      transition: all 0.3s ease;
    }
    .model-card:hover {
      border-color: var(--accent1);
      box-shadow: 0 4px 12px rgba(34, 211, 238, 0.1);
    }
    .model-info {
      flex: 1;
    }
    .model-stats {
      display: flex;
      gap: 20px;
      margin-top: 12px;
    }
    .model-stat {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .model-stat .label {
      font-size: 12px;
      color: var(--muted);
    }
    .model-stat .value {
      font-size: 16px;
      font-weight: 600;
    }
    .model-actions {
      display: flex;
      gap: 8px;
      flex-direction: column;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <div style="margin-bottom: 20px;">
    <a href="/area-clienti/servizio-dettaglio.php?id=1" style="color: var(--accent1);">← Torna a Document Intelligence</a>
  </div>

  <section class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
      <div>
        <h1 style="margin: 0 0 8px 0;">🤖 Modelli AI Addestrati</h1>
        <p class="muted">I tuoi modelli personalizzati per Document Intelligence</p>
      </div>
      <a href="/area-clienti/richiedi-addestramento.php" class="btn primary">
        ➕ Richiedi Addestramento
      </a>
    </div>

    <?php if (!empty($richiesteInCorso)): ?>
      <div style="padding: 16px; background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; margin-bottom: 30px;">
        <h3 style="margin: 0 0 12px 0; color: #fbbf24;">⏳ Richieste in Corso</h3>
        <?php foreach ($richiesteInCorso as $richiesta): ?>
          <div style="padding: 12px; background: #0f172a; border-radius: 8px; margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong><?= htmlspecialchars($richiesta['tipo_modello']) ?></strong>
                <span class="badge" style="margin-left: 8px; background: <?= $richiesta['stato'] === 'in_attesa' ? '#fbbf24' : '#3b82f6' ?>;">
                  <?= $richiesta['stato'] === 'in_attesa' ? 'In Attesa' : 'In Lavorazione' ?>
                </span>
              </div>
              <span class="muted small">Richiesto il <?= date('d/m/Y', strtotime($richiesta['created_at'])) ?></span>
            </div>
            <p class="muted small" style="margin: 8px 0 0 0;"><?= htmlspecialchars($richiesta['descrizione']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($modelliAddestrati)): ?>
      <div style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 64px; margin-bottom: 20px;">🤖</div>
        <h3>Nessun modello addestrato</h3>
        <p class="muted" style="margin: 12px 0 24px 0;">
          Crea modelli personalizzati per automatizzare la lettura dei tuoi documenti specifici
        </p>
        <a href="/area-clienti/richiedi-addestramento.php" class="btn primary">
          Richiedi Primo Addestramento
        </a>
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($modelliAddestrati as $modello): ?>
          <div class="model-card">
            <div class="model-info">
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <h3 style="margin: 0;"><?= htmlspecialchars($modello['nome_modello']) ?></h3>
                <?php if ($modello['attivo']): ?>
                  <span class="badge success">✓ Attivo</span>
                <?php else: ?>
                  <span class="badge" style="background: #6b7280;">Disattivato</span>
                <?php endif; ?>
                <span class="badge" style="background: #3b82f6;">v<?= htmlspecialchars($modello['versione']) ?></span>
              </div>

              <p class="muted small" style="margin-bottom: 12px;">
                Tipo: <strong><?= htmlspecialchars($modello['tipo_modello']) ?></strong>
              </p>

              <div class="model-stats">
                <div class="model-stat">
                  <span class="label">Accuratezza</span>
                  <span class="value" style="color: <?= $modello['accuratezza'] >= 95 ? '#10b981' : ($modello['accuratezza'] >= 90 ? '#fbbf24' : '#ef4444') ?>;">
                    <?= number_format($modello['accuratezza'], 1) ?>%
                  </span>
                </div>

                <div class="model-stat">
                  <span class="label">Documenti Addestramento</span>
                  <span class="value"><?= number_format($modello['num_documenti_addestramento'], 0, ',', '.') ?> doc</span>
                </div>

                <div class="model-stat">
                  <span class="label">Creato il</span>
                  <span class="value"><?= date('d/m/Y', strtotime($modello['created_at'])) ?></span>
                </div>

                <?php if ($modello['last_used']): ?>
                  <div class="model-stat">
                    <span class="label">Ultimo Uso</span>
                    <span class="value"><?= date('d/m/Y', strtotime($modello['last_used'])) ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="model-actions">
              <a href="https://app.finch-ai.it/document-intelligence?model=<?= $modello['id'] ?>"
                 class="btn primary"
                 target="_blank"
                 rel="noopener">
                Usa Modello
              </a>
              <button class="btn ghost small" onclick="viewModelDetails(<?= $modello['id'] ?>)">
                Dettagli
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Info Section -->
  <section class="card">
    <h3>ℹ️ Come Funziona l'Addestramento</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">

      <div style="padding: 20px; background: #0f172a; border-radius: 12px; border-left: 4px solid var(--accent1);">
        <h4 style="margin: 0 0 8px 0;">1️⃣ Prepara i Documenti</h4>
        <p class="muted small" style="margin: 0;">
          Raccogli almeno 30-50 documenti rappresentativi del tipo che vuoi automatizzare
        </p>
      </div>

      <div style="padding: 20px; background: #0f172a; border-radius: 12px; border-left: 4px solid var(--accent2);">
        <h4 style="margin: 0 0 8px 0;">2️⃣ Carica e Descrivi</h4>
        <p class="muted small" style="margin: 0;">
          Carica i file e specifica quali dati vuoi estrarre automaticamente
        </p>
      </div>

      <div style="padding: 20px; background: #0f172a; border-radius: 12px; border-left: 4px solid #10b981;">
        <h4 style="margin: 0 0 8px 0;">3️⃣ Addestramento AI</h4>
        <p class="muted small" style="margin: 0;">
          Il nostro team addestra il modello sui tuoi documenti (2-5 giorni lavorativi)
        </p>
      </div>

      <div style="padding: 20px; background: #0f172a; border-radius: 12px; border-left: 4px solid #8b5cf6;">
        <h4 style="margin: 0 0 8px 0;">4️⃣ Usa il Modello</h4>
        <p class="muted small" style="margin: 0;">
          Il modello sarà disponibile qui e su app.finch-ai.it per processare i tuoi documenti
        </p>
      </div>

    </div>
  </section>

</main>

<script>
function viewModelDetails(modelId) {
  // Placeholder per modale dettagli modello
  alert('Dettagli modello ID: ' + modelId + '\n\nFunzionalità in arrivo: visualizzazione metriche dettagliate, cronologia utilizzo, e opzioni di riaddestramento.');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
