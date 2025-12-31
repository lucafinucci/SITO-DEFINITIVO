# 🚀 Quick Start - Test KPI Dashboard in Locale

## Setup Rapido (3 minuti)

### ✅ Step 1: Avvia XAMPP
```
Apri XAMPP Control Panel
Avvia: Apache ✓  MySQL ✓
```

### ✅ Step 2: Esegui Setup Automatico

**Doppio click su:**
```
SETUP_TEST_KPI.bat
```

Questo script creerà automaticamente:
- ✓ 1 utente admin
- ✓ 5 clienti di test
- ✓ Servizio Document Intelligence
- ✓ Dati di utilizzo ultimi 3 mesi

### ✅ Step 3: Configura Token

**File 1:** `area-clienti\api\admin-kpi-clienti.php` (riga 88)
```php
$apiToken = 'test_token_locale_123456';
```

**File 2:** `area-clienti\api\mock-kpi-webapp.php` (riga 14)
```php
$TOKEN_TEST = 'test_token_locale_123456';
```

⚠️ **I token devono essere identici!**

### ✅ Step 4: Verifica Modalità Mock

**File:** `area-clienti\api\admin-kpi-clienti.php` (riga 76)
```php
$useMockApi = true; // TRUE per test locale
```

### ✅ Step 5: Login Admin

1. Apri browser: http://localhost/area-clienti/login.php
2. Login:
   - **Email:** `admin@finch-ai.it`
   - **Password:** `password`

### ✅ Step 6: Accedi alla Dashboard

Vai a: http://localhost/area-clienti/admin/kpi-clienti.php

---

## 🎯 Cosa Vedrai

### Summary Cards (in alto)
```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ Clienti Attivi  │ Documenti Mese  │ Pagine Mese     │ API Online      │
│      5          │     8,020       │     30,300      │      5/5        │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘
```

### Tabella Clienti
```
Cliente              | Doc/Mese | Pagine/Mese | API Status | Azioni
---------------------|----------|-------------|------------|--------
Mario Rossi          | 1,250    | 4,800       | ✓ Online   | Gestisci
Azienda Test SRL     |          |             |            |
📋 Mostra dettagli   |          |             |            |
---------------------|----------|-------------|------------|--------
Luigi Verdi          | 850      | 3,200       | ✓ Online   | Gestisci
Innovazione SPA      |          |             |            |
📋 Mostra dettagli   |          |             |            |
---------------------|----------|-------------|------------|--------
...                  | ...      | ...         | ...        | ...
```

### Dettagli Espandibili (clicca "📋 Mostra dettagli")
```
┌─────────────────────────────────────────────────────────────────┐
│ Documenti Totali: 12,847   Processati: 11,234   Pagine: 45,623 │
│ Accuratezza: 96.8%   Tempo Risparmiato: 427h   ROI: 340%       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🧪 Test Funzionalità

### ✅ Test 1: Ricerca
Digita nella barra di ricerca:
- "Mario" → Filtra per nome
- "SPA" → Filtra per azienda
- "@test.it" → Filtra per email

### ✅ Test 2: Filtro API
Menu a tendina "Filtro API":
- Tutti i clienti → Mostra tutti
- Solo API online → Mostra solo clienti con API funzionante
- Solo API offline → (vuoto se tutto funziona)

### ✅ Test 3: Refresh
Click su "🔄 Aggiorna" → Ricarica dati

### ✅ Test 4: Dettagli
Click su "📋 Mostra dettagli" → Espande KPI completi dal mock

---

## 🔍 Verifica Funzionamento

### Check Console Browser (F12)

**✓ Tutto OK:**
```javascript
KPI caricati con successo
Array di 5 clienti ricevuto
```

**❌ Errore:**
```javascript
Error: Failed to fetch
Error 401: Token non valido
```

### Check Network (F12 → Network)

**Chiamata a `admin-kpi-clienti.php`:**
- Status: `200 OK` ✓
- Response: `{"success": true, "data": [...]}`

**Chiamata a `mock-kpi-webapp.php` (multipla):**
- Status: `200 OK` ✓ (una per ogni cliente)
- Response: JSON con KPI mock

---

## ❌ Troubleshooting Veloce

### Problema: Pagina bianca
**Soluzione:**
```php
// In cima a admin-kpi-clienti.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Problema: "Accesso negato"
**Soluzione:**
```sql
-- Verifica ruolo
SELECT ruolo FROM utenti WHERE email = 'admin@finch-ai.it';
-- Deve essere 'admin'
```

### Problema: API Status "✗ Offline"
**Soluzione:**
1. Verifica token identici in entrambi i file
2. Verifica `$useMockApi = true`
3. Test diretto mock:
   ```
   http://localhost/area-clienti/api/mock-kpi-webapp.php?cliente_id=1&token=test_token_locale_123456
   ```

### Problema: Nessun cliente
**Soluzione:**
Re-esegui `SETUP_TEST_KPI.bat`

---

## 📊 Dati di Test Creati

### Clienti:
1. **Mario Rossi** - Azienda Test SRL
   - Email: `mario.rossi@aztest.it`
   - Documenti: 1,250 | Pagine: 4,800

2. **Luigi Verdi** - Innovazione SPA
   - Email: `luigi.verdi@innovazione.it`
   - Documenti: 850 | Pagine: 3,200

3. **Anna Bianchi** - Digital Solutions
   - Email: `anna.bianchi@digitalsol.it`
   - Documenti: 2,100 | Pagine: 7,500

4. **Francesco Neri** - Tech Consulting
   - Email: `francesco.neri@techcons.it`
   - Documenti: 3,200 | Pagine: 12,500

5. **Giulia Russo** - Smart Business
   - Email: `giulia.russo@smartbiz.it`
   - Documenti: 620 | Pagine: 2,300

**Tutti con password:** `password`

---

## 🎓 Demo Flusso Completo

```
1. Login Admin
   ↓
2. Dashboard Admin (kpi-clienti.php)
   ↓
3. AJAX → admin-kpi-clienti.php
   ↓
4. Loop per ogni cliente:
   ├─ Query DB locale (servizi_quota_uso)
   └─ cURL → mock-kpi-webapp.php
   ↓
5. Combina dati locali + mock
   ↓
6. Render tabella + summary cards
```

---

## 🚀 Passaggio a Produzione

Quando la webapp è pronta:

1. **Cambia modalità mock:**
   ```php
   // admin-kpi-clienti.php riga 76
   $useMockApi = false;
   ```

2. **Genera token sicuro:**
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```

3. **Configura token produzione:**
   - In `admin-kpi-clienti.php` (riga 88)
   - Sulla webapp in `/api/kpi/documenti.php`

4. **URL produzione:**
   ```php
   // admin-kpi-clienti.php riga 85
   $apiEndpoint = 'https://app.finch-ai.it/api/kpi/documenti';
   ```

---

## 📁 File Creati

```
SITO/
├── area-clienti/
│   ├── api/
│   │   ├── admin-kpi-clienti.php      ← API admin
│   │   └── mock-kpi-webapp.php        ← Mock endpoint
│   └── admin/
│       └── kpi-clienti.php            ← Dashboard admin
├── database/
│   └── setup_test_kpi_dashboard.sql   ← Script SQL setup
├── SETUP_TEST_KPI.bat                 ← Setup automatico
├── GUIDA_TEST_LOCALE_KPI.md           ← Guida completa
├── QUICK_START_TEST_KPI.md            ← Questa guida
└── DOCUMENTAZIONE_API_KPI_WEBAPP.md   ← API spec
```

---

## ✅ Checklist Finale

- [ ] XAMPP avviato (Apache + MySQL)
- [ ] `SETUP_TEST_KPI.bat` eseguito
- [ ] Token configurati (identici in entrambi i file)
- [ ] `$useMockApi = true`
- [ ] Login admin effettuato
- [ ] Dashboard mostra 5 clienti
- [ ] API Status tutti "✓ Online"
- [ ] Dettagli espandibili funzionanti
- [ ] Console browser senza errori

---

**Pronto per testare! 🎉**

Se hai domani consulta [GUIDA_TEST_LOCALE_KPI.md](GUIDA_TEST_LOCALE_KPI.md) per troubleshooting avanzato.
