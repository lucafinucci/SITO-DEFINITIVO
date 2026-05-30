import Layout from '../components/Layout';
import SEO from '../components/SEO';
import ArticleHero from '../components/ArticleHero';
import ArticleBody from '../components/ArticleBody';

export default function ArticleSupportoDecisionaleSynapse() {
  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://finch-ai.it/blog/sistema-supporto-decisionale-ai-pmi-synapse"
    },
    "headline": "Il Sistema di Supporto Decisionale che costruisce la tua memoria aziendale",
    "description": "Come un DSS evoluto basato su AI trasforma documenti sparsi, email e contratti in un database vivo della tua PMI, con risposte verificabili e human-in-the-loop.",
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
    "datePublished": "2026-05-18",
    "dateModified": "2026-05-18",
    "articleSection": "Thought Leadership",
    "inLanguage": "it-IT",
    "keywords": "Sistema di Supporto Decisionale, DSS PMI, AI per imprese, knowledge graph aziendale, document AI, Synapse FinCh-Ai, automazione documentale, decisioni data-driven PMI"
  };

  const breadcrumbsJsonLd = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://finch-ai.it"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Blog",
        "item": "https://finch-ai.it/blog"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "Il Sistema di Supporto Decisionale che costruisce la tua memoria aziendale",
        "item": "https://finch-ai.it/blog/sistema-supporto-decisionale-ai-pmi-synapse"
      }
    ]
  };

  const seoProps = {
    title: "Il Sistema di Supporto Decisionale (DSS) per la memoria aziendale | FinCh-Ai",
    description: "Come un DSS evoluto basato su AI trasforma documenti sparsi, email e contratti in un database vivo della tua PMI, con risposte verificabili.",
    keywords: "Sistema di Supporto Decisionale, DSS PMI, AI per imprese, knowledge graph aziendale, document AI, Synapse FinCh-Ai, automazione documentale, decisioni data-driven PMI",
    canonical: "https://finch-ai.it/blog/sistema-supporto-decisionale-ai-pmi-synapse",
    ogType: "article",
    ogImageAlt: "FinCh-Ai Blog - Il Sistema di Supporto Decisionale che costruisce la tua memoria aziendale",
    article: { 
      publishedTime: "2026-05-18", 
      modifiedTime: "2026-05-18", 
      author: "FinCh-Ai", 
      section: "Thought Leadership" 
    },
    jsonLd: [articleJsonLd, breadcrumbsJsonLd],
  };

  return (
    <Layout>
      <SEO {...seoProps} />

      <ArticleHero
        category="Thought Leadership"
        title="Il Sistema di Supporto Decisionale che costruisce la tua memoria aziendale"
        description="Come un DSS evoluto basato su AI trasforma documenti sparsi, email e contratti in un database vivo della tua PMI, con risposte verificabili e human-in-the-loop."
        meta="18 Maggio 2026 · FinCh-Ai · ~12 min lettura"
      />
      <ArticleBody slug="sistema-supporto-decisionale-ai-pmi-synapse" />
    </Layout>
  );
}
