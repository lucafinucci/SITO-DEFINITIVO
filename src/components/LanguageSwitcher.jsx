import { useLocation, useNavigate } from "react-router-dom";
import { useLocale, switchLangPath } from "@/i18n/routing";

// Compact IT / EN toggle. Navigates to the same page in the target language
// by adding/removing the `/en` path prefix.
export default function LanguageSwitcher() {
  const locale = useLocale();
  const location = useLocation();
  const navigate = useNavigate();

  const go = (lang) => {
    if (lang === locale) return;
    navigate(switchLangPath(location.pathname, lang) + location.search + location.hash);
    window.scrollTo(0, 0);
  };

  return (
    <div
      className="inline-flex items-center rounded-lg border border-border bg-muted/40 p-0.5 text-[13px] font-semibold"
      role="group"
      aria-label="Language"
    >
      {["it", "en"].map((lang) => (
        <button
          key={lang}
          type="button"
          onClick={() => go(lang)}
          aria-pressed={locale === lang}
          className={`rounded-md px-2 py-1 uppercase transition-colors ${
            locale === lang
              ? "bg-primary text-primary-foreground"
              : "text-muted-foreground hover:text-foreground"
          }`}
        >
          {lang}
        </button>
      ))}
    </div>
  );
}
