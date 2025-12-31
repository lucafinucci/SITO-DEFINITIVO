import { Routes, Route } from 'react-router-dom';
import FinchAIMockupAnimated from "@/components/FinchAIMockupAnimated";
import AreaClienti from "@/pages/AreaClienti";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<FinchAIMockupAnimated />} />
      <Route path="/area-clienti/*" element={<AreaClienti />} />
    </Routes>
  );
}
