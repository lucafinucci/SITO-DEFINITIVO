# 📚 Area Clienti Finch-AI - Documentazione Completa

## 🎯 Panoramica

Sistema completo di area clienti con autenticazione sicura, gestione fatture, servizi e dashboard personalizzata.

### ✨ Funzionalità Implementate

- ✅ **Autenticazione sicura** con password hashing (bcrypt)
- ✅ **JWT (JSON Web Tokens)** per autenticazione stateless
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
├── database/
│   ├── schema.sql          # Schema database MySQL
│   ├── seed.sql            # Dati demo (non necessario)
│   └── init.php            # Script inizializzazione (ELIMINARE dopo uso)
│
├── public/
│   ├── area-clienti.html   # Pagina login
│   ├── dashboard.html      # Dashboard post-login
│   ├── mfa-setup.html      # Configurazione MFA
│   │
│   └── api/
│       ├── config/
│       │   └── database.php      # Configurazione DB e JWT
│       │
│       ├── lib/
│       │   ├── jwt.php           # Funzioni JWT
│       │   └── totp.php          # Funzioni TOTP/MFA
│       │
│       ├── auth/
│       │   ├── login.php         # API Login
│       │   ├── logout.php        # API Logout
│       │   ├── check-session.php # Verifica sessione
│       │   └── mfa-setup.php     # Gestione MFA
│       │
│       └── clienti/
│           ├── fatture.php       # Lista fatture
│           ├── servizi.php       # Lista servizi
│           ├── scadenze.php      # Lista scadenze
│           └── download-fattura.php # Download PDF
```

---

## 🚀 Installazione su Aruba

### 1️⃣ Crea Database MySQL

1. Accedi al **pannello Aruba**
2. Vai su **Database MySQL**
3. Crea nuovo database: `finch_ai_clienti`
4. Annota: **host, database, username, password**

### 2️⃣ Configura Credenziali

Modifica `public/api/config/database.php`:

```php
define('DB_HOST', 'tuo-host-mysql.aruba.it');
define('DB_NAME', 'finch_ai_clienti');
define('DB_USER', 'tuo-username');
define('DB_PASS', 'tua-password');

// IMPORTANTE: Cambia questa chiave segreta!
define('JWT_SECRET', 'GENERA_UNA_CHIAVE_CASUALE_LUNGA_E_SICURA');
```

**Genera JWT_SECRET**: Usa [random.org](https://www.random.org/strings/) o:
```bash
openssl rand -base64 32
```

### 3️⃣ Inizializza Database

1. Carica tutti i file su Aruba via **FTP/FileZilla**
2. Visita: `https://tuosito.it/database/init.php`
3. Attendi completamento (vedrai conferma verde)
4. **ELIMINA** il file `database/init.php` per sicurezza!

### 4️⃣ Testa il Sistema

Visita: `https://tuosito.it/area-clienti.html`

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

### JWT Tokens

Token firmati con HMAC-SHA256:
- Scadenza: **2 ore**
- Archiviati in `localStorage` del browser
- Verificati ad ogni richiesta API

### MFA/TOTP

Compatibile con:
- Google Authenticator
- Microsoft Authenticator
- Authy
- Qualsiasi app TOTP (RFC 6238)

**Per abilitare MFA**:
1. Login sulla dashboard
2. Vai su `/mfa-setup.html`
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
- **sessioni**: Token JWT attivi
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

### Autenticazione

| Endpoint | Metodo | Descrizione |
|----------|--------|-------------|
| `/api/auth/login.php` | POST | Login con email/password/OTP |
| `/api/auth/logout.php` | POST | Logout (revoca token) |
| `/api/auth/check-session.php` | GET | Verifica token JWT |
| `/api/auth/mfa-setup.php` | GET/POST/DELETE | Gestione MFA |

### Clienti (richiedono JWT)

| Endpoint | Metodo | Descrizione |
|----------|--------|-------------|
| `/api/clienti/fatture.php` | GET | Lista fatture utente |
| `/api/clienti/servizi.php` | GET | Servizi attivi |
| `/api/clienti/scadenze.php` | GET | Scadenze future |
| `/api/clienti/download-fattura.php?id=X` | GET | Download PDF fattura |

### Autenticazione Richieste

Tutte le API clienti richiedono header:
```
Authorization: Bearer <JWT_TOKEN>
```

---

## 🧪 Testing

### 1. Test Login

```bash
curl -X POST https://tuosito.it/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@finch-ai.it","password":"Demo123!"}'
```

Risposta attesa:
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 2,
    "name": "Luigi Verdi",
    "email": "demo@finch-ai.it",
    "azienda": "Azienda Demo Srl"
  }
}
```

### 2. Test API con JWT

```bash
curl https://tuosito.it/api/clienti/fatture.php \
  -H "Authorization: Bearer <IL_TUO_TOKEN>"
```

---

## 📱 Frontend

### Login Form

```javascript
// area-clienti.html
const res = await fetch('/api/auth/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password, otp })
});

const data = await res.json();
localStorage.setItem('auth_token', data.token);
window.location.href = '/dashboard.html';
```

### Dashboard

```javascript
// dashboard.html
const token = localStorage.getItem('auth_token');
const res = await fetch('/api/clienti/fatture.php', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const fatture = await res.json();
```

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

- Verifica credenziali in `api/config/database.php`
- Controlla che il database esista su Aruba
- Verifica che l'utente abbia permessi sul database

### Errore: "Token non valido"

- Token scaduto (2 ore). Rifare login
- JWT_SECRET modificato dopo login
- Verifica che `localStorage` non sia bloccato

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
✅ Sicurezza avanzata (JWT + MFA)
✅ Dashboard professionale
✅ Gestione fatture e servizi
✅ Log audit completi
✅ Pronto per produzione!
