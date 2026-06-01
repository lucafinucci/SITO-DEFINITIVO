import { useCallback, useRef, useState } from "react";

const DEFAULT_ENDPOINT = "/area-clienti/api/chat/chat.php";
const endpoint = () => import.meta.env.VITE_CHAT_ENDPOINT || DEFAULT_ENDPOINT;

// Parser SSE stream-friendly: scorre il buffer riga per riga e ricompone
// (event, data) emettendo via onEvent ad ogni blocco vuoto.
function makeSseParser(onEvent) {
  let buffer = "";
  let event = "message";
  let data = "";
  return (chunk) => {
    buffer += chunk;
    let idx;
    while ((idx = buffer.indexOf("\n")) !== -1) {
      const rawLine = buffer.slice(0, idx);
      buffer = buffer.slice(idx + 1);
      const line = rawLine.endsWith("\r") ? rawLine.slice(0, -1) : rawLine;
      if (line === "") {
        if (data) {
          try { onEvent(event, JSON.parse(data)); } catch { /* ignora payload non-JSON */ }
        }
        event = "message";
        data = "";
        continue;
      }
      if (line.startsWith("event:")) event = line.slice(6).trim();
      else if (line.startsWith("data:")) data += line.slice(5).trimStart();
    }
  };
}

export function useChatStream({ lang = "it", initialMessages = [], onError, onComplete } = {}) {
  const [messages, setMessages] = useState(initialMessages);
  const [streaming, setStreaming] = useState(false);
  const [error, setError] = useState(null);
  const abortRef = useRef(null);

  // Aggiorna l'ultimo messaggio assistant. Tutti i delta/sources passano da qui.
  const patchLastAssistant = useCallback((patcher) => {
    setMessages((prev) => {
      const last = prev[prev.length - 1];
      if (last?.role !== "assistant") return prev;
      const next = prev.slice();
      next[next.length - 1] = patcher(last);
      return next;
    });
  }, []);

  const reset = useCallback(() => {
    abortRef.current?.abort();
    abortRef.current = null;
    setMessages([]);
    setStreaming(false);
    setError(null);
  }, []);

  const abort = useCallback(() => {
    abortRef.current?.abort();
    abortRef.current = null;
    setStreaming(false);
  }, []);

  const sendMessage = useCallback(
    async (text) => {
      const trimmed = String(text || "").trim();
      if (!trimmed || streaming) return;

      setError(null);
      const history = messages.map(({ role, content }) => ({ role, content }));
      const turns = [
        ...messages,
        { role: "user", content: trimmed },
        { role: "assistant", content: "", sources: [] },
      ];
      setMessages(turns);
      setStreaming(true);

      const controller = new AbortController();
      abortRef.current = controller;

      try {
        const res = await fetch(endpoint(), {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "text/event-stream" },
          body: JSON.stringify({ message: trimmed, history, lang }),
          signal: controller.signal,
        });

        if (!res.ok) {
          let msg = `HTTP ${res.status}`;
          try {
            const j = await res.json();
            if (j?.error) msg = j.error;
          } catch { /* ignore */ }
          throw new Error(msg);
        }

        const reader = res.body.getReader();
        const decoder = new TextDecoder("utf-8");
        const parse = makeSseParser((event, payload) => {
          if (event === "sources" && Array.isArray(payload)) {
            patchLastAssistant((last) => ({ ...last, sources: payload }));
          } else if (event === "delta" && payload?.text) {
            patchLastAssistant((last) => ({ ...last, content: last.content + payload.text }));
          } else if (event === "error") {
            throw new Error(payload?.message || "stream error");
          }
        });

        // eslint-disable-next-line no-constant-condition
        while (true) {
          const { value, done } = await reader.read();
          if (done) break;
          parse(decoder.decode(value, { stream: true }));
        }
        onComplete?.();
      } catch (e) {
        if (e.name === "AbortError") return;
        const msg = e?.message || "Errore di rete";
        setError(msg);
        patchLastAssistant((last) => last.content ? last : { ...last, content: msg, error: true });
        onError?.(msg);
      } finally {
        setStreaming(false);
        abortRef.current = null;
      }
    },
    [lang, messages, streaming, onComplete, onError, patchLastAssistant]
  );

  return { messages, streaming, error, sendMessage, abort, reset };
}
