import LegalPage from "@/components/LegalPage";
import { useLocale } from "@/i18n/routing";

const CONTENT = {
  it: {
    seo: {
      title: "Privacy Policy — Finch-AI",
      description:
        "Informativa sul trattamento dei dati personali di Finch-AI ai sensi del Regolamento (UE) 2016/679 (GDPR).",
      keywords: "privacy policy, GDPR, trattamento dati, Finch-AI",
    },
    lastUpdated: "Ultimo aggiornamento: 31 maggio 2026",
    title: "Privacy Policy",
    intro:
      "La presente informativa descrive le modalità di trattamento dei dati personali degli utenti che consultano questo sito e che interagiscono con Finch-AI, in conformità al Regolamento (UE) 2016/679 (GDPR) e alla normativa italiana vigente.",
    sections: [
      {
        heading: "1. Titolare del trattamento",
        blocks: [
          "Il Titolare del trattamento è Finch-AI, con sede in Via Enrico Mattei 18, 67043 Celano (AQ), P.IVA 02213890664.",
          "Per qualsiasi richiesta relativa al trattamento dei dati personali è possibile scrivere a info@finch-ai.it.",
        ],
      },
      {
        heading: "2. Tipologie di dati raccolti",
        blocks: [
          "Il sito raccoglie i seguenti dati personali:",
          [
            "Dati forniti volontariamente tramite il modulo di contatto (es. nome, email, azienda, messaggio).",
            "Dati di navigazione raccolti automaticamente (es. indirizzo IP, tipo di browser, pagine visitate), anche tramite cookie e strumenti analoghi.",
          ],
        ],
      },
      {
        heading: "3. Finalità e base giuridica del trattamento",
        blocks: [
          "I dati personali sono trattati per le seguenti finalità:",
          [
            "Rispondere alle richieste di contatto e fornire informazioni sui servizi (base giuridica: esecuzione di misure precontrattuali e legittimo interesse).",
            "Garantire il corretto funzionamento e la sicurezza del sito (base giuridica: legittimo interesse).",
            "Analisi statistica e miglioramento del sito, previo consenso ove richiesto (base giuridica: consenso).",
          ],
        ],
      },
      {
        heading: "4. Modalità di trattamento e conservazione",
        blocks: [
          "I dati sono trattati con strumenti elettronici e misure di sicurezza adeguate a prevenirne la perdita, l'uso illecito o non autorizzato. I dati sono conservati per il tempo strettamente necessario al perseguimento delle finalità indicate e, comunque, nel rispetto dei termini di legge.",
        ],
      },
      {
        heading: "5. Comunicazione dei dati",
        blocks: [
          "I dati possono essere trattati da personale autorizzato e da fornitori di servizi (es. hosting, strumenti di analisi) nominati Responsabili del trattamento. I dati non sono diffusi né ceduti a terzi per finalità di marketing senza il consenso dell'interessato.",
        ],
      },
      {
        heading: "6. Diritti dell'interessato",
        blocks: [
          "In qualità di interessato, hai il diritto di:",
          [
            "Accedere ai tuoi dati personali e chiederne la rettifica o la cancellazione.",
            "Limitare od opporti al trattamento e richiedere la portabilità dei dati.",
            "Revocare il consenso in qualsiasi momento, senza pregiudicare la liceità del trattamento precedente.",
            "Proporre reclamo all'Autorità Garante per la protezione dei dati personali.",
          ],
          "Per esercitare i tuoi diritti puoi scrivere a info@finch-ai.it.",
        ],
      },
      {
        heading: "7. Cookie",
        blocks: [
          "Questo sito utilizza cookie e tecnologie simili. Per maggiori dettagli consulta la Cookie Policy.",
        ],
      },
    ],
  },
  en: {
    seo: {
      title: "Privacy Policy — Finch-AI",
      description:
        "How Finch-AI processes personal data under Regulation (EU) 2016/679 (GDPR).",
      keywords: "privacy policy, GDPR, data processing, Finch-AI",
    },
    lastUpdated: "Last updated: 31 May 2026",
    title: "Privacy Policy",
    intro:
      "This policy describes how the personal data of users who browse this website and interact with Finch-AI is processed, in accordance with Regulation (EU) 2016/679 (GDPR) and applicable Italian law.",
    sections: [
      {
        heading: "1. Data controller",
        blocks: [
          "The data controller is Finch-AI, registered at Via Enrico Mattei 18, 67043 Celano (AQ), Italy, VAT no. 02213890664.",
          "For any request regarding the processing of personal data, you can write to info@finch-ai.it.",
        ],
      },
      {
        heading: "2. Types of data collected",
        blocks: [
          "The website collects the following personal data:",
          [
            "Data you voluntarily provide through the contact form (e.g. name, email, company, message).",
            "Browsing data collected automatically (e.g. IP address, browser type, pages visited), also through cookies and similar technologies.",
          ],
        ],
      },
      {
        heading: "3. Purposes and legal basis",
        blocks: [
          "Personal data is processed for the following purposes:",
          [
            "Responding to contact requests and providing information about our services (legal basis: pre-contractual measures and legitimate interest).",
            "Ensuring the proper functioning and security of the website (legal basis: legitimate interest).",
            "Statistical analysis and improvement of the website, subject to consent where required (legal basis: consent).",
          ],
        ],
      },
      {
        heading: "4. Processing methods and retention",
        blocks: [
          "Data is processed with electronic tools and security measures designed to prevent loss, misuse or unauthorized access. Data is kept only for as long as necessary to achieve the stated purposes and in compliance with legal retention periods.",
        ],
      },
      {
        heading: "5. Data sharing",
        blocks: [
          "Data may be processed by authorized staff and by service providers (e.g. hosting, analytics) appointed as data processors. Data is not disclosed or sold to third parties for marketing purposes without the data subject's consent.",
        ],
      },
      {
        heading: "6. Your rights",
        blocks: [
          "As a data subject, you have the right to:",
          [
            "Access your personal data and request its rectification or erasure.",
            "Restrict or object to processing and request data portability.",
            "Withdraw consent at any time, without affecting the lawfulness of prior processing.",
            "Lodge a complaint with the Italian Data Protection Authority.",
          ],
          "To exercise your rights, write to info@finch-ai.it.",
        ],
      },
      {
        heading: "7. Cookies",
        blocks: [
          "This website uses cookies and similar technologies. For more details, see the Cookie Policy.",
        ],
      },
    ],
  },
};

export default function PrivacyPolicy() {
  const locale = useLocale();
  const data = CONTENT[locale] || CONTENT.it;
  return <LegalPage canonical="https://finch-ai.it/privacy-policy" {...data} />;
}
