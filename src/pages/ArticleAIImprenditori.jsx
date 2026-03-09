import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import SEO from '../components/SEO';

export default function ArticleAIImprenditori() {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap';
    document.head.appendChild(fontLink);

    fetch('/blog/intelligenza-artificiale-imprenditori-commercialisti.html')
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
    "headline": "L'Intelligenza Artificiale al Servizio di Imprenditori e Commercialisti",
    "description": "Come un sistema AI può aiutare imprenditori e commercialisti ad analizzare le performance aziendali in modo più rapido, accurato e strategico.",
    "url": "https://finch-ai.it/blog/intelligenza-artificiale-imprenditori-commercialisti",
    "datePublished": "2026-02-28",
    "dateModified": "2026-02-28",
    "author": { "@type": "Organization", "name": "Finch-AI", "url": "https://finch-ai.it" },
    "publisher": { "@type": "Organization", "name": "Finch-AI", "logo": { "@type": "ImageObject", "url": "https://finch-ai.it/assets/images/LOGO.png" } },
    "image": "https://finch-ai.it/assets/images/og-image.png",
    "articleSection": "Intelligenza Artificiale",
    "inLanguage": "it-IT",
    "keywords": "intelligenza artificiale imprenditori, AI commercialisti, analisi performance aziendale, controllo di gestione AI, PMI"
  };

  const seoProps = {
    title: "AI per Imprenditori e Commercialisti: Analisi Aziendale con Intelligenza Artificiale",
    description: "Come l'intelligenza artificiale aiuta imprenditori e commercialisti ad analizzare le performance aziendali in modo più rapido, accurato e strategico con Finch-AI.",
    keywords: "intelligenza artificiale imprenditori, AI per commercialisti, analisi performance aziendale AI, controllo di gestione AI, business intelligence PMI, KPI automatici AI, digital transformation PMI Italia",
    canonical: "https://finch-ai.it/blog/intelligenza-artificiale-imprenditori-commercialisti",
    ogType: "article",
    article: { publishedTime: "2026-02-28", author: "Finch-AI", section: "Intelligenza Artificiale" },
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
                L'Intelligenza Artificiale al Servizio di Imprenditori e Commercialisti
              </h1>
              <p className="mx-auto mb-7 max-w-2xl text-lg text-muted-foreground">
                Come l'AI trasforma l'analisi delle performance aziendali: più velocità, più precisione, migliori decisioni.
              </p>
              <p className="text-sm text-muted-foreground/60">
                28 febbraio 2026 &nbsp;·&nbsp; Finch-AI &nbsp;·&nbsp; ~7 min lettura
              </p>
            </div>
          </div>

          {/* Article Content */}
          <style>{`
            ${articleStyles}
            /* neutralize the negative overlap margin designed for the old hero */
            article { margin-top: 2rem !important; }
          `}</style>
          <div dangerouslySetInnerHTML={{ __html: bodyHtml }} />
        </>
      )}
    </Layout>
  );
}
