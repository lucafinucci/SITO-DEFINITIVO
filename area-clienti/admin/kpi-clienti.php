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

$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>KPI Clienti Document Intelligence - Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .kpi-table th {
      background: #0f172a;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      border-bottom: 2px solid var(--border);
      font-size: 13px;
      color: var(--muted);
    }
    .kpi-table td {
      padding: 14px 12px;
      border-bottom: 1px solid var(--border);
    }
    .kpi-table tr:hover {
      background: rgba(34, 211, 238, 0.05);
    }
    .cliente-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .cliente-nome {
      font-weight: 600;
      font-size: 14px;
    }
    .cliente-azienda {
      color: var(--accent1);
      font-size: 13px;
    }
    .cliente-email {
      color: var(--muted);
      font-size: 12px;
    }
    .kpi-value {
      font-size: 18px;
      font-weight: 700;
      background: linear-gradient(90deg, var(--accent1), var(--accent2));
      -webkit-background-clip: text;
      color: transparent;
    }
    .kpi-label {
      font-size: 11px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 4px;
    }
    .api-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }
    .api-status.online {
      background: rgba(16, 185, 129, 0.2);
      color: #10b981;
    }
    .api-status.offline {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
    }
    .loading-spinner {
      text-align: center;
      padding: 60px 20px;
    }
    .loading-spinner::before {
      content: '';
      display: inline-block;
      width: 40px;
      height: 40px;
      border: 4px solid rgba(34, 211, 238, 0.2);
      border-top-color: var(--accent1);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .summary-card {
      padding: 24px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 12px;
      color: white;
    }
    .summary-card.variant-2 {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .summary-card.variant-3 {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .summary-card.variant-4 {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .summary-number {
      font-size: 36px;
      font-weight: 700;
      margin: 8px 0;
    }
    .summary-label {
      font-size: 13px;
      opacity: 0.9;
    }
    .filters {
      display: flex;
      gap: 12px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .filters input, .filters select {
      padding: 10px 14px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .expand-details {
      cursor: pointer;
      color: var(--accent1);
      font-size: 12px;
      text-decoration: underline;
    }
    .details-row {
      display: none;
      background: #0a0f1e;
    }
    .details-row.show {
      display: table-row;
    }
    .details-content {
      padding: 20px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
    }
    .detail-box {
      padding: 16px;
      background: #0f172a;
      border-radius: 8px;
      border: 1px solid var(--border);
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">

  <div style="margin-bottom: 20px;">
    <a href="/area-clienti/admin/gestione-servizi.php" style="color: var(--accent1);">← Torna a Gestione Servizi</a>
  </div>

  <section class="card">
    <div class="card-header">
      <div>
        <h1 style="margin: 0 0 8px 0;">📊 KPI Document Intelligence - Tutti i Clienti</h1>
        <p class="muted">Monitoraggio centralizzato dei dati provenienti dall'API della webapp</p>
      </div>
      <button class="btn primary" onclick="refreshData()">🔄 Aggiorna</button>
    </div>

    <!-- Summary Cards -->
    <div id="summary-section" style="display: none;">
      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-label">Clienti Attivi</div>
          <div class="summary-number" id="summary-clienti">0</div>
        </div>
        <div class="summary-card variant-2">
          <div class="summary-label">Documenti Totali (mese corrente)</div>
          <div class="summary-number" id="summary-documenti">0</div>
        </div>
        <div class="summary-card variant-3">
          <div class="summary-label">Pagine Analizzate (mese corrente)</div>
          <div class="summary-number" id="summary-pagine">0</div>
        </div>
        <div class="summary-card variant-4">
          <div class="summary-label">API Online</div>
          <div class="summary-number" id="summary-api-online">0</div>
        </div>
      </div>
    </div>

    <!-- Filtri -->
    <div class="filters" style="margin-top: 30px;">
      <input type="text" id="search-input" placeholder="🔍 Cerca per nome, azienda, email..." style="flex: 1; min-width: 250px;">
      <select id="api-filter">
        <option value="all">Tutti i clienti</option>
        <option value="online">Solo API online</option>
        <option value="offline">Solo API offline</option>
      </select>
    </div>

    <!-- Loading -->
    <div id="loading-state" class="loading-spinner"></div>

    <!-- Error -->
    <div id="error-state" class="alert error" style="display: none;"></div>

    <!-- Tabella KPI -->
    <div id="table-container" style="display: none;">
      <table class="kpi-table">
        <thead>
          <tr>
            <th>Cliente</th>
            <th style="text-align: center;">Doc/Mese</th>
            <th style="text-align: center;">Pagine/Mese</th>
            <th style="text-align: center;">API Status</th>
            <th style="text-align: center;">Azioni</th>
          </tr>
        </thead>
        <tbody id="kpi-tbody">
        </tbody>
      </table>

      <p class="muted small" style="text-align: right; margin-top: 16px;">
        Ultimo aggiornamento: <span id="last-update">-</span>
      </p>
    </div>

  </section>

</main>

<script>
let kpiData = [];

async function loadKPIData() {
  const loadingEl = document.getElementById('loading-state');
  const errorEl = document.getElementById('error-state');
  const tableEl = document.getElementById('table-container');
  const summaryEl = document.getElementById('summary-section');

  loadingEl.style.display = 'block';
  errorEl.style.display = 'none';
  tableEl.style.display = 'none';
  summaryEl.style.display = 'none';

  try {
    const response = await fetch('/area-clienti/api/admin-kpi-clienti.php');
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.error || 'Errore nel caricamento dei dati');
    }

    kpiData = result.data || [];

    // Calcola summary
    const totaleClienti = kpiData.length;
    let totaleDocumenti = 0;
    let totalePagine = 0;
    let apiOnline = 0;

    kpiData.forEach(item => {
      totaleDocumenti += item.kpi_locali?.documenti_mese || 0;
      totalePagine += item.kpi_locali?.pagine_mese || 0;
      if (item.api_status?.disponibile) {
        apiOnline++;
      }
    });

    document.getElementById('summary-clienti').textContent = totaleClienti;
    document.getElementById('summary-documenti').textContent = totaleDocumenti.toLocaleString('it-IT');
    document.getElementById('summary-pagine').textContent = totalePagine.toLocaleString('it-IT');
    document.getElementById('summary-api-online').textContent = apiOnline + '/' + totaleClienti;

    renderTable(kpiData);

    document.getElementById('last-update').textContent = new Date().toLocaleString('it-IT');

    loadingEl.style.display = 'none';
    summaryEl.style.display = 'block';
    tableEl.style.display = 'block';

  } catch (error) {
    console.error('Errore KPI:', error);
    errorEl.textContent = 'Errore nel caricamento dei KPI: ' + error.message;
    errorEl.style.display = 'block';
    loadingEl.style.display = 'none';
  }
}

function renderTable(data) {
  const tbody = document.getElementById('kpi-tbody');
  tbody.innerHTML = '';

  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--muted);">Nessun cliente trovato</td></tr>';
    return;
  }

  data.forEach((item, index) => {
    const cliente = item.cliente;
    const kpiLocali = item.kpi_locali;
    const kpiWebapp = item.kpi_webapp;
    const apiStatus = item.api_status;

    const tr = document.createElement('tr');
    tr.dataset.clienteId = cliente.id;

    tr.innerHTML = `
      <td>
        <div class="cliente-info">
          <div class="cliente-nome">${escapeHtml(cliente.nome)} ${escapeHtml(cliente.cognome)}</div>
          ${cliente.azienda ? `<div class="cliente-azienda">${escapeHtml(cliente.azienda)}</div>` : ''}
          <div class="cliente-email">${escapeHtml(cliente.email)}</div>
          <div style="margin-top: 6px;">
            <span class="expand-details" onclick="toggleDetails(${cliente.id})">📋 Mostra dettagli</span>
          </div>
        </div>
      </td>
      <td style="text-align: center;">
        <div class="kpi-value">${(kpiLocali?.documenti_mese || 0).toLocaleString('it-IT')}</div>
        <div class="kpi-label">Documenti</div>
      </td>
      <td style="text-align: center;">
        <div class="kpi-value">${(kpiLocali?.pagine_mese || 0).toLocaleString('it-IT')}</div>
        <div class="kpi-label">Pagine</div>
      </td>
      <td style="text-align: center;">
        <span class="api-status ${apiStatus?.disponibile ? 'online' : 'offline'}">
          ${apiStatus?.disponibile ? '✓ Online' : '✗ Offline'}
        </span>
        ${apiStatus?.http_code ? `<div class="muted small" style="margin-top: 4px;">HTTP ${apiStatus.http_code}</div>` : ''}
      </td>
      <td style="text-align: center;">
        <a href="/area-clienti/admin/gestione-servizi.php" class="btn ghost small">Gestisci</a>
      </td>
    `;

    tbody.appendChild(tr);

    // Riga dettagli nascosta
    const detailsRow = document.createElement('tr');
    detailsRow.className = 'details-row';
    detailsRow.id = `details-${cliente.id}`;
    detailsRow.innerHTML = `
      <td colspan="5">
        <div class="details-content">
          ${renderDetails(kpiWebapp, kpiLocali)}
        </div>
      </td>
    `;
    tbody.appendChild(detailsRow);
  });
}

function renderDetails(kpiWebapp, kpiLocali) {
  if (!kpiWebapp) {
    return `
      <div class="detail-box">
        <p class="muted">Dati webapp non disponibili</p>
        <p class="muted small" style="margin-top: 8px;">L'API esterna non ha restituito dati per questo cliente.</p>
      </div>
    `;
  }

  return `
    <div class="detail-box">
      <div class="kpi-label">Documenti Totali</div>
      <div class="kpi-value" style="font-size: 24px;">${kpiWebapp.documenti_totali || 0}</div>
    </div>
    <div class="detail-box">
      <div class="kpi-label">Documenti Processati</div>
      <div class="kpi-value" style="font-size: 24px;">${kpiWebapp.documenti_processati || 0}</div>
    </div>
    <div class="detail-box">
      <div class="kpi-label">Pagine Analizzate (totale)</div>
      <div class="kpi-value" style="font-size: 24px;">${kpiWebapp.pagine_analizzate_totali || 0}</div>
    </div>
    <div class="detail-box">
      <div class="kpi-label">Accuratezza Media</div>
      <div class="kpi-value" style="font-size: 24px;">${kpiWebapp.accuratezza_media || 'N/D'}%</div>
    </div>
    <div class="detail-box">
      <div class="kpi-label">Tempo Risparmiato</div>
      <div class="kpi-value" style="font-size: 24px;">${kpiWebapp.tempo_risparmiato || 'N/D'}</div>
    </div>
    <div class="detail-box">
      <div class="kpi-label">ROI</div>
      <div class="kpi-value" style="font-size: 24px;">${kpiWebapp.roi || 'N/D'}</div>
    </div>
  `;
}

function toggleDetails(clienteId) {
  const detailsRow = document.getElementById(`details-${clienteId}`);
  if (detailsRow) {
    detailsRow.classList.toggle('show');
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}

function refreshData() {
  loadKPIData();
}

// Filtri
document.getElementById('search-input').addEventListener('input', applyFilters);
document.getElementById('api-filter').addEventListener('change', applyFilters);

function applyFilters() {
  const searchTerm = document.getElementById('search-input').value.toLowerCase();
  const apiFilter = document.getElementById('api-filter').value;

  const filteredData = kpiData.filter(item => {
    const cliente = item.cliente;
    const matchSearch = !searchTerm ||
      (cliente.nome?.toLowerCase().includes(searchTerm)) ||
      (cliente.cognome?.toLowerCase().includes(searchTerm)) ||
      (cliente.azienda?.toLowerCase().includes(searchTerm)) ||
      (cliente.email?.toLowerCase().includes(searchTerm));

    const matchApi = apiFilter === 'all' ||
      (apiFilter === 'online' && item.api_status?.disponibile) ||
      (apiFilter === 'offline' && !item.api_status?.disponibile);

    return matchSearch && matchApi;
  });

  renderTable(filteredData);
}

// Carica all'avvio
document.addEventListener('DOMContentLoaded', loadKPIData);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
