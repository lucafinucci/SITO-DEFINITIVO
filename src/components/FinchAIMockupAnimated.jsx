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
    { id: "hero", label: "Home" },
    { id: "come-funziona", label: "Come funziona" },
    { id: "contatti", label: "Contatti" },
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
              Ogni azienda è unica.<br className="hidden sm:block" />
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 via-sky-400 to-blue-500">
                Anche la tua intelligenza artificiale.
              </span>
            </h1>

            <p className="mt-5 text-lg text-slate-300/90 animate-[fadeUp_0.9s_ease_0.2s_both]">
              Automatizza i DDT, collega l'ERP, visualizza KPI. Finch-AI trasforma documenti e dati
              in decisioni, con precisione industriale e zero attrito operativo.
            </p>

            <div className="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row animate-[fadeUp_0.9s_ease_0.28s_both] w-full sm:w-auto px-4 sm:px-0">
              <a
                href="#contatti"
                className="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 sm:px-5 sm:py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 w-full sm:w-auto text-center min-h-[48px]"
              >
                Prenota una demo
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

            {/* trust badges */}
            <div className="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-slate-400/80 animate-[fadeUp_0.9s_ease_0.36s_both]">
              <span className="inline-flex items-center gap-2">
                <span className="h-2 w-2 rounded-full bg-emerald-400" /> SLA produzione
              </span>
              <span className="inline-flex items-center gap-2">
                <span className="h-2 w-2 rounded-full bg-cyan-400" /> GDPR-ready
              </span>
              <span className="inline-flex items-center gap-2">
                <span className="h-2 w-2 rounded-full bg-blue-400" /> On-prem o Cloud
              </span>
            </div>
          </div>
        </section>

        {/* CHI SIAMO / MISSION */}
        <section className="py-20">
          <div className="mx-auto max-w-5xl">
            <div className="text-center mb-16">
              <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
                Chi Siamo
              </span>
              <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                La nostra <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">Mission</span>
              </h2>
              <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                Trasformiamo l'intelligenza artificiale da promessa a realtà operativa per le aziende italiane
              </p>
            </div>

            <div className="grid gap-8 lg:grid-cols-2 items-center">
              {/* Testo Mission */}
              <div className="space-y-6">
                <div className="relative pl-6 border-l-4 border-cyan-500">
                  <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                    L'AI che funziona davvero
                  </h3>
                  <p className="text-slate-300/90 leading-relaxed">
                    Finch-AI nasce dall'esperienza diretta con le sfide operative delle PMI manifatturiere.
                    Non vendiamo hype: costruiamo sistemi AI che si integrano nei processi reali,
                    riducono i costi operativi e generano ROI misurabile.
                  </p>
                </div>

                <div className="relative pl-6 border-l-4 border-blue-500">
                  <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                    Precisione industriale
                  </h3>
                  <p className="text-slate-300/90 leading-relaxed">
                    Ogni nostro sistema è addestrato su dati reali italiani, validato in produzione
                    e ottimizzato per garantire accuratezza superiore al 99%. Zero errori critici,
                    zero sorprese.
                  </p>
                </div>

                <div className="relative pl-6 border-l-4 border-emerald-500">
                  <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                    Partner, non fornitori
                  </h3>
                  <p className="text-slate-300/90 leading-relaxed">
                    Seguiamo ogni cliente dall'analisi dei requisiti al deployment,
                    con supporto continuativo e aggiornamenti costanti. Il vostro successo
                    è il nostro benchmark.
                  </p>
                </div>
              </div>

              {/* Card Valori */}
              <div className="relative">
                <div className="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 blur-3xl rounded-3xl" />

                <div className="relative space-y-4">
                  {[
                    { icon: "🎯", title: "Focus assoluto", desc: "100% manufacturing & logistics" },
                    { icon: "🔒", title: "Privacy first", desc: "GDPR compliant, dati in Italia" },
                    { icon: "⚡", title: "Rapido deployment", desc: "Operative in 2-4 settimane" },
                    { icon: "🤝", title: "SLA garantito", desc: "Uptime 99.9%, supporto H24" },
                  ].map((item, i) => (
                    <div
                      key={i}
                      className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-6 transition-all hover:border-cyan-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                    >
                      <div className="flex items-start gap-4">
                        <div className="flex-shrink-0 text-4xl">{item.icon}</div>
                        <div className="flex-1">
                          <h4 className="text-lg font-semibold text-white mb-1 group-hover:text-cyan-300 transition-colors">
                            {item.title}
                          </h4>
                          <p className="text-sm text-slate-400 group-hover:text-slate-300 transition-colors">
                            {item.desc}
                          </p>
                        </div>
                        <svg
                          className="w-5 h-5 text-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                        </svg>
                      </div>
                    </div>
                  ))}
                </div>

                {/* Badge certificazione */}
                <div className="relative mt-6 flex items-center justify-center gap-4 p-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/5 backdrop-blur">
                  <div className="flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20 border-2 border-emerald-500/50">
                      <svg className="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-sm font-semibold text-emerald-400">Certificato ISO 27001</div>
                      <div className="text-xs text-slate-400">Sicurezza dati garantita</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* VALUE PILLARS */}
        <section id="come-funziona" className="pb-12">
          <div className="grid gap-4 sm:gap-6 md:grid-cols-3">
            {[
              {
                title: "Automazione DDT",
                desc: "OCR + validazioni di dominio. Da PDF/immagini a dati strutturati senza errori.",
                kpi: "−90% tempo",
                icon: (
                  <svg viewBox="0 0 24 24" className="h-5 w-5 sm:h-6 sm:w-6"><path d="M4 4h10l6 6v10a2 2 0 0 1-2 2H4V4z" fill="none" stroke="currentColor" strokeWidth="1.8"/><path d="M14 4v6h6" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                )
              },
              {
                title: "KPI Intelligence",
                desc: "Dashboard in tempo reale: OEE, produttività, NC. Insight azionabili, non grafici vuoti.",
                kpi: "Decisioni ×3",
                icon: (
                  <svg viewBox="0 0 24 24" className="h-5 w-5 sm:h-6 sm:w-6"><path d="M4 19h16M6 16V8m6 8V5m6 11v-7" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                )
              },
              {
                title: "ERP Connector",
                desc: "Integrazioni pulite con il tuo gestionale. CSV/FTP/API senza attrito.",
                kpi: "Zero attriti",
                icon: (
                  <svg viewBox="0 0 24 24" className="h-5 w-5 sm:h-6 sm:w-6"><path d="M7 8h10M4 12h16M7 16h10" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                )
              }
            ].map((c, i) => (
              <div
                key={i}
                className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/40 p-5 sm:p-6 backdrop-blur transition hover:border-cyan-500/50 hover:shadow-[0_0_0_1px_rgba(34,211,238,0.25),0_10px_30px_-10px_rgba(34,211,238,0.25)]"
              >
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(600px_200px_at_var(--x,50%)_0,rgba(34,211,238,0.12),transparent)]" />
                </div>
                <div className="relative flex items-center gap-3">
                  <div className="rounded-xl border border-slate-700/60 bg-slate-800/60 p-2.5 sm:p-3 text-cyan-300">{c.icon}</div>
                  <h3 className="text-lg sm:text-xl font-semibold">{c.title}</h3>
                </div>
                <p className="relative mt-3 text-sm sm:text-base text-slate-300/90">{c.desc}</p>
                <div className="relative mt-4 inline-flex items-center gap-2 rounded-lg bg-slate-800/50 px-3 py-1 text-xs sm:text-sm text-cyan-300">
                  <span className="h-1.5 w-1.5 rounded-full bg-cyan-300" /> {c.kpi}
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* DASHBOARD PREVIEW */}
        <section className="py-16">
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

        {/* CTA */}
        <section id="contatti" className="pb-24">
          <div className="relative overflow-hidden rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/60 via-slate-900/40 to-slate-900/60 p-6 sm:p-8 lg:p-10">
            <div className="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-cyan-500/20 blur-3xl" />
            <div className="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
              <div>
                <h3 className="text-xl sm:text-2xl font-bold">Pronto a ridurre del 90% i tempi sui DDT?</h3>
                <p className="mt-2 max-w-2xl text-sm sm:text-base text-slate-300/90">
                  Ti mostriamo in 10 minuti come Finch-AI legge, valida e integra i tuoi documenti nel gestionale.
                </p>
              </div>
              <a
                href="mailto:info@finch-ai.it?subject=Demo%20Finch-AI&body=Ciao%2C%20vorrei%20prenotare%20una%20demo."
                className="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4 sm:px-5 sm:py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 w-full sm:w-auto text-center min-h-[48px] whitespace-nowrap"
              >
                Richiedi una demo
                <svg className="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </a>
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
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <a href="mailto:info@finch-ai.it" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    info@finch-ai.it
                  </a>
                </li>
                <li className="flex items-start gap-2">
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <a href="tel:+390123456789" className="text-sm text-slate-400 transition-colors hover:text-cyan-400">
                    +39 012 345 6789
                  </a>
                </li>
                <li className="flex items-start gap-2">
                  <svg className="h-5 w-5 text-cyan-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span className="text-sm text-slate-400">
                    Milano, Italia
                  </span>
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
                  © {new Date().getFullYear()} Finch-AI. Tutti i diritti riservati.
                </p>
                <p className="mt-1 text-xs text-slate-600">
                  P.IVA: 12345678901
                </p>
              </div>
              <div className="flex items-center gap-2 text-xs text-slate-600">
                <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-800/50 bg-slate-900/30 px-3 py-1">
                  <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                  ISO 27001 Certified
                </span>
                <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-800/50 bg-slate-900/30 px-3 py-1">
                  <span className="h-1.5 w-1.5 rounded-full bg-cyan-400" />
                  GDPR Compliant
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
