import { useEffect } from "react";
import { useTranslation } from "react-i18next";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import SEO from "@/components/SEO";
import { useContactModal } from "@/context/ContactModalContext";
import { useLocale } from "@/i18n/routing";
import "@/styles/synapse.css";
import {
  ArrowRight, Shield, Check, Clock, Building2, Scale, Stethoscope, Cog, Landmark,
  Files, Search, Users, FileText, Mail, MessageSquare, Database, Brain, TrendingUp,
  ClipboardCheck, PenTool, Ticket, LayoutGrid, Video, Globe, Lock, FileCheck, ShieldCheck,
  AlertTriangle,
} from "lucide-react";

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

const PROBLEM_ICONS = [Files, Search, Users];
const CAP_ICONS = [Brain, TrendingUp, ClipboardCheck, PenTool];
const SOURCE_ICONS = [FileText, Mail, MessageSquare, Database, Ticket, LayoutGrid, Video, Globe];
const ENT_ICONS = [Shield, Lock, FileCheck, ShieldCheck];
const IN_ICONS = [FileText, Mail, MessageSquare, Database];
const OUT_ICONS = [Brain, TrendingUp, ClipboardCheck, PenTool];

const flowNode = { display: "flex", gap: 12, alignItems: "center", background: "var(--card)", border: "1px solid var(--line)", borderRadius: 13, padding: "13px 15px" };
const flowNi = (grad) => ({ width: 34, height: 34, borderRadius: 9, flex: "none", display: "grid", placeItems: "center", color: "#fff", background: grad });

export default function Synapse() {
  useReveal();
  const { t } = useTranslation("solutions");
  const locale = useLocale();
  const { openContact } = useContactModal();
  const arr = (v) => (Array.isArray(v) ? v : []);

  const problems = t("synapse.problems", { returnObjects: true });
  const caps = t("synapse.caps", { returnObjects: true });
  const sources = t("synapse.sources", { returnObjects: true });
  const entFeats = t("synapse.enterprise.feats", { returnObjects: true });
  const deploys = t("synapse.deploys", { returnObjects: true });
  const inNodes = t("synapse.flow.inNodes", { returnObjects: true });
  const outNodes = t("synapse.flow.outNodes", { returnObjects: true });

  const jsonLd = [
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      name: "Synapse — Finch-AI",
      applicationCategory: "BusinessApplication",
      operatingSystem: "Web, On-premise",
      inLanguage: locale === "en" ? "en-US" : "it-IT",
      description: t("synapse.seo.ldDescription"),
      offers: { "@type": "Offer", availability: "https://schema.org/InStock" },
      publisher: { "@type": "Organization", name: "Finch-AI", url: "https://finch-ai.it" },
    },
  ];

  return (
    <>
      <SEO
        title={t("synapse.seo.title")}
        description={t("synapse.seo.description")}
        keywords={t("synapse.seo.keywords")}
        canonical="https://finch-ai.it/soluzioni/synapse"
        jsonLd={jsonLd}
      />
      <Navbar />

      <main id="top">
        {/* HERO */}
        <section className="syn-hero">
          <div className="hero-grid-bg" />
          <div className="wrap syn-hero-in">
            <div className="reveal in">
              <span className="syn-pill"><span className="ping" /> {t("synapse.hero.pill")}</span>
              <h1 dangerouslySetInnerHTML={{ __html: t("synapse.hero.title") }} />
              <p className="lead">{t("synapse.hero.lead")}</p>
              <div className="syn-hero-cta">
                <button type="button" onClick={() => openContact({ prefill: { need: t("synapse.hero.demoPrefill") } })} className="btn btn-primary">{t("synapse.hero.ctaDemo")} <ArrowRight size={16} /></button>
                <a href="#come-funziona" onClick={(e) => { e.preventDefault(); document.getElementById("come-funziona")?.scrollIntoView({ behavior: "smooth" }); }} className="btn btn-ghost">{t("synapse.hero.ctaHow")}</a>
              </div>
              <div className="syn-trust">
                <span className="dot"><Shield size={16} /> {t("synapse.hero.trust1")}</span>
                <span className="dot"><Check size={16} /> {t("synapse.hero.trust2")}</span>
                <span className="dot"><Clock size={16} /> {t("synapse.hero.trust3")}</span>
              </div>
            </div>
            <div className="reveal in" style={{ transitionDelay: ".12s" }}>
              <div className="syn-card">
                <div className="topbar"><i /><i /><i /><span className="lbl">{t("synapse.hero.cardLabel")}</span></div>
                <div className="syn-chat"><div className="av syn-av-user">MR</div><div className="syn-bub">{t("synapse.hero.chatUser")}</div></div>
                <div className="syn-chat"><div className="av syn-av-ai">S</div><div className="syn-bub">
                  <div className="syn-warn"><AlertTriangle size={15} /> {t("synapse.hero.chatWarn")}</div>
                  {t("synapse.hero.chatAnswerPre")} <strong>99,9%</strong><span className="syn-cite">{t("synapse.hero.chatCite1")}</span>{t("synapse.hero.chatAnswerMid")} <strong>99,5%</strong><span className="syn-cite">{t("synapse.hero.chatCite2")}</span>{t("synapse.hero.chatAnswerPost")}
                </div></div>
              </div>
            </div>
          </div>
        </section>

        {/* STRIP settori */}
        <div className="syn-strip">
          <div className="syn-strip-in">
            <span className="item"><Building2 size={17} /> {t("synapse.strip.banks")}</span>
            <span className="item"><Scale size={17} /> {t("synapse.strip.legal")}</span>
            <span className="item"><Stethoscope size={17} /> {t("synapse.strip.health")}</span>
            <span className="item"><Cog size={17} /> {t("synapse.strip.manufacturing")}</span>
            <span className="item"><Landmark size={17} /> {t("synapse.strip.pa")}</span>
          </div>
        </div>

        {/* PROBLEM */}
        <section className="section">
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">{t("synapse.problemHead.eyebrow")}</span>
              <h2 className="h2">{t("synapse.problemHead.title")}</h2>
              <p className="lead">{t("synapse.problemHead.lead")}</p>
            </div>
            <div className="syn-grid-3">
              {arr(problems).map((p, i) => {
                const Icon = PROBLEM_ICONS[i];
                return (
                  <div className="syn-cardbox reveal" key={p.title} style={{ transitionDelay: `${i * 0.1}s` }}>
                    <div className="syn-ic"><Icon size={22} /></div>
                    <h3>{p.title}</h3>
                    <p>{p.desc}</p>
                  </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* HOW IT WORKS */}
        <div id="come-funziona" style={{ background: "var(--paper-2)", borderTop: "1px solid var(--line)", borderBottom: "1px solid var(--line)" }}>
          <section className="section">
            <div className="wrap">
              <div className="syn-sec-head reveal">
                <span className="eyebrow">{t("synapse.howHead.eyebrow")}</span>
                <h2 className="h2">{t("synapse.howHead.title")}</h2>
                <p className="lead">{t("synapse.howHead.lead")}</p>
              </div>
              <div className="reveal syn-flowgrid">
                {/* INPUT */}
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  <div style={{ textAlign: "center", marginBottom: 6 }}>
                    <div style={{ fontFamily: "var(--mono)", fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--muted-2)" }}>{t("synapse.flow.inLabel")}</div>
                    <h3 className="h3" style={{ marginTop: 5 }}>{t("synapse.flow.inTitle")}</h3>
                  </div>
                  {arr(inNodes).map((node, idx) => {
                    const Ic = IN_ICONS[idx];
                    return (
                      <div style={flowNode} key={node.t}><div style={flowNi("linear-gradient(135deg,#0FA3C4,#13B58E)")}><Ic size={17} /></div><div><div style={{ fontSize: 13.5, fontWeight: 600 }}>{node.t}</div><div style={{ fontSize: 11.5, color: "var(--muted-2)" }}>{node.d}</div></div></div>
                    );
                  })}
                </div>
                <div className="flow-arrow" style={{ padding: "0 14px", color: "var(--green)" }}><ArrowRight size={26} /></div>
                {/* CORE */}
                <div style={{ display: "flex", flexDirection: "column", gap: 14, justifyContent: "center" }}>
                  <div style={{ textAlign: "center", marginBottom: 6 }}>
                    <div style={{ fontFamily: "var(--mono)", fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--muted-2)" }}>{t("synapse.flow.coreLabel")}</div>
                    <h3 className="h3" style={{ marginTop: 5 }}>{t("synapse.flow.coreTitle")}</h3>
                  </div>
                  <div style={{ background: "var(--brand-grad)", borderRadius: 16, padding: 2, boxShadow: "0 24px 60px -30px rgba(20,90,90,.4)" }}>
                    <div style={{ background: "var(--card)", borderRadius: 14, padding: "20px 16px", textAlign: "center" }}>
                      <div style={{ width: 46, height: 46, borderRadius: 12, background: "var(--brand-grad)", display: "grid", placeItems: "center", color: "#fff", margin: "0 auto 12px" }}><Brain size={24} /></div>
                      <div style={{ fontFamily: "var(--serif)", fontWeight: 500, fontSize: 16, marginBottom: 3 }}>{t("synapse.flow.coreCardTitle")}</div>
                      <div style={{ fontSize: 12, color: "var(--muted)", lineHeight: 1.5 }}>{t("synapse.flow.coreCardDesc")}</div>
                    </div>
                  </div>
                  <div style={flowNode}><div style={flowNi("linear-gradient(135deg,#13B58E,#2FB86A)")}><Check size={17} /></div><div><div style={{ fontSize: 13.5, fontWeight: 600 }}>{t("synapse.flow.coreAutoTitle")}</div><div style={{ fontSize: 11.5, color: "var(--muted-2)" }}>{t("synapse.flow.coreAutoDesc")}</div></div></div>
                </div>
                <div className="flow-arrow" style={{ padding: "0 14px", color: "var(--green)" }}><ArrowRight size={26} /></div>
                {/* OUTPUT */}
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  <div style={{ textAlign: "center", marginBottom: 6 }}>
                    <div style={{ fontFamily: "var(--mono)", fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--muted-2)" }}>{t("synapse.flow.outLabel")}</div>
                    <h3 className="h3" style={{ marginTop: 5 }}>{t("synapse.flow.outTitle")}</h3>
                  </div>
                  {arr(outNodes).map((node, idx) => {
                    const Ic = OUT_ICONS[idx];
                    return (
                      <div style={flowNode} key={node.t}><div style={flowNi("linear-gradient(135deg,#2FB86A,#52C53A)")}><Ic size={17} /></div><div><div style={{ fontSize: 13.5, fontWeight: 600 }}>{node.t}</div><div style={{ fontSize: 11.5, color: "var(--muted-2)" }}>{node.d}</div></div></div>
                    );
                  })}
                </div>
              </div>
            </div>
          </section>
        </div>

        {/* CAPABILITIES */}
        <section className="section" id="capacita">
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">{t("synapse.capsHead.eyebrow")}</span>
              <h2 className="h2">{t("synapse.capsHead.title")}</h2>
              <p className="lead">{t("synapse.capsHead.lead")}</p>
            </div>
            <div className="syn-cap-grid">
              {arr(caps).map((c, i) => {
                const Icon = CAP_ICONS[i];
                return (
                  <div className="syn-cap reveal" key={c.num} style={{ transitionDelay: `${i * 0.08}s` }}>
                    <div className="num">{c.num}</div>
                    <div className="syn-ic"><Icon size={26} /></div>
                    <h3>{c.title}</h3>
                    <p>{c.desc}</p>
                    <ul>
                      {arr(c.items).map((it) => (<li key={it}><Check size={16} strokeWidth={2.5} /> {it}</li>))}
                    </ul>
                  </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* SOURCES */}
        <section className="section" id="fonti" style={{ paddingTop: 0 }}>
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">{t("synapse.sourcesHead.eyebrow")}</span>
              <h2 className="h2">{t("synapse.sourcesHead.title")}</h2>
              <p className="lead">{t("synapse.sourcesHead.lead")}</p>
            </div>
            <div className="syn-src-grid reveal">
              {arr(sources).map((s, idx) => {
                const Icon = SOURCE_ICONS[idx];
                return (
                  <div className="syn-src" key={s.nm}>
                    <div className="si"><Icon size={20} /></div>
                    <div className="nm">{s.nm}</div>
                    <div className="ds">{s.ds}</div>
                  </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* ENTERPRISE / SECURITY */}
        <section className="section" id="sicurezza" style={{ paddingTop: 0 }}>
          <div className="wrap reveal">
            <div className="syn-ent">
              <div className="syn-ent-in">
                <div>
                  <span className="eyebrow on-dark" style={{ marginBottom: 16 }}>{t("synapse.enterprise.eyebrow")}</span>
                  <h2 dangerouslySetInnerHTML={{ __html: t("synapse.enterprise.title") }} />
                  <p>{t("synapse.enterprise.text")}</p>
                  <a href="#deploy" onClick={(e) => { e.preventDefault(); document.getElementById("deploy")?.scrollIntoView({ behavior: "smooth" }); }} className="btn btn-lime">{t("synapse.enterprise.cta")} <ArrowRight size={16} /></a>
                </div>
                <div className="syn-ent-feats">
                  {arr(entFeats).map((f, idx) => {
                    const Icon = ENT_ICONS[idx];
                    return (
                      <div className="syn-ent-feat" key={f.ft}>
                        <div className="fi"><Icon size={20} /></div>
                        <div><div className="ft">{f.ft}</div><div className="fd">{f.fd}</div></div>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* DEPLOY */}
        <section className="section" id="deploy" style={{ paddingTop: 0 }}>
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">{t("synapse.deployHead.eyebrow")}</span>
              <h2 className="h2">{t("synapse.deployHead.title")}</h2>
              <p className="lead">{t("synapse.deployHead.lead")}</p>
            </div>
            <div className="syn-dep-grid">
              {arr(deploys).map((d, i) => (
                <div className={`syn-dep${d.feat ? " feat" : ""} reveal`} key={d.title} style={{ transitionDelay: `${i * 0.1}s` }}>
                  <span className="tag">{d.tag}</span>
                  <h3>{d.title}</h3>
                  <p className="pr">{d.pr}</p>
                  <ul>
                    {arr(d.items).map((it) => (<li key={it}><Check size={15} strokeWidth={2.5} /> {it}</li>))}
                  </ul>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* FINAL CTA */}
        <section className="section" id="demo" style={{ paddingTop: 0 }}>
          <div className="wrap reveal">
            <div className="syn-cta-box">
              <h2>{t("synapse.cta.title")}</h2>
              <p>{t("synapse.cta.text")}</p>
              <div className="syn-cta-row">
                <button type="button" onClick={() => openContact({ prefill: { need: t("synapse.cta.demoPrefill") } })} className="btn btn-white">{t("synapse.cta.ctaDemo")} <ArrowRight size={16} /></button>
                <button type="button" onClick={() => openContact({ prefill: { need: t("synapse.cta.expertPrefill") } })} className="btn btn-line">{t("synapse.cta.ctaExpert")}</button>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </>
  );
}
