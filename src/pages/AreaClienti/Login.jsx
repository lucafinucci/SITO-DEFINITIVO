import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';

export default function Login({ onLoginSuccess }) {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    otp: '',
    remember: false
  });
  const [status, setStatus] = useState({ type: '', message: '' });
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const canvasRef = useRef(null);

  // Sfondo rete neurale (canvas)
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d', { alpha: true });
    let w, h, nodes, rafId;
    const MAX_SPEED = 0.3;

    const resize = () => {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
      const count = Math.min(180, Math.floor((w * h) / 11000)); // più nodi e rete più fitta
      const linkDist = Math.min(250, Math.max(170, Math.min(w, h) * 0.27)); // link più visibili
      canvas.dataset.linkDist = linkDist;
      nodes = Array.from({ length: count }).map(() => ({
        x: Math.random() * w,
        y: Math.random() * h,
        vx: (Math.random() - 0.5) * MAX_SPEED,
        vy: (Math.random() - 0.5) * MAX_SPEED,
        r: Math.random() * 1.6 + 0.8,
      }));
    };

    const draw = () => {
      const linkDist = Number(canvas.dataset.linkDist || 140);
      ctx.clearRect(0, 0, w, h);
      ctx.fillStyle = 'rgba(4, 10, 20, 0.7)'; // blu ancora più scuro
      ctx.fillRect(0, 0, w, h);

      // glow radiale al centro come home
      const rg = ctx.createRadialGradient(w * 0.5, h * 0.28, 0, w * 0.5, h * 0.28, Math.max(w, h) * 0.9);
      rg.addColorStop(0, 'rgba(23,162,255,0.28)');
      rg.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = rg;
      ctx.fillRect(0, 0, w, h);

      // nodi
      ctx.fillStyle = 'rgba(56, 189, 248, 1)'; // nodi più luminosi
      for (const n of nodes) {
        n.x += n.vx;
        n.y += n.vy;
        if (n.x < 0 || n.x > w) n.vx *= -1;
        if (n.y < 0 || n.y > h) n.vy *= -1;
        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r * 1.3, 0, Math.PI * 2); // nodi più grandi
        ctx.fill();
      }

      // link
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

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus({ type: '', message: '' });
    setLoading(true);

    try {
      // MOCK LOGIN per sviluppo locale (quando PHP non disponibile)
      // In produzione su Aruba, decommentare la chiamata API sotto

      // Utenti demo
      const users = {
        'admin': {
          password: 'admin123',
          otp: '123456',
          name: 'Demo Admin',
          email: 'admin',
          azienda: 'Finch-AI Demo'
        },
        'demo@finch-ai.it': {
          password: 'demo123',
          otp: '123456',
          name: 'Cliente Demo',
          email: 'demo@finch-ai.it',
          azienda: 'Azienda Demo Srl'
        }
      };

      // Simula delay di rete
      await new Promise(resolve => setTimeout(resolve, 800));

      // Verifica credenziali
      const user = users[formData.email];

      if (!user || user.password !== formData.password) {
        throw new Error('Credenziali non valide');
      }

      if (formData.otp && formData.otp !== user.otp) {
        throw new Error('Codice MFA non valido');
      }

      // Login riuscito
      setStatus({ type: 'success', message: 'Accesso eseguito! Reindirizzamento...' });

      const userData = {
        name: user.name,
        email: user.email,
        azienda: user.azienda
      };

      localStorage.setItem('user', JSON.stringify(userData));

      // Callback al componente padre
      if (onLoginSuccess) {
        onLoginSuccess(userData);
      }

      // Redirect alla dashboard
      setTimeout(() => {
        navigate('/area-clienti/dashboard');
      }, 500);

      /*
      // === VERSIONE CON BACKEND PHP (decommentare su Aruba) ===
      const response = await fetch('/api/auth/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({
          email: formData.email,
          password: formData.password,
          otp: formData.otp,
          remember: formData.remember
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || 'Credenziali non valide');
      }

      setStatus({ type: 'success', message: 'Accesso eseguito! Reindirizzamento...' });

      if (data.user) {
        localStorage.setItem('user', JSON.stringify(data.user));
      }

      if (onLoginSuccess) {
        onLoginSuccess(data.user);
      }

      setTimeout(() => {
        navigate('/area-clienti/dashboard');
      }, 500);
      */

    } catch (error) {
      setStatus({
        type: 'error',
        message: error.message || 'Errore durante l\'accesso. Riprova.'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  return (
    <div className="relative min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 overflow-hidden text-slate-200">
      <canvas
        ref={canvasRef}
        className="fixed inset-0 -z-10 h-full w-full"
        aria-hidden="true"
      />
      <div className="pointer-events-none fixed inset-0 -z-10">
        <div className="absolute inset-0 opacity-65 [background:linear-gradient(120deg,#040a14_20%,#08101f_60%,#050c18_90%)]" />
        <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[length:100%_24px] mix-blend-overlay opacity-55" />
      </div>

      <div className="relative w-full max-w-md">
        {/* Card Login */}
        <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-700/60 rounded-3xl shadow-2xl p-8">
          {/* Header */}
          <div className="text-center mb-8">
            <a href="/" className="inline-block mb-6">
              <div className="relative inline-flex items-center justify-center p-4 bg-white rounded-2xl shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 hover:scale-105">
                <img
                  src="/assets/images/LOGO.png"
                  alt="Finch-AI"
                  className="h-16 w-auto"
                />
              </div>
            </a>

            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-cyan-500/50 bg-cyan-500/10 mb-4">
              <svg className="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <span className="text-sm font-semibold text-cyan-300">Accesso riservato</span>
            </div>

            <h1 className="text-2xl font-bold text-white mb-2">Area Clienti Finch-AI</h1>
            <p className="text-slate-400 text-sm">Inserisci le credenziali per accedere all'area riservata.</p>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-slate-300 mb-2">
                Email aziendale o Username
              </label>
              <input
                id="email"
                name="email"
                type="text"
                value={formData.email}
                onChange={handleChange}
                placeholder="admin"
                autoComplete="username"
                required
                className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
              />
            </div>

            {/* Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-slate-300 mb-2">
                Password
              </label>
              <input
                id="password"
                name="password"
                type="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="••••••••"
                autoComplete="current-password"
                required
                className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
              />
            </div>

            {/* OTP (opzionale) */}
            <div>
              <label htmlFor="otp" className="block text-sm font-medium text-slate-300 mb-2">
                Codice MFA (OTP)
                <span className="text-slate-500 font-normal ml-1">(opzionale)</span>
              </label>
              <input
                id="otp"
                name="otp"
                type="text"
                value={formData.otp}
                onChange={handleChange}
                placeholder="123 456"
                inputMode="numeric"
                autoComplete="one-time-code"
                className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
              />
            </div>

            {/* Remember me */}
            <div className="flex items-center">
              <input
                id="remember"
                name="remember"
                type="checkbox"
                checked={formData.remember}
                onChange={handleChange}
                className="w-4 h-4 text-cyan-500 bg-slate-800 border-slate-700 rounded focus:ring-2 focus:ring-cyan-500"
              />
              <label htmlFor="remember" className="ml-2 text-sm text-slate-300">
                Ricordami su questo dispositivo
              </label>
            </div>

            {/* Status Message */}
            {status.message && (
              <div className={`p-3 rounded-lg text-sm ${
                status.type === 'success'
                  ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-300'
                  : 'bg-red-500/10 border border-red-500/30 text-red-300'
              }`}>
                {status.message}
              </div>
            )}

            {/* Buttons */}
            <div className="space-y-3">
              <button
                type="submit"
                disabled={loading}
                className="w-full py-3 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold rounded-xl shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
              >
                {loading ? 'Accesso in corso...' : 'Accedi'}
              </button>

              <div className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/60 text-sm text-slate-200">
                <p className="font-semibold mb-2 text-white">Recupero credenziali</p>
                <div className="space-y-2">
                  <a
                    href="mailto:info@finch-ai.it?subject=Recupero%20credenziali"
                    className="block px-3 py-2 rounded-lg bg-slate-900/60 border border-slate-700 hover:border-cyan-500/60 text-cyan-300 transition"
                  >
                    Recupera via email
                  </a>
                  <a
                    href="#"
                    className="block px-3 py-2 rounded-lg bg-slate-900/60 border border-slate-700 hover:border-cyan-500/60 text-cyan-300 transition"
                  >
                    Reset password
                  </a>
                  <a
                    href="#"
                    className="block px-3 py-2 rounded-lg bg-slate-900/60 border border-slate-700 hover:border-cyan-500/60 text-cyan-300 transition"
                  >
                    Recupera OTP / MFA
                  </a>
                </div>
              </div>

              <a
                href="mailto:info@finch-ai.it?subject=Supporto%20Area%20Clienti"
                className="block w-full py-3 px-4 text-center border border-slate-700 text-slate-300 font-medium rounded-xl hover:bg-slate-800/50 transition-all"
              >
                Hai bisogno di aiuto?
              </a>
            </div>
          </form>

          {/* Footer note */}
          <div className="mt-6 p-3 bg-slate-800/30 border border-slate-700/50 rounded-lg">
            <p className="text-xs text-slate-400 leading-relaxed">
              <strong className="text-slate-300">Suggerimento:</strong> Abilita MFA per maggiore sicurezza.
              Per problemi di accesso contattaci a{' '}
              <a href="mailto:info@finch-ai.it" className="text-cyan-400 hover:text-cyan-300">
                info@finch-ai.it
              </a>
            </p>
          </div>
        </div>

        {/* Link home */}
        <div className="mt-6 text-center">
          <a
            href="/"
            className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Torna alla home
          </a>
        </div>
      </div>
    </div>
  );
}
