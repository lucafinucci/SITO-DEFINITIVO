export default function Footer() {
    return (
        <footer className="relative border-t border-slate-200 dark:border-slate-800/50 bg-white dark:bg-slate-950/80 backdrop-blur z-10 transition-colors duration-300">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Company Info */}
                    <div className="sm:col-span-2 lg:col-span-1">
                        <div className="mb-6 flex items-center gap-3">
                            <div className="relative flex h-16 w-16 items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                                <img
                                    src="/assets/images/LOGO.png"
                                    alt="Finch-AI"
                                    className="h-10 w-auto object-contain"
                                />
                            </div>
                            <span className="text-xl font-bold dark:text-white text-slate-900">Finch-AI</span>
                        </div>
                        <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Intelligenza artificiale su misura per l'industria. Automatizziamo processi, estraiamo insights e potenziamo le decisioni.
                        </p>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider dark:text-white text-slate-900">Link Rapidi</h4>
                        <ul className="space-y-3">
                            {[
                                { label: "Come Funziona", href: "/#soluzioni" },
                                { label: "Chi Siamo", href: "/#chi-siamo" },
                                { label: "Contatti", href: "/#contatti" },
                            ].map((link, i) => (
                                <li key={i}>
                                    <a
                                        href={link.href}
                                        className="text-sm text-slate-600 dark:text-slate-400 transition-colors hover:text-cyan-600 dark:hover:text-cyan-400"
                                    >
                                        {link.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Contact Info */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider dark:text-white text-slate-900">Contatti</h4>
                        <ul className="space-y-4">
                            <li className="flex items-start gap-3">
                                <i className="ph ph-envelope text-cyan-600 dark:text-cyan-500 text-lg flex-shrink-0"></i>
                                <a href="mailto:info@finch-ai.it" className="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    info@finch-ai.it
                                </a>
                            </li>
                            <li className="flex items-start gap-3">
                                <i className="ph ph-phone text-cyan-600 dark:text-cyan-500 text-lg flex-shrink-0"></i>
                                <div className="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                                    <p>+39 328 717 1587</p>
                                    <p>+41 76 436 6624</p>
                                    <p>+39 375 647 5087</p>
                                </div>
                            </li>
                            <li className="flex items-start gap-3">
                                <i className="ph ph-map-pin text-cyan-600 dark:text-cyan-500 text-lg flex-shrink-0"></i>
                                <div className="text-sm text-slate-600 dark:text-slate-400">
                                    <p>Via Enrico Mattei, 18</p>
                                    <p>67043 Celano (AQ)</p>
                                    <p>Italia</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {/* Socials & Legal */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider dark:text-white text-slate-900">Seguici</h4>
                        <div className="flex gap-4 mb-8">
                            <a href="#" className="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition-all shadow-sm">
                                <i className="ph ph-linkedin-logo text-lg"></i>
                            </a>
                        </div>
                        <div className="flex flex-col gap-2">
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Privacy Policy</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Cookie Policy</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Termini di Servizio</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Note Legali</a>
                        </div>
                    </div>
                </div>

                <div className="border-t border-slate-100 dark:border-slate-800/50 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p className="text-xs text-slate-500 text-center md:text-left">
                        © {new Date().getFullYear()} Finch-AI. Tutti i diritti riservati.
                    </p>
                    <div className="flex items-center gap-2 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                        <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Lun-Ven 9:00-18:00
                    </div>
                </div>
            </div>
        </footer>
    );
}
