# 🚀 TEST LOCALE AREA CLIENTI FINCH-AI

## ✅ TUTTO PRONTO PER IL TEST!

Ho configurato tutto per testare l'area clienti in locale. Segui questi semplici passaggi:

---

## 📋 PASSAGGI RAPIDI (5 minuti)

### **Step 1: Apri phpMyAdmin**

1. Apri il **XAMPP Control Panel**
2. Verifica che **MySQL** sia avviato (pulsante verde)
3. Clicca sul pulsante **"Admin"** accanto a MySQL
4. Si aprirà phpMyAdmin: `http://localhost/phpmyadmin`

### **Step 2: Esegui lo Script SQL**

1. In phpMyAdmin, clicca sulla tab **"SQL"** in alto
2. Apri il file: `database/ESEGUI_IN_PHPMYADMIN.sql`
3. **Copia TUTTO il contenuto** del file
4. **Incolla** nella finestra SQL di phpMyAdmin
5. Clicca **"Esegui"** (o "Go")
6. ✅ Vedrai messaggi di successo

**Questo script fa TUTTO automaticamente:**
- ✓ Crea il database `finch_ai_clienti`
- ✓ Crea tutte le 7 tabelle necessarie
- ✓ Inserisce 3 utenti demo
- ✓ Inserisce 5 servizi
- ✓ Inserisce 8 fatture
- ✓ Inserisce 5 scadenze

### **Step 3: Testa l'Area Clienti**

Apri il browser e vai a:

```
http://localhost/area-clienti/login.php
```

**Credenziali di test:**

| Email | Password | Ruolo |
|-------|----------|-------|
| `demo@finch-ai.it` | `Demo123!` | Cliente |
| `admin@finch-ai.it` | `Admin123!` | Admin |
| `cliente@example.com` | `Cliente123!` | Cliente |

**Prova a fare login con uno degli utenti!**

---

## 🎯 COSA È STATO CONFIGURATO

### ✅ Database
- Host: `127.0.0.1` (fix problema localhost)
- Database: `finch_ai_clienti`
- Utente: `root`
- Password: _(vuota)_

### ✅ File Aggiornati

**1. area-clienti/includes/db.php**
```php
$dbHost = '127.0.0.1';      // ← Modificato
$dbName = 'finch_ai_clienti';  // ← Modificato
$dbUser = 'root';              // ← Modificato
$dbPass = '';                  // ← Modificato
```

**2. public/api/config/database.php**
```php
define('DB_HOST', '127.0.0.1');      // ← Modificato
define('DB_NAME', 'finch_ai_clienti'); // ← Modificato
```

---

## 🔍 COSA PUOI TESTARE

Dopo il login, potrai testare:

### **1. Dashboard** (`/area-clienti/dashboard.php`)
- Visualizzazione KPI
- Grafici (richiede API esterna)
- Quick links

### **2. Fatture** (`/area-clienti/fatture.php`)
- Elenco fatture
- Download PDF (genera PDF demo)
- Filtri e ordinamento

### **3. Servizi** (`/area-clienti/servizi.php`)
- Elenco servizi attivi
- Dettagli servizi
- Date attivazione

### **4. Profilo** (`/area-clienti/profilo.php`)
- Visualizzazione dati utente
- Modifica profilo

### **5. Logout** (`/area-clienti/logout.php`)
- Distrugge la sessione
- Redirect al login

---

## 🌐 ALTERNATIVE DI TEST

### Opzione A: Sistema PHP Tradizionale (CONSIGLIATO PER TEST LOCALE)
**URL:** `http://localhost/area-clienti/login.php`
- Usa sessioni PHP
- Più semplice da testare
- Funziona subito

### Opzione B: Sistema API/JWT
**URL:** `http://localhost/public/area-clienti.html`
- Usa JWT tokens
- Richiede API esterna per KPI
- Più complesso ma più sicuro

---

## 📊 STRUTTURA DATABASE CREATA

```
finch_ai_clienti
├── utenti (3 record)
│   ├── admin@finch-ai.it
│   ├── demo@finch-ai.it
│   └── cliente@example.com
├── servizi (5 record)
│   ├── Document Intelligence
│   ├── Production Analytics
│   ├── Financial Control
│   ├── Supply Chain Optimizer
│   └── Quality Assurance AI
├── utenti_servizi (8 record)
├── fatture (8 record)
├── scadenze (5 record)
├── sessioni (vuota)
└── access_logs (si popola ad ogni login)
```

---

## 🛠️ RISOLUZIONE PROBLEMI

### ❌ Errore: "Credenziali non valide"
**Soluzione:** Verifica di usare le password corrette:
- `Demo123!` (con D maiuscola e ! alla fine)
- `Admin123!` (con A maiuscola e ! alla fine)

### ❌ Errore: "Errore connessione DB"
**Soluzione:**
1. Verifica che MySQL sia avviato in XAMPP
2. Controlla che il database `finch_ai_clienti` esista in phpMyAdmin
3. Riavvia MySQL dal Control Panel XAMPP

### ❌ Errore: "Host 'localhost' is not allowed"
**Soluzione:** Questo è già risolto! I file usano `127.0.0.1` invece di `localhost`

### ❌ Pagina bianca o errore PHP
**Soluzione:**
1. Verifica che Apache sia avviato in XAMPP
2. Controlla i log PHP: `C:\xampp\php\logs\php_error_log`
3. Assicurati che i file siano nella directory corretta

---

## 📁 FILE UTILI CREATI

| File | Scopo |
|------|-------|
| `database/ESEGUI_IN_PHPMYADMIN.sql` | Setup completo database (ESEGUI QUESTO!) |
| `generate-passwords.php` | Genera nuovi hash password |
| `ISTRUZIONI_TEST_LOCALE.md` | Istruzioni dettagliate |
| `README_TEST_LOCALE.md` | Questo file (guida rapida) |

---

## 🎯 CHECKLIST TEST

- [ ] MySQL avviato in XAMPP
- [ ] Apache avviato in XAMPP
- [ ] Script SQL eseguito in phpMyAdmin
- [ ] Database `finch_ai_clienti` visibile in phpMyAdmin
- [ ] Tabelle create (7 tabelle)
- [ ] Login testato con `demo@finch-ai.it` / `Demo123!`
- [ ] Dashboard funzionante
- [ ] Fatture visualizzate
- [ ] Servizi visualizzati
- [ ] Logout funzionante

---

## 🚀 DOPO IL TEST LOCALE

Una volta verificato che tutto funziona, per andare online su Aruba:

1. Cambia le credenziali DB nei file di config
2. Carica tutti i file via FTP
3. Crea database MySQL dal pannello Aruba
4. Esegui lo stesso script SQL sul database Aruba
5. **ELIMINA** il file `database/ESEGUI_IN_PHPMYADMIN.sql` per sicurezza
6. Modifica `JWT_SECRET` in `public/api/config/database.php`
7. Abilita HTTPS

---

## 💡 SUGGERIMENTI

1. **Usa l'utente demo** per i test: `demo@finch-ai.it` / `Demo123!`
2. **Controlla i log** se qualcosa non funziona: phpMyAdmin > tab "SQL" > errori
3. **Testa tutte le pagine** prima di andare online
4. **Verifica le password** (sono case-sensitive!)

---

## ✅ PRONTO!

Ora puoi:

1. ✅ **Eseguire lo script SQL** in phpMyAdmin
2. ✅ **Testare il login** su `http://localhost/area-clienti/login.php`
3. ✅ **Verificare tutte le funzionalità**

**Buon test! 🎉**

---

**Hai problemi?** Controlla il file `ISTRUZIONI_TEST_LOCALE.md` per dettagli completi.
