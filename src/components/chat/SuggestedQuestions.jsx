import { useTranslation } from "react-i18next";
import { track } from "@/lib/track";

export default function SuggestedQuestions({ onPick }) {
  const { t } = useTranslation("common");
  const items = t("chat.suggested", { returnObjects: true });
  if (!Array.isArray(items) || items.length === 0) return null;

  return (
    <div className="flex flex-wrap gap-1.5 px-1">
      {items.map((q, i) => (
        <button
          key={i}
          type="button"
          onClick={() => {
            track("chat_suggestion_clicked", { index: i });
            onPick(q);
          }}
          className="text-xs px-2.5 py-1.5 rounded-full border border-border/70 bg-card/60 hover:bg-primary/10 hover:border-primary/40 text-foreground transition-colors text-left"
        >
          {q}
        </button>
      ))}
    </div>
  );
}
