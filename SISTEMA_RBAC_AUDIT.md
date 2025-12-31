# Sistema RBAC e Audit Trail Completo

Sistema di controllo accessi basato su ruoli (RBAC) con audit trail completo per tracciamento azioni amministratori.

## Indice

1. [Panoramica](#panoramica)
2. [Architettura](#architettura)
3. [Installazione](#installazione)
4. [Ruoli e Permessi](#ruoli-e-permessi)
5. [Utilizzo RBAC](#utilizzo-rbac)
6. [Audit Logging](#audit-logging)
7. [Gestione Team](#gestione-team)
8. [API Reference](#api-reference)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Panoramica

### Caratteristiche Principali

**RBAC (Role-Based Access Control)**:
- 5 ruoli predefiniti (Super Admin, Admin, Manager, Supporto, Contabile)
- 32 permessi granulari
- 4 livelli di accesso
- Gestione gerarchica (admin può gestire solo livelli inferiori)
- Ruoli personalizzabili

**Audit Trail**:
- Log automatico tutte le azioni
- Trigger database per modifiche critiche
- Tracking IP, User-Agent, Request URL
- Diff before/after per modifiche
- 4 livelli severità (info, warning, error, critical)
- 10 categorie (auth, cliente, servizio, fattura, etc)
- Retention automatico (90-365 giorni)
- Export CSV/JSON

**Sicurezza**:
- Protezione account (blocco dopo 5 tentativi falliti)
- Gestione sessioni con scadenza
- IP tracking
- 2FA ready (schema pronto)
- Review flag per azioni sensibili

---

## Architettura

### Schema Database

```
admin_ruoli
├── Ruoli disponibili
├── 32 permessi boolean
├── Livello accesso (1-4)
└── Metadata

utenti (esteso)
├── admin_ruolo_id → admin_ruoli
├── is_super_admin
├── can_login
├── ultimo_accesso
├── tentativi_login_falliti
├── account_bloccato_fino
├── auth_2fa_enabled
├── auth_2fa_secret
└── session_token

audit_log
├── user_id, user_email, user_ruolo
├── user_ip, user_agent
├── azione, entita, entita_id
├── descrizione
├── dati_prima, dati_dopo (JSON)
├── metadata (JSON)
├── livello, categoria
├── successo, richiede_review
└── created_at

admin_sessions
├── user_id → utenti
├── session_token
├── ip_address, user_agent
├── created_at, last_activity
├── expires_at
└── attiva

admin_inviti
├── email, nome, cognome
├── ruolo_id → admin_ruoli
├── invited_by → utenti
├── token, expires_at
├── stato (pending/accepted/expired)
└── created_user_id
```

### Viste Database

```sql
v_admin_team          -- Team con ruoli e statistiche
v_audit_log_dettagliato    -- Audit con join utenti/ruoli
v_audit_statistiche_admin  -- Statistiche per admin
```

### Classi PHP

```
RBACManager
├── can($permission)           → Verifica permesso
├── requirePermission()        → Richiede o lancia eccezione
├── canAny/canAll()           → Multipli permessi
├── isSuperAdmin()            → Check super admin
├── getAccessLevel()          → Livello 1-4
├── canManageAdmin()          → Verifica gerarchia
├── assegnaRuolo()            → Assegna ruolo
├── getTeamAdmin()            → Lista team
└── toggleAdminStatus()       → Attiva/disattiva

AuditLogger
├── log()                     → Log generico
├── logLogin/logLogout()      → Auth
├── logCreate/Update/Delete() → CRUD
├── logView()                 → Visualizzazioni sensibili
├── logExport()               → Export dati
├── logCommunication()        → Email/SMS
├── logError()                → Errori
├── logConfigChange()         → Config
├── getLogs()                 → Recupera log
└── export()                  → Export CSV/JSON
```

---

## Installazione

### 1. Database

```bash
mysql -u root -p finch_ai < database/add_rbac_audit.sql
```

**Cosa viene creato**:
- ✅ Tabella `admin_ruoli` con 5 ruoli predefiniti
- ✅ Estensione `utenti` con campi RBAC/audit
- ✅ Tabella `audit_log` completa
- ✅ Tabella `admin_sessions`
- ✅ Tabella `admin_inviti`
- ✅ 3 viste aggregate
- ✅ 4 eventi automatici (pulizia sessioni, inviti, audit, reset login)
- ✅ 4 trigger audit (cliente insert/update, fattura insert/update)
- ✅ Primo admin esistente → Super Admin

### 2. Verifica Installazione

```sql
-- Verifica ruoli
SELECT nome, display_name, livello_accesso FROM admin_ruoli;

-- Output atteso:
-- +-------------+---------------------+------------------+
-- | nome        | display_name        | livello_accesso  |
-- +-------------+---------------------+------------------+
-- | super_admin | Super Amministratore| 4                |
-- | admin       | Amministratore      | 3                |
-- | manager     | Manager             | 2                |
-- | supporto    | Supporto Clienti    | 1                |
-- | contabile   | Contabile           | 2                |
-- +-------------+---------------------+------------------+

-- Verifica super admin assegnato
SELECT id, email, is_super_admin, admin_ruolo_id
FROM utenti
WHERE ruolo = 'admin' AND is_super_admin = TRUE;
```

---

## Ruoli e Permessi

### Ruoli Predefiniti

#### 1. Super Amministratore (Livello 4)
**Accesso**: TUTTO

- Tutti i 32 permessi abilitati
- Può gestire altri super admin
- Unico che può modificare configurazioni critiche
- Accesso completo audit log

**Casi d'uso**: Founder, CTO, CEO

#### 2. Amministratore (Livello 3)
**Accesso**: Gestione operativa completa

Permessi:
- ✅ Gestione clienti (view, edit)
- ✅ Gestione servizi (view, edit, activate, deactivate)
- ✅ Gestione fatture (view, create, edit, mark_paid)
- ✅ Gestione pagamenti (view, process)
- ✅ Approva/Rifiuta training
- ✅ Invia email/SMS (no broadcast)
- ✅ View team, audit log
- ✅ Export dati
- ❌ Delete clienti/fatture
- ❌ Refund pagamenti
- ❌ Edit settings
- ❌ Gestisci team

**Casi d'uso**: Operations Manager, Account Manager

#### 3. Manager (Livello 2)
**Accesso**: Supervisione e approvazioni

Permessi:
- ✅ View/Edit clienti
- ✅ Activate/Deactivate servizi
- ✅ Create/Edit fatture, mark paid
- ✅ Process pagamenti
- ✅ Approva/Rifiuta training
- ✅ Invia email
- ✅ View team, audit log
- ❌ Delete, Edit servizi
- ❌ Refund, Broadcast
- ❌ Gestisci team, settings

**Casi d'uso**: Team Lead, Supervisor

#### 4. Supporto Clienti (Livello 1)
**Accesso**: Visualizzazione e assistenza

Permessi:
- ✅ View clienti
- ✅ View servizi
- ✅ View fatture/pagamenti/training
- ✅ Invia email
- ❌ Tutte le modifiche/eliminazioni
- ❌ Approva training
- ❌ Process pagamenti
- ❌ View team/audit/export

**Casi d'uso**: Customer Support, Help Desk

#### 5. Contabile (Livello 2)
**Accesso**: Gestione finanziaria

Permessi:
- ✅ View clienti
- ✅ Create/Edit/Delete fatture
- ✅ Mark paid
- ✅ Process pagamenti, refund
- ✅ Invia email
- ✅ View audit log, export
- ❌ Gestisci servizi
- ❌ Approva training
- ❌ Broadcast, SMS
- ❌ Gestisci team, settings

**Casi d'uso**: Contabile, Financial Manager

### Matrice Permessi Completa

| Permesso                  | Super | Admin | Manager | Supporto | Contabile |
|---------------------------|:-----:|:-----:|:-------:|:--------:|:---------:|
| can_view_dashboard        |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_view_analytics        |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_view_clienti          |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_edit_clienti          |   ✅   |   ✅   |    ✅    |    ❌     |     ❌     |
| can_delete_clienti        |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_impersonate_clienti   |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_view_servizi          |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_edit_servizi          |   ✅   |   ✅   |    ❌    |    ❌     |     ❌     |
| can_activate_servizi      |   ✅   |   ✅   |    ✅    |    ❌     |     ❌     |
| can_deactivate_servizi    |   ✅   |   ✅   |    ✅    |    ❌     |     ❌     |
| can_view_fatture          |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_create_fatture        |   ✅   |   ✅   |    ✅    |    ❌     |     ✅     |
| can_edit_fatture          |   ✅   |   ✅   |    ✅    |    ❌     |     ✅     |
| can_delete_fatture        |   ✅   |   ❌   |    ❌    |    ❌     |     ✅     |
| can_mark_paid             |   ✅   |   ✅   |    ✅    |    ❌     |     ✅     |
| can_view_pagamenti        |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_process_pagamenti     |   ✅   |   ✅   |    ✅    |    ❌     |     ✅     |
| can_refund                |   ✅   |   ❌   |    ❌    |    ❌     |     ✅     |
| can_view_training         |   ✅   |   ✅   |    ✅    |    ✅     |     ❌     |
| can_approve_training      |   ✅   |   ✅   |    ✅    |    ❌     |     ❌     |
| can_reject_training       |   ✅   |   ✅   |    ✅    |    ❌     |     ❌     |
| can_send_emails           |   ✅   |   ✅   |    ✅    |    ✅     |     ✅     |
| can_send_sms              |   ✅   |   ✅   |    ❌    |    ❌     |     ❌     |
| can_broadcast             |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_view_settings         |   ✅   |   ✅   |    ❌    |    ❌     |     ❌     |
| can_edit_settings         |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_manage_templates      |   ✅   |   ✅   |    ❌    |    ❌     |     ❌     |
| can_view_team             |   ✅   |   ✅   |    ✅    |    ❌     |     ❌     |
| can_invite_admin          |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_edit_admin            |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_delete_admin          |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_assign_roles          |   ✅   |   ❌   |    ❌    |    ❌     |     ❌     |
| can_view_audit_log        |   ✅   |   ✅   |    ✅    |    ❌     |     ✅     |
| can_export_data           |   ✅   |   ✅   |    ❌    |    ❌     |     ✅     |

---

## Utilizzo RBAC

### Inizializzazione

```php
require 'includes/rbac-manager.php';

// Metodo 1: Auto-load da sessione
$rbac = new RBACManager($pdo);

// Metodo 2: Load specifico utente
$rbac = new RBACManager($pdo, $userId);

// Metodo 3: Helper
$rbac = getRBAC($pdo);
```

### Verifica Permessi

```php
// Check singolo permesso
if ($rbac->can('can_edit_clienti')) {
    // Utente può modificare clienti
}

// Richiedi permesso (lancia eccezione se negato)
try {
    $rbac->requirePermission('can_delete_fatture');
    // Procedi con eliminazione
} catch (PermissionDeniedException $e) {
    // Permesso negato
    echo "Errore: " . $e->getMessage();
}

// Check multipli permessi (OR)
if ($rbac->canAny(['can_edit_servizi', 'can_activate_servizi'])) {
    // Ha almeno uno dei due permessi
}

// Check multipli permessi (AND)
if ($rbac->canAll(['can_view_fatture', 'can_edit_fatture'])) {
    // Ha entrambi i permessi
}

// Check super admin
if ($rbac->isSuperAdmin()) {
    // È super admin
}

// Livello accesso
$level = $rbac->getAccessLevel(); // 1-4
```

### Protezione Pagine

```php
// area-clienti/admin/fatture.php

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';

// Richiedi permesso (redirect automatico se negato)
$rbac = requirePermission($pdo, 'can_view_fatture');

// Oppure manuale
$rbac = new RBACManager($pdo);
if (!$rbac->can('can_view_fatture')) {
    header('Location: dashboard.php?error=permission_denied');
    exit;
}

// Contenuto pagina...
```

### Protezione API

```php
// area-clienti/api/fatture.php

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/rbac-manager.php';

header('Content-Type: application/json');

$rbac = new RBACManager($pdo);

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $rbac->requirePermission('can_create_fatture');
            // Crea fattura...
            break;

        case 'delete':
            $rbac->requirePermission('can_delete_fatture');
            // Elimina fattura...
            break;
    }

    echo json_encode(['success' => true]);

} catch (PermissionDeniedException $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### UI Condizionale

```php
<!-- Mostra pulsante solo se ha permesso -->
<?php if ($rbac->can('can_create_fatture')): ?>
    <button onclick="creaFattura()">Crea Fattura</button>
<?php endif; ?>

<!-- Menu admin -->
<nav>
    <?php if ($rbac->can('can_view_clienti')): ?>
        <a href="clienti.php">Clienti</a>
    <?php endif; ?>

    <?php if ($rbac->can('can_view_fatture')): ?>
        <a href="fatture.php">Fatture</a>
    <?php endif; ?>

    <?php if ($rbac->can('can_view_team')): ?>
        <a href="gestione-team.php">Team</a>
    <?php endif; ?>

    <?php if ($rbac->can('can_view_audit_log')): ?>
        <a href="audit-log.php">Audit Log</a>
    <?php endif; ?>
</nav>
```

---

## Audit Logging

### Inizializzazione

```php
require 'includes/audit-logger.php';

$audit = new AuditLogger($pdo);

// Helper
$audit = auditLog($pdo);
```

### Log Automatico

#### Login/Logout

```php
// Login successo
$audit->logLogin($userId, true);

// Login fallito
$audit->logLogin($userId, false, 'Password errata');

// Logout
$audit->logLogout();
```

#### CRUD Operations

```php
// Creazione
$clienteId = 123;
$dati = [
    'email' => 'cliente@example.com',
    'azienda' => 'ACME Inc',
    'nome' => 'Mario',
    'cognome' => 'Rossi'
];
$audit->logCreate('cliente', $clienteId, $dati);

// Modifica
$datiPrima = ['email' => 'old@example.com', 'azienda' => 'Old Corp'];
$datiDopo = ['email' => 'new@example.com', 'azienda' => 'New Corp'];
$audit->logUpdate('cliente', $clienteId, $datiPrima, $datiDopo);

// Eliminazione
$audit->logDelete('cliente', $clienteId, $dati, 'Cliente eliminato su richiesta');

// Visualizzazione dati sensibili
$audit->logView('fattura', $fatturaId, 'Visualizzata fattura cliente X');
```

#### Comunicazioni

```php
// Email
$audit->logCommunication(
    'email',
    'cliente@example.com',
    'Fattura #123 emessa',
    true  // successo
);

// SMS
$audit->logCommunication(
    'sms',
    '+393123456789',
    'Promemoria scadenza fattura',
    false  // fallito
);
```

#### Export Dati

```php
$audit->logExport(
    'clienti',           // entità
    'csv',               // formato
    ['attivi' => true],  // filtri
    250                  // record esportati
);
```

#### Errori

```php
// Errore normale
$audit->logError(
    'Errore connessione database',
    'database',
    ['host' => 'localhost', 'error' => $e->getMessage()],
    false  // non critico
);

// Errore critico
$audit->logError(
    'Accesso non autorizzato rilevato',
    'security',
    ['ip' => $ip, 'attempts' => 10],
    true  // CRITICO
);
```

#### Configurazioni

```php
$audit->logConfigChange(
    'sms_provider',
    'twilio',
    'vonage'
);
```

### Log Manuale Avanzato

```php
$audit->log([
    'azione' => 'custom_action',
    'entita' => 'servizio',
    'entita_id' => 5,
    'descrizione' => 'Servizio personalizzato modificato',
    'dati_prima' => ['price' => 99],
    'dati_dopo' => ['price' => 149],
    'livello' => 'warning',
    'categoria' => 'servizio',
    'successo' => true,
    'richiede_review' => true,
    'metadata' => [
        'motivo' => 'Adeguamento prezzi',
        'approvato_da' => $approvatorId
    ]
]);
```

### Recupero Log

```php
// Log con filtri
$logs = $audit->getLogs([
    'user_id' => 5,
    'categoria' => 'fattura',
    'livello' => 'warning',
    'data_da' => '2024-01-01',
    'data_a' => '2024-12-31',
    'richiede_review' => true,
    'limit' => 100,
    'offset' => 0
]);

// Conta log
$count = $audit->countLogs([
    'categoria' => 'cliente'
]);

// Statistiche
$stats = $audit->getStatistiche($userId, 30); // ultimi 30 giorni

// Output:
// [
//     'totale_azioni' => 1523,
//     'azioni_successo' => 1498,
//     'azioni_fallite' => 25,
//     'critiche' => 2,
//     'da_revisionare' => 18,
//     'giorni_attivi' => 28,
//     'utenti_attivi' => 5
// ]
```

### Export Audit

```php
// Export CSV
$csv = $audit->export('csv', [
    'data_da' => '2024-01-01',
    'categoria' => 'fattura'
]);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit-log.csv"');
echo $csv;

// Export JSON
$json = $audit->export('json', [
    'livello' => 'critical',
    'limit' => 1000
]);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="audit-log.json"');
echo $json;
```

### Trigger Automatici

I seguenti eventi creano automaticamente log audit tramite trigger database:

```sql
-- Creazione cliente
INSERT INTO utenti (ruolo, email, azienda, ...) VALUES ('cliente', ...);
→ Audit log creato automaticamente

-- Modifica cliente
UPDATE utenti SET email = 'new@example.com' WHERE id = 123;
→ Audit log con diff before/after

-- Creazione fattura
INSERT INTO fatture (numero_fattura, totale, ...) VALUES (...);
→ Audit log creato

-- Cambio stato fattura
UPDATE fatture SET stato = 'pagata' WHERE id = 456;
→ Audit log con evidenza cambio stato
```

---

## Gestione Team

### Interfaccia Web

Accesso: `https://finch-ai.it/area-clienti/admin/gestione-team.php`

**Requisiti**: Permesso `can_view_team`

**Funzionalità**:
- 📊 Dashboard statistiche team
- 👥 Lista admin con ruoli e permessi
- ➕ Invita nuovo admin
- ✏️ Modifica ruolo/permessi
- 🔄 Attiva/Disattiva account
- 🗑️ Elimina admin
- 🔍 Ricerca e filtri
- 📈 Statistiche attività

### Invitare Nuovo Admin

```php
// API: area-clienti/api/team-admin.php

$formData = new FormData();
$formData.append('action', 'invite');
$formData.append('email', 'nuovo@example.com');
$formData.append('nome', 'Mario');
$formData.append('cognome', 'Rossi');
$formData.append('ruolo_id', 3); // Manager
$formData.append('messaggio', 'Benvenuto nel team!');

fetch('api/team-admin.php', {
    method: 'POST',
    body: formData
})
.then(r => r.json())
.then(data => {
    console.log('Invito creato:', data.invite_id);
    console.log('Link:', data.link);
});
```

**Flusso invito**:
1. Admin invia invito → Record in `admin_inviti`
2. Email automatica con token (valido 7 giorni)
3. Nuovo admin clicca link → Pagina accettazione
4. Completa registrazione → Account creato con ruolo assegnato

### Gestione Ruoli

```php
// Assegna ruolo
$rbac->assegnaRuolo($adminId, $ruoloId);

// Via API
POST /api/team-admin.php?action=assign_role
{
    "admin_id": 5,
    "ruolo_id": 2  // Manager
}
```

**Regole**:
- Solo Super Admin può creare altri Super Admin
- Admin può gestire solo livelli inferiori
- Non può modificare il proprio ruolo
- Log audit automatico su ogni cambio

### Attiva/Disattiva Admin

```php
// Disattiva
$rbac->toggleAdminStatus($adminId, false);

// Riattiva
$rbac->toggleAdminStatus($adminId, true);
```

**Effetti disattivazione**:
- ❌ can_login → FALSE
- ❌ Tutte le sessioni invalidate
- ❌ Non può fare login
- ✅ Dati conservati
- ✅ Audit log preservato

### Eliminazione Admin

```php
$rbac->eliminaAdmin($adminId);
```

**Effetti eliminazione**:
- ❌ Record utente eliminato
- ❌ Tutte le sessioni eliminate (CASCADE)
- ✅ Audit log preservato (user_id → NULL)
- ⚠️ Azione irreversibile (doppia conferma UI)

**Protezioni**:
- ❌ Non può eliminare se stesso
- ❌ Non può eliminare Super Admin (a meno che tu sia Super Admin)
- ❌ Non può eliminare admin di livello superiore

---

## API Reference

### RBACManager

```php
class RBACManager {
    // Verifica
    public function can(string $permission): bool
    public function requirePermission(string $permission, string $message = null): void
    public function canAny(array $permissions): bool
    public function canAll(array $permissions): bool

    // Info utente
    public function getCurrentUser(): array|false
    public function getAllUserPermissions(): array
    public function isSuperAdmin(): bool
    public function getAccessLevel(): int  // 0-4

    // Gestione
    public function canManageAdmin(int $targetAdminId): bool
    public function getRuoli(bool $soloAttivi = true): array
    public function getRuolo(int $ruoloId): array|false
    public function assegnaRuolo(int $userId, int $ruoloId): bool
    public function getTeamAdmin(array $filtri = []): array
    public function toggleAdminStatus(int $adminId, bool $stato): bool
    public function eliminaAdmin(int $adminId): bool
}
```

### AuditLogger

```php
class AuditLogger {
    // Log specifici
    public function logLogin(int $userId, bool $successo, string $motivo = null): int|false
    public function logLogout(): int|false
    public function logCreate(string $entita, int $entitaId, array $dati, string $descrizione = null): int|false
    public function logUpdate(string $entita, int $entitaId, array $datiPrima, array $datiDopo, string $descrizione = null): int|false
    public function logDelete(string $entita, int $entitaId, array $dati = null, string $descrizione = null): int|false
    public function logView(string $entita, int $entitaId, string $descrizione = null): int|false
    public function logExport(string $entita, string $formato, array $filtri = null, int $conteggioRecord = null): int|false
    public function logCommunication(string $tipo, string $destinatario, string $oggetto, bool $successo): int|false
    public function logError(string $descrizione, string $entita = null, array $dettagli = null, bool $critico = false): int|false
    public function logConfigChange(string $chiave, $valorePrecedente, $valoreNuovo): int|false

    // Log generico
    public function log(array $dati): int|false

    // Recupero
    public function getLogs(array $filtri = []): array
    public function countLogs(array $filtri = []): int
    public function getStatistiche(int $userId = null, int $giorni = 30): array

    // Export
    public function export(string $formato = 'csv', array $filtri = []): string
}
```

### Helper Functions

```php
// RBAC
function requirePermission(PDO $pdo, string $permission): RBACManager
function getRBAC(PDO $pdo): RBACManager

// Audit
function auditLog(PDO $pdo): AuditLogger
```

---

## Best Practices

### Sicurezza

1. **Principio del Minimo Privilegio**
   ```php
   // ❌ BAD: Assegna Admin a tutti
   $rbac->assegnaRuolo($userId, $adminRoleId);

   // ✅ GOOD: Assegna ruolo minimo necessario
   $rbac->assegnaRuolo($userId, $supportoRoleId);
   ```

2. **Verifica Sempre i Permessi**
   ```php
   // ❌ BAD: Assume che l'utente abbia permesso
   deleteCliente($clienteId);

   // ✅ GOOD: Verifica esplicita
   $rbac->requirePermission('can_delete_clienti');
   deleteCliente($clienteId);
   ```

3. **Log Azioni Sensibili**
   ```php
   // Sempre log per: delete, export, config changes, refund
   $rbac->requirePermission('can_refund');
   $result = processRefund($amount);

   $audit->log([
       'azione' => 'refund',
       'entita' => 'pagamento',
       'entita_id' => $paymentId,
       'descrizione' => "Rimborso €$amount processato",
       'livello' => 'warning',
       'richiede_review' => true
   ]);
   ```

### Performance

1. **Cache Permessi Utente**
   ```php
   // ✅ GOOD: Inizializza una volta per richiesta
   $rbac = new RBACManager($pdo);

   // Riusa istanza
   if ($rbac->can('can_edit_clienti')) { /* ... */ }
   if ($rbac->can('can_view_fatture')) { /* ... */ }
   ```

2. **Batch Audit Logs**
   ```php
   // ❌ BAD: Log in loop
   foreach ($items as $item) {
       processItem($item);
       $audit->logUpdate(...); // N query
   }

   // ✅ GOOD: Log aggregato
   $processed = [];
   foreach ($items as $item) {
       $processed[] = processItem($item);
   }
   $audit->log([
       'azione' => 'batch_update',
       'metadata' => ['count' => count($processed)]
   ]);
   ```

### Manutenzione

1. **Review Periodica Audit Log**
   ```sql
   -- Log critici non revisionati
   SELECT * FROM audit_log
   WHERE richiede_review = TRUE
     AND livello = 'critical'
     AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
   ORDER BY created_at DESC;
   ```

2. **Monitor Tentativi Login Falliti**
   ```sql
   -- IP sospetti (>10 tentativi/ora)
   SELECT user_ip, COUNT(*) AS tentativi
   FROM audit_log
   WHERE azione = 'login_failed'
     AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
   GROUP BY user_ip
   HAVING tentativi > 10
   ORDER BY tentativi DESC;
   ```

3. **Audit Retention Policy**
   - Info/Warning: 90 giorni
   - Error: 180 giorni
   - Critical: 365 giorni
   - Review flag: Mai eliminare automaticamente

---

## Troubleshooting

### Errore: "Permesso negato"

```
PermissionDeniedException: Permesso negato: can_edit_fatture richiesto
```

**Cause**:
1. Utente non ha il ruolo corretto
2. Ruolo non ha il permesso abilitato

**Soluzione**:
```sql
-- Verifica ruolo utente
SELECT u.id, u.email, ar.display_name, ar.can_edit_fatture
FROM utenti u
LEFT JOIN admin_ruoli ar ON u.admin_ruolo_id = ar.id
WHERE u.id = 123;

-- Se can_edit_fatture = 0, abilita permesso nel ruolo
UPDATE admin_ruoli
SET can_edit_fatture = TRUE
WHERE id = [ruolo_id];

-- Oppure assegna ruolo diverso
UPDATE utenti SET admin_ruolo_id = [nuovo_ruolo_id] WHERE id = 123;
```

### Audit Log Non Viene Creato

**Cause**:
1. Variabile MySQL `@current_admin_id` non impostata
2. Trigger non attivo

**Soluzione**:
```php
// Assicurati di inizializzare AuditLogger
$audit = new AuditLogger($pdo);  // Imposta @current_admin_id

// Verifica trigger
SHOW TRIGGERS LIKE 'utenti';
```

### Account Bloccato

```
Account bloccato fino a: 2024-03-15 14:30:00
```

**Causa**: 5+ tentativi login falliti

**Soluzione**:
```sql
-- Sblocca manualmente
UPDATE utenti
SET tentativi_login_falliti = 0,
    account_bloccato_fino = NULL
WHERE id = 123;

-- Oppure attendi scadenza automatica (1 ora)
```

### Sessione Scaduta

**Causa**: Session token scaduto (default: 24h)

**Soluzione**:
```sql
-- Estendi scadenza sessioni attive
UPDATE admin_sessions
SET expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
WHERE user_id = 123 AND attiva = TRUE;
```

---

## Checklist Go-Live

- [ ] Database schema applicato
- [ ] Super Admin assegnato
- [ ] Ruoli configurati correttamente
- [ ] Permessi verificati per ogni ruolo
- [ ] Protezione pagine implementata (`requirePermission`)
- [ ] Protezione API implementata
- [ ] Audit logging su azioni critiche
- [ ] Eventi automatici attivi (sessioni, inviti, cleanup)
- [ ] Trigger audit attivi
- [ ] Retention policy configurata
- [ ] Monitor audit log critici implementato
- [ ] Backup database pre-deploy
- [ ] Test RBAC per ogni ruolo
- [ ] Test audit logging end-to-end

---

**Sistema RBAC e Audit Trail v1.0**
Finch-AI © 2024
