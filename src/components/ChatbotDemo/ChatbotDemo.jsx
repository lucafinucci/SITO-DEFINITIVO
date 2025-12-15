import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import './ChatbotDemo.css';

function ChatbotDemo() {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState([
    {
      type: 'bot',
      text: '👋 Ciao! Sono l\'Assistente AI di Finch. Prova a chiedermi qualcosa sui tuoi dati aziendali!',
      time: new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }),
    },
  ]);
  const [inputText, setInputText] = useState('');

  const suggestedQuestions = [
    'Qual è il fatturato di questo mese?',
    'Mostrami i top 5 clienti',
    'Efficienza produzione settimana scorsa?',
    'Previsione cash flow prossimo trimestre',
    'Ordini in ritardo oggi',
  ];

  const botResponses = {
    'fatturato': {
      text: '📊 Fatturato mese corrente: €245.680\n\n✅ +12% vs mese scorso\n🎯 Obiettivo mensile: €220.000 (111% raggiunto)\n\nVuoi un dettaglio per cliente o prodotto?',
    },
    'clienti': {
      text: '🏆 Top 5 Clienti (ultimi 30 giorni):\n\n1. Acme Corp - €45.200\n2. TechSolutions - €38.900\n3. Global Industries - €32.450\n4. MegaStore - €28.100\n5. SmartFactory - €24.800\n\nTotale: €169.450 (69% del fatturato)',
    },
    'efficienza': {
      text: '⚙️ Efficienza Produzione (ultima settimana):\n\n📈 OEE medio: 78.5% (+5.2% vs media)\n⏱️ Tempi morti: 4.2h (-1.8h vs media)\n✓ Pezzi prodotti: 12.450 (+8%)\n❌ Scarti: 1.2% (-0.5%)\n\n🔥 Ottimo lavoro! Trend positivo',
    },
    'cash flow': {
      text: '💰 Previsione Cash Flow Q1 2025:\n\n📅 Gennaio: +€85.000\n📅 Febbraio: +€92.000\n📅 Marzo: +€78.000\n\n📊 Totale previsto: +€255.000\n⚠️ Picco pagamenti fornitori: 15 Feb (-€120k)\n\n💡 Suggerimento: Considera anticipo fatture cliente TechSolutions',
    },
    'ordini': {
      text: '⏰ Ordini in Ritardo (oggi):\n\n1. ORD-4521 - Acme Corp (2 gg ritardo)\n2. ORD-4518 - MegaStore (1 gg ritardo)\n3. ORD-4507 - SmartFactory (3 gg ritardo)\n\n🚨 Totale: 3 ordini\n📦 Azioni suggerite inviate ai responsabili produzione',
    },
    'default': {
      text: '🤔 Posso aiutarti con:\n\n• KPI finanziari (fatturato, margini, cash flow)\n• Dati di produzione (efficienza, OEE, scarti)\n• Analisi clienti e fornitori\n• Ordini e logistica\n• Previsioni e simulazioni\n\nProva una delle domande suggerite oppure chiedimi qualcosa di specifico!',
    },
  };

  const handleSend = (text = inputText) => {
    if (!text.trim()) return;

    const userMessage = {
      type: 'user',
      text: text,
      time: new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }),
    };

    setMessages([...messages, userMessage]);
    setInputText('');

    setTimeout(() => {
      const lowerText = text.toLowerCase();
      let response = botResponses.default;

      if (lowerText.includes('fatturato') || lowerText.includes('ricavi')) {
        response = botResponses.fatturato;
      } else if (lowerText.includes('clienti') || lowerText.includes('top')) {
        response = botResponses.clienti;
      } else if (lowerText.includes('efficienza') || lowerText.includes('produzione')) {
        response = botResponses.efficienza;
      } else if (lowerText.includes('cash flow') || lowerText.includes('previsione')) {
        response = botResponses['cash flow'];
      } else if (lowerText.includes('ordini') || lowerText.includes('ritardo')) {
        response = botResponses.ordini;
      }

      const botMessage = {
        type: 'bot',
        text: response.text,
        time: new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }),
      };

      setMessages((prev) => [...prev, botMessage]);
    }, 800);
  };

  const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  return (
    <section className="chatbot-demo-section">
      <div className="container">
        <div className="demo-header">
          <h2>💬 Prova il Conversational Assistant</h2>
          <p>Interagisci con l'AI come faresti con un collega. Chiedi KPI, analisi, previsioni in linguaggio naturale.</p>
        </div>

        <div className="demo-container">
          <div className="chatbot-window">
            <div className="chat-header">
              <div className="chat-header-info">
                <div className="bot-avatar">🤖</div>
                <div>
                  <h4>Finch-AI Assistant</h4>
                  <span className="status">
                    <span className="status-dot"></span> Online
                  </span>
                </div>
              </div>
              <div className="chat-actions">
                <button className="minimize-btn" title="Minimizza">−</button>
              </div>
            </div>

            <div className="chat-messages">
              <AnimatePresence>
                {messages.map((msg, index) => (
                  <motion.div
                    key={index}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.3 }}
                    className={`message ${msg.type}`}
                  >
                    {msg.type === 'bot' && <div className="message-avatar">🤖</div>}
                    <div className="message-content">
                      <div className="message-text">{msg.text}</div>
                      <div className="message-time">{msg.time}</div>
                    </div>
                    {msg.type === 'user' && <div className="message-avatar user-avatar">👤</div>}
                  </motion.div>
                ))}
              </AnimatePresence>
            </div>

            <div className="suggested-questions">
              {suggestedQuestions.slice(0, 3).map((question, index) => (
                <button
                  key={index}
                  className="suggestion-chip"
                  onClick={() => handleSend(question)}
                >
                  {question}
                </button>
              ))}
            </div>

            <div className="chat-input-container">
              <input
                type="text"
                className="chat-input"
                placeholder="Chiedimi qualsiasi cosa sui tuoi dati..."
                value={inputText}
                onChange={(e) => setInputText(e.target.value)}
                onKeyPress={handleKeyPress}
              />
              <button className="send-btn" onClick={() => handleSend()}>
                ➤
              </button>
            </div>
          </div>

          <div className="demo-features">
            <h3>✨ Funzionalità</h3>
            <ul className="features-list">
              <li>
                <span className="feature-icon">🔗</span>
                <div>
                  <strong>Integrazione Multi-Sistema</strong>
                  <p>Accesso unificato a ERP, CRM, Excel, database</p>
                </div>
              </li>
              <li>
                <span className="feature-icon">📱</span>
                <div>
                  <strong>Multicanale</strong>
                  <p>Web, Teams, WhatsApp, gestionale, mobile</p>
                </div>
              </li>
              <li>
                <span className="feature-icon">🧠</span>
                <div>
                  <strong>AI Contestuale</strong>
                  <p>Comprende domande complesse e context</p>
                </div>
              </li>
              <li>
                <span className="feature-icon">⚡</span>
                <div>
                  <strong>Risposte Real-Time</strong>
                  <p>Dati sempre aggiornati, zero latenza</p>
                </div>
              </li>
              <li>
                <span className="feature-icon">🔐</span>
                <div>
                  <strong>Sicurezza Enterprise</strong>
                  <p>Permessi granulari, GDPR compliant</p>
                </div>
              </li>
              <li>
                <span className="feature-icon">📊</span>
                <div>
                  <strong>Visualizzazioni Automatiche</strong>
                  <p>Grafici e dashboard on-demand</p>
                </div>
              </li>
            </ul>

            <a href="#contatti" className="demo-cta">
              Attiva l'Assistente per la Tua Azienda →
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}

export default ChatbotDemo;
