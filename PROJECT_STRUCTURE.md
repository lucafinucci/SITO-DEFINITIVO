# Finch-AI Website - Struttura Progetto

## 📁 Struttura File Ottimizzata

```
finch-ai-website/
├── public/                      # File statici
│   ├── assets/
│   │   └── images/
│   │       ├── LOGO.png         # Logo aziendale
│   │       └── FAVICON_README.md
│   ├── robots.txt               # SEO crawler configuration
│   └── site.webmanifest         # PWA manifest
│
├── src/                         # Codice sorgente
│   ├── components/              # Componenti React riutilizzabili
│   │   ├── FinchAIMockupAnimated.jsx  # Landing page principale completa
│   │   ├── FinchAIMockup.jsx          # Mockup alternativo
│   │   ├── Header.jsx                  # Header con navigazione
│   │   └── Footer.jsx                  # Footer con contatti
│   │
│   ├── pages/                   # Pagine del sito
│   │   ├── Home.jsx             # Home page (per routing multi-pagina)
│   │   ├── ChiSiamo.jsx         # Pagina "Per Aziende" / Chi Siamo
│   │   ├── Servizi.jsx          # Pagina "Soluzioni" con dettaglio moduli
│   │   ├── Contatti.jsx         # Pagina "Demo" / Contatti
│   │   └── ThankYou.jsx         # Thank you page post-contatto
│   │
│   ├── utils/                   # Utility functions
│   │   └── utils.js             # Helper functions
│   │
│   ├── App.jsx                  # App principale con React Router
│   ├── main.jsx                 # Entry point React
│   └── index.css                # Stili globali Tailwind
│
├── index.html                   # HTML template con meta tags SEO
├── package.json                 # Dependencies & scripts
├── vite.config.js               # Vite configuration
├── tailwind.config.js           # Tailwind CSS configuration
├── postcss.config.js            # PostCSS configuration
└── components.json              # shadcn/ui configuration

```

## 🚀 Tecnologie Utilizzate

- **React 18** - UI library
- **Vite** - Build tool & dev server (più veloce di create-react-app)
- **React Router DOM** - Client-side routing
- **Tailwind CSS** - Utility-first CSS framework
- **Framer Motion** - Animazioni (già importato, pronto per l'uso)

## 📄 Descrizione Pagine

### Landing Page (/)
**File**: `src/components/FinchAIMockupAnimated.jsx`

Pagina principale one-page con tutte le sezioni:
- Hero con value proposition + metriche rapide
- SEZIONE 1: Il Problema (6 pain points)
- SEZIONE 2: La Soluzione (piattaforma integrata + benefici)
- SEZIONE 3: I Moduli (Document Intelligence, Production Analytics, Financial Control)
- SEZIONE 4: Per Chi (Manufacturing, Logistica, Servizi, Retail)
- SEZIONE 5: Case Study (automotive manufacturing)
- SEZIONE 6: Perché Finch-AI (metriche concrete + vantaggi competitivi)
- SEZIONE 7: Lead Generation (Demo, Whitepaper, Contatto)

### Chi Siamo/Per Aziende (/chi-siamo.html)
**File**: `src/pages/ChiSiamo.jsx`

- Missione e visione aziendale
- Valori (Pragmatismo, Partnership, Trasparenza, Know-how)
- Perché scegliere Finch-AI

### Soluzioni (/servizi.html)
**File**: `src/pages/Servizi.jsx`

Dettaglio approfondito dei 3 moduli:
- **Document Intelligence**: OCR, validazione, integrazione ERP
- **Production Analytics**: KPI real-time, manutenzione predittiva
- **Financial Control**: Integrazione ERP, forecast, marginalità

### Demo/Contatti (/contatti.html)
**File**: `src/pages/Contatti.jsx`

- 3 opzioni di contatto (Demo Live, Whitepaper, Esperto)
- Trust indicators (10 min, 2-4 sett, ROI 6 mesi)
- Informazioni di contatto complete

### Thank You (/thank-you.html)
**File**: `src/pages/ThankYou.jsx`

- Conferma ricezione richiesta
- Link a contenuti correlati
- CTA per tornare alla home

## 🎨 Design System

### Colori
- **Primary**: Cyan (#22D3EE) / Blue (#3B82F6)
- **Accent**: Purple (#A855F7) / Emerald (#10B97D)
- **Background**: Slate gradients (#0F172A → #1E293B)
- **Text**: White / Slate-300

### Componenti UI
- Cards con glassmorphism effect
- Gradient borders
- Hover effects con glow
- Animated metrics cards
- Interactive buttons con icons

## 📊 Metriche Chiave Evidenziate

- **70%** riduzione tempo elaborazione documenti
- **+1000** documenti/giorno elaborati automaticamente
- **99.2%** accuratezza estrazione dati
- **24/7** monitoraggio operativo continuo
- **2-4 settimane** deployment completo
- **ROI 6 mesi** break-even medio

## 🔧 Comandi Disponibili

```bash
# Sviluppo
npm run dev              # Avvia dev server (http://localhost:5173)

# Build di produzione
npm run build            # Compila per produzione → cartella dist/

# Preview build
npm run preview          # Anteprima build di produzione

# Linting
npm run lint             # ESLint check
```

## 📦 Build di Produzione

```bash
npm run build
```

Genera una cartella `dist/` con:
- HTML, CSS, JS ottimizzati e minificati
- Asset compressi
- Code splitting automatico
- Pronto per deploy su qualsiasi hosting statico

## 🌐 Deploy

Il sito può essere deployato su:
- **Vercel** (consigliato per React/Vite)
- **Netlify**
- **GitHub Pages**
- **AWS S3 + CloudFront**
- Qualsiasi web server statico

### Deploy Vercel (Esempio)
```bash
npm install -g vercel
vercel
```

## 🔍 SEO

Il file `index.html` include:
- Meta tags OpenGraph per social media
- Meta tags Twitter Card
- JSON-LD structured data (Organization, SoftwareApplication, WebSite)
- Canonical URL
- Favicon e app icons
- robots.txt e sitemap placeholder

## 📧 Informazioni di Contatto

### Email
- info@finch-ai.it (informazioni generali)
- sales@finch-ai.it (commerciale)

### Telefono
- +39 012 345 6789 (Lun-Ven 9:00-18:00)

### Sede
Via Example, 123
20100 Milano (MI)
Italia

### Dati Societari
- **Ragione Sociale**: Finch-AI S.r.l.
- **P.IVA**: 12345678901
- **REA**: MI-1234567
- **Capitale Sociale**: €10.000 i.v.

## 📄 Licenza & Compliance

- **ISO 27001** Certified
- **GDPR** Compliant
- **SOC 2** Type II

---

**Ultimo aggiornamento**: Novembre 2025
**Versione**: 1.0.0
