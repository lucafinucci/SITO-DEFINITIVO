import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import SEO from '../components/SEO';

export default function ArticleAIAnalisiDati() {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap';
    document.head.appendChild(fontLink);

    fetch('/blog/ai-analisi-dati-pmi-excel-access.html')
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
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://finch-ai.it/blog/ai-analisi-dati-pmi-excel-access"
    },
    "headline": "AI e Analisi Dati: Non Servono Big Data in Tempo Reale per le PMI",
    "description": "Scopri come l'intelligenza artificiale rivoluziona l'analisi dati nelle PMI italiane: bilanci, costi, vendite e previsioni di domanda accessibili anche con Excel e Access.",
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
    "datePublished": "2026-03-12",
    "dateModified": "2026-03-12",
    "articleSection": "Thought Leadership",
    "inLanguage": "it-IT",
    "keywords": "AI PMI, intelligenza artificiale Excel, analisi dati aziendali, demand forecasting PMI, monitoraggio costi AI, efficienza produttiva, bilancio AI, consulenza AI PMI"
  };

  const seoProps = {
    title: "AI e Analisi Dati per PMI: Oltre i Big Data | Finch-AI",
    description: "L'intelligenza artificiale per analizzare bilanci, costi e previsioni è accessibile anche alle PMI che usano Excel. Scopri perché non servono dati in tempo reale.",
    keywords: "AI PMI, intelligenza artificiale Excel, analisi dati aziendali, demand forecasting PMI, monitoraggio costi AI, efficienza produttiva, bilancio AI, consulenza AI PMI",
    canonical: "https://finch-ai.it/blog/ai-analisi-dati-pmi-excel-access",
    ogType: "article",
    article: { publishedTime: "2026-03-12", author: "FinCh-Ai", section: "Thought Leadership" },
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
                Thought Leadership
              </span>
              <h1 className="mb-5 text-3xl font-bold leading-tight text-foreground sm:text-4xl lg:text-[2.6rem]">
                AI e Analisi Dati: Non Servono Big Data in Tempo Reale per le PMI
              </h1>
              <p className="mx-auto mb-7 max-w-2xl text-lg text-muted-foreground">
                L'intelligenza artificiale per analizzare bilanci, costi, vendite e previsioni è accessibile anche alle aziende che lavorano con Excel e Access.
              </p>
              <p className="text-sm text-muted-foreground/60">
                12 Marzo 2026 &nbsp;·&nbsp; FinCh-Ai &nbsp;·&nbsp; ~10 min lettura
              </p>
            </div>
          </div>

          {/* Article Content with grey background wrapper consistent with other articles */}
          <div className="bg-[#F4F8FA] dark:bg-[#0b1220] pb-12">
            <style>{`
              ${articleStyles}
              /* Match the reference article's layout overrides */
              article { 
                margin-top: 2rem !important; 
              }
              /* Dark mode overrides consistent with the reference article styles */
              .dark article {
                background: #111f30 !important;
                box-shadow: 0 4px 30px rgba(0,0,0,0.3) !important;
                color: #e2e8f0 !important;
              }
              .dark .hero__meta, .dark .hero__subtitle {
                color: #94a3b8 !important;
              }
            `}</style>
            <div dangerouslySetInnerHTML={{ __html: bodyHtml }} />
          </div>
        </>
      )}
    </Layout>
  );
}
