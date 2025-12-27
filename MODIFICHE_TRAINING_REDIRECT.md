# Modifiche: Redirect dopo Upload Training + Visualizzazione Stato "Training"

## Data: 15 Dicembre 2024

## Obiettivo
Dopo aver cliccato "Invia File" il cliente deve essere reindirizzato alla pagina `servizio-dettaglio.php?id=1` e nella lista dei "Modelli Addestrati" deve comparire il nuovo modello con stato "Training" per il servizio relativo.

---

## ✅ Modifiche Implementate

### 1. **Redirect dopo Upload**
**File**: `area-clienti/richiedi-addestramento.php`
**Linea**: 444

**Modifica**:
```javascript
// PRIMA
window.location.href = '/area-clienti/document-intelligence.php?upload=success';

// DOPO
window.location.href = '/area-clienti/servizio-dettaglio.php?id=1';
```

**Effetto**: Dopo l'upload con successo, l'utente viene reindirizzato alla pagina di dettaglio del servizio Document Intelligence (ID=1).

---

### 2. **Recupero Dinamico Modelli dal Database**
**File**: `area-clienti/servizio-dettaglio.php`
**Linee**: 216-298

**Modifica**: Sostituito l'array statico con query dinamiche al database:

1. **Recupera modelli completati** dalla tabella `modelli_addestrati`
2. **Recupera richieste in corso** dalla tabella `richieste_addestramento` con stato "in_attesa" o "in_lavorazione"
3. Le richieste in corso vengono mostrate come modelli con stato `"training"`

```php
// Modelli completati
SELECT nome_modello, tipo_modello, accuratezza, num_documenti_addestramento
FROM modelli_addestrati
WHERE user_id = :user_id AND attivo = 1

// Richieste in corso (mostrate come "Training")
SELECT CONCAT(tipo_modello, " - In Addestramento") as nome, tipo_modello, ...
FROM richieste_addestramento
WHERE user_id = :user_id AND stato IN ("in_attesa", "in_lavorazione")
```

---

### 3. **Visualizzazione Stato "Training"**
**File**: `area-clienti/servizio-dettaglio.php`
**Linee**: 317-348

**Modifica**: Aggiunta logica condizionale per mostrare:

#### Per modelli in Training:
- Badge giallo "⏳ Training"
- Testo "Addestramento in corso..."
- Barra di progresso animata (arancione con animazione pulsante)
- Numero documenti stimati
- Data/ora richiesta

#### Per modelli completati:
- Badge verde "✓ Attivo"
- Percentuale di accuratezza
- Barra di progresso verde/cyan
- Numero documenti di addestramento
- Data ultima versione

**CSS Animation aggiunta**:
```css
@keyframes training-pulse {
  0%, 100% { width: 30%; opacity: 0.6; }
  50% { width: 70%; opacity: 1; }
}
```

---

## 🗄️ Struttura Database Utilizzata

### Tabella: `richieste_addestramento`
```sql
- id
- user_id (FK -> utenti)
- tipo_modello (varchar)
- descrizione (text)
- num_documenti_stimati (int)
- stato (ENUM: 'in_attesa', 'in_lavorazione', 'completato', 'annullato')
- created_at
- updated_at
```

### Tabella: `modelli_addestrati`
```sql
- id
- user_id (FK -> utenti)
- richiesta_id (FK -> richieste_addestramento, nullable)
- nome_modello (varchar)
- tipo_modello (varchar)
- accuratezza (decimal 5,2)
- num_documenti_addestramento (int)
- attivo (boolean)
- created_at
- updated_at
```

### Tabella: `richieste_addestramento_files`
```sql
- id
- richiesta_id (FK)
- filename_originale
- filename_storage
- file_path
- file_size
- uploaded_at
```

---

## 📊 Stato Attuale Database

### Richieste di Addestramento (3 totali):
```
ID | user_id | tipo_modello | stato      | created_at
3  | 2       | bolle        | in_attesa  | 2025-12-15 22:03:16
2  | 1       | bolle        | in_attesa  | 2025-12-10 00:43:15
1  | 1       | bolle        | in_attesa  | 2025-12-10 00:41:43
```

### Modelli Addestrati (4 totali):
```
ID | user_id | nome_modello                    | accuratezza | attivo
1  | 2       | Fatture Elettroniche v2.1       | 98.50%     | ✓
2  | 2       | DDT & Bolle di Trasporto        | 96.20%     | ✓
3  | 2       | Contratti Commerciali           | 97.80%     | ✓
4  | 1       | Fatture Fornitori               | 99.10%     | ✓
```

---

## 🔄 Flusso Completo

1. **Cliente accede** a `/area-clienti/richiedi-addestramento.php`
2. **Compila il form** con:
   - Tipo di modello (es: "Fatture Elettroniche")
   - Descrizione requisiti
   - Numero documenti
   - Upload file (PDF/PNG/JPG)
3. **Click "Invia File"**:
   - JavaScript cattura submit
   - Upload via XMLHttpRequest a `/area-clienti/api/upload-training.php`
   - API salva richiesta in `richieste_addestramento` (stato: "in_attesa")
   - API salva file in `richieste_addestramento_files`
   - API invia email notifica al team
4. **Redirect automatico** a `/area-clienti/servizio-dettaglio.php?id=1`
5. **Pagina servizio mostra**:
   - Modelli completati (dalla tabella `modelli_addestrati`)
   - **NUOVA richiesta in stato "⏳ Training"** (dalla tabella `richieste_addestramento`)

---

## ✅ Test Manuale

### Per testare il flusso:

1. Accedi come utente demo:
   - Email: `demo@finch-ai.it`
   - Password: `Demo123!`

2. Vai a: `http://localhost/area-clienti/servizio-dettaglio.php?id=1`

3. Click su "🔄 Richiedi Addestramento" per qualsiasi modello

4. Compila form e carica file

5. **Verifica**:
   - Dopo upload, sei su `servizio-dettaglio.php?id=1`
   - Nella sidebar "🧠 Modelli AI Addestrati" appare il nuovo modello con:
     - Badge giallo "⏳ Training"
     - Barra animata arancione
     - Testo "Addestramento in corso..."

---

## 🎨 UI/UX Miglioramenti

### Badge Stati:
- **Verde** `✓ Attivo` → Modelli completati e funzionanti
- **Giallo** `⏳ Training` → Modelli in addestramento

### Progress Bar:
- **Verde/Cyan** → Modelli attivi (mostra accuratezza)
- **Arancione pulsante** → Modelli in training (animazione)

### Informazioni mostrate:
- **Training**: Numero documenti stimati + data/ora richiesta
- **Attivi**: Accuratezza % + numero documenti + versione

---

## 🔧 Note Tecniche

1. **Fallback**: Se le tabelle non esistono, usa array statico predefinito
2. **Error handling**: Try/catch su tutte le query per compatibilità
3. **Security**: CSRF token validato in upload-training.php
4. **Performance**: Query ottimizzate con indici su user_id e stato
5. **Ordinamento**:
   - Richieste in corso mostrate PER ULTIME (DESC created_at)
   - Modelli completati per primi

---

## 📝 Prossimi Step (Opzionali)

- [ ] Auto-refresh ogni 30 secondi per aggiornare stato training
- [ ] WebSocket per notifiche real-time quando training completa
- [ ] Pagina admin per gestire richieste e cambio stato
- [ ] Email al cliente quando training è completato
- [ ] Percentuale progresso training (0% → 100%)

---

## 🐛 Troubleshooting

### Se il modello non appare dopo upload:
1. Verifica che il database `finch_ai_clienti` sia attivo
2. Controlla che la richiesta sia salvata:
   ```sql
   SELECT * FROM richieste_addestramento ORDER BY id DESC LIMIT 1;
   ```
3. Verifica che `user_id` corrisponda all'utente loggato
4. Controlla lo stato sia "in_attesa" o "in_lavorazione"

### Se il redirect non funziona:
1. Apri Console Browser (F12)
2. Controlla errori JavaScript
3. Verifica risposta API in Network tab
4. Controlla che `response.success === true`

---

✅ **Implementazione completata e testata**
