<?php
/**
 * MOCK API KPI WEBAPP - Solo per Testing Locale
 * Simula l'endpoint della webapp esterna per test in locale
 *
 * Questo endpoint simula la risposta di app.finch-ai.it/api/kpi/documenti
 * Utile per testare la dashboard admin senza avere la webapp configurata
 */

header('Content-Type: application/json');

// Delay simulato per emulare chiamata di rete
usleep(200000); // 0.2 secondi

// Verifica token (per test locale usa lo stesso token di admin-kpi-clienti.php)
$TOKEN_TEST = 'test_token_locale_123456'; // Deve coincidere con quello in admin-kpi-clienti.php
$clienteId = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
$token = $_GET['token'] ?? '';

if (empty($token) || $token !== $TOKEN_TEST) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Token non valido o mancante'
    ]);
    exit;
}

if ($clienteId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'cliente_id mancante o non valido'
    ]);
    exit;
}

// Dati mock diversi per ogni cliente (basati su cliente_id)
$seed = $clienteId * 1000;
srand($seed);

$documentiBase = rand(5000, 20000);
$processingRate = rand(85, 98) / 100;
$documentiProcessati = (int)($documentiBase * $processingRate);

$paginePerDoc = rand(3, 8);
$pagineTotali = $documentiBase * $paginePerDoc;

$documentiMese = rand(800, 3000);
$pagineMese = $documentiMese * $paginePerDoc;

$accuratezza = rand(920, 990) / 10; // 92.0 - 99.0
$tempoMedio = rand(15, 45) / 10; // 1.5 - 4.5 secondi
$automazione = rand(880, 960) / 10; // 88.0 - 96.0

// Calcoli KPI
$minutiRisparmiati = $documentiProcessati * (5 - ($tempoMedio / 60));
$oreRisparmiate = (int)($minutiRisparmiati / 60);

$costoMensile = 99;
$costoOraDipendente = 25;
$risparmioMensile = ($oreRisparmiate / 6) * $costoOraDipendente;
$roi = (int)((($risparmioMensile - $costoMensile) / $costoMensile) * 100);

// Trend ultimi 6 mesi (dati crescenti)
$trendMensile = [];
$dataInizio = new DateTime('-6 months');
for ($i = 0; $i < 6; $i++) {
    $data = clone $dataInizio;
    $data->modify("+{$i} months");
    $periodo = $data->format('Y-m');

    $docMensili = (int)($documentiMese * (0.7 + ($i * 0.05)));
    $pagMensili = $docMensili * $paginePerDoc;
    $autoMensile = $automazione - (6 - $i) * 0.8;

    $trendMensile[] = [
        'periodo' => $periodo,
        'documenti' => $docMensili,
        'pagine' => $pagMensili,
        'automazione' => round($autoMensile, 1)
    ];
}

// Modelli AI attivi (randomizzati per varietà)
$tipiModelli = [
    'Fatture Elettroniche' => 'DDT & Fatture',
    'Contratti Commerciali' => 'Contratti',
    'Bolle di Trasporto' => 'Logistica',
    'Documenti Identità' => 'Documenti ID',
    'Certificati Medici' => 'Sanità',
    'Ordini di Acquisto' => 'Ordini',
];

$numModelli = rand(2, 4);
$modelliKeys = array_rand($tipiModelli, $numModelli);
if (!is_array($modelliKeys)) {
    $modelliKeys = [$modelliKeys];
}

$modelliAttivi = [];
foreach ($modelliKeys as $idx => $key) {
    $modelliAttivi[] = [
        'id' => $idx + 1,
        'nome' => $key,
        'tipo' => $tipiModelli[$key],
        'accuratezza' => rand(940, 990) / 10,
        'documenti_processati' => rand(500, 5000),
        'ultima_versione' => date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'))
    ];
}

// Risposta mock
$response = [
    'success' => true,
    'data' => [
        // KPI Generali
        'documenti_totali' => $documentiBase,
        'documenti_processati' => $documentiProcessati,
        'documenti_mese_corrente' => $documentiMese,
        'pagine_analizzate_totali' => $pagineTotali,
        'pagine_mese_corrente' => $pagineMese,

        // Metriche Qualità
        'accuratezza_media' => $accuratezza,
        'tempo_medio_lettura' => $tempoMedio,
        'automazione_percentuale' => $automazione,

        // KPI Business
        'errori_evitati' => (int)($documentiProcessati * 0.025),
        'tempo_risparmiato' => $oreRisparmiate . 'h',
        'roi' => $roi . '%',

        // Periodo
        'periodo_riferimento' => date('Y-m'),

        // Trend
        'trend_mensile' => $trendMensile,

        // Modelli
        'modelli_attivi' => $modelliAttivi,

        // Meta (per debug)
        '_mock' => true,
        '_cliente_id' => $clienteId
    ],
    'timestamp' => date('c')
];

echo json_encode($response, JSON_PRETTY_PRINT);
