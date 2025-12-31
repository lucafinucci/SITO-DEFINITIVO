# Gestione Voci Fatture - Implementazione Completata

## Panoramica
È stata implementata la funzionalità completa per gestire le voci delle fatture, permettendo agli amministratori di modificare, aggiungere ed eliminare manualmente le voci di ogni fattura emessa.

## File Creati

### 1. `/area-clienti/admin/fattura-dettaglio.php`
Pagina di dettaglio della fattura che mostra:
- **Informazioni Cliente**: azienda, nome, email, telefono
- **Dati Fattura**: periodo, date emissione/scadenza, importi
- **Tabella Voci**: elenco dettagliato di tutte le voci della fattura
- **Totali Calcolati**: imponibile, IVA e totale automaticamente ricalcolati

#### Funzionalità Principali:
- ✅ **Aggiungi Voce**: pulsante per aggiungere nuove voci alla fattura
- ✅ **Modifica Voce**: pulsante di modifica per ogni riga (icona matita ✏️)
- ✅ **Elimina Voce**: pulsante di eliminazione per ogni riga (icona cestino 🗑️)
- ✅ **Ricalcolo Automatico**: i totali vengono ricalcolati automaticamente quando si aggiungono/modificano/eliminano voci

### 2. `/area-clienti/api/fatture-righe.php`
API REST per la gestione delle righe fattura con le seguenti azioni:

#### `action: 'create'`
Crea una nuova voce nella fattura
```json
{
  "action": "create",
  "fattura_id": 123,
  "servizio_id": 5,  // opzionale
  "descrizione": "Descrizione della voce",
  "quantita": 1.00,
  "prezzo_unitario": 100.00,
  "iva_percentuale": 22.00,
  "ordine": 0
}
```

#### `action: 'update'`
Aggiorna una voce esistente
```json
{
  "action": "update",
  "riga_id": 456,
  "servizio_id": 5,  // opzionale
  "descrizione": "Nuova descrizione",
  "quantita": 2.00,
  "prezzo_unitario": 150.00,
  "iva_percentuale": 22.00,
  "ordine": 0
}
```

#### `action: 'delete'`
Elimina una voce dalla fattura
```json
{
  "action": "delete",
  "riga_id": 456
}
```

## Struttura Database

La tabella `fatture_righe` (già esistente) contiene:
- `id`: ID univoco della riga
- `fattura_id`: riferimento alla fattura
- `servizio_id`: collegamento opzionale al servizio
- `descrizione`: testo descrittivo della voce
- `quantita`: quantità (default 1.00)
- `prezzo_unitario`: prezzo per unità
- `imponibile`: calcolato automaticamente (quantità × prezzo)
- `iva_percentuale`: percentuale IVA (default 22%)
- `iva_importo`: importo IVA calcolato
- `totale`: totale della riga (imponibile + IVA)
- `ordine`: per ordinare le voci nella fattura

## Funzionalità Implementate

### Modal di Creazione/Modifica Voce
Un modal elegante e user-friendly che permette di:
- Selezionare un servizio predefinito (opzionale)
- Inserire una descrizione personalizzata
- Specificare quantità e prezzo unitario
- Modificare la percentuale IVA
- Definire l'ordinamento della voce

### Calcoli Automatici
Il sistema calcola automaticamente:
1. **Imponibile della riga**: `quantità × prezzo_unitario`
2. **IVA della riga**: `imponibile × (iva_percentuale / 100)`
3. **Totale della riga**: `imponibile + iva_importo`
4. **Totali della fattura**: somma di tutte le righe

### Ricalcolo Totali Fattura
La funzione `ricalcolaTotaliFattura()` viene chiamata automaticamente dopo ogni operazione (create/update/delete) e:
- Somma tutti gli importi delle righe
- Aggiorna i totali della fattura principale
- Calcola la percentuale IVA media

## Flusso di Utilizzo

1. **Accesso alla Fattura**
   - Dalla pagina `/area-clienti/admin/fatture.php`
   - Cliccare su "👁️ Visualizza Dettaglio" su una fattura

2. **Aggiungere una Voce**
   - Cliccare su "➕ Aggiungi Voce"
   - Compilare il form nel modal
   - Cliccare su "✓ Salva"
   - La pagina si ricarica con i nuovi totali

3. **Modificare una Voce**
   - Cliccare sull'icona ✏️ accanto alla voce
   - Modificare i dati nel modal
   - Cliccare su "✓ Salva"

4. **Eliminare una Voce**
   - Cliccare sull'icona 🗑️ accanto alla voce
   - Confermare l'eliminazione
   - I totali vengono ricalcolati automaticamente

## Sicurezza

- ✅ **Autenticazione**: solo admin possono accedere
- ✅ **CSRF Protection**: token CSRF in tutte le richieste API
- ✅ **Validazione Input**: controlli server-side su tutti i dati
- ✅ **Prepared Statements**: protezione da SQL injection
- ✅ **Controllo Permessi**: verifica ruolo admin su ogni richiesta

## Aggiornamenti File Esistenti

### `/area-clienti/admin/fatture.php`
Aggiornata la query per includere i campi mancanti:
- `anno` e `mese`
- `iva_percentuale`
- `data_pagamento` e `metodo_pagamento`
- `note`

Il link "Visualizza Dettaglio" era già presente e punta correttamente a `fattura-dettaglio.php`.

## Test Consigliati

1. ✅ Creare una nuova voce in una fattura
2. ✅ Modificare una voce esistente
3. ✅ Eliminare una voce
4. ✅ Verificare che i totali si aggiornino correttamente
5. ✅ Testare con diversi valori di IVA
6. ✅ Verificare l'ordinamento delle voci

## Prossimi Sviluppi (Opzionali)

- [ ] Generazione PDF con le voci dettagliate
- [ ] Invio email fattura con dettaglio voci
- [ ] Import/Export voci da CSV
- [ ] Template voci predefinite
- [ ] Drag & drop per riordinare le voci
- [ ] Duplicazione voci
- [ ] Storico modifiche voci

## Screenshot Funzionalità

La pagina di dettaglio mostra:
- Header con numero fattura e stato
- Box informativi per cliente e dati fattura
- Tabella delle voci con colonne: #, Descrizione, Qtà, Prezzo Unit., Imponibile, IVA %, Totale, Azioni
- Box totali con imponibile, IVA e totale complessivo
- Pulsanti per PDF e invio email

Il modal di aggiunta/modifica include:
- Selezione servizio (opzionale)
- Campo descrizione (obbligatorio)
- Campi quantità e prezzo unitario
- Percentuale IVA
- Campo ordinamento
