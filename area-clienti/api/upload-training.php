<?php
/**
 * API Upload Training Documents
 * Gestisce upload file per richieste addestramento AI
 */

// Evita che warning/notice (es. mail()) rompano la risposta JSON
ob_start();

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/security.php';
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/error-handler.php';

header('Content-Type: application/json');

// Verifica autenticazione
if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autenticato']);
    exit;
}

$clienteId = $_SESSION['cliente_id'];

// Verifica metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

// Verifica CSRF - TEMPORANEAMENTE DISABILITATO PER DEBUG
// TODO: Riabilitare in produzione
/*
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}
*/

try {
    $existingRequestId = (int)($_POST['richiesta_id'] ?? 0);

    // Recupera dati form
    $tipoModello = $_POST['tipo_modello'] ?? '';
    $descrizione = trim($_POST['descrizione'] ?? '');
    $numDocumenti = (int)($_POST['num_documenti'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    // URL di ritorno: preferisci il servizio attivo corretto (DOC-INT) per questo utente
    $requestedServizioId = (int)($_POST['servizio_id'] ?? 0);
    $servizioId = 0;

    if ($requestedServizioId > 0) {
        $stmt = $pdo->prepare('
            SELECT 1
            FROM utenti_servizi
            WHERE user_id = :user_id AND servizio_id = :servizio_id AND stato = "attivo"
            LIMIT 1
        ');
        $stmt->execute([
            'user_id' => $clienteId,
            'servizio_id' => $requestedServizioId
        ]);
        if ($stmt->fetchColumn()) {
            $servizioId = $requestedServizioId;
        }
    }

    if ($servizioId <= 0) {
        // 1) tenta di usare il servizio Document Intelligence (se presente/attivo)
        $stmt = $pdo->prepare('
            SELECT s.id
            FROM utenti_servizi us
            JOIN servizi s ON us.servizio_id = s.id
            WHERE us.user_id = :user_id AND us.stato = "attivo" AND s.codice = "DOC-INT"
            ORDER BY us.data_attivazione DESC
            LIMIT 1
        ');
        $stmt->execute(['user_id' => $clienteId]);
        $servizioId = (int)($stmt->fetchColumn() ?: 0);
    }

    if ($servizioId <= 0) {
        // 2) fallback: primo servizio attivo dell'utente
        $stmt = $pdo->prepare('
            SELECT s.id
            FROM utenti_servizi us
            JOIN servizi s ON us.servizio_id = s.id
            WHERE us.user_id = :user_id AND us.stato = "attivo"
            ORDER BY us.data_attivazione DESC
            LIMIT 1
        ');
        $stmt->execute(['user_id' => $clienteId]);
        $servizioId = (int)($stmt->fetchColumn() ?: 0);
    }

    $returnUrl = $servizioId > 0
        ? ('/area-clienti/servizio-dettaglio.php?id=' . $servizioId . '&upload=success')
        : '/area-clienti/dashboard.php?upload=success';

    // Richiede almeno un file
    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        throw new Exception('Seleziona almeno un file da caricare');
    }

    // Se stiamo aggiornando una richiesta esistente, usa i suoi dati e non crearne una nuova
    if ($existingRequestId > 0) {
        $stmt = $pdo->prepare('
            SELECT id, tipo_modello, descrizione, num_documenti_stimati, stato
            FROM richieste_addestramento
            WHERE id = :id AND user_id = :user_id AND stato IN ("in_attesa", "in_lavorazione")
            LIMIT 1
        ');
        $stmt->execute(['id' => $existingRequestId, 'user_id' => $clienteId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            throw new Exception('Richiesta non trovata o non aggiornabile');
        }

        $richiestaId = (int)$existing['id'];
        $tipoModello = $tipoModello ?: $existing['tipo_modello'];
        $descrizione = $descrizione ?: (string)$existing['descrizione'];
        $numDocumenti = $numDocumenti > 0 ? $numDocumenti : (int)$existing['num_documenti_stimati'];

        if ($note !== '') {
            $stmt = $pdo->prepare('UPDATE richieste_addestramento SET note_admin = :note_admin WHERE id = :id AND user_id = :user_id');
            $stmt->execute(['note_admin' => $note, 'id' => $richiestaId, 'user_id' => $clienteId]);
        }
    } else {
        // Validazione (nuova richiesta)
        if (empty($tipoModello) || empty($descrizione) || $numDocumenti < 1) {
            throw new Exception('Dati mancanti o non validi');
        }
    }

    // Recupera info utente
    $stmt = $pdo->prepare('SELECT email, nome, cognome, azienda FROM utenti WHERE id = :id');
    $stmt->execute(['id' => $clienteId]);
    $utente = $stmt->fetch();

    if (!$utente) {
        throw new Exception('Utente non trovato');
    }

    // Salva richiesta nel database (solo se nuova)
    if ($existingRequestId <= 0) {
        $stmt = $pdo->prepare('
            INSERT INTO richieste_addestramento
            (user_id, tipo_modello, descrizione, num_documenti_stimati, note_admin, stato, created_at)
            VALUES (:user_id, :tipo_modello, :descrizione, :num_documenti, :note_admin, "in_attesa", NOW())
        ');
        $stmt->execute([
            'user_id' => $clienteId,
            'tipo_modello' => $tipoModello,
            'descrizione' => $descrizione,
            'num_documenti' => $numDocumenti,
            'note_admin' => $note
        ]);

        $richiestaId = (int)$pdo->lastInsertId();
    }

    // Gestione upload file
    $uploadedFiles = [];
    $uploadErrors = [];

    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        // Directory upload (FUORI da public_html per sicurezza)
        // Priorità: variabile d'ambiente > path relativo di default
        $uploadBaseDir = Config::get('UPLOAD_BASE_DIR');

        if (empty($uploadBaseDir)) {
            // Fallback a path relativo se non configurato
            $uploadBaseDir = __DIR__ . '/../../uploads/training';
            ErrorHandler::logAccess('Using default relative upload path. Consider setting UPLOAD_BASE_DIR in .env');
        }

        $uploadDir = $uploadBaseDir . '/' . $richiestaId;

        // Crea directory se non esiste
        if (!is_dir($uploadBaseDir)) {
            mkdir($uploadBaseDir, 0755, true);
        }
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Processa ogni file
        $fileCount = count($_FILES['files']['name']);

        // Verifica quota documenti/mese - TEMPORANEAMENTE DISABILITATO
        // TODO: Riabilitare in produzione
        /*
        $quota = null;
        $stmt = $pdo->prepare('
            SELECT quota_documenti_mese
            FROM clienti_quote
            WHERE cliente_id = :cliente_id AND servizio_id = :servizio_id
            LIMIT 1
        ');
        $stmt->execute(['cliente_id' => $clienteId, 'servizio_id' => $servizioId]);
        $quota = $stmt->fetchColumn();
        if ($quota === false) {
            $stmt = $pdo->prepare('
                SELECT quota_documenti_mese
                FROM servizi_quote
                WHERE servizio_id = :servizio_id
                LIMIT 1
            ');
            $stmt->execute(['servizio_id' => $servizioId]);
            $quota = $stmt->fetchColumn();
        }
        $quota = $quota !== false ? ($quota !== null ? (int)$quota : null) : null;

        if ($quota !== null) {
            $periodo = date('Y-m');
            $stmt = $pdo->prepare('
                SELECT documenti_usati
                FROM servizi_quota_uso
                WHERE cliente_id = :cliente_id AND servizio_id = :servizio_id AND periodo = :periodo
            ');
            $stmt->execute([
                'cliente_id' => $clienteId,
                'servizio_id' => $servizioId,
                'periodo' => $periodo
            ]);
            $usati = (int)($stmt->fetchColumn() ?: 0);
            if ($usati + $fileCount > $quota) {
                throw new Exception("Quota mensile superata. Disponibili " . max(0, $quota - $usati) . " documenti.");
            }
        }
        */

        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES['files']['name'][$i];
            $fileTmpName = $_FILES['files']['tmp_name'][$i];
            $fileSize = $_FILES['files']['size'][$i];
            $fileError = $_FILES['files']['error'][$i];
            $fileType = $_FILES['files']['type'][$i];

            // Verifica errori upload
            if ($fileError !== UPLOAD_ERR_OK) {
                $uploadErrors[] = "Errore upload {$fileName}";
                continue;
            }

            // Verifica dimensione (10MB max)
            if ($fileSize > 10 * 1024 * 1024) {
                $uploadErrors[] = "{$fileName} troppo grande (max 10MB)";
                continue;
            }

            // Verifica tipo file
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpName);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $uploadErrors[] = "{$fileName} tipo non supportato";
                continue;
            }

            // Genera nome file sicuro
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $safeFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $safeFileName = substr($safeFileName, 0, 100); // Limita lunghezza

            $destination = $uploadDir . '/' . $safeFileName;

            // Sposta file
            if (move_uploaded_file($fileTmpName, $destination)) {
                $uploadedFiles[] = [
                    'original_name' => $fileName,
                    'saved_name' => $safeFileName,
                    'size' => $fileSize,
                    'path' => $destination
                ];

                // Salva riferimento nel database
                $stmt = $pdo->prepare('
                    INSERT INTO richieste_addestramento_files
                    (richiesta_id, filename_originale, filename_storage, file_size, file_path, mime_type)
                    VALUES (:richiesta_id, :original, :storage, :size, :path, :mime_type)
                ');
                $stmt->execute([
                    'richiesta_id' => $richiestaId,
                    'original' => $fileName,
                    'storage' => $safeFileName,
                    'size' => $fileSize,
                    'path' => $destination,
                    'mime_type' => $mimeType
                ]);
            } else {
                $uploadErrors[] = "Impossibile salvare {$fileName}";
            }
        }
    }

    // Aggiorna uso quota (solo file caricati)
    if (!empty($uploadedFiles) && $servizioId > 0) {
        $periodo = date('Y-m');
        $stmt = $pdo->prepare('
            INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati)
            VALUES (:cliente_id, :servizio_id, :periodo, :count)
            ON DUPLICATE KEY UPDATE documenti_usati = documenti_usati + VALUES(documenti_usati)
        ');
        $stmt->execute([
            'cliente_id' => $clienteId,
            'servizio_id' => $servizioId,
            'periodo' => $periodo,
            'count' => count($uploadedFiles)
        ]);
    }

    // Invia email di notifica al team
    $emailTo = Config::get('TRAINING_EMAIL', 'ai-training@finch-ai.it');
    $emailSubject = "Nuova Richiesta Addestramento - {$utente['azienda']}";
    $emailBody = "
NUOVA RICHIESTA ADDESTRAMENTO MODELLO AI

Richiesta ID: {$richiestaId}
Data: " . date('d/m/Y H:i') . "

=== CLIENTE ===
Nome: {$utente['nome']} {$utente['cognome']}
Azienda: {$utente['azienda']}
Email: {$utente['email']}

=== DETTAGLI RICHIESTA ===
Tipo Modello: {$tipoModello}
Num. Documenti Stimati: {$numDocumenti}

Descrizione:
{$descrizione}

Note:
{$note}

=== FILE CARICATI ===
Totale file: " . count($uploadedFiles) . "
";

    foreach ($uploadedFiles as $file) {
        $emailBody .= "- {$file['original_name']} (" . round($file['size'] / 1024, 2) . " KB)\n";
    }

    if (!empty($uploadErrors)) {
        $emailBody .= "\n=== ERRORI UPLOAD ===\n";
        foreach ($uploadErrors as $error) {
            $emailBody .= "- {$error}\n";
        }
    }

    $emailBody .= "\n
Gestisci richiesta: https://finch-ai.it/area-clienti/admin/richieste-addestramento.php?id={$richiestaId}
";

    $emailHeaders = "From: Area Clienti <noreply@finch-ai.it>\r\n";
    $emailHeaders .= "Reply-To: {$utente['email']}\r\n";
    $emailHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $mailSent = @mail($emailTo, $emailSubject, $emailBody, $emailHeaders);

    if (!$mailSent) {
        ErrorHandler::logError('Failed to send training request notification email', [
            'richiesta_id' => $richiestaId,
            'user_id' => $clienteId,
            'email_to' => $emailTo
        ]);
    } else {
        ErrorHandler::logAccess('Training request notification email sent', [
            'richiesta_id' => $richiestaId,
            'user_id' => $clienteId
        ]);
    }

    // Log successo
    ErrorHandler::logAccess('Training request created', [
        'user_id' => $clienteId,
        'request_id' => $richiestaId,
        'files_count' => count($uploadedFiles)
    ]);

    // Risposta
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'success' => true,
        'richiesta_id' => $richiestaId,
        'files_uploaded' => count($uploadedFiles),
        'files_errors' => $uploadErrors,
        'message' => 'Richiesta inviata con successo!',
        'redirect_url' => $returnUrl
    ]);

} catch (Exception $e) {
    ErrorHandler::logError('Upload training error: ' . $e->getMessage());

    http_response_code(500);
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'success' => false,
        'error' => Config::isDebug() ? $e->getMessage() : 'Errore durante il salvataggio'
    ]);
}
