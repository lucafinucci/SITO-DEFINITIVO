<?php
/**
 * Dashboard Opportunità Upselling
 * Visualizzazione intelligente delle opportunità di vendita
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

// Statistiche aggregate
$stats = $pdo->query("
    SELECT
        COUNT(*) as total_opportunities,
        SUM(CASE WHEN opportunity_level = 'high' THEN 1 ELSE 0 END) as high_opportunities,
        SUM(CASE WHEN opportunity_level = 'medium' THEN 1 ELSE 0 END) as medium_opportunities,
        SUM(CASE WHEN opportunity_level = 'low' THEN 1 ELSE 0 END) as low_opportunities,
        SUM(expected_value) as total_expected_value,
        AVG(opportunity_score) as avg_score
    FROM upsell_opportunities
    WHERE status = 'identified'
")->fetch(PDO::FETCH_ASSOC);

// Performance ultimi 30 giorni
$performance = $pdo->query("
    SELECT
        COUNT(*) as total_created,
        SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as total_won,
        SUM(CASE WHEN status = 'won' THEN won_value ELSE 0 END) as revenue_generated
    FROM upsell_opportunities
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch(PDO::FETCH_ASSOC);

$conversionRate = $performance['total_created'] > 0
    ? round(($performance['total_won'] / $performance['total_created']) * 100, 1)
    : 0;

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Upselling - Finch AI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .upsell-dashboard {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
        }

        .stat-card.high { border-left-color: #e74c3c; }
        .stat-card.medium { border-left-color: #f39c12; }
        .stat-card.low { border-left-color: #2ecc71; }
        .stat-card.revenue { border-left-color: #9b59b6; }

        .stat-card h3 {
            font-size: 13px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-card .label {
            font-size: 12px;
            color: #7f8c8d;
        }

        /* Controls */
        .controls {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .controls input,
        .controls select {
            padding: 10px 15px;
            border: 1px solid #dfe6e9;
            border-radius: 6px;
            font-size: 14px;
        }

        .controls input[type="text"] {
            flex: 1;
            min-width: 250px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        /* Opportunities Table */
        .opportunities-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 18px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-top: 1px solid #ecf0f1;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.high {
            background: #ffe5e5;
            color: #e74c3c;
        }

        .badge.medium {
            background: #fff4e5;
            color: #f39c12;
        }

        .badge.low {
            background: #e5f8e8;
            color: #2ecc71;
        }

        .score-bar {
            width: 100px;
            height: 8px;
            background: #ecf0f1;
            border-radius: 4px;
            overflow: hidden;
        }

        .score-fill {
            height: 100%;
            background: linear-gradient(90deg, #2ecc71, #f39c12, #e74c3c);
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state svg {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #95a5a6;
            font-size: 14px;
        }

        /* Chart */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .chart-wrapper {
            max-width: 400px;
            margin: 0 auto;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            font-size: 20px;
            color: #2c3e50;
        }

        .close-modal {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #95a5a6;
            cursor: pointer;
            line-height: 20px;
        }

        .close-modal:hover {
            color: #2c3e50;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section h3 {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .detail-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .churn-warning {
            background: #ffe5e5;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .churn-warning strong {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-nav.php'; ?>

    <div class="upsell-dashboard">
        <div class="page-header">
            <h1>💰 Opportunità Upselling</h1>
            <p>Suggerimenti intelligenti per espandere il revenue per cliente</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Totale Opportunità</h3>
                <div class="value"><?= number_format($stats['total_opportunities']) ?></div>
                <div class="label">Identificate dal sistema</div>
            </div>

            <div class="stat-card high">
                <h3>Alta Priorità</h3>
                <div class="value"><?= number_format($stats['high_opportunities']) ?></div>
                <div class="label">Score > 70%</div>
            </div>

            <div class="stat-card medium">
                <h3>Media Priorità</h3>
                <div class="value"><?= number_format($stats['medium_opportunities']) ?></div>
                <div class="label">Score 40-70%</div>
            </div>

            <div class="stat-card revenue">
                <h3>Revenue Potenziale</h3>
                <div class="value">€<?= number_format($stats['total_expected_value'], 0, ',', '.') ?></div>
                <div class="label">Expected value 12 mesi</div>
            </div>

            <div class="stat-card low">
                <h3>Bassa Priorità</h3>
                <div class="value"><?= number_format($stats['low_opportunities']) ?></div>
                <div class="label">Score < 40%</div>
            </div>

            <div class="stat-card">
                <h3>Conversion Rate (30gg)</h3>
                <div class="value"><?= $conversionRate ?>%</div>
                <div class="label">€<?= number_format($performance['revenue_generated'], 0, ',', '.') ?> generati</div>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <h2 style="margin-bottom: 20px; font-size: 18px;">Distribuzione Opportunità</h2>
            <div class="chart-wrapper">
                <canvas id="opportunitiesChart"></canvas>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <input type="text" id="searchInput" placeholder="🔍 Cerca cliente, azienda, servizio...">

            <select id="levelFilter">
                <option value="">Tutte le priorità</option>
                <option value="high">Alta priorità</option>
                <option value="medium">Media priorità</option>
                <option value="low">Bassa priorità</option>
            </select>

            <select id="statusFilter">
                <option value="">Tutti gli stati</option>
                <option value="identified">Identificata</option>
                <option value="contacted">Contattato</option>
                <option value="demo_scheduled">Demo schedulata</option>
                <option value="proposal_sent">Proposta inviata</option>
            </select>

            <button class="btn btn-primary" onclick="recalculateOpportunities()">
                🔄 Ricalcola Opportunità
            </button>

            <button class="btn btn-success" onclick="exportOpportunities()">
                📊 Esporta CSV
            </button>
        </div>

        <!-- Opportunities Table -->
        <div class="opportunities-container">
            <div class="table-header">
                <h2>Lista Opportunità</h2>
                <span id="resultCount">Caricamento...</span>
            </div>

            <div id="tableContainer">
                <div class="loading">
                    <p>Caricamento opportunità...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h2>Dettagli Opportunità</h2>
            </div>
            <div id="modalBody">
                <!-- Popolato dinamicamente -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart initialization
        const ctx = document.getElementById('opportunitiesChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Alta Priorità', 'Media Priorità', 'Bassa Priorità'],
                datasets: [{
                    data: [
                        <?= $stats['high_opportunities'] ?>,
                        <?= $stats['medium_opportunities'] ?>,
                        <?= $stats['low_opportunities'] ?>
                    ],
                    backgroundColor: ['#e74c3c', '#f39c12', '#2ecc71'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Load opportunities
        let allOpportunities = [];

        async function loadOpportunities() {
            try {
                const response = await fetch('../api/upsell.php?action=get_all');
                const data = await response.json();

                if (data.success) {
                    allOpportunities = data.opportunities;
                    renderTable(allOpportunities);
                }
            } catch (error) {
                console.error('Errore caricamento:', error);
                document.getElementById('tableContainer').innerHTML = `
                    <div class="empty-state">
                        <h3>Errore di caricamento</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }

        function renderTable(opportunities) {
            const container = document.getElementById('tableContainer');
            document.getElementById('resultCount').textContent =
                `${opportunities.length} opportunità trovate`;

            if (opportunities.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3>Nessuna opportunità trovata</h3>
                        <p>Prova a modificare i filtri o ricalcolare le opportunità</p>
                    </div>
                `;
                return;
            }

            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Servizio</th>
                            <th>Score</th>
                            <th>Priorità</th>
                            <th>Expected Value</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            opportunities.forEach(opp => {
                const scorePercent = Math.round(opp.opportunity_score * 100);

                html += `
                    <tr>
                        <td>
                            <strong>${opp.nome} ${opp.cognome}</strong><br>
                            <small style="color: #95a5a6;">${opp.azienda || opp.email}</small>
                        </td>
                        <td>${opp.servizio_nome}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="score-bar">
                                    <div class="score-fill" style="width: ${scorePercent}%"></div>
                                </div>
                                <span style="font-weight: 600;">${scorePercent}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${opp.opportunity_level}">
                                ${opp.opportunity_level === 'high' ? 'ALTA' :
                                  opp.opportunity_level === 'medium' ? 'MEDIA' : 'BASSA'}
                            </span>
                        </td>
                        <td>
                            <strong>€${parseFloat(opp.expected_value).toLocaleString('it-IT', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            })}</strong>
                        </td>
                        <td>${formatStatus(opp.status)}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="viewDetails(${opp.opportunity_id})">
                                    👁️ Dettagli
                                </button>
                                <button class="btn btn-sm btn-success" onclick="markAsContacted(${opp.opportunity_id})">
                                    ✓ Contattato
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function formatStatus(status) {
            const statusMap = {
                'identified': 'Identificata',
                'contacted': 'Contattato',
                'demo_scheduled': 'Demo schedulata',
                'proposal_sent': 'Proposta inviata',
                'won': 'Vinto',
                'lost': 'Perso'
            };
            return statusMap[status] || status;
        }

        // Filters
        document.getElementById('searchInput').addEventListener('input', applyFilters);
        document.getElementById('levelFilter').addEventListener('change', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);

        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const level = document.getElementById('levelFilter').value;
            const status = document.getElementById('statusFilter').value;

            const filtered = allOpportunities.filter(opp => {
                const matchSearch = !search ||
                    opp.nome.toLowerCase().includes(search) ||
                    opp.cognome.toLowerCase().includes(search) ||
                    (opp.azienda && opp.azienda.toLowerCase().includes(search)) ||
                    opp.email.toLowerCase().includes(search) ||
                    opp.servizio_nome.toLowerCase().includes(search);

                const matchLevel = !level || opp.opportunity_level === level;
                const matchStatus = !status || opp.status === status;

                return matchSearch && matchLevel && matchStatus;
            });

            renderTable(filtered);
        }

        // Actions
        async function viewDetails(opportunityId) {
            try {
                const response = await fetch(`../api/upsell.php?action=get_details&id=${opportunityId}`);
                const data = await response.json();

                if (data.success) {
                    showDetailModal(data.opportunity);
                }
            } catch (error) {
                alert('Errore caricamento dettagli: ' + error.message);
            }
        }

        function showDetailModal(opp) {
            const modal = document.getElementById('detailModal');
            const body = document.getElementById('modalBody');

            let reasoning = [];
            try {
                reasoning = JSON.parse(opp.reasoning || '[]');
            } catch(e) {}

            let scores = {};
            try {
                scores = JSON.parse(opp.scores_breakdown || '{}');
            } catch(e) {}

            let html = `
                <div class="detail-section">
                    <h3>Cliente</h3>
                    <div class="detail-content">
                        <p><strong>${opp.nome} ${opp.cognome}</strong></p>
                        <p>${opp.azienda || ''}</p>
                        <p>${opp.email}</p>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Servizio Raccomandato</h3>
                    <div class="detail-content">
                        <p><strong>${opp.servizio_nome}</strong></p>
                        <p>Prezzo mensile: €${parseFloat(opp.prezzo_mensile).toFixed(2)}</p>
                        <p>Expected Value (12m): €${parseFloat(opp.expected_value).toFixed(2)}</p>
                    </div>
                </div>
            `;

            // Churn warning
            if (opp.churn_risk && parseFloat(opp.churn_risk) > 0.7) {
                html += `
                    <div class="churn-warning">
                        <strong>⚠️ ATTENZIONE:</strong> Cliente ad alto rischio churn (${Math.round(opp.churn_risk * 100)}%).
                        Valuta prima azioni di retention.
                    </div>
                `;
            }

            html += `
                <div class="detail-section">
                    <h3>Score Breakdown</h3>
                    <div class="detail-content">
            `;

            for (const [key, value] of Object.entries(scores)) {
                const percent = Math.round(value * 100);
                html += `
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>${key.replace(/_/g, ' ')}</span>
                            <strong>${percent}%</strong>
                        </div>
                        <div class="score-bar" style="width: 100%;">
                            <div class="score-fill" style="width: ${percent}%"></div>
                        </div>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Motivazioni</h3>
                    <div class="detail-content">
                        <ul>
                            ${reasoning.map(r => `<li>${r}</li>`).join('')}
                        </ul>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Pitch Suggerito</h3>
                    <div class="detail-content">
                        <p>${opp.suggested_pitch || 'Nessun pitch disponibile'}</p>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Timing</h3>
                    <div class="detail-content">
                        <p><strong>Momento migliore per contattare:</strong> ${formatTiming(opp.best_time_to_contact)}</p>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button class="btn btn-success" onclick="markAsContacted(${opp.opportunity_id}); closeModal();">
                        ✓ Segna come Contattato
                    </button>
                    <button class="btn btn-warning" onclick="markAsWon(${opp.opportunity_id})">
                        🎉 Segna come Vinto
                    </button>
                    <button class="btn btn-secondary" onclick="closeModal()">
                        Chiudi
                    </button>
                </div>
            `;

            body.innerHTML = html;
            modal.classList.add('active');
        }

        function formatTiming(timing) {
            const timingMap = {
                'now': 'Subito',
                'this_week': 'Questa settimana',
                'this_month': 'Questo mese',
                'after_reengagement': 'Dopo re-engagement'
            };
            return timingMap[timing] || timing;
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        async function markAsContacted(opportunityId) {
            if (!confirm('Segnare questa opportunità come contattata?')) return;

            try {
                const response = await fetch('../api/upsell.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_status',
                        opportunity_id: opportunityId,
                        status: 'contacted'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Opportunità aggiornata!');
                    loadOpportunities();
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        async function markAsWon(opportunityId) {
            const value = prompt('Inserisci il valore della conversione (€):');
            if (!value) return;

            try {
                const response = await fetch('../api/upsell.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'mark_won',
                        opportunity_id: opportunityId,
                        won_value: parseFloat(value)
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('🎉 Opportunità vinta! Ottimo lavoro!');
                    closeModal();
                    loadOpportunities();
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        async function recalculateOpportunities() {
            if (!confirm('Ricalcolare tutte le opportunità? Potrebbe richiedere alcuni minuti.')) return;

            const btn = event.target;
            btn.disabled = true;
            btn.textContent = '⏳ Calcolo in corso...';

            try {
                const response = await fetch('../api/upsell.php?action=recalculate_all');
                const data = await response.json();

                if (data.success) {
                    alert(`✓ Calcolate ${data.opportunities_found} opportunità!\n\n` +
                          `Alta priorità: ${data.high_priority}\n` +
                          `Media priorità: ${data.medium_priority}\n` +
                          `Bassa priorità: ${data.low_priority}`);
                    location.reload();
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = '🔄 Ricalcola Opportunità';
            }
        }

        async function exportOpportunities() {
            window.location.href = '../api/upsell.php?action=export&format=csv';
        }

        // Load on page load
        loadOpportunities();
    </script>
</body>
</html>
