# 📚 Area Clienti Finch-AI - Documentazione Completa

## 🎯 Panoramica

Sistema completo di area clienti con autenticazione sicura, gestione fatture, servizi e dashboard personalizzata.

### ✨ Funzionalità Implementate

- ✅ **Autenticazione sicura** con password hashing (bcrypt)
- ✅ **Sessioni PHP** per autenticazione server-side
- ✅ **MFA/TOTP** (Google Authenticator compatibile)
- ✅ **Dashboard clienti** con statistiche e dati
- ✅ **Gestione fatture** con download PDF
- ✅ **Gestione servizi attivi**
- ✅ **Scadenze e promemoria**
- ✅ **Log accessi** completi per sicurezza
- ✅ **Database MySQL** con schema normalizzato

---

## 📁 Struttura File

```
SITO/
├── area-clienti/
│   ├── login.php                # Login area clienti
│   ├── dashboard.php            # Dashboard post-login
│   ├── mfa-setup.php            # Configurazione MFA
│   ├── fatture.php              # Fatture cliente
│   ├── servizi.php              # Servizi attivi
│   ├── profilo.php              # Profilo utente
│   ├── api/
│   │   ├── download-fattura.php # Download PDF fattura
│   │   ├── genera-pdf-fattura.php
│   │   ├── kpi-proxy.php        # Proxy KPI cliente
│   │   ├── admin-kpi-clienti.php
│   │   └── ...
│   ├── admin/
│   │   ├── gestione-servizi.php
│   │   ├── fatture.php
│   │   ├── scadenzario.php
│   │   └── ...
│   ├── includes/
│   │   ├── auth.php
│   │   ├── db.php
│   │   ├── config.php
│   │   └── ...
│   ├── css/
│   └── js/
├── database/
└── ...
```


---

## 🚀 Installazione su Aruba

### 1️⃣ Crea Database MySQL

1. Accedi al **pannello Aruba**
2. Vai su **Database MySQL**
3. Crea nuovo database: `finch_ai_clienti`
4. Annota: **host, database, username, password**

### 2️⃣ Configura Credenziali

Modifica `.env` (consigliato) oppure i default in `area-clienti/includes/config.php`:

```env
DB_HOST=tuo-host-mysql.aruba.it
DB_NAME=finch_ai_clienti
DB_USER=tuo-username
DB_PASS=tua-password

APP_URL=https://tuosito.it
WEBAPP_API_URL=https://app.finch-ai.it/api/kpi/documenti
WEBAPP_API_TOKEN=INSERISCI_TOKEN
```

### 3️⃣ Inizializza Database

1. Carica tutti i file su Aruba via **FTP/FileZilla**
2. Visita: `https://tuosito.it/database/init.php`
3. Attendi completamento (vedrai conferma verde)
4. **ELIMINA** il file `database/init.php` per sicurezza!

### 4️⃣ Testa il Sistema

Visita: `https://tuosito.it/area-clienti/login.php`

**Credenziali Demo**:
- **Email**: `demo@finch-ai.it`
- **Password**: `Demo123!`
- **OTP**: Lasciare vuoto (MFA non abilitato)

---

## 👥 Utenti Demo Creati

| Email | Password | Ruolo | MFA |
|-------|----------|-------|-----|
| `admin@finch-ai.it` | `Admin123!` | admin | No |
| `demo@finch-ai.it` | `Demo123!` | cliente | No |
| `cliente@example.com` | `Cliente123!` | cliente | No |

---

## 🔐 Sicurezza

### Password Hashing

Le password sono hashate con `password_hash()` PHP (bcrypt):
```php
$hash = password_hash($password, PASSWORD_DEFAULT);
password_verify($password, $hash); // Verifica
```

### Sessioni PHP

Autenticazione tramite sessione server-side:
- Cookie di sessione gestito dal server
- Timeout configurabile in `.env`
- Nessun JWT richiesto lato browser

### MFA/TOTP

Compatibile con:
- Google Authenticator
- Microsoft Authenticator
- Authy
- Qualsiasi app TOTP (RFC 6238)

**Per abilitare MFA**:
1. Login sulla dashboard
2. Vai su `/area-clienti/mfa-setup.php`
3. Scansiona QR code con app
4. Inserisci codice a 6 cifre per confermare

### Headers Sicurezza

Aggiungi in `.htaccess` (Aruba):

```apache
# Security Headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "DENY"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📊 Database Schema

### Tabelle Principali

- **utenti**: Dati utenti e credenziali
- **sessioni**: Sessioni PHP attive
- **servizi**: Catalogo servizi Finch-AI
- **utenti_servizi**: Servizi attivi per cliente
- **fatture**: Fatture emesse
- **scadenze**: Promemoria e scadenze
- **access_logs**: Log accessi e sicurezza

### Relazioni

```
utenti (1) ──< (N) utenti_servizi >── (1) servizi
utenti (1) ──< (N) fatture
utenti (1) ──< (N) scadenze
utenti (1) ──< (N) sessioni
utenti (1) ──< (N) access_logs
```

---

## 📄 Download PDF Fatture

### Configurazione

1. Crea cartella `/fatture/` **fuori da public_html**:
```
/home/tuoutente/
├── public_html/
└── fatture/
    └── 2024/
        ├── FT-2024-001.pdf
        ├── FT-2024-002.pdf
        └── ...
```

2. La path nel database deve essere: `/fatture/2024/FT-2024-001.pdf`

3. L'API verificherà:
   - Utente autenticato
   - Fattura appartiene all'utente
   - File esiste ed è leggibile

### PDF Demo

Se il file non esiste, viene generato un PDF demo minimale.
Per PDF reali, carica i file nella cartella `/fatture/`.

---

## 🛠️ API Endpoints

Le API dell'area clienti sono sotto `/area-clienti/api/` e usano la sessione PHP (non JWT).

### Endpoint principali

| Endpoint | Metodo | Descrizione |
|----------|--------|-------------|
| `/area-clienti/login.php` | GET/POST | Login area clienti |
| `/area-clienti/logout.php` | GET | Logout |
| `/area-clienti/api/download-fattura.php?id=X` | GET | Download PDF fattura |
| `/area-clienti/api/genera-pdf-fattura.php?id=X` | GET | PDF fattura (HTML con `format=html`) |
| `/area-clienti/api/kpi-proxy.php` | GET | KPI cliente (proxy) |
| `/area-clienti/api/admin-kpi-clienti.php` | GET | KPI admin per clienti DOC-INT |

---

## 🧪 Testing

1. Apri `https://tuosito.it/area-clienti/login.php`
2. Effettua login con un utente demo
3. Verifica: `/area-clienti/dashboard.php`, `/area-clienti/fatture.php`
4. (Admin) Verifica: `/area-clienti/admin/gestione-servizi.php`

---

## 📱 Frontend

Le pagine dell'area clienti sono renderizzate server-side in PHP dentro `area-clienti/`.
Non e' richiesta autenticazione JWT lato browser: la sessione viene gestita dal server.

---

## 🔧 Manutenzione

### Aggiungere Nuovo Utente (via MySQL)

```sql
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, ruolo)
VALUES (
  'nuovo@cliente.it',
  '$2y$10$...', -- Genera con: password_hash('LaPassword', PASSWORD_DEFAULT)
  'Mario',
  'Bianchi',
  'Nuova Azienda Srl',
  'cliente'
);
```

### Pulire Sessioni Scadute

```sql
DELETE FROM sessioni WHERE expires_at < NOW();
```

### Revocare Token Utente

```sql
UPDATE sessioni SET revoked = 1 WHERE user_id = 123;
```

### Visualizzare Log Accessi

```sql
SELECT * FROM access_logs
WHERE user_id = 2
ORDER BY created_at DESC
LIMIT 50;
```

---

## ⚠️ Troubleshooting

### Errore: "Database connection failed"

- Verifica credenziali in `.env` o `area-clienti/includes/config.php`
- Controlla che il database esista su Aruba
- Verifica che l'utente abbia permessi sul database

### Errore: "Sessione non valida"

- Sessione scaduta. Rifare login
- Cookie/sessione cancellati dal browser
- Verifica che i cookie siano abilitati

### Errore download fattura

- Verifica path file nel database
- Controlla permessi cartella `/fatture/`
- Path deve essere assoluto o relativo alla root

### MFA non funziona

- Verifica che l'orario server sia corretto
- TOTP dipende dal timestamp
- Usa `discrepancy=1` in `verifyTOTPCode()` per tolleranza ±30s

---

## 🚀 Prossimi Step (Opzionali)

### 1. Notifiche Email

Aggiungi invio email per:
- Conferma login
- Nuova fattura disponibile
- Scadenza in arrivo

### 2. Export Excel

Aggiungi export fatture in formato XLS/CSV

### 3. Pagamenti Online

Integra Stripe/PayPal per pagamenti fatture

### 4. Chat Support

Integra widget chat per supporto clienti

### 5. Multi-azienda

Gestisci più aziende per utente amministratore

---

## 📞 Supporto

Per problemi o domande:
- **Email**: info@finch-ai.it
- **Documentazione completa**: In questo file

---

## 📜 Licenza

© 2024 Finch-AI Srl - Tutti i diritti riservati

---

**🎉 Installazione completata!**

Hai ora un sistema completo di area clienti enterprise-grade con:
✅ Sicurezza avanzata (MFA)
✅ Dashboard professionale
✅ Gestione fatture e servizi
✅ Log audit completi
✅ Pronto per produzione!
