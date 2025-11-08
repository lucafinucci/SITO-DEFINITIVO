import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';

// Definiamo le varianti per le animazioni per riutilizzarle
const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.2, // Anima i figli con un ritardo
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

function Home() {
    return (
        <>
            <motion.section className="hero-new" initial="hidden" animate="visible" variants={containerVariants}>
                <motion.div className="hero-left" variants={itemVariants}>
                    <h1>Trasformiamo i dati in decisioni, l'immaginazione in realtà. La tua AI, su misura.</h1>
                    <p>Sviluppiamo soluzioni di intelligenza artificiale personalizzate per accelerare la trasformazione digitale e la crescita sostenibile della tua PMI.</p>
                    <Link to="/contatti.html" className="cta-button">Inizia il tuo progetto AI</Link>
                </motion.div>
                <motion.div className="hero-right" variants={itemVariants}>
                    <div className="hero-video-wrapper">
                        <video autoPlay loop muted playsInline>
                            <source src="/assets/images/fringuello.mp4" type="video/mp4" />
                            <source src="/assets/images/fringuello.webm" type="video/webm" />
                            Il tuo browser non supporta il tag video.
                        </video>
                    </div>
                </motion.div>
            </motion.section>

            <motion.section className="ai-solutions" initial="hidden" whileInView="visible" viewport={{ once: true, amount: 0.3 }} variants={containerVariants}>
                <motion.h2 className="section-title" variants={itemVariants}>Le Nostre Soluzioni AI</motion.h2>
                <motion.p className="section-subtitle" variants={itemVariants}>Prodotti pronti all'uso e piattaforme personalizzabili, sviluppati con un approccio etico e responsabile per risolvere le sfide più complesse.</motion.p>
                <motion.div className="solutions-grid" variants={containerVariants}>
                    <motion.div className="solution-card" variants={itemVariants}>
                        <div className="solution-icon">📄</div>
                        <h3>Automazione Documentale</h3>
                        <p>Estrai dati, classifica documenti e automatizza i workflow con la nostra AI per la gestione documentale.</p>
                    </motion.div>
                    <motion.div className="solution-card" variants={itemVariants}>
                        <div className="solution-icon">💬</div>
                        <h3>Chatbot Intelligenti</h3>
                        <p>Migliora il customer service e l'engagement con chatbot conversazionali basati su NLP, disponibili 24/7.</p>
                    </motion.div>
                    <motion.div className="solution-card" variants={itemVariants}>
                        <div className="solution-icon">📅</div>
                        <h3>Pianificazione Ottimizzata</h3>
                        <p>Ottimizza la logistica, la produzione e la gestione delle risorse con algoritmi di pianificazione intelligenti.</p>
                    </motion.div>
                    <motion.div className="solution-card" variants={itemVariants}>
                        <div className="solution-icon">⚙️</div>
                        <h3>Sviluppo AI su Misura</h3>
                        <p>Dall'analisi dei requisiti all'implementazione, creiamo soluzioni AI uniche per le tue sfide di business.</p>
                    </motion.div>
                </motion.div>
            </motion.section>
        </>
    );
}

export default Home;