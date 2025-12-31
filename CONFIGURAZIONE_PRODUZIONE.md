# Configurazione Area Clienti - Produzione

## ✅ Problemi Critici Risolti

Tutti i problemi critici identificati sono stati risolti. Di seguito le modifiche effettuate:

### 1. API Key Spostate in .env ✅
- **File modificati**: `kpi-proxy.php`, `.env`
- Le API key non sono più hardcoded nel codice
- Configurare in `.env`:
  ```env
  KPI_API_KEY=your_actual_api_key_here
  KPI_API_TOKEN=your_secure_token_here
  ```

### 2. Directory Upload Protetta ✅
- **File creati**:
  - `uploads/training/.htaccess` (blocca accesso HTTP)
  - `uploads/training/index.php` (impedisce listing)
  - `uploads/training/README.md` (documentazione)
- **File modificati**: `upload-training.php`
- Path upload ora configurabile da `.env`

### 3. Error Handler Riattivato ✅
- **File modificato**: `error-handler.php`
- Rimosso bypass temporaneo
- Debug mode controllato da `APP_DEBUG` in `.env`
- In produzione gli errori vengono loggati ma non mostrati

### 4. Logging Email Implementato ✅
- **File modificati**:
  - `upload-training.php`
  - `richiedi-addestramento.php`
- Rimosso operatore `@` di soppressione
- Aggiunto logging successo/fallimento invio email

### 5. File Test e Backup Rimossi ✅
- Rimosso `TEST_UPLOAD.html`
- Rimosso `document-intelligence.php.BACKUP`

---

## 📋 Checklist Deployment Produzione

### Step 1: Configurazione .env

Modificare il file `.env` con i valori di produzione:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=finch_ai_clienti
DB_USER=your_db_user
DB_PASS=your_secure_password

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_LIFETIME=3600

# Rate Limiting
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900

# API Configuration
KPI_API_ENDPOINT=https://app.finch-ai.it/api/kpi/documenti
KPI_API_KEY=your_production_api_key
KPI_API_TOKEN=your_production_token

# Email Configuration
TRAINING_EMAIL=ai-training@finch-ai.it

# Upload Configuration (IMPORTANTE per Aruba)
# Usare path ASSOLUTO fuori da public_html
UPLOAD_BASE_DIR=/home/finch-ai/uploads/training

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://finch-ai.it

# MFA/TOTP
MFA_ISSUER=Finch-AI
MFA_DIGITS=6
MFA_PERIOD=30
```

### Step 2: Directory Upload su Aruba

**IMPORTANTE**: Su hosting Aruba, posizionare gli upload FUORI da `public_html`:

```bash
# Via SSH su Aruba
cd /home/finch-ai
mkdir -p uploads/training
chmod 755 uploads/training

# Copia .htaccess e index.php
cp public_html/uploads/training/.htaccess uploads/training/
cp public_html/uploads/training/index.php uploads/training/
```

### Step 3: Database

Verificare che tutte le tabelle esistano:

```sql
-- Tabelle richieste
- utenti
- servizi
- utenti_servizi
- richieste_addestramento
- richieste_addestramento_files
- modelli_addestrati
- access_logs
```

Se mancano, eseguire gli script di creazione database.

### Step 4: Permessi File

```bash
# Directory
chmod 755 area-clienti
chmod 755 area-clienti/includes
chmod 755 area-clienti/api
chmod 755 logs

# File sensibili
chmod 600 .env
chmod 644 area-clienti/*.php
chmod 644 area-clienti/includes/*.php
chmod 644 area-clienti/api/*.php

# Directory logs deve essere scrivibile
chmod 755 logs
```

### Step 5: SSL/HTTPS

Verificare che SSL sia attivo e configurato correttamente:

- Certificato SSL valido installato
- Redirect HTTP → HTTPS attivo
- Cookie secure funzionanti

### Step 6: Email SMTP

Configurare SMTP per invio email (opzionale ma consigliato):

Aruba fornisce SMTP, aggiornare `.env` con:
```env
SMTP_HOST=smtps.aruba.it
SMTP_PORT=465
SMTP_USER=noreply@finch-ai.it
SMTP_PASS=your_smtp_password
```

Poi modificare le funzioni `mail()` per usare SMTP.

### Step 7: Test Pre-Deploy

Prima del deploy finale, testare:

- [ ] Login con credenziali test
- [ ] Login con MFA attivo
- [ ] Upload file training (verificare salvataggio)
- [ ] Invio email notifica
- [ ] Dashboard KPI (anche con API non configurata = mock data)
- [ ] Rate limiting (5 tentativi falliti)
- [ ] Redirect da document-intelligence.php obsoleto
- [ ] Accesso diretto a `/uploads/training/` (deve essere negato)

### Step 8: Monitoring

Dopo il deploy, monitorare:

```bash
# Log errori
tail -f logs/error.log

# Log accessi
tail -f logs/access.log

# Log Apache (se disponibile)
tail -f /var/log/apache2/error.log
```

---

## 🔒 Sicurezza Implementata

L'Area Clienti include già:

✅ **Autenticazione**
- Password hash con bcrypt
- MFA/2FA con TOTP (Google Authenticator)
- Session secure cookies

✅ **Protezione Attacchi**
- CSRF token su tutti i form
- Rate limiting su login (5 tentativi / 15 min)
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars)
- File upload validation (tipo, dimensione)

✅ **Logging**
- Access logs (tentativi login, azioni utente)
- Error logs (errori applicazione)
- Email logs (invii riusciti/falliti)

---

## 🚨 Problemi Noti Minori

### 1. Google Charts API per QR Code (PRIORITÀ BASSA)
**File**: `totp.php:86`

Google Charts API è deprecata. Considerare in futuro:
```bash
composer require endroid/qr-code
```

### 2. KPI Dati Statici (PRIORITÀ MEDIA)
**File**: `servizio-dettaglio.php`

I KPI nella dashboard sono hardcoded. Per usare dati reali:
1. Configurare `KPI_API_KEY` e `KPI_API_TOKEN` in `.env`
2. Implementare API endpoint sul server `app.finch-ai.it`

---

## 📞 Supporto

Per problemi o domande:
- Email: supporto@finch-ai.it
- Documentazione: `/area-clienti/README.md`

---

**Ultimo aggiornamento**: 2025-12-16
**Versione**: 1.1
