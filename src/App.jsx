import { Routes, Route } from 'react-router-dom';
import FinchAIMockupAnimated from "@/components/FinchAIMockupAnimated";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<FinchAIMockupAnimated />} />
    </Routes>
  );
}
