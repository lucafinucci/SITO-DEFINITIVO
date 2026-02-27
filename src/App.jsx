import { Routes, Route } from 'react-router-dom';
import FinchAIMockupAnimated from "@/components/FinchAIMockupAnimated";
import AreaClienti from "@/pages/AreaClienti";
import DocumentIntelligence from "@/pages/DocumentIntelligence";
import FinanceIntelligence from "@/pages/FinanceIntelligence";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<FinchAIMockupAnimated />} />
      <Route path="/area-clienti/*" element={<AreaClienti />} />
      <Route path="/soluzioni/document-intelligence" element={<DocumentIntelligence />} />
      <Route path="/soluzioni/finance-intelligence" element={<FinanceIntelligence />} />
    </Routes>
  );
}
