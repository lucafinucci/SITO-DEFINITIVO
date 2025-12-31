# 💾 Sistema Backup Automatici

Documentazione completa del sistema di backup automatici per Finch-AI.

## 📋 Indice

1. [Panoramica](#panoramica)
2. [Componenti](#componenti)
3. [Configurazione](#configurazione)
4. [Utilizzo](#utilizzo)
5. [Scheduling](#scheduling)
6. [Ripristino](#ripristino)
7. [Sicurezza](#sicurezza)
8. [Troubleshooting](#troubleshooting)

---

## 📖 Panoramica

Sistema completo di backup automatici con:

- ✅ **Backup Database MySQL** (full + incrementale)
- ✅ **Backup File Clienti** (uploads, configs, logs)
- ✅ **Compressione GZIP** (riduzione 70-90%)
- ✅ **Crittografia AES-256** (opzionale)
- ✅ **Retention Policy** (30/90/365 giorni)
- ✅ **Verifica Integrità** automatica
- ✅ **Notifiche Email** su errori
- ✅ **Ripristino Semplice** point-in-time

### Strategie di Backup

**3-2-1 Rule:**
- **3** copie dei dati
- **2** diversi supporti di storage
- **1** copia off-site

**Implementazione:**
- 📁 Backup locali: `/backups/` (server primario)
- ☁️ Backup remoti: S3/Google Cloud/Backblaze (opzionale)
- 💾 Backup esterni: Download manuale settimanale

---

## 🏗 Componenti

### File Creati

```
scripts/
├── backup-database.php        # Backup database MySQL
├── backup-files.php           # Backup file clienti
└── cron-backups.sh           # Configurazione cron jobs

backups/
├── database/                 # Backup DB
│   ├── backup_full_*.sql.gz
│   ├── backup_incremental_*.sql.gz
│   ├── metadata.json
│   └── backup.log
└── files/                    # Backup file
    ├── backup_files_*.tar.gz
    ├── metadata.json
    └── backup-files.log
```

### Tipologie Backup

#### 1. Backup Database

**Full (Completo):**
- Dump completo del database
- Include: struttura + dati + trigger + eventi
- Tool: `mysqldump`
- Frequenza consigliata: Giornaliero
- Retention: 30 giorni

**Incremental (Incrementale):**
- Solo record modificati dall'ultimo full
- Basato su timestamp colonne `updated_at`/`created_at`
- Più veloce e leggero
- Frequenza consigliata: Ogni 6-12 ore
- Retention: 7 giorni

**Tables (Specifiche Tabelle):**
- Backup di tabelle selezionate
- Uso: test, migrazioni, debug
- On-demand

#### 2. Backup File

**Full (Completo):**
- Copia tutti i file delle directory configurate
- Tool: TAR + GZIP o ZIP
- Frequenza consigliata: Giornaliero
- Retention: 30 giorni

**Incremental (Incrementale):**
- Solo file modificati (basato su mtime)
- Più veloce
- Frequenza consigliata: Ogni ora
- Retention: 7 giorni

---

## ⚙️ Configurazione

### 1. Directory Backup

Modifica in `backup-database.php` e `backup-files.php`:

```php
// Backup Database
define('BACKUP_DIR', __DIR__ . '/../backups/database');
define('RETENTION_DAYS', 30);
define('RETENTION_WEEKLY', 4);
define('RETENTION_MONTHLY', 12);

// Backup File
define('BACKUP_SOURCES', [
    'uploads' => __DIR__ . '/../uploads',
    'area-clienti-configs' => __DIR__ . '/../area-clienti/configs',
    'user-files' => __DIR__ . '/../user-files',
    'logs' => __DIR__ . '/../logs'
]);
```

### 2. Database Credentials

In `area-clienti/includes/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'finch_ai');
define('DB_USER', 'root');
define('DB_PASS', 'password');
```

### 3. Crittografia (Opzionale)

In `area-clienti/includes/config.php`:

```php
define('BACKUP_ENCRYPTION_KEY', 'your-strong-encryption-key-here');
// O genera automaticamente se non definito
```

### 4. Permessi

```bash
# Crea directory backup
mkdir -p /var/www/finch-ai/backups/{database,files,logs}

# Imposta permessi
chmod 755 /var/www/finch-ai/backups
chmod 755 /var/www/finch-ai/scripts
chmod +x /var/www/finch-ai/scripts/*.php
chmod +x /var/www/finch-ai/scripts/*.sh

# Owner
chown -R www-data:www-data /var/www/finch-ai/backups
```

---

## 🚀 Utilizzo

### Backup Database

```bash
# Backup completo
php scripts/backup-database.php --type=full --compress

# Backup completo con crittografia
php scripts/backup-database.php --type=full --compress --encrypt

# Backup incrementale
php scripts/backup-database.php --type=incremental --compress

# Backup specifiche tabelle
php scripts/backup-database.php --type=tables --tables=utenti,fatture --compress

# Lista backup disponibili
php scripts/backup-database.php --type=list

# Pulizia backup vecchi
php scripts/backup-database.php --type=cleanup
```

### Backup File

```bash
# Backup completo
php scripts/backup-files.php --type=full --compress

# Backup incrementale
php scripts/backup-files.php --type=incremental --compress

# Backup specifica sorgente
php scripts/backup-files.php --type=full --source=uploads --compress

# Lista backup
php scripts/backup-files.php --type=list

# Pulizia
php scripts/backup-files.php --type=cleanup
```

### Output Esempio

```
[2024-12-20 14:30:00] [INFO] Inizio backup completo database: finch_ai
[2024-12-20 14:30:15] [INFO] Backup SQL creato: backup_full_finch_ai_2024-12-20_14-30-00.sql (45.2 MB)
[2024-12-20 14:30:25] [INFO] Backup compresso: backup_full_finch_ai_2024-12-20_14-30-00.sql.gz (8.3 MB)
[2024-12-20 14:30:25] [INFO] Backup completato con successo: backup_full_finch_ai_2024-12-20_14-30-00.sql.gz
[2024-12-20 14:30:26] [INFO] Inizio pulizia backup vecchi
[2024-12-20 14:30:26] [INFO] Eliminato backup vecchio: backup_full_finch_ai_2024-11-15_02-00-00.sql.gz
[2024-12-20 14:30:26] [INFO] Pulizia completata: 3 file eliminati

✅ Backup completato con successo!
File: backup_full_finch_ai_2024-12-20_14-30-00.sql.gz
Dimensione: 8.3 MB
```

---

## ⏰ Scheduling

### Linux/Unix (Cron)

```bash
# Modifica crontab
crontab -e

# Aggiungi queste righe:
```

```cron
# Backup Database Full - Ogni giorno alle 02:00
0 2 * * * /usr/bin/php /var/www/finch-ai/scripts/backup-database.php --type=full --compress >> /var/www/finch-ai/backups/logs/db-$(date +\%Y-\%m-\%d).log 2>&1

# Backup Database Incrementale - Ogni 6 ore
0 */6 * * * /usr/bin/php /var/www/finch-ai/scripts/backup-database.php --type=incremental --compress >> /var/www/finch-ai/backups/logs/db-inc.log 2>&1

# Backup File Full - Ogni giorno alle 03:00
0 3 * * * /usr/bin/php /var/www/finch-ai/scripts/backup-files.php --type=full --compress >> /var/www/finch-ai/backups/logs/files-$(date +\%Y-\%m-\%d).log 2>&1

# Backup File Incrementale - Ogni ora
0 * * * * /usr/bin/php /var/www/finch-ai/scripts/backup-files.php --type=incremental --compress >> /var/www/finch-ai/backups/logs/files-inc.log 2>&1

# Cleanup - Ogni domenica alle 04:00
0 4 * * 0 /usr/bin/php /var/www/finch-ai/scripts/backup-database.php --type=cleanup >> /var/www/finch-ai/backups/logs/cleanup.log 2>&1
5 4 * * 0 /usr/bin/php /var/www/finch-ai/scripts/backup-files.php --type=cleanup >> /var/www/finch-ai/backups/logs/cleanup.log 2>&1
```

**Verifica cron:**
```bash
# Lista cron attivi
crontab -l

# Log cron
tail -f /var/log/syslog | grep CRON
```

### Windows (Task Scheduler)

1. **Apri Task Scheduler** (`taskschd.msc`)

2. **Create Task...**

3. **General Tab:**
   - Name: `Finch-AI Backup Database`
   - Run whether user is logged on or not: ✓
   - Run with highest privileges: ✓

4. **Triggers Tab:**
   - New → Daily, 02:00 AM
   - Repeat task every: (opzionale per incrementali)

5. **Actions Tab:**
   - Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\Users\oneno\Desktop\SITO\scripts\backup-database.php --type=full --compress`
   - Start in: `C:\Users\oneno\Desktop\SITO\scripts`

6. **Ripeti per altri backup**

---

## 🔄 Ripristino

### 1. Ripristino Database

#### Da Backup Completo

```bash
# 1. Decomprimi
gunzip backup_full_finch_ai_2024-12-20_02-00-00.sql.gz

# 2. Ripristina
mysql -u root -p finch_ai < backup_full_finch_ai_2024-12-20_02-00-00.sql

# Oppure usa lo script:
# (funzionalità da implementare in backup-database.php)
```

#### Da Backup Criptato

```bash
# 1. Decripta
openssl enc -aes-256-cbc -d -in backup.sql.gz.enc -out backup.sql.gz -pass file:backup.sql.gz.enc.key

# 2. Decomprimi
gunzip backup.sql.gz

# 3. Ripristina
mysql -u root -p finch_ai < backup.sql
```

#### Ripristino Incrementale

```bash
# 1. Ripristina ultimo full
mysql -u root -p finch_ai < backup_full_2024-12-20.sql

# 2. Applica incrementali in ordine
mysql -u root -p finch_ai < backup_incremental_2024-12-20_08-00.sql
mysql -u root -p finch_ai < backup_incremental_2024-12-20_14-00.sql
# ... e così via
```

### 2. Ripristino File

```bash
# Estrai archivio TAR.GZ
tar -xzf backup_files_full_2024-12-20_03-00-00.tar.gz -C /restore-location/

# Oppure ZIP
unzip backup_files_full_2024-12-20_03-00-00.zip -d /restore-location/

# Copia file nella posizione originale
cp -r /restore-location/backup_files_full_2024-12-20/uploads /var/www/finch-ai/
cp -r /restore-location/backup_files_full_2024-12-20/user-files /var/www/finch-ai/

# Ripristina permessi
chown -R www-data:www-data /var/www/finch-ai/uploads
chmod -R 755 /var/www/finch-ai/uploads
```

### 3. Point-in-Time Recovery

Per ripristinare a uno specifico momento:

```bash
# 1. Trova backup più vicino
php scripts/backup-database.php --type=list

# Output:
# 2024-12-20 02:00:00 - full - 8.3 MB - backup_full_2024-12-20_02-00-00.sql.gz
# 2024-12-20 08:00:00 - incremental - 1.2 MB - backup_incremental_2024-12-20_08-00-00.sql.gz
# 2024-12-20 14:00:00 - incremental - 0.8 MB - backup_incremental_2024-12-20_14-00-00.sql.gz

# 2. Se vuoi ripristinare alle 12:00 del 20/12:
# Usa: full 02:00 + incremental 08:00 (stop prima del 14:00)

# 3. Applica in ordine
mysql -u root -p finch_ai < backup_full_2024-12-20_02-00-00.sql
mysql -u root -p finch_ai < backup_incremental_2024-12-20_08-00-00.sql
```

---

## 🔒 Sicurezza

### Protezione Backup

1. **Permessi File**
   ```bash
   chmod 600 backups/database/*.sql.gz
   chmod 600 backups/files/*.tar.gz
   chown www-data:www-data backups/
   ```

2. **Crittografia**
   - AES-256-CBC per backup critici
   - Password key salvata in file separato `.key`
   - **IMPORTANTE**: Conserva `.key` file in luogo sicuro!

3. **Accesso Limitato**
   ```bash
   # .htaccess in /backups/
   Deny from all
   ```

4. **Off-site Storage**
   - Copia backup fuori dal server
   - S3, Google Cloud, Backblaze
   - Sync con rsync o rclone

### Storage Remoto (Opzionale)

#### AWS S3

```bash
# Installa AWS CLI
apt-get install awscli

# Configura
aws configure

# Sync backup
aws s3 sync /var/www/finch-ai/backups/ s3://finch-ai-backups/ --exclude "*.log"

# Aggiungi a cron dopo backup
0 5 * * * aws s3 sync /var/www/finch-ai/backups/database/ s3://finch-ai-backups/database/ --storage-class GLACIER
```

#### Backblaze B2

```bash
# Installa B2 CLI
pip install b2

# Login
b2 authorize-account <keyID> <applicationKey>

# Sync
b2 sync /var/www/finch-ai/backups/ b2://finch-ai-backups
```

#### Rclone (Universal)

```bash
# Installa
curl https://rclone.org/install.sh | sudo bash

# Configura (Google Drive, Dropbox, ecc.)
rclone config

# Sync
rclone sync /var/www/finch-ai/backups/ remote:finch-ai-backups
```

---

## 📊 Monitoring & Alerting

### Verifica Backup

```bash
# Verifica ultimo backup
LATEST_BACKUP=$(ls -t backups/database/backup_full_*.sql.gz | head -1)
if [ -z "$LATEST_BACKUP" ]; then
    echo "ERRORE: Nessun backup trovato!"
    exit 1
fi

# Verifica età
AGE=$(stat -c %Y "$LATEST_BACKUP")
NOW=$(date +%s)
DIFF=$((NOW - AGE))

if [ $DIFF -gt 86400 ]; then  # 24 ore
    echo "ALERT: Ultimo backup ha più di 24 ore!"
fi
```

### Email Notifiche

Aggiungi a script backup:

```php
// In caso di errore
if (!$result['success']) {
    mail(
        'admin@finch-ai.it',
        'BACKUP FALLITO - Finch-AI',
        "Backup fallito:\n\n" . $result['error'],
        'From: noreply@finch-ai.it'
    );
}
```

### Slack Webhook

```bash
# In cron-backups.sh
curl -X POST -H 'Content-type: application/json' \
--data '{"text":"✅ Backup completato con successo"}' \
https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

---

## 🐛 Troubleshooting

### Problema: mysqldump non trovato

**Soluzione:**
```bash
# Trova mysqldump
which mysqldump

# Se non trovato, installa mysql-client
apt-get install mysql-client

# Oppure specifica path in script
/usr/bin/mysqldump --version
```

---

### Problema: Backup troppo lento

**Soluzioni:**

1. **Usa --single-transaction** (già implementato)
   ```bash
   mysqldump --single-transaction ...
   ```

2. **Escludi tabelle grandi/inutili**
   ```bash
   mysqldump --ignore-table=finch_ai.logs ...
   ```

3. **Backup parallelo per tabelle**
   ```bash
   mysqldump tabella1 > tabella1.sql &
   mysqldump tabella2 > tabella2.sql &
   wait
   ```

---

### Problema: Spazio disco insufficiente

**Diagnostica:**
```bash
# Verifica spazio
df -h

# Dimensione backups
du -sh backups/*

# File più grandi
find backups/ -type f -exec du -h {} + | sort -rh | head -20
```

**Soluzioni:**

1. Riduci retention:
   ```php
   define('RETENTION_DAYS', 7);  // invece di 30
   ```

2. Aumenta compressione:
   ```bash
   gzip -9 file.sql  # max compression
   ```

3. Usa storage remoto:
   ```bash
   aws s3 sync backups/ s3://bucket/ --storage-class GLACIER
   rm -rf backups/old/*
   ```

---

### Problema: Backup criptati non ripristinabili

**Verifica:**
```bash
# Test decrittazione
openssl enc -aes-256-cbc -d -in backup.sql.gz.enc -out test.sql.gz -pass file:backup.sql.gz.enc.key

# Se fallisce: password errata o file corrotto
```

**Prevenzione:**
- Testa ripristino regolarmente
- Conserva `.key` file in 3+ posti diversi
- Backup anche delle chiavi

---

### Problema: Cron non esegue

**Debug:**
```bash
# Log cron
tail -f /var/log/syslog | grep CRON

# Test manuale
/usr/bin/php /full/path/to/backup-database.php --type=full

# Verifica PATH in cron
0 * * * * /usr/bin/env > /tmp/cron-env.txt

# Usa path assoluti sempre
```

---

## ✅ Checklist Pre-Produzione

### Setup Iniziale

- [ ] Directory `backups/` creata con permessi corretti
- [ ] Script backup testati manualmente
- [ ] Configurazione retention policy
- [ ] Cron jobs configurati
- [ ] Email notifiche configurate
- [ ] Storage remoto configurato (opzionale)

### Test

- [ ] Backup database full - OK
- [ ] Backup database incrementale - OK
- [ ] Backup file full - OK
- [ ] Backup file incrementale - OK
- [ ] Compressione funzionante
- [ ] Crittografia funzionante (se usata)
- [ ] Ripristino database testato
- [ ] Ripristino file testato
- [ ] Cleanup automatico funzionante

### Monitoring

- [ ] Verifica backup giornaliera automatica
- [ ] Alert su backup falliti
- [ ] Log review settimanale
- [ ] Test ripristino mensile
- [ ] Verifica spazio disco

---

## 📞 Supporto

Per problemi:
- 📧 Email: dev@finch-ai.it
- 📚 Docs: `/SISTEMA_BACKUP.md`
- 📁 Log: `backups/logs/`

---

## 📝 Changelog

### v1.0.0 (2024-12-20)
- ✅ Script backup database (full/incremental)
- ✅ Script backup file (full/incremental)
- ✅ Compressione GZIP
- ✅ Crittografia AES-256 (opzionale)
- ✅ Retention policy automatica
- ✅ Metadata tracking
- ✅ Cron configuration
- ✅ Documentazione completa

---

**Fine Documentazione Sistema Backup** 💾
