import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';

const newsData = [
  {
    id: 1,
    badge: 'Novità',
    title: 'L\'Analista Finanziario è ora online!',
    date: 'Oggi',
    link: '/soluzioni/finance-intelligence',
    badgeColor: 'bg-green-500',
    ariaLabel: 'Scopri il nuovo Analista Finanziario'
  },
  {
    id: 2,
    badge: 'Novità',
    title: 'Document Intelligence è ora online!',
    date: 'Oggi',
    link: '/soluzioni/document-intelligence',
    badgeColor: 'bg-green-500',
    ariaLabel: 'Scopri il nuovo Document Intelligence'
  }
];

const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.15 }
  }
};

const itemVariants = {
  hidden: { opacity: 0, y: 15 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.5, ease: 'easeOut' }
  }
};

function NewsFeed() {
  // Schema.org per la SEO
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "itemListElement": newsData.map((news, index) => ({
      "@type": "ListItem",
      "position": index + 1,
      "item": {
        "@type": "NewsArticle",
        "headline": news.title,
        "datePublished": "2026-03-19",
        "url": `https://finc-ai.com${news.link}`
      }
    }))
  };

  return (
    <section aria-labelledby="news-heading" className="w-full max-w-6xl mx-auto px-4 py-8 mt-4 mb-4">
      {/* Aggiungiamo Schema.org JSON-LD per SEO */}
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />
      
      <div className="flex items-center justify-between mb-6">
        <h2 id="news-heading" className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
          Ultime Novità
        </h2>
      </div>

      <motion.div 
        className="grid grid-cols-1 md:grid-cols-2 gap-4"
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
        variants={containerVariants}
      >
        {newsData.map((news) => (
          <motion.article 
            key={news.id} 
            variants={itemVariants}
            className="group relative flex flex-col justify-between p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden"
          >
            {/* Effetto sfondo hover */}
            <div className="absolute inset-0 bg-gradient-to-r from-transparent via-gray-50 dark:via-gray-700/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <div className="relative z-10">
              <div className="flex items-center gap-3 mb-3">
                <span className={`px-3 py-1 text-xs font-semibold text-white rounded-full ${news.badgeColor}`}>
                  {news.badge}
                </span>
              </div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-primary transition-colors">
                <Link to={news.link} aria-label={news.ariaLabel} className="focus:outline-none">
                  <span className="absolute inset-0" aria-hidden="true"></span>
                  {news.title}
                </Link>
              </h3>
            </div>
            
            <Link to={news.link} className="relative z-10 mt-4 flex items-center text-primary font-medium text-sm group-hover:translate-x-1 transition-transform focus:outline-none">
              Scopri di più
              <svg className="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </motion.article>
        ))}
      </motion.div>
    </section>
  );
}

export default NewsFeed;
