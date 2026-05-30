import { useEffect } from "react";
import { Link } from "react-router-dom";
import { ArrowUpRight, ArrowDown, Check } from "lucide-react";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import SEO from "@/components/SEO";
import { useContactModal } from "@/context/ContactModalContext";

/* Reveal-on-scroll: aggiunge .in agli elementi .reveal quando entrano in viewport */
function useReveal() {
  useEffect(() => {
    const els = document.querySelectorAll(".reveal");
    const io = new IntersectionObserver(
      (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } }),
      { threshold: 0.12 }
    );
    els.forEach((el) => io.observe(el));
    // scroll all'ancora se presente (es. /#contatti)
    if (window.location.hash) {
      const id = window.location.hash.slice(1);
      setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" }), 200);
    }
    return () => io.disconnect();
  }, []);
}

const Tick = () => (
  <span className="tick"><Check size={12} strokeWidth={3} /></span>
);

const MODULES = [
  {
    n: "01", tag: "Gestionale AI", name: "OmniFlow", href: "/soluzioni/warehouse-intelligence",
    desc: "Il cuore operativo. Orchestra ordini, fatture, magazzino e flussi di lavoro con un copilota che capisce le richieste in linguaggio naturale e agisce al posto tuo.",
    feats: ["Automazione dei flussi ripetitivi end-to-end", "Copilota conversazionale sul tuo gestionale", "Integrazione con i sistemi che già usi"],
  },
  {
    n: "02", tag: "Comprensione documentale", name: "Document Intelligence", href: "/soluzioni/document-intelligence",
    desc: "Legge, classifica ed estrae dati da fatture, contratti, DDT ed e-mail. Trasforma la carta e i PDF in informazioni strutturate, pronte per essere usate.",
    feats: ["Estrazione dati con riconoscimento del contesto", "Riconciliazione e controllo automatici", "Archivio ricercabile in linguaggio naturale"],
  },
  {
    n: "03", tag: "Analisi finanziaria", name: "Finance Intelligence", href: "/soluzioni/finance-intelligence",
    desc: "Dà visibilità sul futuro: cash flow previsionale, marginalità per cliente e prodotto, KPI in tempo reale. La direzione decide sui numeri, non sulle sensazioni.",
    feats: ["Cash flow e scenari previsionali", "Cruscotto KPI aggiornato in tempo reale", "Alert automatici su marginalità e scaduti"],
  },
  {
    n: "04", tag: "Knowledge Intelligence", name: "Synapse", href: "/soluzioni/synapse",
    desc: "Il cervello documentale dell'azienda. Trasforma documenti, email, contratti e conversazioni sparse in un wiki vivo che si auto-mantiene — con ogni risposta citata fino alla fonte.",
    feats: ["Wiki vivo & knowledge graph aziendale", "Risposte con citazioni verificabili", "Dati on-premise, nel tuo perimetro"],
  },
];

export default function Home() {
  useReveal();
  const { openContact } = useContactModal();

  const jsonLd = [
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      name: "Finch-AI",
      url: "https://finch-ai.it",
      logo: "https://finch-ai.it/favicon-512.png",
      description: "Intelligenza artificiale su misura che si adatta ed evolve con le PMI italiane: OmniFlow, Document Intelligence, Finance Intelligence e Synapse.",
      sameAs: ["https://www.linkedin.com/company/finch-ai"],
    },
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      name: "Finch-AI",
      url: "https://finch-ai.it",
      inLanguage: "it-IT",
    },
  ];

  return (
    <>
      <SEO
        title="Finch-AI — Intelligenza artificiale, precisione italiana"
        description="Finch-AI progetta intelligenza artificiale su misura che si adatta ai processi ed evolve con le PMI italiane: OmniFlow, Document Intelligence, Finance Intelligence e Synapse. Operativa in tempi rapidi."
        keywords="AI per PMI, intelligenza artificiale PMI italiane, OmniFlow, Document Intelligence, Finance Intelligence, Synapse, automazione documentale, analisi finanziaria AI"
        canonical="https://finch-ai.it/"
        jsonLd={jsonLd}
      />
      <Navbar />

      <main id="top">
        {/* ============ HERO ============ */}
        <section className="hero">
          <div className="hero-grid-bg" />
          <div className="wrap hero-inner">
            <div className="hero-copy">
              <div className="hero-tag eyebrow reveal">AI Enterprise per le PMI italiane</div>
              <h1 className="display reveal d1">
                L'intelligenza<br />artificiale che <em>lavora</em><br />come la <span className="stroke">tua impresa</span>.
              </h1>
              <p className="lead hero-sub reveal d2">
                Finch-AI progetta intelligenza artificiale su misura che si adatta ai tuoi processi e{" "}
                <strong>evolve insieme alla tua impresa</strong>. Dai moduli pronti all'agente costruito su di te — operativa in tempi rapidi, non in mesi.
              </p>
              <div className="hero-actions reveal d3">
                <button type="button" onClick={() => openContact({ prefill: { need: "Richiesta demo" } })} className="btn btn-primary">
                  Prenota una demo <ArrowUpRight size={16} />
                </button>
                <a href="/#moduli" onClick={(e) => { e.preventDefault(); document.getElementById("moduli")?.scrollIntoView({ behavior: "smooth" }); }} className="btn btn-ghost">
                  Esplora i moduli
                </a>
              </div>
              <div className="hero-stats reveal d4">
                <div className="hero-stat"><div className="n">Adattiva</div><div className="l">Evolve con te</div></div>
                <div className="hero-stat"><div className="n">Su misura</div><div className="l">Su ogni impresa</div></div>
                <div className="hero-stat"><div className="n">100<span>%</span></div><div className="l">Dati in Italia</div></div>
              </div>
            </div>

            {/* infografica generale */}
            <div className="hero-visual reveal d2">
              <div className="flow">
                <div className="flow-head">
                  <span className="ft">Come lavora Finch</span>
                  <span className="fb">AI adattiva</span>
                </div>
                <div className="flow-stage">
                  <div className="flow-lab">01 · Le tue fonti</div>
                  <div className="chips">
                    <span className="chip"><span className="ci" />Documenti</span>
                    <span className="chip"><span className="ci" />Processi</span>
                    <span className="chip"><span className="ci" />Gestionali</span>
                    <span className="chip"><span className="ci" />Email &amp; chat</span>
                  </div>
                </div>
                <div className="flow-arrow"><ArrowDown size={18} /></div>
                <div className="flow-core">
                  <div className="cn">Finch AI</div>
                  <div className="cd"><span>si adatta</span><span>evolve</span></div>
                  <div className="flow-float">Cresce con la tua impresa</div>
                </div>
                <div className="flow-arrow"><ArrowDown size={18} /></div>
                <div className="flow-stage">
                  <div className="flow-lab">03 · Le soluzioni</div>
                  <div className="chips sols">
                    <span className="chip sol"><span className="ci" />OmniFlow</span>
                    <span className="chip sol"><span className="ci" />Document Intelligence</span>
                    <span className="chip sol"><span className="ci" />Finance Intelligence</span>
                    <span className="chip sol"><span className="ci" />Synapse</span>
                    <span className="chip tailor">+ Su misura</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ============ PARTNER STRIP ============ */}
        <section className="partners" id="partner">
          <div className="partners-inner">
            <div className="partners-label">Con il riconoscimento di</div>
            <div className="partners-list">
              <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer" className="partner link">
                <span className="pn">Partner 24 Ore</span>
                <span className="pr">Partner ufficiale</span>
              </a>
              <div className="partner">
                <span className="pn">Confindustria</span>
                <span className="pr">L'Aquila</span>
              </div>
            </div>
          </div>
        </section>

        {/* ============ VALUE ============ */}
        <section className="section value" id="piattaforma">
          <div className="wrap">
            <div className="value-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 22 }}>Il nostro approccio</div>
                <h2 className="h2 reveal d1">Un'unica intelligenza,<br />fatta per la <em>tua impresa</em>.</h2>
              </div>
              <p className="lead reveal d2">
                Le PMI italiane non hanno bisogno dell'ennesimo software. Hanno bisogno di un'AI che capisca i loro documenti, i loro numeri e i loro processi — e li mandi avanti da sola.
              </p>
            </div>
            <div className="value-grid reveal d1">
              <div className="value-cell">
                <span className="num">01</span>
                <h3 className="h3">Modulare per davvero</h3>
                <p>Attivi solo ciò che ti serve, quando ti serve. Ogni modulo lavora da solo o si integra con gli altri, senza riscrivere i tuoi processi.</p>
              </div>
              <div className="value-cell">
                <span className="num">02</span>
                <h3 className="h3">Costruita sui tuoi dati</h3>
                <p>Si addestra sul linguaggio, sui documenti e sulle regole della tua azienda. I dati restano in Italia, sotto il tuo controllo.</p>
              </div>
              <div className="value-cell">
                <span className="num">03</span>
                <h3 className="h3">Si adatta ed evolve</h3>
                <p>Non un software statico: l'AI impara dal tuo lavoro, migliora nel tempo e cresce con l'azienda. Operativa in tempi rapidi, mai con progetti infiniti.</p>
              </div>
            </div>
          </div>
        </section>

        {/* ============ MODULES ============ */}
        <section className="section modules" id="moduli">
          <div className="wrap">
            <div className="modules-head">
              <h2 className="h2 reveal">Quattro moduli pronti.<br />E il resto, <em>su misura</em>.</h2>
              <div className="eyebrow on-dark reveal d1" style={{ paddingBottom: 10 }}>La suite Finch</div>
            </div>

            {MODULES.map((m) => (
              <article className="mod reveal" key={m.name}>
                <div className="mod-num">{m.n}</div>
                <div className="mod-main">
                  <span className="mtag">{m.tag}</span>
                  <h3>{m.name}</h3>
                  <p>{m.desc}</p>
                  <Link to={m.href} onClick={() => window.scrollTo(0, 0)} className="btn-text" style={{ color: "var(--lime)", marginTop: 18 }}>
                    Scopri {m.name} <ArrowUpRight size={15} />
                  </Link>
                </div>
                <div className="mod-feats">
                  {m.feats.map((f) => (
                    <div className="mod-feat" key={f}><Tick />{f}</div>
                  ))}
                </div>
              </article>
            ))}
          </div>
        </section>

        {/* ============ TAILORED / SU MISURA ============ */}
        <section className="section tailored" id="sumisura">
          <div className="wrap tailored-inner">
            <div className="tailored-copy">
              <div className="eyebrow reveal" style={{ marginBottom: 22 }}>Soluzioni su misura</div>
              <h2 className="h2 reveal d1">I moduli sono<br />il punto di <em>partenza</em>.</h2>
              <p className="lead reveal d2" style={{ marginTop: 24 }}>
                Nessuna azienda è uguale a un'altra. Per questo Finch-AI non vende licenze e basta: progettiamo e sviluppiamo l'intelligenza artificiale attorno ai{" "}
                <strong>tuoi</strong> processi, ai tuoi documenti e ai tuoi obiettivi. Dai moduli pronti fino all'agente AI costruito da zero.
              </p>
              <button type="button" onClick={() => openContact({ prefill: { need: "Soluzione su misura" } })} className="btn btn-primary reveal d3" style={{ marginTop: 34 }}>
                Parliamo del tuo caso <ArrowUpRight size={16} />
              </button>
            </div>
            <div className="tailored-cards">
              <div className="tcard reveal d1">
                <span className="tnum">A</span>
                <h4>Consulenza &amp; assessment</h4>
                <p>Analizziamo processi e dati per capire dove l'AI genera valore reale, non sperimentazione.</p>
              </div>
              <div className="tcard reveal d2">
                <span className="tnum">B</span>
                <h4>Sviluppo su misura</h4>
                <p>Agenti e flussi costruiti sul tuo modo di lavorare, integrati con i sistemi che già usi.</p>
              </div>
              <div className="tcard reveal d3">
                <span className="tnum">C</span>
                <h4>Affiancamento continuo</h4>
                <p>Un team italiano che resta al tuo fianco: l'AI evolve insieme all'azienda.</p>
              </div>
            </div>
          </div>
        </section>

        {/* ============ PROCESS ============ */}
        <section className="section process">
          <div className="wrap">
            <div className="process-head">
              <div className="eyebrow center reveal">Il metodo Finch</div>
              <h2 className="h2 reveal d1">Dalla prima call all'AI<br />che lavora, in <em>tempi rapidi</em>.</h2>
            </div>
            <div className="steps reveal d1">
              <div className="step">
                <div className="sn"><span>01</span><span className="ar">→</span></div>
                <h4>Ascolto</h4>
                <p>Una call per capire processi, documenti e numeri della tua impresa. Niente questionari infiniti.</p>
              </div>
              <div className="step">
                <div className="sn"><span>02</span><span className="ar">→</span></div>
                <h4>Configurazione</h4>
                <p>Attiviamo i moduli giusti e li addestriamo sui tuoi dati reali, nel tuo ambiente sicuro.</p>
              </div>
              <div className="step">
                <div className="sn"><span>03</span><span className="ar">→</span></div>
                <h4>Avvio</h4>
                <p>In poco tempo l'AI è operativa. I primi flussi automatici girano da subito, senza attese.</p>
              </div>
              <div className="step">
                <div className="sn"><span>04</span><span className="ar">↗</span></div>
                <h4>Crescita</h4>
                <p>Finch impara dal tuo lavoro e migliora. Aggiungi moduli quando l'azienda è pronta.</p>
              </div>
            </div>
            <p className="process-foot reveal">Affiancamento continuo da un team <b>italiano</b> — non un ticket, una persona.</p>
          </div>
        </section>

        {/* ============ NEWS ============ */}
        <section className="section news" id="news">
          <div className="wrap">
            <div className="news-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 20 }}>News</div>
                <h2 className="h2 reveal d1">Cosa succede<br />in <em>Finch</em>.</h2>
              </div>
              <p className="lead reveal d2">Annunci, traguardi e novità sul prodotto. Una startup che cresce — e lo racconta.</p>
            </div>
            <div className="news-grid">
              <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer" className="news-card reveal">
                <div className="news-meta"><span className="nc">Riconoscimenti</span><span className="nd">Mag 2026</span></div>
                <h3>Finch-AI entra nel network Partner 24 Ore</h3>
                <p>La nostra visione sull'AI per le PMI italiane raccontata sullo speciale Partner 24 Ore, con video intervista.</p>
                <span className="news-go">Leggi <ArrowUpRight size={14} /></span>
              </a>
              <Link to="/soluzioni/synapse" onClick={() => window.scrollTo(0, 0)} className="news-card reveal d1">
                <div className="news-meta"><span className="nc">Prodotto</span><span className="nd">2026</span></div>
                <h3>Presentiamo Synapse, il cervello documentale dell'azienda</h3>
                <p>Da dati sparsi a un wiki vivo che si auto-mantiene: il nuovo modulo di Knowledge Intelligence, con risposte sempre citate alla fonte.</p>
                <span className="news-go">Scopri Synapse <ArrowUpRight size={14} /></span>
              </Link>
              <div className="news-card reveal d2">
                <div className="news-meta"><span className="nc">Ecosistema</span><span className="nd">2026</span></div>
                <h3>Insieme a Confindustria L'Aquila per l'AI nelle imprese</h3>
                <p>Portiamo l'intelligenza artificiale su misura nel tessuto delle PMI del territorio, accanto a Confindustria L'Aquila.</p>
                <span className="news-go muted">Presto online</span>
              </div>
            </div>
          </div>
        </section>

        {/* ============ PRESS ============ */}
        <section className="section press" id="stampa">
          <div className="wrap">
            <div className="press-head">
              <div className="eyebrow on-dark reveal" style={{ marginBottom: 20 }}>Dicono di noi</div>
              <h2 className="h2 reveal d1">La stampa che conta<br />parla di <em>Finch</em>.</h2>
            </div>
            <div className="press-grid">
              <div className="press-video reveal">
                <iframe
                  src="https://www.youtube.com/embed/6IZxDRKazQc"
                  title="Video intervista Partner 24 Ore — Finch-AI"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowFullScreen
                  loading="lazy"
                />
              </div>
              <div className="press-side">
                <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer" className="press-card reveal d1">
                  <div>
                    <span className="kick">Video intervista</span>
                    <div className="src">Partner <span className="it">24 Ore</span></div>
                    <p>Finch-AI nel network Partner 24 Ore: la nostra visione sull'AI per le PMI italiane.</p>
                  </div>
                  <span className="go">Leggi su Partner 24 Ore <ArrowUpRight size={14} /></span>
                </a>
                <div className="press-card soon reveal d2">
                  <div>
                    <span className="kick">In arrivo</span>
                    <div className="src">la <span className="it">Repubblica</span></div>
                    <p>L'articolo dedicato a Finch-AI sarà presto online. Lo trovi qui appena pubblicato.</p>
                  </div>
                  <span className="badge-soon">Articolo in pubblicazione</span>
                </div>
              </div>
            </div>
            <p className="video-cap reveal">▶ Intervista video — Partner 24 Ore · Speciale Partner</p>
          </div>
        </section>

        {/* ============ CLIENTS ============ */}
        <section className="section clients" id="clienti">
          <div className="wrap clients-inner">
            <div className="clients-copy">
              <div className="eyebrow reveal" style={{ marginBottom: 22 }}>I nostri clienti</div>
              <h2 className="h2 reveal d1">Imprese che già<br />lavorano con <em>Finch</em>.</h2>
              <p className="lead reveal d2" style={{ marginTop: 24 }}>
                Aziende italiane che hanno scelto di mettere l'intelligenza artificiale al centro dei propri processi — e di farlo con noi.
              </p>
            </div>
            <div className="client-card reveal d1">
              <p className="client-quote">
                <span className="mark">“</span>I primi case study dei nostri clienti saranno presto online. Stiamo raccogliendo i risultati delle imprese che già lavorano con Finch.<span className="mark">”</span>
              </p>
              <div className="client-meta">
                <div className="client-id">
                  <div className="client-logo">✦</div>
                  <div className="ci">
                    <b>Finch-AI</b>
                    <span>case study in arrivo</span>
                  </div>
                </div>
                <button type="button" onClick={() => openContact({ prefill: { need: "Caso studio" } })} className="btn-text">
                  Diventa un caso studio <ArrowUpRight size={15} />
                </button>
              </div>
            </div>
          </div>
        </section>

        {/* ============ CTA ============ */}
        <section className="section cta" id="contatti">
          <div className="wrap cta-inner">
            <h2 className="h2 reveal">Vediamo cosa può fare<br />l'AI per la <em>tua impresa</em>.</h2>
            <div className="cta-right reveal d1">
              <p>Prenota una demo di 30 minuti. Ti mostriamo Finch sui tuoi documenti e i tuoi numeri, senza impegno.</p>
              <div className="cta-actions">
                <button type="button" onClick={() => openContact({ prefill: { need: "Richiesta demo" } })} className="btn btn-primary">Prenota una demo <ArrowUpRight size={16} /></button>
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
