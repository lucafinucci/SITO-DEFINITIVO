import { useEffect, useState } from "react";

// Versione del banner: incrementala se cambi finalità/strumenti o testi rilevanti.
// Al cambio di versione il consenso viene richiesto di nuovo.
const CONSENT_VERSION = "1";
const STORAGE_KEY = "finch_consent";
const SIX_MONTHS_MS = 1000 * 60 * 60 * 24 * 182; // ~6 mesi

// Evento globale per riaprire il banner (es. dal link "Gestisci cookie" nel footer).
export const OPEN_CONSENT_EVENT = "finch:open-consent";

const ENDPOINT = import.meta.env.VITE_CONSENT_ENDPOINT || "/consent-log.php";

// Aggiorna Google Consent Mode v2 in base alle categorie scelte.
function applyConsent({ statistici, marketing }) {
  if (typeof window === "undefined" || typeof window.gtag !== "function") return;
  window.gtag("consent", "update", {
    analytics_storage: statistici ? "granted" : "denied",
    ad_storage: marketing ? "granted" : "denied",
    ad_user_data: marketing ? "granted" : "denied",
    ad_personalization: marketing ? "granted" : "denied",
  });
}

// Registra la scelta nel DB (best-effort: ignora gli errori).
function logConsent(record) {
  try {
    fetch(ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      keepalive: true,
      body: JSON.stringify({
        consent_id: record.id,
        statistici: record.statistici,
        marketing: record.marketing,
        azione: record.azione,
        version: record.v,
        lang: (document.documentElement.lang || "it").startsWith("en") ? "en" : "it",
        page_url: window.location.href,
      }),
    }).catch(() => {});
  } catch {
    // ignora
  }
}

function readStored() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

// Decide se il consenso salvato è ancora valido (versione + scadenza 6 mesi).
function isValid(stored) {
  if (!stored || stored.v !== CONSENT_VERSION || !stored.ts) return false;
  return Date.now() - stored.ts < SIX_MONTHS_MS;
}

function getCopy() {
  const lang = (typeof document !== "undefined" ? document.documentElement.lang : "it") || "it";
  if (lang.startsWith("en")) {
    return {
      title: "We value your privacy",
      text: "We use cookies for analytics and to measure our advertising. You can accept, decline, or choose by category.",
      policy: "Cookie Policy",
      href: "/en/cookie-policy",
      acceptAll: "Accept all",
      rejectAll: "Decline all",
      customize: "Customize",
      save: "Save preferences",
      back: "Back",
      necessary: "Necessary",
      necessaryDesc: "Required for the site to work. Always active.",
      statistics: "Statistics",
      statisticsDesc: "Google Analytics 4: anonymous usage measurement.",
      marketing: "Marketing",
      marketingDesc: "Google Ads: measure campaigns and remarketing.",
      always: "Always active",
    };
  }
  return {
    title: "Rispettiamo la tua privacy",
    text: "Usiamo i cookie per analisi statistiche e per misurare le nostre campagne pubblicitarie. Puoi accettare, rifiutare o scegliere per categoria.",
    policy: "Cookie Policy",
    href: "/cookie-policy",
    acceptAll: "Accetta tutti",
    rejectAll: "Rifiuta tutti",
    customize: "Personalizza",
    save: "Salva preferenze",
    back: "Indietro",
    necessary: "Necessari",
    necessaryDesc: "Indispensabili al funzionamento del sito. Sempre attivi.",
    statistics: "Statistici",
    statisticsDesc: "Google Analytics 4: misurazione anonima dell'utilizzo.",
    marketing: "Marketing",
    marketingDesc: "Google Ads: misurazione campagne e remarketing.",
    always: "Sempre attivi",
  };
}

export default function CookieConsent() {
  const [visible, setVisible] = useState(false);
  const [showDetails, setShowDetails] = useState(false);
  const [statistici, setStatistici] = useState(false);
  const [marketing, setMarketing] = useState(false);
  const copy = getCopy();

  // Al mount: applica il consenso salvato se valido, altrimenti mostra il banner.
  useEffect(() => {
    const stored = readStored();
    if (isValid(stored)) {
      applyConsent(stored);
    } else {
      setVisible(true);
    }
  }, []);

  // Permette di riaprire il banner dal footer ("Gestisci cookie").
  useEffect(() => {
    const open = () => {
      const stored = readStored();
      setStatistici(!!stored?.statistici);
      setMarketing(!!stored?.marketing);
      setShowDetails(true);
      setVisible(true);
    };
    window.addEventListener(OPEN_CONSENT_EVENT, open);
    return () => window.removeEventListener(OPEN_CONSENT_EVENT, open);
  }, []);

  const persist = (record) => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(record));
    } catch {
      // se non si può salvare, il banner riapparirà al prossimo accesso
    }
    applyConsent(record);
    logConsent(record);
    setVisible(false);
    setShowDetails(false);
  };

  const makeRecord = (stat, mkt, azione) => ({
    v: CONSENT_VERSION,
    ts: Date.now(),
    id: (crypto?.randomUUID && crypto.randomUUID()) || String(Date.now()) + Math.random().toString(16).slice(2),
    statistici: stat,
    marketing: mkt,
    azione,
  });

  const acceptAll = () => persist(makeRecord(true, true, "accept_all"));
  const rejectAll = () => persist(makeRecord(false, false, "reject_all"));
  const saveCustom = () => persist(makeRecord(statistici, marketing, "custom"));

  if (!visible) return null;

  return (
    <div className="fixed inset-x-0 bottom-0 z-[300] p-4 sm:p-6">
      <div className="mx-auto max-w-3xl rounded-2xl border border-border bg-card/95 p-5 shadow-2xl backdrop-blur">
        <h2 className="text-base font-semibold text-foreground">{copy.title}</h2>
        <p className="mt-2 text-sm text-muted-foreground">
          {copy.text}{" "}
          <a href={copy.href} className="text-primary hover:underline">
            {copy.policy}
          </a>
          .
        </p>

        {showDetails && (
          <div className="mt-4 space-y-3">
            <CategoryRow
              title={copy.necessary}
              desc={copy.necessaryDesc}
              checked
              disabled
              alwaysLabel={copy.always}
            />
            <CategoryRow
              title={copy.statistics}
              desc={copy.statisticsDesc}
              checked={statistici}
              onChange={() => setStatistici((v) => !v)}
            />
            <CategoryRow
              title={copy.marketing}
              desc={copy.marketingDesc}
              checked={marketing}
              onChange={() => setMarketing((v) => !v)}
            />
          </div>
        )}

        <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
          {!showDetails ? (
            <button
              type="button"
              onClick={() => setShowDetails(true)}
              className="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
            >
              {copy.customize}
            </button>
          ) : (
            <button
              type="button"
              onClick={() => setShowDetails(false)}
              className="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
            >
              {copy.back}
            </button>
          )}
          <button
            type="button"
            onClick={rejectAll}
            className="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
          >
            {copy.rejectAll}
          </button>
          {showDetails && (
            <button type="button" onClick={saveCustom} className="btn btn-primary">
              {copy.save}
            </button>
          )}
          <button type="button" onClick={acceptAll} className="btn btn-primary">
            {copy.acceptAll}
          </button>
        </div>
      </div>
    </div>
  );
}

function CategoryRow({ title, desc, checked, disabled, onChange, alwaysLabel }) {
  return (
    <div className="flex items-start justify-between gap-4 rounded-xl border border-border bg-secondary/40 px-4 py-3">
      <div>
        <p className="text-sm font-medium text-foreground">{title}</p>
        <p className="text-xs text-muted-foreground">{desc}</p>
      </div>
      {disabled ? (
        <span className="shrink-0 text-xs font-medium text-muted-foreground">{alwaysLabel}</span>
      ) : (
        <label className="relative inline-flex shrink-0 cursor-pointer items-center">
          <input type="checkbox" className="peer sr-only" checked={checked} onChange={onChange} />
          <span className="h-6 w-11 rounded-full bg-muted transition-colors peer-checked:bg-primary" />
          <span className="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5" />
        </label>
      )}
    </div>
  );
}
