import { useTranslation } from 'react-i18next';
import { useCallback } from 'react';

// Returns the active locale, normalized to a supported value.
export function useLocale() {
  const { i18n } = useTranslation();
  return i18n.language === 'en' ? 'en' : 'it';
}

// Hook that returns a function to prefix an absolute app path with the
// active language. Italian (default) keeps the bare path; English gets `/en`.
// External URLs, hashes and mailto/tel links are returned untouched.
export function useLocalizedPath() {
  const locale = useLocale();
  return useCallback(
    (path) => {
      if (typeof path !== 'string' || !path.startsWith('/')) return path;
      if (locale !== 'en') return path;
      return path === '/' ? '/en' : `/en${path}`;
    },
    [locale]
  );
}

// Build the equivalent of the current pathname in the target language.
export function switchLangPath(pathname, targetLang) {
  const stripped = pathname.replace(/^\/en(?=\/|$)/, '') || '/';
  if (targetLang === 'en') {
    return stripped === '/' ? '/en' : `/en${stripped}`;
  }
  return stripped;
}
