import { Link } from "react-router-dom";

export default function Footer() {
  return (
    <footer className="relative border-t border-border bg-background/50 backdrop-blur">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {/* Main Footer Content */}
        <div className="grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
          {/* Company Info */}
          <div className="sm:col-span-2 lg:col-span-1">
            <div className="mb-4 flex items-center gap-3">
              <div className="relative">
                <div className="absolute inset-0 rounded-lg bg-gradient-to-br from-green-500 to-green-700 opacity-30 blur-lg" />
                <div className="relative flex h-12 w-12 items-center justify-center rounded-lg bg-white shadow-lg">
                  <img
                    src="/assets/images/LOGO.png"
                    alt="Finch-AI"
                    className="h-10 w-auto object-contain"
                  />
                </div>
              </div>
              <span className="text-xl font-bold text-foreground">Finch-AI</span>
            </div>
            <p className="text-sm text-muted-foreground leading-relaxed">
              Intelligenza artificiale su misura per l'industria. Automatizziamo processi, estraiamo insights e potenziamo le decisioni.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">Link Rapidi</h4>
            <ul className="space-y-2">
              {[
                { label: "Come Funziona", href: "/#come-funziona" },
                { label: "Chi Siamo", href: "/#chi-siamo" },
                { label: "Contatti", href: "/#contatti" },
              ].map((link, i) => (
                <li key={i}>
                  <Link
                    to={link.href}
                    className="text-sm text-muted-foreground transition-colors hover:text-primary"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contatti */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">Contatti</h4>
            <ul className="space-y-3">
              <li className="flex items-start gap-2">
                <svg className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <div>
                  <a href="mailto:info@finch-ai.it" className="text-sm text-muted-foreground transition-colors hover:text-primary block">
                    info@finch-ai.it
                  </a>
                </div>
              </li>
              <li className="flex items-start gap-2">
                <svg className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <div>
                  <a href="tel:+393287171587" className="text-sm text-muted-foreground transition-colors hover:text-primary block">
                    +39 328 717 1587
                  </a>
                  <a href="tel:+41764366624" className="text-sm text-muted-foreground transition-colors hover:text-primary block">
                    +41 76 436 6624
                  </a>
                  <a href="tel:+393756475087" className="text-sm text-muted-foreground transition-colors hover:text-primary block">
                    +39 375 647 5087
                  </a>
                  <span className="text-xs text-muted-foreground mt-1 block">Lun-Ven 9:00-18:00</span>
                </div>
              </li>
              <li className="flex items-start gap-2">
                <svg className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                  <span className="text-sm text-muted-foreground block">
                    Via Enrico Mattei, 18
                  </span>
                  <span className="text-sm text-muted-foreground block">
                    67043 Celano (AQ)
                  </span>
                  <span className="text-sm text-muted-foreground block">
                    Italia
                  </span>
                </div>
              </li>
            </ul>
          </div>

          {/* Social & Legal */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">Seguici</h4>
            <div className="flex gap-3 mb-6">
              <a
                href="https://www.linkedin.com/company/finch-ai-srl/?viewAsMember=true"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="LinkedIn"
                className="flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-muted/40 text-muted-foreground transition-all hover:border-primary/50 hover:bg-muted/60 hover:text-primary"
              >
                <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                </svg>
              </a>
              <a
                href="https://www.instagram.com/finchaiofficial"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Instagram"
                className="flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-muted\40 text-muted-foreground transition-all hover:border-primary/50 hover:bg-muted/60 hover:text-primary"
              >
                <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
            </div>
            <ul className="space-y-2">
              <li>
                <Link to="/privacy-policy.html" className="text-sm text-muted-foreground transition-colors hover:text-primary">
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link to="/cookie-policy.html" className="text-sm text-muted-foreground transition-colors hover:text-primary">
                  Cookie Policy
                </Link>
              </li>
              <li>
                <Link to="/termini-di-servizio.html" className="text-sm text-muted-foreground transition-colors hover:text-primary">
                  Termini di Servizio
                </Link>
              </li>
              <li>
                <Link to="/note-legali.html" className="text-sm text-muted-foreground transition-colors hover:text-primary">
                  Note Legali
                </Link>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t border-border/50 py-6">
          <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div className="text-center sm:text-left">
              <p className="text-sm text-muted-foreground">
                © {new Date().getFullYear()} Finch-AI S.r.l. - P.IVA 02213890664
              </p>
              <p className="text-xs text-muted-foreground/70 mt-1">
                Tutti i diritti riservati.
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}