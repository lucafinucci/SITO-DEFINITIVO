import React, { useEffect, useState } from 'react';
import SEO from '../components/SEO';
import {
    Globe,
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
    Gauge,
    Sliders,
    PlugZap,
    MapPin,
    LockKeyhole,
    Zap,
    MessageSquare,
    Link as LinkIcon,
    ReceiptText,
    UserCheck,
    Truck,
    Landmark,
    Package,
    BadgeMinus,
    HeartPulse,
    Users,
    Smartphone
} from 'lucide-react';
import Layout from '../components/Layout';

const DocumentIntelligence = () => {
    const [isAnnual, setIsAnnual] = useState(true);

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
            pages_per_month: 400, 
            extra_page_cost: 0.150, 
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
            pages_per_month: 1500, 
            extra_page_cost: 0.120, 
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
            pages_per_month: 4000, 
            extra_page_cost: 0.090, 
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
            "operatingSystem": "Web",
            "url": "https://finch-ai.it/soluzioni/document-intelligence",
            "image": "https://finch-ai.it/assets/images/og-image.png",
            "description": "Automazione AI per l'estrazione dati da ogni tipo di documento. Pronto in 5 minuti, configurazione automatica. Supporta fatture, ricevute, documenti d'identità e altro. 97% accuratezza, human-in-the-loop, integrazione ERP.",
            "featureList": [
                "OCR AI per fatture, ricevute, documenti d'identità, DDT",
                "97% di accuratezza nel riconoscimento dati",
                "Da 11 minuti a 8 secondi per documento",
                "-75% tempo inserimento dati",
                "Verifica human-in-the-loop",
                "Integrazione ERP via API e Webhook",
                "Elaborazione automatica 24/7",
                "GDPR compliant"
            ],
            "offers": [
                { "@type": "Offer", "name": "Demo", "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Basic", "price": "49", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "49", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Professional", "price": "129", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "129", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Business", "price": "249", "priceCurrency": "EUR", "priceSpecification": { "@type": "UnitPriceSpecification", "price": "249", "priceCurrency": "EUR", "unitText": "mese" }, "availability": "https://schema.org/InStock" }
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
        }
    ];

    return (
        <Layout>
            <SEO
                title="Document Intelligence | Estrazione Dati Automatica con AI — Finch-AI"
                description="Leggi ogni documento in 8 secondi. AI pronta all'uso in 3 minuti con configurazione automatica. Estrazione dati da fatture, ricevute, ID e DDT con 97% accuratezza. Da €49/mese."
                keywords="automazione documenti AI, estrazione dati documenti, OCR fatture intelligente, document intelligence PMI, configurazione automatica OCR, riconoscimento documenti identità AI, estrazione dati ricevute, digitalizzazione processi aziendali, human-in-the-loop, API integrazione ERP"
                canonical="https://finch-ai.it/soluzioni/document-intelligence"
                jsonLd={docJsonLd}
            />
            <div className="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-24">
                {/* HERO */}
                <section className="text-center mb-12 sm:mb-24">
                    <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-8 animate-in fade-in slide-in-from-top-4 duration-1000">
                        <Globe className="w-4 h-4" />
                        SaaS Multi-tenant · Document Intelligence
                    </div>

                    <h1 className="text-3xl sm:text-4xl md:text-6xl font-extrabold leading-tight mb-8 animate-in fade-in slide-in-from-top-6 duration-1000 fill-mode-both">
                        Documenti da gestire ogni giorno.<br />
                        <span className="relative inline-block">
                            <span className="relative z-10 text-emerald-600 dark:text-emerald-400">FinCh-Ai li legge per te.</span>
                            <span className="absolute bottom-1 left-0 right-0 h-3 bg-emerald-500/10 -z-0 rounded-sm"></span>
                        </span>
                    </h1>

                    <p className="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto mb-12 animate-in fade-in slide-in-from-top-8 duration-1000 fill-mode-both">
                        Pronto all'uso in <strong className="text-emerald-600 dark:text-emerald-400">3 minuti</strong>. Configurazione quasi interamente automatica, immediata anche per i non esperti.<br />
                        Document Intelligence riconosce, estrae e verifica i dati da ogni tipo di documento automaticamente.
                    </p>

                    <div className="flex flex-wrap justify-center gap-3 sm:gap-6 animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
                        {[
                            { label: "Tempo risparmiato sull'inserimento dati", value: "-75%" },
                            { label: "Velocità in più nella lavorazione Documenti", value: "90%" },
                            { label: "Elaborazione automatica continua", value: "24/7" }
                        ].map((stat, i) => (
                            <div key={i} className="bg-card border border-border rounded-2xl p-4 sm:p-6 shadow-sm flex items-center gap-4 min-w-[160px] sm:min-w-[240px]">
                                <span className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{stat.value}</span>
                                <span className="text-left text-xs text-muted-foreground leading-tight">
                                    <strong className="text-foreground">{stat.label.split(' ')[0] + ' ' + (stat.label.includes('DDT') ? 'Lavorazione' : stat.label.split(' ')[1])}</strong><br />
                                    {stat.label.includes('DDT') ? 'Documenti' : stat.label.split(' ').slice(2).join(' ')}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                {/* BEFORE / AFTER */}
                <section className="grid grid-cols-1 lg:grid-cols-[1fr,auto,1fr] gap-0 items-stretch mb-16 sm:mb-32">
                    <div className="bg-card border border-border rounded-3xl p-5 sm:p-8 shadow-sm relative overflow-hidden flex flex-col">
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

                    <div className="bg-card border border-emerald-500/50 rounded-3xl p-5 sm:p-8 shadow-sm relative overflow-hidden flex flex-col">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-700" />
                        <div className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-[1.5px] mb-4">✅ Dopo — con FinCh-Ai</div>
                        <h3 className="text-2xl font-bold mb-4">Dati estratti in automatico, pronti da verificare</h3>
                        <p className="text-sm text-muted-foreground mb-8">Scannerizzi, carichi o invii via email — ogni documento viene elaborato automaticamente. Tu verifichi solo il risultato.</p>

                        <div className="bg-muted/20 border border-emerald-500/20 rounded-xl p-6 mt-auto">
                            <div className="flex flex-wrap gap-2 mb-6">
                                <span className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3 py-1.5 rounded-full text-[11px] font-semibold flex items-center gap-1.5">
                                    <Download className="w-3 h-3" /> In Elaborazione
                                </span>
                                <span className="text-muted-foreground/30 self-center"><ArrowRight className="w-3 h-3" /></span>
                                <span className="bg-orange-500/10 text-orange-600 dark:text-orange-400 px-3 py-1.5 rounded-full text-[11px] font-semibold flex items-center gap-1.5">
                                    <Eye className="w-3 h-3" /> Da Verificare
                                </span>
                                <span className="text-muted-foreground/30 self-center"><ArrowRight className="w-3 h-3" /></span>
                                <span className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3 py-1.5 rounded-full text-[11px] font-semibold flex items-center gap-1.5">
                                    <CheckCircle className="w-3 h-3" /> Verificato
                                </span>
                                <span className="text-muted-foreground/30 self-center"><ArrowRight className="w-3 h-3" /></span>
                                <span className="bg-purple-500/10 text-purple-600 dark:text-purple-400 px-3 py-1.5 rounded-full text-[11px] font-semibold flex items-center gap-1.5">
                                    <Upload className="w-3 h-3" /> Trasferito
                                </span>
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

                {/* FLOW */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Come Funziona</h2>
                        <p className="text-muted-foreground">Dal documento al gestionale in quattro passaggi automatici</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {[
                            { num: 1, icon: Download, title: "Ricezione", desc: "Scansione, upload manuale o ricezione via email.", tech: "Scan + Upload + Email", colorClass: "bg-emerald-500", textClass: "text-emerald-600 dark:text-emerald-400" },
                            { num: 2, icon: Cpu, title: "Riconoscimento AI", desc: "L'AI estrae automaticamente campi e tabelle.", tech: "Document AI", colorClass: "bg-orange-500", textClass: "text-orange-600 dark:text-orange-400" },
                            { num: 3, icon: CheckSquare, title: "Verifica", desc: "L'operatore rivede i dati estratti e conferma.", tech: "Human-in-the-loop", colorClass: "bg-emerald-500", textClass: "text-emerald-600 dark:text-emerald-400" },
                            { num: 4, icon: Rocket, title: "Trasferimento", desc: "I dati vengono trasferiti al gestionale via API.", tech: "API + Webhook", colorClass: "bg-purple-500", textClass: "text-purple-600 dark:text-purple-400" }
                        ].map((step, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md hover:border-primary/30">
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-lg mx-auto mb-6 ${step.colorClass}`}>
                                    {step.num}
                                </div>
                                <step.icon className="w-8 h-8 mx-auto mb-4 text-foreground" />
                                <h3 className="font-bold text-sm mb-2">{step.title}</h3>
                                <p className="text-xs text-muted-foreground leading-relaxed mb-4">{step.desc}</p>
                                <span className={`inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${step.textClass} bg-current/10`}>
                                    {step.tech}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                {/* INFOGRAFICA */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Il Flusso Completo</h2>
                        <p className="text-muted-foreground">Dalla ricezione del documento all'integrazione nel gestionale</p>
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

                {/* SUPPORTED DOCUMENT TYPES */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-8 sm:mb-16">
                        <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-6">
                            <CheckCircle className="w-4 h-4" />
                            Pronto all'Uso · Zero Configurazione
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Tipologie di Documento Supportate</h2>
                        <p className="text-muted-foreground max-w-3xl mx-auto">Decine di tipologie di documento pronte all'uso, senza alcuna configurazione. Per layout complessi o moduli proprietari, il nostro team crea modelli dedicati in pochi giorni.</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8 mb-8">
                        {[
                            { icon: ReceiptText, title: "Fatture", useCase: "Contabilità Fornitori", desc: "Fornitore, importo, IVA, scadenza, date e righe dettaglio." },
                            { icon: FileText, title: "Ricevute e Scontrini", useCase: "Note Spese", desc: "Rimborsi aziendali e riconciliazione automatica delle spese." },
                            { icon: UserCheck, title: "Documenti d'Identità", useCase: "Onboarding & KYC", desc: "CdI, passaporto, patente — verifiche di identità in pochi secondi." },
                            { icon: Truck, title: "DDT e Bolle di Consegna", useCase: "Logistica & Magazzino", desc: "Ricezione merci, articoli e aggiornamento automatico scorte." },
                            { icon: Landmark, title: "Estratti Conto", useCase: "Riconciliazione Bancaria", desc: "Movimenti bancari con classificazione automatica e partite." },
                            { icon: Package, title: "Ordini d'Acquisto", useCase: "Ufficio Acquisti", desc: "Registrazione ordini fornitori direttamente nel gestionale." },
                            { icon: BadgeMinus, title: "Note di Credito", useCase: "Gestione Resi", desc: "Storno fatture e riconciliazione automatica delle partite aperte." },
                            { icon: HeartPulse, title: "Tessera Sanitaria", useCase: "Strutture Sanitarie", desc: "Codice fiscale e dati paziente per accettazione rapida." },
                            { icon: Users, title: "Cedolini Paga", useCase: "Risorse Umane", desc: "Stipendi, trattenute, contributi e dati per elaborazione HR." }
                        ].map((doc, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-8 text-left shadow-sm hover:border-primary/30 transition-colors">
                                <div className="w-12 h-12 rounded-xl flex items-center justify-center mb-6 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <doc.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold mb-1 text-lg">{doc.title}</h3>
                                <div className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-3">{doc.useCase}</div>
                                <p className="text-sm text-muted-foreground">{doc.desc}</p>
                            </div>
                        ))}
                    </div>

                    <div className="bg-card border border-border rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div className="w-12 h-12 rounded-xl flex items-center justify-center bg-orange-500/10 text-orange-600 dark:text-orange-400 flex-shrink-0">
                            <Cpu className="w-6 h-6" />
                        </div>
                        <div className="flex-grow">
                            <h3 className="font-bold text-lg mb-2">Documenti Complessi? Ci Pensiamo Noi</h3>
                            <p className="text-sm text-muted-foreground mb-4 md:mb-0">Per moduli aziendali, contratti strutturati o formati personalizzati forniamo modelli dedicati. Il nostro team gestisce completamente il setup — nessuna competenza tecnica richiesta da te.</p>
                        </div>
                        <a href="https://documentintelligence.finch-ai.it/" className="inline-flex items-center gap-2 text-orange-600 dark:text-orange-400 font-bold hover:text-orange-700 dark:hover:text-orange-300 transition-colors flex-shrink-0">
                            Richiedi un modello custom
                            <ArrowRight className="w-4 h-4" />
                        </a>
                    </div>
                </section>

                {/* FEATURES */}
                <section className="mb-16 sm:mb-32 text-center">
                    <div className="mb-8 sm:mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Funzionalità Chiave</h2>
                        <p className="text-muted-foreground">Tutto quello che serve per eliminare il data entry manuale</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
                        {[
                            { icon: CheckCircle, title: "Pronto All'Uso", desc: "Fatture, ricevute, identità, DDT, estratti conto e molti altri — zero configurazione. Per documenti complessi, il nostro team crea modelli dedicati.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: Cpu, title: "Addestramento Custom", desc: "Hai documenti particolari? Finch-AI crea modelli personalizzati per ogni tua esigenza specifica.", colorClass: "bg-orange-500/10 text-orange-600" },
                            { icon: MessageSquare, title: "Regole Naturali", desc: "Definisci le regole di estrazione scrivendo in linguaggio naturale. L'AI capisce ed esegue.", colorClass: "bg-blue-500/10 text-blue-600" },
                            { icon: Zap, title: "Pronto in 3 Minuti", desc: "Configurazione automatica e immediata. Non serve essere esperti per iniziare ad automatizzare.", colorClass: "bg-purple-500/10 text-purple-600" },
                            { icon: PlugZap, title: "Integrazione ERP", desc: "Collegamento rapido con il tuo gestionale via API per un flusso di dati fluido e senza intoppi.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: Smartphone, title: "App Mobile", desc: "Acquisitori documenti direttamente da fotocamera. Ideale per operativi su campo — logistica, ricezione merci, vendite.", colorClass: "bg-blue-500/10 text-blue-600" },
                            { icon: ShieldCheck, title: "Sicurezza Enterprise", desc: "Dati protetti da crittografia e ospitati in Europa, in piena conformità con le normative GDPR.", colorClass: "bg-destructive/10 text-destructive" }
                        ].map((f, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-5 sm:p-8 text-left shadow-sm hover:border-primary/30 transition-colors">
                                <div className={`w-12 h-12 rounded-xl flex items-center justify-center mb-6 ${f.colorClass}`}>
                                    <f.icon className="w-6 h-6" />
                                </div>
                                <h3 className="font-bold mb-3">{f.title}</h3>
                                <p className="text-sm text-muted-foreground leading-relaxed">{f.desc}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* PRICING */}
                <section className="mb-16 sm:mb-32">
                    <div className="text-center mb-12 sm:mb-16">
                        <h2 className="text-3xl md:text-5xl font-bold mb-4" style={{ fontFamily: "'DM Serif Display', serif" }}>Piani di Abbonamento</h2>
                        <p className="text-muted-foreground text-lg">Fatturazione basata sulle pagine elaborate · Scala con il tuo business</p>
                    </div>

                    <div className="flex flex-nowrap overflow-x-auto pb-8 gap-4 snap-x xl:grid xl:grid-cols-5 xl:overflow-x-visible xl:pb-0">
                        {plans.map((plan, i) => (
                            <div 
                                key={i} 
                                className={`flex-shrink-0 w-[280px] sm:w-[300px] xl:w-full snap-center bg-white dark:bg-card border rounded-[20px] p-8 text-center flex flex-col transition-all hover:-translate-y-1 ${plan.sort_order === 40 ? 'border-emerald-600 shadow-xl shadow-emerald-600/10 relative' : 'border-[#E0DCD4] shadow-sm'}`}
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
                                <ul className="text-left space-y-4 mb-8 flex-grow border-t border-[#F0EDE6] pt-6">
                                    <li className="text-[14px] flex items-center justify-between gap-3 border-b border-[#F0EDE6] pb-3">
                                        <span className="text-muted-foreground">Pagine/mese</span>
                                        <span className="text-foreground font-bold">{typeof plan.pages_per_month === 'number' ? plan.pages_per_month.toLocaleString() : plan.pages_per_month}</span>
                                    </li>
                                    <li className="text-[14px] flex items-center justify-between gap-3 border-b border-[#F0EDE6] pb-3">
                                        <span className="text-muted-foreground">Tipi documento</span>
                                        <span className="text-foreground font-bold">{plan.max_document_types}</span>
                                    </li>
                                    <li className="text-[14px] flex items-center justify-between gap-3 border-b border-[#F0EDE6] pb-3">
                                        <span className="text-muted-foreground">Utenti</span>
                                        <span className="text-foreground font-bold">{plan.max_users}</span>
                                    </li>
                                    {[
                                        { key: 'email_polling_enabled', label: 'Email polling' },
                                        { key: 'api_transfer_enabled', label: 'Trasferimento via API' },
                                        { key: 'ftp_transfer_enabled', label: 'Trasferimento via FTP' },
                                        { key: 'includes_custom_models', label: 'Modelli custom' }
                                    ].map((feat, j) => (
                                        <li key={j} className="text-[14px] flex items-center justify-between gap-3 border-b border-[#F0EDE6] pb-3 last:border-0 last:pb-0">
                                            <span className={plan[feat.key] ? 'text-foreground font-medium' : 'text-muted-foreground'}>{feat.label}</span>
                                            {plan[feat.key] ? (
                                                <CheckCircle className="w-5 h-5 text-emerald-500 flex-shrink-0" />
                                            ) : (
                                                <div className="w-5 h-5 flex items-center justify-center text-[#BBB] font-bold flex-shrink-0">✕</div>
                                            )}
                                        </li>
                                    ))}
                                    {!plan.contact_us_pricing && (
                                        <li className="text-[14px] flex items-center justify-between gap-3 border-t border-[#F0EDE6] pt-3">
                                            <span className="text-muted-foreground">Pagina extra</span>
                                            <span className="text-foreground font-bold">€{plan.extra_page_cost.toFixed(3).replace('.', ',')}</span>
                                        </li>
                                    )}
                                </ul>
                                <a 
                                    href="https://documentintelligence.finch-ai.it/"
                                    className={`block w-full py-4 rounded-[12px] font-bold text-[16px] transition-all ${plan.sort_order === 40 ? 'bg-emerald-600 text-white hover:brightness-110 shadow-lg shadow-emerald-600/20' : 'bg-emerald-600 outline outline-2 outline-emerald-600 text-white hover:brightness-110'}`}
                                >
                                    {plan.button_text}
                                </a>
                            </div>
                        ))}
                    </div>
                </section>

                {/* CTA */}
                <section className="bg-foreground rounded-[40px] p-7 sm:p-12 md:p-20 text-center text-background relative overflow-hidden shadow-2xl shadow-foreground/20">
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
                </section>
            </div>
        </Layout>
    );
};

export default DocumentIntelligence;
