<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';

// Verifica permesso
$rbac = requirePermission($pdo, 'can_view_team');

// Recupera team
$team = $rbac->getTeamAdmin();
$ruoli = $rbac->getRuoli();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Team Admin - Finch-AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 28px;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #6b7280;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-card .label {
            color: #6b7280;
            font-size: 14px;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 18px;
            font-weight: 700;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 12px 8px 36px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 300px;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
        }

        th {
            padding: 12px 24px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 24px;
            border-top: 1px solid #f3f4f6;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-details .name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 2px;
        }

        .admin-details .email {
            font-size: 13px;
            color: #6b7280;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-super {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-admin {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-manager {
            background: #e0e7ff;
            color: #3730a3;
        }

        .badge-supporto {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-contabile {
            background: #fce7f3;
            color: #831843;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .status-attivo {
            background: #10b981;
        }

        .status-inattivo {
            background: #ef4444;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-edit {
            background: #dbeafe;
            color: #1e40af;
        }

        .action-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-toggle {
            background: #fef3c7;
            color: #92400e;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            background: #f3f4f6;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8b5cf6;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-users"></i>
                Gestione Team Admin
            </h1>
            <?php if ($rbac->can('can_invite_admin')): ?>
            <button class="btn btn-primary" onclick="showInviteModal()">
                <i class="fas fa-user-plus"></i>
                Invita Admin
            </button>
            <?php endif; ?>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon" style="background: #dbeafe; color: #1e40af;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="value"><?php echo count($team); ?></div>
                <div class="label">Totale Admin</div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background: #d1fae5; color: #059669;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="value"><?php echo count(array_filter($team, fn($a) => $a['can_login'])); ?></div>
                <div class="label">Attivi</div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background: #fef3c7; color: #92400e;">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="value"><?php echo count(array_filter($team, fn($a) => $a['is_super_admin'])); ?></div>
                <div class="label">Super Admin</div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background: #e0e7ff; color: #5b21b6;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="value"><?php echo count(array_filter($team, fn($a) => $a['sessioni_attive'] > 0)); ?></div>
                <div class="label">Online Ora</div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>Team Amministratori</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cerca admin..." onkeyup="filterTable()">
                </div>
            </div>

            <?php if (empty($team)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Nessun amministratore trovato</p>
                </div>
            <?php else: ?>
                <table id="teamTable">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Ruolo</th>
                            <th>Livello</th>
                            <th>Stato</th>
                            <th>Ultimo Accesso</th>
                            <th>2FA</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($team as $admin): ?>
                        <tr data-id="<?php echo $admin['id']; ?>">
                            <td>
                                <div class="admin-info">
                                    <div class="admin-avatar">
                                        <?php echo strtoupper(substr($admin['nome'], 0, 1) . substr($admin['cognome'], 0, 1)); ?>
                                    </div>
                                    <div class="admin-details">
                                        <div class="name">
                                            <?php echo htmlspecialchars($admin['nome'] . ' ' . $admin['cognome']); ?>
                                            <?php if ($admin['is_super_admin']): ?>
                                                <i class="fas fa-crown" style="color: #f59e0b; font-size: 12px;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="email"><?php echo htmlspecialchars($admin['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'badge-admin';
                                if ($admin['is_super_admin']) {
                                    $badgeClass = 'badge-super';
                                } elseif ($admin['ruolo_code'] === 'manager') {
                                    $badgeClass = 'badge-manager';
                                } elseif ($admin['ruolo_code'] === 'supporto') {
                                    $badgeClass = 'badge-supporto';
                                } elseif ($admin['ruolo_code'] === 'contabile') {
                                    $badgeClass = 'badge-contabile';
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($admin['ruolo_nome'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <?php for ($i = 0; $i < 4; $i++): ?>
                                        <div style="width: 20px; height: 4px; border-radius: 2px; background: <?php echo $i < ($admin['livello_accesso'] ?? 1) ? '#8b5cf6' : '#e5e7eb'; ?>;"></div>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-dot <?php echo $admin['can_login'] ? 'status-attivo' : 'status-inattivo'; ?>"></span>
                                <?php echo $admin['can_login'] ? 'Attivo' : 'Disattivato'; ?>
                            </td>
                            <td>
                                <?php if ($admin['ultimo_accesso']): ?>
                                    <div style="font-size: 13px;">
                                        <?php echo date('d/m/Y H:i', strtotime($admin['ultimo_accesso'])); ?>
                                    </div>
                                    <?php if ($admin['sessioni_attive'] > 0): ?>
                                        <span style="color: #059669; font-size: 12px;">
                                            <i class="fas fa-circle" style="font-size: 6px;"></i> Online
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">Mai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($admin['auth_2fa_enabled']): ?>
                                    <span style="color: #059669;">
                                        <i class="fas fa-shield-alt"></i> Attivo
                                    </span>
                                <?php else: ?>
                                    <span style="color: #6b7280;">
                                        <i class="fas fa-shield-alt"></i> Off
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($rbac->can('can_edit_admin') && $rbac->canManageAdmin($admin['id'])): ?>
                                        <button class="action-btn action-edit" onclick="editAdmin(this)" title="Modifica" data-id="<?php echo $admin['id']; ?>" data-nome="<?php echo htmlspecialchars($admin['nome'], ENT_QUOTES); ?>" data-cognome="<?php echo htmlspecialchars($admin['cognome'], ENT_QUOTES); ?>" data-email="<?php echo htmlspecialchars($admin['email'], ENT_QUOTES); ?>" data-ruolo="<?php echo (int)$admin['ruolo_id']; ?>" data-super="<?php echo $admin['is_super_admin'] ? '1' : '0'; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if (!$admin['is_super_admin']): ?>
                                            <button class="action-btn action-toggle" onclick="toggleAdmin(<?php echo $admin['id']; ?>, <?php echo $admin['can_login'] ? 'false' : 'true'; ?>)" title="<?php echo $admin['can_login'] ? 'Disattiva' : 'Attiva'; ?>">
                                                <i class="fas fa-<?php echo $admin['can_login'] ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($rbac->can('can_delete_admin') && $rbac->canManageAdmin($admin['id']) && !$admin['is_super_admin']): ?>
                                        <button class="action-btn action-delete" onclick="deleteAdmin(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['nome'] . ' ' . $admin['cognome']); ?>')" title="Elimina">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Invito -->
    <div class="modal" id="inviteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Invita Nuovo Admin</h3>
                <button class="modal-close" onclick="closeModal('inviteModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="inviteForm" onsubmit="sendInvite(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label>Cognome *</label>
                        <input type="text" name="cognome" required>
                    </div>

                    <div class="form-group">
                        <label>Ruolo *</label>
                        <select name="ruolo_id" required>
                            <option value="">Seleziona ruolo...</option>
                            <?php foreach ($ruoli as $ruolo): ?>
                                <?php if (!$ruolo['is_super_admin'] || $rbac->isSuperAdmin()): ?>
                                    <option value="<?php echo $ruolo['id']; ?>">
                                        <?php echo htmlspecialchars($ruolo['display_name']); ?>
                                        (Livello <?php echo $ruolo['livello_accesso']; ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Messaggio Personale (opzionale)</label>
                        <textarea name="messaggio" rows="3" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px;"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('inviteModal')">
                        Annulla
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Invia Invito
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal Modifica -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifica Admin</h3>
                <button class="modal-close" onclick="closeModal('editModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" onsubmit="submitEdit(event)">
                <div class="modal-body">
                    <input type="hidden" name="admin_id" value="">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label>Cognome *</label>
                        <input type="text" name="cognome" required>
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Ruolo</label>
                        <select name="ruolo_id" <?php echo $rbac->can('can_assign_roles') ? '' : 'disabled'; ?>>
                            <option value="">Seleziona ruolo...</option>
                            <?php foreach ($ruoli as $ruolo): ?>
                                <?php if (!$ruolo['is_super_admin'] || $rbac->isSuperAdmin()): ?>
                                    <option value="<?php echo $ruolo['id']; ?>">
                                        <?php echo htmlspecialchars($ruolo['display_name']); ?>
                                        (Livello <?php echo $ruolo['livello_accesso']; ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">
                        Annulla
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Salva modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('teamTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            }
        }

        function showInviteModal() {
            document.getElementById('inviteModal').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        async function sendInvite(e) {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('../api/team-admin.php?action=invite', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Invito inviato con successo!');
                    closeModal('inviteModal');
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        }

        async function toggleAdmin(id, enable) {
            if (!confirm(`Confermi di voler ${enable ? 'attivare' : 'disattivare'} questo admin?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('admin_id', id);
                formData.append('stato', enable ? '1' : '0');

                const response = await fetch('../api/team-admin.php?action=toggle', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        async function deleteAdmin(id, nome) {
            if (!confirm(`Confermi di voler ELIMINARE definitivamente l'admin "${nome}"?\n\nQuesta azione è irreversibile!`)) {
                return;
            }

            if (!confirm('Sei ASSOLUTAMENTE SICURO? Tutti i dati associati verranno eliminati.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('admin_id', id);

                const response = await fetch('../api/team-admin.php?action=delete', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        }

        
        async function submitEdit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('../api/team-admin.php?action=update', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    closeModal('editModal');
                    location.reload();
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        }
        function editAdmin(button) {
            const form = document.getElementById('editForm');
            const modal = document.getElementById('editModal');
            const canAssignRoles = <?php echo $rbac->can('can_assign_roles') ? 'true' : 'false'; ?>;

            form.admin_id.value = button.dataset.id || '';
            form.nome.value = button.dataset.nome || '';
            form.cognome.value = button.dataset.cognome || '';
            form.email.value = button.dataset.email || '';

            const roleSelect = form.querySelector('select[name="ruolo_id"]');
            if (roleSelect) {
                roleSelect.value = button.dataset.ruolo || '';
                if (canAssignRoles) {
                    roleSelect.removeAttribute('disabled');
                } else {
                    roleSelect.setAttribute('disabled', 'disabled');
                }
            }

            modal.classList.add('show');
        }
    </script>
</body>
</html>
