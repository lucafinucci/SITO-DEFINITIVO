import { useState } from "react";
import SEO from "./SEO";
import Navbar from "./Navbar";
import { useTheme } from "../context/ThemeContext";
import {
  FileText,
  LineChart,
  Wallet,
  Warehouse,
  CheckCircle,
  ArrowRight,
  Sparkles,
  ShieldCheck,
  Cpu,
  Database,
  TrendingUp,
  Zap,
  Link2,
  Building2,
  Factory,
  ShoppingCart,
  Truck,
  ChevronRight,
  Mail,
  MapPin,
  Linkedin,
  Instagram,
  BarChart3,
  Target,
  Globe,
  Clock,
  Layers,
  AlertTriangle,
} from "lucide-react";

/* ─── Palette ─────────────────────────────────────────────────────────────────
   Dark  bg  : #07090F   (ultra-dark, matching bi.finch-ai)
   Light bg  : #FFFFFF   (pure white)
   Primary   : teal-400 / teal-600  (matching logo "AI" teal-green)
   Gradient  : teal → cyan (dark) | teal-500 → teal-700 (light)
   Secondary : indigo (platform diagram only)
──────────────────────────────────────────────────────────────────────────────*/

function validateEmail(e) {
  return /\S+@\S+\.\S+/.test(e.trim());
}
const DEFAULT_EMAIL = "info@finch-ai.it";

const platformApps = [
  {
    title: "Document Intelligence",
    subtitle: "Automazione Documentale",
    value: "Automazione documentale basata su AI per ridurre errori e tempi operativi.",
    features: ["OCR intelligente", "Validazione automatica"],
    status: "Operativo",
    href: "/soluzioni/document-intelligence",
    cta: "Scopri come funziona",
    icon: FileText,
    color: "teal",
    featured: true,
  },
  {
    title: "Finance Intelligence",
    subtitle: "Analisi Finanziaria AI",
    value: "Analisi automatica di costi, ricavi e cash flow con forecast intelligente.",
    features: ["Cash flow predittivo", "OIC Conforme"],
    status: "Operativo",
    href: "/soluzioni/finance-intelligence",
    cta: "Esplora la finanza",
    icon: Wallet,
    color: "cyan",
    featured: false,
  },
  {
    title: "Production Intelligence",
    subtitle: "Produzione Potenziata",
    value: "Pianificazione e supporto decisionale potenziati dall'AI in tutte le fasi.",
    features: ["Ottimizzazione cicli", "Supporto decisionale"],
    status: "Operativo",
    href: "#contatti",
    cta: "Vedi la produzione",
    icon: LineChart,
    color: "indigo",
    featured: false,
  },
  {
    title: "Warehouse Intelligence",
    subtitle: "Magazzino Intelligente",
    value: "Gestione integrata di magazzino, ordini e offerte con decisioni AI.",
    features: ["Gestione scorte AI", "Ordini automatici"],
    status: "Operativo",
    href: "#contatti",
    cta: "Scopri il magazzino",
    icon: Warehouse,
    color: "purple",
    featured: false,
  },
];

const sectors = [
  { icon: Factory,   label: "Produzione",      desc: "Efficientamento linee e gestione documentale tecnica.",      tags: ["DDT", "MES", "KPI"],                          color: "teal"   },
  { icon: Truck,     label: "Logistica",        desc: "Automazione smistamento documenti e bolle di carico.",       tags: ["Fatture", "Tracking", "OCR"],                  color: "cyan"   },
  { icon: LineChart, label: "Finanza",          desc: "Analisi flussi di cassa e previsioni automatiche.",          tags: ["Forecast", "P&L", "Analisi"],                  color: "indigo" },
  { icon: Building2, label: "Amministrazione",  desc: "Eliminazione totale del data entry manuale.",               tags: ["Zero Errori", "Tempo Libero", "Integrazione"], color: "purple" },
];

const pillars = [
  { num: "01", icon: Zap,        title: "Deploy Rapido",    desc: "Operativo in meno di 24 ore. Nessuna infrastruttura da costruire, nessun mese di implementazione." },
  { num: "02", icon: Link2,      title: "Zero Lock-in",     desc: "API-First. Si integra con ERP, CRM e qualsiasi sistema aziendale esistente senza vincoli." },
  { num: "03", icon: Target,     title: "ROI Misurabile",   desc: "KPI chiari dal giorno uno. Riduci i costi operativi e scala le decisioni strategiche in mesi, non anni." },
];

const techBadges = ["ERP Ready", "GDPR Compliant", "ISO 27001", "API-First", "OIC Conforme"];

/* Tailwind color tokens per variant */
const colCfg = {
  teal:   { icon: "bg-teal-500/10  border border-teal-500/20  text-teal-400",   accent: "text-teal-400",   hover: "hover:border-teal-400/40"   },
  cyan:   { icon: "bg-cyan-500/10  border border-cyan-500/20  text-cyan-400",   accent: "text-cyan-400",   hover: "hover:border-cyan-400/40"   },
  indigo: { icon: "bg-indigo-500/10 border border-indigo-500/20 text-indigo-400", accent: "text-indigo-400", hover: "hover:border-indigo-400/40" },
  purple: { icon: "bg-purple-500/10 border border-purple-500/20 text-purple-400", accent: "text-purple-400", hover: "hover:border-purple-400/40" },
};
const colCfgLight = {
  teal:   { icon: "bg-teal-50  border border-teal-200  text-teal-700",   accent: "text-teal-700",   hover: "hover:border-teal-300"   },
  cyan:   { icon: "bg-cyan-50  border border-cyan-200  text-cyan-700",   accent: "text-cyan-700",   hover: "hover:border-cyan-300"   },
  indigo: { icon: "bg-indigo-50 border border-indigo-200 text-indigo-700", accent: "text-indigo-700", hover: "hover:border-indigo-300" },
  purple: { icon: "bg-purple-50 border border-purple-200 text-purple-700", accent: "text-purple-700", hover: "hover:border-purple-300" },
};

export default function FinchAIMockupV2() {
  const { theme } = useTheme();
  const [formValues, setFormValues] = useState({ name:"", email:"", phone:"", company:"", need:"", message:"", privacy:true });
  const [formErrors, setFormErrors] = useState({});
  const [formStatus, setFormStatus] = useState("idle");
  const [formErrMsg, setFormErrMsg] = useState("");

  const onField = (f) => (e) => setFormValues((p) => ({ ...p, [f]: f==="privacy" ? e.target.checked : e.target.value }));

  const submit = async (e) => {
    e.preventDefault();
    const errs = {};
    if (!formValues.name.trim())       errs.name    = "Inserisci il nome";
    if (!validateEmail(formValues.email)) errs.email = "Email non valida";
    if (!formValues.message.trim())    errs.message  = "Inserisci un messaggio";
    if (!formValues.privacy)           errs.privacy  = "Consenso obbligatorio";
    if (Object.keys(errs).length) { setFormErrors(errs); return; }
    setFormErrors({}); setFormStatus("loading");
    try {
      const res = await fetch(import.meta.env.VITE_CONTACT_ENDPOINT||"/contact.php", {
        method:"POST", headers:{"Content-Type":"application/json"},
        body: JSON.stringify({...formValues, source: window.location.href}),
      });
      if (!res.ok) throw new Error();
      setFormStatus("success");
      setFormValues({ name:"", email:"", phone:"", company:"", need:"", message:"", privacy:true });
    } catch { setFormStatus("error"); setFormErrMsg(`Invio non riuscito. Scrivi a ${DEFAULT_EMAIL}.`); }
  };

  /* ── Derived design tokens ── */
  const D = theme === 'dark';

  // Root backgrounds
  const bgPage     = D ? "bg-[#0B1220]"  : "bg-white";
  const bgCard     = D ? "bg-white/[0.03] border-white/[0.07]"    : "bg-white border-neutral-200";
  const bgCardHov  = D ? "hover:bg-white/[0.05] hover:border-white/[0.12]" : "hover:shadow-md hover:border-neutral-300";
  const bgMuted    = D ? "bg-white/[0.02] border-white/[0.05]"    : "bg-neutral-50 border-neutral-100";
  const txtHead    = D ? "text-white"      : "text-neutral-900";
  const txtBody    = D ? "text-slate-400"  : "text-neutral-500";
  const txtMute    = D ? "text-slate-600"  : "text-neutral-400";
  const txtPrimary = D ? "text-teal-400"   : "text-teal-600";
  const borderFaint= D ? "border-white/[0.05]" : "border-neutral-200";
  const badgeBg    = D ? "border-teal-400/25 bg-teal-400/[0.07]" : "border-teal-600/20 bg-teal-50";
  const badgeTxt   = D ? "text-teal-300"  : "text-teal-700";
  const ctaPrimary = D
    ? "bg-teal-400 hover:bg-teal-300 shadow-teal-500/20 text-[#0B1220]"
    : "bg-teal-600 hover:bg-teal-700 shadow-teal-500/15 text-white";
  const ctaGhost   = D
    ? "border-white/[0.12] text-white hover:bg-white/[0.05]"
    : "border-neutral-300 text-neutral-700 hover:bg-neutral-50";
  const inputCls   = D
    ? "w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-teal-400/50 focus:outline-none transition"
    : "w-full rounded-xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder-neutral-400 focus:border-teal-400 focus:outline-none transition";

  const col   = (c) => D ? colCfg[c]      : colCfgLight[c];

  /* Logo container style: rounded-2xl, white bg in both modes, subtle shadow */
  const logoCont = (size) => `inline-flex items-center justify-center rounded-2xl bg-white shadow-sm ${size}`;

  return (
    <div className={D ? "dark" : ""}>
      <SEO
        title="Finch-AI | AI Enterprise di Prossima Generazione per PMI"
        description="Piattaforma AI per PMI italiane: Business Intelligence, analisi finanziaria, conto economico automatizzato, Document Intelligence e KPI real-time. Integrazione ERP, deploy rapido, Made in Italy."
        keywords="business intelligence PMI, analisi finanziaria AI, conto economico automatizzato, document intelligence, finance intelligence, KPI real-time, intelligenza artificiale imprese, automazione documentale, integrazione ERP, AI Italia"
        canonical="https://finch-ai.it/"
      />
      <style>{`
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        @keyframes floatA { 0%,100%{transform:translateY(0) rotate(2deg)} 50%{transform:translateY(-8px) rotate(2deg)} }
        @keyframes floatB { 0%,100%{transform:translateY(0) rotate(3deg)} 50%{transform:translateY(-6px) rotate(3deg)} }
        @keyframes floatC { 0%,100%{transform:translateY(0) rotate(-2deg)} 50%{transform:translateY(-10px) rotate(-2deg)} }
        @keyframes pring  { 0%{transform:scale(1);opacity:.5} 100%{transform:scale(1.9);opacity:0} }
        .fa{animation:fadeUp .75s ease both}
        .float-a{animation:floatA 6s ease-in-out infinite}
        .float-b{animation:floatB 5s ease-in-out infinite 1s}
        .float-c{animation:floatC 7s ease-in-out infinite .5s}
        .pring{animation:pring 2s ease-out infinite}
      `}</style>

      <div className={`min-h-screen antialiased transition-colors duration-300 ${bgPage} ${txtHead}`}>

        <Navbar />

        {/* ══════════════════════════════════════════════════════════════════
            HERO
        ══════════════════════════════════════════════════════════════════ */}
        <section id="hero" className="relative min-h-screen overflow-hidden pt-20">
          {/* BG layers */}
          <div className="pointer-events-none absolute inset-0">
            {D ? (
              <>
                <div className="absolute inset-0" style={{background:"radial-gradient(ellipse 75% 50% at 50% -5%, rgba(45,212,191,0.10), transparent)"}}/>
                <div className="absolute inset-0" style={{background:"radial-gradient(ellipse 45% 40% at 85% 90%, rgba(99,102,241,0.07), transparent)"}}/>
                <div className="absolute inset-0" style={{backgroundImage:"linear-gradient(rgba(255,255,255,0.016) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.016) 1px,transparent 1px)",backgroundSize:"40px 40px"}}/>
              </>
            ) : (
              <>
                <div className="absolute inset-0 bg-gradient-to-br from-teal-50/50 via-transparent to-sky-50/30"/>
                <div className="absolute inset-0" style={{backgroundImage:"linear-gradient(rgba(13,148,136,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(13,148,136,0.04) 1px,transparent 1px)",backgroundSize:"40px 40px"}}/>
              </>
            )}
          </div>

          <div className="relative mx-auto flex min-h-[calc(100vh-80px)] max-w-screen-xl items-center px-4 sm:px-6 py-12 sm:py-16 lg:py-20">
            <div className="grid w-full items-center gap-12 lg:grid-cols-[3fr_2fr]">

              {/* ── Left ── */}
              <div className="space-y-8 fa">

                {/* Badge */}
                <div className={`inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${badgeBg}`}
                  style={{animationDelay:".1s"}}>
                  <Sparkles className={`h-3.5 w-3.5 ${txtPrimary}`}/>
                  <span className={`text-xs font-semibold uppercase tracking-widest ${badgeTxt}`}>
                    AI Enterprise di Prossima Generazione
                  </span>
                </div>

                {/* Headline — gradient used ONCE only */}
                <div style={{animationDelay:".15s"}}>
                  <h1 className="font-black leading-none tracking-[-0.03em]"
                    style={{fontFeatureSettings:'"kern" 1, "liga" 1'}}>
                    <span className={`block text-5xl sm:text-7xl lg:text-8xl ${txtHead}`}>Automazione</span>
                    <span className={`block text-5xl sm:text-7xl lg:text-8xl ${txtHead}`}>Intelligente.</span>
                    <span className={`mt-2 block text-3xl sm:text-5xl lg:text-6xl ${D ? "text-teal-400" : "text-teal-600"}`}>
                      Decisioni di Valore.
                    </span>
                  </h1>
                </div>

                <p className={`max-w-xl text-lg leading-relaxed ${txtBody}`} style={{animationDelay:".2s"}}>
                  Finch-AI porta l'intelligenza artificiale nei processi quotidiani delle PMI italiane.
                  Meno errori, più controllo, decisioni supportate dai dati — operativi in 24 ore.
                </p>

                {/* CTAs */}
                <div className="flex flex-wrap gap-4" style={{animationDelay:".25s"}}>
                  <a href="#contatti"
                    className={`inline-flex items-center gap-2 rounded-2xl px-5 py-3 sm:px-8 sm:py-4 text-base font-bold shadow-2xl transition hover:brightness-110 active:scale-95 ${ctaPrimary}`}>
                    Assessment Gratuito <ArrowRight className="h-4 w-4"/>
                  </a>
                  <a href="#moduli"
                    className={`inline-flex items-center gap-2 rounded-2xl border px-5 py-3 sm:px-8 sm:py-4 text-base font-semibold transition active:scale-95 ${ctaGhost}`}>
                    Scopri i Moduli <ChevronRight className="h-4 w-4"/>
                  </a>
                </div>

                {/* Stats */}
                <div className={`flex flex-wrap items-center gap-4 sm:gap-8 border-t pt-8 ${borderFaint}`} style={{animationDelay:".3s"}}>
                  {[["BI","Real-Time"],["<24h","Deploy"],["100%","Made in Italy"]].map(([v,l],i)=>(
                    <div key={i} className="flex flex-col">
                      <span className={`text-3xl font-black tracking-tight ${txtHead}`}>{v}</span>
                      <span className={`text-xs font-semibold uppercase tracking-widest ${txtMute}`}>{l}</span>
                    </div>
                  ))}
                </div>
              </div>

              {/* ── Right: floating dashboard mockup ── */}
              <div className="relative hidden h-[480px] lg:block fa" style={{animationDelay:".2s"}}>

                {/* Card A — main chart */}
                <div className={`float-a absolute left-6 top-10 w-72 rounded-2xl border p-6 shadow-2xl ${D?"border-white/[0.08] bg-white/[0.04] backdrop-blur-xl":"border-neutral-200 bg-white shadow-xl"}`}
                  style={{transform:"rotate(2deg)"}}>
                  <div className="mb-4 flex items-center justify-between">
                    <div>
                      <p className={`text-xs font-semibold uppercase tracking-widest ${txtMute}`}>Revenue Forecast</p>
                      <p className={`text-lg font-bold ${txtHead}`}>Q2 2025</p>
                    </div>
                    <div className={`rounded-lg p-2 ${D?"bg-teal-500/10":"bg-teal-50"}`}>
                      <TrendingUp className={`h-5 w-5 ${txtPrimary}`}/>
                    </div>
                  </div>
                  <div className="flex h-16 items-end gap-1.5">
                    {[40,55,48,70,62,85,78].map((h,i)=>(
                      <div key={i} className={`flex-1 rounded-t-sm ${
                        i===5 ? (D?"bg-teal-400":"bg-teal-500") : (D?"bg-white/10":"bg-neutral-200")
                      }`} style={{height:`${h}%`}}/>
                    ))}
                  </div>
                  <p className={`mt-4 flex items-center gap-1.5 text-sm font-semibold ${txtPrimary}`}>
                    <TrendingUp className="h-4 w-4"/> +23% rispetto al Q1
                  </p>
                </div>

                {/* Card B — precision ring */}
                <div className={`float-b absolute right-0 top-0 w-48 rounded-2xl border p-4 shadow-xl ${D?"border-white/[0.08] bg-white/[0.05] backdrop-blur-xl":"border-neutral-200 bg-white shadow-md"}`}
                  style={{transform:"rotate(3deg)"}}>
                  <p className={`mb-3 text-xs font-semibold uppercase tracking-widest ${txtMute}`}>Precisione AI</p>
                  <div className="flex justify-center">
                    <div className="relative flex h-20 w-20 items-center justify-center">
                      <svg className="absolute inset-0 -rotate-90" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="32" fill="none" strokeWidth="6" className={D?"stroke-white/[0.06]":"stroke-neutral-100"}/>
                        <circle cx="40" cy="40" r="32" fill="none" strokeWidth="6"
                          strokeDasharray="201" strokeDashoffset="14"
                          className={D?"stroke-teal-400":"stroke-teal-500"}
                          strokeLinecap="round"/>
                      </svg>
                      <span className={`text-xl font-black ${txtHead}`}>94%</span>
                    </div>
                  </div>
                </div>

                {/* Card C — DDT count */}
                <div className={`float-c absolute bottom-6 right-4 w-56 rounded-2xl border p-4 shadow-xl ${D?"border-white/[0.08] bg-white/[0.05] backdrop-blur-xl":"border-neutral-200 bg-white shadow-md"}`}
                  style={{transform:"rotate(-2deg)"}}>
                  <div className="flex items-center gap-3">
                    <div className={`flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl ${D?"bg-teal-500/15":"bg-teal-50"}`}>
                      <CheckCircle className={`h-5 w-5 ${txtPrimary}`}/>
                    </div>
                    <div>
                      <p className={`text-xs font-semibold ${txtBody}`}>DDT processati</p>
                      <p className={`text-lg font-black ${txtHead}`}>1.247</p>
                    </div>
                  </div>
                  <div className={`mt-3 h-1.5 rounded-full ${D?"bg-white/[0.06]":"bg-neutral-100"}`}>
                    <div className={`h-full w-[87%] rounded-full ${D?"bg-teal-400":"bg-teal-500"}`}/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            TRUST BAR
        ══════════════════════════════════════════════════════════════════ */}
        <div className={`border-y py-5 ${borderFaint}`}>
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
              <span className={`mr-4 text-xs font-semibold uppercase tracking-[0.2em] ${txtMute}`}>
                Standard e certificazioni
              </span>
              {techBadges.map((b)=>(
                <span key={b} className={`rounded-full border px-4 py-1.5 text-xs font-medium transition ${
                  D ? "border-white/[0.07] text-slate-400 hover:border-white/15 hover:text-slate-200"
                    : "border-neutral-200 text-neutral-500 hover:border-neutral-300 hover:text-neutral-700"}`}>
                  {b}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* ══════════════════════════════════════════════════════════════════
            PROBLEM
        ══════════════════════════════════════════════════════════════════ */}
        <section id="problema" className={`py-16 sm:py-24 ${D ? "bg-rose-500/[0.02]" : "bg-rose-50/40"}`}>
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="mb-8 sm:mb-16 max-w-2xl mx-auto text-center">
              <div className={`mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${
                D ? "border-rose-500/25 bg-rose-500/[0.07]" : "border-rose-200 bg-rose-50"}`}>
                <AlertTriangle className={`h-3.5 w-3.5 ${D ? "text-rose-400" : "text-rose-500"}`}/>
                <span className={`text-xs font-semibold uppercase tracking-widest ${D ? "text-rose-300" : "text-rose-600"}`}>Il Problema</span>
              </div>
              <h2 className={`text-4xl font-black tracking-[-0.02em] sm:text-5xl leading-tight ${txtHead}`}>
                Perché molte aziende<br/>sono ancora frenate?
              </h2>
              <p className={`mt-4 text-lg leading-relaxed ${txtBody}`}>
                Sistemi che non comunicano, dati intrappolati in fogli Excel e processi documentali manuali che rubano tempo prezioso alle decisioni strategiche.
              </p>
            </div>
            <div className="grid gap-6 sm:gap-8 lg:grid-cols-3">
              {[
                { icon: FileText, title: "Data Entry Manuale",    desc: "Settimane perse ogni anno a trascrivere ordini, DDT e fatture, con un tasso di errore inevitabile." },
                { icon: Layers,   title: "Dati Frammentati",      desc: "Informazioni disperse tra silos differenti, rendendo impossibile una visione d'insieme in tempo reale." },
                { icon: Clock,    title: "Tempi Morti Operativi", desc: "Processi decisionali lenti perché basati su dati obsoleti o report che richiedono ore per essere pronti." },
              ].map(({ icon: Icon, title, desc }) => (
                <div key={title} className={`rounded-3xl border p-6 sm:p-8 transition-all duration-300 ${
                  D ? "border-rose-500/10 bg-rose-500/[0.03] hover:border-rose-500/25"
                    : "border-rose-100 bg-white hover:shadow-md hover:border-rose-200"}`}>
                  <div className={`mb-5 flex h-12 w-12 items-center justify-center rounded-xl ${
                    D ? "bg-rose-500/10 text-rose-400" : "bg-rose-50 text-rose-500"}`}>
                    <Icon className="h-6 w-6" strokeWidth={1.5}/>
                  </div>
                  <h3 className={`mb-3 text-xl font-bold ${txtHead}`}>{title}</h3>
                  <p className={`text-sm leading-relaxed ${txtBody}`}>{desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            MODULES
        ══════════════════════════════════════════════════════════════════ */}
        <section id="moduli" className="py-16 sm:py-24 lg:py-32">
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="mb-8 sm:mb-16 max-w-2xl">
              <div className={`mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${badgeBg}`}>
                <Sparkles className={`h-3.5 w-3.5 ${txtPrimary}`}/>
                <span className={`text-xs font-semibold uppercase tracking-widest ${badgeTxt}`}>I Moduli</span>
              </div>
              <h2 className={`text-4xl font-black tracking-[-0.02em] sm:text-5xl leading-tight ${txtHead}`}>
                Un ecosistema AI,<br/>già operativo.
              </h2>
              <p className={`mt-4 text-lg leading-relaxed ${txtBody}`}>
                Quattro moduli nativi, integrabili tra loro e con i tuoi sistemi. Nessun lock-in, deploy in un giorno.
              </p>
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
              {platformApps.map((app) => {
                const Icon = app.icon;
                const c = col(app.color);
                const inner = (
                  <div className={`group flex h-full flex-col rounded-3xl border p-5 sm:p-8 transition-all duration-300 ${D?`border-white/[0.07] bg-white/[0.02] backdrop-blur-sm ${c.hover}`:`border-neutral-200 bg-white ${c.hover}`}`}>
                    <div className="mb-6 flex items-start justify-between">
                      <div className={`flex h-12 w-12 items-center justify-center rounded-xl ${c.icon}`}>
                        <Icon className="h-6 w-6" strokeWidth={1.5}/>
                      </div>
                      <span className={`rounded-full border px-3 py-1 text-xs font-semibold ${
                        D ? "border-teal-500/20 bg-teal-500/10 text-teal-300"
                          : "border-teal-500/30 bg-teal-50 text-teal-700"}`}>
                        {app.status}
                      </span>
                    </div>
                    <p className={`mb-1 text-xs font-semibold uppercase tracking-widest ${txtMute}`}>{app.subtitle}</p>
                    <h3 className={`mb-3 text-xl font-bold ${txtHead}`}>{app.title}</h3>
                    <p className={`mb-6 flex-1 text-sm leading-relaxed ${txtBody}`}>{app.value}</p>
                    <div className="mb-6 flex flex-wrap gap-2">
                      {app.features.map((f)=>(
                        <span key={f} className={`flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ${
                          D?"bg-white/[0.04] text-slate-300":"bg-neutral-50 border border-neutral-200 text-neutral-600"}`}>
                          <CheckCircle className={`h-3 w-3 ${c.accent}`}/>{f}
                        </span>
                      ))}
                    </div>
                    <a href={app.href} className={`inline-flex items-center gap-1.5 text-sm font-semibold transition-all group-hover:gap-3 ${c.accent}`}>
                      {app.cta} <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1"/>
                    </a>
                  </div>
                );
                return app.featured ? (
                  <div key={app.title} className={`rounded-3xl p-[1px] ${
                    D ? "bg-teal-400/20"
                    : "bg-teal-100"}`}>
                    {inner}
                  </div>
                ) : <div key={app.title}>{inner}</div>;
              })}
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            PLATFORM FLOW
        ══════════════════════════════════════════════════════════════════ */}
        <section id="piattaforma" className={`border-y py-12 sm:py-20 lg:py-24 ${borderFaint} ${D?"":"bg-neutral-50/40"}`}>
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="grid items-center gap-10 sm:gap-16 lg:grid-cols-[5fr_6fr]">
              {/* Text */}
              <div className="space-y-8">
                <div>
                  <div className={`mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${badgeBg}`}>
                    <Cpu className={`h-3.5 w-3.5 ${txtPrimary}`}/>
                    <span className={`text-xs font-semibold uppercase tracking-widest ${badgeTxt}`}>Come Funziona</span>
                  </div>
                  <h2 className={`text-4xl font-black tracking-[-0.02em] sm:text-5xl leading-tight ${txtHead}`}>
                    Da dati sparsi<br/>a decisioni precise.
                  </h2>
                  <p className={`mt-4 text-lg leading-relaxed ${txtBody}`}>
                    Finch-AI connette tutte le fonti dati aziendali e le trasforma in insight azionabili in tempo reale.
                  </p>
                </div>
                <div className="space-y-4">
                  {[
                    {icon:Link2,    title:"Integrazione Nativa",  desc:"Si connette con ERP, MES, CRM tramite API standard. Nessun middleware custom."},
                    {icon:Cpu,      title:"AI Agentic Core",       desc:"Motori AI che ragionano, decidono e agiscono autonomamente per ottimizzare ogni processo."},
                    {icon:ShieldCheck, title:"Security & Privacy", desc:"GDPR-compliant by design. Dati crittografati, audit log, zero data sharing con terze parti."},
                  ].map(({icon:Icon,title,desc})=>(
                    <div key={title} className={`flex gap-4 rounded-2xl border p-5 transition ${D?"border-white/[0.06] bg-white/[0.02] hover:border-white/10":"border-neutral-200 bg-white hover:border-neutral-300"}`}>
                      <div className={`flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl ${D?"bg-teal-500/10 text-teal-400":"bg-teal-50 text-teal-600"}`}>
                        <Icon className="h-5 w-5" strokeWidth={1.5}/>
                      </div>
                      <div>
                        <p className={`font-bold ${txtHead}`}>{title}</p>
                        <p className={`mt-1 text-sm leading-relaxed ${txtBody}`}>{desc}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Flow diagram */}
              <div className={`rounded-3xl border p-5 sm:p-8 ${D?"border-white/[0.07] bg-white/[0.02] backdrop-blur-xl":"border-neutral-200 bg-white shadow-sm"}`}>
                {/* Inputs */}
                <div className="flex justify-center gap-3">
                  {[{icon:Database,label:"ERP / MES"},{icon:FileText,label:"Documenti"},{icon:TrendingUp,label:"Finanziari"}].map(({icon:Icon,label})=>(
                    <div key={label} className={`flex flex-col items-center gap-2 rounded-xl border px-4 py-3 ${D?"border-white/[0.08] bg-white/[0.03] text-slate-300":"border-neutral-200 bg-neutral-50 text-neutral-600"}`}>
                      <Icon className="h-4 w-4" strokeWidth={1.5}/><span className="text-xs font-medium">{label}</span>
                    </div>
                  ))}
                </div>
                {/* Connector */}
                <div className="my-5 flex flex-col items-center gap-1">
                  <div className={`h-1.5 w-1.5 rounded-full ${D?"bg-teal-400/60":"bg-teal-400"}`}/>
                  <div className={`h-8 w-px ${D?"bg-gradient-to-b from-teal-400/40 to-teal-400/10":"bg-gradient-to-b from-teal-400/40 to-teal-400/10"}`}/>
                  <div className={`h-1.5 w-1.5 rounded-full ${D?"bg-teal-400/30":"bg-teal-300"}`}/>
                </div>
                {/* Core */}
                <div className="flex flex-col items-center gap-3">
                  <div className="relative flex h-20 w-20 items-center justify-center">
                    <div className={`pring absolute inset-0 rounded-full ${D?"bg-teal-400/15":"bg-teal-400/10"}`}/>
                    <div className={`relative flex h-20 w-20 items-center justify-center rounded-full shadow-2xl ${
                      D ? "bg-teal-400 shadow-teal-400/30"
                      : "bg-teal-600 shadow-teal-500/25"}`}>
                      <Cpu className="h-8 w-8 text-white" strokeWidth={1.5}/>
                    </div>
                  </div>
                  <p className={`text-xs font-black uppercase tracking-[0.15em] ${txtPrimary}`}>Finch-AI Core</p>
                </div>
                {/* Connector */}
                <div className="my-5 flex flex-col items-center gap-1">
                  <div className={`h-1.5 w-1.5 rounded-full ${D?"bg-teal-400/30":"bg-teal-300"}`}/>
                  <div className={`h-8 w-px ${D?"bg-gradient-to-b from-teal-400/10 to-teal-400/40":"bg-gradient-to-b from-teal-400/10 to-teal-400/40"}`}/>
                  <div className={`h-1.5 w-1.5 rounded-full ${D?"bg-teal-400/60":"bg-teal-400"}`}/>
                </div>
                {/* Outputs */}
                <div className="flex justify-center gap-3">
                  {[{icon:BarChart3,label:"Dashboard KPI"},{icon:Target,label:"Decisioni"},{icon:Globe,label:"Insights"}].map(({icon:Icon,label})=>(
                    <div key={label} className={`flex flex-col items-center gap-2 rounded-xl border px-4 py-3 ${
                      D?"border-teal-500/20 bg-teal-500/[0.07] text-teal-300":"border-teal-200 bg-teal-50 text-teal-700"}`}>
                      <Icon className="h-4 w-4" strokeWidth={1.5}/><span className="text-xs font-medium">{label}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            SECTORS
        ══════════════════════════════════════════════════════════════════ */}
        <section id="settori" className="py-16 sm:py-24">
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="mb-8 sm:mb-16 text-center">
              <div className={`mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${badgeBg}`}>
                <Globe className={`h-3.5 w-3.5 ${txtPrimary}`}/>
                <span className={`text-xs font-semibold uppercase tracking-widest ${badgeTxt}`}>Per Chi</span>
              </div>
              <h2 className={`text-4xl font-black tracking-[-0.02em] sm:text-5xl ${txtHead}`}>
                Settori che Trasformiamo
              </h2>
              <p className={`mt-4 text-lg ${txtBody}`}>Soluzioni verticali ottimizzate per le esigenze specifiche del tuo settore.</p>
            </div>
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
              {sectors.map(({ icon: Icon, label, desc, tags, color }) => (
                <div key={label} className={`group rounded-2xl border p-5 sm:p-6 transition-all duration-300 ${
                  D ? `border-white/[0.06] bg-white/[0.02] ${colCfg[color].hover}`
                    : `border-neutral-200 bg-white hover:shadow-md ${colCfgLight[color].hover}`}`}>
                  <div className={`mb-4 flex h-11 w-11 items-center justify-center rounded-xl ${
                    D ? colCfg[color].icon : colCfgLight[color].icon}`}>
                    <Icon className="h-5 w-5" strokeWidth={1.5}/>
                  </div>
                  <p className={`font-bold text-lg ${txtHead}`}>{label}</p>
                  <p className={`mt-1 mb-4 text-sm leading-relaxed ${txtBody}`}>{desc}</p>
                  <div className="flex flex-wrap gap-1.5">
                    {tags.map(tag => (
                      <span key={tag} className={`rounded-full px-2.5 py-1 text-xs font-semibold ${
                        D ? "bg-white/[0.04] text-slate-300"
                          : "border border-neutral-200 bg-neutral-50 text-neutral-600"}`}>
                        {tag}
                      </span>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            PILLARS
        ══════════════════════════════════════════════════════════════════ */}
        <section className={`border-y py-12 sm:py-20 lg:py-24 ${borderFaint} ${D?"bg-white/[0.01]":"bg-neutral-50/50"}`}>
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="mb-8 sm:mb-16 text-center">
              <h2 className={`text-3xl font-black tracking-[-0.02em] sm:text-4xl ${txtHead}`}>Perché scegliere Finch-AI</h2>
            </div>
            <div className="grid gap-8 lg:grid-cols-3">
              {pillars.map(({num,icon:Icon,title,desc})=>(
                <div key={title} className={`rounded-3xl border p-6 sm:p-10 transition ${D?"border-white/[0.06] bg-white/[0.02] hover:border-white/[0.1]":"border-neutral-200 bg-white hover:shadow-md"}`}>
                  <div className={`mb-2 select-none text-7xl font-black leading-none ${D?"text-white/[0.03]":"text-neutral-100"}`}>{num}</div>
                  <div className={`mb-4 flex h-14 w-14 items-center justify-center rounded-2xl ${D?"bg-teal-500/10 text-teal-400":"bg-teal-50 text-teal-600"}`}>
                    <Icon className="h-7 w-7" strokeWidth={1.5}/>
                  </div>
                  <h3 className={`text-2xl font-bold ${txtHead}`}>{title}</h3>
                  <p className={`mt-3 leading-relaxed ${txtBody}`}>{desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            CTA
        ══════════════════════════════════════════════════════════════════ */}
        <section className="relative overflow-hidden py-20 sm:py-32 lg:py-40">
          <div className="pointer-events-none absolute inset-0">
            {D ? (
              <div className="absolute inset-0 bg-[#0d1628]"/>
            ) : (
              <div className="absolute inset-0 bg-neutral-50"/>
            )}
          </div>
          <div className="relative mx-auto max-w-3xl px-4 sm:px-6 text-center">
            <div className={`mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${badgeBg}`}>
              <Zap className={`h-3.5 w-3.5 ${txtPrimary}`}/>
              <span className={`text-xs font-semibold uppercase tracking-widest ${badgeTxt}`}>Inizia oggi</span>
            </div>
            <h2 className={`text-3xl sm:text-5xl font-black tracking-[-0.03em] leading-tight lg:text-6xl ${txtHead}`}>
              Pronto a eliminare<br/>i colli di bottiglia?
            </h2>
            <p className={`mx-auto mt-6 max-w-xl text-lg leading-relaxed ${txtBody}`}>
              Prenota un assessment gratuito. In 30 minuti analizziamo i tuoi processi e identifichiamo dove l'AI genera valore reale.
            </p>
            <div className="mt-10 flex flex-wrap justify-center gap-4">
              <a href="#contatti" className={`inline-flex items-center gap-2 rounded-2xl px-8 py-4 text-base font-bold shadow-2xl transition hover:brightness-110 active:scale-95 ${ctaPrimary}`}>
                Assessment Gratuito <ArrowRight className="h-4 w-4"/>
              </a>
              <a href={`mailto:${DEFAULT_EMAIL}`} className={`inline-flex items-center gap-2 rounded-2xl border px-8 py-4 text-base font-semibold transition active:scale-95 ${ctaGhost}`}>
                Scrivici
              </a>
            </div>
            <p className={`mt-8 flex items-center justify-center gap-2 text-xs ${txtMute}`}>
              <ShieldCheck className="h-3.5 w-3.5"/> Assessment gratuito. Nessuna carta di credito richiesta.
            </p>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            CONTACT FORM
        ══════════════════════════════════════════════════════════════════ */}
        <section id="contatti" className="py-16 sm:py-24 lg:py-32">
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="grid gap-10 sm:gap-16 lg:grid-cols-[1fr_480px]">
              {/* Left info */}
              <div>
                <div className={`mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 ${badgeBg}`}>
                  <Mail className={`h-3.5 w-3.5 ${txtPrimary}`}/>
                  <span className={`text-xs font-semibold uppercase tracking-widest ${badgeTxt}`}>Contattaci</span>
                </div>
                <h2 className={`text-4xl font-black tracking-[-0.02em] sm:text-5xl leading-tight ${txtHead}`}>
                  Parliamo del<br/>tuo progetto.
                </h2>
                <p className={`mt-4 max-w-md text-lg leading-relaxed ${txtBody}`}>
                  Rispondiamo entro 24 ore. Nessun impegno iniziale, solo una conversazione per capire le tue esigenze.
                </p>
                <div className="mt-8 space-y-3">
                  {["Risposta in 24h lavorative","Zero impegno iniziale","Analisi gratuita del caso d'uso"].map((t)=>(
                    <div key={t} className="flex items-center gap-3">
                      <CheckCircle className={`h-4 w-4 flex-shrink-0 ${txtPrimary}`}/>
                      <span className={`text-sm ${txtBody}`}>{t}</span>
                    </div>
                  ))}
                </div>
                <div className="mt-12 space-y-4">
                  {[{icon:Mail,label:DEFAULT_EMAIL},{icon:Globe,label:"finch-ai.it"},{icon:MapPin,label:"Italia"}].map(({icon:Icon,label})=>(
                    <div key={label} className="flex items-center gap-3">
                      <div className={`flex h-9 w-9 items-center justify-center rounded-lg border ${D?"border-white/[0.08] bg-white/[0.03]":"border-neutral-200 bg-neutral-50"}`}>
                        <Icon className={`h-4 w-4 ${txtMute}`}/>
                      </div>
                      <span className={`text-sm ${txtBody}`}>{label}</span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Right form */}
              <div className={`rounded-3xl border p-5 sm:p-8 ${D?"border-white/[0.08] bg-white/[0.02] backdrop-blur-xl":"border-neutral-200 bg-white shadow-sm"}`}>
                <h3 className={`mb-6 text-xl font-bold ${txtHead}`}>Parla con un esperto</h3>
                <form onSubmit={submit} className="space-y-4">
                  <div className="grid gap-4 sm:grid-cols-2">
                    {[
                      {id:"name",  label:"Nome *",  type:"text",  ph:"Mario Rossi",     err:formErrors.name},
                      {id:"email", label:"Email *", type:"email", ph:"nome@azienda.it", err:formErrors.email},
                      {id:"phone", label:"Telefono",type:"tel",   ph:"+39 333 1234567", err:null},
                      {id:"company",label:"Azienda",type:"text",  ph:"Ragione sociale",  err:null},
                    ].map(({id,label,type,ph,err})=>(
                      <div key={id} className="space-y-1.5">
                        <label className={`text-sm font-medium ${txtBody}`}>{label}</label>
                        <input type={type} value={formValues[id]} onChange={onField(id)} className={inputCls} placeholder={ph}/>
                        {err && <p className="text-xs text-rose-400">{err}</p>}
                      </div>
                    ))}
                  </div>
                  <div className="space-y-1.5">
                    <label className={`text-sm font-medium ${txtBody}`}>Esigenza principale</label>
                    <input type="text" value={formValues.need} onChange={onField("need")} className={inputCls} placeholder="Automazione DDT, dashboard KPI..."/>
                  </div>
                  <div className="space-y-1.5">
                    <label className={`text-sm font-medium ${txtBody}`}>Messaggio *</label>
                    <textarea rows={4} value={formValues.message} onChange={onField("message")} className={`${inputCls} resize-none`} placeholder="Descrivi il caso d'uso o cosa vuoi ottenere"/>
                    {formErrors.message && <p className="text-xs text-rose-400">{formErrors.message}</p>}
                  </div>
                  <div className="flex items-start gap-2.5">
                    <input type="checkbox" id="priv2" checked={formValues.privacy} onChange={onField("privacy")}
                      className="mt-0.5 h-4 w-4 rounded accent-teal-500"/>
                    <label htmlFor="priv2" className={`text-sm leading-relaxed ${txtBody}`}>
                      Accetto il trattamento dei dati secondo la{" "}
                      <a href="/privacy-policy.html" className={`underline ${txtPrimary}`}>Privacy Policy</a>.
                    </label>
                  </div>
                  {formErrors.privacy && <p className="text-xs text-rose-400">{formErrors.privacy}</p>}
                  <button type="submit" disabled={formStatus==="loading"}
                    className={`w-full rounded-xl py-3.5 text-sm font-bold shadow-lg transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 ${ctaPrimary}`}>
                    {formStatus==="loading" ? "Invio in corso..." : "Invia messaggio"}
                  </button>
                  {formStatus==="success" && (
                    <div className="rounded-xl border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-400">
                      Messaggio inviato. Ti risponderemo entro 24h lavorative.
                    </div>
                  )}
                  {formStatus==="error" && (
                    <div className="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-400">{formErrMsg}</div>
                  )}
                </form>
              </div>
            </div>
          </div>
        </section>

        {/* ══════════════════════════════════════════════════════════════════
            FOOTER
        ══════════════════════════════════════════════════════════════════ */}
        <footer className={`border-t py-8 ${borderFaint}`}>
          <div className="mx-auto max-w-screen-xl px-4 sm:px-6">
            <div className="flex flex-wrap items-center justify-between gap-4">
              <div className="flex items-center gap-3">
                {/* Logo con bordi arrotondati anche nel footer */}
                <div className={logoCont("px-2.5 py-1.5")}>
                  <img src="/assets/images/LOGO.png" alt="Finch-AI" className="h-7 w-auto"/>
                </div>
                <span className={`text-sm ${txtMute}`}>© 2025 Finch-AI S.r.l.</span>
              </div>
              <div className="flex flex-wrap items-center gap-6">
                {[["Privacy Policy","/privacy-policy.html"],["Cookie Policy","/cookie-policy.html"],["Note Legali","/note-legali.html"]].map(([l,h])=>(
                  <a key={l} href={h} className={`text-xs transition hover:opacity-100 ${txtMute}`}>{l}</a>
                ))}
                {[{Icon:Linkedin,href:"https://www.linkedin.com/company/finch-ai"},{Icon:Instagram,href:"https://www.instagram.com/finch_ai_it"}].map(({Icon,href})=>(
                  <a key={href} href={href} target="_blank" rel="noopener noreferrer"
                    className={`flex h-8 w-8 items-center justify-center rounded-lg border transition ${
                      D?"border-white/[0.08] text-slate-500 hover:border-white/20 hover:text-slate-300"
                       :"border-neutral-200 text-neutral-400 hover:border-neutral-300 hover:text-neutral-600"}`}>
                    <Icon className="h-3.5 w-3.5"/>
                  </a>
                ))}
              </div>
            </div>
          </div>
        </footer>

      </div>
    </div>
  );
}
