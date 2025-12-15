<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];
$servizioId = intval($_GET['id'] ?? 0);

// Recupera dettagli servizio
$stmt = $pdo->prepare('
  SELECT s.*, us.data_attivazione, us.stato, us.note
  FROM servizi s
  JOIN utenti_servizi us ON s.id = us.servizio_id
  WHERE s.id = :servizio_id AND us.user_id = :user_id
  LIMIT 1
');
$stmt->execute(['servizio_id' => $servizioId, 'user_id' => $clienteId]);
$servizio = $stmt->fetch();

if (!$servizio) {
  header('Location: /area-clienti/servizi.php');
  exit;
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($servizio['nome']) ?> - Dettagli Servizio</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <div class="breadcrumb" style="margin-bottom: 20px;">
    <a href="/area-clienti/dashboard.php" style="color: var(--accent1);">Dashboard</a>
    <span style="color: var(--muted); margin: 0 8px;">/</span>
    <a href="/area-clienti/servizi.php" style="color: var(--accent1);">Servizi</a>
    <span style="color: var(--muted); margin: 0 8px;">/</span>
    <span><?= htmlspecialchars($servizio['nome']) ?></span>
  </div>

  <section class="card">
    <div class="card-header" style="margin-bottom: 20px;">
      <div>
        <span class="badge <?= $servizio['stato'] === 'attivo' ? 'success' : 'warning' ?>">
          <?= ucfirst($servizio['stato']) ?>
        </span>
        <h1 style="margin: 8px 0 4px 0; font-size: 28px;"><?= htmlspecialchars($servizio['nome']) ?></h1>
        <p class="muted"><?= htmlspecialchars($servizio['descrizione']) ?></p>
      </div>
      <div style="text-align: right;">
        <div class="service-price">
          <span class="price" style="font-size: 32px;">€<?= number_format($servizio['prezzo_mensile'], 2, ',', '.') ?></span>
          <span class="muted">/mese</span>
        </div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 24px;">
      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Codice Servizio</p>
        <p style="margin: 4px 0 0; font-weight: 600;"><?= htmlspecialchars($servizio['codice']) ?></p>
      </div>

      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Data Attivazione</p>
        <p style="margin: 4px 0 0; font-weight: 600;"><?= date('d/m/Y', strtotime($servizio['data_attivazione'])) ?></p>
      </div>

      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Stato</p>
        <p style="margin: 4px 0 0; font-weight: 600; color: <?= $servizio['stato'] === 'attivo' ? '#10b981' : '#fbbf24' ?>;">
          <?= ucfirst($servizio['stato']) ?>
        </p>
      </div>

      <div style="padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Tempo Attivo</p>
        <p style="margin: 4px 0 0; font-weight: 600;">
          <?php
          $dataAttivazione = new DateTime($servizio['data_attivazione']);
          $oggi = new DateTime();
          $diff = $oggi->diff($dataAttivazione);
          echo $diff->days . ' giorni';
          ?>
        </p>
      </div>
    </div>

    <?php if ($servizio['note']): ?>
      <div style="margin-top: 20px; padding: 14px; background: #0f172a; border-radius: 10px; border: 1px solid var(--border);">
        <p class="muted small">Note</p>
        <p style="margin: 8px 0 0;"><?= nl2br(htmlspecialchars($servizio['note'])) ?></p>
      </div>
    <?php endif; ?>

    <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
      <?php if ($servizio['codice'] === 'DOC-INT'): ?>
        <a href="https://app.finch-ai.it/document-intelligence" class="btn primary" target="_blank" rel="noopener">
          🚀 Accedi alla WebApp
        </a>
        <a href="/area-clienti/fatture.php" class="btn ghost">
          📄 Fatture
        </a>
      <?php else: ?>
        <a href="https://app.finch-ai.it/<?= strtolower($servizio['codice']) ?>" class="btn primary" target="_blank" rel="noopener">
          Accedi al Servizio
        </a>
      <?php endif; ?>
      <a href="/area-clienti/dashboard.php" class="btn ghost">
        ← Torna alla Dashboard
      </a>
      <a href="mailto:supporto@finch-ai.it?subject=Supporto%20<?= urlencode($servizio['nome']) ?>" class="btn ghost">
        Richiedi Supporto
      </a>
    </div>
  </section>

  <?php if ($servizio['codice'] === 'DOC-INT'): ?>
    <!-- Layout principale con sidebar -->
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 20px; align-items: start;">

      <!-- Colonna principale -->
      <div>
        <!-- KPI Dashboard Document Intelligence -->
        <section class="card">
          <h2>📊 Dashboard KPI - Document Intelligence</h2>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 20px;">

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Documenti Processati</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            12.847
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Tempo Medio Lettura</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            2.3 sec
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Automazione %</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            94.2%
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Errori Evitati</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            312
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Tempo Risparmiato</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            427 ore
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">ROI</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            340%
          </h3>
        </div>

          </div>
        </section>

        <!-- Grafici Andamento Mensile -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">

          <!-- Grafico 1: Documenti letti mese per mese -->
          <section class="card">
            <h3>📈 Documenti Processati - Ultimi 6 Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Trend di crescita nell'elaborazione documentale</p>
            <canvas id="chart-documenti-mensili" height="150"></canvas>
          </section>

          <!-- Grafico 2: Automazione % nel tempo -->
          <section class="card">
            <h3>🤖 Automazione % - Ultimi 6 Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Percentuale documenti senza intervento umano</p>
            <canvas id="chart-automazione-mensile" height="150"></canvas>
          </section>

          <!-- Grafico 3: Errori evitati mensili -->
          <section class="card">
            <h3>✅ Errori Evitati - Ultimi 6 Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Numero di errori prevenuti dall'AI</p>
            <canvas id="chart-errori-mensili" height="150"></canvas>
          </section>

          <!-- Grafico 4: Tempo risparmiato mensile -->
          <section class="card">
            <h3>⏱️ Tempo Risparmiato - Ultimi 6 Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Ore risparmiate grazie all'automazione</p>
            <canvas id="chart-tempo-mensile" height="150"></canvas>
          </section>

        </div>
      </div>

      <!-- Sidebar Destra - Modelli AI Addestrati -->
      <aside>
        <section class="card" style="position: sticky; top: 84px;">
          <h3 style="margin-bottom: 16px;">🧠 Modelli AI Addestrati</h3>
          <p class="muted small" style="margin-bottom: 20px;">I tuoi modelli personalizzati per Document Intelligence</p>

          <?php
          // Simula modelli AI addestrati per il cliente
          $modelliAI = [
            [
              'nome' => 'Fatture Elettroniche',
              'tipo' => 'DDT & Fatture',
              'accuracy' => 98.5,
              'documenti' => 4521,
              'ultima_versione' => '2024-11-28',
              'stato' => 'attivo'
            ],
            [
              'nome' => 'Contratti Commerciali',
              'tipo' => 'Contratti',
              'accuracy' => 96.2,
              'documenti' => 1834,
              'ultima_versione' => '2024-11-15',
              'stato' => 'attivo'
            ],
            [
              'nome' => 'Bolle di Trasporto',
              'tipo' => 'Logistica',
              'accuracy' => 97.8,
              'documenti' => 2756,
              'ultima_versione' => '2024-11-20',
              'stato' => 'attivo'
            ],
            [
              'nome' => 'Ordini di Acquisto',
              'tipo' => 'Procurement',
              'accuracy' => 95.4,
              'documenti' => 1236,
              'ultima_versione' => '2024-10-12',
              'stato' => 'addestramento'
            ]
          ];
          ?>

          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($modelliAI as $modello): ?>
              <div style="padding: 14px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border); transition: all 0.2s ease;"
                   onmouseover="this.style.borderColor='#22d3ee'"
                   onmouseout="this.style.borderColor='var(--border)'">

                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                  <div style="flex: 1;">
                    <h4 style="margin: 0; font-size: 14px; font-weight: 600;"><?= htmlspecialchars($modello['nome']) ?></h4>
                    <p class="muted small" style="margin: 4px 0 0;"><?= htmlspecialchars($modello['tipo']) ?></p>
                  </div>
                  <span class="badge <?= $modello['stato'] === 'attivo' ? 'success' : 'warning' ?>" style="font-size: 10px; padding: 3px 8px;">
                    <?= $modello['stato'] === 'attivo' ? '✓ Attivo' : '⏳ Training' ?>
                  </span>
                </div>

                <div style="margin-top: 12px;">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span class="muted small">Accuratezza</span>
                    <span style="font-weight: 600; font-size: 13px; color: #10b981;"><?= number_format($modello['accuracy'], 1, ',', '.') ?>%</span>
                  </div>

                  <div style="width: 100%; height: 6px; background: #1f2937; border-radius: 3px; overflow: hidden;">
                    <div style="width: <?= $modello['accuracy'] ?>%; height: 100%; background: linear-gradient(90deg, #10b981, #22d3ee); border-radius: 3px;"></div>
                  </div>

                  <div style="display: flex; justify-content: space-between; margin-top: 10px; margin-bottom: 12px;">
                    <span class="muted small"><?= number_format($modello['documenti'], 0, ',', '.') ?> doc</span>
                    <span class="muted small">v. <?= date('d/m', strtotime($modello['ultima_versione'])) ?></span>
                  </div>

                  <?php
                  // Recupera il nome del cliente per l'oggetto dell'email
                  $nomeCliente = $_SESSION['cliente_nome_completo'] ?? 'Cliente';

                  // Determina la sigla del tipo di documento per l'oggetto
                  $tipoDocumentoBreve = '';
                  switch($modello['tipo']) {
                    case 'DDT & Fatture':
                      $tipoDocumentoBreve = 'Fatture';
                      break;
                    case 'Logistica':
                      $tipoDocumentoBreve = 'DDT';
                      break;
                    case 'Contratti':
                      $tipoDocumentoBreve = 'Contratti';
                      break;
                    case 'Procurement':
                      $tipoDocumentoBreve = 'Ordini';
                      break;
                    default:
                      $tipoDocumentoBreve = $modello['tipo'];
                  }

                  ?>
                  <a href="/area-clienti/richiedi-addestramento.php?tipo=<?= urlencode($modello['tipo']) ?>"
                     class="btn ghost richiedi-addestramento"
                     data-modello-tipo="<?= htmlspecialchars($modello['tipo']) ?>"
                     style="width: 100%; padding: 8px 12px; font-size: 12px; text-align: center;">
                    🔄 Richiedi Addestramento
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <a href="https://app.finch-ai.it/document-intelligence/models"
             class="btn primary"
             target="_blank"
             rel="noopener"
             style="margin-top: 16px; font-size: 13px; padding: 10px 14px;">
            Gestisci Modelli →
          </a>
        </section>
      </aside>

    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>

<?php if ($servizio['codice'] === 'DOC-INT'): ?>
<script>
// INTERCETTA TUTTI I CLICK SUI LINK - BLOCCA MAILTO
document.addEventListener('click', function(e) {
  const link = e.target.closest('a');
  if (link && link.href && link.href.includes('mailto:training')) {
    e.preventDefault();
    e.stopPropagation();
    const tipo = link.dataset.modelloTipo || link.closest('.card')?.querySelector('[data-modello-tipo]')?.dataset.modelloTipo || '';
    window.location.href = `/area-clienti/richiedi-addestramento.php${tipo ? `?tipo=${encodeURIComponent(tipo)}` : ''}`;
    return false;
  }
}, true);

// Enforce link target for richiesta addestramento (evita vecchi mailto cache o override)
document.querySelectorAll('.richiedi-addestramento').forEach(btn => {
  const tipo = btn.dataset.modelloTipo || '';
  btn.href = `/area-clienti/richiedi-addestramento.php${tipo ? `?tipo=${encodeURIComponent(tipo)}` : ''}`;
});

// Hard override: se qualche build/copia lascia un mailto:training, rimpiazza con il form corretto
document.querySelectorAll('a[href^="mailto:training@"]').forEach(btn => {
  const tipo = btn.dataset.modelloTipo || '';
  btn.href = `/area-clienti/richiedi-addestramento.php${tipo ? `?tipo=${encodeURIComponent(tipo)}` : ''}`;
  btn.removeAttribute('target');
  btn.removeAttribute('rel');
});

// Dati ultimi 6 mesi
const mesi = ['Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];

// Grafico 1: Documenti processati
const ctxDocumenti = document.getElementById('chart-documenti-mensili').getContext('2d');
new Chart(ctxDocumenti, {
  type: 'bar',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Documenti Processati',
      data: [1823, 2156, 2398, 2567, 2734, 2847],
      backgroundColor: 'rgba(34, 211, 238, 0.7)',
      borderColor: '#22d3ee',
      borderWidth: 2,
      borderRadius: 8
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (context) => context.parsed.y.toLocaleString('it-IT') + ' documenti'
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: '#1f2937' },
        ticks: {
          color: '#9ca3af',
          callback: (value) => value.toLocaleString('it-IT')
        }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#9ca3af' }
      }
    }
  }
});

// Grafico 2: Automazione %
const ctxAutomazione = document.getElementById('chart-automazione-mensile').getContext('2d');
new Chart(ctxAutomazione, {
  type: 'line',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Automazione %',
      data: [88.5, 90.2, 91.8, 92.9, 93.7, 94.2],
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      pointRadius: 5,
      pointBackgroundColor: '#10b981',
      pointBorderColor: '#fff',
      pointBorderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (context) => context.parsed.y.toFixed(1) + '%'
        }
      }
    },
    scales: {
      y: {
        min: 85,
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

// Grafico 3: Errori evitati
const ctxErrori = document.getElementById('chart-errori-mensili').getContext('2d');
new Chart(ctxErrori, {
  type: 'bar',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Errori Evitati',
      data: [38, 45, 52, 58, 61, 68],
      backgroundColor: 'rgba(139, 92, 246, 0.7)',
      borderColor: '#8b5cf6',
      borderWidth: 2,
      borderRadius: 8
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (context) => context.parsed.y + ' errori evitati'
        }
      }
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

// Grafico 4: Tempo risparmiato
const ctxTempo = document.getElementById('chart-tempo-mensile').getContext('2d');
new Chart(ctxTempo, {
  type: 'line',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Ore Risparmiate',
      data: [58, 67, 74, 82, 89, 96],
      borderColor: '#f59e0b',
      backgroundColor: 'rgba(245, 158, 11, 0.1)',
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      pointRadius: 5,
      pointBackgroundColor: '#f59e0b',
      pointBorderColor: '#fff',
      pointBorderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (context) => context.parsed.y + ' ore risparmiate'
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: '#1f2937' },
        ticks: {
          color: '#9ca3af',
          callback: (value) => value + ' h'
        }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#9ca3af' }
      }
    }
  }
});
</script>
<?php endif; ?>

</body>
</html>
