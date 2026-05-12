import { Helmet } from 'react-helmet-async';

/**
 * SEO component — renders per-page meta tags via react-helmet-async.
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
  const isLogoFallback = ogImage.endsWith('/LOGO.png');
  const imgW = ogImageWidth || (isLogoFallback ? '512' : '1200');
  const imgH = ogImageHeight || (isLogoFallback ? '512' : '630');

  return (
    <Helmet>
      <title>{title}</title>
      <meta name="description" content={description} />
      {keywords && <meta name="keywords" content={keywords} />}
      {canonical && <link rel="canonical" href={canonical} />}
      <link rel="alternate" hrefLang="it" href={canonical || 'https://finch-ai.it/'} />
      <link rel="alternate" hrefLang="x-default" href={canonical || 'https://finch-ai.it/'} />
      {noIndex
        ? <meta name="robots" content="noindex, nofollow" />
        : <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />}

      {/* Open Graph */}
      <meta property="og:type" content={ogType} />
      {canonical && <meta property="og:url" content={canonical} />}
      <meta property="og:title" content={title} />
      <meta property="og:description" content={description} />
      <meta property="og:image" content={ogImage} />
      <meta property="og:image:secure_url" content={ogImage} />
      <meta property="og:image:type" content="image/png" />
      <meta property="og:image:width" content={imgW} />
      <meta property="og:image:height" content={imgH} />
      <meta property="og:image:alt" content={ogImageAlt} />
      <meta property="og:site_name" content="Finch-AI" />
      <meta property="og:locale" content="it_IT" />

      {/* Twitter Card */}
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:site" content="@FinchAI" />
      {canonical && <meta name="twitter:url" content={canonical} />}
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
