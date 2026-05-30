import React, { useEffect, useRef, useState } from 'react';
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
    Boxes,
    ShoppingCart,
    Truck,
    Banknote,
    Tag,
    Sparkles,
    Zap,
    ShieldCheck,
    PlugZap,
    AlertTriangle,
    Grid3x3,
    Clock,
    Link2,
    Command,
    Repeat,
    LineChart,
    ListChecks,
    Undo2,
    BarChart3,
    Search,
    Lightbulb,
    Building2,
    Forklift,
    ClipboardList,
    Settings,
    PiggyBank,
    PlayCircle,
    Cloud,
    Users,
    LayoutDashboard,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Maximize,
} from 'lucide-react';

const YOUTUBE_URL = 'https://www.youtube.com/@Finch-AI';

function useReveal() {
    useEffect(() => {
        const els = document.querySelectorAll('.reveal');
        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }),
            { threshold: 0.12 }
        );
        els.forEach((el) => io.observe(el));
        return () => io.disconnect();
    }, []);
}

const SHOTS = [
    { src: '/assets/images/warehouse/dashboard.png', w: 1907, h: 914, icon: LayoutDashboard, title: 'Dashboard', sub: 'Panoramica operativa e KPI', alt: "OmniFlow — Dashboard del gestionale AI Warehouse Intelligence di Finch-AI" },
    { src: '/assets/images/warehouse/magazzino.png', w: 1904, h: 857, icon: Boxes, title: 'Magazzino', sub: 'Bin, picking e scorte', alt: "OmniFlow — Vista Magazzino con mappa dei bin e picking guidato" },
    { src: '/assets/images/warehouse/vendite-offerte.png', w: 1913, h: 910, icon: Tag, title: 'Vendite & Offerte', sub: 'Quote builder e margine live', alt: "OmniFlow — Vendite e offerte con margine in tempo reale" },
    { src: '/assets/images/warehouse/ordini.png', w: 1908, h: 850, icon: ListChecks, title: 'Ordini', sub: 'Kanban e Golden Thread', alt: "OmniFlow — Gestione ordini in vista kanban" },
    { src: '/assets/images/warehouse/approvvigionamento.png', w: 1910, h: 914, icon: ShoppingCart, title: 'Approvvigionamento', sub: 'Smart Restock fornitori', alt: "OmniFlow — Approvvigionamento e ordini fornitore" },
    { src: '/assets/images/warehouse/clienti.png', w: 1907, h: 900, icon: Users, title: 'Clienti', sub: 'Anagrafiche e storico', alt: "OmniFlow — Anagrafica e scheda clienti" },
    { src: '/assets/images/warehouse/report.png', w: 1911, h: 900, icon: BarChart3, title: 'Report & Analytics', sub: 'KPI live ed export', alt: "OmniFlow — Report e analytics con KPI in tempo reale" },
];

function ScreenshotCarousel() {
    const [i, setI] = useState(0);
    const n = SHOTS.length;
    const go = (idx) => setI((idx + n) % n);
    const Cur = SHOTS[i].icon;
    return (
        <div className="sol-carousel reveal">
            <div className="sol-stage">
                <div className="sol-chrome">
                    <i /><i /><i />
                    <span className="url">omniflow.finch-ai.it</span>
                </div>
                <div className="sol-frame">
                    {SHOTS.map((s, idx) => (
                        <img
                            key={s.src}
                            src={s.src}
                            alt={s.alt}
                            width={s.w}
                            height={s.h}
                            className={idx === i ? 'on' : ''}
                            loading={idx === 0 ? 'eager' : 'lazy'}
                            decoding="async"
                            aria-hidden={idx !== i}
                        />
                    ))}
                    <button type="button" className="sol-cbtn prev" onClick={() => go(i - 1)} aria-label="Schermata precedente"><ChevronLeft /></button>
                    <button type="button" className="sol-cbtn next" onClick={() => go(i + 1)} aria-label="Schermata successiva"><ChevronRight /></button>
                    <div className="sol-cap">
                        <span className="ci"><Cur size={18} /></span>
                        <span><b>{SHOTS[i].title}</b><span>{SHOTS[i].sub}</span></span>
                    </div>
                </div>
            </div>
            <div className="sol-thumbs" role="tablist" aria-label="Schermate di OmniFlow">
                {SHOTS.map((s, idx) => (
                    <button type="button" key={s.src} className={`sol-thumb${idx === i ? ' on' : ''}`} onClick={() => go(idx)} aria-label={s.title} aria-selected={idx === i} role="tab">
                        <img src={s.src} alt="" width={s.w} height={s.h} loading="lazy" decoding="async" />
                        <span className="tl">{s.title}</span>
                    </button>
                ))}
            </div>
            <div className="sol-dots">
                {SHOTS.map((s, idx) => (
                    <button type="button" key={s.src} className={`sol-dot${idx === i ? ' on' : ''}`} onClick={() => go(idx)} aria-label={`Vai alla schermata ${idx + 1}`} />
                ))}
            </div>
        </div>
    );
}

const cycleSteps = [
    { icon: ShoppingCart, label: 'Compra', sub: 'Acquisti & Fornitori' },
    { icon: Boxes, label: 'Stocca', sub: 'Magazzino WMS' },
    { icon: Tag, label: 'Vendi', sub: 'Offerte & margine live' },
    { icon: Truck, label: 'Consegna', sub: 'OMS & DDT' },
    { icon: Banknote, label: 'Incassa', sub: 'Finanza & cash flow' },
];

const flowSteps = [
    { icon: ShoppingCart, k: '01 · Compra', h: 'Acquisti', p: 'Smart Restock prevede gli stockout e propone ordini pronti da approvare.' },
    { icon: Boxes, k: '02 · Stocca', h: 'Magazzino', p: 'Mappa bin, picking guidato e slotting dinamico ottimizzato dall\'AI.' },
    { icon: Tag, k: '03 · Vendi', h: 'Offerte', p: 'Quote builder con margine live e Smart Swap che propone alternative migliori.' },
    { icon: Truck, k: '04 · Consegna', h: 'Ordini & DDT', p: 'Kanban ordini, pianificazione corrieri e tracking dei documenti di trasporto.' },
    { icon: Banknote, k: '05 · Incassa', h: 'Finanza', p: 'Fatturazione automatica da ordini e timeline del cash flow.' },
];

const highlights = [
    { tag: 'Golden Thread', icon: Link2, title: 'Segui il filo, dal fornitore al cliente', desc: 'Un click rivela tutta la catena: PO → bin → ordine → fattura. Zero ricerche manuali.' },
    { tag: 'Command-K', icon: Command, title: 'Una tastiera, tutto il sistema', desc: 'Scorciatoia globale: trovi e fai qualsiasi cosa in OmniFlow in un attimo.' },
    { tag: 'Smart Swap', icon: Repeat, title: 'Vendi meglio, non solo di più', desc: 'L\'AI propone alternative a margine più alto mentre costruisci l\'offerta.' },
    { tag: 'Price Watch', icon: LineChart, title: 'Il mercato dei fornitori, in chiaro', desc: 'Storico prezzi per ogni articolo e alert se stai pagando più del solito.' },
];

const modules = [
    { icon: ShoppingCart, title: 'Acquisti & Fornitori', desc: 'Scorecard, RFQ automatiche, storico prezzi, ricezione merce.' },
    { icon: Boxes, title: 'Magazzino WMS', desc: 'Mappa bin, picking guidato, slotting, multi-magazzino.' },
    { icon: Tag, title: 'Vendite & Offerte', desc: 'Quote builder, margine live, Good/Better/Best, firma digitale.' },
    { icon: ListChecks, title: 'Ordini OMS', desc: 'Kanban, back-order, Golden Thread, workflow approvazioni.' },
    { icon: Truck, title: 'Spedizioni', desc: 'Pianificazione corrieri, tracking, DDT, dropshipping diretto.' },
    { icon: Banknote, title: 'Finanza', desc: 'Fatture automatiche da ordini, cash flow, integrazione Stripe.' },
    { icon: Undo2, title: 'Resi & Qualità', desc: 'Resi cliente e fornitore, ispezioni qualità, scorecard.' },
    { icon: BarChart3, title: 'Report & Analytics', desc: 'Dashboard per ruolo, KPI live, export dati, scheduler.' },
];

const roles = [
    { icon: Building2, title: 'Direzione', desc: 'KPI in tempo reale e visibilità end-to-end. Marginalità e rotazione sempre sotto controllo, senza riconciliazioni manuali.' },
    { icon: Tag, title: 'Commerciale', desc: 'Offerta in pochi istanti, margine visibile mentre tratti, firma digitale che diventa ordine. Zero ridigitazione.' },
    { icon: ShoppingCart, title: 'Acquisti', desc: 'Stockout previsti e ordini pronti da approvare. Fornitori valutati con dati oggettivi e storico prezzi sempre visibile.' },
    { icon: Forklift, title: 'Magazzino', desc: 'Picking guidato con percorsi ottimizzati e mappa dei bin. Ogni bin collegato all\'ordine, errori ridotti.' },
    { icon: Settings, title: 'Operations', desc: 'Workflow visivi, approvazioni veloci, escalation chiare. L\'AI segnala le eccezioni prima che diventino problemi.' },
    { icon: PiggyBank, title: 'Finance', desc: 'Timeline del cash flow tra incassi attesi e impegni in uscita. Fatturazione automatica e riconciliazione.' },
];

const problems = [
    { icon: Grid3x3, title: 'Reparti che non si parlano', desc: 'Excel, email e gestionali separati: ogni funzione lavora su numeri diversi, spesso disallineati tra loro.' },
    { icon: AlertTriangle, title: 'Decisioni senza il quadro completo', desc: 'Stockout, sovrascorte, offerte imprecise: senza dati aggiornati si decide a intuito, e il margine ne risente.' },
    { icon: Clock, title: 'Troppo lavoro manuale', desc: 'Ridigitazione tra sistemi ed email di allineamento: l\'operatività ripetitiva ruba tempo e introduce errori.' },
];

const faqs = [
    { q: 'OmniFlow sostituisce il mio gestionale attuale?', a: 'OmniFlow è un sistema operativo completo per il ciclo Compra → Stocca → Vendi. Se usi SAP o Oracle per la contabilità generale, OmniFlow si affianca e si integra via API — non li sostituisce.' },
    { q: 'Dove vengono conservati i dati?', a: 'Disponibile in cloud (datacenter in Unione Europea) e on-premise. Dati isolati per tenant, crittografati a riposo e in transito. Conformità GDPR inclusa.' },
    { q: 'Devo attivare tutti i moduli?', a: 'No. OmniFlow è modulare per design: attivi solo i moduli che ti servono oggi e puoi aggiungerne altri in qualsiasi momento, senza migrazioni di dati.' },
    { q: 'I miei dati vengono usati per addestrare modelli AI?', a: 'No. L\'AI utilizza i tuoi dati esclusivamente per rispondere alle tue richieste. Le operazioni di scrittura richiedono sempre la tua conferma esplicita.' },
];

const warehouseJsonLd = [
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "OmniFlow — Warehouse Intelligence by Finch-AI",
        "alternateName": ["OmniFlow", "Finch-AI Warehouse Intelligence", "Gestionale AI OmniFlow"],
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "WarehouseManagement",
        "operatingSystem": "Web, Cloud, On-premise",
        "url": "https://finch-ai.it/soluzioni/warehouse-intelligence",
        "image": "https://finch-ai.it/assets/images/warehouse/dashboard.png",
        "screenshot": "https://finch-ai.it/assets/images/warehouse/dashboard.png",
        "description": "OmniFlow è il gestionale AI modulare per PMI italiane: chiude il ciclo Compra → Stocca → Vendi → Consegna → Incassa in un unico sistema, con 8 moduli integrati e AI attiva su tutta la pipeline. Multi-magazzino, cloud o on-premise, GDPR compliant.",
        "featureList": [
            "8 moduli integrati: Acquisti, Magazzino WMS, Vendite, Ordini, Spedizioni, Finanza, Resi, Analytics",
            "AI Assistant in linguaggio naturale, attivo su ogni schermata",
            "Smart Quote Builder con margine live e Smart Swap AI",
            "Mappa del magazzino, picking guidato e slotting dinamico",
            "Previsioni AI per ridurre stockout e sovrascorte",
            "Golden Thread: PO → Bin → Ordine → Fattura sempre tracciato",
            "Multi-magazzino e multi-tenant",
            "Cloud (datacenter EU) o on-premise",
            "GDPR compliant · dati crittografati a riposo e in transito"
        ],
        "offers": {
            "@type": "Offer",
            "name": "Demo personalizzata gratuita",
            "price": "0",
            "priceCurrency": "EUR",
            "availability": "https://schema.org/InStock",
            "url": "https://finch-ai.it/#contatti"
        },
        "provider": { "@type": "Organization", "name": "Finch-AI S.r.l.", "url": "https://finch-ai.it", "logo": "https://finch-ai.it/assets/images/LOGO.png" },
        "inLanguage": "it-IT",
        "audience": { "@type": "BusinessAudience", "audienceType": "PMI italiane · Direzione · Acquisti · Vendite · Magazzino · Finance" }
    },
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "OmniFlow Warehouse Intelligence",
        "description": "Gestionale AI modulare che integra acquisti, magazzino WMS, vendite, ordini, spedizioni e finanza in un unico sistema con AI nativa.",
        "brand": { "@type": "Brand", "name": "Finch-AI" },
        "category": "Enterprise Resource Planning Software / Warehouse Management System",
        "image": "https://finch-ai.it/assets/images/warehouse/dashboard.png",
        "url": "https://finch-ai.it/soluzioni/warehouse-intelligence",
        "offers": { "@type": "Offer", "url": "https://finch-ai.it/#contatti", "priceCurrency": "EUR", "price": "0", "availability": "https://schema.org/InStock", "seller": { "@type": "Organization", "name": "Finch-AI S.r.l." } }
    },
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://finch-ai.it/" },
            { "@type": "ListItem", "position": 2, "name": "Soluzioni", "item": "https://finch-ai.it/#soluzioni" },
            { "@type": "ListItem", "position": 3, "name": "Warehouse Intelligence — OmniFlow", "item": "https://finch-ai.it/soluzioni/warehouse-intelligence" }
        ]
    },
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "Come funziona il ciclo Compra → Stocca → Vendi → Consegna → Incassa di OmniFlow",
        "description": "Il closed-loop commerce di OmniFlow in 5 passaggi: dall'ordine fornitore all'incasso, con AI integrata su ogni step.",
        "step": [
            { "@type": "HowToStep", "position": 1, "name": "Compra", "text": "L'AI Smart Restock prevede gli stockout e propone ordini fornitore pronti da approvare." },
            { "@type": "HowToStep", "position": 2, "name": "Stocca", "text": "Magazzino WMS con mappa dei bin, picking guidato e slotting dinamico AI." },
            { "@type": "HowToStep", "position": 3, "name": "Vendi", "text": "Smart Quote Builder con margine in tempo reale e Smart Swap AI per alternative a margine più alto." },
            { "@type": "HowToStep", "position": 4, "name": "Consegna", "text": "OMS con kanban ordini, pianificazione corrieri, tracking DDT e dropshipping diretto." },
            { "@type": "HowToStep", "position": 5, "name": "Incassa", "text": "Fatturazione automatica da ordini, timeline cash flow, integrazione Stripe e riconciliazione bancaria." }
        ]
    },
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": faqs.map((f) => ({ "@type": "Question", "name": f.q, "acceptedAnswer": { "@type": "Answer", "text": f.a } }))
    }
];

export default function WarehouseIntelligence() {
    useReveal();
    const { openContact } = useContactModal();
    const [openFaq, setOpenFaq] = useState(null);
    const videoIframeRef = useRef(null);

    useEffect(() => { window.scrollTo(0, 0); }, []);

    const enterFullscreen = () => {
        const el = videoIframeRef.current;
        if (!el) return;
        const req = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
        if (req) req.call(el);
    };

    return (
        <>
            <SEO
                title="OmniFlow · Gestionale AI Modulare per PMI | Warehouse Intelligence — Finch-AI"
                description="OmniFlow: gestionale AI per PMI con WMS, vendite, acquisti, ordini e finanza in un solo sistema. 8 moduli, AI integrata, cloud o on-premise. GDPR. Prenota una demo."
                keywords="gestionale AI, gestionale AI PMI, software gestionale magazzino AI, WMS italiano cloud, ERP modulare PMI, OmniFlow Finch-AI, warehouse intelligence, gestionale closed-loop, Smart Quote Builder, picking guidato AI, slotting dinamico, gestionale ordini fatturazione AI, software previsione stockout AI, Smart Swap, Golden Thread ERP"
                canonical="https://finch-ai.it/soluzioni/warehouse-intelligence"
                ogImage="https://finch-ai.it/assets/images/warehouse/dashboard.png"
                ogImageAlt="OmniFlow — Dashboard del gestionale AI Warehouse Intelligence di Finch-AI"
                jsonLd={warehouseJsonLd}
            />
            <Navbar />

            <main className="sol-main">

                {/* HERO */}
                <section className="syn-hero">
                    <div className="hero-grid-bg" />
                    <div className="wrap syn-hero-in">
                        <div className="reveal in">
                            <span className="syn-pill"><span className="ping" /> Warehouse Intelligence · OmniFlow</span>
                            <h1>Il gestionale con <em>AI integrata</em> che evolve con la tua azienda.</h1>
                            <p className="lead">OmniFlow chiude il ciclo <strong>Compra → Stocca → Vendi → Consegna → Incassa</strong> in un unico sistema. Un solo dato, una sola verità — per tutti i reparti, in tempo reale.</p>
                            <div className="syn-hero-cta">
                                <button type="button" className="btn btn-primary" onClick={() => openContact({ prefill: { need: 'Demo OmniFlow' } })}>Prenota una demo <ArrowRight size={16} /></button>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-ghost"><PlayCircle size={16} /> Scopri come funziona</a>
                            </div>
                            <div className="syn-trust">
                                <span className="dot"><Sparkles size={16} /> AI Assistant integrato</span>
                                <span className="dot"><Boxes size={16} /> Multi-magazzino</span>
                                <span className="dot"><Cloud size={16} /> Cloud o on-premise</span>
                                <span className="dot"><ShieldCheck size={16} /> GDPR · Dati in EU</span>
                            </div>
                        </div>
                        <div className="reveal in" style={{ transitionDelay: '.12s' }}>
                            <div className="sol-viz">
                                <div className="sol-viz-head"><span className="t">Il ciclo OmniFlow</span><span className="b">Closed-loop</span></div>
                                <div className="sol-ring">
                                    {cycleSteps.map((s, idx) => (
                                        <React.Fragment key={s.label}>
                                            <div className="sol-rnode"><span className="ri"><s.icon size={18} /></span><span><span className="rl">{s.label}</span><span className="rs">{s.sub}</span></span></div>
                                            {idx < cycleSteps.length - 1 && <div className="sol-rarrow"><ArrowDown size={16} /></div>}
                                        </React.Fragment>
                                    ))}
                                </div>
                                <div className="sol-loop"><Repeat size={14} /> Un unico flusso, sempre tracciato</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* PROBLEMA */}
                <section className="section" style={{ paddingTop: 'clamp(40px,6vw,80px)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Il problema</span>
                            <h2 className="h2">Quando i dati non circolano, il margine si perde</h2>
                            <p className="lead">Acquisti, magazzino, vendite e finanza spesso usano strumenti separati. Il risultato è lavoro manuale, decisioni al buio e marginalità erosa.</p>
                        </div>
                        <div className="syn-grid-3">
                            {problems.map((p, idx) => (
                                <div className="syn-cardbox reveal" key={p.title} style={{ transitionDelay: `${idx * 0.1}s` }}>
                                    <div className="syn-ic"><p.icon size={22} /></div>
                                    <h3>{p.title}</h3>
                                    <p>{p.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* SOLUZIONE / CICLO */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">La soluzione</span>
                            <h2 className="h2">Un unico flusso, dal fornitore al cliente</h2>
                            <p className="lead">OmniFlow collega ogni fase con l'AI sempre attiva: prevede, suggerisce e automatizza lungo tutta la pipeline.</p>
                        </div>
                        <div className="sol-flow reveal" style={{ marginBottom: 40 }}>
                            {flowSteps.map((s, idx) => (
                                <React.Fragment key={s.h}>
                                    <div className="sol-step"><span className="si"><s.icon size={22} /></span><span className="sk">{s.k}</span><h4>{s.h}</h4><p>{s.p}</p></div>
                                    {idx < flowSteps.length - 1 && <div className="sol-arrow"><ArrowRight /></div>}
                                </React.Fragment>
                            ))}
                        </div>
                        <div className="syn-cap-grid reveal">
                            {highlights.map((h) => (
                                <div className="syn-cap" key={h.tag}>
                                    <span className="num">{h.tag}</span>
                                    <div className="syn-ic"><h.icon size={24} /></div>
                                    <h3>{h.title}</h3>
                                    <p>{h.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* MODULI */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">I moduli</span>
                            <h2 className="h2">Tutto il ciclo del commercio, in un sistema</h2>
                            <p className="lead">Modulare per design: attivi solo ciò che serve oggi e aggiungi quando cresci.</p>
                        </div>
                        <div className="sol-grid-4 reveal">
                            {modules.map((m) => (
                                <div className="syn-cardbox" key={m.title}>
                                    <div className="syn-ic"><m.icon size={22} /></div>
                                    <h3 style={{ fontSize: 18 }}>{m.title}</h3>
                                    <p>{m.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* AI ASSISTANT */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-ent reveal">
                            <div className="syn-ent-in">
                                <div>
                                    <span className="eyebrow on-dark" style={{ marginBottom: 16 }}>AI Assistant</span>
                                    <h2>Un collaboratore in più, <em>sempre disponibile</em></h2>
                                    <p>Conosce tutta la tua azienda, risponde in linguaggio naturale e agisce sotto la tua supervisione — su ogni schermata, per ogni ruolo.</p>
                                    <div className="syn-ent-feats">
                                        <div className="syn-ent-feat"><span className="fi"><Search size={20} /></span><div><div className="ft">Interroga i dati</div><div className="fd">Ordini in ritardo, clienti top, sotto-scorta: risposta istantanea.</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Zap size={20} /></span><div><div className="ft">Crea con un testo</div><div className="fd">Offerte, ordini, RFQ — sempre con conferma prima di scrivere.</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Lightbulb size={20} /></span><div><div className="ft">Suggerisce</div><div className="fd">Alternative a margine più alto, fornitori migliori, slotting ottimale.</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><ShieldCheck size={20} /></span><div><div className="ft">Sotto controllo</div><div className="fd">Ogni scrittura richiede la tua conferma esplicita.</div></div></div>
                                    </div>
                                </div>
                                <div>
                                    <div className="syn-card" style={{ background: '#0d1f17', borderColor: 'rgba(255,255,255,.1)' }}>
                                        <div className="topbar"><i /><i /><i /><span className="lbl" style={{ color: 'rgba(255,255,255,.5)' }}>OmniFlow AI</span></div>
                                        <div className="syn-chat"><div className="av syn-av-user">TU</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>Quali ordini sono in ritardo questa settimana?</div></div>
                                        <div className="syn-chat"><div className="av syn-av-ai">AI</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>Ho trovato <strong>tre ordini in ritardo</strong>: Fratelli Esposito, Centro Edile Brianza e Hotel Mediterraneo. Preparo una comunicazione per i clienti?</div></div>
                                        <div className="syn-chat"><div className="av syn-av-user">TU</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>Top prodotti per margine questo mese</div></div>
                                        <div className="syn-chat"><div className="av syn-av-ai">AI</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>In testa Travertino Romano, Massello Autobloccante e Rovere Parquet. Con <strong>Smart Swap</strong> posso proporre alternative a margine più alto sulle offerte in corso.</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* RUOLI */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Per chi</span>
                            <h2 className="h2">Il vantaggio giusto per ogni ruolo</h2>
                        </div>
                        <div className="syn-grid-3 reveal">
                            {roles.map((r) => (
                                <div className="syn-cardbox" key={r.title}>
                                    <div className="syn-ic"><r.icon size={22} /></div>
                                    <h3>{r.title}</h3>
                                    <p>{r.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* DENTRO OMNIFLOW — carousel */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Dentro OmniFlow</span>
                            <h2 className="h2">Un sistema bello quanto potente</h2>
                            <p className="lead">Web-based, in tempo reale, modulare. Sfoglia le viste reali dell'applicativo.</p>
                        </div>
                        <ScreenshotCarousel />
                    </div>
                </section>

                {/* VIDEO DEMO */}
                <section className="section" id="come-funziona" style={{ paddingTop: 0 }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">In movimento</span>
                            <h2 className="h2">Guarda OmniFlow in azione</h2>
                            <p className="lead">Dal fornitore al cliente, tutto il flusso in un breve video — con AI integrata su ogni passaggio.</p>
                        </div>
                        <div className="reveal" style={{ maxWidth: 960, margin: '0 auto' }}>
                            <div className="sol-video">
                                <iframe ref={videoIframeRef} src="/assets/videos/warehouse-intelligence-demo.html" title="OmniFlow — Video divulgativo" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowFullScreen />
                                <button type="button" onClick={enterFullscreen} className="fs" aria-label="Schermo intero"><Maximize size={15} /> Schermo intero</button>
                            </div>
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">Domande frequenti</span>
                            <h2 className="h2">Hai domande su OmniFlow?</h2>
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
                            <Link className="sol-relcard" to="/soluzioni/document-intelligence">
                                <span className="tag">Document Intelligence</span>
                                <h3>Estrai i dati da fatture, DDT e ricevute</h3>
                                <p>AI pronta all'uso per i documenti aziendali, con app Android per gli operatori in campo e integrazione diretta con OmniFlow.</p>
                                <span className="go">Scopri Document Intelligence <ArrowRight size={14} /></span>
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

                {/* CTA FINALE */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap reveal">
                        <div className="syn-cta-box">
                            <h2>Vedi OmniFlow in una demo</h2>
                            <p>Una demo personalizzata sul tuo settore. Nessun impegno, solo risposte concrete.</p>
                            <div className="syn-cta-row">
                                <button type="button" className="btn btn-white" onClick={() => openContact({ prefill: { need: 'Demo OmniFlow' } })}>Prenota una demo <ArrowRight size={16} /></button>
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
