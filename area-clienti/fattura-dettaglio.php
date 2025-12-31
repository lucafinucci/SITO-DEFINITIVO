<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$clienteId = $_SESSION['cliente_id'];

$fatturaId = (int)($_GET['id'] ?? 0);
if ($fatturaId <= 0) {
    header('Location: /area-clienti/fatture.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

// Verifica compatibilità schema database
$stmt = $pdo->prepare("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'fatture'
      AND COLUMN_NAME IN (
        'anno', 'mese', 'cliente_id', 'user_id',
        'imponibile', 'importo_netto',
        'iva_importo', 'iva', 'iva_percentuale',
        'totale', 'importo_totale',
        'file_pdf_path', 'file_path'
      )
");
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

$hasAnno = in_array('anno', $cols, true);
$hasMese = in_array('mese', $cols, true);
$hasClienteId = in_array('cliente_id', $cols, true);
$hasUserId = in_array('user_id', $cols, true);
$hasImponibile = in_array('imponibile', $cols, true);
$hasImportoNetto = in_array('importo_netto', $cols, true);
$hasIvaImporto = in_array('iva_importo', $cols, true);
$hasIva = in_array('iva', $cols, true);
$hasIvaPercentuale = in_array('iva_percentuale', $cols, true);
$hasTotale = in_array('totale', $cols, true);
$hasImportoTotale = in_array('importo_totale', $cols, true);
$hasFilePdf = in_array('file_pdf_path', $cols, true);
$hasFilePath = in_array('file_path', $cols, true);

$colCliente = $hasClienteId ? 'cliente_id' : ($hasUserId ? 'user_id' : 'cliente_id');
$colImponibile = $hasImponibile ? 'imponibile' : ($hasImportoNetto ? 'importo_netto' : 'imponibile');
$colIva = $hasIvaImporto ? 'iva_importo' : ($hasIva ? 'iva' : 'iva_importo');
$colTotale = $hasTotale ? 'totale' : ($hasImportoTotale ? 'importo_totale' : 'totale');
$colFile = $hasFilePdf ? 'file_pdf_path' : ($hasFilePath ? 'file_path' : 'file_pdf_path');

// Recupera dati fattura (solo se appartiene al cliente loggato)
$stmt = $pdo->prepare("
    SELECT
        f.id,
        f.numero_fattura,
        f.data_emissione,
        f.data_scadenza,
        " . ($hasAnno ? 'f.anno' : 'YEAR(f.data_emissione) AS anno') . ",
        " . ($hasMese ? 'f.mese' : 'MONTH(f.data_emissione) AS mese') . ",
        f.$colImponibile AS imponibile,
        " . ($hasIvaPercentuale ? 'f.iva_percentuale' : '22.00 AS iva_percentuale') . ",
        f.$colIva AS iva_importo,
        f.$colTotale AS totale,
        f.stato,
        " . (in_array('data_pagamento', $cols, true) ? 'f.data_pagamento' : 'NULL AS data_pagamento') . ",
        " . (in_array('metodo_pagamento', $cols, true) ? 'f.metodo_pagamento' : 'NULL AS metodo_pagamento') . ",
        " . (in_array('note', $cols, true) ? 'f.note' : 'NULL AS note') . ",
        f.$colFile AS file_pdf_path
    FROM fatture f
    WHERE f.id = :id AND f.$colCliente = :cliente_id
");
$stmt->execute(['id' => $fatturaId, 'cliente_id' => $clienteId]);
$fattura = $stmt->fetch();

if (!$fattura) {
    header('Location: /area-clienti/fatture.php');
    exit;
}

// Recupera righe fattura
$stmtRighe = $pdo->prepare("
    SELECT
        fr.id,
        fr.servizio_id,
        fr.descrizione,
        fr.quantita,
        fr.prezzo_unitario,
        fr.imponibile,
        fr.iva_percentuale,
        fr.iva_importo,
        fr.totale,
        fr.ordine,
        s.nome AS servizio_nome
    FROM fatture_righe fr
    LEFT JOIN servizi s ON fr.servizio_id = s.id
    WHERE fr.fattura_id = :fattura_id
    ORDER BY fr.ordine ASC, fr.id ASC
");
$stmtRighe->execute(['fattura_id' => $fatturaId]);
$righe = $stmtRighe->fetchAll();

// Verifica se esiste gateway pagamento attivo
$gatewayAttivo = false;
try {
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM payment_gateways_config WHERE attivo = TRUE');
  $stmt->execute();
  $gatewayAttivo = ((int)$stmt->fetchColumn()) > 0;
} catch (PDOException $e) {
  $gatewayAttivo = false;
}

$statoLabels = [
    'bozza' => 'Bozza',
    'emessa' => 'Emessa',
    'inviata' => 'Inviata',
    'pagata' => 'Pagata',
    'scaduta' => 'Scaduta',
    'annullata' => 'Annullata'
];

$statoClass = match($fattura['stato']) {
    'pagata' => 'success',
    'scaduta' => 'danger',
    'inviata', 'emessa' => 'info',
    'annullata' => 'default',
    default => 'default'
};

$isPagabile = in_array($fattura['stato'], ['emessa', 'inviata', 'scaduta'], true);
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fattura <?= htmlspecialchars($fattura['numero_fattura']) ?> - Area Clienti</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .detail-item {
      padding: 15px;
      background: var(--bg-secondary);
      border-radius: 8px;
      border-left: 3px solid var(--primary);
    }
    .detail-item label {
      display: block;
      font-size: 12px;
      color: var(--text-muted);
      margin-bottom: 5px;
      text-transform: uppercase;
      font-weight: 600;
    }
    .detail-item .value {
      font-size: 16px;
      font-weight: 600;
      color: var(--text);
    }
    .righe-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    .righe-table th,
    .righe-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    .righe-table th {
      background: var(--bg-secondary);
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      color: var(--text-muted);
    }
    .righe-table td {
      font-size: 14px;
    }
    .righe-table tr:hover {
      background: var(--bg-secondary);
    }
    .totali-section {
      display: flex;
      justify-content: flex-end;
      margin-top: 30px;
    }
    .totali-box {
      width: 100%;
      max-width: 400px;
      background: var(--bg-secondary);
      border-radius: 8px;
      padding: 20px;
    }
    .totali-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid var(--border);
    }
    .totali-row:last-child {
      border-bottom: none;
      font-size: 20px;
      font-weight: 700;
      margin-top: 10px;
      padding-top: 15px;
      border-top: 2px solid var(--primary);
    }
    .action-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 20px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <div style="margin-bottom: 20px;">
    <a href="/area-clienti/fatture.php" class="btn ghost small">← Torna alle fatture</a>
  </div>

  <section class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
      <h2 style="margin: 0;">📄 Fattura <?= htmlspecialchars($fattura['numero_fattura']) ?></h2>
      <span class="badge <?= $statoClass ?>" style="font-size: 14px; padding: 8px 16px;">
        <?= htmlspecialchars($statoLabels[$fattura['stato']] ?? $fattura['stato']) ?>
      </span>
    </div>

    <div class="detail-grid">
      <div class="detail-item">
        <label>Data Emissione</label>
        <div class="value"><?= date('d/m/Y', strtotime($fattura['data_emissione'])) ?></div>
      </div>

      <div class="detail-item">
        <label>Data Scadenza</label>
        <div class="value"><?= date('d/m/Y', strtotime($fattura['data_scadenza'])) ?></div>
      </div>

      <div class="detail-item">
        <label>Periodo</label>
        <div class="value">
          <?php
          $mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
          $meseIdx = (int)$fattura['mese'] - 1;
          echo $mesi[$meseIdx] ?? $fattura['mese'];
          echo ' ' . $fattura['anno'];
          ?>
        </div>
      </div>

      <?php if ($fattura['data_pagamento'] && $fattura['data_pagamento'] !== '0000-00-00'): ?>
      <div class="detail-item">
        <label>Data Pagamento</label>
        <div class="value"><?= date('d/m/Y', strtotime($fattura['data_pagamento'])) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($fattura['metodo_pagamento']): ?>
      <div class="detail-item">
        <label>Metodo Pagamento</label>
        <div class="value"><?= htmlspecialchars($fattura['metodo_pagamento']) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($fattura['note']): ?>
    <div style="margin: 20px 0; padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
      <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Note</label>
      <p style="margin: 0; white-space: pre-line;"><?= htmlspecialchars($fattura['note']) ?></p>
    </div>
    <?php endif; ?>

    <h3 style="margin-top: 30px; margin-bottom: 15px;">Dettaglio Servizi</h3>

    <?php if (empty($righe)): ?>
      <p class="muted">Nessuna voce di fattura presente.</p>
    <?php else: ?>
      <table class="righe-table">
        <thead>
          <tr>
            <th>Descrizione</th>
            <th style="text-align: center;">Quantità</th>
            <th style="text-align: right;">Prezzo Unit.</th>
            <th style="text-align: right;">Imponibile</th>
            <th style="text-align: center;">IVA %</th>
            <th style="text-align: right;">IVA €</th>
            <th style="text-align: right;">Totale</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($righe as $riga): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($riga['servizio_nome'] ?? $riga['descrizione']) ?></strong>
                <?php if ($riga['servizio_nome'] && $riga['descrizione'] !== $riga['servizio_nome']): ?>
                  <br><span class="muted small"><?= htmlspecialchars($riga['descrizione']) ?></span>
                <?php endif; ?>
              </td>
              <td style="text-align: center;"><?= number_format($riga['quantita'], 2, ',', '.') ?></td>
              <td style="text-align: right;">€<?= number_format($riga['prezzo_unitario'], 2, ',', '.') ?></td>
              <td style="text-align: right;">€<?= number_format($riga['imponibile'], 2, ',', '.') ?></td>
              <td style="text-align: center;"><?= number_format($riga['iva_percentuale'], 0) ?>%</td>
              <td style="text-align: right;">€<?= number_format($riga['iva_importo'], 2, ',', '.') ?></td>
              <td style="text-align: right;"><strong>€<?= number_format($riga['totale'], 2, ',', '.') ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div class="totali-section">
      <div class="totali-box">
        <div class="totali-row">
          <span>Imponibile:</span>
          <span>€<?= number_format($fattura['imponibile'], 2, ',', '.') ?></span>
        </div>
        <div class="totali-row">
          <span>IVA (<?= number_format($fattura['iva_percentuale'], 0) ?>%):</span>
          <span>€<?= number_format($fattura['iva_importo'], 2, ',', '.') ?></span>
        </div>
        <div class="totali-row">
          <span>TOTALE:</span>
          <span>€<?= number_format($fattura['totale'], 2, ',', '.') ?></span>
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <?php if ($fattura['file_pdf_path'] && file_exists(__DIR__ . '/' . $fattura['file_pdf_path'])): ?>
        <a href="/area-clienti/api/download-fattura.php?id=<?= $fatturaId ?>" class="btn" style="background: #6366f1; color: white; border-color: #6366f1;">
          📥 Scarica PDF
        </a>
      <?php endif; ?>

      <?php if ($isPagabile): ?>
        <?php if ($gatewayAttivo): ?>
          <a href="/area-clienti/paga-fattura.php?id=<?= $fatturaId ?>" class="btn" style="background: #10b981; color: white; border-color: #10b981;">
            💳 Paga Ora
          </a>
        <?php else: ?>
          <button class="btn" style="background: #10b981; color: white; border-color: #10b981; opacity: 0.5; cursor: not-allowed;" disabled title="Gateway pagamento non configurato">
            💳 Paga Ora
          </button>
        <?php endif; ?>
      <?php endif; ?>

      <a href="/area-clienti/fatture.php" class="btn ghost">
        📋 Tutte le Fatture
      </a>
    </div>
  </section>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
