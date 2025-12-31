<?php
/**
 * ESEMPIO IMPLEMENTAZIONE API KPI PER WEBAPP
 *
 * Questo file è un ESEMPIO di come implementare l'endpoint
 * /api/kpi/documenti sulla webapp esterna (app.finch-ai.it)
 *
 * NON INCLUDERE QUESTO FILE NELL'AREA CLIENTI
 * Questo file va posizionato su: app.finch-ai.it/api/kpi/documenti.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://tuosito.com'); // Cambia con il tuo dominio
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');

// 1. CONFIGURAZIONE
$TOKEN_SICURO = 'INSERISCI_LO_STESSO_TOKEN_USATO_IN_admin-kpi-clienti.php';

// 2. VERIFICA AUTENTICAZIONE
$clienteId = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
$token = $_GET['token'] ?? '';

if (empty($token) || $token !== $TOKEN_SICURO) {
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

try {
    // 3. CONNESSIONE DATABASE WEBAPP
    // IMPORTANTE: Usa le credenziali del database della WEBAPP, non dell'area clienti!
    $dsn = 'mysql:host=localhost;dbname=webapp_db;charset=utf8mb4';
    $username = 'webapp_user';
    $password = 'webapp_password';

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 4. RECUPERA DATI CLIENTE DALLA WEBAPP

    // Esempio: Tabella "documenti_processati" sulla webapp
    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) as documenti_totali,
            COUNT(CASE WHEN stato = "processato" THEN 1 END) as documenti_processati,
            SUM(num_pagine) as pagine_totali,
            AVG(accuratezza) as accuratezza_media,
            AVG(tempo_elaborazione) as tempo_medio
        FROM documenti_processati
        WHERE cliente_id = :cliente_id
    ');
    $stmt->execute(['cliente_id' => $clienteId]);
    $stats = $stmt->fetch();

    // Dati mese corrente
    $periodoCorrente = date('Y-m');
    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) as documenti_mese,
            SUM(num_pagine) as pagine_mese
        FROM documenti_processati
        WHERE cliente_id = :cliente_id
            AND DATE_FORMAT(created_at, "%Y-%m") = :periodo
    ');
    $stmt->execute([
        'cliente_id' => $clienteId,
        'periodo' => $periodoCorrente
    ]);
    $statsMese = $stmt->fetch();

    // Trend ultimi 6 mesi
    $stmt = $pdo->prepare('
        SELECT
            DATE_FORMAT(created_at, "%Y-%m") as periodo,
            COUNT(*) as documenti,
            SUM(num_pagine) as pagine,
            AVG(accuratezza) as automazione
        FROM documenti_processati
        WHERE cliente_id = :cliente_id
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, "%Y-%m")
        ORDER BY periodo ASC
    ');
    $stmt->execute(['cliente_id' => $clienteId]);
    $trendMensile = $stmt->fetchAll();

    // Modelli AI attivi
    $stmt = $pdo->prepare('
        SELECT
            id,
            nome,
            tipo,
            accuratezza,
            documenti_processati,
            ultima_versione
        FROM modelli_ai
        WHERE cliente_id = :cliente_id
            AND attivo = 1
        ORDER BY ultima_versione DESC
    ');
    $stmt->execute(['cliente_id' => $clienteId]);
    $modelliAttivi = $stmt->fetchAll();

    // 5. CALCOLI KPI
    $documentiTotali = (int)($stats['documenti_totali'] ?? 0);
    $documentiProcessati = (int)($stats['documenti_processati'] ?? 0);
    $pagineTotali = (int)($stats['pagine_totali'] ?? 0);
    $accuratezzaMedia = round((float)($stats['accuratezza_media'] ?? 0), 1);
    $tempoMedio = round((float)($stats['tempo_medio'] ?? 0), 1);

    $documentiMese = (int)($statsMese['documenti_mese'] ?? 0);
    $pagineMese = (int)($statsMese['pagine_mese'] ?? 0);

    // Calcolo tempo risparmiato (esempio: 5 minuti per documento manuale vs 0.5 automatico)
    $minutiRisparmiati = $documentiProcessati * (5 - 0.5);
    $oreRisparmiate = round($minutiRisparmiati / 60);

    // Calcolo ROI (esempio)
    $costoMensile = 99; // Prezzo abbonamento
    $costoOraDipendente = 25;
    $risparmioMensile = ($oreRisparmiate / 6) * $costoOraDipendente; // Ultimi 6 mesi
    $roi = $costoMensile > 0 ? round((($risparmioMensile - $costoMensile) / $costoMensile) * 100) : 0;

    // 6. PREPARA RISPOSTA
    $response = [
        'success' => true,
        'data' => [
            // KPI Generali
            'documenti_totali' => $documentiTotali,
            'documenti_processati' => $documentiProcessati,
            'documenti_mese_corrente' => $documentiMese,
            'pagine_analizzate_totali' => $pagineTotali,
            'pagine_mese_corrente' => $pagineMese,

            // Metriche Qualità
            'accuratezza_media' => $accuratezzaMedia,
            'tempo_medio_lettura' => $tempoMedio,
            'automazione_percentuale' => $documentiTotali > 0
                ? round(($documentiProcessati / $documentiTotali) * 100, 1)
                : 0,

            // KPI Business
            'errori_evitati' => (int)($documentiProcessati * 0.025), // Stima 2.5% errori evitati
            'tempo_risparmiato' => $oreRisparmiate . 'h',
            'roi' => $roi . '%',

            // Periodo
            'periodo_riferimento' => $periodoCorrente,

            // Trend
            'trend_mensile' => array_map(function($row) {
                return [
                    'periodo' => $row['periodo'],
                    'documenti' => (int)$row['documenti'],
                    'pagine' => (int)$row['pagine'],
                    'automazione' => round((float)$row['automazione'], 1)
                ];
            }, $trendMensile),

            // Modelli
            'modelli_attivi' => array_map(function($row) {
                return [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'tipo' => $row['tipo'],
                    'accuratezza' => round((float)$row['accuratezza'], 1),
                    'documenti_processati' => (int)$row['documenti_processati'],
                    'ultima_versione' => $row['ultima_versione']
                ];
            }, $modelliAttivi)
        ],
        'timestamp' => date('c')
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Log errore (non mostrare dettagli in produzione!)
    error_log('KPI API Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore interno del server'
        // In debug mode potresti aggiungere: 'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('KPI API Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore imprevisto'
    ]);
}

/**
 * ESEMPIO TABELLE DATABASE WEBAPP
 *
 * CREATE TABLE documenti_processati (
 *   id INT PRIMARY KEY AUTO_INCREMENT,
 *   cliente_id INT NOT NULL,
 *   nome_file VARCHAR(255),
 *   num_pagine INT DEFAULT 0,
 *   stato ENUM('caricato', 'processing', 'processato', 'errore') DEFAULT 'caricato',
 *   accuratezza DECIMAL(5,2) DEFAULT 0,
 *   tempo_elaborazione DECIMAL(10,2) DEFAULT 0,
 *   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *   INDEX idx_cliente_data (cliente_id, created_at)
 * );
 *
 * CREATE TABLE modelli_ai (
 *   id INT PRIMARY KEY AUTO_INCREMENT,
 *   cliente_id INT NOT NULL,
 *   nome VARCHAR(255),
 *   tipo VARCHAR(100),
 *   accuratezza DECIMAL(5,2) DEFAULT 0,
 *   documenti_processati INT DEFAULT 0,
 *   attivo BOOLEAN DEFAULT 1,
 *   ultima_versione DATE,
 *   INDEX idx_cliente (cliente_id)
 * );
 */
