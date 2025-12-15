import { NavLink, Link } from 'react-router-dom';

const linkStyle = ({ isActive }) => ({
  borderRadius: 12,
  padding: '8px 10px',
  background: isActive ? 'rgba(255,255,255,.10)' : 'transparent'
});

export default function Navbar(){
  return (
    <nav className="nav">
      <div className="container nav-inner">
        <Link to="/" className="logo">
          <img src="/favicon.png" alt="Finch-AI" />
          <span>Finch-AI</span>
        </Link>
        <div className="menu">
          <NavLink to="/soluzioni" style={linkStyle}>Soluzioni AI</NavLink>
          <NavLink to="/servizi" style={linkStyle}>Servizi</NavLink>
          <NavLink to="/tecnologia" style={linkStyle}>Tecnologia</NavLink>
          <NavLink to="/casi-uso" style={linkStyle}>Casi d'uso</NavLink>
          <NavLink to="/chi-siamo" style={linkStyle}>Chi siamo</NavLink>
          <NavLink to="/contatti" style={linkStyle}>Contatti/Demo</NavLink>
        </div>
      </div>
    </nav>
  );
}
