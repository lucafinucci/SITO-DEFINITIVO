export default function Modules() {
    return (
        <section id="come-funziona" className="py-24 relative transition-colors duration-300">
            <div className="absolute left-0 top-1/2 -translate-y-1/2 -ml-20 w-[600px] h-[600px] bg-purple-500/5 rounded-full blur-[120px] pointer-events-none" />

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-purple-500/30 bg-purple-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-300 mb-6 font-mono">
                        I Moduli
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Tre Pilastri, <span className="bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500">Infinite Possibilità</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Ogni modulo risolve un problema specifico, insieme creano un ecosistema che trasforma il tuo business.
                    </p>
                </div>

                <div className="space-y-12">
                    {/* Modulo 1: Document Intelligence */}
                    <div className="group relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/40 p-8 sm:p-10 backdrop-blur transition hover:border-cyan-500/50 hover:shadow-[0_0_40px_rgba(34,211,238,0.1)] dark:hover:shadow-[0_0_40px_rgba(34,211,238,0.2)] shadow-sm">
                        <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                            <div className="h-full w-full bg-[radial-gradient(800px_300px_at_var(--x,50%)_0,rgba(34,211,238,0.15),transparent)]" />
                        </div>

                        <div className="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div>
                                <div className="inline-flex items-center gap-3 mb-6">
                                    <div className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-3 text-cyan-600 dark:text-cyan-300 shadow-lg shadow-cyan-500/10">
                                        <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M4 4h10l6 6v10a2 2 0 0 1-2 2H4V4z" fill="none" stroke="currentColor" strokeWidth="1.8" /><path d="M14 4v6h6" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
                                    </div>
                                    <h3 className="text-2xl sm:text-3xl font-bold dark:text-white text-slate-900">Document Intelligence</h3>
                                </div>

                                <p className="text-lg text-slate-500 dark:text-slate-300/90 mb-6 leading-relaxed">
                                    Trasforma ogni documento in dati strutturati e azionabili. OCR avanzato con validazioni di dominio specifiche per il tuo settore.
                                </p>

                                <div className="space-y-4 mb-6">
                                    {[
                                        "Estrazione automatica da DDT, fatture, ordini",
                                        "Validazione intelligente con regole business",
                                        "Integrazione diretta con ERP/gestionale",
                                        "Gestione eccezioni e anomalie"
                                    ].map((feature, i) => (
                                        <div key={i} className="flex items-start gap-3">
                                            <svg className="h-6 w-6 text-cyan-500 dark:text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span className="text-slate-600 dark:text-slate-300 text-base">{feature}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="rounded-2xl border border-slate-100 dark:border-cyan-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-cyan-500/10 dark:to-blue-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-cyan-600 dark:text-cyan-400 mb-2">90%</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Riduzione tempo elaborazione documenti</div>
                                </div>
                                <div className="rounded-2xl border border-slate-100 dark:border-cyan-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-cyan-500/10 dark:to-blue-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-cyan-600 dark:text-cyan-400 mb-2">99.2%</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Accuratezza estrazione dati</div>
                                </div>
                                <div className="rounded-2xl border border-slate-100 dark:border-cyan-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-cyan-500/10 dark:to-blue-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-cyan-600 dark:text-cyan-400 mb-2">Zero</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Data entry manuale richiesto</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Modulo 2: Production Analytics */}
                    <div className="group relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/40 p-8 sm:p-10 backdrop-blur transition hover:border-purple-500/50 hover:shadow-[0_0_40px_rgba(168,85,247,0.1)] dark:hover:shadow-[0_0_40px_rgba(168,85,247,0.2)] shadow-sm">
                        <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                            <div className="h-full w-full bg-[radial-gradient(800px_300px_at_var(--x,50%)_0,rgba(168,85,247,0.15),transparent)]" />
                        </div>

                        <div className="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div className="order-2 lg:order-1 space-y-4">
                                <div className="rounded-2xl border border-slate-100 dark:border-purple-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-purple-500/10 dark:to-pink-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">Real-time</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Monitoraggio OEE e produttività</div>
                                </div>
                                <div className="rounded-2xl border border-slate-100 dark:border-purple-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-purple-500/10 dark:to-pink-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">3x</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Velocità decisioni strategiche</div>
                                </div>
                                <div className="rounded-2xl border border-slate-100 dark:border-purple-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-purple-500/10 dark:to-pink-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">100%</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Visibilità su tutti i reparti</div>
                                </div>
                            </div>

                            <div className="order-1 lg:order-2">
                                <div className="inline-flex items-center gap-3 mb-6">
                                    <div className="rounded-xl border border-purple-500/30 bg-purple-500/10 p-3 text-purple-600 dark:text-purple-300 shadow-lg shadow-purple-500/10">
                                        <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M4 19h16M6 16V8m6 8V5m6 11v-7" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
                                    </div>
                                    <h3 className="text-2xl sm:text-3xl font-bold dark:text-white text-slate-900">Production Analytics</h3>
                                </div>

                                <p className="text-lg text-slate-500 dark:text-slate-300/90 mb-6 leading-relaxed">
                                    Dashboard intelligenti che trasformano i dati di produzione in insight azionabili. KPI real-time, anomalie predittive, ottimizzazione continua.
                                </p>

                                <div className="space-y-4 mb-6">
                                    {[
                                        "KPI real-time: OEE, disponibilità, performance",
                                        "Analisi predittiva per manutenzione e scorte",
                                        "Alert automatici su anomalie e inefficienze",
                                        "Report personalizzati per ogni reparto"
                                    ].map((feature, i) => (
                                        <div key={i} className="flex items-start gap-3">
                                            <svg className="h-6 w-6 text-purple-500 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span className="text-slate-600 dark:text-slate-300 text-base">{feature}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Modulo 3: Financial Control */}
                    <div className="group relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/40 p-8 sm:p-10 backdrop-blur transition hover:border-emerald-500/50 hover:shadow-[0_0_40px_rgba(16,185,129,0.1)] dark:hover:shadow-[0_0_40px_rgba(16,185,129,0.2)] shadow-sm">
                        <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                            <div className="h-full w-full bg-[radial-gradient(800px_300px_at_var(--x,50%)_0,rgba(16,185,129,0.15),transparent)]" />
                        </div>

                        <div className="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div>
                                <div className="inline-flex items-center gap-3 mb-6">
                                    <div className="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-emerald-600 dark:text-emerald-300 shadow-lg shadow-emerald-500/10">
                                        <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M7 8h10M4 12h16M7 16h10" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
                                    </div>
                                    <h3 className="text-2xl sm:text-3xl font-bold dark:text-white text-slate-900">Financial Control</h3>
                                </div>

                                <p className="text-lg text-slate-500 dark:text-slate-300/90 mb-6 leading-relaxed">
                                    Unifica flussi finanziari e operativi per un controllo totale. Integrazione ERP, riconciliazione automatica, previsioni cash-flow basate su AI.
                                </p>

                                <div className="space-y-4 mb-6">
                                    {[
                                        "Integrazione automatica con qualsiasi ERP",
                                        "Riconciliazione documenti-pagamenti",
                                        "Forecast cash-flow e marginalità",
                                        "Dashboard finanziaria unificata"
                                    ].map((feature, i) => (
                                        <div key={i} className="flex items-start gap-3">
                                            <svg className="h-6 w-6 text-emerald-500 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span className="text-slate-600 dark:text-slate-300 text-base">{feature}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">Zero</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Attrito nelle integrazioni</div>
                                </div>
                                <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">100%</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Sincronizzazione automatica</div>
                                </div>
                                <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                    <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">24/7</div>
                                    <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Controllo finanziario operativo</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
