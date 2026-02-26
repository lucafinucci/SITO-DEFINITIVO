export default function WhyUs() {
    const stats = [
        {
            value: "70%",
            label: "Riduzione tempo elaborazione documenti",
            desc: "Da ore a minuti per processare DDT, fatture e ordini"
        },
        {
            value: "+1000",
            label: "Documenti/giorno analizzati automaticamente",
            desc: "Capacità di elaborazione scalabile senza limiti"
        },
        {
            value: "99.2%",
            label: "Accuratezza estrazione dati",
            desc: "OCR con validazione intelligente domain-specific"
        },
        {
            value: "24/7",
            label: "Monitoraggio operativo continuo",
            desc: "Alert real-time su anomalie e opportunità"
        }
    ];

    const benefits = [
        {
            title: "Deploy Rapido",
            desc: "Operativi in 4-8 settimane, non mesi. Integrazione plug-and-play con i tuoi sistemi esistenti."
        },
        {
            title: "Zero Vendor Lock-in",
            desc: "Dati sempre tuoi, esportabili, API aperte. Integrazione con qualsiasi ERP, CRM o gestionale."
        },
        {
            title: "ROI Garantito",
            desc: "Break-even medio in 6 mesi. Calcolo ROI personalizzato prima di partire. Nessun costo nascosto."
        }
    ];

    return (
        <section id="numbers" className="py-24 relative overflow-hidden transition-colors duration-300">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-300 mb-6 font-mono">
                        Perché Finch-AI
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Numeri che <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-500 to-blue-600">Parlano Chiaro</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Non solo promesse: risultati misurabili sin dal primo giorno
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-20">
                    {stats.map((stat, i) => (
                        <div key={i} className="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/40 backdrop-blur transition hover:border-cyan-500/30 text-center">
                            <div className="text-4xl font-extrabold text-cyan-600 dark:text-cyan-400 mb-3">{stat.value}</div>
                            <div className="text-base font-bold dark:text-white text-slate-900 mb-2">{stat.label}</div>
                            <div className="text-sm text-slate-500 dark:text-slate-400">{stat.desc}</div>
                        </div>
                    ))}
                </div>

                <div className="grid gap-8 lg:grid-cols-3">
                    {benefits.map((benefit, i) => (
                        <div key={i} className="relative p-8 rounded-3xl border border-slate-100 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-900/20 backdrop-blur transition hover:bg-white dark:hover:bg-slate-900/40">
                            <h3 className="text-xl font-bold dark:text-white text-slate-900 mb-4 flex items-center gap-3">
                                <span className="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-500 flex items-center justify-center text-lg">
                                    <i className="ph ph-check-bold"></i>
                                </span>
                                {benefit.title}
                            </h3>
                            <p className="text-slate-600 dark:text-slate-300/90 leading-relaxed italic">
                                {benefit.desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
