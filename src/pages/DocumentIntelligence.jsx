import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import SEO from '@/components/SEO';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import VideoModal from '@/components/VideoModal';
import { useContactModal } from '@/context/ContactModalContext';
import '@/styles/synapse.css';
import '@/styles/solutions.css';
import {
    ArrowRight,
    ArrowDown,
    FileText,
    Download,
    CheckCircle,
    Cpu,
    CheckSquare,
    ShieldCheck,
    PlugZap,
    Zap,
    PlayCircle,
    Sparkles,
    PlusCircle,
    Calculator,
    Tag,
    Layers,
    Camera,
    Wifi,
    Battery,
    ReceiptText,
    Receipt,
    IdCard,
    Truck,
    Landmark,
    ShoppingCart,
    BadgeMinus,
    HeartPulse,
    Contact,
    UserCheck,
    ScanLine,
    ChevronDown,
    Check,
    Minus,
} from 'lucide-react';

const YOUTUBE_URL = 'https://www.youtube.com/@Finch-AI';
const APP_URL = 'https://documentintelligence.finch-ai.it/';

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

const pipeline = [
    { icon: Download, k: '01', h: 'Ricezione', p: 'Scansione, upload manuale o ricezione automatica via email.' },
    { icon: Cpu, k: '02', h: 'Riconoscimento AI', p: 'L\'AI estrae automaticamente campi e tabelle dal documento.' },
    { icon: CheckSquare, k: '03', h: 'Verifica', p: 'L\'operatore rivede i dati e conferma. Nessun errore raggiunge il gestionale senza approvazione.' },
    { icon: PlugZap, k: '04', h: 'Trasferimento', p: 'I dati verificati arrivano nel gestionale via API o webhook.' },
];

const docTypes = [
    { icon: ReceiptText, tag: 'Contabilità fornitori', title: 'Fatture', desc: 'Fornitore, P.IVA, importo, IVA, scadenza e righe. Elettroniche o PDF scansionati.' },
    { icon: Receipt, tag: 'Note spese', title: 'Ricevute e scontrini', desc: 'Rimborsi dipendenti e riconciliazione spese: data, importo, categoria, punto vendita.' },
    { icon: IdCard, tag: 'KYC · Onboarding', title: 'Documenti d\'identità', desc: 'Carta d\'identità, passaporto, patente. Verifica rapida per banche e noleggi.' },
    { icon: Truck, tag: 'Logistica', title: 'DDT e bolle', desc: 'Ricezione merci, codici articolo, quantità e aggiornamento scorte verso il WMS.' },
    { icon: Landmark, tag: 'Riconciliazione', title: 'Estratti conto', desc: 'Movimenti bancari con classificazione automatica e abbinamento partite aperte.' },
    { icon: ShoppingCart, tag: 'Approvvigionamenti', title: 'Ordini d\'acquisto', desc: 'Registrazione ordini fornitori e confronto con i DDT di consegna.' },
    { icon: BadgeMinus, tag: 'Gestione partite', title: 'Note di credito', desc: 'Storno fatture e collegamento automatico alla fattura originale.' },
    { icon: HeartPulse, tag: 'Sanità', title: 'Tessera sanitaria', desc: 'Codice fiscale, nome e data di nascita per un\'accettazione rapida.' },
    { icon: Contact, tag: 'CRM', title: 'Biglietti da visita', desc: 'Nome, azienda, ruolo e contatti: ogni biglietto diventa un contatto nel CRM.' },
];

const ruleCards = [
    { icon: PlusCircle, title: 'Campi personalizzati', desc: 'Crea campi che non esistono nel documento ma che il gestionale richiede.', example: '"Estrai il codice centro di costo dal riferimento ordine. Il formato è CC-XXXX."' },
    { icon: Calculator, title: 'Campi derivati', desc: 'Calcola valori a partire da altri campi già estratti.', example: '"Calcola l\'imponibile netto sottraendo lo sconto dall\'importo totale."' },
    { icon: Tag, title: 'Campi di arricchimento', desc: 'Aggiungi classificazioni e metadati basati sulle tue regole di business.', example: '"Classifica il fornitore come \'strategico\' se l\'importo supera €5.000."' },
];

const features = [
    { icon: CheckCircle, title: 'Pronto all\'uso', desc: 'Tante tipologie di documento pronte senza configurazione. Per i layout complessi creiamo modelli dedicati.' },
    { icon: Cpu, title: 'Modelli AI personalizzati', desc: 'Documenti proprietari o layout aziendali? Creiamo modelli dedicati con precisione superiore ai generici.' },
    { icon: UserCheck, title: 'Human-in-the-loop', desc: 'L\'operatore conferma ogni estrazione prima del trasferimento. Nessun errore arriva al gestionale.' },
    { icon: PlugZap, title: 'Integrazione ERP', desc: 'Collegamento via API o webhook. Ogni dato finisce esattamente dove deve essere.' },
    { icon: Layers, title: 'Multi-canale', desc: 'Email, upload, FTP, API o fotocamera da app Android: il flusso si adatta al tuo modo di lavorare.' },
    { icon: ShieldCheck, title: 'Sicurezza enterprise', desc: 'Dati crittografati e ospitati in Europa, in piena conformità GDPR.' },
];

const plans = [
    { name: 'Demo', price: 'Gratis', per: 'Sempre gratuito', desc: 'Per testare le potenzialità del sistema senza impegno.', rows: [['Pagine/mese', '20'], ['Tipi documento', '1'], ['Utenti', '2'], ['Email polling', false], ['API', false]], btn: 'Inizia gratis', feat: false },
    { name: 'Basic', price: '€49', per: '/ mese + IVA', desc: 'Per piccole aziende che iniziano ad automatizzare.', rows: [['Pagine/mese', '200'], ['Tipi documento', '2'], ['Utenti', '5'], ['Email polling', false], ['API', false]], btn: 'Inizia ora', feat: false },
    { name: 'Business', price: '€129', per: '/ mese + IVA', desc: 'Per aziende in crescita che automatizzano via email.', rows: [['Pagine/mese', '800'], ['Tipi documento', '5'], ['Utenti', '10'], ['Email polling', true], ['API', false]], btn: 'Inizia ora', feat: false },
    { name: 'Professional', price: '€249', per: '/ mese + IVA', desc: 'Integrazione completa con FTP per flussi professionali.', rows: [['Pagine/mese', '2.000'], ['Tipi documento', '10'], ['Utenti', '20'], ['Email polling', true], ['FTP', true]], btn: 'Inizia ora', feat: true },
    { name: 'Enterprise', price: 'Custom', per: 'Offerta su misura', desc: 'Modelli custom e supporto dedicato per grandi volumi.', rows: [['Pagine', 'fair use'], ['Tipi documento', '∞'], ['Utenti', '∞'], ['Modelli custom', true], ['API + FTP', true]], btn: 'Contattaci', feat: false },
];

const faqs = [
    { q: 'Quali tipi di documenti supporta?', a: 'Fatture italiane e internazionali, ricevute e scontrini, documenti d\'identità, DDT e bolle, estratti conto, ordini d\'acquisto, note di credito, tessera sanitaria, cedolini, biglietti da visita e contratti. Per layout proprietari sviluppiamo modelli personalizzati.' },
    { q: 'Si integra con il mio gestionale ERP?', a: 'Sì, tramite API REST o webhook. Puoi configurare la struttura esatta dei dati da inviare — inclusi campi personalizzati e derivati — per piena compatibilità con SAP, Zucchetti, TeamSystem, Sage e altri.' },
    { q: 'Posso definire campi non presenti nel documento?', a: 'Sì: campi personalizzati, derivati e di arricchimento, scritti in linguaggio naturale. Ad esempio "Classifica il fornitore come strategico se l\'importo supera €5.000".' },
    { q: 'C\'è un\'app mobile per chi lavora in campo?', a: 'Sì, un\'app Android che acquisisce documenti dalla fotocamera del telefono. Ideale per operatori di magazzino e logistica che registrano DDT e bolle sul campo, senza tornare in ufficio.' },
    { q: 'Quanto costa?', a: 'Si parte dal piano Basic a €49/mese + IVA (200 pagine/mese). È disponibile un piano Demo gratuito. I piani Business (€129/mese) e Professional (€249/mese) includono volumi maggiori; per grandi volumi c\'è un piano Enterprise su misura.' },
];

const docJsonLd = [
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Finch-AI Document Intelligence",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "DocumentManagement",
        "operatingSystem": "Web, Android",
        "url": "https://finch-ai.it/soluzioni/document-intelligence",
        "image": "https://finch-ai.it/assets/images/og-image.png",
        "description": "Automazione AI per l'estrazione dati da ogni tipo di documento. Pronto all'uso per fatture, ricevute, documenti d'identità, DDT, estratti conto e altro. Regole in linguaggio naturale per campi custom. App Android per operatori in campo. Verifica human-in-the-loop, integrazione ERP.",
        "featureList": [
            "Pronto all'uso per molte tipologie di documenti",
            "Regole di estrazione in linguaggio naturale",
            "Campi personalizzati e derivati per ERP",
            "App Android per operatori in campo",
            "Verifica human-in-the-loop",
            "Integrazione ERP via API e Webhook",
            "GDPR compliant"
        ],
        "offers": [
            { "@type": "Offer", "name": "Demo", "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
            { "@type": "Offer", "name": "Basic", "price": "49", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "49", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" },
            { "@type": "Offer", "name": "Business", "price": "129", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "129", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" },
            { "@type": "Offer", "name": "Professional", "price": "249", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "249", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" }
        ],
        "provider": { "@type": "Organization", "name": "Finch-AI S.r.l.", "url": "https://finch-ai.it" }
    },
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://finch-ai.it/" },
            { "@type": "ListItem", "position": 2, "name": "Soluzioni", "item": "https://finch-ai.it/soluzioni/" },
            { "@type": "ListItem", "position": 3, "name": "Document Intelligence", "item": "https://finch-ai.it/soluzioni/document-intelligence" }
        ]
    },
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "Come automatizzare l'elaborazione dei documenti aziendali con Document Intelligence",
        "description": "Guida in 4 passaggi per estrarre automaticamente i dati dai documenti e trasferirli al gestionale ERP.",
        "step": pipeline.map((s, idx) => ({ "@type": "HowToStep", "position": idx + 1, "name": s.h, "text": s.p }))
    },
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": faqs.map((f) => ({ "@type": "Question", "name": f.q, "acceptedAnswer": { "@type": "Answer", "text": f.a } }))
    }
];

export default function DocumentIntelligence() {
    useReveal();
    const { openContact } = useContactModal();
    const [openFaq, setOpenFaq] = useState(null);
    const [isVideoModalOpen, setIsVideoModalOpen] = useState(false);

    useEffect(() => { window.scrollTo(0, 0); }, []);

    return (
        <>
            <SEO
                title="Document Intelligence AI | Automazione Documenti per PMI Italiane — Finch-AI"
                description="Automatizza fatture, DDT, ricevute e tante tipologie di documento con AI. Configurazione guidata senza codice, campi personalizzati in linguaggio naturale, integrazione ERP, app Android per operatori in campo. Da €49/mese."
                keywords="document intelligence, automazione documenti AI, estrazione dati fatture automatica, OCR intelligente PMI, software gestione documenti AI, digitalizzazione fatture passive, automazione DDT bolle consegna, integrazione ERP documenti, human-in-the-loop OCR, app acquisizione documenti android, campi personalizzati estrazione dati, gestione documentale cloud PMI italiana"
                canonical="https://finch-ai.it/soluzioni/document-intelligence"
                jsonLd={docJsonLd}
            />
            <Navbar />

            <main className="sol-main">

                {/* HERO */}
                <section className="syn-hero">
                    <div className="hero-grid-bg" />
                    <div className="wrap syn-hero-in">
                        <div className="reveal in">
                            <span className="syn-pill"><span className="ping" /> Document Intelligence · AI per documenti</span>
                            <h1>Documenti da gestire ogni giorno. <em>Finch-AI li legge per te.</em></h1>
                            <p className="lead">Configurazione guidata, immediata anche per i non esperti. Document Intelligence riconosce, estrae e verifica i dati da ogni tipo di documento — e li porta nel tuo gestionale.</p>
                            <div className="syn-hero-cta">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-primary">Inizia gratis <ArrowRight size={16} /></a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-ghost"><PlayCircle size={16} /> Scopri come funziona</a>
                            </div>
                            <div className="syn-trust">
                                <span className="dot"><ShieldCheck size={16} /> GDPR · Dati in EU</span>
                                <span className="dot"><Camera size={16} /> App Android inclusa</span>
                                <span className="dot"><PlugZap size={16} /> API per ogni gestionale</span>
                            </div>
                        </div>
                        <div className="reveal in" style={{ transitionDelay: '.12s' }}>
                            <div className="sol-viz">
                                <div className="sol-viz-head"><span className="t">Dalla ricezione al gestionale</span><span className="b">Pipeline</span></div>
                                <div className="sol-ring">
                                    {pipeline.map((s, idx) => (
                                        <React.Fragment key={s.h}>
                                            <div className="sol-rnode"><span className="ri"><s.icon size={18} /></span><span><span className="rl">{s.h}</span><span className="rs">{s.p.split('.')[0]}</span></span></div>
                                            {idx < pipeline.length - 1 && <div className="sol-rarrow"><ArrowDown size={16} /></div>}
                                        </React.Fragment>
                                    ))}
                                </div>
                                <div className="sol-loop"><Sparkles size={14} /> Nessun data entry manuale</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* BROCHURE */}
                <section className="section" style={{ paddingTop: 0, paddingBottom: 'clamp(40px,5vw,70px)' }}>
                    <div className="wrap reveal">
                        <div className="sol-bar">
                            <div className="l"><span className="ic"><FileText size={22} /></span><div><h3>Brochure informativa</h3><p>I dettagli tecnici e i casi d'uso completi di Document Intelligence.</p></div></div>
                            <div className="r">
                                <a href="/it.pdf" download="Brochure-Document-Intelligence-IT.pdf" className="btn btn-primary"><Download size={16} /> IT · Scarica PDF</a>
                                <a href="/en.pdf" download="Brochure-Document-Intelligence-EN.pdf" className="btn btn-ghost"><Download size={16} /> EN · Download PDF</a>
                            </div>
                        </div>
                    </div>
                </section>

                {/* COME FUNZIONA + INFOGRAFICA */}
                <section className="section" id="come-funziona" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Come funziona</span>
                            <h2 className="h2">Dal documento al gestionale, in automatico</h2>
                            <p className="lead">Nessun operatore coinvolto fino alla verifica. Ogni passaggio è automatizzato, ogni eccezione gestita.</p>
                        </div>
                        <div className="sol-flow reveal" style={{ marginBottom: 32 }}>
                            {pipeline.map((s, idx) => (
                                <React.Fragment key={s.h}>
                                    <div className="sol-step"><span className="si"><s.icon size={22} /></span><span className="sk">{s.k}</span><h4>{s.h}</h4><p>{s.p}</p></div>
                                    {idx < pipeline.length - 1 && <div className="sol-arrow"><ArrowRight /></div>}
                                </React.Fragment>
                            ))}
                        </div>
                        <div className="sol-shot reveal">
                            <picture>
                                <source srcSet="/assets/images/infografica_document_intelligence.webp" type="image/webp" />
                                <img src="/assets/images/infografica_document_intelligence.png" alt="Infografica del flusso Document Intelligence — dalla ricezione del documento all'integrazione nel gestionale ERP" width="2708" height="1480" loading="lazy" decoding="async" />
                            </picture>
                        </div>
                    </div>
                </section>

                {/* TIPOLOGIE */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Pronto all'uso</span>
                            <h2 className="h2">Tipologie di documento supportate</h2>
                            <p className="lead">Per ogni tipologia l'attivazione è guidata: scegli i campi, definisci le regole, configura l'output. Nessun codice.</p>
                        </div>
                        <div className="syn-grid-3 reveal">
                            {docTypes.map((d) => (
                                <div className="syn-cardbox" key={d.title}>
                                    <div className="syn-ic"><d.icon size={22} /></div>
                                    <span className="sol-tag"><Layers size={12} /> {d.tag}</span>
                                    <h3 style={{ fontSize: 19 }}>{d.title}</h3>
                                    <p>{d.desc}</p>
                                </div>
                            ))}
                        </div>
                        <div className="syn-ent reveal" style={{ marginTop: 28 }}>
                            <div className="syn-ent-in" style={{ gridTemplateColumns: '1fr auto', alignItems: 'center' }}>
                                <div>
                                    <span className="eyebrow on-dark" style={{ marginBottom: 14 }}>Modelli custom</span>
                                    <h2 style={{ marginBottom: 8 }}>Documenti proprietari? <em>Nessun problema.</em></h2>
                                    <p style={{ marginBottom: 0 }}>Per esigenze specifiche sviluppiamo modelli personalizzati per qualsiasi layout aziendale. Nessuna competenza tecnica richiesta da parte tua.</p>
                                </div>
                                <div><a href={APP_URL} target="_blank" rel="noopener" className="btn btn-lime">Richiedi modello custom <ArrowRight size={16} /></a></div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* REGOLE */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Nessun codice · solo linguaggio naturale</span>
                            <h2 className="h2">Campi personalizzati, scritti come parli</h2>
                            <p className="lead">Definisci le regole di estrazione esattamente come le spiegheresti a un collega. L'AI capisce il contesto e alimenta l'ERP con i campi esatti che ti servono.</p>
                        </div>
                        <div className="sol-grid-2 reveal" style={{ alignItems: 'start' }}>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                                {ruleCards.map((r) => (
                                    <div className="syn-cardbox" key={r.title}>
                                        <div style={{ display: 'flex', gap: 16, alignItems: 'flex-start' }}>
                                            <div className="syn-ic" style={{ marginBottom: 0, flex: 'none' }}><r.icon size={22} /></div>
                                            <div>
                                                <h3 style={{ fontSize: 19 }}>{r.title}</h3>
                                                <p style={{ marginBottom: 10 }}>{r.desc}</p>
                                                <div className="sol-rule" style={{ boxShadow: 'none' }}><div className="body" style={{ padding: 14 }}><p className="quote">{r.example}</p></div></div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="sol-rule">
                                <div className="chrome"><i /><i /><i /><span className="lbl">Regole di estrazione — Fatture fornitore</span></div>
                                <div className="body">
                                    <div>
                                        <div className="sol-rkey"><CheckCircle /> Regola attiva</div>
                                        <div className="active"><div className="fld">Campo · centro_di_costo</div><p className="quote">"Estrai il codice centro di costo dal riferimento ordine. Il formato è CC-XXXX."</p><span className="sol-chip-ok"><Check /> CC-0042 · applicata automaticamente</span></div>
                                    </div>
                                    <div>
                                        <div className="sol-rkey"><PlusCircle /> Nuova regola</div>
                                        <div className="draft">
                                            <div className="fld">Campo · fornitore_strategico</div>
                                            <div className="input">Classifica il fornitore come 'strategico' se l'importo supera €5.000<span className="sol-cursor" /></div>
                                            <div className="sol-draft-foot"><span className="hint"><Sparkles /> L'AI comprende la logica</span><span className="btn btn-primary" style={{ padding: '8px 16px', fontSize: 13 }}>Salva regola</span></div>
                                        </div>
                                    </div>
                                    <div className="out">
                                        <div className="sol-rkey"><PlugZap /> Anteprima output → ERP</div>
                                        <div className="sol-out-grid">
                                            <div className="sol-out-cell"><div className="k">importo_totale</div><div className="v">€ 7.450,00</div></div>
                                            <div className="sol-out-cell"><div className="k">imponibile_netto</div><div className="v">€ 6.803,28</div></div>
                                            <div className="sol-out-cell"><div className="k">centro_di_costo</div><div className="v">CC-0042</div></div>
                                            <div className="sol-out-cell hi"><div className="k">fornitore_strategico</div><div className="v">✓ strategico</div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* APP ANDROID */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">App Android · sempre con te</span>
                            <h2 className="h2">In magazzino, sul campo, ovunque tu riceva documenti</h2>
                        </div>
                        <div className="syn-ent reveal">
                            <div className="syn-ent-in">
                                <div>
                                    <span className="eyebrow on-dark" style={{ marginBottom: 14 }}>Scenario · ricezione merci</span>
                                    <h2>L'operatore inquadra il DDT. <em>Il gestionale si aggiorna da solo.</em></h2>
                                    <p>Non serve tornare in ufficio né un PC. Si apre l'app, si punta la fotocamera sul documento e in pochi istanti i dati sono estratti, verificati e pronti per il WMS.</p>
                                    <div className="syn-ent-feats">
                                        <div className="syn-ent-feat"><span className="fi"><Camera size={20} /></span><div><div className="ft">Fotocamera</div><div className="fd">Nessuno scanner necessario.</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Wifi size={20} /></span><div><div className="ft">In mobilità</div><div className="fd">Sincronizzazione automatica al server.</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><CheckSquare size={20} /></span><div><div className="ft">Verifica a schermo</div><div className="fd">Human-in-the-loop direttamente sul telefono.</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Zap size={20} /></span><div><div className="ft">Subito nel gestionale</div><div className="fd">Dati pronti per il WMS in pochi istanti.</div></div></div>
                                    </div>
                                    <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-lime" style={{ marginTop: 24 }}>Scopri l'app Android <ArrowRight size={16} /></a>
                                </div>
                                <div className="sol-phone-wrap">
                                    <div className="sol-phone">
                                        <div className="badge"><ScanLine size={12} /> Android</div>
                                        <div className="screen">
                                            <div className="sbar"><span>9:41</span><span style={{ display: 'flex', gap: 5 }}><Wifi size={11} /><Battery size={11} /></span></div>
                                            <div className="ahead"><ScanLine size={15} /> Document Intelligence</div>
                                            <div className="view"><span className="br tl" /><span className="br tr" /><span className="br bl" /><span className="br brr" /><span className="scan" /><FileText size={38} style={{ color: 'rgba(255,255,255,.18)' }} /></div>
                                            <div className="data">
                                                <div className="ok"><CheckCircle size={12} /> Dati estratti</div>
                                                <div className="row"><span className="k">Fornitore</span><span className="v">Magazzini Nord SRL</span></div>
                                                <div className="row"><span className="k">N. DDT</span><span className="v">2025/00391</span></div>
                                                <div className="row"><span className="k">Articoli</span><span className="v">8 righe</span></div>
                                                <div className="pcta">Conferma e trasferisci</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* VIDEO DEMO */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">In movimento</span>
                            <h2 className="h2">Guarda Document Intelligence in azione</h2>
                        </div>
                        <div className="reveal" style={{ maxWidth: 960, margin: '0 auto' }}>
                            <div className="sol-video">
                                <iframe src="/assets/videos/document-intelligence-demo.html" title="Document Intelligence — Video divulgativo" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowFullScreen />
                                <button type="button" className="fs" onClick={() => setIsVideoModalOpen(true)} aria-label="Apri il video a schermo intero"><PlayCircle size={15} /> Apri</button>
                            </div>
                        </div>
                    </div>
                </section>

                {/* FUNZIONALITÀ */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Funzionalità</span>
                            <h2 className="h2">Tutto quello che serve per eliminare il data entry</h2>
                        </div>
                        <div className="syn-grid-3 reveal">
                            {features.map((f) => (
                                <div className="syn-cardbox" key={f.title}>
                                    <div className="syn-ic"><f.icon size={22} /></div>
                                    <h3 style={{ fontSize: 19 }}>{f.title}</h3>
                                    <p>{f.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* PRICING */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Prezzi</span>
                            <h2 className="h2">Piani di abbonamento</h2>
                            <p className="lead">Fatturazione basata sulle pagine elaborate. Scala con il tuo business.</p>
                        </div>
                        <div className="sol-price-grid reveal">
                            {plans.map((p) => (
                                <div className={`sol-plan${p.feat ? ' feat' : ''}`} key={p.name}>
                                    {p.feat && <span className="pop">Più popolare</span>}
                                    <div className="nm">{p.name}</div>
                                    <div className="pr">{p.price}</div>
                                    <div className="per">{p.per}</div>
                                    <p className="desc">{p.desc}</p>
                                    <ul>
                                        {p.rows.map(([k, v]) => (
                                            <li key={k}><span>{k}</span>{typeof v === 'boolean' ? (v ? <Check /> : <Minus className="no" />) : <b>{v}</b>}</li>
                                        ))}
                                    </ul>
                                    <a href={APP_URL} target="_blank" rel="noopener" className={`btn ${p.feat ? 'btn-primary' : 'btn-ghost'}`}>{p.btn}</a>
                                </div>
                            ))}
                        </div>
                        <div className="sol-price-note">
                            <span><Sparkles size={14} style={{ verticalAlign: 'middle', marginRight: 6, color: 'var(--green)' }} /> Le pagine extra hanno un costo maggiore rispetto ai piani superiori: per volumi elevati conviene l'upgrade.</span>
                            <span>I limiti sono mensili e si resettano automaticamente.</span>
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Domande frequenti</span>
                            <h2 className="h2">Hai domande su Document Intelligence?</h2>
                        </div>
                        <div className="sol-faq reveal">
                            {faqs.map((item, i) => (
                                <div className={`sol-q${openFaq === i ? ' open' : ''}`} key={item.q}>
                                    <button type="button" className="sol-qbtn" onClick={() => setOpenFaq(openFaq === i ? null : i)} aria-expanded={openFaq === i}>
                                        {item.q}<span className="chev"><ChevronDown /></span>
                                    </button>
                                    {openFaq === i && <div className="sol-a">{item.a}</div>}
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ALTRE SOLUZIONI */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Le altre soluzioni Finch-AI</span>
                            <h2 className="h2">Completa il tuo stack AI</h2>
                        </div>
                        <div className="sol-rel reveal">
                            <Link className="sol-relcard" to="/soluzioni/warehouse-intelligence">
                                <span className="tag">OmniFlow · Warehouse</span>
                                <h3>Il gestionale AI per tutto il ciclo commerciale</h3>
                                <p>Acquisti, magazzino, vendite, ordini e finanza in un unico sistema, con i documenti estratti che entrano direttamente nel gestionale.</p>
                                <span className="go">Scopri OmniFlow <ArrowRight size={14} /></span>
                            </Link>
                            <Link className="sol-relcard" to="/soluzioni/finance-intelligence">
                                <span className="tag">Finance Intelligence</span>
                                <h3>Analisi di bilancio e indici con l'AI</h3>
                                <p>Carica il bilancio Excel e ottieni report OIC, indici finanziari e una chat AI sui tuoi numeri.</p>
                                <span className="go">Scopri Finance Intelligence <ArrowRight size={14} /></span>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap reveal">
                        <div className="syn-cta-box">
                            <h2>Smetti di ricopiare. Inizia ad automatizzare.</h2>
                            <p>Prova Document Intelligence gratis — il primo mese è offerto da noi.</p>
                            <div className="syn-cta-row">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-white">Richiedi accesso gratuito <ArrowRight size={16} /></a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-line"><PlayCircle size={16} /> Scopri come funziona</a>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

            <Footer />

            <VideoModal
                isOpen={isVideoModalOpen}
                onClose={() => setIsVideoModalOpen(false)}
                videoUrl="/assets/videos/document-intelligence-demo.html"
                title="Document Intelligence Demo"
            />
        </>
    );
}
