import { Link, useLocation, useNavigate } from "react-router-dom";

export default function Footer() {
  const location = useLocation();
  const navigate = useNavigate();

  const goToSection = (id) => {
    if (location.pathname === "/") {
      document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
    } else {
      navigate("/");
      setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" }), 120);
    }
  };

  const SectionLink = ({ id, children }) => (
    <a href={`/#${id}`} onClick={(e) => { e.preventDefault(); goToSection(id); }}>{children}</a>
  );
  const RouteLink = ({ to, children }) => (
    <Link to={to} onClick={() => window.scrollTo(0, 0)}>{children}</Link>
  );

  return (
    <footer className="footer">
      <div className="wrap">
        <div className="footer-top">
          <div className="footer-brand">
            <div className="fmark">
              <img className="bird-img" src="/favicon-512.png" alt="Finch-AI" width="36" height="36" />
              Finch-AI
            </div>
            <p>
              Intelligenza artificiale su misura che si adatta ed evolve con le PMI italiane.
              Automazione documentale, analisi finanziaria, knowledge e soluzioni dedicate.
            </p>
          </div>

          <div className="footer-col">
            <h5>Soluzioni</h5>
            <RouteLink to="/soluzioni/warehouse-intelligence">OmniFlow</RouteLink>
            <RouteLink to="/soluzioni/document-intelligence">Document Intelligence</RouteLink>
            <RouteLink to="/soluzioni/finance-intelligence">Finance Intelligence</RouteLink>
            <RouteLink to="/soluzioni/synapse">Synapse</RouteLink>
            <RouteLink to="/soluzioni/aps">Pianificatore APS</RouteLink>
            <SectionLink id="sumisura">Soluzioni su misura</SectionLink>
          </div>

          <div className="footer-col">
            <h5>Azienda</h5>
            <SectionLink id="partner">Partner</SectionLink>
            <SectionLink id="news">News</SectionLink>
            <SectionLink id="stampa">Dicono di noi</SectionLink>
            <SectionLink id="clienti">Clienti</SectionLink>
            <SectionLink id="contatti">Contatti</SectionLink>
          </div>

          <div className="footer-col">
            <h5>Stampa</h5>
            <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer">Partner 24 Ore</a>
            <a href="https://youtu.be/6IZxDRKazQc" target="_blank" rel="noopener noreferrer">Video intervista</a>
            <SectionLink id="stampa">la Repubblica</SectionLink>
          </div>

          <div className="footer-col">
            <h5>Contatti</h5>
            <a href="mailto:info@finch-ai.it">info@finch-ai.it</a>
            <a href="tel:+393756475087">+39 375 647 5087</a>
            <address className="footer-address">
              Finch-AI<br />
              Via Enrico Mattei 18<br />
              67043 Celano (AQ)<br />
              P.IVA 02213890664
            </address>
          </div>
        </div>

        <div className="footer-bot">
          <small>© 2026 Finch-AI · P.IVA 02213890664 · Tutti i diritti riservati</small>
          <div className="fp">
            <a href="#" onClick={(e) => e.preventDefault()}>Privacy</a>
            <a href="#" onClick={(e) => e.preventDefault()}>Cookie</a>
            <a href="mailto:info@finch-ai.it">info@finch-ai.it</a>
          </div>
        </div>
      </div>
    </footer>
  );
}
