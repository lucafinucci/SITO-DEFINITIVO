import { Routes, Route } from 'react-router-dom';
import FinchAIMockupV2 from "@/components/FinchAIMockupV2";
import AreaClienti from "@/pages/AreaClienti";
import DocumentIntelligence from "@/pages/DocumentIntelligence";
import FinanceIntelligence from "@/pages/FinanceIntelligence";
import ArticleAIImprenditori from "@/pages/ArticleAIImprenditori";
import ArticleDocumentIntelligenceDDT from "@/pages/ArticleDocumentIntelligenceDDT";
import ArticleStudiProfessionali from "@/pages/ArticleStudiProfessionali";
import ArticleAIFatturePassive from "@/pages/ArticleAIFatturePassive";
import ArticleAIAnalisiDati from "@/pages/ArticleAIAnalisiDati";
import ArticleAnalisiFinanziaria5Min from "@/pages/ArticleAnalisiFinanziaria5Min";
import ArticlePMIGapEuropeo from "@/pages/ArticlePMIGapEuropeo";
import ArticleAIHumanCentered from "@/pages/ArticleAIHumanCentered";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<FinchAIMockupV2 />} />
      <Route path="/area-clienti/*" element={<AreaClienti />} />
      <Route path="/soluzioni/document-intelligence" element={<DocumentIntelligence />} />
      <Route path="/soluzioni/finance-intelligence" element={<FinanceIntelligence />} />
      <Route path="/blog/intelligenza-artificiale-imprenditori-commercialisti" element={<ArticleAIImprenditori />} />
      <Route path="/blog/document-intelligence-automazione-ddt-bolle-consegna" element={<ArticleDocumentIntelligenceDDT />} />
      <Route path="/blog/intelligenza-artificiale-studi-professionali" element={<ArticleStudiProfessionali />} />
      <Route path="/blog/ai-fatture-passive-document-intelligence-pmi" element={<ArticleAIFatturePassive />} />
      <Route path="/blog/ai-analisi-dati-pmi-excel-access" element={<ArticleAIAnalisiDati />} />
      <Route path="/blog/analisi-finanziaria-5-minuti-pmi-finch-ai" element={<ArticleAnalisiFinanziaria5Min />} />
      <Route path="/blog/pmi-italiane-intelligenza-artificiale-gap-europeo" element={<ArticlePMIGapEuropeo />} />
      <Route path="/blog/ai-human-centered-potenziare-persone-non-sostituirle" element={<ArticleAIHumanCentered />} />
    </Routes>
  );
}
