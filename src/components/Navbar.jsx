import { useState, useEffect, useRef } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { ArrowUpRight, ChevronDown } from "lucide-react";
import ThemeToggle from "./ThemeToggle";
import { useContactModal } from "@/context/ContactModalContext";

const SECTIONS = [
  { id: "piattaforma", label: "Approccio" },
  { id: "news", label: "News" },
  { id: "stampa", label: "Dicono di noi" },
];

const SOLUTIONS = [
  { label: "OmniFlow", href: "/soluzioni/warehouse-intelligence" },
  { label: "Document Intelligence", href: "/soluzioni/document-intelligence" },
  { label: "Finance Intelligence", href: "/soluzioni/finance-intelligence" },
  { label: "Synapse", href: "/soluzioni/synapse" },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [solOpen, setSolOpen] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();
  const { openContact } = useContactModal();
  const solRef = useRef(null);
  const closeTimer = useRef(null);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    setMobileOpen(false);
    setSolOpen(false);
  }, [location.pathname]);

  // Smooth-scroll to a homepage section, navigating home first if needed.
  const goToSection = (id) => {
    setMobileOpen(false);
    if (location.pathname === "/") {
      document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
    } else {
      navigate("/");
      setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" }), 120);
    }
  };

  const openSol = () => {
    if (closeTimer.current) clearTimeout(closeTimer.current);
    setSolOpen(true);
  };
  const closeSol = () => {
    closeTimer.current = setTimeout(() => setSolOpen(false), 180);
  };

  return (
    <>
      <header className={`nav${scrolled ? " scrolled" : ""}`}>
        <div className="nav-inner">
          <Link to="/" className="nav-logo" aria-label="Finch-AI home" onClick={() => window.scrollTo(0, 0)}>
            <img
              src="/assets/images/LOGO.png"
              alt="Finch-AI"
              onError={(e) => {
                e.currentTarget.style.display = "none";
                e.currentTarget.parentNode.classList.add("text-mode");
              }}
            />
            <span className="logo-fallback">Finch<span className="dot">-</span>AI</span>
          </Link>

          <nav className="nav-links">
            <a href="/#piattaforma" onClick={(e) => { e.preventDefault(); goToSection("piattaforma"); }}>Approccio</a>

            {/* Soluzioni dropdown */}
            <div
              ref={solRef}
              onMouseEnter={openSol}
              onMouseLeave={closeSol}
              style={{ position: "relative" }}
            >
              <a
                href="/#moduli"
                onClick={(e) => { e.preventDefault(); setSolOpen(false); goToSection("moduli"); }}
                style={{ display: "inline-flex", alignItems: "center", gap: 5 }}
              >
                Soluzioni
                <ChevronDown size={15} style={{ transition: "transform .2s", transform: solOpen ? "rotate(180deg)" : "none" }} />
              </a>
              {solOpen && (
                <div
                  onMouseEnter={openSol}
                  onMouseLeave={closeSol}
                  className="absolute left-0 top-full mt-3 w-64 overflow-hidden rounded-2xl border border-border bg-popover shadow-2xl ring-1 ring-black/5 dark:ring-white/10"
                  style={{ zIndex: 120 }}
                >
                  <div className="p-2">
                    {SOLUTIONS.map((s) => (
                      <Link
                        key={s.label}
                        to={s.href}
                        onClick={() => { setSolOpen(false); window.scrollTo(0, 0); }}
                        className="group flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-primary hover:text-primary-foreground"
                      >
                        <span>{s.label}</span>
                        <ArrowUpRight size={15} className="opacity-0 -translate-x-1 transition-all group-hover:opacity-100 group-hover:translate-x-0" />
                      </Link>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {SECTIONS.filter((s) => s.id !== "piattaforma").map((s) => (
              <a key={s.id} href={`/#${s.id}`} onClick={(e) => { e.preventDefault(); goToSection(s.id); }}>{s.label}</a>
            ))}
            <Link to="/blog">Blog</Link>
          </nav>

          <div className="nav-cta">
            <Link to="/area-clienti" className="hidden lg:inline-flex text-[15px] font-medium opacity-70 transition-opacity hover:opacity-100">
              Area Clienti
            </Link>
            <ThemeToggle />
            <button type="button" onClick={() => openContact({ prefill: { need: "Richiesta demo" } })} className="btn btn-primary">
              Prenota una demo
              <ArrowUpRight size={16} />
            </button>
            <button
              className={`nav-burger${mobileOpen ? " x" : ""}`}
              aria-label="Menu"
              onClick={() => setMobileOpen((v) => !v)}
            >
              <span></span><span></span><span></span>
            </button>
          </div>
        </div>
      </header>

      {/* Mobile menu */}
      <nav className={`mobile-menu${mobileOpen ? " open" : ""}`}>
        <a href="/#piattaforma" onClick={(e) => { e.preventDefault(); goToSection("piattaforma"); }}>Approccio</a>
        {SOLUTIONS.map((s) => (
          <Link key={s.label} to={s.href} onClick={() => { setMobileOpen(false); window.scrollTo(0, 0); }}>{s.label}</Link>
        ))}
        <a href="/#news" onClick={(e) => { e.preventDefault(); goToSection("news"); }}>News</a>
        <a href="/#stampa" onClick={(e) => { e.preventDefault(); goToSection("stampa"); }}>Dicono di noi</a>
        <Link to="/blog" onClick={() => setMobileOpen(false)}>Blog</Link>
        <Link to="/area-clienti" onClick={() => setMobileOpen(false)}>Area Clienti</Link>
        <button type="button" onClick={() => { setMobileOpen(false); openContact({ prefill: { need: "Richiesta demo" } }); }} className="btn btn-lime">Prenota una demo</button>
      </nav>
    </>
  );
}
