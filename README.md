# Il Mio Sito Web

Questo è un sito web base con struttura HTML, CSS e JavaScript.

## Struttura del Progetto

```
SITO/
├── index.html          # Pagina principale
├── chi-siamo.html      # Pagina chi siamo
├── servizi.html        # Pagina servizi
├── contatti.html       # Pagina contatti
├── assets/
│   ├── css/
│   │   └── style.css   # Stili CSS
│   ├── js/
│   │   └── script.js   # JavaScript
│   ├── images/
│   │   ├── logo.png    # Logo del sito
│   │   └── hero-image.jpg  # Immagine hero
│   └── fonts/          # Font personalizzati
└── README.md           # Questo file
```

## Come Utilizzare

1. Apri il file `index.html` nel tuo browser
2. Naviga tra le diverse pagine utilizzando il menu
3. Personalizza i contenuti modificando i file HTML
4. Modifica gli stili nel file `style.css`
5. Aggiungi funzionalità JavaScript in `script.js`

## Note

- Ricordati di aggiungere le immagini nella cartella `assets/images/`
- Se utilizzi font personalizzati, inseriscili nella cartella `assets/fonts/`
- Il sito è responsive e si adatta a diversi dispositivi

## Personalizzazione

Per personalizzare il sito:
- Modifica i colori nel file CSS
- Aggiungi le tue immagini
- Personalizza i testi nelle pagine HTML
- Estendi le funzionalità JavaScript

## Browser Supportati

- Chrome
- Firefox
- Safari
- Edge

---

## Riattivare il pulsante "Pianifica la riunione" (Calendly)

Il chatbot raccoglie i lead dal form **"Richiedi una valutazione"** e invia due email:
- una al team (`info@finch-ai.it`)
- una di conferma all'utente

Il pulsante **"Pianifica la riunione"** (link Calendly) è **disattivato** finché
non esiste l'account Calendly. Compare automaticamente — sia nella chat sia
nell'email di conferma — appena imposti l'URL nei due punti seguenti. **Non serve
toccare il codice.**

### Passi per riattivarlo
1. **Frontend (pulsante nella chat)** — in `.env` togli il commento e metti l'URL vero:
   ```
   VITE_BOOKING_URL=https://calendly.com/finch-ai/xxxx
   ```
   Poi ricompila: `npm run build` e fai il deploy della cartella `dist/`.

2. **Email (pulsante nell'email di conferma)** — imposta la variabile lato server.
   In locale è in `.env`:
   ```
   CONTACT_BOOKING_URL=https://calendly.com/finch-ai/xxxx
   ```
   In **produzione (Aruba)** `.env` NON viene deployato: imposta
   `CONTACT_BOOKING_URL` tra le variabili d'ambiente PHP del pannello hosting.

> Usa **lo stesso URL** in entrambe le variabili. Se lasci i valori vuoti/commentati,
> i pulsanti restano nascosti e l'email dice semplicemente che il team ricontatterà
> l'utente per fissare l'appuntamento.

### File coinvolti (per riferimento)
- `src/components/chat/LeadForm.jsx` — legge `VITE_BOOKING_URL`, mostra il pulsante solo se valorizzato
- `public/contact.php` — legge `CONTACT_BOOKING_URL`, costruisce le email HTML (team + conferma utente)
- `src/i18n/locales/{it,en}/common.json` — testo del pulsante in `chat.lead.bookCta`

---

Creato nel 2024
