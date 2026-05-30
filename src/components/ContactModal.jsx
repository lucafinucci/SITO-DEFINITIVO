import { useEffect, useMemo, useState } from "react";
import { X, ArrowUpRight, Check } from "lucide-react";

function validateEmail(email) {
  return /\S+@\S+\.\S+/.test(email.trim());
}

const DEFAULT_ENDPOINT = "/contact.php";
const DEFAULT_EMAIL = "info@finch-ai.it";

const EMPTY = { name: "", email: "", phone: "", company: "", need: "", message: "", privacy: true };

export default function ContactModal({ open, onClose, prefill }) {
  const [values, setValues] = useState(EMPTY);
  const [errors, setErrors] = useState({});
  const [status, setStatus] = useState("idle"); // idle | loading | success | error
  const [errorMessage, setErrorMessage] = useState("");

  const endpoint = useMemo(() => import.meta.env.VITE_CONTACT_ENDPOINT || DEFAULT_ENDPOINT, []);

  // Reset/prefill quando si apre; blocca lo scroll del body; ESC per chiudere
  useEffect(() => {
    if (!open) return;
    setValues({ ...EMPTY, ...(prefill || {}) });
    setErrors({});
    setStatus("idle");
    setErrorMessage("");
    const onKey = (e) => { if (e.key === "Escape") onClose(); };
    document.addEventListener("keydown", onKey);
    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = prevOverflow;
    };
  }, [open, prefill, onClose]);

  if (!open) return null;

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
    if (Object.keys(nextErrors).length) { setErrors(nextErrors); return; }
    setErrors({});
    setStatus("loading");

    const payload = {
      name: values.name, email: values.email, phone: values.phone,
      company: values.company, need: values.need, message: values.message,
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
    } catch (err) {
      setStatus("error");
      setErrorMessage(`Invio non riuscito. Riprova o scrivi a ${DEFAULT_EMAIL}.`);
    }
  };

  const inputCls = "w-full rounded-xl border border-border bg-secondary/60 px-3.5 py-2.5 text-[15px] text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition";

  return (
    <div
      className="fixed inset-0 z-[200] flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-label="Contatta Finch-AI"
    >
      {/* backdrop */}
      <div className="absolute inset-0 bg-[#0B1E16]/55 backdrop-blur-sm animate-in fade-in duration-200" onClick={onClose} />

      {/* dialog */}
      <div className="relative w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-3xl border border-border bg-card shadow-2xl ring-1 ring-black/5 dark:ring-white/10 animate-in fade-in zoom-in-95 duration-200">
        <button
          onClick={onClose}
          aria-label="Chiudi"
          className="absolute right-4 top-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
        >
          <X size={18} />
        </button>

        <div className="p-7 sm:p-9">
          {status === "success" ? (
            <div className="py-6 text-center">
              <div className="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-2xl" style={{ background: "var(--brand-grad, var(--green))", color: "#fff" }}>
                <Check size={28} strokeWidth={2.5} />
              </div>
              <h3 style={{ fontFamily: "var(--serif)" }} className="text-2xl font-medium text-foreground">Messaggio inviato</h3>
              <p className="mt-3 text-[15px] text-muted-foreground">Ti rispondiamo entro 24h lavorative. Grazie!</p>
              <button onClick={onClose} className="btn btn-primary" style={{ marginTop: 24 }}>Chiudi</button>
            </div>
          ) : (
            <>
              <span className="eyebrow">Parla con noi</span>
              <h3 style={{ fontFamily: "var(--serif)", fontWeight: 400 }} className="mt-3 text-[28px] leading-tight text-foreground">
                Scrivici, ti rispondiamo presto
              </h3>
              <p className="mt-2 text-[15px] text-muted-foreground">
                Raccontaci la tua esigenza: ti ricontattiamo entro 24h lavorative. Niente impegno.
              </p>

              <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="space-y-1.5">
                    <label className="text-sm text-muted-foreground" htmlFor="cm-name">Nome e cognome *</label>
                    <input id="cm-name" type="text" value={values.name} onChange={onChange("name")} className={inputCls} placeholder="Mario Rossi" />
                    {errors.name && <p className="text-xs text-rose-500">{errors.name}</p>}
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-sm text-muted-foreground" htmlFor="cm-email">Email *</label>
                    <input id="cm-email" type="email" value={values.email} onChange={onChange("email")} className={inputCls} placeholder="nome@azienda.it" />
                    {errors.email && <p className="text-xs text-rose-500">{errors.email}</p>}
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-sm text-muted-foreground" htmlFor="cm-phone">Telefono</label>
                    <input id="cm-phone" type="tel" value={values.phone} onChange={onChange("phone")} className={inputCls} placeholder="+39 333 1234567" />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-sm text-muted-foreground" htmlFor="cm-company">Azienda</label>
                    <input id="cm-company" type="text" value={values.company} onChange={onChange("company")} className={inputCls} placeholder="Ragione sociale" />
                  </div>
                </div>

                <div className="space-y-1.5">
                  <label className="text-sm text-muted-foreground" htmlFor="cm-message">Messaggio *</label>
                  <textarea id="cm-message" rows={4} value={values.message} onChange={onChange("message")} className={inputCls} placeholder="Descrivi il caso d'uso o cosa vuoi ottenere" />
                  {errors.message && <p className="text-xs text-rose-500">{errors.message}</p>}
                </div>

                <div className="flex items-start gap-2.5">
                  <input id="cm-privacy" type="checkbox" checked={values.privacy} onChange={onChange("privacy")} className="mt-1 h-4 w-4 rounded border-border text-primary focus:ring-primary" />
                  <label htmlFor="cm-privacy" className="text-sm text-muted-foreground">
                    Accetto il trattamento dei dati secondo la <a href="/privacy-policy.html" className="text-primary hover:underline">Privacy Policy</a>.
                  </label>
                </div>
                {errors.privacy && <p className="text-xs text-rose-500">{errors.privacy}</p>}

                <div className="flex items-center gap-3 pt-1">
                  <button type="submit" disabled={status === "loading"} className="btn btn-primary" style={{ opacity: status === "loading" ? 0.7 : 1 }}>
                    {status === "loading" ? "Invio..." : <>Invia il messaggio <ArrowUpRight size={16} /></>}
                  </button>
                  <a href={`mailto:${DEFAULT_EMAIL}`} className="text-sm text-muted-foreground hover:text-foreground transition-colors">oppure {DEFAULT_EMAIL}</a>
                </div>

                {status === "error" && (
                  <div className="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3.5 py-2.5 text-sm text-rose-600 dark:text-rose-300">
                    {errorMessage}
                  </div>
                )}
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
