import { useState, useEffect } from "react";
import { Link, useLocation } from "react-router-dom";
import ThemeToggle from "./ThemeToggle";

export default function Navbar() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [activeDropdown, setActiveDropdown] = useState(null);
  const [activeSection, setActiveSection] = useState("hero");
  const location = useLocation();
  const isHomePage = location.pathname === "/";

  const navItems = [
    { id: "hero", label: "Home", href: "/" },
    {
      id: "come-funziona",
      label: "Soluzioni",
      dropdown: [
        { label: "Document Intelligence", href: "/soluzioni/document-intelligence" },
        { label: "Finance Intelligence", href: "/soluzioni/finance-intelligence" },
        { label: "Production Intelligence", disabled: true },
        { label: "Warehouse Intelligence", disabled: true },
      ]
    },
    {
      id: "articoli",
      label: "Articoli",
      dropdown: [
        { label: "AI per Imprenditori e Commercialisti", href: "/blog/intelligenza-artificiale-imprenditori-commercialisti.html", external: true },
      ]
    },
    { id: "contatti", label: "Demo", href: "/#contatti" },
    {
      id: "area-clienti",
      label: "Area Clienti",
      href: "/area-clienti",
    },
  ];

  // Scroll spy for home page
  useEffect(() => {
    if (!isHomePage) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setActiveSection(entry.target.id || "hero");
          }
        });
      },
      { threshold: 0.3, rootMargin: "-100px 0px -50% 0px" }
    );

    const sections = document.querySelectorAll("section[id]");
    sections.forEach((section) => observer.observe(section));

    return () => {
      sections.forEach((section) => observer.unobserve(section));
    };
  }, [isHomePage]);

  const handleLinkClick = (id) => {
    setMobileMenuOpen(false);
    if (isHomePage && id) {
      setActiveSection(id);
    }
  };

  return (
    <>
      <nav className="fixed top-0 left-0 right-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-xl">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-28 sm:h-32 lg:h-36 items-center justify-between">
            {/* Logo */}
            <Link to="/" onClick={() => handleLinkClick("hero")} className="group block w-full max-w-xs">
              <div className="relative w-full">
                <div className="absolute inset-0 rounded-3xl bg-gradient-to-br from-cyan-400 to-blue-500 opacity-0 dark:opacity-50 blur-[60px] transition-all group-hover:dark:opacity-70 group-hover:blur-[80px]" />
                <div className="absolute inset-0 rounded-3xl bg-cyan-400 opacity-0 dark:opacity-30 blur-3xl animate-pulse" />
                <div className="relative flex h-24 w-full sm:h-28 lg:h-32 items-center justify-center rounded-3xl bg-transparent dark:bg-white shadow-none dark:shadow-[0_0_60px_rgba(34,211,238,0.6),0_0_120px_rgba(34,211,238,0.4),0_20px_50px_rgba(0,0,0,0.3)] transition-all duration-300 group-hover:dark:shadow-[0_0_80px_rgba(34,211,238,0.8),0_0_150px_rgba(34,211,238,0.5)] group-hover:scale-[1.02] overflow-hidden border-0 dark:border-4 dark:border-cyan-400/50">
                  <img
                    src="/assets/images/LOGO.png"
                    alt="Finch-AI"
                    className="h-20 sm:h-24 lg:h-28 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 rounded-3xl border-2 border-cyan-400 opacity-0 group-hover:dark:opacity-100 animate-ping" />
                </div>
              </div>
            </Link>

            {/* Desktop Nav */}
            <div className="hidden md:flex items-center gap-1">
              {navItems.map((item) => (
                <div
                  key={item.id}
                  className="relative group/nav"
                  onMouseEnter={() => item.dropdown && setActiveDropdown(item.id)}
                  onMouseLeave={() => setActiveDropdown(null)}
                >
                  <Link
                    to={item.href || `/#${item.id}`}
                    onClick={() => handleLinkClick(item.id)}
                    className={`relative px-4 py-2 text-sm font-medium transition-colors flex items-center gap-1 ${(isHomePage && activeSection === item.id) || (item.dropdown && activeDropdown === item.id)
                      ? "text-primary"
                      : "text-muted-foreground hover:text-foreground"
                      }`}
                  >
                    {item.label}
                    {item.dropdown && (
                      <svg className={`h-4 w-4 transition-transform ${activeDropdown === item.id ? 'rotate-180' : ''}`} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    )}
                    {isHomePage && activeSection === item.id && (
                      <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-green-500 to-green-700 rounded-full" />
                    )}
                  </Link>

                  {item.dropdown && activeDropdown === item.id && (
                    <div className="absolute top-full left-0 mt-1 w-64 overflow-hidden rounded-xl border border-border bg-background/95 shadow-xl backdrop-blur-xl animate-in fade-in slide-in-from-top-2 duration-200">
                      <div className="p-2">
                        {item.dropdown.map((sub) =>
                          sub.disabled ? (
                            <div
                              key={sub.label}
                              className="px-4 py-3 rounded-lg text-sm text-muted-foreground/40 cursor-not-allowed select-none"
                            >
                              {sub.label}
                            </div>
                          ) : (
                            sub.external ? (
                              <a
                                key={sub.label}
                                href={sub.href}
                                target="_blank"
                                rel="noopener noreferrer"
                                onClick={() => setActiveDropdown(null)}
                                className="flex items-center justify-between px-4 py-3 rounded-lg text-sm transition-colors hover:bg-primary/10 hover:text-primary group/sub"
                              >
                                <span>{sub.label}</span>
                                <svg className="h-4 w-4 opacity-0 -translate-x-2 transition-all group-hover/sub:opacity-100 group-hover/sub:translate-x-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                              </a>
                            ) : (
                              <Link
                                key={sub.label}
                                to={sub.href}
                                onClick={() => handleLinkClick(null)}
                                className="flex items-center justify-between px-4 py-3 rounded-lg text-sm transition-colors hover:bg-primary/10 hover:text-primary group/sub"
                              >
                                <span>{sub.label}</span>
                                <svg className="h-4 w-4 opacity-0 -translate-x-2 transition-all group-hover/sub:opacity-100 group-hover/sub:translate-x-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                              </Link>
                            )
                          )
                        )}
                      </div>
                    </div>
                  )}
                </div>
              ))}
              <div className="ml-4 flex items-center border-l border-border/50 pl-4">
                <ThemeToggle />
              </div>
            </div>

            {/* Desktop CTA */}
            <Link
              to="/#contatti"
              onClick={() => handleLinkClick("contatti")}
              className="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-green-600 to-green-800 dark:from-cyan-500 dark:to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-green-500/20 dark:shadow-cyan-500/20 transition hover:brightness-110"
            >
              Contattaci
            </Link>

            {/* Mobile Toggle */}
            <div className="md:hidden flex items-center gap-4">
              <ThemeToggle />
              <button
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                className="inline-flex items-center justify-center p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-muted/50 transition-colors"
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
        </div>
      </nav>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden fixed inset-x-0 top-[112px] sm:top-[128px] lg:top-[144px] z-40 border-b border-border/50 bg-background/95 backdrop-blur-xl">
          <div className="mx-auto max-w-7xl px-4 py-4 space-y-2">
            {navItems.map((item) => (
              <div key={item.id} className="space-y-1">
                <Link
                  to={item.href || `/#${item.id}`}
                  onClick={() => !item.dropdown && handleLinkClick(item.id)}
                  className={`flex items-center justify-between px-4 py-3 rounded-lg text-base font-medium transition-all ${isHomePage && activeSection === item.id
                    ? "bg-primary/10 text-primary border border-primary/30"
                    : "text-muted-foreground hover:text-foreground hover:bg-muted/50"
                    }`}
                >
                  {item.label}
                  {item.dropdown && (
                    <button
                      onClick={(e) => {
                        e.preventDefault();
                        setActiveDropdown(activeDropdown === item.id ? null : item.id);
                      }}
                      className="p-1"
                    >
                      <svg className={`h-5 w-5 transition-transform ${activeDropdown === item.id ? 'rotate-180' : ''}`} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </button>
                  )}
                </Link>
                {item.dropdown && activeDropdown === item.id && (
                  <div className="pl-6 space-y-1 pb-2">
                    {item.dropdown.map((sub) =>
                      sub.disabled ? (
                        <div
                          key={sub.label}
                          className="px-4 py-2 rounded-lg text-sm text-muted-foreground/40 cursor-not-allowed select-none"
                        >
                          {sub.label}
                        </div>
                      ) : (
                        sub.external ? (
                          <a
                            key={sub.label}
                            href={sub.href}
                            target="_blank"
                            rel="noopener noreferrer"
                            onClick={() => setMobileMenuOpen(false)}
                            className="block px-4 py-2 rounded-lg text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors"
                          >
                            {sub.label}
                          </a>
                        ) : (
                          <Link
                            key={sub.label}
                            to={sub.href}
                            onClick={() => handleLinkClick(null)}
                            className="block px-4 py-2 rounded-lg text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors"
                          >
                            {sub.label}
                          </Link>
                        )
                      )
                    )}
                  </div>
                )}
              </div>
            ))}
            <Link
              to="/#contatti"
              onClick={() => handleLinkClick("contatti")}
              className="block mt-4 px-4 py-3 rounded-lg bg-gradient-to-r from-green-600 to-green-800 dark:from-cyan-500 dark:to-blue-600 text-center text-base font-semibold text-white shadow-lg shadow-green-500/20 dark:shadow-cyan-500/20"
            >
              Contattaci
            </Link>
          </div>
        </div>
      )}
    </>
  );
}
