export default function Sectors() {
    const sectors = [
        {
            sector: "Produzione & Industria",
            icon: "ph-factory",
            challenges: "Dati frammentati tra MES e carta, colli di bottiglia non identificati.",
            solutions: [
                "Monitoraggio OEE in tempo reale",
                "Digitalizzazione ordini di produzione",
                "Manutenzione predittiva base"
            ],
            results: "Maggiore efficienza sulle linee"
        },
        {
            sector: "Logistica & Supply Chain",
            icon: "ph-truck",
            challenges: "Migliaia di DDT e documenti cartacei da processare ogni settimana.",
            solutions: [
                "Automazione OCR per DDT e fatture",
                "Tracking automatico stock",
                "Matching bolle-ordini"
            ],
            results: "Zero errori di data-entry"
        },
        {
            sector: "Finance & Controllo",
            icon: "ph-chart-line-up",
            challenges: "Analisi marginalità lenta e basata su dati obsoleti.",
            solutions: [
                "Dashboard costi in tempo reale",
                "Analisi marginalità automatica",
                "Integrazione bilanci ERP"
            ],
            results: "Chiusura mensile accelerata"
        }
    ];

    return (
        <section id="settori" className="py-24 transition-colors duration-300">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-300 mb-6 font-mono">
                        I Settori
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Dove portiamo <span className="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-cyan-500">valore concreto</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Automazione documentale, KPI real-time e insight azionabili per produzione, logistica e finanza.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-3">
                    {sectors.map((item, i) => (
                        <div
                            key={i}
                            className="group relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white/50 dark:bg-slate-900/60 backdrop-blur p-8 transition-all hover:border-blue-500/50 hover:shadow-lg shadow-sm"
                        >
                            <div className="text-4xl mb-6 text-blue-600 dark:text-blue-400">
                                <i className={`ph ph-bold ${item.icon}`}></i>
                            </div>
                            <h3 className="text-2xl font-bold dark:text-white text-slate-900 mb-4 group-hover:text-blue-600 dark:group-hover:text-blue-300 transition-colors">
                                {item.sector}
                            </h3>

                            <p className="text-sm text-slate-500 dark:text-slate-400 mb-6 italic">{item.challenges}</p>

                            <div className="space-y-2 mb-6">
                                {item.solutions.map((solution, j) => (
                                    <div key={j} className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <div className="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></div>
                                        {solution}
                                    </div>
                                ))}
                            </div>

                            <div className="pt-4 border-t border-slate-100 dark:border-slate-800">
                                <span className="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">{item.results}</span>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
