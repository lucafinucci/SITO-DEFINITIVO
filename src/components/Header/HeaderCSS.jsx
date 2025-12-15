import { useState } from 'react';
import { Link } from 'react-router-dom';
import './Header.css';

const HeaderCSS = () => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const toggleMobileMenu = () => {
    setMobileMenuOpen(!mobileMenuOpen);
  };

  const closeMobileMenu = () => {
    setMobileMenuOpen(false);
  };

  return (
    <header className="header">
      <nav className="nav">
        <Link to="/" className="logo">
          <h2>Finch-AI</h2>
        </Link>

        <button
          className="mobile-menu-btn"
          onClick={toggleMobileMenu}
          aria-label="Toggle menu"
        >
          {mobileMenuOpen ? '✕' : '☰'}
        </button>

        <ul className={`nav-links ${mobileMenuOpen ? 'active' : ''}`}>
          <li>
            <Link to="/" onClick={closeMobileMenu}>
              Home
            </Link>
          </li>
          <li>
            <Link to="/soluzioni" onClick={closeMobileMenu}>
              Soluzioni AI
            </Link>
          </li>
          <li>
            <Link to="/servizi" onClick={closeMobileMenu}>
              Servizi
            </Link>
          </li>
          <li>
            <Link to="/tecnologia" onClick={closeMobileMenu}>
              Tecnologia
            </Link>
          </li>
          <li>
            <Link to="/casi-uso" onClick={closeMobileMenu}>
              Casi d'uso
            </Link>
          </li>
          <li>
            <Link to="/chi-siamo" onClick={closeMobileMenu}>
              Chi siamo
            </Link>
          </li>
          <li>
            <Link to="/contatti" onClick={closeMobileMenu}>
              Contatti/Demo
            </Link>
          </li>
        </ul>
      </nav>
    </header>
  );
};

export default HeaderCSS;
