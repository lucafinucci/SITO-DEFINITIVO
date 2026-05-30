import Layout from '../components/Layout';
import SEO from '../components/SEO';
import ArticleHero from '../components/ArticleHero';
import ArticleBody from '../components/ArticleBody';

export default function ArticleDocumentIntelligenceDDT() {
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

      <ArticleHero
        category="Automazione Documentale · AI"
        title="Document Intelligence: addio all'inserimento manuale di DDT e bolle"
        description="Ogni anno le aziende perdono migliaia di ore a digitare, correggere e riconciliare documenti di trasporto. Esiste un modo migliore — ed è già disponibile."
        meta="4 Marzo 2026 · Finch-AI · ~8 min lettura"
      />
      <ArticleBody slug="document-intelligence-automazione-ddt-bolle-consegna" />
    </Layout>
  );
}
