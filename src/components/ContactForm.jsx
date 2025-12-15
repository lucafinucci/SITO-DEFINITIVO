import { useMemo, useState } from "react";

function validateEmail(email) {
  return /\S+@\S+\.\S+/.test(email.trim());
}

function buildMailto(defaultEmail, values) {
  const subject = encodeURIComponent("Richiesta contatto Finch-AI");
  const body = encodeURIComponent(
    `Nome: ${values.name}\n` +
    `Azienda: ${values.company}\n` +
    `Email: ${values.email}\n` +
    `Telefono: ${values.phone}\n` +
    `Esigenza: ${values.need}\n` +
    `Messaggio: ${values.message}`
  );
  return `mailto:${defaultEmail}?subject=${subject}&body=${body}`;
}

const DEFAULT_ENDPOINT = "/contact.php";

export default function ContactForm({ defaultEmail = "info@finch-ai.it" }) {
  const [values, setValues] = useState({
    name: "",
    email: "",
    phone: "",
    company: "",
    need: "",
    message: "",
    privacy: true,
  });
  const [errors, setErrors] = useState({});
  const [status, setStatus] = useState("idle"); // idle | loading | success | error
  const [errorMessage, setErrorMessage] = useState("");

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
    if (!values.name.trim()) next.name = "Inserisci il nome";
    if (!validateEmail(values.email)) next.email = "Email non valida";
    if (!values.message.trim()) next.message = "Inserisci un messaggio";
    if (!values.privacy) next.privacy = "Consenso obbligatorio";
    return next;
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

    const payload = {
      name: values.name,
      email: values.email,
      phone: values.phone,
      company: values.company,
      need: values.need,
      message: values.message,
      source: typeof window !== "undefined" ? window.location.href : "",
    };

    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      if (!res.ok) {
        throw new Error("Request failed");
      }

      setStatus("success");
      setValues({
        name: "",
        email: "",
        phone: "",
        company: "",
        need: "",
        message: "",
        privacy: true,
      });
      setErrorMessage("");
    } catch (err) {
      setStatus("error");
      setErrorMessage(`Invio non riuscito. Riprova o scrivi a ${defaultEmail}.`);
    }
  };

  return (
    <div
      id="contact-form"
      className="group relative overflow-hidden rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/70 to-slate-900/40 backdrop-blur p-6 sm:p-8 transition-all duration-300 hover:border-cyan-400/50 hover:shadow-[0_0_40px_rgba(34,211,238,0.2)] h-full flex flex-col"
    >
      <div className="absolute -inset-px opacity-50 transition-opacity duration-300 group-hover:opacity-80">
        <div className="absolute inset-0 bg-[radial-gradient(800px_300px_at_20%_0%,rgba(56,189,248,0.12),transparent)]" />
        <div className="absolute inset-0 bg-[radial-gradient(800px_300px_at_80%_100%,rgba(59,130,246,0.08),transparent)]" />
        <div className="absolute inset-0 bg-gradient-to-br from-cyan-400/10 via-transparent to-blue-500/10 opacity-0 group-hover:opacity-60 transition-opacity duration-300" />
      </div>

      <div className="relative space-y-6">
        <div>
          <div className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-200">
            Contatto diretto
          </div>
          <h3 className="mt-3 text-2xl font-bold text-white">Parla con un esperto Finch-AI</h3>
          <p className="mt-2 text-sm text-slate-300/90">
            Risposta in 24h lavorative. Meno campi, più sostanza.
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1">
              <label className="text-sm text-slate-300" htmlFor="name">Nome e cognome *</label>
              <input
                id="name"
                type="text"
                value={values.name}
                onChange={onChange("name")}
                className="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                placeholder="Mario Rossi"
              />
              {errors.name && <p className="text-xs text-rose-400">{errors.name}</p>}
            </div>
            <div className="space-y-1">
              <label className="text-sm text-slate-300" htmlFor="email">Email *</label>
              <input
                id="email"
                type="email"
                value={values.email}
                onChange={onChange("email")}
                className="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                placeholder="nome@azienda.it"
              />
              {errors.email && <p className="text-xs text-rose-400">{errors.email}</p>}
            </div>
            <div className="space-y-1">
              <label className="text-sm text-slate-300" htmlFor="phone">Telefono</label>
              <input
                id="phone"
                type="tel"
                value={values.phone}
                onChange={onChange("phone")}
                className="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                placeholder="+39 333 1234567"
              />
            </div>
            <div className="space-y-1">
              <label className="text-sm text-slate-300" htmlFor="company">Azienda</label>
              <input
                id="company"
                type="text"
                value={values.company}
                onChange={onChange("company")}
                className="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                placeholder="Ragione sociale"
              />
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-sm text-slate-300" htmlFor="need">Esigenza (es. documenti, KPI, integrazioni)</label>
            <input
              id="need"
              type="text"
              value={values.need}
              onChange={onChange("need")}
              className="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
              placeholder="Automazione DDT, dashboard KPI, integrazione ERP..."
            />
          </div>

          <div className="space-y-1">
            <label className="text-sm text-slate-300" htmlFor="message">Messaggio *</label>
            <textarea
              id="message"
              rows={4}
              value={values.message}
              onChange={onChange("message")}
              className="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
              placeholder="Descrivi il caso d'uso o cosa vuoi ottenere"
            />
            {errors.message && <p className="text-xs text-rose-400">{errors.message}</p>}
          </div>

          <div className="flex items-start gap-2">
            <input
              id="privacy"
              type="checkbox"
              checked={values.privacy}
              onChange={onChange("privacy")}
              className="mt-1 h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-500"
            />
            <label htmlFor="privacy" className="text-sm text-slate-300">
              Accetto il trattamento dei dati secondo la <a href="/privacy-policy.html" className="text-cyan-300 hover:text-cyan-200">Privacy Policy</a>.
            </label>
          </div>
          {errors.privacy && <p className="text-xs text-rose-400">{errors.privacy}</p>}

          <div className="flex flex-wrap items-center gap-3 pt-2">
            <button
              type="submit"
              disabled={status === "loading"}
              className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-70"
            >
              {status === "loading" ? "Invio..." : "Invia il messaggio"}
            </button>
          </div>

          {status === "success" && (
            <div className="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-200">
              Messaggio inviato. Ti risponderemo entro 24h lavorative.
            </div>
          )}
          {status === "error" && (
            <div className="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
              {errorMessage || `Errore di invio. Riprova o scrivi a ${defaultEmail}.`}
            </div>
          )}
        </form>
      </div>
    </div>
  );
}
