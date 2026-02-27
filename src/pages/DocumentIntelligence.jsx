import React, { useEffect } from 'react';
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
    Link as LinkIcon
} from 'lucide-react';
import Layout from '../components/Layout';

const DocumentIntelligence = () => {
    useEffect(() => {
        window.scrollTo(0, 0);
    }, []);

    return (
        <Layout>
            <div className="max-w-7xl mx-auto px-6 py-12 md:py-24">
                {/* HERO */}
                <section className="text-center mb-24">
                    <div className="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-8 animate-in fade-in slide-in-from-top-4 duration-1000">
                        <Globe className="w-4 h-4" />
                        SaaS Multi-tenant · Document Intelligence
                    </div>

                    <h1 className="text-4xl md:text-6xl font-extrabold leading-tight mb-8 animate-in fade-in slide-in-from-top-6 duration-1000 fill-mode-both">
                        DDT da gestire ogni giorno.<br />
                        <span className="relative inline-block">
                            <span className="relative z-10 text-emerald-600 dark:text-emerald-400">FinCh-Ai li legge per te.</span>
                            <span className="absolute bottom-1 left-0 right-0 h-3 bg-emerald-500/10 -z-0 rounded-sm"></span>
                        </span>
                    </h1>

                    <p className="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto mb-12 animate-in fade-in slide-in-from-top-8 duration-1000 fill-mode-both">
                        Scannerizzati, caricati, ricevuti via email — ogni DDT va aperto, letto, trascritto. <em className="italic text-foreground">A mano.</em><br />
                        Document Intelligence riconosce, estrae e verifica i dati automaticamente.
                    </p>

                    <div className="flex flex-wrap justify-center gap-6 animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
                        {[
                            { label: "Tempo risparmiato sull'inserimento dati", value: "-75%" },
                            { label: "Velocità in più nella lavorazione DDT", value: "90%" },
                            { label: "Elaborazione automatica continua", value: "24/7" }
                        ].map((stat, i) => (
                            <div key={i} className="bg-card border border-border rounded-2xl p-6 shadow-sm flex items-center gap-4 min-w-[240px]">
                                <span className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{stat.value}</span>
                                <span className="text-left text-xs text-muted-foreground leading-tight">
                                    <strong className="text-foreground">{stat.label.split(' ')[0] + ' ' + stat.label.split(' ')[1]}</strong><br />
                                    {stat.label.split(' ').slice(2).join(' ')}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                {/* BEFORE / AFTER */}
                <section className="grid grid-cols-1 lg:grid-cols-[1fr,auto,1fr] gap-0 items-stretch mb-32">
                    <div className="bg-card border border-border rounded-3xl p-8 shadow-sm relative overflow-hidden flex flex-col">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-muted" />
                        <div className="text-[11px] font-bold text-muted-foreground uppercase tracking-[1.5px] mb-4">❌ Prima</div>
                        <h3 className="text-2xl font-bold mb-4">Ore perse ogni giorno a copiare dati a mano</h3>
                        <p className="text-sm text-muted-foreground mb-8">Ogni DDT va aperto, letto, trascritto campo per campo nel gestionale. Errori frequenti, colli di bottiglia.</p>

                        <div className="bg-muted/30 border border-border rounded-xl p-4 space-y-3 mt-auto">
                            {[
                                { icon: Printer, label: "Scannerizza il DDT cartaceo", time: "2 min" },
                                { icon: FileText, label: "Apri il PDF, cerca i dati rilevanti", time: "2 min" },
                                { icon: Keyboard, label: "Trascrivi nel gestionale", time: "4 min" },
                                { icon: Search, label: "Ricontrolla per errori", time: "2 min" },
                                { icon: Folder, label: "Archivia il documento", time: "1 min" },
                                { icon: Clock, label: "Totale per DDT", time: "~11 min", bold: true }
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

                    <div className="bg-card border border-emerald-500/50 rounded-3xl p-8 shadow-sm relative overflow-hidden flex flex-col">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-700" />
                        <div className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-[1.5px] mb-4">✅ Dopo — con FinCh-Ai</div>
                        <h3 className="text-2xl font-bold mb-4">Dati estratti in automatico, pronti da verificare</h3>
                        <p className="text-sm text-muted-foreground mb-8">Scannerizzi, carichi o invii via email — il DDT viene elaborato automaticamente. Tu verifichi solo il risultato.</p>

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
                                    { label: "Nr. DDT", value: "2024/00847" },
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
                <section className="mb-32">
                    <div className="text-center mb-16">
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
                            <div key={i} className="bg-card border border-border rounded-3xl p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md hover:border-primary/30">
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

                {/* FEATURES */}
                <section className="mb-32 text-center">
                    <div className="mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Funzionalità Chiave</h2>
                        <p className="text-muted-foreground">Tutto quello che serve per eliminare il data entry manuale</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {[
                            { icon: CheckCircle, title: "Dati Precisi al 100%", desc: "Elimina gli errori di trascrizione manuale. L'AI garantisce un'estrazione dati accurata.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: Clock, title: "Efficienza Operativa", desc: "Riduci i tempi di lavorazione fino all'80%. Libera il tuo personale da compiti ripetitivi.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: Sliders, title: "Flessibilità Totale", desc: "Il sistema impara e si adatta a qualsiasi documento o modello specifico necessario.", colorClass: "bg-orange-500/10 text-orange-600" },
                            { icon: PlugZap, title: "Integrazione Invisibile", desc: "Collegamento rapido con il tuo gestionale (ERP) per un flusso di dati fluido.", colorClass: "bg-purple-500/10 text-purple-600" },
                            { icon: Gauge, title: "Tutto Sotto Controllo", desc: "Dashboard intuitiva per monitorare lo stato di ogni documento in tempo reale.", colorClass: "bg-emerald-500/10 text-emerald-600" },
                            { icon: ShieldCheck, title: "Privacy e Sicurezza Top", desc: "Dati protetti da crittografia enterprise e server ospitati esclusivamente in Europa.", colorClass: "bg-destructive/10 text-destructive" }
                        ].map((f, i) => (
                            <div key={i} className="bg-card border border-border rounded-3xl p-8 text-left shadow-sm hover:border-primary/30 transition-colors">
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
                <section className="mb-32">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Piani di Abbonamento</h2>
                        <p className="text-muted-foreground">Fatturazione basata sulle pagine elaborate · Scala con il tuo business</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        {[
                            { tier: "Demo", price: "€0", period: "Prova limitata", features: ["20 pagine incluse", "€0,15/pagina extra", "1 layout", "No pooling Email"] },
                            { tier: "Basic", price: "€49", period: "€490/anno (-17%)", features: ["400 pagine incluse", "€0,15/pagina extra", "2 layout", "No pooling Email"] },
                            { tier: "Professional", price: "€129", period: "€1.290/anno (-17%)", features: ["1.500 pagine incluse", "€0,12/pagina extra", "5 layout", "No pooling Email"], popular: true },
                            { tier: "Business", price: "€249", period: "€2.490/anno (-17%)", features: ["4.000 pagine incluse", "€0,09/pagina extra", "10 layout", "Si pooling Email"] },
                            { tier: "Enterprise", price: "Contattaci", period: "Su misura", features: ["12.000 pagine incluse", "€0,07/pagina extra", "Illimitati layout", "Si pooling Email"] }
                        ].map((plan, i) => (
                            <div key={i} className={`bg-card border rounded-3xl p-6 text-center flex flex-col transition-all hover:-translate-y-1 ${plan.popular ? 'border-primary shadow-xl shadow-primary/10 relative' : 'border-border shadow-sm'}`}>
                                {plan.popular && (
                                    <div className="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-primary-foreground text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                                        POPOLARE
                                    </div>
                                )}
                                <div className="text-[11px] font-bold text-muted-foreground uppercase tracking-wider mb-4">{plan.tier}</div>
                                <div className="text-3xl font-bold text-primary mb-2">{plan.price}</div>
                                <div className="text-[12px] text-muted-foreground mb-6">{plan.period}</div>
                                <ul className="text-left space-y-3 mb-8 flex-grow">
                                    {plan.features.map((feat, j) => (
                                        <li key={j} className="text-[12px] flex items-start gap-2 border-b border-border/50 pb-2 last:border-0 last:pb-0">
                                            {feat.startsWith('No') ? (
                                                <span className="text-muted-foreground/30 mt-0.5 font-bold">✕</span>
                                            ) : (
                                                <span className="text-emerald-500 mt-0.5 font-bold">✓</span>
                                            )}
                                            <span className="text-foreground" dangerouslySetInnerHTML={{ __html: feat.replace(/(\d+(\.\d+)?)/g, '<strong class="text-foreground">$1</strong>') }} />
                                        </li>
                                    ))}
                                </ul>
                                <button className={`block w-full py-3 rounded-xl font-bold transition-all ${plan.popular ? 'bg-primary text-primary-foreground hover:brightness-110 shadow-lg shadow-primary/20' : 'border border-primary text-primary hover:bg-primary/5'}`}>
                                    {plan.tier === "Enterprise" ? "Contattaci" : "Inizia ora"}
                                </button>
                            </div>
                        ))}
                    </div>
                </section>

                {/* CTA */}
                <section className="bg-foreground rounded-[40px] p-12 md:p-20 text-center text-background relative overflow-hidden shadow-2xl shadow-foreground/20">
                    <div className="absolute top-0 right-0 w-[400px] h-[400px] bg-[radial-gradient(circle,rgba(45,125,70,0.2)_0%,transparent_70%)] translate-x-1/4 -translate-y-1/4" />

                    <div className="relative z-10">
                        <h2 className="text-3xl md:text-5xl font-bold mb-6">Smetti di ricopiare. Inizia ad automatizzare.</h2>
                        <p className="text-background/70 text-lg mb-12">Prova Document Intelligence gratis — il primo mese è offerto da noi.</p>

                        <div className="flex flex-wrap justify-center gap-4 mb-12">
                            {[
                                { icon: MapPin, text: "Pensato per DDT italiani" },
                                { icon: LockKeyhole, text: "GDPR · Dati in EU" },
                                { icon: Zap, text: "Attivo in 24 ore" },
                                { icon: LinkIcon, text: "API per ogni gestionale" }
                            ].map((badge, i) => (
                                <div key={i} className="flex items-center gap-2 bg-background/10 border border-background/20 px-5 py-3 rounded-full text-sm font-medium">
                                    <badge.icon className="w-4 h-4 text-primary" />
                                    {badge.text}
                                </div>
                            ))}
                        </div>

                        <button className="inline-flex items-center gap-2 bg-primary text-primary-foreground px-10 py-5 rounded-2xl font-bold text-lg hover:brightness-110 transition-all shadow-xl shadow-primary/20 hover:scale-105 active:scale-95">
                            Richiedi accesso gratuito
                            <ArrowRight className="w-5 h-5" />
                        </button>
                    </div>
                </section>
            </div>
        </Layout>
    );
};

export default DocumentIntelligence;
