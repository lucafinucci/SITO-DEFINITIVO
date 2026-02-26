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
                        </div>
                        <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Intelligenza artificiale su misura per l'industria. Automatizziamo processi, estraiamo insights e potenziamo le decisioni per le PMI italiane.
                        </p>
                        <div className="flex items-center gap-2 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                            <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Operational Status: Active
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider dark:text-white text-slate-900">Esplora</h4>
                        <ul className="space-y-3">
                            {[
                                { label: "Piattaforma", href: "#hero" },
                                { label: "Soluzioni", href: "#soluzioni" },
                                { label: "Case Study", href: "#case-studies" },
                                { label: "Chi Siamo", href: "/chi-siamo.html" }, // Keeping existing link
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
                                <i className="ph ph-map-pin text-cyan-600 dark:text-cyan-500 text-lg flex-shrink-0"></i>
                                <div className="text-sm text-slate-600 dark:text-slate-400">
                                    <p>Milano, Italia</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {/* Socials & Legal */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider dark:text-white text-slate-900">Seguici</h4>
                        <div className="flex gap-4 mb-8">
                            {[
                                { id: 'lin', icon: 'ph-linkedin-logo' },
                                { id: 'twi', icon: 'ph-twitter-logo' }
                            ].map((soc) => (
                                <a key={soc.id} href="#" className="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition-all shadow-sm dark:shadow-none">
                                    <i className={`ph ${soc.icon} text-lg`}></i>
                                </a>
                            ))}
                        </div>
                        <div className="flex flex-col gap-2">
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Privacy Policy</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Termini e Condizioni</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300">Cookie Policy</a>
                        </div>
                    </div>
                </div>

                <div className="border-t border-slate-100 dark:border-slate-800/50 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p className="text-xs text-slate-500 text-center md:text-left">
                        © {new Date().getFullYear()} Finch-AI. Tutti i diritti riservati. P.IVA 12345678901
                    </p>
                    <div className="flex items-center gap-3">
                        <span className="text-xs text-slate-400 dark:text-slate-600 font-semibold">Designed & Engineered in Italy</span>
                        <div className="h-4 w-6 relative overflow-hidden rounded shadow-sm opacity-60 dark:opacity-80">
                            <div className="absolute inset-0 grid grid-cols-3 h-full w-full">
                                <div className="bg-green-600"></div>
                                <div className="bg-white"></div>
                                <div className="bg-red-600"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
