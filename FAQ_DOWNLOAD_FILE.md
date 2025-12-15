# ❓ FAQ - Download File da Aruba

## Domande Frequenti

---

### **1. Dove finiscono i file che i clienti caricano?**

I file vengono salvati sul server Aruba in:
```
/home/tuoutente/uploads/training/[ID_RICHIESTA]/
```

Ogni richiesta ha una sua cartella separata per ID.

**Esempio:**
- Richiesta 1 → `/uploads/training/1/`
- Richiesta 2 → `/uploads/training/2/`
- Richiesta 3 → `/uploads/training/3/`

---

### **2. I file sono accessibili pubblicamente via URL?**

**NO!** ❌

I file sono salvati **FUORI** da `public_html`, quindi:
- ❌ `https://tuosito.it/uploads/training/1/file.pdf` → **NON FUNZIONA**
- ✅ Solo tu puoi scaricarli via FTP o pannello admin

Questo è per **proteggere la privacy** dei file dei clienti.

---

### **3. Come posso scaricare i file caricati dai clienti?**

Hai **3 metodi**:

#### **Metodo 1: FTP con FileZilla** (Raccomandato)
- Connetti a `ftp.tuosito.it`
- Naviga a `/uploads/training/`
- Scarica file o cartelle intere

#### **Metodo 2: File Manager Aruba**
- Accedi al pannello Aruba
- File Manager → `uploads/training/`
- Download singolo o ZIP

#### **Metodo 3: Pannello Admin** (Più comodo)
- Vai su `/area-clienti/admin/richieste-addestramento.php`
- Click "📦 Scarica Tutti (ZIP)"
- Scarichi tutti i file in un click

---

### **4. Posso scaricare tutti i file di una richiesta in un solo file?**

**SÌ!** ✅

**Via Pannello Admin:**
1. Vai su `admin/richieste-addestramento.php`
2. Trova la richiesta
3. Click **"📦 Scarica Tutti (ZIP)"**
4. Ricevi un file ZIP con tutti i documenti dentro

Il file ZIP si chiamerà tipo:
```
richiesta_1_20241208_153045.zip
```

---

### **5. I nomi dei file sono cambiati rispetto all'originale?**

**Sul server SÌ**, ma quando scarichi **riottieni il nome originale**.

**Sul server:**
```
67a3b2c1_fattura_cliente.pdf  ← Nome randomizzato (sicurezza)
```

**Quando scarichi:**
```
fattura_cliente.pdf  ← Nome originale ripristinato
```

Questo previene:
- Conflitti se due clienti caricano file con stesso nome
- Sovrascrittura accidentale
- Problemi con caratteri speciali

---

### **6. Quanto spazio occupano i file su Aruba?**

Dipende da quanti file vengono caricati. Per monitorare:

**Via FTP:**
```bash
du -sh /home/tuoutente/uploads/training/
```

**Via File Manager:**
- Naviga a `uploads/training/`
- Vedi dimensione totale

**Limite tipico Aruba:**
- Hosting base: 1-5 GB
- Hosting plus: 10-50 GB
- Se serve più spazio, upgrade piano o pulizia periodica

---

### **7. Posso cancellare i file dopo averli scaricati?**

**SÌ**, ma è consigliato:

1. **Prima** scarica tutto via FTP/pannello
2. **Poi** elimina dal server se serve spazio
3. **Mantieni** almeno per 30-60 giorni
4. **Backup** su tuo PC/cloud prima di eliminare

**Per eliminare:**

**Via FTP:**
- Seleziona cartella richiesta
- Click destro → Elimina

**Via File Manager:**
- Seleziona cartella
- Click "Elimina"

**Via Database (opzionale):**
```sql
-- Elimina record (i file rimangono su disco)
DELETE FROM richieste_addestramento_files WHERE richiesta_id = 1;
DELETE FROM richieste_addestramento WHERE id = 1;
```

---

### **8. Il cliente può riscaricare i file che ha caricato?**

**NO** ❌ (attualmente)

Il sistema è pensato per:
- Cliente → Carica file
- Admin (tu) → Scarica e processa
- Admin → Crea modello
- Cliente → Usa modello creato

Se vuoi permettere al cliente di riscaricare, posso creare questa funzionalità.

---

### **9. Ricevo notifiche quando un cliente carica file?**

**SÌ** ✅ (se configurato)

Dopo ogni upload viene inviata email a:
```
TRAINING_EMAIL=ai-training@finch-ai.it  (configurabile in .env)
```

**Email contiene:**
- Nome cliente e azienda
- Tipo modello richiesto
- Descrizione
- Lista file caricati (nome + dimensione)
- Link diretto al pannello admin

**Se email non arrivano:**
- Aruba potrebbe bloccare funzione `mail()`
- Configura SMTP alternativo
- Oppure controlla manualmente il pannello admin

---

### **10. Come vedo quali richieste sono in attesa?**

**Pannello Admin:**

Vai su: `/area-clienti/admin/richieste-addestramento.php`

Vedrai dashboard con:

```
📊 STATISTICHE
├─ In Attesa: 2        ← Nuove richieste
├─ In Lavorazione: 1   ← Stai processando
├─ Completato: 12      ← Modelli creati
└─ Totale: 15
```

Ogni richiesta mostra:
- 🟡 Giallo = In Attesa
- 🔵 Blu = In Lavorazione
- 🟢 Verde = Completato
- ⚫ Grigio = Annullato

---

### **11. Posso cambiare lo stato di una richiesta?**

**SÍ!** ✅

Nel pannello admin:

```
Richiesta #1 - 🟡 In Attesa

[▶️ Inizia Lavorazione]  ← Click qui
         ↓
Stato → In Lavorazione

[✅ Segna Completato]    ← Quando finito
         ↓
Stato → Completato
```

Stati disponibili:
- **In Attesa** → Cliente ha appena inviato
- **In Lavorazione** → Tu stai processando
- **Completato** → Modello creato e attivo
- **Annullato** → Richiesta non processata

---

### **12. I file hanno limiti di dimensione?**

**SÌ**, attualmente:
- **Singolo file:** 10 MB max
- **Tipi ammessi:** PDF, PNG, JPG, JPEG

**Per cambiare limite:**

1. **Frontend** (`richiedi-addestramento.php` riga ~75):
```javascript
if (file.size > 20 * 1024 * 1024) { // 20MB
```

2. **Backend** (`upload-training.php` riga ~90):
```php
if ($fileSize > 20 * 1024 * 1024) { // 20MB
```

3. **PHP.ini** (Aruba):
```ini
upload_max_filesize = 20M
post_max_size = 20M
```

---

### **13. Posso scaricare file via API/script automatico?**

**SÌ!** ✅

API creata per te:
```
GET /area-clienti/api/download-training-files.php?richiesta_id=1
```

**Esempio con curl:**
```bash
curl -o richiesta_1.zip \
  --cookie "PHPSESSID=abc123..." \
  "https://finch-ai.it/area-clienti/api/download-training-files.php?richiesta_id=1"
```

**Autenticazione richiesta:**
- Cookie sessione valida
- Ruolo = admin

---

### **14. Dove posso vedere i dettagli di una richiesta?**

**Database:**
```sql
-- Info richiesta
SELECT * FROM richieste_addestramento WHERE id = 1;

-- File della richiesta
SELECT filename_originale, file_size, file_path
FROM richieste_addestramento_files
WHERE richiesta_id = 1;
```

**Pannello Admin:**
- Vai su `admin/richieste-addestramento.php`
- Ogni richiesta mostra tutti i dettagli

**Via FTP:**
- Naviga a `/uploads/training/1/`
- Vedi tutti i file fisici

---

### **15. File caricati occupano troppo spazio, cosa faccio?**

**Opzioni:**

1. **Scarica + Elimina periodicamente**
   - Ogni settimana scarica nuove richieste
   - Elimina richieste completate > 60 giorni

2. **Sposta su cloud esterno**
   - Dopo download, carica su Google Drive/Dropbox
   - Elimina da Aruba

3. **Compressione**
   - File già compressi (PDF/JPG)
   - Non guadagni molto

4. **Upgrade piano Aruba**
   - Piano superiore con più spazio

**Script pulizia automatica (esempio):**
```php
// Elimina file di richieste completate > 90 giorni
$stmt = $pdo->query("
  SELECT id FROM richieste_addestramento
  WHERE stato = 'completato'
  AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
");

foreach ($stmt->fetchAll() as $r) {
  // Elimina cartella
  $dir = "/home/user/uploads/training/{$r['id']}";
  shell_exec("rm -rf $dir");
}
```

---

### **16. Posso condividere un file con il cliente?**

Attualmente **NO** (file privati).

**Se serve questa funzione**, posso creare:

1. **Link download temporaneo**
   - Genera URL con token
   - Valido 24 ore
   - Es: `download.php?token=abc123`

2. **Area download cliente**
   - Cliente vede i suoi file caricati
   - Può riscaricarli quando vuole

**Vuoi che implementi questa funzionalità?**

---

### **17. Errore "File not found" quando scarico**

**Cause possibili:**

1. **Path errato in database**
   ```sql
   -- Verifica path
   SELECT file_path FROM richieste_addestramento_files WHERE id = 1;
   ```

2. **File eliminato manualmente**
   - Controlla via FTP se file esiste
   - Path: `/home/user/uploads/training/1/`

3. **Permessi errati**
   ```bash
   # Via FTP/SSH imposta:
   chmod 644 /home/user/uploads/training/1/*
   ```

4. **Path relativo vs assoluto**
   - Usa sempre path assoluto: `/home/user/uploads/...`
   - Non usare: `../../uploads/...`

---

### **18. Come faccio backup dei file caricati?**

**Metodo 1: FTP automatico**

Con FileZilla:
- Sincronizzazione directory
- Download automatico cartella `training/`
- Programma settimanalmente

**Metodo 2: Script rsync (se hai SSH)**
```bash
rsync -avz tuoutente@ftp.tuosito.it:/uploads/training/ ./backup/
```

**Metodo 3: Via pannello admin**
- Scarica tutti ZIP
- Salva su Google Drive/Dropbox

**Frequenza consigliata:** Settimanale

---

### **19. Posso vedere statistiche upload?**

**Nel pannello admin:**
- Numero totale richieste
- Richieste per stato
- File totali caricati

**Query personalizzate:**
```sql
-- File caricati per cliente
SELECT
  u.azienda,
  COUNT(f.id) as num_files,
  SUM(f.file_size) as total_size
FROM utenti u
JOIN richieste_addestramento r ON u.id = r.user_id
JOIN richieste_addestramento_files f ON r.id = f.richiesta_id
GROUP BY u.id;

-- Upload nel tempo
SELECT
  DATE(r.created_at) as data,
  COUNT(*) as num_richieste,
  COUNT(f.id) as num_files
FROM richieste_addestramento r
LEFT JOIN richieste_addestramento_files f ON r.id = f.richiesta_id
GROUP BY DATE(r.created_at)
ORDER BY data DESC;
```

---

### **20. Cosa succede se Aruba si riempie?**

**Avvisi:**
- Aruba invia email quando spazio > 80%
- Pannello mostra utilizzo disco

**Conseguenze:**
- Upload clienti **falliscono**
- Errore: "No space left on device"

**Soluzioni immediate:**
1. Elimina file vecchi
2. Svuota backup/log
3. Contatta Aruba per upgrade

**Prevenzione:**
- Monitor mensile spazio
- Pulizia automatica richieste vecchie
- Alert quando > 70% utilizzo

---

## 📞 Altre Domande?

Consulta:
- 📘 **GUIDA_DOWNLOAD_FILE_ARUBA.md** - Guida completa
- 📊 **FLUSSO_FILE_UPLOAD_DOWNLOAD.txt** - Diagramma flusso
- ⚙️ **INSTALLAZIONE_UPLOAD_TRAINING.md** - Setup tecnico

Oppure controlla:
- Log PHP Aruba (pannello → Log)
- Console browser (F12)
- Database phpMyAdmin
