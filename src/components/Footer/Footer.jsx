import React from 'react';
import './Footer.css';

const Footer = () => {
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-content">
          <div className="footer-section">
            <h3>Finch-AI</h3>
            <p>L'intelligenza che guida la tua strategia</p>
          </div>
          <div className="footer-section">
            <h4>Contatti</h4>
            <p>Email: info@finch-ai.com</p>
            <p>Telefono: +39 02 1234567</p>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
