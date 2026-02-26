import { useEffect, useState } from 'react';
import { Routes, Route, useLocation } from 'react-router-dom';
import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import BackgroundCanvas from './components/ui/BackgroundCanvas';
import BackToTop from './components/ui/BackToTop';
import HomeView from './components/views/HomeView';
import DocumentIntelligenceView from './components/views/DocumentIntelligenceView';

function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);
  return null;
}

export default function App() {
  const [activeSection, setActiveSection] = useState('hero');
  const location = useLocation();

  useEffect(() => {
    if (location.pathname === '/') {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              setActiveSection(entry.target.id);
            }
          });
        },
        { threshold: 0.1, rootMargin: "-30% 0px -30% 0px" }
      );

      const sections = document.querySelectorAll("section[id]");
      sections.forEach((section) => observer.observe(section));

      return () => {
        sections.forEach((section) => observer.unobserve(section));
      };
    } else {
      setActiveSection(''); // Reset active section for other pages
    }
  }, [location.pathname]);

  return (
    <div className="min-h-screen transition-colors duration-300 dark:bg-slate-950 bg-white">
      <ScrollToTop />
      <BackgroundCanvas />
      <Header activeSection={activeSection} setActiveSection={setActiveSection} />
      <main className="relative z-0">
        <Routes>
          <Route path="/" element={<HomeView />} />
          <Route path="/prodotti/document-intelligence" element={<DocumentIntelligenceView />} />
          {/* Add more product routes here internally as needed */}
        </Routes>
      </main>
      <BackToTop />
      <Footer />
    </div>
  );
}
