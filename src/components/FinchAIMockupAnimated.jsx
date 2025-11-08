import { useEffect, useRef, useState } from "react";

export default function FinchAIMockupAnimated() {
  const canvasRef = useRef(null);
  const [activeSection, setActiveSection] = useState("hero");
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

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
                  href={`#${item.id}`}
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
              href="mailto:info@finch-ai.it"
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
                  href={`#${item.id}`}
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
                href="mailto:info@finch-ai.it"
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
                Automazione Intelligente per Decisioni di Valore
              </span>
            </h1>

            <p className="mt-5 text-lg text-slate-300/90 animate-[fadeUp_0.9s_ease_0.2s_both]">
              Piattaforma AI che trasforma documenti, dati produttivi e indicatori finanziari in insight azionabili per la tua azienda
            </p>

            <div className="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row animate-[fadeUp_0.9s_ease_0.28s_both] w-full sm:w-auto px-4 sm:px-0">
              <a
                href="#contatti"
                className="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 sm:px-5 sm:py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 w-full sm:w-auto text-center min-h-[48px]"
              >
                Scopri il Tuo Potenziale di Risparmio
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
                { value: "2-4 sett", label: "Deploy" }
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
                Documenti dispersi, dati non integrati, decisioni basate su informazioni frammentate
              </p>
            </div>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {[
                {
                  icon: "📄",
                  title: "Documenti Caotici",
                  desc: "DDT, fatture e ordini gestiti manualmente. Ore perse in data entry, errori frequenti, informazioni che si perdono tra email e fogli di calcolo."
                },
                {
                  icon: "🔌",
                  title: "Sistemi Isolati",
                  desc: "ERP, CRM, gestionale produzione non comunicano. Dati duplicati, sincronizzazione manuale, visibilità zero sull'insieme."
                },
                {
                  icon: "📊",
                  title: "Decisioni al Buio",
                  desc: "Report obsoleti, KPI non aggiornati, analisi che arrivano troppo tardi. Opportunità perse e problemi scoperti in ritardo."
                },
                {
                  icon: "⏱️",
                  title: "Tempo Sprecato",
                  desc: "Il tuo team passa ore a cercare informazioni, verificare dati e creare report invece di concentrarsi su attività strategiche."
                },
                {
                  icon: "💸",
                  title: "Costi Nascosti",
                  desc: "Inefficienze operative, errori di processo, opportunità di ottimizzazione non colte. Il ROI potenziale che sta sfuggendo."
                },
                {
                  icon: "🎯",
                  title: "Controllo Limitato",
                  desc: "Manca una visione unificata di produzione, finanza e operations. Impossibile prendere decisioni data-driven in tempo reale."
                }
              ].map((problem, i) => (
                <div
                  key={i}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-6 transition-all hover:border-red-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_30px_rgba(239,68,68,0.2)]"
                >
                  <div className="text-4xl mb-4">{problem.icon}</div>
                  <h3 className="text-xl font-bold text-white mb-3 group-hover:text-red-300 transition-colors">
                    {problem.title}
                  </h3>
                  <p className="text-sm text-slate-400 leading-relaxed group-hover:text-slate-300 transition-colors">
                    {problem.desc}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* SEZIONE 2: LA SOLUZIONE */}
        <section className="py-20 bg-gradient-to-b from-slate-900/50 to-transparent">
          <div className="mx-auto max-w-5xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
                La Soluzione
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Un'Unica <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">Piattaforma Integrata</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Finch-AI unisce automazione documentale, analisi produttiva e controllo finanziario in un ecosistema intelligente che lavora per te
              </p>
            </div>

            <div className="grid gap-8 lg:grid-cols-2 items-center mb-12">
              <div className="space-y-6">
                <div className="relative pl-6 border-l-4 border-cyan-500">
                  <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                    Tutto Integrato, Sempre Sincronizzato
                  </h3>
                  <p className="text-slate-300/90 leading-relaxed">
                    I tuoi documenti, dati e sistemi dialogano automaticamente. L'AI legge, estrae, valida e integra informazioni in tempo reale, eliminando data entry manuale e errori.
                  </p>
                </div>

                <div className="relative pl-6 border-l-4 border-blue-500">
                  <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                    Intelligenza che Cresce con Te
                  </h3>
                  <p className="text-slate-300/90 leading-relaxed">
                    La piattaforma impara dai tuoi processi, si adatta alle tue esigenze specifiche e migliora continuamente. Non un software rigido, ma un partner intelligente.
                  </p>
                </div>

                <div className="relative pl-6 border-l-4 border-emerald-500">
                  <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                    Controllo Completo, Zero Complessità
                  </h3>
                  <p className="text-slate-300/90 leading-relaxed">
                    Dashboard unificata con visibilità real-time su produzione, finanza e operations. Decisioni informate in secondi, non giorni.
                  </p>
                </div>
              </div>

              <div className="relative">
                <div className="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 blur-3xl rounded-3xl" />
                <div className="relative rounded-2xl border border-cyan-500/30 bg-slate-900/60 backdrop-blur p-8">
                  <h4 className="text-2xl font-bold text-white mb-6">Benefici Immediati</h4>
                  <div className="space-y-4">
                    {[
                      { metric: "90%", label: "Riduzione tempo elaborazione documenti" },
                      { metric: "99.2%", label: "Accuratezza estrazione dati" },
                      { metric: "3x", label: "Velocità decisionale aumentata" },
                      { metric: "100%", label: "Visibilità real-time su tutti i processi" }
                    ].map((item, i) => (
                      <div key={i} className="flex items-center gap-4 p-4 rounded-xl bg-slate-800/50 border border-slate-700/40">
                        <div className="text-3xl font-bold text-cyan-400">{item.metric}</div>
                        <div className="text-sm text-slate-300">{item.label}</div>
                      </div>
                    ))}
                  </div>
                </div>
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
                Tre Pilastri, <span className="bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">Infinite Possibilità</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Ogni modulo risolve un problema specifico, insieme creano un ecosistema che trasforma il tuo business
              </p>
            </div>

            <div className="space-y-8">
              {/* Modulo 1: Document Intelligence */}
              <div className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-slate-900/40 p-8 sm:p-10 backdrop-blur transition hover:border-cyan-500/50 hover:shadow-[0_0_40px_rgba(34,211,238,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(800px_300px_at_var(--x,50%)_0,rgba(34,211,238,0.15),transparent)]" />
                </div>

                <div className="relative grid lg:grid-cols-2 gap-8 items-center">
                  <div>
                    <div className="inline-flex items-center gap-3 mb-6">
                      <div className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-3 text-cyan-300">
                        <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M4 4h10l6 6v10a2 2 0 0 1-2 2H4V4z" fill="none" stroke="currentColor" strokeWidth="1.8"/><path d="M14 4v6h6" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                      </div>
                      <h3 className="text-2xl sm:text-3xl font-bold text-white">Document Intelligence</h3>
                    </div>

                    <p className="text-lg text-slate-300/90 mb-6 leading-relaxed">
                      Trasforma ogni documento in dati strutturati e azionabili. OCR avanzato con validazioni di dominio specifiche per il tuo settore.
                    </p>

                    <div className="space-y-3 mb-6">
                      {[
                        "Estrazione automatica da DDT, fatture, ordini",
                        "Validazione intelligente con regole business",
                        "Integrazione diretta con ERP/gestionale",
                        "Gestione eccezioni e anomalie"
                      ].map((feature, i) => (
                        <div key={i} className="flex items-start gap-3">
                          <svg className="h-6 w-6 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                          </svg>
                          <span className="text-slate-300">{feature}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="space-y-4">
                    <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
                      <div className="text-4xl font-bold text-cyan-400 mb-2">90%</div>
                      <div className="text-sm text-slate-300">Riduzione tempo elaborazione documenti</div>
                    </div>
                    <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
                      <div className="text-4xl font-bold text-cyan-400 mb-2">99.2%</div>
                      <div className="text-sm text-slate-300">Accuratezza estrazione dati</div>
                    </div>
                    <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
                      <div className="text-4xl font-bold text-cyan-400 mb-2">Zero</div>
                      <div className="text-sm text-slate-300">Data entry manuale richiesto</div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Modulo 2: Production Analytics */}
              <div className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-slate-900/40 p-8 sm:p-10 backdrop-blur transition hover:border-purple-500/50 hover:shadow-[0_0_40px_rgba(168,85,247,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(800px_300px_at_var(--x,50%)_0,rgba(168,85,247,0.15),transparent)]" />
                </div>

                <div className="relative grid lg:grid-cols-2 gap-8 items-center">
                  <div className="order-2 lg:order-1 space-y-4">
                    <div className="rounded-2xl border border-purple-500/30 bg-gradient-to-br from-purple-500/10 to-pink-500/10 p-6">
                      <div className="text-4xl font-bold text-purple-400 mb-2">Real-time</div>
                      <div className="text-sm text-slate-300">Monitoraggio OEE e produttività</div>
                    </div>
                    <div className="rounded-2xl border border-purple-500/30 bg-gradient-to-br from-purple-500/10 to-pink-500/10 p-6">
                      <div className="text-4xl font-bold text-purple-400 mb-2">3x</div>
                      <div className="text-sm text-slate-300">Velocità decisioni strategiche</div>
                    </div>
                    <div className="rounded-2xl border border-purple-500/30 bg-gradient-to-br from-purple-500/10 to-pink-500/10 p-6">
                      <div className="text-4xl font-bold text-purple-400 mb-2">100%</div>
                      <div className="text-sm text-slate-300">Visibilità su tutti i reparti</div>
                    </div>
                  </div>

                  <div className="order-1 lg:order-2">
                    <div className="inline-flex items-center gap-3 mb-6">
                      <div className="rounded-xl border border-purple-500/30 bg-purple-500/10 p-3 text-purple-300">
                        <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M4 19h16M6 16V8m6 8V5m6 11v-7" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                      </div>
                      <h3 className="text-2xl sm:text-3xl font-bold text-white">Production Analytics</h3>
                    </div>

                    <p className="text-lg text-slate-300/90 mb-6 leading-relaxed">
                      Dashboard intelligenti che trasformano i dati di produzione in insight azionabili. KPI real-time, anomalie predittive, ottimizzazione continua.
                    </p>

                    <div className="space-y-3 mb-6">
                      {[
                        "KPI real-time: OEE, disponibilità, performance",
                        "Analisi predittiva per manutenzione e scorte",
                        "Alert automatici su anomalie e inefficienze",
                        "Report personalizzati per ogni reparto"
                      ].map((feature, i) => (
                        <div key={i} className="flex items-start gap-3">
                          <svg className="h-6 w-6 text-purple-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                          </svg>
                          <span className="text-slate-300">{feature}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>

              {/* Modulo 3: Financial Control */}
              <div className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-slate-900/40 p-8 sm:p-10 backdrop-blur transition hover:border-emerald-500/50 hover:shadow-[0_0_40px_rgba(16,185,129,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(800px_300px_at_var(--x,50%)_0,rgba(16,185,129,0.15),transparent)]" />
                </div>

                <div className="relative grid lg:grid-cols-2 gap-8 items-center">
                  <div>
                    <div className="inline-flex items-center gap-3 mb-6">
                      <div className="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-emerald-300">
                        <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M7 8h10M4 12h16M7 16h10" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                      </div>
                      <h3 className="text-2xl sm:text-3xl font-bold text-white">Financial Control</h3>
                    </div>

                    <p className="text-lg text-slate-300/90 mb-6 leading-relaxed">
                      Unifica flussi finanziari e operativi per un controllo totale. Integrazione ERP, riconciliazione automatica, previsioni cash-flow basate su AI.
                    </p>

                    <div className="space-y-3 mb-6">
                      {[
                        "Integrazione automatica con qualsiasi ERP",
                        "Riconciliazione documenti-pagamenti",
                        "Forecast cash-flow e marginalità",
                        "Dashboard finanziaria unificata"
                      ].map((feature, i) => (
                        <div key={i} className="flex items-start gap-3">
                          <svg className="h-6 w-6 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                          </svg>
                          <span className="text-slate-300">{feature}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="space-y-4">
                    <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                      <div className="text-4xl font-bold text-emerald-400 mb-2">Zero</div>
                      <div className="text-sm text-slate-300">Attrito nelle integrazioni</div>
                    </div>
                    <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                      <div className="text-4xl font-bold text-emerald-400 mb-2">100%</div>
                      <div className="text-sm text-slate-300">Sincronizzazione automatica</div>
                    </div>
                    <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                      <div className="text-4xl font-bold text-emerald-400 mb-2">24/7</div>
                      <div className="text-sm text-slate-300">Controllo finanziario operativo</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
                  icon: "🏭",
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
                  icon: "🚚",
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
                  icon: "💼",
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
                  icon: "🛒",
                  challenges: "Gestione ordini multi-canale, inventario, fornitori, riconciliazione pagamenti",
                  solutions: [
                    "Unificazione ordini da tutti i canali",
                    "Sincronizzazione inventario real-time",
                    "Gestione automatica ordini fornitori",
                    "Riconciliazione pagamenti/marketplace"
                  ],
                  results: "100% visibilità stock, zero rotture di stock critiche"
                }
              ].map((item, i) => (
                <div
                  key={i}
                  className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 transition-all hover:border-blue-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_40px_rgba(59,130,246,0.2)]"
                >
                  <div className="text-5xl mb-4">{item.icon}</div>
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
              ))}
            </div>
          </div>
        </section>

        {/* SEZIONE 5: CASE STUDY */}
        <section className="py-20">
          <div className="mx-auto max-w-6xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-300 mb-6">
                Case Study
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Risultati <span className="bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-cyan-500">Misurabili</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Casi reali di aziende che hanno trasformato i loro processi con Finch-AI
              </p>
            </div>

            <div className="space-y-8">
              {/* Case Study 1 */}
              <div className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-gradient-to-br from-slate-900/90 to-slate-900/60 backdrop-blur transition-all hover:border-emerald-500/50 hover:shadow-[0_0_50px_rgba(16,185,129,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(1000px_400px_at_var(--x,50%)_0,rgba(16,185,129,0.1),transparent)]" />
                </div>

                <div className="relative p-8 sm:p-10">
                  <div className="grid lg:grid-cols-3 gap-8">
                    <div className="lg:col-span-2 space-y-6">
                      <div>
                        <div className="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-sm font-semibold text-emerald-400 mb-4">
                          Manufacturing PMI
                        </div>
                        <h3 className="text-2xl sm:text-3xl font-bold text-white mb-4">
                          Produttore Componentistica Automotive
                        </h3>
                        <p className="text-lg text-slate-300/90 leading-relaxed">
                          120 dipendenti, 500+ DDT/settimana, gestionale SAP legacy, processo manuale complesso
                        </p>
                      </div>

                      <div>
                        <h4 className="text-lg font-semibold text-white mb-3">Il Problema</h4>
                        <p className="text-slate-300/90 leading-relaxed">
                          3 persone dedicate full-time a inserimento DDT in SAP. Errori frequenti, ritardi nella chiusura commesse,
                          visibilità zero su magazzino real-time. Impossibile scalare senza assumere ulteriore personale amministrativo.
                        </p>
                      </div>

                      <div>
                        <h4 className="text-lg font-semibold text-white mb-3">La Soluzione Finch-AI</h4>
                        <div className="grid sm:grid-cols-2 gap-3">
                          {[
                            "Automazione OCR per DDT in/out",
                            "Integrazione SAP bidirezionale",
                            "Dashboard produzione real-time",
                            "Alert automatici su anomalie"
                          ].map((item, i) => (
                            <div key={i} className="flex items-center gap-2">
                              <div className="h-2 w-2 rounded-full bg-emerald-400"></div>
                              <span className="text-sm text-slate-300">{item}</span>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>

                    <div className="space-y-4">
                      <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                        <div className="text-4xl font-bold text-emerald-400 mb-2">92%</div>
                        <div className="text-sm text-slate-300">Riduzione tempo elaborazione DDT</div>
                      </div>
                      <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                        <div className="text-4xl font-bold text-emerald-400 mb-2">2.5 FTE</div>
                        <div className="text-sm text-slate-300">Risorse liberate per attività strategiche</div>
                      </div>
                      <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                        <div className="text-4xl font-bold text-emerald-400 mb-2">4 sett</div>
                        <div className="text-sm text-slate-300">Tempo di deployment</div>
                      </div>
                      <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                        <div className="text-4xl font-bold text-emerald-400 mb-2">ROI 6 mesi</div>
                        <div className="text-sm text-slate-300">Break-even raggiunto</div>
                      </div>
                    </div>
                  </div>

                  <div className="mt-8 pt-6 border-t border-slate-700/50">
                    <blockquote className="italic text-slate-300/90">
                      "Finch-AI ci ha permesso di scalare del 40% senza assumere personale amministrativo.
                      I nostri responsabili di produzione ora hanno visibilità real-time su tutto.
                      Non è un software, è come avere un team di analisti H24."
                    </blockquote>
                    <div className="mt-3 text-sm font-semibold text-cyan-400">
                      — Marco R., Operations Manager
                    </div>
                  </div>
                </div>
              </div>

              {/* Case Study 2 - Placeholder per futuri casi */}
              <div className="relative overflow-hidden rounded-3xl border border-slate-700/60 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 sm:p-10">
                <div className="text-center">
                  <div className="text-6xl mb-4">📊</div>
                  <h3 className="text-2xl font-bold text-white mb-4">Altri Case Study in Arrivo</h3>
                  <p className="text-slate-300/90 max-w-2xl mx-auto leading-relaxed">
                    Stiamo documentando altri successi dei nostri clienti.
                    Vuoi essere il prossimo case study? Inizia con una demo personalizzata.
                  </p>
                  <a
                    href="#contatti"
                    className="inline-flex items-center gap-2 mt-6 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
                  >
                    Richiedi Demo
                    <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </a>
                </div>
              </div>
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
                  icon: "⚡",
                  desc: "Da ore a minuti per processare DDT, fatture e ordini"
                },
                {
                  metric: "+1000",
                  label: "Documenti/giorno analizzati automaticamente",
                  icon: "📄",
                  desc: "Capacità di elaborazione scalabile senza limiti"
                },
                {
                  metric: "99.2%",
                  label: "Accuratezza estrazione dati",
                  icon: "🎯",
                  desc: "OCR con validazione intelligente domain-specific"
                },
                {
                  metric: "24/7",
                  label: "Monitoraggio operativo continuo",
                  icon: "👁️",
                  desc: "Alert real-time su anomalie e opportunità"
                }
              ].map((item, i) => (
                <div
                  key={i}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-6 transition-all hover:border-cyan-500/50 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                >
                  <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="h-full w-full bg-[radial-gradient(400px_200px_at_50%_0,rgba(34,211,238,0.1),transparent)]" />
                  </div>

                  <div className="relative">
                    <div className="text-4xl mb-4">{item.icon}</div>
                    <div className="text-4xl font-bold text-cyan-400 mb-2">{item.metric}</div>
                    <div className="text-sm font-semibold text-white mb-2">{item.label}</div>
                    <div className="text-xs text-slate-400 leading-relaxed">{item.desc}</div>
                  </div>
                </div>
              ))}
            </div>

            {/* Vantaggi Competitivi */}
            <div className="grid md:grid-cols-3 gap-6">
              {[
                {
                  title: "Deploy Rapido",
                  desc: "Operativi in 2-4 settimane, non mesi. Integrazione plug-and-play con i tuoi sistemi esistenti.",
                  icon: "🚀"
                },
                {
                  title: "Zero Vendor Lock-in",
                  desc: "Dati sempre tuoi, esportabili, API aperte. Integrazione con qualsiasi ERP, CRM o gestionale.",
                  icon: "🔓"
                },
                {
                  title: "ROI Garantito",
                  desc: "Break-even medio in 6 mesi. Calcolo ROI personalizzato prima di partire. Nessun costo nascosto.",
                  icon: "💰"
                }
              ].map((item, i) => (
                <div
                  key={i}
                  className="relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-6 transition-all hover:border-blue-500/50 hover:bg-slate-900/80"
                >
                  <div className="text-4xl mb-4">{item.icon}</div>
                  <h3 className="text-xl font-bold text-white mb-3">{item.title}</h3>
                  <p className="text-sm text-slate-300/90 leading-relaxed">{item.desc}</p>
                </div>
              ))}
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
              { icon: "📊", label: "Reports generati", value: "1.2K", trend: "+12%" },
              { icon: "⚡", label: "Automazioni attive", value: "47", trend: "+8%" },
              { icon: "🎯", label: "Accuracy media", value: "98.5%", trend: "+2.1%" },
            ].map((stat, i) => (
              <div key={i} className="group relative overflow-hidden rounded-xl border border-slate-700/60 bg-slate-900/60 p-4 backdrop-blur transition-all hover:border-cyan-500/40 hover:shadow-lg hover:shadow-cyan-500/10 min-w-[75vw] sm:min-w-0 snap-center touch-pan-x">
                <div className="flex items-center justify-between">
                  <div className="w-full">
                    <div className="flex items-center gap-2 mb-1">
                      <span className="text-xl sm:text-2xl">{stat.icon}</span>
                      <span className="text-xs font-medium text-slate-400">{stat.label}</span>
                    </div>
                    <div className="flex items-baseline gap-2">
                      <span className="text-xl sm:text-2xl font-bold text-white">{stat.value}</span>
                      <span className="text-sm font-semibold text-emerald-400">{stat.trend}</span>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Scroll Indicator for Mobile */}
          <div className="mt-4 flex justify-center gap-2 sm:hidden">
            <div className="h-1 w-8 rounded-full bg-cyan-500/30"></div>
            <div className="h-1 w-8 rounded-full bg-slate-700/50"></div>
            <div className="h-1 w-8 rounded-full bg-slate-700/50"></div>
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
                Scopri come Finch-AI può ottimizzare i tuoi processi in 10 minuti
              </p>
            </div>

            <div className="grid lg:grid-cols-3 gap-6 mb-12">
              {/* CTA 1: Demo Personalizzata */}
              <div className="group relative overflow-hidden rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 transition-all hover:border-cyan-500/50 hover:shadow-[0_0_40px_rgba(34,211,238,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(34,211,238,0.1),transparent)]" />
                </div>

                <div className="relative text-center">
                  <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 mb-6">
                    <svg className="h-8 w-8 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <h3 className="text-xl font-bold text-white mb-3">Demo Live</h3>
                  <p className="text-sm text-slate-300/90 mb-6">
                    Sessione personalizzata di 30 minuti con un nostro esperto. Vedi Finch-AI in azione sui tuoi documenti.
                  </p>
                  <a
                    href="mailto:info@finch-ai.it?subject=Richiesta%20Demo%20Finch-AI&body=Buongiorno%2C%0A%0AVorrei%20prenotare%20una%20demo%20personalizzata%20di%20Finch-AI.%0A%0AAzienda%3A%20%0ASettore%3A%20%0ANumero%20dipendenti%3A%20%0ATelefono%3A%20%0A%0AGrazie"
                    className="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
                  >
                    Prenota Demo
                    <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </a>
                </div>
              </div>

              {/* CTA 2: Whitepaper */}
              <div className="group relative overflow-hidden rounded-3xl border border-purple-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 transition-all hover:border-purple-500/50 hover:shadow-[0_0_40px_rgba(168,85,247,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(168,85,247,0.1),transparent)]" />
                </div>

                <div className="relative text-center">
                  <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/30 mb-6">
                    <svg className="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                  </div>
                  <h3 className="text-xl font-bold text-white mb-3">Whitepaper Gratuito</h3>
                  <p className="text-sm text-slate-300/90 mb-6">
                    "AI Documentale per il Manufacturing: Guida Pratica 2025". Casi d'uso, ROI, implementazione.
                  </p>
                  <a
                    href="mailto:info@finch-ai.it?subject=Richiesta%20Whitepaper&body=Buongiorno%2C%0A%0AVorrei%20ricevere%20il%20whitepaper%20%22AI%20Documentale%20per%20il%20Manufacturing%22.%0A%0ANome%3A%20%0AAzienda%3A%20%0AEmail%3A%20%0A%0AGrazie"
                    className="inline-flex items-center justify-center gap-2 w-full rounded-xl border border-purple-500/50 bg-purple-500/10 px-5 py-3 font-semibold text-purple-300 transition hover:bg-purple-500/20 hover:border-purple-500/70"
                  >
                    Scarica Gratis
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </a>
                </div>
              </div>

              {/* CTA 3: Contatto Veloce */}
              <div className="group relative overflow-hidden rounded-3xl border border-emerald-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 transition-all hover:border-emerald-500/50 hover:shadow-[0_0_40px_rgba(16,185,129,0.2)]">
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(16,185,129,0.1),transparent)]" />
                </div>

                <div className="relative text-center">
                  <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 mb-6">
                    <svg className="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                  </div>
                  <h3 className="text-xl font-bold text-white mb-3">Parla con un Esperto</h3>
                  <p className="text-sm text-slate-300/90 mb-6">
                    Hai domande specifiche? Parliamo del tuo caso d'uso e troviamo la soluzione migliore.
                  </p>
                  <a
                    href="mailto:info@finch-ai.it?subject=Richiesta%20Informazioni&body=Buongiorno%2C%0A%0AVorrei%20maggiori%20informazioni%20su%20Finch-AI.%0A%0ANome%3A%20%0AAzienda%3A%20%0ATelefono%3A%20%0A%0ADescrizione%20esigenza%3A%0A%0A%0AGrazie"
                    className="inline-flex items-center justify-center gap-2 w-full rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-5 py-3 font-semibold text-emerald-300 transition hover:bg-emerald-500/20 hover:border-emerald-500/70"
                  >
                    Contattaci
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </a>
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
                <div className="text-3xl font-bold text-cyan-400 mb-2">2-4 sett</div>
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
                  { label: "Dashboard", href: "#dashboard" },
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
                    <a href="mailto:sales@finch-ai.it" className="text-sm text-slate-400 transition-colors hover:text-cyan-400 block mt-1">
                      sales@finch-ai.it
                    </a>
                  </div>
                </li>
                <li className="flex items-start gap-2">
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <div>
                    <a href="tel:+390123456789" className="text-sm text-slate-400 transition-colors hover:text-cyan-400 block">
                      +39 012 345 6789
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
                      Via Example, 123
                    </span>
                    <span className="text-sm text-slate-400 block">
                      20100 Milano (MI)
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
                {[
                  { icon: "linkedin", href: "#", label: "LinkedIn" },
                  { icon: "twitter", href: "#", label: "Twitter" },
                  { icon: "github", href: "#", label: "GitHub" },
                ].map((social, i) => (
                  <a
                    key={i}
                    href={social.href}
                    aria-label={social.label}
                    className="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-700/60 bg-slate-900/40 text-slate-400 transition-all hover:border-cyan-500/50 hover:bg-slate-800/60 hover:text-cyan-400"
                  >
                    {social.icon === "linkedin" && (
                      <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                      </svg>
                    )}
                    {social.icon === "twitter" && (
                      <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                      </svg>
                    )}
                    {social.icon === "github" && (
                      <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                      </svg>
                    )}
                  </a>
                ))}
              </div>
              <ul className="space-y-2">
                <li>
                  <a href="#privacy" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="#termini" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    Termini di Servizio
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
                <p className="mt-1 text-xs text-slate-600">
                  P.IVA: 12345678901 | REA: MI-1234567 | Cap. Soc. €10.000 i.v.
                </p>
              </div>
              <div className="flex flex-wrap items-center justify-center gap-2 text-xs text-slate-600">
                <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-800/50 bg-slate-900/30 px-3 py-1">
                  <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                  ISO 27001
                </span>
                <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-800/50 bg-slate-900/30 px-3 py-1">
                  <span className="h-1.5 w-1.5 rounded-full bg-cyan-400" />
                  GDPR
                </span>
                <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-800/50 bg-slate-900/30 px-3 py-1">
                  <span className="h-1.5 w-1.5 rounded-full bg-blue-400" />
                  SOC 2
                </span>
              </div>
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
