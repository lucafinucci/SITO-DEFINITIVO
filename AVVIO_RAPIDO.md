# 🚀 GUIDA AVVIO RAPIDO - Finch-AI

## ✅ PREREQUISITI COMPLETATI

- ✅ File copiati in `C:\xampp\htdocs\SITO\`
- ✅ Proxy Vite configurato
- ✅ XAMPP Apache e MySQL attivi

---

## 📋 PROCEDURA COMPLETA

### **STEP 1: Inizializza Database** 🗄️

1. Apri il browser
2. Vai a: **`http://localhost/SITO/fix-and-init-db.php`**
3. Verifica che vedi:
   - ✓ Connessione MySQL riuscita
   - ✓ Database 'finch_ai_clienti' creato
   - ✓ 7 tabelle create
   - ✓ 3 utenti demo inseriti

**IMPORTANTE**: Questo passaggio va fatto UNA SOLA VOLTA!

---

### **STEP 2: Avvia Server React** ⚛️

Apri un terminale nella cartella del progetto:

```bash
cd c:\Users\oneno\Desktop\SITO
npm run dev
```

Vedrai:
```
  VITE v5.4.2  ready in 324 ms
  ➜  Local:   http://localhost:5173/
```

**LASCIA QUESTO TERMINALE APERTO!**

---

### **STEP 3: Testa il Sito** 🌐

1. **Sito Principale**:
   - Apri: `http://localhost:5173`
   - Verifica che si carica correttamente

2. **Test Area Clienti**:
   - Clicca sul pulsante **"Area Clienti"** nel menu
   - Dovrebbe aprire la pagina di login

3. **Login di Test**:
   - **Email**: `demo@finch-ai.it`
   - **Password**: `Demo123!`
   - Clicca "Accedi"
   - Dovresti vedere la dashboard

---

## 🔗 URL IMPORTANTI

| Componente | URL |
|------------|-----|
| **Sito React** | `http://localhost:5173` |
| **Area Clienti** | `http://localhost:5173/area-clienti/login.php` |
| **Init Database** | `http://localhost/SITO/fix-and-init-db.php` |
| **phpMyAdmin** | `http://localhost/phpmyadmin` |

---

## 👥 CREDENZIALI DEMO

| Email | Password | Ruolo |
|-------|----------|-------|
| `admin@finch-ai.it` | `Admin123!` | Admin |
| `demo@finch-ai.it` | `Demo123!` | Cliente |
| `cliente@example.com` | `Cliente123!` | Cliente |

---

## 🔧 COME FUNZIONA IL PROXY

Grazie al proxy Vite configurato in `vite.config.js`:

```javascript
server: {
  proxy: {
    '/area-clienti': {
      target: 'http://localhost',  // Apache porta 80
      changeOrigin: true,
    },
  },
}
```

Quando clicchi "Area Clienti" da `localhost:5173`, Vite inoltra automaticamente la richiesta ad Apache sulla porta 80, quindi tutto funziona come un unico sito!

---

## ⚠️ TROUBLESHOOTING

### Problema: "Database connection failed"
**Soluzione**:
- Verifica che XAMPP MySQL sia avviato
- Riesegui: `http://localhost/SITO/fix-and-init-db.php`

### Problema: "404 Not Found" sull'area clienti
**Soluzione**:
- Verifica che XAMPP Apache sia avviato
- Verifica che i file siano in `C:\xampp\htdocs\SITO\area-clienti\`

### Problema: Vite non si avvia
**Soluzione**:
```bash
# Reinstalla dipendenze
npm install

# Riavvia server
npm run dev
```

---

## 🎯 CHECKLIST RAPIDA

Prima di iniziare a lavorare:

- [ ] XAMPP Apache avviato (porta 80)
- [ ] XAMPP MySQL avviato (porta 3306)
- [ ] Database inizializzato (una volta sola)
- [ ] `npm run dev` in esecuzione
- [ ] Browser aperto su `http://localhost:5173`

---

## 🛑 COME FERMARE TUTTO

1. **Terminale Vite**: Premi `Ctrl + C`
2. **XAMPP**: Clicca "Stop" su Apache e MySQL nel Control Panel

---

**✨ Setup completato! Buon lavoro!**
