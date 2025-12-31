import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

export default function Dashboard() {
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [servizi, setServizi] = useState([]);
  const [loading, setLoading] = useState(true);

  // Solo servizi attivi e cliccabili
  const serviziAttivi = (servizi || []).filter((s) => s?.stato === 'attivo');

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
      navigate('/area-clienti');
      return;
    }
    setUser(JSON.parse(userData));
    loadDashboardData();
  }, [navigate]);

  const loadDashboardData = async () => {
    try {
      const res = await fetch('/api/clienti/servizi.php', { credentials: 'include' });
      if (res.ok) setServizi(await res.json());
    } catch (error) {
      console.error('Errore caricamento servizi:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950 text-slate-200">
        Caricamento...
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-200">
      <header className="border-b border-slate-800 bg-slate-900/70 backdrop-blur">
        <div className="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
          <div>
            <p className="text-xs uppercase tracking-wide text-cyan-300">Area Clienti</p>
            <h1 className="text-lg font-semibold text-white">Benvenuto, {user?.name || user?.email}</h1>
          </div>
          <button
            onClick={() => {
              localStorage.removeItem('user');
              navigate('/');
            }}
            className="text-sm text-slate-300 hover:text-red-400 transition"
          >
            Esci
          </button>
        </div>
      </header>

      <main className="max-w-5xl mx-auto px-4 py-8">
        <h2 className="text-xl font-bold text-white mb-4">I tuoi servizi attivi</h2>

        {serviziAttivi.length === 0 ? (
          <p className="text-slate-400">Nessun servizio attivo al momento.</p>
        ) : (
          <div className="space-y-4">
            {serviziAttivi.map((s) => (
              <a
                key={s.id}
                href={`/area-clienti/servizio-dettaglio.php?id=${s.id}`}
                className="block rounded-xl border border-slate-800 bg-slate-900/80 p-5 hover:border-cyan-500/70 hover:bg-slate-900 transition"
              >
                <div className="flex items-center justify-between gap-3">
                  <div>
                    <h3 className="text-lg font-semibold text-white">{s.nome}</h3>
                    <p className="text-sm text-slate-400">{s.descrizione}</p>
                    <p className="text-xs text-slate-500 mt-1">Attivo dal: {s.dataAttivazione}</p>
                  </div>
                  <span className="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                    attivo
                  </span>
                </div>
              </a>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
