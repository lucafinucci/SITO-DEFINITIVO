import { useEffect, useState } from 'react';
import Navbar from '../components/Navbar';
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

        const nav = doc.querySelector('nav.navbar');
        if (nav) nav.remove();

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
      if (document.head.contains(fontLink)) {
        document.head.removeChild(fontLink);
      }
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

  if (loading) {
    return (
      <>
        <SEO
          title="Automazione DDT con Document Intelligence AI | Blog Finch-AI"
          description="Come Document Intelligence elimina l'inserimento manuale di DDT e bolle: OCR AI al 97%, zero errori, integrazione ERP automatica. Guida completa per PMI."
          keywords="automazione DDT, document intelligence logistica, gestione bolle consegna AI, OCR DDT automatico, eliminare inserimento manuale documenti, digitalizzazione documenti trasporto, integrazione ERP DDT"
          canonical="https://finch-ai.it/blog/document-intelligence-automazione-ddt-bolle-consegna"
          ogType="article"
          article={{ publishedTime: "2026-03-04", author: "Finch-AI", section: "Automazione Documentale" }}
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
        title="Automazione DDT con Document Intelligence AI | Blog Finch-AI"
        description="Come Document Intelligence elimina l'inserimento manuale di DDT e bolle: OCR AI al 97%, zero errori, integrazione ERP automatica. Guida completa per PMI."
        keywords="automazione DDT, document intelligence logistica, gestione bolle consegna AI, OCR DDT automatico, eliminare inserimento manuale documenti, digitalizzazione documenti trasporto, integrazione ERP DDT"
        canonical="https://finch-ai.it/blog/document-intelligence-automazione-ddt-bolle-consegna"
        ogType="article"
        article={{ publishedTime: "2026-03-04", author: "Finch-AI", section: "Automazione Documentale" }}
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
