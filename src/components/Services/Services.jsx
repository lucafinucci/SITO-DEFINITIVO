import { motion } from 'framer-motion';
import './Services.css';
import { Bot, BarChart3, Settings, Lightbulb, Target, MessageSquare } from 'lucide-react';

const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
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

function Services() {
  const services = [
    {
      title: 'Modelli Predittivi e Prescrittivi Personalizzati',
      description: 'Il tuo business è unico. Sviluppiamo algoritmi AI custom per le tue sfide specifiche: previsione domanda, ottimizzazione prezzi, riduzione difetti, o qualsiasi altro problema misurabile.',
      benefit: '✓ Soluzioni che si adattano al 100% al tuo contesto',
      icon: Bot,
    },
    {
      title: 'Dashboard Integrate Multi-Sorgente',
      description: 'Fine del caos dati. Unifichiamo ERP, CRM, Excel, database e sistemi legacy in un\'unica dashboard real-time. Decidi con informazioni complete, non frammentate.',
      benefit: '✓ Tutti i tuoi dati in un unico posto, sempre aggiornati',
      icon: BarChart3,
    },
    {
      title: 'Pianificazione, Schedulazione e Controllo Avanzato',
      description: 'Schedulazione produzione che si adatta automaticamente a urgenze, disponibilità risorse e vincoli. Algoritmi ottimizzano tempi, riducono colli di bottiglia e massimizzano output.',
      benefit: '✓ Produzione più fluida, meno ritardi, maggiore saturazione',
      icon: Settings,
    },
    {
      title: 'AI Strategy & Digital Transformation',
      description: 'Non sapete da dove iniziare? Vi accompagniamo nella definizione della strategia AI, nella trasformazione digitale dei processi e nell\'adozione di best practice operative.',
      benefit: '✓ Roadmap chiara dal concept al ROI misurabile',
      icon: Lightbulb,
    },
  ];

  const handleConsultingClick = () => {
    // Scroll to contact section
    const contactSection = document.getElementById('contatti');
    if (contactSection) {
      contactSection.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <section id="servizi" className="custom-services">
      <div className="container">
        <motion.div
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.2 }}
          variants={containerVariants}
          className="services-header"
        >
          <motion.h2 variants={itemVariants} className="flex items-center gap-2">
            <Target className="h-6 w-6" />
            Servizi Personalizzati: L'AI che si Adatta a Te
          </motion.h2>
          <motion.p variants={itemVariants} className="services-subtitle">
            Oltre ai 7 moduli standard, sviluppiamo soluzioni custom per aziende e PA con esigenze specifiche. Dalla previsione della domanda all'ottimizzazione logistica, dall'analisi predittiva al supporto decisionale avanzato.
          </motion.p>
        </motion.div>

        <motion.div
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.2 }}
          variants={containerVariants}
          className="services-grid"
        >
          {services.map((service, index) => {
            const IconComponent = service.icon;
            return (
            <motion.div key={index} variants={itemVariants} className="service-card">
              <div className="service-icon">
                <IconComponent className="h-10 w-10" strokeWidth={1.5} />
              </div>
              <h4>{service.title}</h4>
              <p>{service.description}</p>
              {service.benefit && <p className="service-benefit">{service.benefit}</p>}
            </motion.div>
            );
          })}
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="consulting-cta"
        >
          <p className="consulting-quote">
            <MessageSquare className="h-5 w-5 inline-block mr-2" />
            <strong>Hai un problema specifico?</strong> Raccontaci la tua sfida e progettiamo insieme la soluzione AI su misura. Prima consulenza gratuita per valutare fattibilità e ROI.
          </p>
          <button className="cta-tertiary" onClick={handleConsultingClick}>
            Richiedi Consulenza Gratuita →
          </button>
        </motion.div>
      </div>
    </section>
  );
}

export default Services;
