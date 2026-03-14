import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import SEO from '../components/SEO';

export default function ArticleDocumentIntelligenceDDT() {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap';
    document.head.appendChild(fontLink);

    fetch('/blog/document-intelligence-automazione-ddt-bolle-consegna.html')
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Remove elements replaced by the SPA layout
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
    "headline": "Document Intelligence: Addio all'Inserimento Manuale di DDT e Bolle",
    "description": "Come la Document Intelligence trasforma la gestione di DDT e documenti di trasporto: meno errori, zero digitazione manuale, flussi verificati in automatico.",
    "url": "https://finch-ai.it/blog/document-intelligence-automazione-ddt-bolle-consegna",
    "datePublished": "2026-03-04",
    "dateModified": "2026-03-04",
    "author": { "@type": "Organization", "name": "Finch-AI", "url": "https://finch-ai.it" },
    "publisher": { "@type": "Organization", "name": "Finch-AI", "logo": { "@type": "ImageObject", "url": "https://finch-ai.it/assets/images/LOGO.png" } },
    "image": "https://finch-ai.it/assets/images/og-image.png",
    "articleSection": "Automazione Documentale",
    "inLanguage": "it-IT",
    "keywords": "automazione DDT, document intelligence, OCR AI, gestione bolle consegna, inserimento manuale documenti, logistica PMI"
  };

  const seoProps = {
    title: "Automazione DDT con Document Intelligence AI | Blog Finch-AI",
    description: "Come Document Intelligence elimina l'inserimento manuale di DDT e bolle: OCR AI al 97%, zero errori, integrazione ERP automatica. Guida completa per PMI.",
    keywords: "automazione DDT, document intelligence logistica, gestione bolle consegna AI, OCR DDT automatico, eliminare inserimento manuale documenti, digitalizzazione documenti trasporto, integrazione ERP DDT",
    canonical: "https://finch-ai.it/blog/document-intelligence-automazione-ddt-bolle-consegna",
    ogType: "article",
    article: { publishedTime: "2026-03-04", author: "Finch-AI", section: "Automazione Documentale" },
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
                Automazione Documentale · AI
              </span>
              <h1 className="mb-5 text-3xl font-bold leading-tight text-foreground sm:text-4xl lg:text-[2.6rem]">
                Document Intelligence: addio all'inserimento manuale di DDT e bolle
              </h1>
              <p className="mx-auto mb-7 max-w-2xl text-lg text-muted-foreground">
                Ogni anno le aziende perdono migliaia di ore a digitare, correggere e riconciliare documenti di trasporto. Esiste un modo migliore — ed è già disponibile.
              </p>
              <p className="text-sm text-muted-foreground/60">
                4 Marzo 2026 &nbsp;·&nbsp; Finch-AI &nbsp;·&nbsp; ~8 min lettura
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
            html.dark .highlight-box, html.dark blockquote { background: linear-gradient(135deg, #0f1d2e 0%, #0a1a1a 100%) !important; color: #e2e8f0 !important; }
            html.dark .highlight-box p, html.dark blockquote p { color: #cbd5e1 !important; }
            html.dark .cta, html.dark .article-cta { background: linear-gradient(135deg, #0f1d2e 0%, #0a1a1a 100%) !important; color: #e2e8f0 !important; }
            html.dark .cta p, html.dark .article-cta p { color: #cbd5e1 !important; }
            html.dark .references, html.dark .references-section { border-top-color: #1e3a5f !important; }
            html.dark .references ol li, html.dark .references-section ol li { border-bottom-color: #1e3a5f !important; color: #94a3b8 !important; }
            html.dark .references .ref-title, html.dark .references-section .ref-title { color: #cbd5e1 !important; }
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
