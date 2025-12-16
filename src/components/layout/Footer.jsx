export default function Footer() {
    return (
        <footer className="relative border-t border-slate-800/50 bg-slate-950/80 backdrop-blur z-10">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Company Info */}
                    <div className="sm:col-span-2 lg:col-span-1">
                        <div className="mb-6 flex items-center gap-3">
                            <div className="relative flex h-16 w-16 items-center justify-center rounded-xl bg-slate-800 border border-slate-700">
                                <img
                                    src="/assets/images/LOGO.png"
                                    alt="Finch-AI"
                                    className="h-10 w-auto object-contain"
                                />
                            </div>
                        </div>
                        <p className="text-sm text-slate-400 leading-relaxed mb-6">
                            Intelligenza artificiale su misura per l'industria. Automatizziamo processi, estraiamo insights e potenziamo le decisioni per le PMI italiane.
                        </p>
                        <div className="flex items-center gap-2 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                            <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Operational Status: Active
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider text-white">Esplora</h4>
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
                                        className="text-sm text-slate-400 transition-colors hover:text-cyan-400"
                                    >
                                        {link.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Contact Info */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider text-white">Contatti</h4>
                        <ul className="space-y-4">
                            <li className="flex items-start gap-3">
                                <svg className="h-5 w-5 text-cyan-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a href="mailto:info@finch-ai.it" className="text-sm text-slate-400 hover:text-white transition-colors">
                                    info@finch-ai.it
                                </a>
                            </li>
                            <li className="flex items-start gap-3">
                                <svg className="h-5 w-5 text-cyan-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div className="text-sm text-slate-400">
                                    <p>Milano, Italia</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {/* Socials & Legal */}
                    <div>
                        <h4 className="mb-6 text-xs font-bold uppercase tracking-wider text-white">Seguici</h4>
                        <div className="flex gap-4 mb-8">
                            {/* Placeholders for social icons */}
                            {['lin', 'twi'].map((soc) => (
                                <a key={soc} href="#" className="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-slate-400 hover:border-cyan-500 hover:text-cyan-400 transition-all">
                                    <span className="text-xs uppercase font-bold">{soc}</span>
                                </a>
                            ))}
                        </div>
                        <div className="flex flex-col gap-2">
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-300">Privacy Policy</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-300">Termini e Condizioni</a>
                            <a href="#" className="text-xs text-slate-500 hover:text-slate-300">Cookie Policy</a>
                        </div>
                    </div>
                </div>

                <div className="border-t border-slate-800/50 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p className="text-xs text-slate-500 text-center md:text-left">
                        © {new Date().getFullYear()} Finch-AI. Tutti i diritti riservati. P.IVA 12345678901
                    </p>
                    <div className="flex items-center gap-3">
                        <span className="text-xs text-slate-600 font-semibold">Designed & Engineered in Italy</span>
                        <div className="h-4 w-6 relative overflow-hidden rounded shadow-sm opacity-80">
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
