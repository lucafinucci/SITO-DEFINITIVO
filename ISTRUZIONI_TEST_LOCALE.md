# 🔧 Istruzioni per Test Locale Area Clienti

## 🚨 Problema Riscontrato

MariaDB in XAMPP non permette connessioni da `localhost`. Errore:
```
Host 'localhost' is not allowed to connect to this MariaDB server
```

## ✅ SOLUZIONE RAPIDA - Usa phpMyAdmin

### **Step 1: Accedi a phpMyAdmin**

1. Apri XAMPP Control Panel
2. Clicca su **"Admin"** accanto a MySQL
3. Si aprirà phpMyAdmin nel browser: `http://localhost/phpmyadmin`

### **Step 2: Crea il Database**

1. In phpMyAdmin, clicca su **"Nuovo"** (o "New") nella barra laterale
2. Nome database: `finch_ai_clienti`
3. Collation: `utf8mb4_unicode_ci`
4. Clicca **"Crea"**

### **Step 3: Importa lo Schema**

1. Seleziona il database `finch_ai_clienti` appena creato
2. Clicca sulla tab **"SQL"** in alto
3. Copia e incolla il contenuto del file: `database/schema.sql`
4. Clicca **"Esegui"** (o "Go")
5. ✅ Le tabelle saranno create

### **Step 4: Inserisci i Dati Demo**

1. Sempre nella tab **"SQL"**
2. Copia e incolla questo codice:

```sql
-- Genera password hashate (bcrypt)
SET @hash_admin = '$2y$10$YourHashedPasswordHere1';
SET @hash_demo = '$2y$10$YourHashedPasswordHere2';
SET @hash_cliente = '$2y$10$YourHashedPasswordHere3';

-- IMPORTANTE: Esegui questo in PHP per generare le password vere
-- password_hash('Admin123!', PASSWORD_DEFAULT)
-- password_hash('Demo123!', PASSWORD_DEFAULT)
-- password_hash('Cliente123!', PASSWORD_DEFAULT)

-- Per ora usiamo hash di esempio (password: password)
INSERT INTO utenti (email, password_hash, nome, cognome, azienda, telefono, ruolo, mfa_enabled, attivo) VALUES
('admin@finch-ai.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mario', 'Rossi', 'Finch-AI Srl', '+39 02 1234567', 'admin', FALSE, TRUE),
('demo@finch-ai.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luigi', 'Verdi', 'Azienda Demo Srl', '+39 06 7654321', 'cliente', FALSE, TRUE),
('cliente@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paolo', 'Bianchi', 'Example Corp', '+39 011 9876543', 'cliente', FALSE, TRUE);

-- Servizi
INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
('Document Intelligence', 'OCR e validazione documenti automatica con AI', 'DOC-INT', 1500.00, TRUE),
('Production Analytics', 'Dashboard KPI e monitoraggio real-time', 'PROD-ANA', 1200.00, TRUE),
('Financial Control', 'Integrazione ERP e forecast economico', 'FIN-CTR', 1800.00, TRUE),
('Supply Chain Optimizer', 'Ottimizzazione logistica e inventario', 'SUP-OPT', 2000.00, TRUE),
('Quality Assurance AI', 'Controllo qualità automatizzato', 'QA-AI', 1600.00, TRUE);

-- Assegnazione servizi
INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
(1, 1, '2024-01-01', 'attivo'),
(1, 2, '2024-01-01', 'attivo'),
(1, 3, '2024-01-15', 'attivo'),
(2, 1, '2024-01-01', 'attivo'),
(2, 2, '2024-01-01', 'attivo'),
(2, 3, '2024-02-15', 'attivo'),
(3, 1, '2024-03-01', 'attivo'),
(3, 4, '2024-03-15', 'attivo');

-- Fatture
INSERT INTO fatture (user_id, numero_fattura, data_emissione, data_scadenza, importo_netto, iva, importo_totale, stato, file_path) VALUES
(1, 'FT-2024-001', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-001.pdf'),
(1, 'FT-2024-010', '2024-10-15', '2024-11-14', 4100.00, 902.00, 5002.00, 'emessa', '/fatture/2024/FT-2024-010.pdf'),
(2, 'FT-2024-004', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-004.pdf'),
(2, 'FT-2024-011', '2024-10-15', '2024-11-14', 4500.00, 990.00, 5490.00, 'emessa', '/fatture/2024/FT-2024-011.pdf'),
(3, 'FT-2024-007', '2024-03-15', '2024-04-14', 3500.00, 770.00, 4270.00, 'pagata', '/fatture/2024/FT-2024-007.pdf'),
(3, 'FT-2024-012', '2024-10-15', '2024-11-14', 3500.00, 770.00, 4270.00, 'emessa', '/fatture/2024/FT-2024-012.pdf');

-- Scadenze
INSERT INTO scadenze (user_id, tipo, descrizione, data_scadenza, urgente, completata) VALUES
(1, 'Pagamento', 'Fattura FT-2024-010', '2024-12-14', TRUE, FALSE),
(2, 'Pagamento', 'Fattura FT-2024-011', '2024-12-14', TRUE, FALSE),
(3, 'Pagamento', 'Fattura FT-2024-012', '2024-12-14', TRUE, FALSE);
```

3. Clicca **"Esegui"**
4. ✅ Dati demo inseriti

---

## 🔐 Credenziali di Test

Le password sono hashate con bcrypt. L'hash di esempio corrisponde a:

**Password per TUTTI gli utenti:** `password`

| Email | Password | Ruolo |
|-------|----------|-------|
| admin@finch-ai.it | password | admin |
| demo@finch-ai.it | password | cliente |
| cliente@example.com | password | cliente |

⚠️ **NOTA:** Per usare le password originali (Admin123!, Demo123!, Cliente123!), devi generare nuovi hash.

### Generare Password Hash Corrette

Crea il file `generate-passwords.php`:

```php
<?php
echo "Admin123!: " . password_hash('Admin123!', PASSWORD_DEFAULT) . "\n";
echo "Demo123!: " . password_hash('Demo123!', PASSWORD_DEFAULT) . "\n";
echo "Cliente123!: " . password_hash('Cliente123!', PASSWORD_DEFAULT) . "\n";
```

Esegui: `C:\xampp\php\php.exe generate-passwords.php`

Poi aggiorna i record in phpMyAdmin:

```sql
UPDATE utenti SET password_hash = 'NUOVO_HASH' WHERE email = 'admin@finch-ai.it';
```

---

## 🌐 Test dell'Area Clienti

### Opzione 1: Sistema PHP Tradizionale

**URL:** `http://localhost/area-clienti/login.php`

1. Inserisci email: `demo@finch-ai.it`
2. Inserisci password: `password`
3. Clicca "Accedi"
4. ✅ Dovresti vedere la dashboard

### Opzione 2: Sistema API/JWT

**URL:** `http://localhost/public/area-clienti.html`

1. Inserisci email: `demo@finch-ai.it`
2. Inserisci password: `password`
3. Lascia OTP vuoto (MFA non abilitato)
4. Clicca "Accedi"
5. ✅ Redirect a dashboard.html

---

## 🛠️ Risoluzione Problema "localhost"

### Soluzione A: Fix Configurazioni (CONSIGLIATO)

Modifica i file di configurazione per usare `127.0.0.1`:

**File: `area-clienti/includes/db.php`**
```php
$dbHost = '127.0.0.1';  // ← Cambia qui
$dbName = 'finch_ai_clienti';
$dbUser = 'root';
$dbPass = '';
```

**File: `public/api/config/database.php`**
```php
define('DB_HOST', '127.0.0.1');  // ← Cambia qui
define('DB_NAME', 'finch_ai_clienti');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Soluzione B: Fix Permessi MariaDB (AVANZATO)

Tramite phpMyAdmin, tab SQL:

```sql
-- Crea utente root per localhost
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED VIA mysql_native_password;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password;
FLUSH PRIVILEGES;
```

---

## 📋 Checklist Test Completo

- [ ] Database `finch_ai_clienti` creato
- [ ] Tabelle importate da `schema.sql`
- [ ] Dati demo inseriti
- [ ] File `db.php` configurato con `127.0.0.1`
- [ ] File `database.php` configurato con `127.0.0.1`
- [ ] Test login su `/area-clienti/login.php`
- [ ] Accesso dashboard riuscito
- [ ] Visualizzazione fatture funzionante
- [ ] Visualizzazione servizi funzionante

---

## 🎯 File da Aggiornare

### 1. `area-clienti/includes/db.php` (linea 3)
```php
$dbHost = '127.0.0.1';  // ← Modifica questa riga
```

### 2. `public/api/config/database.php` (linea 12)
```php
define('DB_HOST', '127.0.0.1');  // ← Modifica questa riga
```

---

## 🚀 Pronti per il Test!

Dopo aver completato questi passaggi, l'area clienti sarà completamente funzionante in locale.

Per qualsiasi problema, controlla:
- XAMPP MySQL è avviato
- phpMyAdmin funziona su `http://localhost/phpmyadmin`
- I file di configurazione usano `127.0.0.1` e non `localhost`
