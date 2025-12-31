<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// Verifica che sia admin
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

if (!$user || $user['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

try {
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
$colClienteExpr = ($hasClienteId && $hasUserId) ? "COALESCE(f.cliente_id, f.user_id)" : ($hasClienteId ? "f.cliente_id" : ($hasUserId ? "f.user_id" : "f.cliente_id"));
    $colTotale = $hasTotale ? 'totale' : ($hasImportoTotale ? 'importo_totale' : 'totale');
    $colPagamento = $hasDataPagamento ? 'data_pagamento' : null;
    $colPagamentoExpr = $colPagamento ? "COALESCE(STR_TO_DATE(f.$colPagamento, '%Y-%m-%d'), STR_TO_DATE(f.$colPagamento, '%d/%m/%Y'))" : "NULL";
$colScadenzaExpr = "COALESCE(STR_TO_DATE(f.data_scadenza, '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(f.data_scadenza, '%Y-%m-%d'), STR_TO_DATE(f.data_scadenza, '%d/%m/%Y'))";
$colScadenzaSelect = "DATE_FORMAT($colScadenzaExpr, '%Y-%m-%d')";

    $action = $_GET['action'] ?? 'eventi';

    switch ($action) {
        case 'eventi':
            // Recupera eventi calendario per un mese specifico
            $anno = (int)($_GET['anno'] ?? date('Y'));
            $mese = (int)($_GET['mese'] ?? date('n'));

            if ($mese < 1 || $mese > 12) {
                throw new Exception('Mese non valido');
            }

            // Primo e ultimo giorno del mese
            $primoGiorno = sprintf('%04d-%02d-01', $anno, $mese);
            $ultimoGiorno = date('Y-m-t', strtotime($primoGiorno));

            // Recupera eventi
            $stmt = $pdo->prepare("
                SELECT
                    f.id AS fattura_id,
                    f.numero_fattura,
                    u.azienda,
                    u.email AS cliente_email,
                    f.data_emissione,
                    $colScadenzaSelect AS data_scadenza,
                    $colPagamentoExpr AS data_pagamento,
                    f.$colTotale AS totale,
                    f.stato,
                    DATEDIFF($colScadenzaExpr, CURDATE()) AS giorni_a_scadenza,
                    CASE
                        WHEN $colScadenzaExpr < CURDATE() AND f.stato != 'pagata'
                        THEN DATEDIFF(CURDATE(), $colScadenzaExpr)
                        ELSE 0
                    END AS giorni_ritardo,
                    CASE
                        WHEN f.stato = 'scaduta' THEN 1
                        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF($colScadenzaExpr, CURDATE()) <= 7 THEN 2
                        WHEN f.stato IN ('emessa', 'inviata') THEN 3
                        ELSE 4
                    END AS priorita,
                    CASE
                        WHEN f.stato = 'pagata' THEN 'success'
                        WHEN f.stato = 'scaduta' THEN 'danger'
                        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF($colScadenzaExpr, CURDATE()) <= 7 THEN 'warning'
                        WHEN f.stato IN ('emessa', 'inviata') THEN 'info'
                        ELSE 'default'
                    END AS colore,
                    CASE
                        WHEN f.stato = 'pagata' THEN 'pagamento'
                        ELSE 'scadenza'
                    END AS tipo_evento
                FROM fatture f
                JOIN utenti u ON $colClienteExpr = u.id
                WHERE f.stato IN ('emessa', 'inviata', 'scaduta', 'pagata')
                  AND (
                    (f.stato != 'pagata' AND $colScadenzaExpr BETWEEN :primo_scad AND :ultimo_scad)
                    OR
                    (f.stato = 'pagata' AND $colPagamentoExpr BETWEEN :primo_pag AND :ultimo_pag)
                  )
                ORDER BY $colScadenzaExpr ASC
            ");
            $stmt->execute([
                'primo_scad' => $primoGiorno,
                'ultimo_scad' => $ultimoGiorno,
                'primo_pag' => $primoGiorno,
                'ultimo_pag' => $ultimoGiorno
            ]);
            $eventi = $stmt->fetchAll();

            // Formatta eventi per FullCalendar
            $eventiCalendario = [];
            foreach ($eventi as $evento) {
                if ($evento['tipo_evento'] === 'scadenza') {
                    // Evento scadenza
                    $title = $evento['azienda'] . ' - ' . $evento['numero_fattura'];
                    $description = '€' . number_format($evento['totale'], 2, ',', '.');

                    if ($evento['giorni_ritardo'] > 0) {
                        $description .= ' - Scaduta da ' . $evento['giorni_ritardo'] . ' giorni';
                    } elseif ($evento['giorni_a_scadenza'] >= 0) {
                        $description .= ' - Scade tra ' . $evento['giorni_a_scadenza'] . ' giorni';
                    }

                    $eventiCalendario[] = [
                        'id' => 'scad-' . $evento['fattura_id'],
                        'title' => $title,
                        'start' => $evento['data_scadenza'],
                        'description' => $description,
                        'backgroundColor' => getColorCode($evento['colore']),
                        'borderColor' => getColorCode($evento['colore']),
                        'textColor' => '#fff',
                        'extendedProps' => [
                            'fattura_id' => $evento['fattura_id'],
                            'numero_fattura' => $evento['numero_fattura'],
                            'azienda' => $evento['azienda'],
                            'email' => $evento['cliente_email'],
                            'importo' => $evento['totale'],
                            'stato' => $evento['stato'],
                            'tipo' => 'scadenza',
                            'priorita' => $evento['priorita']
                        ]
                    ];

                } else {
                    // Evento pagamento
                    $eventiCalendario[] = [
                        'id' => 'pag-' . $evento['fattura_id'],
                        'title' => '✓ ' . $evento['azienda'] . ' - ' . $evento['numero_fattura'],
                        'start' => $evento['data_pagamento'],
                        'description' => '€' . number_format($evento['totale'], 2, ',', '.') . ' - Pagata',
                        'backgroundColor' => '#10b981',
                        'borderColor' => '#10b981',
                        'textColor' => '#fff',
                        'extendedProps' => [
                            'fattura_id' => $evento['fattura_id'],
                            'numero_fattura' => $evento['numero_fattura'],
                            'azienda' => $evento['azienda'],
                            'importo' => $evento['totale'],
                            'stato' => 'pagata',
                            'tipo' => 'pagamento',
                            'priorita' => 4
                        ]
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'eventi' => $eventiCalendario
            ]);
            break;

        case 'statistiche':
            // Recupera statistiche scadenzario
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

            echo json_encode([
                'success' => true,
                'statistiche' => $stats
            ]);
            break;

        case 'lista':
            // Lista scadenze prossimi N giorni
            $giorni = min(365, max(1, (int)($_GET['giorni'] ?? 30)));

            $stmt = $pdo->prepare("
                SELECT
                    f.id AS fattura_id,
                    f.numero_fattura,
                    u.azienda,
                    u.email AS cliente_email,
                    $colScadenzaSelect AS data_scadenza,
                    f.$colTotale AS totale,
                    f.stato,
                    DATEDIFF($colScadenzaExpr, CURDATE()) AS giorni_a_scadenza,
                    CASE
                        WHEN $colScadenzaExpr < CURDATE() AND f.stato != 'pagata'
                        THEN DATEDIFF(CURDATE(), $colScadenzaExpr)
                        ELSE 0
                    END AS giorni_ritardo,
                    CASE
                        WHEN f.stato = 'scaduta' THEN 1
                        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF($colScadenzaExpr, CURDATE()) <= 7 THEN 2
                        WHEN f.stato IN ('emessa', 'inviata') THEN 3
                        ELSE 4
                    END AS priorita,
                    CASE
                        WHEN f.stato = 'pagata' THEN 'success'
                        WHEN f.stato = 'scaduta' THEN 'danger'
                        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF($colScadenzaExpr, CURDATE()) <= 7 THEN 'warning'
                        WHEN f.stato IN ('emessa', 'inviata') THEN 'info'
                        ELSE 'default'
                    END AS colore
                FROM fatture f
                JOIN utenti u ON $colClienteExpr = u.id
                WHERE $colScadenzaExpr BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :giorni DAY)
                  AND f.stato IN ('emessa', 'inviata', 'scaduta')
                ORDER BY priorita ASC, f.data_scadenza ASC
                LIMIT 100
            ");
            $stmt->execute(['giorni' => $giorni]);
            $scadenze = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'scadenze' => $scadenze
            ]);
            break;

        case 'riepilogo-giorno':
            // Riepilogo scadenze per un giorno specifico
            $data = $_GET['data'] ?? date('Y-m-d');

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                throw new Exception('Data non valida');
            }

            // Scadenze del giorno
            $stmt = $pdo->prepare("
                SELECT
                    f.id AS fattura_id,
                    f.numero_fattura,
                    u.azienda,
                    u.email AS cliente_email,
                    f.$colTotale AS totale,
                    f.stato,
                    CASE
                        WHEN f.stato = 'scaduta' THEN 1
                        WHEN f.stato IN ('emessa', 'inviata') AND DATEDIFF($colScadenzaExpr, CURDATE()) <= 7 THEN 2
                        WHEN f.stato IN ('emessa', 'inviata') THEN 3
                        ELSE 4
                    END AS priorita
                FROM fatture f
                JOIN utenti u ON $colClienteExpr = u.id
                WHERE DATE($colScadenzaExpr) = :data
                  AND f.stato IN ('emessa', 'inviata', 'scaduta')
                ORDER BY priorita ASC, f.$colTotale DESC
            ");
            $stmt->execute(['data' => $data]);
            $scadenze = $stmt->fetchAll();

            // Pagamenti del giorno
            $stmt = $pdo->prepare("
                SELECT
                    f.id AS fattura_id,
                    f.numero_fattura,
                    u.azienda,
                    f.$colTotale AS totale
                FROM fatture f
                JOIN utenti u ON $colClienteExpr = u.id
                WHERE $colPagamentoExpr IS NOT NULL
                  AND DATE($colPagamentoExpr) = :data
                  AND f.stato = 'pagata'
                ORDER BY f.$colTotale DESC
            ");
            $stmt->execute(['data' => $data]);
            $pagamenti = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $data,
                'scadenze' => $scadenze,
                'pagamenti' => $pagamenti,
                'totale_scadenze' => array_sum(array_column($scadenze, 'totale')),
                'totale_pagamenti' => array_sum(array_column($pagamenti, 'totale'))
            ]);
            break;

        default:
            throw new Exception('Azione non riconosciuta');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function getColorCode($colore) {
    return match($colore) {
        'success' => '#10b981',
        'danger' => '#ef4444',
        'warning' => '#f59e0b',
        'info' => '#3b82f6',
        default => '#6b7280'
    };
}
