# 💰 Sistema di Upselling Intelligente - Documentazione Completa

## Indice
1. [Panoramica](#panoramica)
2. [Architettura](#architettura)
3. [Algoritmo di Scoring](#algoritmo-di-scoring)
4. [Database Schema](#database-schema)
5. [API Reference](#api-reference)
6. [Dashboard](#dashboard)
7. [Integrazione con Churn Prediction](#integrazione-churn)
8. [Best Practices](#best-practices)
9. [KPI e Metriche](#kpi-metriche)
10. [Troubleshooting](#troubleshooting)

---

## Panoramica

Il sistema di **Upselling Intelligente** identifica automaticamente opportunità di vendita per espandere il revenue per cliente attraverso:

- **Upselling**: Upgrade a servizi premium/enterprise
- **Cross-selling**: Vendita di servizi complementari
- **Bundling**: Pacchetti di servizi scontati

### Caratteristiche Principali

✅ **Scoring ML-based** - Algoritmo a 5 componenti con pesi personalizzabili
✅ **Collaborative Filtering** - Analisi pattern di clienti simili
✅ **Pitch Automation** - Generazione automatica messaggi di vendita
✅ **Integrazione Churn** - Considera rischio abbandono nel timing
✅ **ROI Tracking** - Monitoraggio conversioni e performance
✅ **Servizi Complementari** - Mapping relazioni tra servizi

---

## Architettura

### Componenti Sistema

```
┌─────────────────────────────────────────────────────────┐
│                  UPSELL INTELLIGENCE                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────────┐    ┌──────────────┐   ┌──────────┐  │
│  │ UpsellEngine │───▶│  Database    │◀──│ ChurnAPI │  │
│  │   (PHP)      │    │  Tables      │   │          │  │
│  └──────────────┘    └──────────────┘   └──────────┘  │
│         │                    │                          │
│         │                    │                          │
│         ▼                    ▼                          │
│  ┌──────────────┐    ┌──────────────┐                  │
│  │ REST API     │    │  Dashboard   │                  │
│  │ upsell.php   │    │  Frontend    │                  │
│  └──────────────┘    └──────────────┘                  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### File Structure

```
area-clienti/
├── includes/
│   └── upsell-engine.php          # Core engine
├── api/
│   └── upsell.php                 # REST API
├── admin/
│   └── upsell-dashboard.php       # Visual dashboard
└── ...

database/
└── add_upsell_opportunities.sql   # Schema + sample data
```

---

## Algoritmo di Scoring

### Formula Generale

```
Total Score = Σ(Component_Score × Weight)

Dove:
- usage_pattern:      30%
- customer_health:    25%
- budget_capacity:    20%
- similar_customers:  15%
- lifecycle_stage:    10%
```

### 1. Usage Pattern Score (30%)

Analizza pattern di utilizzo per identificare bisogni impliciti.

**Metriche:**
- Richieste API/giorno
- Feature adoption rate
- Volume documenti processati
- Frequenza accessi

**Logica:**
```php
if ($requestsPerDay > 1000) {
    // Heavy user → suggerisci piano Enterprise
    $score = 0.90;
} else if ($requestsPerDay > 500) {
    // Medium user → suggerisci upgrade Premium
    $score = 0.70;
} else {
    // Light user → cross-sell add-ons
    $score = 0.40;
}
```

**Output:** `0.00 - 1.00`

---

### 2. Customer Health Score (25%)

Valuta "salute" del cliente prima di proporre upsell.

**Metriche:**
- Churn probability (da ChurnPredictor)
- Payment behavior
- Support ticket ratio
- Engagement trend

**Logica:**
```php
// Integrazione con churn prediction
$churnRisk = getChurnProbability($clienteId);

if ($churnRisk > 0.70) {
    // Alto rischio → NO UPSELL, focus retention
    return 0.10;
} else if ($churnRisk > 0.40) {
    // Medio rischio → cautela
    return 0.50;
} else {
    // Basso rischio → ottima opportunità
    return 0.90;
}
```

**⚠️ REGOLA CRITICA:** Non fare upselling a clienti ad alto rischio churn!

---

### 3. Budget Capacity Score (20%)

Stima capacità di spesa vs prezzo servizio.

**Metriche:**
- Current MRR (Monthly Recurring Revenue)
- LTV (Lifetime Value)
- Price ratio (new service / current spending)
- Historical payment data

**Logica:**
```php
$currentMRR = getCurrentMonthlySpending($clienteId);
$newServicePrice = $servizio['prezzo_mensile'];

$priceRatio = $newServicePrice / $currentMRR;

if ($priceRatio < 0.20) {
    // Piccolo investimento → alta probabilità
    $score = 0.90;
} else if ($priceRatio < 0.50) {
    // Investimento medio → considerabile
    $score = 0.60;
} else {
    // Grande investimento → bassa probabilità
    $score = 0.30;
}
```

---

### 4. Similar Customers Score (15%)

**Collaborative filtering** - "Clienti simili hanno comprato..."

**Algoritmo:**
```sql
-- Trova clienti simili (per LTV)
SELECT COUNT(DISTINCT sa.cliente_id) as adopters
FROM servizi_attivi sa
WHERE sa.servizio_id = :target_service
AND sa.cliente_id IN (
    -- Clienti con LTV simile (±30%)
    SELECT id FROM utenti
    WHERE lifetime_value BETWEEN :ltv * 0.7 AND :ltv * 1.3
)
```

**Scoring:**
```php
$totalSimilar = countSimilarCustomers($clienteId);
$adopters = countAdoptersOfService($servizioId, $similarCustomers);

$score = $adopters / $totalSimilar; // 0.00 - 1.00

// Esempio: se 8 su 10 clienti simili hanno il servizio → 0.80
```

---

### 5. Lifecycle Stage Score (10%)

Il **timing** è fondamentale per l'upselling.

**Stage Definition:**
```php
$daysAsCustomer = getDaysSinceSignup($clienteId);

if ($daysAsCustomer < 30) {
    $stage = 'new';          // Onboarding fase
    $score = 0.20;           // NO upsell ora
} else if ($daysAsCustomer < 90) {
    $stage = 'growing';      // Learning fase
    $score = 0.50;           // Cautela
} else if ($daysAsCustomer < 365) {
    $stage = 'mature';       // Prime upsell
    $score = 0.80;
} else {
    $stage = 'loyal';        // Migliori opportunità
    $score = 1.00;
}
```

**Best Practice:** Clienti nuovi (<30 giorni) = focus su adoption, non upsell.

---

### Classificazione Opportunità

```php
// Calcolo score finale
$totalScore =
    $usagePattern * 0.30 +
    $customerHealth * 0.25 +
    $budgetCapacity * 0.20 +
    $similarCustomers * 0.15 +
    $lifecycleStage * 0.10;

// Classificazione
if ($totalScore >= 0.70) {
    $level = 'high';         // Priorità ALTA - contatto immediato
} else if ($totalScore >= 0.40) {
    $level = 'medium';       // Priorità MEDIA - questa settimana
} else {
    $level = 'low';          // Priorità BASSA - monitoraggio
}
```

---

## Database Schema

### Tabella: `upsell_opportunities`

Memorizza tutte le opportunità identificate.

```sql
CREATE TABLE upsell_opportunities (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servizio_id INT NOT NULL,

    -- Scoring
    opportunity_score DECIMAL(5,4) NOT NULL,      -- 0.0000 - 1.0000
    opportunity_level ENUM('low','medium','high'),
    expected_value DECIMAL(10,2) NOT NULL,        -- Score × Price × 12

    -- AI reasoning
    reasoning JSON NULL,                          -- Array motivazioni
    scores_breakdown JSON NULL,                   -- Score per componente
    suggested_pitch TEXT NULL,                    -- Pitch automatico

    -- Timing
    best_time_to_contact ENUM('now', 'this_week', 'this_month', 'after_reengagement'),

    -- Status tracking
    status ENUM('identified', 'contacted', 'demo_scheduled', 'proposal_sent', 'won', 'lost', 'on_hold'),
    assigned_to INT NULL,

    -- Outcome
    contacted_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    won_value DECIMAL(10,2) NULL,
    lost_reason TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY (cliente_id, servizio_id)
);
```

### Tabella: `servizi_complementari`

Mapping relazioni tra servizi.

```sql
CREATE TABLE servizi_complementari (
    servizio_base_id INT NOT NULL,
    servizio_complementare_id INT NOT NULL,
    relevance_score DECIMAL(3,2) DEFAULT 1.00,    -- 0.00 - 1.00

    UNIQUE KEY (servizio_base_id, servizio_complementare_id)
);

-- Esempio dati
INSERT INTO servizi_complementari VALUES
(1, 2, 0.90),  -- Doc Intelligence Basic → Premium (forte relazione)
(1, 4, 0.70),  -- Doc Intelligence Basic → AI Training (media)
(2, 3, 0.85);  -- Premium → Enterprise (upgrade path)
```

### Tabella: `upsell_conversions`

Traccia conversioni per ROI analysis.

```sql
CREATE TABLE upsell_conversions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    opportunity_id BIGINT NOT NULL,
    cliente_id INT NOT NULL,
    servizio_id INT NOT NULL,

    converted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contract_value DECIMAL(10,2) NOT NULL,
    contract_duration_months INT DEFAULT 12,

    conversion_source ENUM('sales_call', 'email_campaign', 'automated_pitch', 'self_serve'),
    sales_rep_id INT NULL
);
```

---

## API Reference

### Endpoint Base
```
POST/GET /area-clienti/api/upsell.php?action=<action>
```

### 1. Find Opportunities (Single Customer)

**Request:**
```bash
GET /api/upsell.php?action=find_single&cliente_id=123
```

**Response:**
```json
{
  "success": true,
  "opportunities": [
    {
      "servizio_id": 2,
      "servizio_nome": "Document Intelligence Premium",
      "total_score": 0.7845,
      "level": "high",
      "expected_value": 1788.00,
      "reasoning": [
        "Heavy API usage (1200 requests/day) suggests need for higher tier",
        "Customer health excellent (churn risk: 12%)",
        "85% of similar customers use this service"
      ],
      "suggested_pitch": "Based on your 1200+ daily API requests, upgrading to Premium would unlock...",
      "best_time": "now"
    }
  ]
}
```

---

### 2. Recalculate All Opportunities

**Request:**
```bash
GET /api/upsell.php?action=recalculate_all&limit=1000
```

**Response:**
```json
{
  "success": true,
  "opportunities_found": 247,
  "high_priority": 38,
  "medium_priority": 125,
  "low_priority": 84
}
```

**Note:** Eseguire via CRON giornalmente:
```bash
0 4 * * * curl https://finch-ai.com/area-clienti/api/upsell.php?action=recalculate_all
```

---

### 3. Get All Opportunities

**Request:**
```bash
GET /api/upsell.php?action=get_all&status=identified&level=high
```

**Parameters:**
- `status` (optional): `identified`, `contacted`, `won`, `lost`
- `level` (optional): `low`, `medium`, `high`

**Response:**
```json
{
  "success": true,
  "opportunities": [
    {
      "opportunity_id": 456,
      "cliente_id": 123,
      "nome": "Mario",
      "cognome": "Rossi",
      "azienda": "Acme Corp",
      "servizio_nome": "Premium Support",
      "opportunity_score": 0.8234,
      "expected_value": 1188.00,
      "churn_risk": 0.15
    }
  ]
}
```

---

### 4. Update Status

**Request:**
```bash
POST /api/upsell.php
Content-Type: application/json

{
  "action": "update_status",
  "opportunity_id": 456,
  "status": "contacted"
}
```

**Valid Statuses:**
- `identified` → iniziale
- `contacted` → primo contatto fatto
- `demo_scheduled` → demo prenotata
- `proposal_sent` → proposta inviata
- `won` → vinto ✓
- `lost` → perso
- `on_hold` → in pausa

---

### 5. Mark as Won

**Request:**
```bash
POST /api/upsell.php
Content-Type: application/json

{
  "action": "mark_won",
  "opportunity_id": 456,
  "won_value": 1490.00,
  "source": "sales_call"
}
```

**Comportamento:**
- Aggiorna status a `won`
- Salva `won_value` e `closed_at`
- Crea record in `upsell_conversions`
- Log audit trail
- Calcola conversion rate

---

### 6. Statistics

**Request:**
```bash
GET /api/upsell.php?action=stats&days=30
```

**Response:**
```json
{
  "success": true,
  "stats": {
    "total_opportunities": 247,
    "total_won": 38,
    "total_lost": 12,
    "total_revenue": 45230.00,
    "avg_score": 0.6543,
    "avg_days_to_close": 14.5,
    "conversion_rate": 15.38
  },
  "top_services": [
    {
      "nome": "Document Intelligence Premium",
      "opportunities": 82,
      "conversions": 15,
      "revenue": 22350.00
    }
  ]
}
```

---

### 7. Export CSV

**Request:**
```bash
GET /api/upsell.php?action=export&format=csv
```

**Scarica CSV:**
```csv
Email,Nome,Cognome,Azienda,Servizio,Score (%),Priorità,Expected Value (€),Stato,Timing,Data Creazione
mario@acme.com,Mario,Rossi,Acme Corp,Premium Support,82.34,HIGH,1188.00,identified,now,2025-12-20
```

---

### 8. ROI Calculation

**Request:**
```bash
GET /api/upsell.php?action=roi&days=30
```

**Response:**
```json
{
  "success": true,
  "roi_percentage": 1845.50,
  "period_days": 30
}
```

**Formula ROI:**
```php
ROI = ((Total Revenue - Total Cost) / Total Cost) × 100

Where:
- Total Revenue = SUM(won_value)
- Total Cost = COUNT(contacted) × €10 (costo stimato contatto)
```

**Esempio:**
```
Revenue = €45,230
Cost = 150 contatti × €10 = €1,500
ROI = (€45,230 - €1,500) / €1,500 × 100 = 2915%
```

---

## Dashboard

### URL
```
https://finch-ai.com/area-clienti/admin/upsell-dashboard.php
```

### Features

#### 1. Stats Cards
- **Totale Opportunità** - Identificate dal sistema
- **Alta Priorità** - Score > 70%
- **Media Priorità** - Score 40-70%
- **Bassa Priorità** - Score < 40%
- **Revenue Potenziale** - Expected value 12 mesi
- **Conversion Rate** - Ultimi 30 giorni

#### 2. Chart
- Doughnut chart distribuzione priorità
- Aggiornato real-time

#### 3. Filters
- 🔍 **Search** - Cliente, azienda, servizio
- **Priorità** - All/High/Medium/Low
- **Status** - All/Identified/Contacted/Won/Lost

#### 4. Opportunities Table
**Colonne:**
- Cliente (nome, azienda)
- Servizio raccomandato
- Score (barra + percentuale)
- Priorità (badge colorato)
- Expected Value (€)
- Stato
- Azioni (👁️ Dettagli, ✓ Contattato)

#### 5. Detail Modal

Click su "👁️ Dettagli" mostra:
- Info cliente completa
- Servizio raccomandato + prezzo
- ⚠️ **Churn warning** se rischio alto
- **Score breakdown** per componente (visual bars)
- **Motivazioni** AI (reasoning bullets)
- **Pitch suggerito** ready-to-use
- **Timing raccomandato**
- **Azioni rapide:**
  - ✓ Segna come Contattato
  - 🎉 Segna come Vinto
  - Chiudi

#### 6. Bulk Actions
- 🔄 **Ricalcola Opportunità** - Batch processing
- 📊 **Esporta CSV** - Download report

---

## Integrazione Churn

### Perché è Importante

**❌ Errore comune:** Fare upselling a clienti che stanno per abbandonare.

**✅ Approccio corretto:** Controllare churn risk PRIMA di proporre upsell.

### Implementazione

```php
// In UpsellEngine::scoreCustomerHealth()

$churnRisk = $this->getChurnProbability($clienteId);

if ($churnRisk > 0.70) {
    // STOP - Cliente ad alto rischio
    // Azione: Focus su retention, NO upsell
    return 0.10;
}

if ($churnRisk > 0.40) {
    // CAUTELA - Cliente a medio rischio
    // Azione: Contatto per capire problemi, poi upsell leggero
    return 0.50;
}

// Cliente sano - ottima opportunità upsell
return 0.90;
```

### Dashboard Integration

Il dashboard mostra **⚠️ Warning** se churn risk > 70%:

```html
<div class="churn-warning">
    <strong>⚠️ ATTENZIONE:</strong> Cliente ad alto rischio churn (78%).
    Valuta prima azioni di retention.
</div>
```

### Workflow Consigliato

```
1. Sistema identifica opportunità upsell
     ↓
2. Controlla churn_predictions
     ↓
3a. Churn HIGH → Crea retention_action invece di upsell
3b. Churn MEDIUM → Upsell cauto + retention parallela
3c. Churn LOW → Full upsell
```

---

## Best Practices

### 1. Timing dell'Upselling

**✅ Momenti Ottimali:**
- Dopo 90 giorni di utilizzo (lifecycle mature)
- Quando usage pattern supera limiti piano attuale
- Dopo risoluzione ticket supporto positivo
- Fine anno fiscale cliente (budget disponibile)

**❌ Momenti da Evitare:**
- Primi 30 giorni (onboarding)
- Durante problemi tecnici attivi
- Pagamenti in ritardo
- Alto rischio churn

### 2. Personalizzazione Pitch

**Usa sempre `suggested_pitch` generato dal sistema:**

```php
// Bad - pitch generico
"Upgrade to Premium for more features!"

// Good - pitch personalizzato
"Based on your 1200+ daily API requests (3x your current plan limit),
upgrading to Premium would eliminate throttling and unlock advanced
analytics. 85% of companies with similar usage have already upgraded."
```

### 3. Score Thresholds

**Recommended Actions per Priority:**

| Priority | Score | Action | SLA |
|----------|-------|--------|-----|
| HIGH | ≥70% | Contatto immediato | 24h |
| MEDIUM | 40-70% | Contatto questa settimana | 7d |
| LOW | <40% | Monitoraggio | 30d |

### 4. A/B Testing Pitches

Traccia quale pitch funziona meglio:

```php
// In upsell_conversions
conversion_source ENUM(
    'sales_call',
    'email_campaign',
    'automated_pitch',
    'self_serve'
)
```

Analizza conversion rate per source:
```sql
SELECT
    conversion_source,
    COUNT(*) as conversions,
    AVG(contract_value) as avg_value
FROM upsell_conversions
GROUP BY conversion_source
ORDER BY conversions DESC;
```

### 5. Follow-up Sequence

**Esempio timeline:**

| Day | Action | Channel |
|-----|--------|---------|
| 0 | Opportunity identificata | System |
| 1 | Email personalizzata | Automated |
| 3 | Follow-up call | Sales rep |
| 7 | Demo schedulata | Calendar |
| 10 | Proposal sent | Email |
| 14 | Close/Lost | Update status |

### 6. Servizi Complementari

**Strategia Cross-sell:**

```
Cliente ha: Document Intelligence Basic
     ↓
Sistema suggerisce (in ordine):
1. Premium Support (relevance: 0.90) ← Addon facile
2. AI Training Custom (relevance: 0.70) ← Cross-sell
3. Premium Plan (relevance: 0.85) ← Upgrade
```

Configura in `servizi_complementari` con `relevance_score` accurati.

---

## KPI e Metriche

### KPI Primari

#### 1. Conversion Rate
```sql
SELECT
    (SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
FROM upsell_opportunities
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

**Target:** 15-20% conversion rate

#### 2. Average Deal Size
```sql
SELECT AVG(won_value) as avg_deal_size
FROM upsell_opportunities
WHERE status = 'won'
AND closed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

#### 3. Revenue per Customer
```sql
SELECT
    cliente_id,
    SUM(won_value) as total_upsell_revenue
FROM upsell_opportunities
WHERE status = 'won'
GROUP BY cliente_id
ORDER BY total_upsell_revenue DESC
LIMIT 10;
```

#### 4. Time to Close
```sql
SELECT AVG(DATEDIFF(closed_at, created_at)) as avg_days_to_close
FROM upsell_opportunities
WHERE status IN ('won', 'lost');
```

**Target:** < 14 giorni

#### 5. Opportunity Pipeline Value
```sql
SELECT
    opportunity_level,
    COUNT(*) as count,
    SUM(expected_value) as pipeline_value
FROM upsell_opportunities
WHERE status IN ('identified', 'contacted', 'demo_scheduled', 'proposal_sent')
GROUP BY opportunity_level;
```

### Dashboard KPIs

**Monthly Report Template:**

```
┌─────────────────────────────────────────┐
│     UPSELLING PERFORMANCE - DEC 2025    │
├─────────────────────────────────────────┤
│ Opportunities Identified:    247        │
│ High Priority:                38  (15%) │
│ Medium Priority:             125  (51%) │
│ Low Priority:                 84  (34%) │
│                                         │
│ Conversions:                  38  (15%) │
│ Revenue Generated:      €45,230         │
│ Avg Deal Size:           €1,190         │
│ Avg Days to Close:          14.5        │
│                                         │
│ Pipeline Value:         €294,360        │
│ Expected Revenue (20%): €58,872         │
│                                         │
│ ROI:                      2915%         │
└─────────────────────────────────────────┘
```

### Alerts da Configurare

```php
// 1. Conversion rate troppo basso
if ($conversionRate < 10%) {
    alert("⚠️ Conversion rate sotto target (10%)");
}

// 2. Opportunità high non contactate
$uncontacted = countUncontactedHighPriority();
if ($uncontacted > 5) {
    alert("⚠️ {$uncontacted} opportunità HIGH non contactate!");
}

// 3. Deal stuck
$stuckDeals = getOpportunitiesOlderThan(30); // 30 giorni
if (count($stuckDeals) > 0) {
    alert("⚠️ {count($stuckDeals)} deal bloccati > 30 giorni");
}
```

---

## Troubleshooting

### Problema 1: Score Troppo Bassi

**Sintomo:** Tutte le opportunità hanno score < 0.30

**Possibili Cause:**
1. Pesi configurati male
2. Dati insufficienti (clienti nuovi)
3. Churn risk troppo alto generalizzato

**Fix:**
```php
// Verifica pesi in UpsellEngine
$this->weights = [
    'usage_pattern' => 0.30,      // ✓ Somma = 1.00
    'customer_health' => 0.25,
    'budget_capacity' => 0.20,
    'similar_customers' => 0.15,
    'lifecycle_stage' => 0.10
];

// Verifica dati minimi
SELECT
    COUNT(*) as total_clienti,
    AVG(DATEDIFF(NOW(), created_at)) as avg_days_as_customer
FROM utenti
WHERE ruolo = 'cliente';

// Se avg_days < 30 → normale avere score bassi
```

---

### Problema 2: Nessuna Opportunità Identificata

**Sintomo:** `findOpportunities()` ritorna array vuoto

**Debug Steps:**

```php
// 1. Verifica servizi disponibili
SELECT COUNT(*) FROM servizi WHERE attivo = TRUE;
// Se 0 → aggiungi servizi

// 2. Verifica servizi già attivi cliente
SELECT * FROM servizi_attivi WHERE cliente_id = 123;

// 3. Verifica threshold
// In UpsellEngine::findOpportunities()
if ($score['total_score'] >= 0.30) { // ← Threshold troppo alto?
    // Prova 0.20 per testing
}

// 4. Log debugging
error_log("Cliente $clienteId - Score: " . $score['total_score']);
```

---

### Problema 3: Expected Value Errato

**Sintomo:** Expected value sempre 0 o negativo

**Formula Corretta:**
```php
$expectedValue =
    $totalScore *                    // Probability (0-1)
    $servizio['prezzo_mensile'] *    // Monthly price
    12;                              // Annual value

// Esempio:
// Score: 0.75
// Prezzo: €149/mese
// Expected: 0.75 × €149 × 12 = €1,341
```

**Fix:**
```php
// Verifica prezzo servizio non NULL
SELECT * FROM servizi WHERE prezzo_mensile IS NULL OR prezzo_mensile = 0;

// Aggiorna prezzi mancanti
UPDATE servizi SET prezzo_mensile = 149.00 WHERE id = 2;
```

---

### Problema 4: Collaborative Filtering Non Funziona

**Sintomo:** `similar_customers` score sempre 0

**Cause:**
1. Pochi clienti nel database
2. LTV variance troppo alta
3. Nessun cliente ha il servizio target

**Debug:**
```sql
-- 1. Verifica clienti con LTV simile
SELECT COUNT(*) FROM utenti
WHERE lifetime_value BETWEEN 500 AND 1500  -- ±30% di 1000
AND ruolo = 'cliente';

-- 2. Verifica adozione servizio
SELECT COUNT(*) FROM servizi_attivi
WHERE servizio_id = 2;  -- Target service

-- 3. Se pochi dati, abbassa peso similar_customers
$this->weights['similar_customers'] = 0.05;  // Era 0.15
$this->weights['usage_pattern'] = 0.40;      // Aumenta altri
```

---

### Problema 5: Dashboard Non Carica Opportunità

**Sintomo:** Loading infinito o tabella vuota

**Fix:**

```javascript
// 1. Verifica API response in browser console
fetch('/area-clienti/api/upsell.php?action=get_all')
    .then(r => r.json())
    .then(data => console.log(data));

// 2. Verifica permessi RBAC
// In upsell.php:
$rbac->requirePermission('can_view_analytics');
// Se manca → aggiungi permesso all'admin

// 3. Verifica CORS/headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  // Solo per debug

// 4. Check PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

### Problema 6: Pitch Generico

**Sintomo:** `suggested_pitch` sempre uguale

**Causa:** Template non personalizzato

**Fix:**
```php
// In UpsellEngine::generatePitch()

// Bad - generico
$pitch = "Upgrade to {$servizio['nome']} for more features!";

// Good - personalizzato
$pitch = "Based on your {$features['requests_per_day']} daily API requests " .
         "(which is {$percentOverLimit}% over your current plan limit), " .
         "upgrading to {$servizio['nome']} would unlock unlimited requests " .
         "and advanced analytics. {$percentSimilar}% of companies with similar " .
         "usage have already upgraded.";
```

Usa sempre:
- Dati specifici del cliente
- Comparazioni (vs piano attuale)
- Social proof (clienti simili)
- Benefici concreti (non feature list)

---

## Conclusioni

Il sistema di **Upselling Intelligente** è progettato per:

✅ **Automatizzare** l'identificazione opportunità
✅ **Personalizzare** approccio per ogni cliente
✅ **Integrare** con churn prediction per timing ottimale
✅ **Tracciare** ROI e performance
✅ **Scalare** analisi su migliaia di clienti

### Quick Start Checklist

- [ ] Eseguire `database/add_upsell_opportunities.sql`
- [ ] Configurare servizi in tabella `servizi`
- [ ] Mappare servizi complementari
- [ ] Testare API: `GET /api/upsell.php?action=find_single&cliente_id=1`
- [ ] Accedere a dashboard: `/admin/upsell-dashboard.php`
- [ ] Configurare CRON per ricalcolo giornaliero
- [ ] Assegnare permesso `can_view_analytics` ai sales rep
- [ ] Monitorare KPI settimanalmente

### Support

Per domande o issue:
- 📧 Email: support@finch-ai.com
- 📖 Docs: Questo file
- 🐛 Bug: GitHub Issues

---

**Versione:** 1.0
**Data:** Dicembre 2025
**Autore:** Finch AI Development Team
**License:** Proprietary
