import { useEffect, useState } from 'react';
import Navbar from '../components/Navbar';

export default function ArticleAIImprenditori() {
  const [bodyHtml, setBodyHtml] = useState('');
  const [articleStyles, setArticleStyles] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Load Google Fonts needed by the article
    const fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap';
    document.head.appendChild(fontLink);

    fetch('/blog/intelligenza-artificiale-imprenditori-commercialisti.html')
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Remove article's own navbar (replaced by SPA navbar)
        const nav = doc.querySelector('nav.navbar');
        if (nav) nav.remove();

        // Extract styles from <head>
        const rawStyles = Array.from(doc.querySelectorAll('style'))
          .map(s => s.textContent)
          .join('\n');

        // Remove html{} and body{} rules to avoid conflicts with SPA globals
        const filtered = rawStyles
          .replace(/\bhtml\s*\{[^}]*\}/g, '')
          .replace(/\bbody\s*\{[^}]*\}/g, '');

        setArticleStyles(filtered);
        setBodyHtml(doc.body.innerHTML);
        setLoading(false);
      });

    return () => {
      if (document.head.contains(fontLink)) {
        document.head.removeChild(fontLink);
      }
    };
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
      </div>
    );
  }

  return (
    <>
      <Navbar />
      <style>{articleStyles}</style>
      <div className="pt-28 sm:pt-32 lg:pt-36">
        <div dangerouslySetInnerHTML={{ __html: bodyHtml }} />
      </div>
    </>
  );
}
