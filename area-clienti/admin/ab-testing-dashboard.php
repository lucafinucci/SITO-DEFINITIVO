<?php
/**
 * Dashboard A/B Testing
 * Visualizzazione e gestione test scientifici
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_analytics');

// Statistiche generali
$stats = $pdo->query("
    SELECT
        COUNT(*) as total_tests,
        SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_tests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tests,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_tests
    FROM ab_tests
")->fetch(PDO::FETCH_ASSOC);

// Tests attivi
$activeTests = $pdo->query("
    SELECT * FROM v_ab_active_tests
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Tests completi (ultimi 10)
$completedTests = $pdo->query("
    SELECT * FROM ab_tests
    WHERE status = 'completed'
    ORDER BY end_date DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A/B Testing - Finch AI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .ab-dashboard {
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            color: #2c3e50;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
        }

        /* Tests Grid */
        .tests-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .tests-section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .test-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            cursor: pointer;
            transition: all 0.3s;
        }

        .test-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .test-card.running {
            border-left-color: #2ecc71;
        }

        .test-card.completed {
            border-left-color: #95a5a6;
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-header h3 {
            font-size: 16px;
            color: #2c3e50;
            margin: 0;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.running {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-badge.draft {
            background: #f8d7da;
            color: #721c24;
        }

        .test-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 15px;
        }

        .test-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .test-stat {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 6px;
        }

        .test-stat label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 5px;
        }

        .test-stat .value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        .test-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
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
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 1200px;
            width: 90%;
            max-height: 90vh;
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

        .variants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .variant-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #3498db;
        }

        .variant-card.winner {
            border-left-color: #f39c12;
            background: #fffbf0;
        }

        .variant-card.control {
            border-left-color: #95a5a6;
        }

        .variant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .variant-header h4 {
            font-size: 16px;
            color: #2c3e50;
            margin: 0;
        }

        .winner-badge {
            background: #f39c12;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .variant-metrics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .metric-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
        }

        .metric-item label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 5px;
        }

        .metric-item .value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        .significance-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .significance-box.not-significant {
            background: #fff3cd;
            border-left-color: #ffc107;
        }

        .significance-box h5 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2c3e50;
        }

        .significance-box p {
            margin: 5px 0;
            font-size: 13px;
            color: #495057;
        }

        .chart-container {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #dfe6e9;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-nav.php'; ?>

    <div class="ab-dashboard">
        <div class="page-header">
            <div>
                <h1>🧪 A/B Testing</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">Test scientifici di prezzi, offerte e comunicazioni</p>
            </div>
            <a href="#" class="btn btn-primary" onclick="showCreateModal(); return false;">
                ➕ Nuovo Test
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Totale Test</h3>
                <div class="value"><?= $stats['total_tests'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Test Attivi</h3>
                <div class="value" style="color: #2ecc71;"><?= $stats['running_tests'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Test Completati</h3>
                <div class="value" style="color: #3498db;"><?= $stats['completed_tests'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Bozze</h3>
                <div class="value" style="color: #95a5a6;"><?= $stats['draft_tests'] ?? 0 ?></div>
            </div>
        </div>

        <!-- Active Tests -->
        <div class="tests-section">
            <h2>🟢 Test Attivi</h2>

            <?php if (empty($activeTests)): ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3>Nessun test attivo</h3>
                    <p>Crea un nuovo test per iniziare</p>
                </div>
            <?php else: ?>
                <?php foreach ($activeTests as $test): ?>
                    <div class="test-card running" onclick="viewTestResults(<?= $test['id'] ?>)">
                        <div class="test-header">
                            <h3><?= htmlspecialchars($test['name']) ?></h3>
                            <span class="status-badge running">Running</span>
                        </div>

                        <div class="test-meta">
                            <span>📊 <?= ucfirst($test['test_type']) ?></span>
                            <span>🎯 <?= htmlspecialchars($test['success_metric']) ?></span>
                            <span>📅 Dal <?= date('d/m/Y', strtotime($test['start_date'])) ?></span>
                        </div>

                        <div class="test-stats">
                            <div class="test-stat">
                                <label>Partecipanti</label>
                                <div class="value"><?= number_format($test['total_participants'] ?? 0) ?></div>
                            </div>
                            <div class="test-stat">
                                <label>Conversioni</label>
                                <div class="value"><?= number_format($test['total_conversions'] ?? 0) ?></div>
                            </div>
                            <div class="test-stat">
                                <label>Revenue</label>
                                <div class="value">€<?= number_format($test['total_revenue'] ?? 0, 0, ',', '.') ?></div>
                            </div>
                        </div>

                        <div class="test-actions">
                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); viewTestResults(<?= $test['id'] ?>)">
                                📈 Risultati
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); pauseTest(<?= $test['id'] ?>)">
                                ⏸️ Pausa
                            </button>
                            <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); completeTest(<?= $test['id'] ?>)">
                                ✓ Completa
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Completed Tests -->
        <?php if (!empty($completedTests)): ?>
        <div class="tests-section">
            <h2>✅ Test Completati (Ultimi 10)</h2>

            <?php foreach ($completedTests as $test): ?>
                <div class="test-card completed" onclick="viewTestResults(<?= $test['id'] ?>)">
                    <div class="test-header">
                        <h3><?= htmlspecialchars($test['name']) ?></h3>
                        <span class="status-badge completed">Completed</span>
                    </div>

                    <div class="test-meta">
                        <span>📊 <?= ucfirst($test['test_type']) ?></span>
                        <span>📅 <?= date('d/m/Y', strtotime($test['start_date'])) ?> - <?= date('d/m/Y', strtotime($test['end_date'])) ?></span>
                    </div>

                    <button class="btn btn-sm btn-primary" style="margin-top: 10px;" onclick="event.stopPropagation(); viewTestResults(<?= $test['id'] ?>)">
                        👁️ Vedi Risultati
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Results Modal -->
    <div id="resultsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="resultsBody">
                <!-- Popolato dinamicamente -->
            </div>
        </div>
    </div>

    <!-- Create Test Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCreateModal()">&times;</span>
            <h2>Crea Nuovo Test A/B</h2>

            <form id="createTestForm" onsubmit="createTest(event)">
                <div class="form-group">
                    <label>Nome Test *</label>
                    <input type="text" name="name" required placeholder="es: Premium Pricing Test Q1 2025">
                </div>

                <div class="form-group">
                    <label>Descrizione</label>
                    <textarea name="description" rows="3" placeholder="Obiettivo e ipotesi del test..."></textarea>
                </div>

                <div class="form-group">
                    <label>Tipo Test *</label>
                    <select name="test_type" required>
                        <option value="pricing">Pricing</option>
                        <option value="offer">Offerta/Promozione</option>
                        <option value="email">Email Subject/Content</option>
                        <option value="cta">Call-to-Action</option>
                        <option value="landing_page">Landing Page</option>
                        <option value="feature">Feature</option>
                        <option value="onboarding">Onboarding</option>
                        <option value="other">Altro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Metrica Successo *</label>
                    <select name="success_metric" required>
                        <option value="conversion_rate">Conversion Rate</option>
                        <option value="revenue_per_visitor">Revenue per Visitor</option>
                        <option value="revenue_per_conversion">Revenue per Conversion</option>
                        <option value="total_revenue">Total Revenue</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Confidence Level</label>
                    <select name="confidence_level">
                        <option value="90">90%</option>
                        <option value="95" selected>95% (raccomandato)</option>
                        <option value="99">99%</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Crea Test</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        async function viewTestResults(testId) {
            try {
                const response = await fetch(`../api/ab-testing.php?action=get_results&test_id=${testId}`);
                const data = await response.json();

                if (data.success) {
                    showResultsModal(data.results);
                }
            } catch (error) {
                alert('Errore caricamento risultati: ' + error.message);
            }
        }

        function showResultsModal(results) {
            const test = results.test;
            const variants = results.variants;
            const winner = results.winner;

            let html = `
                <h2>${test.name}</h2>
                <p style="color: #7f8c8d; margin-bottom: 30px;">${test.description || ''}</p>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
                    <strong>Tipo:</strong> ${test.test_type} &nbsp;|&nbsp;
                    <strong>Metrica Successo:</strong> ${test.success_metric} &nbsp;|&nbsp;
                    <strong>Confidence:</strong> ${test.confidence_level}%
                </div>

                <h3 style="margin-bottom: 20px;">Varianti e Risultati</h3>
                <div class="variants-grid">
            `;

            variants.forEach(variant => {
                const isWinner = winner && winner.variant_id === variant.variant_id;
                const isControl = variant.is_control;

                html += `
                    <div class="variant-card ${isWinner ? 'winner' : ''} ${isControl ? 'control' : ''}">
                        <div class="variant-header">
                            <h4>${variant.variant_name}</h4>
                            ${isWinner ? '<span class="winner-badge">🏆 WINNER</span>' : ''}
                            ${isControl ? '<span class="status-badge">Control</span>' : ''}
                        </div>

                        <div class="variant-metrics">
                            <div class="metric-item">
                                <label>Views</label>
                                <div class="value">${variant.views.toLocaleString()}</div>
                            </div>
                            <div class="metric-item">
                                <label>Conversions</label>
                                <div class="value">${variant.conversions.toLocaleString()}</div>
                            </div>
                            <div class="metric-item">
                                <label>Conv. Rate</label>
                                <div class="value">${variant.conversion_rate_percentage}%</div>
                            </div>
                            <div class="metric-item">
                                <label>Revenue</label>
                                <div class="value">€${variant.total_revenue.toLocaleString()}</div>
                            </div>
                        </div>

                        ${variant.vs_control ? `
                            <div class="significance-box ${variant.vs_control.is_significant ? '' : 'not-significant'}">
                                <h5>${variant.vs_control.is_significant ? '✓ Statisticamente Significativo' : '⚠️ Non Significativo'}</h5>
                                <p><strong>Lift:</strong> ${variant.vs_control.lift > 0 ? '+' : ''}${variant.vs_control.lift}%</p>
                                <p><strong>P-value:</strong> ${variant.vs_control.p_value}</p>
                                <p><strong>Z-score:</strong> ${variant.vs_control.z_score}</p>
                                <p style="margin-top: 10px; font-style: italic;">${variant.vs_control.message}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
            });

            html += `
                </div>

                <div class="chart-container">
                    <canvas id="resultsChart"></canvas>
                </div>
            `;

            document.getElementById('resultsBody').innerHTML = html;
            document.getElementById('resultsModal').classList.add('active');

            // Chart
            setTimeout(() => {
                const ctx = document.getElementById('resultsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: variants.map(v => v.variant_name),
                        datasets: [{
                            label: 'Conversion Rate (%)',
                            data: variants.map(v => v.conversion_rate_percentage),
                            backgroundColor: variants.map((v, i) =>
                                winner && winner.variant_id === v.variant_id ? '#f39c12' : '#3498db'
                            )
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Confronto Conversion Rate'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }, 100);
        }

        function closeModal() {
            document.getElementById('resultsModal').classList.remove('active');
        }

        function showCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('active');
        }

        async function createTest(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);

            // TODO: Aggiungi UI per definire varianti
            data.variants = [
                {
                    name: 'Control',
                    key: 'control',
                    is_control: true,
                    traffic_allocation: 50,
                    config: {}
                },
                {
                    name: 'Variant A',
                    key: 'variant_a',
                    is_control: false,
                    traffic_allocation: 50,
                    config: {}
                }
            ];

            try {
                const response = await fetch('../api/ab-testing.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'create_test',
                        ...data
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Test creato con successo!');
                    location.reload();
                } else {
                    alert('Errore: ' + result.error);
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        async function pauseTest(testId) {
            if (!confirm('Mettere in pausa questo test?')) return;

            try {
                const response = await fetch('../api/ab-testing.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'pause_test',
                        test_id: testId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        async function completeTest(testId) {
            if (!confirm('Completare questo test? I risultati saranno finalizzati.')) return;

            try {
                // Prima ottieni risultati
                const response = await fetch(`../api/ab-testing.php?action=get_results&test_id=${testId}`);
                const data = await response.json();

                const winnerVariantId = data.results.winner ? data.results.winner.variant_id : null;

                // Completa test
                const response2 = await fetch('../api/ab-testing.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'complete_test',
                        test_id: testId,
                        winner_variant_id: winnerVariantId
                    })
                });

                const data2 = await response2.json();
                if (data2.success) {
                    alert('Test completato!');
                    location.reload();
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }
    </script>
</body>
</html>
