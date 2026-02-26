export default function About() {
    return (
        <section id="chi-siamo" className="py-24 relative transition-colors duration-300 bg-slate-50 dark:bg-transparent">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="grid lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <span className="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-300 mb-6 font-mono">
                            Chi Siamo
                        </span>
                        <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                            L'AI che parla il <span className="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-cyan-500">linguaggio dell'industria</span>
                        </h2>
                        <p className="text-lg text-slate-600 dark:text-slate-300/90 mb-8 leading-relaxed">
                            Finch-AI nasce per eliminare tempi morti e decisioni al buio: automazione documentale, KPI real-time e insight azionabili per produzione, logistica e finanza.
                        </p>

                        <div className="space-y-6">
                            {[
                                { title: "Missione", desc: "Portiamo AI operativa nelle linee produttive e negli uffici: meno data entry, più decisioni basate su numeri, con integrazione nativa a ERP/CRM/MES." },
                                { title: "Team", desc: "Data scientist e ingegneri con esperienza in manufacturing, supply chain e sistemi ERP. Delivery rapido (4–8 settimane) e modelli adattati sui tuoi processi." },
                                { title: "Tecnologia & Sicurezza", desc: "Moduli AI specializzati, OCR avanzato, integrazione API-first. Dati in UE, cifrati in transito e a riposo, privacy by design (GDPR)." }
                            ].map((item, i) => (
                                <div key={i} className="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-sm transition hover:border-blue-500/30">
                                    <h3 className="text-lg font-bold dark:text-white text-slate-900 mb-2">{item.title}</h3>
                                    <p className="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{item.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="relative">
                        <div className="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 dark:blur-3xl blur-2xl rounded-3xl" />
                        <div className="relative rounded-3xl border border-slate-200 dark:border-slate-700/50 bg-white dark:bg-slate-900/80 p-10 shadow-2xl">
                            <div className="space-y-8">
                                {[
                                    { value: "-70%", label: "Tempo ciclo documenti" },
                                    { value: "99%", label: "Accuratezza estrazione dati" },
                                    { value: "4–8 sett", label: "Go-live medio" }
                                ].map((stat, i) => (
                                    <div key={i} className="flex items-center gap-6">
                                        <div className="text-4xl font-extrabold text-blue-600 dark:text-blue-400 min-w-[120px]">{stat.value}</div>
                                        <div className="text-base font-semibold text-slate-600 dark:text-slate-300">{stat.label}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
