import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { HelmetProvider } from 'react-helmet-async';
import App from '@/App';
import { ThemeProvider } from '@/context/ThemeContext';
import { ContactModalProvider } from '@/context/ContactModalContext';
import './index.css';
import './styles/finch-design.css';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <HelmetProvider>
      <ThemeProvider>
        <BrowserRouter>
          <ContactModalProvider>
            <App />
          </ContactModalProvider>
        </BrowserRouter>
      </ThemeProvider>
    </HelmetProvider>
  </React.StrictMode>
);