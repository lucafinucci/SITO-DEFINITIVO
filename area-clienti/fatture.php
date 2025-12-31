<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/fatture-settings.php';

$clienteId = $_SESSION['cliente_id'];
$fattureSettings = getFattureSettings($pdo);
$mostraSoloInviate = (int)$fattureSettings['mostra_cliente_solo_inviate'] === 1;
$filtroStato = $_GET['stato'] ?? 'tutte';
$filtroStatiValidi = ['tutte', 'da-pagare', 'pagate'];
if (!in_array($filtroStato, $filtroStatiValidi, true)) {
    $filtroStato = 'tutte';
}

$hasPeriodo = false;
$colCliente = 'user_id';
$hasClienteId = false;
$hasUserId = false;
$colTotale = 'importo_totale';
try {
    $stmtCols = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fatture'
          AND COLUMN_NAME IN ('anno', 'mese', 'cliente_id', 'user_id', 'totale', 'importo_totale')
    ");
    $stmtCols->execute();
    $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
    $hasPeriodo = in_array('anno', $cols, true) && in_array('mese', $cols, true);
    $hasClienteId = in_array('cliente_id', $cols, true);
    $hasUserId = in_array('user_id', $cols, true);
    if ($hasClienteId && $hasUserId) {
        $colCliente = 'COALESCE(cliente_id, user_id)';
    } elseif ($hasClienteId) {
        $colCliente = 'cliente_id';
    } elseif ($hasUserId) {
        $colCliente = 'user_id';
    }
    if (in_array('totale', $cols, true)) {
        $colTotale = 'totale';
    } elseif (in_array('importo_totale', $cols, true)) {
        $colTotale = 'importo_totale';
    }
} catch (PDOException $e) {
    $hasPeriodo = false;
}

// Download handler
if (isset($_GET['file'])) {
    $fatturaId = (int)$_GET['file'];
    if ($fatturaId <= 0) {
        http_response_code(400);
        echo 'ID fattura mancante';
        exit;
    }
    header('Location: /area-clienti/api/download-fattura.php?id=' . $fatturaId);
    exit;
}

// Elenco fatture
$selectFields = "id, numero_fattura, data_emissione, data_scadenza, $colTotale AS importo_totale, stato";
if ($hasPeriodo) {
    $selectFields .= ', anno, mese';
}

$where = "$colCliente = :user_id";
if ($mostraSoloInviate) {
    $where .= " AND stato IN ('inviata', 'pagata', 'scaduta', 'annullata')";
}
if ($filtroStato === 'da-pagare') {
    $where .= " AND stato IN ('emessa', 'inviata', 'scaduta')";
} elseif ($filtroStato === 'pagate') {
    $where .= " AND stato = 'pagata'";
}

$stmt = $pdo->prepare(
    "SELECT $selectFields
     FROM fatture
     WHERE $where
     ORDER BY data_emissione DESC"
);
$stmt->execute(['user_id' => $clienteId]);
$rows = $stmt->fetchAll();

$statoLabels = [
    'bozza' => 'Bozza',
    'emessa' => 'Emessa',
    'inviata' => 'Inviata',
    'pagata' => 'Pagata',
    'scaduta' => 'Scaduta',
    'annullata' => 'Annullata'
];

$fattureServizi = [];
if (!empty($rows)) {
    $fattureIds = array_map(function ($row) {
        return (int)$row['id'];
    }, $rows);
    $placeholders = implode(',', array_fill(0, count($fattureIds), '?'));
    try {
        $stmt = $pdo->prepare("
            SELECT fr.fattura_id, COALESCE(s.nome, fr.descrizione) AS servizio_nome
            FROM fatture_righe fr
            LEFT JOIN servizi s ON fr.servizio_id = s.id
            WHERE fr.fattura_id IN ($placeholders)
            ORDER BY fr.id ASC
        ");
        $stmt->execute($fattureIds);
        foreach ($stmt->fetchAll() as $row) {
            $fattureServizi[(int)$row['fattura_id']][] = $row['servizio_nome'];
        }
    } catch (PDOException $e) {
        // Tabella righe fattura non disponibile o schema diverso
    }
}

?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fatture - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <div class="card">
    <h2>Fatture</h2>
    <div style="display: flex; gap: 10px; margin: 12px 0 18px 0; flex-wrap: wrap;">
      <a class="btn ghost small" href="/area-clienti/fatture.php?stato=tutte" style="<?= $filtroStato === 'tutte' ? 'border-color: var(--primary); color: var(--primary);' : '' ?>">
        Tutte
      </a>
      <a class="btn ghost small" href="/area-clienti/fatture.php?stato=da-pagare" style="<?= $filtroStato === 'da-pagare' ? 'border-color: var(--primary); color: var(--primary);' : '' ?>">
        Da pagare
      </a>
      <a class="btn ghost small" href="/area-clienti/fatture.php?stato=pagate" style="<?= $filtroStato === 'pagate' ? 'border-color: var(--primary); color: var(--primary);' : '' ?>">
        Pagate
      </a>
    </div>
    <?php if (!$rows): ?>
      <p class="muted">Nessuna fattura trovata.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Periodo</th>
            <th>Numero</th>
            <th>Scadenza</th>
            <th>Stato</th>
            <th>Importo</th>
            <th>Servizi</th>
            <th>Download</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['data_emissione']))); ?></td>
              <?php
                $periodo = date('m/Y', strtotime($r['data_emissione']));
                if ($hasPeriodo && !empty($r['anno']) && !empty($r['mese'])) {
                  $periodo = sprintf('%02d/%d', (int)$r['mese'], (int)$r['anno']);
                }
              ?>
              <td><?php echo htmlspecialchars($periodo); ?></td>
              <td><?php echo htmlspecialchars($r['numero_fattura']); ?></td>
              <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['data_scadenza']))); ?></td>
              <?php
                $statoRaw = strtolower(trim((string)($r['stato'] ?? '')));
                $statoLabel = $statoLabels[$statoRaw] ?? ($statoRaw !== '' ? ucfirst($statoRaw) : 'N/D');
              ?>
              <td><?php echo htmlspecialchars($statoLabel); ?></td>
              <td>€ <?php echo htmlspecialchars(number_format($r['importo_totale'], 2, ',', '.')); ?></td>
              <?php
                $servizi = $fattureServizi[(int)$r['id']] ?? [];
                if (!empty($servizi)) {
                  $serviziHtml = implode('<br>', array_map(function ($nome) {
                    return htmlspecialchars((string)$nome);
                  }, $servizi));
                } else {
                  $serviziHtml = '<span class="muted">Nessun servizio</span>';
                }
              ?>
              <td><?php echo $serviziHtml; ?></td>
              <td><a class="btn primary" href="/area-clienti/api/download-fattura.php?id=<?php echo (int)$r['id']; ?>" target="_blank">Download</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
