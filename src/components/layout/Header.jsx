import { useState } from "react";

export default function Header({ activeSection, setActiveSection }) {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const navItems = [
        { id: "hero", label: "Finch-AI Platform", matchingIds: ["hero", "problem"] },
        { id: "soluzioni", label: "Soluzioni", matchingIds: ["soluzioni", "come-funziona", "sectors"] },
        { id: "case-studies", label: "Case Studies", matchingIds: ["case-studies"] },
        { id: "contatti", label: "Demo", matchingIds: ["contatti"] },
    ];

    const isSectionActive = (item) => {
        if (activeSection === item.id) return true;
        if (item.matchingIds && item.matchingIds.includes(activeSection)) return true;
        return false;
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/50 bg-slate-900/80 backdrop-blur-xl transition-all duration-300">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex h-20 sm:h-24 lg:h-28 items-center justify-between">
                    {/* Logo Area - RESTORED ORIGINAL */}
                    <a href="#hero" className="group block w-auto">
                        <div className="relative w-full">
                            {/* Glow effect espanso */}
                            <div className="absolute inset-0 rounded-3xl bg-gradient-to-br from-cyan-400 to-blue-500 opacity-50 blur-[60px] transition-all group-hover:opacity-70 group-hover:blur-[80px]" />
                            <div className="absolute inset-0 rounded-3xl bg-cyan-400 opacity-30 blur-3xl animate-pulse" />

                            {/* Logo container FULL WIDTH */}
                            <div className="relative flex h-16 w-auto px-6 sm:h-20 lg:h-24 items-center justify-center rounded-3xl bg-white shadow-[0_0_60px_rgba(34,211,238,0.6),0_0_120px_rgba(34,211,238,0.4),0_20px_50px_rgba(0,0,0,0.3)] transition-all duration-300 group-hover:shadow-[0_0_80px_rgba(34,211,238,0.8),0_0_150px_rgba(34,211,238,0.5)] group-hover:scale-[1.02] overflow-hidden border-4 border-cyan-400/50">
                                <img
                                    src="/assets/images/LOGO.png"
                                    alt="Finch-AI"
                                    className="h-12 sm:h-14 lg:h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                                />

                                {/* Ring pulsante multiplo */}
                                <div className="absolute inset-0 rounded-3xl border-2 border-cyan-400 opacity-0 group-hover:opacity-100 animate-ping" />
                                <div className="absolute inset-0 rounded-3xl border border-cyan-300 opacity-0 group-hover:opacity-60" style={{ animationDelay: '0.1s' }} />
                            </div>

                            {/* Riflessione sotto */}
                            <div className="absolute -bottom-2 left-0 right-0 h-8 bg-gradient-to-b from-cyan-400/20 to-transparent blur-xl opacity-60" />
                        </div>
                    </a>

                    {/* Desktop Nav Links */}
                    <div className="hidden md:flex items-center gap-1">
                        {navItems.map((item) => (
                            <a
                                key={item.id}
                                href={`#${item.id}`}
                                onClick={() => setActiveSection(item.id)}
                                className={`relative px-4 py-2 text-sm font-medium transition-colors ${isSectionActive(item)
                                    ? "text-cyan-300"
                                    : "text-slate-400 hover:text-slate-200"
                                    }`}
                            >
                                {item.label}
                                {isSectionActive(item) && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full" />
                                )}
                            </a>
                        ))}
                    </div>

                    {/* Desktop CTA Button */}
                    <a
                        href="mailto:info@finch-ai.it"
                        className="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 hover:shadow-cyan-500/40"
                    >
                        Contattaci
                    </a>

                    {/* Mobile Menu Button */}
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-cyan-300 hover:bg-slate-800/50 transition-colors"
                        aria-label="Toggle menu"
                    >
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {mobileMenuOpen ? (
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            ) : (
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            )}
                        </svg>
                    </button>
                </div>
            </div>

            {/* Mobile Menu Dropdown */}
            {mobileMenuOpen && (
                <div className="md:hidden border-t border-slate-800/50 bg-slate-900/95 backdrop-blur-xl animate-fade-in">
                    <div className="mx-auto max-w-7xl px-4 py-4 space-y-2">
                        {navItems.map((item) => (
                            <a
                                key={item.id}
                                href={`#${item.id}`}
                                onClick={() => setMobileMenuOpen(false)}
                                className={`block px-4 py-3 rounded-lg text-base font-medium transition-all ${isSectionActive(item)
                                    ? "bg-cyan-500/10 text-cyan-300 border border-cyan-500/30"
                                    : "text-slate-400 hover:text-slate-200 hover:bg-slate-800/50"
                                    }`}
                            >
                                {item.label}
                            </a>
                        ))}
                        <a
                            href="mailto:info@finch-ai.it"
                            onClick={() => setMobileMenuOpen(false)}
                            className="block mt-4 px-4 py-3 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-center text-base font-semibold text-white shadow-lg shadow-cyan-500/20"
                        >
                            Contattaci
                        </a>
                    </div>
                </div>
            )}
        </nav>
    );
}
