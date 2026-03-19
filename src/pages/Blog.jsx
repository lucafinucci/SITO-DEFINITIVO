import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import SEO from '../components/SEO';
import { blogArticles } from '../data/blogArticles';

export default function Blog() {
  const seoProps = {
    title: "Il Blog sull'Intelligenza Artificiale per PMI | FinCh-Ai",
    description: "Rimani aggiornato su come l'Intelligenza Artificiale sta trasformando le PMI italiane. Scopri guide, casi studio e insights per ottimizzare la tua azienda.",
    keywords: "Blog intelligenza artificiale, AI per PMI, innovazione aziendale, FinCh-AI blog, articoli intelligenza artificiale, casi studio AI",
    canonical: "https://finch-ai.it/blog",
    ogType: "website"
  };

  return (
    <Layout>
      <SEO {...seoProps} />
      
      {/* Blog Hero Section */}
      <div className="relative overflow-hidden border-b border-border/40">
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-br from-teal-500/10 via-transparent to-emerald-500/5" />
        <div className="relative mx-auto max-w-7xl px-4 py-16 sm:py-24 text-center">
          <span className="mb-5 inline-block rounded-full border border-teal-500/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-teal-500 dark:text-teal-400">
            Insights & Aggiornamenti
          </span>
          <h1 className="mb-6 text-4xl font-bold leading-tight text-foreground sm:text-5xl lg:text-6xl">
            Il Blog di FinCh-AI
          </h1>
          <p className="mx-auto max-w-2xl text-lg text-muted-foreground sm:text-xl">
            Scopri come l'Intelligenza Artificiale sta rivoluzionando i flussi di lavoro di PMI e studi professionali. Guide pratiche, analisi e use case reali.
          </p>
        </div>
      </div>

      {/* Blog Cards Grid */}
      <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {blogArticles.map((article) => (
            <Link 
              key={article.id} 
              to={article.path}
              className="group flex flex-col justify-between overflow-hidden rounded-2xl border border-border/50 bg-card transition-all hover:-translate-y-1 hover:border-teal-500/50 hover:shadow-xl hover:shadow-teal-500/10"
            >
              <div className="flex-1 p-6">
                <div className="mb-4 flex items-center justify-between">
                  <span className="inline-flex items-center rounded-md bg-teal-500/10 px-2.5 py-1 text-xs font-medium text-teal-600 dark:text-teal-400">
                    {article.category}
                  </span>
                  <span className="text-xs text-muted-foreground">
                    {article.readTime}
                  </span>
                </div>
                <h3 className="mb-3 text-xl font-semibold text-foreground group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                  {article.title}
                </h3>
                <p className="text-sm text-muted-foreground line-clamp-3">
                  {article.description}
                </p>
              </div>
              <div className="border-t border-border/50 bg-muted/20 px-6 py-4">
                <div className="flex items-center justify-between">
                  <span className="text-xs text-muted-foreground">{article.date}</span>
                  <span className="text-sm font-medium text-teal-600 dark:text-teal-400 group-hover:underline">
                    Leggi di più &rarr;
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </Layout>
  );
}
