# Script CRON - Generazione Automatica Fatture

## Descrizione

Questo script genera automaticamente le fatture mensili per tutti i clienti con servizi attivi.

## Configurazione

### Linux / Unix

Aggiungi al crontab:

```bash
# Genera fatture il primo giorno di ogni mese alle 02:00
0 2 1 * * php /path/to/area-clienti/cron/genera-fatture-automatico.php
```

Per modificare il crontab:
```bash
crontab -e
```

### Windows Task Scheduler

1. Apri "Utilità di pianificazione" (Task Scheduler)
2. Crea una nuova attività di base:
   - **Nome**: Genera Fatture Mensili
   - **Trigger**: Il primo giorno di ogni mese alle 02:00
   - **Azione**: Avvia un programma
   - **Programma**: `C:\xampp\php\php.exe` (o il path del tuo PHP)
   - **Argomenti**: `"C:\Users\oneno\Desktop\SITO\area-clienti\cron\genera-fatture-automatico.php"`

## Esecuzione Manuale

Per testare lo script manualmente:

```bash
php area-clienti/cron/genera-fatture-automatico.php
```

## Log

I log vengono salvati in:
```
area-clienti/cron/logs/fatture-YYYY-MM.log
```

## Funzionamento

Lo script:
1. Calcola il mese precedente
2. Trova tutti i clienti con servizi attivi nel periodo
3. Per ogni cliente, genera una fattura con le righe dei servizi
4. Calcola pro-rata per servizi attivati/disattivati a metà mese
5. Imposta lo stato "emessa" automaticamente
6. Skippa clienti che hanno già una fattura per quel periodo

## Personalizzazione

Per modificare il comportamento:
- **IVA**: Modifica `$ivaPercentuale` (default: 22%)
- **Stato iniziale**: Modifica `"emessa"` nella query INSERT (può essere "bozza")
- **Giorni scadenza**: Modifica `+30 days` per la data di scadenza
