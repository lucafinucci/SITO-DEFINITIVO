import { useEffect, useRef, useState } from 'react';

function AnimatedCounter({ end, duration = 2000, suffix = '', decimals = 0 }) {
  const [count, setCount] = useState(0);
  const [isVisible, setIsVisible] = useState(false);
  const counterRef = useRef(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !isVisible) {
          setIsVisible(true);
        }
      },
      { threshold: 0.1 }
    );

    if (counterRef.current) {
      observer.observe(counterRef.current);
    }

    return () => {
      if (counterRef.current) {
        observer.unobserve(counterRef.current);
      }
    };
  }, [isVisible]);

  useEffect(() => {
    if (!isVisible) return;

    let startTime;
    let animationFrame;

    const animate = (currentTime) => {
      if (!startTime) startTime = currentTime;
      const progress = Math.min((currentTime - startTime) / duration, 1);

      const easeOutQuart = 1 - Math.pow(1 - progress, 4);
      const currentValue = easeOutQuart * end;

      setCount(decimals > 0 ? parseFloat(currentValue.toFixed(decimals)) : Math.floor(currentValue));

      if (progress < 1) {
        animationFrame = requestAnimationFrame(animate);
      } else {
        setCount(end);
      }
    };

    animationFrame = requestAnimationFrame(animate);

    return () => {
      if (animationFrame) {
        cancelAnimationFrame(animationFrame);
      }
    };
  }, [isVisible, end, duration, decimals]);

  const formatNumber = (num) => {
    if (decimals > 0) {
      return num.toLocaleString('it-IT', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }
    return num.toLocaleString('it-IT');
  };

  return (
    <span ref={counterRef}>
      {formatNumber(count)}{suffix}
    </span>
  );
}

export default function FinchAIMockup() {
  return (
    <>
      {/* Background layers */}
      <div className="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        {/* radial glow */}
        <div className="absolute -top-1/4 left-1/2 h-[120vh] w-[120vh] -translate-x-1/2 rounded-full bg-[radial-gradient(ellipse_at_center,rgba(23,162,255,0.18),rgba(0,0,0,0))]" />
        {/* animated gradient veil */}
        <div className="absolute inset-0 opacity-50 [background:linear-gradient(120deg,#0b1220,35%,#0a1a2b_60%,#03101f_85%)]" />
        {/* neural mesh */}
        <svg className="absolute inset-0 h-full w-full opacity-30" viewBox="0 0 1200 800" aria-hidden="true">
          <defs>
            <linearGradient id="g1" x1="0" x2="1">
              <stop offset="0%" stopColor="#00E0FF"/>
              <stop offset="100%" stopColor="#3B82F6"/>
            </linearGradient>
            <filter id="glow">
              <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
              <feMerge>
                <feMergeNode in="coloredBlur"/>
                <feMergeNode in="SourceGraphic"/>
              </feMerge>
            </filter>
          </defs>
          {Array.from({length: 18}).map((_, i) => (
            <path
              key={i}
              d={`
                M ${-50+i*70} ${50+i*20}
                C ${120+i*40} ${120+i*10}, ${300+i*25} ${-20+i*30}, ${520+i*20} ${80+i*18}
                S ${850+i*15} ${220+i*8}, ${1200} ${180+i*12}
              `}
              stroke="url(#g1)"
              strokeWidth="0.8"
              fill="none"
              filter="url(#glow)"
              className="animate-pulse"
              style={{ animationDuration: `${3.5 + (i%6)*0.35}s`, animationDirection: i%2 ? 'reverse' : 'normal' }}
            />
          ))}
        </svg>
        {/* subtle scanline */}
        <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[length:100%_28px] mix-blend-overlay" />
      </div>

      {/* Page container */}
      <main className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-slate-200">
        {/* HERO */}
        <section className="pt-20 sm:pt-28 lg:pt-36 pb-12">
          <div className="mx-auto max-w-3xl text-center">
            <span className="inline-flex items-center gap-2 rounded-full border border-slate-700/60 bg-slate-900/40 px-3 py-1 text-xs uppercase tracking-wider text-cyan-300/80 animate-fade-in opacity-0" style={{ animationDelay: '0.1s', animationFillMode: 'forwards' }}>
              <span className="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-ping" />
              AI per Operazioni Reali
            </span>
            <h1 className="mt-5 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl animate-fade-in opacity-0" style={{ animationDelay: '0.2s', animationFillMode: 'forwards' }}>
              Ogni azienda è unica.<br className="hidden sm:block" />
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 via-sky-400 to-blue-500">
                Anche la tua intelligenza artificiale.
              </span>
            </h1>
            <p className="mt-5 text-lg text-slate-300/90 animate-fade-in opacity-0" style={{ animationDelay: '0.3s', animationFillMode: 'forwards' }}>
              Automatizza i DDT, collega l'ERP, visualizza KPI. Finch-AI trasforma documenti e dati
              in decisioni, con precisione industriale e zero attrito operativo.
            </p>

            <div className="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row animate-fade-in opacity-0" style={{ animationDelay: '0.4s', animationFillMode: 'forwards' }}>
              <a
                href="#contatti"
                className="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
              >
                Prenota una demo
                <svg className="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </a>
              <a
                href="#come-funziona"
                className="inline-flex items-center gap-2 rounded-xl border border-slate-700/70 bg-slate-900/40 px-5 py-3 font-semibold text-slate-200 hover:border-slate-500/80 hover:bg-slate-900/60"
              >
                Guarda come funziona
              </a>
            </div>

            {/* trust badges */}
            <div className="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-slate-400/80 animate-fade-in opacity-0" style={{ animationDelay: '0.5s', animationFillMode: 'forwards' }}>
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

        {/* VALUE PILLARS */}
        <section id="come-funziona" className="pb-6">
          <div className="grid gap-6 md:grid-cols-3">
            {[
              {
                title: "Automazione DDT",
                desc: "OCR + validazioni di dominio. Da PDF/immagini a dati strutturati senza errori.",
                kpi: "−90% tempo",
                icon: (
                  <svg viewBox="0 0 24 24" className="h-6 w-6"><path d="M4 4h10l6 6v10a2 2 0 0 1-2 2H4V4z" fill="none" stroke="currentColor" strokeWidth="1.8"/><path d="M14 4v6h6" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                )
              },
              {
                title: "KPI Intelligence",
                desc: "Dashboard in tempo reale: OEE, produttività, NC. Insight azionabili, non grafici vuoti.",
                kpi: "Decisioni ×3",
                icon: (
                  <svg viewBox="0 0 24 24" className="h-6 w-6"><path d="M4 19h16M6 16V8m6 8V5m6 11v-7" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                )
              },
              {
                title: "ERP Connector",
                desc: "Integrazioni pulite con il tuo gestionale. CSV/FTP/API senza attrito.",
                kpi: "Zero attriti",
                icon: (
                  <svg viewBox="0 0 24 24" className="h-6 w-6"><path d="M7 8h10M4 12h16M7 16h10" fill="none" stroke="currentColor" strokeWidth="1.8"/></svg>
                )
              }
            ].map((c, i) => (
              <div
                key={i}
                className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/40 p-6 backdrop-blur transition hover:border-cyan-500/50 hover:shadow-[0_0_0_1px_rgba(34,211,238,0.25),0_10px_30px_-10px_rgba(34,211,238,0.25)]"
              >
                <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                  <div className="h-full w-full bg-[radial-gradient(600px_200px_at_var(--x,50%)_0,rgba(34,211,238,0.12),transparent)]" />
                </div>
                <div className="relative flex items-center gap-3">
                  <div className="rounded-xl border border-slate-700/60 bg-slate-800/60 p-3 text-cyan-300">{c.icon}</div>
                  <h3 className="text-xl font-semibold">{c.title}</h3>
                </div>
                <p className="relative mt-3 text-slate-300/90">{c.desc}</p>
                <div className="relative mt-4 inline-flex items-center gap-2 rounded-lg bg-slate-800/50 px-3 py-1 text-sm text-cyan-300">
                  <span className="h-1.5 w-1.5 rounded-full bg-cyan-300" /> {c.kpi}
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* PROOF / NUMBERS */}
        <section className="py-10">
          <div className="grid gap-6 sm:grid-cols-3">
            {[
              {label: "DDT processati/mese", numValue: 50000, displaySuffix: "+", prefix: "", decimals: 0, gradient: "from-cyan-400 via-cyan-300 to-blue-400"},
              {label: "Accuratezza estrazione", numValue: 99.2, displaySuffix: "%", prefix: "", decimals: 1, gradient: "from-emerald-400 via-cyan-300 to-sky-400"},
              {label: "Tempo medio per DDT", numValue: 3.5, displaySuffix: "s", prefix: "≤ ", decimals: 1, gradient: "from-blue-400 via-sky-300 to-cyan-400"},
            ].map((s, i) => (
              <div
                key={i}
                className="group relative rounded-2xl border border-slate-700/60 bg-slate-900/40 p-6 text-center transition-all duration-300 hover:border-cyan-500/50 hover:shadow-[0_0_30px_-5px_rgba(34,211,238,0.3)]"
              >
                {/* Animated glow on hover */}
                <div className="absolute inset-0 rounded-2xl bg-gradient-to-br from-cyan-500/0 via-blue-500/0 to-cyan-500/0 opacity-0 transition-opacity duration-500 group-hover:opacity-10" />

                <div className="relative">
                  <div className={`bg-clip-text text-transparent bg-gradient-to-r ${s.gradient} text-4xl sm:text-5xl font-extrabold tracking-tight animate-fade-in opacity-0`} style={{ animationDelay: `${0.6 + i * 0.1}s`, animationFillMode: 'forwards' }}>
                    {s.prefix}
                    <AnimatedCounter
                      end={s.numValue}
                      suffix={s.displaySuffix}
                      duration={2000}
                      decimals={s.decimals}
                    />
                  </div>

                  {/* Animated underline */}
                  <div className="mx-auto mt-3 h-1 w-0 rounded-full bg-gradient-to-r from-cyan-400 to-blue-500 transition-all duration-500 group-hover:w-16" />

                  <div className="mt-3 text-sm font-medium text-slate-400 transition-colors duration-300 group-hover:text-slate-300">
                    {s.label}
                  </div>
                </div>

                {/* Subtle pulse effect */}
                <div className="absolute inset-0 -z-10 rounded-2xl bg-gradient-to-br from-cyan-500/5 to-blue-500/5 opacity-0 blur-xl transition-opacity duration-500 group-hover:opacity-100" />
              </div>
            ))}
          </div>
        </section>

        {/* CTA STRONG */}
        <section id="contatti" className="pb-20">
          <div className="relative overflow-hidden rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/60 via-slate-900/40 to-slate-900/60 p-8 sm:p-10">
            <div className="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-cyan-500/20 blur-3xl" />
            <div className="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
              <div>
                <h3 className="text-2xl font-bold">Pronto a ridurre del 90% i tempi sui DDT?</h3>
                <p className="mt-2 max-w-2xl text-slate-300/90">
                  Ti mostriamo in 10 minuti come Finch-AI legge, valida e integra i tuoi documenti nel gestionale.
                </p>
              </div>
              <a
                href="mailto:info@finch-ai.it?subject=Demo%20Finch-AI&body=Ciao%2C%20vorrei%20prenotare%20una%20demo."
                className="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
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
    </>
  );
}
