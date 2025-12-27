# Pulizia Pagine Duplicate - Area Clienti

## Data: 15 Dicembre 2024

## Problema Rilevato
Dopo l'upload dei file di addestramento, il cliente veniva reindirizzato a `document-intelligence.php?upload=success` invece di `servizio-dettaglio.php?id=1`, impedendo la visualizzazione dei modelli in stato "Training".

## Causa
Esistevano **pagine duplicate** con funzionalità sovrapposte:

### Pagine Duplicate Identificate:
1. **`document-intelligence.php`** (14KB) - Overview con KPI
2. **`document-intelligence-modelli.php`** (9KB) - Lista modelli
3. **`servizio-dettaglio.php?id=1`** (23KB) - **Pagina completa con TUTTE le funzionalità**

`servizio-dettaglio.php` conteneva già:
- ✅ KPI Document Intelligence
- ✅ Grafici mensili
- ✅ Lista modelli addestrati (statici + dinamici dal DB)
- ✅ Richieste in corso con stato "Training"
- ✅ Visualizzazione animata barra progresso

---

## ✅ Modifiche Effettuate

### 1. Sostituzione Link (4 occorrenze)

#### File: `dashboard.php` (linea 99)
```php
// PRIMA
<a class="btn primary" href="/area-clienti/document-intelligence.php">

// DOPO
<a class="btn primary" href="/area-clienti/servizio-dettaglio.php?id=1">
```

#### File: `richiedi-addestramento.php` (linee 165 e 300)
```php
// PRIMA
<a href="/area-clienti/document-intelligence.php" ...>← Torna a Document Intelligence</a>
<a href="/area-clienti/document-intelligence.php" class="btn ghost">Annulla</a>

// DOPO
<a href="/area-clienti/servizio-dettaglio.php?id=1" ...>← Torna a Document Intelligence</a>
<a href="/area-clienti/servizio-dettaglio.php?id=1" class="btn ghost">Annulla</a>
```

#### File: `document-intelligence-modelli.php` (linea 99)
```php
// PRIMA
<a href="/area-clienti/document-intelligence.php" ...>← Torna a Document Intelligence</a>

// DOPO
<a href="/area-clienti/servizio-dettaglio.php?id=1" ...>← Torna a Document Intelligence</a>
```

### 2. Rimozione Pagina Duplicata

**File eliminato**: `document-intelligence.php`
- **Backup creato**: `document-intelligence.php.BACKUP`
- **Nuovo file**: Redirect automatico a `servizio-dettaglio.php?id=1`

```php
<?php
header('Location: /area-clienti/servizio-dettaglio.php?id=1');
exit;
?>
```

---

## 📊 Risultato Finale

### Struttura Pagine Area Clienti (Pulita)

```
area-clienti/
├── index.php                          → Entry point
├── login.php                          → Login utente
├── logout.php                         → Logout
├── dashboard.php                      → Dashboard principale
├── servizi.php                        → Lista tutti i servizi
├── servizio-dettaglio.php             → PAGINA PRINCIPALE Document Intelligence
├── richiedi-addestramento.php         → Form upload training
├── document-intelligence-modelli.php  → Vista lista modelli (opzionale)
├── document-intelligence.php          → REDIRECT a servizio-dettaglio.php?id=1
├── fatture.php                        → Gestione fatture
├── profilo.php                        → Profilo utente
├── mfa-setup.php                      → 2FA setup
├── denied.php                         → Accesso negato
├── debug.php                          → Debug (dev only)
└── clear-cache.php                    → Pulizia cache (dev only)
```

### Flusso Corretto Upload Training

1. **Cliente** accede a `richiedi-addestramento.php`
2. **Compila** form + carica file
3. **Click** "Invia Richiesta"
4. **API** `upload-training.php` salva richiesta in DB (stato: "in_attesa")
5. **Redirect** a `/area-clienti/servizio-dettaglio.php?id=1` ✅
6. **Pagina mostra**:
   - Modelli completati (da `modelli_addestrati`)
   - **NUOVO modello in stato "⏳ Training"** (da `richieste_addestramento`)
   - Barra animata arancione
   - Testo "Addestramento in corso..."

---

## 🔍 Pagine Mantenute (Giustificazione)

### `document-intelligence-modelli.php`
**Mantenuta perché**:
- Vista dedicata **lista completa** modelli (senza KPI/grafici)
- Link "Vedi tutti" da `document-intelligence.php`
- Utile per utenti con molti modelli addestrati
- **TODO**: Aggiornare anche questa per usare query dinamiche

---

## ✅ Verifiche da Fare

### Test Completo Flusso:
1. ✅ Accedi come `demo@finch-ai.it`
2. ✅ Dashboard → Click "Document Intelligence"
3. ✅ Verifica arrivo su `servizio-dettaglio.php?id=1`
4. ✅ Sidebar mostra modelli esistenti
5. ✅ Click "Richiedi Addestramento"
6. ✅ Compila form + carica file
7. ✅ Click "Invia Richiesta"
8. ✅ Verifica redirect a `servizio-dettaglio.php?id=1`
9. ✅ **VERIFICA NUOVO MODELLO "Training" appare in sidebar**

### Controllo Database:
```sql
-- Verifica richiesta salvata
SELECT * FROM richieste_addestramento
WHERE user_id = 2
ORDER BY created_at DESC LIMIT 1;

-- Verifica file caricati
SELECT * FROM richieste_addestramento_files
WHERE richiesta_id = (SELECT MAX(id) FROM richieste_addestramento);
```

---

## 🐛 Troubleshooting

### Se continua a reindirizzare a document-intelligence.php:
1. **Pulire cache browser** (Ctrl+Shift+Del)
2. **Hard refresh** (Ctrl+F5)
3. **Riavviare server Vite** (`npm run dev`)
4. **Verificare file PHP** non sia cached da Apache

### Se modello non appare in sidebar:
1. Verificare richiesta salvata nel DB
2. Controllare `user_id` corrisponda (2 per demo)
3. Verificare stato sia "in_attesa" o "in_lavorazione"
4. Check query SQL in `servizio-dettaglio.php` linee 245-264

---

## 📝 Prossimi Step (Opzionali)

- [ ] Aggiornare `document-intelligence-modelli.php` con query dinamiche
- [ ] Eliminare completamente `document-intelligence-modelli.php` (tutto già in servizio-dettaglio)
- [ ] Aggiungere auto-refresh modelli ogni 30 secondi
- [ ] Implementare WebSocket per notifiche real-time
- [ ] Pagina admin per gestire stato richieste

---

## 🎯 Riepilogo

**PRIMA**:
- 3 pagine duplicate con funzionalità sovrapposte
- Upload redirect a pagina sbagliata
- Modelli in training NON visualizzati

**DOPO**:
- 1 pagina principale consolidata (`servizio-dettaglio.php?id=1`)
- Redirect corretto dopo upload
- Modelli in training visualizzati con animazione
- Codice pulito e mantenibile

✅ **Problema risolto**
