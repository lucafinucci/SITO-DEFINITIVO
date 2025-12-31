<?php
/**
 * Audit Log - Chi ha fatto cosa e quando
 * Visualizzazione completa audit trail
 */

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/audit-logger.php';

$rbac = getRBAC($pdo);
$rbac->requirePermission('can_view_audit_log');

$audit = new AuditLogger($pdo);

// Recupera statistiche generali
$stats = $pdo->query('
    SELECT
        COUNT(*) as totale,
        SUM(CASE WHEN successo = TRUE THEN 1 ELSE 0 END) as successi,
        SUM(CASE WHEN successo = FALSE THEN 1 ELSE 0 END) as fallimenti,
        SUM(CASE WHEN livello = "critical" THEN 1 ELSE 0 END) as critici,
        SUM(CASE WHEN richiede_review = TRUE THEN 1 ELSE 0 END) as da_revisionare,
        COUNT(DISTINCT user_id) as admin_attivi
    FROM audit_log
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
')->fetch();

// Azioni recenti
$azioniRecenti = $pdo->query('
    SELECT COUNT(*) as count
    FROM audit_log
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
')->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - Chi ha fatto cosa e quando</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
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
        }

        .header p {
            color: #718096;
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .stat-card.success { border-left-color: #48bb78; }
        .stat-card.danger { border-left-color: #f56565; }
        .stat-card.warning { border-left-color: #ed8936; }
        .stat-card.info { border-left-color: #4299e1; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #718096;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filters-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
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

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-export {
            background: #48bb78;
            color: white;
            margin-left: auto;
        }

        .btn-export:hover {
            background: #38a169;
        }

        .log-table-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
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
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #1a202c;
        }

        .user-email {
            font-size: 0.75rem;
            color: #718096;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-danger {
            background: #fed7d7;
            color: #742a2a;
        }

        .badge-warning {
            background: #feebc8;
            color: #7c2d12;
        }

        .badge-info {
            background: #bee3f8;
            color: #2c5282;
        }

        .badge-critical {
            background: #feb2b2;
            color: #742a2a;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .action-tag {
            background: #edf2f7;
            color: #2d3748;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
        }

        .timestamp {
            color: #718096;
            font-size: 0.875rem;
        }

        .view-details {
            color: #667eea;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .view-details:hover {
            text-decoration: underline;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #4a5568;
        }

        .page-btn:hover:not(:disabled) {
            border-color: #667eea;
            color: #667eea;
        }

        .page-btn.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            padding: 2rem;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .modal-header h2 {
            color: #1a202c;
            font-size: 1.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #718096;
            line-height: 1;
        }

        .close-modal:hover {
            color: #1a202c;
        }

        .detail-section {
            margin-bottom: 1.5rem;
        }

        .detail-section h3 {
            color: #4a5568;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .detail-content {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .diff-view {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .diff-before {
            background: #fed7d7;
            padding: 1rem;
            border-radius: 8px;
        }

        .diff-after {
            background: #c6f6d5;
            padding: 1rem;
            border-radius: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #718096;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .diff-view {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .btn-export {
                margin-left: 0;
            }

            table {
                font-size: 0.75rem;
            }

            th, td {
                padding: 0.5rem;
            }
        }

        .review-flag {
            background: #feebc8;
            color: #7c2d12;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .review-flag::before {
            content: '⚠️';
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Audit Log - Chi ha fatto cosa e quando</h1>
            <p>Tracciamento completo di tutte le azioni amministrative</p>
        </div>

        <!-- Statistiche -->
        <div class="stats-grid">
            <div class="stat-card info">
                <div class="stat-value"><?= number_format($stats['totale']) ?></div>
                <div class="stat-label">Azioni Totali (30gg)</div>
            </div>

            <div class="stat-card success">
                <div class="stat-value"><?= number_format($stats['successi']) ?></div>
                <div class="stat-label">Successi</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-value"><?= number_format($stats['fallimenti']) ?></div>
                <div class="stat-label">Fallimenti</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-value"><?= number_format($stats['critici']) ?></div>
                <div class="stat-label">Eventi Critici</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['da_revisionare']) ?></div>
                <div class="stat-label">Da Revisionare</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?= number_format($azioniRecenti) ?></div>
                <div class="stat-label">Ultime 24h</div>
            </div>
        </div>

        <!-- Filtri -->
        <div class="filters-card">
            <form id="filterForm">
                <div class="filters-grid">
                    <div class="form-group">
                        <label>Admin</label>
                        <select name="user_id" id="userFilter">
                            <option value="">Tutti gli admin</option>
                            <?php
                            $admins = $pdo->query('
                                SELECT DISTINCT u.id, u.nome, u.cognome, u.email
                                FROM audit_log al
                                JOIN utenti u ON al.user_id = u.id
                                ORDER BY u.nome, u.cognome
                            ')->fetchAll();
                            foreach ($admins as $admin):
                            ?>
                                <option value="<?= $admin['id'] ?>">
                                    <?= htmlspecialchars($admin['nome'] . ' ' . $admin['cognome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Azione</label>
                        <select name="azione" id="azioneFilter">
                            <option value="">Tutte le azioni</option>
                            <?php
                            $azioni = $pdo->query('
                                SELECT DISTINCT azione
                                FROM audit_log
                                ORDER BY azione
                            ')->fetchAll();
                            foreach ($azioni as $azione):
                            ?>
                                <option value="<?= $azione['azione'] ?>">
                                    <?= htmlspecialchars($azione['azione']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Entità</label>
                        <select name="entita" id="entitaFilter">
                            <option value="">Tutte le entità</option>
                            <option value="cliente">Cliente</option>
                            <option value="servizio">Servizio</option>
                            <option value="fattura">Fattura</option>
                            <option value="pagamento">Pagamento</option>
                            <option value="admin">Admin</option>
                            <option value="settings">Impostazioni</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Livello</label>
                        <select name="livello" id="livelloFilter">
                            <option value="">Tutti i livelli</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria" id="categoriaFilter">
                            <option value="">Tutte le categorie</option>
                            <option value="auth">Autenticazione</option>
                            <option value="cliente">Cliente</option>
                            <option value="servizio">Servizio</option>
                            <option value="fattura">Fattura</option>
                            <option value="pagamento">Pagamento</option>
                            <option value="team">Team</option>
                            <option value="comunicazione">Comunicazione</option>
                            <option value="sistema">Sistema</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Periodo</label>
                        <select name="periodo" id="periodoFilter">
                            <option value="24h">Ultime 24 ore</option>
                            <option value="7d">Ultimi 7 giorni</option>
                            <option value="30d" selected>Ultimi 30 giorni</option>
                            <option value="90d">Ultimi 90 giorni</option>
                            <option value="custom">Personalizzato</option>
                        </select>
                    </div>

                    <div class="form-group" id="customDatesGroup" style="display: none;">
                        <label>Da - A</label>
                        <input type="date" name="data_inizio" id="dataInizio">
                    </div>

                    <div class="form-group" style="visibility: hidden;" id="customDatesEndGroup">
                        <label>&nbsp;</label>
                        <input type="date" name="data_fine" id="dataFine">
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">🔍 Filtra</button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">↺ Reset</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleReviewOnly()">
                        ⚠️ Solo da revisionare
                    </button>
                    <?php if ($rbac->can('can_export_data')): ?>
                        <button type="button" class="btn btn-export" onclick="exportLogs('csv')">
                            📥 Export CSV
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabella Log -->
        <div class="log-table-card">
            <div class="table-wrapper">
                <table id="logTable">
                    <thead>
                        <tr>
                            <th>Quando</th>
                            <th>Chi</th>
                            <th>Azione</th>
                            <th>Cosa</th>
                            <th>Stato</th>
                            <th>Livello</th>
                            <th>IP</th>
                            <th>Dettagli</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        <tr>
                            <td colspan="8" class="loading">Caricamento log...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <!-- Modal Dettagli -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Dettagli Azione</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilters = {};
        let reviewOnlyMode = false;

        // Carica log al caricamento pagina
        document.addEventListener('DOMContentLoaded', () => {
            loadLogs();
        });

        // Gestione form filtri
        document.getElementById('filterForm').addEventListener('submit', (e) => {
            e.preventDefault();
            currentPage = 1;
            loadLogs();
        });

        // Mostra/nascondi date personalizzate
        document.getElementById('periodoFilter').addEventListener('change', (e) => {
            const customGroup = document.getElementById('customDatesGroup');
            const customEndGroup = document.getElementById('customDatesEndGroup');
            if (e.target.value === 'custom') {
                customGroup.style.display = 'flex';
                customEndGroup.style.visibility = 'visible';
            } else {
                customGroup.style.display = 'none';
                customEndGroup.style.visibility = 'hidden';
            }
        });

        // Carica log
        async function loadLogs() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);

            const params = new URLSearchParams();
            params.append('action', 'list');
            params.append('page', currentPage);

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            if (reviewOnlyMode) {
                params.append('richiede_review', '1');
            }

            try {
                const response = await fetch(`../api/audit-log.php?${params.toString()}`);
                const data = await response.json();

                if (data.success) {
                    renderLogs(data.logs);
                    renderPagination(data.total, data.per_page);
                } else {
                    showError(data.error);
                }
            } catch (error) {
                console.error('Errore:', error);
                showError('Errore nel caricamento dei log');
            }
        }

        // Renderizza log nella tabella
        function renderLogs(logs) {
            const tbody = document.getElementById('logTableBody');

            if (logs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <p>Nessun log trovato con i filtri selezionati</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = logs.map(log => `
                <tr>
                    <td>
                        <div class="timestamp">
                            ${formatDate(log.created_at)}
                        </div>
                    </td>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                ${getInitials(log.user_email)}
                            </div>
                            <div class="user-details">
                                <div class="user-name">${escapeHtml(log.user_email || 'Sistema')}</div>
                                <div class="user-email">${escapeHtml(log.user_ruolo || '-')}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="action-tag">${escapeHtml(log.azione)}</span>
                    </td>
                    <td>
                        <div>${escapeHtml(log.entita)}${log.entita_id ? ' #' + log.entita_id : ''}</div>
                        <div style="font-size: 0.75rem; color: #718096; margin-top: 0.25rem;">
                            ${escapeHtml(log.descrizione || '-')}
                        </div>
                    </td>
                    <td>
                        ${log.successo
                            ? '<span class="badge badge-success">Successo</span>'
                            : '<span class="badge badge-danger">Fallito</span>'}
                    </td>
                    <td>
                        ${getLivelloBadge(log.livello)}
                        ${log.richiede_review ? '<div class="review-flag" style="margin-top: 0.5rem;">Da revisionare</div>' : ''}
                    </td>
                    <td>
                        <div style="font-family: 'Courier New', monospace; font-size: 0.75rem;">
                            ${escapeHtml(log.user_ip || '-')}
                        </div>
                    </td>
                    <td>
                        <a href="#" class="view-details" onclick="showDetails(${log.id}); return false;">
                            Vedi dettagli →
                        </a>
                    </td>
                </tr>
            `).join('');
        }

        // Renderizza paginazione
        function renderPagination(total, perPage) {
            const totalPages = Math.ceil(total / perPage);
            const pagination = document.getElementById('pagination');

            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = `
                <button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
                    ← Prec
                </button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `
                        <button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<span style="padding: 0.5rem;">...</span>`;
                }
            }

            html += `
                <button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
                    Succ →
                </button>
            `;

            pagination.innerHTML = html;
        }

        // Cambia pagina
        function changePage(page) {
            currentPage = page;
            loadLogs();
        }

        // Mostra dettagli log
        async function showDetails(logId) {
            try {
                const response = await fetch(`../api/audit-log.php?action=details&id=${logId}`);
                const data = await response.json();

                if (data.success) {
                    const log = data.log;
                    const modalBody = document.getElementById('modalBody');

                    let html = `
                        <div class="detail-section">
                            <h3>Informazioni Generali</h3>
                            <div class="detail-content">
Quando: ${formatDate(log.created_at)}
Chi: ${escapeHtml(log.user_email || 'Sistema')} (${escapeHtml(log.user_ruolo || '-')})
Azione: ${escapeHtml(log.azione)}
Entità: ${escapeHtml(log.entita)}${log.entita_id ? ' #' + log.entita_id : ''}
Stato: ${log.successo ? 'Successo ✓' : 'Fallito ✗'}
Livello: ${escapeHtml(log.livello)}
Categoria: ${escapeHtml(log.categoria || '-')}
                            </div>
                        </div>

                        <div class="detail-section">
                            <h3>Descrizione</h3>
                            <div class="detail-content">${escapeHtml(log.descrizione || 'Nessuna descrizione')}</div>
                        </div>
                    `;

                    if (log.dati_prima || log.dati_dopo) {
                        html += `
                            <div class="detail-section">
                                <h3>Modifiche Effettuate</h3>
                                <div class="diff-view">
                                    <div>
                                        <h4 style="margin-bottom: 0.5rem; color: #742a2a;">Prima</h4>
                                        <div class="diff-before">${log.dati_prima ? JSON.stringify(JSON.parse(log.dati_prima), null, 2) : 'N/A'}</div>
                                    </div>
                                    <div>
                                        <h4 style="margin-bottom: 0.5rem; color: #22543d;">Dopo</h4>
                                        <div class="diff-after">${log.dati_dopo ? JSON.stringify(JSON.parse(log.dati_dopo), null, 2) : 'N/A'}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    html += `
                        <div class="detail-section">
                            <h3>Dettagli Tecnici</h3>
                            <div class="detail-content">
IP: ${escapeHtml(log.user_ip || '-')}
User Agent: ${escapeHtml(log.user_agent || '-')}
URL: ${escapeHtml(log.request_url || '-')}
Metodo: ${escapeHtml(log.request_method || '-')}
                            </div>
                        </div>
                    `;

                    if (log.metadata) {
                        html += `
                            <div class="detail-section">
                                <h3>Metadata</h3>
                                <div class="detail-content">${JSON.stringify(JSON.parse(log.metadata), null, 2)}</div>
                            </div>
                        `;
                    }

                    modalBody.innerHTML = html;
                    document.getElementById('detailsModal').classList.add('active');
                } else {
                    alert('Errore nel caricamento dei dettagli');
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nel caricamento dei dettagli');
            }
        }

        // Chiudi modal
        function closeModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        // Reset filtri
        function resetFilters() {
            document.getElementById('filterForm').reset();
            reviewOnlyMode = false;
            currentPage = 1;
            loadLogs();
        }

        // Toggle modalità "solo da revisionare"
        function toggleReviewOnly() {
            reviewOnlyMode = !reviewOnlyMode;
            currentPage = 1;
            loadLogs();

            const btn = event.target;
            if (reviewOnlyMode) {
                btn.style.background = '#ed8936';
                btn.style.color = 'white';
            } else {
                btn.style.background = '#e2e8f0';
                btn.style.color = '#4a5568';
            }
        }

        // Export log
        async function exportLogs(formato) {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);

            const params = new URLSearchParams();
            params.append('action', 'export');
            params.append('formato', formato);

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            if (reviewOnlyMode) {
                params.append('richiede_review', '1');
            }

            window.location.href = `../api/audit-log.php?${params.toString()}`;
        }

        // Utility functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Ora';
            if (minutes < 60) return `${minutes}m fa`;
            if (hours < 24) return `${hours}h fa`;
            if (days < 7) return `${days}g fa`;

            return date.toLocaleString('it-IT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getInitials(email) {
            if (!email) return '?';
            return email.substring(0, 2).toUpperCase();
        }

        function getLivelloBadge(livello) {
            const badges = {
                'info': '<span class="badge badge-info">Info</span>',
                'warning': '<span class="badge badge-warning">Warning</span>',
                'error': '<span class="badge badge-danger">Error</span>',
                'critical': '<span class="badge badge-critical">Critical</span>'
            };
            return badges[livello] || badges.info;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showError(message) {
            const tbody = document.getElementById('logTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
                        <div class="empty-state-icon">⚠️</div>
                        <p>${escapeHtml(message)}</p>
                    </td>
                </tr>
            `;
        }

        // Chiudi modal cliccando fuori
        document.getElementById('detailsModal').addEventListener('click', (e) => {
            if (e.target.id === 'detailsModal') {
                closeModal();
            }
        });
    </script>
</body>
</html>
