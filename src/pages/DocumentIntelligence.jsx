import React, { useEffect, useState } from 'react';
import SEO from '../components/SEO';
import {
    ArrowRight,
    Printer,
    FileText,
    Keyboard,
    Search,
    Folder,
    Clock,
    Download,
    Eye,
    CheckCircle,
    Upload,
    Cpu,
    CheckSquare,
    Rocket,
    ShieldCheck,
    PlugZap,
    MapPin,
    Zap,
    MessageSquare,
    Link as LinkIcon,
    ReceiptText,
    UserCheck,
    Truck,
    Landmark,
    BadgeMinus,
    HeartPulse,
    Smartphone,
    ScanLine,
    PlayCircle,
    PackageCheck,
    Sparkles,
    PlusCircle,
    Calculator,
    Tag,
    Layers,
    Camera,
    Wifi,
    Battery,
    Receipt,
    IdCard,
    ShoppingCart,
    Banknote,
    Contact,
    FileSignature,
    ChevronDown,
} from 'lucide-react';
import Layout from '../components/Layout';
import VideoModal from '../components/VideoModal';

const DocumentIntelligence = () => {
    const [isAnnual, setIsAnnual] = useState(true);
    const [openFaq, setOpenFaq] = useState(null);
    const [isVideoModalOpen, setIsVideoModalOpen] = useState(false);

    const plans = [
        {
            display_name: 'Demo',
            description: 'Piano demo gratuito con limiti ridotti. Solo modelli AI generici disponibili.',
            annual_monthly_equivalent: 'Gratis',
            base_monthly_cost: 'Gratis',
            contact_us_pricing: false,
            is_free: true,
            pages_per_month: 20,
            extra_page_cost: 0.150,
            max_document_types: 1,
            max_users: 2,
            email_polling_enabled: false,
            api_transfer_enabled: true,
            ftp_transfer_enabled: true,
            includes_custom_models: false,
            sort_order: 10,
            button_text: 'Inizia gratis'
        },
        {
            display_name: 'Basic',
            description: 'Piano base per piccole aziende. Solo modelli AI generici disponibili.',
            annual_monthly_equivalent: '41',
            base_monthly_cost: '49',
            contact_us_pricing: false,
            is_free: false,
            pages_per_month: 200,
            extra_page_cost: 0.200,
            max_document_types: 2,
            max_users: 5,
            email_polling_enabled: false,
            api_transfer_enabled: true,
            ftp_transfer_enabled: true,
            includes_custom_models: false,
            sort_order: 20,
            button_text: 'Inizia ora'
        },
        {
            display_name: 'Business',
            description: 'Piano business per aziende in crescita. Modelli AI personalizzati disponibili su richiesta: maggiore precisione, accuratezza superiore e riduzione degli errori.',
            annual_monthly_equivalent: '107',
            base_monthly_cost: '129',
            contact_us_pricing: false,
            is_free: false,
            pages_per_month: 800,
            extra_page_cost: 0.170,
            max_document_types: 5,
            max_users: 10,
            email_polling_enabled: false,
            api_transfer_enabled: true,
            ftp_transfer_enabled: true,
            includes_custom_models: true,
            sort_order: 40,
            button_text: 'Inizia ora'
        },
        {
            display_name: 'Professional',
            description: 'Piano professionale con email polling. Modelli AI personalizzati inclusi: precisione superiore, accuratezza elevata e riduzione significativa degli errori.',
            annual_monthly_equivalent: '207',
            base_monthly_cost: '249',
            contact_us_pricing: false,
            is_free: false,
            pages_per_month: 2000,
            extra_page_cost: 0.140,
            max_document_types: 10,
            max_users: 20,
            email_polling_enabled: true,
            api_transfer_enabled: true,
            ftp_transfer_enabled: true,
            includes_custom_models: true,
            sort_order: 50,
            button_text: 'Inizia ora'
        },
        {
            display_name: 'Enterprise',
            description: 'Piano enterprise per grandi organizzazioni. Modelli AI personalizzati inclusi: massima precisione, accuratezza e riduzione degli errori. Contattaci per un preventivo personalizzato.',
            annual_monthly_equivalent: null,
            base_monthly_cost: null,
            contact_us_pricing: true,
            is_free: false,
            pages_per_month: 'Illimitate',
            extra_page_cost: null,
            max_document_types: 'Illimitati',
            max_users: 'Illimitati',
            email_polling_enabled: true,
            api_transfer_enabled: true,
            ftp_transfer_enabled: true,
            includes_custom_models: true,
            sort_order: 60,
            button_text: 'Contattaci'
        },
    ];

    useEffect(() => {
        window.scrollTo(0, 0);
    }, []);

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
            "description": "Automazione AI per l'estrazione dati da ogni tipo di documento. Pronto all'uso per fatture, ricevute, documenti d'identità, DDT, estratti conto e altro. Regole in linguaggio naturale per campi custom. App Android per operatori in campo. 97% accuratezza, human-in-the-loop, integrazione ERP.",
            "featureList": [
                "Pronto all'uso per 11+ tipologie di documenti",
                "97% di accuratezza nel riconoscimento dati",
                "Da 11 minuti a 8 secondi per documento",
                "Regole di estrazione in linguaggio naturale",
                "Campi personalizzati e derivati per ERP",
                "App Android per operatori in campo",
                "Verifica human-in-the-loop",
                "Integrazione ERP via API e Webhook",
                "Elaborazione automatica 24/7",
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
            "step": [
                { "@type": "HowToStep", "position": 1, "name": "Ricezione del documento", "text": "Il documento viene ricevuto tramite scansione, upload manuale o ricezione automatica via email." },
                { "@type": "HowToStep", "position": 2, "name": "Riconoscimento AI", "text": "L'intelligenza artificiale estrae automaticamente tutti i campi e le tabelle dal documento, applicando le regole configurate." },
                { "@type": "HowToStep", "position": 3, "name": "Verifica human-in-the-loop", "text": "L'operatore rivede i dati estratti e conferma prima del trasferimento. Nessun errore raggiunge il gestionale senza approvazione." },
                { "@type": "HowToStep", "position": 4, "name": "Trasferimento al gestionale", "text": "I dati verificati vengono inviati automaticamente al gestionale tramite API REST o webhook, nei campi esatti richiesti dal sistema." }
            ]
        },
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "Quali tipi di documenti supporta Document Intelligence?",
                    "acceptedAnswer": { "@type": "Answer", "text": "Document Intelligence supporta fatture italiane e internazionali, ricevute e scontrini, documenti d'identità (carta d'identità, passaporto, patente), DDT e bolle di consegna, estratti conto bancari, ordini d'acquisto, note di credito, tessera sanitaria, cedolini paga, biglietti da visita e contratti." }
                },
                {
                    "@type": "Question",
                    "name": "Quanto tempo ci vuole per configurare Document Intelligence su un nuovo tipo di documento?",
                    "acceptedAnswer": { "@type": "Answer", "text": "La configurazione è guidata e veloce: si scelgono i campi da estrarre, si definiscono eventuali regole in linguaggio naturale e si configura la struttura dati da inviare al gestionale. Non è richiesta nessuna competenza tecnica o scrittura di codice." }
                },
                {
                    "@type": "Question",
                    "name": "Document Intelligence si integra con il mio gestionale ERP?",
                    "acceptedAnswer": { "@type": "Answer", "text": "Sì. Document Intelligence si integra con qualsiasi gestionale tramite API REST o webhook. È possibile configurare la struttura esatta dei dati da inviare, inclusi campi personalizzati e derivati, per piena compatibilità con SAP, Zucchetti, TeamSystem, Sage e altri." }
                },
                {
                    "@type": "Question",
                    "name": "È possibile definire campi personalizzati non presenti nel documento originale?",
                    "acceptedAnswer": { "@type": "Answer", "text": "Sì. Document Intelligence permette di definire campi personalizzati, derivati e di arricchimento usando il linguaggio naturale. Ad esempio: 'Classifica il fornitore come strategico se l'importo supera €5.000' oppure 'Calcola l'imponibile netto sottraendo lo sconto dall'importo totale'." }
                },
                {
                    "@type": "Question",
                    "name": "Esiste un'app mobile per acquisire documenti in campo?",
                    "acceptedAnswer": { "@type": "Answer", "text": "Sì, è disponibile un'app Android che permette di acquisire documenti direttamente dalla fotocamera del telefono. È ideale per operatori di magazzino e logistica che devono registrare DDT e bolle di consegna direttamente sul campo, senza tornare in ufficio." }
                },
                {
                    "@type": "Question",
                    "name": "Quanto costa Document Intelligence?",
                    "acceptedAnswer": { "@type": "Answer", "text": "Document Intelligence parte da €49/mese per il piano Basic (400 pagine/mese). È disponibile un piano Demo gratuito con 20 pagine/mese per testare il servizio. I piani Business (€129/mese) e Professional (€249/mese) includono volumi maggiori e modelli AI personalizzati. Per grandi volumi è disponibile un piano Enterprise su misura." }
                }
            ]
        }
    ];

    return (
        <Layout>
            <SEO
                title="Document Intelligence AI | Automazione Documenti per PMI Italiane — Finch-AI"
                description="Automatizza fatture, DDT, ricevute e 11+ tipi di documento con AI. Configurazione guidata senza codice, campi personalizzati in linguaggio naturale, integrazione ERP, app Android per operatori in campo. Da €49/mese."
                keywords="document intelligence, automazione documenti AI, estrazione dati fatture automatica, OCR intelligente PMI, software gestione documenti AI, digitalizzazione fatture passive, automazione DDT bolle consegna, integrazione ERP documenti, human-in-the-loop OCR, estrazione dati ricevute, riconoscimento documenti identità AI, app acquisizione documenti android, campi personalizzati estrazione dati, automazione data entry ufficio, gestione documentale cloud PMI italiana"
                canonical="https://finch-ai.it/soluzioni/document-intelligence"
                jsonLd={docJsonLd}
            />
            <div className="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-24">

                {/* ─── HERO ─────────────────────────────────────────────────── */}
                <section className="text-center mb-12 sm:mb-24">
                    <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-8 animate-in fade-in slide-in-from-top-4 duration-1000">
                        <ScanLine className="w-4 h-4" />
                        Document Intelligence · AI per Documenti Aziendali
                    </div>

                    <h1 className="text-3xl sm:text-4xl md:text-6xl font-extrabold leading-tight mb-8 animate-in fade-in slide-in-from-top-6 duration-1000 fill-mode-both">
                        Documenti da gestire ogni giorno.<br />
                        <span className="relative inline-block">
                            <span className="relative z-10 text-emerald-600 dark:text-emerald-400">Finch-AI li legge per te.</span>
                            <span className="absolute bottom-1 left-0 right-0 h-3 bg-emerald-500/10 -z-0 rounded-sm"></span>
                        </span>
                    </h1>

                    <p className="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto mb-10 animate-in fade-in slide-in-from-top-8 duration-1000 fill-mode-both">
                        Pronto all'uso in <strong className="text-emerald-600 dark:text-emerald-400">3 minuti</strong>. Configurazione quasi interamente automatica, immediata anche per i non esperti.<br />
                        Document Intelligence riconosce, estrae e verifica i dati da ogni tipo di documento automaticamente.
                    </p>

                    {/* CTA Buttons */}
                    <div className="flex flex-wrap justify-center gap-4 mb-12 animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
                        <a href="https://documentintelligence.finch-ai.it/" target="_blank" rel="noopener noreferrer"
                            className="inline-flex items-center gap-2 bg-emerald-600 text-white px-5 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-500/20">
                            <Rocket className="w-5 h-5" />
                            Inizia Gratis
                        </a>
                        <button onClick={() => setIsVideoModalOpen(true)}
                            className="inline-flex items-center gap-2 bg-card border border-border text-foreground px-5 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-muted transition-all">
                            <PlayCircle className="w-5 h-5 text-emerald-500" />
                            Scopri come funziona
                        </button>
                    </div>

                    {/* Stats */}
                    <div className="flex flex-wrap justify-center gap-3 sm:gap-6 mb-8 animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
                        {[
                            { label: "Tempo risparmiato sull'inserimento dati", value: "-75%" },
                            { label: "Velocità in più nella lavorazione Documenti", value: "90%" },
                            { label: "Elaborazione automatica continua", value: "24/7" }
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
                            { icon: <Zap className="w-4 h-4" />, text: "Pronto in 3 minuti" },
                            { icon: <ShieldCheck className="w-4 h-4" />, text: "GDPR · Dati in EU" },
                            { icon: <Smartphone className="w-4 h-4" />, text: "App Android inclusa" },
                            { icon: <PlugZap className="w-4 h-4" />, text: "API per ogni gestionale" },
                        ].map((pill, i) => (
                            <div key={i} className="inline-flex items-center gap-2 bg-card border border-border px-4 py-2 rounded-full text-sm text-muted-foreground shadow-sm">
                                <span className="text-emerald-600 dark:text-emerald-400">{pill.icon}</span>
                                {pill.text}
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── VIDEO DEMO ───────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="bg-card border border-border rounded-3xl overflow-hidden shadow-2xl relative group">
                        <div className="absolute inset-0 bg-emerald-600/5 pointer-events-none group-hover:bg-transparent transition-colors" />
                        <div className="aspect-video w-full">
                            <iframe
                                src="/assets/videos/document-intelligence-demo.html"
                                title="Document Intelligence Marketing Video"
                                className="w-full h-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowFullScreen
                            />
                        </div>
                    </div>
                </section>

                {/* ─── BEFORE / AFTER ──────────────────────────────────────── */}
                <section className="grid grid-cols-1 lg:grid-cols-[1fr,auto,1fr] gap-0 items-stretch mb-16 sm:mb-32">
                    <div className="bg-card border border-border rounded-3xl p-5 sm:p-8 shadow-sm relative overflow-hidden flex flex-col transition-all hover:-translate-y-1 hover:shadow-md">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-muted" />
                        <div className="text-[11px] font-bold text-muted-foreground uppercase tracking-[1.5px] mb-4">❌ Prima</div>
                        <h3 className="text-2xl font-bold mb-4">Ore perse ogni giorno a copiare dati a mano</h3>
                        <p className="text-sm text-muted-foreground mb-8">Ogni fattura, DDT o scontrino va aperto, letto, trascritto campo per campo nel gestionale. Errori frequenti, colli di bottiglia.</p>

                        <div className="bg-muted/30 border border-border rounded-xl p-4 space-y-3 mt-auto">
                            {[
                                { icon: Printer, label: "Scannerizza o scarica il file", time: "2 min" },
                                { icon: FileText, label: "Apri il documento, cerca i dati", time: "2 min" },
                                { icon: Keyboard, label: "Trascrivi nel gestionale", time: "4 min" },
                                { icon: Search, label: "Ricontrolla per errori", time: "2 min" },
                                { icon: Folder, label: "Archivia il documento", time: "1 min" },
                                { icon: Clock, label: "Totale per Documento", time: "~11 min", bold: true }
                            ].map((step, i) => (
                                <div key={i} className="flex items-center justify-between py-2 border-b border-border/50 last:border-0">
                                    <div className="flex items-center gap-3">
                                        <step.icon className="w-4 h-4 text-muted-foreground" />
                                        <span className={`text-[13px] ${step.bold ? 'font-bold text-foreground' : 'text-muted-foreground'}`}>{step.label}</span>
                                    </div>
                                    <span className={`text-[11px] px-2 py-0.5 rounded font-mono ${step.bold ? 'bg-destructive text-destructive-foreground' : 'bg-destructive/10 text-destructive'}`}>
                                        {step.time}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="flex items-center justify-center p-8">
                        <div className="w-14 h-14 bg-emerald-600 rounded-full flex items-center justify-center text-white shadow-lg shadow-emerald-600/20 animate-pulse">
                            <ArrowRight className="w-6 h-6" />
                        </div>
                    </div>

                    <div className="bg-card border border-emerald-500/50 rounded-3xl p-5 sm:p-8 shadow-sm relative overflow-hidden flex flex-col transition-all hover:-translate-y-1 hover:shadow-md">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-700" />
                        <div className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-[1.5px] mb-4">✅ Dopo — con Finch-AI</div>
                        <h3 className="text-2xl font-bold mb-4">Dati estratti in automatico, pronti da verificare</h3>
                        <p className="text-sm text-muted-foreground mb-8">Scannerizzi, carichi o invii via email — ogni documento viene elaborato automaticamente. Tu verifichi solo il risultato.</p>

                        <div className="bg-muted/20 border border-emerald-500/20 rounded-xl p-6 mt-auto">
                            <div className="flex flex-wrap gap-2 mb-6">
                                {[
                                    { icon: Download, label: "In Elaborazione", cls: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400" },
                                    { icon: Eye, label: "Da Verificare", cls: "bg-orange-500/10 text-orange-600 dark:text-orange-400" },
                                    { icon: CheckCircle, label: "Verificato", cls: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400" },
                                    { icon: Upload, label: "Trasferito", cls: "bg-purple-500/10 text-purple-600 dark:text-purple-400" },
                                ].map((s, i) => (
                                    <React.Fragment key={i}>
                                        <span className={`${s.cls} px-3 py-1.5 rounded-full text-[11px] font-semibold flex items-center gap-1.5`}>
                                            <s.icon className="w-3 h-3" /> {s.label}
                                        </span>
                                        {i < 3 && <span className="text-muted-foreground/30 self-center"><ArrowRight className="w-3 h-3" /></span>}
                                    </React.Fragment>
                                ))}
                            </div>

                            <div className="grid grid-cols-2 gap-3 mb-6">
                                {[
                                    { label: "Fornitore", value: "Rossi S.r.l." },
                                    { label: "Nr. Documento", value: "2024/00847" },
                                    { label: "Data", value: "15/01/2025" },
                                    { label: "Articoli", value: "12 righe" }
                                ].map((field, i) => (
                                    <div key={i} className="bg-emerald-500/5 p-3 rounded-lg border border-emerald-500/10">
                                        <div className="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">{field.label}</div>
                                        <div className="font-mono text-[12px] text-foreground">{field.value}</div>
                                    </div>
                                ))}
                            </div>

                            <div className="bg-emerald-500/10 p-3 rounded-lg text-[12px] text-emerald-600 dark:text-emerald-400 flex items-center gap-2 font-medium border border-emerald-500/20">
                                <CheckCircle className="w-4 h-4" /> Confidence 97% · Dati estratti in 8 secondi
                            </div>
                        </div>
                    </div>
                </section>

                {/* ─── COME FUNZIONA ────────────────────────────────────────── */}
                <section id="come-funziona" className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Come Funziona
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Dal documento al gestionale in quattro passaggi automatici</h2>
                        <p className="text-muted-foreground">Nessun operatore coinvolto fino alla fase di verifica.</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {[
                            { num: 1, icon: Download, title: "Ricezione", desc: "Scansione, upload manuale o ricezione via email.", tech: "Scan + Upload + Email", colorClass: "bg-emerald-500", textClass: "text-emerald-600 dark:text-emerald-400", bgClass: "bg-emerald-500/10" },
                            { num: 2, icon: Cpu, title: "Riconoscimento AI", desc: "L'AI estrae automaticamente campi e tabelle.", tech: "Document AI", colorClass: "bg-orange-500", textClass: "text-orange-600 dark:text-orange-400", bgClass: "bg-orange-500/10" },
                            { num: 3, icon: CheckSquare, title: "Verifica", desc: "L'operatore rivede i dati estratti e conferma.", tech: "Human-in-the-loop", colorClass: "bg-emerald-500", textClass: "text-emerald-600 dark:text-emerald-400", bgClass: "bg-emerald-500/10" },
                            { num: 4, icon: Rocket, title: "Trasferimento", desc: "I dati vengono trasferiti al gestionale via API.", tech: "API + Webhook", colorClass: "bg-purple-500", textClass: "text-purple-600 dark:text-purple-400", bgClass: "bg-purple-500/10" }
                        ].map((step, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md hover:border-primary/30">
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-lg mx-auto mb-6 ${step.colorClass}`}>
                                    {step.num}
                                </div>
                                <div className={`w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4 ${step.bgClass} ${step.textClass}`}>
                                    <step.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold text-sm mb-2">{step.title}</h3>
                                <p className="text-xs text-muted-foreground leading-relaxed mb-4">{step.desc}</p>
                                <span className={`inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${step.bgClass} ${step.textClass}`}>
                                    {step.tech}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── INFOGRAFICA ──────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Il Flusso Completo
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Dalla ricezione all'integrazione nel gestionale</h2>
                        <p className="text-muted-foreground">Ogni passaggio automatizzato, ogni eccezione gestita</p>
                    </div>
                    <div className="bg-card border border-border rounded-3xl p-4 sm:p-8 shadow-sm overflow-hidden">
                        <img
                            src="/assets/images/infografica_document_intelligence.png"
                            alt="Infografica flusso Document Intelligence — dalla ricezione all'integrazione ERP"
                            className="w-full h-auto rounded-xl"
                            loading="lazy"
                        />
                    </div>
                </section>

                {/* ─── TIPOLOGIE DI DOCUMENTO ───────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <PackageCheck className="w-3.5 h-3.5" />
                            Pronto all'Uso · Zero Configurazione
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Tipologie di Documento Supportate</h2>
                        <p className="text-muted-foreground max-w-3xl mx-auto">
                            Per ogni tipologia, l'attivazione è guidata e veloce: scegli i campi da estrarre, definisci eventuali regole e configura la struttura dati da inviare al gestionale.
                            Nessuna competenza tecnica, nessun codice — un processo strutturato che garantisce estrazioni precise e affidabili fin dal primo documento.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 mb-8">
                        {[
                            {
                                icon: ReceiptText,
                                title: "Fatture",
                                useCase: "Contabilità Fornitori · AP Automation",
                                desc: "Fornitore, P.IVA, importo, IVA, scadenza, numero documento, righe dettaglio. Supporta fatture italiane e internazionali, elettroniche e PDF scansionati.",
                            },
                            {
                                icon: Receipt,
                                title: "Ricevute e Scontrini",
                                useCase: "Note Spese Aziendali",
                                desc: "Rimborsi dipendenti e riconciliazione automatica delle spese. Estrae data, importo, categoria e punto vendita da qualsiasi ricevuta.",
                            },
                            {
                                icon: IdCard,
                                title: "Documenti d'Identità",
                                useCase: "KYC · Onboarding Clienti",
                                desc: "Carta d'identità, passaporto, patente di guida. Verifica di identità in pochi secondi — ideale per banche, assicurazioni e noleggi.",
                            },
                            {
                                icon: Truck,
                                title: "DDT e Bolle di Consegna",
                                useCase: "Logistica · Gestione Magazzino",
                                desc: "Ricezione merci, codici articolo, quantità e aggiornamento automatico delle scorte. Integrazione diretta con WMS e gestionali logistici.",
                            },
                            {
                                icon: Landmark,
                                title: "Estratti Conto Bancari",
                                useCase: "Riconciliazione Bancaria Automatica",
                                desc: "Tutti i movimenti bancari con classificazione automatica delle categorie e abbinamento partite aperte.",
                            },
                            {
                                icon: ShoppingCart,
                                title: "Ordini d'Acquisto",
                                useCase: "Gestione Approvvigionamenti",
                                desc: "Registrazione ordini fornitori direttamente nel gestionale. Tracciamento stato ordine e confronto con DDT di consegna.",
                            },
                            {
                                icon: BadgeMinus,
                                title: "Note di Credito",
                                useCase: "Storno · Gestione Partite",
                                desc: "Storno fatture e riconciliazione automatica delle partite aperte. Collegamento automatico alla fattura originale.",
                            },
                            {
                                icon: HeartPulse,
                                title: "Tessera Sanitaria",
                                useCase: "Accettazione Pazienti · Strutture Sanitarie",
                                desc: "Codice fiscale, nome e data di nascita per accettazione rapida. Ideale per ambulatori, cliniche e studi medici.",
                            },
                            {
                                icon: Banknote,
                                title: "Cedolini Paga",
                                useCase: "Gestione Risorse Umane",
                                desc: "Stipendi, trattenute, contributi INPS/INAIL e dati per elaborazione HR. Supporta tutti i principali CCNL.",
                            },
                            {
                                icon: Contact,
                                title: "Biglietti da Visita",
                                useCase: "Alimentazione CRM · Gestione Contatti",
                                desc: "Nome, azienda, ruolo, email, telefono e indirizzo. Ogni biglietto acquisito con la fotocamera diventa un contatto nel CRM.",
                            },
                            {
                                icon: FileSignature,
                                title: "Contratti",
                                useCase: "Gestione Documentale · Archiviazione",
                                desc: "Parti contraenti, date, scadenze e clausole chiave. Organizzazione automatica nell'archivio documentale aziendale.",
                            },
                        ].map((doc, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-8 text-left shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                <div className="w-12 h-12 rounded-xl flex items-center justify-center mb-4 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <doc.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold text-lg mb-2">{doc.title}</h3>
                                <div className="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider mb-3">
                                    <Layers className="w-3 h-3" />
                                    {doc.useCase}
                                </div>
                                <p className="text-sm text-muted-foreground leading-relaxed">{doc.desc}</p>
                            </div>
                        ))}
                    </div>

                    {/* Custom models callout */}
                    <div className="bg-gradient-to-r from-orange-500/5 to-amber-500/5 border border-orange-500/20 rounded-3xl p-6 sm:p-10 flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div className="w-14 h-14 rounded-2xl flex items-center justify-center bg-orange-500/10 text-orange-600 dark:text-orange-400 flex-shrink-0">
                            <Sparkles className="w-7 h-7" />
                        </div>
                        <div className="flex-grow">
                            <h3 className="font-bold text-xl mb-2">Documenti proprietari? Nessun problema.</h3>
                            <p className="text-sm text-muted-foreground leading-relaxed">
                                Per esigenze specifiche, Finch-AI può sviluppare modelli personalizzati per qualsiasi documento proprietario o layout aziendale.{' '}
                                <strong className="text-foreground">Nessuna competenza tecnica richiesta da parte tua.</strong>
                            </p>
                        </div>
                        <a href="https://documentintelligence.finch-ai.it/"
                            className="inline-flex items-center gap-2 bg-orange-600 text-white px-6 py-3 rounded-full font-bold hover:bg-orange-500 transition-all flex-shrink-0 whitespace-nowrap shadow-lg shadow-orange-500/20">
                            Richiedi Modello Custom
                            <ArrowRight className="w-4 h-4" />
                        </a>
                    </div>
                </section>

                {/* ─── REGOLE IN LINGUAGGIO NATURALE ───────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <MessageSquare className="w-3.5 h-3.5" />
                            Nessun Codice · Solo Linguaggio Naturale
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">
                            Campi Personalizzati<br />
                            <span className="text-emerald-600 dark:text-emerald-400">in Linguaggio Naturale</span>
                        </h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            Definisci regole di estrazione scrivendo esattamente come parleresti a un collega.
                            L'AI capisce il contesto aziendale e alimenta il tuo ERP con i campi esatti di cui ha bisogno.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">

                        {/* Left: 3 use-case cards */}
                        <div className="space-y-5">
                            {[
                                {
                                    icon: PlusCircle,
                                    color: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
                                    borderColor: "border-emerald-500/20",
                                    label: "Campi Personalizzati",
                                    desc: "Crea campi che non esistono nel documento originale ma che il tuo sistema gestionale richiede.",
                                    example: '"Estrai il codice centro di costo dal campo riferimento ordine. Il formato è CC-XXXX."',
                                },
                                {
                                    icon: Calculator,
                                    color: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
                                    borderColor: "border-blue-500/20",
                                    label: "Campi Derivati",
                                    desc: "Calcola automaticamente valori a partire da altri campi già estratti dal documento.",
                                    example: '"Calcola l\'imponibile netto sottraendo lo sconto dall\'importo totale della fattura."',
                                },
                                {
                                    icon: Tag,
                                    color: "bg-purple-500/10 text-purple-600 dark:text-purple-400",
                                    borderColor: "border-purple-500/20",
                                    label: "Campi di Arricchimento",
                                    desc: "Aggiungi classificazioni e metadati intelligenti basati sulle tue regole di business.",
                                    example: '"Classifica il fornitore come \'strategico\' se l\'importo supera €5.000."',
                                },
                            ].map((item, i) => (
                                <div key={i} className="bg-card border border-border rounded-3xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                    <div className="flex items-start gap-4">
                                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${item.color}`}>
                                            <item.icon className="w-5 h-5" />
                                        </div>
                                        <div className="flex-grow min-w-0">
                                            <h3 className="font-bold mb-1">{item.label}</h3>
                                            <p className="text-sm text-muted-foreground mb-4 leading-relaxed">{item.desc}</p>
                                            <div className={`bg-muted/40 border-l-2 border-primary rounded-r-lg px-4 py-3`}>
                                                <p className="text-xs font-mono text-foreground/80 italic leading-relaxed">{item.example}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Right: Rule editor mockup */}
                        <div className="bg-card border border-border rounded-3xl overflow-hidden shadow-xl">
                            {/* Window chrome */}
                            <div className="bg-muted/50 border-b border-border px-4 py-3 flex items-center gap-2">
                                <div className="w-3 h-3 rounded-full bg-destructive/50" />
                                <div className="w-3 h-3 rounded-full bg-yellow-400/60" />
                                <div className="w-3 h-3 rounded-full bg-emerald-500/60" />
                                <span className="ml-3 text-[11px] text-muted-foreground font-mono">
                                    Regole di Estrazione — Fatture Fornitore
                                </span>
                            </div>

                            <div className="p-6 space-y-5">
                                {/* Active rule */}
                                <div>
                                    <div className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <CheckCircle className="w-3 h-3 text-emerald-500" /> Regola Attiva
                                    </div>
                                    <div className="bg-emerald-500/5 border border-emerald-500/20 rounded-xl p-4">
                                        <div className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">
                                            Campo: centro_di_costo
                                        </div>
                                        <p className="text-sm text-foreground/80 font-mono leading-relaxed italic">
                                            "Estrai il codice centro di costo dal campo riferimento ordine. Il formato è CC-XXXX."
                                        </p>
                                        <div className="mt-3">
                                            <span className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded-full text-[10px] font-semibold">
                                                ✓ CC-0042 · Confermato su 234 documenti
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-border" />

                                {/* New rule being typed */}
                                <div>
                                    <div className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <PlusCircle className="w-3 h-3 text-primary" /> Nuova Regola
                                    </div>
                                    <div className="bg-muted/20 border border-dashed border-primary/40 rounded-xl p-4">
                                        <div className="text-[11px] font-bold text-primary uppercase tracking-wider mb-3">
                                            Campo: fornitore_strategico
                                        </div>
                                        <div className="bg-background border border-border rounded-lg p-3 min-h-[64px] text-sm text-foreground/80 font-mono leading-relaxed">
                                            Classifica il fornitore come 'strategico' se l'importo supera €5.000
                                            <span className="inline-block w-0.5 h-4 bg-primary ml-0.5 animate-pulse align-middle" />
                                        </div>
                                        <div className="mt-3 flex items-center justify-between gap-3">
                                            <span className="text-[11px] text-muted-foreground flex items-center gap-1.5 flex-shrink-0">
                                                <Sparkles className="w-3 h-3 text-primary" />
                                                L'AI comprende automaticamente la logica
                                            </span>
                                            <div className="bg-primary text-primary-foreground px-3 py-1.5 rounded-lg text-[11px] font-bold opacity-90 flex-shrink-0">
                                                Salva Regola
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Output preview */}
                                <div className="bg-muted/20 border border-border rounded-xl p-4">
                                    <div className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                        <PlugZap className="w-3 h-3 text-primary" /> Anteprima Output → ERP
                                    </div>
                                    <div className="grid grid-cols-2 gap-2">
                                        {[
                                            { key: "importo_totale", val: "€ 7.450,00" },
                                            { key: "imponibile_netto", val: "€ 6.803,28" },
                                            { key: "centro_di_costo", val: "CC-0042" },
                                            { key: "fornitore_strategico", val: "✓ strategico", highlight: true },
                                        ].map((field, i) => (
                                            <div key={i} className={`rounded-lg p-2 border ${field.highlight ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-muted/30 border-border'}`}>
                                                <div className="text-[9px] font-bold uppercase tracking-wider text-muted-foreground mb-0.5">{field.key}</div>
                                                <div className={`text-xs font-mono font-bold ${field.highlight ? 'text-emerald-600 dark:text-emerald-400' : 'text-foreground'}`}>{field.val}</div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* ─── APP ANDROID ──────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            <Smartphone className="w-3.5 h-3.5" />
                            App Android · Sempre con Te
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">
                            In magazzino. Sul campo.<br />
                            <span className="text-emerald-600 dark:text-emerald-400">Ovunque tu riceva documenti.</span>
                        </h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            L'app Android di Document Intelligence porta la potenza dell'AI direttamente in campo —
                            nessun PC, nessun scanner, nessun ritardo.
                        </p>
                    </div>

                    <div className="bg-card border border-border rounded-3xl overflow-hidden shadow-sm hover:border-primary/30 hover:shadow-lg transition-all">
                        <div className="grid grid-cols-1 lg:grid-cols-2 items-center">

                            {/* Left: story + bullets */}
                            <div className="p-8 sm:p-12">
                                <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-wider mb-6">
                                    <Truck className="w-3.5 h-3.5" />
                                    Scenario: Ricezione Merci in Magazzino
                                </div>
                                <h3 className="text-2xl font-bold mb-4 leading-snug">
                                    L'operatore inquadra il DDT con il telefono. Il gestionale si aggiorna da solo.
                                </h3>
                                <p className="text-muted-foreground mb-8 leading-relaxed">
                                    Non serve tornare in ufficio, non serve un PC. L'operatore di magazzino apre l'app,
                                    punta la fotocamera sul documento di trasporto e in pochi secondi tutti i dati sono
                                    estratti, verificati e pronti per il WMS.
                                </p>

                                <ul className="space-y-4 mb-8">
                                    {[
                                        { icon: Camera, text: "Acquisizione con fotocamera — nessun scanner necessario" },
                                        { icon: Wifi, text: "Funziona in mobilità — sincronizzazione automatica al server" },
                                        { icon: CheckSquare, text: "Verifica human-in-the-loop direttamente su schermo" },
                                        { icon: Zap, text: "Dati nel gestionale in meno di 30 secondi" },
                                    ].map((item, i) => (
                                        <li key={i} className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                                <item.icon className="w-4 h-4" />
                                            </div>
                                            <span className="text-sm text-foreground">{item.text}</span>
                                        </li>
                                    ))}
                                </ul>

                                <a href="https://documentintelligence.finch-ai.it/"
                                    className="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-full font-bold hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-500/20">
                                    Scopri l'App Android
                                    <ArrowRight className="w-4 h-4" />
                                </a>
                            </div>

                            {/* Right: phone mockup */}
                            <div className="bg-gradient-to-br from-emerald-500/5 to-teal-500/10 border-t lg:border-t-0 lg:border-l border-border p-8 sm:p-12 flex items-center justify-center min-h-[420px]">
                                <div className="relative">
                                    {/* Phone frame */}
                                    <div className="w-56 bg-foreground rounded-[32px] p-2 shadow-2xl shadow-foreground/20">
                                        {/* Screen */}
                                        <div className="bg-background rounded-[26px] overflow-hidden">
                                            {/* Status bar */}
                                            <div className="bg-muted/80 px-4 py-2 flex items-center justify-between">
                                                <span className="text-[9px] text-muted-foreground font-mono">9:41</span>
                                                <div className="flex items-center gap-1">
                                                    <Wifi className="w-3 h-3 text-muted-foreground" />
                                                    <Battery className="w-3 h-3 text-muted-foreground" />
                                                </div>
                                            </div>
                                            {/* App header */}
                                            <div className="bg-emerald-600 px-4 py-3 flex items-center gap-2">
                                                <ScanLine className="w-4 h-4 text-white" />
                                                <span className="text-white text-[11px] font-bold">Document Intelligence</span>
                                            </div>
                                            {/* Camera viewfinder */}
                                            <div className="relative bg-slate-900 h-36 flex items-center justify-center overflow-hidden">
                                                <div className="absolute inset-3 border border-white/10 rounded-md" />
                                                {/* Corner brackets */}
                                                <div className="absolute top-3 left-3 w-5 h-5 border-t-2 border-l-2 border-emerald-400 rounded-tl-sm" />
                                                <div className="absolute top-3 right-3 w-5 h-5 border-t-2 border-r-2 border-emerald-400 rounded-tr-sm" />
                                                <div className="absolute bottom-3 left-3 w-5 h-5 border-b-2 border-l-2 border-emerald-400 rounded-bl-sm" />
                                                <div className="absolute bottom-3 right-3 w-5 h-5 border-b-2 border-r-2 border-emerald-400 rounded-br-sm" />
                                                {/* Scan line */}
                                                <div className="absolute left-4 right-4 h-0.5 bg-emerald-400/80 top-1/2 -translate-y-1/2 animate-pulse" />
                                                <FileText className="w-10 h-10 text-white/15" />
                                            </div>
                                            {/* Extracted data */}
                                            <div className="p-3 space-y-1.5">
                                                <div className="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                                                    <CheckCircle className="w-3 h-3" /> Estratto in 6 secondi
                                                </div>
                                                {[
                                                    { k: "Fornitore", v: "Magazzini Nord SRL" },
                                                    { k: "N. DDT", v: "2025/00391" },
                                                    { k: "Articoli", v: "8 righe" },
                                                ].map((f, i) => (
                                                    <div key={i} className="flex justify-between bg-muted/30 rounded px-2 py-1.5">
                                                        <span className="text-[9px] text-muted-foreground">{f.k}</span>
                                                        <span className="text-[9px] font-bold text-foreground">{f.v}</span>
                                                    </div>
                                                ))}
                                                <div className="w-full mt-2 bg-emerald-600 text-white rounded-lg py-1.5 text-[10px] font-bold text-center">
                                                    Conferma e Trasferisci
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Floating Android badge */}
                                    <div className="absolute -top-4 -right-6 bg-emerald-600 text-white px-3 py-1.5 rounded-full text-[10px] font-bold shadow-lg whitespace-nowrap flex items-center gap-1.5">
                                        <Smartphone className="w-3 h-3" />
                                        Android
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* ─── FUNZIONALITÀ CHIAVE ──────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Funzionalità
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Tutto quello che serve per eliminare il data entry</h2>
                        <p className="text-muted-foreground">Un sistema completo, progettato per integrarsi con il tuo modo di lavorare</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
                        {[
                            {
                                icon: CheckCircle,
                                title: "Pronto All'Uso",
                                desc: "11 tipologie di documento pronte senza alcuna configurazione. Per layout complessi, il nostro team crea modelli dedicati in pochi giorni.",
                                colorClass: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
                            },
                            {
                                icon: Cpu,
                                title: "Modelli AI Personalizzati",
                                desc: "Hai documenti particolari o layout aziendali proprietari? Finch-AI crea modelli dedicati con precisione superiore rispetto ai modelli generici.",
                                colorClass: "bg-orange-500/10 text-orange-600 dark:text-orange-400",
                            },
                            {
                                icon: UserCheck,
                                title: "Human-in-the-Loop",
                                desc: "L'operatore rivede e conferma ogni estrazione prima del trasferimento. Nessun errore arriva al gestionale senza approvazione umana.",
                                colorClass: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
                            },
                            {
                                icon: Zap,
                                title: "Pronto in 3 Minuti",
                                desc: "Configurazione automatica e immediata. Non serve essere esperti per iniziare ad automatizzare il flusso documentale.",
                                colorClass: "bg-purple-500/10 text-purple-600 dark:text-purple-400",
                            },
                            {
                                icon: PlugZap,
                                title: "Integrazione ERP",
                                desc: "Collegamento rapido con il tuo gestionale via API o webhook. Ogni dato estratto finisce esattamente dove deve essere.",
                                colorClass: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
                            },
                            {
                                icon: Layers,
                                title: "Multi-Canale",
                                desc: "Ricevi documenti via email, upload manuale, FTP, API o fotocamera da app Android. Il flusso si adatta al tuo modo di lavorare.",
                                colorClass: "bg-teal-500/10 text-teal-600 dark:text-teal-400",
                            },
                            {
                                icon: ShieldCheck,
                                title: "Sicurezza Enterprise",
                                desc: "Dati protetti da crittografia e ospitati in Europa, in piena conformità con le normative GDPR.",
                                colorClass: "bg-destructive/10 text-destructive",
                            },
                        ].map((f, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-8 text-left shadow-sm hover:-translate-y-1 hover:shadow-md hover:border-primary/30 transition-all">
                                <div className={`w-12 h-12 rounded-xl flex items-center justify-center mb-6 ${f.colorClass}`}>
                                    <f.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold mb-3">{f.title}</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed">{f.desc}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── PRICING ──────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-12 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Prezzi
                        </div>
                        <h2 className="text-3xl md:text-5xl font-bold mb-4" style={{ fontFamily: "'DM Serif Display', serif" }}>Piani di Abbonamento</h2>
                        <p className="text-muted-foreground text-lg">Fatturazione basata sulle pagine elaborate · Scala con il tuo business</p>
                    </div>

                    <div className="flex flex-nowrap overflow-x-auto pb-8 gap-4 snap-x xl:grid xl:grid-cols-5 xl:overflow-x-visible xl:pb-0">
                        {plans.map((plan, i) => (
                            <div
                                key={i}
                                className={`flex-shrink-0 w-[280px] sm:w-[300px] xl:w-full snap-center bg-white dark:bg-card border rounded-[20px] p-8 text-center flex flex-col transition-all hover:-translate-y-1 ${plan.sort_order === 40 ? 'border-emerald-600 shadow-xl shadow-emerald-600/10 relative' : 'border-[#E0DCD4] dark:border-border shadow-sm'}`}
                                style={{ fontFamily: "'DM Sans', sans-serif" }}
                            >
                                {plan.sort_order === 40 && (
                                    <div className="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-[10px] font-bold px-4 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">
                                        Più popolare
                                    </div>
                                )}
                                <div className="text-[11px] font-bold text-muted-foreground uppercase tracking-[1.5px] mb-4">{plan.display_name}</div>
                                <div className="text-3xl font-bold text-emerald-600 mb-1" style={{ fontFamily: "'DM Serif Display', serif" }}>
                                    {plan.contact_us_pricing ? 'Contattaci' : plan.is_free ? 'Gratis' : `€${plan.base_monthly_cost}/mese + IVA`}
                                </div>
                                <div className="text-[13px] text-emerald-600 font-medium mb-4 min-h-[20px]">
                                    {!plan.contact_us_pricing && !plan.is_free && (
                                        `Annuale: €${plan.annual_monthly_equivalent}/mese (risparmi 17%)`
                                    )}
                                    {plan.is_free && 'Sempre gratuito'}
                                    {plan.contact_us_pricing && 'Soluzione su misura'}
                                </div>
                                <p className="text-[12px] text-muted-foreground leading-relaxed mb-6 text-left min-h-[60px]">
                                    {plan.description}
                                </p>
                                <ul className="text-left space-y-4 mb-8 flex-grow border-t border-border pt-6">
                                    <li className="text-[14px] flex items-center justify-between gap-3 border-b border-border pb-3">
                                        <span className="text-muted-foreground">Pagine/mese</span>
                                        <span className="text-foreground font-bold">{typeof plan.pages_per_month === 'number' ? plan.pages_per_month.toLocaleString() : plan.pages_per_month}</span>
                                    </li>
                                    <li className="text-[14px] flex items-center justify-between gap-3 border-b border-border pb-3">
                                        <span className="text-muted-foreground">Tipi documento</span>
                                        <span className="text-foreground font-bold">{plan.max_document_types}</span>
                                    </li>
                                    <li className="text-[14px] flex items-center justify-between gap-3 border-b border-border pb-3">
                                        <span className="text-muted-foreground">Utenti</span>
                                        <span className="text-foreground font-bold">{plan.max_users}</span>
                                    </li>
                                    {[
                                        { key: 'email_polling_enabled', label: 'Email polling' },
                                        { key: 'api_transfer_enabled', label: 'Trasferimento via API' },
                                        { key: 'ftp_transfer_enabled', label: 'Trasferimento via FTP' },
                                        { key: 'includes_custom_models', label: 'Modelli custom' }
                                    ].map((feat, j) => (
                                        <li key={j} className="text-[14px] flex items-center justify-between gap-3 border-b border-border pb-3 last:border-0 last:pb-0">
                                            <span className={plan[feat.key] ? 'text-foreground font-medium' : 'text-muted-foreground'}>{feat.label}</span>
                                            {plan[feat.key] ? (
                                                <CheckCircle className="w-5 h-5 text-emerald-500 flex-shrink-0" />
                                            ) : (
                                                <div className="w-5 h-5 flex items-center justify-center text-muted-foreground/40 font-bold flex-shrink-0">✕</div>
                                            )}
                                        </li>
                                    ))}
                                    {!plan.contact_us_pricing && (
                                        <li className="text-[14px] flex items-center justify-between gap-3 border-t border-border pt-3">
                                            <span className="text-muted-foreground">Pagina extra</span>
                                            <span className="text-foreground font-bold">€{plan.extra_page_cost.toFixed(3).replace('.', ',')}</span>
                                        </li>
                                    )}
                                </ul>
                                <a
                                    href="https://documentintelligence.finch-ai.it/"
                                    className={`block w-full py-4 rounded-[12px] font-bold text-[16px] transition-all ${plan.sort_order === 40 ? 'bg-emerald-600 text-white hover:brightness-110 shadow-lg shadow-emerald-600/20' : 'bg-emerald-600 text-white hover:brightness-110'}`}
                                >
                                    {plan.button_text}
                                </a>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ─── FAQ ──────────────────────────────────────────────────── */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Domande Frequenti
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Hai domande su Document Intelligence?</h2>
                        <p className="text-muted-foreground">Le risposte alle domande più comuni sul nostro sistema di automazione documentale.</p>
                    </div>

                    <div className="max-w-3xl mx-auto space-y-3">
                        {[
                            {
                                q: "Quali tipi di documenti supporta Document Intelligence?",
                                a: "Document Intelligence supporta fatture italiane e internazionali, ricevute e scontrini, documenti d'identità (carta d'identità, passaporto, patente), DDT e bolle di consegna, estratti conto bancari, ordini d'acquisto, note di credito, tessera sanitaria, cedolini paga, biglietti da visita e contratti. Per documenti proprietari o layout aziendali specifici, Finch-AI sviluppa modelli personalizzati."
                            },
                            {
                                q: "Quanto tempo ci vuole per configurare un nuovo tipo di documento?",
                                a: "La configurazione è guidata e veloce: si scelgono i campi da estrarre, si definiscono eventuali regole in linguaggio naturale e si configura la struttura dati da inviare al gestionale. Non è richiesta nessuna competenza tecnica né scrittura di codice."
                            },
                            {
                                q: "È possibile definire campi che non sono presenti nel documento originale?",
                                a: "Sì. Document Intelligence permette di definire campi personalizzati, derivati e di arricchimento scrivendo in linguaggio naturale. Ad esempio: \"Classifica il fornitore come strategico se l'importo supera €5.000\" oppure \"Calcola l'imponibile netto sottraendo lo sconto dall'importo totale\"."
                            },
                            {
                                q: "Come avviene l'integrazione con il gestionale ERP?",
                                a: "Document Intelligence si integra con qualsiasi gestionale tramite API REST o webhook. Puoi configurare la struttura esatta dei dati da inviare — inclusi campi personalizzati e derivati — per piena compatibilità con SAP, Zucchetti, TeamSystem, Sage e altri sistemi gestionali."
                            },
                            {
                                q: "C'è un'app mobile per chi lavora in campo o in magazzino?",
                                a: "Sì, è disponibile un'app Android che permette di acquisire documenti direttamente dalla fotocamera del telefono. È ideale per operatori di magazzino e logistica che devono registrare DDT e bolle di consegna sul campo, senza tornare in ufficio o usare uno scanner."
                            },
                            {
                                q: "Quanto costa Document Intelligence?",
                                a: "Document Intelligence parte da €49/mese per il piano Basic (400 pagine/mese). È disponibile un piano Demo gratuito con 20 pagine/mese. I piani Business (€129/mese) e Professional (€249/mese) includono volumi maggiori e modelli AI personalizzati. Per grandi volumi è disponibile un piano Enterprise su misura."
                            },
                        ].map((item, i) => (
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

                {/* ─── CTA ──────────────────────────────────────────────────── */}
                <section className="mb-12">
                    <div className="bg-foreground rounded-[40px] p-7 sm:p-12 md:p-20 text-center text-background relative overflow-hidden shadow-2xl shadow-foreground/20">
                        <div className="absolute top-0 right-0 w-[200px] h-[200px] md:w-[400px] md:h-[400px] bg-[radial-gradient(circle,rgba(45,125,70,0.2)_0%,transparent_70%)] translate-x-1/4 -translate-y-1/4" />

                        <div className="relative z-10">
                            <h2 className="text-2xl sm:text-3xl md:text-5xl font-bold mb-6">Smetti di ricopiare. Inizia ad automatizzare.</h2>
                            <p className="text-background/70 text-base sm:text-lg mb-8 sm:mb-12">Prova Document Intelligence gratis — il primo mese è offerto da noi.</p>

                            <div className="flex flex-wrap justify-center gap-3 sm:gap-4 mb-8 sm:mb-12">
                                {[
                                    { icon: MapPin, text: "Configurazione Automatica" },
                                    { icon: ShieldCheck, text: "GDPR · Dati in EU" },
                                    { icon: Zap, text: "Pronto in 3 minuti" },
                                    { icon: LinkIcon, text: "API per ogni gestionale" }
                                ].map((badge, i) => (
                                    <div key={i} className="flex items-center gap-2 bg-background/10 border border-background/20 px-5 py-3 rounded-full text-sm font-medium">
                                        <badge.icon className="w-4 h-4 text-primary" />
                                        {badge.text}
                                    </div>
                                ))}
                            </div>

                            <a
                                href="https://documentintelligence.finch-ai.it/"
                                className="inline-flex items-center gap-2 bg-primary text-primary-foreground px-6 py-4 sm:px-10 sm:py-5 rounded-2xl font-bold text-base sm:text-lg hover:brightness-110 transition-all shadow-xl shadow-primary/20 hover:scale-105 active:scale-95"
                            >
                                Richiedi accesso gratuito
                                <ArrowRight className="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </section>

            </div>
            
            <VideoModal 
                isOpen={isVideoModalOpen} 
                onClose={() => setIsVideoModalOpen(false)} 
                videoUrl="/assets/videos/document-intelligence-demo.html"
                title="Document Intelligence Demo"
            />
        </Layout>
    );
};

export default DocumentIntelligence;
