<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';
require '../includes/audit-logger.php';
require '../includes/email-manager.php';

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

            $baseUrl = Config::get('APP_URL', '');
            if (empty($baseUrl)) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $baseUrl = $protocol . '://' . $host;
            }

            $linkInvito = rtrim($baseUrl, '/') . '/area-clienti/accept-invite.php?token=' . $token;

            $subject = 'Invito Area Admin Finch-AI';
            $destinatarioNome = trim($nome . ' ' . $cognome);
            $messagePersonal = $messaggio ? nl2br(htmlspecialchars($messaggio)) : null;

            $bodyHtml = '<!DOCTYPE html>'
                . '<html lang="it">'
                . '<head><meta charset="UTF-8"></head>'
                . '<body style="font-family: Arial, sans-serif; color: #111827;">'
                . '<p>Ciao ' . htmlspecialchars($destinatarioNome) . ',</p>'
                . '<p>Sei stato invitato ad accedere all&#39;Area Admin di Finch-AI.</p>'
                . '<p><a href="' . htmlspecialchars($linkInvito) . '" style="display:inline-block;padding:10px 16px;background:#7c3aed;color:#fff;text-decoration:none;border-radius:6px;">Accetta invito</a></p>'
                . '<p>Questo invito scade il ' . date('d/m/Y', strtotime($expiresAt)) . '.</p>'
                . ($messagePersonal ? '<p>Messaggio personale:</p><blockquote style="margin:0 0 16px 0;padding:12px;border-left:3px solid #e5e7eb;background:#f9fafb;">' . $messagePersonal . '</blockquote>' : '')
                . '<p>Se non ti aspettavi questa email, puoi ignorarla.</p>'
                . '</body></html>';

            $bodyText = "Ciao {$destinatarioNome},

"
                . "Sei stato invitato ad accedere all'Area Admin di Finch-AI.
"
                . "Accetta invito: {$linkInvito}

"
                . "L'invito scade il " . date('d/m/Y', strtotime($expiresAt)) . ".
"
                . ($messaggio ? "
Messaggio personale:
{$messaggio}
" : '')
                . "
Se non ti aspettavi questa email, puoi ignorarla.";

            $emailManager = new EmailManager($pdo);
            $emailQueueId = $emailManager->addToQueue([
                'template_id' => null,
                'destinatario_email' => $email,
                'destinatario_nome' => $destinatarioNome,
                'oggetto' => $subject,
                'corpo_html' => $bodyHtml,
                'corpo_testo' => $bodyText,
                'mittente_email' => 'noreply@finch-ai.it',
                'mittente_nome' => 'Finch-AI',
                'reply_to' => null,
                'cliente_id' => null,
                'fattura_id' => null,
                'variabili' => json_encode([
                    'invito_link' => $linkInvito,
                    'scadenza' => $expiresAt
                ]),
                'priorita' => 'alta',
                'data_pianificazione' => null
            ]);

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


        case 'update':
            // Aggiorna dati admin
            $rbac->requirePermission('can_edit_admin');

            $adminId = (int)($_POST['admin_id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $cognome = trim($_POST['cognome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $ruoloId = (int)($_POST['ruolo_id'] ?? 0);

            if (!$adminId || $nome == '' || $cognome == '' || $email == '') {
                throw new Exception('Dati mancanti');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email non valida');
            }

            if (!$rbac->canManageAdmin($adminId)) {
                throw new PermissionDeniedException('Non hai permesso di gestire questo admin');
            }

            $stmt = $pdo->prepare('SELECT id, nome, cognome, email, admin_ruolo_id, is_super_admin FROM utenti WHERE id = :id AND ruolo = "admin"');
            $stmt->execute(['id' => $adminId]);
            $adminData = $stmt->fetch();

            if (!$adminData) {
                throw new Exception('Admin non trovato');
            }

            $stmt = $pdo->prepare('SELECT id FROM utenti WHERE email = :email AND id != :id LIMIT 1');
            $stmt->execute(['email' => $email, 'id' => $adminId]);
            if ($stmt->fetch()) {
                throw new Exception('Email già registrata');
            }

            $stmt = $pdo->prepare('UPDATE utenti SET nome = :nome, cognome = :cognome, email = :email WHERE id = :id');
            $stmt->execute([
                'nome' => $nome,
                'cognome' => $cognome,
                'email' => $email,
                'id' => $adminId
            ]);

            $newData = [
                'nome' => $nome,
                'cognome' => $cognome,
                'email' => $email,
                'ruolo_id' => $adminData['admin_ruolo_id']
            ];

            if ($ruoloId && $ruoloId != (int)$adminData['admin_ruolo_id']) {
                if (!$rbac->can('can_assign_roles')) {
                    throw new PermissionDeniedException('Permesso negato: can_assign_roles richiesto');
                }
                $rbac->assegnaRuolo($adminId, $ruoloId);
                $newData['ruolo_id'] = $ruoloId;
            }

            $audit->logUpdate('admin', $adminId, [
                'nome' => $adminData['nome'],
                'cognome' => $adminData['cognome'],
                'email' => $adminData['email'],
                'ruolo_id' => $adminData['admin_ruolo_id']
            ], $newData, 'Aggiornamento dati admin');

            echo json_encode([
                'success' => true,
                'message' => 'Admin aggiornato'
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
