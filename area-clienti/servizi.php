<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];

$stmt = $pdo->prepare(
    'SELECT s.nome, s.descrizione, us.data_attivazione, us.stato
     FROM utenti_servizi us
     JOIN servizi s ON us.servizio_id = s.id
     WHERE us.user_id = :user_id AND us.stato = "attivo"
     ORDER BY s.nome'
);
$stmt->execute(['user_id' => $clienteId]);
$servizi = $stmt->fetchAll();

?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servizi attivi - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <div class="card">
    <h2>Servizi attivi</h2>
    <?php if (!$servizi): ?>
      <p class="muted">Nessun servizio attivo.</p>
    <?php else: ?>
      <div class="service-grid">
        <?php foreach ($servizi as $servizio): ?>
          <div class="service-card">
            <h3><?php echo htmlspecialchars($servizio['nome'] ?? 'Servizio'); ?></h3>
            <p class="muted"><?php echo htmlspecialchars($servizio['descrizione'] ?? 'Nessuna descrizione.'); ?></p>
            <p class="small">Attivo dal: <?php echo htmlspecialchars(date('d/m/Y', strtotime($servizio['data_attivazione']))); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
