export default function Hero() {
    return (
        <section id="hero" className="relative pt-32 sm:pt-40 lg:pt-48 pb-16 overflow-visible z-10">
            <div className="mx-auto max-w-4xl text-center px-4 sm:px-6 lg:px-8">
                <span className="inline-flex items-center gap-2 rounded-full border border-slate-700/60 bg-slate-900/40 px-6 py-2 text-2xl tracking-wider text-cyan-300/80 animate-[fadeIn_0.8s_ease_0.05s_both]">
                    <span className="h-2.5 w-2.5 rounded-full bg-cyan-400 animate-ping" />
                    Soluzioni AI per l'Industria Italiana
                </span>

                <h1 className="mt-8 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-7xl animate-[fadeUp_0.9s_ease_0.12s_both] tracking-tight">
                    <span className="text-white">Trasforma i tuoi Dati in </span>
                    <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 via-sky-400 to-blue-500">
                        Decisioni di Valore
                    </span>
                </h1>

                <p className="mt-6 text-lg sm:text-xl text-slate-300/90 animate-[fadeUp_0.9s_ease_0.2s_both] max-w-2xl mx-auto leading-relaxed">
                    La piattaforma AI che automatizza i documenti, monitora la produzione e ottimizza i flussi finanziari per le PMI.
                </p>

                <div className="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 animate-[fadeUp_0.9s_ease_0.28s_both] w-full">
                    <a
                        href="#contatti"
                        className="group w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-4 font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:scale-105 hover:shadow-cyan-500/40"
                    >
                        Prenota una Demo
                        <svg className="h-5 w-5 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                    </a>
                    <a
                        href="#come-funziona"
                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700/70 bg-slate-900/40 px-8 py-4 font-semibold text-slate-200 hover:border-slate-500/80 hover:bg-slate-900/60 hover:text-white transition-all"
                    >
                        Come Funziona
                    </a>
                </div>

                {/* Quick Stats Bar */}
                <div className="mt-20 grid grid-cols-2 md:grid-cols-4 gap-4 animate-[fadeUp_1s_ease_0.4s_both]">
                    {[
                        { value: "70%", label: "Risparmio Tempo", sub: "Amministrativo" },
                        { value: "+1k", label: "Documenti/Giorno", sub: "Analizzati" },
                        { value: "99%", label: "Accuratezza", sub: "Garantita" },
                        { value: "4 sett", label: "Deployment", sub: "Completo" }
                    ].map((stat, i) => (
                        <div key={i} className="group p-4 rounded-2xl bg-slate-900/30 border border-slate-800 hover:border-cyan-500/30 transition-colors">
                            <div className="text-3xl font-bold text-white group-hover:text-cyan-400 transition-colors mb-1">{stat.value}</div>
                            <div className="text-sm font-semibold text-slate-400">{stat.label}</div>
                            <div className="text-xs text-slate-500">{stat.sub}</div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
