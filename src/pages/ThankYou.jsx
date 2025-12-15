function ThankYou() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-white flex items-center justify-center px-4">
      <div className="mx-auto max-w-2xl text-center">
        <div className="mb-8">
          <div className="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-cyan-500/20 to-blue-500/20 border border-cyan-500/30 mb-6">
            <svg className="h-12 w-12 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
            <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">
              Grazie!
            </span>
          </h1>

          <p className="text-lg sm:text-xl text-slate-300/90 mb-4">
            Abbiamo ricevuto la tua richiesta.
          </p>
          <p className="text-slate-400">
            Il nostro team ti contatterà entro 24 ore per discutere le tue esigenze.
          </p>
        </div>

        <div className="rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 mb-8">
          <h2 className="text-xl font-bold text-white mb-4">Nel Frattempo</h2>
          <div className="space-y-4">
            <a
              href="/servizi.html"
              className="flex items-center justify-between p-4 rounded-xl bg-slate-800/50 border border-slate-700/40 hover:border-cyan-500/50 hover:bg-slate-800/70 transition-all group"
            >
              <div className="flex items-center gap-3">
                <svg className="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span className="text-slate-300 group-hover:text-white transition-colors">Esplora le Soluzioni</span>
              </div>
              <svg className="h-5 w-5 text-slate-500 group-hover:text-cyan-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </a>

            <a
              href="/chi-siamo.html"
              className="flex items-center justify-between p-4 rounded-xl bg-slate-800/50 border border-slate-700/40 hover:border-cyan-500/50 hover:bg-slate-800/70 transition-all group"
            >
              <div className="flex items-center gap-3">
                <svg className="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span className="text-slate-300 group-hover:text-white transition-colors">Scopri Chi Siamo</span>
              </div>
              <svg className="h-5 w-5 text-slate-500 group-hover:text-cyan-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>

        <a
          href="/"
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-4 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
        >
          Torna alla Home
          <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </a>
      </div>
    </div>
  );
}

export default ThankYou;
