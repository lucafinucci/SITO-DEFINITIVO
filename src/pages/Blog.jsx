import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { getBlogPosts, getMediaUrl, formatDate } from '../lib/cms';
import { Calendar, Tag, ArrowRight, Loader2 } from 'lucide-react';

export default function Blog() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    async function fetchPosts() {
      try {
        setLoading(true);
        const response = await getBlogPosts({ limit: 9, page });
        setPosts(response.docs);
        setTotalPages(response.totalPages);
      } catch (err) {
        setError('Impossibile caricare gli articoli. Riprova più tardi.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchPosts();
  }, [page]);

  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-white">
      {/* Header */}
      <header className="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/50 bg-slate-900/80 backdrop-blur-xl">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-20 items-center justify-between">
            <Link to="/" className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-lg">
                <img
                  src="/assets/images/LOGO.png"
                  alt="Finch-AI"
                  className="h-8 w-auto object-contain"
                />
              </div>
              <span className="text-xl font-bold text-white">Finch-AI</span>
            </Link>
            <nav className="flex items-center gap-6">
              <Link to="/" className="text-sm text-slate-400 hover:text-white transition-colors">
                Home
              </Link>
              <Link to="/blog" className="text-sm text-cyan-300 font-medium">
                Blog
              </Link>
              <Link to="/use-cases" className="text-sm text-slate-400 hover:text-white transition-colors">
                Use Cases
              </Link>
              <Link to="/team" className="text-sm text-slate-400 hover:text-white transition-colors">
                Team
              </Link>
            </nav>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="pt-32 pb-16 px-4">
        <div className="mx-auto max-w-6xl text-center">
          <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
            Blog
          </span>
          <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
            <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">
              Insights & News
            </span>
          </h1>
          <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
            Articoli, guide e approfondimenti sull'intelligenza artificiale applicata al business
          </p>
        </div>
      </section>

      {/* Blog Posts Grid */}
      <section className="pb-24 px-4">
        <div className="mx-auto max-w-6xl">
          {loading ? (
            <div className="flex items-center justify-center py-20">
              <Loader2 className="h-8 w-8 text-cyan-400 animate-spin" />
              <span className="ml-3 text-slate-400">Caricamento articoli...</span>
            </div>
          ) : error ? (
            <div className="text-center py-20">
              <p className="text-red-400">{error}</p>
              <button
                onClick={() => window.location.reload()}
                className="mt-4 px-4 py-2 rounded-lg bg-cyan-500/20 text-cyan-300 hover:bg-cyan-500/30 transition-colors"
              >
                Riprova
              </button>
            </div>
          ) : posts.length === 0 ? (
            <div className="text-center py-20">
              <p className="text-slate-400">Nessun articolo pubblicato ancora.</p>
              <Link
                to="/"
                className="mt-4 inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300"
              >
                Torna alla home
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          ) : (
            <>
              <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                {posts.map((post) => (
                  <article
                    key={post.id}
                    className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur transition-all hover:border-cyan-500/50 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                  >
                    {/* Featured Image */}
                    <div className="aspect-video overflow-hidden">
                      {post.featuredImage ? (
                        <img
                          src={getMediaUrl(post.featuredImage, 'card')}
                          alt={post.featuredImage.alt || post.title}
                          className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                      ) : (
                        <div className="h-full w-full bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                          <span className="text-4xl">📝</span>
                        </div>
                      )}
                    </div>

                    {/* Content */}
                    <div className="p-6">
                      {/* Tags */}
                      {post.tags && post.tags.length > 0 && (
                        <div className="flex flex-wrap gap-2 mb-3">
                          {post.tags.slice(0, 2).map((tagItem, i) => (
                            <span
                              key={i}
                              className="inline-flex items-center gap-1 rounded-full bg-slate-800 px-2 py-1 text-xs text-slate-400"
                            >
                              <Tag className="h-3 w-3" />
                              {tagItem.tag}
                            </span>
                          ))}
                        </div>
                      )}

                      <h2 className="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors line-clamp-2">
                        {post.title}
                      </h2>

                      <p className="text-sm text-slate-400 mb-4 line-clamp-3">
                        {post.excerpt}
                      </p>

                      {/* Meta */}
                      <div className="flex items-center justify-between pt-4 border-t border-slate-700/50">
                        <div className="flex items-center gap-2 text-xs text-slate-500">
                          <Calendar className="h-4 w-4" />
                          {formatDate(post.publishedAt)}
                        </div>
                        <Link
                          to={`/blog/${post.slug}`}
                          className="inline-flex items-center gap-1 text-sm font-semibold text-cyan-400 hover:text-cyan-300 transition-colors"
                        >
                          Leggi
                          <ArrowRight className="h-4 w-4" />
                        </Link>
                      </div>
                    </div>
                  </article>
                ))}
              </div>

              {/* Pagination */}
              {totalPages > 1 && (
                <div className="flex items-center justify-center gap-2 mt-12">
                  <button
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={page === 1}
                    className="px-4 py-2 rounded-lg border border-slate-700 text-slate-400 hover:border-cyan-500/50 hover:text-cyan-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    Precedente
                  </button>
                  <span className="px-4 py-2 text-slate-400">
                    Pagina {page} di {totalPages}
                  </span>
                  <button
                    onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                    disabled={page === totalPages}
                    className="px-4 py-2 rounded-lg border border-slate-700 text-slate-400 hover:border-cyan-500/50 hover:text-cyan-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    Successiva
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-slate-800/50 py-8">
        <div className="mx-auto max-w-7xl px-4 text-center text-sm text-slate-500">
          © {new Date().getFullYear()} Finch-AI S.r.l. Tutti i diritti riservati.
        </div>
      </footer>
    </div>
  );
}
