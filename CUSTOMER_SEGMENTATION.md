# 🎯 Sistema di Segmentazione Clienti - Documentazione Completa

## Indice
1. [Panoramica](#panoramica)
2. [Algoritmo K-means](#algoritmo-kmeans)
3. [Feature Engineering](#feature-engineering)
4. [Personas Generate](#personas-generate)
5. [Database Schema](#database-schema)
6. [API Reference](#api-reference)
7. [Dashboard](#dashboard)
8. [Integrazione Ecosystem](#integrazione-ecosystem)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Panoramica

Il **Sistema di Segmentazione Clienti** utilizza **machine learning non supervisionato** (K-means clustering) per identificare automaticamente gruppi di clienti con comportamenti simili.

### Benefici

✅ **Automazione** - Identifica pattern senza regole predefinite
✅ **Scalabilità** - Analizza migliaia di clienti in minuti
✅ **Personalizzazione** - Crea personas basate su dati reali
✅ **Actionability** - Raccomandazioni strategiche per segmento
✅ **Integrazione** - Connesso a churn prediction e upselling

### Use Cases

- **Marketing Targeted**: Campagne email/SMS personalizzate per segmento
- **Product Strategy**: Capire quali feature sviluppare per ogni persona
- **Pricing Optimization**: Piani diversi per budget diversi
- **Churn Prevention**: Identificare segmenti at-risk
- **Upselling Smart**: Priorità opportunità per segmento ad alto valore

---

## Algoritmo K-means

### Cos'è K-means?

K-means è un algoritmo di **clustering non supervisionato** che raggruppa punti in K cluster minimizzando la distanza intra-cluster.

### Workflow Completo

```
1. FEATURE EXTRACTION
   ↓
   Estrai 6 metriche comportamentali per ogni cliente:
   - LTV Score
   - Engagement Score
   - Usage Intensity
   - Service Diversity
   - Payment Reliability
   - Tenure Score

2. NORMALIZATION
   ↓
   Min-max normalization (0-1 scale)
   per rendere feature comparabili

3. OPTIMAL K DETECTION
   ↓
   Elbow method: testa K=2...10
   Trova K con massima riduzione WCSS

4. K-MEANS++ INITIALIZATION
   ↓
   Smart centroid initialization
   (migliore di random)

5. ITERATION
   ↓
   Repeat until convergence:
   - Assignment: assegna punti a centroid più vicino
   - Update: ricalcola centroids

6. PROFILING
   ↓
   Analizza ogni cluster:
   - Calcola medie (LTV, engagement, etc)
   - Identifica caratteristiche dominanti
   - Genera persona name/description
   - Crea raccomandazioni strategiche

7. SAVE TO DB
   ↓
   Salva assignments e profiles
```

### Formula Distanza

```php
// Euclidean distance con pesi
distance = √(Σ weight_i × (feature1_i - feature2_i)²)

// Esempio con 2 clienti
Cliente A: [ltv=0.8, engagement=0.6, usage=0.9, ...]
Cliente B: [ltv=0.7, engagement=0.5, usage=0.8, ...]

distance = √(
    0.25 × (0.8 - 0.7)² +  // ltv_score weight
    0.20 × (0.6 - 0.5)² +  // engagement weight
    0.20 × (0.9 - 0.8)² +  // usage weight
    ...
)
```

### Convergenza

L'algoritmo converge quando **nessun punto cambia cluster** tra iterazioni successive.

Tipicamente converge in **10-50 iterazioni**.

---

## Feature Engineering

### 1. LTV Score (25% weight)

**Definizione:** Lifetime Value normalizzato

**Formula:**
```php
ltv_score = min(1.0, log(ltv + 1) / log(target_ltv + 1))

// Esempio:
// LTV = €5,000
// Target = €10,000
// Score = log(5001) / log(10001) = 0.92
```

**Interpretazione:**
- `0.00 - 0.30` = Low value
- `0.30 - 0.70` = Medium value
- `0.70 - 1.00` = High value

---

### 2. Engagement Score (20% weight)

**Definizione:** Quanto il cliente è attivo

**Metriche:**
- Days since last login
- Actions last 30 days (API calls, clicks, etc)

**Formula:**
```php
login_score = 1.0 - min(1.0, days_since_login / 30)
action_score = min(1.0, actions_last_30d / 100)

engagement_score = (login_score × 0.6) + (action_score × 0.4)

// Esempio:
// Last login: 5 giorni fa
// Actions: 50
//
// login_score = 1.0 - (5/30) = 0.83
// action_score = 50/100 = 0.50
// engagement = (0.83×0.6) + (0.50×0.4) = 0.70
```

**Interpretazione:**
- `< 0.30` = Hibernating (dormienti)
- `0.30 - 0.70` = Standard engagement
- `> 0.70` = Highly engaged

---

### 3. Usage Intensity (20% weight)

**Definizione:** Quanto intensamente usa i servizi

**Metriche:**
- Active services count
- Usage count last 30 days (API calls, documents processed, etc)

**Formula:**
```php
service_score = min(1.0, active_services / 5)
usage_score = min(1.0, usage_count / 1000)

usage_intensity = (service_score × 0.4) + (usage_score × 0.6)

// Esempio:
// Active services: 3
// Usage: 500 calls
//
// service_score = 3/5 = 0.60
// usage_score = 500/1000 = 0.50
// intensity = (0.60×0.4) + (0.50×0.6) = 0.54
```

**Interpretazione:**
- `< 0.30` = Light user
- `0.30 - 0.70` = Standard user
- `> 0.70` = Power user

---

### 4. Service Diversity (15% weight)

**Definizione:** Varietà di servizi utilizzati

**Formula:**
```php
diversity_score = min(1.0, distinct_service_types / 4)

// Esempio:
// Cliente usa: Doc Intelligence, AI Training, Support
// 3 tipi diversi
//
// score = 3/4 = 0.75
```

**Interpretazione:**
- Più varietà = cliente più integrato = meno churn risk

---

### 5. Payment Reliability (10% weight)

**Definizione:** Affidabilità nei pagamenti

**Formula:**
```php
reliability = on_time_payments / total_invoices

// Esempio:
// 18 fatture pagate in tempo su 20 totali
// reliability = 18/20 = 0.90
```

**Interpretazione:**
- `< 0.70` = Problemi payment
- `0.70 - 0.90` = Accettabile
- `> 0.90` = Eccellente

---

### 6. Tenure Score (10% weight)

**Definizione:** Anzianità cliente

**Formula:**
```php
tenure_score = min(1.0, days_as_customer / 365)

// Esempio:
// Cliente da 180 giorni
// score = 180/365 = 0.49
```

**Interpretazione:**
- `< 0.20` (< 73 giorni) = New customer
- `0.20 - 0.50` (73-182 giorni) = Growing
- `0.50 - 1.00` (> 182 giorni) = Mature/Loyal

---

## Personas Generate

### Mapping Caratteristiche → Personas

Il sistema identifica **automaticamente** le personas in base alle caratteristiche dominanti:

### 👑 VIP Champions

**Caratteristiche:**
- `high_value` + `highly_engaged`

**Profilo:**
- LTV > €5,000
- Engagement > 70%
- Churn risk < 30%

**Raccomandazioni:**
```json
[
  {
    "priority": "high",
    "action": "upsell",
    "message": "Ottimi candidati per upselling a servizi premium"
  },
  {
    "priority": "low",
    "action": "advocacy",
    "message": "Richiedi referral, recensioni, case study"
  }
]
```

---

### ⚠️ At-Risk VIPs

**Caratteristiche:**
- `high_value` + `at_risk`

**Profilo:**
- LTV > €5,000
- Churn risk > 60%

**Raccomandazioni:**
```json
[
  {
    "priority": "critical",
    "action": "retention_campaign",
    "message": "Avvia immediatamente campagna retention con contatto personale"
  }
]
```

**⚠️ CRITICO:** Questo segmento ha massima priorità - revenue alto a rischio!

---

### 🚀 Power Users Budget

**Caratteristiche:**
- `power_user` + `low_value`

**Profilo:**
- Usage > 70%
- LTV < €1,000

**Raccomandazioni:**
```json
[
  {
    "priority": "high",
    "action": "pricing_optimization",
    "message": "Considera upgrade o piano enterprise per heavy usage"
  }
]
```

**Insight:** Stanno "abusando" del piano economico - opportunità pricing!

---

### 🌱 New Explorers

**Caratteristiche:**
- `new_customer`

**Profilo:**
- Tenure < 60 giorni

**Raccomandazioni:**
```json
[
  {
    "priority": "medium",
    "action": "onboarding",
    "message": "Focus su onboarding e training per massimizzare adozione"
  }
]
```

**Goal:** Portarli a "Loyal Advocates" o "VIP Champions"

---

### 💎 Loyal Advocates

**Caratteristiche:**
- `loyal` + `engagement > 0.5`

**Profilo:**
- Tenure > 180 giorni
- Engagement > 50%

**Raccomandazioni:**
```json
[
  {
    "priority": "low",
    "action": "advocacy",
    "message": "Richiedi referral, recensioni, case study"
  }
]
```

**Value:** Ambassador del brand, testimonial perfetti

---

### 😴 Hibernating

**Caratteristiche:**
- `low_engagement`

**Profilo:**
- Engagement < 30%
- Last login > 15 giorni

**Raccomandazioni:**
```json
[
  {
    "priority": "medium",
    "action": "reengagement",
    "message": "Email automation per riattivare clienti dormienti"
  }
]
```

**Strategia:** Win-back campaign con offer speciale

---

### 👤 Standard Users

**Caratteristiche:**
- Nessuna caratteristica dominante

**Profilo:**
- Medie su tutte le metriche

**Raccomandazioni:**
```json
[
  {
    "priority": "low",
    "action": "monitoring",
    "message": "Monitora evoluzione verso altre personas"
  }
]
```

---

## Database Schema

### customer_segments

Assegnazione clienti → segmenti

```sql
CREATE TABLE customer_segments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    segment_id INT NOT NULL,
    assignment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY (cliente_id),
    FOREIGN KEY (cliente_id) REFERENCES utenti(id)
);
```

### segment_profiles

Profili personas

```sql
CREATE TABLE segment_profiles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    segment_id INT NOT NULL,

    -- Persona
    persona_name VARCHAR(100) NOT NULL,
    persona_description TEXT,
    persona_icon VARCHAR(10) DEFAULT '👤',

    -- Stats
    size INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,

    -- Metriche medie
    avg_ltv DECIMAL(10,2),
    avg_engagement DECIMAL(5,4),
    avg_usage DECIMAL(5,4),
    avg_churn_risk DECIMAL(5,4),
    avg_tenure_days INT,

    -- JSON fields
    characteristics JSON,        -- ['high_value', 'power_user']
    recommendations JSON,         -- [{priority, action, message}]
    centroid_data JSON,          -- Feature values

    UNIQUE KEY (segment_id)
);
```

### segment_history

Track migrazioni tra segmenti

```sql
CREATE TABLE segment_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    old_segment_id INT,
    new_segment_id INT NOT NULL,
    migration_date DATE NOT NULL,

    FOREIGN KEY (cliente_id) REFERENCES utenti(id)
);
```

**Use case:** Identifica trend (es: "20 clienti sono passati da VIP Champions a At-Risk VIPs questo mese")

---

## API Reference

### Endpoint Base
```
POST/GET /area-clienti/api/segmentation.php?action=<action>
```

### 1. Recalculate Segmentation

**Request:**
```bash
GET /api/segmentation.php?action=recalculate&clusters=5
```

**Parameters:**
- `clusters` (optional): Numero cluster fisso (default: auto-detect con Elbow)

**Response:**
```json
{
  "success": true,
  "num_clusters": 5,
  "total_customers": 1247,
  "iterations": 23,
  "profiles": [
    {
      "segment_id": 0,
      "persona_name": "VIP Champions",
      "size": 187,
      "percentage": 15.0,
      "avg_ltv": 8234.50,
      "characteristics": ["high_value", "highly_engaged"],
      "recommendations": [...]
    }
  ]
}
```

**Scheduling CRON:**
```bash
# Ogni domenica alle 02:00
0 2 * * 0 curl https://finch-ai.com/area-clienti/api/segmentation.php?action=recalculate
```

---

### 2. Assign Customer to Segment

**Request:**
```bash
GET /api/segmentation.php?action=assign_customer&cliente_id=123
```

**Response:**
```json
{
  "success": true,
  "segment": {
    "segment_id": 2,
    "persona_name": "Power Users Budget",
    "persona_icon": "🚀",
    "persona_description": "Usano intensamente la piattaforma ma spendono poco. Opportunità upselling.",
    "characteristics": ["power_user", "low_value"],
    "recommendations": [...]
  }
}
```

**Use case:** Nuovo cliente si registra → assegna automaticamente a segmento esistente

---

### 3. Get All Segments

**Request:**
```bash
GET /api/segmentation.php?action=get_all_segments
```

**Response:**
```json
{
  "success": true,
  "segments": [
    {
      "segment_id": 0,
      "persona_name": "VIP Champions",
      "size": 187,
      "percentage": 15.0,
      "avg_ltv": 8234.50,
      "characteristics": ["high_value", "highly_engaged"]
    }
  ]
}
```

---

### 4. Get Segment Customers

**Request:**
```bash
GET /api/segmentation.php?action=get_segment_customers&segment_id=0
```

**Response:**
```json
{
  "success": true,
  "customers": [
    {
      "cliente_id": 456,
      "email": "mario@acme.com",
      "nome": "Mario",
      "cognome": "Rossi",
      "azienda": "Acme Corp",
      "lifetime_value": 12450.00,
      "days_since_last_login": 2
    }
  ]
}
```

---

### 5. Segment Value Analysis

**Request:**
```bash
GET /api/segmentation.php?action=segment_value_analysis
```

**Response:**
```json
{
  "success": true,
  "value_analysis": [
    {
      "segment_id": 0,
      "persona_name": "VIP Champions",
      "size": 187,
      "avg_ltv": 8234.50,
      "total_segment_value": 1539851.50,
      "high_upsell_opportunities": 23,
      "high_churn_risk_count": 5
    }
  ]
}
```

**Insights:**
- Quale segmento genera più revenue?
- Quale ha più opportunità upselling?
- Quale è più a rischio?

---

### 6. Export CSV

**Request:**
```bash
GET /api/segmentation.php?action=export&format=csv
```

**Download CSV:**
```csv
Email,Nome,Cognome,Azienda,Segmento,Icon,Lifetime Value (€),Servizi Attivi,Giorni Inattivo,Data Assegnazione
mario@acme.com,Mario,Rossi,Acme Corp,VIP Champions,👑,12450.00,5,2,2025-12-15
```

---

## Dashboard

### URL
```
https://finch-ai.com/area-clienti/admin/segmentation-dashboard.php
```

### Features

#### 1. Stats Bar
- **Segmenti Attivi** - Numero cluster identificati
- **Clienti Segmentati** - Totale clienti processati
- **Ultimo Aggiornamento** - Timestamp

#### 2. Segment Cards Grid

Ogni card mostra:
- **Persona Icon + Name** (es: 👑 VIP Champions)
- **Description** breve
- **Stats:**
  - Clienti (count + %)
  - LTV Medio
  - Engagement %
  - Rischio Churn %
- **Caratteristiche** (tags)
- **Azioni:**
  - 👥 Vedi Clienti
  - 📧 Crea Campagna

Click su card → Modal con dettagli completi

#### 3. Visualizations

**Chart 1: Segment Distribution (Doughnut)**
- Mostra % clienti per segmento

**Chart 2: Segment Value (Bar)**
- Mostra LTV medio per segmento

#### 4. Detail Modal

Mostra per segmento:
- Stats complete (6 metriche)
- Caratteristiche dominanti
- Raccomandazioni strategiche (prioritized list)
- Lista clienti
- Azioni: Vedi clienti, Crea campagna

---

## Integrazione Ecosystem

### 1. Integrazione Churn Prediction

**Feature Exchange:**
```php
// In CustomerSegmentation::extractFeatures()
$churnRisk = getChurnProbability($clienteId);

// Usa churn_risk come feature per clustering
$customer['churn_probability'] = $churnRisk;
```

**Benefit:** Segmenti considerano rischio abbandono

**Use case:**
```
Segmento "At-Risk VIPs" = high_value + high_churn
→ Priorità massima per retention
```

---

### 2. Integrazione Upselling

**Targeting Intelligente:**
```php
// Prima di fare upselling, controlla segmento
$segment = getCustomerSegment($clienteId);

if ($segment['persona_name'] === 'At-Risk VIPs') {
    // NO upsell, focus retention
    return false;
}

if ($segment['persona_name'] === 'VIP Champions') {
    // SÌ upsell, alta probabilità successo
    $upsellPriority = 'high';
}
```

**Analysis per Segmento:**
```sql
SELECT
    sp.persona_name,
    COUNT(uo.id) as upsell_opportunities,
    AVG(uo.opportunity_score) as avg_score
FROM customer_segments cs
JOIN segment_profiles sp ON cs.segment_id = sp.segment_id
LEFT JOIN upsell_opportunities uo ON cs.cliente_id = uo.cliente_id
GROUP BY sp.segment_id;
```

---

### 3. Campagne Marketing Targeted

**Workflow:**
```
1. Admin seleziona segmento (es: "Hibernating")
2. Sceglie campaign type (email/SMS)
3. Usa template personalizzato per persona
4. Scheduler invia a tutti del segmento
5. Track performance (open rate, conversion)
```

**Esempio Template:**

**Per "Hibernating":**
```
Subject: Ti manchiamo! 🎁 Offer speciale per te

Ciao {nome},
Abbiamo notato che non accedi da un po'.
Ecco 20% di sconto su qualsiasi servizio per riattivare il tuo account.

[CTA: Torna su Finch AI]
```

**Per "VIP Champions":**
```
Subject: Nuova feature esclusiva per i nostri VIP 👑

Ciao {nome},
Come nostro cliente VIP, hai accesso anticipato a...

[CTA: Scopri la novità]
```

---

## Best Practices

### 1. Frequenza Re-segmentazione

**Raccomandato:** Settimanale (ogni domenica)

**Perché:**
- Clienti cambiano comportamento
- Nuovi clienti da assegnare
- Pattern stagionali

**CRON Setup:**
```bash
0 2 * * 0 php /path/to/recalculate_segmentation_cron.php
```

```php
// recalculate_segmentation_cron.php
<?php
require 'includes/db.php';
require 'includes/customer-segmentation.php';

$segmentation = new CustomerSegmentation($pdo);
$result = $segmentation->performClustering();

echo "Segmentation completed: {$result['num_clusters']} clusters, {$result['total_customers']} customers\n";
```

---

### 2. Numero Cluster Ottimale

**Default:** Auto-detect con Elbow method

**Override manuale solo se:**
- Sai esattamente quante personas vuoi
- Elbow method dà risultati strani (es: K=2 troppo poco)

**Regole empiriche:**
```
Clienti < 100:    K = 3-4
Clienti 100-500:  K = 4-6
Clienti > 500:    K = 5-8
```

---

### 3. Interpretazione Migrazioni

**Monitor segment_history:**

```sql
SELECT
    old_persona,
    new_persona,
    COUNT(*) as migrations
FROM v_segment_migrations
WHERE migration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY old_persona, new_persona
ORDER BY migrations DESC;
```

**Red flags:**
```
VIP Champions → At-Risk VIPs:  15 migrations  ← 🚨 PROBLEMA!
Loyal Advocates → Hibernating:  8 migrations  ← 🚨 RE-ENGAGEMENT!
```

---

### 4. Actionable Insights

**Per ogni segmento, rispondi:**

1. **Chi sono?** (descrizione persona)
2. **Quanti sono?** (size + %)
3. **Quanto valgono?** (total segment value)
4. **Cosa vogliono?** (comportamenti dominanti)
5. **Cosa fare?** (raccomandazioni prioritizzate)

---

## Troubleshooting

### Problema 1: Tutti nello stesso cluster

**Sintomo:** 95% clienti nel cluster 0

**Causa:** Feature variance troppo bassa

**Debug:**
```sql
SELECT
    MIN(lifetime_value) as min_ltv,
    MAX(lifetime_value) as max_ltv,
    AVG(lifetime_value) as avg_ltv
FROM utenti;

-- Se min ≈ max → problema
```

**Fix:**
- Controlla dati: clienti troppo simili?
- Aumenta K (più cluster)
- Rivedi feature engineering

---

### Problema 2: Cluster vuoti

**Sintomo:** Alcuni cluster con size = 0

**Causa:** K troppo alto rispetto a clienti

**Fix:**
```php
// Usa Elbow method invece di K fisso
$result = $segmentation->performClustering(null); // Auto-detect
```

---

### Problema 3: Personas generiche

**Sintomo:** Tutte "Standard Users"

**Causa:** Caratteristiche non abbastanza distintive

**Debug:**
```php
// Stampa score breakdown
foreach ($customers as $customer) {
    echo "Cliente {$customer['id']}: LTV={$customer['ltv_score']}, Eng={$customer['engagement_score']}\n";
}
```

**Fix:**
- Abbassa threshold caratteristiche
- Aggiungi nuove features (es: industry, company size)

---

## Conclusioni

Il **Sistema di Segmentazione Clienti** è il foundation per **customer intelligence**:

✅ **Identifica** automaticamente gruppi comportamentali
✅ **Personalizza** strategia marketing/sales per persona
✅ **Prioritizza** azioni su segmenti ad alto valore/rischio
✅ **Integra** con churn prediction e upselling per decisioni olistiche

### Quick Start

```bash
# 1. Installa database
mysql -u root -p finch_ai < database/add_customer_segmentation.sql

# 2. Esegui prima segmentazione
curl "http://localhost/area-clienti/api/segmentation.php?action=recalculate"

# 3. Visualizza dashboard
https://finch-ai.com/area-clienti/admin/segmentation-dashboard.php
```

### Next Steps

1. Configura CRON settimanale
2. Crea campaign templates per ogni persona
3. Integra con email automation
4. Monitor migrations per early warning
5. Usa per priorità roadmap prodotto

---

**Versione:** 1.0
**Data:** Dicembre 2025
**Autore:** Finch AI Development Team
