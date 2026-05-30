import { Link } from 'react-router-dom';
import { ArrowUpRight } from 'lucide-react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import SEO from '../components/SEO';
import useReveal from '../hooks/useReveal';
import { blogArticles } from '../data/blogArticles';

export default function Blog() {
  useReveal();

  const seoProps = {
    title: "Il Blog sull'Intelligenza Artificiale per PMI | FinCh-Ai",
    description: "Rimani aggiornato su come l'Intelligenza Artificiale sta trasformando le PMI italiane. Scopri guide, casi studio e insights per ottimizzare la tua azienda.",
    keywords: "Blog intelligenza artificiale, AI per PMI, innovazione aziendale, FinCh-AI blog, articoli intelligenza artificiale, casi studio AI",
    canonical: "https://finch-ai.it/blog",
    ogType: "website"
  };

  return (
    <>
      <SEO {...seoProps} />
      <Navbar />

      <main id="top">
        {/* ============ HEADER ============ */}
        <section className="section" style={{ paddingBottom: 0 }}>
          <div className="wrap">
            <div className="news-head">
              <div>
                <div className="eyebrow reveal" style={{ marginBottom: 20 }}>Insights & Aggiornamenti</div>
                <h2 className="h2 reveal d1">Il blog di <em>Finch</em>.</h2>
              </div>
              <p className="lead reveal d2">
                Come l'intelligenza artificiale sta rivoluzionando i flussi di lavoro di PMI e studi professionali. Guide pratiche, analisi e use case reali.
              </p>
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
                  to={article.path}
                  onClick={() => window.scrollTo(0, 0)}
                  className={`news-card reveal${i % 3 === 1 ? ' d1' : i % 3 === 2 ? ' d2' : ''}`}
                >
                  <div className="news-meta">
                    <span className="nc">{article.category}</span>
                    <span className="nd">{article.date} · {article.readTime}</span>
                  </div>
                  <h3>{article.title}</h3>
                  <p>{article.description}</p>
                  <span className="news-go">Leggi <ArrowUpRight size={14} /></span>
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
