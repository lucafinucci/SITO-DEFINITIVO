<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/fatture-settings.php';

$clienteId = $_SESSION['cliente_id'];

// Verifica che sia admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    header('Location: /area-clienti/denied.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$csrfToken = $_SESSION['csrf_token'] ?? '';
$fattureSettings = getFattureSettings($pdo);
$invioModalita = $fattureSettings['invio_modalita'];
$mostraSoloInviate = (int)$fattureSettings['mostra_cliente_solo_inviate'] === 1;

// Filtri
$annoFilter = (int)($_GET['anno'] ?? date('Y'));
$meseFilter = (int)($_GET['mese'] ?? 0);
$statoFilter = $_GET['stato'] ?? '';
$clienteFilter = (int)($_GET['cliente'] ?? 0);
$statiValidi = ['bozza', 'emessa', 'inviata', 'pagata', 'scaduta', 'annullata'];
if (!in_array($statoFilter, $statiValidi, true)) {
    $statoFilter = '';
}

// Paginazione
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = ['1=1'];
$params = [];

// Compatibilita schema: supporto tabelle fatture legacy
$stmt = $pdo->prepare("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'fatture'
      AND COLUMN_NAME IN (
        'anno', 'mese',
        'cliente_id', 'user_id',
        'imponibile', 'importo_netto',
        'iva_importo', 'iva',
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
$hasTotale = in_array('totale', $cols, true);
$hasImportoTotale = in_array('importo_totale', $cols, true);
$hasFilePdf = in_array('file_pdf_path', $cols, true);
$hasFilePath = in_array('file_path', $cols, true);

$colCliente = $hasClienteId ? 'cliente_id' : ($hasUserId ? 'user_id' : 'cliente_id');
$colImponibile = $hasImponibile ? 'imponibile' : ($hasImportoNetto ? 'importo_netto' : 'imponibile');
$colIva = $hasIvaImporto ? 'iva_importo' : ($hasIva ? 'iva' : 'iva_importo');
$colTotale = $hasTotale ? 'totale' : ($hasImportoTotale ? 'importo_totale' : 'totale');
$colFile = $hasFilePdf ? 'file_pdf_path' : ($hasFilePath ? 'file_path' : 'file_pdf_path');

if ($annoFilter) {
    $where[] = $hasAnno ? 'f.anno = :anno' : 'YEAR(f.data_emissione) = :anno';
    $params['anno'] = $annoFilter;
}

if ($meseFilter >= 1 && $meseFilter <= 12) {
    $where[] = $hasMese ? 'f.mese = :mese' : 'MONTH(f.data_emissione) = :mese';
    $params['mese'] = $meseFilter;
}

if ($statoFilter) {
    $where[] = 'f.stato = :stato';
    $params['stato'] = $statoFilter;
}

if ($clienteFilter > 0) {
    $where[] = "f.$colCliente = :cliente_id";
    $params['cliente_id'] = $clienteFilter;
}

$whereSql = implode(' AND ', $where);

// Conta totale
$stmt = $pdo->prepare("SELECT COUNT(*) FROM fatture f WHERE $whereSql");
$stmt->execute($params);
$totaleFatture = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($totaleFatture / $perPage));

// Recupera fatture
$stmt = $pdo->prepare("
    SELECT
        f.id,
        f.numero_fattura,
        f.data_emissione,
        f.data_scadenza,
        f.stato,
        f.$colCliente AS cliente_id,
        f.$colImponibile AS imponibile,
        f.$colIva AS iva_importo,
        f.$colTotale AS totale,
        f.$colFile AS file_pdf_path,
        " . ($hasAnno ? 'f.anno' : 'YEAR(f.data_emissione) AS anno') . ",
        " . ($hasMese ? 'f.mese' : 'MONTH(f.data_emissione) AS mese') . ",
        " . (in_array('iva_percentuale', $cols, true) ? 'f.iva_percentuale' : '22.00 AS iva_percentuale') . ",
        " . (in_array('data_pagamento', $cols, true) ? 'f.data_pagamento' : 'NULL AS data_pagamento') . ",
        " . (in_array('metodo_pagamento', $cols, true) ? 'f.metodo_pagamento' : 'NULL AS metodo_pagamento') . ",
        " . (in_array('note', $cols, true) ? 'f.note' : 'NULL AS note') . ",
        u.azienda,
        u.nome AS cliente_nome,
        u.cognome AS cliente_cognome,
        u.email AS cliente_email
    FROM fatture f
    JOIN utenti u ON f.$colCliente = u.id
    WHERE $whereSql
    ORDER BY f.data_emissione DESC, f.id DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue(':' . $key, $value, $type);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$fatture = $stmt->fetchAll();

// Statistiche totali (rispettano filtri)
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS totale,
        SUM(CASE WHEN stato = 'bozza' THEN 1 ELSE 0 END) AS bozze,
        SUM(CASE WHEN stato = 'emessa' THEN 1 ELSE 0 END) AS emesse,
        SUM(CASE WHEN stato = 'inviata' THEN 1 ELSE 0 END) AS inviate,
        SUM(CASE WHEN stato = 'pagata' THEN 1 ELSE 0 END) AS pagate,
        SUM(CASE WHEN stato = 'scaduta' THEN 1 ELSE 0 END) AS scadute,
        SUM($colImponibile) AS totale_imponibile,
        SUM($colTotale) AS totale_fatturato,
        SUM(CASE WHEN stato = 'pagata' THEN $colTotale ELSE 0 END) AS totale_incassato,
        SUM(CASE WHEN stato IN ('emessa', 'inviata', 'scaduta') THEN $colTotale ELSE 0 END) AS totale_da_incassare
    FROM fatture f
    WHERE $whereSql
");
$stmt->execute($params);
$stats = $stmt->fetch();

$baseParams = [
    'anno' => $annoFilter ?: '',
    'mese' => $meseFilter ?: '',
    'stato' => $statoFilter,
    'cliente' => $clienteFilter ?: ''
];

// Recupera lista clienti per filtro
$stmtClienti = $pdo->prepare("
    SELECT id, nome, cognome, azienda, email
    FROM utenti
    WHERE ruolo != 'admin'
    ORDER BY azienda ASC, cognome ASC
");
$stmtClienti->execute();
$listaClienti = $stmtClienti->fetchAll();
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Fatturazione - Admin</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .fatture-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }
    .filters select,
    .filters input {
      padding: 8px 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .fattura-card {
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
    }
    .fattura-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 16px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border);
    }
    .fattura-numero {
      font-size: 18px;
      font-weight: 700;
      color: #3b82f6;
      margin-bottom: 4px;
    }
    .badge-stato {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge-bozza { background: #6b7280; color: #fff; }
    .badge-emessa { background: #3b82f6; color: #fff; }
    .badge-inviata { background: #8b5cf6; color: #fff; }
    .badge-pagata { background: #10b981; color: #fff; }
    .badge-scaduta { background: #ef4444; color: #fff; }
    .badge-annullata { background: #1f2937; color: #9ca3af; }
    .fattura-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 16px;
    }
    .fattura-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--border);
    }
    .pagination {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-top: 16px;
      flex-wrap: wrap;
    }
    .pagination-links {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }
    .pagination-links .active {
      background: #8b5cf6;
      border-color: #8b5cf6;
      color: white;
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
      max-width: 500px;
      width: 90%;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .modal-header h3 {
      margin: 0;
    }
    .close-modal {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 24px;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
    }
    .close-modal:hover {
      color: white;
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 600;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .modal-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 24px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid var(--border);">
    <div>
      <h1 style="margin: 0 0 8px 0;">💶 Fatturazione</h1>
      <p class="muted">Gestione fatture clienti</p>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div style="display: flex; gap: 12px;">
        <a href="/area-clienti/admin/gestione-servizi.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          Servizi Clienti
        </a>
        <a href="/area-clienti/admin/richieste-addestramento.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          Richieste Addestramento
        </a>
        <a href="/area-clienti/admin/fatture.php"
           style="padding: 8px 16px; background: #8b5cf6; border: 1px solid #8b5cf6; border-radius: 8px; color: white; text-decoration: none; font-size: 14px;">
          Fatture
        </a>
        <a href="/area-clienti/admin/scadenzario.php"
           style="padding: 8px 16px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #a78bfa; text-decoration: none; font-size: 14px; transition: all 0.2s;">
          📅 Scadenzario
        </a>
      </div>
      <span class="badge" style="background: #8b5cf6;">Admin</span>
    </div>
  </div>

  <!-- Statistiche -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 30px;">
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Totale Fatture</p>
      <h2 style="margin: 0; color: #3b82f6;"><?= (int)$stats['totale'] ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Fatturato</p>
      <h2 style="margin: 0; color: #10b981;">€<?= number_format((float)$stats['totale_fatturato'], 0, ',', '.') ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Incassato</p>
      <h2 style="margin: 0; color: #10b981;">€<?= number_format((float)$stats['totale_incassato'], 0, ',', '.') ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Da incassare</p>
      <h2 style="margin: 0; color: #f59e0b;">€<?= number_format((float)$stats['totale_da_incassare'], 0, ',', '.') ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Pagate</p>
      <h2 style="margin: 0;"><?= (int)$stats['pagate'] ?></h2>
    </div>
    <div class="card" style="padding: 16px;">
      <p class="muted small" style="margin: 0 0 8px 0;">Scadute</p>
      <h2 style="margin: 0; color: #ef4444;"><?= (int)$stats['scadute'] ?></h2>
    </div>
  </div>

  <div class="card" style="padding: 20px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
      <div>
        <h3 style="margin: 0 0 6px 0;">Impostazioni invio fatture</h3>
        <p class="muted small" style="margin: 0;">
          Manuale = invio tramite pulsante. Automatico = invio quando la fattura passa a "Emessa".
        </p>
      </div>
      <button class="btn primary small" type="button" onclick="salvaImpostazioniFatture()">Salva impostazioni</button>
    </div>
    <div style="display: flex; gap: 24px; margin-top: 16px; flex-wrap: wrap;">
      <label style="display: flex; flex-direction: column; gap: 6px;">
        <span class="muted small">Modalita invio</span>
        <select id="fattureInvioModalita">
          <option value="manuale" <?= $invioModalita === 'manuale' ? 'selected' : '' ?>>Manuale</option>
          <option value="automatico" <?= $invioModalita === 'automatico' ? 'selected' : '' ?>>Automatico</option>
        </select>
      </label>
      <label style="display: flex; align-items: center; gap: 10px; margin-top: 22px;">
        <input type="checkbox" id="fattureMostraInviate" <?= $mostraSoloInviate ? 'checked' : '' ?>>
        <span class="muted small">Mostra al cliente solo dopo invio (stato "Inviata")</span>
      </label>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="fatture-toolbar">
    <button class="btn primary" onclick="mostraModalGenerazione()">
      ➕ Genera Fatture Mensili
    </button>

    <!-- Filtri -->
    <form method="get" class="filters">
      <select name="anno">
        <option value="">Tutti gli anni</option>
        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
          <option value="<?= $y ?>" <?= $annoFilter === $y ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
      <select name="mese">
        <option value="">Tutti i mesi</option>
        <?php
        $mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
                 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
        for ($m = 1; $m <= 12; $m++):
        ?>
          <option value="<?= $m ?>" <?= $meseFilter === $m ? 'selected' : '' ?>><?= $mesi[$m - 1] ?></option>
        <?php endfor; ?>
      </select>
      <select name="stato">
        <option value="">Tutti gli stati</option>
        <option value="bozza" <?= $statoFilter === 'bozza' ? 'selected' : '' ?>>Bozza</option>
        <option value="emessa" <?= $statoFilter === 'emessa' ? 'selected' : '' ?>>Emessa</option>
        <option value="inviata" <?= $statoFilter === 'inviata' ? 'selected' : '' ?>>Inviata</option>
        <option value="pagata" <?= $statoFilter === 'pagata' ? 'selected' : '' ?>>Pagata</option>
        <option value="scaduta" <?= $statoFilter === 'scaduta' ? 'selected' : '' ?>>Scaduta</option>
        <option value="annullata" <?= $statoFilter === 'annullata' ? 'selected' : '' ?>>Annullata</option>
      </select>
      <select name="cliente">
        <option value="">Tutti i clienti</option>
        <?php foreach ($listaClienti as $cli): ?>
          <option value="<?= $cli['id'] ?>" <?= $clienteFilter === (int)$cli['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cli['azienda'] ?: ($cli['nome'] . ' ' . $cli['cognome'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn ghost small" type="submit">Filtra</button>
      <a class="btn ghost small" href="/area-clienti/admin/fatture.php">Reset</a>
    </form>
  </div>

  <!-- Lista Fatture -->
  <section class="card">
    <h3>📋 Fatture</h3>

    <?php if (empty($fatture)): ?>
      <p class="muted" style="text-align: center; padding: 40px 0;">Nessuna fattura presente</p>
    <?php else: ?>
      <?php foreach ($fatture as $fattura): ?>
        <div class="fattura-card">
          <div class="fattura-header">
            <div>
              <div class="fattura-numero"><?= htmlspecialchars($fattura['numero_fattura']) ?></div>
              <p class="muted small" style="margin: 0;">
                <strong><?= htmlspecialchars($fattura['azienda']) ?></strong> •
                <?= htmlspecialchars($fattura['cliente_nome'] . ' ' . $fattura['cliente_cognome']) ?>
              </p>
            </div>
            <span class="badge-stato badge-<?= $fattura['stato'] ?>">
              <?= ucfirst($fattura['stato']) ?>
            </span>
          </div>

          <div class="fattura-grid">
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Periodo</p>
              <p style="margin: 0; font-weight: 600;"><?= sprintf('%02d/%d', $fattura['mese'], $fattura['anno']) ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Data Emissione</p>
              <p style="margin: 0; font-weight: 600;"><?= date('d/m/Y', strtotime($fattura['data_emissione'])) ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Scadenza</p>
              <p style="margin: 0; font-weight: 600;"><?= date('d/m/Y', strtotime($fattura['data_scadenza'])) ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Imponibile</p>
              <p style="margin: 0; font-weight: 600;">€<?= number_format((float)$fattura['imponibile'], 2, ',', '.') ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">IVA (<?= number_format((float)$fattura['iva_percentuale'], 0, ',', '.') ?>%)</p>
              <p style="margin: 0; font-weight: 600;">€<?= number_format((float)$fattura['iva_importo'], 2, ',', '.') ?></p>
            </div>
            <div>
              <p class="muted small" style="margin: 0 0 4px 0;">Totale</p>
              <p style="margin: 0; font-weight: 700; font-size: 18px; color: #10b981;">€<?= number_format((float)$fattura['totale'], 2, ',', '.') ?></p>
            </div>
          </div>

          <?php if ($fattura['note']): ?>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
              <p class="muted small" style="margin: 0 0 4px 0;">Note:</p>
              <p style="margin: 0;"><?= nl2br(htmlspecialchars($fattura['note'])) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($fattura['data_pagamento']): ?>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
              <p class="muted small" style="margin: 0;">
                Pagata il <?= date('d/m/Y', strtotime($fattura['data_pagamento'])) ?>
                <?php if ($fattura['metodo_pagamento']): ?>
                  via <?= htmlspecialchars($fattura['metodo_pagamento']) ?>
                <?php endif; ?>
              </p>
            </div>
          <?php endif; ?>

          <div class="fattura-actions">
            <?php if ($fattura['stato'] === 'bozza'): ?>
              <button class="btn ghost small" onclick="aggiornaStato(<?= $fattura['id'] ?>, 'emessa')">
                📤 Segna come Emessa
              </button>
            <?php endif; ?>

            <?php if ($fattura['stato'] === 'emessa'): ?>
              <button class="btn ghost small" onclick="aggiornaStato(<?= $fattura['id'] ?>, 'inviata')">
                ✉️ Segna come Inviata
              </button>
            <?php endif; ?>

            <?php if (in_array($fattura['stato'], ['emessa', 'inviata', 'scaduta'], true)): ?>
              <?php if (false): ?>
              <button class="btn success small" onclick="location.href='/area-clienti/paga-fattura.php?id=<?= $fattura['id'] ?>'" style="background: #10b981; color: white; border-color: #10b981;">
                💳 Paga Ora
              </button>
              <?php endif; ?>
              <button class="btn ghost small" onclick="mostraModalPagamento(<?= $fattura['id'] ?>, '<?= htmlspecialchars($fattura['numero_fattura']) ?>', <?= $fattura['totale'] ?>)">
                ✓ Segna come Pagata
              </button>
            <?php endif; ?>

            <button class="btn ghost small" onclick="location.href='/area-clienti/admin/fattura-dettaglio.php?id=<?= $fattura['id'] ?>'">
              👁️ Visualizza Dettaglio
            </button>

            <?php if ($fattura['file_pdf_path']): ?>
              <a class="btn ghost small" href="<?= htmlspecialchars($fattura['file_pdf_path']) ?>" target="_blank">
                📄 Scarica PDF
              </a>
            <?php else: ?>
              <button class="btn ghost small" onclick="generaPDF(<?= $fattura['id'] ?>)">
                📄 Genera PDF
              </button>
            <?php endif; ?>

            <?php if ($fattura['stato'] === 'bozza'): ?>
              <button class="btn ghost small" onclick="eliminaFattura(<?= $fattura['id'] ?>, '<?= htmlspecialchars($fattura['numero_fattura']) ?>')" style="color: #ef4444; border-color: #ef4444;">
                🗑️ Elimina
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <!-- Paginazione -->
  <div class="pagination">
    <span class="muted small">Pagina <?= $page ?> di <?= $totalPages ?> / Totale: <?= $totaleFatture ?></span>
    <div class="pagination-links">
      <?php
      $params = $baseParams;
      if ($page > 1) {
        $params['page'] = $page - 1;
        echo '<a class="btn ghost small" href="/area-clienti/admin/fatture.php?' . http_build_query($params) . '">< Prec</a>';
      }
      $startPage = max(1, $page - 2);
      $endPage = min($totalPages, $page + 2);
      for ($p = $startPage; $p <= $endPage; $p++) {
        $params['page'] = $p;
        $active = $p === $page ? ' active' : '';
        echo '<a class="btn ghost small' . $active . '" href="/area-clienti/admin/fatture.php?' . http_build_query($params) . '">' . $p . '</a>';
      }
      if ($page < $totalPages) {
        $params['page'] = $page + 1;
        echo '<a class="btn ghost small" href="/area-clienti/admin/fatture.php?' . http_build_query($params) . '">Succ ></a>';
      }
      ?>
    </div>
  </div>

</main>

<!-- Modal Generazione Fatture -->
<div id="modalGenerazione" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Genera Fatture Mensili</h3>
      <button class="close-modal" onclick="chiudiModalGenerazione()">×</button>
    </div>

    <form id="formGenerazione">
      <div class="form-group">
        <label>Anno</label>
        <select id="genAnno" name="anno">
          <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Mese</label>
        <select id="genMese" name="mese">
          <?php
          $mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
                   'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
          for ($m = 1; $m <= 12; $m++):
          ?>
            <option value="<?= $m ?>"><?= $mesi[$m - 1] ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Modalità</label>
        <select id="genModalita" name="modalita">
          <option value="auto">Genera solo fatture mancanti</option>
          <option value="force">Rigenera tutte (elimina bozze esistenti)</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiModalGenerazione()">Annulla</button>
        <button type="submit" class="btn primary">✓ Genera Fatture</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Pagamento -->
<div id="modalPagamento" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Registra Pagamento</h3>
      <button class="close-modal" onclick="chiudiModalPagamento()">×</button>
    </div>

    <form id="formPagamento">
      <input type="hidden" id="pagFatturaId" name="fattura_id">

      <div class="form-group">
        <label>Fattura</label>
        <input type="text" id="pagNumeroFattura" readonly style="background: #0f172a; color: var(--muted);">
      </div>

      <div class="form-group">
        <label>Importo</label>
        <input type="number" id="pagImporto" name="importo" step="0.01" required>
      </div>

      <div class="form-group">
        <label>Data Pagamento</label>
        <input type="date" id="pagData" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="form-group">
        <label>Metodo di Pagamento</label>
        <select id="pagMetodo" name="metodo_pagamento" required>
          <option value="Bonifico bancario">Bonifico bancario</option>
          <option value="Carta di credito">Carta di credito</option>
          <option value="PayPal">PayPal</option>
          <option value="Stripe">Stripe</option>
          <option value="Contanti">Contanti</option>
          <option value="Assegno">Assegno</option>
          <option value="Altro">Altro</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiModalPagamento()">Annulla</button>
        <button type="submit" class="btn primary">✓ Registra Pagamento</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';

async function salvaImpostazioniFatture() {
  const invioModalita = document.getElementById('fattureInvioModalita').value;
  const mostraSoloInviate = document.getElementById('fattureMostraInviate').checked;

  try {
    const response = await fetch('/area-clienti/api/fatture-settings.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        invio_modalita: invioModalita,
        mostra_cliente_solo_inviate: mostraSoloInviate
      })
    });

    const result = await response.json();
    if (result.success) {
      alert('✅ Impostazioni salvate');
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile salvare'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
}
const modalGenerazione = document.getElementById('modalGenerazione');
const modalPagamento = document.getElementById('modalPagamento');

// Imposta mese precedente di default
const now = new Date();
const mesePrecedente = now.getMonth(); // 0-11
const annoPrecedente = mesePrecedente === 0 ? now.getFullYear() - 1 : now.getFullYear();
document.getElementById('genMese').value = mesePrecedente === 0 ? 12 : mesePrecedente;
document.getElementById('genAnno').value = annoPrecedente;

function mostraModalGenerazione() {
  modalGenerazione.classList.add('show');
}

function chiudiModalGenerazione() {
  modalGenerazione.classList.remove('show');
}

function mostraModalPagamento(fatturaId, numeroFattura, importo) {
  document.getElementById('pagFatturaId').value = fatturaId;
  document.getElementById('pagNumeroFattura').value = numeroFattura;
  document.getElementById('pagImporto').value = importo.toFixed(2);
  modalPagamento.classList.add('show');
}

function chiudiModalPagamento() {
  modalPagamento.classList.remove('show');
}

modalGenerazione.addEventListener('click', function(e) {
  if (e.target === modalGenerazione) chiudiModalGenerazione();
});

modalPagamento.addEventListener('click', function(e) {
  if (e.target === modalPagamento) chiudiModalPagamento();
});

document.getElementById('formGenerazione').addEventListener('submit', async function(e) {
  e.preventDefault();

  const anno = parseInt(document.getElementById('genAnno').value);
  const mese = parseInt(document.getElementById('genMese').value);
  const modalita = document.getElementById('genModalita').value;

  if (!confirm(`Generare fatture per ${mese}/${anno}?`)) {
    return;
  }

  try {
    const response = await fetch('/area-clienti/api/genera-fatture-mensili.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ anno, mese, modalita })
    });

    const result = await response.json();

    if (result.success) {
      alert(`✅ ${result.message}\n\nPeriodo: ${result.periodo}\nGenerate: ${result.generate}\nSkippate: ${result.skippate}`);
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile generare fatture'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
});

document.getElementById('formPagamento').addEventListener('submit', async function(e) {
  e.preventDefault();

  const fatturaId = parseInt(document.getElementById('pagFatturaId').value);
  const importo = parseFloat(document.getElementById('pagImporto').value);
  const dataPagamento = document.getElementById('pagData').value;
  const metodoPagamento = document.getElementById('pagMetodo').value;

  try {
    const response = await fetch('/area-clienti/api/fatture.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        action: 'mark-paid',
        fattura_id: fatturaId,
        importo,
        data_pagamento: dataPagamento,
        metodo_pagamento: metodoPagamento
      })
    });

    const result = await response.json();

    if (result.success) {
      alert('✅ Pagamento registrato con successo!');
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile registrare il pagamento'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
});

async function aggiornaStato(fatturaId, nuovoStato) {
  if (!confirm(`Cambiare stato in "${nuovoStato}"?`)) return;

  try {
    const response = await fetch('/area-clienti/api/fatture.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ action: 'update-status', fattura_id: fatturaId, stato: nuovoStato })
    });

    const result = await response.json();

    if (result.success) {
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile aggiornare lo stato'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
}

async function eliminaFattura(fatturaId, numeroFattura) {
  if (!confirm(`Eliminare definitivamente la fattura ${numeroFattura}?`)) return;

  try {
    const response = await fetch('/area-clienti/api/fatture.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ action: 'delete', fattura_id: fatturaId })
    });

    const result = await response.json();

    if (result.success) {
      alert('✅ Fattura eliminata');
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile eliminare la fattura'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
}

function generaPDF(fatturaId) {
  // Apri il PDF in una nuova finestra
  window.open('/area-clienti/api/genera-pdf-fattura.php?id=' + fatturaId, '_blank');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
