# Fix: Redirect alla Pagina di Origine dopo Richiesta Addestramento

## 🐛 Problema Identificato

L'utente poteva accedere al form "Richiedi Addestramento" da **DUE pagine diverse**:

1. **[servizio-dettaglio.php?id=1](area-clienti/servizio-dettaglio.php)** - Pagina principale del servizio Document Intelligence con KPI
2. **[document-intelligence-modelli.php](area-clienti/document-intelligence-modelli.php)** - Pagina dedicata alla gestione modelli AI

**Comportamento Errato**:
- Indipendentemente dalla pagina di origine, il redirect dopo l'invio andava sempre a `servizio-dettaglio.php?id=1`
- L'utente che partiva da `document-intelligence-modelli.php` si ritrovava su una pagina diversa

---

## ✅ Soluzione Implementata

### Sistema di Tracking Pagina di Origine

Implementato un parametro `?from=` per tracciare la provenienza:
- `?from=modelli` → Redirect a `document-intelligence-modelli.php`
- `?from=servizio` (default) → Redirect a `servizio-dettaglio.php?id=1`

---

## 📝 Modifiche Effettuate

### 1. **Link Aggiornati con Parametro `from`**

#### [document-intelligence-modelli.php](area-clienti/document-intelligence-modelli.php)
```php
// Prima
<a href="/area-clienti/richiedi-addestramento.php" class="btn primary">

// Dopo
<a href="/area-clienti/richiedi-addestramento.php?from=modelli" class="btn primary">
```

#### [servizio-dettaglio.php](area-clienti/servizio-dettaglio.php#L383)
```php
// Prima
<a href="/area-clienti/richiedi-addestramento.php?tipo=<?= urlencode($modello['tipo']) ?>">

// Dopo
<a href="/area-clienti/richiedi-addestramento.php?tipo=<?= urlencode($modello['tipo']) ?>&from=servizio">
```

**Anche JavaScript aggiornato** per forzare il parametro `&from=servizio` in tutti i link dinamici.

---

### 2. **Logica Redirect in richiedi-addestramento.php**

#### [richiedi-addestramento.php:9-13](area-clienti/richiedi-addestramento.php#L9-L13)
```php
// Determina pagina di ritorno
$from = $_GET['from'] ?? 'servizio'; // default: servizio-dettaglio
$returnUrl = ($from === 'modelli')
    ? '/area-clienti/document-intelligence-modelli.php?upload=success'
    : '/area-clienti/servizio-dettaglio.php?id=1&upload=success';
```

#### Redirect dopo invio (linea 100):
```php
// Redirect alla pagina di origine
header('Location: ' . $returnUrl);
exit;
```

#### Link "Torna a..." dinamico (linea 188-190):
```php
<a href="<?= htmlspecialchars(str_replace('&upload=success', '', $returnUrl)) ?>">
  ← Torna a <?= $from === 'modelli' ? 'Modelli AI' : 'Document Intelligence' ?>
</a>
```

#### Pulsante "Annulla" dinamico (linea 325):
```php
<a href="<?= htmlspecialchars(str_replace('&upload=success', '', $returnUrl)) ?>" class="btn ghost">
  Annulla
</a>
```

#### Campo hidden nel form (linea 207):
```php
<input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
```

---

### 3. **API upload-training.php**

#### [upload-training.php:44-49](area-clienti/api/upload-training.php#L44-L49)
```php
$from = $_POST['from'] ?? 'servizio'; // Pagina di origine

// Determina URL di ritorno
$returnUrl = ($from === 'modelli')
    ? '/area-clienti/document-intelligence-modelli.php?upload=success'
    : '/area-clienti/servizio-dettaglio.php?id=1&upload=success';
```

#### Risposta JSON (linea 250):
```php
'redirect_url' => $returnUrl
```

---

### 4. **Alert di Successo in document-intelligence-modelli.php**

#### [document-intelligence-modelli.php:44-45](area-clienti/document-intelligence-modelli.php#L44-L45)
```php
// Messaggio di successo dopo upload training
$uploadSuccess = isset($_GET['upload']) && $_GET['upload'] === 'success';
```

#### Alert visivo (linea 105-109):
```php
<?php if ($uploadSuccess): ?>
<div class="alert success" style="margin-bottom: 20px;">
  ✅ <strong>Richiesta inviata con successo!</strong> Il nostro team analizzerà
  la tua richiesta di addestramento entro 24 ore. Riceverai un'email con
  il preventivo e la timeline.
</div>
<?php endif; ?>
```

---

## 🎯 Flussi Corretti

### Flusso A: Da "Modelli AI"
```
1. Utente su: document-intelligence-modelli.php
2. Clicca "➕ Richiedi Addestramento"
3. Va a: richiedi-addestramento.php?from=modelli
4. Compila form e invia (con o senza file)
5. ✅ Redirect a: document-intelligence-modelli.php?upload=success
6. ✅ Mostra alert verde di conferma
```

### Flusso B: Da "Servizio Detail"
```
1. Utente su: servizio-dettaglio.php?id=1
2. Clicca "🔄 Richiedi Addestramento" su un modello
3. Va a: richiedi-addestramento.php?tipo=Fatture&from=servizio
4. Compila form (tipo pre-compilato) e invia
5. ✅ Redirect a: servizio-dettaglio.php?id=1&upload=success
6. ✅ Mostra alert verde di conferma
```

---

## 📊 Tabella Redirect

| **Pagina Origine** | **Parametro** | **Redirect dopo Invio** |
|--------------------|---------------|-------------------------|
| `document-intelligence-modelli.php` | `?from=modelli` | `document-intelligence-modelli.php?upload=success` |
| `servizio-dettaglio.php?id=1` | `?from=servizio` | `servizio-dettaglio.php?id=1&upload=success` |
| Link diretto (nessun parametro) | Default: `servizio` | `servizio-dettaglio.php?id=1&upload=success` |

---

## 🧪 Test

### Test 1: Da Modelli AI
```
1. Vai a: http://localhost/area-clienti/document-intelligence-modelli.php
2. Clicca "➕ Richiedi Addestramento"
3. Verifica URL: ?from=modelli
4. Compila form (con o senza file)
5. Invia
6. ✅ Verifica redirect a document-intelligence-modelli.php?upload=success
7. ✅ Verifica alert verde presente
```

### Test 2: Da Servizio Detail
```
1. Vai a: http://localhost/area-clienti/servizio-dettaglio.php?id=1
2. Clicca "🔄 Richiedi Addestramento" su un modello
3. Verifica URL: ?tipo=...&from=servizio
4. Compila form
5. Invia
6. ✅ Verifica redirect a servizio-dettaglio.php?id=1&upload=success
7. ✅ Verifica alert verde presente
```

### Test 3: Pulsante "Annulla"
```
1. Da entrambe le pagine, clicca "Richiedi Addestramento"
2. Clicca pulsante "Annulla" nel form
3. ✅ Verifica che torni alla pagina di origine corretta
```

### Test 4: Link "Torna a..."
```
1. Da entrambe le pagine, clicca "Richiedi Addestramento"
2. Clicca link "← Torna a ..." in alto
3. ✅ Verifica che il testo sia dinamico:
   - "← Torna a Modelli AI" (se from=modelli)
   - "← Torna a Document Intelligence" (se from=servizio)
4. ✅ Verifica che torni alla pagina corretta
```

---

## 📄 File Modificati

1. **[document-intelligence-modelli.php](area-clienti/document-intelligence-modelli.php)**
   - Aggiunti parametri `?from=modelli` ai link (2 posizioni)
   - Aggiunto controllo `$uploadSuccess` e alert

2. **[servizio-dettaglio.php](area-clienti/servizio-dettaglio.php)**
   - Aggiunto parametro `&from=servizio` al link HTML
   - Aggiornato JavaScript per forzare `&from=servizio` su tutti i link dinamici (3 funzioni)

3. **[richiedi-addestramento.php](area-clienti/richiedi-addestramento.php)**
   - Aggiunta logica determinazione `$returnUrl` dinamico
   - Aggiornato redirect dopo invio
   - Aggiornati link "Torna a..." e "Annulla"
   - Aggiunto campo hidden `from` nel form

4. **[api/upload-training.php](area-clienti/api/upload-training.php)**
   - Aggiunta ricezione parametro `from` da POST
   - Aggiunta logica determinazione `$returnUrl` dinamico
   - Aggiornata risposta JSON con URL corretto

---

## ✨ Benefici

- ✅ UX migliorata: l'utente torna sempre dove ha iniziato
- ✅ Navigazione coerente e prevedibile
- ✅ Feedback visivo chiaro su entrambe le pagine
- ✅ Flessibile: facile aggiungere nuove pagine di origine
- ✅ Backward compatible: default su servizio-dettaglio se manca parametro

---

**Data fix**: 2024-12-16
**Versione**: 1.3
**Status**: ✅ Risolto e Testato
