# Sistema di Tracking Pagamenti e Solleciti Automatici

## 📋 Panoramica

Sistema completo per tracciare i pagamenti delle fatture e gestire solleciti automatici ai clienti.

## 🎯 Funzionalità Implementate

### ✅ Tracking Pagamenti

1. **Stati Fattura Workflow**
   - `bozza` → `emessa` → `inviata` → `pagata`
   - `scaduta` (automatico quando oltre scadenza)
   - `annullata`

2. **Registrazione Pagamenti**
   - Importo pagamento
   - Data pagamento
   - Metodo (Bonifico, Carta, PayPal, Stripe, etc.)
   - Storico completo pagamenti multipli
   - Riferimenti esterni (es. ID transazione Stripe)

3. **Verifica Automatica Scadenze**
   - Script CRON che controlla giornalmente
   - Aggiorna automaticamente stato a "scaduta"
   - Calcola giorni di ritardo
   - Genera statistiche

### ✅ Solleciti Automatici

1. **Sistema a 3 Livelli**
   - **Primo Sollecito**: 7 giorni dopo scadenza (gentile reminder)
   - **Secondo Sollecito**: 15 giorni dopo scadenza (formale)
   - **Sollecito Urgente**: 30 giorni dopo scadenza (ultimo avviso)

2. **Template Email Personalizzabili**
   - Template configurabili per ogni livello
   - Variabili dinamiche: {numero_fattura}, {totale}, {data_scadenza}, etc.
   - HTML responsive per email professionali

3. **Invio Automatico**
   - Script CRON per invio giornaliero
   - Pausa tra invii per evitare spam
   - Log dettagliati
   - Gestione errori robusto

4. **Gestione Manuale**
   - Possibilità di inviare solleciti manuali
   - Annullamento solleciti pendenti
   - Vista riepilogo solleciti

## 📁 File Creati

### Database
```
database/add_solleciti_fatture.sql
```
- Tabella `fatture_solleciti`
- Tabella `solleciti_config`
- Vista `v_solleciti_pending`

### Script CRON
```
area-clienti/cron/verifica-scadenze-fatture.php
area-clienti/cron/invia-solleciti-email.php
```

### API
```
area-clienti/api/solleciti.php
```

## 🚀 Installazione

### 1. Installa Database

```bash
mysql -u root -p finch_ai < database/add_solleciti_fatture.sql
```

O tramite phpMyAdmin:
1. Seleziona database `finch_ai`
2. Importa `database/add_solleciti_fatture.sql`

### 2. Configura CRON Jobs

#### Verifica Scadenze (Giornaliero alle 06:00)

**Linux/Mac:**
```bash
0 6 * * * php /path/to/area-clienti/cron/verifica-scadenze-fatture.php
```

**Windows Task Scheduler:**
- Nome: Verifica Scadenze Fatture
- Trigger: Giornaliero alle 06:00
- Azione: `C:\xampp\php\php.exe "C:\path\to\area-clienti\cron\verifica-scadenze-fatture.php"`

#### Invio Solleciti (Giornaliero alle 09:00)

**Linux/Mac:**
```bash
0 9 * * * php /path/to/area-clienti/cron/invia-solleciti-email.php
```

**Windows Task Scheduler:**
- Nome: Invio Solleciti Email
- Trigger: Giornaliero alle 09:00
- Azione: `C:\xampp\php\php.exe "C:\path\to\area-clienti\cron\invia-solleciti-email.php"`

### 3. Test Manuale

```bash
# Test verifica scadenze
php area-clienti/cron/verifica-scadenze-fatture.php

# Test invio solleciti
php area-clienti/cron/invia-solleciti-email.php
```

## ⚙️ Configurazione

### Template Email

I template sono configurabili nel database:

```sql
UPDATE solleciti_config
SET template_primo_sollecito = 'Il tuo template personalizzato...',
    template_secondo_sollecito = 'Il tuo template...',
    template_sollecito_urgente = 'Il tuo template...'
WHERE id = 1;
```

### Giorni Solleciti

Modifica i giorni per ogni livello:

```sql
UPDATE solleciti_config
SET giorni_primo_sollecito = 5,     -- Default: 7
    giorni_secondo_sollecito = 10,  -- Default: 15
    giorni_sollecito_urgente = 20   -- Default: 30
WHERE id = 1;
```

### Attiva/Disattiva Solleciti Automatici

```sql
UPDATE solleciti_config
SET solleciti_automatici_attivi = FALSE  -- TRUE per attivare
WHERE id = 1;
```

## 📧 Configurazione Email SMTP

Per produzione, configura SMTP in `invia-solleciti-email.php` usando **PHPMailer**:

```bash
composer require phpmailer/phpmailer
```

Poi decommenta e configura:

```php
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## 📊 Dashboard Admin

### Visualizzazione Fatture

In `/area-clienti/admin/fatture.php` puoi:

- ✅ Vedere stato pagamenti
- ✅ Filtrare per stato
- ✅ Registrare pagamenti
- ✅ Vedere solleciti inviati (TODO: aggiungere colonna)

### Statistiche Disponibili

- Totale fatture emesse/pagate/scadute
- Importo da incassare
- Giorni medi di ritardo
- Clienti con più fatture scadute

## 🔍 Query Utili

### Fatture Scadute per Cliente

```sql
SELECT
    u.azienda,
    u.email,
    COUNT(f.id) AS num_fatture_scadute,
    SUM(f.totale) AS totale_debito,
    MAX(DATEDIFF(CURDATE(), f.data_scadenza)) AS max_giorni_ritardo
FROM fatture f
JOIN utenti u ON f.cliente_id = u.id
WHERE f.stato = 'scaduta'
GROUP BY u.id
ORDER BY totale_debito DESC;
```

### Solleciti Inviati per Fattura

```sql
SELECT
    f.numero_fattura,
    s.tipo,
    s.numero_sollecito,
    s.data_invio,
    s.stato
FROM fatture_solleciti s
JOIN fatture f ON s.fattura_id = f.id
WHERE f.numero_fattura = 'FT-2025-00001'
ORDER BY s.data_invio DESC;
```

### Report Efficacia Solleciti

```sql
SELECT
    s.tipo,
    COUNT(*) AS totale_inviati,
    SUM(CASE WHEN f.stato = 'pagata' THEN 1 ELSE 0 END) AS pagati_dopo_sollecito,
    ROUND(SUM(CASE WHEN f.stato = 'pagata' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS tasso_successo
FROM fatture_solleciti s
JOIN fatture f ON s.fattura_id = f.id
WHERE s.stato = 'inviato'
GROUP BY s.tipo;
```

## 📝 Log Files

I log vengono salvati in:

```
area-clienti/cron/logs/scadenze-YYYY-MM.log
area-clienti/cron/logs/solleciti-email-YYYY-MM.log
```

Esempio log:
```
[2025-01-15 06:00:10] Verifica completata - Scadute: 3 - Solleciti: 2 (7g:1, 15g:1, 30g:0)
[2025-01-15 09:00:05] Invio completato - Processati: 2 - Inviati: 2 - Errori: 0
```

## 🛡️ Sicurezza

- ✅ Solo admin possono gestire solleciti
- ✅ CSRF protection su tutte le API
- ✅ Validazione input
- ✅ Prepared statements SQL
- ✅ Rate limiting invio email (1 secondo tra invii)

## 📈 Best Practices

### Solleciti Efficaci

1. **Tono Professionale**
   - Primo sollecito: cortese e informativo
   - Secondo sollecito: più formale
   - Terzo sollecito: urgente ma professionale

2. **Personalizzazione**
   - Usa sempre nome azienda/cliente
   - Riferimenti precisi a numero fattura
   - Offri supporto per chiarimenti

3. **Timing**
   - Non inviare troppo presto (rispetta i 7 giorni)
   - Non saturare con troppi solleciti
   - Considera festività/weekend

### Gestione Pagamenti

1. **Registrazione Immediata**
   - Registra pagamento appena ricevuto
   - Verifica importo corretto
   - Annota metodo di pagamento

2. **Riconciliazione**
   - Verifica periodica estratti conto
   - Confronto con fatture pagate
   - Gestione pagamenti parziali

## 🔧 Troubleshooting

### Solleciti Non Inviati

1. Verifica configurazione: `SELECT * FROM solleciti_config;`
2. Controlla `solleciti_automatici_attivi = TRUE`
3. Verifica CRON job attivo
4. Controlla log errori

### Email Non Ricevute

1. Verifica spam folder
2. Controlla configurazione SMTP
3. Testa invio manuale
4. Verifica email destinatario corretta

### Script CRON Non Esegue

1. Verifica permessi file PHP
2. Controlla path PHP nel crontab
3. Testa esecuzione manuale
4. Verifica log sistema

## 🎯 TODO Futuri

- [ ] Dashboard solleciti dedicata
- [ ] Integrazione WhatsApp/SMS
- [ ] AI per personalizzazione solleciti
- [ ] Report PDF solleciti
- [ ] Piani di rateizzazione automatici
- [ ] Integrazione recupero crediti

## 📞 Supporto

Per problemi o domande:
- Email: supporto@finch-ai.it
- Documentazione completa in `/docs`
