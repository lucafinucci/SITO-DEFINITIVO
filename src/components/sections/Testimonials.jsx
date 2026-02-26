export default function Testimonials() {
    return (
        <section id="case-studies" className="py-24 relative transition-colors duration-300">
            {/* Background gradient */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none" />

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-300 mb-6 font-mono">
                        Case Study
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Risultati <span className="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-cyan-500">Misurabili</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Casi reali di aziende che hanno trasformato i loro processi con Finch-AI.
                    </p>
                </div>

                <div className="space-y-12">
                    {/* Case Study 1 */}
                    <div className="group relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white/50 dark:bg-gradient-to-br dark:from-slate-900/90 dark:to-slate-900/60 backdrop-blur transition-all hover:border-emerald-500/50 hover:shadow-[0_0_50px_rgba(16,185,129,0.1)] dark:hover:shadow-[0_0_50px_rgba(16,185,129,0.2)] shadow-sm">
                        <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                            <div className="h-full w-full bg-[radial-gradient(1000px_400px_at_var(--x,50%)_0,rgba(16,185,129,0.1),transparent)]" />
                        </div>

                        <div className="relative p-8 sm:p-10 lg:p-12">
                            <div className="grid lg:grid-cols-3 gap-8 lg:gap-12">
                                <div className="lg:col-span-2 space-y-8">
                                    <div>
                                        <div className="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4">
                                            Manufacturing PMI
                                        </div>
                                        <h3 className="text-2xl sm:text-3xl font-bold dark:text-white text-slate-900 mb-4">
                                            Produttore Componentistica Automotive
                                        </h3>
                                        <p className="text-lg text-slate-500 dark:text-slate-300/90 leading-relaxed">
                                            120 dipendenti, 500+ DDT/settimana, gestionale SAP legacy, processo manuale complesso.
                                        </p>
                                    </div>

                                    <div>
                                        <h4 className="text-lg font-semibold text-red-600 dark:text-red-400 mb-3">Il Problema</h4>
                                        <p className="text-slate-600 dark:text-slate-300/90 leading-relaxed text-base">
                                            3 persone dedicate full-time a inserimento DDT in SAP. Errori frequenti, ritardi nella chiusura commesse,
                                            visibilità zero su magazzino real-time. Impossibile scalare senza assumere ulteriore personale amministrativo.
                                        </p>
                                    </div>

                                    <div>
                                        <h4 className="text-lg font-semibold text-emerald-600 dark:text-emerald-400 mb-3">La Soluzione Finch-AI</h4>
                                        <div className="grid sm:grid-cols-2 gap-4">
                                            {[
                                                "Automazione OCR per DDT in/out",
                                                "Integrazione SAP bidirezionale",
                                                "Dashboard produzione real-time",
                                                "Alert automatici su anomalie"
                                            ].map((item, i) => (
                                                <div key={i} className="flex items-center gap-3">
                                                    <div className="h-2 w-2 rounded-full bg-emerald-500 dark:bg-emerald-400 shrink-0"></div>
                                                    <span className="text-sm text-slate-600 dark:text-slate-300">{item}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                        <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">92%</div>
                                        <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Riduzione tempo elaborazione DDT</div>
                                    </div>
                                    <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                        <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">2.5 FTE</div>
                                        <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Risorse liberate</div>
                                    </div>
                                    <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                        <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">4 sett</div>
                                        <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Tempo di deployment</div>
                                    </div>
                                    <div className="rounded-2xl border border-slate-100 dark:border-emerald-500/30 bg-slate-50 dark:bg-gradient-to-br dark:from-emerald-500/10 dark:to-teal-500/10 p-6 backdrop-blur-sm shadow-inner">
                                        <div className="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">ROI 6m</div>
                                        <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">Break-even raggiunto</div>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-10 pt-8 border-t border-slate-100 dark:border-slate-700/50">
                                <blockquote className="italic text-slate-500 dark:text-slate-300/90 text-lg leading-relaxed">
                                    "Finch-AI ci ha permesso di scalare del 40% senza assumere personale amministrativo.
                                    I nostri responsabili di produzione ora hanno visibilità real-time su tutto.
                                    Non è un software, è come avere un team di analisti H24."
                                </blockquote>
                                <div className="mt-4 flex items-center gap-3">
                                    <div className="h-10 w-10 rounded-full bg-cyan-100 dark:bg-cyan-500/20 flex items-center justify-center text-cyan-600 dark:text-cyan-400 font-bold">M</div>
                                    <div>
                                        <div className="text-sm font-semibold dark:text-white text-slate-900">Marco R.</div>
                                        <div className="text-xs text-slate-400">Operations Manager, Automotive</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Call to Action for more cases */}
                    <div className="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-slate-50/50 dark:bg-gradient-to-br dark:from-slate-900/60 dark:to-slate-900/40 backdrop-blur p-8 sm:p-12 text-center shadow-sm">
                        <h3 className="text-2xl font-bold dark:text-white text-slate-900 mb-4">La tua azienda potrebbe essere la prossima success story</h3>
                        <p className="text-slate-600 dark:text-slate-300/90 max-w-2xl mx-auto mb-8">
                            Scopri quanto puoi risparmiare e ottimizzare con una demo personalizzata basata sui tuoi dati reali.
                        </p>
                        <a
                            href="#contatti"
                            className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:scale-105"
                        >
                            Richiedi Analisi Gratuita
                            <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}
