# Directory Upload Training

Questa directory contiene i file caricati dagli utenti per le richieste di addestramento AI.

## Sicurezza

- `.htaccess` blocca l'accesso diretto via HTTP
- `index.php` impedisce il listing della directory
- I file sono organizzati per ID richiesta: `/{richiesta_id}/{files}`

## Configurazione Aruba

Per ambiente produzione su Aruba, configurare il path assoluto nel file `.env`:

```env
UPLOAD_BASE_DIR=/home/username/uploads/training
```

**IMPORTANTE**: Assicurarsi che questa directory sia FUORI da `public_html` in produzione.

## Permessi

Permessi consigliati:
- Directory: `755` (drwxr-xr-x)
- File: `644` (-rw-r--r--)

```bash
chmod 755 uploads/training
chmod 644 uploads/training/.htaccess
```
