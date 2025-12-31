<?php
/**
 * Script Backup File Clienti
 * Backup automatico di file caricati dai clienti, configurazioni, uploads
 *
 * Usage:
 *   php backup-files.php [--type=full|incremental] [--compress]
 */

require_once __DIR__ . '/../area-clienti/includes/config.php';

// Configurazione
define('BACKUP_DIR', __DIR__ . '/../backups/files');
define('RETENTION_DAYS', 30);
define('RETENTION_WEEKLY', 4);
define('RETENTION_MONTHLY', 12);

// Directory da backuppare
define('BACKUP_SOURCES', [
    'uploads' => __DIR__ . '/../uploads',
    'area-clienti-configs' => __DIR__ . '/../area-clienti/configs',
    'user-files' => __DIR__ . '/../user-files',
    'logs' => __DIR__ . '/../logs'
]);

// Parse argomenti
$options = getopt('', ['type:', 'compress', 'source:']);
$backupType = $options['type'] ?? 'full';
$compress = isset($options['compress']);
$specificSource = $options['source'] ?? null;

class FileBackup {
    private $backupDir;
    private $logFile;
    private $sources;

    public function __construct($backupDir, array $sources) {
        $this->backupDir = $backupDir;
        $this->sources = $sources;
        $this->logFile = $backupDir . '/backup-files.log';

        // Crea directory se non esiste
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
    }

    /**
     * Backup completo
     */
    public function backupFull($compress = true, $specificSource = null) {
        $this->log("Inizio backup completo file");

        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_files_full_{$timestamp}";
        $backupPath = $this->backupDir . '/' . $backupName;

        // Crea directory backup
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $totalSize = 0;
        $totalFiles = 0;
        $sources = $specificSource
            ? [$specificSource => $this->sources[$specificSource]]
            : $this->sources;

        foreach ($sources as $name => $sourcePath) {
            if (!is_dir($sourcePath)) {
                $this->log("Saltata sorgente inesistente: $name ($sourcePath)", 'WARNING');
                continue;
            }

            $this->log("Backup sorgente: $name");

            $destPath = $backupPath . '/' . $name;
            $result = $this->copyDirectory($sourcePath, $destPath);

            $totalSize += $result['size'];
            $totalFiles += $result['files'];

            $this->log("  - {$result['files']} file, {$this->formatBytes($result['size'])}");
        }

        $this->log("Totale: $totalFiles file, " . $this->formatBytes($totalSize));

        // Comprimi se richiesto
        if ($compress) {
            $this->log("Compressione archivio...");
            $archivePath = $this->createArchive($backupPath, $backupName);

            // Rimuovi directory originale
            $this->removeDirectory($backupPath);

            $filesize = filesize($archivePath);
            $this->log("Archivio creato: " . basename($archivePath) . " (" . $this->formatBytes($filesize) . ")");

            $filepath = $archivePath;
        } else {
            $filepath = $backupPath;
            $filesize = $totalSize;
        }

        // Salva metadata
        $this->saveMetadata($filepath, 'full', $filesize, [
            'files_count' => $totalFiles,
            'sources' => array_keys($sources)
        ]);

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => basename($filepath),
            'size' => $filesize,
            'size_formatted' => $this->formatBytes($filesize),
            'files_count' => $totalFiles
        ];
    }

    /**
     * Backup incrementale (solo file modificati)
     */
    public function backupIncremental($compress = true) {
        $this->log("Inizio backup incrementale file");

        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_files_incremental_{$timestamp}";
        $backupPath = $this->backupDir . '/' . $backupName;

        // Trova ultimo backup
        $lastBackupTime = $this->getLastBackupTime();
        $this->log("Ultimo backup: " . date('Y-m-d H:i:s', $lastBackupTime));

        // Crea directory backup
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $totalSize = 0;
        $totalFiles = 0;

        foreach ($this->sources as $name => $sourcePath) {
            if (!is_dir($sourcePath)) {
                continue;
            }

            $this->log("Scan modifiche: $name");

            $destPath = $backupPath . '/' . $name;
            $result = $this->copyModifiedFiles($sourcePath, $destPath, $lastBackupTime);

            if ($result['files'] > 0) {
                $totalSize += $result['size'];
                $totalFiles += $result['files'];

                $this->log("  - {$result['files']} file modificati, {$this->formatBytes($result['size'])}");
            } else {
                $this->log("  - Nessuna modifica");
            }
        }

        if ($totalFiles === 0) {
            $this->log("Nessun file modificato - backup saltato");
            $this->removeDirectory($backupPath);

            return [
                'success' => true,
                'skipped' => true,
                'message' => 'Nessun file modificato'
            ];
        }

        $this->log("Totale: $totalFiles file modificati, " . $this->formatBytes($totalSize));

        // Comprimi
        if ($compress) {
            $archivePath = $this->createArchive($backupPath, $backupName);
            $this->removeDirectory($backupPath);

            $filesize = filesize($archivePath);
            $filepath = $archivePath;
        } else {
            $filepath = $backupPath;
            $filesize = $totalSize;
        }

        $this->saveMetadata($filepath, 'incremental', $filesize, [
            'files_count' => $totalFiles,
            'since' => date('Y-m-d H:i:s', $lastBackupTime)
        ]);

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => basename($filepath),
            'size' => $filesize,
            'files_count' => $totalFiles
        ];
    }

    /**
     * Copia directory ricorsivamente
     */
    private function copyDirectory($source, $dest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $totalSize = 0;
        $totalFiles = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname();

            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item, $destPath);
                $totalSize += $item->getSize();
                $totalFiles++;
            }
        }

        return [
            'size' => $totalSize,
            'files' => $totalFiles
        ];
    }

    /**
     * Copia solo file modificati dopo una certa data
     */
    private function copyModifiedFiles($source, $dest, $sinceTimestamp) {
        $totalSize = 0;
        $totalFiles = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            // Solo file modificati dopo $sinceTimestamp
            if ($item->getMTime() > $sinceTimestamp) {
                $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
                $destDir = dirname($destPath);

                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                copy($item, $destPath);
                $totalSize += $item->getSize();
                $totalFiles++;
            }
        }

        return [
            'size' => $totalSize,
            'files' => $totalFiles
        ];
    }

    /**
     * Crea archivio ZIP o TAR.GZ
     */
    private function createArchive($sourcePath, $archiveName) {
        $archivePath = $this->backupDir . '/' . $archiveName . '.tar.gz';

        // Usa tar + gzip (migliore compressione)
        $command = sprintf(
            'tar -czf %s -C %s %s 2>&1',
            escapeshellarg($archivePath),
            escapeshellarg(dirname($sourcePath)),
            escapeshellarg(basename($sourcePath))
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // Fallback: usa ZipArchive
            return $this->createZipArchive($sourcePath, $archiveName);
        }

        return $archivePath;
    }

    /**
     * Crea archivio ZIP (fallback)
     */
    private function createZipArchive($sourcePath, $archiveName) {
        $archivePath = $this->backupDir . '/' . $archiveName . '.zip';

        $zip = new ZipArchive();

        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Impossibile creare archivio ZIP");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $filePath = $item->getRealPath();
                $relativePath = substr($filePath, strlen($sourcePath) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $archivePath;
    }

    /**
     * Rimuovi directory ricorsivamente
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Ottieni timestamp ultimo backup
     */
    private function getLastBackupTime() {
        $metadataFile = $this->backupDir . '/metadata.json';

        if (!file_exists($metadataFile)) {
            return 0;
        }

        $metadata = json_decode(file_get_contents($metadataFile), true);

        if (empty($metadata)) {
            return 0;
        }

        // Ultimo backup (qualsiasi tipo)
        $lastBackup = end($metadata);

        return strtotime($lastBackup['created_at']);
    }

    /**
     * Salva metadata
     */
    private function saveMetadata($filepath, $type, $size, $extra = []) {
        $metadata = [
            'filename' => basename($filepath),
            'filepath' => $filepath,
            'type' => $type,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'created_at' => date('Y-m-d H:i:s'),
            'extra' => $extra
        ];

        $metadataFile = $this->backupDir . '/metadata.json';
        $allMetadata = [];

        if (file_exists($metadataFile)) {
            $allMetadata = json_decode(file_get_contents($metadataFile), true);
        }

        $allMetadata[] = $metadata;

        file_put_contents(
            $metadataFile,
            json_encode($allMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Pulizia backup vecchi
     */
    public function cleanup() {
        $this->log("Inizio pulizia backup file vecchi");

        $files = array_merge(
            glob($this->backupDir . '/backup_files_*.tar.gz'),
            glob($this->backupDir . '/backup_files_*.zip')
        );

        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            $age = $now - filemtime($file);
            $days = floor($age / 86400);

            $isWeekly = date('w', filemtime($file)) == 0;
            $isMonthly = date('j', filemtime($file)) == 1;

            $shouldDelete = false;

            if ($isMonthly && $days > (RETENTION_MONTHLY * 30)) {
                $shouldDelete = true;
            } elseif ($isWeekly && $days > (RETENTION_WEEKLY * 7)) {
                $shouldDelete = true;
            } elseif ($days > RETENTION_DAYS) {
                $shouldDelete = true;
            }

            if ($shouldDelete) {
                unlink($file);
                $deleted++;
                $this->log("Eliminato backup vecchio: " . basename($file));
            }
        }

        $this->log("Pulizia completata: $deleted archivi eliminati");

        return $deleted;
    }

    /**
     * Lista backup disponibili
     */
    public function listBackups() {
        $metadataFile = $this->backupDir . '/metadata.json';

        if (!file_exists($metadataFile)) {
            return [];
        }

        $metadata = json_decode(file_get_contents($metadataFile), true);

        usort($metadata, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $metadata;
    }

    /**
     * Estrai backup
     */
    public function extract($filepath, $destination) {
        $this->log("Estrazione backup: " . basename($filepath));

        if (!file_exists($filepath)) {
            throw new Exception("File backup non trovato");
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        // Determina tipo archivio
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);

        if ($ext === 'gz') {
            // TAR.GZ
            $command = sprintf(
                'tar -xzf %s -C %s 2>&1',
                escapeshellarg($filepath),
                escapeshellarg($destination)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Estrazione fallita: " . implode("\n", $output));
            }
        } elseif ($ext === 'zip') {
            // ZIP
            $zip = new ZipArchive();

            if ($zip->open($filepath) !== true) {
                throw new Exception("Impossibile aprire archivio ZIP");
            }

            $zip->extractTo($destination);
            $zip->close();
        } else {
            throw new Exception("Formato archivio non supportato: $ext");
        }

        $this->log("Estrazione completata: $destination");

        return [
            'success' => true,
            'destination' => $destination
        ];
    }

    /**
     * Log
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";

        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        echo $logMessage;
    }

    /**
     * Formatta byte
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// =============================================================================
// ESECUZIONE SCRIPT
// =============================================================================

try {
    $backup = new FileBackup(BACKUP_DIR, BACKUP_SOURCES);

    switch ($backupType) {
        case 'full':
            $result = $backup->backupFull($compress, $specificSource);
            break;

        case 'incremental':
            $result = $backup->backupIncremental($compress);
            break;

        case 'cleanup':
            $deleted = $backup->cleanup();
            $result = ['success' => true, 'deleted' => $deleted];
            break;

        case 'list':
            $backups = $backup->listBackups();
            echo "\n=== BACKUP FILE DISPONIBILI ===\n\n";
            foreach ($backups as $b) {
                $files = $b['extra']['files_count'] ?? 'N/A';
                echo "{$b['created_at']} - {$b['type']} - {$files} file - {$b['size_formatted']} - {$b['filename']}\n";
            }
            exit(0);

        default:
            throw new Exception("Tipo backup non valido: $backupType");
    }

    if ($result['success']) {
        if (isset($result['skipped'])) {
            echo "\n⏭️  {$result['message']}\n";
        } else {
            echo "\n✅ Backup file completato con successo!\n";
            echo "File: {$result['filename']}\n";
            echo "Dimensione: {$result['size_formatted']}\n";
            if (isset($result['files_count'])) {
                echo "File backuppati: {$result['files_count']}\n";
            }
        }

        // Cleanup automatico
        $backup->cleanup();

        exit(0);
    } else {
        echo "\n❌ Backup fallito: {$result['error']}\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    exit(1);
}
