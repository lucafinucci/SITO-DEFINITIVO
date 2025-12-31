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

header('Content-Type: text/html; charset=utf-8');

// Compatibilita schema fatture (legacy)
$stmt = $pdo->prepare("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'fatture'
      AND COLUMN_NAME IN ('cliente_id', 'user_id', 'totale', 'importo_totale', 'data_pagamento')
");
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
$hasClienteId = in_array('cliente_id', $cols, true);
$hasUserId = in_array('user_id', $cols, true);
$hasTotale = in_array('totale', $cols, true);
$hasImportoTotale = in_array('importo_totale', $cols, true);
$hasDataPagamento = in_array('data_pagamento', $cols, true);

$colCliente = $hasClienteId ? 'cliente_id' : ($hasUserId ? 'user_id' : 'cliente_id');
$colTotale = $hasTotale ? 'totale' : ($hasImportoTotale ? 'importo_totale' : 'totale');
$colPagamento = $hasDataPagamento ? 'data_pagamento' : null;

// Recupera statistiche (senza view)
$colPagamentoExpr = $colPagamento ? "COALESCE(STR_TO_DATE(f.$colPagamento, '%Y-%m-%d'), STR_TO_DATE(f.$colPagamento, '%d/%m/%Y'))" : "NULL";
$colScadenzaExpr = "COALESCE(STR_TO_DATE(f.data_scadenza, '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(f.data_scadenza, '%Y-%m-%d'), STR_TO_DATE(f.data_scadenza, '%d/%m/%Y'))";
$stmt = $pdo->query("
    SELECT
        SUM(CASE WHEN DATE($colScadenzaExpr) = CURDATE() AND f.stato != 'pagata' THEN 1 ELSE 0 END) AS scadenze_oggi,
        SUM(CASE WHEN DATE($colScadenzaExpr) = CURDATE() AND f.stato != 'pagata' THEN f.$colTotale ELSE 0 END) AS importo_oggi,
        SUM(CASE
            WHEN $colScadenzaExpr BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND f.stato != 'pagata'
            THEN 1 ELSE 0
        END) AS scadenze_settimana,
        SUM(CASE
            WHEN $colScadenzaExpr BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND f.stato != 'pagata'
            THEN f.$colTotale ELSE 0
        END) AS importo_settimana,
        SUM(CASE
            WHEN MONTH($colScadenzaExpr) = MONTH(CURDATE())
            AND YEAR($colScadenzaExpr) = YEAR(CURDATE())
            AND f.stato != 'pagata'
            THEN 1 ELSE 0
        END) AS scadenze_mese,
        SUM(CASE
            WHEN MONTH($colScadenzaExpr) = MONTH(CURDATE())
            AND YEAR($colScadenzaExpr) = YEAR(CURDATE())
            AND f.stato != 'pagata'
            THEN f.$colTotale ELSE 0
        END) AS importo_mese,
        SUM(CASE WHEN f.stato = 'scaduta' THEN 1 ELSE 0 END) AS fatture_scadute,
        SUM(CASE WHEN f.stato = 'scaduta' THEN f.$colTotale ELSE 0 END) AS importo_scaduto,
        SUM(CASE
            WHEN f.stato = 'pagata'
            AND MONTH($colPagamentoExpr) = MONTH(CURDATE())
            AND YEAR($colPagamentoExpr) = YEAR(CURDATE())
            THEN 1 ELSE 0
        END) AS pagate_mese,
        SUM(CASE
            WHEN f.stato = 'pagata'
            AND MONTH($colPagamentoExpr) = MONTH(CURDATE())
            AND YEAR($colPagamentoExpr) = YEAR(CURDATE())
            THEN f.$colTotale ELSE 0
        END) AS importo_pagato_mese
    FROM fatture f
    WHERE f.stato IN ('emessa', 'inviata', 'scaduta', 'pagata')
");
$stats = $stmt->fetch();

$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scadenzario Fatture - Admin Finch-AI</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <style>
        .scadenzario-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
            margin-bottom: 30px;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--border);
        }

        .stat-card.danger {
            border-left-color: #ef4444;
        }

        .stat-card.warning {
            border-left-color: #f59e0b;
        }

        .stat-card.info {
            border-left-color: #3b82f6;
        }

        .stat-card.success {
            border-left-color: #10b981;
        }

        .stat-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 4px;
        }

        .stat-description {
            font-size: 13px;
            color: #6c757d;
        }

        .calendar-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            color: #0f172a;
        }

        .fc {
            font-family: inherit;
        }
        .calendar-section .fc {
            color: #0f172a;
        }
        .calendar-section .fc-toolbar-title {
            color: #0f172a;
            font-weight: 700;
        }
        .calendar-section .fc-col-header-cell-cushion,
        .calendar-section .fc-daygrid-day-number {
            color: #0f172a;
        }
        .calendar-section .fc-button-primary {
            background: #1f2937;
            border-color: #1f2937;
            color: #f8fafc;
        }
        .calendar-section .fc-button-primary:not(:disabled).fc-button-active,
        .calendar-section .fc-button-primary:not(:disabled):active {
            background: #8b5cf6;
            border-color: #8b5cf6;
            color: #fff;
        }
        .calendar-section .fc-button-primary:focus {
            box-shadow: none;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 12px;
        }

        .fc-daygrid-day.fc-day-today {
            background: #f0f9ff !important;
        }

        .sidebar-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .scadenza-item {
            padding: 15px;
            border-left: 4px solid var(--border);
            margin-bottom: 12px;
            border-radius: 4px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s;
        }

        .scadenza-item:hover {
            background: #e9ecef;
            transform: translateX(4px);
        }

        .scadenza-item.priorita-1 {
            border-left-color: #ef4444;
        }

        .scadenza-item.priorita-2 {
            border-left-color: #f59e0b;
        }

        .scadenza-item.priorita-3 {
            border-left-color: #3b82f6;
        }

        .scadenza-azienda {
            font-weight: 600;
            color: #212529;
            margin-bottom: 4px;
        }

        .scadenza-details {
            font-size: 13px;
            color: #6c757d;
        }

        .scadenza-importo {
            font-size: 16px;
            font-weight: 700;
            color: #8b5cf6;
            margin-top: 4px;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-overlay.active {
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
        }

        .detail-value {
            color: #212529;
        }

        .legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #0f172a;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #0f172a;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }

        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .toggle-btn {
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .toggle-btn.active {
            background: #8b5cf6;
            color: white;
            border-color: #8b5cf6;
        }

        .list-view {
            display: none;
        }

        .list-view.active {
            display: block;
        }

        .calendar-view.active {
            display: block;
        }

        .calendar-view {
            display: none;
        }
    </style>
</head>
<body>
    <div class="scadenzario-container">
        <!-- Header -->
        <div class="header-section">
            <div class="header-title">
                <div>
                    <h1 style="margin: 0 0 8px 0;">📅 Scadenzario Fatture</h1>
                    <p style="margin: 0; color: #6c757d;">Visualizza e gestisci scadenze e pagamenti</p>
                </div>
                <div>
                    <a href="fatture.php" class="btn ghost">← Torna a Fatture</a>
                    <a href="gestione-servizi.php" class="btn ghost">Dashboard</a>
                </div>
            </div>

            <!-- Statistiche -->
            <div class="stats-grid">
                <div class="stat-card danger">
                    <div class="stat-label">Fatture Scadute</div>
                    <div class="stat-value"><?= $stats['fatture_scadute'] ?? 0 ?></div>
                    <div class="stat-description">€<?= number_format($stats['importo_scaduto'] ?? 0, 2, ',', '.') ?></div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-label">Scadenze Oggi</div>
                    <div class="stat-value"><?= $stats['scadenze_oggi'] ?? 0 ?></div>
                    <div class="stat-description">€<?= number_format($stats['importo_oggi'] ?? 0, 2, ',', '.') ?></div>
                </div>

                <div class="stat-card info">
                    <div class="stat-label">Scadenze Questa Settimana</div>
                    <div class="stat-value"><?= $stats['scadenze_settimana'] ?? 0 ?></div>
                    <div class="stat-description">€<?= number_format($stats['importo_settimana'] ?? 0, 2, ',', '.') ?></div>
                </div>

                <div class="stat-card success">
                    <div class="stat-label">Pagamenti Questo Mese</div>
                    <div class="stat-value"><?= $stats['pagate_mese'] ?? 0 ?></div>
                    <div class="stat-description">€<?= number_format($stats['importo_pagato_mese'] ?? 0, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Toggle Visualizzazione -->
        <div class="view-toggle">
            <button class="toggle-btn active" onclick="switchView('calendar')">📅 Vista Calendario</button>
            <button class="toggle-btn" onclick="switchView('list')">📋 Vista Lista</button>
        </div>

        <!-- Vista Calendario -->
        <div class="calendar-view active">
            <div class="calendar-section">
                <div id="calendar"></div>

                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #ef4444;"></div>
                        <span>Scaduta</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #f59e0b;"></div>
                        <span>Scade entro 7 giorni</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #3b82f6;"></div>
                        <span>In scadenza</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #10b981;"></div>
                        <span>Pagata</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Lista -->
        <div class="list-view">
            <div class="sidebar-section">
                <h3>Prossime Scadenze (30 giorni)</h3>
                <div id="lista-scadenze"></div>
            </div>
        </div>
    </div>

    <!-- Modal Dettagli -->
    <div id="modal-dettagli" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title" style="margin: 0;"></h3>
                <button class="modal-close" onclick="chiudiModal()">×</button>
            </div>
            <div class="modal-body" id="modal-body"></div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn ghost" onclick="chiudiModal()">Chiudi</button>
                <button class="btn primary" id="modal-action-btn"></button>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
        let calendar;
        let currentView = 'calendar';

        document.addEventListener('DOMContentLoaded', function() {
            initCalendar();
            loadListaScadenze();
        });

        function initCalendar() {
            const calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'it',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,listWeek'
                },
                buttonText: {
                    today: 'Oggi',
                    month: 'Mese',
                    week: 'Settimana',
                    list: 'Lista'
                },
                events: function(info, successCallback, failureCallback) {
                    const anno = info.start.getFullYear();
                    const mese = info.start.getMonth() + 1;

                    fetch(`/area-clienti/api/scadenzario.php?action=eventi&anno=${anno}&mese=${mese}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                successCallback(data.eventi);
                            } else {
                                failureCallback(data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Errore caricamento eventi:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    mostraDettagliFattura(info.event.extendedProps);
                },
                height: 'auto',
                firstDay: 1 // Lunedì
            });

            calendar.render();
        }

        function switchView(view) {
            currentView = view;

            // Aggiorna pulsanti
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Mostra/nascondi viste
            document.querySelector('.calendar-view').classList.toggle('active', view === 'calendar');
            document.querySelector('.list-view').classList.toggle('active', view === 'list');

            if (view === 'list') {
                loadListaScadenze();
            }
        }

        function loadListaScadenze() {
            fetch('/area-clienti/api/scadenzario.php?action=lista&giorni=30')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderListaScadenze(data.scadenze);
                    }
                })
                .catch(error => console.error('Errore:', error));
        }

        function renderListaScadenze(scadenze) {
            const container = document.getElementById('lista-scadenze');

            if (scadenze.length === 0) {
                container.innerHTML = '<p class="muted">Nessuna scadenza nei prossimi 30 giorni</p>';
                return;
            }

            container.innerHTML = scadenze.map(scadenza => `
                <div class="scadenza-item priorita-${scadenza.priorita}" onclick='mostraDettagliFatturaId(${scadenza.fattura_id})'>
                    <div class="scadenza-azienda">${escapeHtml(scadenza.azienda)}</div>
                    <div class="scadenza-details">
                        Fattura: ${escapeHtml(scadenza.numero_fattura)} -
                        Scadenza: ${formatDate(scadenza.data_scadenza)}
                        ${scadenza.giorni_ritardo > 0 ? `<span style="color: #ef4444;">- Scaduta da ${scadenza.giorni_ritardo} giorni</span>` : ''}
                        ${scadenza.giorni_a_scadenza >= 0 && scadenza.giorni_a_scadenza <= 7 ? `<span style="color: #f59e0b;">- Scade tra ${scadenza.giorni_a_scadenza} giorni</span>` : ''}
                    </div>
                    <div class="scadenza-importo">€${formatNumber(scadenza.totale)}</div>
                </div>
            `).join('');
        }

        function mostraDettagliFattura(props) {
            const modal = document.getElementById('modal-dettagli');
            const title = document.getElementById('modal-title');
            const body = document.getElementById('modal-body');
            const actionBtn = document.getElementById('modal-action-btn');

            title.textContent = `Fattura ${props.numero_fattura}`;

            body.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Azienda:</div>
                    <div class="detail-value">${escapeHtml(props.azienda)}</div>
                </div>
                ${props.email ? `
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">${escapeHtml(props.email)}</div>
                </div>
                ` : ''}
                <div class="detail-row">
                    <div class="detail-label">Importo:</div>
                    <div class="detail-value" style="font-size: 20px; font-weight: 700; color: #8b5cf6;">
                        €${formatNumber(props.importo)}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Stato:</div>
                    <div class="detail-value">
                        <span class="badge ${getStatoClass(props.stato)}">${getStatoLabel(props.stato)}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tipo:</div>
                    <div class="detail-value">${props.tipo === 'scadenza' ? '📅 Scadenza' : '✓ Pagamento'}</div>
                </div>
            `;

            actionBtn.textContent = '👁️ Visualizza Fattura';
            actionBtn.onclick = () => {
                window.location.href = `/area-clienti/admin/fattura-dettaglio.php?id=${props.fattura_id}`;
            };

            modal.classList.add('active');
        }

        function mostraDettagliFatturaId(fatturaId) {
            window.location.href = `/area-clienti/admin/fattura-dettaglio.php?id=${fatturaId}`;
        }

        function chiudiModal() {
            document.getElementById('modal-dettagli').classList.remove('active');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('it-IT');
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('it-IT', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function getStatoClass(stato) {
            const classes = {
                'pagata': 'success',
                'scaduta': 'danger',
                'inviata': 'info',
                'emessa': 'default'
            };
            return classes[stato] || 'default';
        }

        function getStatoLabel(stato) {
            const labels = {
                'pagata': 'Pagata',
                'scaduta': 'Scaduta',
                'inviata': 'Inviata',
                'emessa': 'Emessa'
            };
            return labels[stato] || stato;
        }

        // Chiudi modal cliccando fuori
        document.getElementById('modal-dettagli').addEventListener('click', function(e) {
            if (e.target === this) {
                chiudiModal();
            }
        });
    </script>
</body>
</html>
