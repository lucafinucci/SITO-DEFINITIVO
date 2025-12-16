export default function Problem() {
    const problems = [
        {
            icon: "📄",
            title: "Documenti Caotici",
            desc: "DDT, fatture e ordini gestiti manualmente. Ore perse in data entry, errori frequenti, informazioni che si perdono tra email e fogli di calcolo."
        },
        {
            icon: "🔌",
            title: "Sistemi Isolati",
            desc: "ERP, CRM, gestionale produzione non comunicano. Dati duplicati, sincronizzazione manuale, visibilità zero sull'insieme."
        },
        {
            icon: "📊",
            title: "Decisioni al Buio",
            desc: "Report obsoleti, KPI non aggiornati, analisi che arrivano troppo tardi. Opportunità perse e problemi scoperti in ritardo."
        },
        {
            icon: "⏱️",
            title: "Tempo Sprecato",
            desc: "Il tuo team passa ore a cercare informazioni, verificare dati e creare report invece di concentrarsi su attività strategiche."
        },
        {
            icon: "💸",
            title: "Costi Nascosti",
            desc: "Inefficienze operative, errori di processo, opportunità di ottimizzazione non colte. Il ROI potenziale che sta sfuggendo."
        },
        {
            icon: "🎯",
            title: "Controllo Limitato",
            desc: "Manca una visione unificata di produzione, finanza e operations. Impossibile prendere decisioni data-driven in tempo reale."
        }
    ];

    return (
        <section id="problem" className="py-20 relative">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-300 mb-6">
                        Il Problema
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                        Il Caos che <span className="bg-clip-text text-transparent bg-gradient-to-r from-red-400 to-orange-500">Rallenta la Tua Azienda</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Documenti dispersi, dati non integrati, decisioni basate su informazioni frammentate
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {problems.map((problem, i) => (
                        <div
                            key={i}
                            className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 transition-all hover:border-red-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_30px_rgba(239,68,68,0.15)]"
                        >
                            <div className="text-4xl mb-6 bg-slate-800/50 w-16 h-16 flex items-center justify-center rounded-xl">{problem.icon}</div>
                            <h3 className="text-xl font-bold text-white mb-3 group-hover:text-red-300 transition-colors">
                                {problem.title}
                            </h3>
                            <p className="text-sm text-slate-400 leading-relaxed group-hover:text-slate-300 transition-colors">
                                {problem.desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
