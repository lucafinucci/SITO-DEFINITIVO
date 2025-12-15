# 🚀 QUICK START - Test Locale Area Clienti

## Requisiti
- XAMPP/WAMP/MAMP installato
- Browser web

---

## 📋 Step by Step (5 minuti)

### 1. Installa XAMPP
Download: https://www.apachefriends.org/download.html
- Installa Apache + MySQL + PHP

### 2. Avvia Servizi
- Apri XAMPP Control Panel
- Clicca "Start" su **Apache**
- Clicca "Start" su **MySQL**

### 3. Crea Database
1. Apri browser: `http://localhost/phpmyadmin`
2. Clicca "Nuovo" (sidebar sinistra)
3. Nome database: `finch_ai_clienti`
4. Clicca "Crea"

### 4. Configura Progetto
1. Copia la cartella `SITO/public` in:
   ```
   C:\xampp\htdocs\finch-ai\
   ```

2. Copia anche la cartella `SITO/database` in:
   ```
   C:\xampp\htdocs\finch-ai\
   ```

3. Modifica il file:
   ```
   C:\xampp\htdocs\finch-ai\public\api\config\database.php
   ```

   Cambia così:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'finch_ai_clienti');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Lascia vuoto per XAMPP
   define('JWT_SECRET', 'test-secret-key-123456');
   ```

### 5. Inizializza Database
Apri nel browser:
```
http://localhost/finch-ai/database/init.php
```

Vedrai una pagina verde di conferma con le credenziali demo.

### 6. Testa il Login!
```
http://localhost/finch-ai/public/area-clienti.html
```

**Credenziali:**
- Email: `demo@finch-ai.it`
- Password: `Demo123!`
- OTP: Lascia vuoto

Dopo il login, verrai reindirizzato alla **Dashboard**!

---

## 📍 URL da Visitare

| Pagina | URL |
|--------|-----|
| Area Clienti (Login) | http://localhost/finch-ai/public/area-clienti.html |
| Dashboard | http://localhost/finch-ai/public/dashboard.html |
| Setup MFA | http://localhost/finch-ai/public/mfa-setup.html |
| Inizializzazione DB | http://localhost/finch-ai/database/init.php |

---

## 🐛 Problemi Comuni

### "Database connection failed"
✅ Verifica che MySQL sia avviato in XAMPP
✅ Verifica le credenziali in `database.php`

### "404 Not Found"
✅ Controlla che i file siano in `C:\xampp\htdocs\finch-ai\`
✅ URL deve iniziare con `http://localhost/finch-ai/...`

### "Headers already sent"
✅ Rimuovi spazi vuoti prima di `<?php` nei file API

---

## ✅ Test Funzionamento

1. **Login** → Ricevi token JWT ✓
2. **Dashboard** → Vedi statistiche e tabelle ✓
3. **Download Fattura** → Scarica PDF demo ✓
4. **Setup MFA** → Vedi QR code Google Authenticator ✓

---

## 🎯 Dopo il Test

Quando tutto funziona in locale:
1. Carica su Aruba via FTP
2. Configura database MySQL Aruba
3. Modifica credenziali in `database.php`
4. Esegui `init.php` su Aruba
5. **Elimina** `init.php` per sicurezza

---

**Buon test! 🚀**
