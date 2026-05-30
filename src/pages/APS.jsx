import { useEffect } from "react";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import SEO from "@/components/SEO";
import { useContactModal } from "@/context/ContactModalContext";
import { ArrowUpRight, Check, Cpu, MessageSquare, GitBranch, Layers, Settings, FlaskConical } from "lucide-react";

function useReveal() {
  useEffect(() => {
    const els = document.querySelectorAll(".reveal");
    const io = new IntersectionObserver(
      (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } }),
      { threshold: 0.12 }
    );
    els.forEach((el) => io.observe(el));
    return () => io.disconnect();
  }, []);
}

const Tick = () => (<span className="tick"><Check size={12} strokeWidth={3} /></span>);

const FEATURES = [
  {
    Icon: Cpu,
    title: "Ottimizzazione avanzata della schedulazione",
    desc: "Algoritmi euristici, metaeuristici ed esatti per generare piani produttivi ottimali, bilanciando vincoli, risorse e priorità.",
  },
  {
    Icon: MessageSquare,
    title: "Interfaccia conversazionale in linguaggio naturale",
    desc: "Interagisci con il pianificatore come con un esperto di produzione: chiedi, simula e ripianifica in linguaggio naturale.",
  },
  {
    Icon: GitBranch,
    title: "Schedulazione multi-vincolo end-to-end",
    desc: "Ordini, macchinari, turni e materiali in un unico motore di pianificazione coerente, sempre aggiornato.",
  },
];

const AMBITI = [
  { Icon: Layers, t: "Schedulazione ordini di produzione" },
  { Icon: Settings, t: "Gestione risorse, macchinari e turni" },
  { Icon: GitBranch, t: "Ottimizzazione lotti e sequenze" },
  { Icon: FlaskConical, t: "Simulazione scenari produttivi" },
];

export default function APS() {
  useReveal();
  const { openContact } = useContactModal();

  const jsonLd = [
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      name: "APS — Advanced Planning System | Finch-AI",
      applicationCategory: "BusinessApplication",
      operatingSystem: "Web",
      description: "APS di Finch-AI è il pianificatore della produzione manifatturiera: ottimizzazione multi-algoritmo della schedulazione e interfaccia conversazionale in linguaggio naturale.",
      offers: { "@type": "Offer", availability: "https://schema.org/InStock" },
      publisher: { "@type": "Organization", name: "Finch-AI", url: "https://finch-ai.it" },
    },
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      itemListElement: [
        { "@type": "ListItem", position: 1, name: "Home", item: "https://finch-ai.it/" },
        { "@type": "ListItem", position: 2, name: "Soluzioni", item: "https://finch-ai.it/#moduli" },
        { "@type": "ListItem", position: 3, name: "APS", item: "https://finch-ai.it/soluzioni/aps" },
      ],
    },
  ];

  return (
    <>
      <SEO
        title="Pianificatore APS — Finch-AI | Advanced Planning System per la produzione"
        description="APS (Advanced Planning System) di Finch-AI: pianificazione avanzata della produzione manifatturiera con ottimizzazione multi-algoritmo e interfaccia conversazionale in linguaggio naturale."
        keywords="APS, advanced planning system, pianificazione produzione, schedulazione produzione, MES, ottimizzazione produzione, AI manifatturiero, Finch-AI"
        canonical="https://finch-ai.it/soluzioni/aps"
        jsonLd={jsonLd}
      />
      <Navbar />

      <main id="top">
        {/* HERO */}
        <section className="hero">
          <div className="hero-grid-bg" />
          <div className="wrap hero-inner">
            <div className="hero-copy">
              <div className="hero-tag eyebrow reveal">Pianificazione · Produzione</div>
              <h1 className="display reveal d1" style={{ fontSize: "clamp(40px,6vw,84px)" }}>
                Pianificatore <span className="stroke">APS</span>
              </h1>
              <p className="lead hero-sub reveal d2">
                Sistema di pianificazione avanzata per la produzione manifatturiera: ottimizzazione multi-algoritmo e
                interfaccia conversazionale in linguaggio naturale per gestire la <strong>schedulazione senza complessità</strong>.
              </p>
              <div className="hero-actions reveal d3">
                <button type="button" onClick={() => openContact({ prefill: { need: "Demo APS — pianificatore produzione" } })} className="btn btn-primary">
                  Richiedi una demo <ArrowUpRight size={16} />
                </button>
                <a href="/#moduli" className="btn btn-ghost">
                  Tutte le soluzioni
                </a>
              </div>
            </div>

            {/* hero visual: badge APS + ambiti */}
            <div className="hero-visual reveal d2">
              <div style={{ background: "var(--fcard)", border: "1px solid var(--line)", borderRadius: "var(--radius-lg)", padding: "clamp(28px,3vw,40px)", boxShadow: "0 30px 80px -30px rgba(11,30,22,.3)" }}>
                <div style={{
                  width: 150, height: 150, margin: "0 auto 26px", borderRadius: "50%",
                  display: "grid", placeItems: "center",
                  border: "2px solid var(--green)", background: "radial-gradient(circle, rgba(14,158,146,.10), transparent 70%)",
                }}>
                  <span style={{ fontFamily: "var(--serif)", fontWeight: 600, fontSize: 40, letterSpacing: "-.02em", color: "var(--green)" }}>APS</span>
                </div>
                <div className="eyebrow" style={{ justifyContent: "center", width: "100%", marginBottom: 18 }}>Ambiti di applicazione</div>
                <ul style={{ listStyle: "none", display: "flex", flexDirection: "column", gap: 12, padding: 0, margin: 0 }}>
                  {AMBITI.map(({ Icon, t }) => (
                    <li key={t} style={{ display: "flex", gap: 12, alignItems: "center", fontSize: 15.5, color: "var(--ink)" }}>
                      <span style={{ width: 34, height: 34, borderRadius: 9, flex: "none", display: "grid", placeItems: "center", background: "rgba(14,158,146,.1)", color: "var(--green)" }}>
                        <Icon size={17} />
                      </span>
                      {t}
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          </div>
        </section>

        {/* FEATURES (dark, come la sezione moduli) */}
        <section className="section modules">
          <div className="wrap">
            <div className="modules-head">
              <h2 className="h2 reveal">Pianificazione che parla<br />la <em>tua lingua</em>.</h2>
              <div className="eyebrow on-dark reveal d1" style={{ paddingBottom: 10 }}>Advanced Planning System</div>
            </div>

            {FEATURES.map((f, i) => (
              <article className="mod reveal" key={f.title}>
                <div className="mod-num">{String(i + 1).padStart(2, "0")}</div>
                <div className="mod-main">
                  <h3>{f.title}</h3>
                  <p style={{ marginTop: 8 }}>{f.desc}</p>
                </div>
                <div className="mod-feats">
                  <div className="mod-feat"><Tick />Vincoli di capacità, materiali e competenze</div>
                  <div className="mod-feat"><Tick />Ripianificazione rapida al cambiare degli ordini</div>
                  <div className="mod-feat"><Tick />Integrabile con ERP e MES esistenti</div>
                </div>
              </article>
            ))}
          </div>
        </section>

        {/* AMBITI griglia */}
        <section className="section value">
          <div className="wrap">
            <div className="value-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 22 }}>Dove genera valore</div>
                <h2 className="h2 reveal d1">Dalla teoria al reparto,<br />in <em>tempi rapidi</em>.</h2>
              </div>
              <p className="lead reveal d2">
                APS porta l'ottimizzazione matematica nella pianificazione quotidiana, con un'interfaccia che chiunque in produzione sa usare.
              </p>
            </div>
            <div className="value-grid reveal d1">
              {AMBITI.map(({ Icon, t }, i) => (
                <div className="value-cell" key={t}>
                  <span className="num">{String(i + 1).padStart(2, "0")}</span>
                  <h3 className="h3" style={{ fontSize: 21 }}>{t}</h3>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* CTA */}
        <section className="section cta" id="contatti">
          <div className="wrap cta-inner">
            <h2 className="h2 reveal">Pronto a pianificare<br />con <em>l'AI</em>?</h2>
            <div className="cta-right reveal d1">
              <p>Ti mostriamo APS sui tuoi ordini e vincoli reali in una demo di 30 minuti, senza impegno.</p>
              <div className="cta-actions">
                <button type="button" onClick={() => openContact({ prefill: { need: "Demo APS — pianificatore produzione" } })} className="btn btn-primary">Richiedi una demo <ArrowUpRight size={16} /></button>
                <button type="button" onClick={() => openContact()} className="btn btn-ghost">Scrivici</button>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </>
  );
}
