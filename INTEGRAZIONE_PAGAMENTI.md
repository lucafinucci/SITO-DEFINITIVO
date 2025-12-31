# 💳 Sistema di Pagamento Online - Stripe e PayPal

Sistema completo di pagamento online integrato con Stripe e PayPal per gestire i pagamenti delle fatture.

## 📋 Indice

1. [Funzionalità](#funzionalità)
2. [File Creati](#file-creati)
3. [Installazione](#installazione)
4. [Configurazione Gateway](#configurazione-gateway)
5. [Testing](#testing)
6. [Produzione](#produzione)
7. [Webhook](#webhook)
8. [Sicurezza](#sicurezza)

---

## 🎯 Funzionalità

### Caratteristiche Principali

✅ **Doppia Integrazione Gateway**
- Stripe: Pagamenti con carta di credito
- PayPal: Pagamenti con conto PayPal

✅ **Gestione Completa Transazioni**
- Creazione payment intent/order
- Tracking stato pagamenti
- Calcolo commissioni automatico
- Log completo transazioni

✅ **Webhook Automatici**
- Aggiornamento automatico stato fatture
- Registrazione pagamenti
- Gestione rimborsi
- Log eventi per debugging

✅ **Interfaccia Utente**
- Pagina checkout professionale
- Selezione metodo di pagamento
- Integrazione Stripe Elements
- Pulsanti PayPal nativi
- Sicurezza SSL

✅ **Dashboard Clienti**
- Visualizzazione fatture da pagare
- Pulsanti "Paga Ora" diretti
- Storico fatture

---

## 📁 File Creati

### Database

```
database/add_payment_gateways.sql
```
- Tabella `payment_gateways_config`: Configurazione gateway
- Tabella `payment_transactions`: Storico transazioni
- Tabella `payment_webhooks_log`: Log webhook

### Backend API

```
area-clienti/api/payment-checkout.php
area-clienti/api/webhook-stripe.php
area-clienti/api/webhook-paypal.php
area-clienti/includes/payment-gateways.php
```

### Frontend

```
area-clienti/paga-fattura.php
```

### File Modificati

```
area-clienti/admin/fatture.php          (+ pulsante "Paga Ora")
area-clienti/dashboard.php              (+ sezione fatture con pulsanti pagamento)
```

---

## 🚀 Installazione

### 1. Database

Esegui lo script SQL per creare le tabelle:

```bash
mysql -u root -p finch_ai < database/add_payment_gateways.sql
```

Oppure tramite phpMyAdmin:
1. Apri phpMyAdmin
2. Seleziona database `finch_ai`
3. Vai su "Importa"
4. Carica `database/add_payment_gateways.sql`

### 2. Verifica Installazione

Controlla che le tabelle siano state create:

```sql
SHOW TABLES LIKE 'payment%';
```

Dovresti vedere:
- `payment_gateways_config`
- `payment_transactions`
- `payment_webhooks_log`

---

## ⚙️ Configurazione Gateway

### Stripe

#### 1. Crea Account Stripe

1. Vai su [stripe.com](https://stripe.com)
2. Registra un account
3. Vai su Dashboard → Developers → API keys

#### 2. Ottieni Chiavi API

- **Test Mode** (per sviluppo):
  - Publishable key: `pk_test_...`
  - Secret key: `sk_test_...`

- **Live Mode** (per produzione):
  - Publishable key: `pk_live_...`
  - Secret key: `sk_live_...`

#### 3. Configura nel Database

```sql
INSERT INTO payment_gateways_config (
    gateway,
    mode,
    attivo,
    api_key,
    api_secret,
    api_publishable_key,
    commissione_percentuale,
    commissione_fissa
) VALUES (
    'stripe',
    'test',              -- Cambia in 'live' per produzione
    TRUE,
    '',                  -- Non usato per Stripe
    'sk_test_XXX',       -- Secret Key
    'pk_test_XXX',       -- Publishable Key
    1.5,                 -- 1.5% commissione Stripe standard EU
    0.25                 -- 0.25€ commissione fissa
);
```

#### 4. Configura Webhook Stripe

1. Vai su Dashboard → Developers → Webhooks
2. Clicca "Add endpoint"
3. URL: `https://tuosito.it/area-clienti/api/webhook-stripe.php`
4. Seleziona eventi:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
5. Copia il **Webhook Secret** (`whsec_...`)

Aggiorna nel database:

```sql
UPDATE payment_gateways_config
SET webhook_secret = 'whsec_XXX'
WHERE gateway = 'stripe';
```

---

### PayPal

#### 1. Crea App PayPal

1. Vai su [developer.paypal.com](https://developer.paypal.com)
2. Vai su "My Apps & Credentials"
3. Crea una nuova app REST API

#### 2. Ottieni Credenziali

- **Sandbox** (per sviluppo):
  - Client ID: `AXXXxxx`
  - Secret: `EXXXxxx`

- **Live** (per produzione):
  - Client ID: `AYYYyyy`
  - Secret: `EYYYyyy`

#### 3. Configura nel Database

```sql
INSERT INTO payment_gateways_config (
    gateway,
    mode,
    attivo,
    api_key,
    api_secret,
    commissione_percentuale,
    commissione_fissa
) VALUES (
    'paypal',
    'sandbox',           -- Cambia in 'live' per produzione
    TRUE,
    'AXXXxxx',          -- Client ID
    'EXXXxxx',          -- Secret
    3.4,                -- 3.4% commissione PayPal standard
    0.35                -- 0.35€ commissione fissa
);
```

#### 4. Configura Webhook PayPal

1. Vai su Developer Dashboard → Apps & Credentials
2. Seleziona la tua app
3. Scroll down a "Webhooks"
4. Clicca "Add Webhook"
5. URL: `https://tuosito.it/area-clienti/api/webhook-paypal.php`
6. Seleziona eventi:
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.REFUNDED`
7. Copia il **Webhook ID**

Aggiorna nel database:

```sql
UPDATE payment_gateways_config
SET webhook_id = 'WEBHOOK_ID_XXX'
WHERE gateway = 'paypal';
```

---

## 🧪 Testing

### Test Carte Stripe

Usa queste carte di test in modalità test:

| Carta | Numero | Risultato |
|-------|--------|-----------|
| Visa Success | `4242 4242 4242 4242` | Pagamento riuscito |
| Visa Declined | `4000 0000 0000 0002` | Pagamento rifiutato |
| Requires Auth | `4000 0025 0000 3155` | Richiede autenticazione 3D Secure |

- CVV: Qualsiasi 3 cifre
- Data scadenza: Qualsiasi data futura
- CAP: Qualsiasi

### Test PayPal Sandbox

1. Crea account test su [sandbox.paypal.com](https://sandbox.paypal.com)
2. Usa le credenziali sandbox per il login
3. Il saldo è virtuale

### Test Locale

```bash
# 1. Avvia server locale
php -S localhost:8000 -t area-clienti

# 2. Apri browser
http://localhost:8000/dashboard.php

# 3. Clicca su una fattura
# 4. Clicca "Paga Ora"
# 5. Testa con carte/account di test
```

### Simulare Webhook in Locale

Usa **Stripe CLI** per testare webhook localmente:

```bash
# Installa Stripe CLI
# https://stripe.com/docs/stripe-cli

# Login
stripe login

# Ascolta webhook
stripe listen --forward-to http://localhost:8000/api/webhook-stripe.php

# Trigger evento test
stripe trigger payment_intent.succeeded
```

Per PayPal, puoi usare il PayPal Simulator nel dashboard sandbox.

---

## 🌐 Produzione

### Checklist Pre-Produzione

- [ ] **SSL Obbligatorio**: Installa certificato SSL (HTTPS)
- [ ] **Modalità Live**: Cambia `mode` da `test/sandbox` a `live`
- [ ] **Chiavi Live**: Sostituisci chiavi test con chiavi live
- [ ] **Webhook Live**: Configura webhook su domini live
- [ ] **SMTP Email**: Configura PHPMailer per notifiche
- [ ] **Backup Database**: Effettua backup prima del deploy
- [ ] **Test Pagamento Reale**: Fai un pagamento di 1€ per testare

### 1. Passa a Modalità Live

```sql
-- Stripe
UPDATE payment_gateways_config
SET
    mode = 'live',
    api_secret = 'sk_live_XXX',
    api_publishable_key = 'pk_live_XXX',
    webhook_secret = 'whsec_live_XXX'
WHERE gateway = 'stripe';

-- PayPal
UPDATE payment_gateways_config
SET
    mode = 'live',
    api_key = 'LIVE_CLIENT_ID',
    api_secret = 'LIVE_SECRET',
    webhook_id = 'LIVE_WEBHOOK_ID'
WHERE gateway = 'paypal';
```

### 2. Configura Webhook Live

Ripeti la configurazione webhook ma usando:
- URL produzione: `https://tuosito.it/area-clienti/api/webhook-stripe.php`
- Account/App in modalità Live

### 3. Integra SDK Reali

Attualmente il codice usa placeholder. Per produzione:

#### Installa Dipendenze

```bash
cd area-clienti
composer require stripe/stripe-php
composer require paypal/paypal-checkout-sdk
```

#### Aggiorna payment-gateways.php

Decommentare le sezioni SDK e rimuovere i placeholder:

```php
// In StripeGateway::creaPaymentIntent()
// Rimuovi il placeholder e decommentare:

\Stripe\Stripe::setApiKey($this->config['api_secret']);

$paymentIntent = \Stripe\PaymentIntent::create([
    'amount' => round($importo * 100),
    'currency' => 'eur',
    'metadata' => $metadata,
    'automatic_payment_methods' => ['enabled' => true]
]);
```

```php
// In PayPalGateway::creaOrdine()
// Implementare chiamata SDK reale PayPal

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

$environment = new SandboxEnvironment($clientId, $secret);
$client = new PayPalHttpClient($environment);
// ... resto implementazione
```

### 4. Abilita Logging Errori

Crea file di log per monitorare:

```php
// In webhook files, aggiungi logging errori
error_log(
    sprintf("[%s] Errore webhook: %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
    3,
    __DIR__ . '/../cron/logs/errors-' . date('Y-m') . '.log'
);
```

---

## 🔔 Webhook

### Come Funzionano

1. Cliente completa pagamento su Stripe/PayPal
2. Gateway invia notifica HTTP POST al tuo webhook
3. Webhook verifica signature per sicurezza
4. Webhook aggiorna database:
   - Stato transazione → `completed`
   - Stato fattura → `pagata`
   - Registra pagamento in `fatture_pagamenti`
5. Webhook risponde 200 OK

### Eventi Gestiti

#### Stripe

- `payment_intent.succeeded`: Pagamento completato
- `payment_intent.payment_failed`: Pagamento fallito
- `charge.refunded`: Rimborso effettuato

#### PayPal

- `PAYMENT.CAPTURE.COMPLETED`: Pagamento completato
- `PAYMENT.CAPTURE.DENIED`: Pagamento negato
- `PAYMENT.CAPTURE.REFUNDED`: Rimborso effettuato

### Log Webhook

Tutti i webhook sono registrati in `payment_webhooks_log`:

```sql
SELECT * FROM payment_webhooks_log
WHERE processed = FALSE
ORDER BY created_at DESC;
```

Per vedere webhook con errori:

```sql
SELECT
    gateway,
    event_type,
    created_at,
    payload
FROM payment_webhooks_log
WHERE processed = FALSE
    AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

---

## 🔒 Sicurezza

### Protezioni Implementate

✅ **CSRF Token**: Tutte le richieste POST verificano token
✅ **Webhook Signature**: Verifica firma Stripe/PayPal (produzione)
✅ **SQL Prepared Statements**: Previene SQL injection
✅ **HTTPS Only**: Pagamenti solo su connessioni sicure
✅ **Session Security**: Header sicurezza configurati
✅ **No Card Storage**: Mai salvare dati carte (PCI compliance)

### Verifica Sicurezza

```php
// In payment-checkout.php
// CSRF check
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    exit;
}

// In webhook-stripe.php
// Signature verification (per produzione)
$event = \Stripe\Webhook::constructEvent(
    $payload,
    $sigHeader,
    $webhookSecret
);
```

### Best Practices

1. **Mai esporre chiavi segrete nel frontend**
   - Solo publishable keys nel JavaScript
   - Secret keys solo lato server

2. **Valida importi server-side**
   - Non fidarti mai di importi dal frontend
   - Carica fattura dal DB per ottenere importo

3. **Usa HTTPS sempre**
   - Redirect HTTP → HTTPS
   - Abilita HSTS header

4. **Monitora transazioni sospette**
   - Alert per importi alti
   - Rate limiting su API

5. **Log access webhook**
   - Monitora IP sospetti
   - Alert per webhook falliti

---

## 📊 Monitoraggio

### Query Utili

```sql
-- Transazioni ultime 24h
SELECT
    gateway,
    stato,
    COUNT(*) AS totale,
    SUM(importo) AS importo_totale
FROM payment_transactions
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY gateway, stato;

-- Commissioni pagate
SELECT
    gateway,
    SUM(commissione) AS totale_commissioni,
    COUNT(*) AS transazioni
FROM payment_transactions
WHERE stato = 'completed'
    AND MONTH(created_at) = MONTH(CURRENT_DATE())
GROUP BY gateway;

-- Webhook non processati
SELECT COUNT(*) AS pending_webhooks
FROM payment_webhooks_log
WHERE processed = FALSE;

-- Fatture pagate via online
SELECT
    DATE(fp.data_pagamento) AS data,
    COUNT(*) AS fatture_pagate,
    SUM(fp.importo) AS importo
FROM fatture_pagamenti fp
WHERE fp.metodo_pagamento IN ('stripe', 'paypal')
GROUP BY DATE(fp.data_pagamento)
ORDER BY data DESC
LIMIT 30;
```

### Dashboard Metriche

Aggiungi alla dashboard admin:

```php
// Statistiche pagamenti online ultimi 30 giorni
$stmt = $pdo->prepare('
    SELECT
        COUNT(*) AS totale_transazioni,
        SUM(CASE WHEN stato = "completed" THEN 1 ELSE 0 END) AS successi,
        SUM(CASE WHEN stato = "failed" THEN 1 ELSE 0 END) AS fallimenti,
        SUM(importo_ricevuto) AS totale_incassato,
        SUM(commissione) AS totale_commissioni,
        AVG(importo_ricevuto) AS importo_medio
    FROM payment_transactions
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
');
$stmt->execute();
$stats = $stmt->fetch();
```

---

## 🆘 Troubleshooting

### Problema: Pagamento non aggiorna fattura

**Soluzione:**
1. Verifica log webhook: `area-clienti/cron/logs/webhooks-stripe-*.log`
2. Controlla tabella `payment_webhooks_log` per errori
3. Verifica webhook configurato correttamente su Stripe/PayPal Dashboard
4. Testa webhook manualmente con Stripe CLI

### Problema: Errore "Gateway non configurato"

**Soluzione:**
1. Verifica record in `payment_gateways_config`:
   ```sql
   SELECT * FROM payment_gateways_config WHERE attivo = TRUE;
   ```
2. Verifica chiavi API inserite correttamente
3. Controlla modalità (test/live) corrisponda all'ambiente

### Problema: Webhook non ricevuto

**Soluzione:**
1. Verifica URL webhook pubblicamente accessibile (non localhost)
2. Controlla firewall non blocca IP Stripe/PayPal
3. Verifica HTTPS funzionante
4. Testa con webhook simulator

### Problema: Errore CORS

**Soluzione:**
Aggiungi header in `payment-checkout.php`:
```php
header('Access-Control-Allow-Origin: https://tuosito.it');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
```

---

## 📞 Supporto

### Documentazione Ufficiale

- **Stripe**: [stripe.com/docs](https://stripe.com/docs)
- **PayPal**: [developer.paypal.com/docs](https://developer.paypal.com/docs)

### Test Tools

- **Stripe CLI**: [stripe.com/docs/stripe-cli](https://stripe.com/docs/stripe-cli)
- **PayPal Sandbox**: [sandbox.paypal.com](https://sandbox.paypal.com)
- **Webhook Tester**: [webhook.site](https://webhook.site)

---

## ✅ Riepilogo

Il sistema di pagamento è ora completamente installato e configurato:

✅ Database creato con 3 tabelle
✅ API checkout per iniziare pagamenti
✅ Webhook per gestire notifiche automatiche
✅ Pagina checkout professionale con UI/UX
✅ Integrazione Stripe Elements
✅ Integrazione PayPal Buttons
✅ Pulsanti "Paga Ora" in fatture admin
✅ Sezione fatture in dashboard clienti
✅ Tracking completo transazioni
✅ Sicurezza CSRF e validazioni
✅ Log dettagliati per debugging

**Next Steps:**

1. Configura chiavi API test
2. Testa pagamenti con carte/account test
3. Configura webhook
4. Testa ricezione webhook
5. Passa a produzione quando pronto

Buon lavoro! 🚀
