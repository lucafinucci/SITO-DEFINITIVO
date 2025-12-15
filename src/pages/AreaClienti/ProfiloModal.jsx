import { useState } from 'react';

export default function ProfiloModal({ user, onClose, onSave }) {
  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    azienda: user?.azienda || '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  const [status, setStatus] = useState({ type: '', message: '' });
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus({ type: '', message: '' });

    // Validazione password
    if (formData.newPassword && formData.newPassword !== formData.confirmPassword) {
      setStatus({ type: 'error', message: 'Le password non coincidono' });
      return;
    }

    if (formData.newPassword && formData.newPassword.length < 6) {
      setStatus({ type: 'error', message: 'La password deve essere di almeno 6 caratteri' });
      return;
    }

    setLoading(true);

    try {
      // Simula salvataggio (in produzione chiamare API)
      await new Promise(resolve => setTimeout(resolve, 1000));

      const updatedUser = {
        name: formData.name,
        email: formData.email,
        azienda: formData.azienda
      };

      localStorage.setItem('user', JSON.stringify(updatedUser));

      setStatus({ type: 'success', message: 'Profilo aggiornato con successo!' });

      setTimeout(() => {
        onSave(updatedUser);
        onClose();
      }, 1500);

    } catch (error) {
      setStatus({ type: 'error', message: 'Errore durante il salvataggio' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div className="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-bold text-white">Gestione Profilo</h2>
          <button
            onClick={onClose}
            className="text-slate-400 hover:text-white transition-colors"
          >
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Nome */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-2">
              Nome completo
            </label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
              required
            />
          </div>

          {/* Email */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-2">
              Email
            </label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
              required
            />
          </div>

          {/* Azienda */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-2">
              Azienda
            </label>
            <input
              type="text"
              name="azienda"
              value={formData.azienda}
              onChange={handleChange}
              className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
            />
          </div>

          {/* Divider */}
          <div className="pt-4 border-t border-slate-700">
            <p className="text-sm font-medium text-slate-300 mb-4">Cambia Password (opzionale)</p>
          </div>

          {/* Password attuale */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-2">
              Password attuale
            </label>
            <input
              type="password"
              name="currentPassword"
              value={formData.currentPassword}
              onChange={handleChange}
              placeholder="••••••••"
              className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
            />
          </div>

          {/* Nuova password */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-2">
              Nuova password
            </label>
            <input
              type="password"
              name="newPassword"
              value={formData.newPassword}
              onChange={handleChange}
              placeholder="••••••••"
              className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
            />
          </div>

          {/* Conferma password */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-2">
              Conferma nuova password
            </label>
            <input
              type="password"
              name="confirmPassword"
              value={formData.confirmPassword}
              onChange={handleChange}
              placeholder="••••••••"
              className="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
            />
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
          <div className="flex gap-3 pt-2">
            <button
              type="button"
              onClick={onClose}
              className="flex-1 py-3 px-4 border border-slate-700 text-slate-300 font-medium rounded-xl hover:bg-slate-800 transition-all"
            >
              Annulla
            </button>
            <button
              type="submit"
              disabled={loading}
              className="flex-1 py-3 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold rounded-xl shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
            >
              {loading ? 'Salvataggio...' : 'Salva Modifiche'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
