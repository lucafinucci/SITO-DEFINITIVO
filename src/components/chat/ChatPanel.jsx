import { useEffect, useLayoutEffect, useRef, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { X, Send, RefreshCw, ShieldAlert, CalendarCheck } from "lucide-react";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/i18n/routing";
import { track } from "@/lib/track";
import MessageBubble from "./MessageBubble";
import SuggestedQuestions from "./SuggestedQuestions";
import LeadForm from "./LeadForm";

export default function ChatPanel({ open, onClose, chat }) {
  const { t } = useTranslation("common");
  const lp = useLocalizedPath();
  const { messages, streaming, sendMessage, reset } = chat;

  const [input, setInput] = useState("");
  const [view, setView] = useState("chat"); // "chat" | "lead"
  const inputRef = useRef(null);
  const scrollRef = useRef(null);
  const lastFocusRef = useRef(null);

  const openLead = () => { setView("lead"); track("chat_lead_open"); };
  const backToChat = () => setView("chat");

  // Torna sempre alla vista chat quando il pannello viene chiuso.
  useEffect(() => { if (!open) setView("chat"); }, [open]);

  // Auto-scroll quando arrivano nuovi messaggi o delta
  useLayoutEffect(() => {
    if (!open || !scrollRef.current) return;
    const el = scrollRef.current;
    el.scrollTop = el.scrollHeight;
  }, [open, messages]);

  // ESC per chiudere, focus iniziale sull'input, restore focus alla chiusura
  useEffect(() => {
    if (!open) return;
    lastFocusRef.current = document.activeElement;
    const t = setTimeout(() => inputRef.current?.focus(), 80);
    const onKey = (e) => { if (e.key === "Escape") onClose(); };
    document.addEventListener("keydown", onKey);
    return () => {
      clearTimeout(t);
      document.removeEventListener("keydown", onKey);
      lastFocusRef.current?.focus?.();
    };
  }, [open, onClose]);

  const handleSend = (e) => {
    e?.preventDefault?.();
    if (!input.trim() || streaming) return;
    sendMessage(input);
    setInput("");
  };

  const handlePick = (q) => {
    if (streaming) return;
    sendMessage(q);
  };

  return (
    <AnimatePresence>
      {open && (
        <motion.div
          role="dialog"
          aria-modal="true"
          aria-label={t("chat.title")}
          initial={{ opacity: 0, y: 24, scale: 0.98 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, y: 24, scale: 0.98 }}
          transition={{ duration: 0.18, ease: "easeOut" }}
          className={[
            "fixed z-[60] flex flex-col",
            "bg-background/95 backdrop-blur-md border border-border/70 shadow-2xl",
            "inset-x-2 bottom-2 top-2 sm:inset-auto sm:bottom-24 sm:right-6 sm:top-auto sm:left-auto",
            "sm:w-[380px] sm:h-[600px] sm:max-h-[80vh] rounded-2xl overflow-hidden",
          ].join(" ")}
        >
          {/* Header */}
          <header className="flex items-center gap-2 px-4 py-3 border-b border-border/60 bg-card/40">
            <div className="w-8 h-8 rounded-full bg-primary/15 grid place-items-center">
              <span className="text-primary text-sm font-bold">AI</span>
            </div>
            <div className="flex-1 min-w-0">
              <h2 className="text-sm font-semibold leading-tight truncate">{t("chat.title")}</h2>
              <p className="text-[0.7rem] text-muted-foreground leading-tight truncate flex items-center gap-1.5">
                <span className="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500" />
                {t("chat.subtitle")}
              </p>
            </div>
            <button
              type="button"
              onClick={reset}
              disabled={streaming || messages.length === 0}
              title={t("chat.reset")}
              aria-label={t("chat.reset")}
              className="p-1.5 rounded-md text-muted-foreground hover:bg-muted disabled:opacity-30"
            >
              <RefreshCw size={14} />
            </button>
            <button
              type="button"
              onClick={onClose}
              title={t("chat.close")}
              aria-label={t("chat.close")}
              className="p-1.5 rounded-md text-muted-foreground hover:bg-muted"
            >
              <X size={16} />
            </button>
          </header>

          {/* Body */}
          <div
            ref={scrollRef}
            className="flex-1 overflow-y-auto px-3 py-3 space-y-3"
            aria-live="polite"
          >
            {view === "lead" ? (
              <LeadForm messages={messages} onBack={backToChat} />
            ) : messages.length === 0 ? (
              <div className="space-y-3 pt-2">
                <div className="text-sm text-muted-foreground px-1">{t("chat.welcome")}</div>
                <SuggestedQuestions onPick={handlePick} />
              </div>
            ) : (
              messages.map((m, i) => (
                <MessageBubble
                  key={i}
                  role={m.role}
                  content={m.content}
                  sources={m.sources}
                  error={m.error}
                  streaming={streaming && i === messages.length - 1 && m.role === "assistant"}
                />
              ))
            )}
          </div>

          {/* CTA: richiedi valutazione / appuntamento (nascosta nella vista lead) */}
          {view === "chat" && (
            <div className="border-t border-border/60 bg-card/20 px-3 py-2">
              <button
                type="button"
                onClick={openLead}
                className="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-primary/40 bg-primary/10 px-3 py-2 text-sm font-medium text-primary hover:bg-primary/20 transition-colors"
              >
                <CalendarCheck size={16} /> {t("chat.lead.cta")}
              </button>
            </div>
          )}

          {/* Input (nascosto nella vista lead) */}
          {view === "chat" && (
          <form onSubmit={handleSend} className="border-t border-border/60 bg-card/30">
            <div className="flex items-end gap-2 px-3 py-2.5">
              <textarea
                ref={inputRef}
                value={input}
                onChange={(e) => setInput(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    handleSend();
                  }
                }}
                placeholder={t("chat.placeholder")}
                rows={1}
                maxLength={1500}
                disabled={streaming}
                className="flex-1 resize-none bg-transparent text-sm leading-snug px-2 py-1.5 max-h-32 outline-none placeholder:text-muted-foreground/70 disabled:opacity-60"
              />
              <button
                type="submit"
                disabled={!input.trim() || streaming}
                aria-label={t("chat.send")}
                className="shrink-0 grid place-items-center w-9 h-9 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
              >
                <Send size={15} />
              </button>
            </div>
            <p className="px-3 pb-2 text-[0.65rem] leading-tight text-muted-foreground flex items-start gap-1.5">
              <ShieldAlert size={12} className="mt-0.5 shrink-0" />
              <span>
                {t("chat.gdpr")}{" "}
                <a href={lp("/privacy-policy")} className="underline hover:text-foreground">
                  {t("chat.privacyLink")}
                </a>
              </span>
            </p>
          </form>
          )}
        </motion.div>
      )}
    </AnimatePresence>
  );
}
