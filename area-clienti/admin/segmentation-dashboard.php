<?php
/**
 * Dashboard Segmentazione Clienti
 * Visualizzazione cluster, personas e insights comportamentali
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

// Statistiche segmentazione
$stats = $pdo->query("
    SELECT
        COUNT(DISTINCT segment_id) as total_segments,
        COUNT(DISTINCT cliente_id) as total_customers,
        MAX(assignment_date) as last_update
    FROM customer_segments
")->fetch(PDO::FETCH_ASSOC);

// Profili segmenti
$segments = $pdo->query("
    SELECT * FROM segment_profiles
    ORDER BY size DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segmentazione Clienti - Finch AI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .segmentation-dashboard {
            padding: 20px;
            max-width: 1600px;
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

        /* Stats Bar */
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            border-left: 3px solid #3498db;
        }

        .stat-item:first-child {
            border-left: none;
        }

        .stat-item h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 10px;
        }

        .stat-item .value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
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
            align-items: center;
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

        /* Segments Grid */
        .segments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .segment-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 5px solid #3498db;
            cursor: pointer;
        }

        .segment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .segment-card.vip {
            border-left-color: #f39c12;
        }

        .segment-card.at-risk {
            border-left-color: #e74c3c;
        }

        .segment-card.power-user {
            border-left-color: #9b59b6;
        }

        .segment-card.new {
            border-left-color: #2ecc71;
        }

        .segment-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .persona-icon {
            font-size: 48px;
            margin-right: 15px;
        }

        .persona-info h3 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .persona-info p {
            font-size: 13px;
            color: #7f8c8d;
        }

        .segment-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .segment-stat {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
        }

        .segment-stat label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 5px;
        }

        .segment-stat .value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        .segment-characteristics {
            margin-bottom: 20px;
        }

        .segment-characteristics h4 {
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .characteristics-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            background: #ecf0f1;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #2c3e50;
        }

        .segment-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        /* Visualization */
        .visualization-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .visualization-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }

        .chart-wrapper {
            position: relative;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
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
            max-width: 900px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close-modal {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #95a5a6;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #2c3e50;
        }

        .recommendations-list {
            list-style: none;
            padding: 0;
        }

        .recommendation-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .recommendation-item.critical {
            border-left-color: #e74c3c;
        }

        .recommendation-item.high {
            border-left-color: #f39c12;
        }

        .recommendation-item .priority {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .recommendation-item .action {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .recommendation-item .message {
            font-size: 14px;
            color: #7f8c8d;
        }

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .customer-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
        }

        .customer-table td {
            padding: 12px;
            border-top: 1px solid #ecf0f1;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-nav.php'; ?>

    <div class="segmentation-dashboard">
        <div class="page-header">
            <h1>🎯 Segmentazione Clienti</h1>
            <p>Cluster automatici basati su comportamento e caratteristiche</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <h3>Segmenti Attivi</h3>
                <div class="value"><?= $stats['total_segments'] ?? 0 ?></div>
            </div>
            <div class="stat-item">
                <h3>Clienti Segmentati</h3>
                <div class="value"><?= number_format($stats['total_customers'] ?? 0) ?></div>
            </div>
            <div class="stat-item">
                <h3>Ultimo Aggiornamento</h3>
                <div class="value" style="font-size: 16px;">
                    <?= $stats['last_update'] ? date('d/m/Y H:i', strtotime($stats['last_update'])) : 'Mai' ?>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <button class="btn btn-primary" onclick="recalculateSegmentation()">
                🔄 Ricalcola Segmentazione
            </button>
            <button class="btn btn-success" onclick="exportSegments()">
                📊 Esporta Dati
            </button>
            <div style="flex: 1;"></div>
            <span style="font-size: 13px; color: #7f8c8d;">
                Algoritmo: K-means clustering con 6 feature comportamentali
            </span>
        </div>

        <!-- Segments Grid -->
        <div class="segments-grid">
            <?php foreach ($segments as $segment):
                $characteristics = json_decode($segment['characteristics'] ?? '[]', true);
                $recommendations = json_decode($segment['recommendations'] ?? '[]', true);

                // Determina classe CSS
                $cardClass = 'segment-card';
                if (in_array('high_value', $characteristics)) {
                    $cardClass .= ' vip';
                } elseif (in_array('at_risk', $characteristics)) {
                    $cardClass .= ' at-risk';
                } elseif (in_array('power_user', $characteristics)) {
                    $cardClass .= ' power-user';
                } elseif (in_array('new_customer', $characteristics)) {
                    $cardClass .= ' new';
                }
            ?>
            <div class="<?= $cardClass ?>" onclick="viewSegmentDetails(<?= $segment['segment_id'] ?>)">
                <div class="segment-header">
                    <div class="persona-icon"><?= $segment['persona_icon'] ?></div>
                    <div class="persona-info">
                        <h3><?= htmlspecialchars($segment['persona_name']) ?></h3>
                        <p><?= htmlspecialchars($segment['persona_description']) ?></p>
                    </div>
                </div>

                <div class="segment-stats">
                    <div class="segment-stat">
                        <label>Clienti</label>
                        <div class="value"><?= number_format($segment['size']) ?> (<?= $segment['percentage'] ?>%)</div>
                    </div>
                    <div class="segment-stat">
                        <label>LTV Medio</label>
                        <div class="value">€<?= number_format($segment['avg_ltv'], 0, ',', '.') ?></div>
                    </div>
                    <div class="segment-stat">
                        <label>Engagement</label>
                        <div class="value"><?= round($segment['avg_engagement'] * 100) ?>%</div>
                    </div>
                    <div class="segment-stat">
                        <label>Rischio Churn</label>
                        <div class="value"><?= round($segment['avg_churn_risk'] * 100) ?>%</div>
                    </div>
                </div>

                <div class="segment-characteristics">
                    <h4>Caratteristiche</h4>
                    <div class="characteristics-tags">
                        <?php foreach ($characteristics as $char): ?>
                            <span class="tag"><?= str_replace('_', ' ', ucfirst($char)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="segment-actions">
                    <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); viewCustomers(<?= $segment['segment_id'] ?>)">
                        👥 Vedi Clienti
                    </button>
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); createCampaign(<?= $segment['segment_id'] ?>)">
                        📧 Crea Campagna
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Visualizations -->
        <div class="visualization-container">
            <h2>Visualizzazioni</h2>
            <div class="chart-grid">
                <div class="chart-wrapper">
                    <canvas id="segmentDistributionChart"></canvas>
                </div>
                <div class="chart-wrapper">
                    <canvas id="segmentValueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalBody">
                <!-- Popolato dinamicamente -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Campaign Modal -->
    <div id="campaignModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCampaignModal()">&times;</span>
            <h2>Crea Campagna Segmento</h2>

            <form id="campaignForm" onsubmit="submitCampaign(event)">
                <input type="hidden" name="segment_id" value="">

                <div class="form-group">
                    <label>Nome Campagna *</label>
                    <input type="text" name="campaign_name" required placeholder="es: Retention Q1">
                </div>

                <div class="form-group">
                    <label>Tipo Campagna *</label>
                    <select name="campaign_type" required>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="call">Call</option>
                        <option value="in_app">In-app</option>
                        <option value="mixed">Multicanale</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Azione Target *</label>
                    <select name="target_action" required>
                        <option value="retention">Retention</option>
                        <option value="upsell">Upsell</option>
                        <option value="reengagement">Re-engagement</option>
                        <option value="onboarding">Onboarding</option>
                        <option value="advocacy">Advocacy</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Oggetto</label>
                    <input type="text" name="subject" placeholder="Oggetto email (opzionale)">
                </div>

                <div class="form-group">
                    <label>Messaggio</label>
                    <textarea name="message" rows="4" placeholder="Testo della campagna..."></textarea>
                </div>

                <div class="form-group">
                    <label>Data Pianificazione</label>
                    <input type="date" name="scheduled_date">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCampaignModal()">Annulla</button>
                    <button type="submit" class="btn btn-primary">Crea Campagna</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Chart 1: Segment Distribution (Doughnut)
        const distributionCtx = document.getElementById('segmentDistributionChart').getContext('2d');
        const distributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($segments, 'persona_name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($segments, 'size')) ?>,
                    backgroundColor: [
                        '#f39c12', '#e74c3c', '#9b59b6', '#2ecc71',
                        '#3498db', '#1abc9c', '#34495e', '#95a5a6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribuzione Clienti per Segmento'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Chart 2: Segment Value (Bar)
        const valueCtx = document.getElementById('segmentValueChart').getContext('2d');
        const valueChart = new Chart(valueCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($segments, 'persona_name')) ?>,
                datasets: [{
                    label: 'LTV Medio',
                    data: <?= json_encode(array_column($segments, 'avg_ltv')) ?>,
                    backgroundColor: '#3498db',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'LTV Medio per Segmento'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '€' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // View segment details
        async function viewSegmentDetails(segmentId) {
            try {
                const response = await fetch(`../api/segmentation.php?action=get_segment_details&segment_id=${segmentId}`);
                const data = await response.json();

                if (data.success) {
                    showDetailModal(data.segment);
                }
            } catch (error) {
                alert('Errore caricamento dettagli: ' + error.message);
            }
        }

        function showDetailModal(segment) {
            const recommendations = JSON.parse(segment.recommendations || '[]');
            const characteristics = JSON.parse(segment.characteristics || '[]');

            let html = `
                <h2>${segment.persona_icon} ${segment.persona_name}</h2>
                <p style="color: #7f8c8d; margin-bottom: 30px;">${segment.persona_description}</p>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
                    <div class="segment-stat">
                        <label>Clienti</label>
                        <div class="value">${segment.size} (${segment.percentage}%)</div>
                    </div>
                    <div class="segment-stat">
                        <label>LTV Medio</label>
                        <div class="value">€${parseFloat(segment.avg_ltv).toLocaleString('it-IT')}</div>
                    </div>
                    <div class="segment-stat">
                        <label>Engagement</label>
                        <div class="value">${Math.round(segment.avg_engagement * 100)}%</div>
                    </div>
                    <div class="segment-stat">
                        <label>Usage</label>
                        <div class="value">${Math.round(segment.avg_usage * 100)}%</div>
                    </div>
                    <div class="segment-stat">
                        <label>Rischio Churn</label>
                        <div class="value">${Math.round(segment.avg_churn_risk * 100)}%</div>
                    </div>
                    <div class="segment-stat">
                        <label>Anzianità Media</label>
                        <div class="value">${segment.avg_tenure_days} giorni</div>
                    </div>
                </div>

                <h3 style="margin-bottom: 15px;">Caratteristiche Dominanti</h3>
                <div class="characteristics-tags" style="margin-bottom: 30px;">
                    ${characteristics.map(c => `<span class="tag">${c.replace(/_/g, ' ')}</span>`).join('')}
                </div>

                <h3 style="margin-bottom: 15px;">Raccomandazioni Strategiche</h3>
                <ul class="recommendations-list">
                    ${recommendations.map(rec => `
                        <li class="recommendation-item ${rec.priority}">
                            <div class="priority">${rec.priority.toUpperCase()}</div>
                            <div class="action">${rec.action.replace(/_/g, ' ')}</div>
                            <div class="message">${rec.message}</div>
                        </li>
                    `).join('')}
                </ul>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="viewCustomers(${segment.segment_id})">
                        👥 Vedi Tutti i Clienti
                    </button>
                    <button class="btn btn-success" onclick="createCampaign(${segment.segment_id})">
                        📧 Crea Campagna Targeted
                    </button>
                </div>
            `;

            document.getElementById('modalBody').innerHTML = html;
            document.getElementById('detailModal').classList.add('active');
        }

        async function viewCustomers(segmentId) {
            try {
                const response = await fetch(`../api/segmentation.php?action=get_segment_customers&segment_id=${segmentId}`);
                const data = await response.json();

                if (data.success) {
                    showCustomersModal(data.customers, segmentId);
                }
            } catch (error) {
                alert('Errore caricamento clienti: ' + error.message);
            }
        }

        function showCustomersModal(customers, segmentId) {
            let html = `
                <h2>Clienti del Segmento</h2>
                <p style="color: #7f8c8d; margin-bottom: 20px;">${customers.length} clienti trovati</p>

                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Azienda</th>
                            <th>LTV</th>
                            <th>Engagement</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            customers.forEach(customer => {
                html += `
                    <tr>
                        <td>
                            <strong>${customer.nome} ${customer.cognome}</strong><br>
                            <small style="color: #95a5a6;">${customer.email}</small>
                        </td>
                        <td>${customer.azienda || '-'}</td>
                        <td>€${parseFloat(customer.lifetime_value).toLocaleString('it-IT')}</td>
                        <td>${customer.days_since_last_login} giorni fa</td>
                        <td>
                            <a href="cliente-dettaglio.php?id=${customer.cliente_id}" class="btn btn-sm btn-primary">
                                👁️ Dettagli
                            </a>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            document.getElementById('modalBody').innerHTML = html;
            document.getElementById('detailModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        async function recalculateSegmentation() {
            if (!confirm('Ricalcolare la segmentazione per tutti i clienti? Potrebbe richiedere alcuni minuti.')) {
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.textContent = '⏳ Calcolo in corso...';

            try {
                const response = await fetch('../api/segmentation.php?action=recalculate');
                const data = await response.json();

                if (data.success) {
                    alert(`✓ Segmentazione completata!\n\n` +
                          `Segmenti: ${data.num_clusters}\n` +
                          `Clienti processati: ${data.total_customers}\n` +
                          `Iterazioni: ${data.iterations}`);
                    location.reload();
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = '🔄 Ricalcola Segmentazione';
            }
        }

        function createCampaign(segmentId) {
            const form = document.getElementById('campaignForm');
            const modal = document.getElementById('campaignModal');

            form.segment_id.value = segmentId;
            modal.classList.add('active');
        }

        function closeCampaignModal() {
            document.getElementById('campaignModal').classList.remove('active');
        }

        async function submitCampaign(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const payload = Object.fromEntries(formData);

            payload.segment_id = parseInt(payload.segment_id, 10);
            if (!payload.scheduled_date) {
                delete payload.scheduled_date;
            }

            if (payload.scheduled_date) {
                payload.status = 'scheduled';
            } else {
                payload.status = 'draft';
            }

            try {
                const response = await fetch('../api/segmentation.php?action=create_campaign', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    closeCampaignModal();
                    alert('Campagna creata con successo!');
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        function exportSegments() {
            window.location.href = '../api/segmentation.php?action=export&format=csv';
        }
    </script>
</body>
</html>
