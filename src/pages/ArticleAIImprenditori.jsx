import { useEffect, useState } from 'react';
import Navbar from '../components/Navbar';
import SEO from '../components/SEO';

export default function ArticleAIImprenditori() {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Load Google Fonts needed by the article
    const fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap';
    document.head.appendChild(fontLink);

    fetch('/blog/intelligenza-artificiale-imprenditori-commercialisti.html')
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Remove article's own navbar (replaced by SPA navbar)
        const nav = doc.querySelector('nav.navbar');
        if (nav) nav.remove();

        // Extract styles from <head>
        const rawStyles = Array.from(doc.querySelectorAll('style'))
          .map(s => s.textContent)
          .join('\n');

        // Remove html{} and body{} rules to avoid conflicts with SPA globals
        const filtered = rawStyles
          .replace(/\bhtml\s*\{[^}]*\}/g, '')
          .replace(/\bbody\s*\{[^}]*\}/g, '');

        setArticleStyles(filtered);
        setBodyHtml(doc.body.innerHTML);
        setLoading(false);
      });

    return () => {
      if (document.head.contains(fontLink)) {
        document.head.removeChild(fontLink);
      }
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

  if (loading) {
    return (
      <>
        <SEO
          title="AI per Imprenditori e Commercialisti: Analisi Aziendale con Intelligenza Artificiale"
          description="Come l'intelligenza artificiale aiuta imprenditori e commercialisti ad analizzare le performance aziendali in modo più rapido, accurato e strategico con Finch-AI."
          keywords="intelligenza artificiale imprenditori, AI per commercialisti, analisi performance aziendale AI, controllo di gestione AI, business intelligence PMI, KPI automatici AI, digital transformation PMI Italia"
          canonical="https://finch-ai.it/blog/intelligenza-artificiale-imprenditori-commercialisti"
          ogType="article"
          article={{ publishedTime: "2026-02-28", author: "Finch-AI", section: "Intelligenza Artificiale" }}
          jsonLd={[articleJsonLd]}
        />
        <div className="min-h-screen flex items-center justify-center">
          <div className="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
        </div>
      </>
    );
  }

  return (
    <>
      <SEO
        title="AI per Imprenditori e Commercialisti: Analisi Aziendale con Intelligenza Artificiale"
        description="Come l'intelligenza artificiale aiuta imprenditori e commercialisti ad analizzare le performance aziendali in modo più rapido, accurato e strategico con Finch-AI."
        keywords="intelligenza artificiale imprenditori, AI per commercialisti, analisi performance aziendale AI, controllo di gestione AI, business intelligence PMI, KPI automatici AI, digital transformation PMI Italia"
        canonical="https://finch-ai.it/blog/intelligenza-artificiale-imprenditori-commercialisti"
        ogType="article"
        article={{ publishedTime: "2026-02-28", author: "Finch-AI", section: "Intelligenza Artificiale" }}
        jsonLd={[articleJsonLd]}
      />
      <Navbar />
      <style>{articleStyles}</style>
      <div className="pt-28 sm:pt-32 lg:pt-36">
        <div dangerouslySetInnerHTML={{ __html: bodyHtml }} />
      </div>
    </>
  );
}
