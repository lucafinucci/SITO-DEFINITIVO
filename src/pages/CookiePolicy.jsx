import LegalPage from "@/components/LegalPage";
import { useLocale } from "@/i18n/routing";

const CONTENT = {
  it: {
    seo: {
      title: "Cookie Policy — Finch-AI",
      description:
        "Informativa sull'uso dei cookie e delle tecnologie simili sul sito Finch-AI.",
      keywords: "cookie policy, cookie, GDPR, Finch-AI",
    },
    lastUpdated: "Ultimo aggiornamento: 4 giugno 2026",
    title: "Cookie Policy",
    intro:
      "Questa Cookie Policy spiega cosa sono i cookie, quali tipologie utilizziamo su questo sito e come puoi gestirne le preferenze.",
    sections: [
      {
        heading: "1. Cosa sono i cookie",
        blocks: [
          "I cookie sono piccoli file di testo che i siti web salvano sul dispositivo dell'utente durante la navigazione. Consentono al sito di funzionare correttamente, di ricordare le preferenze e di raccogliere informazioni statistiche.",
        ],
      },
      {
        heading: "2. Categorie di cookie utilizzati",
        blocks: [
          "Al primo accesso mostriamo un banner che consente di accettare, rifiutare o scegliere per categoria. I cookie non necessari vengono attivati solo dopo il tuo consenso (Google Consent Mode v2).",
          [
            "Cookie tecnici/necessari: indispensabili per il funzionamento del sito (es. preferenze di lingua, tema e memorizzazione delle scelte sui cookie). Non richiedono consenso e sono sempre attivi.",
            "Cookie statistici: Google Analytics 4 (GA4), per misurare in forma anonima e aggregata l'utilizzo del sito e migliorarne i contenuti. Attivati solo previo consenso.",
            "Cookie di marketing: Google Ads, per misurare l'efficacia delle campagne pubblicitarie ed eventuale remarketing. Attivati solo previo consenso.",
          ],
        ],
      },
      {
        heading: "3. Cookie di terze parti",
        blocks: [
          "Di seguito i principali cookie di terze parti che possono essere installati previo consenso:",
          [
            "_ga (Google Analytics): distingue gli utenti in forma anonima — durata fino a 2 anni.",
            "_ga_<ID> (Google Analytics 4): mantiene lo stato della sessione — durata fino a 2 anni.",
            "_gid (Google Analytics): distingue gli utenti — durata 24 ore.",
            "_gcl_au (Google Ads/Conversion Linker): attribuzione delle conversioni pubblicitarie — durata fino a 90 giorni.",
          ],
          "Per maggiori informazioni: Privacy Policy di Google (policies.google.com/privacy) e \"Come Google utilizza i dati\" (policies.google.com/technologies/partner-sites).",
        ],
      },
      {
        heading: "4. Gestione e revoca del consenso",
        blocks: [
          "Puoi modificare o revocare in qualsiasi momento le tue scelte tramite il link \"Gestisci cookie\" presente nel footer del sito, che riapre il banner delle preferenze.",
          "In alternativa, puoi gestire o eliminare i cookie dalle impostazioni del tuo browser (Chrome, Firefox, Safari, Edge). La disattivazione dei cookie tecnici potrebbe compromettere alcune funzionalità del sito.",
        ],
      },
      {
        heading: "5. Titolare e contatti",
        blocks: [
          "Il Titolare del trattamento è Finch-AI, Via Enrico Mattei 18, 67043 Celano (AQ), P.IVA 02213890664. Per informazioni sull'uso dei cookie scrivi a info@finch-ai.it.",
          "Per il trattamento dei dati personali consulta la Privacy Policy.",
        ],
      },
    ],
  },
  en: {
    seo: {
      title: "Cookie Policy — Finch-AI",
      description: "How the Finch-AI website uses cookies and similar technologies.",
      keywords: "cookie policy, cookies, GDPR, Finch-AI",
    },
    lastUpdated: "Last updated: 4 June 2026",
    title: "Cookie Policy",
    intro:
      "This Cookie Policy explains what cookies are, which types we use on this website and how you can manage your preferences.",
    sections: [
      {
        heading: "1. What cookies are",
        blocks: [
          "Cookies are small text files that websites save on the user's device while browsing. They allow the site to work properly, remember preferences and collect statistical information.",
        ],
      },
      {
        heading: "2. Categories of cookies we use",
        blocks: [
          "On your first visit we show a banner that lets you accept, decline or choose by category. Non-necessary cookies are only enabled after your consent (Google Consent Mode v2).",
          [
            "Strictly necessary cookies: essential for the site to work (e.g. language, theme and storing your cookie choices). They do not require consent and are always active.",
            "Statistics cookies: Google Analytics 4 (GA4), to measure site usage in an anonymous and aggregated way and improve its content. Only set with consent.",
            "Marketing cookies: Google Ads, to measure advertising campaign performance and possible remarketing. Only set with consent.",
          ],
        ],
      },
      {
        heading: "3. Third-party cookies",
        blocks: [
          "The main third-party cookies that may be set after consent are:",
          [
            "_ga (Google Analytics): distinguishes users anonymously — lasts up to 2 years.",
            "_ga_<ID> (Google Analytics 4): keeps session state — lasts up to 2 years.",
            "_gid (Google Analytics): distinguishes users — lasts 24 hours.",
            "_gcl_au (Google Ads/Conversion Linker): advertising conversion attribution — lasts up to 90 days.",
          ],
          "For more information: Google Privacy Policy (policies.google.com/privacy) and \"How Google uses data\" (policies.google.com/technologies/partner-sites).",
        ],
      },
      {
        heading: "4. Managing and withdrawing consent",
        blocks: [
          "You can change or withdraw your choices at any time via the \"Manage cookies\" link in the site footer, which reopens the preferences banner.",
          "Alternatively, you can manage or delete cookies through your browser settings (Chrome, Firefox, Safari, Edge). Disabling necessary cookies may affect some features of the website.",
        ],
      },
      {
        heading: "5. Controller and contacts",
        blocks: [
          "The data controller is Finch-AI, Via Enrico Mattei 18, 67043 Celano (AQ), Italy, VAT no. 02213890664. For information about the use of cookies, write to info@finch-ai.it.",
          "For the processing of personal data, see the Privacy Policy.",
        ],
      },
    ],
  },
};

export default function CookiePolicy() {
  const locale = useLocale();
  const data = CONTENT[locale] || CONTENT.it;
  return <LegalPage canonical="https://finch-ai.it/cookie-policy" {...data} />;
}
