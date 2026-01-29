import { Routes, Route } from 'react-router-dom';
import FinchAIMockupAnimated from "@/components/FinchAIMockupAnimated";
import AreaClienti from "@/pages/AreaClienti";

// CMS-powered pages
import Blog from "@/pages/Blog";
import BlogPost from "@/pages/BlogPost";
import Team from "@/pages/Team";
import UseCasesPage from "@/pages/UseCasesPage";
import UseCasePage from "@/pages/UseCasePage";

export default function App() {
  return (
    <Routes>
      {/* Main landing page */}
      <Route path="/" element={<FinchAIMockupAnimated />} />

      {/* CMS-powered pages */}
      <Route path="/blog" element={<Blog />} />
      <Route path="/blog/:slug" element={<BlogPost />} />
      <Route path="/team" element={<Team />} />
      <Route path="/use-cases" element={<UseCasesPage />} />
      <Route path="/use-cases/:slug" element={<UseCasePage />} />

      {/* Client area (existing PHP backend) */}
      <Route path="/area-clienti/*" element={<AreaClienti />} />
    </Routes>
  );
}
