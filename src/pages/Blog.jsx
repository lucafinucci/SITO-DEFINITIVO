import { Link } from 'react-router-dom';
import { ArrowUpRight } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import SEO from '../components/SEO';
import useReveal from '../hooks/useReveal';
import { blogArticles } from '../data/blogArticles';
import { useLocalizedPath } from '@/i18n/routing';

export default function Blog() {
  useReveal();
  const { t } = useTranslation('blog');
  const lp = useLocalizedPath();

  return (
    <>
      <SEO
        title={t('seo.title')}
        description={t('seo.description')}
        keywords={t('seo.keywords')}
        canonical="https://finch-ai.it/blog"
        ogType="website"
      />
      <Navbar />

      <main id="top">
        {/* ============ HEADER ============ */}
        <section className="section" style={{ paddingBottom: 0 }}>
          <div className="wrap">
            <div className="news-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 20 }}>{t('header.eyebrow')}</div>
                <h2 className="h2 reveal d1" dangerouslySetInnerHTML={{ __html: t('header.title') }} />
              </div>
              <p className="lead reveal d2">{t('header.lead')}</p>
            </div>
          </div>
        </section>

        {/* ============ ARTICOLI ============ */}
        <section className="section" style={{ paddingTop: 'clamp(40px,5vw,64px)' }}>
          <div className="wrap">
            <div className="news-grid">
              {blogArticles.map((article, i) => (
                <Link
                  key={article.id}
                  to={lp(article.path)}
                  onClick={() => window.scrollTo(0, 0)}
                  className={`news-card reveal${i % 3 === 1 ? ' d1' : i % 3 === 2 ? ' d2' : ''}`}
                >
                  <div className="news-meta">
                    <span className="nc">{t(`articles.${article.id}.category`)}</span>
                    <span className="nd">{t(`articles.${article.id}.date`)} · {article.readTime}</span>
                  </div>
                  <h3>{t(`articles.${article.id}.title`)}</h3>
                  <p>{t(`articles.${article.id}.description`)}</p>
                  <span className="news-go">{t('readMore')} <ArrowUpRight size={14} /></span>
                </Link>
              ))}
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </>
  );
}
