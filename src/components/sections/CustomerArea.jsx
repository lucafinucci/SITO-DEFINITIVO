export default function CustomerArea() {
    const features = [
        {
            title: "Fatture e documenti",
            subtitle: "Download fatture",
            desc: "Scarica fatture e ricevute in PDF, storico completo per periodo, con filtri per progetto e centro di costo.",
            icon: "ph ph-file-pdf"
        },
        {
            title: "Costi per pagina & training",
            subtitle: "Monitoraggio dettagliato",
            desc: "Dashboard di consumo: costi per pagina elaborata, cicli di addestramento, storage modelli e trend temporali.",
            icon: "ph ph-chart-pie"
        },
        {
            title: "Accesso sicuro",
            subtitle: "Ruoli, MFA, audit",
            desc: "Login sicuro con MFA, ruoli (Admin/Finance/Viewer), audit trail su download e modifiche, notifiche anomalie.",
            icon: "ph ph-lock-key"
        }
    ];

    return (
        <section id="area-clienti" className="py-24 relative overflow-hidden transition-colors duration-300">
            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <span className="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-300 mb-6 font-mono">
                        Area Clienti
                    </span>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                        Scarica le fatture e <span className="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">monitora i costi in sicurezza</span>
                    </h2>
                    <p className="text-lg sm:text-xl text-slate-600 dark:text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
                        Accesso riservato con controllo ruoli, audit trail e download delle fatture. Dashboard per costi per pagina, addestramento e utilizzo.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-3">
                    {features.map((item, i) => (
                        <div key={i} className="group p-8 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white/40 dark:bg-slate-900/40 backdrop-blur transition hover:bg-white dark:hover:bg-slate-900/60 shadow-sm hover:border-emerald-500/50">
                            <div className="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mb-6 shadow-sm">
                                <i className={item.icon}></i>
                            </div>
                            <div className="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">{item.title}</div>
                            <h3 className="text-xl font-bold dark:text-white text-slate-900 mb-3">{item.subtitle}</h3>
                            <p className="text-sm text-slate-500 dark:text-slate-400 leading-relaxed italic">{item.desc}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
