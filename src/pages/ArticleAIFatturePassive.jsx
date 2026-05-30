import Layout from '../components/Layout';
import SEO from '../components/SEO';
import ArticleHero from '../components/ArticleHero';
import ArticleBody from '../components/ArticleBody';

export default function ArticleAIFatturePassive() {
  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "AI per Fatture Passive: Come la Document Intelligence Automatizza il Ciclo Passivo delle PMI",
    "description": "Scopri come l'intelligenza artificiale e la Document Intelligence trasformano la gestione delle fatture passive nelle PMI italiane: dall'estrazione automatica dei dati alla registrazione contabile.",
    "url": "https://finch-ai.it/blog/ai-fatture-passive-document-intelligence-pmi",
    "datePublished": "2026-03-10",
    "dateModified": "2026-03-10",
    "author": { "@type": "Organization", "name": "FinCh-Ai", "url": "https://finch-ai.it" },
    "publisher": { "@type": "Organization", "name": "FinCh-Ai", "logo": { "@type": "ImageObject", "url": "https://finch-ai.it/assets/images/LOGO.png" } },
    "image": "https://finch-ai.it/assets/images/og-image.png",
    "articleSection": "Thought Leadership",
    "inLanguage": "it-IT",
    "keywords": "AI fatture passive, document intelligence, automazione fatture, ciclo passivo, PMI italiane, estrazione dati fatture, intelligenza artificiale contabilità"
  };

  const seoProps = {
    title: "AI per Fatture Passive | Document Intelligence per PMI | Finch-AI",
    description: "Scopri come l'intelligenza artificiale e la Document Intelligence trasformano la gestione delle fatture passive nelle PMI italiane: dall'estrazione automatica dei dati alla registrazione contabile.",
    keywords: "AI fatture passive, document intelligence, automazione fatture, ciclo passivo, PMI italiane, estrazione dati fatture, intelligenza artificiale contabilità",
    canonical: "https://finch-ai.it/blog/ai-fatture-passive-document-intelligence-pmi",
    ogType: "article",
    article: { publishedTime: "2026-03-10", author: "FinCh-Ai", section: "Thought Leadership" },
    jsonLd: [articleJsonLd],
  };

  return (
    <Layout>
      <SEO {...seoProps} />

      <ArticleHero
        category="Thought Leadership"
        title="AI per Fatture Passive: Come la Document Intelligence Automatizza il Ciclo Passivo delle PMI"
        description="Dalla ricezione alla registrazione contabile: come l'intelligenza artificiale sta eliminando ore di lavoro manuale nella gestione delle fatture fornitori."
        meta="10 Marzo 2026 · FinCh-Ai · ~8 min lettura"
      />
      <ArticleBody slug="ai-fatture-passive-document-intelligence-pmi" />
    </Layout>
  );
}
