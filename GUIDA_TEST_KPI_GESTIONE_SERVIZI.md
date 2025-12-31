# 🎯 Guida Test KPI in Gestione Servizi

## ✅ Cosa Abbiamo Fatto

Ho **integrato i KPI Document Intelligence direttamente nella pagina Gestione Servizi esistente**.

Ora quando un cliente ha il servizio **Document Intelligence attivo**, vedrai una **sezione KPI** nella sua card che mostra:
- **Doc/Mese**: Documenti processati (da database locale)
- **Pagine/Mese**: Pagine analizzate (da database locale)
- **Accuratezza**: Percentuale accuratezza (da API webapp)
- **API Status**: Stato connessione API (Online/Offline)

---

## 🚀 Test Rapido (3 passi)

### **STEP 1: Setup Database**

Esegui il file batch per creare i dati di test:
```
SETUP_TEST_KPI.bat
```

Questo crea:
- 1 utente admin
- 5 clienti con Document Intelligence
- Dati di utilizzo

---

### **STEP 2: Login Admin**

1. Vai a: `http://localhost/area-clienti/login.php`
2. Login con:
   - **Email**: `admin@finch-ai.it`
   - **Password**: `password`

---

### **STEP 3: Apri Gestione Servizi**

Vai a: `http://localhost:5173/area-clienti/admin/gestione-servizi.php`

(O nella porta che vedi nello screenshot - può essere 5173 o altra)

---

## 📊 Cosa Dovresti Vedere

### Cliente con Document Intelligence

Nella card del cliente **Luigi Verdi - Azienda Demo Srl** (che vedi nello screenshot):

1. **Servizi Attivi**: Vedrai `✓ Document Intelligence €290/mese`

2. **Sezione KPI** (subito sotto):
   ```
   ┌─────────────────────────────────────────────────┐
   │ 📊 KPI Document Intelligence           🔄       │
   ├─────────────────────────────────────────────────┤
   │ Doc/Mese  │ Pagine/Mese  │ Accuratezza │ API   │
   │   1,250   │    4,800     │   96.8%     │ ✓ On  │
   └─────────────────────────────────────────────────┘
   ```

3. **Stato Caricamento**:
   - ⏳ All'inizio: "Caricamento KPI..."
   - ✅ Dopo ~0.5s: Mostra i 4 KPI
   - ❌ Se errore: Mostra messaggio errore rosso

---

## 🧪 Test Funzionalità

### Test 1: Verifica KPI Caricati

Per ogni cliente con Document Intelligence dovresti vedere:
- **Doc/Mese** e **Pagine/Mese**: Numeri dal database locale
- **Accuratezza**: Percentuale dal mock API (es. 96.8%)
- **API Status**: ✓ Online (verde)

### Test 2: Refresh KPI

Clicca il pulsante **🔄** nella sezione KPI:
- I KPI si ricaricano
- Dovresti vedere "Caricamento..." e poi i dati aggiornati

### Test 3: Console Browser

1. Apri Developer Tools (F12)
2. Vai su **Console**
3. Non dovresti vedere errori rossi
4. Dovresti vedere log tipo: `Errore KPI cliente X: ...` solo se c'è un problema

### Test 4: Network Tab

1. F12 → **Network**
2. Ricarica pagina
3. Cerca chiamate a `admin-kpi-clienti.php?cliente_id=X`
4. Status dovrebbe essere **200 OK**
5. Response dovrebbe contenere JSON con `"success": true`

---

## 📋 Clienti di Test Creati

Se hai eseguito `SETUP_TEST_KPI.bat`, dovresti vedere questi clienti:

1. **Mario Rossi** - Azienda Test SRL
2. **Luigi Verdi** - Innovazione SPA (quello nello screenshot)
3. **Anna Bianchi** - Digital Solutions
4. **Francesco Neri** - Tech Consulting
5. **Giulia Russo** - Smart Business

**Tutti con Document Intelligence attivo** → Tutti mostrano la sezione KPI

---

## ❌ Troubleshooting

### Problema 1: Non vedo la sezione KPI

**Cause possibili:**
- Il cliente non ha Document Intelligence attivo
- Verifica nel database:
  ```sql
  SELECT u.nome, u.cognome, s.codice
  FROM utenti u
  JOIN utenti_servizi us ON u.id = us.user_id
  JOIN servizi s ON us.servizio_id = s.id
  WHERE s.codice = 'DOC-INT' AND us.stato = 'attivo';
  ```

### Problema 2: API Status "✗ Offline"

**Soluzione:**
1. Verifica che i token siano identici:
   - `area-clienti/api/admin-kpi-clienti.php` riga 88
   - `area-clienti/api/mock-kpi-webapp.php` riga 16
   - Devono entrambi essere: `test_token_locale_123456`

2. Verifica modalità mock:
   - `area-clienti/api/admin-kpi-clienti.php` riga 76
   - Deve essere: `$useMockApi = true;`

3. Test API mock diretta:
   ```
   http://localhost/area-clienti/api/mock-kpi-webapp.php?cliente_id=1&token=test_token_locale_123456
   ```
   Dovresti vedere JSON con dati KPI

### Problema 3: "Errore caricamento"

**Debug:**
1. F12 → Console → Cerca errori
2. F12 → Network → Cerca chiamata `admin-kpi-clienti.php`
3. Guarda la risposta (Response tab)
4. Se vedi errore PHP, attiva debug:
   ```php
   // In cima a admin-kpi-clienti.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

### Problema 4: KPI sempre a zero

**Soluzione:**
Aggiungi dati di test manualmente:
```sql
-- Sostituisci X con l'ID del cliente
SET @cliente_id = X;

INSERT INTO servizi_quota_uso (cliente_id, servizio_id, periodo, documenti_usati, pagine_analizzate)
SELECT
    @cliente_id,
    id,
    DATE_FORMAT(NOW(), '%Y-%m'),
    1500,  -- documenti
    6000   -- pagine
FROM servizi
WHERE codice = 'DOC-INT'
LIMIT 1
ON DUPLICATE KEY UPDATE
    documenti_usati = 1500,
    pagine_analizzate = 6000;
```

---

## 🎨 Come Appare

### Prima (senza KPI):
```
┌─────────────────────────────────────┐
│ Azienda Demo Srl                    │
│ Luigi Verdi                         │
│                                     │
│ Servizi Attivi:                     │
│ ✓ Document Intelligence €290/mese  │
│                                     │
│ ➕ Attiva Nuovo Servizio            │
└─────────────────────────────────────┘
```

### Dopo (con KPI):
```
┌──────────────────────────────────────────┐
│ Azienda Demo Srl                         │
│ Luigi Verdi                              │
│                                          │
│ Servizi Attivi:                          │
│ ✓ Document Intelligence €290/mese       │
│                                          │
│ ┌──────────────────────────────────────┐ │
│ │ 📊 KPI Document Intelligence    🔄  │ │
│ ├──────────────────────────────────────┤ │
│ │ Doc/Mese │ Pag/Mese │ Acc │ Status  │ │
│ │  1,250   │  4,800   │96.8%│ ✓ On    │ │
│ └──────────────────────────────────────┘ │
│                                          │
│ ➕ Attiva Nuovo Servizio                 │
└──────────────────────────────────────────┘
```

---

## 🔧 Configurazione

### File Modificati

1. **gestione-servizi.php**
   - Aggiunta sezione KPI per clienti con Document Intelligence
   - Funzioni JavaScript per caricare KPI via AJAX

2. **admin-kpi-clienti.php**
   - Token configurato: `test_token_locale_123456`
   - Modalità mock: `$useMockApi = true`

3. **mock-kpi-webapp.php**
   - Token configurato: `test_token_locale_123456`

---

## 🌐 Flusso Dati

```
Gestione Servizi (pagina)
    ↓ Per ogni cliente con DOC-INT
JavaScript loadKPIForCliente(clienteId)
    ↓ AJAX GET
admin-kpi-clienti.php?cliente_id=X
    ↓
    ├─► DB Locale (servizi_quota_uso)
    │   → documenti_mese, pagine_mese
    │
    └─► Mock API (mock-kpi-webapp.php)
        → accuratezza_media, altri KPI
    ↓
JSON Response combinato
    ↓
Render KPI nella card cliente
```

---

## ✅ Checklist

Prima di testare:
- [ ] XAMPP avviato (Apache + MySQL)
- [ ] `SETUP_TEST_KPI.bat` eseguito
- [ ] Token identici nei 2 file API
- [ ] `$useMockApi = true` in admin-kpi-clienti.php
- [ ] Login come admin
- [ ] Apri gestione-servizi.php

Dopo aver aperto la pagina:
- [ ] Vedo almeno un cliente con Document Intelligence
- [ ] Vedo sezione KPI sotto i servizi attivi
- [ ] KPI mostrano numeri (non trattini o errori)
- [ ] API Status è "✓ Online" (verde)
- [ ] Pulsante 🔄 ricarica i dati
- [ ] Console browser senza errori

---

## 🚀 Passaggio a Produzione

Quando avrai la webapp esterna pronta:

1. Implementa endpoint `/api/kpi/documenti` sulla webapp
2. Cambia in `admin-kpi-clienti.php`:
   ```php
   $useMockApi = false; // Riga 76
   ```
3. Genera token sicuro e configuralo su entrambi i lati

---

**Tutto pronto per il test! 🎉**

Apri gestione-servizi.php e dovresti vedere i KPI per ogni cliente con Document Intelligence attivo!
