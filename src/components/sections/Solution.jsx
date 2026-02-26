export default function Solution() {
    return (
        <section id="soluzioni" className="py-24 transition-colors duration-300 dark:bg-gradient-to-b dark:from-slate-900/50 dark:to-transparent bg-transparent relative overflow-hidden">
            {/* Decorative background element */}
            <div className="absolute top-0 right-0 -mr-20 -mt-20 w-[500px] h-[500px] bg-cyan-500/5 rounded-full blur-[100px] pointer-events-none" />

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-300 mb-6 font-mono">
                        La Soluzione
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Un'Unica <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-500 to-blue-600">Piattaforma Integrata</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Finch-AI unisce automazione documentale, analisi produttiva e controllo finanziario in un ecosistema intelligente che lavora per te.
                    </p>
                </div>

                <div className="grid gap-12 lg:grid-cols-2 items-center mb-16">
                    <div className="space-y-8">
                        <div className="relative pl-8 border-l-4 border-cyan-500 transition-all hover:bg-slate-50 dark:hover:bg-slate-800/30 p-4 rounded-r-xl">
                            <h3 className="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-3">
                                Tutto Integrato, Sempre Sincronizzato
                            </h3>
                            <p className="text-slate-500 dark:text-slate-300/90 leading-relaxed text-lg">
                                I tuoi documenti, dati e sistemi dialogano automaticamente. L'AI legge, estrae, valida e integra informazioni in tempo reale, eliminando data entry manuale e errori.
                            </p>
                        </div>

                        <div className="relative pl-8 border-l-4 border-blue-500 transition-all hover:bg-slate-50 dark:hover:bg-slate-800/30 p-4 rounded-r-xl">
                            <h3 className="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-3">
                                Intelligenza che Cresce con Te
                            </h3>
                            <p className="text-slate-500 dark:text-slate-300/90 leading-relaxed text-lg">
                                La piattaforma impara dai tuoi processi, si adatta alle tue esigenze specifiche e migliora continuamente. Non un software rigido, ma un partner intelligente.
                            </p>
                        </div>

                        <div className="relative pl-8 border-l-4 border-emerald-500 transition-all hover:bg-slate-50 dark:hover:bg-slate-800/30 p-4 rounded-r-xl">
                            <h3 className="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-3">
                                Controllo Completo, Zero Complessità
                            </h3>
                            <p className="text-slate-500 dark:text-slate-300/90 leading-relaxed text-lg">
                                Dashboard unificata con visibilità real-time su produzione, finanza e operations. Decisioni informate in secondi, non giorni.
                            </p>
                        </div>
                    </div>

                    <div className="relative">
                        <div className="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 dark:blur-3xl blur-2xl rounded-3xl" />
                        <div className="relative rounded-2xl border border-slate-200 dark:border-cyan-500/30 bg-white/80 dark:bg-slate-900/80 backdrop-blur p-8 lg:p-10 shadow-2xl dark:shadow-cyan-500/5 transition-all">
                            <h4 className="text-2xl font-bold dark:text-white text-slate-900 mb-8 border-b border-slate-200 dark:border-slate-700/50 pb-4">Benefici Immediati</h4>
                            <div className="space-y-6">
                                {[
                                    { metric: "90%", label: "Riduzione tempo elaborazione documenti" },
                                    { metric: "99.2%", label: "Accuratezza estrazione dati" },
                                    { metric: "3x", label: "Velocità decisionale aumentata" },
                                    { metric: "100%", label: "Visibilità real-time su tutti i processi" }
                                ].map((item, i) => (
                                    <div key={i} className="flex items-center gap-5 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/40 hover:border-cyan-500/30 transition-colors">
                                        <div className="text-3xl font-bold text-cyan-600 dark:text-cyan-400 min-w-[3.5rem]">{item.metric}</div>
                                        <div className="text-sm sm:text-base text-slate-600 dark:text-slate-300">{item.label}</div>
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
