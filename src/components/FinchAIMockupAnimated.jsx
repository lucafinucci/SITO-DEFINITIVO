import { useRef, useEffect, useState } from "react";
import {
  FileText as FileTextIcon,
  LineChart,
  Wallet as WalletIcon,
  Warehouse,
  CheckCircle,
  Clock,
  Search,
  Users,
  BarChart3,
  Cpu,
  Layers,
  Database,
  ChevronRight,
  Monitor,
  Sparkles,
  ArrowRight,
  TrendingUp,
  Target,
  ShieldCheck,
  Zap as ZapIcon,
  Link as LinkIcon,
} from "lucide-react";
import ContactForm from "./ContactForm";
import Layout from "./Layout";

export default function FinchAIMockupAnimated() {
  const platformApps = [
    {
      title: "Finch-AI Document Intelligence",
      value: "Automazione documentale basata su AI per ridurre errori e tempi operativi.",
      description: "Configurazione autonoma assistita dall'AI.",
      output: "Dati documentali strutturati e verificati",
      status: "Operativo",
      cta: "Scopri come funziona",
      href: "/soluzioni/document-intelligence",
      icon: <FileTextIcon className="h-6 w-6" strokeWidth={1.5} />,
    },
    {
      title: "Finch-AI Production Intelligence",
      value: "Pianificazione e supporto decisionale potenziati dall'AI.",
      description: "Configurazione e gestione assistite dall'AI in tutte le fasi.",
      output: "Pianificazione operativa e supporto decisionale in produzione",
      status: "Operativo",
      cta: "Vedi la produzione",
      href: "#contatti",
      icon: <LineChart className="h-6 w-6" strokeWidth={1.5} />,
    },
    {
      title: "Finch-AI Finance Intelligence",
      value: "Analisi automatica di costi, ricavi e cash flow.",
      description: "Previsioni economico-finanziarie per guidare le decisioni.",
      output: "Forecast e indicatori economici intelligenti",
      status: "Operativo",
      cta: "Esplora la finanza",
      href: "#contatti",
      icon: <WalletIcon className="h-6 w-6" strokeWidth={1.5} />,
    },
    {
      title: "Finch-AI Warehouse Intelligence",
      value: "Gestione integrata di magazzino, ordini e offerte.",
      description: "Decisioni e operatività potenziate dall'AI.",
      output: "Magazzino, ordini e offerte sincronizzati in un unico flusso",
      status: "Operativo",
      cta: "Scopri il magazzino",
      href: "#contatti",
      icon: <Warehouse className="h-6 w-6" strokeWidth={1.5} />,
    },
  ];

  return (
    <Layout>
      {/* HERO */}
      <section id="hero" className="pb-12 pt-12 lg:pt-24">
        <div className="mx-auto max-w-3xl text-center">
          <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary animate-[fadeUp_0.8s_ease_both]">
            <Sparkles className="h-3.5 w-3.5" />
            AI Enterprise di Prossima Generazione
          </span>

          <h1 className="mt-5 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl animate-[fadeUp_0.9s_ease_0.12s_both]">
            <span className="text-foreground dark:hidden">
              Automazione <span className="text-emerald-500">Intelligente</span> per<br />
              <span className="text-emerald-500">Decisioni di Valore</span>
            </span>
            <span className="hidden dark:inline bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 via-sky-400 to-blue-500">
              Automazione Intelligente per<br />
              Decisioni di Valore
            </span>
          </h1>

          <p className="mt-6 text-lg text-muted-foreground sm:text-xl animate-[fadeUp_1s_ease_0.24s_both] leading-relaxed">
            Eliminiamo i colli di bottiglia nei processi aziendali con soluzioni AI verticali, integrando dati e persone in un unico flusso operativo fluido e intelligente.
          </p>

          <div className="mt-10 flex flex-wrap items-center justify-center gap-4 animate-[fadeUp_1.1s_ease_0.36s_both]">
            <a
              href="#come-funziona"
              className="group flex items-center gap-2 rounded-xl bg-primary px-8 py-4 text-sm font-bold text-primary-foreground shadow-xl shadow-primary/20 transition hover:brightness-110 active:scale-95"
            >
              Scopri le soluzioni
              <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </a>
            <a
              href="#contatti"
              className="rounded-xl border border-border bg-card/50 px-8 py-4 text-sm font-bold text-foreground backdrop-blur-sm transition hover:bg-card active:scale-95"
            >
              Richiedi una demo
            </a>
          </div>
        </div>
      </section>

      {/* IL PROBLEMA */}
      <section id="problema" className="py-20 relative overflow-hidden">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-rose-500/30 bg-rose-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-6">
              Il Problema
            </span>
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mb-6">
              Perché molte aziende sono ancora <span className="text-rose-500">frenate?</span>
            </h2>
            <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
              Sistemi che non comunicano, dati intrappolati in fogli Excel e processi documentali manuali che rubano tempo prezioso alle decisioni strategiche.
            </p>
          </div>

          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {[
              {
                icon: <FileTextIcon className="h-8 w-8 text-rose-500" />,
                title: "Data Entry Manuale",
                desc: "Settimane perse ogni anno a trascrivere ordini, DDT e fatture, con un tasso di errore inevitabile."
              },
              {
                icon: <BarChart3 className="h-8 w-8 text-rose-500" />,
                title: "Dati Frammentati",
                desc: "Informazioni disperse tra silos differenti, rendendo impossibile una visione d'insieme in tempo reale."
              },
              {
                icon: <Clock className="h-8 w-8 text-rose-500" />,
                title: "Tempi Morti Operativi",
                desc: "Processi decisionali lenti perché basati su dati obsoleti o report che richiedono ore per essere pronti."
              }
            ].map((card, i) => (
              <div key={i} className="group relative overflow-hidden rounded-2xl border border-border bg-card/60 backdrop-blur p-8 transition-all hover:border-rose-500/30">
                <div className="absolute top-0 right-0 p-4 opacity-5">
                  {card.icon}
                </div>
                <div className="mb-6">{card.icon}</div>
                <h3 className="text-xl font-bold text-foreground mb-4">{card.title}</h3>
                <p className="text-muted-foreground leading-relaxed">{card.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* L'ECOSISTEMA */}
      <section id="ecosistema" className="py-24 relative overflow-hidden">
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full -z-10 dark:block hidden">
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary/5 rounded-full blur-[120px]" />
        </div>

        <div className="mx-auto max-w-6xl">
          <div className="flex flex-col lg:flex-row items-center gap-16">
            <div className="lg:w-1/2">
              <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-6">
                L'Ecosistema Finch-AI
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mb-8 leading-tight">
                Una piattaforma <span className="text-primary tracking-tight">integrata</span> per tutta l'azienda.
              </h2>
              <div className="space-y-6">
                {[
                  {
                    title: "Integrazione Nativa",
                    desc: "Ci colleghiamo al tuo ERP, CRM e MES esistente tramite API e connettori sicuri.",
                    icon: <LinkIcon className="h-5 w-5" />
                  },
                  {
                    title: "AI Agentic",
                    desc: "Non semplici chatbot, ma agenti AI capaci di eseguire task complessi autonomamente.",
                    icon: <Sparkles className="h-5 w-5" />
                  },
                  {
                    title: "Security & Privacy",
                    desc: "Dati crittografati e processi conformi alle normative più stringenti.",
                    icon: <ShieldCheck className="h-5 w-5" />
                  }
                ].map((item, i) => (
                  <div key={i} className="flex gap-4 group cursor-default">
                    <div className="flex-shrink-0 h-10 w-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                      {item.icon}
                    </div>
                    <div>
                      <h3 className="font-bold text-foreground text-lg mb-1">{item.title}</h3>
                      <p className="text-muted-foreground text-sm leading-relaxed">{item.desc}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="lg:w-1/2 relative">
              <div className="relative z-10 rounded-3xl border border-border bg-card/80 backdrop-blur-sm p-4 shadow-2xl">
                <div className="rounded-2xl bg-muted/30 p-8">
                  <div className="grid grid-cols-2 gap-4">
                    <div className="bg-primary/5 border border-primary/10 rounded-xl p-4 flex flex-col justify-center items-center text-center">
                      <FileTextIcon className="h-8 w-8 text-primary mb-2" />
                      <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Documenti</span>
                    </div>
                    <div className="bg-primary/5 border border-primary/10 rounded-xl p-4 flex flex-col justify-center items-center text-center">
                      <Database className="h-8 w-8 text-primary mb-2" />
                      <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">ERP/MES</span>
                    </div>
                    <div className="bg-primary/5 border border-primary/10 rounded-xl p-4 col-span-2 flex items-center justify-center gap-4">
                      <div className="h-12 w-12 rounded-full bg-primary flex items-center justify-center text-white shrink-0 shadow-lg shadow-primary/20">
                        <Cpu className="h-6 w-6 animate-spin-slow" />
                      </div>
                      <div className="text-left">
                        <span className="block text-xs font-bold uppercase tracking-wider text-primary">Finch-AI Core</span>
                        <span className="text-[10px] text-muted-foreground">Elaborazione neurale in corso...</span>
                      </div>
                    </div>
                    <div className="bg-primary/5 border border-primary/10 rounded-xl p-4 col-span-2 flex justify-between items-center px-6">
                      <span className="text-xs font-medium">Insights</span>
                      <ArrowRight className="h-4 w-4 text-primary" />
                      <span className="text-xs font-medium">Decisioni</span>
                    </div>
                  </div>
                </div>
              </div>
              <div className="absolute -top-10 -right-10 w-40 h-40 bg-primary/20 rounded-full blur-[60px] -z-10" />
              <div className="absolute -bottom-10 -left-10 w-60 h-60 bg-primary/10 rounded-full blur-[80px] -z-10" />
            </div>
          </div>
        </div>
      </section>

      {/* I MODULI */}
      <section id="come-funziona" className="py-20">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary dark:text-purple-300 mb-6">
              I Moduli
            </span>
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mb-6">
              Un ecosistema intelligente, <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-green-800 dark:from-purple-400 dark:to-pink-500">già operativo</span>
            </h2>
            <p className="text-lg sm:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">
              Quattro applicazioni già operative, integrate in un unico ecosistema AI pensato per adattarsi all'identità di ogni azienda.
            </p>
          </div>

          <div className="grid gap-8 lg:grid-cols-2">
            {platformApps.map((app, i) => (
              <div
                key={i}
                className="group relative flex flex-col overflow-hidden rounded-3xl border border-border bg-card/60 p-8 backdrop-blur transition-all duration-300 hover:border-primary/50 hover:shadow-2xl hover:shadow-primary/5"
              >
                <div className="mb-6 flex items-center justify-between">
                  <div className="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-primary/30 bg-primary/10 text-primary">
                    {app.icon}
                  </div>
                  <span className="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-200">
                    {app.status}
                  </span>
                </div>
                <h3 className="mb-3 text-2xl font-bold text-foreground">{app.title}</h3>
                <p className="mb-6 text-muted-foreground leading-relaxed">{app.value}</p>
                <div className="mb-8 flex flex-col gap-3 rounded-2xl bg-muted/40 p-5">
                  <div className="flex items-center gap-2 text-xs">
                    <CheckCircle className="h-4 w-4 text-primary" />
                    <span className="font-semibold text-foreground">Punto di forza:</span>
                    <span className="text-muted-foreground">{app.description}</span>
                  </div>
                  <div className="flex items-center gap-2 text-xs">
                    <ChevronRight className="h-4 w-4 text-primary" />
                    <span className="font-semibold text-foreground">Risultato:</span>
                    <span className="text-muted-foreground font-mono">{app.output}</span>
                  </div>
                </div>
                <a
                  href={app.href}
                  className="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-foreground px-6 py-3 text-sm font-bold text-background transition-all hover:opacity-90 active:scale-95"
                >
                  {app.cta}
                  <ArrowRight className="h-4 w-4" />
                </a>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* PER CHI */}
      <section className="py-20 bg-gradient-to-b from-transparent to-card/50">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary dark:text-blue-300 mb-6">
              Per Chi
            </span>
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mb-6">
              Settori che <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-green-800 dark:from-blue-400 dark:to-cyan-500">Trasformiamo</span>
            </h2>
            <p className="text-lg sm:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">
              Soluzioni verticali ottimizzate per le esigenze specifiche del tuo settore
            </p>
          </div>

          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {[
              {
                title: "Produzione",
                desc: "Efficientamento linee e gestione documentale tecnica.",
                icon: <Database className="h-6 w-6" />,
                tags: ["DDT", "MES", "KPI"]
              },
              {
                title: "Logistica",
                desc: "Automazione smistamento documenti e bolle di carico.",
                icon: <Warehouse className="h-6 w-6" />,
                tags: ["Fatture", "Tracking", "OCR"]
              },
              {
                title: "Finanza",
                desc: "Analisi flussi di cassa e previsioni automatiche.",
                icon: <TrendingUp className="h-6 w-6" />,
                tags: ["Forecast", "P&I", "Analisi"]
              },
              {
                title: "Amministrazione",
                desc: "Eliminazione totale del data entry manuale.",
                icon: <Users className="h-6 w-6" />,
                tags: ["Zero Errori", "Tempo Libero", "Integrazione"]
              }
            ].map((sector, i) => (
              <div key={i} className="group rounded-3xl border border-border bg-card/40 p-8 transition-all hover:bg-card hover:border-primary/20">
                <div className="mb-6 h-12 w-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center transition-transform group-hover:scale-110">
                  {sector.icon}
                </div>
                <h3 className="text-lg font-bold text-foreground mb-3">{sector.title}</h3>
                <p className="text-sm text-muted-foreground mb-6 leading-relaxed">{sector.desc}</p>
                <div className="flex flex-wrap gap-2">
                  {sector.tags.map(tag => (
                    <span key={tag} className="text-[10px] bg-muted px-2 py-1 rounded-md text-muted-foreground font-medium">{tag}</span>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* PERCHÉ FINCH-AI */}
      <section className="py-20 bg-gradient-to-b from-card/50 to-transparent">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary dark:text-cyan-300 mb-6">
              Perché Finch-AI
            </span>
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mb-6">
              Oltre il semplice <span className="text-primary italic">software</span>
            </h2>
            <p className="text-lg sm:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">
              Costruiamo il futuro dell'operatività aziendale basandoci su tre pilastri fondamentali.
            </p>
          </div>

          <div className="grid gap-12 lg:grid-cols-3">
            {[
              {
                title: "Deploy Rapido",
                desc: "I nostri moduli sono pronti all'uso e si integrano nei flussi esistenti in meno di 24 ore.",
                icon: ZapIcon
              },
              {
                title: "Zero Vendor Lock-in",
                desc: "La tua infrastruttura dati rimane tua. Noi forniamo l'intelligenza per gestirla meglio.",
                icon: Layers
              },
              {
                title: "ROI Garantito",
                desc: "Risultati misurabili dalla prima settimana: ore uomo salvate e riduzione drastica degli errori.",
                icon: TrendingUp
              }
            ].map((item, i) => {
              const IconComponent = item.icon;
              return (
                <div
                  key={i}
                  className="relative overflow-hidden rounded-2xl border border-border bg-card/60 backdrop-blur p-6 transition-all hover:border-primary/50 hover:bg-card/80"
                >
                  <div className="mb-4 text-primary">
                    <IconComponent className="h-10 w-10" strokeWidth={1.5} />
                  </div>
                  <h3 className="text-xl font-bold text-foreground mb-3">{item.title}</h3>
                  <p className="text-sm text-muted-foreground leading-relaxed">{item.desc}</p>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* CHI SIAMO */}
      <section id="chi-siamo" className="py-20">
        <div className="mx-auto max-w-5xl">
          <div className="text-center mb-12">
            <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary dark:text-cyan-300">
              Chi Siamo
            </span>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-foreground mt-4">
              I pionieri dell'AI operativa
            </h2>
            <p className="text-lg text-muted-foreground mt-4 max-w-3xl mx-auto">
              Finch-AI nasce per eliminare tempi morti e decisioni al buio: automazione documentale, KPI real-time e insight azionabili per produzione, logistica e finanza.
            </p>
          </div>

          <div className="grid gap-6 lg:grid-cols-3">
            <div className="rounded-2xl border border-border bg-card/60 p-6 backdrop-blur">
              <h3 className="text-xl font-semibold text-foreground mb-3">Missione</h3>
              <p className="text-sm text-muted-foreground">
                Portiamo AI operativa nelle linee produttive e negli uffici: meno data entry, più decisioni basate su numeri, con integrazione nativa a ERP/CRM/MES.
              </p>
            </div>
            <div className="rounded-2xl border border-border bg-card/60 p-6 backdrop-blur">
              <h3 className="text-xl font-semibold text-foreground mb-3">Modularità</h3>
              <p className="text-sm text-muted-foreground">
                Il nostro ecosistema intelligente nasce già operativo, con moduli pronti all'uso che si adattano perfettamente all'identità di ogni azienda.
              </p>
            </div>
            <div className="rounded-2xl border border-border bg-card/60 p-6 backdrop-blur">
              <h3 className="text-xl font-semibold text-foreground mb-3">Tecnologia</h3>
              <p className="text-sm text-muted-foreground">
                Utilizziamo modelli di AI allo stato dell'arte (LLM, Vision, Forecasting) gestiti tramite un'infrastruttura sicura, veloce e scalabile.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* AREA CLIENTI */}
      <section id="area-clienti" className="py-20">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-12">
            <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary dark:text-cyan-300">
              Area Clienti
            </span>
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mt-4">
              Pronto a <span className="text-primary italic">semplificare?</span>
            </h2>
            <p className="text-lg md:text-xl text-muted-foreground mt-6 max-w-2xl mx-auto">
              Ottieni ora l'accesso alla piattaforma Finch-AI e inizia a trasformare i tuoi dati in decisioni di valore in meno di 24 ore.
            </p>
            <div className="mt-10 flex flex-wrap justify-center gap-4">
              <a
                href="/area-clienti"
                className="group flex items-center gap-2 rounded-xl bg-foreground px-10 py-4 text-sm font-bold text-background transition-all hover:opacity-90 active:scale-95"
              >
                Log-in Area Clienti
                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
              </a>
              <a
                href="#contatti"
                className="rounded-xl border border-border bg-card/50 px-10 py-4 text-sm font-bold text-foreground backdrop-blur-sm transition hover:bg-card active:scale-95"
              >
                Richiedi supporto
              </a>
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mt-16 overflow-hidden uppercase tracking-widest text-[10px] font-bold text-muted-foreground/40 text-center">
            <div className="p-4 border border-border/30 rounded-xl">Document AI</div>
            <div className="p-4 border border-border/30 rounded-xl">Ops Optimization</div>
            <div className="p-4 border border-border/30 rounded-xl">Finance AI</div>
            <div className="p-4 border border-border/30 rounded-xl">Data Strategy</div>
          </div>
        </div>
      </section>

      {/* FORM E CONTATTI */}
      <section id="contatti" className="py-20">
        <div className="mx-auto max-w-6xl">
          <div className="grid gap-12 lg:grid-cols-[1fr,450px]">
            <div id="contact-form">
              <span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-6">
                Contatti
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-foreground mb-8">
                Richiedi una <span className="text-primary underline decoration-primary/20">demo personalizzata</span>
              </h2>
              <p className="text-lg text-muted-foreground mb-12 leading-relaxed">
                Raccontaci le tue sfide operative. Verrai contattato da un nostro esperto per una sessione di analisi gratuita sul potenziale dell'AI nella tua azienda.
              </p>

              <div className="grid gap-6 sm:grid-cols-2">
                {[
                  {
                    icon: <Target className="h-6 w-6" />,
                    title: "Analisi Mockup",
                    desc: "Ricevi una simulazione reale sui tuoi dati."
                  },
                  {
                    icon: <Monitor className="h-6 w-6" />,
                    title: "Demo Live",
                    desc: "Vedi la piattaforma in azione dashboard alla mano."
                  }
                ].map((feature, i) => (
                  <div key={i} className="flex gap-4 p-4 rounded-2xl bg-card border border-border">
                    <div className="flex-shrink-0 h-10 w-10 bg-primary/10 text-primary flex items-center justify-center rounded-lg">
                      {feature.icon}
                    </div>
                    <div>
                      <h4 className="font-bold text-foreground">{feature.title}</h4>
                      <p className="text-xs text-muted-foreground mt-1">{feature.desc}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <ContactForm />
          </div>
        </div>
      </section>

      {/* CSS keyframes (inline for extra punch) */}
      <style>{`
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeUp {
          from { opacity: 0; transform: translate3d(0, 20px, 0); }
          to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
        @keyframes spin-slow {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
          animation: spin-slow 12s linear infinite;
        }
      `}</style>
    </Layout>
  );
}
