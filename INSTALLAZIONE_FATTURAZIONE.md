# Sistema di Fatturazione Automatica - Installazione

## 📋 Panoramica

Sistema completo di fatturazione automatica con generazione mensile delle fatture per i servizi attivi.

## 🗄️ Installazione Database

### 1. Esegui lo script SQL

Importa il file SQL nel tuo database:

```bash
mysql -u root -p finch_ai < database/add_fatture_tables.sql
```

O tramite phpMyAdmin:
1. Apri phpMyAdmin
2. Seleziona il database `finch_ai`
3. Vai su "Importa"
4. Seleziona il file `database/add_fatture_tables.sql`
5. Clicca "Esegui"

### 2. Verifica Tabelle Create

Le seguenti tabelle dovrebbero essere create:
- `fatture` - Tabella principale fatture
- `fatture_righe` - Righe dettaglio fatture
- `fatture_pagamenti` - Storico pagamenti
- `fatture_config` - Configurazione numerazione
- `v_fatture_riepilogo` - Vista riepilogo (opzionale)

## ⚙️ Configurazione

### 1. Personalizza i Dati Azienda

Modifica il file `area-clienti/api/genera-pdf-fattura.php` alle righe 170-175 con i tuoi dati:

```php
<div class="company-name">La Tua Azienda</div>
<div class="company-details">
    Via Example 123, 00100 Roma (RM)<br>
    P.IVA: IT12345678901 • Tel: +39 06 1234567<br>
    Email: fatturazione@tuaazienda.it • Web: www.tuaazienda.it
</div>
```

E le coordinate bancarie alla riga 310:

```php
Pagamento da effettuarsi entro il ' . $dataScadenza . ' tramite bonifico bancario<br>
IBAN: IT00 A000 0000 0000 0000 0000 000 • BIC: TUOBIC<br>
```

### 2. Configura IVA Predefinita

L'IVA di default è 22%. Per modificarla:
- File: `area-clienti/api/genera-fatture-mensili.php` - Riga 43
- File: `area-clienti/cron/genera-fatture-automatico.php` - Riga 34

```php
$ivaPercentuale = 22.00; // Modifica qui
```

## 🔄 Generazione Automatica Fatture (CRON)

### Opzione 1: Linux/Unix

Aggiungi al crontab:

```bash
# Genera fatture il primo giorno di ogni mese alle 02:00
0 2 1 * * php /var/www/html/area-clienti/cron/genera-fatture-automatico.php
```

Per modificare:
```bash
crontab -e
```

### Opzione 2: Windows Task Scheduler

1. Apri "Utilità di pianificazione"
2. Crea nuova attività:
   - **Nome**: Genera Fatture Mensili
   - **Trigger**: Mensile, primo giorno del mese, ore 02:00
   - **Azione**: Avvia programma
     - Programma: `C:\xampp\php\php.exe`
     - Argomenti: `"C:\Users\oneno\Desktop\SITO\area-clienti\cron\genera-fatture-automatico.php"`

### Test Manuale

```bash
php area-clienti/cron/genera-fatture-automatico.php
```

## 📊 Utilizzo

### Dashboard Admin

Accedi a: `/area-clienti/admin/fatture.php`

Funzionalità disponibili:
- ✅ Visualizza tutte le fatture con filtri (anno, mese, stato)
- ✅ Genera fatture mensili (manuale o automatico)
- ✅ Gestisci stati: bozza → emessa → inviata → pagata
- ✅ Registra pagamenti
- ✅ Genera PDF fatture
- ✅ Statistiche fatturato

### Generazione Manuale Fatture

1. Vai su `/area-clienti/admin/fatture.php`
2. Clicca "Genera Fatture Mensili"
3. Seleziona anno e mese
4. Scegli modalità:
   - **Auto**: Genera solo fatture mancanti
   - **Force**: Rigenera tutte (elimina bozze esistenti)
5. Clicca "Genera Fatture"

### Stati Fattura

1. **Bozza** - Fattura creata ma non finalizzata
2. **Emessa** - Fattura emessa ufficialmente
3. **Inviata** - Fattura inviata al cliente
4. **Pagata** - Fattura pagata dal cliente
5. **Scaduta** - Fattura scaduta non pagata
6. **Annullata** - Fattura annullata

## 🔧 Funzionalità Avanzate

### Calcolo Pro-Rata

Il sistema calcola automaticamente il pro-rata per servizi:
- Attivati a metà mese
- Disattivati a metà mese

Esempio: Servizio da €100/mese attivo solo 15 giorni su 30 = €50

### Numerazione Fatture

Formato: `FT-2025-00001`
- Prefisso personalizzabile
- Numerazione progressiva annuale
- Reset automatico ogni anno

### Personalizzazione Numerazione

Modifica nella tabella `fatture_config`:

```sql
UPDATE fatture_config
SET prefisso = 'FINCH',
    formato = '{prefisso}/{anno}/{numero}'
WHERE anno = 2025;
```

Risultato: `FINCH/2025/00001`

## 📄 Generazione PDF

### Attuale (Anteprima HTML)

Le fatture vengono generate come HTML. Per visualizzare:
```
/area-clienti/api/genera-pdf-fattura.php?id=1
```

### Integrazione Libreria PDF (Opzionale)

Per generare PDF reali, installa una libreria come **TCPDF**:

```bash
composer require tecnickcom/tcpdf
```

Poi decommenta le righe nel file `genera-pdf-fattura.php` (righe 70-75).

## 📊 Report ed Export

Export disponibili (in arrivo):
- Export CSV fatture
- Report mensile contabilità
- Riepilogo clienti/fatturato

## 🔐 Sicurezza

- ✅ Protezione CSRF su tutte le API
- ✅ Verifica ruolo admin
- ✅ Validazione input
- ✅ Prepared statements SQL
- ✅ Escape HTML output

## 📝 Log

I log della generazione automatica vengono salvati in:
```
area-clienti/cron/logs/fatture-YYYY-MM.log
```

Esempio log:
```
[2025-01-01 02:00:15] Generazione completata - Periodo: 12/2024 - Generate: 15 - Skippate: 0 - Errori: 0
```

## 🆘 Troubleshooting

### Errore "Fattura già esistente"
La fattura per quel cliente/periodo esiste già. Usa modalità "Force" per rigenerare.

### Numerazione non sequenziale
Verifica la tabella `fatture_config` e resetta se necessario:

```sql
UPDATE fatture_config SET ultimo_numero = 0 WHERE anno = 2025;
```

### CRON non si esegue
- Verifica i permessi del file PHP
- Controlla i log del sistema
- Testa esecuzione manuale

## 🎯 Prossimi Passi

Funzionalità in arrivo:
- [ ] Invio automatico email fatture
- [ ] Integrazione pagamenti Stripe
- [ ] Solleciti automatici
- [ ] Export Excel/CSV
- [ ] Template email personalizzabili
- [ ] Fatture elettroniche XML

## 📞 Supporto

Per problemi o domande, contatta il supporto tecnico.
