<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];

// Verifica che l'utente abbia accesso a Document Intelligence
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

// Simula KPI (in produzione questi dati verrebbero da API o database)
$kpi = [
  'documenti_processati' => 12847,
  'tempo_medio_lettura' => '2.3 sec',
  'automazione_percentuale' => 94.2,
  'errori_evitati' => 312,
  'tempo_risparmiato' => '427 ore',
  'roi' => '340%',
  'costo_risparmiato_mese' => '€ 8.450,00'
];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Document Intelligence - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <!-- Breadcrumb -->
  <div class="breadcrumb" style="margin-bottom: 20px;">
    <a href="/area-clienti/dashboard.php" style="color: var(--accent1);">Dashboard</a>
    <span style="color: var(--muted); margin: 0 8px;">/</span>
    <a href="/area-clienti/servizi.php" style="color: var(--accent1);">Servizi</a>
    <span style="color: var(--muted); margin: 0 8px;">/</span>
    <span>Document Intelligence</span>
  </div>

  <!-- Header con CTA -->
  <section class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
      <div>
        <span class="badge success">Servizio Attivo</span>
        <h1 style="margin: 8px 0 4px 0; font-size: 32px;">📄 Document Intelligence</h1>
        <p class="muted" style="font-size: 16px;">OCR e validazione documenti automatica con AI</p>
      </div>
      <div>
        <a href="https://app.finch-ai.it/document-intelligence" class="btn primary" target="_blank" rel="noopener" style="font-size: 16px; padding: 14px 24px;">
          🚀 Accedi al Modulo Document Intelligence
        </a>
      </div>
    </div>
  </section>

  <!-- KPI Dedicati -->
  <section class="card">
    <h2>📊 KPI Document Intelligence</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 20px;">

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">Documenti Processati</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
          <?= number_format($kpi['documenti_processati'], 0, ',', '.') ?>
        </h3>
      </div>

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">Tempo Medio di Lettura</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
          <?= $kpi['tempo_medio_lettura'] ?>
        </h3>
      </div>

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">% Documenti senza Intervento Umano</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
          <?= number_format($kpi['automazione_percentuale'], 1, ',', '.') ?>%
        </h3>
      </div>

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">Errori Evitati</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
          <?= number_format($kpi['errori_evitati'], 0, ',', '.') ?>
        </h3>
      </div>

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">Tempo Risparmiato Totale</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
          <?= $kpi['tempo_risparmiato'] ?>
        </h3>
      </div>

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">ROI Document Intelligence</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
          <?= $kpi['roi'] ?>
        </h3>
      </div>

      <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
        <p class="muted small">Costo Risparmiato nel Mese</p>
        <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, #10b981, #22d3ee); -webkit-background-clip: text; color: transparent;">
          <?= $kpi['costo_risparmiato_mese'] ?>
        </h3>
      </div>

    </div>
  </section>

  <!-- Grafici Attività -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">

    <!-- Documenti Processati per Giorno -->
    <section class="card">
      <h3>📈 Documenti Letti per Giorno</h3>
      <p class="muted small">Ultimi 30 giorni</p>
      <canvas id="chart-documenti-giorno" height="200"></canvas>
    </section>

    <!-- Automazione % nel Tempo -->
    <section class="card">
      <h3>🤖 Automazione % nel Tempo</h3>
      <p class="muted small">Trend mensile</p>
      <canvas id="chart-automazione" height="200"></canvas>
    </section>

  </div>

  <!-- Documenti per Tipo -->
  <section class="card">
    <h3>📑 Distribuzione per Tipo di Documento</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">

      <div>
        <canvas id="chart-tipo-documento" height="200"></canvas>
      </div>

      <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php
        $tipiDoc = [
          ['nome' => 'DDT', 'count' => 5234, 'color' => '#22d3ee'],
          ['nome' => 'Fatture', 'count' => 3821, 'color' => '#3b82f6'],
          ['nome' => 'Contratti', 'count' => 2156, 'color' => '#8b5cf6'],
          ['nome' => 'Bolle', 'count' => 1636, 'color' => '#10b981'],
        ];
        foreach ($tipiDoc as $tipo):
        ?>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #0f172a; border-radius: 10px; border-left: 4px solid <?= $tipo['color'] ?>;">
            <span style="font-weight: 600;"><?= $tipo['nome'] ?></span>
            <span style="color: var(--muted);"><?= number_format($tipo['count'], 0, ',', '.') ?> documenti</span>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- Modelli AI Addestrati -->
  <?php
  // Recupera modelli addestrati dell'utente
  $stmt = $pdo->prepare('
    SELECT id, nome_modello, tipo_modello, accuratezza, num_documenti_addestramento, attivo
    FROM modelli_addestrati
    WHERE user_id = :user_id AND attivo = TRUE
    ORDER BY created_at DESC
    LIMIT 3
  ');
  $stmt->execute(['user_id' => $clienteId]);
  $modelliAddestrati = $stmt->fetchAll();
  ?>

  <section class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0;">🤖 Modelli AI Addestrati</h3>
      <a href="/area-clienti/document-intelligence-modelli.php" class="btn ghost small">Vedi tutti</a>
    </div>

    <p class="muted small" style="margin-bottom: 20px;">I tuoi modelli personalizzati per Document Intelligence</p>

    <?php if (empty($modelliAddestrati)): ?>
      <div style="padding: 40px 20px; text-align: center; background: #0f172a; border-radius: 12px; border: 2px dashed var(--border);">
        <div style="font-size: 48px; margin-bottom: 12px;">🎯</div>
        <h4 style="margin: 0 0 8px 0;">Nessun modello addestrato</h4>
        <p class="muted small" style="margin: 0 0 20px 0;">
          Crea modelli personalizzati per i tuoi documenti specifici (fatture, DDT, contratti, ecc.)
        </p>
        <a href="/area-clienti/richiedi-addestramento.php" class="btn primary">
          ➕ Richiedi Addestramento
        </a>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <?php foreach ($modelliAddestrati as $modello): ?>
          <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
              <div>
                <h4 style="margin: 0 0 4px 0; font-size: 16px;"><?= htmlspecialchars($modello['nome_modello']) ?></h4>
                <p class="muted small" style="margin: 0;"><?= htmlspecialchars($modello['tipo_modello']) ?></p>
              </div>
              <span class="badge success">✓ Attivo</span>
            </div>

            <div style="display: flex; gap: 16px; margin-bottom: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
              <div>
                <p class="muted small" style="margin: 0 0 4px 0;">Accuratezza</p>
                <p style="margin: 0; font-size: 18px; font-weight: 600; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
                  <?= number_format($modello['accuratezza'], 1) ?>%
                </p>
              </div>
              <div>
                <p class="muted small" style="margin: 0 0 4px 0;">Documenti</p>
                <p style="margin: 0; font-size: 18px; font-weight: 600;">
                  <?= number_format($modello['num_documenti_addestramento'], 0, ',', '.') ?>
                </p>
              </div>
            </div>

            <a href="https://app.finch-ai.it/document-intelligence?model=<?= $modello['id'] ?>"
               class="btn ghost small"
               target="_blank"
               rel="noopener"
               style="width: 100%;">
              Usa Modello
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="text-align: center;">
        <a href="/area-clienti/richiedi-addestramento.php" class="btn primary">
          ➕ Richiedi Nuovo Addestramento
        </a>
      </div>
    <?php endif; ?>
  </section>

  <!-- Azioni Rapide -->
  <section class="card">
    <h3>⚡ Azioni Rapide</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-top: 16px;">
      <a href="https://app.finch-ai.it/document-intelligence" class="btn primary" target="_blank" rel="noopener">
        Carica Nuovo Documento
      </a>
      <a href="https://app.finch-ai.it/document-intelligence/history" class="btn ghost" target="_blank" rel="noopener">
        Storico Documenti
      </a>
      <a href="https://app.finch-ai.it/document-intelligence/reports" class="btn ghost" target="_blank" rel="noopener">
        Esporta Report
      </a>
      <a href="mailto:supporto@finch-ai.it?subject=Supporto%20Document%20Intelligence" class="btn ghost">
        Richiedi Supporto
      </a>
    </div>
  </section>

</main>

<script>
// Documenti per Giorno
const ctxGiorno = document.getElementById('chart-documenti-giorno').getContext('2d');
new Chart(ctxGiorno, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_map(fn($i) => date('d/m', strtotime("-$i days")), range(29, 0))) ?>,
    datasets: [{
      label: 'Documenti Processati',
      data: [320, 385, 412, 398, 445, 423, 467, 501, 489, 512, 534, 498, 521, 545, 567, 589, 612, 634, 656, 678, 695, 712, 734, 756, 778, 801, 823, 845, 867, 889],
      borderColor: '#22d3ee',
      backgroundColor: 'rgba(34, 211, 238, 0.1)',
      tension: 0.4,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: '#1f2937' },
        ticks: { color: '#9ca3af' }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#9ca3af' }
      }
    }
  }
});

// Automazione %
const ctxAuto = document.getElementById('chart-automazione').getContext('2d');
new Chart(ctxAuto, {
  type: 'line',
  data: {
    labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
    datasets: [{
      label: 'Automazione %',
      data: [78, 81, 84, 86, 88, 89, 91, 92, 93, 93.5, 94, 94.2],
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: {
        min: 70,
        max: 100,
        grid: { color: '#1f2937' },
        ticks: {
          color: '#9ca3af',
          callback: (value) => value + '%'
        }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#9ca3af' }
      }
    }
  }
});

// Tipo Documento (Doughnut)
const ctxTipo = document.getElementById('chart-tipo-documento').getContext('2d');
new Chart(ctxTipo, {
  type: 'doughnut',
  data: {
    labels: ['DDT', 'Fatture', 'Contratti', 'Bolle'],
    datasets: [{
      data: [5234, 3821, 2156, 1636],
      backgroundColor: ['#22d3ee', '#3b82f6', '#8b5cf6', '#10b981'],
      borderWidth: 0
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { color: '#e5e7eb', padding: 15 }
      }
    }
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
