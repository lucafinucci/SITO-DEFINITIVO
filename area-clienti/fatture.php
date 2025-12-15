<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];

// Download handler
if (isset($_GET['file'])) {
    // Questa logica di download è gestita dall'API sicura, non da qui.
    // Per coerenza, reindirizziamo all'endpoint API corretto.
    // Nota: questo richiede che l'utente sia loggato anche nel sistema API/JWT.
    // Una soluzione migliore sarebbe un proxy PHP che gestisce il download.
    // Per ora, lasciamo un placeholder.
    echo "Il download è gestito tramite l'endpoint API sicuro.";
    exit;
}

// Elenco fatture
$stmt = $pdo->prepare(
    'SELECT id, numero_fattura, data_emissione, importo_totale, stato, file_path 
     FROM fatture 
     WHERE user_id = :user_id 
     ORDER BY data_emissione DESC'
);
$stmt->execute(['user_id' => $clienteId]);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Fatture - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <div class="card">
    <h2>Fatture</h2>
    <?php if (!$rows): ?>
      <p class="muted">Nessuna fattura trovata.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Numero</th>
            <th>Importo</th>
            <th>Download</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['data_emissione']))); ?></td>
              <td><?php echo htmlspecialchars($r['numero_fattura']); ?></td>
              <td>€ <?php echo htmlspecialchars(number_format($r['importo_totale'], 2, ',', '.')); ?></td>
              <td><a class="btn primary" href="/api/clienti/download-fattura.php?id=<?php echo $r['id']; ?>" target="_blank">PDF</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
