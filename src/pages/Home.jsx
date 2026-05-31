import { useEffect } from "react";
import { Link } from "react-router-dom";
import { ArrowUpRight, ArrowDown, Check } from "lucide-react";
import { useTranslation } from "react-i18next";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import SEO from "@/components/SEO";
import { useContactModal } from "@/context/ContactModalContext";
import { useLocale, useLocalizedPath } from "@/i18n/routing";

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

// Render a translated string that contains inline markup (<br>, <em>, <strong>…).
const Html = ({ as: Tag = "span", html, ...rest }) => (
  <Tag {...rest} dangerouslySetInnerHTML={{ __html: html }} />
);

const MODULE_LINKS = [
  { key: "omniflow", href: "/soluzioni/warehouse-intelligence", n: "01" },
  { key: "document", href: "/soluzioni/document-intelligence", n: "02" },
  { key: "finance", href: "/soluzioni/finance-intelligence", n: "03" },
  { key: "synapse", href: "/soluzioni/synapse", n: "04" },
  { key: "aps", href: "/soluzioni/aps", n: "05" },
];

export default function Home() {
  useReveal();
  const { t } = useTranslation("home");
  const locale = useLocale();
  const lp = useLocalizedPath();
  const { openContact } = useContactModal();

  const jsonLd = [
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      name: "Finch-AI",
      url: "https://finch-ai.it",
      logo: "https://finch-ai.it/favicon-512.png",
      description: t("seo.orgDescription"),
      sameAs: ["https://www.linkedin.com/company/finch-ai"],
    },
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      name: "Finch-AI",
      url: "https://finch-ai.it",
      inLanguage: locale === "en" ? "en-US" : "it-IT",
    },
  ];

  return (
    <>
      <SEO
        title={t("seo.title")}
        description={t("seo.description")}
        keywords={t("seo.keywords")}
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
              <div className="hero-tag eyebrow reveal">{t("hero.tag")}</div>
              <Html as="h1" className="display reveal d1" html={t("hero.title")} />
              <Html as="p" className="lead hero-sub reveal d2" html={t("hero.sub")} />
              <div className="hero-actions reveal d3">
                <button type="button" onClick={() => openContact({ prefill: { need: t("cta.demoPrefill") } })} className="btn btn-primary">
                  {t("hero.demoBtn")} <ArrowUpRight size={16} />
                </button>
                <a href={`${lp("/")}#moduli`} onClick={(e) => { e.preventDefault(); document.getElementById("moduli")?.scrollIntoView({ behavior: "smooth" }); }} className="btn btn-ghost">
                  {t("hero.exploreBtn")}
                </a>
              </div>
              <div className="hero-stats reveal d4">
                <div className="hero-stat"><div className="n">{t("hero.stat1n")}</div><div className="l">{t("hero.stat1l")}</div></div>
                <div className="hero-stat"><div className="n">{t("hero.stat2n")}</div><div className="l">{t("hero.stat2l")}</div></div>
              </div>
            </div>

            {/* infografica generale */}
            <div className="hero-visual reveal d2">
              <div className="flow">
                <div className="flow-head">
                  <span className="ft">{t("hero.flowTitle")}</span>
                  <span className="fb">{t("hero.flowBadge")}</span>
                </div>
                <div className="flow-stage">
                  <div className="flow-lab">{t("hero.flowStage1")}</div>
                  <div className="chips">
                    <span className="chip"><span className="ci" />{t("hero.flowChipDocs")}</span>
                    <span className="chip"><span className="ci" />{t("hero.flowChipProc")}</span>
                    <span className="chip"><span className="ci" />{t("hero.flowChipErp")}</span>
                    <span className="chip"><span className="ci" />{t("hero.flowChipEmail")}</span>
                  </div>
                </div>
                <div className="flow-arrow"><ArrowDown size={18} /></div>
                <div className="flow-core">
                  <div className="cn">{t("hero.flowCoreName")}</div>
                  <div className="cd"><span>{t("hero.flowCoreAdapt")}</span><span>{t("hero.flowCoreEvolve")}</span></div>
                  <div className="flow-float">{t("hero.flowFloat")}</div>
                </div>
                <div className="flow-arrow"><ArrowDown size={18} /></div>
                <div className="flow-stage">
                  <div className="flow-lab">{t("hero.flowStage3")}</div>
                  <div className="chips sols">
                    <span className="chip sol"><span className="ci" />OmniFlow</span>
                    <span className="chip sol"><span className="ci" />Document Intelligence</span>
                    <span className="chip sol"><span className="ci" />Finance Intelligence</span>
                    <span className="chip sol"><span className="ci" />Synapse</span>
                    <span className="chip sol"><span className="ci" />APS</span>
                    <span className="chip tailor">{t("hero.flowTailor")}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ============ PARTNER STRIP ============ */}
        <section className="partners" id="partner">
          <div className="partners-inner">
            <div className="partners-label">{t("partners.label")}</div>
            <div className="partners-list">
              <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer" className="partner link">
                <span className="pn">{t("partners.partner24Name")}</span>
                <span className="pr">{t("partners.partner24Role")}</span>
              </a>
              <div className="partner">
                <span className="pn">{t("partners.confindustriaName")}</span>
                <span className="pr">{t("partners.confindustriaRole")}</span>
              </div>
            </div>
          </div>
        </section>

        {/* ============ VALUE ============ */}
        <section className="section value" id="piattaforma">
          <div className="wrap">
            <div className="value-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 22 }}>{t("value.eyebrow")}</div>
                <Html as="h2" className="h2 reveal d1" html={t("value.title")} />
              </div>
              <p className="lead reveal d2">{t("value.lead")}</p>
            </div>
            <div className="value-grid reveal d1">
              <div className="value-cell">
                <span className="num">01</span>
                <h3 className="h3">{t("value.cell1Title")}</h3>
                <p>{t("value.cell1Text")}</p>
              </div>
              <div className="value-cell">
                <span className="num">02</span>
                <h3 className="h3">{t("value.cell2Title")}</h3>
                <p>{t("value.cell2Text")}</p>
              </div>
              <div className="value-cell">
                <span className="num">03</span>
                <h3 className="h3">{t("value.cell3Title")}</h3>
                <p>{t("value.cell3Text")}</p>
              </div>
            </div>
          </div>
        </section>

        {/* ============ MODULES ============ */}
        <section className="section modules" id="moduli">
          <div className="wrap">
            <div className="modules-head">
              <Html as="h2" className="h2 reveal" html={t("modules.title")} />
              <div className="eyebrow on-dark reveal d1" style={{ paddingBottom: 10 }}>{t("modules.eyebrow")}</div>
            </div>

            {MODULE_LINKS.map((m) => {
              const name = t(`modules.items.${m.key}.name`);
              const feats = t(`modules.items.${m.key}.feats`, { returnObjects: true });
              return (
                <article className="mod reveal" key={m.key}>
                  <div className="mod-num">{m.n}</div>
                  <div className="mod-main">
                    <span className="mtag">{t(`modules.items.${m.key}.tag`)}</span>
                    <h3>{name}</h3>
                    <p>{t(`modules.items.${m.key}.desc`)}</p>
                    <Link to={lp(m.href)} onClick={() => window.scrollTo(0, 0)} className="btn-text" style={{ color: "var(--lime)", marginTop: 18 }}>
                      {t("modules.discover", { name })} <ArrowUpRight size={15} />
                    </Link>
                  </div>
                  <div className="mod-feats">
                    {(Array.isArray(feats) ? feats : []).map((f) => (
                      <div className="mod-feat" key={f}><Tick />{f}</div>
                    ))}
                  </div>
                </article>
              );
            })}
          </div>
        </section>

        {/* ============ TAILORED / SU MISURA ============ */}
        <section className="section tailored" id="sumisura">
          <div className="wrap tailored-inner">
            <div className="tailored-copy">
              <div className="eyebrow reveal" style={{ marginBottom: 22 }}>{t("tailored.eyebrow")}</div>
              <Html as="h2" className="h2 reveal d1" html={t("tailored.title")} />
              <Html as="p" className="lead reveal d2" style={{ marginTop: 24 }} html={t("tailored.lead")} />
              <button type="button" onClick={() => openContact({ prefill: { need: t("tailored.ctaPrefill") } })} className="btn btn-primary reveal d3" style={{ marginTop: 34 }}>
                {t("tailored.cta")} <ArrowUpRight size={16} />
              </button>
            </div>
            <div className="tailored-cards">
              <div className="tcard reveal d1">
                <span className="tnum">A</span>
                <h4>{t("tailored.cardATitle")}</h4>
                <p>{t("tailored.cardAText")}</p>
              </div>
              <div className="tcard reveal d2">
                <span className="tnum">B</span>
                <h4>{t("tailored.cardBTitle")}</h4>
                <p>{t("tailored.cardBText")}</p>
              </div>
              <div className="tcard reveal d3">
                <span className="tnum">C</span>
                <h4>{t("tailored.cardCTitle")}</h4>
                <p>{t("tailored.cardCText")}</p>
              </div>
            </div>
          </div>
        </section>

        {/* ============ PROCESS ============ */}
        <section className="section process">
          <div className="wrap">
            <div className="process-head">
              <div className="eyebrow center reveal">{t("process.eyebrow")}</div>
              <Html as="h2" className="h2 reveal d1" html={t("process.title")} />
            </div>
            <div className="steps reveal d1">
              <div className="step">
                <div className="sn"><span>01</span><span className="ar">→</span></div>
                <h4>{t("process.step1Title")}</h4>
                <p>{t("process.step1Text")}</p>
              </div>
              <div className="step">
                <div className="sn"><span>02</span><span className="ar">→</span></div>
                <h4>{t("process.step2Title")}</h4>
                <p>{t("process.step2Text")}</p>
              </div>
              <div className="step">
                <div className="sn"><span>03</span><span className="ar">→</span></div>
                <h4>{t("process.step3Title")}</h4>
                <p>{t("process.step3Text")}</p>
              </div>
              <div className="step">
                <div className="sn"><span>04</span><span className="ar">↗</span></div>
                <h4>{t("process.step4Title")}</h4>
                <p>{t("process.step4Text")}</p>
              </div>
            </div>
            <Html as="p" className="process-foot reveal" html={t("process.foot")} />
          </div>
        </section>

        {/* ============ NEWS ============ */}
        <section className="section news" id="news">
          <div className="wrap">
            <div className="news-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 20 }}>{t("news.eyebrow")}</div>
                <Html as="h2" className="h2 reveal d1" html={t("news.title")} />
              </div>
              <p className="lead reveal d2">{t("news.lead")}</p>
            </div>
            <div className="news-grid">
              <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer" className="news-card reveal">
                <div className="news-meta"><span className="nc">{t("news.card1Cat")}</span><span className="nd">{t("news.card1Date")}</span></div>
                <h3>{t("news.card1Title")}</h3>
                <p>{t("news.card1Text")}</p>
                <span className="news-go">{t("news.card1Go")} <ArrowUpRight size={14} /></span>
              </a>
              <Link to={lp("/soluzioni/synapse")} onClick={() => window.scrollTo(0, 0)} className="news-card reveal d1">
                <div className="news-meta"><span className="nc">{t("news.card2Cat")}</span><span className="nd">{t("news.card2Date")}</span></div>
                <h3>{t("news.card2Title")}</h3>
                <p>{t("news.card2Text")}</p>
                <span className="news-go">{t("news.card2Go")} <ArrowUpRight size={14} /></span>
              </Link>
              <a href="https://www.confindustria.aq.it/imprese-associate" target="_blank" rel="noopener noreferrer" className="news-card reveal d2">
                <div className="news-meta"><span className="nc">{t("news.card3Cat")}</span><span className="nd">{t("news.card3Date")}</span></div>
                <h3>{t("news.card3Title")}</h3>
                <p>{t("news.card3Text")}</p>
                <span className="news-go">{t("news.card3Go")} <ArrowUpRight size={14} /></span>
              </a>
            </div>
          </div>
        </section>

        {/* ============ PRESS ============ */}
        <section className="section press" id="stampa">
          <div className="wrap">
            <div className="press-head">
              <div className="eyebrow on-dark reveal" style={{ marginBottom: 20 }}>{t("press.eyebrow")}</div>
              <Html as="h2" className="h2 reveal d1" html={t("press.title")} />
            </div>
            <div className="press-grid">
              <div className="press-video reveal">
                <iframe
                  src="https://www.youtube.com/embed/6IZxDRKazQc"
                  title={t("press.videoTitle")}
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowFullScreen
                  loading="lazy"
                />
              </div>
              <div className="press-side">
                <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer" className="press-card reveal d1">
                  <div>
                    <span className="kick">{t("press.card1Kick")}</span>
                    <div className="src">Partner <span className="it">24 Ore</span></div>
                    <p>{t("press.card1Text")}</p>
                  </div>
                  <span className="go">{t("press.card1Go")} <ArrowUpRight size={14} /></span>
                </a>
                <div className="press-card soon reveal d2">
                  <div>
                    <span className="kick">{t("press.card2Kick")}</span>
                    <div className="src">la <span className="it">Repubblica</span></div>
                    <p>{t("press.card2Text")}</p>
                  </div>
                  <span className="badge-soon">{t("press.card2Badge")}</span>
                </div>
              </div>
            </div>
            <p className="video-cap reveal">{t("press.videoCap")}</p>
          </div>
        </section>

        {/* ============ CLIENTS ============ */}
        <section className="section clients" id="clienti">
          <div className="wrap clients-inner">
            <div className="clients-copy">
              <div className="eyebrow reveal" style={{ marginBottom: 22 }}>{t("clients.eyebrow")}</div>
              <Html as="h2" className="h2 reveal d1" html={t("clients.title")} />
              <p className="lead reveal d2" style={{ marginTop: 24 }}>{t("clients.lead")}</p>
            </div>
            <div className="client-card reveal d1">
              <p className="client-quote">
                <span className="mark">“</span>{t("clients.quote")}<span className="mark">”</span>
              </p>
              <div className="client-meta">
                <div className="client-id">
                  <div className="client-logo">✦</div>
                  <div className="ci">
                    <b>{t("clients.metaName")}</b>
                    <span>{t("clients.metaRole")}</span>
                  </div>
                </div>
                <button type="button" onClick={() => openContact({ prefill: { need: t("clients.ctaPrefill") } })} className="btn-text">
                  {t("clients.cta")} <ArrowUpRight size={15} />
                </button>
              </div>
            </div>
          </div>
        </section>

        {/* ============ CTA ============ */}
        <section className="section cta" id="contatti">
          <div className="wrap cta-inner">
            <Html as="h2" className="h2 reveal" html={t("cta.title")} />
            <div className="cta-right reveal d1">
              <p>{t("cta.text")}</p>
              <div className="cta-actions">
                <button type="button" onClick={() => openContact({ prefill: { need: t("cta.demoPrefill") } })} className="btn btn-primary">{t("cta.demoBtn")} <ArrowUpRight size={16} /></button>
                <button type="button" onClick={() => openContact()} className="btn btn-ghost">{t("cta.writeBtn")}</button>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </>
  );
}
