import { Link, useLocation, useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/i18n/routing";

export default function Footer() {
  const { t } = useTranslation("common");
  const lp = useLocalizedPath();
  const location = useLocation();
  const navigate = useNavigate();
  const homePath = lp("/");

  const goToSection = (id) => {
    if (location.pathname === homePath) {
      document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
    } else {
      navigate(homePath);
      setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" }), 120);
    }
  };

  const SectionLink = ({ id, children }) => (
    <a href={`${homePath}#${id}`} onClick={(e) => { e.preventDefault(); goToSection(id); }}>{children}</a>
  );
  const RouteLink = ({ to, children }) => (
    <Link to={lp(to)} onClick={() => window.scrollTo(0, 0)}>{children}</Link>
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
            <p>{t("footer.brandDesc")}</p>
            <div className="footer-social">
              <a href="https://www.linkedin.com/company/finch-ai-srl/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                  <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14zM8.34 18.34V9.99H5.67v8.35h2.67zM7 8.81a1.55 1.55 0 1 0 0-3.1 1.55 1.55 0 0 0 0 3.1zm11.34 9.53v-4.58c0-2.46-1.31-3.6-3.06-3.6-1.41 0-2.04.78-2.4 1.32v-1.13h-2.66c.04.75 0 8.35 0 8.35h2.66v-4.66c0-.24.02-.48.09-.65.19-.48.63-.97 1.36-.97.96 0 1.35.73 1.35 1.8v4.48h2.66z"/>
                </svg>
              </a>
              <a href="https://www.youtube.com/@Finch-AI" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                  <path d="M23.5 6.5a3.02 3.02 0 0 0-2.12-2.14C19.5 3.85 12 3.85 12 3.85s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.5C0 8.39 0 12 0 12s0 3.61.5 5.5a3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14C24 15.61 24 12 24 12s0-3.61-.5-5.5zM9.6 15.6V8.4l6.2 3.6-6.2 3.6z"/>
                </svg>
              </a>
              <a href="https://www.instagram.com/finchaiofficial" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                  <path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 1.62c-3.15 0-3.52.01-4.76.07-1.15.05-1.77.24-2.19.4-.55.22-.94.47-1.35.88-.41.41-.66.8-.88 1.35-.16.42-.35 1.04-.4 2.19-.06 1.24-.07 1.61-.07 4.76s.01 3.52.07 4.76c.05 1.15.24 1.77.4 2.19.22.55.47.94.88 1.35.41.41.8.66 1.35.88.42.16 1.04.35 2.19.4 1.24.06 1.61.07 4.76.07s3.52-.01 4.76-.07c1.15-.05 1.77-.24 2.19-.4.55-.22.94-.47 1.35-.88.41-.41.66-.8.88-1.35.16-.42.35-1.04.4-2.19.06-1.24.07-1.61.07-4.76s-.01-3.52-.07-4.76c-.05-1.15-.24-1.77-.4-2.19a3.64 3.64 0 0 0-.88-1.35 3.64 3.64 0 0 0-1.35-.88c-.42-.16-1.04-.35-2.19-.4-1.24-.06-1.61-.07-4.76-.07zm0 2.76a5.3 5.3 0 1 0 0 10.6 5.3 5.3 0 0 0 0-10.6zm0 8.74a3.44 3.44 0 1 1 0-6.88 3.44 3.44 0 0 1 0 6.88zm6.74-8.94a1.24 1.24 0 1 1-2.48 0 1.24 1.24 0 0 1 2.48 0z"/>
                </svg>
              </a>
            </div>
          </div>

          <div className="footer-col">
            <h5>{t("footer.colSolutions")}</h5>
            <RouteLink to="/soluzioni/warehouse-intelligence">{t("solutionsMenu.omniflow")}</RouteLink>
            <RouteLink to="/soluzioni/document-intelligence">{t("solutionsMenu.document")}</RouteLink>
            <RouteLink to="/soluzioni/finance-intelligence">{t("solutionsMenu.finance")}</RouteLink>
            <RouteLink to="/soluzioni/synapse">{t("solutionsMenu.synapse")}</RouteLink>
            <RouteLink to="/soluzioni/aps">{t("solutionsMenu.aps")}</RouteLink>
            <SectionLink id="sumisura">{t("footer.customSolutions")}</SectionLink>
          </div>

          <div className="footer-col">
            <h5>{t("footer.colCompany")}</h5>
            <SectionLink id="partner">{t("footer.partner")}</SectionLink>
            <SectionLink id="news">{t("footer.news")}</SectionLink>
            <SectionLink id="stampa">{t("footer.press")}</SectionLink>
            <SectionLink id="clienti">{t("footer.clients")}</SectionLink>
            <SectionLink id="contatti">{t("footer.contacts")}</SectionLink>
          </div>

          <div className="footer-col">
            <h5>{t("footer.colPress")}</h5>
            <a href="https://partner24ore.ilsole24ore.com/partner/finch-ai/" target="_blank" rel="noopener noreferrer">{t("footer.partner24")}</a>
            <a href="https://youtu.be/6IZxDRKazQc" target="_blank" rel="noopener noreferrer">{t("footer.videoInterview")}</a>
            <SectionLink id="stampa">{t("footer.repubblica")}</SectionLink>
          </div>

          <div className="footer-col">
            <h5>{t("footer.colContacts")}</h5>
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
          <small>{t("footer.copyright")}</small>
          <div className="fp">
            <RouteLink to="/privacy-policy">{t("footer.privacy")}</RouteLink>
            <RouteLink to="/cookie-policy">{t("footer.cookie")}</RouteLink>
            <a href="mailto:info@finch-ai.it">info@finch-ai.it</a>
          </div>
        </div>
      </div>
    </footer>
  );
}
