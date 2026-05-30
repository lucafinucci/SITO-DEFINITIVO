import { useEffect, useState } from 'react';
import '../styles/article.css';

/* Carica il corpo statico di un articolo da public/blog/<slug>.html,
   rimuove nav/hero/footer originali, filtra le regole html/body e inietta
   gli stili dell'articolo. Il tema editoriale (font + palette) è applicato
   da article.css che ri-mappa i token dell'articolo. */
export default function ArticleBody({ slug }) {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    fetch(`/blog/${slug}.html`)
      .then(r => r.text())
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
  }, [slug]);

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
      <div dangerouslySetInnerHTML={{ __html: bodyHtml }} />
    </>
  );
}
