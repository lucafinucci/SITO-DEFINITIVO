import { Helmet } from 'react-helmet-async';
import { useLocale } from '@/i18n/routing';

const SITE_ORIGIN = 'https://finch-ai.it';

// Given the canonical Italian URL of a page, derive both language URLs.
function deriveAlternates(canonical) {
  if (!canonical) return { it: `${SITE_ORIGIN}/`, en: `${SITE_ORIGIN}/en` };
  let path = canonical;
  try {
    path = new URL(canonical).pathname;
  } catch {
    path = canonical.replace(SITE_ORIGIN, '');
  }
  path = path.replace(/^\/en(?=\/|$)/, '') || '/';
  const itHref = `${SITE_ORIGIN}${path}`;
  const enHref = path === '/' ? `${SITE_ORIGIN}/en` : `${SITE_ORIGIN}/en${path}`;
  return { it: itHref, en: enHref };
}

/**
 * SEO component — renders per-page meta tags via react-helmet-async.
 * Emits language-aware canonical, hreflang and og:locale for IT/EN.
 */
export default function SEO({
  title,
  description,
  keywords,
  canonical,
  ogType = 'website',
  ogImage = 'https://finch-ai.it/assets/images/LOGO.png',
  ogImageAlt = 'Finch-AI — Soluzioni AI per PMI',
  ogImageWidth,
  ogImageHeight,
  article,
  jsonLd = [],
  noIndex = false,
}) {
  const locale = useLocale();
  const isLogoFallback = ogImage.endsWith('/LOGO.png');
  const imgW = ogImageWidth || (isLogoFallback ? '512' : '1200');
  const imgH = ogImageHeight || (isLogoFallback ? '512' : '630');

  const alt = deriveAlternates(canonical);
  const selfCanonical = locale === 'en' ? alt.en : alt.it;
  const ogLocale = locale === 'en' ? 'en_US' : 'it_IT';

  return (
    <Helmet htmlAttributes={{ lang: locale }}>
      <title>{title}</title>
      <meta name="description" content={description} />
      {keywords && <meta name="keywords" content={keywords} />}
      <link rel="canonical" href={selfCanonical} />
      <link rel="alternate" hrefLang="it" href={alt.it} />
      <link rel="alternate" hrefLang="en" href={alt.en} />
      <link rel="alternate" hrefLang="x-default" href={alt.it} />
      {noIndex
        ? <meta name="robots" content="noindex, nofollow" />
        : <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />}

      {/* Open Graph */}
      <meta property="og:type" content={ogType} />
      <meta property="og:url" content={selfCanonical} />
      <meta property="og:title" content={title} />
      <meta property="og:description" content={description} />
      <meta property="og:image" content={ogImage} />
      <meta property="og:image:secure_url" content={ogImage} />
      <meta property="og:image:type" content="image/png" />
      <meta property="og:image:width" content={imgW} />
      <meta property="og:image:height" content={imgH} />
      <meta property="og:image:alt" content={ogImageAlt} />
      <meta property="og:site_name" content="Finch-AI" />
      <meta property="og:locale" content={ogLocale} />

      {/* Twitter Card */}
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:site" content="@FinchAI" />
      <meta name="twitter:url" content={selfCanonical} />
      <meta name="twitter:title" content={title} />
      <meta name="twitter:description" content={description} />
      <meta name="twitter:image" content={ogImage} />
      <meta name="twitter:image:alt" content={ogImageAlt} />

      {/* Article-specific OG tags */}
      {article?.publishedTime && (
        <meta property="article:published_time" content={article.publishedTime} />
      )}
      {article?.modifiedTime && (
        <meta property="article:modified_time" content={article.modifiedTime} />
      )}
      {article?.author && (
        <meta property="article:author" content={article.author} />
      )}
      {article?.section && (
        <meta property="article:section" content={article.section} />
      )}

      {/* JSON-LD structured data */}
      {jsonLd.map((schema, i) => (
        <script key={i} type="application/ld+json">
          {JSON.stringify(schema)}
        </script>
      ))}
    </Helmet>
  );
}
