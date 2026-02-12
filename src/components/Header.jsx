import { useEffect } from 'react';
import { NavLink } from 'react-router-dom';

function Header() {
  useEffect(() => {
    const header = document.querySelector('header');
    const handleScroll = () => {
      if (window.pageYOffset > 100) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    };

    window.addEventListener('scroll', handleScroll);

    // Cleanup function
    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, []);

  return (
    <header>
      <nav>
        <NavLink to="/" className="logo">
          <img src="/assets/images/LOGO.png" alt="FINCH-AI Logo" />
        </NavLink>
        <ul>
          <li><NavLink to="/">Home</NavLink></li>
          <li><NavLink to="/servizi.html">Soluzioni</NavLink></li>
          <li><NavLink to="/chi-siamo.html">Per Aziende</NavLink></li>
          <li><NavLink to="/contatti.html">Demo</NavLink></li>
        </ul>
      </nav>
    </header>
  );
}

export default Header;