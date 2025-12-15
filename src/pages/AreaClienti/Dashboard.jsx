import { useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ProfiloModal from './ProfiloModal';
import FattureModal from './FattureModal';

export default function Dashboard() {
  const navigate = useNavigate();
  const canvasRef = useRef(null);

  const [user, setUser] = useState(null);
  const [fatture, setFatture] = useState([]);
  const [servizi, setServizi] = useState([]);
  const [scadenze, setScadenze] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showProfiloModal, setShowProfiloModal] = useState(false);
  const [showFattureModal, setShowFattureModal] = useState(false);

  // Dati di fallback per mostrare la UI anche senza API
  const sampleFatture = [
    { id: 1, numero: 'FT-2025-001', data: '05/01/2025', importo: '€ 1.200', stato: 'Pagata', filename: 'FT-2025-001.pdf' },
    { id: 2, numero: 'FT-2025-002', data: '15/01/2025', importo: '€ 980', stato: 'In scadenza', filename: 'FT-2025-002.pdf' },
    { id: 3, numero: 'FT-2025-003', data: '25/01/2025', importo: '€ 750', stato: 'Scaduta', filename: 'FT-2025-003.pdf' },
    { id: 4, numero: 'FT-2025-004', data: '01/02/2025', importo: '€ 1.430', stato: 'Pagata', filename: 'FT-2025-004.pdf' },
    { id: 5, numero: 'FT-2025-005', data: '10/02/2025', importo: '€ 1.050', stato: 'In scadenza', filename: 'FT-2025-005.pdf' },
  ];

  const sampleServizi = [
    { id: 1, nome: 'OCR Documenti', descrizione: 'Elaborazione DDT e fatture', stato: 'attivo', dataAttivazione: '02/01/2025' },
    { id: 2, nome: 'Dashboard KPI', descrizione: 'Monitoraggio produzione', stato: 'attivo', dataAttivazione: '12/01/2025' },
  ];

  const sampleScadenze = [
    { id: 1, descrizione: 'Fattura FT-2025-002', data: '15/02/2025', urgente: false },
    { id: 2, descrizione: 'Rinnovo servizio OCR', data: '20/02/2025', urgente: true },
  ];

  const sampleCosti = {
    costoMese: '€ 3.260',
    costoPagina: '€ 0,021',
    costoTraining: '€ 680',
    storage: '48 GB',
  };

  const fattureRecenti = (fatture.length ? fatture : sampleFatture).slice(0, 5);
  const serviziAttivi = servizi.length ? servizi : sampleServizi;
  const scadenzeNext = scadenze.length ? scadenze : sampleScadenze;
  const kpiCosti = sampleCosti;

  const statoBadge = (stato) => {
    switch (stato) {
      case 'Pagata':
        return 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30';
      case 'In scadenza':
        return 'bg-amber-500/10 text-amber-300 border border-amber-500/30';
      case 'Scaduta':
        return 'bg-red-500/10 text-red-300 border border-red-500/30';
      default:
        return 'bg-slate-700/40 text-slate-200 border border-slate-600/60';
    }
  };

  // Sfondo rete neurale (canvas) stile homepage
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d', { alpha: true });
    let w, h, nodes, rafId;
    const MAX_SPEED = 0.28;

    const resize = () => {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
      const count = Math.min(220, Math.floor((w * h) / 9000));
      const linkDist = Math.min(280, Math.max(180, Math.min(w, h) * 0.28));
      canvas.dataset.linkDist = linkDist;
      nodes = Array.from({ length: count }).map(() => ({
        x: Math.random() * w,
        y: Math.random() * h,
        vx: (Math.random() - 0.5) * MAX_SPEED,
        vy: (Math.random() - 0.5) * MAX_SPEED,
        r: Math.random() * 2 + 1,
      }));
    };

    const draw = () => {
      const linkDist = Number(canvas.dataset.linkDist || 180);
      ctx.clearRect(0, 0, w, h);
      ctx.fillStyle = 'rgba(4, 10, 20, 0.75)';
      ctx.fillRect(0, 0, w, h);

      const rg = ctx.createRadialGradient(w * 0.5, h * 0.28, 0, w * 0.5, h * 0.28, Math.max(w, h) * 0.95);
      rg.addColorStop(0, 'rgba(23,162,255,0.3)');
      rg.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = rg;
      ctx.fillRect(0, 0, w, h);

      ctx.fillStyle = 'rgba(56, 189, 248, 1)';
      for (const n of nodes) {
        n.x += n.vx; n.y += n.vy;
        if (n.x < 0 || n.x > w) n.vx *= -1;
        if (n.y < 0 || n.y > h) n.vy *= -1;
        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
        ctx.fill();
      }

      ctx.lineWidth = 1.4;
      const grad = ctx.createLinearGradient(0, 0, w, h);
      grad.addColorStop(0, 'rgba(34, 211, 238, 1)');
      grad.addColorStop(1, 'rgba(59, 130, 246, 1)');
      ctx.strokeStyle = grad;
      for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
          const dx = nodes[i].x - nodes[j].x;
          const dy = nodes[i].y - nodes[j].y;
          const dist = Math.hypot(dx, dy);
          if (dist < linkDist) {
            ctx.globalAlpha = 1 - dist / linkDist;
            ctx.beginPath();
            ctx.moveTo(nodes[i].x, nodes[i].y);
            ctx.lineTo(nodes[j].x, nodes[j].y);
            ctx.stroke();
          }
        }
      }
      ctx.globalAlpha = 1;
      rafId = requestAnimationFrame(draw);
    };

    resize();
    draw();
    const onVisibility = () => {
      if (document.hidden) cancelAnimationFrame(rafId);
      else draw();
    };
    window.addEventListener('resize', resize);
    document.addEventListener('visibilitychange', onVisibility);
    return () => {
      cancelAnimationFrame(rafId);
      window.removeEventListener('resize', resize);
      document.removeEventListener('visibilitychange', onVisibility);
    };
  }, []);

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
      const [fattureRes, serviziRes, scadenzeRes] = await Promise.all([
        fetch('/api/clienti/fatture.php', { credentials: 'include' }),
        fetch('/api/clienti/servizi.php', { credentials: 'include' }),
        fetch('/api/clienti/scadenze.php', { credentials: 'include' })
      ]);

      if (fattureRes.ok) setFatture(await fattureRes.json());
      if (serviziRes.ok) setServizi(await serviziRes.json());
      if (scadenzeRes.ok) setScadenze(await scadenzeRes.json());
    } catch (error) {
      console.error('Errore caricamento dati:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    try {
      await fetch('/api/auth/logout.php', { method: 'POST', credentials: 'include' });
    } catch (error) {
      console.error('Errore logout:', error);
    }
    localStorage.removeItem('user');
    navigate('/');
  };

  const downloadFattura = async (fatturaId, filename) => {
    try {
      const response = await fetch(`/api/clienti/download-fattura.php?id=${fatturaId}`, {
        credentials: 'include'
      });
      if (!response.ok) throw new Error('Errore download');
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error) {
      alert('Errore durante il download della fattura');
    }
  };

  if (loading) {
    return (
      <div className="relative min-h-screen flex items-center justify-center overflow-hidden text-slate-200">
        <canvas ref={canvasRef} className="fixed inset-0 -z-10 h-full w-full" aria-hidden="true" />
        <div className="pointer-events-none fixed inset-0 -z-10">
          <div className="absolute inset-0 opacity-65 [background:linear-gradient(120deg,#040a14_20%,#08101f_60%,#050c18_90%)]" />
          <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[length:100%_24px] mix-blend-overlay opacity-55" />
        </div>
        <div className="relative text-center">
          <div className="w-16 h-16 border-4 border-cyan-500 border-t-transparent rounded-full animate-spin mx-auto mb-4" />
          <p className="text-slate-300">Caricamento...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="relative min-h-screen overflow-hidden text-slate-200">
      <canvas ref={canvasRef} className="fixed inset-0 -z-10 h-full w-full" aria-hidden="true" />
      <div className="pointer-events-none fixed inset-0 -z-10">
        <div className="absolute inset-0 opacity-65 [background:linear-gradient(120deg,#040a14_20%,#08101f_60%,#050c18_90%)]" />
        <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[length:100%_24px] mix-blend-overlay opacity-55" />
      </div>

      {/* Header */}
      <header className="border-b border-slate-800/60 bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
          <div className="flex items-center gap-4">
            <a href="/" className="flex items-center gap-3">
              <span className="relative inline-flex items-center justify-center">
                <span className="absolute inset-0 rounded-2xl bg-cyan-400/40 blur-2xl opacity-70" />
                <span className="relative inline-flex items-center justify-center p-3 bg-white rounded-2xl shadow-[0_10px_40px_rgba(34,211,238,0.35)] border-4 border-cyan-400/50">
                  <img src="/assets/images/LOGO.png" alt="Finch-AI" className="h-12 w-auto drop-shadow-[0_8px_24px_rgba(34,211,238,0.45)]" />
                </span>
              </span>
            </a>
            <div className="h-8 w-px bg-slate-700" />
            <div>
              <p className="text-xs uppercase tracking-[0.08em] text-cyan-300">Area Clienti</p>
              <h1 className="text-lg font-bold text-white">Benvenuto, {user?.name || user?.email}</h1>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <span className="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-300">
              Account attivo
            </span>
            <button
              onClick={() => setShowProfiloModal(true)}
              className="px-4 py-2 text-sm font-medium text-slate-300 hover:text-cyan-400 transition-colors"
            >
              Profilo
            </button>
            <button
              onClick={handleLogout}
              className="px-4 py-2 text-sm font-medium text-slate-300 hover:text-red-400 transition-colors"
            >
              Esci
            </button>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {/* KPI e azioni rapide */}
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
          <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-5">
            <p className="text-sm text-slate-400">Fatture totali</p>
            <p className="text-2xl font-bold text-white">{(fatture.length || sampleFatture.length)}</p>
            <p className="text-xs text-slate-500 mt-1">Ultime 30 gg</p>
          </div>
          <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-5">
            <p className="text-sm text-slate-400">Scadenze aperte</p>
            <p className="text-2xl font-bold text-amber-300">{scadenzeNext.length}</p>
            <p className="text-xs text-slate-500 mt-1">Rinnovi, pagamenti</p>
          </div>
          <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-5">
            <p className="text-sm text-slate-400">Servizi attivi</p>
            <p className="text-2xl font-bold text-emerald-300">{serviziAttivi.length}</p>
            <p className="text-xs text-slate-500 mt-1">OCR, KPI, integrazioni</p>
          </div>
          <div className="bg-gradient-to-r from-cyan-500/15 to-blue-600/15 border border-cyan-500/40 rounded-2xl p-5">
            <p className="text-sm text-slate-200">Azioni rapide</p>
            <div className="mt-3 space-y-2 text-sm">
              <button
                onClick={() => setShowFattureModal(true)}
                className="w-full text-left px-3 py-2 rounded-lg bg-slate-900/60 border border-slate-700/60 hover:border-cyan-500/60 transition"
              >
                Scarica tutte le fatture
              </button>
              <button
                onClick={() => alert('Funzionalità in arrivo: esportazione CSV dei costi')}
                className="w-full text-left px-3 py-2 rounded-lg bg-slate-900/60 border border-slate-700/60 hover:border-cyan-500/60 transition"
              >
                Esporta CSV costi
              </button>
              <a
                href="mailto:info@finch-ai.it?subject=Richiesta%20Assistenza"
                className="block w-full text-left px-3 py-2 rounded-lg bg-slate-900/60 border border-slate-700/60 hover:border-cyan-500/60 transition"
              >
                Apri ticket
              </a>
            </div>
          </div>
        </div>

        {/* Fatture + Costi */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
          <div className="lg:col-span-2 bg-slate-900/80 border border-slate-700/60 rounded-2xl p-6">
            <div className="flex items-center justify-between mb-4">
              <div>
                <h2 className="text-xl font-bold text-white">Fatture e pagamenti</h2>
                <p className="text-sm text-slate-400">Ultime 5 fatture</p>
              </div>
              <button
                onClick={() => setShowFattureModal(true)}
                className="px-3 py-1.5 text-sm text-cyan-300 border border-cyan-500/40 rounded-lg hover:bg-cyan-500/10 transition"
              >
                Vedi tutte
              </button>
            </div>
            <div className="space-y-3">
              {fattureRecenti.map((f) => (
                <div key={f.id} className="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl hover:bg-slate-800 transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-10 h-10 bg-cyan-500/10 rounded-lg flex items-center justify-center">
                      <svg className="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div>
                      <p className="font-semibold text-white">{f.numero}</p>
                      <p className="text-xs text-slate-400">{f.data} · {f.importo}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className={`px-3 py-1 rounded-full text-xs font-semibold ${statoBadge(f.stato)}`}>{f.stato}</span>
                    <button
                      onClick={() => downloadFattura(f.id, f.filename)}
                      className="px-3 py-2 text-sm bg-cyan-500/10 text-cyan-300 rounded-lg hover:bg-cyan-500/20 transition flex items-center gap-1"
                    >
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      PDF
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-6 space-y-4">
            <div>
              <h2 className="text-xl font-bold text-white">Costi e utilizzo</h2>
              <p className="text-sm text-slate-400">Breakdown ultimo mese</p>
            </div>
            <div className="space-y-3 text-sm">
              <div className="flex justify-between text-slate-300">
                <span>Costo mese</span><span className="font-semibold text-white">{kpiCosti.costoMese}</span>
              </div>
              <div className="flex justify-between text-slate-300">
                <span>Costo per pagina</span><span className="font-semibold text-white">{kpiCosti.costoPagina}</span>
              </div>
              <div className="flex justify-between text-slate-300">
                <span>Costo training</span><span className="font-semibold text-white">{kpiCosti.costoTraining}</span>
              </div>
              <div className="flex justify-between text-slate-300">
                <span>Storage modelli</span><span className="font-semibold text-white">{kpiCosti.storage}</span>
              </div>
            </div>
            <div className="space-y-2">
              <p className="text-xs text-slate-400">Distribuzione costi</p>
              <div className="w-full bg-slate-800/60 h-3 rounded-full overflow-hidden">
                <div className="h-full bg-cyan-500" style={{ width: '45%' }} />
              </div>
              <div className="w-full bg-slate-800/60 h-3 rounded-full overflow-hidden">
                <div className="h-full bg-blue-500" style={{ width: '30%' }} />
              </div>
              <div className="w-full bg-slate-800/60 h-3 rounded-full overflow-hidden">
                <div className="h-full bg-emerald-500" style={{ width: '25%' }} />
              </div>
            </div>
          </div>
        </div>

        {/* Servizi + Scadenze + Supporto */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-6 lg:col-span-2">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold text-white">Servizi attivi</h2>
              <span className="text-sm text-slate-400">{serviziAttivi.length} servizi</span>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {serviziAttivi.map((s) => (
                <div key={s.id} className="p-5 bg-slate-800/50 rounded-xl border border-slate-700/50">
                  <div className="flex items-start justify-between mb-3">
                    <div>
                      <h3 className="font-bold text-white mb-1">{s.nome}</h3>
                      <p className="text-sm text-slate-400">{s.descrizione}</p>
                    </div>
                    <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                      s.stato === 'attivo'
                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                        : 'bg-amber-500/10 text-amber-300 border border-amber-500/30'
                    }`}>
                      {s.stato}
                    </span>
                  </div>
                  <p className="text-xs text-slate-500">Attivo dal: {s.dataAttivazione}</p>
                </div>
              ))}
            </div>
          </div>

          <div className="space-y-4">
            <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-5">
              <div className="flex items-center justify-between mb-3">
                <h2 className="text-lg font-bold text-white">Scadenze</h2>
                <span className="text-sm text-slate-400">{scadenzeNext.length}</span>
              </div>
              <div className="space-y-2">
                {scadenzeNext.map((s) => (
                  <div key={s.id} className="p-3 rounded-lg bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
                    <div>
                      <p className="text-sm text-white">{s.descrizione}</p>
                      <p className="text-xs text-slate-400">{s.data}</p>
                    </div>
                    <span className={`px-2 py-1 rounded-full text-[11px] font-semibold ${
                      s.urgente ? 'bg-red-500/10 text-red-400 border border-red-500/30' : 'bg-amber-500/10 text-amber-300 border border-amber-500/30'
                    }`}>
                      {s.urgente ? 'Urgente' : 'Programmata'}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-slate-900/80 border border-slate-700/60 rounded-2xl p-5">
              <h2 className="text-lg font-bold text-white mb-3">Supporto</h2>
              <div className="space-y-2">
                <a href="mailto:info@finch-ai.it" className="block px-3 py-2 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-sm text-slate-300 transition">
                  Email: info@finch-ai.it
                </a>
                <a href="https://wa.me/393287171587" target="_blank" rel="noopener noreferrer" className="block px-3 py-2 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-sm text-slate-300 transition">
                  WhatsApp: +39 328 717 1587
                </a>
                <a href="tel:+393287171587" className="block px-3 py-2 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-sm text-slate-300 transition">
                  Telefono: +39 328 717 1587
                </a>
              </div>
              <p className="text-xs text-slate-500 mt-3">Assistenza Lun-Ven 9:00-18:00 · Risposta entro 24h</p>
            </div>
          </div>
        </div>
      </main>

      {/* Modali */}
      {showProfiloModal && (
        <ProfiloModal
          user={user}
          onClose={() => setShowProfiloModal(false)}
          onSave={(updatedUser) => setUser(updatedUser)}
        />
      )}

      {showFattureModal && (
        <FattureModal
          fatture={fatture.length ? fatture : sampleFatture}
          onClose={() => setShowFattureModal(false)}
          onDownload={downloadFattura}
        />
      )}
    </div>
  );
}
