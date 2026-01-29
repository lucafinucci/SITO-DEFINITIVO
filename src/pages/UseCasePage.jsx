import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { getUseCase, getMediaUrl, getIndustryLabel, extractTextFromRichText } from '../lib/cms';
import { ArrowLeft, Briefcase, Quote, Loader2, ChevronLeft, ChevronRight } from 'lucide-react';

export default function UseCasePage() {
  const { slug } = useParams();
  const [useCase, setUseCase] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeImage, setActiveImage] = useState(0);

  useEffect(() => {
    async function fetchCase() {
      try {
        setLoading(true);
        const data = await getUseCase(slug);
        if (!data) {
          setError('Case study non trovato');
        } else {
          setUseCase(data);
        }
      } catch (err) {
        setError('Impossibile caricare il case study. Riprova più tardi.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchCase();
  }, [slug]);

  // Render Lexical rich text
  function renderRichText(content) {
    if (!content || !content.root) return null;

    function renderNode(node, index) {
      if (node.type === 'text') {
        let text = node.text;
        if (node.format & 1) text = <strong key={index}>{text}</strong>;
        if (node.format & 2) text = <em key={index}>{text}</em>;
        return text;
      }

      if (node.type === 'paragraph') {
        return (
          <p key={index} className="mb-4 text-slate-300 leading-relaxed">
            {node.children?.map(renderNode)}
          </p>
        );
      }

      if (node.type === 'heading') {
        const Tag = `h${node.tag || 3}`;
        return (
          <Tag key={index} className="text-xl font-bold text-white mb-3 mt-5">
            {node.children?.map(renderNode)}
          </Tag>
        );
      }

      if (node.type === 'list') {
        const ListTag = node.listType === 'number' ? 'ol' : 'ul';
        return (
          <ListTag key={index} className="mb-4 pl-6 space-y-2 text-slate-300">
            {node.children?.map((child, i) => (
              <li key={i} className={node.listType === 'number' ? 'list-decimal' : 'list-disc'}>
                {child.children?.map(renderNode)}
              </li>
            ))}
          </ListTag>
        );
      }

      if (node.children) {
        return node.children.map(renderNode);
      }

      return null;
    }

    return renderNode(content.root, 0);
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 flex items-center justify-center">
        <Loader2 className="h-8 w-8 text-cyan-400 animate-spin" />
        <span className="ml-3 text-slate-400">Caricamento case study...</span>
      </div>
    );
  }

  if (error || !useCase) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 flex flex-col items-center justify-center text-white">
        <h1 className="text-2xl font-bold mb-4">{error || 'Case study non trovato'}</h1>
        <Link
          to="/use-cases"
          className="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300"
        >
          <ArrowLeft className="h-4 w-4" />
          Torna ai case study
        </Link>
      </div>
    );
  }

  const allImages = [
    useCase.featuredImage,
    ...(useCase.images?.map((img) => img.image) || []),
  ].filter(Boolean);

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
              <Link to="/blog" className="text-sm text-slate-400 hover:text-white transition-colors">
                Blog
              </Link>
              <Link to="/use-cases" className="text-sm text-cyan-300 font-medium">
                Use Cases
              </Link>
              <Link to="/team" className="text-sm text-slate-400 hover:text-white transition-colors">
                Team
              </Link>
            </nav>
          </div>
        </div>
      </header>

      {/* Content */}
      <article className="pt-28 pb-24">
        <div className="mx-auto max-w-5xl px-4">
          {/* Back link */}
          <Link
            to="/use-cases"
            className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-cyan-400 mb-6 transition-colors"
          >
            <ArrowLeft className="h-4 w-4" />
            Torna ai case study
          </Link>

          {/* Header */}
          <div className="flex flex-col md:flex-row md:items-start gap-6 mb-8">
            {/* Client Logo */}
            {useCase.clientLogo && (
              <div className="flex-shrink-0 w-24 h-24 rounded-xl bg-white p-4 flex items-center justify-center">
                <img
                  src={getMediaUrl(useCase.clientLogo)}
                  alt={useCase.clientName}
                  className="max-w-full max-h-full object-contain"
                />
              </div>
            )}

            <div className="flex-grow">
              <span className="inline-flex items-center rounded-full bg-blue-500/10 border border-blue-500/30 px-3 py-1 text-xs font-medium text-blue-300 mb-3">
                <Briefcase className="h-3 w-3 mr-1" />
                {getIndustryLabel(useCase.industry)}
              </span>
              <h1 className="text-3xl sm:text-4xl font-extrabold mb-2">{useCase.title}</h1>
              <p className="text-lg text-slate-400">Cliente: {useCase.clientName}</p>
            </div>
          </div>

          {/* Results Metrics */}
          {useCase.results && useCase.results.length > 0 && (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
              {useCase.results.map((result, i) => (
                <div
                  key={i}
                  className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-4 text-center"
                >
                  <div className="text-3xl font-bold text-cyan-400">{result.value}</div>
                  <div className="text-sm text-slate-300">{result.metric}</div>
                </div>
              ))}
            </div>
          )}

          {/* Image Gallery */}
          {allImages.length > 0 && (
            <div className="mb-12">
              <div className="relative aspect-video rounded-2xl overflow-hidden mb-4">
                <img
                  src={getMediaUrl(allImages[activeImage], 'hero')}
                  alt={allImages[activeImage]?.alt || useCase.title}
                  className="w-full h-full object-cover"
                />
                {allImages.length > 1 && (
                  <>
                    <button
                      onClick={() => setActiveImage((i) => (i - 1 + allImages.length) % allImages.length)}
                      className="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                      <ChevronLeft className="h-6 w-6" />
                    </button>
                    <button
                      onClick={() => setActiveImage((i) => (i + 1) % allImages.length)}
                      className="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                      <ChevronRight className="h-6 w-6" />
                    </button>
                  </>
                )}
              </div>
              {allImages.length > 1 && (
                <div className="flex gap-2 justify-center">
                  {allImages.map((_, i) => (
                    <button
                      key={i}
                      onClick={() => setActiveImage(i)}
                      className={`w-2 h-2 rounded-full transition-colors ${
                        i === activeImage ? 'bg-cyan-400' : 'bg-slate-600 hover:bg-slate-500'
                      }`}
                    />
                  ))}
                </div>
              )}
            </div>
          )}

          {/* Challenge Section */}
          <section className="mb-12">
            <h2 className="text-2xl font-bold text-white mb-4 flex items-center gap-2">
              <span className="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center text-red-400">
                !
              </span>
              La Sfida
            </h2>
            <div className="pl-10">
              {renderRichText(useCase.challenge)}
            </div>
          </section>

          {/* Solution Section */}
          <section className="mb-12">
            <h2 className="text-2xl font-bold text-white mb-4 flex items-center gap-2">
              <span className="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center text-cyan-400">
                ✓
              </span>
              La Soluzione
            </h2>
            <div className="pl-10">
              {renderRichText(useCase.solution)}
            </div>
          </section>

          {/* Testimonial */}
          {useCase.testimonialQuote && (
            <section className="mb-12">
              <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 p-8">
                <Quote className="h-10 w-10 text-cyan-500/50 mb-4" />
                <blockquote className="text-xl text-white italic mb-4 leading-relaxed">
                  "{useCase.testimonialQuote}"
                </blockquote>
                {useCase.testimonialAuthor && (
                  <p className="text-cyan-400 font-medium">— {useCase.testimonialAuthor}</p>
                )}
              </div>
            </section>
          )}

          {/* CTA */}
          <section className="mt-16 pt-8 border-t border-slate-700/50">
            <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div>
                <h3 className="text-xl font-bold text-white">Vuoi risultati simili?</h3>
                <p className="text-slate-400">Contattaci per una demo personalizzata.</p>
              </div>
              <Link
                to="/#contatti"
                className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
              >
                Richiedi una demo
              </Link>
            </div>
          </section>
        </div>
      </article>

      {/* Footer */}
      <footer className="border-t border-slate-800/50 py-8">
        <div className="mx-auto max-w-7xl px-4 text-center text-sm text-slate-500">
          © {new Date().getFullYear()} Finch-AI S.r.l. Tutti i diritti riservati.
        </div>
      </footer>
    </div>
  );
}
