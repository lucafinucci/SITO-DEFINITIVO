import { useState, useEffect } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Login from './Login';
import Dashboard from './Dashboard';

export default function AreaClienti() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Verifica se l'utente è già autenticato
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      // Controlla se c'è un utente salvato in localStorage
      const user = localStorage.getItem('user');

      if (user) {
        // Opzionale: verifica la sessione con il backend
        const response = await fetch('/api/auth/check-session.php', {
          credentials: 'include'
        });

        if (response.ok) {
          setIsAuthenticated(true);
        } else {
          localStorage.removeItem('user');
          setIsAuthenticated(false);
        }
      } else {
        setIsAuthenticated(false);
      }
    } catch (error) {
      console.error('Errore verifica autenticazione:', error);
      setIsAuthenticated(false);
    } finally {
      setLoading(false);
    }
  };

  const handleLoginSuccess = (user) => {
    setIsAuthenticated(true);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 flex items-center justify-center">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin mx-auto mb-4" />
          <p className="text-slate-400">Caricamento...</p>
        </div>
      </div>
    );
  }

  return (
    <Routes>
      <Route
        path="/"
        element={
          isAuthenticated ? (
            <Navigate to="/area-clienti/dashboard" replace />
          ) : (
            <Login onLoginSuccess={handleLoginSuccess} />
          )
        }
      />
      <Route
        path="/dashboard"
        element={
          isAuthenticated ? (
            <Dashboard />
          ) : (
            <Navigate to="/area-clienti" replace />
          )
        }
      />
    </Routes>
  );
}
