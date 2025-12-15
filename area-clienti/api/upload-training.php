<?php
/**
 * API Upload Training Documents
 * Gestisce upload file per richieste addestramento AI
 */

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

// Verifica CSRF
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}

try {
    // Recupera dati form
    $tipoModello = $_POST['tipo_modello'] ?? '';
    $descrizione = trim($_POST['descrizione'] ?? '');
    $numDocumenti = (int)($_POST['num_documenti'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    // Validazione
    if (empty($tipoModello) || empty($descrizione) || $numDocumenti < 1) {
        throw new Exception('Dati mancanti o non validi');
    }

    // Recupera info utente
    $stmt = $pdo->prepare('SELECT email, nome, cognome, azienda FROM utenti WHERE id = :id');
    $stmt->execute(['id' => $clienteId]);
    $utente = $stmt->fetch();

    if (!$utente) {
        throw new Exception('Utente non trovato');
    }

    // Salva richiesta nel database
    $stmt = $pdo->prepare('
        INSERT INTO richieste_addestramento
        (user_id, tipo_modello, descrizione, num_documenti_stimati, note, stato, created_at)
        VALUES (:user_id, :tipo_modello, :descrizione, :num_documenti, :note, "in_attesa", NOW())
    ');
    $stmt->execute([
        'user_id' => $clienteId,
        'tipo_modello' => $tipoModello,
        'descrizione' => $descrizione,
        'num_documenti' => $numDocumenti,
        'note' => $note
    ]);

    $richiestaId = $pdo->lastInsertId();

    // Gestione upload file
    $uploadedFiles = [];
    $uploadErrors = [];

    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        // Directory upload (FUORI da public_html per sicurezza)
        $uploadBaseDir = __DIR__ . '/../../uploads/training';

        // Su Aruba, usa path assoluto tipo:
        // $uploadBaseDir = '/home/tuoutente/uploads/training';

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
                    (richiesta_id, filename_originale, filename_salvato, file_size, file_path)
                    VALUES (:richiesta_id, :original, :saved, :size, :path)
                ');
                $stmt->execute([
                    'richiesta_id' => $richiestaId,
                    'original' => $fileName,
                    'saved' => $safeFileName,
                    'size' => $fileSize,
                    'path' => $destination
                ]);
            } else {
                $uploadErrors[] = "Impossibile salvare {$fileName}";
            }
        }
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

    @mail($emailTo, $emailSubject, $emailBody, $emailHeaders);

    // Log successo
    ErrorHandler::logAccess('Training request created', [
        'user_id' => $clienteId,
        'request_id' => $richiestaId,
        'files_count' => count($uploadedFiles)
    ]);

    // Risposta
    echo json_encode([
        'success' => true,
        'richiesta_id' => $richiestaId,
        'files_uploaded' => count($uploadedFiles),
        'files_errors' => $uploadErrors,
        'message' => 'Richiesta inviata con successo!'
    ]);

} catch (Exception $e) {
    ErrorHandler::logError('Upload training error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => Config::isDebug() ? $e->getMessage() : 'Errore durante il salvataggio'
    ]);
}
