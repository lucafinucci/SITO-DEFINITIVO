import { useRef, useState } from "react";
import { useLocation } from "react-router-dom";
import { MessageCircle } from "lucide-react";
import { useTranslation } from "react-i18next";
import { useLocale } from "@/i18n/routing";
import { track } from "@/lib/track";
import { useChatStream } from "./useChatStream";
import ChatPanel from "./ChatPanel";

const STORAGE_KEY = "finch-chat-session";

function loadHistory() {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : null;
    return Array.isArray(parsed) ? parsed : [];
  } catch { return []; }
}

function saveHistory(messages) {
  try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(messages)); } catch { /* quota */ }
}

const isAreaClienti = (path) =>
  path.startsWith("/area-clienti") || path.startsWith("/en/area-clienti");

export default function ChatWidget() {
  const { t } = useTranslation("common");
  const lang = useLocale();
  const location = useLocation();
  const [open, setOpen] = useState(false);
  // Read-once dal sessionStorage; lo stato successivo vive nell'hook.
  const [initial] = useState(loadHistory);

  const chat = useChatStream({
    lang,
    initialMessages: initial,
    onError: (msg) => { track("chat_error", { msg }); saveHistory(messagesRef.current); },
    onComplete: () => { track("chat_message_completed"); saveHistory(messagesRef.current); },
  });

  // Aggiorna il ref nel render senza side effect; gli onComplete/onError vi leggono.
  const messagesRef = useRef(chat.messages);
  messagesRef.current = chat.messages;

  if (isAreaClienti(location.pathname)) return null;

  const handleToggle = () => {
    const next = !open;
    setOpen(next);
    track(next ? "chat_open" : "chat_close");
  };

  const handleClose = () => { setOpen(false); track("chat_close"); };

  return (
    <>
      <button
        type="button"
        onClick={handleToggle}
        aria-label={open ? t("chat.close") : t("chat.openAria")}
        aria-expanded={open}
        className={[
          "fixed z-[55] bottom-5 right-5 sm:bottom-6 sm:right-6",
          "h-14 w-14 rounded-full grid place-items-center",
          "bg-primary text-primary-foreground shadow-lg shadow-primary/30",
          "hover:scale-105 active:scale-95 transition-transform",
          "ring-1 ring-primary/30",
          open ? "opacity-0 pointer-events-none sm:opacity-100 sm:pointer-events-auto" : "opacity-100",
        ].join(" ")}
      >
        <MessageCircle size={22} />
      </button>

      <ChatPanel open={open} onClose={handleClose} chat={chat} />
    </>
  );
}
