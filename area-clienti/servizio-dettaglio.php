<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];
$servizioId = intval($_GET['id'] ?? 0);

// Messaggio di successo dopo upload training
$uploadSuccess = isset($_GET['upload']) && $_GET['upload'] === 'success';

// Recupera dettagli servizio
$stmt = $pdo->prepare('
  SELECT s.*, us.data_attivazione, us.data_disattivazione, us.stato, us.note,
         pp.prezzo_mensile AS prezzo_personalizzato,
         COALESCE(pp.prezzo_mensile, s.prezzo_mensile) AS prezzo_finale
  FROM servizi s
  JOIN utenti_servizi us ON s.id = us.servizio_id
  LEFT JOIN clienti_prezzi_personalizzati pp
    ON pp.cliente_id = us.user_id AND pp.servizio_id = s.id
  WHERE s.id = :servizio_id AND us.user_id = :user_id
  ORDER BY (us.stato = "attivo") DESC, us.data_attivazione DESC, us.id DESC
  LIMIT 1
');
$stmt->execute(['servizio_id' => $servizioId, 'user_id' => $clienteId]);
$servizio = $stmt->fetch();

if (!$servizio) {
  header('Location: /area-clienti/servizi.php');
  exit;
}

$webappUrl = null;
try {
  $stmtCols = $pdo->prepare("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utenti'
      AND COLUMN_NAME = 'webapp_url'
  ");
  $stmtCols->execute();
  $hasWebappUrl = (bool)$stmtCols->fetchColumn();
  if ($hasWebappUrl) {
    $stmt = $pdo->prepare('SELECT webapp_url FROM utenti WHERE id = :id');
    $stmt->execute(['id' => $clienteId]);
    $webappUrlValue = trim((string)$stmt->fetchColumn());
    if ($webappUrlValue !== '') {
      $webappUrl = $webappUrlValue;
    }
  }
} catch (PDOException $e) {
  $webappUrl = null;
}

$currentPeriod = date('Y-m');
$docUsage = 0;
$stmt = $pdo->prepare('
  SELECT documenti_usati
  FROM servizi_quota_uso
  WHERE cliente_id = :cliente_id AND servizio_id = :servizio_id AND periodo = :periodo
  LIMIT 1
');
$stmt->execute([
  'cliente_id' => $clienteId,
  'servizio_id' => $servizioId,
  'periodo' => $currentPeriod
]);
$docUsageValue = $stmt->fetchColumn();
if ($docUsageValue !== false) {
  $docUsage = (int)$docUsageValue;
}

$kpiTempoMedio = null;
$kpiAutomazione = null;
$kpiErroriEvitati = null;
$kpiTempoRisparmiato = null;
$kpiRoi = null;

$rangeOptions = [
  3 => '3 mesi',
  6 => '6 mesi',
  12 => '12 mesi',
  24 => '24 mesi'
];
$rangeMonths = (int)($_GET['range'] ?? 6);
if (!array_key_exists($rangeMonths, $rangeOptions)) {
  $rangeMonths = 6;
}

$monthLabels = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
$chartPeriods = [];
$chartLabels = [];
$startMonth = new DateTime('first day of this month');
$startMonth->modify('-' . ($rangeMonths - 1) . ' months');
for ($i = 0; $i < $rangeMonths; $i++) {
  $dt = (clone $startMonth)->modify('+' . $i . ' months');
  $period = $dt->format('Y-m');
  $chartPeriods[] = $period;
  $chartLabels[] = $monthLabels[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
}

$usageByPeriod = [];
if (!empty($chartPeriods)) {
  $stmt = $pdo->prepare('
    SELECT periodo, documenti_usati
    FROM servizi_quota_uso
    WHERE cliente_id = :cliente_id
      AND servizio_id = :servizio_id
      AND periodo BETWEEN :start_period AND :end_period
  ');
  $stmt->execute([
    'cliente_id' => $clienteId,
    'servizio_id' => $servizioId,
    'start_period' => $chartPeriods[0],
    'end_period' => $chartPeriods[count($chartPeriods) - 1]
  ]);
  foreach ($stmt->fetchAll() as $row) {
    $usageByPeriod[$row['periodo']] = (int)$row['documenti_usati'];
  }
}

$chartDocSeries = [];
foreach ($chartPeriods as $period) {
  $chartDocSeries[] = $usageByPeriod[$period] ?? 0;
}
$hasDocSeries = array_sum($chartDocSeries) > 0;
$hasOtherSeries = false;
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($servizio['nome']) ?> - Dettagli Servizio</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <div class="breadcrumb" style="margin-bottom: 20px;">
    <a href="/area-clienti/dashboard.php" style="color: var(--accent1);">Dashboard</a>
    <span style="color: var(--muted); margin: 0 8px;">/</span>
    <a href="/area-clienti/servizi.php" style="color: var(--accent1);">Servizi</a>
    <span style="color: var(--muted); margin: 0 8px;">/</span>
    <span><?= htmlspecialchars($servizio['nome']) ?></span>
  </div>

  <?php if ($uploadSuccess): ?>
  <div class="alert success" style="margin-bottom: 20px;">
    ✅ <strong>Richiesta inviata con successo!</strong> Il nostro team analizzerà la tua richiesta di addestramento entro 24 ore. Riceverai un'email con il preventivo e la timeline.
  </div>
  <?php endif; ?>

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
          <span class="price" style="font-size: 32px;">€<?= number_format($servizio['prezzo_finale'], 2, ',', '.') ?></span>
          <span class="muted">/mese</span>
          <?php if ($servizio['prezzo_personalizzato'] !== null): ?>
            <span class="badge" style="margin-left: 8px;">Personalizzato</span>
          <?php endif; ?>
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
        <a href="<?= htmlspecialchars($webappUrl ?? 'https://app.finch-ai.it/document-intelligence') ?>" class="btn primary" target="_blank" rel="noopener">
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
            <?= number_format($docUsage, 0, ',', '.') ?>
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Tempo Medio Lettura</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            <?= $kpiTempoMedio !== null ? htmlspecialchars($kpiTempoMedio) . ' sec' : 'n/d' ?>
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Automazione %</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            <?= $kpiAutomazione !== null ? htmlspecialchars($kpiAutomazione) . '%' : 'n/d' ?>
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Errori Evitati</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            <?= $kpiErroriEvitati !== null ? htmlspecialchars($kpiErroriEvitati) : 'n/d' ?>
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">Tempo Risparmiato</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            <?= $kpiTempoRisparmiato !== null ? htmlspecialchars($kpiTempoRisparmiato) . ' ore' : 'n/d' ?>
          </h3>
        </div>

        <div style="padding: 16px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border);">
          <p class="muted small">ROI</p>
          <h3 style="margin: 8px 0 0; font-size: 28px; background: linear-gradient(90deg, var(--accent1), var(--accent2)); -webkit-background-clip: text; color: transparent;">
            <?= $kpiRoi !== null ? htmlspecialchars($kpiRoi) . '%' : 'n/d' ?>
          </h3>
        </div>

          </div>
        </section>

        <!-- Grafici Andamento Mensile -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
          <h3 style="margin: 0;">Grafici andamento mensile</h3>
          <form method="get" style="display: flex; align-items: center; gap: 8px;">
            <input type="hidden" name="id" value="<?= (int)$servizioId ?>">
            <label class="muted small" for="range">Periodo</label>
            <select id="range" name="range" class="input" onchange="this.form.submit()">
              <?php foreach ($rangeOptions as $value => $label): ?>
                <option value="<?= (int)$value ?>" <?= $rangeMonths === (int)$value ? 'selected' : '' ?>>
                  <?= htmlspecialchars($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 12px;">

          <!-- Grafico 1: Documenti letti mese per mese -->
          <section class="card">
            <h3>📈 Documenti Processati - Ultimi <?= (int)$rangeMonths ?> Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Trend di crescita nell'elaborazione documentale</p>
            <canvas id="chart-documenti-mensili" height="150"></canvas>
            <?php if (!$hasDocSeries): ?>
              <p class="muted small" style="margin-top: 12px;">Nessun dato disponibile nel periodo selezionato.</p>
            <?php endif; ?>
          </section>

          <!-- Grafico 2: Automazione % nel tempo -->
          <section class="card">
            <h3>🤖 Automazione % - Ultimi <?= (int)$rangeMonths ?> Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Percentuale documenti senza intervento umano</p>
            <canvas id="chart-automazione-mensile" height="150"></canvas>
            <?php if (!$hasOtherSeries): ?>
              <p class="muted small" style="margin-top: 12px;">Dati non disponibili.</p>
            <?php endif; ?>
          </section>

          <!-- Grafico 3: Errori evitati mensili -->
          <section class="card">
            <h3>✅ Errori Evitati - Ultimi <?= (int)$rangeMonths ?> Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Numero di errori prevenuti dall'AI</p>
            <canvas id="chart-errori-mensili" height="150"></canvas>
            <?php if (!$hasOtherSeries): ?>
              <p class="muted small" style="margin-top: 12px;">Dati non disponibili.</p>
            <?php endif; ?>
          </section>

          <!-- Grafico 4: Tempo risparmiato mensile -->
          <section class="card">
            <h3>⏱️ Tempo Risparmiato - Ultimi <?= (int)$rangeMonths ?> Mesi</h3>
            <p class="muted small" style="margin-bottom: 16px;">Ore risparmiate grazie all'automazione</p>
            <canvas id="chart-tempo-mensile" height="150"></canvas>
            <?php if (!$hasOtherSeries): ?>
              <p class="muted small" style="margin-top: 12px;">Dati non disponibili.</p>
            <?php endif; ?>
          </section>

        </div>
      </div>

      <!-- Sidebar Destra - Modelli AI Addestrati -->
      <aside>
        <section class="card" style="position: sticky; top: 84px;">
          <h3 style="margin-bottom: 16px;">🧠 Modelli AI Addestrati</h3>
          <p class="muted small" style="margin-bottom: 20px;">I tuoi modelli personalizzati per Document Intelligence</p>

          <?php
          // Recupera modelli AI addestrati dal database
          $modelliAI = [];

          // 1. Modelli già completati (dalla tabella modelli_addestrati)
          try {
            $stmt = $pdo->prepare('
              SELECT
                nome_modello as nome,
                tipo_modello as tipo,
                accuratezza as accuracy,
                num_documenti_addestramento as documenti,
                updated_at as ultima_versione,
                "attivo" as stato
              FROM modelli_addestrati
              WHERE user_id = :user_id AND attivo = 1
              ORDER BY updated_at DESC
            ');
            $stmt->execute(['user_id' => $clienteId]);
            $modelliCompletati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($modelliCompletati as $modello) {
              $modelliAI[] = $modello;
            }
          } catch (PDOException $e) {
            // Tabella non esiste ancora, usa dati statici
          }

          // 2. Richieste di addestramento in corso (dalla tabella richieste_addestramento)
          try {
            $stmt = $pdo->prepare('
              SELECT
                ra.id as richiesta_id,
                CONCAT(ra.tipo_modello, " - In Addestramento") as nome,
                ra.tipo_modello as tipo,
                0 as accuracy,
                ra.num_documenti_stimati as documenti,
                ra.created_at as ultima_versione,
                "training" as stato,
                (SELECT COUNT(*) FROM richieste_addestramento_files raf WHERE raf.richiesta_id = ra.id) as files_count,
                (
                  SELECT SUBSTRING_INDEX(
                    GROUP_CONCAT(raf.filename_originale ORDER BY raf.id SEPARATOR "||"),
                    "||",
                    3
                  )
                  FROM richieste_addestramento_files raf
                  WHERE raf.richiesta_id = ra.id
                ) as files_preview
              FROM richieste_addestramento ra
              JOIN (
                SELECT tipo_modello, MAX(created_at) as max_created_at
                FROM richieste_addestramento
                WHERE user_id = :user_id
                  AND stato IN ("in_attesa", "in_lavorazione")
                GROUP BY tipo_modello
              ) latest
                ON latest.tipo_modello = ra.tipo_modello
               AND latest.max_created_at = ra.created_at
              WHERE ra.user_id = :user_id
                AND ra.stato IN ("in_attesa", "in_lavorazione")
              ORDER BY ra.created_at DESC
            ');
            $stmt->execute(['user_id' => $clienteId]);
            $richiesteInCorso = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($richiesteInCorso as $richiesta) {
              $modelliAI[] = $richiesta;
            }
          } catch (PDOException $e) {
            // Tabella non esiste ancora
          }

          // Se nessun modello trovato, usa dati statici di fallback
          if (empty($modelliAI)) {
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
              ]
           ];
         }
         ?>

          <?php
          $hasTrainingInCorso = false;
          $trainingRequestId = 0;
          $trainingTipo = '';
          foreach ($modelliAI as $m) {
            if (($m['stato'] ?? '') === 'training') {
              $hasTrainingInCorso = true;
              $trainingRequestId = (int)($m['richiesta_id'] ?? 0);
              $trainingTipo = (string)($m['tipo'] ?? '');
              break;
            }
          }
          ?>

          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($modelliAI as $modello): ?>
              <div data-stato="<?= htmlspecialchars($modello['stato']) ?>" style="padding: 14px; background: #0f172a; border-radius: 12px; border: 1px solid var(--border); transition: all 0.2s ease;"
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
                  <?php if ($modello['stato'] === 'training'): ?>
                    <!-- Modello in addestramento - mostra progress animato -->
                    <div style="margin-bottom: 6px;">
                      <span class="muted small">Addestramento in corso...</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #1f2937; border-radius: 3px; overflow: hidden; position: relative;">
                      <div style="width: 50%; height: 100%; background: linear-gradient(90deg, #fbbf24, #f59e0b); border-radius: 3px; animation: training-pulse 2s ease-in-out infinite;"></div>
                    </div>
                    <style>
                      @keyframes training-pulse {
                        0%, 100% { width: 30%; opacity: 0.6; }
                        50% { width: 70%; opacity: 1; }
                      }
                    </style>
                    <div style="display: flex; justify-content: space-between; margin-top: 10px; margin-bottom: 12px;">
                      <span class="muted small">
                        <?= number_format((int)($modello['files_count'] ?? 0), 0, ',', '.') ?> doc caricati
                      </span>
                      <span class="muted small"><?= date('d/m H:i', strtotime($modello['ultima_versione'])) ?></span>
                    </div>

                    <?php
                    $filesPreviewRaw = (string)($modello['files_preview'] ?? '');
                    $filesPreview = array_values(array_filter(array_map('trim', explode('||', $filesPreviewRaw))));
                    $filesTotal = (int)($modello['files_count'] ?? 0);
                    $filesMore = max(0, $filesTotal - count($filesPreview));
                    ?>

                    <?php if (!empty($filesPreview)): ?>
                      <div style="margin-top: 6px; margin-bottom: 12px;">
                        <div class="muted small" style="margin-bottom: 6px;">Documenti in addestramento:</div>
                        <ul style="margin: 0; padding-left: 16px; color: #9ca3af; font-size: 12px; line-height: 1.4;">
                          <?php foreach ($filesPreview as $fn): ?>
                            <li style="word-break: break-word;"><?= htmlspecialchars($fn) ?></li>
                          <?php endforeach; ?>
                          <?php if ($filesMore > 0): ?>
                            <li class="muted small">… e altri <?= (int)$filesMore ?></li>
                          <?php endif; ?>
                        </ul>
                      </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <!-- Modello completato - mostra accuracy -->
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
                  <?php endif; ?>

                  <!-- CTA addestramento spostata sotto: un solo blocco -->
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border);">
            <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700;">🔄 Richiedi addestramento</h4>
            <p class="muted small" style="margin: 0 0 10px 0;">Invia nuovi documenti per addestrare un modello.</p>
            <?php
            $ctaQuery = ['servizio_id' => (int)$servizio['id']];
            if ($hasTrainingInCorso && $trainingRequestId > 0) {
              $ctaQuery['richiesta_id'] = $trainingRequestId;
              if ($trainingTipo !== '') $ctaQuery['tipo'] = $trainingTipo;
            }
            $ctaHref = '/area-clienti/richiedi-addestramento.php?' . http_build_query($ctaQuery);
            ?>
            <a href="<?= htmlspecialchars($ctaHref) ?>"
               class="btn primary"
               style="width: 100%; padding: 10px 14px; font-size: 13px; text-align: center;">
              <?= $hasTrainingInCorso ? '📎 Aggiungi documenti' : '➕ Richiedi addestramento' ?>
            </a>
          </div>

          <!-- Pulsante "Gestisci Modelli" rimosso -->
        </section>
      </aside>

    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>

<?php if ($servizio['codice'] === 'DOC-INT'): ?>
<script>
// INTERCETTA TUTTI I CLICK SUI LINK - BLOCCA MAILTO
document.addEventListener('click', function(e) {
  const link = e.target.closest('a');
  if (link && link.href && link.href.includes('mailto:training')) {
    e.preventDefault();
    e.stopPropagation();
    const tipo = link.dataset.modelloTipo || link.closest('.card')?.querySelector('[data-modello-tipo]')?.dataset.modelloTipo || '';
    const qs = new URLSearchParams();
    if (tipo) qs.set('tipo', tipo);
    qs.set('servizio_id', <?= (int)$servizio['id'] ?>);
    window.location.href = `/area-clienti/richiedi-addestramento.php?${qs.toString()}`;
    return false;
  }
}, true);

// Enforce link target for richiesta addestramento (evita vecchi mailto cache o override)
document.querySelectorAll('.richiedi-addestramento').forEach(btn => {
  const tipo = btn.dataset.modelloTipo || '';
  const qs = new URLSearchParams();
  if (tipo) qs.set('tipo', tipo);
  qs.set('servizio_id', <?= (int)$servizio['id'] ?>);
  btn.href = `/area-clienti/richiedi-addestramento.php?${qs.toString()}`;
});

// Hard override: se qualche build/copia lascia un mailto:training, rimpiazza con il form corretto
document.querySelectorAll('a[href^="mailto:training@"]').forEach(btn => {
  const tipo = btn.dataset.modelloTipo || '';
  const qs = new URLSearchParams();
  if (tipo) qs.set('tipo', tipo);
  qs.set('servizio_id', <?= (int)$servizio['id'] ?>);
  btn.href = `/area-clienti/richiedi-addestramento.php?${qs.toString()}`;
  btn.removeAttribute('target');
  btn.removeAttribute('rel');
});

// Dati dinamici
const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
const docSeries = <?= json_encode($chartDocSeries) ?>;
const hasDocSeries = <?= $hasDocSeries ? 'true' : 'false' ?>;
const hasOtherSeries = <?= $hasOtherSeries ? 'true' : 'false' ?>;

const ctxDocumentiEl = document.getElementById('chart-documenti-mensili');
if (ctxDocumentiEl) {
  if (hasDocSeries) {
    const ctxDocumenti = ctxDocumentiEl.getContext('2d');
    new Chart(ctxDocumenti, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [{
          label: 'Documenti Processati',
          data: docSeries,
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
  } else {
    ctxDocumentiEl.style.display = 'none';
  }
}

const ctxAutomazioneEl = document.getElementById('chart-automazione-mensile');
const ctxErroriEl = document.getElementById('chart-errori-mensili');
const ctxTempoEl = document.getElementById('chart-tempo-mensile');
if (!hasOtherSeries) {
  if (ctxAutomazioneEl) ctxAutomazioneEl.style.display = 'none';
  if (ctxErroriEl) ctxErroriEl.style.display = 'none';
  if (ctxTempoEl) ctxTempoEl.style.display = 'none';
}
</script>
<?php endif; ?>

</body>
</html>
