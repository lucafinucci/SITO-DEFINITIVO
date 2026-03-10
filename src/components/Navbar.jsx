import { useState, useEffect, useRef } from "react";
import { Link, useLocation } from "react-router-dom";
import ThemeToggle from "./ThemeToggle";

export default function Navbar() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [activeDropdown, setActiveDropdown] = useState(null);
  const [activeSection, setActiveSection] = useState("hero");
  const location = useLocation();
  const isHomePage = location.pathname === "/";
  const navRef = useRef(null);
  const timeoutRef = useRef(null);

  const navItems = [
    { id: "hero", label: "Home", href: "/" },
    {
      id: "moduli",
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
        { label: "AI per Studi Professionali", href: "/blog/intelligenza-artificiale-studi-professionali" },
        { label: "AI per Imprenditori e Commercialisti", href: "/blog/intelligenza-artificiale-imprenditori-commercialisti" },
        { label: "Automazione Documentale con Document Intelligence", href: "/blog/document-intelligence-automazione-ddt-bolle-consegna" },
      ]
    },
    { id: "contatti", label: "Demo", href: "/#contatti" },
    { id: "area-clienti", label: "Area Clienti", href: "/area-clienti" },
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
    return () => sections.forEach((section) => observer.unobserve(section));
  }, [isHomePage]);

  // Close dropdown on click outside
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (navRef.current && !navRef.current.contains(e.target)) {
        setActiveDropdown(null);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const toggleDropdown = (id) => {
    setActiveDropdown((prev) => (prev === id ? null : id));
  };

  const handleMouseEnter = (id) => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    setActiveDropdown(id);
  };

  const handleMouseLeave = () => {
    timeoutRef.current = setTimeout(() => {
      setActiveDropdown(null);
    }, 200); // 200ms delay to move mouse
  };

  const handleLinkClick = (id) => {
    setMobileMenuOpen(false);
    setActiveDropdown(null);
    if (id) {
      setActiveSection(id);
      setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: "smooth" });
      }, 50);
    }
  };

  return (
    <>
      <nav ref={navRef} className="fixed top-0 left-0 right-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-xl">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-20 items-center justify-between">

            {/* Logo */}
            <Link to="/" onClick={() => handleLinkClick("hero")} className="flex-shrink-0">
              <div className="inline-flex items-center rounded-2xl bg-white px-4 py-2.5 shadow-sm transition hover:shadow-md">
                <img src="/assets/images/LOGO.png" alt="Finch-AI" className="h-16 w-auto object-contain" />
              </div>
            </Link>

            {/* Desktop Nav */}
            <div className="hidden md:flex items-center gap-1">
              {navItems.map((item) => (
                <div key={item.id} className="relative">
                  {item.dropdown ? (
                    <button
                      onMouseEnter={() => handleMouseEnter(item.id)}
                      onMouseLeave={handleMouseLeave}
                      onClick={() => {
                        if (isHomePage && item.id === "moduli") {
                          handleLinkClick(item.id);
                        } else {
                          toggleDropdown(item.id);
                        }
                      }}
                      className={`relative px-4 py-2 text-sm font-medium transition-colors flex items-center gap-1 ${activeDropdown === item.id ? "text-primary" : "text-muted-foreground hover:text-foreground"
                        }`}
                    >
                      {item.label}
                      <svg
                        className={`h-4 w-4 transition-transform duration-200 ${activeDropdown === item.id ? "rotate-180" : ""}`}
                        viewBox="0 0 20 20" fill="currentColor"
                      >
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </button>
                  ) : (
                    <Link
                      to={item.href}
                      onClick={() => handleLinkClick(item.id)}
                      className={`relative px-4 py-2 text-sm font-medium transition-colors flex items-center ${isHomePage && activeSection === item.id ? "text-primary" : "text-muted-foreground hover:text-foreground"
                        }`}
                    >
                      {item.label}
                      {isHomePage && activeSection === item.id && (
                        <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full" />
                      )}
                    </Link>
                  )}

                  {item.dropdown && activeDropdown === item.id && (
                    <div
                      onMouseEnter={() => handleMouseEnter(item.id)}
                      onMouseLeave={handleMouseLeave}
                      className="absolute top-full left-0 mt-1 w-64 overflow-hidden rounded-xl border border-border bg-background/95 shadow-xl backdrop-blur-xl animate-in fade-in slide-in-from-top-2 duration-200"
                    >
                      <div className="p-2">
                        {item.dropdown.map((sub) =>
                          sub.disabled ? (
                            <div
                              key={sub.label}
                              className="px-4 py-3 rounded-lg text-sm text-muted-foreground/40 cursor-not-allowed select-none"
                            >
                              {sub.label}
                            </div>
                          ) : sub.external ? (
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
              className="hidden sm:inline-flex items-center gap-2 rounded-lg bg-teal-600 dark:bg-teal-400 px-4 py-2 text-sm font-semibold text-white dark:text-[#0B1220] shadow-lg shadow-teal-500/20 transition hover:brightness-110"
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
        <div className="md:hidden fixed inset-x-0 top-[80px] z-40 border-b border-border/50 bg-background/95 backdrop-blur-xl">
          <div className="mx-auto max-w-7xl px-4 py-4 space-y-2">
            {navItems.map((item) => (
              <div key={item.id} className="space-y-1">
                {item.dropdown ? (
                  <button
                    onClick={() => {
                      toggleDropdown(item.id);
                      if (isHomePage && item.id === "moduli") {
                        handleLinkClick(item.id);
                      }
                    }}
                    className={`w-full flex items-center justify-between px-4 py-3 rounded-lg text-base font-medium transition-all ${activeDropdown === item.id
                      ? "bg-primary/10 text-primary border border-primary/30"
                      : "text-muted-foreground hover:text-foreground hover:bg-muted/50"
                      }`}
                  >
                    {item.label}
                    <svg className={`h-5 w-5 transition-transform ${activeDropdown === item.id ? "rotate-180" : ""}`} viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                  </button>
                ) : (
                  <Link
                    to={item.href}
                    onClick={() => handleLinkClick(item.id)}
                    className={`flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all ${isHomePage && activeSection === item.id
                      ? "bg-primary/10 text-primary border border-primary/30"
                      : "text-muted-foreground hover:text-foreground hover:bg-muted/50"
                      }`}
                  >
                    {item.label}
                  </Link>
                )}

                {item.dropdown && activeDropdown === item.id && (
                  <div className="pl-6 space-y-1 pb-2">
                    {item.dropdown.map((sub) =>
                      sub.disabled ? (
                        <div key={sub.label} className="px-4 py-2 rounded-lg text-sm text-muted-foreground/40 cursor-not-allowed select-none">
                          {sub.label}
                        </div>
                      ) : sub.external ? (
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
                    )}
                  </div>
                )}
              </div>
            ))}
            <Link
              to="/#contatti"
              onClick={() => handleLinkClick("contatti")}
              className="block mt-4 px-4 py-3 rounded-lg bg-teal-600 dark:bg-teal-400 text-center text-base font-semibold text-white dark:text-[#0B1220] shadow-lg shadow-teal-500/20"
            >
              Contattaci
            </Link>
          </div>
        </div>
      )}
    </>
  );
}
