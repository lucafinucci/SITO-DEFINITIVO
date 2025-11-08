import { Routes, Route } from 'react-router-dom';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import Home from '@/pages/Home';
import ChiSiamo from '@/pages/ChiSiamo';
import Servizi from '@/pages/Servizi';
import Contatti from '@/pages/Contatti';
import ThankYou from '@/pages/ThankYou';

function App() {
  return (
    <>
      <Header />
      <main>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/chi-siamo.html" element={<ChiSiamo />} />
          <Route path="/servizi.html" element={<Servizi />} />
          <Route path="/contatti.html" element={<Contatti />} />
          <Route path="/thank-you.html" element={<ThankYou />} />
        </Routes>
      </main>
      <Footer />
    </>
  );
}

export default App;