export default function Modules() {
    const modules = [
        {
            id: "doc-intel",
            tag: "Operativo",
            title: "Finch-AI Document Intelligence",
            desc: "Automazione documentale basata su AI per ridurre errori e tempi operativi.",
            feature: "Configurazione autonoma assistita dall'AI.",
            output: "Output: Dati documentali strutturati e verificati",
            cta: "Scopri come funziona",
            link: "/prodotti/document-intelligence",
            color: "cyan",
            icon: (
                <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M4 4h10l6 6v10a2 2 0 0 1-2 2H4V4z" fill="none" stroke="currentColor" strokeWidth="1.8" /><path d="M14 4v6h6" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
            )
        },
        {
            id: "prod-intel",
            tag: "Operativo",
            title: "Finch-AI Production Intelligence",
            desc: "Pianificazione e supporto decisionale potenziati dall'AI.",
            feature: "Configurazione e gestione assistite dall'AI in tutte le fasi.",
            output: "Output: Pianificazione operativa e supporto decisionale in produzione",
            cta: "Vedi la produzione",
            link: "#",
            color: "purple",
            icon: (
                <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M4 19h16M6 16V8m6 8V5m6 11v-7" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
            )
        },
        {
            id: "fin-intel",
            tag: "Operativo",
            title: "Finch-AI Finance Intelligence",
            desc: "Analisi automatica di costi, ricavi e cash flow.",
            feature: "Previsioni economico-finanziarie per guidare le decisioni.",
            output: "Output: Forecast e indicatori economici intelligenti",
            cta: "Esplora la finanza",
            link: "#",
            color: "emerald",
            icon: (
                <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M7 8h10M4 12h16M7 16h10" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
            )
        },
        {
            id: "wh-intel",
            tag: "Operativo",
            title: "Finch-AI Warehouse Intelligence",
            desc: "Gestione integrata di magazzino, ordini e offerte.",
            feature: "Decisioni e operatività potenziate dall'AI.",
            output: "Output: Magazzino, ordini e offerte sincronizzati in un unico flusso",
            cta: "Scopri il magazzino",
            link: "#",
            color: "blue",
            icon: (
                <svg viewBox="0 0 24 24" className="h-8 w-8"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" fill="none" stroke="currentColor" strokeWidth="1.8" /></svg>
            )
        }
    ];

    return (
        <section id="soluzioni" className="py-24 relative transition-colors duration-300">
            <div className="absolute left-0 top-1/2 -translate-y-1/2 -ml-20 w-[600px] h-[600px] bg-purple-500/5 rounded-full blur-[120px] pointer-events-none" />

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-purple-500/30 bg-purple-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-300 mb-6 font-mono">
                        I Moduli
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Un ecosistema intelligente, <span className="bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500">già operativo</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Quattro applicazioni già operative, integrate in un unico ecosistema AI pensato per adattarsi all'identità di ogni azienda.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-2">
                    {modules.map((module) => (
                        <div
                            key={module.id}
                            className={`group relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/40 p-8 backdrop-blur transition hover:border-${module.color}-500/50 shadow-sm hover:shadow-xl hover:shadow-${module.color}-500/5`}
                        >
                            <div className="relative z-10">
                                <div className="flex justify-between items-start mb-6">
                                    <div className={`rounded-xl border border-${module.color}-500/30 bg-${module.color}-500/10 p-3 text-${module.color}-600 dark:text-${module.color}-300 shadow-lg`}>
                                        {module.icon}
                                    </div>
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-${module.color}-500/10 text-${module.color}-600 dark:text-${module.color}-400 border border-${module.color}-500/20`}>
                                        {module.tag}
                                    </span>
                                </div>

                                <h3 className="text-2xl font-bold dark:text-white text-slate-900 mb-4">{module.title}</h3>
                                <p className="text-slate-600 dark:text-slate-300 mb-4 leading-relaxed">{module.desc}</p>

                                <div className="space-y-3 mb-8">
                                    <div className="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium">
                                        <i className="ph ph-check-circle text-lg"></i>
                                        {module.feature}
                                    </div>
                                    <div className={`text-sm font-bold text-${module.color}-600 dark:text-${module.color}-400`}>
                                        {module.output}
                                    </div>
                                </div>

                                <a
                                    href={module.link}
                                    className={`inline-flex items-center gap-2 text-sm font-bold transition-all group-hover:gap-3 text-slate-900 dark:text-white hover:text-${module.color}-500 dark:hover:text-${module.color}-400`}
                                >
                                    {module.cta}
                                    <i className="ph ph-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="mt-16 text-center">
                    <p className="inline-block px-8 py-4 rounded-2xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-medium max-w-2xl italic">
                        "Finch-AI non è una collezione di strumenti, ma un ecosistema che cresce insieme alla tua azienda, partendo da ciò che conta davvero."
                    </p>
                </div>
            </div>
        </section>
    );
}
