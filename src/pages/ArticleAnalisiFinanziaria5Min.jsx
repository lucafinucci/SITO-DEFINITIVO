import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import SEO from '../components/SEO';

export default function ArticleAnalisiFinanziaria5Min() {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap';
    document.head.appendChild(fontLink);

    fetch('/blog/analisi-finanziaria-5-minuti-pmi-finch-ai.html')
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        doc.querySelector('nav.navbar')?.remove();
        doc.querySelector('header.hero')?.remove();
        doc.querySelector('footer.site-footer')?.remove();

        const rawStyles = Array.from(doc.querySelectorAll('style'))
          .map(s => s.textContent)
          .join('\n');

        const filtered = rawStyles
          .replace(/\bhtml\s*\{[^}]*\}/g, '')
          .replace(/\bbody\s*\{[^}]*\}/g, '');

        setArticleStyles(filtered);
        setBodyHtml(doc.body.innerHTML);
        setLoading(false);
      });

    return () => {
      if (document.head.contains(fontLink)) document.head.removeChild(fontLink);
    };
  }, []);

  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://finch-ai.it/blog/analisi-finanziaria-5-minuti-pmi-finch-ai"
    },
    "headline": "Analisi finanziaria in 5 minuti: cosa può fare una PMI con FinCh-Ai oggi",
    "description": "Scopri come l'AI trasforma l'analisi finanziaria delle PMI italiane: dai numeri grezzi di ERP e bilanci a insight strategici in pochi minuti. Demo e casi concreti con FinCh-Ai.",
    "image": "https://finch-ai.it/assets/images/og-image.png",
    "author": {
      "@type": "Organization",
      "name": "FinCh-Ai",
      "url": "https://finch-ai.it"
    },
    "publisher": {
      "@type": "Organization",
      "name": "FinCh-Ai",
      "logo": {
        "@type": "ImageObject",
        "url": "https://finch-ai.it/assets/images/LOGO.png"
      }
    },
    "datePublished": "2026-03-13",
    "dateModified": "2026-03-13",
    "articleSection": "Business Insight",
    "inLanguage": "it-IT",
    "keywords": "analisi finanziaria PMI, AI analisi bilancio, FinCh-Ai, business intelligence PMI, insight finanziari automatici, controllo di gestione AI"
  };

  const seoProps = {
    title: "Analisi Finanziaria in 5 Minuti per PMI con AI | Finch-AI",
    description: "Scopri come l'AI trasforma l'analisi finanziaria delle PMI italiane: dai numeri grezzi di ERP e bilanci a insight strategici in pochi minuti con FinCh-Ai.",
    keywords: "analisi finanziaria PMI, AI analisi bilancio, FinCh-Ai, business intelligence PMI, insight finanziari automatici, controllo di gestione AI",
    canonical: "https://finch-ai.it/blog/analisi-finanziaria-5-minuti-pmi-finch-ai",
    ogType: "article",
    article: { publishedTime: "2026-03-13", author: "FinCh-Ai", section: "Business Insight" },
    jsonLd: [articleJsonLd],
  };

  return (
    <Layout>
      <SEO {...seoProps} />

      {loading ? (
        <div className="min-h-[60vh] flex items-center justify-center">
          <div className="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
        </div>
      ) : (
        <>
          {/* Article Hero */}
          <div className="relative overflow-hidden border-b border-border/40">
            <div className="pointer-events-none absolute inset-0 bg-gradient-to-br from-teal-500/10 via-transparent to-emerald-500/5" />
            <div className="relative mx-auto max-w-3xl px-4 py-14 sm:py-20 text-center">
              <span className="mb-5 inline-block rounded-full border border-teal-500/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-teal-500 dark:text-teal-400">
                Business Insight
              </span>
              <h1 className="mb-5 text-3xl font-bold leading-tight text-foreground sm:text-4xl lg:text-[2.6rem]">
                Analisi finanziaria in 5 minuti: cosa può fare una PMI con FinCh-Ai oggi
              </h1>
              <p className="mx-auto mb-7 max-w-2xl text-lg text-muted-foreground">
                Dai numeri grezzi di ERP e bilanci a insight strategici in pochi minuti. Scopri come l'AI trasforma l'analisi finanziaria delle PMI italiane.
              </p>
              <p className="text-sm text-muted-foreground/60">
                13 marzo 2026 &nbsp;·&nbsp; FinCh-Ai &nbsp;·&nbsp; ~7 min lettura
              </p>
            </div>
          </div>

          {/* Article Content */}
          <style>{`
            ${articleStyles}
            article { max-width: 780px !important; margin: 2rem auto 3rem !important; padding: 2.8rem 2.8rem 3.2rem !important; background: #FFFFFF !important; border-radius: 12px !important; box-shadow: 0 4px 30px rgba(4,142,227,0.08) !important; position: relative !important; z-index: 1 !important; }
            @media (max-width: 640px) { article { padding: 1.5rem 1.2rem 2rem !important; margin: 1rem 0.8rem 2rem !important; } }
            html.dark article { background: #111f30 !important; box-shadow: 0 4px 30px rgba(0,0,0,0.3) !important; color: #e2e8f0 !important; }
            html.dark p { color: #cbd5e1 !important; }
            html.dark h2 { color: #e2e8f0 !important; }
            html.dark h3 { color: #67e8f9 !important; }
            html.dark .toc { background: linear-gradient(135deg, #0f1d2e 0%, #0a1a1a 100%) !important; }
            html.dark .toc a, html.dark .toc ol li a { color: #cbd5e1 !important; }
            html.dark .toc a:hover, html.dark .toc ol li a:hover { color: #67e8f9 !important; }
            html.dark .info-box, html.dark blockquote { background: linear-gradient(135deg, #0f1d2e 0%, #0a1a1a 100%) !important; color: #e2e8f0 !important; }
            html.dark .info-box p, html.dark blockquote p { color: #cbd5e1 !important; }
            html.dark .info-box-title { color: #67e8f9 !important; }
            html.dark .cta { background: linear-gradient(135deg, #0f1d2e 0%, #0a1a1a 100%) !important; color: #e2e8f0 !important; }
            html.dark .cta p { color: #cbd5e1 !important; }
            html.dark .references-section { border-top-color: #1e3a5f !important; }
            html.dark .references-list li { border-bottom-color: #1e3a5f !important; color: #94a3b8 !important; }
            html.dark ul li::before { background: linear-gradient(135deg, #0284c7 0%, #22d3ee 100%) !important; }
            html.dark ul li strong, html.dark ol.steps li strong { color: #67e8f9 !important; }
            html.dark strong { color: #67e8f9 !important; }
          `}</style>
          <div dangerouslySetInnerHTML={{ __html: bodyHtml }} />
        </>
      )}
    </Layout>
  );
}
