<?php
/**
 * Script Backup Database MySQL
 * Backup automatico del database con compressione e retention
 *
 * Usage:
 *   php backup-database.php [--type=full|incremental] [--compress] [--encrypt]
 */

require_once __DIR__ . '/../area-clienti/includes/db.php';
require_once __DIR__ . '/../area-clienti/includes/config.php';

// Configurazione
define('BACKUP_DIR', __DIR__ . '/../backups/database');
define('RETENTION_DAYS', 30);           // Backup completi conservati per 30 giorni
define('RETENTION_DAILY', 7);           // Backup giornalieri ultimi 7 giorni
define('RETENTION_WEEKLY', 4);          // Backup settimanali ultimi 4 settimane
define('RETENTION_MONTHLY', 12);        // Backup mensili ultimi 12 mesi

// Parse argomenti
$options = getopt('', ['type:', 'compress', 'encrypt', 'tables:']);
$backupType = $options['type'] ?? 'full';
$compress = isset($options['compress']);
$encrypt = isset($options['encrypt']);
$specificTables = isset($options['tables']) ? explode(',', $options['tables']) : null;

class DatabaseBackup {
    private $pdo;
    private $dbName;
    private $backupDir;
    private $logFile;

    public function __construct($pdo, $backupDir) {
        $this->pdo = $pdo;
        $this->backupDir = $backupDir;
        $this->logFile = $backupDir . '/backup.log';

        // Crea directory se non esiste
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Ottieni nome database
        $stmt = $pdo->query('SELECT DATABASE()');
        $this->dbName = $stmt->fetchColumn();
    }

    /**
     * Esegue backup completo
     */
    public function backupFull($compress = true, $encrypt = false) {
        $this->log("Inizio backup completo database: {$this->dbName}");

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_full_{$this->dbName}_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        try {
            // Usa mysqldump
            $this->mysqldump($filepath);

            $filesize = filesize($filepath);
            $this->log("Backup SQL creato: $filename (" . $this->formatBytes($filesize) . ")");

            // Comprimi se richiesto
            if ($compress) {
                $filepath = $this->compress($filepath);
                $filesize = filesize($filepath);
                $this->log("Backup compresso: " . basename($filepath) . " (" . $this->formatBytes($filesize) . ")");
            }

            // Cripta se richiesto
            if ($encrypt) {
                $filepath = $this->encrypt($filepath);
                $filesize = filesize($filepath);
                $this->log("Backup criptato: " . basename($filepath) . " (" . $this->formatBytes($filesize) . ")");
            }

            // Salva metadata
            $this->saveMetadata($filepath, 'full', $filesize);

            $this->log("Backup completato con successo: " . basename($filepath));

            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => basename($filepath),
                'size' => $filesize,
                'size_formatted' => $this->formatBytes($filesize)
            ];

        } catch (Exception $e) {
            $this->log("ERRORE backup: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Esegue backup incrementale (solo dati modificati)
     */
    public function backupIncremental($compress = true) {
        $this->log("Inizio backup incrementale");

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_incremental_{$this->dbName}_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        try {
            // Trova ultimo backup
            $lastBackup = $this->getLastBackupTime();

            // Tabelle con timestamp
            $tablesWithTimestamp = [
                'utenti' => 'updated_at',
                'servizi_attivi' => 'updated_at',
                'fatture' => 'updated_at',
                'audit_log' => 'created_at',
                'auth_2fa_log' => 'created_at'
            ];

            $sql = "-- Backup Incrementale da {$lastBackup}\n\n";

            foreach ($tablesWithTimestamp as $table => $timestampCol) {
                // Esporta solo record modificati
                $stmt = $this->pdo->prepare("
                    SELECT * FROM `{$table}`
                    WHERE `{$timestampCol}` > :last_backup
                ");
                $stmt->execute(['last_backup' => $lastBackup]);

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($rows) > 0) {
                    $sql .= "-- Tabella: {$table} (" . count($rows) . " record)\n";
                    $sql .= "REPLACE INTO `{$table}` VALUES\n";

                    foreach ($rows as $i => $row) {
                        $values = array_map(function($v) {
                            return $v === null ? 'NULL' : $this->pdo->quote($v);
                        }, array_values($row));

                        $sql .= '(' . implode(',', $values) . ')';
                        $sql .= ($i < count($rows) - 1) ? ",\n" : ";\n\n";
                    }
                }
            }

            file_put_contents($filepath, $sql);

            $filesize = filesize($filepath);
            $this->log("Backup incrementale creato: $filename (" . $this->formatBytes($filesize) . ")");

            if ($compress) {
                $filepath = $this->compress($filepath);
                $filesize = filesize($filepath);
            }

            $this->saveMetadata($filepath, 'incremental', $filesize);

            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => basename($filepath),
                'size' => $filesize
            ];

        } catch (Exception $e) {
            $this->log("ERRORE backup incrementale: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup specifiche tabelle
     */
    public function backupTables(array $tables, $compress = true) {
        $this->log("Backup tabelle: " . implode(', ', $tables));

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_tables_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        $tablesList = implode(' ', $tables);
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s %s > %s 2>&1',
            DB_USER,
            DB_PASS,
            DB_HOST,
            $this->dbName,
            $tablesList,
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("mysqldump fallito: " . implode("\n", $output));
        }

        $filesize = filesize($filepath);

        if ($compress) {
            $filepath = $this->compress($filepath);
            $filesize = filesize($filepath);
        }

        $this->saveMetadata($filepath, 'tables', $filesize, ['tables' => $tables]);

        return [
            'success' => true,
            'filepath' => $filepath,
            'size' => $filesize
        ];
    }

    /**
     * Esegue mysqldump
     */
    private function mysqldump($filepath) {
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --single-transaction --routines --triggers --events %s > %s 2>&1',
            DB_USER,
            DB_PASS,
            DB_HOST,
            $this->dbName,
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("mysqldump fallito: " . implode("\n", $output));
        }

        if (!file_exists($filepath) || filesize($filepath) === 0) {
            throw new Exception("File backup vuoto o non creato");
        }
    }

    /**
     * Comprimi file con gzip
     */
    private function compress($filepath) {
        $this->log("Compressione file: " . basename($filepath));

        $gzFilepath = $filepath . '.gz';

        $fp = fopen($filepath, 'rb');
        $gzFp = gzopen($gzFilepath, 'wb9'); // Massima compressione

        while (!feof($fp)) {
            gzwrite($gzFp, fread($fp, 1024 * 512));
        }

        fclose($fp);
        gzclose($gzFp);

        // Rimuovi originale
        unlink($filepath);

        return $gzFilepath;
    }

    /**
     * Cripta file con OpenSSL
     */
    private function encrypt($filepath) {
        $this->log("Crittografia file: " . basename($filepath));

        $encFilepath = $filepath . '.enc';

        // Usa password da config o genera
        $password = defined('BACKUP_ENCRYPTION_KEY')
            ? BACKUP_ENCRYPTION_KEY
            : bin2hex(random_bytes(32));

        $command = sprintf(
            'openssl enc -aes-256-cbc -salt -in %s -out %s -pass pass:%s',
            escapeshellarg($filepath),
            escapeshellarg($encFilepath),
            escapeshellarg($password)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Crittografia fallita");
        }

        // Rimuovi originale
        unlink($filepath);

        // Salva password in file separato (SICURO!)
        file_put_contents(
            $encFilepath . '.key',
            $password,
            LOCK_EX
        );
        chmod($encFilepath . '.key', 0600);

        return $encFilepath;
    }

    /**
     * Salva metadata backup
     */
    private function saveMetadata($filepath, $type, $size, $extra = []) {
        $metadata = [
            'filename' => basename($filepath),
            'filepath' => $filepath,
            'type' => $type,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'created_at' => date('Y-m-d H:i:s'),
            'database' => $this->dbName,
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
     * Ottieni timestamp ultimo backup
     */
    private function getLastBackupTime() {
        $metadataFile = $this->backupDir . '/metadata.json';

        if (!file_exists($metadataFile)) {
            return '1970-01-01 00:00:00';
        }

        $metadata = json_decode(file_get_contents($metadataFile), true);

        if (empty($metadata)) {
            return '1970-01-01 00:00:00';
        }

        // Ultimo backup completo
        $fullBackups = array_filter($metadata, fn($m) => $m['type'] === 'full');

        if (empty($fullBackups)) {
            return '1970-01-01 00:00:00';
        }

        return end($fullBackups)['created_at'];
    }

    /**
     * Pulizia backup vecchi (retention policy)
     */
    public function cleanup() {
        $this->log("Inizio pulizia backup vecchi");

        $files = glob($this->backupDir . '/backup_*');
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            $age = $now - filemtime($file);
            $days = floor($age / 86400);

            // Determina tipo backup dal nome
            $isDaily = strpos($file, '_full_') !== false;
            $isWeekly = date('w', filemtime($file)) == 0; // Domenica
            $isMonthly = date('j', filemtime($file)) == 1; // Primo del mese

            $shouldDelete = false;

            if ($isMonthly && $days > (RETENTION_MONTHLY * 30)) {
                $shouldDelete = true;
            } elseif ($isWeekly && $days > (RETENTION_WEEKLY * 7)) {
                $shouldDelete = true;
            } elseif ($isDaily && $days > RETENTION_DAILY) {
                $shouldDelete = true;
            }

            if ($shouldDelete) {
                unlink($file);
                $deleted++;
                $this->log("Eliminato backup vecchio: " . basename($file));

                // Elimina anche .key se esiste
                if (file_exists($file . '.key')) {
                    unlink($file . '.key');
                }
            }
        }

        $this->log("Pulizia completata: $deleted file eliminati");

        return $deleted;
    }

    /**
     * Verifica integrità backup
     */
    public function verify($filepath) {
        $this->log("Verifica integrità: " . basename($filepath));

        // Se compresso, decomprimi temporaneamente
        if (substr($filepath, -3) === '.gz') {
            $tempFile = sys_get_temp_dir() . '/' . basename($filepath, '.gz');

            $gzFp = gzopen($filepath, 'rb');
            $fp = fopen($tempFile, 'wb');

            while (!gzeof($gzFp)) {
                fwrite($fp, gzread($gzFp, 4096));
            }

            gzclose($gzFp);
            fclose($fp);

            $filepath = $tempFile;
        }

        // Verifica SQL syntax
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s -e "source %s" 2>&1',
            DB_USER,
            DB_PASS,
            DB_HOST,
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        // Rimuovi temp file
        if (isset($tempFile) && file_exists($tempFile)) {
            unlink($tempFile);
        }

        if ($returnCode === 0) {
            $this->log("Verifica OK: " . basename($filepath));
            return true;
        } else {
            $this->log("Verifica FALLITA: " . basename($filepath), 'ERROR');
            return false;
        }
    }

    /**
     * Ripristina backup
     */
    public function restore($filepath, $dryRun = false) {
        $this->log("Ripristino backup: " . basename($filepath));

        if (!file_exists($filepath)) {
            throw new Exception("File backup non trovato");
        }

        // Se compresso, decomprimi
        if (substr($filepath, -3) === '.gz') {
            $this->log("Decompressione backup...");
            $tempFile = sys_get_temp_dir() . '/' . basename($filepath, '.gz');

            $gzFp = gzopen($filepath, 'rb');
            $fp = fopen($tempFile, 'wb');

            while (!gzeof($gzFp)) {
                fwrite($fp, gzread($gzFp, 4096));
            }

            gzclose($gzFp);
            fclose($fp);

            $filepath = $tempFile;
        }

        // Se criptato, decripta
        if (substr($filepath, -4) === '.enc') {
            $this->log("Decrittazione backup...");

            $keyFile = $filepath . '.key';
            if (!file_exists($keyFile)) {
                throw new Exception("File chiave non trovato");
            }

            $password = file_get_contents($keyFile);
            $decFilepath = sys_get_temp_dir() . '/' . basename($filepath, '.enc');

            $command = sprintf(
                'openssl enc -aes-256-cbc -d -in %s -out %s -pass pass:%s',
                escapeshellarg($filepath),
                escapeshellarg($decFilepath),
                escapeshellarg($password)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Decrittazione fallita");
            }

            $filepath = $decFilepath;
        }

        if ($dryRun) {
            $this->log("Dry run - ripristino simulato");
            return ['success' => true, 'dry_run' => true];
        }

        // Esegui ripristino
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s 2>&1',
            DB_USER,
            DB_PASS,
            DB_HOST,
            $this->dbName,
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        // Rimuovi file temporanei
        if (isset($tempFile) && file_exists($tempFile)) {
            unlink($tempFile);
        }
        if (isset($decFilepath) && file_exists($decFilepath)) {
            unlink($decFilepath);
        }

        if ($returnCode === 0) {
            $this->log("Ripristino completato con successo");
            return ['success' => true];
        } else {
            throw new Exception("Ripristino fallito: " . implode("\n", $output));
        }
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

        // Ordina per data (più recente prima)
        usort($metadata, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $metadata;
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
     * Formatta byte in formato leggibile
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
    $backup = new DatabaseBackup($pdo, BACKUP_DIR);

    switch ($backupType) {
        case 'full':
            $result = $backup->backupFull($compress, $encrypt);
            break;

        case 'incremental':
            $result = $backup->backupIncremental($compress);
            break;

        case 'tables':
            if (!$specificTables) {
                throw new Exception("Specificare tabelle con --tables=tabella1,tabella2");
            }
            $result = $backup->backupTables($specificTables, $compress);
            break;

        case 'cleanup':
            $deleted = $backup->cleanup();
            $result = ['success' => true, 'deleted' => $deleted];
            break;

        case 'list':
            $backups = $backup->listBackups();
            echo "\n=== BACKUP DISPONIBILI ===\n\n";
            foreach ($backups as $b) {
                echo "{$b['created_at']} - {$b['type']} - {$b['size_formatted']} - {$b['filename']}\n";
            }
            exit(0);

        default:
            throw new Exception("Tipo backup non valido: $backupType");
    }

    if ($result['success']) {
        echo "\n✅ Backup completato con successo!\n";
        if (isset($result['filename'])) {
            echo "File: {$result['filename']}\n";
        }
        if (isset($result['size_formatted'])) {
            echo "Dimensione: {$result['size_formatted']}\n";
        }

        // Cleanup automatico dopo backup
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
