<?php
/**
 * Churn Prediction Dashboard
 * Dashboard per visualizzare clienti a rischio abbandono
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/churn-predictor.php';

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

// Recupera statistiche churn
$churnPredictor = new ChurnPredictor($pdo);
$stats = $churnPredictor->getChurnStats();

// Recupera clienti a rischio dalla vista
$stmt = $pdo->query("
    SELECT *
    FROM v_churn_dashboard
    WHERE churn_probability IS NOT NULL
    ORDER BY churn_probability DESC
    LIMIT 100
");
$clienti = $stmt->fetchAll();

// Statistiche per grafico
$riskDistribution = [
    'high' => 0,
    'medium' => 0,
    'low' => 0
];

foreach ($clienti as $cliente) {
    $riskDistribution[$cliente['risk_level']]++;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Churn Prediction - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2rem;
            color: #1a202c;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header p {
            color: #718096;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
        }

        .stat-card.danger {
            border-left-color: #f56565;
        }

        .stat-card.warning {
            border-left-color: #ed8936;
        }

        .stat-card.success {
            border-left-color: #48bb78;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a202c;
        }

        .stat-label {
            color: #718096;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .stat-change.up {
            color: #f56565;
        }

        .stat-change.down {
            color: #48bb78;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .card h2 {
            color: #1a202c;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f7fafc;
        }

        th {
            padding: 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }

        tbody tr:hover {
            background: #f7fafc;
            cursor: pointer;
        }

        .risk-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .risk-high {
            background: #fed7d7;
            color: #742a2a;
        }

        .risk-medium {
            background: #feebc8;
            color: #7c2d12;
        }

        .risk-low {
            background: #c6f6d5;
            color: #22543d;
        }

        .probability-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .probability-fill {
            height: 100%;
            transition: width 0.3s;
        }

        .probability-fill.high {
            background: linear-gradient(90deg, #f56565, #e53e3e);
        }

        .probability-fill.medium {
            background: linear-gradient(90deg, #ed8936, #dd6b20);
        }

        .probability-fill.low {
            background: linear-gradient(90deg, #48bb78, #38a169);
        }

        .cliente-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .cliente-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .cliente-details {
            display: flex;
            flex-direction: column;
        }

        .cliente-name {
            font-weight: 600;
            color: #1a202c;
        }

        .cliente-email {
            font-size: 0.75rem;
            color: #718096;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .metric-small {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .trend-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .action-btn:hover {
            background: #5568d3;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <span>📊</span>
                Churn Prediction Dashboard
            </h1>
            <p>Analisi predittiva clienti a rischio abbandono</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total_clienti'] ?? 0) ?></div>
                <div class="stat-label">Clienti Analizzati</div>
                <div class="metric-small">
                    Ultimo aggiornamento: <?= date('d/m/Y H:i', strtotime($stats['last_updated'] ?? 'now')) ?>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-value"><?= $stats['high_risk'] ?? 0 ?></div>
                <div class="stat-label">Alto Rischio</div>
                <div class="stat-change up">
                    ⚠️ Richiede azione immediata
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-value"><?= $stats['medium_risk'] ?? 0 ?></div>
                <div class="stat-label">Rischio Medio</div>
                <div class="stat-change">
                    👀 Monitoraggio attivo
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-value"><?= $stats['low_risk'] ?? 0 ?></div>
                <div class="stat-label">Basso Rischio</div>
                <div class="stat-change down">
                    ✅ Clienti stabili
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Customer List -->
            <div class="card">
                <h2>Clienti a Rischio</h2>

                <div class="actions">
                    <button class="btn btn-primary" onclick="ricalcolaChurn()">
                        🔄 Ricalcola Predizioni
                    </button>
                    <button class="btn btn-secondary" onclick="exportCSV()">
                        📥 Export CSV
                    </button>
                </div>

                <div class="filters">
                    <select class="filter-select" id="riskFilter" onchange="filterTable()">
                        <option value="">Tutti i livelli di rischio</option>
                        <option value="high">Alto Rischio</option>
                        <option value="medium">Rischio Medio</option>
                        <option value="low">Basso Rischio</option>
                    </select>

                    <select class="filter-select" id="sortBy" onchange="filterTable()">
                        <option value="probability">Ordina per: Probabilità</option>
                        <option value="ltv">Ordina per: Lifetime Value</option>
                        <option value="inactive">Ordina per: Giorni Inattivo</option>
                    </select>
                </div>

                <div class="table-wrapper">
                    <table id="clientiTable">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Rischio</th>
                                <th>Probabilità Churn</th>
                                <th>LTV</th>
                                <th>Servizi Attivi</th>
                                <th>Ultimo Login</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clienti as $cliente): ?>
                            <tr data-risk="<?= $cliente['risk_level'] ?>" onclick="showDetails(<?= $cliente['cliente_id'] ?>)">
                                <td>
                                    <div class="cliente-info">
                                        <div class="cliente-avatar">
                                            <?= strtoupper(substr($cliente['nome'], 0, 1) . substr($cliente['cognome'], 0, 1)) ?>
                                        </div>
                                        <div class="cliente-details">
                                            <div class="cliente-name">
                                                <?= htmlspecialchars($cliente['nome'] . ' ' . $cliente['cognome']) ?>
                                            </div>
                                            <div class="cliente-email">
                                                <?= htmlspecialchars($cliente['email']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="risk-badge risk-<?= $cliente['risk_level'] ?>">
                                        <?= strtoupper($cliente['risk_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="min-width: 100px;">
                                        <div style="margin-bottom: 0.5rem;">
                                            <?= round($cliente['churn_probability'] * 100, 1) ?>%
                                        </div>
                                        <div class="probability-bar">
                                            <div class="probability-fill <?= $cliente['risk_level'] ?>"
                                                 style="width: <?= $cliente['churn_probability'] * 100 ?>%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    €<?= number_format($cliente['lifetime_value'] ?? 0, 2) ?>
                                </td>
                                <td>
                                    <?= $cliente['servizi_attivi'] ?? 0 ?>
                                </td>
                                <td>
                                    <?php
                                    $giorni = $cliente['giorni_inattivo'] ?? 0;
                                    $color = $giorni > 30 ? '#f56565' : ($giorni > 14 ? '#ed8936' : '#48bb78');
                                    ?>
                                    <span style="color: <?= $color ?>">
                                        <?= $giorni ?> giorni fa
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" onclick="event.stopPropagation(); createAction(<?= $cliente['cliente_id'] ?>)">
                                        ⚡ Azione
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Risk Distribution Chart -->
            <div class="card">
                <h2>Distribuzione Rischio</h2>
                <div class="chart-container">
                    <canvas id="riskChart"></canvas>
                </div>

                <div style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem; font-size: 1rem;">Azioni Rapide</h3>

                    <button class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;" onclick="contactHighRisk()">
                        📞 Contatta Tutti Alto Rischio
                    </button>

                    <button class="btn btn-secondary" style="width: 100%; margin-bottom: 0.5rem;" onclick="scheduleReview()">
                        📅 Programma Review Clienti
                    </button>

                    <button class="btn btn-secondary" style="width: 100%;" onclick="generateReport()">
                        📊 Genera Report Churn
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart.js Risk Distribution
        const ctx = document.getElementById('riskChart').getContext('2d');
        const riskChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Alto Rischio', 'Rischio Medio', 'Basso Rischio'],
                datasets: [{
                    data: [
                        <?= $riskDistribution['high'] ?>,
                        <?= $riskDistribution['medium'] ?>,
                        <?= $riskDistribution['low'] ?>
                    ],
                    backgroundColor: [
                        '#f56565',
                        '#ed8936',
                        '#48bb78'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Filter table
        function filterTable() {
            const riskFilter = document.getElementById('riskFilter').value;
            const rows = document.querySelectorAll('#clientiTable tbody tr');

            rows.forEach(row => {
                const risk = row.dataset.risk;

                if (riskFilter === '' || risk === riskFilter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Show cliente details
        function showDetails(clienteId) {
            window.location.href = `churn-details.php?id=${clienteId}`;
        }

        // Create retention action
        function createAction(clienteId) {
            window.location.href = `../api/churn.php?action=create_action&cliente_id=${clienteId}`;
        }

        // Ricalcola churn
        async function ricalcolaChurn() {
            if (!confirm('Ricalcolare le predizioni per tutti i clienti? Operazione richiede alcuni minuti.')) {
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Calcolo in corso...';

            try {
                const response = await fetch('../api/churn.php?action=recalculate_all');
                const data = await response.json();

                if (data.success) {
                    alert(`✅ Predizioni aggiornate per ${data.processed} clienti`);
                    location.reload();
                } else {
                    alert('❌ Errore: ' + data.error);
                }
            } catch (error) {
                alert('❌ Errore di connessione');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🔄 Ricalcola Predizioni';
            }
        }

        // Export CSV
        function exportCSV() {
            window.location.href = '../api/churn.php?action=export&format=csv';
        }

        // Contact high risk
        function contactHighRisk() {
            if (confirm('Creare task di contatto per tutti i clienti ad alto rischio?')) {
                window.location.href = '../api/churn.php?action=bulk_contact&risk=high';
            }
        }

        // Schedule review
        function scheduleReview() {
            window.location.href = 'churn-review.php';
        }

        // Generate report
        function generateReport() {
            window.open('../api/churn.php?action=report&format=pdf', '_blank');
        }
    </script>
</body>
</html>
