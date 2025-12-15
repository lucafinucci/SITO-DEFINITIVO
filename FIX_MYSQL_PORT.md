# 🔧 Fix MySQL Port Conflict

## Problema
MySQL non si avvia in XAMPP perché la porta 3306 è occupata.

## ✅ Soluzione Rapida

### Opzione 1: Ferma il servizio MySQL in conflitto

1. **Apri CMD come Amministratore**
2. Esegui:
   ```cmd
   net stop MySQL80
   ```
   O se hai MariaDB:
   ```cmd
   net stop MariaDB
   ```

3. **Riavvia MySQL in XAMPP**
   - Clicca "Start" nel pannello XAMPP

---

### Opzione 2: Cambia porta MySQL in XAMPP

1. **In XAMPP clicca "Config" accanto a MySQL**
2. Seleziona **"my.ini"**
3. Cerca la riga:
   ```
   port=3306
   ```
4. Cambia in:
   ```
   port=3307
   ```
5. **Salva il file**
6. **Aggiorna .env:**
   ```env
   DB_HOST=127.0.0.1:3307
   ```
7. **Riavvia MySQL in XAMPP**

---

### Opzione 3: Usa il MySQL che è già running

Se vedi che c'è già un MySQL running (nella lista sotto in XAMPP), usa quello!

**Aggiorna solo il .env:**
```env
DB_HOST=127.0.0.1
DB_NAME=finch_ai_clienti
DB_USER=root
DB_PASS=
```

Poi esegui `SETUP_DATABASE.php` nel browser.

---

## ✅ Verifica quale MySQL è attivo

Apri CMD e esegui:
```cmd
netstat -ano | findstr :3306
```

Se vedi un risultato, significa che la porta 3306 è occupata.

Per vedere il processo:
```cmd
tasklist | findstr mysql
```

---

## 🎯 La soluzione PIÙ SEMPLICE

**Usa il MySQL che hai già installato nel sistema** invece di quello di XAMPP:

1. Non avviare MySQL in XAMPP
2. Lascia il .env così com'è
3. Esegui `SETUP_DATABASE.php` - dovrebbe funzionare con il MySQL di sistema

---

## 📞 Se nulla funziona

Dimmi quale di questi comandi ti dà risultato:

```cmd
mysql --version
```

Se funziona, hai MySQL installato e puoi usare quello!
