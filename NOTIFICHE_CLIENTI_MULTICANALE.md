# Sistema Notifiche Clienti Multi-Canale

Sistema completo di notifiche per i clienti con supporto Email, SMS e Browser, con preferenze personalizzabili per ogni tipo di evento.

## Indice

1. [Panoramica](#panoramica)
2. [Architettura](#architettura)
3. [Installazione](#installazione)
4. [Configurazione SMS](#configurazione-sms)
5. [Utilizzo](#utilizzo)
6. [Preferenze Utente](#preferenze-utente)
7. [Template](#template)
8. [API Reference](#api-reference)
9. [Esempi Pratici](#esempi-pratici)
10. [Monitoraggio](#monitoraggio)
11. [Risoluzione Problemi](#risoluzione-problemi)

---

## Panoramica

Il sistema di notifiche clienti estende le notifiche admin esistenti con:

### Caratteristiche Principali

- **Multi-Canale**: Browser, Email, SMS
- **Preferenze Personalizzate**: Ogni cliente sceglie come ricevere le notifiche
- **7 Tipi di Notifiche** per clienti:
  - Servizio Attivato
  - Servizio Disattivato
  - Fattura Emessa
  - Fattura in Scadenza (promemoria)
  - Pagamento Confermato
  - Aggiornamento Servizio
  - Manutenzione Sistema

- **Multi-Provider SMS**: Supporto per Twilio, Vonage (Nexmo), AWS SNS
- **Validazione Automatica**: Numeri telefono formato internazionale
- **Tracking Completo**: Log di tutte le notifiche inviate
- **Template SMS**: Template predefiniti per ciascun tipo di evento

---

## Architettura

### Schema Database

```
notifiche (estesa)
├── canale: ENUM('browser', 'email', 'sms', 'push')
├── stato_invio: ENUM('pending', 'sent', 'failed', 'delivered')
├── inviato_at: TIMESTAMP
├── errore_invio: TEXT
└── tentativi_invio: INT

notifiche_preferenze (estesa)
├── telefono_sms: VARCHAR(20)
├── sms_enabled: BOOLEAN
├── push_enabled: BOOLEAN
├── servizio_attivato_canale: ENUM('email', 'sms', 'entrambi', 'nessuno')
├── servizio_disattivato_canale: ENUM(...)
├── fattura_emessa_canale: ENUM(...)
├── fattura_scadenza_canale: ENUM(...)
├── pagamento_confermato_canale: ENUM(...)
└── aggiornamento_sistema_canale: ENUM(...)

sms_config
├── provider: ENUM('twilio', 'vonage', 'aws_sns', 'custom')
├── api_key: VARCHAR(255)
├── api_secret: VARCHAR(255)
├── sender_number: VARCHAR(20)
└── attivo: BOOLEAN

sms_log
├── destinatario_numero: VARCHAR(20)
├── messaggio: TEXT
├── stato: ENUM('pending', 'sent', 'delivered', 'failed', 'undelivered')
├── provider: VARCHAR(50)
├── message_id: VARCHAR(255)
├── costo: DECIMAL(10,4)
└── tracking timestamps

sms_templates
├── codice: VARCHAR(100) UNIQUE
├── messaggio: TEXT (max 160 caratteri)
├── variabili_disponibili: JSON
└── tipo_notifica: VARCHAR(100)
```

### Classi PHP

```
NotificheManager (esteso)
├── inviaMultiCanale()          → Routing automatico canali
├── determinaCanali()           → Legge preferenze utente
├── inviaViaEmail()             → Integrazione EmailManager
├── inviaViaSMS()               → Integrazione SMSManager
└── getPreferenze()             → Recupera preferenze utente

SMSManager (nuovo)
├── send()                      → Invio SMS diretto
├── sendFromTemplate()          → Invio da template
├── sendViaProvider()           → Routing provider
├── sendViaTwilio()            → Implementazione Twilio
├── sendViaVonage()            → Implementazione Vonage
├── sendViaAWS()               → Implementazione AWS SNS
├── validaTelefono()           → Validazione formato internazionale
└── getStatistiche()           → Metriche SMS
```

---

## Installazione

### 1. Database

```bash
mysql -u root -p finch_ai < database/add_notifiche_clienti.sql
```

Lo script esegue:
- ✅ Estende tabella `notifiche` con supporto canali
- ✅ Estende `notifiche_preferenze` con configurazioni SMS
- ✅ Crea tabelle `sms_config`, `sms_log`, `sms_templates`
- ✅ Inserisce 6 template SMS predefiniti
- ✅ Crea preferenze default per clienti esistenti
- ✅ Crea vista `v_notifiche_clienti` filtrata
- ✅ Crea vista `v_sms_statistiche` per analytics
- ✅ Event auto-cleanup vecchi SMS (90 giorni)

### 2. File PHP

Tutti i file sono già stati creati:

```
area-clienti/
├── includes/
│   ├── sms-manager.php                    ← Gestione SMS
│   └── notifiche-manager.php (esteso)     ← Multi-canale
└── preferenze-notifiche.php               ← UI preferenze cliente
```

### 3. Verifica Installazione

```bash
# Controlla tabelle
mysql> SHOW TABLES LIKE '%sms%';
mysql> SHOW TABLES LIKE '%notifiche%';

# Verifica template SMS
mysql> SELECT codice, nome FROM sms_templates;

# Output atteso:
# +-------------------------+------------------------+
# | codice                  | nome                   |
# +-------------------------+------------------------+
# | servizio-attivato-sms   | Servizio Attivato      |
# | fattura-emessa-sms      | Fattura Emessa         |
# | fattura-scadenza-sms    | Promemoria Scadenza    |
# | pagamento-confermato-sms| Pagamento Confermato   |
# | servizio-disattivato-sms| Servizio Disattivato   |
# | manutenzione-sms        | Manutenzione Programmata|
# +-------------------------+------------------------+
```

---

## Configurazione SMS

### Opzione 1: Twilio (Consigliato)

1. **Crea account**: https://www.twilio.com/try-twilio
2. **Ottieni credenziali**:
   - Account SID → `api_key`
   - Auth Token → `api_secret`
   - Numero Twilio → `sender_number`

3. **Inserisci configurazione**:

```sql
INSERT INTO sms_config (provider, api_key, api_secret, sender_number, attivo)
VALUES (
    'twilio',
    'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',   -- Account SID
    'your_auth_token_here',
    '+393123456789',                        -- Tuo numero Twilio
    TRUE
);
```

**Costi Twilio** (2024):
- SMS Italia: ~€0.075/SMS
- SMS Internazionali: €0.05-0.15/SMS
- Numero virtuale: ~€1/mese

### Opzione 2: Vonage (Nexmo)

1. **Crea account**: https://dashboard.nexmo.com/sign-up
2. **Ottieni credenziali**:
   - API Key
   - API Secret
   - Numero virtuale

3. **Inserisci configurazione**:

```sql
INSERT INTO sms_config (provider, api_key, api_secret, sender_number, attivo)
VALUES (
    'vonage',
    'your_api_key',
    'your_api_secret',
    'FinchAI',  -- Può essere alphanumerico (max 11 caratteri)
    TRUE
);
```

**Costi Vonage** (2024):
- SMS Italia: ~€0.064/SMS
- Numero virtuale: ~€0.90/mese

### Opzione 3: AWS SNS

1. **Prerequisito**: Installa AWS SDK

```bash
cd area-clienti
composer require aws/aws-sdk-php
```

2. **Configura AWS**:

```sql
INSERT INTO sms_config (provider, api_key, api_secret, sender_number, attivo)
VALUES (
    'aws_sns',
    'your_aws_access_key_id',
    'your_aws_secret_access_key',
    'N/A',  -- Non necessario per SNS
    TRUE
);
```

3. **Configura IAM Policy**:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "sns:Publish"
            ],
            "Resource": "*"
        }
    ]
}
```

**Costi AWS SNS** (2024):
- SMS Transazionali: ~€0.06/SMS (Italia)
- Senza costi di setup

### Test Configurazione

```php
<?php
require 'includes/db.php';
require 'includes/sms-manager.php';

$smsManager = new SMSManager($pdo);

// Test invio
$result = $smsManager->send(
    '+393123456789',  // Tuo numero di test
    'Test SMS da Finch-AI. Sistema configurato correttamente!',
    []
);

if ($result) {
    echo "✓ SMS inviato con successo!\n";
} else {
    echo "✗ Errore invio SMS\n";
}
```

---

## Utilizzo

### Notificare un Cliente

#### 1. Servizio Attivato

```php
require 'includes/notifiche-manager.php';

// Dopo attivazione servizio
$servizio = [
    'id' => 5,
    'nome' => 'Document Intelligence Pro',
    'descrizione' => 'Analisi avanzata documenti',
    'prezzo_mensile' => 99.00
];

notificaClienteServizioAttivato($pdo, $clienteId, $servizio);

// Risultato:
// - Notifica browser creata
// - Email inviata (se abilitata in preferenze)
// - SMS inviato (se abilitato in preferenze)
```

#### 2. Fattura Emessa

```php
$fattura = [
    'id' => 123,
    'numero_fattura' => 'FINCH-2024-00123',
    'totale' => 199.00,
    'data_scadenza' => '2024-03-15'
];

notificaClienteFatturaEmessa($pdo, $clienteId, $fattura);
```

#### 3. Promemoria Scadenza

```php
// CRON giornaliero: fatture che scadono tra 3 giorni
$stmt = $pdo->prepare('
    SELECT f.*, f.cliente_id
    FROM fatture f
    WHERE f.stato IN ("emessa", "inviata")
      AND f.data_scadenza = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
');
$stmt->execute();

foreach ($stmt->fetchAll() as $fattura) {
    notificaClienteFatturaInScadenza(
        $pdo,
        $fattura['cliente_id'],
        $fattura,
        3  // Giorni mancanti
    );
}
```

#### 4. Pagamento Confermato

```php
// Dopo conferma pagamento Stripe/PayPal
notificaClientePagamentoConfermato(
    $pdo,
    $clienteId,
    $fattura,
    $importoPagato,
    $metodoPagamento  // 'Stripe', 'PayPal', 'Bonifico'
);
```

#### 5. Manutenzione Programmata

```php
// Notifica tutti i clienti attivi
notificaClienteManutenzione(
    $pdo,
    $clienteId,  // Singolo cliente
    '2024-03-20 02:00:00',  // Data/ora manutenzione
    '2 ore',                // Durata
    'Aggiornamento server database per migliorare le performance'
);
```

#### 6. Broadcast a Tutti i Clienti

```php
// Esempio: Nuovo feature rollout
broadcastNotificaClienti(
    $pdo,
    'aggiornamento_servizio',
    'Nuova Funzionalità Disponibile!',
    'Abbiamo rilasciato la nuova dashboard analytics. Scoprila ora!',
    [
        'priorita' => 'normale',
        'icona' => '🚀',
        'link_azione' => '/area-clienti/analytics.php',
        'label_azione' => 'Scopri Analytics',
        'dati_extra' => [
            'feature_name' => 'Analytics Dashboard',
            'release_date' => date('d/m/Y')
        ]
    ]
);

// Notifica TUTTI i clienti con servizi attivi
```

---

## Preferenze Utente

### Interfaccia Web

I clienti accedono alle preferenze tramite:

```
https://finch-ai.it/area-clienti/preferenze-notifiche.php
```

**Configurazioni disponibili**:

1. **Canali Globali**:
   - 🌐 Notifiche Browser (on/off)
   - 📧 Email (on/off)
   - 📱 SMS (on/off + numero telefono)

2. **Preferenze per Evento**:
   Ogni tipo di evento può essere configurato:
   - Solo Email
   - Solo SMS
   - Entrambi (Email + SMS)
   - Nessuno (disabilitato)

### Configurazione Programmatica

```php
// Aggiorna preferenze cliente via codice
$stmt = $pdo->prepare('
    UPDATE notifiche_preferenze
    SET
        sms_enabled = TRUE,
        telefono_sms = :telefono,
        fattura_scadenza_canale = "entrambi"
    WHERE utente_id = :cliente_id
');

$stmt->execute([
    'telefono' => '+393123456789',
    'cliente_id' => $clienteId
]);
```

### Preferenze Default

Alla registrazione, ogni cliente riceve:

```
✅ Browser: Abilitato
✅ Email: Abilitato
❌ SMS: Disabilitato (deve inserire numero)

Canali per evento:
- Servizi: Email
- Fatture: Email
- Scadenze: Entrambi (consigliato)
- Pagamenti: Email
- Aggiornamenti: Email
```

---

## Template

### Template SMS

Tutti i template SMS sono in `sms_templates`:

```sql
-- Visualizza template
SELECT codice, messaggio FROM sms_templates WHERE attivo = TRUE;
```

#### Esempio Template: Fattura in Scadenza

```
Finch-AI: Promemoria! Fattura {numero_fattura} (€{importo}) scade il {data_scadenza}.
Paga ora: {link_pagamento}
```

**Variabili disponibili**: `numero_fattura`, `importo`, `data_scadenza`, `link_pagamento`

**Caratteri**: 159/160 (SMS singolo)

### Creare Nuovo Template

```sql
INSERT INTO sms_templates (
    codice,
    nome,
    descrizione,
    messaggio,
    variabili_disponibili,
    tipo_notifica
) VALUES (
    'nuovo-feature-sms',
    'Nuova Feature',
    'Notifica rilascio nuova funzionalità',
    'Finch-AI: 🚀 Nuova feature "{feature_name}" disponibile! Scoprila: {link}',
    JSON_ARRAY('feature_name', 'link'),
    'aggiornamento_servizio'
);
```

**Limiti SMS**:
- SMS Singolo: max 160 caratteri
- SMS Concatenati: 153 caratteri/parte
- Il sistema notifica automaticamente se >160 caratteri

### Modificare Template Esistente

```sql
UPDATE sms_templates
SET messaggio = 'Nuovo testo del template qui...'
WHERE codice = 'fattura-scadenza-sms';
```

---

## API Reference

### SMSManager

```php
class SMSManager {
    /**
     * Invia SMS da template
     * @return bool Success
     */
    public function sendFromTemplate(
        string $codiceTemplate,
        array $destinatario,  // ['telefono' => '+39...', 'nome' => '...']
        array $variabili,     // ['numero_fattura' => 'F-123', ...]
        array $opzioni = []   // ['notifica_id', 'cliente_id', 'fattura_id']
    )

    /**
     * Invia SMS diretto
     * @return bool Success
     */
    public function send(
        string $numeroDestinatario,  // '+393123456789'
        string $messaggio,            // Max 160 caratteri
        array $opzioni = []
    )

    /**
     * Valida numero telefono
     * @return string|false Numero formattato o false
     */
    public function validaTelefono(string $numero)

    /**
     * Statistiche SMS
     * @return array Statistiche ultimi N giorni
     */
    public function getStatistiche(int $periodo = 30)
}
```

### NotificheManager (Esteso)

```php
class NotificheManager {
    /**
     * Crea notifica con routing multi-canale automatico
     */
    public function crea(array $dati)

    // Metodi privati per routing
    private function inviaMultiCanale($notificaId, $dati)
    private function determinaCanali($tipo, $preferenze)
    private function inviaViaEmail($dati, $preferenze)
    private function inviaViaSMS($dati, $preferenze, $notificaId)
}
```

### Helper Functions Clienti

```php
// Servizi
notificaClienteServizioAttivato($pdo, $clienteId, $servizio)
notificaClienteServizioDisattivato($pdo, $clienteId, $servizio, $motivazione = null)

// Fatturazione
notificaClienteFatturaEmessa($pdo, $clienteId, $fattura)
notificaClienteFatturaInScadenza($pdo, $clienteId, $fattura, $giorniMancanti)

// Pagamenti
notificaClientePagamentoConfermato($pdo, $clienteId, $fattura, $importo, $metodo)

// Altro
notificaClienteAggiornamentoServizio($pdo, $clienteId, $servizio, $tipo, $dettagli)
notificaClienteManutenzione($pdo, $clienteId, $dataOra, $durata, $dettagli)

// Broadcast
broadcastNotificaClienti($pdo, $tipo, $titolo, $messaggio, $opzioni = [])
```

---

## Esempi Pratici

### Scenario 1: Attivazione Servizio con Notifica Multi-Canale

```php
// area-clienti/api/attiva-servizio.php

require '../includes/auth.php';
require '../includes/db.php';
require '../includes/notifiche-manager.php';

// ... logica attivazione servizio ...

// Recupera dati servizio
$stmt = $pdo->prepare('SELECT * FROM servizi WHERE id = :id');
$stmt->execute(['id' => $servizioId]);
$servizio = $stmt->fetch();

// Notifica cliente (automaticamente via tutti i canali abilitati)
notificaClienteServizioAttivato($pdo, $clienteId, $servizio);

// Il sistema:
// 1. Crea notifica in DB
// 2. Controlla preferenze cliente
// 3. Invia email se abilitata
// 4. Invia SMS se abilitato e numero presente
// 5. Notifica browser in tempo reale
```

### Scenario 2: CRON Promemoria Scadenze

```php
// area-clienti/cron/promemoria-scadenze.php

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/notifiche-manager.php';

// Fatture che scadono tra 3 giorni
$stmt = $pdo->prepare('
    SELECT
        f.*,
        u.id AS cliente_id,
        u.email,
        u.azienda
    FROM fatture f
    JOIN utenti u ON f.cliente_id = u.id
    WHERE f.stato IN ("emessa", "inviata")
      AND f.data_scadenza = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
      AND NOT EXISTS (
          SELECT 1 FROM notifiche n
          WHERE n.fattura_id = f.id
            AND n.tipo = "fattura_in_scadenza"
            AND DATE(n.created_at) = CURDATE()
      )
');

$stmt->execute();
$fatture = $stmt->fetchAll();

$log = [];

foreach ($fatture as $fattura) {
    try {
        notificaClienteFatturaInScadenza(
            $pdo,
            $fattura['cliente_id'],
            $fattura,
            3  // 3 giorni
        );

        $log[] = "✓ Promemoria inviato: {$fattura['numero_fattura']} → {$fattura['azienda']}";

    } catch (Exception $e) {
        $log[] = "✗ Errore {$fattura['numero_fattura']}: {$e->getMessage()}";
    }
}

// Scrivi log
$logFile = __DIR__ . '/../../logs/promemoria-scadenze-' . date('Y-m') . '.log';
file_put_contents(
    $logFile,
    date('Y-m-d H:i:s') . "\n" . implode("\n", $log) . "\n\n",
    FILE_APPEND
);
```

**Aggiungi a crontab**:

```cron
# Promemoria scadenze: ogni giorno alle 9:00
0 9 * * * php /var/www/finch-ai/area-clienti/cron/promemoria-scadenze.php
```

### Scenario 3: Webhook Stripe con Notifica

```php
// area-clienti/webhook/stripe.php

require '../includes/db.php';
require '../includes/notifiche-manager.php';

$payload = file_get_contents('php://input');
$event = json_decode($payload);

if ($event->type === 'payment_intent.succeeded') {
    $paymentIntent = $event->data->object;

    // Recupera fattura da metadata
    $fatturaId = $paymentIntent->metadata->fattura_id;

    $stmt = $pdo->prepare('
        SELECT f.*, u.id AS cliente_id
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.id = :id
    ');
    $stmt->execute(['id' => $fatturaId]);
    $fattura = $stmt->fetch();

    if ($fattura) {
        // Aggiorna fattura
        $pdo->prepare('UPDATE fatture SET stato = "pagata" WHERE id = :id')
            ->execute(['id' => $fatturaId]);

        // Notifica cliente (email + SMS se abilitato)
        notificaClientePagamentoConfermato(
            $pdo,
            $fattura['cliente_id'],
            $fattura,
            $paymentIntent->amount / 100,  // Cent → Euro
            'Stripe'
        );

        // Cliente riceve:
        // - Email di conferma con ricevuta
        // - SMS: "Pagamento di €199.00 confermato..."
        // - Notifica browser in area clienti
    }
}
```

---

## Monitoraggio

### Dashboard SMS

```sql
-- Statistiche ultimi 30 giorni
SELECT
    data,
    totale_inviati,
    consegnati,
    falliti,
    tasso_consegna,
    FORMAT(costo_totale, 2) AS costo_eur,
    clienti_unici
FROM v_sms_statistiche
ORDER BY data DESC
LIMIT 30;
```

**Output esempio**:

```
+------------+----------------+------------+---------+----------------+------------+----------------+
| data       | totale_inviati | consegnati | falliti | tasso_consegna | costo_eur  | clienti_unici  |
+------------+----------------+------------+---------+----------------+------------+----------------+
| 2024-03-15 | 127            | 124        | 3       | 97.64          | 9.53       | 89             |
| 2024-03-14 | 95             | 93         | 2       | 97.89          | 7.13       | 67             |
+------------+----------------+------------+---------+----------------+------------+----------------+
```

### Query Utili

```sql
-- SMS falliti oggi
SELECT
    destinatario_numero,
    destinatario_nome,
    messaggio,
    errore,
    created_at
FROM sms_log
WHERE DATE(created_at) = CURDATE()
  AND stato = 'failed'
ORDER BY created_at DESC;

-- Top 10 clienti per SMS ricevuti
SELECT
    u.azienda,
    COUNT(*) AS sms_ricevuti,
    SUM(sl.costo) AS costo_totale
FROM sms_log sl
JOIN utenti u ON sl.cliente_id = u.id
WHERE sl.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY u.id
ORDER BY sms_ricevuti DESC
LIMIT 10;

-- Efficacia canali per tipo notifica
SELECT
    tipo,
    canale,
    COUNT(*) AS totale,
    SUM(CASE WHEN stato_invio = 'sent' THEN 1 ELSE 0 END) AS inviati,
    SUM(CASE WHEN stato_invio = 'failed' THEN 1 ELSE 0 END) AS falliti
FROM notifiche
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
  AND canale IN ('email', 'sms')
GROUP BY tipo, canale
ORDER BY tipo, canale;
```

### Alert Automatici

```php
// CRON giornaliero: controlla tasso fallimento SMS
<?php
require 'includes/db.php';
require 'includes/notifiche-manager.php';

$stmt = $pdo->query("
    SELECT
        totale_inviati,
        falliti,
        tasso_consegna
    FROM v_sms_statistiche
    WHERE data = CURDATE()
");

$stats = $stmt->fetch();

// Alert se tasso consegna < 95%
if ($stats && $stats['tasso_consegna'] < 95) {
    notificaErroreSistema(
        $pdo,
        'Alert SMS: Tasso Consegna Basso',
        "Tasso consegna SMS oggi: {$stats['tasso_consegna']}%. " .
        "Falliti: {$stats['falliti']}/{$stats['totale_inviati']}",
        'urgente'
    );
}
```

---

## Risoluzione Problemi

### SMS Non Vengono Inviati

**1. Controlla configurazione provider**:

```sql
SELECT * FROM sms_config WHERE attivo = TRUE;
```

✅ Deve esserci almeno una configurazione attiva.

**2. Verifica credenziali**:

```bash
# Test Twilio
curl -X GET "https://api.twilio.com/2010-04-01/Accounts/ACxxx.json" \
  -u "ACxxx:your_auth_token"

# Risposta attesa: 200 OK con dati account
```

**3. Controlla log errori**:

```sql
SELECT
    destinatario_numero,
    errore,
    created_at
FROM sms_log
WHERE stato = 'failed'
ORDER BY created_at DESC
LIMIT 10;
```

**Errori comuni**:

- `Invalid phone number`: Numero non in formato internazionale
- `Insufficient funds`: Credito Twilio/Vonage esaurito
- `Unverified phone number`: Numero non verificato (account trial)

### Numero Telefono Non Valido

Il sistema valida automaticamente:

```php
$smsManager = new SMSManager($pdo);

// Test validazione
$test = [
    '3123456789',      // → +393123456789 ✓
    '+393123456789',   // → +393123456789 ✓
    '393123456789',    // → +393123456789 ✓
    '06 1234 5678',    // → false ✗ (troppo corto)
    'abc123',          // → false ✗ (non numerico)
];

foreach ($test as $numero) {
    $validato = $smsManager->validaTelefono($numero);
    echo "$numero → " . ($validato ?: 'INVALID') . "\n";
}
```

### Cliente Non Riceve Notifiche

**1. Verifica preferenze**:

```sql
SELECT
    email_enabled,
    sms_enabled,
    telefono_sms,
    servizio_attivato_canale,
    fattura_scadenza_canale
FROM notifiche_preferenze
WHERE utente_id = 123;
```

**2. Controlla log notifiche**:

```sql
SELECT
    tipo,
    canale,
    stato_invio,
    errore_invio,
    created_at
FROM notifiche
WHERE utente_id = 123
ORDER BY created_at DESC
LIMIT 20;
```

**3. Test invio diretto**:

```php
// Test SMS diretto
require 'includes/sms-manager.php';

$smsManager = new SMSManager($pdo);
$result = $smsManager->send(
    '+393123456789',
    'Test notifica Finch-AI',
    ['cliente_id' => 123]
);

var_dump($result);  // Deve essere true
```

### Costi SMS Elevati

**1. Analizza distribuzione**:

```sql
-- SMS per tipo notifica
SELECT
    n.tipo,
    COUNT(*) AS totale_sms,
    SUM(sl.costo) AS costo_totale
FROM sms_log sl
JOIN notifiche n ON sl.notifica_id = n.id
WHERE sl.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY n.tipo
ORDER BY costo_totale DESC;
```

**2. Ottimizzazioni**:

```sql
-- Disabilita SMS per notifiche meno urgenti
UPDATE notifiche_preferenze
SET aggiornamento_sistema_canale = 'email'  -- Era 'entrambi'
WHERE sms_enabled = TRUE;

-- Risparmio stimato: ~40% SMS mensili
```

**3. Configura budget SMS**:

```php
// Aggiungi controllo budget
class SMSManager {
    private function checkBudget() {
        $stmt = $this->pdo->query("
            SELECT SUM(costo) AS totale_mese
            FROM sms_log
            WHERE MONTH(created_at) = MONTH(CURDATE())
              AND YEAR(created_at) = YEAR(CURDATE())
        ");

        $spesa = $stmt->fetchColumn();

        if ($spesa > 100) {  // Budget mensile: €100
            throw new Exception('Budget SMS mensile superato');
        }
    }
}
```

---

## Performance e Scalabilità

### Ottimizzazioni Database

```sql
-- Indici per performance
ALTER TABLE sms_log ADD INDEX idx_created_stato (created_at, stato);
ALTER TABLE notifiche ADD INDEX idx_utente_tipo (utente_id, tipo, created_at);

-- Partitioning sms_log per data (opzionale per grandi volumi)
ALTER TABLE sms_log
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

### Coda SMS

Per volumi elevati (>1000 SMS/giorno), implementa coda:

```php
// area-clienti/cron/processa-coda-sms.php

$stmt = $pdo->prepare('
    SELECT * FROM sms_log
    WHERE stato = "pending"
    ORDER BY created_at ASC
    LIMIT 100
');

$stmt->execute();
$sms_in_coda = $stmt->fetchAll();

$smsManager = new SMSManager($pdo);

foreach ($sms_in_coda as $sms) {
    try {
        $result = $smsManager->send(
            $sms['destinatario_numero'],
            $sms['messaggio'],
            ['log_id' => $sms['id']]  // Per aggiornamento
        );

        // Log successo/fallimento già gestito in SMSManager

    } catch (Exception $e) {
        error_log("Errore SMS {$sms['id']}: {$e->getMessage()}");
    }

    // Rate limiting: 5 SMS/secondo
    usleep(200000);  // 0.2 secondi
}
```

**Crontab**:

```cron
# Processa coda SMS: ogni minuto
* * * * * php /var/www/finch-ai/area-clienti/cron/processa-coda-sms.php
```

---

## Conformità e Privacy

### GDPR

Il sistema è conforme GDPR:

1. **Consenso**: Cliente abilita esplicitamente SMS in preferenze
2. **Opt-out**: Possibilità di disabilitare in qualsiasi momento
3. **Dati Minimi**: Solo numero telefono necessario per servizio
4. **Retention**: Auto-cleanup SMS dopo 90 giorni

### Informativa Privacy

Aggiungi alla privacy policy:

```
Notifiche SMS

Con il tuo consenso, possiamo inviarti notifiche via SMS riguardanti:
- Scadenze fatture
- Conferme pagamento
- Aggiornamenti servizi

Puoi gestire le preferenze SMS in qualsiasi momento dalla tua area clienti.
I tuoi dati di contatto non saranno condivisi con terze parti.
```

---

## Checklist Go-Live

Prima di andare in produzione:

- [ ] Database schema applicato
- [ ] SMS provider configurato e testato
- [ ] Template SMS verificati (lunghezza < 160 caratteri)
- [ ] Preferenze default clienti create
- [ ] CRON promemoria scadenze attivo
- [ ] Monitoraggio errori configurato
- [ ] Budget SMS mensile impostato
- [ ] Privacy policy aggiornata
- [ ] Test end-to-end su account di test
- [ ] Backup database pre-deploy

---

## Supporto

Per assistenza:

**Documentazione correlata**:
- [Email Manager](SISTEMA_EMAIL_AUTOMATICHE.md)
- [Notifiche Admin](database/add_notifiche_sistema.sql)
- [Scadenzario](SCADENZARIO_FATTURE.md)

**Provider SMS**:
- Twilio Docs: https://www.twilio.com/docs/sms
- Vonage Docs: https://developer.vonage.com/messaging/sms/overview
- AWS SNS: https://docs.aws.amazon.com/sns/

**Debug**:

```bash
# Log applicativo
tail -f logs/error.log

# Log sistema
tail -f /var/log/apache2/error.log

# Log MySQL
tail -f /var/log/mysql/error.log
```

---

**Sistema Notifiche Clienti Multi-Canale v1.0**
Finch-AI © 2024
