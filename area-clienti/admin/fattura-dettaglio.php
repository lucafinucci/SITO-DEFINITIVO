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

$fatturaId = (int)($_GET['id'] ?? 0);
if ($fatturaId <= 0) {
    header('Location: /area-clienti/admin/fatture.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$csrfToken = $_SESSION['csrf_token'] ?? '';

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

// Recupera dati fattura
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
        f.$colFile AS file_pdf_path,
        u.id AS cliente_id,
        u.azienda,
        u.nome AS cliente_nome,
        u.cognome AS cliente_cognome,
        u.email AS cliente_email,
        u.telefono AS cliente_telefono
    FROM fatture f
    JOIN utenti u ON f.$colCliente = u.id
    WHERE f.id = :id
");
$stmt->execute(['id' => $fatturaId]);
$fattura = $stmt->fetch();

if (!$fattura) {
    header('Location: /area-clienti/admin/fatture.php');
    exit;
}

$fatturaStato = strtolower(trim((string)$fattura['stato']));
$pagamentoVal = isset($fattura['data_pagamento']) ? trim((string)$fattura['data_pagamento']) : '';
$haPagamento = $pagamentoVal !== '' && $pagamentoVal !== '0000-00-00' && $pagamentoVal !== '0000-00-00 00:00:00';
$haPagamentoRegistrato = false;
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'fatture_pagamenti'
");
$stmt->execute();
if ((int)$stmt->fetchColumn() > 0) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM fatture_pagamenti WHERE fattura_id = :id');
    $stmt->execute(['id' => $fatturaId]);
    $haPagamentoRegistrato = (int)$stmt->fetchColumn() > 0;
}
$isEditable = !($fatturaStato === 'pagata' || $haPagamento || $haPagamentoRegistrato);

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

// Recupera lista servizi per dropdown
$stmtServizi = $pdo->prepare("SELECT id, nome, codice FROM servizi WHERE attivo = 1 ORDER BY nome ASC");
$stmtServizi->execute();
$servizi = $stmtServizi->fetchAll();
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Dettaglio Fattura <?= htmlspecialchars($fattura['numero_fattura']) ?> - Admin</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .badge-stato {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }
    .badge-bozza { background: #6b7280; color: #fff; }
    .badge-emessa { background: #3b82f6; color: #fff; }
    .badge-inviata { background: #8b5cf6; color: #fff; }
    .badge-pagata { background: #10b981; color: #fff; }
    .badge-scaduta { background: #ef4444; color: #fff; }
    .badge-annullata { background: #1f2937; color: #9ca3af; }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .info-box {
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
    }

    .info-box h4 {
      margin: 0 0 12px 0;
      color: var(--muted);
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .info-box p {
      margin: 4px 0;
      font-size: 14px;
    }

    .righe-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }

    .righe-table th {
      background: #0f172a;
      padding: 12px;
      text-align: left;
      font-size: 12px;
      text-transform: uppercase;
      color: var(--muted);
      font-weight: 600;
      border-bottom: 2px solid var(--border);
    }

    .righe-table td {
      padding: 12px;
      border-bottom: 1px solid var(--border);
    }

    .righe-table tbody tr:hover {
      background: rgba(139, 92, 246, 0.05);
    }

    .righe-table .text-right {
      text-align: right;
    }

    .righe-table .actions {
      display: flex;
      gap: 8px;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal.show {
      display: flex;
    }

    .modal-content {
      background: #1e293b;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 30px;
      max-width: 700px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid var(--border);
    }

    .modal-header h3 {
      margin: 0;
    }

    .close-modal {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 28px;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
      line-height: 1;
    }

    .close-modal:hover {
      color: white;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 600;
      color: var(--muted);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .modal-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
    }

    .totali-box {
      background: #0f172a;
      border: 2px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }

    .totali-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid var(--border);
    }

    .totali-row:last-child {
      border-bottom: none;
      font-size: 18px;
      font-weight: 700;
      color: #10b981;
      padding-top: 16px;
      margin-top: 8px;
      border-top: 2px solid var(--border);
    }

    .icon-btn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 6px 8px;
      font-size: 16px;
      border-radius: 6px;
      transition: all 0.2s;
    }

    .icon-btn:hover {
      background: rgba(139, 92, 246, 0.2);
    }

    .icon-btn.danger:hover {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid var(--border);">
    <div>
      <h1 style="margin: 0 0 8px 0;">📄 Fattura <?= htmlspecialchars($fattura['numero_fattura']) ?></h1>
      <p class="muted">Gestione dettaglio fattura</p>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <span class="badge-stato badge-<?= $fattura['stato'] ?>">
        <?= ucfirst($fattura['stato']) ?>
      </span>
      <a href="/area-clienti/admin/fatture.php" class="btn ghost">
        ← Torna alle fatture
      </a>
      <span class="badge" style="background: #8b5cf6;">Admin</span>
    </div>
  </div>

  <!-- Informazioni Cliente e Fattura -->
  <div class="info-grid">
    <div class="info-box">
      <h4>Cliente</h4>
      <p><strong><?= htmlspecialchars($fattura['azienda']) ?></strong></p>
      <p><?= htmlspecialchars($fattura['cliente_nome'] . ' ' . $fattura['cliente_cognome']) ?></p>
      <p class="muted"><?= htmlspecialchars($fattura['cliente_email']) ?></p>
      <?php if ($fattura['cliente_telefono']): ?>
        <p class="muted"><?= htmlspecialchars($fattura['cliente_telefono']) ?></p>
      <?php endif; ?>
    </div>

    <div class="info-box">
      <h4>Dati Fattura</h4>
      <p><strong>Periodo:</strong> <?= sprintf('%02d/%d', $fattura['mese'], $fattura['anno']) ?></p>
      <p><strong>Emissione:</strong> <?= date('d/m/Y', strtotime($fattura['data_emissione'])) ?></p>
      <p><strong>Scadenza:</strong> <?= date('d/m/Y', strtotime($fattura['data_scadenza'])) ?></p>
      <?php if ($fattura['data_pagamento']): ?>
        <p><strong>Pagata il:</strong> <?= date('d/m/Y', strtotime($fattura['data_pagamento'])) ?></p>
        <?php if ($fattura['metodo_pagamento']): ?>
          <p><strong>Metodo:</strong> <?= htmlspecialchars($fattura['metodo_pagamento']) ?></p>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="info-box">
      <h4>Importi</h4>
      <p><strong>Imponibile:</strong> €<?= number_format((float)$fattura['imponibile'], 2, ',', '.') ?></p>
      <p><strong>IVA (<?= number_format((float)$fattura['iva_percentuale'], 0) ?>%):</strong> €<?= number_format((float)$fattura['iva_importo'], 2, ',', '.') ?></p>
      <p style="font-size: 16px; font-weight: 700; color: #10b981; margin-top: 8px;">
        <strong>Totale:</strong> €<?= number_format((float)$fattura['totale'], 2, ',', '.') ?>
      </p>
    </div>
  </div>

  <?php if ($fattura['note']): ?>
    <div class="card" style="margin-bottom: 30px;">
      <h4 style="margin: 0 0 12px 0;">Note</h4>
      <p style="margin: 0; color: var(--muted);"><?= nl2br(htmlspecialchars($fattura['note'])) ?></p>
    </div>
  <?php endif; ?>

  <!-- Righe Fattura -->
  <section class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0;">📋 Voci di Fattura</h3>
      <?php if ($isEditable): ?>
        <button class="btn primary" onclick="apriModalAggiungiRiga()">
          ➕ Aggiungi Voce
        </button>
      <?php else: ?>
        <span class="muted small">Fattura pagata: modifiche disabilitate.</span>
      <?php endif; ?>
    </div>

    <?php if (empty($righe)): ?>
      <p class="muted" style="text-align: center; padding: 40px 0;">
        Nessuna voce presente. Clicca su "Aggiungi Voce" per iniziare.
      </p>
    <?php else: ?>
      <table class="righe-table">
        <thead>
          <tr>
            <th style="width: 40px;">#</th>
            <th>Descrizione</th>
            <th class="text-right" style="width: 100px;">Qtà</th>
            <th class="text-right" style="width: 120px;">Prezzo Unit.</th>
            <th class="text-right" style="width: 120px;">Imponibile</th>
            <th class="text-right" style="width: 80px;">IVA %</th>
            <th class="text-right" style="width: 120px;">Totale</th>
            <th style="width: 100px;">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($righe as $idx => $riga): ?>
            <tr>
              <td><?= $idx + 1 ?></td>
              <td>
                <strong><?= htmlspecialchars($riga['descrizione']) ?></strong>
                <?php if ($riga['servizio_nome']): ?>
                  <br><span class="muted small"><?= htmlspecialchars($riga['servizio_nome']) ?></span>
                <?php endif; ?>
              </td>
              <td class="text-right"><?= number_format((float)$riga['quantita'], 2, ',', '.') ?></td>
              <td class="text-right">€<?= number_format((float)$riga['prezzo_unitario'], 2, ',', '.') ?></td>
              <td class="text-right">€<?= number_format((float)$riga['imponibile'], 2, ',', '.') ?></td>
              <td class="text-right"><?= number_format((float)$riga['iva_percentuale'], 0) ?>%</td>
              <td class="text-right"><strong>€<?= number_format((float)$riga['totale'], 2, ',', '.') ?></strong></td>
              <td>
                <?php if ($isEditable): ?>
                  <div class="actions">
                    <button class="icon-btn" onclick="apriModalModificaRiga(<?= htmlspecialchars(json_encode($riga), ENT_QUOTES, 'UTF-8') ?>)" title="Modifica">
                      ✏️
                    </button>
                    <button class="icon-btn danger" onclick="eliminaRiga(<?= $riga['id'] ?>)" title="Elimina">
                      🗑️
                    </button>
                  </div>
                <?php else: ?>
                  <span class="muted small">Solo lettura</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Totali -->
      <div class="totali-box">
        <div class="totali-row">
          <span>Imponibile</span>
          <span id="totaleImponibile">€<?= number_format((float)$fattura['imponibile'], 2, ',', '.') ?></span>
        </div>
        <div class="totali-row">
          <span>IVA (<?= number_format((float)$fattura['iva_percentuale'], 0) ?>%)</span>
          <span id="totaleIva">€<?= number_format((float)$fattura['iva_importo'], 2, ',', '.') ?></span>
        </div>
        <div class="totali-row">
          <span>TOTALE</span>
          <span id="totaleFattura">€<?= number_format((float)$fattura['totale'], 2, ',', '.') ?></span>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <!-- Azioni -->
  <div style="display: flex; gap: 12px; margin-top: 30px; flex-wrap: wrap;">
    <?php if ($fattura['file_pdf_path']): ?>
      <a class="btn ghost" href="<?= htmlspecialchars($fattura['file_pdf_path']) ?>" target="_blank">
        📄 Scarica PDF
      </a>
    <?php else: ?>
      <button class="btn ghost" onclick="generaPDF()">
        📄 Genera PDF
      </button>
    <?php endif; ?>

    <button class="btn ghost" onclick="inviaEmail()">
      📧 Invia per Email
    </button>

    <a href="/area-clienti/admin/fatture.php" class="btn ghost">
      ← Torna alle fatture
    </a>
  </div>

</main>

<!-- Modal Aggiungi/Modifica Riga -->
<div id="modalRiga" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalRigaTitolo">Aggiungi Voce</h3>
      <button class="close-modal" onclick="chiudiModalRiga()">×</button>
    </div>

    <form id="formRiga">
      <input type="hidden" id="rigaId" name="riga_id">
      <input type="hidden" id="fatturaId" name="fattura_id" value="<?= $fatturaId ?>">

      <div class="form-group full-width">
        <label>Servizio (opzionale)</label>
        <select id="servizioId" name="servizio_id">
          <option value="">-- Nessun servizio --</option>
          <?php foreach ($servizi as $serv): ?>
            <option value="<?= $serv['id'] ?>"><?= htmlspecialchars($serv['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group full-width">
        <label>Descrizione *</label>
        <textarea id="descrizione" name="descrizione" required></textarea>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label>Quantità *</label>
          <input type="number" id="quantita" name="quantita" step="0.01" value="1.00" required>
        </div>

        <div class="form-group">
          <label>Prezzo Unitario (€) *</label>
          <input type="number" id="prezzoUnitario" name="prezzo_unitario" step="0.01" value="0.00" required>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label>IVA (%)</label>
          <input type="number" id="ivaPercentuale" name="iva_percentuale" step="0.01" value="22.00" required>
        </div>

        <div class="form-group">
          <label>Ordinamento</label>
          <input type="number" id="ordine" name="ordine" value="0">
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiModalRiga()">Annulla</button>
        <button type="submit" class="btn primary" id="btnSalvaRiga">✓ Salva</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';
const fatturaId = <?= $fatturaId ?>;
const fatturaModificabile = <?= $isEditable ? 'true' : 'false' ?>;
const modalRiga = document.getElementById('modalRiga');

function apriModalAggiungiRiga() {
  if (!fatturaModificabile) {
    alert('Fattura pagata: modifiche non consentite.');
    return;
  }
  document.getElementById('modalRigaTitolo').textContent = 'Aggiungi Voce';
  document.getElementById('formRiga').reset();
  document.getElementById('rigaId').value = '';
  document.getElementById('fatturaId').value = fatturaId;
  document.getElementById('quantita').value = '1.00';
  document.getElementById('prezzoUnitario').value = '0.00';
  document.getElementById('ivaPercentuale').value = '22.00';
  document.getElementById('ordine').value = '0';
  modalRiga.classList.add('show');
}

function apriModalModificaRiga(riga) {
  if (!fatturaModificabile) {
    alert('Fattura pagata: modifiche non consentite.');
    return;
  }
  document.getElementById('modalRigaTitolo').textContent = 'Modifica Voce';
  document.getElementById('rigaId').value = riga.id;
  document.getElementById('fatturaId').value = fatturaId;
  document.getElementById('servizioId').value = riga.servizio_id || '';
  document.getElementById('descrizione').value = riga.descrizione;
  document.getElementById('quantita').value = riga.quantita;
  document.getElementById('prezzoUnitario').value = riga.prezzo_unitario;
  document.getElementById('ivaPercentuale').value = riga.iva_percentuale;
  document.getElementById('ordine').value = riga.ordine;
  modalRiga.classList.add('show');
}

function chiudiModalRiga() {
  modalRiga.classList.remove('show');
}

modalRiga.addEventListener('click', function(e) {
  if (e.target === modalRiga) chiudiModalRiga();
});

document.getElementById('formRiga').addEventListener('submit', async function(e) {
  e.preventDefault();

  if (!fatturaModificabile) {
    alert('Fattura pagata: modifiche non consentite.');
    return;
  }

  const rigaId = document.getElementById('rigaId').value;
  const data = {
    action: rigaId ? 'update' : 'create',
    riga_id: rigaId ? parseInt(rigaId) : null,
    fattura_id: parseInt(document.getElementById('fatturaId').value),
    servizio_id: document.getElementById('servizioId').value ? parseInt(document.getElementById('servizioId').value) : null,
    descrizione: document.getElementById('descrizione').value,
    quantita: parseFloat(document.getElementById('quantita').value),
    prezzo_unitario: parseFloat(document.getElementById('prezzoUnitario').value),
    iva_percentuale: parseFloat(document.getElementById('ivaPercentuale').value),
    ordine: parseInt(document.getElementById('ordine').value)
  };

  try {
    const response = await fetch('/area-clienti/api/fatture-righe.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.success) {
      alert('✅ Voce salvata con successo!');
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile salvare la voce'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
});

async function eliminaRiga(rigaId) {
  if (!fatturaModificabile) {
    alert('Fattura pagata: modifiche non consentite.');
    return;
  }
  if (!confirm('Eliminare questa voce dalla fattura?')) return;

  try {
    const response = await fetch('/area-clienti/api/fatture-righe.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        action: 'delete',
        riga_id: rigaId
      })
    });

    const result = await response.json();

    if (result.success) {
      alert('✅ Voce eliminata');
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile eliminare la voce'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
}

function generaPDF() {
  // Apri il PDF in una nuova finestra
  window.open('/area-clienti/api/genera-pdf-fattura.php?id=' + fatturaId, '_blank');
}

async function inviaEmail() {
  if (!confirm('Inviare la fattura via email al cliente?')) return;

  try {
    const response = await fetch('/area-clienti/api/invia-fattura-email.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        fattura_id: fatturaId
      })
    });

    const result = await response.json();

    if (result.success) {
      alert('✓ ' + result.message);
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile inviare l\'email'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
