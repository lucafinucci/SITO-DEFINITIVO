<?php
/**
 * RBAC Manager - Role-Based Access Control
 * Gestione permessi granulari per team admin
 */

class RBACManager {
    private $pdo;
    private $currentUser;
    private $permissions;

    public function __construct($pdo, $userId = null) {
        $this->pdo = $pdo;

        if ($userId) {
            $this->loadUser($userId);
        } elseif (isset($_SESSION['cliente_id'])) {
            $this->loadUser($_SESSION['cliente_id']);
        }
    }

    /**
     * Carica utente e permessi
     */
    private function loadUser($userId) {
        $stmt = $this->pdo->prepare('
            SELECT
                u.*,
                ar.*
            FROM utenti u
            LEFT JOIN admin_ruoli ar ON u.admin_ruolo_id = ar.id
            WHERE u.id = :id AND u.ruolo = "admin"
        ');
        $stmt->execute(['id' => $userId]);
        $this->currentUser = $stmt->fetch();

        if ($this->currentUser) {
            $this->loadPermissions();
        }
    }

    /**
     * Carica permessi in array
     */
    private function loadPermissions() {
        if (!$this->currentUser) {
            $this->permissions = [];
            return;
        }

        // Super admin ha tutti i permessi
        if ($this->currentUser['is_super_admin']) {
            $this->permissions = array_fill_keys($this->getAllPermissions(), true);
            return;
        }

        // Carica permessi dal ruolo
        $this->permissions = [];
        foreach ($this->getAllPermissions() as $permission) {
            $this->permissions[$permission] = (bool)($this->currentUser[$permission] ?? false);
        }
    }

    /**
     * Lista tutti i permessi disponibili
     */
    private function getAllPermissions() {
        return [
            'can_view_dashboard',
            'can_view_analytics',
            'can_view_clienti',
            'can_edit_clienti',
            'can_delete_clienti',
            'can_impersonate_clienti',
            'can_view_servizi',
            'can_edit_servizi',
            'can_activate_servizi',
            'can_deactivate_servizi',
            'can_view_fatture',
            'can_create_fatture',
            'can_edit_fatture',
            'can_delete_fatture',
            'can_mark_paid',
            'can_view_pagamenti',
            'can_process_pagamenti',
            'can_refund',
            'can_view_training',
            'can_approve_training',
            'can_reject_training',
            'can_send_emails',
            'can_send_sms',
            'can_broadcast',
            'can_view_settings',
            'can_edit_settings',
            'can_manage_templates',
            'can_view_team',
            'can_invite_admin',
            'can_edit_admin',
            'can_delete_admin',
            'can_assign_roles',
            'can_view_audit_log',
            'can_export_data'
        ];
    }

    /**
     * Verifica se l'utente ha un permesso
     */
    public function can($permission) {
        if (!$this->currentUser) {
            return false;
        }

        // Super admin può tutto
        if ($this->currentUser['is_super_admin']) {
            return true;
        }

        return $this->permissions[$permission] ?? false;
    }

    /**
     * Verifica permesso o lancia eccezione
     */
    public function requirePermission($permission, $message = null) {
        if (!$this->can($permission)) {
            $message = $message ?? "Permesso negato: $permission richiesto";
            throw new PermissionDeniedException($message);
        }
    }

    /**
     * Verifica se utente ha ALMENO UNO dei permessi
     */
    public function canAny(array $permissions) {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se utente ha TUTTI i permessi
     */
    public function canAll(array $permissions) {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Ottieni info utente corrente
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * Ottieni tutti i permessi utente
     */
    public function getAllUserPermissions() {
        return $this->permissions;
    }

    /**
     * Verifica se è super admin
     */
    public function isSuperAdmin() {
        return $this->currentUser && $this->currentUser['is_super_admin'];
    }

    /**
     * Ottieni livello accesso (1-4)
     */
    public function getAccessLevel() {
        if (!$this->currentUser) {
            return 0;
        }

        if ($this->currentUser['is_super_admin']) {
            return 4;
        }

        return (int)($this->currentUser['livello_accesso'] ?? 1);
    }

    /**
     * Verifica se può modificare un altro admin
     */
    public function canManageAdmin($targetAdminId) {
        if (!$this->can('can_edit_admin')) {
            return false;
        }

        // Super admin può gestire tutti
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Non può gestire se stesso tramite questa funzione
        if ($this->currentUser['id'] == $targetAdminId) {
            return false;
        }

        // Recupera target admin
        $stmt = $this->pdo->prepare('
            SELECT u.*, ar.livello_accesso
            FROM utenti u
            LEFT JOIN admin_ruoli ar ON u.admin_ruolo_id = ar.id
            WHERE u.id = :id
        ');
        $stmt->execute(['id' => $targetAdminId]);
        $targetAdmin = $stmt->fetch();

        if (!$targetAdmin) {
            return false;
        }

        // Non può gestire super admin
        if ($targetAdmin['is_super_admin']) {
            return false;
        }

        // Può gestire solo admin con livello inferiore
        $myLevel = $this->getAccessLevel();
        $targetLevel = (int)($targetAdmin['livello_accesso'] ?? 1);

        return $myLevel > $targetLevel;
    }

    /**
     * Ottieni tutti i ruoli disponibili
     */
    public function getRuoli($soloAttivi = true) {
        $sql = 'SELECT * FROM admin_ruoli';

        if ($soloAttivi) {
            $sql .= ' WHERE attivo = TRUE';
        }

        $sql .= ' ORDER BY livello_accesso DESC';

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Ottieni ruolo per ID
     */
    public function getRuolo($ruoloId) {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_ruoli WHERE id = :id');
        $stmt->execute(['id' => $ruoloId]);
        return $stmt->fetch();
    }

    /**
     * Assegna ruolo a utente
     */
    public function assegnaRuolo($userId, $ruoloId) {
        // Verifica permesso
        $this->requirePermission('can_assign_roles');

        // Verifica che non stia modificando super admin
        $stmt = $this->pdo->prepare('SELECT is_super_admin FROM utenti WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $target = $stmt->fetch();

        if ($target && $target['is_super_admin'] && !$this->isSuperAdmin()) {
            throw new PermissionDeniedException('Solo super admin può modificare altri super admin');
        }

        // Assegna ruolo
        $stmt = $this->pdo->prepare('
            UPDATE utenti
            SET admin_ruolo_id = :ruolo_id
            WHERE id = :user_id
        ');

        return $stmt->execute([
            'user_id' => $userId,
            'ruolo_id' => $ruoloId
        ]);
    }

    /**
     * Lista team admin
     */
    public function getTeamAdmin($filtri = []) {
        $this->requirePermission('can_view_team');

        $where = ['u.ruolo = "admin"'];
        $params = [];

        if (isset($filtri['ruolo_id'])) {
            $where[] = 'u.admin_ruolo_id = :ruolo_id';
            $params['ruolo_id'] = $filtri['ruolo_id'];
        }

        if (isset($filtri['attivo'])) {
            $where[] = 'u.can_login = :attivo';
            $params['attivo'] = $filtri['attivo'];
        }

        $whereSql = implode(' AND ', $where);

        $stmt = $this->pdo->prepare("
            SELECT * FROM v_admin_team
            WHERE $whereSql
            ORDER BY livello_accesso DESC, data_registrazione DESC
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Disattiva/Riattiva account admin
     */
    public function toggleAdminStatus($adminId, $stato) {
        $this->requirePermission('can_edit_admin');

        // Non può disattivare se stesso
        if ($adminId == $this->currentUser['id']) {
            throw new PermissionDeniedException('Non puoi disattivare il tuo account');
        }

        // Verifica livello
        if (!$this->canManageAdmin($adminId)) {
            throw new PermissionDeniedException('Non hai permesso di gestire questo admin');
        }

        $stmt = $this->pdo->prepare('
            UPDATE utenti
            SET can_login = :stato
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $adminId,
            'stato' => $stato ? 1 : 0
        ]);
    }

    /**
     * Elimina admin
     */
    public function eliminaAdmin($adminId) {
        $this->requirePermission('can_delete_admin');

        // Non può eliminare se stesso
        if ($adminId == $this->currentUser['id']) {
            throw new PermissionDeniedException('Non puoi eliminare il tuo account');
        }

        // Verifica livello
        if (!$this->canManageAdmin($adminId)) {
            throw new PermissionDeniedException('Non hai permesso di eliminare questo admin');
        }

        $stmt = $this->pdo->prepare('DELETE FROM utenti WHERE id = :id AND ruolo = "admin"');
        return $stmt->execute(['id' => $adminId]);
    }
}

/**
 * Eccezione permessi
 */
class PermissionDeniedException extends Exception {
    public function __construct($message = "Permesso negato", $code = 403) {
        parent::__construct($message, $code);
    }
}

/**
 * Helper: Verifica permesso middleware
 */
function requirePermission($pdo, $permission) {
    $rbac = new RBACManager($pdo);

    if (!$rbac->can($permission)) {
        http_response_code(403);
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Permesso negato',
                'required_permission' => $permission
            ]);
        } else {
            header('Location: /area-clienti/dashboard.php?error=permission_denied');
        }
        exit;
    }

    return $rbac;
}

/**
 * Helper: Ottieni RBAC per utente corrente
 */
function getRBAC($pdo) {
    return new RBACManager($pdo);
}
