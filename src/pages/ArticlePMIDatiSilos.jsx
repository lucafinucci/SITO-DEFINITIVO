import Layout from '../components/Layout';
import SEO from '../components/SEO';
import ArticleHero from '../components/ArticleHero';
import ArticleBody from '../components/ArticleBody';

export default function ArticlePMIDatiSilos() {
  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://finch-ai.it/blog/pmi-problema-dati-silos-frammentati"
    },
    "headline": "Perché molte PMI hanno già un problema di dati, anche se non se ne accorgono | FinCh-Ai Blog",
    "description": "Excel, email, PDF, ERP: i dati ci sono, ma nessuno li vede davvero. Come i silos informativi frenano le decisioni e quanto costa ignorarli.",
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
    "datePublished": "2026-03-21",
    "dateModified": "2026-03-21",
    "articleSection": "Business Insight",
    "inLanguage": "it-IT",
    "keywords": "silos dati PMI, dati frammentati azienda, Excel ERP integrazione, digitalizzazione PMI, business intelligence, decisioni data-driven, FinCh-AI"
  };

  const seoProps = {
    title: "Perché molte PMI hanno già un problema di dati, anche se non se ne accorgono | FinCh-Ai",
    description: "Excel, email, PDF, ERP: i dati ci sono, ma nessuno li vede davvero. Come i silos informativi frenano le decisioni e quanto costa ignorarli.",
    keywords: "silos dati PMI, dati frammentati azienda, Excel ERP integrazione, digitalizzazione PMI, business intelligence, decisioni data-driven, FinCh-AI",
    canonical: "https://finch-ai.it/blog/pmi-problema-dati-silos-frammentati",
    ogType: "article",
    article: { publishedTime: "2026-03-21", author: "FinCh-Ai", section: "Business Insight" },
    jsonLd: [articleJsonLd],
  };

  return (
    <Layout>
      <SEO {...seoProps} />

      <ArticleHero
        category="Business Insight"
        title="Perché molte PMI hanno già un problema di dati, anche se non se ne accorgono"
        description="Excel, email, PDF, ERP: i dati ci sono, ma nessuno li vede davvero. Come i silos informativi frenano le decisioni e quanto costa ignorarli."
        meta="21 marzo 2026 · FinCh-Ai · ~8 min lettura"
      />
      <ArticleBody slug="pmi-problema-dati-silos-frammentati" />
    </Layout>
  );
}
