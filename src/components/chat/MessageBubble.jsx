import { memo, useMemo } from "react";
import { Link } from "react-router-dom";
import { track } from "@/lib/track";

// Render markdown lite (grassetto, citazioni inline) in modo sicuro: niente HTML
// utente, solo nodi React costruiti dal parser. Le citazioni [N](url) diventano
// link interni cliccabili che usano <Link> di react-router quando il path è interno.

const CITATION_RE = /\[(\d+)\]\((https?:\/\/[^\s)]+|\/[^\s)]*)\)/g;
const BOLD_RE = /\*\*([^*]+)\*\*/g;

const PILL_CLASSES =
  "inline-flex items-center align-baseline text-[0.7rem] font-semibold leading-none px-1.5 py-0.5 mx-0.5 rounded-full bg-primary/15 text-primary hover:bg-primary/25 transition-colors";

function CitationPill({ n, url, source }) {
  const onClick = () => track("chat_citation_clicked", { url, n, source });
  if (url.startsWith("/")) {
    return <Link to={url} onClick={onClick} className={PILL_CLASSES}>{n}</Link>;
  }
  return (
    <a href={url} target="_blank" rel="noopener noreferrer" onClick={onClick} className={PILL_CLASSES}>{n}</a>
  );
}

function renderBold(text, keyPrefix) {
  const nodes = [];
  let lastIdx = 0;
  let i = 0;
  BOLD_RE.lastIndex = 0;
  let m;
  while ((m = BOLD_RE.exec(text)) !== null) {
    if (m.index > lastIdx) nodes.push(text.slice(lastIdx, m.index));
    nodes.push(<strong key={`${keyPrefix}b${i++}`} className="font-semibold">{m[1]}</strong>);
    lastIdx = m.index + m[0].length;
  }
  if (lastIdx < text.length) nodes.push(text.slice(lastIdx));
  return nodes;
}

function renderInline(text, keyPrefix = "") {
  const nodes = [];
  let lastIdx = 0;
  let i = 0;
  CITATION_RE.lastIndex = 0;
  let m;
  while ((m = CITATION_RE.exec(text)) !== null) {
    if (m.index > lastIdx) nodes.push(...renderBold(text.slice(lastIdx, m.index), `${keyPrefix}t${i++}`));
    nodes.push(<CitationPill key={`${keyPrefix}c${i++}`} n={m[1]} url={m[2]} source="inline" />);
    lastIdx = m.index + m[0].length;
  }
  if (lastIdx < text.length) nodes.push(...renderBold(text.slice(lastIdx), `${keyPrefix}t${i++}`));
  return nodes;
}

function renderBlocks(content) {
  return content.split(/\n{2,}/).map((p, idx) => (
    <p key={idx} className="whitespace-pre-wrap leading-relaxed [&:not(:last-child)]:mb-2">
      {renderInline(p, `p${idx}-`)}
    </p>
  ));
}

const BUBBLE_BASE = "max-w-[85%] rounded-2xl px-3.5 py-2.5 text-sm";
const BUBBLE_USER = "bg-primary text-primary-foreground rounded-br-sm";
const BUBBLE_ERROR = "bg-destructive/10 text-destructive border border-destructive/30 rounded-bl-sm";
const BUBBLE_ASSISTANT = "bg-card text-card-foreground border border-border/60 rounded-bl-sm";

function bubbleClass(isUser, error) {
  if (isUser) return `${BUBBLE_BASE} ${BUBBLE_USER}`;
  if (error) return `${BUBBLE_BASE} ${BUBBLE_ERROR}`;
  return `${BUBBLE_BASE} ${BUBBLE_ASSISTANT}`;
}

function MessageBubble({ role, content, sources, error, streaming }) {
  const isUser = role === "user";
  const isEmpty = !content && streaming;
  // Riparsa solo quando content cambia: durante streaming evita re-allocazione dei nodi.
  const blocks = useMemo(() => (isEmpty ? null : renderBlocks(content)), [content, isEmpty]);

  // Mostra in fondo SOLO le fonti effettivamente citate inline nella risposta.
  // Se il modello non ha usato il contesto del sito, non compare alcun riferimento.
  const shownSources = useMemo(() => {
    if (!sources?.length || !content) return [];
    const cited = new Set();
    CITATION_RE.lastIndex = 0;
    let m;
    while ((m = CITATION_RE.exec(content)) !== null) cited.add(m[1]);
    return sources.filter((s) => cited.has(String(s.n)));
  }, [sources, content]);

  return (
    <div className={`flex ${isUser ? "justify-end" : "justify-start"}`}>
      <div className={bubbleClass(isUser, error)}>
        {isEmpty ? (
          <span className="inline-flex gap-1 items-center text-muted-foreground">
            <span className="w-1.5 h-1.5 rounded-full bg-current opacity-70 animate-pulse" />
            <span className="w-1.5 h-1.5 rounded-full bg-current opacity-70 animate-pulse [animation-delay:120ms]" />
            <span className="w-1.5 h-1.5 rounded-full bg-current opacity-70 animate-pulse [animation-delay:240ms]" />
          </span>
        ) : (
          blocks
        )}
        {!isUser && shownSources.length > 0 && !streaming && !error && (
          <ul className="mt-2 pt-2 border-t border-border/50 space-y-1 text-[0.7rem] text-muted-foreground">
            {shownSources.map((s) => (
              <li key={s.n} className="flex gap-1.5">
                <span className="font-semibold text-primary">[{s.n}]</span>
                <Link
                  to={s.url}
                  onClick={() => track("chat_citation_clicked", { url: s.url, n: s.n, source: "list" })}
                  className="hover:underline truncate"
                >
                  {s.title}
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}

export default memo(MessageBubble);
