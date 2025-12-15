function ChiSiamo() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-white">
      <section className="pt-32 pb-20 px-4">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
              Chi Siamo
            </span>
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">
                Per Aziende
              </span>
            </h1>
            <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
              Siamo partner tecnologico delle PMI italiane nella trasformazione digitale guidata dall'AI
            </p>
          </div>

          {/* Mission & Vision */}
          <div className="grid md:grid-cols-2 gap-8 mb-16">
            <div className="rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8">
              <div className="text-4xl mb-4">🎯</div>
              <h2 className="text-2xl font-bold text-white mb-4">La Nostra Missione</h2>
              <p className="text-slate-300/90 leading-relaxed">
                Rendere l'intelligenza artificiale accessibile e concreta per le PMI italiane.
                Non promesse futuristiche, ma soluzioni operative che generano valore dal primo giorno.
              </p>
            </div>

            <div className="rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8">
              <div className="text-4xl mb-4">🚀</div>
              <h2 className="text-2xl font-bold text-white mb-4">La Nostra Visione</h2>
              <p className="text-slate-300/90 leading-relaxed">
                Un'Italia dove ogni azienda, indipendentemente dalla dimensione,
                può competere globalmente grazie all'automazione intelligente e decisioni data-driven.
              </p>
            </div>
          </div>

          {/* Valori */}
          <div className="mb-16">
            <h2 className="text-3xl font-bold text-white mb-8 text-center">I Nostri Valori</h2>
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {[
                {
                  icon: "🔧",
                  title: "Pragmatismo",
                  desc: "Soluzioni concrete che risolvono problemi reali, non tecnologia fine a se stessa"
                },
                {
                  icon: "🤝",
                  title: "Partnership",
                  desc: "Non siamo fornitori, siamo partner del tuo successo a lungo termine"
                },
                {
                  icon: "📊",
                  title: "Trasparenza",
                  desc: "ROI chiaro, metriche misurabili, nessun costo nascosto o promesse irrealistiche"
                },
                {
                  icon: "🎓",
                  title: "Know-how",
                  desc: "Competenza tecnica profonda unita a comprensione del business manifatturiero"
                }
              ].map((value, i) => (
                <div key={i} className="rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-6 text-center">
                  <div className="text-4xl mb-3">{value.icon}</div>
                  <h3 className="text-lg font-bold text-white mb-2">{value.title}</h3>
                  <p className="text-sm text-slate-300/90 leading-relaxed">{value.desc}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Perché Sceglierci */}
          <div className="rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-10">
            <h2 className="text-3xl font-bold text-white mb-8 text-center">Perché Scegliere Finch-AI</h2>
            <div className="space-y-4">
              {[
                "Esperienza verticale nel manufacturing e logistica italiana",
                "Deploy rapido 2-4 settimane, non mesi di consulenza teorica",
                "ROI misurabile e garantito con break-even medio in 6 mesi",
                "Integrazione plug-and-play con i tuoi sistemi esistenti (SAP, Zucchetti, TeamSystem, etc.)",
                "Supporto italiano con team locale sempre disponibile",
                "Zero vendor lock-in: dati sempre tuoi, esportabili, API aperte"
              ].map((item, i) => (
                <div key={i} className="flex items-start gap-3">
                  <svg className="h-6 w-6 text-cyan-400 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span className="text-slate-300 leading-relaxed">{item}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

export default ChiSiamo;
