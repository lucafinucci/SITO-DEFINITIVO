import { useState, useEffect } from "react";
import { Link, useLocation } from "react-router-dom";

export default function Header({ activeSection, setActiveSection }) {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [productsMenuOpen, setProductsMenuOpen] = useState(false);
    const [theme, setTheme] = useState(localStorage.getItem('theme') || 'dark');
    const location = useLocation();

    const navItems = [
        { id: "hero", label: "Home", path: "/", matchingIds: ["hero", "problem"] },
        { id: "soluzioni", label: "Come Funziona", path: "/#soluzioni", matchingIds: ["soluzioni", "come-funziona", "sectors"] },
        { id: "chi-siamo", label: "Chi Siamo", path: "/#chi-siamo", matchingIds: ["chi-siamo"] },
        { id: "contatti", label: "Contatti", path: "/#contatti", matchingIds: ["contatti"] },
    ];

    const products = [
        { id: "docai", label: "Document Intelligence", path: "/prodotti/document-intelligence", icon: "ph-bold ph-files" },
        // Add more products here in the future
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
        if (location.pathname !== '/' && item.path === '/') return false;
        if (activeSection === item.id) return true;
        if (item.matchingIds && item.matchingIds.includes(activeSection)) return true;
        return false;
    };

    const isProductActive = () => {
        return location.pathname.startsWith('/prodotti');
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/50 dark:bg-slate-900/80 bg-white/80 backdrop-blur-xl transition-all duration-300">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex h-16 sm:h-20 items-center justify-between">
                    {/* Logo Area */}
                    <Link to="/" className="group block w-auto" onClick={() => setActiveSection('hero')}>
                        <div className="relative">
                            <div className="relative flex h-10 sm:h-12 w-auto px-4 items-center justify-center rounded-xl bg-white shadow-lg transition-all duration-300 group-hover:scale-[1.02] border-2 border-cyan-400/30">
                                <img
                                    src="/assets/images/LOGO.png"
                                    alt="Finch-AI"
                                    className="h-6 sm:h-8 w-auto object-contain"
                                />
                            </div>
                        </div>
                    </Link>

                    {/* Desktop Nav Actions */}
                    <div className="hidden md:flex items-center gap-6">
                        {/* Nav Links */}
                        <div className="flex items-center gap-1">
                            {/* Platform Home */}
                            <Link
                                to="/"
                                onClick={() => setActiveSection('hero')}
                                className={`relative px-3 py-2 text-sm font-medium transition-colors ${isSectionActive(navItems[0])
                                    ? "text-cyan-500 dark:text-cyan-300"
                                    : "text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-slate-200"
                                    }`}
                            >
                                Home
                                {isSectionActive(navItems[0]) && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full" />
                                )}
                            </Link>

                            {/* Products Dropdown */}
                            <div
                                className="relative"
                                onMouseEnter={() => setProductsMenuOpen(true)}
                                onMouseLeave={() => setProductsMenuOpen(false)}
                            >
                                <button
                                    className={`flex items-center gap-1 px-3 py-2 text-sm font-medium transition-colors ${isProductActive()
                                        ? "text-cyan-500 dark:text-cyan-300"
                                        : "text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-slate-200"
                                        }`}
                                >
                                    I nostri prodotti
                                    <i className={`ph ph-caret-down transition-transform duration-200 ${productsMenuOpen ? 'rotate-180' : ''}`}></i>
                                </button>

                                {productsMenuOpen && (
                                    <div className="absolute top-full left-0 w-64 pt-2 animate-in fade-in slide-in-from-top-1">
                                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl p-2 backdrop-blur-xl">
                                            {products.map((product) => (
                                                <Link
                                                    key={product.id}
                                                    to={product.path}
                                                    className="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
                                                    onClick={() => setProductsMenuOpen(false)}
                                                >
                                                    <div className="w-10 h-10 flex items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-500 group-hover:bg-cyan-500 group-hover:text-white transition-all">
                                                        <i className={`${product.icon} text-lg`}></i>
                                                    </div>
                                                    <div>
                                                        <div className="text-sm font-semibold text-slate-900 dark:text-white">{product.label}</div>
                                                        <div className="text-[10px] text-slate-500 dark:text-slate-400">Automazione documenti</div>
                                                    </div>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Other links */}
                            {navItems.slice(1).map((item) => (
                                <Link
                                    key={item.id}
                                    to={item.path}
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
                                </Link>
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
                        <Link
                            to="/"
                            onClick={() => { setMobileMenuOpen(false); setActiveSection('hero'); }}
                            className={`block px-4 py-3 rounded-lg text-base font-medium transition-all ${location.pathname === '/' && activeSection === 'hero'
                                ? "bg-cyan-500/10 text-cyan-600 dark:text-cyan-300 border border-cyan-500/30"
                                : "text-slate-600 dark:text-slate-400"
                                }`}
                        >
                            Home
                        </Link>

                        <div className="py-2">
                            <div className="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">I nostri prodotti</div>
                            {products.map((product) => (
                                <Link
                                    key={product.id}
                                    to={product.path}
                                    onClick={() => setMobileMenuOpen(false)}
                                    className={`flex items-center gap-3 px-4 py-3 rounded-lg text-base font-medium transition-all ${location.pathname === product.path
                                        ? "bg-cyan-500/10 text-cyan-600 dark:text-cyan-300 border border-cyan-500/30"
                                        : "text-slate-600 dark:text-slate-400"
                                        }`}
                                >
                                    <i className={`${product.icon} text-lg`}></i>
                                    {product.label}
                                </Link>
                            ))}
                        </div>

                        {navItems.slice(1).map((item) => (
                            <Link
                                key={item.id}
                                to={item.path}
                                onClick={() => { setMobileMenuOpen(false); setActiveSection(item.id); }}
                                className={`block px-4 py-3 rounded-lg text-base font-medium transition-all ${isSectionActive(item)
                                    ? "bg-cyan-500/10 text-cyan-600 dark:text-cyan-300 border border-cyan-500/30"
                                    : "text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-slate-200"
                                    }`}
                            >
                                {item.label}
                            </Link>
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
