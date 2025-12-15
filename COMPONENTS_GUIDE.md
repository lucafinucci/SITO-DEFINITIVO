# Guida Componenti Modulari

## 📦 Nuovi Componenti Creati

Sono stati creati 3 componenti modulari riutilizzabili che puoi combinare come preferisci:

### 1. Hero Component
**File**: `src/components/Hero/Hero.jsx`

Sezione hero full-screen con:
- Badge animato "AI per Operazioni Reali"
- Heading con gradient text
- Subtitle descrittivo
- 2 CTA buttons (primario + secondario)
- Quick stats bar (4 metriche chiave)
- Background pattern animato
- Gradient overlay

**Uso**:
```jsx
import Hero from '@/components/Hero/Hero';

function MyPage() {
  return <Hero />;
}
```

---

### 2. Ecosystem Component
**File**: `src/components/Ecosystem/Ecosystem.jsx`

Sezione ecosistema con i 3 moduli principali:
- **Document Intelligence** (cyan theme)
- **Production Analytics** (purple theme)
- **Financial Control** (emerald theme)

Ogni card include:
- Icon + titolo
- Descrizione
- Lista di 3 features principali
- Metrica chiave evidenziata
- Hover effects con glow
- Animazioni con Framer Motion

**Uso**:
```jsx
import Ecosystem from '@/components/Ecosystem/Ecosystem';

function MyPage() {
  return <Ecosystem />;
}
```

---

### 3. Services Component
**File**: `src/components/Services/Services.jsx`

Sezione "Perché Finch-AI" con:
- **4 benefits cards** con metriche (70%, +1000, 99.2%, 24/7)
- **3 competitive advantages** (Deploy Rapido, Zero Lock-in, ROI Garantito)
- **Case study preview** con risultati misurabili (automotive)

**Uso**:
```jsx
import Services from '@/components/Services/Services';

function MyPage() {
  return <Services />;
}
```

---

## 🎨 Design System

### Color Themes
Ogni componente usa temi colore consistenti:

- **Cyan/Blue**: Primary theme, CTA, metriche principali
- **Purple/Pink**: Ecosystem, innovazione
- **Emerald/Teal**: Success, financial, ROI

### Animazioni
Tutti i componenti usano **Framer Motion** per:
- Fade in con stagger children
- Scroll-triggered animations (`whileInView`)
- Hover effects fluidi
- Transition consistenti (0.6s ease-out)

### Responsive
Tutti i componenti sono:
- Mobile-first
- Breakpoints: `sm:` `md:` `lg:`
- Grid responsive: da 1 colonna mobile a 3-4 desktop

---

## 🔄 Due Versioni Disponibili

### Versione 1: Landing Page Completa
**File**: `src/pages/Home.jsx`

Home page con tutte le sezioni integrate in un unico componente gigante (FinchAIMockupAnimated). Include tutto: Hero, Problema, Soluzione, Moduli, Settori, Case Study, Perché Finch-AI, Contatti.

**Quando usare**: Se vuoi una landing page one-page completa senza separazioni.

---

### Versione 2: Componenti Modulari
**File**: `src/pages/HomeModular.jsx`

Home page costruita con componenti separati e riutilizzabili:
```jsx
<Hero />
<Ecosystem />
<Services />
<ContactSection />
```

**Quando usare**:
- Vuoi massima flessibilità
- Vuoi riordinare le sezioni facilmente
- Vuoi riutilizzare componenti in altre pagine
- Vuoi mantenere il codice più pulito e manutenibile

---

## 🚀 Come Switchare Tra le Versioni

### Opzione A: Usa HomeModular come default
Modifica `src/App.jsx`:

```jsx
import HomeModular from '@/pages/HomeModular'; // invece di Home

function App() {
  return (
    <>
      <Header />
      <main>
        <Routes>
          <Route path="/" element={<HomeModular />} /> {/* qui */}
          <Route path="/chi-siamo.html" element={<ChiSiamo />} />
          {/* ... altre routes */}
        </Routes>
      </main>
      <Footer />
    </>
  );
}
```

### Opzione B: Usa componenti singoli in pagine custom
Crea nuove pagine combinando i componenti come vuoi:

```jsx
// src/pages/CustomLanding.jsx
import Hero from '@/components/Hero/Hero';
import Services from '@/components/Services/Services';

function CustomLanding() {
  return (
    <div>
      <Hero />
      {/* Altre sezioni custom */}
      <Services />
    </div>
  );
}
```

---

## 📂 Struttura Componenti

```
src/components/
├── Hero/
│   └── Hero.jsx              # Hero section standalone
├── Ecosystem/
│   └── Ecosystem.jsx         # 3 moduli (Document, Production, Financial)
├── Services/
│   └── Services.jsx          # Benefits + Competitive + Case Study
├── Header.jsx                # Navigation header
├── Footer.jsx                # Footer con contatti
└── FinchAIMockupAnimated.jsx # Landing completa (old version)
```

---

## 🎯 Best Practices

### 1. Import con Path Alias
Usa sempre `@/` per import puliti:
```jsx
import Hero from '@/components/Hero/Hero';
// invece di: import Hero from '../../components/Hero/Hero';
```

### 2. Animazioni Consistenti
Tutti i componenti usano le stesse variants per consistenza:
```jsx
const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.15 }
  }
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6, ease: 'easeOut' }
  }
};
```

### 3. Scroll Animations
Usa `whileInView` per animazioni trigger su scroll:
```jsx
<motion.div
  initial="hidden"
  whileInView="visible"
  viewport={{ once: true, amount: 0.2 }}
  variants={containerVariants}
>
  {/* content */}
</motion.div>
```

### 4. Responsive Grid
Pattern standard per responsive:
```jsx
<div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
  {/* Mobile: 1 col, Tablet: 2 cols, Desktop: 4 cols */}
</div>
```

---

## 🔗 Link Interni

Tutti i componenti usano anchor links per navigazione smooth:
- `#contatti` → Sezione contatti
- `#come-funziona` → Ecosystem section
- `#ecosystem` → ID della sezione
- `#services` → ID della sezione

---

## ✨ Customizzazione

### Cambiare Colori
Modifica le classi Tailwind nei componenti:
```jsx
// Da cyan a purple
className="text-cyan-400"  →  className="text-purple-400"
className="from-cyan-500"  →  className="from-purple-500"
```

### Cambiare Metriche
Modifica gli array di dati nei componenti:
```jsx
const benefits = [
  { metric: '70%', label: '...' },  // Modifica qui
  // ...
];
```

### Aggiungere Nuove Sezioni
Crea un nuovo componente in `src/components/NomeSezione/` e importalo:
```jsx
import NuovaSezione from '@/components/NuovaSezione/NuovaSezione';

function Home() {
  return (
    <>
      <Hero />
      <NuovaSezione />
      <Ecosystem />
    </>
  );
}
```

---

## 📊 Metriche Chiave Usate

I componenti evidenziano queste metriche:
- **70%** riduzione tempo elaborazione
- **+1000** documenti/giorno
- **99.2%** accuratezza estrazione
- **24/7** monitoraggio continuo
- **2-4 sett** deployment
- **ROI 6 mesi** break-even
- **90%** riduzione tempo documenti
- **3x** velocità decisioni
- **100%** sincronizzazione
- **92%** riduzione tempo DDT (case study)

---

**Ultima modifica**: Novembre 2025
**Versione**: 2.0.0
