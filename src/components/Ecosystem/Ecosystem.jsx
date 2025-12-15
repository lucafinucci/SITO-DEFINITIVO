import { motion } from 'framer-motion';
import './Ecosystem.css';

const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.15,
    },
  },
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6, ease: 'easeOut' },
  },
};

function Ecosystem() {
  const modules = {
    diagnosi: [
      {
        icon: '🧩',
        title: 'Finch-AI Assessment',
        description: 'Analisi intelligente di processi, dati e KPI per individuare inefficienze e opportunità.',
        output: "Diagnosi operativa + piano d'azione",
        type: 'foundation',
      },
    ],
    esecuzione: [
      {
        icon: '🧾',
        title: 'Finch-AI Document Intelligence',
        description: 'OCR + AI per leggere DDT, fatture, ordini, contratti e documenti tecnici.',
        output: 'Database strutturato integrabile con gestionale',
      },
      {
        icon: '⚙️',
        title: 'Finch-AI Production',
        description: 'Analisi, pianificazione e ottimizzazione della produzione con algoritmi predittivi.',
        output: 'Piano produzione ottimizzato + KPI',
        type: 'highlight',
      },
      {
        icon: '💼',
        title: 'Finch-AI Finance',
        description: 'Analisi automatica di costi, ricavi, cash flow e previsioni economico-finanziarie.',
        output: 'Forecast + indicatori economici intelligenti',
      },
      {
        icon: '📦',
        title: 'Finch-AI Warehouse',
        description: 'Gestione intelligente del magazzino: scorte, movimenti, OCR logistico, previsioni e ottimizzazioni.',
        output: 'Giacenze sincronizzate + previsioni riordino',
      },
    ],
    strategia: [
      {
        icon: '📊',
        title: 'Finch-AI Strategic Planner',
        description: 'Simulazione scenari produttivi, economici e strategici.',
        output: 'Piani strategici + simulazioni “what-if”',
      },
      {
        icon: '💬',
        title: 'Finch-AI Conversational Assistant',
        description: 'Chatbot AI multicanale per interrogare KPI e dati aziendali con linguaggio naturale.',
        output: 'Accesso immediato a dati operativi e strategici',
      },
    ],
  };

  return (
    <section id="ecosystem" className="ecosystem">
      <div className="container">
        <motion.div
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.2 }}
          variants={containerVariants}
          className="ecosystem-header"
        >
          <motion.h2 variants={itemVariants}>
            🚀 7 Moduli AI che Trasformano la Tua Azienda
          </motion.h2>
          <motion.p variants={itemVariants} className="ecosystem-subtitle">
            Un ecosistema completo per ridurre costi, automatizzare processi e prendere decisioni basate sui dati. Dall'assessment iniziale alla pianificazione strategica, ogni modulo risolve un problema reale.
          </motion.p>
        </motion.div>

        <motion.div
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.1 }}
          variants={containerVariants}
          className="ecosystem-architecture"
        >
          {/* Colonna Diagnosi */}
          <motion.div variants={itemVariants} className="column-diagnosis">
            <h3>🧩 Fase 1: Assessment & Diagnosi</h3>
            {modules.diagnosi.map((module, index) => (
              <div key={index} className={`module-card ${module.type || ''}`}>
                <div className="module-icon">{module.icon}</div>
                <h4>{module.title}</h4>
                <p>{module.description}</p>
                <div className="output">
                  <strong>Output:</strong> {module.output}
                </div>
              </div>
            ))}
          </motion.div>

          {/* Colonna Esecuzione */}
          <motion.div variants={itemVariants} className="column-execution">
            <h3>⚙️ Fase 2: Automazione & Ottimizzazione</h3>
            {modules.esecuzione.map((module, index) => (
              <div key={index} className={`module-card ${module.type || ''}`}>
                <div className="module-icon">{module.icon}</div>
                <h4>{module.title}</h4>
                <p>{module.description}</p>
                <div className="output">
                  <strong>Output:</strong> {module.output}
                </div>
              </div>
            ))}
          </motion.div>

          {/* Colonna Strategia */}
          <motion.div variants={itemVariants} className="column-strategy">
            <h3>📊 Fase 3: Pianificazione & Decisione</h3>
            {modules.strategia.map((module, index) => (
              <div key={index} className="module-card">
                <div className="module-icon">{module.icon}</div>
                <h4>{module.title}</h4>
                <p>{module.description}</p>
                <div className="output">
                  <strong>Output:</strong> {module.output}
                </div>
              </div>
            ))}
          </motion.div>
        </motion.div>

        {/* CTA */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="ecosystem-cta"
        >
          <p className="cta-text">
            💡 <strong>Non serve implementare tutti i moduli subito.</strong> Inizia con l'assessment gratuito e scegli i moduli che risolvono i tuoi problemi più urgenti. ROI visibile in 3-6 mesi.
          </p>
          <a href="#contatti" className="cta-button">
            Richiedi Assessment Gratuito →
          </a>
        </motion.div>
      </div>
    </section>
  );
}

export default Ecosystem;
