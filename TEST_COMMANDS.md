# 🧪 Test Commands - Area Clienti

Comandi utili per testare le funzionalità implementate.

---

## 🚀 Quick Test

### Test Automatico Completo
```
http://localhost/test-security-features.php
```
Apri questa URL nel browser per eseguire tutti i test automatici.

---

## 📋 Test Manuali

### 1. Test Login Base
```
1. Vai su: http://localhost/area-clienti/login.php
2. Inserisci: demo@finch-ai.it / Demo123!
3. Verifica redirect a dashboard
4. Verifica KPI caricati
```

### 2. Test CSRF Protection
```
1. Apri login.php
2. Apri DevTools (F12) > Console
3. Esegui:
   fetch('/area-clienti/login.php', {
     method: 'POST',
     headers: {'Content-Type': 'application/x-www-form-urlencoded'},
     body: 'email=test@test.it&password=test'
   }).then(r => r.text()).then(console.log)

4. Risultato atteso: "Token di sicurezza non valido"
```

### 3. Test Rate Limiting
```
1. Vai su login.php
2. Inserisci email valida + password ERRATA
3. Ripeti 5 volte
4. Al 6° tentativo: "Troppi tentativi. Riprova tra X minuti"
5. Attendi 15 minuti O cancella sessione browser
6. Riprova: dovrebbe funzionare
```

### 4. Test MFA Setup
```
1. Login con utente esistente
2. Vai su Profilo
3. Click "Attiva MFA"
4. Scansiona QR con Google Authenticator
5. Inserisci codice 6 cifre
6. Verifica "MFA attivato"
7. Logout
8. Login: richiede OTP
9. Inserisci codice dall'app
10. Accesso OK
```

### 5. Test Cache KPI
```
1. Login
2. Vai su Dashboard
3. Apri DevTools > Network
4. Ricarica pagina
5. Cerca chiamata: kpi-proxy.php
6. Prima chiamata: "cached": false
7. Ricarica entro 5 minuti
8. Seconda chiamata: "cached": true
9. Aspetta 6 minuti e ricarica
10. Terza chiamata: "cached": false (nuova fetch)
```

### 6. Test Input Validation
```
# Email invalida
1. Login con: "notanemail"
2. Risultato: "Email non valida"

# Password troppo corta
1. Profilo > Cambia Password
2. Nuova password: "test"
3. Risultato: "Almeno 8 caratteri"

# Password senza numeri
1. Nuova password: "testtesttest"
2. Risultato: "Deve contenere almeno un numero"
```

### 7. Test Error Logging
```
1. Vai su: http://localhost/area-clienti/login.php
2. Fai un login (successo o fallimento)
3. Controlla file:
   c:\Users\oneno\Desktop\SITO\logs\access.log
4. Verifica presenza log entry
```

---

## 🔍 Debug Commands

### Check .env Configuration
```bash
# Windows CMD
type .env

# PowerShell
Get-Content .env

# Linux/Mac
cat .env
```

### Check Database Connection
```bash
# Da terminale MySQL
mysql -u root -p

USE finch_ai_clienti;
SHOW TABLES;
SELECT * FROM utenti LIMIT 1;
```

### Check Logs
```bash
# Windows PowerShell
Get-Content logs\error.log -Tail 20
Get-Content logs\access.log -Tail 20

# Linux/Mac
tail -20 logs/error.log
tail -20 logs/access.log

# Live monitoring
tail -f logs/error.log
```

### Check Cache
```bash
# Windows
dir cache

# Linux/Mac
ls -lah cache/
```

### Clear Cache Manualmente
```bash
# Windows
del /q cache\*.cache

# Linux/Mac
rm cache/*.cache
```

---

## 🐛 Test Error Scenarios

### Test Database Error
```php
// Modifica temporanea db.php
DB_PASS=wrong_password

// Vai su login.php
// Risultato atteso:
// - Production: Pagina errore generica
// - Debug: Dettagli errore connessione
```

### Test Missing .env
```bash
# Rinomina temporaneamente
mv .env .env.bak

# Vai su login.php
# Risultato: Usa valori di default
# Ripristina
mv .env.bak .env
```

### Test Invalid CSRF Token
```javascript
// DevTools Console
fetch('/area-clienti/profilo.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: 'csrf_token=invalid&nome=Test&cognome=User&email=test@test.it&update_profile=1'
}).then(r => r.text()).then(html => {
  console.log(html.includes('Token di sicurezza non valido'));
});
```

---

## 📊 Performance Testing

### Test KPI Cache Performance
```javascript
// DevTools Console
console.time('KPI Load');
fetch('/area-clienti/api/kpi-proxy.php')
  .then(r => r.json())
  .then(data => {
    console.timeEnd('KPI Load');
    console.log('Cached:', data.cached);
    console.log('Data:', data.data);
  });

// Prima chiamata: ~200-500ms (no cache)
// Seconda chiamata: <50ms (cached)
```

### Test Dashboard Load Time
```javascript
// DevTools > Network > Disable cache
// Ricarica dashboard
// Check waterfall:
// - HTML: <100ms
// - CSS: <50ms
// - JS: <100ms
// - KPI API: <100ms (cached) o ~500ms (no cache)
// Total: <500ms
```

---

## 🔐 Security Audit

### Check Session Security
```javascript
// DevTools > Application > Cookies
// Verifica cookie di sessione:
// - HttpOnly: ✓
// - Secure: ✓ (se HTTPS)
// - SameSite: Lax
```

### Check HTTPS (Production)
```bash
# Da browser
# Verifica lucchetto verde nella barra indirizzi
# Controlla certificato SSL
```

### Check Headers
```bash
# DevTools > Network > login.php > Headers
# Verifica:
# - X-Content-Type-Options: nosniff
# - X-Frame-Options: DENY
# (se configurato in .htaccess)
```

---

## 🎯 SQL Queries per Testing

### Verifica MFA Utente
```sql
SELECT id, email, mfa_enabled, mfa_secret
FROM utenti
WHERE email = 'demo@finch-ai.it';
```

### Verifica Access Logs
```sql
SELECT * FROM access_logs
ORDER BY created_at DESC
LIMIT 10;
```

### Reset MFA per Utente
```sql
UPDATE utenti
SET mfa_enabled = FALSE, mfa_secret = NULL
WHERE email = 'demo@finch-ai.it';
```

### Check Servizi Attivi
```sql
SELECT u.email, s.nome, us.stato, us.data_attivazione
FROM utenti_servizi us
JOIN utenti u ON us.user_id = u.id
JOIN servizi s ON us.servizio_id = s.id
WHERE us.stato = 'attivo';
```

### Aggiungi Utente Test
```sql
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, attivo)
VALUES (
  'test@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
  'Test',
  'User',
  'Test Company',
  TRUE
);
```

---

## 🧹 Cleanup Commands

### Reset Test Data
```sql
-- Reset access logs
DELETE FROM access_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Reset test user
DELETE FROM utenti WHERE email LIKE '%test%';
```

### Clear All Cache
```php
<?php
require 'area-clienti/includes/cache.php';
Cache::clear();
echo "Cache cleared!";
```

### Clear Expired Cache Only
```php
<?php
require 'area-clienti/includes/cache.php';
$deleted = Cache::clearExpired();
echo "Deleted {$deleted} expired entries";
```

---

## 📱 Mobile Testing

### Responsive Test
```
1. DevTools > Toggle device toolbar (Ctrl+Shift+M)
2. Seleziona device: iPhone 12, iPad, Galaxy S20
3. Test login flow
4. Test dashboard display
5. Test MFA QR code visibility
```

### Real Device Test
```
1. Stesso WiFi di dev server
2. Trova IP locale: ipconfig (Win) / ifconfig (Mac/Linux)
3. Mobile browser: http://192.168.X.X/area-clienti/login.php
4. Test completo flusso
```

---

## ✅ Test Checklist

Prima di deploy in produzione:

- [ ] Test automatico (`test-security-features.php`) tutto verde
- [ ] Login funzionante
- [ ] CSRF protection attivo su tutti i form
- [ ] Rate limiting testato (5 tentativi)
- [ ] MFA setup e login funzionante
- [ ] Cache KPI funzionante
- [ ] Validation input funzionante
- [ ] Error logging attivo
- [ ] `.env` configurato correttamente
- [ ] `.env` NON committato su git
- [ ] `APP_DEBUG=false` in produzione
- [ ] HTTPS attivo (produzione)
- [ ] Backup database fatto
- [ ] Logs directory writable

---

## 📞 Support

Problemi con i test?
- Email: supporto@finch-ai.it
- Docs: `SECURITY_IMPROVEMENTS.md`
- Logs: `logs/error.log` e `logs/access.log`

---

© 2024 Finch-AI - Test Suite v1.0
