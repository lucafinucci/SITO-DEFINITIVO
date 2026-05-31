import { useTranslation } from 'react-i18next';
import Layout from './Layout';
import SEO from './SEO';
import ArticleHero from './ArticleHero';
import ArticleBody from './ArticleBody';
import { useLocale } from '@/i18n/routing';

/**
 * Shared blog-article page. Renders SEO, hero and the static body, pulling all
 * copy from the `articles` namespace by `k` (translation key). `slug` is the
 * static HTML file under public/blog (or public/blog/en in English).
 */
export default function ArticlePage({ k, slug, canonical }) {
  const { t } = useTranslation('articles');
  const locale = useLocale();
  const base = `${k}.`;

  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": { "@type": "WebPage", "@id": canonical },
    "headline": t(`${base}ld.headline`),
    "description": t(`${base}ld.description`),
    "url": canonical,
    "datePublished": t(`${base}ld.datePublished`),
    "dateModified": t(`${base}ld.datePublished`),
    "author": { "@type": "Organization", "name": "Finch-AI", "url": "https://finch-ai.it" },
    "publisher": { "@type": "Organization", "name": "Finch-AI", "logo": { "@type": "ImageObject", "url": "https://finch-ai.it/assets/images/LOGO.png" } },
    "image": "https://finch-ai.it/assets/images/og-image.png",
    "articleSection": t(`${base}ld.section`),
    "inLanguage": locale === 'en' ? 'en-US' : 'it-IT',
    "keywords": t(`${base}ld.keywords`),
  };

  return (
    <Layout>
      <SEO
        title={t(`${base}seo.title`)}
        description={t(`${base}seo.description`)}
        keywords={t(`${base}seo.keywords`)}
        canonical={canonical}
        ogType="article"
        article={{ publishedTime: t(`${base}ld.datePublished`), author: "Finch-AI", section: t(`${base}ld.section`) }}
        jsonLd={[articleJsonLd]}
      />
      <ArticleHero
        category={t(`${base}hero.category`)}
        title={t(`${base}hero.title`)}
        description={t(`${base}hero.description`)}
        meta={t(`${base}hero.meta`)}
      />
      <ArticleBody slug={slug} />
    </Layout>
  );
}
