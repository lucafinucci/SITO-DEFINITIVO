# 📅 Scadenzario Fatture - Sistema di Gestione Scadenze

Sistema completo di visualizzazione e gestione delle scadenze fatture con calendario interattivo e dashboard statistiche.

## 📋 Indice

1. [Funzionalità](#funzionalità)
2. [File Creati](#file-creati)
3. [Installazione](#installazione)
4. [Utilizzo](#utilizzo)
5. [Viste Database](#viste-database)
6. [API](#api)
7. [Calendario](#calendario)
8. [Best Practices](#best-practices)

---

## 🎯 Funzionalità

### Caratteristiche Principali

✅ **Vista Calendario Interattiva**
- Calendario mensile con FullCalendar
- Eventi colorati per priorità
- Click su evento per dettagli fattura
- Navigazione mese/settimana/lista

✅ **Dashboard Statistiche**
- Fatture scadute in tempo reale
- Scadenze oggi
- Scadenze questa settimana
- Pagamenti del mese

✅ **Sistema di Priorità**
- 🔴 Priorità 1: Fatture scadute
- 🟡 Priorità 2: Scadenza entro 7 giorni
- 🔵 Priorità 3: Scadenze normali
- 🟢 Priorità 4: Fatture pagate

✅ **Doppia Visualizzazione**
- Vista Calendario: Visualizzazione grafica mensile
- Vista Lista: Elenco scadenze prossimi 30 giorni

✅ **Integrazione Completa**
- Link diretto a fattura da calendario
- Aggiornamento automatico dati
- Colori dinamici per stato fattura

---

## 📁 File Creati

### Database

```
database/add_scadenzario_views.sql
```
- Vista `v_scadenzario_fatture`: Tutte le scadenze con calcoli
- Vista `v_dashboard_scadenze`: Riepilogo giornaliero
- Vista `v_statistiche_scadenzario`: KPI scadenzario
- Indici per performance

### Backend API

```
area-clienti/api/scadenzario.php
```
- Endpoint per eventi calendario
- Endpoint per statistiche
- Endpoint per lista scadenze
- Endpoint per riepilogo giorno

### Frontend

```
area-clienti/admin/scadenzario.php
```
- Pagina calendario interattivo
- Dashboard statistiche
- Vista lista scadenze
- Modal dettagli fattura

### File Modificati

```
area-clienti/admin/gestione-servizi.php      (+ link scadenzario)
area-clienti/admin/fatture.php               (+ link scadenzario)
area-clienti/admin/richieste-addestramento.php (+ link scadenzario)
```

---

## 🚀 Installazione

### 1. Database

Esegui lo script SQL per creare le viste:

```bash
mysql -u root -p finch_ai < database/add_scadenzario_views.sql
```

Oppure tramite phpMyAdmin:
1. Apri phpMyAdmin
2. Seleziona database `finch_ai`
3. Vai su "Importa"
4. Carica `database/add_scadenzario_views.sql`

### 2. Verifica Installazione

Controlla che le viste siano state create:

```sql
SHOW FULL TABLES WHERE Table_type = 'VIEW';
```

Dovresti vedere:
- `v_scadenzario_fatture`
- `v_dashboard_scadenze`
- `v_statistiche_scadenzario`

### 3. Verifica Indici

```sql
SHOW INDEX FROM fatture WHERE Key_name LIKE 'idx_%';
```

Dovresti vedere:
- `idx_data_scadenza`
- `idx_data_pagamento`
- `idx_stato_scadenza`

### 4. Test Query

Prova una query rapida:

```sql
SELECT * FROM v_statistiche_scadenzario;
```

---

## 💻 Utilizzo

### Accesso Scadenzario

1. Fai login come admin
2. Vai a Dashboard Admin
3. Clicca su **"📅 Scadenzario"** nella navigazione

### Vista Calendario

#### Navigazione

- **Frecce ← →**: Naviga tra i mesi
- **Oggi**: Torna al mese corrente
- **Mese/Settimana/Lista**: Cambia visualizzazione

#### Colori Eventi

| Colore | Significato | Stato |
|--------|-------------|-------|
| 🔴 Rosso | Fattura scaduta | `scaduta` |
| 🟡 Arancione | Scade entro 7 giorni | `emessa/inviata` (< 7gg) |
| 🔵 Blu | In scadenza | `emessa/inviata` |
| 🟢 Verde | Pagata | `pagata` |

#### Interazione

- **Click su evento**: Apre modal con dettagli fattura
- **Modal**: Pulsante "Visualizza Fattura" per aprire dettaglio completo

### Vista Lista

1. Clicca su **"📋 Vista Lista"**
2. Vedi elenco scadenze prossimi 30 giorni
3. Ordinate per priorità (urgenti prima)
4. Click su card per vedere dettagli

### Dashboard Statistiche

Le 4 card in alto mostrano:

1. **Fatture Scadute** (rosso)
   - Numero fatture scadute
   - Importo totale da recuperare

2. **Scadenze Oggi** (arancione)
   - Fatture che scadono oggi
   - Importo in scadenza

3. **Scadenze Questa Settimana** (blu)
   - Fatture prossimi 7 giorni
   - Importo settimana

4. **Pagamenti Questo Mese** (verde)
   - Fatture pagate questo mese
   - Incassi del mese

---

## 🗄️ Viste Database

### v_scadenzario_fatture

Vista principale con tutte le scadenze.

**Colonne principali:**
- `fattura_id`, `numero_fattura`
- `azienda`, `cliente_email`
- `data_scadenza`, `data_pagamento`
- `totale`, `stato`
- `giorni_a_scadenza` (calcolato)
- `giorni_ritardo` (calcolato)
- `priorita` (1-4)
- `colore` (success/danger/warning/info)
- `tipo_evento` (scadenza/pagamento)

**Esempio query:**

```sql
-- Fatture che scadono nei prossimi 7 giorni
SELECT
    numero_fattura,
    azienda,
    data_scadenza,
    totale,
    giorni_a_scadenza
FROM v_scadenzario_fatture
WHERE giorni_a_scadenza BETWEEN 0 AND 7
  AND stato IN ('emessa', 'inviata')
ORDER BY giorni_a_scadenza ASC;
```

```sql
-- Fatture scadute con importo alto (> 1000€)
SELECT
    numero_fattura,
    azienda,
    data_scadenza,
    totale,
    giorni_ritardo
FROM v_scadenzario_fatture
WHERE stato = 'scaduta'
  AND totale > 1000
ORDER BY totale DESC;
```

### v_dashboard_scadenze

Riepilogo scadenze raggruppate per giorno.

**Colonne:**
- `data`
- `num_fatture`
- `importo_totale`
- `num_scadute`
- `importo_scaduto`
- `priorita_max`

**Esempio query:**

```sql
-- Giorni con più scadenze questo mese
SELECT
    data,
    num_fatture,
    importo_totale
FROM v_dashboard_scadenze
WHERE MONTH(data) = MONTH(CURRENT_DATE())
  AND YEAR(data) = YEAR(CURRENT_DATE())
ORDER BY num_fatture DESC
LIMIT 10;
```

### v_statistiche_scadenzario

KPI globali in tempo reale.

**Metriche disponibili:**
- `scadenze_oggi` / `importo_oggi`
- `scadenze_settimana` / `importo_settimana`
- `scadenze_mese` / `importo_mese`
- `fatture_scadute` / `importo_scaduto`
- `pagate_mese` / `importo_pagato_mese`

**Esempio query:**

```sql
-- Dashboard KPI completa
SELECT
    CONCAT(scadenze_oggi, ' fatture (€', FORMAT(importo_oggi, 2), ')') AS oggi,
    CONCAT(scadenze_settimana, ' fatture (€', FORMAT(importo_settimana, 2), ')') AS settimana,
    CONCAT(fatture_scadute, ' fatture (€', FORMAT(importo_scaduto, 2), ')') AS scadute,
    CONCAT(pagate_mese, ' fatture (€', FORMAT(importo_pagato_mese, 2), ')') AS pagamenti_mese
FROM v_statistiche_scadenzario;
```

---

## 🔌 API

### GET /api/scadenzario.php

#### Action: eventi

Recupera eventi per il calendario.

**Parametri:**
- `action=eventi`
- `anno` (int): Anno (default: corrente)
- `mese` (int): Mese 1-12 (default: corrente)

**Response:**
```json
{
  "success": true,
  "eventi": [
    {
      "id": "scad-123",
      "title": "Acme Corp - FAT-2025-001",
      "start": "2025-01-15",
      "description": "€1,500.00 - Scade tra 5 giorni",
      "backgroundColor": "#f59e0b",
      "borderColor": "#f59e0b",
      "textColor": "#fff",
      "extendedProps": {
        "fattura_id": 123,
        "numero_fattura": "FAT-2025-001",
        "azienda": "Acme Corp",
        "email": "billing@acme.com",
        "importo": 1500.00,
        "stato": "inviata",
        "tipo": "scadenza",
        "priorita": 2
      }
    }
  ]
}
```

#### Action: statistiche

Recupera KPI dashboard.

**Parametri:**
- `action=statistiche`

**Response:**
```json
{
  "success": true,
  "statistiche": {
    "scadenze_oggi": 2,
    "importo_oggi": 3400.00,
    "scadenze_settimana": 8,
    "importo_settimana": 12500.00,
    "scadenze_mese": 25,
    "importo_mese": 45000.00,
    "fatture_scadute": 5,
    "importo_scaduto": 8900.00,
    "pagate_mese": 30,
    "importo_pagato_mese": 67000.00
  }
}
```

#### Action: lista

Lista scadenze prossimi N giorni.

**Parametri:**
- `action=lista`
- `giorni` (int): Numero giorni (default: 30, max: 365)

**Response:**
```json
{
  "success": true,
  "scadenze": [
    {
      "fattura_id": 123,
      "numero_fattura": "FAT-2025-001",
      "azienda": "Acme Corp",
      "cliente_email": "billing@acme.com",
      "data_scadenza": "2025-01-15",
      "totale": 1500.00,
      "stato": "inviata",
      "giorni_a_scadenza": 5,
      "giorni_ritardo": 0,
      "priorita": 2,
      "colore": "warning"
    }
  ]
}
```

#### Action: riepilogo-giorno

Dettaglio scadenze e pagamenti per un giorno specifico.

**Parametri:**
- `action=riepilogo-giorno`
- `data` (string): Data formato YYYY-MM-DD

**Response:**
```json
{
  "success": true,
  "data": "2025-01-15",
  "scadenze": [...],
  "pagamenti": [...],
  "totale_scadenze": 3400.00,
  "totale_pagamenti": 2100.00
}
```

---

## 📅 Calendario

### FullCalendar Integration

Lo scadenzario usa [FullCalendar](https://fullcalendar.io/) versione 6.1.10.

#### Configurazione

```javascript
calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'it',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,listWeek'
    },
    events: function(info, successCallback, failureCallback) {
        // Carica eventi da API
    },
    eventClick: function(info) {
        // Mostra dettagli fattura
    }
});
```

#### Personalizzazioni

**Colori Priorità:**
```javascript
function getColorCode(colore) {
    return match(colore) {
        'success' => '#10b981',  // Verde - Pagata
        'danger' => '#ef4444',   // Rosso - Scaduta
        'warning' => '#f59e0b',  // Arancione - Scade presto
        'info' => '#3b82f6',     // Blu - Normale
        default => '#6b7280'     // Grigio - Default
    };
}
```

**Formato Eventi:**
```javascript
{
    id: 'scad-123',
    title: 'Acme Corp - FAT-2025-001',
    start: '2025-01-15',
    backgroundColor: '#f59e0b',
    borderColor: '#f59e0b',
    extendedProps: {
        // Dati aggiuntivi fattura
    }
}
```

### Viste Disponibili

1. **dayGridMonth** (default)
   - Vista mensile con griglia giorni
   - Eventi come blocchi colorati

2. **dayGridWeek**
   - Vista settimanale
   - Più dettaglio per giorno

3. **listWeek**
   - Lista eventi settimana
   - Formato tabella

---

## 🎨 Best Practices

### Gestione Scadenze

1. **Monitoraggio Quotidiano**
   - Controlla dashboard ogni mattina
   - Verifica "Scadenze Oggi"
   - Invia solleciti per fatture urgenti

2. **Pianificazione Settimanale**
   - Ogni lunedì controlla "Scadenze Settimana"
   - Prepara reminder clienti
   - Coordina con team vendite

3. **Review Mensile**
   - Fine mese: analizza fatture scadute
   - Identifica clienti problematici
   - Pianifica recupero crediti

### Alert e Notifiche

Puoi creare alert automatici con query:

```sql
-- Alert: Scadenze critiche (> 30 giorni ritardo)
SELECT
    numero_fattura,
    azienda,
    cliente_email,
    totale,
    giorni_ritardo,
    CONCAT('Sollecito urgente: ', giorni_ritardo, ' giorni di ritardo') AS azione
FROM v_scadenzario_fatture
WHERE giorni_ritardo > 30
ORDER BY totale DESC;
```

### Report Periodici

**Report Settimanale Scadenze:**
```sql
SELECT
    DATE_FORMAT(data_scadenza, '%W %d/%m') AS giorno,
    COUNT(*) AS fatture,
    SUM(totale) AS importo
FROM v_scadenzario_fatture
WHERE data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
  AND stato IN ('emessa', 'inviata')
GROUP BY data_scadenza
ORDER BY data_scadenza;
```

**Top 10 Clienti Debitori:**
```sql
SELECT
    azienda,
    COUNT(*) AS fatture_scadute,
    SUM(totale) AS debito_totale,
    MAX(giorni_ritardo) AS ritardo_max
FROM v_scadenzario_fatture
WHERE stato = 'scaduta'
GROUP BY azienda, cliente_id
ORDER BY debito_totale DESC
LIMIT 10;
```

### Automazione

Per automatizzare report, crea CRON job:

```php
// area-clienti/cron/report-scadenzario.php
// Invia email giornaliera con scadenze

$stmt = $pdo->query('SELECT * FROM v_statistiche_scadenzario');
$stats = $stmt->fetch();

if ($stats['scadenze_oggi'] > 0) {
    // Invia email alert
    mail('admin@finch-ai.it',
         "Scadenzario: {$stats['scadenze_oggi']} fatture scadono oggi",
         "Totale: €" . number_format($stats['importo_oggi'], 2)
    );
}
```

**CRON Configuration:**
```bash
# Invia report ogni mattina alle 8:00
0 8 * * * php /path/to/area-clienti/cron/report-scadenzario.php
```

---

## 📊 Query Utili

### Scadenze per Cliente

```sql
SELECT
    u.azienda,
    COUNT(*) AS fatture_attive,
    SUM(CASE WHEN f.stato = 'scaduta' THEN 1 ELSE 0 END) AS scadute,
    SUM(f.totale) AS debito_totale
FROM fatture f
JOIN utenti u ON f.cliente_id = u.id
WHERE f.stato IN ('emessa', 'inviata', 'scaduta')
GROUP BY u.id, u.azienda
HAVING fatture_attive > 0
ORDER BY debito_totale DESC;
```

### Trend Pagamenti

```sql
SELECT
    DATE_FORMAT(data_pagamento, '%Y-%m') AS mese,
    COUNT(*) AS fatture_pagate,
    SUM(totale) AS incassi,
    AVG(DATEDIFF(data_pagamento, data_emissione)) AS giorni_medi_pagamento
FROM fatture
WHERE stato = 'pagata'
  AND data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(data_pagamento, '%Y-%m')
ORDER BY mese DESC;
```

### Performance Recupero Crediti

```sql
SELECT
    CASE
        WHEN giorni_ritardo <= 7 THEN '0-7 giorni'
        WHEN giorni_ritardo <= 15 THEN '8-15 giorni'
        WHEN giorni_ritardo <= 30 THEN '16-30 giorni'
        ELSE '> 30 giorni'
    END AS fascia_ritardo,
    COUNT(*) AS num_fatture,
    SUM(totale) AS importo,
    AVG(totale) AS importo_medio
FROM v_scadenzario_fatture
WHERE stato = 'scaduta'
GROUP BY fascia_ritardo
ORDER BY MIN(giorni_ritardo);
```

---

## 🆘 Troubleshooting

### Problema: Vista non aggiornata

**Soluzione:**
Le viste si aggiornano automaticamente. Se vedi dati vecchi:

```sql
-- Forza refresh (non necessario, ma per sicurezza)
DROP VIEW IF EXISTS v_scadenzario_fatture;
-- Poi ri-esegui create view dal file SQL
```

### Problema: Calendario vuoto

**Soluzione:**
1. Verifica che esistano fatture: `SELECT COUNT(*) FROM fatture WHERE stato != 'bozza'`
2. Controlla API: Apri `/area-clienti/api/scadenzario.php?action=eventi&anno=2025&mese=1`
3. Verifica console browser per errori JavaScript

### Problema: Statistiche a zero

**Soluzione:**
```sql
-- Verifica vista statistiche
SELECT * FROM v_statistiche_scadenzario;

-- Se tutto a zero, verifica tabella fatture
SELECT stato, COUNT(*) FROM fatture GROUP BY stato;
```

### Problema: Colori eventi sbagliati

**Soluzione:**
Verifica calcolo priorità nella vista:

```sql
SELECT
    numero_fattura,
    stato,
    data_scadenza,
    DATEDIFF(data_scadenza, CURDATE()) AS giorni,
    priorita,
    colore
FROM v_scadenzario_fatture
LIMIT 10;
```

---

## ✅ Checklist Utilizzo

### Setup Iniziale
- [ ] Database viste create
- [ ] Indici applicati
- [ ] API testata
- [ ] Calendario visualizza eventi
- [ ] Statistiche corrette

### Uso Quotidiano
- [ ] Controlla "Scadenze Oggi" ogni mattina
- [ ] Verifica fatture scadute (rosse)
- [ ] Invia solleciti urgenti
- [ ] Aggiorna stato fatture pagate

### Review Settimanale
- [ ] Analizza "Scadenze Settimana"
- [ ] Pianifica reminder clienti
- [ ] Verifica trend pagamenti
- [ ] Report clienti problematici

### Review Mensile
- [ ] Analizza KPI mese
- [ ] Verifica recupero crediti
- [ ] Identifica pattern ritardi
- [ ] Ottimizza processo fatturazione

---

## 📞 Risorse

### Documentazione

- **FullCalendar**: [fullcalendar.io/docs](https://fullcalendar.io/docs)
- **MySQL Views**: [dev.mysql.com/doc](https://dev.mysql.com/doc/refman/8.0/en/views.html)

### Performance

Per database grandi (>10k fatture):
- Gli indici sono già ottimizzati
- Le viste usano query efficienti
- Considera archiviazione fatture vecchie

---

## 🎉 Riepilogo

Hai ora un sistema completo di scadenzario con:

✅ Vista calendario interattiva FullCalendar
✅ Dashboard KPI in tempo reale
✅ Sistema priorità automatico
✅ Doppia visualizzazione calendario/lista
✅ 3 viste database ottimizzate
✅ API RESTful completa
✅ Integrazione navigazione admin
✅ Query e report utili

**Prossimi passi:**

1. Installa viste database
2. Apri `/area-clienti/admin/scadenzario.php`
3. Esplora calendario e statistiche
4. Configura alert automatici (opzionale)

Buon lavoro con lo scadenzario! 📅
