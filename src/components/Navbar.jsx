import { useState, useEffect, useRef } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { ArrowUpRight, ChevronDown } from "lucide-react";
import { useTranslation } from "react-i18next";
import ThemeToggle from "./ThemeToggle";
import LanguageSwitcher from "./LanguageSwitcher";
import { useLocalizedPath } from "@/i18n/routing";
import { useContactModal } from "@/context/ContactModalContext";

const SOLUTIONS = [
  { key: "omniflow", href: "/soluzioni/warehouse-intelligence" },
  { key: "document", href: "/soluzioni/document-intelligence" },
  { key: "finance", href: "/soluzioni/finance-intelligence" },
  { key: "synapse", href: "/soluzioni/synapse" },
  { key: "aps", href: "/soluzioni/aps" },
];

export default function Navbar() {
  const { t } = useTranslation("common");
  const lp = useLocalizedPath();
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
  const homePath = lp("/");
  const goToSection = (id) => {
    setMobileOpen(false);
    if (location.pathname === homePath) {
      document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
    } else {
      navigate(homePath);
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
          <Link to={lp("/")} className="nav-logo" aria-label="Finch-AI home" onClick={() => window.scrollTo(0, 0)}>
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
            <a href={`${homePath}#piattaforma`} onClick={(e) => { e.preventDefault(); goToSection("piattaforma"); }}>{t("nav.approccio")}</a>

            {/* Soluzioni dropdown */}
            <div
              ref={solRef}
              onMouseEnter={openSol}
              onMouseLeave={closeSol}
              style={{ position: "relative" }}
            >
              <a
                href={`${homePath}#moduli`}
                onClick={(e) => { e.preventDefault(); setSolOpen(false); goToSection("moduli"); }}
                style={{ display: "inline-flex", alignItems: "center", gap: 5 }}
              >
                {t("nav.soluzioni")}
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
                        key={s.key}
                        to={lp(s.href)}
                        onClick={() => { setSolOpen(false); window.scrollTo(0, 0); }}
                        className="group flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-primary hover:text-primary-foreground"
                      >
                        <span>{t(`solutionsMenu.${s.key}`)}</span>
                        <ArrowUpRight size={15} className="opacity-0 -translate-x-1 transition-all group-hover:opacity-100 group-hover:translate-x-0" />
                      </Link>
                    ))}
                  </div>
                </div>
              )}
            </div>

            <a href={`${homePath}#news`} onClick={(e) => { e.preventDefault(); goToSection("news"); }}>{t("nav.news")}</a>
            <a href={`${homePath}#stampa`} onClick={(e) => { e.preventDefault(); goToSection("stampa"); }}>{t("nav.dicono")}</a>
            <Link to={lp("/blog")}>{t("nav.blog")}</Link>
          </nav>

          <div className="nav-cta">
            <Link to={lp("/area-clienti")} className="hidden lg:inline-flex whitespace-nowrap text-[15px] font-medium opacity-70 transition-opacity hover:opacity-100">
              {t("nav.areaClienti")}
            </Link>
            <LanguageSwitcher />
            <ThemeToggle />
            <button type="button" onClick={() => openContact({ prefill: { need: t("contact.demoPrefill") } })} className="btn btn-primary">
              {t("nav.demo")}
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
        <a href={`${homePath}#piattaforma`} onClick={(e) => { e.preventDefault(); goToSection("piattaforma"); }}>{t("nav.approccio")}</a>
        {SOLUTIONS.map((s) => (
          <Link key={s.key} to={lp(s.href)} onClick={() => { setMobileOpen(false); window.scrollTo(0, 0); }}>{t(`solutionsMenu.${s.key}`)}</Link>
        ))}
        <a href={`${homePath}#news`} onClick={(e) => { e.preventDefault(); goToSection("news"); }}>{t("nav.news")}</a>
        <a href={`${homePath}#stampa`} onClick={(e) => { e.preventDefault(); goToSection("stampa"); }}>{t("nav.dicono")}</a>
        <Link to={lp("/blog")} onClick={() => setMobileOpen(false)}>{t("nav.blog")}</Link>
        <Link to={lp("/area-clienti")} onClick={() => setMobileOpen(false)}>{t("nav.areaClienti")}</Link>
        <div className="mt-2"><LanguageSwitcher /></div>
        <button type="button" onClick={() => { setMobileOpen(false); openContact({ prefill: { need: t("contact.demoPrefill") } }); }} className="btn btn-lime">{t("nav.demo")}</button>
      </nav>
    </>
  );
}
