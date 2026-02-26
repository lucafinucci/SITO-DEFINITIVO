export default function Problem() {
    const problems = [
        {
            icon: "ph-file-search",
            title: "Documenti Caotici",
            desc: "DDT, fatture e ordini gestiti manualmente. Ore perse in data entry, errori frequenti, informazioni che si perdono tra email e fogli di calcolo."
        },
        {
            icon: "ph-plugs",
            title: "Sistemi Isolati",
            desc: "ERP, CRM, gestionale produzione non comunicano. Dati duplicati, sincronizzazione manuale, visibilità zero sull'insieme."
        },
        {
            icon: "ph-chart-line-down",
            title: "Decisioni al Buio",
            desc: "Report obsoleti, KPI non aggiornati, analisi che arrivano troppo tardi. Opportunità perse e problemi scoperti in ritardo."
        },
        {
            icon: "ph-timer",
            title: "Tempo Sprecato",
            desc: "Il tuo team passa ore a cercare informazioni, verificare dati e creare report invece di concentrarsi su attività strategiche."
        },
        {
            icon: "ph-currency-circle-dollar",
            title: "Costi Nascosti",
            desc: "Inefficienze operative, errori di processo, opportunità di ottimizzazione non colte. Il ROI potenziale che sta sfuggendo."
        },
        {
            icon: "ph-target",
            title: "Controllo Limitato",
            desc: "Manca una visione unificata di produzione, finanza e operations. Impossibile prendere decisioni data-driven in tempo reale."
        }
    ];

    return (
        <section id="problem" className="py-20 relative transition-colors duration-300">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-300 mb-6 font-mono">
                        Il Problema
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Il Caos che <span className="bg-clip-text text-transparent bg-gradient-to-r from-red-500 to-orange-500">Rallenta la Tua Azienda</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Documenti dispersi, dati non integrati, decisioni basate su informazioni frammentate
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {problems.map((problem, i) => (
                        <div
                            key={i}
                            className="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700/60 bg-white/50 dark:bg-slate-900/60 backdrop-blur p-8 transition-all hover:border-red-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/80 hover:shadow-[0_0_30px_rgba(239,68,68,0.1)] dark:hover:shadow-[0_0_30px_rgba(239,68,68,0.15)] shadow-sm"
                        >
                            <div className="text-3xl mb-6 bg-slate-100 dark:bg-slate-800/50 w-16 h-16 flex items-center justify-center rounded-xl text-red-600 dark:text-red-400">
                                <i className={`ph ${problem.icon}`}></i>
                            </div>
                            <h3 className="text-xl font-bold dark:text-white text-slate-900 mb-3 group-hover:text-red-600 dark:group-hover:text-red-300 transition-colors">
                                {problem.title}
                            </h3>
                            <p className="text-sm text-slate-500 dark:text-slate-400 leading-relaxed group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors">
                                {problem.desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
