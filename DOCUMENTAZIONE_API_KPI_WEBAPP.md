# Documentazione API KPI Document Intelligence

## Endpoint sulla WebApp (app.finch-ai.it)

### Endpoint: GET /api/kpi/documenti

Questo endpoint deve essere implementato sulla webapp esterna `https://app.finch-ai.it` per fornire i KPI di Document Intelligence all'area clienti.

#### Parametri Query String

| Parametro | Tipo | Obbligatorio | Descrizione |
|-----------|------|--------------|-------------|
| `cliente_id` | integer | Sì | ID del cliente nel database |
| `token` | string | Sì | Token di autenticazione condiviso |
| `timestamp` | integer | No | Unix timestamp della richiesta |

#### Headers Richiesta

```
Accept: application/json
User-Agent: Finch-AI Admin/1.0
```

#### Esempio Richiesta

```bash
GET https://app.finch-ai.it/api/kpi/documenti?cliente_id=123&token=INSERISCI_TOKEN_SICURO&timestamp=1703260800
```

#### Risposta di Successo (200 OK)

```json
{
  "success": true,
  "data": {
    "documenti_totali": 12847,
    "documenti_processati": 12234,
    "documenti_mese_corrente": 2847,
    "pagine_analizzate_totali": 45623,
    "pagine_mese_corrente": 8945,
    "accuratezza_media": 96.8,
    "tempo_medio_lettura": 2.4,
    "automazione_percentuale": 94.2,
    "errori_evitati": 312,
    "tempo_risparmiato": "427h",
    "roi": "340%",
    "periodo_riferimento": "2024-12",
    "trend_mensile": [
      {
        "periodo": "2024-07",
        "documenti": 1850,
        "pagine": 6200,
        "automazione": 88.0
      },
      {
        "periodo": "2024-08",
        "documenti": 2100,
        "pagine": 7150,
        "automazione": 90.0
      },
      {
        "periodo": "2024-09",
        "documenti": 2350,
        "pagine": 7890,
        "automazione": 91.5
      },
      {
        "periodo": "2024-10",
        "documenti": 2450,
        "pagine": 8234,
        "automazione": 92.8
      },
      {
        "periodo": "2024-11",
        "documenti": 2600,
        "pagine": 8756,
        "automazione": 93.5
      },
      {
        "periodo": "2024-12",
        "documenti": 2847,
        "pagine": 8945,
        "automazione": 94.2
      }
    ],
    "modelli_attivi": [
      {
        "id": 1,
        "nome": "Fatture Elettroniche",
        "tipo": "DDT & Fatture",
        "accuratezza": 98.5,
        "documenti_processati": 4521,
        "ultima_versione": "2024-11-28"
      },
      {
        "id": 2,
        "nome": "Contratti Commerciali",
        "tipo": "Contratti",
        "accuratezza": 96.2,
        "documenti_processati": 1834,
        "ultima_versione": "2024-11-15"
      }
    ]
  },
  "timestamp": "2024-12-23T15:30:00Z"
}
```

#### Risposta di Errore (401 Unauthorized)

```json
{
  "success": false,
  "error": "Token non valido o mancante"
}
```

#### Risposta di Errore (404 Not Found)

```json
{
  "success": false,
  "error": "Cliente non trovato"
}
```

#### Risposta di Errore (500 Internal Server Error)

```json
{
  "success": false,
  "error": "Errore interno del server"
}
```

---

## Implementazione Lato Area Clienti

### File Implementati

1. **API Admin**: `/area-clienti/api/admin-kpi-clienti.php`
   - Recupera i KPI per tutti i clienti o per un cliente specifico
   - Combina dati locali (database area-clienti) + dati webapp
   - Richiede autenticazione admin

2. **Dashboard Admin**: `/area-clienti/admin/kpi-clienti.php`
   - Visualizzazione tabellare dei KPI per ogni cliente
   - Filtri di ricerca e stato API
   - Summary cards con totali aggregati
   - Dettagli espandibili per ogni cliente

### Utilizzo

#### Accesso alla Dashboard

```
URL: https://tuosito.com/area-clienti/admin/kpi-clienti.php
Autenticazione: Richiesto ruolo "admin"
```

#### API Endpoint Admin

```bash
# Tutti i clienti
GET /area-clienti/api/admin-kpi-clienti.php

# Cliente specifico
GET /area-clienti/api/admin-kpi-clienti.php?cliente_id=123
```

### Configurazione

Modifica il file `/area-clienti/api/admin-kpi-clienti.php` alla riga 72:

```php
$apiEndpoint = 'https://app.finch-ai.it/api/kpi/documenti';
$apiToken = 'IL_TUO_TOKEN_SICURO_QUI';
```

**IMPORTANTE**: Sostituisci `IL_TUO_TOKEN_SICURO_QUI` con un token sicuro condiviso tra area-clienti e webapp.

### Generazione Token Sicuro

```bash
# Genera un token sicuro con OpenSSL
openssl rand -hex 32
```

Oppure in PHP:

```php
bin2hex(random_bytes(32))
```

---

## Flusso dei Dati

```
┌─────────────────────┐
│  Admin Dashboard    │
│  kpi-clienti.php    │
└──────────┬──────────┘
           │
           │ AJAX Request
           ▼
┌─────────────────────┐
│  API Admin          │
│ admin-kpi-clienti   │
└──────────┬──────────┘
           │
           ├──► Database Locale
           │    (servizi_quota_uso)
           │
           └──► API Esterna
                (app.finch-ai.it)
```

---

## Sicurezza

1. **Autenticazione Admin**: Solo utenti con ruolo "admin" possono accedere
2. **Token Condiviso**: Comunicazione sicura tra area-clienti e webapp
3. **HTTPS Obbligatorio**: Tutte le chiamate API devono usare HTTPS
4. **Timeout**: Le chiamate API hanno timeout di 5 secondi
5. **Fallback**: In caso di errore API, vengono mostrati solo i dati locali

---

## Testing

### 1. Test API Admin (locale)

```bash
curl -b "PHPSESSID=your_session_id" \
  "http://localhost/area-clienti/api/admin-kpi-clienti.php"
```

### 2. Test Dashboard

Accedi come admin a:
```
http://localhost/area-clienti/admin/kpi-clienti.php
```

### 3. Test API Webapp (se implementata)

```bash
curl "https://app.finch-ai.it/api/kpi/documenti?cliente_id=1&token=YOUR_TOKEN"
```

---

## Troubleshooting

### API sempre offline

1. Verifica che l'endpoint sia raggiungibile
2. Controlla il token di autenticazione
3. Verifica i log PHP per errori cURL
4. Verifica che HTTPS sia configurato correttamente

### Dati non aggiornati

1. La cache è di 5 minuti, premi "Aggiorna" sulla dashboard
2. Verifica che i dati siano presenti nella tabella `servizi_quota_uso`
3. Controlla che i clienti abbiano Document Intelligence attivo

### Errore 403 Forbidden

Verifica che l'utente loggato abbia ruolo "admin" nel database:

```sql
SELECT id, email, ruolo FROM utenti WHERE id = YOUR_USER_ID;
```

---

## Prossimi Sviluppi

- [ ] Aggiungere export CSV dei KPI
- [ ] Grafici aggregati per tutti i clienti
- [ ] Notifiche quando API esterna non risponde
- [ ] Storico KPI mensili
- [ ] Comparazione performance tra clienti
