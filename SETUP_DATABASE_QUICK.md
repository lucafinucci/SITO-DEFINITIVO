# 🚀 Setup Database Veloce

## Problema
Errore "Errore connessione DB" quando accedi all'Area Clienti.

## Causa
Il database `finch_ai_clienti` non esiste ancora.

## ✅ Soluzione Rapida

### Opzione 1: Via phpMyAdmin (CONSIGLIATO)

1. **Apri phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Clicca su "New" (Nuovo) nella sidebar sinistra**

3. **Nome database:**
   ```
   finch_ai_clienti
   ```

4. **Collation:**
   ```
   utf8mb4_unicode_ci
   ```

5. **Clicca "Create"**

6. **Importa schema:**
   - Clicca sul database appena creato
   - Tab "Import" (Importa)
   - Scegli file: `database/schema.sql`
   - Clicca "Go"

7. **FATTO!** Ora ricarica: `http://localhost:5173/area-clienti/login.php`

---

### Opzione 2: Via Script PHP (se phpMyAdmin non funziona)

Apri nel browser:
```
http://localhost:5173/SETUP_DATABASE.php
```

---

### Opzione 3: Via Command Line

Se hai accesso MySQL command line:

```bash
# Windows PowerShell
cd C:\xampp\mysql\bin
.\mysql.exe -u root

# Poi esegui:
CREATE DATABASE finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finch_ai_clienti;
SOURCE C:/Users/oneno/Desktop/SITO/database/schema.sql;
exit;
```

---

## ⚠️ Problemi Comuni

### "Host 'localhost' is not allowed to connect"

Usa invece di `localhost`:
- Prova `127.0.0.1` nel file `.env`:
  ```env
  DB_HOST=127.0.0.1
  ```

### "Access denied for user 'root'@'localhost'"

Il root ha una password. Nel `.env` aggiungi:
```env
DB_PASS=tua_password_root
```

---

## ✅ Verifica Funzionamento

Dopo aver creato il database:

1. Vai su: `http://localhost:5173/area-clienti/login.php`
2. Non dovrebbe più dare "Errore connessione DB"
3. Se funziona, procedi con login demo

---

## 🆘 Aiuto

Se continua a non funzionare, controlla:
- XAMPP MySQL/MariaDB è avviato?
- File `.env` è nella root del progetto?
- Permessi corretti su `.env`?
