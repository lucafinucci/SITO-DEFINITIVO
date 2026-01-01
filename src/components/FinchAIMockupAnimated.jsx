import { useEffect, useRef, useState } from "react";
import ContactForm from "./ContactForm";
import { 
  FileStack, Network, TrendingDown, Clock, DollarSign, Target,
  Factory, Truck, Briefcase, ShoppingCart, Zap, Rocket, Unlock, TrendingUp,
  Eye, FileText, Settings, Wallet, Package, BarChart3, MessageSquare, Globe,
  FileText as FileTextIcon, LineChart, Wallet as WalletIcon, Warehouse
} from "lucide-react";

export default function FinchAIMockupAnimated() {
  const canvasRef = useRef(null);
  const [activeSection, setActiveSection] = useState("hero");
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const formatWhatsappLink = (phone) => `https://wa.me/${phone.replace(/\D+/g, "")}`;

  // Scroll spy for active section
  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setActiveSection(entry.target.id || "hero");
          }
        });
      },
      { threshold: 0.3, rootMargin: "-100px 0px -50% 0px" }
    );

    const sections = document.querySelectorAll("section[id]");
    sections.forEach((section) => observer.observe(section));

    return () => {
      sections.forEach((section) => observer.unobserve(section));
    };
  }, []);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext("2d", { alpha: true });
    let w = (canvas.width = window.innerWidth);
    let h = (canvas.height = window.innerHeight);

    // Handle resize
    const onResize = () => {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
    };
    window.addEventListener("resize", onResize);

    // Particles
    const PARTICLES = Math.min(90, Math.floor((w * h) / 18000)); // scale with viewport
    const MAX_SPEED = 0.4;
    const LINK_DIST = Math.min(180, Math.max(110, Math.min(w, h) * 0.22));

    const rnd = (min, max) => Math.random() * (max - min) + min;

    const nodes = Array.from({ length: PARTICLES }).map(() => ({
      x: rnd(0, w),
      y: rnd(0, h),
      vx: rnd(-MAX_SPEED, MAX_SPEED),
      vy: rnd(-MAX_SPEED, MAX_SPEED),
      r: rnd(0.6, 1.8),
    }));

    let rafId;
    const gradientStroke = () => {
      const g = ctx.createLinearGradient(0, 0, w, h);
      g.addColorStop(0, "rgba(0,224,255,0.85)"); // cyan
      g.addColorStop(1, "rgba(59,130,246,0.85)"); // blue-500
      return g;
    };

    const draw = () => {
      ctx.clearRect(0, 0, w, h);

      // subtle dark veil
      ctx.fillStyle = "rgba(7,12,22,0.75)";
      ctx.fillRect(0, 0, w, h);

      // radial glow
      const rg = ctx.createRadialGradient(w * 0.5, h * 0.3, 0, w * 0.5, h * 0.3, Math.max(w, h) * 0.7);
      rg.addColorStop(0, "rgba(23,162,255,0.10)");
      rg.addColorStop(1, "rgba(0,0,0,0)");
      ctx.fillStyle = rg;
      ctx.fillRect(0, 0, w, h);

      // update & draw nodes
      ctx.globalCompositeOperation = "lighter";
      for (let i = 0; i < nodes.length; i++) {
        const n = nodes[i];
        n.x += n.vx;
        n.y += n.vy;

        // bounce
        if (n.x < 0 || n.x > w) n.vx *= -1;
        if (n.y < 0 || n.y > h) n.vy *= -1;

        // node point
        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
        ctx.fillStyle = "rgba(56,189,248,0.65)"; // cyan-400
        ctx.fill();
      }

      // links
      ctx.lineWidth = 0.7;
      ctx.strokeStyle = gradientStroke();
      for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
          const dx = nodes[i].x - nodes[j].x;
          const dy = nodes[i].y - nodes[j].y;
          const dist = Math.hypot(dx, dy);
          if (dist < LINK_DIST) {
            const alpha = 1 - dist / LINK_DIST;
            ctx.globalAlpha = alpha * 0.6;
            ctx.beginPath();
            ctx.moveTo(nodes[i].x, nodes[i].y);
            ctx.lineTo(nodes[j].x, nodes[j].y);
            ctx.stroke();
          }
        }
      }
      ctx.globalAlpha = 1;

      rafId = requestAnimationFrame(draw);
    };

    draw();
    return () => {
      cancelAnimationFrame(rafId);
      window.removeEventListener("resize", onResize);
    };
  }, []);

  const navItems = [
    { id: "hero", label: "Finch-AI Platform" },
    { id: "come-funziona", label: "Soluzioni" },
    { id: "contatti", label: "Demo" },
    {
      id: "area-clienti",
      label: "Area Clienti",
      href: import.meta.env.VITE_AREA_CLIENTI_URL || "/area-clienti/login.php",
    },
  ];

  const platformApps = [
    {
      title: "Finch-AI Document Intelligence",
      value: "Automazione documentale basata su AI per ridurre errori e tempi operativi.",
      description: "Configurazione autonoma assistita dall'AI.",
      output: "Dati documentali strutturati e verificati",
      status: "Operativo",
      cta: "Scopri come funziona",
      icon: <FileTextIcon className="h-6 w-6" strokeWidth={1.5} />,
    },
    {
      title: "Finch-AI Production Intelligence",
      value: "Pianificazione e supporto decisionale potenziati dall'AI.",
      description: "Configurazione e gestione assistite dall'AI in tutte le fasi.",
      output: "Pianificazione operativa e supporto decisionale in produzione",
      status: "Operativo",
      cta: "Vedi la produzione",
      icon: <LineChart className="h-6 w-6" strokeWidth={1.5} />,
    },
    {
      title: "Finch-AI Finance Intelligence",
      value: "Analisi automatica di costi, ricavi e cash flow.",
      description: "Previsioni economico-finanziarie per guidare le decisioni.",
      output: "Forecast e indicatori economici intelligenti",
      status: "Operativo",
      cta: "Esplora la finanza",
      icon: <WalletIcon className="h-6 w-6" strokeWidth={1.5} />,
    },
    {
      title: "Finch-AI Warehouse Intelligence",
      value: "Gestione integrata di magazzino, ordini e offerte.",
      description: "Decisioni e operatività potenziate dall'AI.",
      output: "Magazzino, ordini e offerte sincronizzati in un unico flusso",
      status: "Operativo",
      cta: "Scopri il magazzino",
      icon: <Warehouse className="h-6 w-6" strokeWidth={1.5} />,
    },
  ];

  return (
    <>
      {/* Sticky Navigation */}
      <nav className="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/50 bg-slate-900/80 backdrop-blur-xl">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-28 sm:h-32 lg:h-36 items-center justify-between">
            {/* Logo - OCCUPANTE MASSIMO */}
            <a href="#hero" className="group block w-full max-w-xs">
              <div className="relative w-full">
                {/* Glow effect espanso */}
                <div className="absolute inset-0 rounded-3xl bg-gradient-to-br from-cyan-400 to-blue-500 opacity-50 blur-[60px] transition-all group-hover:opacity-70 group-hover:blur-[80px]" />
                <div className="absolute inset-0 rounded-3xl bg-cyan-400 opacity-30 blur-3xl animate-pulse" />

                {/* Logo container FULL WIDTH */}
                <div className="relative flex h-24 w-full sm:h-28 lg:h-32 items-center justify-center rounded-3xl bg-white shadow-[0_0_60px_rgba(34,211,238,0.6),0_0_120px_rgba(34,211,238,0.4),0_20px_50px_rgba(0,0,0,0.3)] transition-all duration-300 group-hover:shadow-[0_0_80px_rgba(34,211,238,0.8),0_0_150px_rgba(34,211,238,0.5)] group-hover:scale-[1.02] overflow-hidden border-4 border-cyan-400/50">
                  <img
                    src="/assets/images/LOGO.png"
                    alt="Finch-AI"
                    className="h-20 sm:h-24 lg:h-28 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                  />

                  {/* Ring pulsante multiplo */}
                  <div className="absolute inset-0 rounded-3xl border-2 border-cyan-400 opacity-0 group-hover:opacity-100 animate-ping" />
                  <div className="absolute inset-0 rounded-3xl border border-cyan-300 opacity-0 group-hover:opacity-60" style={{ animationDelay: '0.1s' }} />
                </div>

                {/* Riflessione sotto */}
                <div className="absolute -bottom-2 left-0 right-0 h-8 bg-gradient-to-b from-cyan-400/20 to-transparent blur-xl opacity-60" />
              </div>
            </a>

            {/* Desktop Nav Links */}
            <div className="hidden md:flex items-center gap-1">
              {navItems.map((item) => (
                <a
                  key={item.id}
                  href={item.href || `#${item.id}`}
                  className={`relative px-4 py-2 text-sm font-medium transition-colors ${
                    activeSection === item.id
                      ? "text-cyan-300"
                      : "text-slate-400 hover:text-slate-200"
                  }`}
                >
                  {item.label}
                  {activeSection === item.id && (
                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full" />
                  )}
                </a>
              ))}
            </div>

            {/* Desktop CTA Button */}
            <a
              href="#contact-form"
              className="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
            >
              Contattaci
            </a>

            {/* Mobile Menu Button */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-cyan-300 hover:bg-slate-800/50 transition-colors"
              aria-label="Toggle menu"
            >
              <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {mobileMenuOpen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>
          </div>
        </div>

        {/* Mobile Menu Dropdown */}
        {mobileMenuOpen && (
          <div className="md:hidden border-t border-slate-800/50 bg-slate-900/95 backdrop-blur-xl">
            <div className="mx-auto max-w-7xl px-4 py-4 space-y-2">
              {navItems.map((item) => (
                <a
                  key={item.id}
                  href={item.href || `#${item.id}`}
                  onClick={() => setMobileMenuOpen(false)}
                  className={`block px-4 py-3 rounded-lg text-base font-medium transition-all ${
                    activeSection === item.id
                      ? "bg-cyan-500/10 text-cyan-300 border border-cyan-500/30"
                      : "text-slate-400 hover:text-slate-200 hover:bg-slate-800/50"
                  }`}
                >
                  {item.label}
                </a>
              ))}
              <a
                href="#contact-form"
                onClick={() => setMobileMenuOpen(false)}
                className="block mt-4 px-4 py-3 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-center text-base font-semibold text-white shadow-lg shadow-cyan-500/20"
              >
                Contattaci
              </a>
            </div>
          </div>
        )}
      </nav>

      {/* Background canvas */}
      <canvas
        ref={canvasRef}
        className="fixed inset-0 -z-10 h-full w-full"
        aria-hidden="true"
      />

      {/* Additional aesthetic layers */}
      <div className="pointer-events-none fixed inset-0 -z-10">
        {/* diagonal gradient veil */}
        <div className="absolute inset-0 opacity-40 [background:linear-gradient(120deg,#0b1220_20%,#0a1a2b_60%,#03101f_85%)]" />
        {/* soft scanlines */}
        <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[length:100%_28px] mix-blend-overlay" />
      </div>

      {/* Page container */}
      <main className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-slate-200 selection:bg-cyan-500/30 selection:text-white">
        {/* HERO */}
        <section id="hero" className="pt-40 sm:pt-48 lg:pt-56 pb-12">
          <div className="mx-auto max-w-3xl text-center">
            <span className="inline-flex items-center gap-2 rounded-full border border-slate-700/60 bg-slate-900/40 px-3 py-1 text-xs uppercase tracking-wider text-cyan-300/80 animate-[fadeIn_0.8s_ease_0.05s_both]">
              <span className="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-ping" />
              AI per Operazioni Reali
            </span>

            <h1 className="mt-5 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl animate-[fadeUp_0.9s_ease_0.12s_both]">
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 via-sky-400 to-blue-500">
                Automazione Intelligente per<br />
                Decisioni di Valore
              </span>
            </h1>

            <p className="mt-5 text-lg text-slate-300/90 animate-[fadeUp_0.9s_ease_0.2s_both]">
              Aiutiamo la tua PMI a usare l'Intelligenza Artificiale in modo semplice, per arrivare a risultati concreti senza complessità tecniche.
            </p>

            <div className="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row animate-[fadeUp_0.9s_ease_0.28s_both] w-full sm:w-auto px-4 sm:px-0">
              <a
                href="#contatti"
                className="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 sm:px-5 sm:py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 w-full sm:w-auto text-center min-h-[48px]"
              >
                Avvia la tua trasformazione AI
                <svg className="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </a>
              <a
                href="#come-funziona"
                className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700/70 bg-slate-900/40 px-6 py-4 sm:px-5 sm:py-3 font-semibold text-slate-200 hover:border-slate-500/80 hover:bg-slate-900/60 w-full sm:w-auto text-center min-h-[48px]"
              >
                Guarda come funziona
              </a>
            </div>

            {/* Quick Stats Bar */}
            <div className="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-6 max-w-4xl mx-auto">
              {[
                { value: "70%", label: "Riduzione tempi" },
                { value: "+1000", label: "Doc/giorno" },
                { value: "99.2%", label: "Accuratezza" },
                { value: "4-8 sett", label: "Deploy" }
              ].map((stat, i) => (
                <div key={i} className="text-center p-4 rounded-xl bg-slate-900/40 border border-slate-700/50 backdrop-blur">
                  <div className="text-3xl font-bold text-cyan-400 mb-1">{stat.value}</div>
                  <div className="text-xs text-slate-400 uppercase tracking-wide">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* SEZIONE 1: IL PROBLEMA */}
        <section className="py-20">
          <div className="mx-auto max-w-5xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-300 mb-6">
                Il Problema
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Il Caos che <span className="bg-clip-text text-transparent bg-gradient-to-r from-red-400 to-orange-500">Rallenta la Tua Azienda</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Dati cartacei non integrati, decisioni basate su informazioni frammentate
              </p>
            </div>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {[
                {
                  icon: FileStack,
                  title: "Documenti Caotici",
                  desc: "DDT, fatture e ordini gestiti manualmente. Ore perse in data entry, errori frequenti, informazioni che si perdono tra email e fogli di calcolo."
                },
                {
                  icon: Network,
                  title: "Sistemi Isolati",
                  desc: "ERP, CRM, gestionale produzione non comunicano. Dati duplicati, sincronizzazione manuale, visibilità zero sull'insieme."
                },
                {
                  icon: TrendingDown,
                  title: "Decisioni al Buio",
                  desc: "Report obsoleti, KPI non aggiornati, analisi che arrivano troppo tardi. Opportunità perse e problemi scoperti in ritardo."
                },
                {
                  icon: Clock,
                  title: "Tempo Sprecato",
                  desc: "Il tuo team passa ore a cercare informazioni, verificare dati e creare report invece di concentrarsi su attività strategiche."
                },
                {
                  icon: DollarSign,
                  title: "Costi Nascosti",
                  desc: "Inefficienze operative, errori di processo, opportunità di ottimizzazione non colte. Il ROI potenziale che sta sfuggendo."
                },
                {
                  icon: Target,
                  title: "Controllo Limitato",
                  desc: "Manca una visione unificata di produzione, finanza e operations. Impossibile prendere decisioni data-driven in tempo reale."
                }
              ].map((problem, i) => {
                const IconComponent = problem.icon;
                return (
                <div
                  key={i}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-6 transition-all hover:border-red-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_30px_rgba(239,68,68,0.2)]"
                >
                  <div className="mb-4 text-red-400">
                    <IconComponent className="h-10 w-10" strokeWidth={1.5} />
                  </div>
                  <h3 className="text-xl font-bold text-white mb-3 group-hover:text-red-300 transition-colors">
                    {problem.title}
                  </h3>
                  <p className="text-sm text-slate-400 leading-relaxed group-hover:text-slate-300 transition-colors">
                    {problem.desc}
                  </p>
                </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* ECOSISTEMA FINCH-AI */}
        <section className="py-16">
          <div className="mx-auto max-w-5xl">
            <div className="relative overflow-hidden rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/80 via-slate-900/60 to-slate-900/30 p-8 sm:p-10 backdrop-blur">
              <div className="absolute -inset-px opacity-0 sm:opacity-100">
                <div className="absolute inset-0 bg-[radial-gradient(900px_300px_at_30%_0%,rgba(56,189,248,0.12),transparent)]" />
                <div className="absolute inset-0 bg-[radial-gradient(900px_300px_at_70%_100%,rgba(59,130,246,0.08),transparent)]" />
              </div>
              <div className="relative space-y-6">
                <div className="inline-flex items-center gap-2 rounded-full border border-cyan-500/40 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-200">
                  <Globe className="h-4 w-4" />
                  Ecosistema Finch-AI
                </div>
                <h2 className="text-3xl sm:text-4xl font-extrabold text-white">
                  L'intelligenza artificiale che cresce con la tua azienda.
                </h2>
                <p className="text-lg text-slate-300/90 max-w-3xl leading-relaxed">
                  Ogni azienda è unica — e ha bisogno della propria intelligenza artificiale. Finch-AI costruisce un ecosistema modulare, integrato e personalizzato che si adatta all'identità, ai processi e agli obiettivi di ogni impresa.
                </p>
                <div className="grid gap-4 sm:grid-cols-2">
                  {[
                    {
                      title: "AI su misura, non generica",
                      desc: "Moduli personalizzati sulle tue operation, dati e vincoli. Crescono con il business, non il contrario."
                    },
                    {
                      title: "Sistema, non solo piattaforma",
                      desc: "I moduli collaborano tra loro: documenti, produzione, finanza e magazzino si parlano in tempo reale."
                    },
                    {
                      title: "Ecosistema modulare",
                      desc: "Attiva i moduli che servono ora e aggiungi gli altri quando il business evolve, senza ripartire da zero."
                    }
                  ].map((item, i) => (
                    <div
                      key={i}
                      className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-5 backdrop-blur"
                    >
                      <h3 className="text-lg font-semibold text-white mb-2">{item.title}</h3>
                      <p className="text-sm text-slate-300 leading-relaxed">{item.desc}</p>
                    </div>
                  ))}
                </div>
                <p className="text-sm text-slate-400">
                  Non vendiamo una piattaforma: offriamo un sistema intelligente completo, fatto di moduli che collaborano tra loro e crescono insieme alla tua azienda.
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* SEZIONE 3: I MODULI CON BENEFICI */}
        <section id="come-funziona" className="py-20">
          <div className="mx-auto max-w-6xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-purple-500/30 bg-purple-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-purple-300 mb-6">
                I Moduli
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Un ecosistema intelligente, <span className="bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">già operativo</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Quattro applicazioni già operative, integrate in un unico ecosistema AI pensato per adattarsi all'identità di ogni azienda.
              </p>
            </div>

            {/* Moduli operativi */}
            <div className="grid gap-6 sm:grid-cols-2">
              {platformApps.map((app) => (
                <div
                  key={app.title}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/50 p-6 backdrop-blur transition hover:border-cyan-500/50 hover:bg-slate-900/70 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                >
                  <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="h-full w-full bg-[radial-gradient(600px_240px_at_var(--x,50%)_0,rgba(34,211,238,0.12),transparent)]" />
                  </div>
                  <div className="relative flex h-full flex-col gap-4">
                    <div className="flex items-start justify-between gap-4">
                      <div className="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-cyan-500/30 bg-cyan-500/10 text-cyan-300">
                        {app.icon}
                      </div>
                      <span className="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-200">
                        {app.status}
                      </span>
                    </div>
                    <div>
                      <h3 className="text-xl font-semibold text-white">{app.title}</h3>
                      <p className="mt-2 text-sm font-semibold text-slate-200">
                        {app.value}
                      </p>
                      <p className="mt-2 text-sm text-slate-300/90">
                        {app.description}
                      </p>
                    </div>
                    <div className="mt-auto space-y-3">
                      <p className="text-sm font-semibold text-cyan-300">Output: {app.output}</p>
                      <a
                        href="#contatti"
                        className="inline-flex items-center gap-2 rounded-lg border border-slate-700/70 bg-slate-900/40 px-3 py-2 text-sm font-semibold text-slate-200 transition hover:border-cyan-500/60 hover:text-cyan-200"
                      >
                        {app.cta}
                        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            <p className="mt-10 text-center text-sm text-slate-400">
              Finch-AI non è una collezione di strumenti, ma un ecosistema che cresce insieme alla tua azienda, partendo da ciò che conta davvero.
            </p>
          </div>
        </section>

        {/* SEZIONE 4: PER CHI */}
        <section className="py-20 bg-gradient-to-b from-transparent to-slate-900/50">
          <div className="mx-auto max-w-6xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-300 mb-6">
                Per Chi
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Settori che <span className="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-cyan-500">Trasformiamo</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Soluzioni verticali ottimizzate per le esigenze specifiche del tuo settore
              </p>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
              {[
                {
                  sector: "Manufacturing & Produzione",
                  icon: Factory,
                  challenges: "Gestione DDT, tracciabilità lotti, integrazione MES/ERP, monitoraggio OEE",
                  solutions: [
                    "Automazione completa ciclo DDT in/out",
                    "Tracciabilità real-time materiali e WIP",
                    "KPI produzione live su dashboard",
                    "Integrazione bidirezionale con ERP"
                  ],
                  results: "90% riduzione tempo amministrativo, 99.5% accuratezza dati"
                },
                {
                  sector: "Logistica & Distribuzione",
                  icon: Truck,
                  challenges: "Volume documenti elevato, multi-vettore, gestione resi, fatturazione automatica",
                  solutions: [
                    "OCR multi-formato per ogni vettore",
                    "Matching automatico ordine-DDT-fattura",
                    "Gestione eccezioni e resi intelligente",
                    "Dashboard spedizioni real-time"
                  ],
                  results: "Elaborazione 10x più veloce, zero errori di trascrizione"
                },
                {
                  sector: "Servizi & Consulenza",
                  icon: Briefcase,
                  challenges: "Timesheet, fatturazione progetti, controllo margini, reportistica clienti",
                  solutions: [
                    "Automazione timesheet e approval",
                    "Fatturazione automatica da milestone",
                    "Analisi marginalità per progetto/cliente",
                    "Report personalizzati automatici"
                  ],
                  results: "Chiusura mensile in 2 giorni invece di 10"
                },
                {
                  sector: "Retail & E-commerce",
                  icon: ShoppingCart,
                  challenges: "Gestione ordini multi-canale, inventario, fornitori, riconciliazione pagamenti",
                  solutions: [
                    "Unificazione ordini da tutti i canali",
                    "Sincronizzazione inventario real-time",
                    "Gestione automatica ordini fornitori",
                    "Riconciliazione pagamenti/marketplace"
                  ],
                  results: "100% visibilità stock, zero rotture di stock critiche"
                }
              ].map((item, i) => {
                const IconComponent = item.icon;
                return (
                <div
                  key={i}
                  className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 transition-all hover:border-blue-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_40px_rgba(59,130,246,0.2)]"
                >
                  <div className="mb-4 text-blue-400">
                    <IconComponent className="h-12 w-12" strokeWidth={1.5} />
                  </div>
                  <h3 className="text-2xl font-bold text-white mb-4 group-hover:text-blue-300 transition-colors">
                    {item.sector}
                  </h3>

                  <div className="mb-6">
                    <div className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Sfide comuni</div>
                    <p className="text-slate-300/90 leading-relaxed">{item.challenges}</p>
                  </div>

                  <div className="mb-6">
                    <div className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-3">Come ti aiutiamo</div>
                    <div className="space-y-2">
                      {item.solutions.map((solution, j) => (
                        <div key={j} className="flex items-start gap-2">
                          <svg className="h-5 w-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <span className="text-sm text-slate-300">{solution}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="pt-4 border-t border-slate-700/50">
                    <div className="text-sm font-semibold text-blue-400">{item.results}</div>
                  </div>
                </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* SEZIONE: PERCHÉ FINCH-AI */}
        <section className="py-20 bg-gradient-to-b from-slate-900/50 to-transparent">
          <div className="mx-auto max-w-6xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
                Perché Finch-AI
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Numeri che <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-blue-500">Parlano Chiaro</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Non solo promesse: risultati misurabili sin dal primo giorno
              </p>
            </div>

            {/* Metriche Principali */}
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
              {[
                {
                  metric: "70%",
                  label: "Riduzione tempo elaborazione documenti",
                  icon: Zap,
                  desc: "Da ore a minuti per processare DDT, fatture e ordini"
                },
                {
                  metric: "+1000",
                  label: "Documenti/giorno analizzati automaticamente",
                  icon: FileText,
                  desc: "Capacità di elaborazione scalabile senza limiti"
                },
                {
                  metric: "99.2%",
                  label: "Accuratezza estrazione dati",
                  icon: Target,
                  desc: "OCR con validazione intelligente domain-specific"
                },
                {
                  metric: "24/7",
                  label: "Monitoraggio operativo continuo",
                  icon: Eye,
                  desc: "Alert real-time su anomalie e opportunità"
                }
              ].map((item, i) => {
                const IconComponent = item.icon;
                return (
                <div
                  key={i}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-6 transition-all hover:border-cyan-500/50 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                >
                  <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="h-full w-full bg-[radial-gradient(400px_200px_at_50%_0,rgba(34,211,238,0.1),transparent)]" />
                  </div>

                  <div className="relative">
                    <div className="mb-4 text-cyan-400">
                      <IconComponent className="h-10 w-10" strokeWidth={1.5} />
                    </div>
                    <div className="text-4xl font-bold text-cyan-400 mb-2">{item.metric}</div>
                    <div className="text-sm font-semibold text-white mb-2">{item.label}</div>
                    <div className="text-xs text-slate-400 leading-relaxed">{item.desc}</div>
                  </div>
                </div>
                );
              })}
            </div>

            {/* Vantaggi Competitivi */}
            <div className="grid md:grid-cols-3 gap-6">
              {[
                {
                  title: "Deploy Rapido",
                  desc: "Operativi in 4-8 settimane, non mesi. Integrazione plug-and-play con i tuoi sistemi esistenti.",
                  icon: Rocket
                },
                {
                  title: "Zero Vendor Lock-in",
                  desc: "Dati sempre tuoi, esportabili, API aperte. Integrazione con qualsiasi ERP, CRM o gestionale.",
                  icon: Unlock
                },
                {
                  title: "ROI Garantito",
                  desc: "Break-even medio in 6 mesi. Calcolo ROI personalizzato prima di partire. Nessun costo nascosto.",
                  icon: TrendingUp
                }
              ].map((item, i) => {
                const IconComponent = item.icon;
                return (
                <div
                  key={i}
                  className="relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-6 transition-all hover:border-blue-500/50 hover:bg-slate-900/80"
                >
                  <div className="mb-4 text-blue-400">
                    <IconComponent className="h-10 w-10" strokeWidth={1.5} />
                  </div>
                  <h3 className="text-xl font-bold text-white mb-3">{item.title}</h3>
                  <p className="text-sm text-slate-300/90 leading-relaxed">{item.desc}</p>
                </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* DASHBOARD PREVIEW - Rimossa, sostituita da sezioni 4 e 5 sopra */}
        <section className="py-16 hidden">
          <div className="text-center mb-12">
            <h2 className="text-3xl sm:text-4xl font-extrabold text-white mb-4">
              Dashboard in <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">Tempo Reale</span>
            </h2>
            <p className="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto px-4">
              Visualizza KPI, monitoraggio produzione e analisi predittiva su un'interfaccia moderna e intuitiva
            </p>
          </div>

          {/* Mobile: Horizontal Scroll, Desktop: Grid */}
          <div className="lg:grid lg:gap-6 lg:grid-cols-2 flex lg:flex-none gap-4 overflow-x-auto snap-x snap-mandatory scrollbar-hide pb-4 -mx-4 px-4 lg:mx-0 lg:px-0 lg:overflow-visible">
            {/* Chart Card 1 - Line Chart */}
            <div className="group relative rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900/90 to-slate-900/50 p-4 sm:p-6 backdrop-blur shadow-xl shadow-slate-900/50 transition-all hover:shadow-2xl hover:shadow-cyan-500/10 hover:border-cyan-500/30 min-w-[85vw] sm:min-w-0 snap-center lg:snap-align-none">
              <div className="absolute inset-0 rounded-2xl bg-gradient-to-br from-cyan-500/5 to-blue-500/5 opacity-0 transition-opacity group-hover:opacity-100" />

              <div className="relative">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                  <div>
                    <h3 className="text-base sm:text-lg font-semibold text-white">Produttività Operativa</h3>
                    <p className="text-xs sm:text-sm text-slate-400">Trend ultimi 30 giorni</p>
                  </div>
                  <div className="flex items-center gap-2 rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs sm:text-sm font-semibold text-emerald-400 self-start sm:self-auto">
                    <svg className="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    +24%
                  </div>
                </div>

                {/* Mock Line Chart */}
                <div className="relative h-48 flex items-end gap-2">
                  {[45, 52, 48, 61, 58, 72, 68, 75, 71, 82, 78, 85, 92, 88, 95].map((height, i) => (
                    <div key={i} className="flex-1 flex flex-col justify-end">
                      <div
                        className="w-full rounded-t bg-gradient-to-t from-cyan-500/80 to-blue-500/80 shadow-lg shadow-cyan-500/20 transition-all duration-300 group-hover:from-cyan-400 group-hover:to-blue-400"
                        style={{ height: `${height}%` }}
                      />
                    </div>
                  ))}
                </div>

                <div className="mt-4 flex items-center justify-between text-xs text-slate-500">
                  <span>1 Gen</span>
                  <span>15 Gen</span>
                  <span>30 Gen</span>
                </div>
              </div>
            </div>

            {/* Chart Card 2 - Circular Progress */}
            <div className="group relative rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900/90 to-slate-900/50 p-4 sm:p-6 backdrop-blur shadow-xl shadow-slate-900/50 transition-all hover:shadow-2xl hover:shadow-blue-500/10 hover:border-blue-500/30 min-w-[85vw] sm:min-w-0 snap-center lg:snap-align-none">
              <div className="absolute inset-0 rounded-2xl bg-gradient-to-br from-blue-500/5 to-cyan-500/5 opacity-0 transition-opacity group-hover:opacity-100" />

              <div className="relative">
                <div className="mb-6">
                  <h3 className="text-base sm:text-lg font-semibold text-white">KPI Globali</h3>
                  <p className="text-xs sm:text-sm text-slate-400">Performance real-time</p>
                </div>

                <div className="grid grid-cols-2 gap-3 sm:gap-4">
                  {[
                    { label: "OEE", value: 87, color: "cyan" },
                    { label: "Qualità", value: 94, color: "emerald" },
                    { label: "Disponibilità", value: 92, color: "blue" },
                    { label: "Performance", value: 89, color: "violet" },
                  ].map((kpi, i) => (
                    <div key={i} className="relative flex flex-col items-center justify-center p-3 sm:p-4 rounded-xl bg-slate-800/50 border border-slate-700/40">
                      {/* Circular Progress */}
                      <svg className="h-16 w-16 sm:h-20 sm:w-20 -rotate-90" viewBox="0 0 36 36">
                        <path
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="rgb(51 65 85)"
                          strokeWidth="2"
                        />
                        <path
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke={`rgb(${kpi.color === 'cyan' ? '34 211 238' : kpi.color === 'emerald' ? '16 185 129' : kpi.color === 'blue' ? '59 130 246' : '139 92 246'})`}
                          strokeWidth="2"
                          strokeDasharray={`${kpi.value}, 100`}
                          className="transition-all duration-1000"
                        />
                      </svg>
                      <div className="absolute inset-0 flex flex-col items-center justify-center">
                        <span className="text-xl sm:text-2xl font-bold text-white">{kpi.value}%</span>
                      </div>
                      <span className="mt-2 text-xs font-medium text-slate-400">{kpi.label}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Stats Bar - Mobile Horizontal Scroll */}
          <div className="mt-8 flex sm:grid gap-4 sm:grid-cols-3 overflow-x-auto snap-x snap-mandatory scrollbar-hide pb-4 -mx-4 px-4 sm:mx-0 sm:px-0 sm:overflow-visible">
            {[
              { icon: BarChart3, label: "Reports generati", value: "1.2K", trend: "+12%" },
              { icon: Zap, label: "Automazioni attive", value: "47", trend: "+8%" },
              { icon: Target, label: "Accuracy media", value: "98.5%", trend: "+2.1%" },
            ].map((stat, i) => {
              const IconComponent = stat.icon;
              return (
              <div key={i} className="group relative overflow-hidden rounded-xl border border-slate-700/60 bg-slate-900/60 p-4 backdrop-blur transition-all hover:border-cyan-500/40 hover:shadow-lg hover:shadow-cyan-500/10 min-w-[75vw] sm:min-w-0 snap-center touch-pan-x">
                <div className="flex items-center justify-between">
                  <div className="w-full">
                    <div className="flex items-center gap-2 mb-1">
                      <IconComponent className="h-5 w-5 sm:h-6 sm:w-6 text-cyan-400" strokeWidth={1.5} />
                      <span className="text-xs font-medium text-slate-400">{stat.label}</span>
                    </div>
                    <div className="flex items-baseline gap-2">
                      <span className="text-xl sm:text-2xl font-bold text-white">{stat.value}</span>
                      <span className="text-sm font-semibold text-emerald-400">{stat.trend}</span>
                    </div>
                  </div>
                </div>
              </div>
              );
            })}
          </div>

          {/* Scroll Indicator for Mobile */}
          <div className="mt-4 flex justify-center gap-2 sm:hidden">
            <div className="h-1 w-8 rounded-full bg-cyan-500/30"></div>
            <div className="h-1 w-8 rounded-full bg-slate-700/50"></div>
            <div className="h-1 w-8 rounded-full bg-slate-700/50"></div>
          </div>
        </section>

        {/* SEZIONE CHI SIAMO */}
        <section id="chi-siamo" className="py-20">
          <div className="mx-auto max-w-5xl">
            <div className="text-center mb-12">
              <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300">
                Chi Siamo
              </span>
              <h2 className="text-3xl sm:text-4xl font-extrabold text-white mt-4">
                L'AI che parla il linguaggio dell'industria
              </h2>
              <p className="text-lg text-slate-300/90 mt-4 max-w-3xl mx-auto">
                Finch-AI nasce per eliminare tempi morti e decisioni al buio: automazione documentale, KPI real-time e insight azionabili per produzione, logistica e finanza.
              </p>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
              <div className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-6 backdrop-blur">
                <h3 className="text-xl font-semibold text-white mb-3">Missione</h3>
                <p className="text-sm text-slate-300">
                  Portiamo AI operativa nelle linee produttive e negli uffici: meno data entry, più decisioni basate su numeri, con integrazione nativa a ERP/CRM/MES.
                </p>
              </div>
              <div className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-6 backdrop-blur">
                <h3 className="text-xl font-semibold text-white mb-3">Team</h3>
                <p className="text-sm text-slate-300">
                  Data scientist e ingegneri con esperienza in manufacturing, supply chain e sistemi ERP. Delivery rapido (4–8 settimane) e modelli adattati sui tuoi processi.
                </p>
              </div>
              <div className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-6 backdrop-blur">
                <h3 className="text-xl font-semibold text-white mb-3">Tecnologia &amp; Sicurezza</h3>
                <p className="text-sm text-slate-300">
                  Moduli AI specializzati, OCR avanzato, integrazione API-first. Dati in UE, cifrati in transito e a riposo, privacy by design (GDPR).
                </p>
              </div>
            </div>

            <div className="grid gap-6 sm:grid-cols-3 mt-10">
              <div className="p-5 rounded-2xl bg-slate-900/50 border border-slate-700/50 text-center">
                <div className="text-3xl font-bold text-cyan-400 mb-1">-70%</div>
                <div className="text-sm text-slate-400">Tempo ciclo documenti</div>
              </div>
              <div className="p-5 rounded-2xl bg-slate-900/50 border border-slate-700/50 text-center">
                <div className="text-3xl font-bold text-cyan-400 mb-1">99%</div>
                <div className="text-sm text-slate-400">Accuratezza estrazione dati</div>
              </div>
              <div className="p-5 rounded-2xl bg-slate-900/50 border border-slate-700/50 text-center">
                <div className="text-3xl font-bold text-cyan-400 mb-1">4–8 sett</div>
                <div className="text-sm text-slate-400">Go-live medio</div>
              </div>
            </div>

          </div>
        </section>

        {/* AREA CLIENTI */}
        <section id="area-clienti" className="py-20">
          <div className="mx-auto max-w-6xl">
            <div className="text-center mb-12">
              <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300">
                Area Clienti
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mt-4">
                Scarica le fatture e monitora i costi in sicurezza
              </h2>
              <p className="text-lg text-slate-300/90 mt-4 max-w-3xl mx-auto">
                Accesso riservato con controllo ruoli, audit trail e download delle fatture. Dashboard per costi per pagina, addestramento e utilizzo.
              </p>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
              <div className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-6 backdrop-blur flex flex-col gap-3">
                <div className="inline-flex items-center gap-2 text-emerald-300 text-sm font-semibold">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 4h16v16H4z" strokeWidth="2" />
                    <path d="M9 4v4h6V4" strokeWidth="2" />
                    <path d="M9 12h6" strokeWidth="2" />
                  </svg>
                  Fatture e documenti
                </div>
                <h3 className="text-xl font-bold text-white">Download fatture</h3>
                <p className="text-sm text-slate-300">
                  Scarica fatture e ricevute in PDF, storico completo per periodo, con filtri per progetto e centro di costo.
                </p>
              </div>

              <div className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-6 backdrop-blur flex flex-col gap-3">
                <div className="inline-flex items-center gap-2 text-cyan-300 text-sm font-semibold">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 19h16" strokeWidth="2" />
                    <path d="M7 16l3-8 4 10 3-6" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                    <circle cx="7" cy="16" r="1.3" fill="currentColor" />
                    <circle cx="10" cy="8" r="1.3" fill="currentColor" />
                    <circle cx="14" cy="18" r="1.3" fill="currentColor" />
                    <circle cx="17" cy="12" r="1.3" fill="currentColor" />
                  </svg>
                  Costi per pagina & training
                </div>
                <h3 className="text-xl font-bold text-white">Monitoraggio dettagliato</h3>
                <p className="text-sm text-slate-300">
                  Dashboard di consumo: costi per pagina elaborata, cicli di addestramento, storage modelli e trend temporali.
                </p>
              </div>

              <div className="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-6 backdrop-blur flex flex-col gap-3">
                <div className="inline-flex items-center gap-2 text-amber-300 text-sm font-semibold">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 2l7 4v6c0 5-3 8-7 10-4-2-7-5-7-10V6l7-4z" strokeWidth="2" />
                    <path d="M9 12l2 2 4-4" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                  Accesso sicuro
                </div>
                <h3 className="text-xl font-bold text-white">Ruoli, MFA, audit</h3>
                <p className="text-sm text-slate-300">
                  Login sicuro con MFA, ruoli (Admin/Finance/Viewer), audit trail su download e modifiche, notifiche anomalie.
                </p>
              </div>
            </div>

          </div>
        </section>

        {/* SEZIONE CONTATTI & LEAD GENERATION */}
        <section id="contatti" className="pb-24">
          <div className="mx-auto max-w-6xl">
            <div className="text-center mb-12">
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Inizia la <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-blue-500">Trasformazione</span>
              </h2>
              <p className="text-lg text-slate-300/90 max-w-2xl mx-auto">
                Scopri come Finch-AI può ottimizzare i tuoi processi in 10 minuti. Risposta garantita entro 24h lavorative.
              </p>
            </div>

            <div className="grid lg:grid-cols-2 gap-8 mb-12 items-stretch">
              <ContactForm />

              <div className="space-y-6 h-full flex flex-col">
                <div className="group relative overflow-hidden rounded-3xl border border-emerald-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-6 transition-all hover:border-emerald-500/50 hover:shadow-[0_0_40px_rgba(16,185,129,0.2)] flex-1">
                  <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(16,185,129,0.1),transparent)]" />
                  </div>
                  <div className="relative space-y-3">
                    <div className="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-200">
                      Contatto rapido
                    </div>
                    <h3 className="text-xl font-bold text-white">Preferisci parlare subito?</h3>
                    <p className="text-sm text-slate-300/90">
                      Chiamaci o scrivici su WhatsApp. Dal form possiamo richiamarti all'ora che preferisci.
                    </p>
                    <div className="flex flex-wrap items-center gap-3">
                      {[{ label: "+39 328 717 1587", phone: "+393287171587" }, { label: "+41 76 436 6624", phone: "+41764366624" }, { label: "+39 375 647 5087", phone: "+393756475087" }].map((contact, idx) => (
                        <span key={contact.phone} className="inline-flex items-center gap-2">
                          <svg aria-hidden="true" className="h-4 w-4 text-emerald-400" viewBox="0 0 32 32" fill="currentColor">
                            <path d="M16.02 5.333A10.68 10.68 0 0 0 5.333 16c0 1.874.5 3.704 1.458 5.312l-1.562 5.354 5.52-1.49A10.57 10.57 0 0 0 16 26.667 10.68 10.68 0 0 0 26.667 16 10.68 10.68 0 0 0 16.02 5.333Zm6.26 14.563c-.26.74-1.52 1.407-2.092 1.5-.573.094-1.316.133-2.12-.133-.487-.16-1.113-.364-1.918-.703-3.372-1.46-5.563-4.87-5.732-5.098-.167-.227-1.367-1.814-1.367-3.463 0-1.647.862-2.457 1.168-2.788.304-.333.665-.417.887-.417.222 0 .444 0 .64.012.207.01.486-.078.76.58.26.64.882 2.21.96 2.37.078.16.13.347.026.56-.108.227-.17.347-.333.534-.16.188-.34.418-.49.562-.162.162-.33.338-.14.665.188.333.836 1.376 1.795 2.227 1.233 1.1 2.27 1.45 2.604 1.61.333.162.528.14.72-.085.193-.222.83-.964 1.052-1.293.22-.333.44-.278.74-.167.304.11 1.914.904 2.24 1.068.333.16.553.245.64.38.084.13.084.74-.176 1.48Z" />
                          </svg>
                          <div className="flex items-center gap-2">
                            <a href={formatWhatsappLink(contact.phone)} className="text-sm text-emerald-300 hover:text-emerald-200" target="_blank" rel="noopener noreferrer">
                              WhatsApp
                            </a>
                            <span className="text-slate-600">|</span>
                            <span className="text-sm text-emerald-200">{contact.label}</span>
                          </div>
                          {idx < 2 && <span className="text-slate-600">•</span>}
                          {idx < 2 && <span className="text-slate-600">&bull;</span>}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-6 transition-all hover:border-slate-500/60 hover:shadow-[0_0_30px_rgba(148,163,184,0.2)]">
                  <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(148,163,184,0.15),transparent)]" />
                  </div>
                  <div className="relative space-y-3">
                    <div className="inline-flex items-center gap-2 rounded-full border border-slate-600/70 bg-slate-800/60 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-200">
                      Email diretta
                    </div>
                    <h3 className="text-xl font-bold text-white">Se preferisci, scrivici</h3>
                    <p className="text-sm text-slate-300/90">info@finch-ai.it</p>
                    <a href="mailto:info@finch-ai.it" className="inline-flex items-center gap-2 text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                      Apri email
                      <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            {/* Trust Indicators */}
            <div className="grid sm:grid-cols-3 gap-6 text-center">
              <div className="p-6 rounded-2xl bg-slate-900/40 border border-slate-700/50">
                <div className="text-3xl font-bold text-cyan-400 mb-2">10 min</div>
                <div className="text-sm text-slate-400">Setup demo personalizzata</div>
              </div>
              <div className="p-6 rounded-2xl bg-slate-900/40 border border-slate-700/50">
                <div className="text-3xl font-bold text-cyan-400 mb-2">4-8 sett</div>
                <div className="text-sm text-slate-400">Deployment completo</div>
              </div>
              <div className="p-6 rounded-2xl bg-slate-900/40 border border-slate-700/50">
                <div className="text-3xl font-bold text-cyan-400 mb-2">ROI 6 mesi</div>
                <div className="text-sm text-slate-400">Return on Investment medio</div>
              </div>
            </div>
          </div>
        </section>
      </main>

      {/* FOOTER */}
      <footer className="relative border-t border-slate-800/50 bg-slate-950/50 backdrop-blur">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          {/* Main Footer Content */}
          <div className="grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
            {/* Company Info */}
            <div className="sm:col-span-2 lg:col-span-1">
              <div className="mb-4 flex items-center gap-3">
                <div className="relative">
                  <div className="absolute inset-0 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-500 opacity-30 blur-lg" />
                  <div className="relative flex h-12 w-12 items-center justify-center rounded-lg bg-white shadow-lg">
                    <img
                      src="/assets/images/LOGO.png"
                      alt="Finch-AI"
                      className="h-10 w-auto object-contain"
                    />
                  </div>
                </div>
                <span className="text-xl font-bold text-white">Finch-AI</span>
              </div>
              <p className="text-sm text-slate-400 leading-relaxed">
                Intelligenza artificiale su misura per l'industria. Automatizziamo processi, estraiamo insights e potenziamo le decisioni.
              </p>
            </div>

            {/* Quick Links */}
            <div>
              <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider text-white">Link Rapidi</h4>
              <ul className="space-y-2">
                {[
                  { label: "Come Funziona", href: "#come-funziona" },
                  { label: "Chi Siamo", href: "#chi-siamo" },
                  { label: "Contatti", href: "#contatti" },
                ].map((link, i) => (
                  <li key={i}>
                    <a
                      href={link.href}
                      className="text-sm text-slate-400 transition-colors hover:text-cyan-400"
                    >
                      {link.label}
                    </a>
                  </li>
                ))}
              </ul>
            </div>

            {/* Contatti */}
            <div>
              <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider text-white">Contatti</h4>
              <ul className="space-y-3">
                <li className="flex items-start gap-2">
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <div>
                    <a href="mailto:info@finch-ai.it" className="text-sm text-slate-400 transition-colors hover:text-cyan-400 block">
                      info@finch-ai.it
                    </a>
                  </div>
                </li>
                <li className="flex items-start gap-2">
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <div>
                    <a href="tel:+393287171587" className="text-sm text-slate-400 transition-colors hover:text-cyan-400 block">
                      +39 328 717 1587
                    </a>
                    <a href="tel:+41764366624" className="text-sm text-slate-400 transition-colors hover:text-cyan-400 block">
                      +41 76 436 6624
                    </a>
                    <a href="tel:+393756475087" className="text-sm text-slate-400 transition-colors hover:text-cyan-400 block">
                      +39 375 647 5087
                    </a>
                    <span className="text-xs text-slate-500 mt-1 block">Lun-Ven 9:00-18:00</span>
                  </div>
                </li>
                <li className="flex items-start gap-2">
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <div>
                    <span className="text-sm text-slate-400 block">
                      Via Enrico Mattei, 18
                    </span>
                    <span className="text-sm text-slate-400 block">
                      67043 Celano (AQ)
                    </span>
                    <span className="text-sm text-slate-400 block">
                      Italia
                    </span>
                  </div>
                </li>
              </ul>
            </div>

            {/* Social & Legal */}
            <div>
              <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider text-white">Seguici</h4>
              <div className="flex gap-3 mb-6">
                <a
                  href="#"
                  aria-label="LinkedIn"
                  className="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-700/60 bg-slate-900/40 text-slate-400 transition-all hover:border-cyan-500/50 hover:bg-slate-800/60 hover:text-cyan-400"
                >
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                  </svg>
                </a>
              </div>
              <ul className="space-y-2">
                <li>
                  <a href="/privacy-policy.html" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="/cookie-policy.html" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    Cookie Policy
                  </a>
                </li>
                <li>
                  <a href="/termini-di-servizio.html" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    Termini di Servizio
                  </a>
                </li>
                <li>
                  <a href="/note-legali.html" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    Note Legali
                  </a>
                </li>
              </ul>
            </div>
          </div>

          {/* Bottom Bar */}
          <div className="border-t border-slate-800/50 py-6">
            <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div className="text-center sm:text-left">
              <p className="text-sm text-slate-500">
                © {new Date().getFullYear()} Finch-AI S.r.l. Tutti i diritti riservati.
              </p>
            </div>
            <div className="flex flex-wrap items-center justify-center gap-2 text-xs text-slate-600" />
            </div>
          </div>
        </div>
      </footer>

      {/* CSS keyframes (inline, no deps) */}
      <style>{`
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeUp {
          from { opacity: 0; transform: translate3d(0, 10px, 0); }
          to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
      `}</style>

      {/* mouse-follow highlight for cards */}
      <script
        dangerouslySetInnerHTML={{
          __html: `
            document.addEventListener('mousemove', (e) => {
              document.querySelectorAll('[class*="group relative overflow-hidden"]').forEach(card => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--x', (e.clientX - rect.left) + 'px');
              });
            });
          `
        }}
      />
    </>
  );
}
