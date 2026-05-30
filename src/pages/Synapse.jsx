import { useEffect } from "react";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import SEO from "@/components/SEO";
import { useContactModal } from "@/context/ContactModalContext";
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

const CAPS = [
  { num: "01", Icon: Brain, title: "Conoscenza", desc: "Chiedi in linguaggio naturale e ottieni risposte precise, con ogni affermazione citata fino alla riga esatta della fonte originale.", items: ["Ricerca semantica su tutto l'archivio", "Citazioni verificabili, niente allucinazioni", "Onboarding più rapido dei nuovi assunti"] },
  { num: "02", Icon: TrendingUp, title: "Supporto alle decisioni", desc: "Synapse confronta documenti, rileva contraddizioni e incoerenze, segnala rischi e ti dà il quadro completo prima che tu decida.", items: ["Rilevamento automatico di contraddizioni", "Alert proattivi su scadenze e rischi", "Confronti cross-documento istantanei"] },
  { num: "03", Icon: ClipboardCheck, title: "Valutazione documenti", desc: "Carica un contratto, una proposta, un dossier di due diligence: Synapse lo analizza contro le tue policy e i tuoi standard, ed evidenzia ciò che conta.", items: ["Gap analysis su normative e policy", "Estrazione di clausole e obblighi", "Red flag tracker per due diligence"] },
  { num: "04", Icon: PenTool, title: "Scrittura documenti", desc: "Genera bozze di report, sintesi, risposte e proposte già fondate sulla tua conoscenza aziendale — pronte da rivedere, non da scrivere da zero.", items: ["Report e sintesi con fonti integrate", "Bozze coerenti con il tuo stile aziendale", "Playbook ricorrenti automatizzati"] },
];

const SOURCES = [
  { Icon: FileText, nm: "SharePoint & Drive", ds: "File server, cloud storage" },
  { Icon: Mail, nm: "Email & Exchange", ds: "Outlook on-prem e cloud" },
  { Icon: MessageSquare, nm: "Teams & Slack", ds: "Conversazioni, trascrizioni" },
  { Icon: Database, nm: "ERP & CRM", ds: "Salesforce, gestionali, DB" },
  { Icon: Ticket, nm: "Ticketing", ds: "Zendesk, Jira, ServiceNow" },
  { Icon: LayoutGrid, nm: "Data room", ds: "Due diligence, contratti" },
  { Icon: Video, nm: "Audio & video", ds: "Call, registrazioni, ASR" },
  { Icon: Globe, nm: "Web & normativa", ds: "Gazzetta, siti, fonti pubbliche" },
];

const PROBLEMS = [
  { Icon: Files, title: "Documenti sparsi", desc: "Contratti su SharePoint, decisioni in email, know-how in conversazioni. Niente è collegato e tutto invecchia in silenzio." },
  { Icon: Search, title: "Ricerche che ripartono da zero", desc: "Ogni domanda costa ore: leggere gli stessi documenti, ricostruire lo stesso contesto, sperando di non perdere un dettaglio." },
  { Icon: Users, title: "Il know-how se ne va", desc: "Quando una persona chiave lascia l'azienda, porta con sé anni di esperienza che non è mai stata scritta da nessuna parte." },
];

const ENT_FEATS = [
  { Icon: Shield, ft: "On-premise", fd: "Deploy nel tuo cloud o data center" },
  { Icon: Lock, ft: "RBAC granulare", fd: "Permessi fino alla singola pagina" },
  { Icon: FileCheck, ft: "Audit log immutabile", fd: "Tracciabilità per SOC2 / ISO 27001" },
  { Icon: ShieldCheck, ft: "GDPR by design", fd: "Right-to-be-forgotten nativo" },
];

const DEPLOYS = [
  { tag: "Cloud", title: "SaaS gestito", pr: "Operativo in pochi giorni", feat: false, items: ["Zero installazione", "Connettori OAuth pronti", "Aggiornamenti automatici"] },
  { tag: "Più scelto · Enterprise", title: "Ibrido federato", pr: "Dati on-prem, gestione cloud", feat: true, items: ["I dati restano dentro l'azienda", "Connettore sicuro dietro firewall", "Modelli LLM a tua scelta"] },
  { tag: "On-premise", title: "Totalmente in casa", pr: "Per i vincoli più stringenti", feat: false, items: ["Deploy sul tuo Kubernetes", "Modelli AI on-premise", "Anche ambienti air-gapped"] },
];

const flowNode = { display: "flex", gap: 12, alignItems: "center", background: "var(--card)", border: "1px solid var(--line)", borderRadius: 13, padding: "13px 15px" };
const flowNi = (grad) => ({ width: 34, height: 34, borderRadius: 9, flex: "none", display: "grid", placeItems: "center", color: "#fff", background: grad });

export default function Synapse() {
  useReveal();
  const { openContact } = useContactModal();

  const jsonLd = [
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      name: "Synapse — Finch-AI",
      applicationCategory: "BusinessApplication",
      operatingSystem: "Web, On-premise",
      description: "Synapse di Finch-AI trasforma documenti, email, contratti e conversazioni sparse in un wiki vivo che si auto-mantiene, con risposte citate fino alla fonte.",
      offers: { "@type": "Offer", availability: "https://schema.org/InStock" },
      publisher: { "@type": "Organization", name: "Finch-AI", url: "https://finch-ai.it" },
    },
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      itemListElement: [
        { "@type": "ListItem", position: 1, name: "Home", item: "https://finch-ai.it/" },
        { "@type": "ListItem", position: 2, name: "Soluzioni", item: "https://finch-ai.it/#moduli" },
        { "@type": "ListItem", position: 3, name: "Synapse", item: "https://finch-ai.it/soluzioni/synapse" },
      ],
    },
  ];

  return (
    <>
      <SEO
        title="Synapse — Finch-AI | Il cervello documentale della tua azienda"
        description="Synapse di Finch-AI trasforma documenti, email, contratti e conversazioni sparse in un wiki vivo che si auto-mantiene. Conoscenza, supporto alle decisioni, valutazione e scrittura documenti per banche e aziende strutturate."
        keywords="Synapse, knowledge intelligence, wiki aziendale AI, knowledge graph, gestione documentale AI, enterprise AI, on-premise, RAG, citazioni verificabili"
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
              <span className="syn-pill"><span className="ping" /> Knowledge Intelligence · Enterprise</span>
              <h1>Il <em>cervello documentale</em> della tua azienda</h1>
              <p className="lead">
                Synapse trasforma documenti, contratti, email e conversazioni sparse in un wiki vivo che si auto-mantiene.
                La conoscenza aziendale smette di disperdersi: si accumula, si collega, e risponde — con ogni affermazione citata fino alla fonte originale.
              </p>
              <div className="syn-hero-cta">
                <button type="button" onClick={() => openContact({ prefill: { need: "Demo Synapse" } })} className="btn btn-primary">Richiedi una demo <ArrowRight size={16} /></button>
                <a href="#come-funziona" onClick={(e) => { e.preventDefault(); document.getElementById("come-funziona")?.scrollIntoView({ behavior: "smooth" }); }} className="btn btn-ghost">Scopri come funziona</a>
              </div>
              <div className="syn-trust">
                <span className="dot"><Shield size={16} /> Dati on-premise</span>
                <span className="dot"><Check size={16} /> Citazioni verificabili</span>
                <span className="dot"><Clock size={16} /> Sempre aggiornato</span>
              </div>
            </div>
            <div className="reveal in" style={{ transitionDelay: ".12s" }}>
              <div className="syn-card">
                <div className="topbar"><i /><i /><i /><span className="lbl">Chiedi a Synapse</span></div>
                <div className="syn-chat"><div className="av syn-av-user">MR</div><div className="syn-bub">Qual è l'SLA che stiamo offrendo a Beta SpA per il prodotto Plus?</div></div>
                <div className="syn-chat"><div className="av syn-av-ai">S</div><div className="syn-bub">
                  <div className="syn-warn"><AlertTriangle size={15} /> Ho trovato una discrepanza tra i documenti.</div>
                  La proposta commerciale indica <strong>99,9%</strong><span className="syn-cite">prop-Beta.pdf p.7</span>, ma la MSA nel data room riporta <strong>99,5%</strong><span className="syn-cite">MSA-rev3 art.9.2</span>. Suggerisco di allineare prima della firma.
                </div></div>
              </div>
            </div>
          </div>
        </section>

        {/* STRIP settori */}
        <div className="syn-strip">
          <div className="syn-strip-in">
            <span className="item"><Building2 size={17} /> Banche &amp; Assicurazioni</span>
            <span className="item"><Scale size={17} /> Studi legali</span>
            <span className="item"><Stethoscope size={17} /> Sanità</span>
            <span className="item"><Cog size={17} /> Manifatturiero</span>
            <span className="item"><Landmark size={17} /> PA &amp; Enti</span>
          </div>
        </div>

        {/* PROBLEM */}
        <section className="section">
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">Il problema</span>
              <h2 className="h2">La tua azienda sa più di quanto riesca a ricordare</h2>
              <p className="lead">Ogni giorno produci conoscenza preziosa. Ma si disperde in mille sistemi, e quando serve nessuno la ritrova.</p>
            </div>
            <div className="syn-grid-3">
              {PROBLEMS.map(({ Icon, title, desc }, i) => (
                <div className="syn-cardbox reveal" key={title} style={{ transitionDelay: `${i * 0.1}s` }}>
                  <div className="syn-ic"><Icon size={22} /></div>
                  <h3>{title}</h3>
                  <p>{desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* HOW IT WORKS */}
        <div id="come-funziona" style={{ background: "var(--paper-2)", borderTop: "1px solid var(--line)", borderBottom: "1px solid var(--line)" }}>
          <section className="section">
            <div className="wrap">
              <div className="syn-sec-head reveal">
                <span className="eyebrow">Come funziona</span>
                <h2 className="h2">Da dati sparsi a conoscenza che lavora per te</h2>
                <p className="lead">Synapse legge le tue fonti, le compila in un cervello documentale vivo, e produce valore concreto — non semplici risposte.</p>
              </div>
              <div className="reveal syn-flowgrid">
                {/* INPUT */}
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  <div style={{ textAlign: "center", marginBottom: 6 }}>
                    <div style={{ fontFamily: "var(--mono)", fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--muted-2)" }}>01 · Da dove prende i dati</div>
                    <h3 className="h3" style={{ marginTop: 5 }}>Le tue fonti</h3>
                  </div>
                  {[[FileText, "Documenti & contratti", "PDF, Word, scansioni, OCR"], [Mail, "Email & Office 365", "Outlook, SharePoint, Drive"], [MessageSquare, "Chat & call", "Teams, Slack, trascrizioni"], [Database, "Gestionali & CRM", "ERP, Salesforce, database"]].map(([Ic, t, d]) => (
                    <div style={flowNode} key={t}><div style={flowNi("linear-gradient(135deg,#0FA3C4,#13B58E)")}><Ic size={17} /></div><div><div style={{ fontSize: 13.5, fontWeight: 600 }}>{t}</div><div style={{ fontSize: 11.5, color: "var(--muted-2)" }}>{d}</div></div></div>
                  ))}
                </div>
                <div className="flow-arrow" style={{ padding: "0 14px", color: "var(--green)" }}><ArrowRight size={26} /></div>
                {/* CORE */}
                <div style={{ display: "flex", flexDirection: "column", gap: 14, justifyContent: "center" }}>
                  <div style={{ textAlign: "center", marginBottom: 6 }}>
                    <div style={{ fontFamily: "var(--mono)", fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--muted-2)" }}>02 · Cosa è</div>
                    <h3 className="h3" style={{ marginTop: 5 }}>Il cervello Synapse</h3>
                  </div>
                  <div style={{ background: "var(--brand-grad)", borderRadius: 16, padding: 2, boxShadow: "0 24px 60px -30px rgba(20,90,90,.4)" }}>
                    <div style={{ background: "var(--card)", borderRadius: 14, padding: "20px 16px", textAlign: "center" }}>
                      <div style={{ width: 46, height: 46, borderRadius: 12, background: "var(--brand-grad)", display: "grid", placeItems: "center", color: "#fff", margin: "0 auto 12px" }}><Brain size={24} /></div>
                      <div style={{ fontFamily: "var(--serif)", fontWeight: 500, fontSize: 16, marginBottom: 3 }}>Wiki vivo &amp; knowledge graph</div>
                      <div style={{ fontSize: 12, color: "var(--muted)", lineHeight: 1.5 }}>Agenti AI estraggono entità, collegano concetti, rilevano contraddizioni e mantengono tutto aggiornato — con citazioni verificabili su ogni affermazione.</div>
                    </div>
                  </div>
                  <div style={flowNode}><div style={flowNi("linear-gradient(135deg,#13B58E,#2FB86A)")}><Check size={17} /></div><div><div style={{ fontSize: 13.5, fontWeight: 600 }}>Auto-manutenzione</div><div style={{ fontSize: 11.5, color: "var(--muted-2)" }}>Si aggiorna ad ogni nuova fonte</div></div></div>
                </div>
                <div className="flow-arrow" style={{ padding: "0 14px", color: "var(--green)" }}><ArrowRight size={26} /></div>
                {/* OUTPUT */}
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  <div style={{ textAlign: "center", marginBottom: 6 }}>
                    <div style={{ fontFamily: "var(--mono)", fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--muted-2)" }}>03 · Cosa produce</div>
                    <h3 className="h3" style={{ marginTop: 5 }}>Valore concreto</h3>
                  </div>
                  {[[Brain, "Conoscenza", "Risposte istantanee citate"], [TrendingUp, "Supporto alle decisioni", "Confronti, rischi, alert"], [ClipboardCheck, "Valutazione documenti", "Due diligence, conformità"], [PenTool, "Scrittura documenti", "Bozze, report, sintesi"]].map(([Ic, t, d]) => (
                    <div style={flowNode} key={t}><div style={flowNi("linear-gradient(135deg,#2FB86A,#52C53A)")}><Ic size={17} /></div><div><div style={{ fontSize: 13.5, fontWeight: 600 }}>{t}</div><div style={{ fontSize: 11.5, color: "var(--muted-2)" }}>{d}</div></div></div>
                  ))}
                </div>
              </div>
            </div>
          </section>
        </div>

        {/* CAPABILITIES */}
        <section className="section" id="capacita">
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">Cosa produce</span>
              <h2 className="h2">Quattro modi in cui Synapse lavora per te</h2>
              <p className="lead">Non un chatbot che risponde e dimentica. Un sistema che accumula valore ogni giorno.</p>
            </div>
            <div className="syn-cap-grid">
              {CAPS.map(({ num, Icon, title, desc, items }, i) => (
                <div className="syn-cap reveal" key={num} style={{ transitionDelay: `${i * 0.08}s` }}>
                  <div className="num">{num}</div>
                  <div className="syn-ic"><Icon size={26} /></div>
                  <h3>{title}</h3>
                  <p>{desc}</p>
                  <ul>
                    {items.map((it) => (<li key={it}><Check size={16} strokeWidth={2.5} /> {it}</li>))}
                  </ul>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* SOURCES */}
        <section className="section" id="fonti" style={{ paddingTop: 0 }}>
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">Fonti dati</span>
              <h2 className="h2">Si connette dove vive già la tua conoscenza</h2>
              <p className="lead">Nessuna migrazione. Synapse legge le tue fonti dove sono — anche dietro il firewall aziendale.</p>
            </div>
            <div className="syn-src-grid reveal">
              {SOURCES.map(({ Icon, nm, ds }) => (
                <div className="syn-src" key={nm}>
                  <div className="si"><Icon size={20} /></div>
                  <div className="nm">{nm}</div>
                  <div className="ds">{ds}</div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* ENTERPRISE / SECURITY */}
        <section className="section" id="sicurezza" style={{ paddingTop: 0 }}>
          <div className="wrap reveal">
            <div className="syn-ent">
              <div className="syn-ent-in">
                <div>
                  <span className="eyebrow on-dark" style={{ marginBottom: 16 }}>Sicurezza enterprise</span>
                  <h2>I tuoi dati non escono mai dal tuo <em>perimetro</em></h2>
                  <p>Synapse è progettato per banche, assicurazioni, sanità e PA. Funziona dietro il tuo firewall, con i tuoi modelli, sotto il tuo controllo. Conformità non come opzione, ma come fondamenta.</p>
                  <a href="#deploy" onClick={(e) => { e.preventDefault(); document.getElementById("deploy")?.scrollIntoView({ behavior: "smooth" }); }} className="btn btn-lime">Scopri le opzioni di deploy <ArrowRight size={16} /></a>
                </div>
                <div className="syn-ent-feats">
                  {ENT_FEATS.map(({ Icon, ft, fd }) => (
                    <div className="syn-ent-feat" key={ft}>
                      <div className="fi"><Icon size={20} /></div>
                      <div><div className="ft">{ft}</div><div className="fd">{fd}</div></div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* DEPLOY */}
        <section className="section" id="deploy" style={{ paddingTop: 0 }}>
          <div className="wrap">
            <div className="syn-sec-head reveal">
              <span className="eyebrow">Distribuzione</span>
              <h2 className="h2">Dal cloud all'air-gapped, una sola piattaforma</h2>
              <p className="lead">Scegli il modello che rispetta i tuoi vincoli di compliance, senza compromessi sulle funzionalità.</p>
            </div>
            <div className="syn-dep-grid">
              {DEPLOYS.map(({ tag, title, pr, feat, items }, i) => (
                <div className={`syn-dep${feat ? " feat" : ""} reveal`} key={title} style={{ transitionDelay: `${i * 0.1}s` }}>
                  <span className="tag">{tag}</span>
                  <h3>{title}</h3>
                  <p className="pr">{pr}</p>
                  <ul>
                    {items.map((it) => (<li key={it}><Check size={15} strokeWidth={2.5} /> {it}</li>))}
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
              <h2>Trasforma la tua conoscenza in vantaggio competitivo</h2>
              <p>Scopri in una demo personalizzata come Synapse può dare voce ai documenti della tua azienda. Parliamo del tuo caso d'uso specifico.</p>
              <div className="syn-cta-row">
                <button type="button" onClick={() => openContact({ prefill: { need: "Demo Synapse" } })} className="btn btn-white">Richiedi una demo <ArrowRight size={16} /></button>
                <button type="button" onClick={() => openContact({ prefill: { need: "Synapse — parla con un esperto" } })} className="btn btn-line">Parla con un esperto</button>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </>
  );
}
