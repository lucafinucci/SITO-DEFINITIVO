import { useEffect, useState } from 'react';
import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import BackgroundCanvas from './components/ui/BackgroundCanvas';
import BackToTop from './components/ui/BackToTop';
import Hero from './components/sections/Hero';
import DocAI from './components/sections/DocAI';
import Problem from './components/sections/Problem';
import Solution from './components/sections/Solution';
import Modules from './components/sections/Modules';
import Sectors from './components/sections/Sectors';
import Testimonials from './components/sections/Testimonials';
import Contact from './components/sections/Contact';

export default function App() {
  const [activeSection, setActiveSection] = useState('hero');

  useEffect(() => {
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
  }, []);

  return (
    <div className="min-h-screen transition-colors duration-300 dark:bg-slate-950 bg-white">
      <BackgroundCanvas />
      <Header activeSection={activeSection} setActiveSection={setActiveSection} />
      <main className="relative z-0">
        <Hero />
        <DocAI />
        <Problem />
        <Solution />
        <Modules />
        <Sectors />
        <Testimonials />
        <Contact />
      </main>
      <BackToTop />
      <Footer />
    </div>
  );
}
