# 📧 Sistema Email Automatiche - Guida Completa

Sistema completo di gestione email con template, coda intelligente, tracking e automazioni basate su eventi.

## 📋 Indice

1. [Panoramica](#panoramica)
2. [Architettura](#architettura)
3. [Installazione](#installazione)
4. [Template Email](#template-email)
5. [Email Automatiche](#email-automatiche)
6. [API e Hook](#api-e-hook)
7. [CRON Jobs](#cron-jobs)
8. [Tracking e Analytics](#tracking-e-analytics)
9. [Best Practices](#best-practices)

---

## 🎯 Panoramica

### Funzionalità Principali

✅ **Template Riutilizzabili**
- 5 template predefiniti pronti all'uso
- Variabili dinamiche `{placeholder}`
- HTML + testo plain
- Personalizzazione completa

✅ **Coda Intelligente**
- 4 livelli di priorità
- Retry automatico su fallimenti
- Scheduling programmato
- Processing batch efficiente

✅ **Email Automatiche**
- Benvenuto nuovo cliente
- Conferma attivazione servizio
- Notifica fattura emessa
- Conferma pagamento ricevuto
- Solleciti automatici scadenze

✅ **Tracking Completo**
- Stato invio (inviata/fallita/aperta/click)
- Tasso apertura email
- Tasso click link
- Analytics dettagliato

✅ **Integrazione Eventi**
- Hook su eventi sistema
- Invio automatico trigger-based
- Nessun codice duplicato

---

## 🏗️ Architettura

### Database Tables

```
email_templates       → Template riutilizzabili
email_queue          → Coda email da inviare
email_log            → Storico email inviate
v_email_statistics   → Vista statistiche
```

### PHP Classes

```
EmailManager         → Classe principale gestione email
email-hooks.php      → Hook integrazione eventi
```

### CRON Scripts

```
processa-coda-email.php        → Processa coda (ogni 5 min)
invia-email-scadenze.php       → Email scadenze (giornaliero)
```

---

## 🚀 Installazione

### 1. Database

Esegui lo script SQL:

```bash
mysql -u root -p finch_ai < database/add_email_templates.sql
```

Verifica installazione:

```sql
SHOW TABLES LIKE 'email%';
-- Dovresti vedere: email_templates, email_queue, email_log

SELECT COUNT(*) FROM email_templates WHERE predefinito = TRUE;
-- Risultato: 5 (template predefiniti)
```

### 2. Configurazione SMTP (Produzione)

Per produzione, installa PHPMailer:

```bash
cd area-clienti
composer require phpmailer/phpmailer
```

Modifica `email-manager.php` metodo `sendEmail()`:

```php
private function sendEmail($data) {
    require 'vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  // o tuo provider
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';
    $mail->Password = 'your-app-password';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($data['from'], $data['from_name']);
    $mail->addAddress($data['to'], $data['to_name']);
    if ($data['reply_to']) {
        $mail->addReplyTo($data['reply_to']);
    }

    $mail->Subject = $data['subject'];
    $mail->Body = $data['html'];
    $mail->AltBody = $data['text'];
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    return $mail->send();
}
```

### 3. CRON Jobs

Configura i CRON jobs sul server:

**Linux/Mac:**
```bash
crontab -e

# Aggiungi queste righe:
*/5 * * * * php /path/to/area-clienti/cron/processa-coda-email.php
0 9 * * * php /path/to/area-clienti/cron/invia-email-scadenze.php
```

**Windows Task Scheduler:**
```
Azione: Avvia Programma
Programma: C:\xampp\php\php.exe
Argomenti: C:\path\to\area-clienti\cron\processa-coda-email.php
```

### 4. Verifica Installazione

Test rapido:

```php
require 'includes/db.php';
require 'includes/email-manager.php';

$emailManager = new EmailManager($pdo);

// Test invio
$queueId = $emailManager->addToQueue([
    'template_id' => null,
    'destinatario_email' => 'test@example.com',
    'destinatario_nome' => 'Test User',
    'oggetto' => 'Test Email Sistema',
    'corpo_html' => '<h1>Test</h1><p>Funziona!</p>',
    'corpo_testo' => 'Test - Funziona!',
    'mittente_email' => 'noreply@finch-ai.it',
    'mittente_nome' => 'Finch-AI',
    'reply_to' => null,
    'priorita' => 'alta'
]);

echo "Email aggiunta alla coda con ID: $queueId\n";

// Processa subito
$result = $emailManager->processQueue(1);
print_r($result);
```

---

## 📝 Template Email

### Template Predefiniti

#### 1. benvenuto-cliente

**Quando:** Nuovo cliente registrato
**Variabili:**
- `{nome_cliente}` - Nome completo
- `{email}` - Email cliente
- `{azienda}` - Nome azienda
- `{link_area_clienti}` - URL area clienti

**Uso:**
```php
sendWelcomeEmail($pdo, $cliente);
```

#### 2. servizio-attivato

**Quando:** Servizio attivato per cliente
**Variabili:**
- `{nome_servizio}` - Nome servizio
- `{descrizione_servizio}` - Descrizione
- `{data_attivazione}` - Data attivazione
- `{prezzo_mensile}` - Prezzo
- `{link_servizio}` - URL dettaglio servizio

**Uso:**
```php
onServizioAttivato($pdo, $userId, $servizioId);
```

#### 3. fattura-emessa

**Quando:** Nuova fattura emessa
**Variabili:**
- `{numero_fattura}` - Numero fattura
- `{data_emissione}` - Data emissione
- `{data_scadenza}` - Data scadenza
- `{imponibile}`, `{iva_percentuale}`, `{iva_importo}`, `{totale}`
- `{link_paga}` - URL pagamento
- `{link_pdf}` - URL download PDF

**Uso:**
```php
sendInvoiceEmail($pdo, $fattura, $cliente);
```

#### 4. pagamento-ricevuto

**Quando:** Pagamento ricevuto
**Variabili:**
- `{numero_fattura}` - Numero fattura
- `{importo_pagato}` - Importo
- `{data_pagamento}` - Data/ora
- `{metodo_pagamento}` - Metodo (Stripe/PayPal/etc)
- `{riferimento_transazione}` - ID transazione

**Uso:**
```php
onPagamentoRicevuto($pdo, $fatturaId, $importo, 'stripe', 'pi_xxx');
```

#### 5. sollecito-primo

**Quando:** 7 giorni dopo scadenza fattura
**Variabili:**
- `{numero_fattura}` - Numero fattura
- `{data_scadenza}` - Data scadenza
- `{totale}` - Importo
- `{giorni_ritardo}` - Giorni di ritardo
- `{link_paga}` - URL pagamento

**Uso:** Automatico via CRON `invia-email-scadenze.php`

### Creare Nuovo Template

```sql
INSERT INTO email_templates (
    codice,
    nome,
    descrizione,
    categoria,
    oggetto,
    corpo_html,
    corpo_testo,
    variabili_disponibili,
    attivo
) VALUES (
    'custom-template',
    'Mio Template Custom',
    'Descrizione template',
    'marketing',
    'Oggetto con {variabile}',
    '<html>Corpo HTML con {variabile}</html>',
    'Corpo testo con {variabile}',
    '["variabile", "altra_variabile"]',
    TRUE
);
```

### Usare Template Custom

```php
$emailManager = new EmailManager($pdo);

$emailManager->sendFromTemplate(
    'custom-template',  // Codice template
    [
        'email' => 'cliente@example.com',
        'nome' => 'Mario Rossi'
    ],
    [
        'variabile' => 'Valore 1',
        'altra_variabile' => 'Valore 2'
    ],
    [
        'cliente_id' => 123,
        'priorita' => 'alta'
    ]
);
```

---

## 🤖 Email Automatiche

### Eventi Trigger

| Evento | Hook | Email Inviata |
|--------|------|---------------|
| Nuovo cliente | `onClienteRegistrato()` | benvenuto-cliente |
| Servizio attivato | `onServizioAttivato()` | servizio-attivato |
| Fattura emessa | `onFatturaEmessa()` | fattura-emessa |
| Pagamento ricevuto | `onPagamentoRicevuto()` | pagamento-ricevuto |
| Richiesta addestramento | `onRichiestaAddestramentoRicevuta()` | Conferma richiesta |
| Servizio disattivato | `onServizioDisattivato()` | Notifica disattivazione |

### Scadenze Automatiche (CRON)

**Script:** `invia-email-scadenze.php`
**Frequenza:** Giornaliera (9:00 AM)

**Cosa fa:**
1. ✅ Promemoria fatture in scadenza (3 giorni prima)
2. ✅ Sollecito primo livello (7 giorni dopo scadenza)
3. ✅ Conferme pagamenti ricevuti (giorno stesso)

**Logica solleciti:**
- Invia SOLO se non già inviato oggi
- Verifica in `email_log` per evitare duplicati
- Priorità `alta` per solleciti

### Integrare Hook nel Codice

#### Esempio: Nuovo Cliente

```php
// In registrazione.php o signup API

// ... codice registrazione cliente ...

if ($clienteCreato) {
    // Trigger email benvenuto
    require_once 'includes/email-hooks.php';
    onClienteRegistrato($pdo, $nuovoClienteId);
}
```

#### Esempio: Fattura Pagata

```php
// In webhook-stripe.php dopo pagamento confermato

// Aggiorna fattura
$stmt = $pdo->prepare('UPDATE fatture SET stato = "pagata" WHERE id = :id');
$stmt->execute(['id' => $fatturaId]);

// Trigger conferma pagamento
require_once '../includes/email-hooks.php';
onPagamentoRicevuto($pdo, $fatturaId, $importo, 'stripe', $paymentIntentId);
```

---

## 🔌 API e Hook

### Classe EmailManager

#### Metodi Principali

```php
// Carica template
$template = $emailManager->getTemplate('codice-template');

// Render template con variabili
$rendered = $emailManager->renderTemplate($template, ['var' => 'valore']);

// Invia da template
$queueId = $emailManager->sendFromTemplate(
    'codice-template',
    ['email' => 'dest@example.com', 'nome' => 'Nome'],
    ['variabile' => 'valore'],
    ['priorita' => 'alta', 'cliente_id' => 123]
);

// Aggiungi a coda manualmente
$queueId = $emailManager->addToQueue([...]);

// Processa coda (CRON)
$result = $emailManager->processQueue(50);

// Statistiche
$stats = $emailManager->getStatistics(30); // ultimi 30 giorni
```

### Hook Functions

```php
// Benvenuto cliente
onClienteRegistrato($pdo, $clienteId);

// Servizio attivato
onServizioAttivato($pdo, $userId, $servizioId);

// Fattura emessa
onFatturaEmessa($pdo, $fatturaId);

// Pagamento ricevuto
onPagamentoRicevuto($pdo, $fatturaId, $importo, $metodo, $riferimento);

// Richiesta addestramento
onRichiestaAddestramentoRicevuta($pdo, $richiestaId);

// Servizio disattivato
onServizioDisattivato($pdo, $userId, $servizioId, $motivazione);
```

### Helper Shortcut

```php
// Email benvenuto
sendWelcomeEmail($pdo, $cliente);

// Email fattura
sendInvoiceEmail($pdo, $fattura, $cliente);
```

---

## ⏰ CRON Jobs

### processa-coda-email.php

**Frequenza:** Ogni 5 minuti
**Limite:** 50 email per esecuzione

**Cosa fa:**
1. Recupera email da coda con priorità
2. Invia email via SMTP/mail()
3. Registra in `email_log`
4. Gestisce retry su fallimenti (max 3)
5. Log attività

**Priorità Processing:**
1. Urgente
2. Alta
3. Normale
4. Bassa

**Configurazione:**
```bash
*/5 * * * * php /path/to/processa-coda-email.php >> /var/log/email-queue.log 2>&1
```

**Monitoraggio:**
```bash
tail -f area-clienti/cron/logs/email-queue-2025-01.log
```

### invia-email-scadenze.php

**Frequenza:** Giornaliera 9:00 AM
**Limite:** Nessuno (processa tutte)

**Cosa fa:**
1. Trova fatture in scadenza (3 giorni)
2. Trova fatture scadute 7 giorni (sollecito)
3. Trova pagamenti ricevuti oggi
4. Invia email appropriate
5. Log attività

**Configurazione:**
```bash
0 9 * * * php /path/to/invia-email-scadenze.php >> /var/log/email-scadenze.log 2>&1
```

**Personalizzazione Giorni:**

Modifica le query nello script:
```php
// Cambio da 3 a 5 giorni preavviso
WHERE f.data_scadenza = DATE_ADD(CURDATE(), INTERVAL 5 DAY)

// Cambio sollecito da 7 a 10 giorni
WHERE f.data_scadenza = DATE_SUB(CURDATE(), INTERVAL 10 DAY)
```

---

## 📊 Tracking e Analytics

### Stato Email

| Stato | Significato |
|-------|-------------|
| `in_coda` | In attesa di invio |
| `inviata` | Inviata con successo |
| `fallita` | Invio fallito dopo 3 tentativi |
| `aperta` | Email aperta dal destinatario |
| `click` | Link cliccato |

### Statistiche

#### Query Statistiche Base

```sql
-- Statistiche ultimi 30 giorni
SELECT * FROM v_email_statistics
WHERE data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY data DESC;

-- Tasso apertura per template
SELECT
    et.nome,
    COUNT(el.id) AS totale_inviate,
    SUM(CASE WHEN el.stato IN ('aperta', 'click') THEN 1 ELSE 0 END) AS aperte,
    ROUND(SUM(CASE WHEN el.stato IN ('aperta', 'click') THEN 1 ELSE 0 END) * 100.0 / COUNT(el.id), 2) AS tasso_apertura
FROM email_log el
JOIN email_templates et ON el.template_id = et.id
WHERE el.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY et.id, et.nome
ORDER BY tasso_apertura DESC;

-- Email fallite
SELECT
    destinatario_email,
    oggetto,
    errore,
    created_at
FROM email_log
WHERE stato = 'fallita'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

#### Usando EmailManager

```php
$stats = $emailManager->getStatistics(30);

echo "Totale email: {$stats['totale']}\n";
echo "Inviate: {$stats['inviate']}\n";
echo "Fallite: {$stats['fallite']}\n";
echo "Aperte: {$stats['aperte']}\n";
echo "Click: {$stats['click']}\n";
echo "Tasso apertura: {$stats['tasso_apertura']}%\n";
echo "Tasso click: {$stats['tasso_click']}%\n";
```

### Tracking Aperture (Avanzato)

Per trackare aperture, aggiungi pixel invisibile in template:

```html
<img src="https://tuosito.it/area-clienti/api/track-open.php?id={email_log_id}" width="1" height="1" style="display:none;" />
```

Crea `track-open.php`:
```php
<?php
require 'includes/db.php';

$emailLogId = (int)($_GET['id'] ?? 0);

if ($emailLogId) {
    $stmt = $pdo->prepare('
        UPDATE email_log
        SET stato = "aperta",
            data_apertura = CURRENT_TIMESTAMP,
            ip_apertura = :ip,
            user_agent = :ua
        WHERE id = :id AND stato = "inviata"
    ');
    $stmt->execute([
        'id' => $emailLogId,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Pixel trasparente 1x1
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
```

---

## 💡 Best Practices

### 1. Non Sovraccaricare

✅ **Limita Invii:**
- Massimo 50 email per CRON run
- Pausa 1 secondo tra invii
- Monitora bounce rate

❌ **Evita:**
- Invio email in loop infiniti
- Centinaia di email istantanee
- Nessun throttling

### 2. Personalizzazione

✅ **Sempre:**
- Usa nome destinatario
- Personalizza contenuto
- Variabili dinamiche

❌ **Mai:**
- Email generiche "Gentile Cliente"
- Contenuto identico per tutti
- Ignorare contesto utente

### 3. Gestione Errori

✅ **Implementa:**
- Retry logic (3 tentativi)
- Log errori dettagliati
- Alert su troppi fallimenti

❌ **Evita:**
- Ignorare email fallite
- Nessun logging
- Retry infiniti

### 4. GDPR e Privacy

✅ **Rispetta:**
- Consenso email marketing
- Opt-out facile (unsubscribe)
- Non vendere dati email

❌ **Mai:**
- Spam
- Vendere liste email
- Ignorare unsubscribe

### 5. Testing

✅ **Test sempre:**
- Template su diversi client email
- Variabili rendering
- Link funzionanti
- Responsive design

❌ **Non:**
- Testare solo su un client
- Assumere funzioni senza test
- Ignorare mobile

### 6. Monitoraggio

✅ **Monitora:**
- Tasso apertura (>20% buono)
- Tasso click (>2% buono)
- Bounce rate (<5% buono)
- Email in coda

❌ **Ignora:**
- Metriche per mesi
- Coda in crescita infinita
- Troppi bounce

---

## 🆘 Troubleshooting

### Email Non Inviate

**Problema:** Email restano in coda
**Soluzione:**
```sql
SELECT * FROM email_queue WHERE stato = 'in_coda' ORDER BY created_at;
```
- Verifica CRON attivo
- Controlla errori in `email_queue.errore`
- Verifica configurazione SMTP

### Email Finiscono in Spam

**Problema:** Tutte le email vanno in spam
**Soluzione:**
1. Configura SPF/DKIM/DMARC per dominio
2. Usa SMTP autenticato (non PHP mail())
3. Evita parole spam ("GRATIS", "OFFERTA", ecc)
4. Riscalda IP gradualmente

### Troppi Bounce

**Problema:** Molte email rimbalzano
**Soluzione:**
```sql
SELECT destinatario_email, COUNT(*)
FROM email_log
WHERE stato = 'bounce'
GROUP BY destinatario_email
HAVING COUNT(*) > 3;
```
- Pulisci email invalide
- Verifica blacklist domini
- Implementa verifica email al signup

### Coda Troppo Grande

**Problema:** Migliaia di email in coda
**Soluzione:**
1. Aumenta frequenza CRON (da 5 a 1 minuto)
2. Aumenta limite processamento (da 50 a 100)
3. Usa priorità per processare urgenti prima
4. Considera servizio email esterno (SendGrid/Mailgun)

---

## ✅ Checklist

### Setup Iniziale
- [ ] Database installato
- [ ] Template predefiniti presenti
- [ ] EmailManager testato
- [ ] CRON configurati
- [ ] SMTP produzione configurato

### Testing
- [ ] Test invio template
- [ ] Test coda priorità
- [ ] Test retry fallimenti
- [ ] Test hook eventi
- [ ] Test email su Gmail/Outlook/Apple

### Monitoraggio
- [ ] Log CRON verificati
- [ ] Statistiche controllate settimanalmente
- [ ] Alert configurati per troppi fallimenti
- [ ] Bounce rate < 5%
- [ ] Tasso apertura > 20%

### Ottimizzazione
- [ ] SPF/DKIM configurati
- [ ] Template ottimizzati mobile
- [ ] Link tracking implementato
- [ ] Unsubscribe link presente
- [ ] A/B testing oggetti email

---

## 🎉 Conclusione

Hai ora un sistema email completo e professionale:

✅ 5 Template pronti all'uso
✅ Coda intelligente con priorità
✅ 6 Email automatiche integrate
✅ CRON jobs configurabili
✅ Tracking e analytics
✅ Best practices implementate

**Prossimi passi:**

1. Installa database e testa invio
2. Configura CRON jobs
3. Personalizza template
4. Monitora statistiche
5. Ottimizza tasso apertura

Buon lavoro con il sistema email! 📧
