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

$csrfToken = $_SESSION['csrf_token'] ?? '';

$hasWebappUrl = false;
try {
    $stmtCols = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'utenti'
          AND COLUMN_NAME = 'webapp_url'
    ");
    $stmtCols->execute();
    $hasWebappUrl = (bool)$stmtCols->fetchColumn();
} catch (PDOException $e) {
    $hasWebappUrl = false;
}

// Recupera tutti i clienti con i loro servizi
$clientiSelect = '
    SELECT
        u.id,
        u.nome,
        u.cognome,
        u.email,
        u.azienda,
        u.created_at,
        u.cliente_dal' . ($hasWebappUrl ? ', u.webapp_url' : ', NULL AS webapp_url') . '
    FROM utenti u
    WHERE u.ruolo != "admin"
    ORDER BY u.azienda ASC, u.cognome ASC
';
$stmt = $pdo->prepare($clientiSelect);
$stmt->execute();
$clienti = $stmt->fetchAll();

// Recupera tutti i servizi disponibili
$stmt = $pdo->prepare('
    SELECT id, nome, descrizione, codice, prezzo_mensile, costo_per_pagina, attivo
    FROM servizi
    WHERE attivo = 1
    ORDER BY nome ASC
');
$stmt->execute();
$serviziDisponibili = $stmt->fetchAll();
$serviziById = [];
foreach ($serviziDisponibili as $svcItem) {
    $serviziById[(int)$svcItem['id']] = $svcItem;
}

// Quote servizi (documenti/mese)
$serviziQuote = [];
$stmt = $pdo->prepare('SELECT servizio_id, quota_documenti_mese FROM servizi_quote');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    $serviziQuote[(int)$row['servizio_id']] = $row['quota_documenti_mese'] !== null ? (int)$row['quota_documenti_mese'] : null;
}
$currentPeriod = date('Y-m');

// Versioni servizi
$versioniByServizio = [];
if (!empty($serviziDisponibili)) {
    $serviziIds = array_map(function ($svc) {
        return (int)$svc['id'];
    }, $serviziDisponibili);
    $placeholders = implode(',', array_fill(0, count($serviziIds), '?'));
    $stmt = $pdo->prepare("
        SELECT
            sv.id,
            sv.servizio_id,
            sv.action,
            sv.changed_fields,
            sv.created_at,
            u.nome,
            u.cognome,
            u.email
        FROM servizi_versioni sv
        LEFT JOIN utenti u ON sv.changed_by = u.id
        WHERE sv.servizio_id IN ($placeholders)
        ORDER BY sv.created_at DESC
    ");
    $stmt->execute($serviziIds);
    foreach ($stmt->fetchAll() as $row) {
        $versioniByServizio[$row['servizio_id']][] = $row;
    }
}

// Catalogo pacchetti
$stmt = $pdo->prepare('SELECT id, nome, descrizione, prezzo_mensile, attivo FROM pacchetti ORDER BY nome ASC');
$stmt->execute();
$pacchettiList = $stmt->fetchAll();

// Catalogo servizi on-demand
$stmt = $pdo->prepare('SELECT id, nome, descrizione, prezzo_unitario, attivo FROM servizi_on_demand ORDER BY nome ASC');
$stmt->execute();
$onDemandList = $stmt->fetchAll();

// Servizi per pacchetto
$stmt = $pdo->prepare('
    SELECT ps.pacchetto_id, s.id AS servizio_id, s.nome
    FROM pacchetti_servizi ps
    JOIN servizi s ON ps.servizio_id = s.id
    ORDER BY s.nome ASC
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    $pacchettiServizi[(int)$row['pacchetto_id']][] = $row;
}

// Recupera servizi attivi per ogni cliente (ora a livello aziendale)
$serviziClienti = [];
$stmt = $pdo->prepare('
    SELECT
        ase.id,
        u.id as user_id,
        ase.servizio_id,
        ase.data_attivazione,
        ase.data_disattivazione,
        ase.stato,
        ase.note,
        s.nome as servizio_nome,
        s.codice as servizio_codice,
        s.prezzo_mensile,
        s.costo_per_pagina,
        app.prezzo_mensile AS prezzo_personalizzato,
        app.costo_per_pagina AS costo_per_pagina_personalizzato,
        COALESCE(app.prezzo_mensile, s.prezzo_mensile) AS prezzo_finale,
        COALESCE(app.costo_per_pagina, s.costo_per_pagina) AS costo_per_pagina_finale
    FROM utenti u
    INNER JOIN aziende_servizi ase ON u.azienda_id = ase.azienda_id
    JOIN servizi s ON ase.servizio_id = s.id
    LEFT JOIN aziende_prezzi_personalizzati app
        ON app.azienda_id = u.azienda_id AND app.servizio_id = s.id
    WHERE ase.stato = "attivo" AND u.azienda_id IS NOT NULL
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    $serviziClienti[$row['user_id']][] = $row;
}

// Popolarita servizi per suggerimenti upsell
$servicePopularity = [];
$stmt = $pdo->prepare('
    SELECT servizio_id, COUNT(*) AS tot
    FROM utenti_servizi
    WHERE stato = "attivo"
    GROUP BY servizio_id
');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    $servicePopularity[(int)$row['servizio_id']] = (int)$row['tot'];
}

$clientIds = array_column($clienti, 'id');
$clientIdSet = array_flip($clientIds);

// Churn rate (ultimi 30 giorni) basato su servizi disattivati
$churnStart = (new DateTime('today 00:00:00'))->modify('-30 days');
$serviziAttiviStart = 0;
$serviziDisattivati = 0;
if (!empty($clientIds)) {
    $clientPlaceholders = implode(',', array_fill(0, count($clientIds), '?'));
    $paramsStart = $clientIds;
    $paramsStart[] = $churnStart->format('Y-m-d');
    $paramsStart[] = $churnStart->format('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM utenti_servizi us
        JOIN utenti u ON u.id = us.user_id
        WHERE u.id IN ($clientPlaceholders)
          AND u.ruolo != 'admin'
          AND us.data_attivazione < ?
          AND (us.data_disattivazione IS NULL OR us.data_disattivazione >= ?)
    ");
    $stmt->execute($paramsStart);
    $serviziAttiviStart = (int)$stmt->fetchColumn();

    $paramsStop = $clientIds;
    $paramsStop[] = $churnStart->format('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM utenti_servizi us
        JOIN utenti u ON u.id = us.user_id
        WHERE u.id IN ($clientPlaceholders)
          AND u.ruolo != 'admin'
          AND us.data_disattivazione IS NOT NULL
          AND us.data_disattivazione >= ?
    ");
    $stmt->execute($paramsStop);
    $serviziDisattivati = (int)$stmt->fetchColumn();
}

$churnRate = $serviziAttiviStart > 0 ? ($serviziDisattivati / $serviziAttiviStart) : 0;
$churnRatePct = $churnRate * 100;

// === Dati profilo cliente avanzato ===
$notesByClient = [];
$tagsByClient = [];
$docsByClient = [];
$eventsByClient = [];
$prezziPersonalizzati = [];
$costiPaginaPersonalizzati = [];
$scontiByClient = [];
$couponByClient = [];
$pacchettiByClient = [];
$pacchettiServiziByClient = [];
$clientiQuote = [];
$acquistiByClient = [];
$usageByClient = [];
$timelineByClient = [];
$allTags = [];
$couponsList = [];
$contractsByClient = [];
$renewalAlerts = [];
$hasContrattoServizio = false;
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM clienti_contratti LIKE 'servizio_id'");
    $stmt->execute();
    $hasContrattoServizio = (bool)$stmt->fetch();
} catch (PDOException $e) {
    $hasContrattoServizio = false;
}

if (!empty($clientIds)) {
    $placeholders = implode(',', array_fill(0, count($clientIds), '?'));

    // Note
    $stmt = $pdo->prepare("
        SELECT cliente_id, note, created_at
        FROM clienti_note
        WHERE cliente_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $notesByClient[$row['cliente_id']][] = $row;
    }

    // Tag
    $stmt = $pdo->prepare("
        SELECT r.cliente_id, t.id, t.nome, t.colore
        FROM clienti_tag_rel r
        JOIN clienti_tag t ON r.tag_id = t.id
        WHERE r.cliente_id IN ($placeholders)
        ORDER BY t.nome ASC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $tagsByClient[$row['cliente_id']][] = $row;
    }

    // Catalogo tag globale
    $stmt = $pdo->prepare('SELECT id, nome, colore FROM clienti_tag ORDER BY nome ASC');
    $stmt->execute();
    $allTags = $stmt->fetchAll();

    // Prezzi personalizzati (ora a livello aziendale)
    $stmt = $pdo->prepare("
        SELECT u.id as cliente_id, app.servizio_id, app.prezzo_mensile, app.costo_per_pagina
        FROM utenti u
        INNER JOIN aziende_prezzi_personalizzati app ON u.azienda_id = app.azienda_id
        WHERE u.id IN ($placeholders)
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $prezziPersonalizzati[$row['cliente_id']][(int)$row['servizio_id']] = (float)$row['prezzo_mensile'];
        $costiPaginaPersonalizzati[$row['cliente_id']][(int)$row['servizio_id']] =
            $row['costo_per_pagina'] !== null ? (float)$row['costo_per_pagina'] : null;
    }

    // Quote personalizzate per cliente (ora a livello aziendale)
    $stmt = $pdo->prepare("
        SELECT u.id as cliente_id, aq.servizio_id, aq.quota_documenti_mese
        FROM utenti u
        INNER JOIN aziende_quote aq ON u.azienda_id = aq.azienda_id
        WHERE u.id IN ($placeholders)
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $clientiQuote[$row['cliente_id']][(int)$row['servizio_id']] = $row['quota_documenti_mese'] !== null ? (int)$row['quota_documenti_mese'] : null;
    }

    // Acquisti on-demand per cliente
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.cliente_id,
            a.servizio_id,
            a.quantita,
            a.prezzo_unitario,
            a.totale,
            a.data_acquisto,
            a.stato,
            s.nome
        FROM clienti_acquisti_onetime a
        JOIN servizi_on_demand s ON a.servizio_id = s.id
        WHERE a.cliente_id IN ($placeholders)
        ORDER BY a.data_acquisto DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $acquistiByClient[$row['cliente_id']][] = $row;
    }

    // Utilizzo quote periodo corrente
    $stmt = $pdo->prepare("
        SELECT cliente_id, servizio_id, documenti_usati
        FROM servizi_quota_uso
        WHERE cliente_id IN ($placeholders)
          AND periodo = ?
    ");
    $params = $clientIds;
    $params[] = $currentPeriod;
    $stmt->execute($params);
    foreach ($stmt->fetchAll() as $row) {
        $usageByClient[$row['cliente_id']][(int)$row['servizio_id']] = (int)$row['documenti_usati'];
    }

    // Pacchetti assegnati ai clienti
    $stmt = $pdo->prepare("
        SELECT
            cp.id,
            cp.cliente_id,
            cp.pacchetto_id,
            cp.data_inizio,
            cp.data_fine,
            cp.attivo,
            p.nome,
            p.prezzo_mensile
        FROM clienti_pacchetti cp
        JOIN pacchetti p ON cp.pacchetto_id = p.id
        WHERE cp.cliente_id IN ($placeholders)
          AND cp.attivo = 1
        ORDER BY cp.created_at DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $pacchettiByClient[$row['cliente_id']][] = $row;
    }

    foreach ($pacchettiByClient as $clienteId => $bundles) {
        foreach ($bundles as $bundle) {
            $bundleId = (int)$bundle['pacchetto_id'];
            if (!empty($pacchettiServizi[$bundleId])) {
                foreach ($pacchettiServizi[$bundleId] as $svc) {
                    $pacchettiServiziByClient[$clienteId][(int)$svc['servizio_id']] = true;
                }
            }
        }
    }

    // Sconti temporanei
    $stmt = $pdo->prepare("
        SELECT id, cliente_id, servizio_id, tipo, valore, data_inizio, data_fine, note, attivo
        FROM clienti_sconti
        WHERE cliente_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $scontiByClient[$row['cliente_id']][] = $row;
    }

    // Coupon assegnati
    $stmt = $pdo->prepare("
        SELECT
            cc.id,
            cc.cliente_id,
            cc.usato,
            cc.assegnato_il,
            cc.usato_il,
            c.id AS coupon_id,
            c.codice,
            c.tipo,
            c.valore,
            c.data_inizio,
            c.data_fine,
            c.max_usi,
            c.usi,
            c.attivo
        FROM clienti_coupon cc
        JOIN coupon c ON cc.coupon_id = c.id
        WHERE cc.cliente_id IN ($placeholders)
        ORDER BY cc.assegnato_il DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $couponByClient[$row['cliente_id']][] = $row;
    }

    // Contratti per cliente
    if ($hasContrattoServizio) {
        $stmt = $pdo->prepare("
            SELECT c.id, c.cliente_id, c.servizio_id, c.titolo, c.data_inizio, c.data_scadenza, c.valore_annuo, c.stato,
                   s.nome AS servizio_nome
            FROM clienti_contratti c
            LEFT JOIN servizi s ON c.servizio_id = s.id
            WHERE c.cliente_id IN ($placeholders)
            ORDER BY c.data_scadenza ASC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT id, cliente_id, titolo, data_inizio, data_scadenza, valore_annuo, stato
            FROM clienti_contratti
            WHERE cliente_id IN ($placeholders)
            ORDER BY data_scadenza ASC
        ");
    }
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $contractsByClient[$row['cliente_id']][] = $row;
    }

    // Alert scadenze (entro 30 giorni)
    $stmt = $pdo->prepare("
        SELECT c.id, c.cliente_id, c.titolo, c.data_scadenza, c.valore_annuo, u.azienda
        FROM clienti_contratti c
        JOIN utenti u ON u.id = c.cliente_id
        WHERE c.data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY c.data_scadenza ASC
    ");
    $stmt->execute();
    $renewalAlerts = $stmt->fetchAll();

    // Documenti
    $stmt = $pdo->prepare("
        SELECT cliente_id, id, nome, file_path, created_at
        FROM clienti_documenti
        WHERE cliente_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $docsByClient[$row['cliente_id']][] = $row;
    }

    // Eventi manuali/email
    $stmt = $pdo->prepare("
        SELECT cliente_id, tipo, titolo, dettagli, created_at
        FROM clienti_eventi
        WHERE cliente_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $eventsByClient[$row['cliente_id']][] = $row;
    }

    // Eventi servizi
    $stmt = $pdo->prepare("
        SELECT us.user_id AS cliente_id, us.data_attivazione, us.data_disattivazione, s.nome
        FROM utenti_servizi us
        JOIN servizi s ON us.servizio_id = s.id
        WHERE us.user_id IN ($placeholders)
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        if (!empty($row['data_attivazione'])) {
            $timelineByClient[$row['cliente_id']][] = [
                'date' => $row['data_attivazione'],
                'type' => 'servizio',
                'title' => 'Servizio attivato: ' . $row['nome'],
                'details' => null
            ];
        }
        if (!empty($row['data_disattivazione'])) {
            $timelineByClient[$row['cliente_id']][] = [
                'date' => $row['data_disattivazione'],
                'type' => 'servizio',
                'title' => 'Servizio disattivato: ' . $row['nome'],
                'details' => null
            ];
        }
    }

    // Eventi richieste addestramento
    $stmt = $pdo->prepare("
        SELECT user_id AS cliente_id, tipo_modello, created_at
        FROM richieste_addestramento
        WHERE user_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($clientIds);
    foreach ($stmt->fetchAll() as $row) {
        $timelineByClient[$row['cliente_id']][] = [
            'date' => $row['created_at'],
            'type' => 'richiesta',
            'title' => 'Richiesta addestramento: ' . $row['tipo_modello'],
            'details' => null
        ];
    }

    // Unifica eventi manuali/email nella timeline
    foreach ($eventsByClient as $clienteId => $eventi) {
        foreach ($eventi as $evento) {
            $timelineByClient[$clienteId][] = [
                'date' => $evento['created_at'],
                'type' => $evento['tipo'],
                'title' => $evento['titolo'],
                'details' => $evento['dettagli']
            ];
        }
    }

    // Ordina timeline
    foreach ($timelineByClient as $clienteId => $items) {
        usort($items, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });
        $timelineByClient[$clienteId] = $items;
    }
}

// === KPI principali ===
$totaleClienti = count($clienti);
$totaleServiziAttivi = 0;
$totalePacchettiAttivi = 0;
$ricaviMensili = 0;
foreach ($serviziClienti as $servizi) {
    $clienteId = $servizi[0]['user_id'] ?? null;
    if (!$clienteId || !isset($clientIdSet[$clienteId])) {
        continue;
    }
    $bundleServices = $clienteId ? ($pacchettiServiziByClient[$clienteId] ?? []) : [];
    foreach ($servizi as $s) {
        if ($bundleServices && isset($bundleServices[(int)$s['servizio_id']])) {
            continue;
        }
        $totaleServiziAttivi++;
        $ricaviMensili += (float)$s['prezzo_finale'];
    }
}

foreach ($pacchettiByClient as $clienteId => $bundles) {
    if (!$clienteId || !isset($clientIdSet[$clienteId])) {
        continue;
    }
    foreach ($bundles as $bundle) {
        $totalePacchettiAttivi++;
        $ricaviMensili += (float)$bundle['prezzo_mensile'];
    }
}

// CLV (stima semplice): ARPU / churn_rate
$arpu = $totaleClienti > 0 ? ($ricaviMensili / $totaleClienti) : 0;
$clv = $churnRate > 0 ? ($arpu / $churnRate) : 0;
$clvDisplay = $churnRate > 0 ? '?' . number_format($clv, 0, ',', '.') : 'n/a';

// Report mensile (default: mese precedente)
$reportMonth = (new DateTime('first day of last month 00:00:00'))->format('Y-m');


// Catalogo coupon globale
$stmt = $pdo->prepare('
    SELECT id, codice, tipo, valore, data_inizio, data_fine, max_usi, usi, attivo
    FROM coupon
    ORDER BY created_at DESC
');
$stmt->execute();
$couponsList = $stmt->fetchAll();

// === Previsioni ricavi ===
$forecastMonths = 6;
$forecast = [];
$baseMRR = $ricaviMensili;
$forecastDate = new DateTime('first day of next month 00:00:00');
for ($i = 0; $i < $forecastMonths; $i++) {
    $monthLabel = $forecastDate->format('m/Y');
    $projected = $baseMRR * pow((1 - $churnRate), $i + 1);
    $forecast[] = [
        'label' => $monthLabel,
        'mrr' => $projected
    ];
    $forecastDate->modify('+1 month');
}

// === Metriche per servizio ===
$marginPct = (float)Config::get('SERVICE_MARGIN_PCT', 35);
$stmt = $pdo->prepare('
    SELECT
        s.id,
        s.nome,
        s.prezzo_mensile,
        COALESCE(SUM(CASE WHEN us.stato = "attivo" THEN COALESCE(pp.prezzo_mensile, s.prezzo_mensile) END), 0) AS mrr,
        SUM(CASE WHEN us.stato = "attivo" THEN 1 ELSE 0 END) AS attivi,
        COUNT(us.id) AS totali
    FROM servizi s
    LEFT JOIN utenti_servizi us ON us.servizio_id = s.id
    LEFT JOIN clienti_prezzi_personalizzati pp
        ON pp.servizio_id = s.id AND pp.cliente_id = us.user_id
    GROUP BY s.id
    ORDER BY totali DESC, attivi DESC, s.nome ASC
');
$stmt->execute();
$serviceStats = $stmt->fetchAll();

foreach ($serviceStats as &$service) {
    $mrr = (float)$service['mrr'];
    $service['mrr'] = $mrr;
    $service['margin'] = $mrr * ($marginPct / 100);
}
unset($service);

// === Grafici temporali (range giorni) ===
$rangeDays = (int)($_GET['range'] ?? 90);
if (!in_array($rangeDays, [30, 90, 365], true)) {
    $rangeDays = 90;
}

$end = new DateTime('today 00:00:00');
$start = (clone $end)->modify('-' . ($rangeDays - 1) . ' days');
$endNext = (clone $end)->modify('+1 day');

$days = [];
$labels = [];
for ($i = 0; $i < $rangeDays; $i++) {
    $dt = (clone $start)->modify("+{$i} days");
    $days[] = $dt->format('Y-m-d');
    $labels[] = $dt->format('d/m');
}

function buildSeries(array $daysList, array $countsByDay) {
    $series = [];
    foreach ($daysList as $day) {
        $series[] = (int)($countsByDay[$day] ?? 0);
    }
    return $series;
}

function renderSparkline(array $values, $stroke, $label, array $xLabels) {
    $count = count($values);
    if ($count === 0) {
        return '';
    }

    $width = 320;
    $height = 120;
    $padding = 12;

    $min = min($values);
    $max = max($values);
    $range = ($max - $min) ?: 1;

    $points = [];
    $circles = [];
    for ($i = 0; $i < $count; $i++) {
        $x = $padding + ($count === 1 ? 0 : ($i / ($count - 1)) * ($width - 2 * $padding));
        $y = $height - $padding - (($values[$i] - $min) / $range) * ($height - 2 * $padding);
        $points[] = number_format($x, 2, '.', '') . ' ' . number_format($y, 2, '.', '');
        $labelText = htmlspecialchars($xLabels[$i] ?? '', ENT_QUOTES, 'UTF-8');
        $valueText = htmlspecialchars((string)$values[$i], ENT_QUOTES, 'UTF-8');
        $circles[] = '<circle cx="' . number_format($x, 2, '.', '') . '" cy="' . number_format($y, 2, '.', '') . '" r="3" fill="' . $stroke . '"><title>' . $labelText . ': ' . $valueText . '</title></circle>';
    }

    $path = 'M ' . implode(' L ', $points);
    $xFirst = $padding;
    $xLast = $padding + ($width - 2 * $padding);
    $yBottom = $height - $padding;
    $area = $path . ' L ' . number_format($xLast, 2, '.', '') . ' ' . number_format($yBottom, 2, '.', '') .
        ' L ' . number_format($xFirst, 2, '.', '') . ' ' . number_format($yBottom, 2, '.', '') . ' Z';

    return '<svg class="sparkline" viewBox="0 0 ' . $width . ' ' . $height . '" preserveAspectRatio="none" role="img" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">' .
        '<path d="' . $area . '" fill="' . $stroke . '" fill-opacity="0.15" stroke="none"></path>' .
        '<path d="' . $path . '" fill="none" stroke="' . $stroke . '" stroke-width="2"></path>' .
        implode('', $circles) .
        '</svg>';
}

// Attivazioni / disattivazioni per giorno
$stmt = $pdo->prepare('
    SELECT DATE(data_attivazione) day, COUNT(*) cnt
    FROM utenti_servizi
    WHERE data_attivazione >= :start AND data_attivazione < :end
    GROUP BY day
');
$stmt->execute(['start' => $start->format('Y-m-d'), 'end' => $endNext->format('Y-m-d')]);
$attivazioni = [];
foreach ($stmt->fetchAll() as $row) {
    $attivazioni[$row['day']] = (int)$row['cnt'];
}

$stmt = $pdo->prepare('
    SELECT DATE(data_disattivazione) day, COUNT(*) cnt
    FROM utenti_servizi
    WHERE data_disattivazione IS NOT NULL
      AND data_disattivazione >= :start AND data_disattivazione < :end
    GROUP BY day
');
$stmt->execute(['start' => $start->format('Y-m-d'), 'end' => $endNext->format('Y-m-d')]);
$disattivazioni = [];
foreach ($stmt->fetchAll() as $row) {
    $disattivazioni[$row['day']] = (int)$row['cnt'];
}

// Conteggio servizi attivi a inizio periodo
$stmt = $pdo->prepare('
    SELECT COUNT(*) 
    FROM utenti_servizi
    WHERE data_attivazione < :start1
      AND (data_disattivazione IS NULL OR data_disattivazione >= :start2)
');
$stmt->execute([
    'start1' => $start->format('Y-m-d'),
    'start2' => $start->format('Y-m-d'),
]);
$activeStart = (int)$stmt->fetchColumn();

$seriesServiziAttivi = [];
$running = $activeStart;
foreach ($days as $day) {
    $running += (int)($attivazioni[$day] ?? 0);
    $running -= (int)($disattivazioni[$day] ?? 0);
    $seriesServiziAttivi[] = $running;
}
$serviziAttiviDelta = null;
if (count($seriesServiziAttivi) > 1) {
    $first = $seriesServiziAttivi[0];
    $last = $seriesServiziAttivi[count($seriesServiziAttivi) - 1];
    if ($first === 0) {
        $serviziAttiviDelta = $last > 0 ? 100 : 0;
    } else {
        $serviziAttiviDelta = (($last - $first) / $first) * 100;
    }
}

// Nuovi clienti per giorno
$stmt = $pdo->prepare('
    SELECT DATE(created_at) day, COUNT(*) cnt
    FROM utenti
    WHERE ruolo != "admin"
      AND created_at >= :start AND created_at < :end
    GROUP BY day
');
$stmt->execute(['start' => $start->format('Y-m-d'), 'end' => $endNext->format('Y-m-d')]);
$nuoviClienti = [];
foreach ($stmt->fetchAll() as $row) {
    $nuoviClienti[$row['day']] = (int)$row['cnt'];
}
$seriesNuoviClienti = buildSeries($days, $nuoviClienti);
$rangeHalf = (int)floor($rangeDays / 2);
$seriesNuoviClientiCurrent = array_slice($seriesNuoviClienti, -$rangeHalf);
$seriesNuoviClientiPrev = array_slice($seriesNuoviClienti, 0, $rangeHalf);
$sumNuoviClientiCurrent = array_sum($seriesNuoviClientiCurrent);
$sumNuoviClientiPrev = array_sum($seriesNuoviClientiPrev);
$nuoviClientiDelta = null;
if ($sumNuoviClientiPrev > 0) {
    $nuoviClientiDelta = (($sumNuoviClientiCurrent - $sumNuoviClientiPrev) / $sumNuoviClientiPrev) * 100;
} elseif ($sumNuoviClientiCurrent > 0) {
    $nuoviClientiDelta = 100;
} else {
    $nuoviClientiDelta = 0;
}

// Richieste addestramento per giorno
$stmt = $pdo->prepare('
    SELECT DATE(created_at) day, COUNT(*) cnt
    FROM richieste_addestramento
    WHERE created_at >= :start AND created_at < :end
    GROUP BY day
');
$stmt->execute(['start' => $start->format('Y-m-d'), 'end' => $endNext->format('Y-m-d')]);
$richiesteTraining = [];
foreach ($stmt->fetchAll() as $row) {
    $richiesteTraining[$row['day']] = (int)$row['cnt'];
}
$seriesRichiesteTraining = buildSeries($days, $richiesteTraining);
$seriesRichiesteCurrent = array_slice($seriesRichiesteTraining, -$rangeHalf);
$seriesRichiestePrev = array_slice($seriesRichiesteTraining, 0, $rangeHalf);
$sumRichiesteCurrent = array_sum($seriesRichiesteCurrent);
$sumRichiestePrev = array_sum($seriesRichiestePrev);
$richiesteDelta = null;
if ($sumRichiestePrev > 0) {
    $richiesteDelta = (($sumRichiesteCurrent - $sumRichiestePrev) / $sumRichiestePrev) * 100;
} elseif ($sumRichiesteCurrent > 0) {
    $richiesteDelta = 100;
} else {
    $richiesteDelta = 0;
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Gestione Servizi Clienti - Admin</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid var(--border);
    }
    .admin-nav {
      display: flex;
      gap: 12px;
    }
    .admin-nav a {
      padding: 8px 16px;
      background: rgba(139, 92, 246, 0.1);
      border: 1px solid rgba(139, 92, 246, 0.3);
      border-radius: 8px;
      color: #a78bfa;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.2s;
    }
    .admin-nav a:hover {
      background: rgba(139, 92, 246, 0.2);
      border-color: #8b5cf6;
    }
    .admin-nav a.active {
      background: #8b5cf6;
      color: white;
      border-color: #8b5cf6;
    }
    .cliente-card {
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .cliente-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 16px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border);
    }
    .btn.danger {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fca5a5;
    }
    .btn.danger:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: #ef4444;
    }
    .cliente-info h4 {
      margin: 0 0 4px 0;
      font-size: 18px;
    }
    .cliente-info .company {
      color: #06b6d4;
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 4px;
    }
    .servizi-attivi {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 16px;
    }
    .servizio-badge {
      padding: 6px 12px;
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.3);
      border-radius: 6px;
      color: #10b981;
      font-size: 12px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .servizio-badge .price {
      color: #6ee7b7;
      font-size: 11px;
    }
    .servizi-disponibili {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 12px;
    }
    .servizio-btn {
      padding: 6px 12px;
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.3);
      border-radius: 6px;
      color: #60a5fa;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .servizio-btn:hover {
      background: rgba(59, 130, 246, 0.2);
      border-color: #3b82f6;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 30px;
    }
    .stat-card {
      padding: 20px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
    }
    .stat-card .label {
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 8px;
    }
    .stat-card .value {
      font-size: 28px;
      font-weight: 700;
      margin: 0;
    }
    .stat-card.primary .value { color: #3b82f6; }
    .stat-card.success .value { color: #10b981; }
    .stat-card.warning .value { color: #f59e0b; }
    .charts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 16px;
      margin-bottom: 30px;
    }
    .chart-card {
      padding: 16px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
    }
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 10px;
      gap: 12px;
    }
    .chart-header h4 {
      margin: 0;
      font-size: 22px;
    }
    .sparkline {
      width: 100%;
      height: 120px;
      display: block;
    }
    .chart-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 6px;
    }
    .delta {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 600;
    }
    .delta.positive { color: #10b981; }
    .delta.negative { color: #ef4444; }
    .delta.neutral { color: var(--muted); }
    .chart-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }
    .chart-filters {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .chart-filters .pill {
      padding: 6px 12px;
      background: rgba(139, 92, 246, 0.1);
      border: 1px solid rgba(139, 92, 246, 0.3);
      border-radius: 999px;
      color: #a78bfa;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .chart-filters .pill:hover {
      background: rgba(139, 92, 246, 0.2);
      border-color: #8b5cf6;
    }
    .chart-filters .pill.active {
      background: #8b5cf6;
      border-color: #8b5cf6;
      color: #fff;
    }
    .service-table {
      width: 100%;
      border-collapse: collapse;
    }
    .service-table th,
    .service-table td {
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      text-align: left;
      font-size: 14px;
    }
    .service-table th {
      font-size: 12px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .service-table td.num {
      text-align: right;
      white-space: nowrap;
    }
    .profile-details {
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--border);
    }
    .profile-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 16px;
    }
    .profile-section {
      background: rgba(255,255,255,0.02);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px;
    }
    .profile-section h5 {
      margin: 0 0 8px 0;
      font-size: 14px;
    }
    .tag-list {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-bottom: 8px;
    }
    .tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 8px;
      border-radius: 999px;
      font-size: 12px;
      background: rgba(139, 92, 246, 0.15);
      color: #c4b5fd;
      border: 1px solid rgba(139, 92, 246, 0.3);
    }
    .tag button {
      background: none;
      border: none;
      color: inherit;
      cursor: pointer;
      padding: 0;
      font-size: 12px;
      line-height: 1;
    }
    .tag-form {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }
    .tag-form input,
    .tag-form select {
      padding: 6px 8px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 12px;
    }
    .pricing-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .pricing-item {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
      padding: 8px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(15, 23, 42, 0.6);
    }
    .pricing-item .title {
      font-size: 13px;
      font-weight: 600;
    }
    .pricing-actions {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .pricing-actions input {
      width: 120px;
      padding: 6px 8px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 12px;
    }
    .promo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 16px;
    }
    .promo-card {
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 12px;
      background: rgba(15, 23, 42, 0.6);
    }
    .promo-form {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }
    .promo-form input,
    .promo-form select {
      padding: 6px 8px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 12px;
    }
    .promo-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 10px;
    }
    .promo-item {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
      padding: 8px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, 0.02);
    }
    .promo-item .title {
      font-size: 13px;
      font-weight: 600;
    }
    .bundle-item {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: flex-start;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, 0.02);
    }
    .bundle-services {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 8px;
    }
    .service-admin-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 16px;
    }
    .service-admin-card {
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 14px;
      background: rgba(15, 23, 42, 0.6);
    }
    .service-admin-card h4 {
      margin: 0 0 8px 0;
    }
    .service-admin-form {
      display: grid;
      gap: 10px;
    }
    .service-admin-form input,
    .service-admin-form textarea,
    .service-admin-form select {
      padding: 8px 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 13px;
    }
    .service-admin-history {
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--border);
      font-size: 12px;
    }
    .service-admin-history .history-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
      padding: 8px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, 0.02);
      margin-bottom: 8px;
    }
    .usage-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .usage-row {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 8px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, 0.02);
    }
    .usage-bar {
      height: 6px;
      background: rgba(148, 163, 184, 0.2);
      border-radius: 999px;
      overflow: hidden;
    }
    .usage-fill {
      height: 100%;
      background: linear-gradient(90deg, #38bdf8, #8b5cf6);
    }
    .promo-actions {
      display: flex;
      gap: 8px;
      align-items: center;
    }
    .timeline-filters {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 8px;
    }
    .timeline-filters input,
    .timeline-filters select {
      padding: 6px 8px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 12px;
    }
    .note-actions {
      display: flex;
      gap: 6px;
      margin-top: 6px;
    }
    .timeline {
      display: flex;
      flex-direction: column;
      gap: 8px;
      max-height: 260px;
      overflow-y: auto;
    }
    .timeline-item {
      padding: 8px 10px;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: rgba(255,255,255,0.03);
    }
    .timeline-item .title {
      font-weight: 600;
      margin-bottom: 4px;
    }
    .timeline-item .meta {
      font-size: 12px;
      color: var(--muted);
    }
    .note-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 220px;
      overflow-y: auto;
    }
    .note-item {
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.03);
      font-size: 13px;
    }
    .doc-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 220px;
      overflow-y: auto;
    }
    .doc-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.03);
      font-size: 13px;
    }
    .upsell-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .upsell-item {
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.03);
      font-size: 13px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
    }
    .alert-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .alert-item {
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.03);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }
    .contract-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 220px;
      overflow-y: auto;
    }
    .contract-item {
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.03);
      font-size: 13px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
    }
    .forecast-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 12px;
    }
    .forecast-card {
      padding: 14px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      text-align: center;
    }
    .forecast-card .label {
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 6px;
    }
    .forecast-card .value {
      font-size: 20px;
      font-weight: 700;
    }
    .report-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
    }
    .report-card {
      padding: 16px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
    }
    .report-card h4 {
      margin: 0 0 6px 0;
      font-size: 16px;
    }
    .report-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px;
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
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: white;
      font-size: 14px;
    }
    .form-group textarea {
      min-height: 80px;
      resize: vertical;
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

  <div class="admin-header">
    <div>
      <h1 style="margin: 0 0 8px 0;">⚙️ Gestione Servizi Clienti</h1>
      <p class="muted">Pannello amministratore</p>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div class="admin-nav">
        <a href="/area-clienti/admin/gestione-servizi.php" class="active">Servizi Clienti</a>
        <a href="/area-clienti/admin/richieste-addestramento.php">Richieste Addestramento</a>
        <a href="/area-clienti/admin/fatture.php">Fatture</a>
        <a href="/area-clienti/admin/scadenzario.php">📅 Scadenzario</a>
        <a href="/area-clienti/admin/pipeline.php">Pipeline Vendite</a>
        <a href="/area-clienti/admin/preventivi.php">Preventivi</a>
        <a href="/area-clienti/admin/ticket.php">Ticket</a>
      </div>
      <span class="badge" style="background: #8b5cf6;">Admin</span>
    </div>
  </div>

  <!-- Statistiche -->
  <div class="stats-grid">
    <div class="stat-card primary">
      <p class="label">Clienti Attivi</p>
      <h2 class="value"><?= $totaleClienti ?></h2>
    </div>
    <div class="stat-card success">
      <p class="label">Servizi Attivi</p>
      <h2 class="value"><?= $totaleServiziAttivi ?></h2>
    </div>
    <div class="stat-card warning">
      <p class="label">Ricavi Mensili</p>
      <h2 class="value">€<?= number_format($ricaviMensili, 0, ',', '.') ?></h2>
    </div>
    <div class="stat-card info">
      <p class="label">Pacchetti Attivi</p>
      <h2 class="value"><?= $totalePacchettiAttivi ?></h2>
    </div>
    <div class="stat-card">
      <p class="label">Servizi Disponibili</p>
      <h2 class="value"><?= count($serviziDisponibili) ?></h2>
    </div>
  </div>

  <!-- Alert rinnovi -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Rinnovi in scadenza (30 giorni)</h3>
      <span class="muted small"><?= count($renewalAlerts) ?> alert</span>
    </div>
    <?php if (empty($renewalAlerts)): ?>
      <p class="muted" style="text-align: center; padding: 20px 0;">Nessuna scadenza imminente</p>
    <?php else: ?>
      <div class="alert-list">
        <?php foreach ($renewalAlerts as $alert): ?>
          <div class="alert-item">
            <div>
              <div style="font-weight: 600;"><?= htmlspecialchars($alert['azienda']) ?> · <?= htmlspecialchars($alert['titolo']) ?></div>
              <div class="muted small">Scadenza: <?= date('d/m/Y', strtotime($alert['data_scadenza'])) ?></div>
            </div>
            <div class="muted small">€<?= number_format((float)$alert['valore_annuo'], 0, ',', '.') ?>/anno</div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- KPI principali -->
  <div class="stats-grid">
    <div class="stat-card">
      <p class="label">Ricavi mensili ricorrenti</p>
      <h2 class="value">€<?= number_format($ricaviMensili, 0, ',', '.') ?></h2>
    </div>
    <div class="stat-card warning">
      <p class="label">Tasso di abbandono (30g)</p>
      <h2 class="value"><?= number_format($churnRatePct, 1, ',', '.') ?>%</h2>
    </div>
    <div class="stat-card success">
      <p class="label">Valore medio cliente</p>
      <h2 class="value"><?= $clvDisplay ?></h2>
    </div>
  </div>

  <!-- Previsioni ricavi -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Previsioni ricavi</h3>
      <span class="muted small">Basato su ricavi mensili ricorrenti e tasso di abbandono 30g</span>
    </div>
    <div class="forecast-grid">
      <?php foreach ($forecast as $item): ?>
        <div class="forecast-card">
          <div class="label"><?= htmlspecialchars($item['label']) ?></div>
          <div class="value">€<?= number_format($item['mrr'], 0, ',', '.') ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Report esportabili -->
  <section class="card" style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 12px;">Report esportabili</h3>
    <div class="report-grid">
      <div class="report-card">
        <h4>Clienti</h4>
        <p class="muted small">Anagrafica + servizi attivi + MRR per cliente.</p>
        <div class="report-actions">
          <a class="btn ghost small" href="/area-clienti/api/export-clienti.php">Export CSV</a>
        </div>
      </div>
      <div class="report-card">
        <h4>Servizi</h4>
        <p class="muted small">Servizi più richiesti, attivi/totali, MRR e margine.</p>
        <div class="report-actions">
          <a class="btn ghost small" href="/area-clienti/api/export-servizi.php">Export CSV</a>
        </div>
      </div>
      <div class="report-card">
        <h4>Fatturato</h4>
        <p class="muted small">Totali per mese da fatture emesse.</p>
        <div class="report-actions">
          <a class="btn ghost small" href="/area-clienti/api/export-fatturato.php">Export CSV</a>
        </div>
      </div>
      <div class="report-card">
        <h4>Utilizzo servizi per cliente</h4>
        <p class="muted small">Servizi attivi/totalizzati e MRR per cliente.</p>
        <div class="report-actions">
          <a class="btn ghost small" href="/area-clienti/api/export-uso-servizi.php">Export CSV</a>
        </div>
      </div>
      <div class="report-card">
        <h4>Report mensile contabilità</h4>
        <p class="muted small">Riepilogo fatture del mese precedente.</p>
        <div class="report-actions">
          <a class="btn ghost small" href="/area-clienti/api/report-mensile.php?month=<?= $reportMonth ?>">Scarica <?= $reportMonth ?></a>
        </div>
      </div>
    </div>
  </section>

  <!-- Coupon e promozioni -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Coupon e promozioni</h3>
      <span class="muted small">Sconti temporanei e codici coupon</span>
    </div>
    <div class="promo-grid">
      <div class="promo-card">
        <h4>Crea coupon</h4>
        <form class="promo-form js-coupon-form">
          <input type="text" name="codice" placeholder="CODICE (es. WELCOME10)" required>
          <select name="tipo">
            <option value="percentuale">Percentuale</option>
            <option value="fisso">Importo fisso</option>
          </select>
          <input type="number" step="0.01" min="0" name="valore" placeholder="Valore" required>
          <input type="date" name="data_inizio" placeholder="Inizio">
          <input type="date" name="data_fine" placeholder="Fine">
          <input type="number" min="1" name="max_usi" placeholder="Max usi (opzionale)">
          <button class="btn ghost small" type="submit">Crea coupon</button>
        </form>
      </div>
      <div class="promo-card">
        <h4>Coupon attivi</h4>
        <div class="promo-list">
          <?php if (empty($couponsList)): ?>
            <span class="muted small">Nessun coupon creato</span>
          <?php else: ?>
            <?php foreach ($couponsList as $coupon): ?>
              <div class="promo-item">
                <div>
                  <div class="title"><?= htmlspecialchars($coupon['codice']) ?></div>
                  <div class="muted small">
                    <?= $coupon['tipo'] === 'percentuale' ? number_format((float)$coupon['valore'], 0, ',', '.') . '%' : '€' . number_format((float)$coupon['valore'], 2, ',', '.') ?>
                    · <?= $coupon['attivo'] ? 'attivo' : 'disattivato' ?>
                    <?php if (!empty($coupon['data_inizio']) || !empty($coupon['data_fine'])): ?>
                      · <?= $coupon['data_inizio'] ? date('d/m/Y', strtotime($coupon['data_inizio'])) : 'sempre' ?> → <?= $coupon['data_fine'] ? date('d/m/Y', strtotime($coupon['data_fine'])) : 'sempre' ?>
                    <?php endif; ?>
                    <?php if (!empty($coupon['max_usi'])): ?>
                      · usi <?= (int)$coupon['usi'] ?>/<?= (int)$coupon['max_usi'] ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="promo-actions">
                  <button
                    class="btn ghost small js-coupon-toggle"
                    type="button"
                    data-coupon-id="<?= (int)$coupon['id'] ?>"
                    data-attivo="<?= (int)$coupon['attivo'] ?>">
                    <?= $coupon['attivo'] ? 'Disattiva' : 'Attiva' ?>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Gestione pacchetti -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Pacchetti</h3>
      <span class="muted small">Bundle di servizi con prezzo dedicato</span>
    </div>
    <div class="promo-grid">
      <div class="promo-card">
        <h4>Crea pacchetto</h4>
        <form class="promo-form js-bundle-create">
          <input type="text" name="nome" placeholder="Nome pacchetto" required>
          <input type="number" step="0.01" min="0" name="prezzo_mensile" placeholder="Prezzo mensile" required>
          <input type="text" name="descrizione" placeholder="Descrizione (opzionale)">
          <button class="btn ghost small" type="submit">Crea</button>
        </form>
      </div>
      <div class="promo-card">
        <h4>Pacchetti disponibili</h4>
        <div class="promo-list">
          <?php if (empty($pacchettiList)): ?>
            <span class="muted small">Nessun pacchetto creato</span>
          <?php else: ?>
            <?php foreach ($pacchettiList as $bundle): ?>
              <div class="bundle-item">
                <div>
                  <div class="title"><?= htmlspecialchars($bundle['nome']) ?></div>
                  <div class="muted small">€<?= number_format((float)$bundle['prezzo_mensile'], 2, ',', '.') ?>/mese · <?= $bundle['attivo'] ? 'attivo' : 'disattivato' ?></div>
                  <?php if (!empty($bundle['descrizione'])): ?>
                    <div class="muted small"><?= htmlspecialchars($bundle['descrizione']) ?></div>
                  <?php endif; ?>
                  <div class="bundle-services">
                    <?php if (empty($pacchettiServizi[(int)$bundle['id']])): ?>
                      <span class="muted small">Nessun servizio nel pacchetto</span>
                    <?php else: ?>
                      <?php foreach ($pacchettiServizi[(int)$bundle['id']] as $svc): ?>
                        <span class="tag">
                          <?= htmlspecialchars($svc['nome']) ?>
                          <button type="button" class="js-bundle-remove-service" data-bundle-id="<?= (int)$bundle['id'] ?>" data-servizio-id="<?= (int)$svc['servizio_id'] ?>">×</button>
                        </span>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="promo-actions" style="flex-direction: column; align-items: flex-end;">
                  <button
                    class="btn ghost small js-bundle-toggle"
                    type="button"
                    data-bundle-id="<?= (int)$bundle['id'] ?>"
                    data-attivo="<?= (int)$bundle['attivo'] ?>">
                    <?= $bundle['attivo'] ? 'Disattiva' : 'Attiva' ?>
                  </button>
                  <form class="promo-form js-bundle-add-service" data-bundle-id="<?= (int)$bundle['id'] ?>">
                    <select name="servizio_id" required>
                      <option value="">Aggiungi servizio</option>
                      <?php foreach ($serviziDisponibili as $svc): ?>
                        <option value="<?= (int)$svc['id'] ?>"><?= htmlspecialchars($svc['nome']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn ghost small" type="submit">Aggiungi</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Catalogo servizi -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Catalogo servizi</h3>
      <span class="muted small">Versioning e modifica rapida dei servizi</span>
    </div>
    <div class="service-admin-list">
      <?php foreach ($serviziDisponibili as $svc): ?>
        <?php
          $history = $versioniByServizio[$svc['id']] ?? [];
          $history = array_slice($history, 0, 5);
        ?>
        <div class="service-admin-card">
          <h4><?= htmlspecialchars($svc['nome']) ?></h4>
          <form class="service-admin-form js-service-update" data-servizio-id="<?= (int)$svc['id'] ?>">
            <input type="text" name="nome" value="<?= htmlspecialchars($svc['nome']) ?>" placeholder="Nome servizio" required>
            <input type="text" name="codice" value="<?= htmlspecialchars($svc['codice']) ?>" placeholder="Codice" required>
            <input type="number" step="0.01" min="0" name="prezzo_mensile" value="<?= number_format((float)$svc['prezzo_mensile'], 2, '.', '') ?>" placeholder="Prezzo mensile">
            <input type="number" step="0.0001" min="0" name="costo_per_pagina" value="<?= number_format((float)$svc['costo_per_pagina'], 4, '.', '') ?>" placeholder="Costo per pagina">
            <input type="number" min="0" name="quota_documenti_mese" value="<?= $serviziQuote[(int)$svc['id']] !== null ? (int)$serviziQuote[(int)$svc['id']] : '' ?>" placeholder="Quota documenti/mese">
            <select name="attivo">
              <option value="1" <?= $svc['attivo'] ? 'selected' : '' ?>>Attivo</option>
              <option value="0" <?= !$svc['attivo'] ? 'selected' : '' ?>>Disattivo</option>
            </select>
            <textarea name="descrizione" placeholder="Descrizione"><?= htmlspecialchars($svc['descrizione'] ?? '') ?></textarea>
            <button type="submit" class="btn ghost small">Salva modifiche</button>
          </form>

          <div class="service-admin-history">
            <div class="muted small" style="margin-bottom: 6px;">Ultime modifiche</div>
            <?php if (empty($history)): ?>
              <div class="muted small">Nessuna modifica registrata</div>
            <?php else: ?>
              <?php foreach ($history as $item): ?>
                <?php
                  $changedFields = json_decode($item['changed_fields'] ?? '', true) ?: [];
                  $fieldsSummary = [];
                  foreach ($changedFields as $field => $values) {
                    $fieldsSummary[] = $field;
                  }
                ?>
                <div class="history-item">
                  <div>
                    <strong><?= htmlspecialchars($item['action']) ?></strong>
                    · <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                  </div>
                  <div class="muted small">
                    <?= !empty($fieldsSummary) ? 'Campi: ' . htmlspecialchars(implode(', ', $fieldsSummary)) : 'Modifica dettagliata' ?>
                  </div>
                  <div class="muted small">
                    <?= htmlspecialchars(trim(($item['nome'] ?? '') . ' ' . ($item['cognome'] ?? ''))) ?>
                    <?php if (!empty($item['email'])): ?>
                      · <?= htmlspecialchars($item['email']) ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Servizi on-demand -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Servizi on-demand</h3>
      <span class="muted small">Acquisti una-tantum</span>
    </div>
    <div class="promo-grid">
      <div class="promo-card">
        <h4>Crea servizio on-demand</h4>
        <form class="promo-form js-ondemand-create">
          <input type="text" name="nome" placeholder="Nome servizio" required>
          <input type="number" step="0.01" min="0" name="prezzo_unitario" placeholder="Prezzo unitario" required>
          <input type="text" name="descrizione" placeholder="Descrizione (opzionale)">
          <button class="btn ghost small" type="submit">Crea</button>
        </form>
      </div>
      <div class="promo-card">
        <h4>Catalogo on-demand</h4>
        <div class="promo-list">
          <?php if (empty($onDemandList)): ?>
            <span class="muted small">Nessun servizio on-demand</span>
          <?php else: ?>
            <?php foreach ($onDemandList as $item): ?>
              <div class="bundle-item">
                <div>
                  <div class="title"><?= htmlspecialchars($item['nome']) ?></div>
                  <div class="muted small">€<?= number_format((float)$item['prezzo_unitario'], 2, ',', '.') ?> · <?= $item['attivo'] ? 'attivo' : 'disattivato' ?></div>
                  <?php if (!empty($item['descrizione'])): ?>
                    <div class="muted small"><?= htmlspecialchars($item['descrizione']) ?></div>
                  <?php endif; ?>
                </div>
                <div class="promo-actions">
                  <button
                    class="btn ghost small js-ondemand-toggle"
                    type="button"
                    data-ondemand-id="<?= (int)$item['id'] ?>"
                    data-attivo="<?= (int)$item['attivo'] ?>">
                    <?= $item['attivo'] ? 'Disattiva' : 'Attiva' ?>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Metriche per servizio -->
  <section class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
      <h3 style="margin: 0;">Metriche per servizio</h3>
      <span class="muted small">Margine stimato: <?= number_format($marginPct, 0, ',', '.') ?>%</span>
    </div>

    <?php if (empty($serviceStats)): ?>
      <p class="muted" style="text-align: center; padding: 30px 0;">Nessun servizio disponibile</p>
    <?php else: ?>
      <table class="service-table">
        <thead>
          <tr>
            <th>Servizio</th>
            <th class="num">Attivi</th>
            <th class="num">Totali</th>
            <th class="num">MRR</th>
            <th class="num">Margine stimato</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($serviceStats as $service): ?>
            <tr>
              <td><?= htmlspecialchars($service['nome']) ?></td>
              <td class="num"><?= (int)$service['attivi'] ?></td>
              <td class="num"><?= (int)$service['totali'] ?></td>
              <td class="num">€<?= number_format($service['mrr'], 0, ',', '.') ?></td>
              <td class="num">€<?= number_format($service['margin'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <!-- Grafici temporali -->
  <section class="card" style="margin-bottom: 30px;">
    <div class="chart-toolbar">
      <h3 style="margin: 0;">Grafici temporali</h3>
      <form method="get" class="chart-filters">
        <button class="pill <?= $rangeDays === 30 ? 'active' : '' ?>" type="submit" name="range" value="30">30g</button>
        <button class="pill <?= $rangeDays === 90 ? 'active' : '' ?>" type="submit" name="range" value="90">90g</button>
        <button class="pill <?= $rangeDays === 365 ? 'active' : '' ?>" type="submit" name="range" value="365">365g</button>
      </form>
    </div>
    <div class="charts-grid">
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <p class="muted small" style="margin: 0 0 4px 0;">Servizi attivi</p>
            <h4><?= $seriesServiziAttivi ? end($seriesServiziAttivi) : 0 ?></h4>
          </div>
          <div style="text-align: right;">
            <span class="muted small">Ultimi <?= $rangeDays ?> giorni</span>
            <?php
            $deltaClass = 'neutral';
            if ($serviziAttiviDelta > 0) $deltaClass = 'positive';
            if ($serviziAttiviDelta < 0) $deltaClass = 'negative';
            ?>
            <div class="delta <?= $deltaClass ?>">
              <?= $serviziAttiviDelta >= 0 ? '▲' : '▼' ?>
              <?= number_format(abs($serviziAttiviDelta), 1, ',', '.') ?>%
            </div>
          </div>
        </div>
        <?= renderSparkline($seriesServiziAttivi, '#3b82f6', 'Andamento servizi attivi', $labels) ?>
        <div class="chart-footer">
          <span class="muted small"><?= $labels[0] ?></span>
          <span class="muted small"><?= $labels[count($labels) - 1] ?></span>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-header">
          <div>
            <p class="muted small" style="margin: 0 0 4px 0;">Nuovi clienti</p>
            <h4><?= $seriesNuoviClienti ? end($seriesNuoviClienti) : 0 ?></h4>
          </div>
          <div style="text-align: right;">
            <span class="muted small">Ultimi <?= $rangeDays ?> giorni</span>
            <?php
            $deltaClass = 'neutral';
            if ($nuoviClientiDelta > 0) $deltaClass = 'positive';
            if ($nuoviClientiDelta < 0) $deltaClass = 'negative';
            ?>
            <div class="delta <?= $deltaClass ?>">
              <?= $nuoviClientiDelta >= 0 ? '▲' : '▼' ?>
              <?= number_format(abs($nuoviClientiDelta), 1, ',', '.') ?>%
            </div>
          </div>
        </div>
        <?= renderSparkline($seriesNuoviClienti, '#10b981', 'Andamento nuovi clienti', $labels) ?>
        <div class="chart-footer">
          <span class="muted small"><?= $labels[0] ?></span>
          <span class="muted small"><?= $labels[count($labels) - 1] ?></span>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-header">
          <div>
            <p class="muted small" style="margin: 0 0 4px 0;">Richieste addestramento</p>
            <h4><?= $seriesRichiesteTraining ? end($seriesRichiesteTraining) : 0 ?></h4>
          </div>
          <div style="text-align: right;">
            <span class="muted small">Ultimi <?= $rangeDays ?> giorni</span>
            <?php
            $deltaClass = 'neutral';
            if ($richiesteDelta > 0) $deltaClass = 'positive';
            if ($richiesteDelta < 0) $deltaClass = 'negative';
            ?>
            <div class="delta <?= $deltaClass ?>">
              <?= $richiesteDelta >= 0 ? '▲' : '▼' ?>
              <?= number_format(abs($richiesteDelta), 1, ',', '.') ?>%
            </div>
          </div>
        </div>
        <?= renderSparkline($seriesRichiesteTraining, '#f59e0b', 'Andamento richieste addestramento', $labels) ?>
        <div class="chart-footer">
          <span class="muted small"><?= $labels[0] ?></span>
          <span class="muted small"><?= $labels[count($labels) - 1] ?></span>
        </div>
      </div>
    </div>
  </section>

  <!-- Nuovo Cliente -->
  <section class="card">
    <div class="card-header">
      <h3>➕ Nuovo Cliente</h3>
    </div>
    <form class="form-grid js-create-client-form">
      <label>
        Nome
        <input type="text" name="nome" required>
      </label>
      <label>
        Cognome
        <input type="text" name="cognome" required>
      </label>
      <label>
        Email
        <input type="email" name="email" required>
      </label>
      <label>
        Azienda
        <input type="text" name="azienda" required>
      </label>
      <label>
        Password
        <input type="password" name="password" minlength="8" required>
      </label>
      <label>
        WebApp URL (opzionale)
        <input type="url" name="webapp_url" placeholder="https://cliente.example.com/document-intelligence">
      </label>
      <label style="display: flex; align-items: center; gap: 8px;">
        <input type="checkbox" name="attiva_docint" value="1">
        Attiva DOC-INT
      </label>
      <button class="btn primary" type="submit">Crea Cliente</button>
    </form>
  </section>

  <!-- Lista Clienti -->
  <section class="card">
    <h3>📋 Clienti e Servizi Attivi</h3>

    <?php if (empty($clienti)): ?>
      <p class="muted" style="text-align: center; padding: 40px 0;">Nessun cliente presente</p>
    <?php else: ?>
      <?php foreach ($clienti as $cliente): ?>
        <div class="cliente-card">
          <div class="cliente-header">
            <div class="cliente-info">
              <p class="company"><?= htmlspecialchars($cliente['azienda']) ?></p>
              <h4><?= htmlspecialchars($cliente['nome'] . ' ' . $cliente['cognome']) ?></h4>
              <p class="muted small" style="margin: 0;">
                <?= htmlspecialchars($cliente['email']) ?>
              </p>
              <button
                class="btn danger small js-delete-cliente"
                type="button"
                data-cliente-id="<?= (int)$cliente['id'] ?>"
                style="margin-top: 8px;">
                Elimina cliente
              </button>
            </div>
          </div>

          <!-- Form Cliente Dal -->
          <form class="form-grid js-cliente-dal-form" data-cliente-id="<?= (int)$cliente['id'] ?>" style="margin-top: 12px;">
            <label>
              Cliente dal
              <input
                type="date"
                name="cliente_dal"
                value="<?= htmlspecialchars($cliente['cliente_dal'] ?? date('Y-m-d', strtotime($cliente['created_at']))) ?>"
                required
              >
            </label>
            <button class="btn ghost small" type="submit">Salva Data</button>
          </form>

          <?php if ($hasWebappUrl): ?>
            <form class="form-grid js-webapp-form" data-cliente-id="<?= (int)$cliente['id'] ?>" style="margin-top: 12px;">
              <label>
                WebApp URL
                <input
                  type="url"
                  name="webapp_url"
                  value="<?= htmlspecialchars($cliente['webapp_url'] ?? '') ?>"
                  placeholder="https://cliente.example.com/document-intelligence"
                >
              </label>
              <button class="btn ghost small" type="submit">Salva URL</button>
            </form>
          <?php else: ?>
            <p class="muted small" style="margin-top: 12px;">
              WebApp URL non configurabile: manca la colonna <code>utenti.webapp_url</code>.
            </p>
          <?php endif; ?>

          <!-- Servizi Attivi -->
          <?php if (!empty($serviziClienti[$cliente['id']])): ?>
            <div>
              <p class="muted small" style="margin: 0 0 8px 0;">Servizi Attivi:</p>
              <div class="servizi-attivi">
                <?php foreach ($serviziClienti[$cliente['id']] as $servizio): ?>
                  <div class="servizio-badge">
                    <span>✓ <?= htmlspecialchars($servizio['servizio_nome']) ?></span>
                    <span class="price">€<?= number_format($servizio['prezzo_finale'], 0, ',', '.') ?>/mese</span>
                    <?php if ($servizio['prezzo_personalizzato'] !== null): ?>
                      <span class="badge" style="margin-left: 6px;">Personalizzato</span>
                    <?php endif; ?>
                    <?php if (!empty($pacchettiServiziByClient[$cliente['id']][(int)$servizio['servizio_id']])): ?>
                      <span class="badge" style="margin-left: 6px;">Nel pacchetto</span>
                    <?php endif; ?>
                    <button
                      onclick="disattivaServizio(<?= $servizio['id'] ?>, '<?= htmlspecialchars($cliente['nome'] . ' ' . $cliente['cognome']) ?>', '<?= htmlspecialchars($servizio['servizio_nome']) ?>')"
                      style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0; margin-left: 4px;"
                      title="Disattiva servizio">
                      ×
                    </button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <p class="muted small">Nessun servizio attivo</p>
          <?php endif; ?>

          <!-- KPI Document Intelligence -->
          <?php
            $hasDocInt = false;
            if (!empty($serviziClienti[$cliente['id']])) {
              foreach ($serviziClienti[$cliente['id']] as $servizio) {
                if ($servizio['servizio_codice'] === 'DOC-INT') {
                  $hasDocInt = true;
                  break;
                }
              }
            }
          ?>
          <?php if ($hasDocInt): ?>
          <div style="margin-top: 16px; padding: 16px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 12px; border: 1px solid rgba(102, 126, 234, 0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <h5 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                📊 <span>KPI Document Intelligence</span>
              </h5>
              <button class="btn ghost small" onclick="refreshClienteKPI(<?= $cliente['id'] ?>)" style="padding: 4px 8px;">🔄</button>
            </div>

            <div id="kpi-container-<?= $cliente['id'] ?>" style="display: none;">
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px;">
                <div style="padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                  <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">Doc/Mese</div>
                  <div style="font-size: 20px; font-weight: 700; color: var(--accent1);" id="kpi-doc-<?= $cliente['id'] ?>">-</div>
                </div>
                <div style="padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                  <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">Pagine/Mese</div>
                  <div style="font-size: 20px; font-weight: 700; color: var(--accent2);" id="kpi-pag-<?= $cliente['id'] ?>">-</div>
                </div>
                <div style="padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                  <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">Accuratezza</div>
                  <div style="font-size: 20px; font-weight: 700; color: #10b981;" id="kpi-acc-<?= $cliente['id'] ?>">-</div>
                </div>
                <div style="padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                  <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">API Status</div>
                  <div style="font-size: 13px; font-weight: 600;" id="kpi-status-<?= $cliente['id'] ?>">
                    <span style="color: var(--muted);">⏳ Loading...</span>
                  </div>
                </div>
              </div>
            </div>

            <div id="kpi-loading-<?= $cliente['id'] ?>" style="text-align: center; padding: 20px; color: var(--muted);">
              <span style="font-size: 12px;">⏳ Caricamento KPI...</span>
            </div>

            <div id="kpi-error-<?= $cliente['id'] ?>" style="display: none; padding: 12px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; color: #ef4444; font-size: 12px;">
              ⚠️ <span id="kpi-error-msg-<?= $cliente['id'] ?>">Errore caricamento</span>
            </div>
          </div>
          <script>
            (function() {
              const clienteId = <?= $cliente['id'] ?>;
              loadKPIForCliente(clienteId);
            })();
          </script>
          <?php endif; ?>

          <!-- Pulsante Attiva Servizio -->
          <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
            <button
              class="btn primary small"
              onclick="mostraModalAttivazione(<?= $cliente['id'] ?>, '<?= htmlspecialchars($cliente['nome'] . ' ' . $cliente['cognome']) ?>', '<?= htmlspecialchars($cliente['azienda']) ?>')">
              ➕ Attiva Nuovo Servizio
            </button>
          </div>

          <?php
            $clienteNotes = $notesByClient[$cliente['id']] ?? [];
            $clienteTags = $tagsByClient[$cliente['id']] ?? [];
            $clienteDocs = $docsByClient[$cliente['id']] ?? [];
            $clienteTimeline = $timelineByClient[$cliente['id']] ?? [];
            $clientePrezzi = $prezziPersonalizzati[$cliente['id']] ?? [];
            $clienteCostiPagina = $costiPaginaPersonalizzati[$cliente['id']] ?? [];
            $clienteSconti = $scontiByClient[$cliente['id']] ?? [];
            $clienteCoupons = $couponByClient[$cliente['id']] ?? [];
            $clientePacchetti = $pacchettiByClient[$cliente['id']] ?? [];
            $clienteQuote = $clientiQuote[$cliente['id']] ?? [];
            $clienteUsage = $usageByClient[$cliente['id']] ?? [];
            $clienteAcquisti = $acquistiByClient[$cliente['id']] ?? [];
            $attiviIds = [];
            if (!empty($serviziClienti[$cliente['id']])) {
              foreach ($serviziClienti[$cliente['id']] as $s) {
                $attiviIds[(int)$s['servizio_id']] = true;
              }
            }
            $suggested = [];
            foreach ($serviziDisponibili as $svc) {
              if (!isset($attiviIds[(int)$svc['id']])) {
                $svc['pop'] = $servicePopularity[(int)$svc['id']] ?? 0;
                $suggested[] = $svc;
              }
            }
            usort($suggested, function ($a, $b) {
              return ($b['pop'] <=> $a['pop']) ?: strcmp($a['nome'], $b['nome']);
            });
            $suggested = array_slice($suggested, 0, 3);
          ?>

          <details class="profile-details" data-cliente-id="<?= $cliente['id'] ?>">
            <summary class="btn ghost small">Profilo cliente avanzato</summary>
            <div class="profile-grid" style="margin-top: 12px;">
              <div class="profile-section">
                <h5>Tag / Categorie</h5>
                <div class="tag-list">
                  <?php if (empty($clienteTags)): ?>
                    <span class="muted small">Nessun tag</span>
                  <?php else: ?>
                    <?php foreach ($clienteTags as $tag): ?>
                      <span class="tag" style="<?= $tag['colore'] ? 'border-color:' . htmlspecialchars($tag['colore']) . ';color:' . htmlspecialchars($tag['colore']) . ';' : '' ?>">
                        <?= htmlspecialchars($tag['nome']) ?>
                        <button type="button" onclick="rimuoviTag(<?= $cliente['id'] ?>, <?= (int)$tag['id'] ?>)">×</button>
                      </span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="js-tag-form tag-form" data-cliente-id="<?= $cliente['id'] ?>">
                  <select name="tag_id">
                    <option value="">Seleziona tag esistente</option>
                    <?php foreach ($allTags as $tagItem): ?>
                      <option value="<?= (int)$tagItem['id'] ?>"><?= htmlspecialchars($tagItem['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="text" name="tag" placeholder="Oppure nuovo tag (es. VIP)">
                  <input type="color" name="color" value="#a78bfa" title="Colore tag">
                  <button class="btn ghost small" type="submit" name="action" value="add">Aggiungi</button>
                  <button class="btn ghost small" type="submit" name="action" value="color">Salva colore</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Note interne</h5>
                <div class="note-list">
                  <?php if (empty($clienteNotes)): ?>
                    <span class="muted small">Nessuna nota</span>
                  <?php else: ?>
                    <?php foreach ($clienteNotes as $note): ?>
                      <div class="note-item" data-note-id="<?= (int)$note['id'] ?>">
                        <div><?= nl2br(htmlspecialchars($note['note'])) ?></div>
                        <div class="meta"><?= date('d/m/Y H:i', strtotime($note['created_at'])) ?></div>
                        <div class="note-actions">
                          <button class="btn ghost small js-note-edit" type="button">Modifica</button>
                          <button class="btn ghost small js-note-delete" type="button">Elimina</button>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="js-note-form" data-cliente-id="<?= $cliente['id'] ?>">
                  <textarea name="note" placeholder="Aggiungi nota..." required></textarea>
                  <button class="btn ghost small" type="submit">Salva nota</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Documenti</h5>
                <div class="doc-list">
                  <?php if (empty($clienteDocs)): ?>
                    <span class="muted small">Nessun documento</span>
                  <?php else: ?>
                    <?php foreach ($clienteDocs as $doc): ?>
                      <div class="doc-item">
                        <span><?= htmlspecialchars($doc['nome']) ?></span>
                        <a class="btn ghost small" href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">Apri</a>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="js-doc-form" data-cliente-id="<?= $cliente['id'] ?>" enctype="multipart/form-data">
                  <input type="file" name="documento" required>
                  <button class="btn ghost small" type="submit">Carica</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Prezzi personalizzati</h5>
                <div class="pricing-list">
                  <?php foreach ($serviziDisponibili as $svc): ?>
                    <?php
                      $customPrice = $clientePrezzi[(int)$svc['id']] ?? null;
                      $customCostoPagina = $clienteCostiPagina[(int)$svc['id']] ?? null;
                      $isActive = isset($attiviIds[(int)$svc['id']]);
                      $baseCostoPagina = isset($svc['costo_per_pagina']) ? (float)$svc['costo_per_pagina'] : 0.0;
                    ?>
                    <div class="pricing-item">
                      <div>
                        <div class="title">
                          <?= htmlspecialchars($svc['nome']) ?>
                          <?php if ($isActive): ?>
                            <span class="badge" style="margin-left: 6px;">attivo</span>
                          <?php endif; ?>
                        </div>
                        <div class="muted small">Base: €<?= number_format($svc['prezzo_mensile'], 2, ',', '.') ?>/mese</div>
                        <?php if ($baseCostoPagina > 0): ?>
                          <div class="muted small">Costo pagina: €<?= number_format($baseCostoPagina, 4, ',', '.') ?></div>
                        <?php endif; ?>
                      </div>
                      <div class="pricing-actions">
                        <input
                          type="number"
                          step="0.01"
                          min="0"
                          placeholder="Prezzo custom"
                          data-field="prezzo_mensile"
                          value="<?= $customPrice !== null ? number_format($customPrice, 2, '.', '') : '' ?>"
                        >
                        <input
                          type="number"
                          step="0.0001"
                          min="0"
                          placeholder="Costo per pagina"
                          data-field="costo_per_pagina"
                          value="<?= $customCostoPagina !== null ? number_format($customCostoPagina, 4, '.', '') : '' ?>"
                        >
                        <button
                          class="btn ghost small js-price-save"
                          type="button"
                          data-cliente-id="<?= $cliente['id'] ?>"
                          data-servizio-id="<?= $svc['id'] ?>">
                          Salva
                        </button>
                        <?php if ($customPrice !== null): ?>
                          <button
                            class="btn ghost small js-price-remove"
                            type="button"
                            data-cliente-id="<?= $cliente['id'] ?>"
                            data-servizio-id="<?= $svc['id'] ?>">
                            Rimuovi
                          </button>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="profile-section">
                <h5>Quote documenti/mese</h5>
                <div class="pricing-list">
                  <?php foreach ($serviziDisponibili as $svc): ?>
                    <?php
                      $overrideQuota = $clienteQuote[(int)$svc['id']] ?? null;
                      $baseQuota = $serviziQuote[(int)$svc['id']] ?? null;
                    ?>
                    <div class="pricing-item">
                      <div>
                        <div class="title"><?= htmlspecialchars($svc['nome']) ?></div>
                        <div class="muted small">
                          Base: <?= $baseQuota === null ? 'illimitata' : $baseQuota . ' doc/mese' ?>
                        </div>
                      </div>
                      <div class="pricing-actions">
                        <input
                          type="number"
                          min="0"
                          placeholder="Override quota"
                          value="<?= $overrideQuota !== null ? (int)$overrideQuota : '' ?>"
                        >
                        <button
                          class="btn ghost small js-quota-save"
                          type="button"
                          data-cliente-id="<?= $cliente['id'] ?>"
                          data-servizio-id="<?= $svc['id'] ?>">
                          Salva
                        </button>
                        <?php if ($overrideQuota !== null): ?>
                          <button
                            class="btn ghost small js-quota-remove"
                            type="button"
                            data-cliente-id="<?= $cliente['id'] ?>"
                            data-servizio-id="<?= $svc['id'] ?>">
                            Rimuovi
                          </button>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="profile-section">
                <h5>Utilizzo risorse (<?= htmlspecialchars($currentPeriod) ?>)</h5>
                <div class="usage-list">
                  <?php if (empty($serviziClienti[$cliente['id']])): ?>
                    <span class="muted small">Nessun servizio attivo</span>
                  <?php else: ?>
                    <?php foreach ($serviziClienti[$cliente['id']] as $svc): ?>
                      <?php
                        $svcId = (int)$svc['servizio_id'];
                        $used = $clienteUsage[$svcId] ?? 0;
                        $quotaEff = array_key_exists($svcId, $clienteQuote)
                          ? $clienteQuote[$svcId]
                          : ($serviziQuote[$svcId] ?? null);
                        $percent = ($quotaEff && $quotaEff > 0) ? min(100, (int)round(($used / $quotaEff) * 100)) : 0;
                      ?>
                      <div class="usage-row">
                        <div style="display: flex; justify-content: space-between; gap: 8px;">
                          <strong><?= htmlspecialchars($svc['servizio_nome']) ?></strong>
                          <span class="muted small">
                            <?= $quotaEff === null ? 'illimitata' : ($used . ' / ' . $quotaEff . ' doc') ?>
                          </span>
                        </div>
                        <div class="usage-bar">
                          <div class="usage-fill" style="width: <?= $quotaEff === null ? 0 : $percent ?>%;"></div>
                        </div>
                        <div class="muted small">
                          <?= $quotaEff === null ? ($used . ' documenti usati') : ($percent . '% utilizzato') ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

              <div class="profile-section">
                <h5>Pacchetti attivi</h5>
                <div class="promo-list">
                  <?php if (empty($clientePacchetti)): ?>
                    <span class="muted small">Nessun pacchetto attivo</span>
                  <?php else: ?>
                    <?php foreach ($clientePacchetti as $bundle): ?>
                      <div class="promo-item">
                        <div>
                          <div class="title"><?= htmlspecialchars($bundle['nome']) ?></div>
                          <div class="muted small">€<?= number_format((float)$bundle['prezzo_mensile'], 2, ',', '.') ?>/mese</div>
                          <?php if (!empty($bundle['data_inizio']) || !empty($bundle['data_fine'])): ?>
                            <div class="muted small">
                              <?= $bundle['data_inizio'] ? date('d/m/Y', strtotime($bundle['data_inizio'])) : 'sempre' ?>
                              → <?= $bundle['data_fine'] ? date('d/m/Y', strtotime($bundle['data_fine'])) : 'sempre' ?>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="promo-actions">
                          <button
                            class="btn ghost small js-bundle-remove"
                            type="button"
                            data-assignment-id="<?= (int)$bundle['id'] ?>">
                            Rimuovi
                          </button>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="promo-form js-bundle-assign" data-cliente-id="<?= $cliente['id'] ?>">
                  <select name="pacchetto_id" required>
                    <option value="">Seleziona pacchetto</option>
                    <?php foreach ($pacchettiList as $bundleItem): ?>
                      <?php if ($bundleItem['attivo']): ?>
                        <option value="<?= (int)$bundleItem['id'] ?>">
                          <?= htmlspecialchars($bundleItem['nome']) ?> (€<?= number_format((float)$bundleItem['prezzo_mensile'], 2, ',', '.') ?>/mese)
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                  <input type="date" name="data_inizio">
                  <input type="date" name="data_fine">
                  <button class="btn ghost small" type="submit">Assegna pacchetto</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Acquisti on-demand</h5>
                <div class="promo-list">
                  <?php if (empty($clienteAcquisti)): ?>
                    <span class="muted small">Nessun acquisto registrato</span>
                  <?php else: ?>
                    <?php foreach ($clienteAcquisti as $acq): ?>
                      <div class="promo-item">
                        <div>
                          <div class="title"><?= htmlspecialchars($acq['nome']) ?></div>
                          <div class="muted small">
                            <?= number_format((float)$acq['quantita'], 2, ',', '.') ?> × €<?= number_format((float)$acq['prezzo_unitario'], 2, ',', '.') ?>
                            · €<?= number_format((float)$acq['totale'], 2, ',', '.') ?>
                          </div>
                          <div class="muted small"><?= date('d/m/Y', strtotime($acq['data_acquisto'])) ?> · <?= htmlspecialchars($acq['stato']) ?></div>
                        </div>
                        <div class="promo-actions">
                          <?php if ($acq['stato'] === 'da_fatturare'): ?>
                            <button
                              class="btn ghost small js-ondemand-remove"
                              type="button"
                              data-acquisto-id="<?= (int)$acq['id'] ?>">
                              Annulla
                            </button>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="promo-form js-ondemand-assign" data-cliente-id="<?= $cliente['id'] ?>">
                  <select name="servizio_id" required>
                    <option value="">Seleziona servizio</option>
                    <?php foreach ($onDemandList as $item): ?>
                      <?php if ($item['attivo']): ?>
                        <option value="<?= (int)$item['id'] ?>">
                          <?= htmlspecialchars($item['nome']) ?> (€<?= number_format((float)$item['prezzo_unitario'], 2, ',', '.') ?>)
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                  <input type="number" step="0.01" min="1" name="quantita" value="1">
                  <input type="date" name="data_acquisto" value="<?= date('Y-m-d') ?>">
                  <input type="number" step="0.01" min="0" name="prezzo_unitario" placeholder="Prezzo (override)">
                  <button class="btn ghost small" type="submit">Aggiungi</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Sconti temporanei</h5>
                <div class="promo-list">
                  <?php if (empty($clienteSconti)): ?>
                    <span class="muted small">Nessuno sconto attivo</span>
                  <?php else: ?>
                    <?php foreach ($clienteSconti as $sconto): ?>
                      <div class="promo-item">
                        <div>
                          <div class="title">
                            <?= htmlspecialchars($sconto['servizio_id'] ? ($serviziById[(int)$sconto['servizio_id']]['nome'] ?? 'Servizio') : 'Tutti i servizi') ?>
                          </div>
                          <div class="muted small">
                            <?= $sconto['tipo'] === 'percentuale' ? number_format((float)$sconto['valore'], 0, ',', '.') . '%' : '€' . number_format((float)$sconto['valore'], 2, ',', '.') ?>
                            <?php if (!empty($sconto['data_inizio']) || !empty($sconto['data_fine'])): ?>
                              · <?= $sconto['data_inizio'] ? date('d/m/Y', strtotime($sconto['data_inizio'])) : 'sempre' ?>
                              → <?= $sconto['data_fine'] ? date('d/m/Y', strtotime($sconto['data_fine'])) : 'sempre' ?>
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="promo-actions">
                          <button
                            class="btn ghost small js-discount-remove"
                            type="button"
                            data-discount-id="<?= (int)$sconto['id'] ?>">
                            Rimuovi
                          </button>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="promo-form js-discount-form" data-cliente-id="<?= $cliente['id'] ?>">
                  <select name="servizio_id">
                    <option value="">Tutti i servizi</option>
                    <?php foreach ($serviziDisponibili as $svc): ?>
                      <option value="<?= (int)$svc['id'] ?>"><?= htmlspecialchars($svc['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="tipo">
                    <option value="percentuale">Percentuale</option>
                    <option value="fisso">Importo fisso</option>
                  </select>
                  <input type="number" step="0.01" min="0" name="valore" placeholder="Valore" required>
                  <input type="date" name="data_inizio">
                  <input type="date" name="data_fine">
                  <input type="text" name="note" placeholder="Nota (opzionale)">
                  <button class="btn ghost small" type="submit">Aggiungi sconto</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Coupon assegnati</h5>
                <div class="promo-list">
                  <?php if (empty($clienteCoupons)): ?>
                    <span class="muted small">Nessun coupon assegnato</span>
                  <?php else: ?>
                    <?php foreach ($clienteCoupons as $coupon): ?>
                      <div class="promo-item">
                        <div>
                          <div class="title"><?= htmlspecialchars($coupon['codice']) ?></div>
                          <div class="muted small">
                            <?= $coupon['tipo'] === 'percentuale' ? number_format((float)$coupon['valore'], 0, ',', '.') . '%' : '€' . number_format((float)$coupon['valore'], 2, ',', '.') ?>
                            · <?= $coupon['attivo'] ? 'attivo' : 'disattivato' ?>
                            <?php if (!empty($coupon['data_inizio']) || !empty($coupon['data_fine'])): ?>
                              · <?= $coupon['data_inizio'] ? date('d/m/Y', strtotime($coupon['data_inizio'])) : 'sempre' ?>
                              → <?= $coupon['data_fine'] ? date('d/m/Y', strtotime($coupon['data_fine'])) : 'sempre' ?>
                            <?php endif; ?>
                            <?php if ($coupon['usato']): ?>
                              · usato
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="promo-actions">
                          <button
                            class="btn ghost small js-coupon-remove"
                            type="button"
                            data-assignment-id="<?= (int)$coupon['id'] ?>">
                            Rimuovi
                          </button>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="promo-form js-coupon-assign" data-cliente-id="<?= $cliente['id'] ?>">
                  <select name="coupon_id" required>
                    <option value="">Seleziona coupon</option>
                    <?php foreach ($couponsList as $couponItem): ?>
                      <?php if ($couponItem['attivo']): ?>
                        <option value="<?= (int)$couponItem['id'] ?>">
                          <?= htmlspecialchars($couponItem['codice']) ?> (<?= $couponItem['tipo'] === 'percentuale' ? number_format((float)$couponItem['valore'], 0, ',', '.') . '%' : '€' . number_format((float)$couponItem['valore'], 2, ',', '.') ?>)
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn ghost small" type="submit">Assegna coupon</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Timeline interazioni</h5>
                <div class="timeline-filters">
                  <input type="text" class="js-timeline-search" data-cliente-id="<?= $cliente['id'] ?>" placeholder="Cerca...">
                  <select class="js-timeline-type" data-cliente-id="<?= $cliente['id'] ?>">
                    <option value="">Tutti i tipi</option>
                    <option value="servizio">Servizi</option>
                    <option value="richiesta">Richieste</option>
                    <option value="email">Email</option>
                    <option value="manuale">Manuale</option>
                    <option value="sistema">Sistema</option>
                  </select>
                </div>
                <div class="timeline">
                  <?php if (empty($clienteTimeline)): ?>
                    <span class="muted small">Nessuna interazione</span>
                  <?php else: ?>
                    <?php foreach ($clienteTimeline as $item): ?>
                      <div class="timeline-item" data-type="<?= htmlspecialchars($item['type']) ?>" data-text="<?= htmlspecialchars($item['title'] . ' ' . ($item['details'] ?? '')) ?>">
                        <div class="title"><?= htmlspecialchars($item['title']) ?></div>
                        <?php if (!empty($item['details'])): ?>
                          <div class="muted small" style="margin-bottom: 4px;"><?= nl2br(htmlspecialchars($item['details'])) ?></div>
                        <?php endif; ?>
                        <div class="meta"><?= date('d/m/Y H:i', strtotime($item['date'])) ?> · <?= htmlspecialchars($item['type']) ?></div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="js-event-form" data-cliente-id="<?= $cliente['id'] ?>">
                  <input type="text" name="titolo" placeholder="Titolo evento" required>
                  <textarea name="dettagli" placeholder="Dettagli evento (opzionale)"></textarea>
                  <button class="btn ghost small" type="submit">Aggiungi evento</button>
                </form>
              </div>

              <div class="profile-section">
                <h5>Upselling suggerito</h5>
                <div class="upsell-list">
                  <?php if (empty($suggested)): ?>
                    <span class="muted small">Nessun suggerimento disponibile</span>
                  <?php else: ?>
                    <?php foreach ($suggested as $svc): ?>
                      <div class="upsell-item">
                        <span><?= htmlspecialchars($svc['nome']) ?></span>
                        <span class="muted small">€<?= number_format((float)$svc['prezzo_mensile'], 0, ',', '.') ?>/mese</span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <p class="muted small" style="margin-top: 8px;">Suggerimenti basati su servizi più attivi e profilo cliente.</p>
              </div>

              <div class="profile-section">
                <h5>Contratti e rinnovi</h5>
                <div class="contract-list">
                  <?php $contratti = $contractsByClient[$cliente['id']] ?? []; ?>
                  <?php if (empty($contratti)): ?>
                    <span class="muted small">Nessun contratto</span>
                  <?php else: ?>
                    <?php foreach ($contratti as $c): ?>
                      <?php
                        $serviceLabel = '';
                        if (!empty($c['servizio_nome'])) {
                            $serviceLabel = ' - ' . $c['servizio_nome'];
                        }
                        $rangeLabel = date('d/m/Y', strtotime($c['data_scadenza']));
                        $durataLabel = '';
                        if (!empty($c['data_inizio'])) {
                            $start = new DateTime($c['data_inizio']);
                            $end = new DateTime($c['data_scadenza']);
                            $rangeLabel = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
                            $durataLabel = ' (' . $start->diff($end)->days . ' gg)';
                        }
                      ?>
                      <div class="contract-item">
                        <span><?= htmlspecialchars($c['titolo']) ?><?= htmlspecialchars($serviceLabel) ?> - <?= $rangeLabel ?><?= $durataLabel ?></span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                          <select onchange="aggiornaStatoContratto(<?= (int)$c['id'] ?>, this.value)">
                            <?php foreach (['attivo' => 'Attivo', 'in_rinnovo' => 'In rinnovo', 'rinnovato' => 'Rinnovato', 'scaduto' => 'Scaduto'] as $st => $lbl): ?>
                              <option value="<?= $st ?>" <?= $c['stato'] === $st ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button class="btn danger small" type="button" onclick="eliminaContratto(<?= (int)$c['id'] ?>)">Elimina</button>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <form class="js-contract-form" data-cliente-id="<?= $cliente['id'] ?>" data-has-servizio="<?= $hasContrattoServizio ? '1' : '0' ?>">
                  <?php $serviziCliente = $serviziClienti[$cliente['id']] ?? []; ?>
                  <?php if ($hasContrattoServizio): ?>
                    <select name="servizio_id" required <?= empty($serviziCliente) ? 'disabled' : '' ?>>
                      <option value="">Seleziona servizio...</option>
                      <?php foreach ($serviziCliente as $svc): ?>
                        <option value="<?= (int)$svc['servizio_id'] ?>"><?= htmlspecialchars($svc['servizio_nome']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php endif; ?>
                  <input type="text" name="titolo" placeholder="Titolo contratto" required>
                  <input type="date" name="data_inizio">
                  <input type="date" name="data_scadenza" required>
                  <input type="number" name="valore_annuo" placeholder="Valore annuo" min="0" step="0.01">
                  <textarea name="note" placeholder="Note (opzionale)"></textarea>
                  <button class="btn ghost small" type="submit">Aggiungi contratto</button>
                </form>
              </div>
            </div>
          </details>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

</main>

<!-- Modal Attivazione Servizio -->
<div id="modalAttivazione" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Attiva Servizio</h3>
      <button class="close-modal" onclick="chiudiModal()">×</button>
    </div>

    <form id="formAttivazione">
      <input type="hidden" id="userId" name="user_id">

      <div class="form-group">
        <label>Cliente</label>
        <input type="text" id="clienteNome" readonly style="background: #0f172a; border: 1px solid var(--border); padding: 10px; border-radius: 8px; width: 100%; color: var(--muted);">
      </div>

      <div class="form-group">
        <label>Servizio da attivare</label>
        <select id="servizioId" name="servizio_id" required>
          <option value="">Seleziona un servizio...</option>
          <?php foreach ($serviziDisponibili as $s): ?>
            <option value="<?= $s['id'] ?>" data-prezzo="<?= $s['prezzo_mensile'] ?>">
              <?= htmlspecialchars($s['nome']) ?> - €<?= number_format($s['prezzo_mensile'], 0, ',', '.') ?>/mese
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Data attivazione</label>
        <input
          type="date"
          id="dataAttivazione"
          name="data_attivazione"
          value="<?= date('Y-m-d') ?>"
          style="background: #0f172a; border: 1px solid var(--border); padding: 10px; border-radius: 8px; width: 100%; color: white;"
          required>
      </div>

      <div class="form-group">
        <label>Note (opzionale)</label>
        <textarea id="note" name="note" placeholder="Note interne sull'attivazione..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn ghost" onclick="chiudiModal()">Annulla</button>
        <button type="submit" class="btn primary">✓ Attiva Servizio</button>
      </div>
    </form>
  </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';
let modalEl = document.getElementById('modalAttivazione');

// Prezzi personalizzati per cliente e servizio
const prezziPersonalizzati = <?= json_encode($prezziPersonalizzati ?? []) ?>;
const serviziDisponibili = <?= json_encode(array_map(function($s) {
  return ['id' => $s['id'], 'nome' => $s['nome'], 'prezzo_mensile' => $s['prezzo_mensile']];
}, $serviziDisponibili)) ?>;

function mostraModalAttivazione(userId, clienteNome, azienda) {
  document.getElementById('userId').value = userId;
  document.getElementById('clienteNome').value = `${azienda} - ${clienteNome}`;
  document.getElementById('servizioId').value = '';
  document.getElementById('note').value = '';

  // Aggiorna i prezzi nel select in base al cliente
  const selectServizio = document.getElementById('servizioId');
  const options = selectServizio.querySelectorAll('option[value]:not([value=""])');

  options.forEach(option => {
    const servizioId = parseInt(option.value);
    const servizio = serviziDisponibili.find(s => s.id == servizioId);
    if (!servizio) return;

    // Controlla se esiste un prezzo personalizzato per questo cliente e servizio
    const prezzoPersonalizzato = prezziPersonalizzati[userId]?.[servizioId];
    const prezzoFinale = prezzoPersonalizzato !== undefined ? prezzoPersonalizzato : servizio.prezzo_mensile;

    // Aggiorna il testo dell'option
    option.textContent = `${servizio.nome} - €${Math.round(prezzoFinale)}/mese${prezzoPersonalizzato !== undefined ? ' (Personalizzato)' : ''}`;
    option.setAttribute('data-prezzo', prezzoFinale);
  });

  modalEl.classList.add('show');
}

function chiudiModal() {
  modalEl.classList.remove('show');
}

// Chiudi modal cliccando fuori
modalEl.addEventListener('click', function(e) {
  if (e.target === modalEl) {
    chiudiModal();
  }
});

// Submit form attivazione
document.getElementById('formAttivazione').addEventListener('submit', async function(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  const data = {
    user_id: formData.get('user_id'),
    servizio_id: formData.get('servizio_id'),
    data_attivazione: formData.get('data_attivazione'),
    note: formData.get('note')
  };

  try {
    const response = await fetch('/area-clienti/api/attiva-servizio.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.success) {
      alert('✓ Servizio attivato con successo!');
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile attivare il servizio'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
});

async function disattivaServizio(utenteServizioId, clienteNome, servizioNome) {
  if (!confirm(`Vuoi disattivare "${servizioNome}" per ${clienteNome}?`)) {
    return;
  }

  try {
    const response = await fetch('/area-clienti/api/disattiva-servizio.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ utente_servizio_id: utenteServizioId })
    });

    const result = await response.json();

    if (result.success) {
      alert('✓ Servizio disattivato con successo!');
      location.reload();
    } else {
      alert('❌ Errore: ' + (result.error || 'Impossibile disattivare il servizio'));
    }
  } catch (error) {
    console.error('Errore:', error);
    alert('❌ Errore di connessione');
  }
}

async function postJson(url, payload) {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify(payload)
  });
  const result = await response.json();
  if (!response.ok || !result.success) {
    throw new Error(result.error || 'Operazione non riuscita');
  }
  return result;
}

document.querySelectorAll('.js-webapp-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const url = form.querySelector('input[name="webapp_url"]').value.trim();
    try {
      await postJson('/area-clienti/api/clienti-webapp.php', { cliente_id: clienteId, webapp_url: url });
      alert('URL WebApp salvato.');
    } catch (err) {
      alert('⚠️ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-cliente-dal-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const clienteDal = form.querySelector('input[name="cliente_dal"]').value.trim();
    try {
      await postJson('/area-clienti/api/clienti-dal.php', { cliente_id: clienteId, cliente_dal: clienteDal });
      alert('✅ Data cliente salvata.');
      location.reload();
    } catch (err) {
      alert('⚠️ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-create-client-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData);
    payload.attiva_docint = payload.attiva_docint ? '1' : '0';
    try {
      const result = await postJson('/area-clienti/api/clienti-create.php', payload);
      alert('Cliente creato con successo.');
      location.reload();
    } catch (err) {
      alert('⚠️ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-delete-cliente').forEach((button) => {
  button.addEventListener('click', async () => {
    const clienteId = button.dataset.clienteId;
    if (!clienteId) return;
    if (!confirm('Eliminare definitivamente questo cliente?')) return;
    if (!confirm('Conferma finale: eliminare cliente e dati collegati?')) return;
    try {
      await postJson('/area-clienti/api/clienti-delete.php', { cliente_id: clienteId });
      alert('Cliente eliminato.');
      location.reload();
    } catch (err) {
      alert('?? ' + err.message);
    }
  });
});

document.querySelectorAll('.js-note-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const note = form.querySelector('textarea[name="note"]').value.trim();
    if (!note) return;
    try {
      await postJson('/area-clienti/api/clienti-note.php', { cliente_id: clienteId, note });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-tag-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const action = e.submitter ? e.submitter.value : 'add';
    const clienteId = form.dataset.clienteId;
    const tag = form.querySelector('input[name="tag"]').value.trim();
    const tagId = form.querySelector('select[name="tag_id"]').value;
    const color = form.querySelector('input[name="color"]').value;
    if (action === 'color' && !tagId) {
      alert('❌ Seleziona un tag dal catalogo.');
      return;
    }
    if (action === 'add' && !tagId && !tag) {
      return;
    }
    try {
      await postJson('/area-clienti/api/clienti-tag.php', {
        cliente_id: clienteId,
        tag: tag,
        tag_id: tagId,
        color: color,
        action: action
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

async function rimuoviTag(clienteId, tagId) {
  if (!confirm('Rimuovere il tag dal cliente?')) return;
  try {
    await postJson('/area-clienti/api/clienti-tag.php', { cliente_id: clienteId, tag_id: tagId, action: 'remove' });
    location.reload();
  } catch (err) {
    alert('❌ ' + err.message);
  }
}

document.querySelectorAll('.js-price-save').forEach((button) => {
  button.addEventListener('click', async () => {
    const clienteId = button.dataset.clienteId;
    const servizioId = button.dataset.servizioId;
    const container = button.closest('.pricing-item');
    const priceInput = container ? container.querySelector('input[data-field="prezzo_mensile"]') : null;
    const costInput = container ? container.querySelector('input[data-field="costo_per_pagina"]') : null;
    const priceValue = priceInput ? priceInput.value.trim() : '';
    const costValue = costInput ? costInput.value.trim() : '';
    const payload = { cliente_id: clienteId, servizio_id: servizioId };
    if (priceValue !== '') {
      const prezzo = parseFloat(priceValue);
      if (Number.isNaN(prezzo) || prezzo < 0) {
        alert('? Inserisci un prezzo valido.');
        return;
      }
      payload.prezzo_mensile = prezzo;
    }
    if (costValue !== '') {
      const costo = parseFloat(costValue);
      if (Number.isNaN(costo) || costo < 0) {
        alert('? Inserisci un costo per pagina valido.');
        return;
      }
      payload.costo_per_pagina = costo;
    }
    if (!('prezzo_mensile' in payload) && !('costo_per_pagina' in payload)) {
      alert('? Inserisci un prezzo o un costo per pagina.');
      return;
    }
    try {
      await postJson('/area-clienti/api/clienti-prezzi.php', payload);
      location.reload();
    } catch (err) {
      alert('? ' + err.message);
    }
  });
});

document.querySelectorAll('.js-price-remove').forEach((button) => {
  button.addEventListener('click', async () => {
    if (!confirm('Rimuovere il prezzo personalizzato?')) return;
    const clienteId = button.dataset.clienteId;
    const servizioId = button.dataset.servizioId;
    try {
      await postJson('/area-clienti/api/clienti-prezzi.php', {
        cliente_id: clienteId,
        servizio_id: servizioId,
        action: 'delete'
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-quota-save').forEach((button) => {
  button.addEventListener('click', async () => {
    const clienteId = button.dataset.clienteId;
    const servizioId = button.dataset.servizioId;
    const container = button.closest('.pricing-item');
    const input = container ? container.querySelector('input') : null;
    const value = input ? input.value.trim() : '';
    const quota = value === '' ? null : parseInt(value, 10);
    if (value !== '' && (Number.isNaN(quota) || quota < 0)) {
      alert('❌ Inserisci una quota valida.');
      return;
    }
    try {
      await postJson('/area-clienti/api/clienti-quote.php', {
        cliente_id: clienteId,
        servizio_id: servizioId,
        quota_documenti_mese: quota
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-quota-remove').forEach((button) => {
  button.addEventListener('click', async () => {
    if (!confirm('Rimuovere la quota personalizzata?')) return;
    const clienteId = button.dataset.clienteId;
    const servizioId = button.dataset.servizioId;
    try {
      await postJson('/area-clienti/api/clienti-quote.php', {
        action: 'delete',
        cliente_id: clienteId,
        servizio_id: servizioId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-discount-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const servizioId = form.querySelector('select[name="servizio_id"]').value;
    const tipo = form.querySelector('select[name="tipo"]').value;
    const valore = form.querySelector('input[name="valore"]').value;
    const dataInizio = form.querySelector('input[name="data_inizio"]').value;
    const dataFine = form.querySelector('input[name="data_fine"]').value;
    const note = form.querySelector('input[name="note"]').value;
    try {
      await postJson('/area-clienti/api/clienti-sconti.php', {
        cliente_id: clienteId,
        servizio_id: servizioId,
        tipo: tipo,
        valore: valore,
        data_inizio: dataInizio,
        data_fine: dataFine,
        note: note
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-discount-remove').forEach((button) => {
  button.addEventListener('click', async () => {
    if (!confirm('Rimuovere lo sconto?')) return;
    const discountId = button.dataset.discountId;
    try {
      await postJson('/area-clienti/api/clienti-sconti.php', {
        action: 'delete',
        discount_id: discountId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-coupon-assign').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const couponId = form.querySelector('select[name="coupon_id"]').value;
    if (!couponId) return;
    try {
      await postJson('/area-clienti/api/clienti-coupon.php', {
        action: 'assign',
        cliente_id: clienteId,
        coupon_id: couponId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-coupon-remove').forEach((button) => {
  button.addEventListener('click', async () => {
    if (!confirm('Rimuovere il coupon assegnato?')) return;
    const assignmentId = button.dataset.assignmentId;
    try {
      await postJson('/area-clienti/api/clienti-coupon.php', {
        action: 'remove',
        assignment_id: assignmentId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

const couponForm = document.querySelector('.js-coupon-form');
if (couponForm) {
  couponForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(couponForm);
    const payload = {
      codice: formData.get('codice'),
      tipo: formData.get('tipo'),
      valore: formData.get('valore'),
      data_inizio: formData.get('data_inizio'),
      data_fine: formData.get('data_fine'),
      max_usi: formData.get('max_usi')
    };
    try {
      await postJson('/area-clienti/api/coupon.php', payload);
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
}

document.querySelectorAll('.js-coupon-toggle').forEach((button) => {
  button.addEventListener('click', async () => {
    const couponId = button.dataset.couponId;
    const attivo = button.dataset.attivo === '1' ? 0 : 1;
    try {
      await postJson('/area-clienti/api/coupon.php', {
        action: 'toggle',
        coupon_id: couponId,
        attivo: attivo
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

const bundleCreateForm = document.querySelector('.js-bundle-create');
if (bundleCreateForm) {
  bundleCreateForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(bundleCreateForm);
    try {
      await postJson('/area-clienti/api/pacchetti.php', {
        nome: formData.get('nome'),
        descrizione: formData.get('descrizione'),
        prezzo_mensile: formData.get('prezzo_mensile')
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
}

document.querySelectorAll('.js-bundle-toggle').forEach((button) => {
  button.addEventListener('click', async () => {
    const bundleId = button.dataset.bundleId;
    const attivo = button.dataset.attivo === '1' ? 0 : 1;
    try {
      await postJson('/area-clienti/api/pacchetti.php', {
        action: 'toggle',
        pacchetto_id: bundleId,
        attivo: attivo
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-bundle-add-service').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const bundleId = form.dataset.bundleId;
    const servizioId = form.querySelector('select[name=\"servizio_id\"]').value;
    if (!servizioId) return;
    try {
      await postJson('/area-clienti/api/pacchetti-servizi.php', {
        action: 'add',
        pacchetto_id: bundleId,
        servizio_id: servizioId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-bundle-remove-service').forEach((button) => {
  button.addEventListener('click', async () => {
    const bundleId = button.dataset.bundleId;
    const servizioId = button.dataset.servizioId;
    try {
      await postJson('/area-clienti/api/pacchetti-servizi.php', {
        action: 'remove',
        pacchetto_id: bundleId,
        servizio_id: servizioId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-bundle-assign').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const pacchettoId = form.querySelector('select[name=\"pacchetto_id\"]').value;
    const dataInizio = form.querySelector('input[name=\"data_inizio\"]').value;
    const dataFine = form.querySelector('input[name=\"data_fine\"]').value;
    if (!pacchettoId) return;
    try {
      await postJson('/area-clienti/api/clienti-pacchetti.php', {
        action: 'assign',
        cliente_id: clienteId,
        pacchetto_id: pacchettoId,
        data_inizio: dataInizio,
        data_fine: dataFine
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-bundle-remove').forEach((button) => {
  button.addEventListener('click', async () => {
    if (!confirm('Rimuovere il pacchetto?')) return;
    const assignmentId = button.dataset.assignmentId;
    try {
      await postJson('/area-clienti/api/clienti-pacchetti.php', {
        action: 'remove',
        assignment_id: assignmentId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-service-update').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const servizioId = form.dataset.servizioId;
    const payload = {
      servizio_id: servizioId,
      nome: form.querySelector('input[name="nome"]').value.trim(),
      codice: form.querySelector('input[name="codice"]').value.trim(),
      prezzo_mensile: form.querySelector('input[name="prezzo_mensile"]').value,
      costo_per_pagina: form.querySelector('input[name="costo_per_pagina"]').value,
      quota_documenti_mese: form.querySelector('input[name="quota_documenti_mese"]').value,
      attivo: form.querySelector('select[name="attivo"]').value,
      descrizione: form.querySelector('textarea[name="descrizione"]').value
    };
    if (!payload.nome || !payload.codice) {
      alert('❌ Nome e codice sono obbligatori.');
      return;
    }
    try {
      await postJson('/area-clienti/api/servizi-update.php', payload);
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

const ondemandCreateForm = document.querySelector('.js-ondemand-create');
if (ondemandCreateForm) {
  ondemandCreateForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(ondemandCreateForm);
    try {
      await postJson('/area-clienti/api/ondemand-servizi.php', {
        nome: formData.get('nome'),
        descrizione: formData.get('descrizione'),
        prezzo_unitario: formData.get('prezzo_unitario')
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
}

document.querySelectorAll('.js-ondemand-toggle').forEach((button) => {
  button.addEventListener('click', async () => {
    const itemId = button.dataset.ondemandId;
    const attivo = button.dataset.attivo === '1' ? 0 : 1;
    try {
      await postJson('/area-clienti/api/ondemand-servizi.php', {
        action: 'toggle',
        servizio_id: itemId,
        attivo: attivo
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-ondemand-assign').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const servizioId = form.querySelector('select[name="servizio_id"]').value;
    const quantita = form.querySelector('input[name="quantita"]').value;
    const dataAcquisto = form.querySelector('input[name="data_acquisto"]').value;
    const prezzoOverride = form.querySelector('input[name="prezzo_unitario"]').value;
    if (!servizioId) return;
    try {
      await postJson('/area-clienti/api/ondemand-acquisti.php', {
        action: 'add',
        cliente_id: clienteId,
        servizio_id: servizioId,
        quantita: quantita,
        data_acquisto: dataAcquisto,
        prezzo_unitario: prezzoOverride
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-ondemand-remove').forEach((button) => {
  button.addEventListener('click', async () => {
    if (!confirm('Annullare l\'acquisto on-demand?')) return;
    const acquistoId = button.dataset.acquistoId;
    try {
      await postJson('/area-clienti/api/ondemand-acquisti.php', {
        action: 'cancel',
        acquisto_id: acquistoId
      });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.note-item .js-note-edit').forEach((button) => {
  button.addEventListener('click', async (e) => {
    const noteItem = e.target.closest('.note-item');
    const noteId = noteItem.dataset.noteId;
    const currentText = noteItem.querySelector('div').innerText.trim();
    const nuovoTesto = prompt('Modifica nota:', currentText);
    if (nuovoTesto === null) return;
    const testo = nuovoTesto.trim();
    if (!testo) return;
    try {
      await postJson('/area-clienti/api/clienti-note.php', { action: 'update', note_id: noteId, note: testo });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.note-item .js-note-delete').forEach((button) => {
  button.addEventListener('click', async (e) => {
    const noteItem = e.target.closest('.note-item');
    const noteId = noteItem.dataset.noteId;
    if (!confirm('Eliminare questa nota?')) return;
    try {
      await postJson('/area-clienti/api/clienti-note.php', { action: 'delete', note_id: noteId });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-event-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const titolo = form.querySelector('input[name="titolo"]').value.trim();
    const dettagli = form.querySelector('textarea[name="dettagli"]').value.trim();
    if (!titolo) return;
    try {
      await postJson('/area-clienti/api/clienti-eventi.php', { cliente_id: clienteId, titolo, dettagli });
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-doc-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const fileInput = form.querySelector('input[name="documento"]');
    if (!fileInput.files.length) return;
    const data = new FormData();
    data.append('cliente_id', clienteId);
    data.append('documento', fileInput.files[0]);
    try {
      const response = await fetch('/area-clienti/api/clienti-documenti.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken },
        body: data
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.error || 'Upload fallito');
      }
      location.reload();
    } catch (err) {
      alert('❌ ' + err.message);
    }
  });
});

document.querySelectorAll('.js-contract-form').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clienteId = form.dataset.clienteId;
    const hasServizio = form.dataset.hasServizio === '1';
    const servizioSelect = form.querySelector('select[name="servizio_id"]');
    const servizioId = servizioSelect ? servizioSelect.value : '';
    const titolo = form.querySelector('input[name="titolo"]').value.trim();
    const dataInizio = form.querySelector('input[name="data_inizio"]').value;
    const dataScadenza = form.querySelector('input[name="data_scadenza"]').value;
    const valoreAnn = form.querySelector('input[name="valore_annuo"]').value;
    const note = form.querySelector('textarea[name="note"]').value.trim();
    if (!titolo || !dataScadenza) {
      alert('? Compila almeno titolo e data scadenza.');
      return;
    }
    if (hasServizio && !servizioId) {
      alert('? Seleziona un servizio.');
      return;
    }
    try {
      const payload = {
        cliente_id: clienteId,
        titolo: titolo,
        data_inizio: dataInizio,
        data_scadenza: dataScadenza,
        valore_annuo: valoreAnn,
        note: note,
        stato: 'attivo'
      };
      if (hasServizio && servizioId) {
        payload.servizio_id = servizioId;
      }
      await postJson('/area-clienti/api/rinnovi.php', payload);
      location.reload();
    } catch (err) {
      alert('?? ' + err.message);
    }
  });
});

async function aggiornaStatoContratto(id, stato) {
  try {
    await postJson('/area-clienti/api/rinnovi.php', { action: 'update-status', id, stato });
  } catch (err) {
    alert('❌ ' + err.message);
  }
}

async function eliminaContratto(id) {
  if (!confirm('Eliminare questo contratto?')) return;
  try {
    await postJson('/area-clienti/api/rinnovi.php', { action: 'delete', id });
    location.reload();
  } catch (err) {
    alert('? ' + err.message);
  }
}

function applyTimelineFilters(clienteId) {
  const search = document.querySelector(`.js-timeline-search[data-cliente-id="${clienteId}"]`).value.trim().toLowerCase();
  const type = document.querySelector(`.js-timeline-type[data-cliente-id="${clienteId}"]`).value;
  const items = document.querySelectorAll(`details[data-cliente-id="${clienteId}"] .timeline-item`);
  items.forEach((item) => {
    const matchesType = !type || item.dataset.type === type;
    const text = item.dataset.text.toLowerCase();
    const matchesSearch = !search || text.includes(search);
    item.style.display = matchesType && matchesSearch ? '' : 'none';
  });
}

document.querySelectorAll('.js-timeline-search').forEach((input) => {
  input.addEventListener('input', () => applyTimelineFilters(input.dataset.clienteId));
});

document.querySelectorAll('.js-timeline-type').forEach((select) => {
  select.addEventListener('change', () => applyTimelineFilters(select.dataset.clienteId));
});

// === KPI Document Intelligence Functions ===
async function loadKPIForCliente(clienteId) {
  const loadingEl = document.getElementById(`kpi-loading-${clienteId}`);
  const containerEl = document.getElementById(`kpi-container-${clienteId}`);
  const errorEl = document.getElementById(`kpi-error-${clienteId}`);

  if (!loadingEl || !containerEl || !errorEl) return;

  loadingEl.style.display = 'block';
  containerEl.style.display = 'none';
  errorEl.style.display = 'none';

  try {
    const response = await fetch(`/area-clienti/api/admin-kpi-clienti.php?cliente_id=${clienteId}`);
    const result = await response.json();

    if (!result.success || !result.data || result.data.length === 0) {
      throw new Error(result.error || 'Nessun dato disponibile');
    }

    const clienteData = result.data[0];
    const kpiLocali = clienteData.kpi_locali || {};
    const kpiWebapp = clienteData.kpi_webapp || null;
    const apiStatus = clienteData.api_status || {};

    // Aggiorna KPI
    document.getElementById(`kpi-doc-${clienteId}`).textContent =
      (kpiLocali.documenti_mese || 0).toLocaleString('it-IT');

    document.getElementById(`kpi-pag-${clienteId}`).textContent =
      (kpiLocali.pagine_mese || 0).toLocaleString('it-IT');

    document.getElementById(`kpi-acc-${clienteId}`).textContent =
      kpiWebapp && kpiWebapp.accuratezza_media ? `${kpiWebapp.accuratezza_media}%` : 'N/D';

    // API Status
    const statusEl = document.getElementById(`kpi-status-${clienteId}`);
    if (apiStatus.disponibile) {
      statusEl.innerHTML = '<span style="color: #10b981;">✓ Online</span>';
    } else {
      statusEl.innerHTML = '<span style="color: #ef4444;">✗ Offline</span>';
    }

    loadingEl.style.display = 'none';
    containerEl.style.display = 'block';

  } catch (error) {
    console.error('Errore KPI cliente ' + clienteId + ':', error);
    document.getElementById(`kpi-error-msg-${clienteId}`).textContent = error.message;
    loadingEl.style.display = 'none';
    errorEl.style.display = 'block';
  }
}

function refreshClienteKPI(clienteId) {
  loadKPIForCliente(clienteId);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
