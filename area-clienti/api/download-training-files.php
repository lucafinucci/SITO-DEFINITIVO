<?php
/**
 * API Download File Training
 * Permette download sicuro file caricati dai clienti
 * SOLO per utenti admin
 */

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/security.php';
require __DIR__ . '/../includes/error-handler.php';

// Verifica autenticazione
if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    die('Accesso negato');
}

// Recupera utente
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

// SOLO admin possono scaricare file di altri utenti
$isAdmin = ($user && $user['ruolo'] === 'admin');

// Parametri richiesta
$fileId = (int)($_GET['file_id'] ?? 0);
$richiestaId = (int)($_GET['richiesta_id'] ?? 0);

if (!$fileId && !$richiestaId) {
    http_response_code(400);
    die('Parametro mancante: file_id o richiesta_id');
}

// Download singolo file
if ($fileId) {
    $stmt = $pdo->prepare('
        SELECT f.*, r.user_id
        FROM richieste_addestramento_files f
        JOIN richieste_addestramento r ON f.richiesta_id = r.id
        WHERE f.id = :file_id
    ');
    $stmt->execute(['file_id' => $fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        http_response_code(404);
        die('File non trovato');
    }

    // Verifica permessi
    if (!$isAdmin && $file['user_id'] != $_SESSION['cliente_id']) {
        http_response_code(403);
        die('Non hai permesso di scaricare questo file');
    }

    // Path file
    $filePath = $file['file_path'];

    if (!file_exists($filePath)) {
        http_response_code(404);
        die('File non trovato sul server');
    }

    // Download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['filename_originale'] . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache');

    readfile($filePath);

    ErrorHandler::logAccess('File downloaded', [
        'file_id' => $fileId,
        'user_id' => $_SESSION['cliente_id']
    ]);

    exit;
}

// Download tutti i file di una richiesta (ZIP)
if ($richiestaId) {
    $stmt = $pdo->prepare('SELECT user_id FROM richieste_addestramento WHERE id = :id');
    $stmt->execute(['id' => $richiestaId]);
    $richiesta = $stmt->fetch();

    if (!$richiesta) {
        http_response_code(404);
        die('Richiesta non trovata');
    }

    // Verifica permessi
    if (!$isAdmin && $richiesta['user_id'] != $_SESSION['cliente_id']) {
        http_response_code(403);
        die('Non hai permesso di scaricare questi file');
    }

    // Recupera tutti i file della richiesta
    $stmt = $pdo->prepare('
        SELECT id, filename_originale, file_path
        FROM richieste_addestramento_files
        WHERE richiesta_id = :richiesta_id
    ');
    $stmt->execute(['richiesta_id' => $richiestaId]);
    $files = $stmt->fetchAll();

    if (empty($files)) {
        http_response_code(404);
        die('Nessun file trovato per questa richiesta');
    }

    // Crea ZIP temporaneo
    $zipName = 'richiesta_' . $richiestaId . '_' . date('Ymd_His') . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        http_response_code(500);
        die('Impossibile creare archivio ZIP');
    }

    // Aggiungi file allo ZIP
    foreach ($files as $file) {
        if (file_exists($file['file_path'])) {
            $zip->addFile($file['file_path'], $file['filename_originale']);
        }
    }

    $zip->close();

    // Download ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: no-cache');

    readfile($zipPath);

    // Elimina ZIP temporaneo
    unlink($zipPath);

    ErrorHandler::logAccess('Richiesta files downloaded as ZIP', [
        'richiesta_id' => $richiestaId,
        'files_count' => count($files),
        'user_id' => $_SESSION['cliente_id']
    ]);

    exit;
}
