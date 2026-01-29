import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { getBlogPost, getMediaUrl, formatDate, extractTextFromRichText } from '../lib/cms';
import { Calendar, Tag, ArrowLeft, User, Loader2, Share2 } from 'lucide-react';

export default function BlogPost() {
  const { slug } = useParams();
  const [post, setPost] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchPost() {
      try {
        setLoading(true);
        const data = await getBlogPost(slug);
        if (!data) {
          setError('Articolo non trovato');
        } else {
          setPost(data);
        }
      } catch (err) {
        setError('Impossibile caricare l\'articolo. Riprova più tardi.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchPost();
  }, [slug]);

  // Render Lexical rich text content
  function renderRichText(content) {
    if (!content || !content.root) return null;

    function renderNode(node, index) {
      if (node.type === 'text') {
        let text = node.text;
        if (node.format & 1) text = <strong key={index}>{text}</strong>; // Bold
        if (node.format & 2) text = <em key={index}>{text}</em>; // Italic
        if (node.format & 8) text = <u key={index}>{text}</u>; // Underline
        if (node.format & 16) text = <code key={index} className="bg-slate-800 px-1 rounded">{text}</code>; // Code
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
        const Tag = `h${node.tag || 2}`;
        const className = node.tag === 'h1'
          ? 'text-3xl font-bold text-white mb-4 mt-8'
          : node.tag === 'h2'
          ? 'text-2xl font-bold text-white mb-4 mt-6'
          : 'text-xl font-bold text-white mb-3 mt-5';
        return (
          <Tag key={index} className={className}>
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

      if (node.type === 'quote') {
        return (
          <blockquote key={index} className="border-l-4 border-cyan-500 pl-4 my-6 italic text-slate-400">
            {node.children?.map(renderNode)}
          </blockquote>
        );
      }

      if (node.type === 'link') {
        return (
          <a
            key={index}
            href={node.url}
            className="text-cyan-400 hover:text-cyan-300 underline"
            target={node.url?.startsWith('http') ? '_blank' : undefined}
            rel={node.url?.startsWith('http') ? 'noopener noreferrer' : undefined}
          >
            {node.children?.map(renderNode)}
          </a>
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
        <span className="ml-3 text-slate-400">Caricamento articolo...</span>
      </div>
    );
  }

  if (error || !post) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 flex flex-col items-center justify-center text-white">
        <h1 className="text-2xl font-bold mb-4">{error || 'Articolo non trovato'}</h1>
        <Link
          to="/blog"
          className="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300"
        >
          <ArrowLeft className="h-4 w-4" />
          Torna al blog
        </Link>
      </div>
    );
  }

  const metaTitle = post.seo?.metaTitle || post.title;
  const metaDescription = post.seo?.metaDescription || post.excerpt;

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

      {/* Article */}
      <article className="pt-28 pb-24">
        {/* Hero Image */}
        {post.featuredImage && (
          <div className="w-full h-[50vh] max-h-[500px] overflow-hidden mb-8">
            <img
              src={getMediaUrl(post.featuredImage, 'hero')}
              alt={post.featuredImage.alt || post.title}
              className="w-full h-full object-cover"
            />
          </div>
        )}

        <div className="mx-auto max-w-3xl px-4">
          {/* Back link */}
          <Link
            to="/blog"
            className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-cyan-400 mb-6 transition-colors"
          >
            <ArrowLeft className="h-4 w-4" />
            Torna al blog
          </Link>

          {/* Tags */}
          {post.tags && post.tags.length > 0 && (
            <div className="flex flex-wrap gap-2 mb-4">
              {post.tags.map((tagItem, i) => (
                <span
                  key={i}
                  className="inline-flex items-center gap-1 rounded-full bg-cyan-500/10 border border-cyan-500/30 px-3 py-1 text-xs text-cyan-300"
                >
                  <Tag className="h-3 w-3" />
                  {tagItem.tag}
                </span>
              ))}
            </div>
          )}

          {/* Title */}
          <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-6 leading-tight">
            {post.title}
          </h1>

          {/* Meta info */}
          <div className="flex flex-wrap items-center gap-6 mb-8 pb-8 border-b border-slate-700/50">
            {post.author && (
              <div className="flex items-center gap-2 text-slate-400">
                <User className="h-4 w-4" />
                <span className="text-sm">{post.author.name}</span>
              </div>
            )}
            <div className="flex items-center gap-2 text-slate-400">
              <Calendar className="h-4 w-4" />
              <span className="text-sm">{formatDate(post.publishedAt)}</span>
            </div>
            <button
              onClick={() => navigator.share?.({ title: post.title, url: window.location.href })}
              className="flex items-center gap-2 text-slate-400 hover:text-cyan-400 transition-colors"
            >
              <Share2 className="h-4 w-4" />
              <span className="text-sm">Condividi</span>
            </button>
          </div>

          {/* Excerpt */}
          <p className="text-xl text-slate-300 leading-relaxed mb-8">
            {post.excerpt}
          </p>

          {/* Content */}
          <div className="prose prose-invert max-w-none">
            {renderRichText(post.content)}
          </div>

          {/* Share */}
          <div className="mt-12 pt-8 border-t border-slate-700/50">
            <div className="flex items-center justify-between">
              <Link
                to="/blog"
                className="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors"
              >
                <ArrowLeft className="h-4 w-4" />
                Altri articoli
              </Link>
              <a
                href={`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}`}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0077B5] text-white hover:bg-[#006097] transition-colors"
              >
                Condividi su LinkedIn
              </a>
            </div>
          </div>
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
