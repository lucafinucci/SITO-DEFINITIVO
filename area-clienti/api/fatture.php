<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/fatture-settings.php';
require __DIR__ . '/../includes/fatture-email.php';

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

// Verifica CSRF
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'update-status':
            // Aggiorna stato fattura
            $fatturaId = (int)($input['fattura_id'] ?? 0);
            $nuovoStato = $input['stato'] ?? '';

            $statiValidi = ['bozza', 'emessa', 'inviata', 'pagata', 'scaduta', 'annullata'];
            if (!in_array($nuovoStato, $statiValidi, true)) {
                throw new Exception('Stato non valido');
            }

            if (!$fatturaId) {
                throw new Exception('ID fattura mancante');
            }

            $fattureSettings = getFattureSettings($pdo);

            if ($nuovoStato === 'emessa' && $fattureSettings['invio_modalita'] === 'automatico') {
                $sendResult = sendFatturaEmail($pdo, $fatturaId);
                if (!$sendResult['success']) {
                    throw new Exception('Invio automatico fallito: ' . ($sendResult['error'] ?? 'errore sconosciuto'));
                }
                $nuovoStato = 'inviata';
            }

            $updateSql = 'UPDATE fatture SET stato = :stato';
            $paramsUpdate = ['stato' => $nuovoStato, 'id' => $fatturaId];

            if ($nuovoStato === 'pagata') {
                $stmtCols = $pdo->prepare("
                    SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'fatture'
                      AND COLUMN_NAME IN ('data_pagamento')
                ");
                $stmtCols->execute();
                $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

                if (in_array('data_pagamento', $cols, true)) {
                    $updateSql .= ', data_pagamento = :data_pagamento';
                    $paramsUpdate['data_pagamento'] = date('Y-m-d');
                }
            }

            $updateSql .= ' WHERE id = :id';
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute($paramsUpdate);

            echo json_encode(['success' => true, 'message' => 'Stato aggiornato']);
            break;

        case 'mark-paid':
            // Segna fattura come pagata
            $fatturaId = (int)($input['fattura_id'] ?? 0);
            $dataPagamento = $input['data_pagamento'] ?? '';
            if ($dataPagamento === '') {
                $dataPagamento = date('Y-m-d');
            }
            $metodoPagamento = $input['metodo_pagamento'] ?? 'Bonifico bancario';
            $importo = (float)($input['importo'] ?? 0);

            if (!$fatturaId) {
                throw new Exception('ID fattura mancante');
            }

            // Recupera fattura
            $stmt = $pdo->prepare('SELECT * FROM fatture WHERE id = :id');
            $stmt->execute(['id' => $fatturaId]);
            $fattura = $stmt->fetch();

            if (!$fattura) {
                throw new Exception('Fattura non trovata');
            }

            // Se importo non specificato, usa totale fattura
            if ($importo <= 0) {
                $importo = (float)$fattura['totale'];
            }

            $pdo->beginTransaction();

            // Aggiorna fattura (compatibilita schema)
            $stmtCols = $pdo->prepare("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'fatture'
                  AND COLUMN_NAME IN ('data_pagamento', 'metodo_pagamento')
            ");
            $stmtCols->execute();
            $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

            $updateFields = ["stato = 'pagata'"];
            $paramsUpdate = ['id' => $fatturaId];

            if (in_array('data_pagamento', $cols, true)) {
                $updateFields[] = 'data_pagamento = :data_pagamento';
                $paramsUpdate['data_pagamento'] = $dataPagamento;
            }

            if (in_array('metodo_pagamento', $cols, true)) {
                $updateFields[] = 'metodo_pagamento = :metodo_pagamento';
                $paramsUpdate['metodo_pagamento'] = $metodoPagamento;
            }

            $stmt = $pdo->prepare('UPDATE fatture SET ' . implode(', ', $updateFields) . ' WHERE id = :id');
            $stmt->execute($paramsUpdate);

            // Registra pagamento (se tabella disponibile)
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'fatture_pagamenti'
            ");
            $stmt->execute();
            $hasPagamenti = ((int)$stmt->fetchColumn()) > 0;

            if ($hasPagamenti) {
                $stmt = $pdo->prepare('
                    INSERT INTO fatture_pagamenti (
                        fattura_id,
                        importo,
                        data_pagamento,
                        metodo_pagamento,
                        created_by
                    ) VALUES (
                        :fattura_id,
                        :importo,
                        :data_pagamento,
                        :metodo_pagamento,
                        :created_by
                    )
                ');
                $stmt->execute([
                    'fattura_id' => $fatturaId,
                    'importo' => $importo,
                    'data_pagamento' => $dataPagamento,
                    'metodo_pagamento' => $metodoPagamento,
                    'created_by' => $_SESSION['cliente_id']
                ]);
            }

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Pagamento registrato']);
            break;

        case 'delete':
            // Elimina fattura (solo se in bozza)
            $fatturaId = (int)($input['fattura_id'] ?? 0);

            if (!$fatturaId) {
                throw new Exception('ID fattura mancante');
            }

            $stmt = $pdo->prepare('SELECT stato FROM fatture WHERE id = :id');
            $stmt->execute(['id' => $fatturaId]);
            $fattura = $stmt->fetch();

            if (!$fattura) {
                throw new Exception('Fattura non trovata');
            }

            if ($fattura['stato'] !== 'bozza') {
                throw new Exception('Puoi eliminare solo fatture in bozza');
            }

            $stmt = $pdo->prepare('DELETE FROM fatture WHERE id = :id');
            $stmt->execute(['id' => $fatturaId]);

            echo json_encode(['success' => true, 'message' => 'Fattura eliminata']);
            break;

        case 'add-note':
            // Aggiungi nota a fattura
            $fatturaId = (int)($input['fattura_id'] ?? 0);
            $note = trim($input['note'] ?? '');

            if (!$fatturaId) {
                throw new Exception('ID fattura mancante');
            }

            $stmt = $pdo->prepare('UPDATE fatture SET note = :note WHERE id = :id');
            $stmt->execute(['note' => $note, 'id' => $fatturaId]);

            echo json_encode(['success' => true, 'message' => 'Nota aggiornata']);
            break;

        default:
            throw new Exception('Azione non riconosciuta');
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
