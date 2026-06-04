import { useEffect, useState } from "react";

// Chiave localStorage per ricordare la scelta dell'utente.
const STORAGE_KEY = "finch_consent";

// Aggiorna Google Consent Mode v2 in base alla scelta.
function applyConsent(granted) {
  if (typeof window === "undefined" || typeof window.gtag !== "function") return;
  const value = granted ? "granted" : "denied";
  window.gtag("consent", "update", {
    ad_storage: value,
    analytics_storage: value,
    ad_user_data: value,
    ad_personalization: value,
  });
}

// Testi IT/EN scelti in base alla lingua corrente del documento.
function getCopy() {
  const lang = (typeof document !== "undefined" ? document.documentElement.lang : "it") || "it";
  if (lang.startsWith("en")) {
    return {
      text: "We use cookies for analytics and to measure our advertising. You can accept or decline.",
      policy: "Cookie Policy",
      accept: "Accept",
      decline: "Decline",
      href: "/en/cookie-policy",
    };
  }
  return {
    text: "Usiamo i cookie per analisi statistiche e per misurare le nostre campagne pubblicitarie. Puoi accettare o rifiutare.",
    policy: "Cookie Policy",
    accept: "Accetta",
    decline: "Rifiuta",
    href: "/cookie-policy",
  };
}

export default function CookieConsent() {
  const [visible, setVisible] = useState(false);
  const copy = getCopy();

  useEffect(() => {
    let stored = null;
    try {
      stored = localStorage.getItem(STORAGE_KEY);
    } catch {
      // localStorage non disponibile (es. modalità privacy): mostra comunque il banner.
    }
    if (stored === "granted") {
      applyConsent(true);
    } else if (stored === "denied") {
      applyConsent(false);
    } else {
      setVisible(true);
    }
  }, []);

  const choose = (granted) => {
    try {
      localStorage.setItem(STORAGE_KEY, granted ? "granted" : "denied");
    } catch {
      // ignora: se non si può salvare, il banner riapparirà al prossimo accesso
    }
    applyConsent(granted);
    setVisible(false);
  };

  if (!visible) return null;

  return (
    <div className="fixed inset-x-0 bottom-0 z-[300] p-4 sm:p-6">
      <div className="mx-auto flex max-w-3xl flex-col gap-4 rounded-2xl border border-border bg-card/95 p-5 shadow-2xl backdrop-blur sm:flex-row sm:items-center sm:justify-between">
        <p className="text-sm text-muted-foreground">
          {copy.text}{" "}
          <a href={copy.href} className="text-primary hover:underline">
            {copy.policy}
          </a>
          .
        </p>
        <div className="flex shrink-0 items-center gap-3">
          <button
            type="button"
            onClick={() => choose(false)}
            className="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
          >
            {copy.decline}
          </button>
          <button
            type="button"
            onClick={() => choose(true)}
            className="btn btn-primary"
          >
            {copy.accept}
          </button>
        </div>
      </div>
    </div>
  );
}
