import Layout from '../components/Layout';
import SEO from '../components/SEO';
import ArticleHero from '../components/ArticleHero';
import ArticleBody from '../components/ArticleBody';

export default function ArticleAIHumanCentered() {
  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://finch-ai.it/blog/ai-human-centered-potenziare-persone-non-sostituirle"
    },
    "headline": "AI Human-Centered: Potenziare le Persone, Non Sostituirle | FinCh-Ai Blog",
    "description": "La filosofia Human-Centered di FinCh-AI: l'intelligenza artificiale come alleata della produttività umana, non come sostituta. Rischi, mitigazioni e vantaggi concreti per le PMI italiane.",
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
    "datePublished": "2026-03-17",
    "dateModified": "2026-03-17",
    "articleSection": "Intelligenza Artificiale",
    "inLanguage": "it-IT",
    "keywords": "AI human-centered, intelligenza artificiale PMI, human-in-the-loop, AI augmentation, FinCh-AI, produttività AI, rischi automazione, AI etica"
  };

  const seoProps = {
    title: "AI Human-Centered: Potenziare le Persone, Non Sostituirle | FinCh-Ai",
    description: "La filosofia Human-Centered di FinCh-AI: l'intelligenza artificiale come alleata della produttività umana, non come sostituta. Rischi, mitigazioni e vantaggi per le PMI.",
    keywords: "AI human-centered, intelligenza artificiale PMI, human-in-the-loop, AI augmentation, FinCh-AI, produttività AI, rischi automazione, AI etica",
    canonical: "https://finch-ai.it/blog/ai-human-centered-potenziare-persone-non-sostituirle",
    ogType: "article",
    article: { publishedTime: "2026-03-17", author: "FinCh-Ai", section: "Intelligenza Artificiale" },
    jsonLd: [articleJsonLd],
  };

  return (
    <Layout>
      <SEO {...seoProps} />

      <ArticleHero
        category="Intelligenza Artificiale"
        title="AI Human-Centered: Potenziare le Persone, Non Sostituirle"
        description="La filosofia Human-Centered di FinCh-AI: l'intelligenza artificiale come alleata della produttività umana, non come sostituta."
        meta="17 marzo 2026 · FinCh-Ai · ~10 min lettura"
      />
      <ArticleBody slug="ai-human-centered-potenziare-persone-non-sostituirle" />
    </Layout>
  );
}
