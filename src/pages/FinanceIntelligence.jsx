import { useEffect } from 'react';
import {
    TrendingUp,
    Upload,
    Brain,
    MessageSquare,
    FileText,
    BarChart3,
    ShieldCheck,
    Globe,
    Calendar,
    Zap,
    CheckCircle,
    X,
    Minus,
    ArrowRight,
    Lock,
    Trash2,
    Mail,
    Server,
    AlertTriangle,
    BookOpen,
    Users,
    Webhook,
    Building2,
    KeyRound,
    BadgeCheck,
    Headphones,
    BrainCircuit,
    FileBarChart2,
    LineChart,
} from 'lucide-react';
import Layout from '../components/Layout';

const FinanceIntelligence = () => {
    useEffect(() => {
        window.scrollTo(0, 0);
    }, []);

    const pricingPlans = [
        {
            tier: 'Demo',
            price: 'Gratis',
            period: 'Prova limitata',
            popular: false,
            btnText: 'Inizia gratis',
            features: {
                livelloAI: 'Essenziale',
                reportMese: '2',
                tipoReport: 'Sintetico',
                indiciFinanziari: '6',
                riclassificazione: '—',
                analisiTrend: '—',
                benchmark: '—',
                chatAI: '5 domande',
                profonditaChat: 'Q&A base',
                exportPDF: '—',
                utenti: '1',
                scenariWhatIf: false,
                integrazioneERP: false,
                apiWebhook: false,
                ssoSaml: false,
                slaGarantito: false,
                supporto: 'Self-service',
            },
        },
        {
            tier: 'Starter',
            price: '€49',
            period: '/mese',
            popular: false,
            btnText: 'Inizia con Starter',
            features: {
                livelloAI: 'Essenziale',
                reportMese: '5',
                tipoReport: 'Sintetico',
                indiciFinanziari: '6–8',
                riclassificazione: '—',
                analisiTrend: '—',
                benchmark: '—',
                chatAI: '30/mese',
                profonditaChat: 'Q&A dati',
                exportPDF: 'Base',
                utenti: '1',
                scenariWhatIf: false,
                integrazioneERP: false,
                apiWebhook: false,
                ssoSaml: false,
                slaGarantito: false,
                supporto: 'Email 48h',
            },
        },
        {
            tier: 'Professional',
            price: '€99',
            period: '/mese',
            popular: true,
            btnText: 'Scegli Professional',
            features: {
                livelloAI: 'Avanzata',
                reportMese: '10',
                tipoReport: 'Professionale',
                indiciFinanziari: '15+',
                riclassificazione: 'OIC completa',
                analisiTrend: 'Multi-anno',
                benchmark: 'Base',
                chatAI: '60',
                profonditaChat: 'Analisi',
                exportPDF: 'Professionale',
                utenti: '3',
                scenariWhatIf: false,
                integrazioneERP: false,
                apiWebhook: false,
                ssoSaml: false,
                slaGarantito: false,
                supporto: 'Email 24h',
            },
        },
        {
            tier: 'Business',
            price: '€299',
            period: '/mese',
            popular: false,
            btnText: 'Scegli Business',
            features: {
                livelloAI: 'Premium',
                reportMese: '20',
                tipoReport: 'Strategico',
                indiciFinanziari: '20+',
                riclassificazione: 'OIC + custom',
                analisiTrend: '+ proiezioni',
                benchmark: 'Dettagliato',
                chatAI: 'Illimitate',
                profonditaChat: 'Strategica',
                exportPDF: 'White-label',
                utenti: '5',
                scenariWhatIf: true,
                integrazioneERP: false,
                apiWebhook: false,
                ssoSaml: false,
                slaGarantito: false,
                supporto: 'Prioritario',
            },
        },
        {
            tier: 'Enterprise',
            price: 'Su misura',
            period: '',
            popular: false,
            btnText: 'Contattaci',
            features: {
                livelloAI: 'Premium',
                reportMese: 'Illimitati',
                tipoReport: 'Strategico+',
                indiciFinanziari: 'Custom',
                riclassificazione: 'Tutto custom',
                analisiTrend: '+ scenari',
                benchmark: 'Custom',
                chatAI: 'Illimitate',
                profonditaChat: 'Advisory',
                exportPDF: 'Full custom',
                utenti: '30+',
                scenariWhatIf: true,
                integrazioneERP: true,
                apiWebhook: true,
                ssoSaml: true,
                slaGarantito: true,
                supporto: 'Dedicato + AM',
            },
        },
    ];

    const featureRows = [
        { key: 'livelloAI', label: 'Livello AI', icon: <BrainCircuit className="h-4 w-4" /> },
        { key: 'reportMese', label: 'Report/mese', icon: <FileBarChart2 className="h-4 w-4" /> },
        { key: 'tipoReport', label: 'Tipo Report', icon: <FileText className="h-4 w-4" /> },
        { key: 'indiciFinanziari', label: 'Indici finanziari', icon: <BarChart3 className="h-4 w-4" /> },
        { key: 'riclassificazione', label: 'Riclassificazione', icon: <BookOpen className="h-4 w-4" /> },
        { key: 'analisiTrend', label: 'Analisi trend', icon: <LineChart className="h-4 w-4" /> },
        { key: 'benchmark', label: 'Benchmark', icon: <TrendingUp className="h-4 w-4" /> },
        { key: 'chatAI', label: 'Chat AI', icon: <MessageSquare className="h-4 w-4" /> },
        { key: 'profonditaChat', label: 'Profondità Chat', icon: <Brain className="h-4 w-4" /> },
        { key: 'exportPDF', label: 'Export PDF', icon: <FileText className="h-4 w-4" /> },
        { key: 'utenti', label: 'Utenti', icon: <Users className="h-4 w-4" /> },
        { key: 'scenariWhatIf', label: 'Scenari What-If', icon: <AlertTriangle className="h-4 w-4" />, bool: true },
        { key: 'integrazioneERP', label: 'Integrazione ERP', icon: <Building2 className="h-4 w-4" />, bool: true, note: '(a parte)' },
        { key: 'apiWebhook', label: 'API / Webhook', icon: <Webhook className="h-4 w-4" />, bool: true },
        { key: 'ssoSaml', label: 'SSO / SAML', icon: <KeyRound className="h-4 w-4" />, bool: true },
        { key: 'slaGarantito', label: 'SLA garantito', icon: <BadgeCheck className="h-4 w-4" />, bool: true, note: '99.5%' },
        { key: 'supporto', label: 'Supporto', icon: <Headphones className="h-4 w-4" /> },
    ];

    const renderCell = (plan, row) => {
        const val = plan.features[row.key];
        if (row.bool) {
            if (val === true) {
                return (
                    <span className="inline-flex items-center justify-center">
                        <CheckCircle className="h-5 w-5 text-emerald-500" />
                        {row.note && plan.tier === 'Enterprise' && (
                            <span className="ml-1 text-[10px] text-muted-foreground">({row.note})</span>
                        )}
                    </span>
                );
            }
            return <Minus className="h-4 w-4 text-muted-foreground/30 mx-auto" />;
        }
        if (val === '—' || !val) {
            return <Minus className="h-4 w-4 text-muted-foreground/30 mx-auto" />;
        }
        return <span className="text-foreground font-medium text-xs">{val}</span>;
    };

    return (
        <Layout>
            <div className="max-w-7xl mx-auto px-6 py-12 md:py-24">

                {/* HERO */}
                <section className="text-center mb-24">
                    <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-8">
                        <TrendingUp className="w-4 h-4" />
                        Finance Intelligence · AI Finanziario per PMI Italiane
                    </div>

                    <h1 className="text-4xl md:text-6xl font-extrabold leading-tight mb-8">
                        Il tuo bilancio parla.<br />
                        <span className="relative inline-block">
                            <span className="relative z-10 text-emerald-600 dark:text-emerald-400">Ora puoi ascoltarlo.</span>
                            <span className="absolute bottom-1 left-0 right-0 h-3 bg-emerald-500/10 -z-0 rounded-sm"></span>
                        </span>
                    </h1>

                    <p className="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto mb-12">
                        Carica il tuo Conto Economico in Excel. In 3 minuti hai un report OIC professionale, indici finanziari, trend — e un <em className="italic text-foreground not-italic font-semibold">assistente AI</em> che risponde alle tue domande sui numeri.
                    </p>

                    <div className="flex flex-wrap justify-center gap-4 mb-16">
                        <button className="inline-flex items-center gap-2 bg-emerald-600 text-white px-8 py-4 rounded-full font-bold hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-500/20">
                            <Upload className="w-5 h-5" />
                            Prova Gratis — Carica Excel
                        </button>
                        <a href="#video" className="inline-flex items-center gap-2 bg-card border border-border text-foreground px-8 py-4 rounded-full font-bold hover:bg-muted transition-all">
                            <Zap className="w-5 h-5 text-emerald-500" />
                            Guarda il Video
                        </a>
                    </div>

                    <div className="flex flex-wrap justify-center gap-6 mb-12">
                        {[
                            { value: "3 min", label: "Dal caricamento Excel al report completo" },
                            { value: "15+", label: "Indici finanziari calcolati e spiegati" },
                            { value: "OIC", label: "Conforme Art. 2425 C.C. italiano" },
                        ].map((stat, i) => (
                            <div key={i} className="bg-card border border-border rounded-2xl p-6 shadow-sm flex items-center gap-4 min-w-[240px]">
                                <span className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{stat.value}</span>
                                <span className="text-left text-xs text-muted-foreground leading-tight">
                                    {stat.label}
                                </span>
                            </div>
                        ))}
                    </div>

                    {/* Trust pills */}
                    <div className="flex flex-wrap justify-center gap-3">
                        {[
                            { icon: <Globe className="w-4 h-4" />, text: "Conforme OIC · Art. 2425 C.C." },
                            { icon: <Lock className="w-4 h-4" />, text: "GDPR Compliant · Dati in EU" },
                            { icon: <Trash2 className="w-4 h-4" />, text: "Dati cancellati dopo il test" },
                            { icon: <Zap className="w-4 h-4" />, text: "Report in 3 minuti" },
                        ].map((pill, i) => (
                            <div key={i} className="inline-flex items-center gap-2 bg-card border border-border px-4 py-2 rounded-full text-sm text-muted-foreground shadow-sm">
                                <span className="text-emerald-600 dark:text-emerald-400">{pill.icon}</span>
                                {pill.text}
                            </div>
                        ))}
                    </div>
                </section>

                {/* VIDEO SECTION */}
                <section className="mb-32 flex justify-center" id="video">
                    <div className="max-w-5xl w-full">
                        <div className="relative aspect-video rounded-[32px] overflow-hidden shadow-2xl border border-border bg-black group cursor-pointer">
                            <video
                                className="w-full h-full object-cover"
                                controls
                                poster=""
                                preload="metadata"
                            >
                                <source src="/Analista_Finanziario_Clip_01.mp4" type="video/mp4" />
                                Il tuo browser non supporta il tag video.
                            </video>

                            {/* Overlay caption */}
                            <div className="absolute bottom-6 left-6 right-6 flex items-center justify-between pointer-events-none">
                                <div className="bg-black/40 backdrop-blur-md px-4 py-2 rounded-full border border-white/20">
                                    <span className="text-white text-xs font-medium flex items-center gap-2">
                                        <div className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" />
                                        Demo Analista Finanziario AI
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p className="text-center mt-6 text-sm text-muted-foreground italic">
                            Guarda come l'AI analizza un bilancio completo e risponde in linguaggio naturale.
                        </p>
                    </div>
                </section>

                {/* COME FUNZIONA */}
                <section className="mb-32">
                    <div className="text-center mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Come Funziona
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Dal tuo Excel al report. In tre passi.</h2>
                        <p className="text-muted-foreground">Nessuna configurazione, nessuna formazione. Funziona dal primo minuto.</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {[
                            {
                                num: 1,
                                icon: <Upload className="w-8 h-8" />,
                                title: "Carica il tuo Excel",
                                desc: "Trascina il file del Conto Economico. Accettiamo qualsiasi formato Excel — il sistema riconosce automaticamente la struttura.",
                                time: "30 secondi",
                                colorClass: "bg-emerald-500",
                                timeClass: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
                            },
                            {
                                num: 2,
                                icon: <Brain className="w-8 h-8" />,
                                title: "L'AI analizza tutto",
                                desc: "Riclassificazione OIC, calcolo di tutti gli indici finanziari, analisi dei margini, trend anno su anno. Tutto automatico.",
                                time: "2–3 minuti",
                                colorClass: "bg-orange-500",
                                timeClass: "bg-orange-500/10 text-orange-600 dark:text-orange-400",
                            },
                            {
                                num: 3,
                                icon: <MessageSquare className="w-8 h-8" />,
                                title: "Parla con i tuoi numeri",
                                desc: 'Scarica il report PDF professionale. Poi chiedi all\'AI: "Qual è il mio margine operativo?" "Dove sto perdendo margine?"',
                                time: "Domande illimitate",
                                colorClass: "bg-emerald-700",
                                timeClass: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
                            },
                        ].map((step, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md hover:border-primary/30">
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-lg mx-auto mb-6 ${step.colorClass}`}>
                                    {step.num}
                                </div>
                                <div className="flex justify-center mb-4 text-foreground">{step.icon}</div>
                                <h3 className="font-bold text-lg mb-3">{step.title}</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed mb-6">{step.desc}</p>
                                <span className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ${step.timeClass}`}>
                                    <Zap className="w-3 h-3" /> {step.time}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                {/* FUNZIONALITÀ */}
                <section className="mb-32">
                    <div className="text-center mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Funzionalità
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Tutto quello che un CFO vorrebbe.<br />Al costo di un caffè al giorno.</h2>
                        <p className="text-muted-foreground">Pensato per chi gestisce un'impresa, non per chi fa il commercialista.</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {[
                            { icon: <FileBarChart2 className="w-6 h-6" />, title: "Report OIC Professionale", desc: "Riclassificazione conforme agli standard OIC e al Codice Civile Art. 2425. Pronto per la banca, l'investitore o il CdA.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: <BarChart3 className="w-6 h-6" />, title: "Indici Finanziari Completi", desc: "ROE, ROI, ROS, EBITDA margin, indici di liquidità, solidità patrimoniale, Altman Z-Score — calcolati e spiegati in italiano.", colorClass: "bg-orange-500/10 text-orange-600" },
                            { icon: <MessageSquare className="w-6 h-6" />, title: "Chat AI in Italiano", desc: "Fai domande sui tuoi dati in linguaggio naturale. L'AI capisce il contesto e risponde con numeri specifici della tua azienda.", colorClass: "bg-blue-500/10 text-blue-600" },
                            { icon: <LineChart className="w-6 h-6" />, title: "Confronto Pluriennale", desc: "Carica più anni e visualizza i trend. L'AI identifica variazioni significative e segnala criticità prima che diventino problemi.", colorClass: "bg-purple-500/10 text-purple-600" },
                            { icon: <FileText className="w-6 h-6" />, title: "Export PDF Professionale", desc: "Report formattato con grafici, tabelle e commenti dell'AI. Pronto da condividere senza ulteriori modifiche.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: <Globe className="w-6 h-6" />, title: "100% Italiano", desc: "Terminologia contabile italiana, normativa OIC, standard del Codice Civile. Non una traduzione — costruito per il mercato italiano.", colorClass: "bg-red-500/10 text-red-600" },
                        ].map((f, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-8 text-left shadow-sm hover:border-primary/30 transition-colors">
                                <div className={`w-12 h-12 rounded-xl flex items-center justify-center mb-6 ${f.colorClass}`}>
                                    {f.icon}
                                </div>
                                <h3 className="font-bold mb-3">{f.title}</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed">{f.desc}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* PRIVACY */}
                <section className="mb-32">
                    <div className="text-center mb-12">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            I Tuoi Dati, Le Tue Regole
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Prova senza pensieri. Cancellazione totale.</h2>
                        <p className="text-muted-foreground max-w-xl mx-auto">Sappiamo che i dati finanziari sono sensibili. Per questo abbiamo progettato il sistema con la privacy al centro.</p>
                    </div>

                    <div className="max-w-4xl mx-auto bg-card border-2 border-emerald-500/40 rounded-3xl p-10 relative overflow-hidden shadow-xl shadow-emerald-500/5">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-700 rounded-t-3xl" />

                        <div className="flex items-center gap-4 mb-8">
                            <div className="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <ShieldCheck className="w-7 h-7" />
                            </div>
                            <div>
                                <h3 className="text-xl font-bold text-foreground">La nostra promessa sui dati</h3>
                                <p className="text-sm text-muted-foreground">Trasparenza totale su cosa succede con le tue informazioni</p>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                            {[
                                { icon: <Upload className="w-5 h-5" />, title: "Il tuo Excel non viene archiviato", desc: "Il file viene elaborato in memoria per generare il report. Non viene salvato sui nostri server, né in alcun database." },
                                { icon: <Trash2 className="w-5 h-5" />, title: "Cancellazione automatica", desc: "Dopo che hai scaricato il report e terminato la sessione, tutti i dati della tua analisi vengono eliminati. Automaticamente." },
                                { icon: <Mail className="w-5 h-5" />, title: "Conserviamo solo la tua email", desc: "L'unico dato che conserviamo è il tuo indirizzo email, per poterti ricontattare. Nessun dato finanziario, nessun numero." },
                                { icon: <Server className="w-5 h-5" />, title: "GDPR · Server in EU", desc: "Infrastruttura europea, conforme al GDPR. I tuoi dati non lasciano mai l'Unione Europea durante l'elaborazione." },
                            ].map((p, i) => (
                                <div key={i} className="flex gap-3 p-4 rounded-2xl bg-muted/20 border border-border">
                                    <div className="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                                        {p.icon}
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-sm text-foreground mb-1">{p.title}</h4>
                                        <p className="text-xs text-muted-foreground leading-relaxed">{p.desc}</p>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl px-6 py-4 flex items-center gap-3 text-emerald-600 dark:text-emerald-400 font-semibold text-sm">
                            <CheckCircle className="w-5 h-5 flex-shrink-0" />
                            In sintesi: carica, analizza, scarica il report. Poi tutto sparisce. Rimane solo la tua email.
                        </div>
                    </div>
                </section>

                {/* PRICING - TABELLA COMPLETA */}
                <section className="mb-32">
                    <div className="text-center mb-16">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary mb-4">
                            Prezzi
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Meno di un caffè al giorno.</h2>
                        <p className="text-muted-foreground">Scegli il piano giusto per la tua impresa. Puoi cambiare o disdire in qualsiasi momento.</p>
                    </div>

                    {/* Desktop table */}
                    <div className="hidden lg:block overflow-x-auto rounded-3xl border border-border shadow-sm">
                        <table className="w-full text-sm">
                            <thead>
                                <tr>
                                    <th className="text-left px-6 py-5 bg-muted/30 border-b border-border text-xs font-bold uppercase tracking-wider text-muted-foreground w-52">
                                        Funzionalità
                                    </th>
                                    {pricingPlans.map((plan) => (
                                        <th
                                            key={plan.tier}
                                            className={`px-4 py-5 text-center border-b border-border ${plan.popular
                                                ? 'bg-primary/10 border-l border-r border-primary/30'
                                                : 'bg-muted/30'
                                                }`}
                                        >
                                            {plan.popular && (
                                                <div className="mb-1">
                                                    <span className="inline-flex items-center gap-1 bg-primary text-primary-foreground text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">
                                                        Consigliato
                                                    </span>
                                                </div>
                                            )}
                                            <div className="font-bold text-foreground text-sm">{plan.tier}</div>
                                            <div className={`text-xl font-extrabold mt-1 ${plan.popular ? 'text-primary' : 'text-foreground'}`}>
                                                {plan.price}
                                            </div>
                                            <div className="text-xs text-muted-foreground">{plan.period}</div>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {featureRows.map((row, rowIdx) => (
                                    <tr
                                        key={row.key}
                                        className={rowIdx % 2 === 0 ? 'bg-card' : 'bg-muted/10'}
                                    >
                                        <td className="px-6 py-3 text-xs font-medium text-muted-foreground">
                                            <div className="flex items-center gap-2">
                                                <span className="text-primary">{row.icon}</span>
                                                {row.label}
                                            </div>
                                        </td>
                                        {pricingPlans.map((plan) => (
                                            <td
                                                key={plan.tier}
                                                className={`px-4 py-3 text-center text-xs ${plan.popular ? 'bg-primary/5 border-l border-r border-primary/20' : ''
                                                    }`}
                                            >
                                                {renderCell(plan, row)}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                                <tr className="border-t border-border bg-muted/20">
                                    <td className="px-6 py-5" />
                                    {pricingPlans.map((plan) => (
                                        <td
                                            key={plan.tier}
                                            className={`px-4 py-5 text-center ${plan.popular ? 'bg-primary/5 border-l border-r border-primary/20' : ''
                                                }`}
                                        >
                                            <button
                                                className={`w-full py-2.5 px-4 rounded-xl text-xs font-bold transition-all ${plan.popular
                                                    ? 'bg-primary text-primary-foreground hover:brightness-110 shadow-lg shadow-primary/20'
                                                    : 'border border-border text-foreground hover:bg-muted/50'
                                                    }`}
                                            >
                                                {plan.btnText}
                                            </button>
                                        </td>
                                    ))}
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {/* Mobile cards fallback */}
                    <div className="lg:hidden grid gap-4">
                        {pricingPlans.map((plan) => (
                            <div
                                key={plan.tier}
                                className={`bg-card border rounded-3xl p-6 ${plan.popular ? 'border-primary shadow-xl shadow-primary/10' : 'border-border'
                                    }`}
                            >
                                {plan.popular && (
                                    <div className="mb-2">
                                        <span className="inline-flex items-center gap-1 bg-primary text-primary-foreground text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">
                                            Consigliato
                                        </span>
                                    </div>
                                )}
                                <div className="flex items-baseline gap-2 mb-1">
                                    <span className={`text-2xl font-extrabold ${plan.popular ? 'text-primary' : 'text-foreground'}`}>{plan.price}</span>
                                    <span className="text-xs text-muted-foreground">{plan.period}</span>
                                </div>
                                <div className="font-semibold text-foreground mb-4">{plan.tier}</div>
                                <div className="space-y-2 mb-6">
                                    {featureRows.map((row) => {
                                        const val = plan.features[row.key];
                                        if (row.bool && !val) return null;
                                        return (
                                            <div key={row.key} className="flex items-center justify-between text-xs border-b border-border/50 pb-1.5">
                                                <span className="text-muted-foreground flex items-center gap-1">
                                                    <span className="text-primary">{row.icon}</span>
                                                    {row.label}
                                                </span>
                                                <span className="font-medium text-foreground">
                                                    {row.bool ? <CheckCircle className="h-4 w-4 text-emerald-500" /> : val === '—' ? <Minus className="h-3 w-3 text-muted-foreground/30" /> : val}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>
                                <button
                                    className={`w-full py-3 rounded-xl text-sm font-bold transition-all ${plan.popular
                                        ? 'bg-primary text-primary-foreground hover:brightness-110'
                                        : 'border border-border text-foreground hover:bg-muted/50'
                                        }`}
                                >
                                    {plan.btnText}
                                </button>
                            </div>
                        ))}
                    </div>
                </section>

                {/* CTA FINALE */}
                <section className="mb-12">
                    <div className="bg-foreground rounded-[40px] p-12 md:p-20 text-center text-background relative overflow-hidden shadow-2xl shadow-foreground/20">
                        <div className="absolute top-0 right-0 w-[400px] h-[400px] bg-[radial-gradient(circle,rgba(45,125,70,0.15)_0%,transparent_70%)] translate-x-1/4 -translate-y-1/4" />
                        <div className="relative z-10">
                            <h2 className="text-3xl md:text-5xl font-bold mb-6">Prova adesso. Gratis. Senza impegno.</h2>
                            <p className="text-background/70 text-lg mb-12 max-w-xl mx-auto">
                                Carica il tuo Conto Economico e scopri cosa l'AI vede nei tuoi numeri. Bastano 3 minuti e un file Excel.
                            </p>
                            <div className="flex flex-wrap justify-center gap-4 mb-8">
                                {[
                                    { icon: <Lock className="w-4 h-4" />, text: "Nessuna carta di credito" },
                                    { icon: <Trash2 className="w-4 h-4" />, text: "Dati cancellati dopo il test" },
                                    { icon: <Mail className="w-4 h-4" />, text: "Conserviamo solo l'email" },
                                    { icon: <Zap className="w-4 h-4" />, text: "Attivo in 3 minuti" },
                                ].map((badge, i) => (
                                    <div key={i} className="flex items-center gap-2 bg-background/10 border border-background/20 px-5 py-3 rounded-full text-sm font-medium">
                                        <span className="text-emerald-500">{badge.icon}</span>
                                        {badge.text}
                                    </div>
                                ))}
                            </div>
                            <button className="inline-flex items-center gap-2 bg-emerald-600 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-emerald-500 transition-all shadow-xl shadow-emerald-500/20 hover:scale-105 active:scale-95">
                                <Upload className="w-5 h-5" />
                                Carica il tuo Excel → Prova Gratis
                            </button>
                        </div>
                    </div>
                </section>

            </div>
        </Layout>
    );
};

export default FinanceIntelligence;
