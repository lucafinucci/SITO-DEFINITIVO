import { useEffect } from "react";
import { useTranslation } from "react-i18next";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import SEO from "@/components/SEO";
import { useContactModal } from "@/context/ContactModalContext";
import { useLocale, useLocalizedPath } from "@/i18n/routing";
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

const FEATURE_ICONS = [Cpu, MessageSquare, GitBranch];
const AMBITI_ICONS = [Layers, Settings, GitBranch, FlaskConical];

export default function APS() {
  useReveal();
  const { t } = useTranslation("solutions");
  const locale = useLocale();
  const lp = useLocalizedPath();
  const { openContact } = useContactModal();
  const arr = (v) => (Array.isArray(v) ? v : []);

  const ambiti = t("aps.ambiti", { returnObjects: true });
  const features = t("aps.features", { returnObjects: true });
  const featRows = t("aps.featRows", { returnObjects: true });

  const jsonLd = [
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      name: "APS — Advanced Planning System | Finch-AI",
      applicationCategory: "BusinessApplication",
      operatingSystem: "Web",
      inLanguage: locale === "en" ? "en-US" : "it-IT",
      description: t("aps.seo.ldDescription"),
      offers: { "@type": "Offer", availability: "https://schema.org/InStock" },
      publisher: { "@type": "Organization", name: "Finch-AI", url: "https://finch-ai.it" },
    },
  ];

  return (
    <>
      <SEO
        title={t("aps.seo.title")}
        description={t("aps.seo.description")}
        keywords={t("aps.seo.keywords")}
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
              <div className="hero-tag eyebrow reveal">{t("aps.hero.tag")}</div>
              <h1 className="display reveal d1" style={{ fontSize: "clamp(40px,6vw,84px)" }}>
                {t("aps.hero.titlePre")} <span className="stroke">APS</span>
              </h1>
              <p className="lead hero-sub reveal d2" dangerouslySetInnerHTML={{ __html: t("aps.hero.lead") }} />
              <div className="hero-actions reveal d3">
                <button type="button" onClick={() => openContact({ prefill: { need: t("aps.hero.demoPrefill") } })} className="btn btn-primary">
                  {t("aps.hero.ctaDemo")} <ArrowUpRight size={16} />
                </button>
                <a href={`${lp("/")}#moduli`} className="btn btn-ghost">
                  {t("aps.hero.ctaAll")}
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
                <div className="eyebrow" style={{ justifyContent: "center", width: "100%", marginBottom: 18 }}>{t("aps.hero.ambitiLabel")}</div>
                <ul style={{ listStyle: "none", display: "flex", flexDirection: "column", gap: 12, padding: 0, margin: 0 }}>
                  {arr(ambiti).map((label, idx) => {
                    const Icon = AMBITI_ICONS[idx];
                    return (
                      <li key={label} style={{ display: "flex", gap: 12, alignItems: "center", fontSize: 15.5, color: "var(--ink)" }}>
                        <span style={{ width: 34, height: 34, borderRadius: 9, flex: "none", display: "grid", placeItems: "center", background: "rgba(14,158,146,.1)", color: "var(--green)" }}>
                          <Icon size={17} />
                        </span>
                        {label}
                      </li>
                    );
                  })}
                </ul>
              </div>
            </div>
          </div>
        </section>

        {/* FEATURES (dark, come la sezione moduli) */}
        <section className="section modules">
          <div className="wrap">
            <div className="modules-head">
              <h2 className="h2 reveal" dangerouslySetInnerHTML={{ __html: t("aps.featuresHead.title") }} />
              <div className="eyebrow on-dark reveal d1" style={{ paddingBottom: 10 }}>{t("aps.featuresHead.eyebrow")}</div>
            </div>

            {arr(features).map((f, i) => (
              <article className="mod reveal" key={f.title}>
                <div className="mod-num">{String(i + 1).padStart(2, "0")}</div>
                <div className="mod-main">
                  <h3>{f.title}</h3>
                  <p style={{ marginTop: 8 }}>{f.desc}</p>
                </div>
                <div className="mod-feats">
                  {arr(featRows).map((row) => (
                    <div className="mod-feat" key={row}><Tick />{row}</div>
                  ))}
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
                <div className="eyebrow reveal" style={{ marginBottom: 22 }}>{t("aps.valueHead.eyebrow")}</div>
                <h2 className="h2 reveal d1" dangerouslySetInnerHTML={{ __html: t("aps.valueHead.title") }} />
              </div>
              <p className="lead reveal d2">{t("aps.valueHead.lead")}</p>
            </div>
            <div className="value-grid reveal d1">
              {arr(ambiti).map((label, i) => (
                <div className="value-cell" key={label}>
                  <span className="num">{String(i + 1).padStart(2, "0")}</span>
                  <h3 className="h3" style={{ fontSize: 21 }}>{label}</h3>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* CTA */}
        <section className="section cta" id="contatti">
          <div className="wrap cta-inner">
            <h2 className="h2 reveal" dangerouslySetInnerHTML={{ __html: t("aps.cta.title") }} />
            <div className="cta-right reveal d1">
              <p>{t("aps.cta.text")}</p>
              <div className="cta-actions">
                <button type="button" onClick={() => openContact({ prefill: { need: t("aps.cta.demoPrefill") } })} className="btn btn-primary">{t("aps.cta.ctaDemo")} <ArrowUpRight size={16} /></button>
                <button type="button" onClick={() => openContact()} className="btn btn-ghost">{t("aps.cta.ctaWrite")}</button>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </>
  );
}
