import React, { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import SEO from '../components/SEO';
import {
    ArrowRight,
    Boxes,
    Maximize,
    ShoppingCart,
    Truck,
    Banknote,
    DollarSign,
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
    Send,
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
    Rocket,
    CheckCircle,
    ChevronDown,
    MessageSquare,
    Cloud,
    Users,
    Eye,
} from 'lucide-react';
import Layout from '../components/Layout';

const WarehouseIntelligence = () => {
    const [openFaq, setOpenFaq] = useState(null);
    const videoIframeRef = useRef(null);

    useEffect(() => {
        window.scrollTo(0, 0);
    }, []);

    const enterFullscreen = () => {
        const el = videoIframeRef.current;
        if (!el) return;
        const req = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
        if (req) req.call(el);
    };

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
                "Mappa 3D del magazzino, picking guidato e slotting dinamico",
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
            "provider": {
                "@type": "Organization",
                "name": "Finch-AI S.r.l.",
                "url": "https://finch-ai.it",
                "logo": "https://finch-ai.it/assets/images/LOGO.png"
            },
            "inLanguage": "it-IT",
            "audience": {
                "@type": "BusinessAudience",
                "audienceType": "PMI italiane · Direzione · Acquisti · Vendite · Magazzino · Finance"
            }
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
            "offers": {
                "@type": "Offer",
                "url": "https://finch-ai.it/#contatti",
                "priceCurrency": "EUR",
                "price": "0",
                "priceSpecification": {
                    "@type": "PriceSpecification",
                    "valueAddedTaxIncluded": false
                },
                "availability": "https://schema.org/InStock",
                "seller": { "@type": "Organization", "name": "Finch-AI S.r.l." }
            }
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
            "description": "Guida in 5 passaggi al closed-loop commerce di OmniFlow: dall'ordine fornitore all'incasso, con AI integrata su ogni step.",
            "image": "https://finch-ai.it/assets/images/warehouse/dashboard.png",
            "totalTime": "PT30S",
            "step": [
                { "@type": "HowToStep", "position": 1, "name": "Compra", "text": "L'AI Smart Restock prevede gli stockout e propone ordini fornitore pronti da approvare. Scorecard fornitori e storico prezzi sempre visibili." },
                { "@type": "HowToStep", "position": 2, "name": "Stocca", "text": "Magazzino WMS con mappa 3D dei bin, picking guidato da percorsi ottimizzati e slotting dinamico AI." },
                { "@type": "HowToStep", "position": 3, "name": "Vendi", "text": "Smart Quote Builder con margine in tempo reale. Smart Swap AI propone alternative a margine più alto mentre costruisci l'offerta." },
                { "@type": "HowToStep", "position": 4, "name": "Consegna", "text": "OMS con kanban ordini, pianificazione corrieri, tracking DDT e gestione dropshipping diretto." },
                { "@type": "HowToStep", "position": 5, "name": "Incassa", "text": "Fatturazione automatica da ordini, timeline cash flow, integrazione Stripe e riconciliazione bancaria." }
            ]
        },
        {
            "@context": "https://schema.org",
            "@type": "ItemList",
            "name": "Moduli di OmniFlow",
            "description": "Gli 8 moduli AI di OmniFlow Warehouse Intelligence — modulare per design, attivi solo ciò che serve oggi.",
            "itemListElement": [
                { "@type": "ListItem", "position": 1, "name": "Acquisti & Fornitori — Smart Restock AI" },
                { "@type": "ListItem", "position": 2, "name": "Magazzino WMS — Slotting AI" },
                { "@type": "ListItem", "position": 3, "name": "Vendite & Offerte — Smart Swap AI" },
                { "@type": "ListItem", "position": 4, "name": "Gestione Ordini OMS — Priorità AI" },
                { "@type": "ListItem", "position": 5, "name": "Spedizioni & Consegne — Route AI" },
                { "@type": "ListItem", "position": 6, "name": "Finanza & Fatturazione — Cash Flow AI" },
                { "@type": "ListItem", "position": 7, "name": "Resi (RMA) & Qualità — Quality AI" },
                { "@type": "ListItem", "position": 8, "name": "Report & Analytics — Insight AI" }
            ]
        },
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "OmniFlow sostituisce il mio gestionale ERP attuale?",
                    "acceptedAnswer": { "@type": "Answer", "text": "OmniFlow è un sistema operativo completo per il ciclo Compra → Stocca → Vendi. Se usi SAP, Oracle, Zucchetti o TeamSystem per la contabilità generale, OmniFlow si affianca e si integra via API REST e webhook — non li sostituisce." }
                },
                {
                    "@type": "Question",
                    "name": "Quanto tempo richiede l'implementazione del gestionale OmniFlow?",
                    "acceptedAnswer": { "@type": "Answer", "text": "L'onboarding standard richiede 2–6 settimane a seconda della complessità del catalogo e dei processi aziendali. Il team Finch-AI segue ogni implementazione con sessioni dedicate, configurazione guidata e formazione per tutti i ruoli." }
                },
                {
                    "@type": "Question",
                    "name": "Dove vengono conservati i dati di OmniFlow? È GDPR compliant?",
                    "acceptedAnswer": { "@type": "Answer", "text": "OmniFlow è disponibile in modalità cloud (datacenter in Unione Europea) e on-premise sui server della tua azienda. I dati sono isolati per tenant, crittografati a riposo e in transito. La conformità GDPR è inclusa di default." }
                },
                {
                    "@type": "Question",
                    "name": "Devo attivare tutti gli 8 moduli di OmniFlow?",
                    "acceptedAnswer": { "@type": "Answer", "text": "No. OmniFlow è modulare per design: attivi solo i moduli che ti servono oggi (anche solo Magazzino WMS o solo Vendite, ad esempio) e puoi aggiungerne altri in qualsiasi momento, senza migrazioni di dati né interruzioni operative." }
                },
                {
                    "@type": "Question",
                    "name": "I miei dati aziendali vengono usati per addestrare modelli AI?",
                    "acceptedAnswer": { "@type": "Answer", "text": "No. L'AI di OmniFlow utilizza i tuoi dati esclusivamente per rispondere alle tue richieste interne. Le operazioni di scrittura richiedono sempre conferma esplicita dell'utente. I tuoi dati non lasciano il tuo tenant e non vengono usati per training di modelli generici." }
                },
                {
                    "@type": "Question",
                    "name": "OmniFlow funziona per PMI o solo per grandi aziende?",
                    "acceptedAnswer": { "@type": "Answer", "text": "OmniFlow è progettato specificamente per PMI italiane (10–500 dipendenti). La natura modulare permette di partire con i moduli essenziali a costi accessibili e crescere con l'azienda, senza i costi di licenza tipici dei grandi ERP enterprise." }
                }
            ]
        }
    ];

    const cycleSteps = [
        { icon: ShoppingCart, label: "Compra", sub: "Acquisti & Fornitori" },
        { icon: Boxes, label: "Stocca", sub: "Magazzino WMS" },
        { icon: DollarSign, label: "Vendi", sub: "Vendite & Offerte" },
        { icon: Truck, label: "Consegna", sub: "OMS & DDT" },
        { icon: Banknote, label: "Incassa", sub: "Finanza & KPI" },
    ];

    const highlights = [
        { tag: "Golden Thread", icon: Link2, title: "Segui il filo. Dal fornitore al cliente.", desc: "Un click rivela tutta la catena: PO → bin → ordine → fattura. Zero ricerche manuali." },
        { tag: "Command-K Universe", icon: Command, title: "Una tastiera. Tutto il sistema.", desc: "Scorciatoia globale: trova e fai qualsiasi cosa in OmniFlow in meno di un secondo." },
        { tag: "Smart Swap", icon: Repeat, title: "Vendi meglio, non solo di più.", desc: "L'AI propone alternative a margine più alto mentre costruisci l'offerta, in tempo reale." },
        { tag: "Price Watch", icon: LineChart, title: "Il mercato dei fornitori, in chiaro.", desc: "Grafico storico prezzi per ogni articolo. Alert automatico se stai pagando più del solito." },
    ];

    const modules = [
        { icon: ShoppingCart, title: "Acquisti & Fornitori", desc: "Scorecard affidabilità, RFQ automatiche, storico prezzi, ricezione merce.", ai: "Smart Restock AI" },
        { icon: Boxes, title: "Magazzino WMS", desc: "Mappa 3D dei bin, picking guidato, slotting dinamico, multi-magazzino.", ai: "Slotting AI" },
        { icon: DollarSign, title: "Vendite & Offerte", desc: "Smart Quote Builder, margine live, Good/Better/Best, firma digitale.", ai: "Smart Swap AI" },
        { icon: ListChecks, title: "Gestione Ordini OMS", desc: "Kanban ordini, back-order linking, Golden Thread, workflow approvazioni.", ai: "Priorità AI" },
        { icon: Truck, title: "Spedizioni & Consegne", desc: "Pianificazione corrieri, tracking, DDT, dropshipping diretto.", ai: "Route AI" },
        { icon: Banknote, title: "Finanza & Fatturazione", desc: "Fatture automatiche da ordini, timeline cash flow, integrazione Stripe.", ai: "Cash Flow AI" },
        { icon: Undo2, title: "Resi (RMA) & Qualità", desc: "Resi cliente e fornitore, ispezioni qualità, scorecard fornitori.", ai: "Quality AI" },
        { icon: BarChart3, title: "Report & Analytics", desc: "Dashboard per ruolo, KPI live, export dati, scheduler report.", ai: "Insight AI" },
    ];

    const roles = [
        { tag: "Direzione", title: "Chi guida l'azienda", desc: "Timeline cash flow, KPI in tempo reale, visibilità end-to-end. Un'unica fonte di verità senza riconciliazioni manuali.", benefit: "Marginalità e rotazione sempre sotto controllo" },
        { tag: "Commerciale", title: "Sales & Deal Closer", desc: "Offerta in meno di un minuto. Margine visibile mentre tratti. Firma digitale → ordine automatico. Zero ridigitazione.", benefit: "−70% tempo per offerta complessa" },
        { tag: "Acquisti", title: "Procurement & Buyer", desc: "Stockout previsti, ordini fornitore pronti da approvare. Fornitori valutati con dati oggettivi. Storico prezzi sempre visibile.", benefit: "−40% stockout con previsioni AI" },
        { tag: "Magazzino", title: "Warehouse & Floor", desc: "Picking guidato con percorsi ottimizzati. Mappa 3D del magazzino. Errori di spedizione drasticamente ridotti.", benefit: "Ogni bin collegato all'ordine" },
        { tag: "Operations", title: "Operations Manager", desc: "Workflow visivi, approvazioni veloci, escalation chiare. L'AI segnala le eccezioni prima che diventino problemi.", benefit: "Controllo senza operatività manuale" },
        { tag: "Finance", title: "CFO & Finance", desc: "Timeline cash flow: incassi attesi vs impegni in uscita. Fatturazione automatica da ordini. Integrazione Stripe.", benefit: "Riconciliazione automatica" },
    ];

    const faqs = [
        { q: "OmniFlow sostituisce il mio gestionale attuale?", a: "OmniFlow è un sistema operativo completo per il ciclo Compra → Stocca → Vendi. Se usi SAP o Oracle per la contabilità generale, OmniFlow si affianca e si integra — non li sostituisce." },
        { q: "Quanto tempo richiede l'implementazione?", a: "L'onboarding standard richiede 2–6 settimane a seconda della complessità del catalogo e dei processi. Il team Finch-AI segue ogni implementazione con sessioni dedicate." },
        { q: "Dove vengono conservati i dati?", a: "Disponibile in modalità cloud (datacenter in Unione Europea) e on-premise. Dati isolati per tenant, crittografati a riposo e in transito. Conformità GDPR inclusa." },
        { q: "Devo attivare tutti i moduli?", a: "No. OmniFlow è modulare per design: attivi solo i moduli che ti servono oggi e puoi aggiungerne altri in qualsiasi momento, senza migrazioni di dati." },
        { q: "I miei dati vengono usati per addestrare modelli AI?", a: "No. L'AI utilizza i tuoi dati esclusivamente per rispondere alle tue richieste. Le operazioni di scrittura richiedono sempre conferma esplicita dell'utente." },
    ];

    const scrollToVideo = (e) => {
        e.preventDefault();
        const el = document.getElementById('come-funziona');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    };

    return (
        <Layout>
            <SEO
                title="OmniFlow · Gestionale AI Modulare per PMI | Warehouse Intelligence — Finch-AI"
                description="OmniFlow: gestionale AI per PMI con WMS, vendite, acquisti, ordini e finanza in un solo sistema. 8 moduli, AI integrata, cloud o on-premise. GDPR. Prenota una demo."
                keywords="gestionale AI, gestionale AI PMI, software gestionale magazzino AI, WMS italiano cloud, ERP modulare PMI, OmniFlow Finch-AI, warehouse intelligence, gestionale closed-loop, gestionale acquisti vendite logistica AI, Smart Quote Builder, picking guidato AI, slotting dinamico, gestionale modulare cloud on-premise, software AI commercio, gestionale ordini fatturazione AI, gestionale PMI italiane, software previsione stockout AI, Smart Swap, Golden Thread ERP"
                canonical="https://finch-ai.it/soluzioni/warehouse-intelligence"
                ogImage="https://finch-ai.it/assets/images/warehouse/dashboard.png"
                ogImageAlt="OmniFlow — Dashboard del gestionale AI Warehouse Intelligence di Finch-AI"
                jsonLd={warehouseJsonLd}
            />
            <div className="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-24">

                {/* ─── HERO ─────────────────────────────────────────────────── */}
                <section className="text-center mb-12 sm:mb-24">
                    <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-8 animate-in fade-in slide-in-from-top-4 duration-1000">
                        <Boxes className="w-4 h-4" />
                        Warehouse Intelligence · OmniFlow
                    </div>

                    <h1 className="text-3xl sm:text-4xl md:text-6xl font-extrabold leading-tight mb-8 animate-in fade-in slide-in-from-top-6 duration-1000 fill-mode-both">
                        Il gestionale con <span className="relative inline-block">
                            <span className="relative z-10 text-emerald-600 dark:text-emerald-400">AI integrata</span>
                            <span className="absolute bottom-1 left-0 right-0 h-3 bg-emerald-500/10 -z-0 rounded-sm"></span>
                        </span><br />
                        che evolve con la tua azienda.
                    </h1>

                    <p className="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto mb-10 animate-in fade-in slide-in-from-top-8 duration-1000 fill-mode-both">
                        OmniFlow è un gestionale completo con <strong className="text-emerald-600 dark:text-emerald-400">AI integrata su tutta la pipeline</strong> e i flussi di lavoro —
                        si adatta all'utente, evolve con l'azienda e chiude il ciclo Compra → Stocca → Vendi → Consegna → Incassa in un solo sistema.
                    </p>

                    {/* CTA Buttons */}
                    <div className="flex flex-wrap justify-center gap-4 mb-12 animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
                        <a href="/#contatti"
                            className="inline-flex items-center gap-2 bg-emerald-600 text-white px-5 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-500/20">
                            <Rocket className="w-5 h-5" />
                            Prenota una Demo
                        </a>
                        <button onClick={scrollToVideo}
                            className="inline-flex items-center gap-2 bg-card border border-border text-foreground px-5 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-muted transition-all">
                            <PlayCircle className="w-5 h-5 text-emerald-500" />
                            Scopri come funziona
                        </button>
                    </div>

                    {/* Stats */}
                    <div className="flex flex-wrap justify-center gap-3 sm:gap-6 mb-8 animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
                        {[
                            { label: "tempo per offerta complessa", value: "−70%" },
                            { label: "stockout con previsioni AI", value: "−40%" },
                            { label: "marginalità media con Smart Swap", value: "+15%" }
                        ].map((stat, i) => (
                            <div key={i} className="bg-card border border-border rounded-2xl p-4 sm:p-6 shadow-sm flex items-center gap-4 min-w-[160px] sm:min-w-[240px]">
                                <span className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{stat.value}</span>
                                <span className="text-left text-xs text-muted-foreground leading-tight">{stat.label}</span>
                            </div>
                        ))}
                    </div>

                    {/* Trust pills */}
                    <div className="flex flex-wrap justify-center gap-3">
                        {[
                            { icon: <Sparkles className="w-4 h-4" />, text: "AI Assistant integrato" },
                            { icon: <Boxes className="w-4 h-4" />, text: "Multi-magazzino" },
                            { icon: <Cloud className="w-4 h-4" />, text: "Cloud o on-premise" },
                            { icon: <ShieldCheck className="w-4 h-4" />, text: "GDPR · Dati in EU" },
                            { icon: <Users className="w-4 h-4" />, text: "Onboarding dedicato" },
                        ].map((pill, i) => (
                            <div key={i} className="inline-flex items-center gap-2 bg-card border border-border px-4 py-2 rounded-full text-sm text-muted-foreground shadow-sm">
                                <span className="text-emerald-600 dark:text-emerald-400">{pill.icon}</span>
                                {pill.text}
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── VIDEO DEMO ───────────────────────────────────────────── */}
                <section id="come-funziona" className="mb-16 sm:mb-32 scroll-mt-24">
                    <div className="text-center mb-8 sm:mb-12">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <PlayCircle className="w-3.5 h-3.5" />
                            Scopri come funziona
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">OmniFlow in 2 minuti</h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            Dal fornitore al cliente, tutto il flusso in un video — con AI integrata su ogni passaggio.
                        </p>
                    </div>
                    <div className="bg-card border border-border rounded-3xl overflow-hidden shadow-2xl relative group">
                        <div className="absolute inset-0 bg-emerald-600/5 pointer-events-none group-hover:bg-transparent transition-colors" />
                        <div className="aspect-video w-full">
                            <iframe
                                ref={videoIframeRef}
                                src="/assets/videos/warehouse-intelligence-demo.html"
                                title="OmniFlow — Video divulgativo"
                                className="w-full h-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                allowFullScreen
                            />
                        </div>
                        <button
                            type="button"
                            onClick={enterFullscreen}
                            aria-label="Schermo intero"
                            title="Schermo intero"
                            className="absolute bottom-4 right-4 z-10 inline-flex items-center gap-2 bg-black/60 hover:bg-black/80 text-white px-3 py-2 rounded-full text-xs font-bold backdrop-blur-sm border border-white/10 transition-all shadow-lg"
                        >
                            <Maximize className="w-4 h-4" />
                            <span className="hidden sm:inline">Schermo intero</span>
                        </button>
                    </div>
                    <p className="text-center mt-4 text-xs text-muted-foreground font-mono">
                        Premi ▶ nel player per avviare · durata: 1 minuto 52 secondi
                    </p>
                </section>

                {/* ─── PROBLEMA ─────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Il problema
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">L'azienda opera a silos. Ogni funzione è un'isola.</h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            Acquisti, Magazzino, Vendite, Finanza, Logistica e Qualità usano sistemi separati.
                            Il dato non fluisce — il risultato è operatività manuale, decisioni sbagliate e margini erosi.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                        {[
                            { icon: Grid3x3, title: "Dati frammentati tra sistemi diversi", before: "Excel, email e gestionali separati: ogni reparto ha la sua versione della verità.", after: "Un solo sistema unificato — dati condivisi in tempo reale, da Acquisti a Finanza." },
                            { icon: AlertTriangle, title: "Decisioni prese senza visibilità", before: "Stockout, sovrascorte, offerte imprecise: decidere senza dati costa margine ogni giorno.", after: "AI integrata su tutta la pipeline: prevede, segnala e suggerisce prima che sia tardi." },
                            { icon: Clock, title: "Tempo perso in operatività manuale", before: "Copy-paste, ridigitazione tra sistemi, email di allineamento: l'operatività fagocita il margine.", after: "Workflow automatizzati da offerta a fattura — zero ridigitazione, zero riconciliazione." },
                        ].map((card, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-6 sm:p-8 shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                <div className="w-12 h-12 rounded-xl flex items-center justify-center mb-5 bg-orange-500/10 text-orange-600 dark:text-orange-400">
                                    <card.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold text-lg mb-5">{card.title}</h3>
                                <div className="space-y-3">
                                    <div className="bg-destructive/5 border border-destructive/20 rounded-xl px-4 py-3">
                                        <div className="text-[10px] font-bold text-destructive uppercase tracking-wider mb-1">❌ Prima</div>
                                        <p className="text-xs text-muted-foreground leading-relaxed">{card.before}</p>
                                    </div>
                                    <div className="bg-emerald-500/5 border border-emerald-500/20 rounded-xl px-4 py-3">
                                        <div className="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">✅ Dopo</div>
                                        <p className="text-xs text-foreground leading-relaxed">{card.after}</p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── SOLUZIONE / CICLO ────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            La soluzione
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Un unico flusso. Dal fornitore al cliente.</h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            OmniFlow chiude il ciclo completo Compra → Stocca → Vendi → Consegna → Incassa.
                            Un solo dato, una sola verità — per tutti i reparti, in tempo reale.
                        </p>
                    </div>

                    {/* Cycle row */}
                    <div className="bg-card border border-border rounded-3xl p-6 sm:p-10 mb-8 shadow-sm">
                        <div className="flex flex-wrap items-center justify-center gap-3 sm:gap-2">
                            {cycleSteps.map((step, i) => (
                                <React.Fragment key={i}>
                                    <div className="flex flex-col items-center text-center min-w-[110px]">
                                        <div className="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                            <step.icon className="w-7 h-7" />
                                        </div>
                                        <h4 className="font-bold text-sm">{step.label}</h4>
                                        <p className="text-[11px] text-muted-foreground mt-1">{step.sub}</p>
                                    </div>
                                    {i < cycleSteps.length - 1 && (
                                        <ArrowRight className="w-5 h-5 text-emerald-500/60 mx-1 hidden sm:block" />
                                    )}
                                </React.Fragment>
                            ))}
                        </div>
                    </div>

                    {/* Highlight cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        {highlights.map((h, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-6 sm:p-8 shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                <div className="flex items-start gap-4">
                                    <div className="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <h.icon className="w-6 h-6" />
                                    </div>
                                    <div className="flex-grow min-w-0">
                                        <div className="inline-flex items-center bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider mb-3">
                                            {h.tag}
                                        </div>
                                        <h3 className="font-bold text-lg mb-2">{h.title}</h3>
                                        <p className="text-sm text-muted-foreground leading-relaxed">{h.desc}</p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── MODULI ───────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <Grid3x3 className="w-3.5 h-3.5" />
                            I moduli
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">
                            Tutto il ciclo del commercio,<br />
                            <span className="text-emerald-600 dark:text-emerald-400">in un unico sistema.</span>
                        </h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            Modulare per design — attivi solo ciò che serve oggi e aggiungi quando cresci.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                        {modules.map((m, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-7 text-left shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all flex flex-col">
                                <div className="w-12 h-12 rounded-xl flex items-center justify-center mb-4 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <m.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold text-base mb-2">{m.title}</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed mb-4 flex-grow">{m.desc}</p>
                                <div className="inline-flex items-center gap-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider self-start">
                                    <Sparkles className="w-3 h-3" />
                                    {m.ai}
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── AI ASSISTANT ─────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32" aria-labelledby="ai-assistant-heading">
                    <div className="bg-card border border-border rounded-3xl overflow-hidden shadow-sm hover:border-primary/30 hover:shadow-lg transition-all">
                        <div className="grid grid-cols-1 lg:grid-cols-2 items-center">

                            {/* Left: story + bullets */}
                            <div className="p-8 sm:p-12">
                                <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-wider mb-6">
                                    <Sparkles className="w-3.5 h-3.5" />
                                    AI Assistant
                                </div>
                                <h2 id="ai-assistant-heading" className="text-2xl md:text-3xl font-bold mb-4 leading-snug">
                                    Un collaboratore in più,<br />
                                    <span className="text-emerald-600 dark:text-emerald-400">sempre disponibile.</span>
                                </h2>
                                <p className="text-muted-foreground mb-8 leading-relaxed">
                                    Conosce tutta la tua azienda. Risponde in linguaggio naturale.
                                    Agisce sotto la tua supervisione — attivo su ogni schermata, per ogni ruolo.
                                </p>

                                <ul className="space-y-5">
                                    {[
                                        { icon: Search, title: "Interroga i dati in tempo reale", desc: "Ordini in ritardo, clienti top, articoli sotto scorta — risposta istantanea senza costruire report." },
                                        { icon: Zap, title: "Crea & Modifica con un testo", desc: "Offerte, ordini, RFQ, RMA, anagrafiche — sempre con conferma esplicita prima di scrivere." },
                                        { icon: Lightbulb, title: "Suggerisce & Ottimizza", desc: "Alternative a margine più alto, fornitori migliori, slotting ottimale. Oltre 50 strumenti specializzati." },
                                    ].map((item, i) => (
                                        <li key={i} className="flex items-start gap-3">
                                            <div className="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                                <item.icon className="w-4 h-4" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-sm mb-1">{item.title}</h4>
                                                <p className="text-xs text-muted-foreground leading-relaxed">{item.desc}</p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Right: chat mockup */}
                            <div className="bg-gradient-to-br from-emerald-500/5 to-teal-500/10 border-t lg:border-t-0 lg:border-l border-border p-6 sm:p-10 flex items-center justify-center min-h-[480px]">
                                <div className="w-full max-w-sm bg-background border border-border rounded-2xl overflow-hidden shadow-xl">
                                    {/* Header */}
                                    <div className="bg-muted/50 px-4 py-3 flex items-center gap-3 border-b border-border">
                                        <div className="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-[11px] font-bold">AI</div>
                                        <div className="flex-grow">
                                            <div className="text-xs font-bold">OmniFlow AI</div>
                                            <div className="text-[10px] text-muted-foreground">Il tuo assistente intelligente</div>
                                        </div>
                                        <div className="flex items-center gap-1.5 text-[10px] text-emerald-600 dark:text-emerald-400">
                                            <span className="w-2 h-2 rounded-full bg-emerald-500" />
                                            Online
                                        </div>
                                    </div>
                                    {/* Messages */}
                                    <div className="p-4 space-y-3 bg-muted/10">
                                        <div className="ml-auto max-w-[85%] bg-emerald-600 text-white text-[11px] px-3 py-2 rounded-2xl rounded-br-sm">
                                            Quali ordini sono in ritardo questa settimana?
                                        </div>
                                        <div className="max-w-[90%] bg-background border border-border text-[11px] text-foreground px-3 py-2 rounded-2xl rounded-bl-sm leading-relaxed">
                                            Trovati <strong>3 ordini in ritardo</strong>: ORD-0007 (Fratelli Esposito, +2gg), ORD-0009 (Centro Edile Brianza, +1gg), ORD-0011 (Hotel Mediterraneo, oggi). Preparo una comunicazione?
                                        </div>
                                        <div className="ml-auto max-w-[85%] bg-emerald-600 text-white text-[11px] px-3 py-2 rounded-2xl rounded-br-sm">
                                            Top prodotti per margine questo mese
                                        </div>
                                        <div className="max-w-[90%] bg-background border border-border text-[11px] text-foreground px-3 py-2 rounded-2xl rounded-bl-sm leading-relaxed">
                                            I <strong>5 prodotti con margine più alto</strong>: Travertino Romano (42%), Massello Autobloccante (38%), Rovere Parquet (37%). <strong>Smart Swap</strong> può aumentare la media del +15% su 3 offerte in corso.
                                        </div>
                                    </div>
                                    {/* Input */}
                                    <div className="border-t border-border p-3 flex items-center gap-2">
                                        <div className="flex-grow bg-muted/30 rounded-full px-3 py-1.5 text-[11px] text-muted-foreground">
                                            Chiedi qualsiasi cosa…
                                        </div>
                                        <button className="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center" aria-label="Invia">
                                            <Send className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* ─── RUOLI ────────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <Users className="w-3.5 h-3.5" />
                            Per chi
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">
                            Il vantaggio giusto<br />
                            <span className="text-emerald-600 dark:text-emerald-400">per ogni ruolo.</span>
                        </h2>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                        {roles.map((r, i) => {
                            const icons = [Building2, DollarSign, ShoppingCart, Forklift, Settings, PiggyBank];
                            const Icon = icons[i];
                            return (
                                <div key={i} className="bg-card border border-border rounded-3xl p-6 sm:p-8 shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <Icon className="w-5 h-5" />
                                        </div>
                                        <span className="inline-flex items-center bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                            {r.tag}
                                        </span>
                                    </div>
                                    <h3 className="font-bold text-lg mb-2">{r.title}</h3>
                                    <p className="text-sm text-muted-foreground leading-relaxed mb-4">{r.desc}</p>
                                    <p className="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                        <ArrowRight className="w-3.5 h-3.5" />
                                        {r.benefit}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </section>

                {/* ─── RISULTATI ────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <BarChart3 className="w-3.5 h-3.5" />
                            Risultati misurabili
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Numeri concreti.</h2>
                        <p className="text-muted-foreground">Performance reali sull'intero ciclo operativo.</p>
                    </div>

                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        {[
                            { value: "−70%", label: "tempo per generare un'offerta complessa" },
                            { value: "−40%", label: "stockout grazie alle previsioni AI" },
                            { value: "+15%", label: "marginalità media con Smart Swap" },
                            { value: "30s", label: "dal preventivo firmato all'ordine pronto" },
                        ].map((stat, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-6 sm:p-8 text-center shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                <div className="text-4xl sm:text-5xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-3">{stat.value}</div>
                                <div className="text-xs sm:text-sm text-muted-foreground leading-tight">{stat.label}</div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── SCREENSHOT DASHBOARD ─────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-12">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <Eye className="w-3.5 h-3.5" />
                            Il prodotto
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Un sistema bello quanto potente.</h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            Web-based, in tempo reale, modulare. Attivi solo ciò che ti serve.
                            L'AI integrata cresce con la tua azienda — più la usi, più ti conosce.
                        </p>
                    </div>

                    <div className="bg-card border border-border rounded-3xl overflow-hidden shadow-2xl relative">
                        <div className="absolute inset-0 bg-gradient-to-t from-emerald-600/10 via-transparent to-transparent pointer-events-none" />
                        <img
                            src="/assets/images/warehouse/dashboard.png"
                            alt="OmniFlow — Dashboard principale del gestionale AI Warehouse Intelligence: dashboard, AI Assistant, vendite, magazzino, ordini e analytics in un'unica interfaccia"
                            width="1907"
                            height="914"
                            className="w-full h-auto block"
                            loading="lazy"
                            decoding="async"
                        />
                    </div>
                </section>

                {/* ─── FAQ ──────────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <MessageSquare className="w-3.5 h-3.5" />
                            Domande Frequenti
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Hai domande su OmniFlow?</h2>
                        <p className="text-muted-foreground">Le risposte alle domande più comuni sul nostro gestionale modulare.</p>
                    </div>

                    <div className="max-w-3xl mx-auto space-y-3">
                        {faqs.map((item, i) => (
                            <div key={i} className="bg-card border border-border rounded-2xl overflow-hidden transition-all hover:border-primary/30">
                                <button
                                    className="w-full flex items-center justify-between gap-4 px-6 py-5 text-left"
                                    onClick={() => setOpenFaq(openFaq === i ? null : i)}
                                    aria-expanded={openFaq === i}
                                >
                                    <span className="font-semibold text-sm sm:text-base leading-snug">{item.q}</span>
                                    <ChevronDown className={`w-5 h-5 text-muted-foreground flex-shrink-0 transition-transform duration-300 ${openFaq === i ? 'rotate-180' : ''}`} />
                                </button>
                                {openFaq === i && (
                                    <div className="px-6 pb-5 text-sm text-muted-foreground leading-relaxed border-t border-border pt-4">
                                        {item.a}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── RELATED SOLUTIONS (internal linking SEO) ─────────────── */}
                <section className="mb-16 sm:mb-24" aria-labelledby="altre-soluzioni-heading">
                    <div className="text-center mb-8 sm:mb-12">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Le altre soluzioni Finch-AI
                        </div>
                        <h2 id="altre-soluzioni-heading" className="text-2xl md:text-3xl font-bold mb-3">
                            Completa il tuo stack AI per la PMI
                        </h2>
                        <p className="text-muted-foreground text-sm max-w-2xl mx-auto">
                            OmniFlow si integra con le altre soluzioni Finch-AI per una piattaforma AI end-to-end:
                            documenti, finanza e operazioni in un unico ecosistema.
                        </p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <Link
                            to="/soluzioni/document-intelligence"
                            className="bg-card border border-border rounded-3xl p-6 sm:p-8 shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all group"
                        >
                            <div className="flex items-center gap-3 mb-3">
                                <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <ClipboardList className="w-5 h-5" />
                                </div>
                                <span className="inline-flex items-center bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    Document Intelligence
                                </span>
                            </div>
                            <h3 className="font-bold text-lg mb-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                Automatizza l'estrazione dati da fatture, DDT e ricevute
                            </h3>
                            <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                                AI pronta all'uso per 11+ tipi di documenti aziendali, con app Android per operatori in campo
                                e integrazione diretta con OmniFlow.
                            </p>
                            <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                Scopri Document Intelligence
                                <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
                            </span>
                        </Link>
                        <Link
                            to="/soluzioni/finance-intelligence"
                            className="bg-card border border-border rounded-3xl p-6 sm:p-8 shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all group"
                        >
                            <div className="flex items-center gap-3 mb-3">
                                <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <BarChart3 className="w-5 h-5" />
                                </div>
                                <span className="inline-flex items-center bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    Finance Intelligence
                                </span>
                            </div>
                            <h3 className="font-bold text-lg mb-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                Analisi bilancio e indici finanziari in 3 minuti con AI
                            </h3>
                            <p className="text-sm text-muted-foreground leading-relaxed mb-3">
                                Carica il bilancio Excel, ottieni report OIC, 15+ indici finanziari (ROE, ROI, EBITDA, Z-Score Altman)
                                e una chat AI sui tuoi dati finanziari.
                            </p>
                            <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                Scopri Finance Intelligence
                                <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
                            </span>
                        </Link>
                    </div>
                </section>

                {/* ─── CTA FINALE ───────────────────────────────────────────── */}
                <section className="mb-12" aria-labelledby="cta-finale-heading">
                    <div className="bg-foreground rounded-[40px] p-7 sm:p-12 md:p-20 text-center text-background relative overflow-hidden shadow-2xl shadow-foreground/20">
                        <div className="absolute top-0 right-0 w-[200px] h-[200px] md:w-[400px] md:h-[400px] bg-[radial-gradient(circle,rgba(45,125,70,0.2)_0%,transparent_70%)] translate-x-1/4 -translate-y-1/4" />

                        <div className="relative z-10">
                            <h2 id="cta-finale-heading" className="text-2xl sm:text-3xl md:text-5xl font-bold mb-6">Vedi OmniFlow in 20 minuti.</h2>
                            <p className="text-background/70 text-base sm:text-lg mb-8 sm:mb-12">
                                Una demo personalizzata sul tuo settore. Nessun impegno, solo risposte concrete.
                            </p>

                            <div className="flex flex-wrap justify-center gap-3 sm:gap-4 mb-8 sm:mb-12">
                                {[
                                    { icon: ShieldCheck, text: "GDPR · Dati in EU" },
                                    { icon: Cloud, text: "Cloud o on-premise" },
                                    { icon: Users, text: "Onboarding dedicato" },
                                    { icon: PlugZap, text: "Integrazioni su misura" }
                                ].map((badge, i) => (
                                    <div key={i} className="flex items-center gap-2 bg-background/10 border border-background/20 px-5 py-3 rounded-full text-sm font-medium">
                                        <badge.icon className="w-4 h-4 text-primary" />
                                        {badge.text}
                                    </div>
                                ))}
                            </div>

                            <a
                                href="/#contatti"
                                className="inline-flex items-center gap-2 bg-primary text-primary-foreground px-6 py-4 sm:px-10 sm:py-5 rounded-2xl font-bold text-base sm:text-lg hover:brightness-110 transition-all shadow-xl shadow-primary/20 hover:scale-105 active:scale-95"
                            >
                                Prenota una Demo
                                <ArrowRight className="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </section>

            </div>
        </Layout>
    );
};

export default WarehouseIntelligence;
