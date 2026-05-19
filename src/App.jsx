import { Routes, Route } from 'react-router-dom';
import FinchAIMockupV2 from "@/components/FinchAIMockupV2";
import AreaClienti from "@/pages/AreaClienti";
import DocumentIntelligence from "@/pages/DocumentIntelligence";
import FinanceIntelligence from "@/pages/FinanceIntelligence";
import WarehouseIntelligence from "@/pages/WarehouseIntelligence";
import ArticleAIImprenditori from "@/pages/ArticleAIImprenditori";
import ArticleDocumentIntelligenceDDT from "@/pages/ArticleDocumentIntelligenceDDT";
import ArticleStudiProfessionali from "@/pages/ArticleStudiProfessionali";
import ArticleAIFatturePassive from "@/pages/ArticleAIFatturePassive";
import ArticleAIAnalisiDati from "@/pages/ArticleAIAnalisiDati";
import ArticleAnalisiFinanziaria5Min from "@/pages/ArticleAnalisiFinanziaria5Min";
import ArticlePMIGapEuropeo from "@/pages/ArticlePMIGapEuropeo";
import ArticleAIHumanCentered from "@/pages/ArticleAIHumanCentered";
import ArticlePMIDatiSilos from "@/pages/ArticlePMIDatiSilos";
import ArticleSupportoDecisionaleSynapse from "@/pages/ArticleSupportoDecisionaleSynapse";
import Blog from "@/pages/Blog";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<FinchAIMockupV2 />} />
      <Route path="/area-clienti/*" element={<AreaClienti />} />
      <Route path="/soluzioni/document-intelligence" element={<DocumentIntelligence />} />
      <Route path="/soluzioni/finance-intelligence" element={<FinanceIntelligence />} />
      <Route path="/soluzioni/warehouse-intelligence" element={<WarehouseIntelligence />} />
      <Route path="/blog" element={<Blog />} />
      <Route path="/blog/intelligenza-artificiale-imprenditori-commercialisti" element={<ArticleAIImprenditori />} />
      <Route path="/blog/document-intelligence-automazione-ddt-bolle-consegna" element={<ArticleDocumentIntelligenceDDT />} />
      <Route path="/blog/intelligenza-artificiale-studi-professionali" element={<ArticleStudiProfessionali />} />
      <Route path="/blog/ai-fatture-passive-document-intelligence-pmi" element={<ArticleAIFatturePassive />} />
      <Route path="/blog/ai-analisi-dati-pmi-excel-access" element={<ArticleAIAnalisiDati />} />
      <Route path="/blog/analisi-finanziaria-5-minuti-pmi-finch-ai" element={<ArticleAnalisiFinanziaria5Min />} />
      <Route path="/blog/pmi-italiane-intelligenza-artificiale-gap-europeo" element={<ArticlePMIGapEuropeo />} />
      <Route path="/blog/ai-human-centered-potenziare-persone-non-sostituirle" element={<ArticleAIHumanCentered />} />
      <Route path="/blog/pmi-problema-dati-silos-frammentati" element={<ArticlePMIDatiSilos />} />
      <Route path="/blog/sistema-supporto-decisionale-ai-pmi-synapse" element={<ArticleSupportoDecisionaleSynapse />} />
    </Routes>
  );
}
