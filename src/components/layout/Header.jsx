import { useState, useEffect } from "react";

export default function Header({ activeSection, setActiveSection }) {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [theme, setTheme] = useState(localStorage.getItem('theme') || 'dark');

    const navItems = [
        { id: "hero", label: "Finch-AI Platform", matchingIds: ["hero", "problem"] },
        { id: "docai", label: "Doc Intelligence", matchingIds: ["docai"] },
        { id: "soluzioni", label: "Soluzioni", matchingIds: ["soluzioni", "come-funziona", "sectors"] },
        { id: "case-studies", label: "Case Studies", matchingIds: ["case-studies"] },
        { id: "contatti", label: "Demo", matchingIds: ["contatti"] },
    ];

    useEffect(() => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        localStorage.setItem('theme', theme);
    }, [theme]);

    const toggleTheme = () => {
        setTheme(theme === 'dark' ? 'light' : 'dark');
    };

    const isSectionActive = (item) => {
        if (activeSection === item.id) return true;
        if (item.matchingIds && item.matchingIds.includes(activeSection)) return true;
        return false;
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/50 dark:bg-slate-900/80 bg-white/80 backdrop-blur-xl transition-all duration-300">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex h-16 sm:h-20 items-center justify-between">
                    {/* Logo Area */}
                    <a href="#hero" className="group block w-auto">
                        <div className="relative">
                            <div className="relative flex h-10 sm:h-12 w-auto px-4 items-center justify-center rounded-xl bg-white shadow-lg transition-all duration-300 group-hover:scale-[1.02] border-2 border-cyan-400/30">
                                <img
                                    src="/assets/images/LOGO.png"
                                    alt="Finch-AI"
                                    className="h-6 sm:h-8 w-auto object-contain"
                                />
                            </div>
                        </div>
                    </a>

                    {/* Desktop Nav Actions */}
                    <div className="hidden md:flex items-center gap-6">
                        {/* Nav Links */}
                        <div className="flex items-center gap-1">
                            {navItems.map((item) => (
                                <a
                                    key={item.id}
                                    href={`#${item.id}`}
                                    onClick={() => setActiveSection(item.id)}
                                    className={`relative px-3 py-2 text-sm font-medium transition-colors ${isSectionActive(item)
                                        ? "text-cyan-500 dark:text-cyan-300"
                                        : "text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-slate-200"
                                        }`}
                                >
                                    {item.label}
                                    {isSectionActive(item) && (
                                        <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full" />
                                    )}
                                </a>
                            ))}
                        </div>

                        <div className="flex items-center gap-4 border-l border-slate-200 dark:border-slate-800 pl-6">
                            {/* Theme Toggle */}
                            <button
                                onClick={toggleTheme}
                                className="relative flex items-center justify-center p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-300 border border-slate-200 dark:border-slate-700 shadow-sm"
                                aria-label="Toggle theme"
                            >
                                {theme === 'dark' ? (
                                    <i className="ph-bold ph-sun text-xl text-amber-500"></i>
                                ) : (
                                    <i className="ph-bold ph-moon text-xl text-indigo-600"></i>
                                )}
                            </button>

                            {/* CTA Button */}
                            <a
                                href="mailto:info@finch-ai.it"
                                className="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
                            >
                                Contattaci
                            </a>
                        </div>
                    </div>

                    {/* Mobile Menu Actions */}
                    <div className="flex md:hidden items-center gap-2">
                        <button
                            onClick={toggleTheme}
                            className="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
                        >
                            {theme === 'dark' ? (
                                <i className="ph-bold ph-sun text-xl text-amber-500"></i>
                            ) : (
                                <i className="ph-bold ph-moon text-xl text-indigo-600"></i>
                            )}
                        </button>
                        <button
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-300"
                            aria-label="Toggle menu"
                        >
                            <i className={`ph ${mobileMenuOpen ? 'ph-x' : 'ph-list'} text-2xl`}></i>
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile Menu Dropdown */}
            {mobileMenuOpen && (
                <div className="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl animate-fade-in">
                    <div className="px-4 py-4 space-y-2">
                        {navItems.map((item) => (
                            <a
                                key={item.id}
                                href={`#${item.id}`}
                                onClick={() => setMobileMenuOpen(false)}
                                className={`block px-4 py-3 rounded-lg text-base font-medium transition-all ${isSectionActive(item)
                                    ? "bg-cyan-500/10 text-cyan-600 dark:text-cyan-300 border border-cyan-500/30"
                                    : "text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-slate-200"
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
