export default function FattureModal({ fatture, onClose, onDownload }) {
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

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div className="bg-slate-900 border border-slate-700 rounded-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden shadow-2xl flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b border-slate-700">
          <div>
            <h2 className="text-2xl font-bold text-white">Tutte le Fatture</h2>
            <p className="text-sm text-slate-400 mt-1">{fatture.length} fatture totali</p>
          </div>
          <button
            onClick={onClose}
            className="text-slate-400 hover:text-white transition-colors"
          >
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Lista Fatture */}
        <div className="overflow-y-auto p-6 space-y-3">
          {fatture.map((f) => (
            <div
              key={f.id}
              className="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl hover:bg-slate-800 transition-colors border border-slate-700/50"
            >
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
                <span className={`px-3 py-1 rounded-full text-xs font-semibold ${statoBadge(f.stato)}`}>
                  {f.stato}
                </span>
                <button
                  onClick={() => onDownload(f.id, f.filename)}
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

        {/* Footer */}
        <div className="p-6 border-t border-slate-700 bg-slate-900/50">
          <button
            onClick={onClose}
            className="w-full py-3 px-4 bg-slate-800 text-white font-medium rounded-xl hover:bg-slate-700 transition-all"
          >
            Chiudi
          </button>
        </div>
      </div>
    </div>
  );
}
