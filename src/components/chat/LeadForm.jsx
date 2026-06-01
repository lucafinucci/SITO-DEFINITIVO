import { useMemo, useState } from "react";
import { ArrowLeft, Check, Send } from "lucide-react";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/i18n/routing";
import { track } from "@/lib/track";

function validateEmail(email) {
  return /\S+@\S+\.\S+/.test(email.trim());
}

const DEFAULT_ENDPOINT = "/contact.php";

// Costruisce una trascrizione leggibile dei messaggi della chat da allegare al lead.
function buildTranscript(messages) {
  return (messages || [])
    .filter((m) => m.content && (m.role === "user" || m.role === "assistant"))
    .map((m) => `${m.role === "user" ? "Utente" : "Assistente"}: ${m.content}`)
    .join("\n");
}

export default function LeadForm({ messages = [], onBack }) {
  const { t } = useTranslation("common");
  const lp = useLocalizedPath();

  // Precompila la descrizione con l'ultimo messaggio dell'utente, se presente.
  const initialProblem = useMemo(() => {
    const lastUser = [...messages].reverse().find((m) => m.role === "user" && m.content);
    return lastUser?.content || "";
  }, [messages]);

  const [values, setValues] = useState({
    name: "",
    email: "",
    phone: "",
    company: "",
    problem: initialProblem,
    day: "",
    slot: "any",
    privacy: true,
  });
  const [errors, setErrors] = useState({});
  const [status, setStatus] = useState("idle"); // idle | loading | success | error

  const endpoint = useMemo(
    () => import.meta.env.VITE_CONTACT_ENDPOINT || DEFAULT_ENDPOINT,
    []
  );

  const onChange = (field) => (e) => {
    const value = field === "privacy" ? e.target.checked : e.target.value;
    setValues((prev) => ({ ...prev, [field]: value }));
  };

  const validate = () => {
    const next = {};
    if (!values.name.trim()) next.name = t("chat.lead.errors.name");
    if (!validateEmail(values.email)) next.email = t("chat.lead.errors.email");
    if (!values.problem.trim()) next.problem = t("chat.lead.errors.problem");
    if (!values.privacy) next.privacy = t("chat.lead.errors.consent");
    return next;
  };

  const slotLabel = (slot) => {
    if (slot === "morning") return t("chat.lead.slotMorning");
    if (slot === "afternoon") return t("chat.lead.slotAfternoon");
    return t("chat.lead.slotAny");
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const nextErrors = validate();
    if (Object.keys(nextErrors).length) {
      setErrors(nextErrors);
      return;
    }
    setErrors({});
    setStatus("loading");

    const transcript = buildTranscript(messages);
    const preferred = [values.day, slotLabel(values.slot)].filter(Boolean).join(" ");
    const message =
      values.problem +
      (preferred ? `\n\nFascia preferita: ${preferred}` : "") +
      (transcript ? `\n\n--- Conversazione ---\n${transcript}` : "");

    const payload = {
      name: values.name,
      email: values.email,
      phone: values.phone,
      company: values.company,
      need: "Richiesta valutazione + appuntamento (chatbot)",
      message,
      source: typeof window !== "undefined" ? window.location.href : "",
    };

    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error("Request failed");
      setStatus("success");
      track("chat_lead_submitted", { slot: values.slot });
    } catch {
      setStatus("error");
    }
  };

  const inputCls =
    "w-full rounded-lg border border-border bg-muted/50 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition";

  if (status === "success") {
    return (
      <div className="flex flex-col items-center justify-center text-center gap-4 py-10 px-4">
        <div className="grid h-12 w-12 place-items-center rounded-2xl bg-primary/15 text-primary">
          <Check size={24} strokeWidth={2.5} />
        </div>
        <p className="text-sm text-foreground">{t("chat.lead.success")}</p>
        <button
          type="button"
          onClick={onBack}
          className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
        >
          <ArrowLeft size={15} /> {t("chat.lead.back")}
        </button>
      </div>
    );
  }

  return (
    <div className="px-1 py-1">
      <button
        type="button"
        onClick={onBack}
        className="inline-flex items-center gap-1.5 mb-3 text-xs font-medium text-muted-foreground hover:text-foreground"
      >
        <ArrowLeft size={14} /> {t("chat.lead.back")}
      </button>

      <h3 className="text-sm font-semibold text-foreground">{t("chat.lead.title")}</h3>
      <p className="mt-1 text-xs text-muted-foreground">{t("chat.lead.intro")}</p>

      <form onSubmit={handleSubmit} className="mt-4 space-y-3">
        <div className="space-y-1">
          <label className="text-xs text-muted-foreground" htmlFor="lead-name">
            {t("chat.lead.nameLabel")}
          </label>
          <input
            id="lead-name"
            type="text"
            value={values.name}
            onChange={onChange("name")}
            className={inputCls}
            placeholder={t("chat.lead.namePlaceholder")}
          />
          {errors.name && <p className="text-xs text-rose-500">{errors.name}</p>}
        </div>

        <div className="space-y-1">
          <label className="text-xs text-muted-foreground" htmlFor="lead-email">
            {t("chat.lead.emailLabel")}
          </label>
          <input
            id="lead-email"
            type="email"
            value={values.email}
            onChange={onChange("email")}
            className={inputCls}
            placeholder={t("chat.lead.emailPlaceholder")}
          />
          {errors.email && <p className="text-xs text-rose-500">{errors.email}</p>}
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-1">
            <label className="text-xs text-muted-foreground" htmlFor="lead-phone">
              {t("chat.lead.phoneLabel")}
            </label>
            <input
              id="lead-phone"
              type="tel"
              value={values.phone}
              onChange={onChange("phone")}
              className={inputCls}
              placeholder={t("chat.lead.phonePlaceholder")}
            />
          </div>
          <div className="space-y-1">
            <label className="text-xs text-muted-foreground" htmlFor="lead-company">
              {t("chat.lead.companyLabel")}
            </label>
            <input
              id="lead-company"
              type="text"
              value={values.company}
              onChange={onChange("company")}
              className={inputCls}
              placeholder={t("chat.lead.companyPlaceholder")}
            />
          </div>
        </div>

        <div className="space-y-1">
          <label className="text-xs text-muted-foreground" htmlFor="lead-problem">
            {t("chat.lead.problemLabel")}
          </label>
          <textarea
            id="lead-problem"
            rows={3}
            value={values.problem}
            onChange={onChange("problem")}
            className={inputCls + " resize-none"}
            placeholder={t("chat.lead.problemPlaceholder")}
          />
          {errors.problem && <p className="text-xs text-rose-500">{errors.problem}</p>}
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-1">
            <label className="text-xs text-muted-foreground" htmlFor="lead-day">
              {t("chat.lead.dayLabel")}
            </label>
            <input
              id="lead-day"
              type="date"
              value={values.day}
              onChange={onChange("day")}
              className={inputCls}
            />
          </div>
          <div className="space-y-1">
            <label className="text-xs text-muted-foreground" htmlFor="lead-slot">
              {t("chat.lead.slotLabel")}
            </label>
            <select
              id="lead-slot"
              value={values.slot}
              onChange={onChange("slot")}
              className={inputCls}
            >
              <option value="morning">{t("chat.lead.slotMorning")}</option>
              <option value="afternoon">{t("chat.lead.slotAfternoon")}</option>
              <option value="any">{t("chat.lead.slotAny")}</option>
            </select>
          </div>
        </div>

        <p className="text-[0.7rem] text-muted-foreground">{t("chat.lead.attached")}</p>

        <div className="flex items-start gap-2">
          <input
            id="lead-privacy"
            type="checkbox"
            checked={values.privacy}
            onChange={onChange("privacy")}
            className="mt-0.5 h-4 w-4 rounded border-border text-primary focus:ring-primary"
          />
          <label htmlFor="lead-privacy" className="text-xs text-muted-foreground">
            {t("chat.lead.consent")}{" "}
            <a href={lp("/privacy-policy")} className="text-primary hover:underline">
              {t("chat.lead.consentLink")}
            </a>
            .
          </label>
        </div>
        {errors.privacy && <p className="text-xs text-rose-500">{errors.privacy}</p>}

        <button
          type="submit"
          disabled={status === "loading"}
          className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90 disabled:opacity-60 transition-colors"
        >
          {status === "loading" ? (
            t("chat.lead.submitting")
          ) : (
            <>
              <Send size={15} /> {t("chat.lead.submit")}
            </>
          )}
        </button>

        {status === "error" && (
          <div className="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs text-rose-600 dark:text-rose-300">
            {t("chat.lead.error")}
          </div>
        )}
      </form>
    </div>
  );
}
