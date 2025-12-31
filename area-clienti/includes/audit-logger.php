<?php
/**
 * Audit Logger - Sistema di tracciamento completo azioni
 * Log automatico di tutte le operazioni critiche
 */

class AuditLogger {
    private $pdo;
    private $currentUserId;
    private $currentUserEmail;
    private $currentUserRole;

    public function __construct($pdo) {
        $this->pdo = $pdo;

        // Carica info utente corrente dalla sessione
        if (isset($_SESSION['cliente_id'])) {
            $this->loadCurrentUser($_SESSION['cliente_id']);
        }
    }

    /**
     * Carica info utente corrente
     */
    private function loadCurrentUser($userId) {
        $stmt = $this->pdo->prepare('
            SELECT id, email, ruolo FROM utenti WHERE id = :id
        ');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if ($user) {
            $this->currentUserId = $user['id'];
            $this->currentUserEmail = $user['email'];
            $this->currentUserRole = $user['ruolo'];

            // Imposta variabile MySQL per trigger
            $this->pdo->exec("SET @current_admin_id = {$this->currentUserId}");
        }
    }

    /**
     * Log azione
     */
    public function log(array $dati) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO audit_log (
                    user_id,
                    user_email,
                    user_ruolo,
                    user_ip,
                    user_agent,
                    azione,
                    entita,
                    entita_id,
                    descrizione,
                    dati_prima,
                    dati_dopo,
                    metadata,
                    request_url,
                    request_method,
                    livello,
                    categoria,
                    successo,
                    richiede_review
                ) VALUES (
                    :user_id,
                    :user_email,
                    :user_ruolo,
                    :user_ip,
                    :user_agent,
                    :azione,
                    :entita,
                    :entita_id,
                    :descrizione,
                    :dati_prima,
                    :dati_dopo,
                    :metadata,
                    :request_url,
                    :request_method,
                    :livello,
                    :categoria,
                    :successo,
                    :richiede_review
                )
            ');

            $stmt->execute([
                'user_id' => $this->currentUserId,
                'user_email' => $this->currentUserEmail,
                'user_ruolo' => $this->currentUserRole,
                'user_ip' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'azione' => $dati['azione'],
                'entita' => $dati['entita'],
                'entita_id' => $dati['entita_id'] ?? null,
                'descrizione' => $dati['descrizione'] ?? null,
                'dati_prima' => isset($dati['dati_prima']) ? json_encode($dati['dati_prima']) : null,
                'dati_dopo' => isset($dati['dati_dopo']) ? json_encode($dati['dati_dopo']) : null,
                'metadata' => isset($dati['metadata']) ? json_encode($dati['metadata']) : null,
                'request_url' => $_SERVER['REQUEST_URI'] ?? null,
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'livello' => $dati['livello'] ?? 'info',
                'categoria' => $dati['categoria'] ?? 'altro',
                'successo' => $dati['successo'] ?? true,
                'richiede_review' => $dati['richiede_review'] ?? false
            ]);

            return $this->pdo->lastInsertId();

        } catch (Exception $e) {
            error_log("Errore audit log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ottieni IP client
     */
    private function getClientIP() {
        $ipAddress = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        // Valida IPv4/IPv6
        if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $ipAddress;
        }

        return 'unknown';
    }

    /**
     * Log login
     */
    public function logLogin($userId, $successo = true, $motivo = null) {
        return $this->log([
            'azione' => $successo ? 'login_success' : 'login_failed',
            'entita' => 'auth',
            'entita_id' => $userId,
            'descrizione' => $successo
                ? "Login effettuato con successo"
                : "Tentativo login fallito: $motivo",
            'livello' => $successo ? 'info' : 'warning',
            'categoria' => 'auth',
            'successo' => $successo,
            'richiede_review' => !$successo,
            'metadata' => [
                'motivo_fallimento' => $motivo,
                'ip' => $this->getClientIP()
            ]
        ]);
    }

    /**
     * Log logout
     */
    public function logLogout() {
        return $this->log([
            'azione' => 'logout',
            'entita' => 'auth',
            'descrizione' => 'Logout effettuato',
            'livello' => 'info',
            'categoria' => 'auth',
            'successo' => true
        ]);
    }

    /**
     * Log creazione entità
     */
    public function logCreate($entita, $entitaId, $dati, $descrizione = null) {
        return $this->log([
            'azione' => 'create',
            'entita' => $entita,
            'entita_id' => $entitaId,
            'descrizione' => $descrizione ?? "Creato nuovo $entita #$entitaId",
            'dati_dopo' => $dati,
            'livello' => 'info',
            'categoria' => $this->getCategoriaPerEntita($entita),
            'successo' => true
        ]);
    }

    /**
     * Log modifica entità
     */
    public function logUpdate($entita, $entitaId, $datiPrima, $datiDopo, $descrizione = null) {
        // Trova differenze
        $cambiamenti = $this->findDifferences($datiPrima, $datiDopo);

        return $this->log([
            'azione' => 'update',
            'entita' => $entita,
            'entita_id' => $entitaId,
            'descrizione' => $descrizione ?? $this->generateUpdateDescription($entita, $cambiamenti),
            'dati_prima' => $datiPrima,
            'dati_dopo' => $datiDopo,
            'livello' => 'info',
            'categoria' => $this->getCategoriaPerEntita($entita),
            'successo' => true,
            'metadata' => ['cambiamenti' => $cambiamenti]
        ]);
    }

    /**
     * Log eliminazione entità
     */
    public function logDelete($entita, $entitaId, $dati = null, $descrizione = null) {
        return $this->log([
            'azione' => 'delete',
            'entita' => $entita,
            'entita_id' => $entitaId,
            'descrizione' => $descrizione ?? "Eliminato $entita #$entitaId",
            'dati_prima' => $dati,
            'livello' => 'warning',
            'categoria' => $this->getCategoriaPerEntita($entita),
            'successo' => true,
            'richiede_review' => true
        ]);
    }

    /**
     * Log visualizzazione dati sensibili
     */
    public function logView($entita, $entitaId, $descrizione = null) {
        return $this->log([
            'azione' => 'view',
            'entita' => $entita,
            'entita_id' => $entitaId,
            'descrizione' => $descrizione ?? "Visualizzato $entita #$entitaId",
            'livello' => 'info',
            'categoria' => $this->getCategoriaPerEntita($entita),
            'successo' => true
        ]);
    }

    /**
     * Log export dati
     */
    public function logExport($entita, $formato, $filtri = null, $conteggioRecord = null) {
        return $this->log([
            'azione' => 'export',
            'entita' => $entita,
            'descrizione' => "Export dati $entita in formato $formato" .
                ($conteggioRecord ? " ($conteggioRecord record)" : ''),
            'livello' => 'warning',
            'categoria' => 'altro',
            'successo' => true,
            'richiede_review' => true,
            'metadata' => [
                'formato' => $formato,
                'filtri' => $filtri,
                'count' => $conteggioRecord
            ]
        ]);
    }

    /**
     * Log invio email/SMS
     */
    public function logCommunication($tipo, $destinatario, $oggetto, $successo = true) {
        return $this->log([
            'azione' => "send_$tipo",
            'entita' => $tipo,
            'descrizione' => "$tipo inviato a $destinatario: $oggetto",
            'livello' => $successo ? 'info' : 'error',
            'categoria' => $tipo,
            'successo' => $successo,
            'metadata' => [
                'destinatario' => $destinatario,
                'oggetto' => $oggetto
            ]
        ]);
    }

    /**
     * Log errore
     */
    public function logError($descrizione, $entita = null, $dettagli = null, $critico = false) {
        return $this->log([
            'azione' => 'error',
            'entita' => $entita ?? 'sistema',
            'descrizione' => $descrizione,
            'livello' => $critico ? 'critical' : 'error',
            'categoria' => 'altro',
            'successo' => false,
            'richiede_review' => $critico,
            'metadata' => $dettagli
        ]);
    }

    /**
     * Log cambio configurazione
     */
    public function logConfigChange($chiave, $valorePrecedente, $valoreNuovo) {
        return $this->log([
            'azione' => 'config_change',
            'entita' => 'config',
            'descrizione' => "Configurazione '$chiave' modificata",
            'dati_prima' => ['valore' => $valorePrecedente],
            'dati_dopo' => ['valore' => $valoreNuovo],
            'livello' => 'warning',
            'categoria' => 'config',
            'successo' => true,
            'richiede_review' => true
        ]);
    }

    /**
     * Trova differenze tra array
     */
    private function findDifferences($prima, $dopo) {
        $diff = [];

        foreach ($dopo as $key => $value) {
            if (!isset($prima[$key]) || $prima[$key] !== $value) {
                $diff[$key] = [
                    'old' => $prima[$key] ?? null,
                    'new' => $value
                ];
            }
        }

        return $diff;
    }

    /**
     * Genera descrizione update automatica
     */
    private function generateUpdateDescription($entita, $cambiamenti) {
        if (empty($cambiamenti)) {
            return "Modificato $entita (nessun cambiamento rilevato)";
        }

        $fields = array_keys($cambiamenti);
        $firstField = $fields[0];

        if (count($fields) === 1) {
            return "Modificato $entita: $firstField cambiato";
        }

        return "Modificato $entita: " . implode(', ', array_slice($fields, 0, 3)) .
            (count($fields) > 3 ? ' e altri ' . (count($fields) - 3) . ' campi' : '');
    }

    /**
     * Mappa entità -> categoria
     */
    private function getCategoriaPerEntita($entita) {
        $map = [
            'cliente' => 'cliente',
            'utente' => 'cliente',
            'servizio' => 'servizio',
            'fattura' => 'fattura',
            'pagamento' => 'pagamento',
            'training' => 'training',
            'richiesta_addestramento' => 'training',
            'email' => 'email',
            'sms' => 'sms',
            'admin' => 'team',
            'ruolo' => 'team',
            'auth' => 'auth',
            'config' => 'config'
        ];

        return $map[$entita] ?? 'altro';
    }

    /**
     * Recupera log con filtri
     */
    public function getLogs($filtri = []) {
        $where = [];
        $params = [];

        if (isset($filtri['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $filtri['user_id'];
        }

        if (isset($filtri['azione'])) {
            $where[] = 'azione = :azione';
            $params['azione'] = $filtri['azione'];
        }

        if (isset($filtri['entita'])) {
            $where[] = 'entita = :entita';
            $params['entita'] = $filtri['entita'];
        }

        if (isset($filtri['categoria'])) {
            $where[] = 'categoria = :categoria';
            $params['categoria'] = $filtri['categoria'];
        }

        if (isset($filtri['livello'])) {
            $where[] = 'livello = :livello';
            $params['livello'] = $filtri['livello'];
        }

        if (isset($filtri['data_da'])) {
            $where[] = 'created_at >= :data_da';
            $params['data_da'] = $filtri['data_da'];
        }

        if (isset($filtri['data_a'])) {
            $where[] = 'created_at <= :data_a';
            $params['data_a'] = $filtri['data_a'];
        }

        if (isset($filtri['richiede_review'])) {
            $where[] = 'richiede_review = :richiede_review';
            $params['richiede_review'] = $filtri['richiede_review'];
        }

        $whereSql = empty($where) ? '1=1' : implode(' AND ', $where);
        $limit = $filtri['limit'] ?? 100;
        $offset = $filtri['offset'] ?? 0;

        $stmt = $this->pdo->prepare("
            SELECT * FROM v_audit_log_dettagliato
            WHERE $whereSql
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Conta log
     */
    public function countLogs($filtri = []) {
        $where = [];
        $params = [];

        if (isset($filtri['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $filtri['user_id'];
        }

        if (isset($filtri['categoria'])) {
            $where[] = 'categoria = :categoria';
            $params['categoria'] = $filtri['categoria'];
        }

        $whereSql = empty($where) ? '1=1' : implode(' AND ', $where);

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM audit_log WHERE $whereSql
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Statistiche audit
     */
    public function getStatistiche($userId = null, $giorni = 30) {
        $where = 'created_at >= DATE_SUB(NOW(), INTERVAL :giorni DAY)';
        $params = ['giorni' => $giorni];

        if ($userId) {
            $where .= ' AND user_id = :user_id';
            $params['user_id'] = $userId;
        }

        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS totale_azioni,
                SUM(CASE WHEN successo = TRUE THEN 1 ELSE 0 END) AS azioni_successo,
                SUM(CASE WHEN successo = FALSE THEN 1 ELSE 0 END) AS azioni_fallite,
                SUM(CASE WHEN livello = 'critical' THEN 1 ELSE 0 END) AS critiche,
                SUM(CASE WHEN richiede_review = TRUE THEN 1 ELSE 0 END) AS da_revisionare,
                COUNT(DISTINCT DATE(created_at)) AS giorni_attivi,
                COUNT(DISTINCT user_id) AS utenti_attivi
            FROM audit_log
            WHERE $where
        ");

        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Export audit log
     */
    public function export($formato = 'csv', $filtri = []) {
        $logs = $this->getLogs(array_merge($filtri, ['limit' => 10000]));

        switch ($formato) {
            case 'csv':
                return $this->exportCSV($logs);
            case 'json':
                return $this->exportJSON($logs);
            default:
                throw new Exception("Formato export non supportato: $formato");
        }
    }

    /**
     * Export CSV
     */
    private function exportCSV($logs) {
        $output = fopen('php://temp', 'r+');

        // Header
        fputcsv($output, [
            'ID', 'Data/Ora', 'Utente', 'Email', 'Ruolo', 'Azione',
            'Entità', 'ID Entità', 'Descrizione', 'IP', 'Livello',
            'Categoria', 'Successo'
        ]);

        // Dati
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['user_nome'] . ' ' . $log['user_cognome'],
                $log['user_email'],
                $log['user_ruolo_nome'],
                $log['azione'],
                $log['entita'],
                $log['entita_id'],
                $log['descrizione'],
                $log['user_ip'],
                $log['livello'],
                $log['categoria'],
                $log['successo'] ? 'Sì' : 'No'
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export JSON
     */
    private function exportJSON($logs) {
        return json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Helper globale per audit
 */
function auditLog($pdo) {
    return new AuditLogger($pdo);
}
