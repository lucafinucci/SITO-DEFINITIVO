export default function Sectors() {
    const sectors = [
        {
            sector: "Manufacturing & Produzione",
            icon: "🏭",
            challenges: "Gestione DDT, tracciabilità lotti, integrazione MES/ERP, monitoraggio OEE",
            solutions: [
                "Automazione completa ciclo DDT in/out",
                "Tracciabilità real-time materiali e WIP",
                "KPI produzione live su dashboard",
                "Integrazione bidirezionale con ERP"
            ],
            results: "90% riduzione tempo amministrativo, 99.5% accuratezza dati"
        },
        {
            sector: "Logistica & Distribuzione",
            icon: "🚚",
            challenges: "Volume documenti elevato, multi-vettore, gestione resi, fatturazione automatica",
            solutions: [
                "OCR multi-formato per ogni vettore",
                "Matching automatico ordine-DDT-fattura",
                "Gestione eccezioni e resi intelligente",
                "Dashboard spedizioni real-time"
            ],
            results: "Elaborazione 10x più veloce, zero errori di trascrizione"
        },
        {
            sector: "Servizi & Consulenza",
            icon: "💼",
            challenges: "Timesheet, fatturazione progetti, controllo margini, reportistica clienti",
            solutions: [
                "Automazione timesheet e approval",
                "Fatturazione automatica da milestone",
                "Analisi marginalità per progetto/cliente",
                "Report personalizzati automatici"
            ],
            results: "Chiusura mensile in 2 giorni invece di 10"
        },
        {
            sector: "Retail & E-commerce",
            icon: "🛒",
            challenges: "Gestione ordini multi-canale, inventario, fornitori, riconciliazione pagamenti",
            solutions: [
                "Unificazione ordini da tutti i canali",
                "Sincronizzazione inventario real-time",
                "Gestione automatica ordini fornitori",
                "Riconciliazione pagamenti/marketplace"
            ],
            results: "100% visibilità stock, zero rotture di stock critiche"
        }
    ];

    return (
        <section id="sectors" className="py-24 bg-gradient-to-b from-transparent to-slate-900/50">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-300 mb-6">
                        Per Chi
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6">
                        Settori che <span className="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-cyan-500">Trasformiamo</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Soluzioni verticali ottimizzate per le esigenze specifiche del tuo settore.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-2">
                    {sectors.map((item, i) => (
                        <div
                            key={i}
                            className="group relative overflow-hidden rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 lg:p-10 transition-all hover:border-blue-500/50 hover:bg-slate-900/80 hover:shadow-[0_0_40px_rgba(59,130,246,0.2)]"
                        >
                            <div className="text-5xl mb-6">{item.icon}</div>
                            <h3 className="text-2xl sm:text-3xl font-bold text-white mb-6 group-hover:text-blue-300 transition-colors">
                                {item.sector}
                            </h3>

                            <div className="mb-8">
                                <div className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Sfide comuni</div>
                                <p className="text-slate-300/90 leading-relaxed text-base">{item.challenges}</p>
                            </div>

                            <div className="mb-8">
                                <div className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-3">Come ti aiutiamo</div>
                                <div className="space-y-2">
                                    {item.solutions.map((solution, j) => (
                                        <div key={j} className="flex items-start gap-2">
                                            <svg className="h-5 w-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span className="text-sm text-slate-300">{solution}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="pt-6 border-t border-slate-700/50">
                                <div className="text-base font-semibold text-blue-400">{item.results}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
