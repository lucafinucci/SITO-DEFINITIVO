# 📦 Installazione Sistema Upload Training su Aruba

## 🎯 Funzionalità Implementata

Sistema completo di richiesta addestramento modelli AI con upload file:
- ✅ Interfaccia drag & drop per upload documenti
- ✅ Validazione file (PDF, PNG, JPG - max 10MB)
- ✅ Progress bar durante upload
- ✅ Salvataggio richieste nel database
- ✅ Email di notifica al team
- ✅ Gestione modelli addestrati

---

## 📁 File Creati

### 1. Pagine Frontend
- `area-clienti/richiedi-addestramento.php` - Form con upload file
- `area-clienti/document-intelligence-modelli.php` - Lista modelli addestrati
- `area-clienti/document-intelligence.php` - Aggiornata con sezione modelli

### 2. API Backend
- `area-clienti/api/upload-training.php` - Gestione upload e salvataggio

### 3. Database
- `database/add_training_tables.sql` - Nuove tabelle necessarie

---

## 🚀 Installazione su Aruba

### Step 1: Crea le Tabelle Database

Accedi a **phpMyAdmin** su Aruba ed esegui questo SQL:

```sql
-- Copia e incolla il contenuto di database/add_training_tables.sql
```

O in alternativa, carica `database/add_training_tables.sql` tramite phpMyAdmin (Import).

### Step 2: Crea Directory Upload

Su Aruba, tramite **File Manager** o **FTP**:

```
/home/tuoutente/
├── public_html/          (il tuo sito web)
└── uploads/              (FUORI da public_html per sicurezza)
    └── training/         (crea questa cartella)
```

**Importante**: La cartella `uploads/training/` deve essere **fuori** da `public_html` per evitare accesso diretto ai file.

### Step 3: Configura Permessi

Imposta permessi sulla cartella upload:
```
uploads/training/ → 755 (rwxr-xr-x)
```

### Step 4: Aggiorna Path Upload in API

Modifica `area-clienti/api/upload-training.php` alla riga 64:

```php
// Cambia questo:
$uploadBaseDir = __DIR__ . '/../../uploads/training';

// In questo (usa il tuo path assoluto Aruba):
$uploadBaseDir = '/home/tuoutente/uploads/training';
```

Per trovare il path assoluto su Aruba:
1. Vai su File Manager
2. Entra in `uploads/training`
3. Leggi il path completo in alto

### Step 5: Configura Email (Opzionale)

Nel file `.env` aggiungi:

```env
TRAINING_EMAIL=ai-training@finch-ai.it
```

Oppure modifica direttamente in `upload-training.php` riga 90:

```php
$emailTo = Config::get('TRAINING_EMAIL', 'tuaemail@finch-ai.it');
```

### Step 6: Carica i File

Via FTP (FileZilla) carica:
```
✓ area-clienti/richiedi-addestramento.php
✓ area-clienti/document-intelligence-modelli.php
✓ area-clienti/document-intelligence.php (sovrascrivi)
✓ area-clienti/api/upload-training.php
```

---

## 🧪 Test Funzionamento

### 1. Test Pagina Upload

Visita: `https://tuosito.it/area-clienti/richiedi-addestramento.php`

Verifica:
- ✓ Form si carica correttamente
- ✓ Drag & drop funziona
- ✓ Puoi selezionare file
- ✓ Progress bar appare durante upload

### 2. Test Upload File

1. Compila form:
   - Tipo Modello: Fatture
   - Descrizione: Test addestramento
   - Num. Documenti: 10
2. Carica 2-3 file PDF o immagini
3. Clicca "Invia Richiesta"

Risultato atteso:
- ✓ Upload completato con successo
- ✓ Redirect a document-intelligence.php
- ✓ Nuova richiesta visibile come "In Attesa"

### 3. Verifica Database

Controlla in phpMyAdmin:

```sql
-- Verifica richiesta salvata
SELECT * FROM richieste_addestramento ORDER BY id DESC LIMIT 1;

-- Verifica file caricati
SELECT * FROM richieste_addestramento_files ORDER BY id DESC LIMIT 5;
```

### 4. Verifica File su Server

Via File Manager controlla:
```
/home/tuoutente/uploads/training/[ID_RICHIESTA]/
  ├── [hash]_file1.pdf
  ├── [hash]_file2.pdf
  └── [hash]_file3.jpg
```

---

## 📊 Dati Demo Modelli (Opzionale)

Per testare la visualizzazione modelli, inserisci dati demo:

```sql
-- Inserisci modello di test
INSERT INTO modelli_addestrati
(richiesta_id, user_id, nome_modello, tipo_modello, versione, accuratezza, num_documenti_addestramento, attivo)
VALUES
(1, 2, 'Fatture Elettroniche', 'Fatture', '1.0', 98.5, 4521, TRUE),
(2, 2, 'Contratti Commerciali', 'Contratti', '1.2', 96.4, 1834, TRUE),
(3, 2, 'Bolle di Trasporto', 'DDT', '2.0', 97.8, 2756, TRUE);
```

Nota: Sostituisci `user_id = 2` con l'ID del tuo utente test.

---

## 🔒 Sicurezza

✅ **Implementato:**
- Verifica autenticazione sessione
- CSRF token protection
- Validazione tipo file (MIME type check)
- Limite dimensione file (10MB)
- Nomi file randomizzati (uniqid)
- Upload fuori da public_html
- Sanitizzazione input

✅ **Recommended per Produzione:**
- Abilita HTTPS su Aruba
- Implementa virus scan sui file caricati
- Limita numero upload per utente/giorno
- Log dettagliati upload

---

## 🎨 Personalizzazione UI

### Cambia Dimensione Max File

In `richiedi-addestramento.php` (riga ~75):
```javascript
if (file.size > 10 * 1024 * 1024) { // 10MB
  // Cambia in: 20 * 1024 * 1024 per 20MB
```

E in `upload-training.php` (riga ~90):
```php
if ($fileSize > 10 * 1024 * 1024) { // 10MB
```

### Aggiungi Altri Formati File

In `richiedi-addestramento.php` (riga ~143):
```html
accept=".pdf,.png,.jpg,.jpeg"
<!-- Aggiungi: ,.docx,.xlsx -->
```

E in `upload-training.php` (riga ~98):
```php
$allowedTypes = ['application/pdf', 'image/png', 'image/jpeg'];
// Aggiungi: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
```

---

## 🛠️ Troubleshooting

### Errore: "Directory not writable"

```bash
# Su Aruba File Manager:
# Vai su uploads/training/
# Click destro → Permessi → Imposta 755
```

### Errore: "File upload failed"

Verifica `php.ini` su Aruba:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

Se non puoi modificare php.ini, crea `.htaccess` in `area-clienti/`:
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

### Email non arrivano

Aruba potrebbe bloccare funzione `mail()`. Alternative:
1. Usa SMTP con PHPMailer
2. Configura email Aruba tramite pannello
3. Disabilita email (commenta righe 90-114 in upload-training.php)

### File non vengono salvati

Verifica path assoluto:
```php
// In upload-training.php aggiungi debug:
error_log("Upload path: " . $uploadBaseDir);
error_log("Is writable: " . (is_writable($uploadBaseDir) ? 'YES' : 'NO'));
```

---

## 📞 Supporto

Per problemi:
1. Verifica log errori PHP su Aruba (pannello → Log)
2. Controlla console browser (F12)
3. Testa con file piccoli (< 1MB) prima

---

## ✅ Checklist Installazione

- [ ] Tabelle database create
- [ ] Cartella `uploads/training/` creata fuori public_html
- [ ] Permessi 755 sulla cartella
- [ ] Path upload aggiornato in API
- [ ] File caricati via FTP
- [ ] Test upload funzionante
- [ ] File salvati correttamente
- [ ] Richieste visibili in database
- [ ] Sezione modelli visibile su document-intelligence.php

**Installazione completata!** 🎉
