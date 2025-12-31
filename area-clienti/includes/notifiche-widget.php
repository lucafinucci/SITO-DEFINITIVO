<!-- Widget Notifiche Smart -->
<style>
    .notifiche-widget {
        position: relative;
        display: inline-block;
    }

    .notifiche-bell {
        position: relative;
        cursor: pointer;
        font-size: 20px;
        padding: 8px;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .notifiche-bell:hover {
        background: rgba(139, 92, 246, 0.1);
    }

    .notifiche-badge {
        position: absolute;
        top: 4px;
        right: 4px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 5px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .notifiche-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        width: 400px;
        max-height: 600px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
    }

    .notifiche-dropdown.show {
        display: flex;
    }

    .notifiche-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }

    .notifiche-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .notifiche-actions {
        display: flex;
        gap: 8px;
    }

    .notifiche-action-btn {
        padding: 4px 8px;
        font-size: 12px;
        background: none;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s;
    }

    .notifiche-action-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .notifiche-list {
        flex: 1;
        overflow-y: auto;
        max-height: 500px;
    }

    .notifica-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        gap: 12px;
    }

    .notifica-item:hover {
        background: #f9fafb;
    }

    .notifica-item.non-letta {
        background: #eff6ff;
    }

    .notifica-icona {
        font-size: 24px;
        flex-shrink: 0;
    }

    .notifica-content {
        flex: 1;
        min-width: 0;
    }

    .notifica-titolo {
        font-weight: 600;
        font-size: 14px;
        color: #111827;
        margin-bottom: 4px;
    }

    .notifica-messaggio {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    .notifica-tempo {
        font-size: 11px;
        color: #9ca3af;
    }

    .notifica-badge-priorita {
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
    }

    .priorita-urgente {
        background: #fee2e2;
        color: #991b1b;
    }

    .priorita-alta {
        background: #fef3c7;
        color: #92400e;
    }

    .notifiche-empty {
        padding: 40px 20px;
        text-align: center;
        color: #9ca3af;
    }

    .notifiche-empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .notifiche-footer {
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
        background: #f9fafb;
    }

    .notifiche-footer a {
        color: #8b5cf6;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .notifiche-footer a:hover {
        text-decoration: underline;
    }

    .notifica-link-azione {
        display: inline-block;
        margin-top: 8px;
        padding: 4px 12px;
        background: #8b5cf6;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        transition: background 0.2s;
    }

    .notifica-link-azione:hover {
        background: #7c3aed;
    }
</style>

<div class="notifiche-widget" id="notifiche-widget">
    <div class="notifiche-bell" onclick="toggleNotifiche()">
        🔔
        <span class="notifiche-badge" id="notifiche-count" style="display: none;">0</span>
    </div>

    <div class="notifiche-dropdown" id="notifiche-dropdown">
        <div class="notifiche-header">
            <h3>Notifiche</h3>
            <div class="notifiche-actions">
                <button class="notifiche-action-btn" onclick="markAllAsRead()" title="Segna tutte come lette">
                    ✓ Tutte lette
                </button>
            </div>
        </div>

        <div class="notifiche-list" id="notifiche-list">
            <div class="notifiche-empty">
                <div class="notifiche-empty-icon">🔔</div>
                <div>Nessuna notifica</div>
            </div>
        </div>

        <div class="notifiche-footer">
            <a href="/area-clienti/admin/notifiche.php">Visualizza tutte</a>
        </div>
    </div>
</div>

<script>
let notificheDropdownOpen = false;
let ultimoIdNotifica = 0;
let pollingInterval = null;

// Toggle dropdown notifiche
function toggleNotifiche() {
    notificheDropdownOpen = !notificheDropdownOpen;
    const dropdown = document.getElementById('notifiche-dropdown');
    dropdown.classList.toggle('show', notificheDropdownOpen);

    if (notificheDropdownOpen) {
        loadNotifiche();
    }
}

// Carica notifiche
async function loadNotifiche() {
    try {
        const response = await fetch('/area-clienti/api/notifiche.php?action=list&limit=20');
        const data = await response.json();

        if (data.success) {
            renderNotifiche(data.notifiche);
            if (data.notifiche.length > 0) {
                ultimoIdNotifica = Math.max(...data.notifiche.map(n => n.id));
            }
        }
    } catch (error) {
        console.error('Errore caricamento notifiche:', error);
    }
}

// Render notifiche
function renderNotifiche(notifiche) {
    const list = document.getElementById('notifiche-list');

    if (notifiche.length === 0) {
        list.innerHTML = `
            <div class="notifiche-empty">
                <div class="notifiche-empty-icon">🔔</div>
                <div>Nessuna notifica</div>
            </div>
        `;
        return;
    }

    list.innerHTML = notifiche.map(n => `
        <div class="notifica-item ${n.letta ? '' : 'non-letta'}" onclick="handleNotificaClick(${n.id}, '${n.link_azione || ''}')">
            <div class="notifica-icona">${n.icona}</div>
            <div class="notifica-content">
                <div class="notifica-titolo">
                    ${escapeHtml(n.titolo)}
                    ${n.priorita === 'urgente' ? '<span class="notifica-badge-priorita priorita-urgente">URGENTE</span>' : ''}
                    ${n.priorita === 'alta' ? '<span class="notifica-badge-priorita priorita-alta">ALTA</span>' : ''}
                </div>
                <div class="notifica-messaggio">${escapeHtml(n.messaggio)}</div>
                <div class="notifica-tempo">${n.tempo_relativo}</div>
                ${n.link_azione ? `<a href="${n.link_azione}" class="notifica-link-azione" onclick="event.stopPropagation()">${n.label_azione || 'Visualizza'}</a>` : ''}
            </div>
        </div>
    `).join('');
}

// Click su notifica
async function handleNotificaClick(id, link) {
    // Segna come letta
    await markAsRead(id);

    // Se ha link, naviga
    if (link) {
        window.location.href = link;
    }
}

// Segna come letta
async function markAsRead(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'mark-read');
        formData.append('id', id);

        await fetch('/area-clienti/api/notifiche.php', {
            method: 'POST',
            body: formData
        });

        // Ricarica count
        updateNotificheCount();

    } catch (error) {
        console.error('Errore segna come letta:', error);
    }
}

// Segna tutte come lette
async function markAllAsRead() {
    if (!confirm('Segnare tutte le notifiche come lette?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'mark-all-read');

        const response = await fetch('/area-clienti/api/notifiche.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            loadNotifiche();
            updateNotificheCount();
        }

    } catch (error) {
        console.error('Errore segna tutte come lette:', error);
    }
}

// Aggiorna contatore
async function updateNotificheCount() {
    try {
        const response = await fetch('/area-clienti/api/notifiche.php?action=count');
        const data = await response.json();

        if (data.success) {
            const badge = document.getElementById('notifiche-count');
            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Errore update count:', error);
    }
}

// Polling per nuove notifiche
async function pollNuoveNotifiche() {
    try {
        const response = await fetch(`/area-clienti/api/notifiche.php?action=poll&ultimo_id=${ultimoIdNotifica}&timeout=15`);
        const data = await response.json();

        if (data.success && data.count > 0) {
            // Nuove notifiche ricevute
            ultimoIdNotifica = Math.max(...data.notifiche.map(n => n.id));

            // Aggiorna count
            updateNotificheCount();

            // Se dropdown aperto, ricarica
            if (notificheDropdownOpen) {
                loadNotifiche();
            }

            // Mostra notifica desktop (opzionale)
            if (Notification.permission === 'granted') {
                data.notifiche.forEach(n => {
                    new Notification(n.titolo, {
                        body: n.messaggio,
                        icon: '/favicon.ico',
                        badge: n.icona
                    });
                });
            }
        }

        // Riavvia polling
        setTimeout(pollNuoveNotifiche, 1000);

    } catch (error) {
        console.error('Errore polling:', error);
        // Riprova dopo 5 secondi in caso di errore
        setTimeout(pollNuoveNotifiche, 5000);
    }
}

// Chiudi dropdown quando si clicca fuori
document.addEventListener('click', function(event) {
    const widget = document.getElementById('notifiche-widget');
    if (widget && !widget.contains(event.target) && notificheDropdownOpen) {
        toggleNotifiche();
    }
});

// Richiedi permesso notifiche desktop
if (Notification.permission === 'default') {
    Notification.requestPermission();
}

// Escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Inizializza
document.addEventListener('DOMContentLoaded', function() {
    updateNotificheCount();
    pollNuoveNotifiche(); // Avvia polling
    setInterval(updateNotificheCount, 30000); // Update count ogni 30 sec
});
</script>
