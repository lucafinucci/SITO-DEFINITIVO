import React, { useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import SEO from '@/components/SEO';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import { useContactModal } from '@/context/ContactModalContext';
import '@/styles/synapse.css';
import '@/styles/solutions.css';
import {
    ArrowRight,
    ArrowDown,
    Upload,
    Brain,
    MessageSquare,
    FileText,
    BarChart3,
    Globe,
    Lock,
    Trash2,
    Mail,
    Server,
    ShieldCheck,
    Scale,
    PlayCircle,
    Play,
    LineChart,
    FileBarChart2,
    CheckCircle,
    Check,
    Minus,
} from 'lucide-react';

const YOUTUBE_URL = 'https://www.youtube.com/@Finch-AI';
const APP_URL = 'https://bi.finch-ai.it';

function useReveal() {
    useEffect(() => {
        const els = document.querySelectorAll('.reveal');
        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }),
            { threshold: 0.1 }
        );
        els.forEach((el) => io.observe(el));
        return () => io.disconnect();
    }, []);
}

const workflow = [
    { icon: Upload, h: 'Carichi l\'Excel', s: 'Conto economico' },
    { icon: Brain, h: 'L\'AI analizza', s: 'Riclassificazione OIC + indici' },
    { icon: FileBarChart2, h: 'Report OIC', s: 'PDF professionale' },
    { icon: MessageSquare, h: 'Chat sui numeri', s: 'In linguaggio naturale' },
];

const steps = [
    { icon: Upload, k: '01', h: 'Carica il tuo Excel', p: 'Trascina il Conto Economico. Accettiamo qualsiasi formato: il sistema riconosce la struttura.' },
    { icon: Brain, k: '02', h: 'L\'AI analizza tutto', p: 'Riclassificazione OIC, indici finanziari, analisi dei margini e trend anno su anno. Tutto automatico.' },
    { icon: MessageSquare, k: '03', h: 'Parla con i tuoi numeri', p: 'Scarichi il report PDF, poi chiedi all\'AI: "Qual è il mio margine operativo?" "Dove sto perdendo margine?"' },
];

const features = [
    { icon: FileBarChart2, title: 'Report OIC professionale', desc: 'Riclassificazione conforme agli standard OIC e all\'Art. 2425 C.C. Pronto per la banca, l\'investitore o il CdA.' },
    { icon: BarChart3, title: 'Indici finanziari completi', desc: 'ROE, ROI, ROS, EBITDA margin, liquidità, solidità patrimoniale e Z-Score di Altman — calcolati e spiegati in italiano.' },
    { icon: MessageSquare, title: 'Chat AI in italiano', desc: 'Domande sui tuoi dati in linguaggio naturale: l\'AI capisce il contesto e risponde con i numeri della tua azienda.' },
    { icon: LineChart, title: 'Confronto pluriennale', desc: 'Carica più anni e visualizza i trend. L\'AI identifica le variazioni significative e segnala le criticità.' },
    { icon: FileText, title: 'Export PDF professionale', desc: 'Report con grafici, tabelle e commenti dell\'AI. Pronto da condividere senza ulteriori modifiche.' },
    { icon: Globe, title: '100% italiano', desc: 'Terminologia contabile italiana, normativa OIC, standard del Codice Civile. Costruito per il mercato italiano.' },
];

const privacy = [
    { icon: Upload, title: 'L\'Excel non viene archiviato', desc: 'Il file è elaborato in memoria per generare il report. Non viene salvato sui nostri server.' },
    { icon: Trash2, title: 'Cancellazione automatica', desc: 'Dopo il download del report e la fine della sessione, i dati dell\'analisi vengono eliminati.' },
    { icon: Mail, title: 'Conserviamo solo la tua email', desc: 'L\'unico dato che teniamo è l\'indirizzo email, per ricontattarti. Nessun dato finanziario.' },
    { icon: Server, title: 'GDPR · server in EU', desc: 'Infrastruttura europea conforme al GDPR. I dati non lasciano mai l\'Unione Europea.' },
];

const plans = [
    { name: 'Demo', price: 'Gratis', per: 'prova', feat: false, btn: 'Inizia gratis' },
    { name: 'Starter', price: '€49', per: '/mese', feat: false, btn: 'Starter' },
    { name: 'Professional', price: '€99', per: '/mese', feat: true, btn: 'Professional' },
    { name: 'Business', price: '€299', per: '/mese', feat: false, btn: 'Business' },
    { name: 'Enterprise', price: 'Su misura', per: '', feat: false, btn: 'Contattaci' },
];

// righe della tabella: valori per [Demo, Starter, Professional, Business, Enterprise]
const rows = [
    { label: 'Report / mese', vals: ['2', '5', '10', '20', 'Illimitati'] },
    { label: 'Indici finanziari', vals: ['6', '6–8', '15+', '20+', 'Custom'] },
    { label: 'Riclassificazione', vals: [null, null, 'OIC completa', 'OIC + custom', 'Tutto custom'] },
    { label: 'Analisi trend', vals: [null, null, 'Multi-anno', '+ proiezioni', '+ scenari'] },
    { label: 'Chat AI', vals: ['5 domande', '30/mese', '60/mese', 'Illimitate', 'Illimitate'] },
    { label: 'Export PDF', vals: [null, 'Base', 'Professionale', 'White-label', 'Full custom'] },
    { label: 'Utenti', vals: ['1', '1', '3', '5', '30+'] },
    { label: 'Scenari What-If', vals: [false, false, false, true, true] },
    { label: 'Integrazione ERP / API', vals: [false, false, false, false, true] },
    { label: 'SSO / SAML', vals: [false, false, false, false, true] },
    { label: 'SLA garantito', vals: [null, null, null, null, '99,5%'] },
    { label: 'Supporto', vals: ['Self-service', 'Email 48h', 'Email 24h', 'Prioritario', 'Dedicato + AM'] },
];

const renderVal = (v) => {
    if (v === true) return <Check />;
    if (v === false || v === null) return <Minus className="mut" />;
    return v;
};

const faqsForLd = [
    { q: 'Il mio bilancio è al sicuro?', a: 'Sì. Il file Excel è elaborato in memoria, non viene archiviato sui nostri server e i dati dell\'analisi vengono cancellati al termine della sessione. Conserviamo solo il tuo indirizzo email. Infrastruttura europea conforme al GDPR.' },
    { q: 'Il report è conforme alla normativa italiana?', a: 'Sì: la riclassificazione segue gli standard OIC e l\'Art. 2425 del Codice Civile, con terminologia contabile italiana.' },
    { q: 'Quanto costa?', a: 'Si parte dal piano Starter a €49/mese. È disponibile una Demo gratuita. I piani Professional (€99/mese) e Business (€299/mese) ampliano report, indici e funzioni; per esigenze enterprise c\'è un\'offerta su misura.' },
];

const financeJsonLd = [
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Finch-AI Finance Intelligence",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "FinancialApplication",
        "operatingSystem": "Web",
        "url": "https://finch-ai.it/soluzioni/finance-intelligence",
        "image": "https://finch-ai.it/assets/images/og-image.png",
        "description": "Analisi finanziaria automatica basata su AI per PMI e commercialisti. Dal file Excel al report OIC professionale, con indici finanziari, riclassificazione OIC (Art. 2425 C.C.), analisi Z-Score Altman e chat AI in linguaggio naturale.",
        "featureList": [
            "Riclassificazione bilancio OIC Art. 2425 C.C.",
            "Indici finanziari: ROE, ROI, ROS, EBITDA, liquidità, Z-Score Altman",
            "Report professionale PDF da Excel",
            "Chat AI in linguaggio naturale sui dati finanziari",
            "Analisi trend multi-anno",
            "Scenario what-if e simulazioni",
            "GDPR compliant, dati in Italia"
        ],
        "offers": [
            { "@type": "Offer", "name": "Demo", "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
            { "@type": "Offer", "name": "Starter", "price": "49", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "49", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" },
            { "@type": "Offer", "name": "Professional", "price": "99", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "99", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" },
            { "@type": "Offer", "name": "Business", "price": "299", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "299", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" }
        ],
        "provider": { "@type": "Organization", "name": "Finch-AI S.r.l.", "url": "https://finch-ai.it" }
    },
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://finch-ai.it/" },
            { "@type": "ListItem", "position": 2, "name": "Soluzioni", "item": "https://finch-ai.it/soluzioni/" },
            { "@type": "ListItem", "position": 3, "name": "Finance Intelligence", "item": "https://finch-ai.it/soluzioni/finance-intelligence" }
        ]
    },
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": faqsForLd.map((f) => ({ "@type": "Question", "name": f.q, "acceptedAnswer": { "@type": "Answer", "text": f.a } }))
    }
];

export default function FinanceIntelligence() {
    useReveal();
    const { openContact } = useContactModal();
    const videoRef = useRef(null);

    useEffect(() => { window.scrollTo(0, 0); }, []);

    const handleWatchVideo = (e) => {
        e.preventDefault();
        const v = videoRef.current;
        if (v) {
            v.scrollIntoView({ behavior: 'smooth', block: 'center' });
            v.play().catch(() => {});
        }
    };

    return (
        <>
            <SEO
                title="Finance Intelligence | Analisi Bilancio AI per PMI — Finch-AI"
                description="Dal bilancio Excel al report OIC professionale. Indici finanziari (ROE, ROI, EBITDA, Z-Score Altman), riclassificazione automatica, chat AI sui dati. Da €49/mese."
                keywords="analisi finanziaria AI, report bilancio automatico, software analisi bilancio PMI, indici finanziari ROE ROI EBITDA, riclassificazione OIC automatica, Z-Score Altman, cash flow AI, software CFO PMI, Finance Intelligence, commercialisti AI, bilancio OIC art 2425"
                canonical="https://finch-ai.it/soluzioni/finance-intelligence"
                jsonLd={financeJsonLd}
            />
            <Navbar />

            <main className="sol-main">

                {/* HERO */}
                <section className="syn-hero">
                    <div className="hero-grid-bg" />
                    <div className="wrap syn-hero-in">
                        <div className="reveal in">
                            <span className="syn-pill"><span className="ping" /> Finance Intelligence · AI finanziario per PMI</span>
                            <h1>Il tuo bilancio parla. <em>Ora puoi ascoltarlo.</em></h1>
                            <p className="lead">Carica il Conto Economico in Excel e ottieni un report OIC professionale, indici finanziari e trend — con un assistente AI che risponde alle tue domande sui numeri.</p>
                            <div className="syn-hero-cta">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-primary"><Upload size={16} /> Prova gratis</a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-ghost"><PlayCircle size={16} /> Scopri come funziona</a>
                            </div>
                            <div className="syn-trust">
                                <span className="dot"><Scale size={16} /> Conforme OIC · Art. 2425 C.C.</span>
                                <span className="dot"><Lock size={16} /> GDPR · Dati in EU</span>
                                <span className="dot"><Trash2 size={16} /> Dati cancellati dopo il test</span>
                            </div>
                        </div>
                        <div className="reveal in" style={{ transitionDelay: '.12s' }}>
                            <div className="sol-viz">
                                <div className="sol-viz-head"><span className="t">Dal file al dialogo</span><span className="b">Workflow</span></div>
                                <div className="sol-ring">
                                    {workflow.map((s, idx) => (
                                        <React.Fragment key={s.h}>
                                            <div className="sol-rnode"><span className="ri"><s.icon size={18} /></span><span><span className="rl">{s.h}</span><span className="rs">{s.s}</span></span></div>
                                            {idx < workflow.length - 1 && <div className="sol-rarrow"><ArrowDown size={16} /></div>}
                                        </React.Fragment>
                                    ))}
                                </div>
                                <div className="sol-loop"><MessageSquare size={14} /> Parla con il tuo bilancio</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* VIDEO */}
                <section className="section" id="video" style={{ paddingTop: 'clamp(40px,5vw,70px)', paddingBottom: 'clamp(40px,5vw,70px)' }}>
                    <div className="wrap reveal" style={{ maxWidth: 980 }}>
                        <div className="sol-video">
                            <video ref={videoRef} controls preload="metadata" poster="">
                                <source src="/Analista_Finanziario_Clip_01.mp4" type="video/mp4" />
                                Il tuo browser non supporta il tag video.
                            </video>
                            <div className="cap"><span className="pulse" /> Demo · Analista Finanziario AI</div>
                        </div>
                        <p style={{ textAlign: 'center', fontFamily: 'var(--mono)', fontSize: 12, color: 'var(--muted-2)', marginTop: 16 }}>
                            <a href="#video" onClick={handleWatchVideo} style={{ color: 'var(--green-deep)', fontWeight: 600 }}><Play size={12} style={{ verticalAlign: 'middle', marginRight: 5 }} />Guarda come l'AI analizza un bilancio e risponde in linguaggio naturale.</a>
                        </p>
                    </div>
                </section>

                {/* COME FUNZIONA */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Come funziona</span>
                            <h2 className="h2">Dal tuo Excel al report, in tre passi</h2>
                            <p className="lead">Nessuna configurazione, nessuna formazione. Funziona dal primo minuto.</p>
                        </div>
                        <div className="sol-flow reveal">
                            {steps.map((s, idx) => (
                                <React.Fragment key={s.h}>
                                    <div className="sol-step"><span className="si"><s.icon size={22} /></span><span className="sk">{s.k}</span><h4>{s.h}</h4><p>{s.p}</p></div>
                                    {idx < steps.length - 1 && <div className="sol-arrow"><ArrowRight /></div>}
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                </section>

                {/* FUNZIONALITÀ */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Funzionalità</span>
                            <h2 className="h2">Tutto quello che un CFO vorrebbe</h2>
                            <p className="lead">Pensato per chi gestisce un'impresa, non per chi fa il commercialista.</p>
                        </div>
                        <div className="syn-cap-grid reveal">
                            {features.map((f) => (
                                <div className="syn-cap" key={f.title}>
                                    <div className="syn-ic"><f.icon size={24} /></div>
                                    <h3>{f.title}</h3>
                                    <p>{f.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* PRIVACY */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">I tuoi dati, le tue regole</span>
                            <h2 className="h2">Prova senza pensieri. Cancellazione totale.</h2>
                            <p className="lead">Sappiamo che i dati finanziari sono sensibili. Per questo il sistema è progettato con la privacy al centro.</p>
                        </div>
                        <div className="syn-ent reveal">
                            <div style={{ position: 'relative', zIndex: 1 }}>
                                <div className="sol-priv-head">
                                    <div className="ph"><ShieldCheck size={24} /></div>
                                    <div><div className="pn">La nostra promessa sui dati</div><div className="ps">Trasparenza totale su cosa succede con le tue informazioni</div></div>
                                </div>
                                <div className="sol-priv-grid">
                                    {privacy.map((p) => (
                                        <div className="sol-priv" key={p.title}><span className="pi"><p.icon size={18} /></span><div><div className="pt">{p.title}</div><div className="pd">{p.desc}</div></div></div>
                                    ))}
                                </div>
                                <div className="sol-priv-note"><CheckCircle size={18} /> In sintesi: carica, analizza, scarica il report. Poi tutto sparisce. Rimane solo la tua email.</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* PRICING */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Prezzi</span>
                            <h2 className="h2">Meno di un caffè al giorno</h2>
                            <p className="lead">Scegli il piano giusto per la tua impresa. Puoi cambiare o disdire in qualsiasi momento.</p>
                        </div>

                        {/* tabella desktop */}
                        <div className="sol-tablewrap reveal">
                            <table className="sol-table">
                                <thead>
                                    <tr>
                                        <th>Funzionalità</th>
                                        {plans.map((p) => (
                                            <th key={p.name} className={p.feat ? 'feat-col' : undefined}>
                                                {p.feat && <div className="pop">Consigliato</div>}
                                                <div className="nm">{p.name}</div>
                                                <div className="pr" style={p.price === 'Su misura' ? { fontSize: 18 } : undefined}>{p.price}</div>
                                                <div className="per">{p.per || ' '}</div>
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.map((r) => (
                                        <tr key={r.label}>
                                            <th>{r.label}</th>
                                            {r.vals.map((v, idx) => (
                                                <td key={idx} className={plans[idx].feat ? 'feat-col' : undefined}>{renderVal(v)}</td>
                                            ))}
                                        </tr>
                                    ))}
                                    <tr>
                                        <td />
                                        {plans.map((p) => (
                                            <td key={p.name} className={p.feat ? 'feat-col' : undefined}>
                                                <a href={APP_URL} target="_blank" rel="noopener" className={`btn ${p.feat ? 'btn-primary' : 'btn-ghost'}`} style={{ padding: '9px 16px', fontSize: 13 }}>{p.btn}</a>
                                            </td>
                                        ))}
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {/* cards mobile */}
                        <div className="sol-pcards reveal">
                            {plans.map((p, pi) => (
                                <div className={`sol-plan${p.feat ? ' feat' : ''}`} key={p.name}>
                                    {p.feat && <span className="pop">Consigliato</span>}
                                    <div className="nm">{p.name}</div>
                                    <div className="pr" style={p.price === 'Su misura' ? { fontSize: 24 } : undefined}>{p.price}</div>
                                    <div className="per">{p.per || ' '}</div>
                                    <ul>
                                        {rows.map((r) => {
                                            const v = r.vals[pi];
                                            if (v === false || v === null) return null;
                                            return <li key={r.label}><span>{r.label}</span>{v === true ? <Check /> : <b>{v}</b>}</li>;
                                        })}
                                    </ul>
                                    <a href={APP_URL} target="_blank" rel="noopener" className={`btn ${p.feat ? 'btn-primary' : 'btn-ghost'}`}>{p.btn}</a>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ALTRE SOLUZIONI */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Le altre soluzioni Finch-AI</span>
                            <h2 className="h2">Completa il tuo stack AI</h2>
                        </div>
                        <div className="sol-rel reveal">
                            <Link className="sol-relcard" to="/soluzioni/document-intelligence">
                                <span className="tag">Document Intelligence</span>
                                <h3>Estrai i dati da fatture, DDT e ricevute</h3>
                                <p>AI pronta all'uso per i documenti aziendali, con app Android per gli operatori in campo e integrazione ERP.</p>
                                <span className="go">Scopri Document Intelligence <ArrowRight size={14} /></span>
                            </Link>
                            <Link className="sol-relcard" to="/soluzioni/warehouse-intelligence">
                                <span className="tag">OmniFlow · Warehouse</span>
                                <h3>Il gestionale AI per tutto il ciclo commerciale</h3>
                                <p>Acquisti, magazzino, vendite, ordini e finanza in un unico sistema con AI integrata.</p>
                                <span className="go">Scopri OmniFlow <ArrowRight size={14} /></span>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap reveal">
                        <div className="syn-cta-box">
                            <h2>Prova adesso. Gratis. Senza impegno.</h2>
                            <p>Carica il tuo Conto Economico e scopri cosa l'AI vede nei tuoi numeri. Basta un file Excel.</p>
                            <div className="syn-cta-row">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-white"><Upload size={16} /> Prova gratis</a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-line"><PlayCircle size={16} /> Scopri come funziona</a>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

            <Footer />
        </>
    );
}
