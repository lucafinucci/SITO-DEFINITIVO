<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/audit-logger.php';

header('Content-Type: application/json');

$rbac = new RBACManager($pdo);
$audit = new AuditLogger($pdo);

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'invite':
            // Invita nuovo admin
            $rbac->requirePermission('can_invite_admin');

            $email = $_POST['email'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $cognome = $_POST['cognome'] ?? '';
            $ruoloId = (int)($_POST['ruolo_id'] ?? 0);
            $messaggio = $_POST['messaggio'] ?? null;

            if (!$email || !$nome || !$cognome || !$ruoloId) {
                throw new Exception('Dati mancanti');
            }

            // Verifica email non già registrata
            $stmt = $pdo->prepare('SELECT id FROM utenti WHERE email = :email');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                throw new Exception('Email già registrata');
            }

            // Genera token univoco
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Crea invito
            $stmt = $pdo->prepare('
                INSERT INTO admin_inviti (
                    email, nome, cognome, ruolo_id, invited_by,
                    token, expires_at, messaggio_personale
                ) VALUES (
                    :email, :nome, :cognome, :ruolo_id, :invited_by,
                    :token, :expires_at, :messaggio
                )
            ');

            $stmt->execute([
                'email' => $email,
                'nome' => $nome,
                'cognome' => $cognome,
                'ruolo_id' => $ruoloId,
                'invited_by' => $_SESSION['cliente_id'],
                'token' => $token,
                'expires_at' => $expiresAt,
                'messaggio' => $messaggio
            ]);

            $inviteId = $pdo->lastInsertId();

            // TODO: Invia email invito
            $linkInvito = "https://finch-ai.it/area-clienti/accept-invite.php?token=$token";

            // Log audit
            $audit->log([
                'azione' => 'invite_admin',
                'entita' => 'admin',
                'descrizione' => "Invitato nuovo admin: $nome $cognome ($email)",
                'categoria' => 'team',
                'livello' => 'warning',
                'richiede_review' => true,
                'metadata' => [
                    'email' => $email,
                    'ruolo_id' => $ruoloId,
                    'token' => $token
                ]
            ]);

            echo json_encode([
                'success' => true,
                'invite_id' => $inviteId,
                'link' => $linkInvito
            ]);
            break;

        case 'toggle':
            // Attiva/Disattiva admin
            $rbac->requirePermission('can_edit_admin');

            $adminId = (int)($_POST['admin_id'] ?? 0);
            $stato = (bool)($_POST['stato'] ?? false);

            if (!$adminId) {
                throw new Exception('ID admin mancante');
            }

            $success = $rbac->toggleAdminStatus($adminId, $stato);

            // Log audit
            $audit->log([
                'azione' => $stato ? 'enable_admin' : 'disable_admin',
                'entita' => 'admin',
                'entita_id' => $adminId,
                'descrizione' => "Admin #$adminId " . ($stato ? 'attivato' : 'disattivato'),
                'categoria' => 'team',
                'livello' => 'warning',
                'richiede_review' => true
            ]);

            echo json_encode([
                'success' => $success,
                'message' => 'Stato aggiornato'
            ]);
            break;

        case 'delete':
            // Elimina admin
            $rbac->requirePermission('can_delete_admin');

            $adminId = (int)($_POST['admin_id'] ?? 0);

            if (!$adminId) {
                throw new Exception('ID admin mancante');
            }

            // Recupera dati prima di eliminare
            $stmt = $pdo->prepare('SELECT * FROM utenti WHERE id = :id');
            $stmt->execute(['id' => $adminId]);
            $adminData = $stmt->fetch();

            $success = $rbac->eliminaAdmin($adminId);

            // Log audit
            $audit->logDelete('admin', $adminId, $adminData,
                "Admin eliminato: {$adminData['nome']} {$adminData['cognome']} ({$adminData['email']})");

            echo json_encode([
                'success' => $success,
                'message' => 'Admin eliminato'
            ]);
            break;

        case 'assign_role':
            // Assegna ruolo
            $rbac->requirePermission('can_assign_roles');

            $adminId = (int)($_POST['admin_id'] ?? 0);
            $ruoloId = (int)($_POST['ruolo_id'] ?? 0);

            if (!$adminId || !$ruoloId) {
                throw new Exception('Dati mancanti');
            }

            // Recupera vecchio ruolo
            $stmt = $pdo->prepare('
                SELECT u.admin_ruolo_id, ar.display_name AS vecchio_ruolo
                FROM utenti u
                LEFT JOIN admin_ruoli ar ON u.admin_ruolo_id = ar.id
                WHERE u.id = :id
            ');
            $stmt->execute(['id' => $adminId]);
            $old = $stmt->fetch();

            // Nuovo ruolo
            $stmt = $pdo->prepare('SELECT display_name FROM admin_ruoli WHERE id = :id');
            $stmt->execute(['id' => $ruoloId]);
            $nuovoRuolo = $stmt->fetch();

            $success = $rbac->assegnaRuolo($adminId, $ruoloId);

            // Log audit
            $audit->logUpdate('admin', $adminId,
                ['ruolo' => $old['vecchio_ruolo']],
                ['ruolo' => $nuovoRuolo['display_name']],
                "Ruolo admin #$adminId cambiato: {$old['vecchio_ruolo']} → {$nuovoRuolo['display_name']}"
            );

            echo json_encode([
                'success' => $success,
                'message' => 'Ruolo assegnato'
            ]);
            break;

        case 'list':
            // Lista team
            $rbac->requirePermission('can_view_team');

            $filtri = [
                'ruolo_id' => $_GET['ruolo_id'] ?? null,
                'attivo' => isset($_GET['attivo']) ? (bool)$_GET['attivo'] : null
            ];

            $team = $rbac->getTeamAdmin(array_filter($filtri));

            echo json_encode([
                'success' => true,
                'team' => $team,
                'count' => count($team)
            ]);
            break;

        default:
            throw new Exception('Azione non valida');
    }

} catch (PermissionDeniedException $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
