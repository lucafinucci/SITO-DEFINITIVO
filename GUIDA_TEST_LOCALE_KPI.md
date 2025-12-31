# Guida Testing Locale - Dashboard KPI Admin

## Prerequisiti

- XAMPP avviato (Apache e MySQL)
- Database area clienti configurato
- Almeno un utente admin nel database
- Almeno un cliente con servizio Document Intelligence attivo

---

## Step 1: Verifica Database

### 1.1 Verifica Utente Admin

Apri phpMyAdmin o il tuo client MySQL e verifica:

```sql
-- Verifica che esista un admin
SELECT id, email, nome, cognome, ruolo FROM utenti WHERE ruolo = 'admin';
```

Se non esiste, creane uno:

```sql
-- Crea utente admin (cambia email e password!)
INSERT INTO utenti (email, password, nome, cognome, ruolo, created_at)
VALUES (
    'admin@finch-ai.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'Admin',
    'Test',
    'admin',
    NOW()
);
```

### 1.2 Verifica Cliente con Document Intelligence

```sql
-- Verifica clienti con Document Intelligence attivo
SELECT
    u.id,
    u.email,
    u.nome,
    u.cognome,
    u.azienda,
    s.nome AS servizio,
    us.stato
FROM utenti u
JOIN utenti_servizi us ON u.id = us.user_id
JOIN servizi s ON us.servizio_id = s.id
WHERE s.codice = 'DOC-INT'
    AND us.stato = 'attivo';
```

Se non ci sono clienti, creane uno:

```sql
-- 1. Crea un cliente di test
INSERT INTO utenti (email, password, nome, cognome, azienda, ruolo, created_at)
VALUES (
    'cliente1@test.it',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Mario',
    'Rossi',
    'Azienda Test SRL',
    'cliente',
    NOW()
);

-- 2. Ottieni l'ID del cliente appena creato
SET @cliente_id = LAST_INSERT_ID();

-- 3. Verifica che esista il servizio Document Intelligence
SELECT id, codice, nome FROM servizi WHERE codice = 'DOC-INT';

-- 4. Attiva il servizio per il cliente
INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
SELECT
    @cliente_id,
    id,
    NOW(),
    'attivo'
FROM servizi
WHERE codice = 'DOC-INT'
LIMIT 1;

-- 5. Aggiungi dati di utilizzo (opzionale)
INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati, pagine_analizzate)
SELECT
    @cliente_id,
    id,
    DATE_FORMAT(NOW(), '%Y-%m'),
    1250, -- documenti usati
    4800  -- pagine analizzate
FROM servizi
WHERE codice = 'DOC-INT'
LIMIT 1;
```

---

## Step 2: Configurazione File

### 2.1 Configura il Token

Apri `area-clienti\api\admin-kpi-clienti.php` alla riga 88 e imposta un token:

```php
$apiToken = 'test_token_locale_123456';
```

Apri `area-clienti\api\mock-kpi-webapp.php` alla riga 14 e usa lo **stesso token**:

```php
$TOKEN_TEST = 'test_token_locale_123456';
```

### 2.2 Verifica Modalità Mock

In `area-clienti\api\admin-kpi-clienti.php` alla riga 76, verifica che sia:

```php
$useMockApi = true; // TRUE = usa endpoint mock locale
```

---

## Step 3: Login come Admin

### 3.1 Accedi all'Area Clienti

1. Apri browser: `http://localhost/area-clienti/login.php`
2. Inserisci credenziali admin:
   - Email: `admin@finch-ai.it`
   - Password: `password` (se hai usato la query sopra)

### 3.2 Verifica di essere Admin

Dopo il login, dovresti vedere nel menu le voci admin.

---

## Step 4: Accedi alla Dashboard KPI

### 4.1 URL Diretto

Apri nel browser:

```
http://localhost/area-clienti/admin/kpi-clienti.php
```

### 4.2 Cosa Dovresti Vedere

Se tutto è configurato correttamente, vedrai:

1. **Summary Cards** in alto:
   - Clienti Attivi
   - Documenti Totali (mese corrente)
   - Pagine Analizzate (mese corrente)
   - API Online (dovrebbe essere N/N se il mock funziona)

2. **Tabella Clienti** con:
   - Dati del cliente (nome, azienda, email)
   - Documenti/mese (dati locali dal database)
   - Pagine/mese (dati locali dal database)
   - API Status: **✓ Online** (verde) se il mock funziona
   - Pulsante "📋 Mostra dettagli"

3. **Dettagli Espandibili**:
   Clicca su "📋 Mostra dettagli" per vedere i KPI completi dal mock

---

## Step 5: Test API Diretta

### 5.1 Test API Mock (Simulazione Webapp)

Apri nel browser o con curl:

```bash
# Windows PowerShell
Invoke-WebRequest "http://localhost/area-clienti/api/mock-kpi-webapp.php?cliente_id=1&token=test_token_locale_123456"

# CMD
curl "http://localhost/area-clienti/api/mock-kpi-webapp.php?cliente_id=1&token=test_token_locale_123456"

# Browser
http://localhost/area-clienti/api/mock-kpi-webapp.php?cliente_id=1&token=test_token_locale_123456
```

**Risposta attesa**: JSON con dati KPI mock

### 5.2 Test API Admin

Devi essere loggato come admin. Nel browser:

```
http://localhost/area-clienti/api/admin-kpi-clienti.php
```

**Risposta attesa**: JSON con array di clienti e loro KPI

---

## Troubleshooting

### ❌ Errore: "Nessun cliente con Document Intelligence attivo"

**Soluzione**: Esegui le query dello Step 1.2 per creare un cliente di test

### ❌ Errore: "Accesso negato" o redirect a denied.php

**Soluzione**:
1. Verifica di essere loggato come admin
2. Controlla nel database: `SELECT ruolo FROM utenti WHERE id = TUO_ID;`
3. Se il ruolo non è 'admin', aggiornalo:
   ```sql
   UPDATE utenti SET ruolo = 'admin' WHERE id = TUO_ID;
   ```

### ❌ API Status sempre "✗ Offline"

**Soluzione**:
1. Verifica che `$useMockApi = true` in `admin-kpi-clienti.php`
2. Verifica che il token sia identico in entrambi i file
3. Apri la console del browser (F12) e cerca errori
4. Testa l'API mock direttamente (Step 5.1)

### ❌ Pagina bianca o errore 500

**Soluzione**:
1. Attiva error reporting in PHP:
   ```php
   // In cima a admin-kpi-clienti.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Controlla i log di Apache: `C:\xampp\apache\logs\error.log`
3. Verifica che il database sia accessibile

### ❌ Dati locali (documenti/pagine) a zero

**Soluzione**:
Inserisci dati di test nella tabella `servizi_quota_uso`:

```sql
INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati, pagine_analizzate)
SELECT
    u.id,
    s.id,
    DATE_FORMAT(NOW(), '%Y-%m'),
    FLOOR(RAND() * 2000) + 500,  -- Random 500-2500 documenti
    FLOOR(RAND() * 8000) + 2000  -- Random 2000-10000 pagine
FROM utenti u
CROSS JOIN servizi s
WHERE s.codice = 'DOC-INT'
    AND u.ruolo = 'cliente'
    AND EXISTS (
        SELECT 1 FROM utenti_servizi us
        WHERE us.user_id = u.id
            AND us.servizio_id = s.id
            AND us.stato = 'attivo'
    );
```

---

## Verifica Console Browser

Apri la console del browser (F12 → Console) e cerca:

- ✅ Messaggi di successo dal caricamento KPI
- ❌ Errori di rete (404, 500, ecc.)
- ⚠️ Warning JavaScript

---

## Debug Avanzato

### Verifica Chiamata AJAX

Nella console del browser (F12 → Network):

1. Ricarica la pagina
2. Cerca la chiamata a `admin-kpi-clienti.php`
3. Clicca sulla chiamata
4. Verifica:
   - **Status**: dovrebbe essere 200
   - **Response**: dovrebbe contenere JSON con `success: true`
   - **Preview**: visualizza i dati formattati

### Log PHP Personalizzato

Aggiungi questo in `admin-kpi-clienti.php` dopo la riga 88:

```php
// Debug: Log chiamata API
error_log("=== KPI API Call ===");
error_log("API Endpoint: " . $apiEndpoint);
error_log("Token: " . substr($apiToken, 0, 10) . "...");
error_log("Num Clienti: " . count($clienti));
```

Controlla il log: `C:\xampp\apache\logs\error.log`

---

## Passaggio a Produzione

Quando la webapp esterna sarà pronta:

1. In `admin-kpi-clienti.php` riga 76:
   ```php
   $useMockApi = false; // Passa a false
   ```

2. Configura l'endpoint reale (riga 85):
   ```php
   $apiEndpoint = 'https://app.finch-ai.it/api/kpi/documenti';
   ```

3. Genera un token sicuro:
   ```bash
   # In PHP
   php -r "echo bin2hex(random_bytes(32));"
   ```

4. Usa lo **stesso token** sia in area-clienti che sulla webapp

---

## Dati di Test Aggiuntivi

### Crea Più Clienti di Test

```sql
-- Cliente 2
INSERT INTO utenti (email, password, nome, cognome, azienda, ruolo, created_at)
VALUES ('cliente2@test.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Luigi', 'Verdi', 'Innovazione SPA', 'cliente', NOW());

SET @cliente2_id = LAST_INSERT_ID();

INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
SELECT @cliente2_id, id, NOW(), 'attivo' FROM servizi WHERE codice = 'DOC-INT' LIMIT 1;

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati, pagine_analizzate)
SELECT @cliente2_id, id, DATE_FORMAT(NOW(), '%Y-%m'), 850, 3200
FROM servizi WHERE codice = 'DOC-INT' LIMIT 1;

-- Cliente 3
INSERT INTO utenti (email, password, nome, cognome, azienda, ruolo, created_at)
VALUES ('cliente3@test.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Anna', 'Bianchi', 'Digital Solutions', 'cliente', NOW());

SET @cliente3_id = LAST_INSERT_ID();

INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato)
SELECT @cliente3_id, id, NOW(), 'attivo' FROM servizi WHERE codice = 'DOC-INT' LIMIT 1;

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati, pagine_analizzate)
SELECT @cliente3_id, id, DATE_FORMAT(NOW(), '%Y-%m'), 2100, 7500
FROM servizi WHERE codice = 'DOC-INT' LIMIT 1;
```

---

## Checklist Finale

- [ ] Database configurato con utente admin
- [ ] Almeno un cliente con Document Intelligence attivo
- [ ] Token identico in `admin-kpi-clienti.php` e `mock-kpi-webapp.php`
- [ ] `$useMockApi = true` in `admin-kpi-clienti.php`
- [ ] Login come admin effettuato
- [ ] Dashboard accessibile a `http://localhost/area-clienti/admin/kpi-clienti.php`
- [ ] API Status mostra "✓ Online" (verde)
- [ ] Dettagli espandibili funzionanti
- [ ] Nessun errore nella console del browser

---

## Supporto

Se incontri problemi, verifica:

1. **Log Apache**: `C:\xampp\apache\logs\error.log`
2. **Log PHP**: Aggiungi `error_log()` nei punti critici
3. **Console Browser**: F12 → Console e Network
4. **Query Database**: Verifica che i dati siano presenti

Buon testing! 🚀
