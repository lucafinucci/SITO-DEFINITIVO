# 📥 Guida Completa: Download File da Aruba

## 🎯 Come Funziona l'Upload e Download dei File

Quando un cliente carica file dall'area clienti, ecco cosa succede:

---

## 🔄 **FLUSSO COMPLETO STEP-BY-STEP**

### **1. Cliente Carica File** (dal browser)

```
Cliente visita: https://finch-ai.it/area-clienti/richiedi-addestramento.php
                    ↓
Drag & drop 5 file PDF (es: fatture esempio)
                    ↓
Click "Invia Richiesta"
                    ↓
JavaScript invia file via AJAX POST
```

---

### **2. Server Aruba Riceve File** (PHP temporaneo)

```
File arrivano a: /tmp/phpXXXXXX (directory temporanea PHP)
                    ↓
PHP valida:
  ✓ Dimensione < 10MB?
  ✓ Tipo file = PDF/PNG/JPG?
  ✓ Utente autenticato?
  ✓ CSRF token valido?
```

---

### **3. PHP Salva File in Modo Permanente**

```php
// File ricevuto in /tmp/phpABC123
$tmpFile = $_FILES['files']['tmp_name'][0];

// Sposta in directory permanente
$destinazione = '/home/tuoutente/uploads/training/1/67a3b2c1_fattura1.pdf';
move_uploaded_file($tmpFile, $destinazione);
```

**Risultato:**
```
/home/tuoutente/uploads/training/
                          ↓
                          1/  ← ID Richiesta
                          ↓
    ├── 67a3b2c1_fattura1.pdf   ← File 1
    ├── 67a3b2c2_fattura2.pdf   ← File 2
    ├── 67a3b2c3_fattura3.pdf   ← File 3
    ├── 67a3b2c4_fattura4.pdf   ← File 4
    └── 67a3b2c5_fattura5.pdf   ← File 5
```

---

### **4. Database Registra Info File**

```sql
INSERT INTO richieste_addestramento_files
(richiesta_id, filename_originale, filename_salvato, file_size, file_path)
VALUES
(1, 'fattura1.pdf', '67a3b2c1_fattura1.pdf', 245678, '/home/tuoutente/uploads/training/1/67a3b2c1_fattura1.pdf');
```

**Tabella risultante:**
| id | richiesta_id | filename_originale | filename_salvato | file_size | file_path |
|----|--------------|-------------------|------------------|-----------|-----------|
| 1  | 1            | fattura1.pdf      | 67a3b2c1_fattura1.pdf | 245678 | /home/tuoutente/uploads/.../67a3b2c1_fattura1.pdf |
| 2  | 1            | fattura2.pdf      | 67a3b2c2_fattura2.pdf | 198234 | /home/tuoutente/uploads/.../67a3b2c2_fattura2.pdf |

---

### **5. Email Notifica (Opzionale)**

```
PHP invia email a: ai-training@finch-ai.it

Oggetto: Nuova Richiesta Addestramento - Azienda Demo Srl

Corpo:
=== CLIENTE ===
Nome: Mario Rossi
Azienda: Azienda Demo Srl
Email: mario@demo.it

=== FILE CARICATI ===
- fattura1.pdf (240 KB)
- fattura2.pdf (194 KB)
- fattura3.pdf (312 KB)
- fattura4.pdf (278 KB)
- fattura5.pdf (189 KB)
```

---

## 📦 **DOVE SONO I FILE SUL SERVER ARUBA?**

### **Struttura Completa:**

```
Aruba Account: tuoutente
│
├── 📁 public_html/                    ← Sito Web Pubblico
│   ├── index.html
│   ├── area-clienti/
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   └── api/
│   │       └── upload-training.php   ← Script che riceve file
│   └── ...
│
└── 📁 uploads/                        ← ⚠️ FUORI da public_html
    └── training/                      ← File clienti
        ├── 1/                         ← Richiesta ID 1 (Cliente A)
        │   ├── 67a3b2c1_fattura1.pdf
        │   ├── 67a3b2c2_fattura2.pdf
        │   └── 67a3b2c3_fattura3.pdf
        ├── 2/                         ← Richiesta ID 2 (Cliente B)
        │   ├── 67a3d4e5_contratto1.pdf
        │   └── 67a3d4e6_contratto2.pdf
        └── 3/                         ← Richiesta ID 3 (Cliente C)
            ├── 67a3f7g8_ddt1.pdf
            └── 67a3f7g9_ddt2.pdf
```

---

## 🔒 **PERCHÉ FUORI DA public_html?**

### **Scenario SBAGLIATO (file in public_html):**

```
public_html/uploads/training/1/fattura_cliente.pdf
                    ↓
Accessibile a TUTTI tramite URL:
https://finch-ai.it/uploads/training/1/fattura_cliente.pdf
                    ↓
⚠️ CHIUNQUE può scaricare file privati dei clienti!
```

### **Scenario CORRETTO (file fuori da public_html):**

```
/home/tuoutente/uploads/training/1/fattura_cliente.pdf
                    ↓
NON ha URL pubblico
                    ↓
✅ SOLO tu (via FTP) o script PHP autenticati possono accedere
```

---

## 📥 **METODI PER SCARICARE I FILE**

### **METODO 1: FTP con FileZilla** ⭐ Raccomandato

#### **A. Installazione FileZilla**
1. Scarica: https://filezilla-project.org/
2. Installa (gratis, open source)

#### **B. Configurazione Connessione**
```
Host:     ftp.tuosito.it
Username: tuoutente
Password: *** (password Aruba)
Porta:    21 (FTP) o 22 (SFTP - più sicuro)
```

#### **C. Download File**
1. Connetti a Aruba
2. Nel pannello DESTRO (server), naviga a: `/uploads/training/`
3. Vedrai cartelle:
   ```
   /uploads/training/
   ├── 1/
   ├── 2/
   └── 3/
   ```
4. Entra nella cartella (es: `1/`)
5. Vedrai i file:
   ```
   67a3b2c1_fattura1.pdf
   67a3b2c2_fattura2.pdf
   67a3b2c3_fattura3.pdf
   ```
6. **Scarica:**
   - Click destro → Download
   - Oppure trascina i file nel pannello SINISTRO (tuo PC)

#### **D. Download Cartella Intera**
- Click destro sulla cartella `1/` → Download
- FileZilla scarica tutto il contenuto

**Vantaggi:**
- ✅ Download illimitati
- ✅ Mantiene nomi originali
- ✅ Puoi scaricare cartelle intere
- ✅ Nessuna modifica al sito

---

### **METODO 2: File Manager Aruba** (Web)

#### **A. Accesso**
1. Vai su: https://www.aruba.it
2. Login con credenziali
3. Pannello di Controllo → **File Manager**

#### **B. Navigazione**
1. Vedrai la root del tuo account
2. Click su `uploads/`
3. Click su `training/`
4. Vedrai le cartelle per ID richiesta: `1/`, `2/`, `3/`

#### **C. Download**
- **File singolo:** Click destro → Download
- **Cartella intera:** Seleziona cartella → "Scarica come ZIP"

**Vantaggi:**
- ✅ Nessun software richiesto
- ✅ Interfaccia web semplice
- ✅ Download ZIP automatico

**Svantaggi:**
- ❌ Più lento per molti file
- ❌ Limite dimensione download (dipende da piano Aruba)

---

### **METODO 3: Pannello Admin nel Sito** ⭐ Consigliato

#### **A. Accesso Pannello**

Ho creato una pagina admin per te:

```
URL: https://finch-ai.it/area-clienti/admin/richieste-addestramento.php
     ↓
Login come admin (ruolo = 'admin')
     ↓
Vedi tutte le richieste di tutti i clienti
```

#### **B. Funzionalità Pannello**

**Dashboard con:**
- 📊 Statistiche (in attesa, in lavorazione, completate)
- 📋 Lista completa richieste
- 👤 Info cliente per richiesta
- 📄 Lista file caricati
- ⬇️ Download singolo file
- 📦 Download tutti file (ZIP)

**Ogni richiesta mostra:**
```
Richiesta #1
├── Cliente: Mario Rossi - Azienda Demo Srl
├── Tipo: Fatture Elettroniche
├── File caricati: 5 file
├── Stato: In Attesa
└── Azioni:
    ├── 📦 Scarica Tutti (ZIP)    ← Scarica tutto in 1 click
    ├── ▶️ Inizia Lavorazione     ← Cambia stato
    └── ✉️ Invia Email            ← Rispondi al cliente
```

#### **C. Download File**

**Download singolo:**
```
Click "⬇️ Download" accanto al file
                    ↓
File scaricato con nome originale: fattura1.pdf
```

**Download tutti (ZIP):**
```
Click "📦 Scarica Tutti (ZIP)"
                    ↓
File scaricato: richiesta_1_20241208_153045.zip
                    ↓
Contiene tutti i 5 file con nomi originali
```

**API utilizzata:**
```
/area-clienti/api/download-training-files.php?richiesta_id=1
                    ↓
PHP legge file dal server
                    ↓
Crea ZIP temporaneo
                    ↓
Invia download al browser
                    ↓
Elimina ZIP temporaneo
```

**Vantaggi:**
- ✅ Interfaccia dedicata
- ✅ Vedi info cliente + descrizione
- ✅ Download ZIP automatico
- ✅ Tracciamento download nei log
- ✅ Gestione stati richieste

---

## 🗂️ **ORGANIZZAZIONE FILE**

### **Come Identificare i File**

#### **Database = Fonte di Verità**

```sql
-- Trova tutte le richieste
SELECT
    r.id,
    r.tipo_modello,
    u.azienda,
    u.email,
    r.created_at
FROM richieste_addestramento r
JOIN utenti u ON r.user_id = u.id
ORDER BY r.created_at DESC;
```

**Risultato:**
| id | tipo_modello | azienda | email | created_at |
|----|--------------|---------|-------|------------|
| 1  | fatture      | Azienda A | clienteA@test.it | 2024-12-08 15:30 |
| 2  | contratti    | Azienda B | clienteB@test.it | 2024-12-07 10:15 |

#### **Poi Trova i File**

```sql
-- File per richiesta ID 1
SELECT
    filename_originale,
    file_size,
    file_path
FROM richieste_addestramento_files
WHERE richiesta_id = 1;
```

**Risultato:**
| filename_originale | file_size | file_path |
|-------------------|-----------|-----------|
| fattura1.pdf | 245678 | /home/.../1/67a3b2c1_fattura1.pdf |
| fattura2.pdf | 198234 | /home/.../1/67a3b2c2_fattura2.pdf |

---

## 🔐 **SICUREZZA DOWNLOAD**

### **Chi Può Scaricare?**

#### **API Download (download-training-files.php):**

```php
// Verifica autenticazione
if (!isset($_SESSION['cliente_id'])) {
    die('Accesso negato'); // ❌ Non loggato
}

// Verifica ruolo
if ($user['ruolo'] !== 'admin' && $file['user_id'] != $_SESSION['cliente_id']) {
    die('Non hai permesso'); // ❌ Non sei admin né proprietario
}

// OK, download consentito ✅
```

**Regole:**
- ✅ **Admin** può scaricare TUTTI i file
- ✅ **Cliente** può scaricare SOLO i PROPRI file
- ❌ **Non loggati** NON possono scaricare nulla
- ❌ **Cliente A** NON può scaricare file di Cliente B

### **Log Download**

Ogni download viene tracciato:

```php
ErrorHandler::logAccess('File downloaded', [
    'file_id' => 123,
    'user_id' => 1,
    'ip' => '192.168.1.1',
    'timestamp' => '2024-12-08 15:30:45'
]);
```

---

## 📊 **WORKFLOW COMPLETO ADMIN**

### **Scenario Reale:**

```
1. CLIENTE INVIA RICHIESTA
   ↓
   Mario Rossi carica 5 fatture per addestramento modello

2. EMAIL NOTIFICA
   ↓
   Ricevi email: "Nuova richiesta da Azienda Demo Srl"

3. ACCEDI AL PANNELLO ADMIN
   ↓
   https://finch-ai.it/area-clienti/admin/richieste-addestramento.php

4. VEDI RICHIESTA
   ↓
   Richiesta #1
   - Cliente: Mario Rossi - Azienda Demo Srl
   - Tipo: Fatture Elettroniche
   - Descrizione: "Fatture con estrazione codice, data, importo..."
   - File: 5 file caricati

5. SCARICA FILE
   ↓
   Click "📦 Scarica Tutti (ZIP)"
   ↓
   Download: richiesta_1_20241208.zip (1.2 MB)

6. ADDESTRA MODELLO
   ↓
   Usi i file per addestrare modello AI

7. AGGIORNA STATO
   ↓
   Click "▶️ Inizia Lavorazione"
   ↓
   Stato cambia: In Attesa → In Lavorazione

8. MODELLO PRONTO
   ↓
   Inserisci in database:
   INSERT INTO modelli_addestrati (...)

9. COMPLETA RICHIESTA
   ↓
   Click "✅ Segna Completato"

10. CLIENTE VEDE MODELLO
    ↓
    Mario Rossi accede e vede il suo nuovo modello attivo!
```

---

## 🛠️ **FILE CREATI PER TE**

### **1. API Download**
📄 `area-clienti/api/download-training-files.php`

**Funzioni:**
- Download singolo file: `?file_id=123`
- Download richiesta completa (ZIP): `?richiesta_id=1`
- Verifica permessi (admin o proprietario)
- Log download

### **2. Pannello Admin**
📄 `area-clienti/admin/richieste-addestramento.php`

**Funzioni:**
- Lista tutte le richieste
- Statistiche (in attesa, in lavorazione, ecc.)
- Dettagli cliente per richiesta
- Download file (singolo o ZIP)
- Gestione stati

---

## ✅ **CHECKLIST UTILIZZO**

### **Setup Iniziale:**
- [ ] Crea cartella `/uploads/training/` (permessi 755)
- [ ] Carica file API: `download-training-files.php`
- [ ] Carica pannello admin: `admin/richieste-addestramento.php`
- [ ] Crea utente admin (ruolo = 'admin')

### **Ogni Nuova Richiesta:**
1. [ ] Ricevi email notifica
2. [ ] Accedi a pannello admin
3. [ ] Visualizza dettagli richiesta
4. [ ] Scarica file (ZIP o singoli)
5. [ ] Cambia stato: "In Lavorazione"
6. [ ] Addestra modello
7. [ ] Inserisci modello nel database
8. [ ] Cambia stato: "Completato"
9. [ ] (Opzionale) Invia email al cliente

---

## 🔄 **ALTERNATIVE FTP**

### **Se Preferisci Command Line:**

**Linux/Mac (Terminal):**
```bash
# Connetti via SFTP
sftp tuoutente@ftp.tuosito.it

# Naviga
cd uploads/training/1

# Scarica tutti i file
get *

# Scarica ricorsivo (con sottocartelle)
get -r .

# Esci
exit
```

**Windows (PowerShell):**
```powershell
# WinSCP Command Line
winscp.com /command "open sftp://tuoutente@ftp.tuosito.it" "cd /uploads/training/1" "get * C:\Downloads\" "exit"
```

---

## 📞 **SUPPORTO**

### **File Non Trovati?**

```bash
# Via FTP, verifica path
ls -la /home/tuoutente/uploads/training/

# Dovresti vedere:
drwxr-xr-x  5  tuoutente  tuoutente  4096  Dec  8 15:30  .
drwxr-xr-x  3  tuoutente  tuoutente  4096  Dec  8 15:00  ..
drwxr-xr-x  2  tuoutente  tuoutente  4096  Dec  8 15:30  1
drwxr-xr-x  2  tuoutente  tuoutente  4096  Dec  7 10:15  2
```

### **Permessi Errati?**

```bash
# Imposta permessi corretti
chmod 755 /home/tuoutente/uploads/training/
chmod -R 644 /home/tuoutente/uploads/training/*
```

---

## 🎯 **RIEPILOGO**

### **File Cliente:**
```
Browser Cliente
      ↓ Upload
Server Aruba: /home/tuoutente/uploads/training/[ID]/file.pdf
      ↓ Registrato in
Database: richieste_addestramento_files
```

### **Download Admin:**
```
Metodo 1: FTP FileZilla → /uploads/training/[ID]/
Metodo 2: File Manager Aruba → uploads/training/[ID]/
Metodo 3: Pannello Admin → Click "Scarica ZIP"
```

### **File Sicuri:**
- ✅ Fuori da public_html (NON accessibili via URL)
- ✅ Solo admin può scaricare file di altri
- ✅ Download tracciati nei log
- ✅ Nomi file randomizzati (prevenzione conflitti)

---

**Hai accesso completo ai file dei clienti tramite FTP, File Manager o Pannello Admin! 🎉**
