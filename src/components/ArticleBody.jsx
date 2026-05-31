import { useEffect, useState } from 'react';
import { useLocale } from '@/i18n/routing';
import '../styles/article.css';

/* Carica il corpo statico di un articolo da public/blog/<slug>.html
   (o public/blog/en/<slug>.html in inglese, con fallback all'italiano),
   rimuove nav/hero/footer originali, filtra le regole html/body e inietta
   gli stili dell'articolo. Il tema editoriale (font + palette) è applicato
   da article.css che ri-mappa i token dell'articolo. */
export default function ArticleBody({ slug }) {
  const locale = useLocale();
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    // In English, try /blog/en/<slug>.html first; fall back to the Italian
    // body if the translated file isn't available yet.
    const fetchBody = async () => {
      if (locale === 'en') {
        const res = await fetch(`/blog/en/${slug}.html`);
        if (res.ok) {
          const text = await res.text();
          // A SPA 404 returns index.html; detect it and fall back.
          if (!/<!doctype html>\s*<html[^>]*>\s*<head>[\s\S]*id="root"/i.test(text)) {
            return text;
          }
        }
      }
      return (await fetch(`/blog/${slug}.html`)).text();
    };
    fetchBody()
      .then(html => {
        if (cancelled) return;
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Rimuove gli elementi sostituiti dal layout SPA (le varianti
        // di markup differiscono tra i vari articoli statici).
        [
          'nav.navbar',
          'header.hero',
          'header.site-header',
          'section.article-hero',
          'footer.site-footer',
        ].forEach((sel) => doc.querySelector(sel)?.remove());

        const filtered = Array.from(doc.querySelectorAll('style'))
          .map(s => s.textContent)
          .join('\n')
          .replace(/\bhtml\s*\{[^}]*\}/g, '')
          .replace(/\bbody\s*\{[^}]*\}/g, '');

        setArticleStyles(filtered);
        setBodyHtml(doc.body.innerHTML);
        setLoading(false);
      });

    return () => { cancelled = true; };
  }, [slug, locale]);

  if (loading) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <div className="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
      </div>
    );
  }

  return (
    <>
      <style>{articleStyles}</style>
      <div className="article-body" dangerouslySetInnerHTML={{ __html: bodyHtml }} />
    </>
  );
}
