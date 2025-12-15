<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Area Clienti Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <section class="kpi-grid">
    <div class="card kpi" data-kpi="documenti">
      <p>Documenti processati</p>
      <h2 id="kpi-documenti">--</h2>
    </div>
    <div class="card kpi" data-kpi="tempo">
      <p>Tempo risparmiato</p>
      <h2 id="kpi-tempo">--</h2>
    </div>
    <div class="card kpi" data-kpi="costo">
      <p>Costo risparmiato</p>
      <h2 id="kpi-costo">--</h2>
    </div>
    <div class="card kpi" data-kpi="automazione">
      <p>Automazione %</p>
      <h2 id="kpi-automazione">--</h2>
    </div>
    <div class="card kpi" data-kpi="errori">
      <p>Errori evitati</p>
      <h2 id="kpi-errori">--</h2>
    </div>
    <div class="card kpi" data-kpi="roi">
      <p>ROI</p>
      <h2 id="kpi-roi">--</h2>
    </div>
  </section>

  <section class="card">
    <div class="card-header">
      <div>
        <p class="muted small">Document Intelligence</p>
        <h3>Trend mensile</h3>
      </div>
    </div>
    <canvas id="chart-trend" height="120"></canvas>
  </section>

  <?php
  // Recupera servizi attivi dell'utente
  $stmt = $pdo->prepare('
    SELECT s.id, s.nome, s.descrizione, s.codice, s.prezzo_mensile,
           us.data_attivazione, us.stato
    FROM utenti_servizi us
    JOIN servizi s ON us.servizio_id = s.id
    WHERE us.user_id = :user_id AND us.stato = "attivo"
    ORDER BY us.data_attivazione DESC
  ');
  $stmt->execute(['user_id' => $clienteId]);
  $serviziAttivi = $stmt->fetchAll();
  ?>

  <section class="card">
    <div class="card-header">
      <h3>I tuoi servizi attivi</h3>
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
            </div>
            <div class="service-price">
              <span class="price">€<?= number_format($servizio['prezzo_mensile'], 2, ',', '.') ?></span>
              <span class="muted small">/mese</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="quick-links card">
    <h3>Azioni rapide</h3>
    <div class="links">
      <a class="btn primary" href="/area-clienti/document-intelligence.php">📄 Document Intelligence</a>
      <a class="btn ghost" href="/area-clienti/servizi.php">Servizi attivi</a>
      <a class="btn ghost" href="/area-clienti/fatture.php">Visualizza fatture</a>
      <a class="btn ghost" href="/area-clienti/profilo.php">Aggiorna profilo</a>
    </div>
  </section>
</main>

<script src="/area-clienti/js/kpi.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
