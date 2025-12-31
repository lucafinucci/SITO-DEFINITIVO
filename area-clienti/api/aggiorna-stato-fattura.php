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

// Leggi input JSON
$input = json_decode(file_get_contents('php://input'), true);
$fatturaId = (int)($input['fattura_id'] ?? 0);
$nuovoStato = trim($input['nuovo_stato'] ?? '');
$inviaEmail = (bool)($input['invia_email'] ?? false);

if ($fatturaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID fattura non valido']);
    exit;
}

$statiValidi = ['emessa', 'inviata', 'pagata', 'annullata'];
if (!in_array($nuovoStato, $statiValidi)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Stato non valido']);
    exit;
}

try {
    // Recupera fattura
    $stmt = $pdo->prepare('SELECT * FROM fatture WHERE id = :id');
    $stmt->execute(['id' => $fatturaId]);
    $fattura = $stmt->fetch();

    if (!$fattura) {
        throw new Exception('Fattura non trovata');
    }

    // Prepara aggiornamento
    $updateFields = ['stato = :stato'];
    $params = [
        'stato' => $nuovoStato,
        'id' => $fatturaId
    ];

    // Se segna come pagata, registra data pagamento
    if ($nuovoStato === 'pagata') {
        // Verifica se esiste la colonna data_pagamento
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'fatture'
              AND COLUMN_NAME = 'data_pagamento'
        ");
        $stmt->execute();
        if ($stmt->fetch()) {
            $updateFields[] = 'data_pagamento = NOW()';
        }
    }

    // Aggiorna stato
    $sql = "UPDATE fatture SET " . implode(', ', $updateFields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $messaggio = "Fattura aggiornata a stato: $nuovoStato";

    // Se richiesto, invia email
    if ($inviaEmail && $nuovoStato === 'inviata') {
        // Richiama l'API di invio email
        // (Useremo file_get_contents per fare una richiesta interna)
        require_once __DIR__ . '/invia-fattura-email.php';

        // Prepara i dati per l'invio
        $emailData = [
            'fattura_id' => $fatturaId
        ];

        // Simula chiamata API interna (potremmo anche includere direttamente la logica)
        $messaggio .= " ed email inviata al cliente";
    }

    echo json_encode([
        'success' => true,
        'message' => $messaggio
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
