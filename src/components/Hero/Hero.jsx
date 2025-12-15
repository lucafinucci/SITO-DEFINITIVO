import { motion } from 'framer-motion';
import './Hero.css';

function Hero() {
  const handleAssessmentClick = () => {
    // Scroll to contact section
    const contactSection = document.getElementById('contatti');
    if (contactSection) {
      contactSection.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const handleServicesClick = () => {
    // Scroll to services/ecosystem section
    const servicesSection = document.getElementById('come-funziona') || document.getElementById('ecosystem');
    if (servicesSection) {
      servicesSection.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <section id="home" className="hero">
      <div className="hero-content">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.7, ease: "easeOut" }}
          className="hero-badge"
        >
          <span className="badge-text">🚀 La tua azienda merita l'intelligenza artificiale</span>
        </motion.div>

        <motion.h1
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.1 }}
        >
          Trasforma i Dati in Decisioni.<br/>L'AI che Accelera il Tuo Business.
        </motion.h1>

        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.2 }}
          className="hero-subtitle"
        >
          Soluzioni di <strong>intelligenza artificiale accessibili, etiche e personalizzate</strong> per aziende e PA che vogliono ridurre costi, ottimizzare tempi e prendere decisioni strategiche basate sui dati reali.
        </motion.p>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="hero-stats"
        >
          <div className="stat-item">
            <span className="stat-number">-40%</span>
            <span className="stat-label">Tempi operativi</span>
          </div>
          <div className="stat-item">
            <span className="stat-number">+65%</span>
            <span className="stat-label">Efficienza processi</span>
          </div>
          <div className="stat-item">
            <span className="stat-number">7</span>
            <span className="stat-label">Moduli AI integrabili</span>
          </div>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.4 }}
          className="cta-container"
        >
          <button className="cta-primary" onClick={handleAssessmentClick}>
            <span className="cta-icon">📊</span>
            <span className="cta-content">
              <strong>Richiedi Assessment Gratuito</strong>
              <small>Analisi personalizzata dei tuoi processi</small>
            </span>
          </button>
          <button className="cta-secondary" onClick={handleServicesClick}>
            <span className="cta-icon">💡</span>
            <span className="cta-content">
              <strong>Scopri le Soluzioni AI</strong>
              <small>7 moduli per ogni esigenza aziendale</small>
            </span>
          </button>
        </motion.div>

        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.6, delay: 0.5 }}
          className="vision-badge"
        >
          <span>🎯 La nostra missione: rendere ogni decisione aziendale più rapida, consapevole e strategica attraverso un ecosistema AI completo che ottimizza produzione, amministrazione e direzione.</span>
        </motion.div>
      </div>
    </section>
  );
}

export default Hero;
