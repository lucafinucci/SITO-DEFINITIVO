function Contatti() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-white">
      <section className="pt-32 pb-20 px-4">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
              Demo
            </span>
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">
                Inizia la Trasformazione
              </span>
            </h1>
            <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
              Scopri come Finch-AI può ottimizzare i tuoi processi in 10 minuti
            </p>
          </div>

          {/* Contact Options */}
          <div className="grid lg:grid-cols-3 gap-6 mb-12">
            {/* Demo Live */}
            <div className="group relative overflow-hidden rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 transition-all hover:border-cyan-500/50 hover:shadow-[0_0_40px_rgba(34,211,238,0.2)]">
              <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(34,211,238,0.1),transparent)]" />
              </div>

              <div className="relative text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 mb-6">
                  <svg className="h-8 w-8 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                </div>
                <h3 className="text-xl font-bold text-white mb-3">Demo Live</h3>
                <p className="text-sm text-slate-300/90 mb-6">
                  Sessione personalizzata di 30 minuti con un nostro esperto. Vedi Finch-AI in azione sui tuoi documenti.
                </p>
                <a
                  href="mailto:info@finch-ai.it?subject=Richiesta%20Demo%20Finch-AI&body=Buongiorno%2C%0A%0AVorrei%20prenotare%20una%20demo%20personalizzata%20di%20Finch-AI.%0A%0AAzienda%3A%20%0ASettore%3A%20%0ANumero%20dipendenti%3A%20%0ATelefono%3A%20%0A%0AGrazie"
                  className="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
                >
                  Prenota Demo
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </a>
              </div>
            </div>

            {/* Whitepaper */}
            <div className="group relative overflow-hidden rounded-3xl border border-purple-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 transition-all hover:border-purple-500/50 hover:shadow-[0_0_40px_rgba(168,85,247,0.2)]">
              <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(168,85,247,0.1),transparent)]" />
              </div>

              <div className="relative text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/30 mb-6">
                  <svg className="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                </div>
                <h3 className="text-xl font-bold text-white mb-3">Whitepaper Gratuito</h3>
                <p className="text-sm text-slate-300/90 mb-6">
                  "AI Documentale per il Manufacturing: Guida Pratica 2025". Casi d'uso, ROI, implementazione.
                </p>
                <a
                  href="mailto:info@finch-ai.it?subject=Richiesta%20Whitepaper&body=Buongiorno%2C%0A%0AVorrei%20ricevere%20il%20whitepaper%20%22AI%20Documentale%20per%20il%20Manufacturing%22.%0A%0ANome%3A%20%0AAzienda%3A%20%0AEmail%3A%20%0A%0AGrazie"
                  className="inline-flex items-center justify-center gap-2 w-full rounded-xl border border-purple-500/50 bg-purple-500/10 px-5 py-3 font-semibold text-purple-300 transition hover:bg-purple-500/20 hover:border-purple-500/70"
                >
                  Scarica Gratis
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                </a>
              </div>
            </div>

            {/* Contatto Veloce */}
            <div className="group relative overflow-hidden rounded-3xl border border-emerald-500/30 bg-gradient-to-br from-slate-900/60 to-slate-900/40 backdrop-blur p-8 transition-all hover:border-emerald-500/50 hover:shadow-[0_0_40px_rgba(16,185,129,0.2)]">
              <div className="absolute -inset-px opacity-0 group-hover:opacity-100 transition-opacity">
                <div className="h-full w-full bg-[radial-gradient(600px_300px_at_50%_0,rgba(16,185,129,0.1),transparent)]" />
              </div>

              <div className="relative text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 mb-6">
                  <svg className="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </div>
                <h3 className="text-xl font-bold text-white mb-3">Parla con un Esperto</h3>
                <p className="text-sm text-slate-300/90 mb-6">
                  Hai domande specifiche? Parliamo del tuo caso d'uso e troviamo la soluzione migliore.
                </p>
                <a
                  href="mailto:info@finch-ai.it?subject=Richiesta%20Informazioni&body=Buongiorno%2C%0A%0AVorrei%20maggiori%20informazioni%20su%20Finch-AI.%0A%0ANome%3A%20%0AAzienda%3A%20%0ATelefono%3A%20%0A%0ADescrizione%20esigenza%3A%0A%0A%0AGrazie"
                  className="inline-flex items-center justify-center gap-2 w-full rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-5 py-3 font-semibold text-emerald-300 transition hover:bg-emerald-500/20 hover:border-emerald-500/70"
                >
                  Contattaci
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </a>
              </div>
            </div>
          </div>

          {/* Trust Indicators */}
          <div className="grid sm:grid-cols-3 gap-6 text-center mb-16">
            <div className="p-6 rounded-2xl bg-slate-900/40 border border-slate-700/50">
              <div className="text-3xl font-bold text-cyan-400 mb-2">10 min</div>
              <div className="text-sm text-slate-400">Setup demo personalizzata</div>
            </div>
            <div className="p-6 rounded-2xl bg-slate-900/40 border border-slate-700/50">
              <div className="text-3xl font-bold text-cyan-400 mb-2">2-4 sett</div>
              <div className="text-sm text-slate-400">Deployment completo</div>
            </div>
            <div className="p-6 rounded-2xl bg-slate-900/40 border border-slate-700/50">
              <div className="text-3xl font-bold text-cyan-400 mb-2">ROI 6 mesi</div>
              <div className="text-sm text-slate-400">Return on Investment medio</div>
            </div>
          </div>

          {/* Contact Details */}
          <div className="rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 lg:p-12">
            <h2 className="text-2xl font-bold text-white mb-8 text-center">Informazioni di Contatto</h2>
            <div className="grid md:grid-cols-3 gap-8">
              <div className="text-center md:text-left">
                <div className="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/30 mb-4">
                  <svg className="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <h3 className="text-lg font-bold text-white mb-2">Email</h3>
                <a href="mailto:info@finch-ai.it" className="text-slate-300 hover:text-cyan-400 transition-colors block mb-1">
                  info@finch-ai.it
                </a>
              </div>

              <div className="text-center md:text-left">
                <div className="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/30 mb-4">
                  <svg className="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
                <h3 className="text-lg font-bold text-white mb-2">Telefono</h3>
                <a href="tel:+393287171587" className="text-slate-300 hover:text-cyan-400 transition-colors block mb-1">
                  +39 328 717 1587
                </a>
                <a href="tel:+41764366624" className="text-slate-300 hover:text-cyan-400 transition-colors block mb-1">
                  +41 76 436 6624
                </a>
                <a href="tel:+393756475087" className="text-slate-300 hover:text-cyan-400 transition-colors block mb-1">
                  +39 375 647 5087
                </a>
                <p className="text-sm text-slate-500">Lun-Ven 9:00-18:00</p>
              </div>

              <div className="text-center md:text-left">
                <div className="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/30 mb-4">
                  <svg className="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <h3 className="text-lg font-bold text-white mb-2">Sede</h3>
                <p className="text-slate-300">Via Enrico Mattei, 18</p>
                <p className="text-slate-300">67043 Celano (AQ)</p>
                <p className="text-slate-300">Italia</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

export default Contatti;
