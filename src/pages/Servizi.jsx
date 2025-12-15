function Servizi() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-white">
      <section className="pt-32 pb-20 px-4">
        <div className="mx-auto max-w-6xl">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 rounded-full border border-purple-500/30 bg-purple-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-purple-300 mb-6">
              Soluzioni
            </span>
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">
                Soluzioni AI per Ogni Esigenza
              </span>
            </h1>
            <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
              Tre moduli integrabili che trasformano documenti, produzione e finanza in un ecosistema intelligente
            </p>
          </div>

          {/* Moduli Dettagliati */}
          <div className="space-y-12">
            {/* Document Intelligence */}
            <div className="rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-8 lg:p-12">
              <div className="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                  <div className="inline-flex items-center gap-3 mb-6">
                    <div className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-3 text-cyan-300">
                      <svg viewBox="0 0 24 24" className="h-10 w-10">
                        <path d="M4 4h10l6 6v10a2 2 0 0 1-2 2H4V4z" fill="none" stroke="currentColor" strokeWidth="1.8"/>
                        <path d="M14 4v6h6" fill="none" stroke="currentColor" strokeWidth="1.8"/>
                      </svg>
                    </div>
                    <h2 className="text-3xl font-bold text-white">Document Intelligence</h2>
                  </div>

                  <p className="text-lg text-slate-300/90 mb-6 leading-relaxed">
                    Automazione completa del ciclo documentale: OCR avanzato, estrazione dati strutturati,
                    validazione intelligente e integrazione diretta con i tuoi sistemi.
                  </p>

                  <div className="space-y-3 mb-8">
                    {[
                      "OCR multi-formato: DDT, fatture, ordini, bolle, documenti doganali",
                      "Validazione automatica con regole business personalizzate",
                      "Integrazione bidirezionale ERP/Gestionale (SAP, Zucchetti, etc.)",
                      "Gestione eccezioni con workflow approvativo",
                      "Archiviazione automatica conforme normative fiscali"
                    ].map((feature, i) => (
                      <div key={i} className="flex items-start gap-3">
                        <svg className="h-6 w-6 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                        <span className="text-slate-300">{feature}</span>
                      </div>
                    ))}
                  </div>

                  <div className="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 border border-cyan-500/30 px-4 py-2 text-sm font-semibold text-cyan-300">
                    <span className="h-2 w-2 rounded-full bg-cyan-400 animate-pulse" />
                    70% riduzione tempo elaborazione
                  </div>
                </div>

                <div className="space-y-4">
                  <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
                    <div className="text-sm font-semibold text-cyan-300 uppercase tracking-wider mb-2">Casi d'uso</div>
                    <ul className="space-y-2 text-sm text-slate-300">
                      <li>• Manufacturing: automazione DDT in/out + integrazione SAP</li>
                      <li>• Logistica: gestione documenti multi-vettore</li>
                      <li>• Retail: ordini fornitori da email/PDF automatici</li>
                      <li>• Servizi: fatturazione automatica da timesheet</li>
                    </ul>
                  </div>

                  <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
                    <div className="text-sm font-semibold text-cyan-300 uppercase tracking-wider mb-2">Metriche</div>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <div className="text-2xl font-bold text-cyan-400">99.2%</div>
                        <div className="text-xs text-slate-400">Accuratezza dati</div>
                      </div>
                      <div>
                        <div className="text-2xl font-bold text-cyan-400">+1000</div>
                        <div className="text-xs text-slate-400">Doc/giorno</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Production Analytics */}
            <div className="rounded-3xl border border-purple-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-8 lg:p-12">
              <div className="grid lg:grid-cols-2 gap-8 items-center">
                <div className="order-2 lg:order-1">
                  <div className="space-y-4">
                    <div className="rounded-2xl border border-purple-500/30 bg-gradient-to-br from-purple-500/10 to-pink-500/10 p-6">
                      <div className="text-sm font-semibold text-purple-300 uppercase tracking-wider mb-2">KPI Monitorati</div>
                      <ul className="space-y-2 text-sm text-slate-300">
                        <li>• OEE (Overall Equipment Effectiveness) real-time</li>
                        <li>• Disponibilità macchine e tempi fermo</li>
                        <li>• Performance vs. capacità teorica</li>
                        <li>• Quality rate e scarti per lotto/turno</li>
                        <li>• Analisi colli di bottiglia produttivi</li>
                      </ul>
                    </div>

                    <div className="rounded-2xl border border-purple-500/30 bg-gradient-to-br from-purple-500/10 to-pink-500/10 p-6">
                      <div className="text-sm font-semibold text-purple-300 uppercase tracking-wider mb-2">Funzionalità Avanzate</div>
                      <ul className="space-y-2 text-sm text-slate-300">
                        <li>• Previsioni manutenzione predittiva</li>
                        <li>• Alert anomalie automatici</li>
                        <li>• Dashboard personalizzate per reparto</li>
                        <li>• Export report automatici (Excel/PDF)</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div className="order-1 lg:order-2">
                  <div className="inline-flex items-center gap-3 mb-6">
                    <div className="rounded-xl border border-purple-500/30 bg-purple-500/10 p-3 text-purple-300">
                      <svg viewBox="0 0 24 24" className="h-10 w-10">
                        <path d="M4 19h16M6 16V8m6 8V5m6 11v-7" fill="none" stroke="currentColor" strokeWidth="1.8"/>
                      </svg>
                    </div>
                    <h2 className="text-3xl font-bold text-white">Production Analytics</h2>
                  </div>

                  <p className="text-lg text-slate-300/90 mb-6 leading-relaxed">
                    Trasforma i dati di produzione in insight azionabili. Dashboard real-time,
                    analisi predittiva e ottimizzazione continua dei processi.
                  </p>

                  <div className="space-y-3 mb-8">
                    {[
                      "Integrazione MES/SCADA per raccolta dati automatica",
                      "KPI real-time: OEE, disponibilità, performance, qualità",
                      "Machine learning per anomalie e manutenzione predittiva",
                      "Alert intelligenti su WhatsApp/Email/Telegram",
                      "Report automatici personalizzati per ogni ruolo"
                    ].map((feature, i) => (
                      <div key={i} className="flex items-start gap-3">
                        <svg className="h-6 w-6 text-purple-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                        <span className="text-slate-300">{feature}</span>
                      </div>
                    ))}
                  </div>

                  <div className="inline-flex items-center gap-2 rounded-full bg-purple-500/10 border border-purple-500/30 px-4 py-2 text-sm font-semibold text-purple-300">
                    <span className="h-2 w-2 rounded-full bg-purple-400 animate-pulse" />
                    3x velocità decisioni strategiche
                  </div>
                </div>
              </div>
            </div>

            {/* Financial Control */}
            <div className="rounded-3xl border border-emerald-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-8 lg:p-12">
              <div className="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                  <div className="inline-flex items-center gap-3 mb-6">
                    <div className="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-emerald-300">
                      <svg viewBox="0 0 24 24" className="h-10 w-10">
                        <path d="M7 8h10M4 12h16M7 16h10" fill="none" stroke="currentColor" strokeWidth="1.8"/>
                      </svg>
                    </div>
                    <h2 className="text-3xl font-bold text-white">Financial Control</h2>
                  </div>

                  <p className="text-lg text-slate-300/90 mb-6 leading-relaxed">
                    Unifica flussi finanziari e operativi per controllo totale. Integrazione ERP,
                    riconciliazione automatica, forecast intelligenti basati su AI.
                  </p>

                  <div className="space-y-3 mb-8">
                    {[
                      "Integrazione universale ERP (SAP, Zucchetti, TeamSystem, etc.)",
                      "Riconciliazione automatica documenti-pagamenti-movimenti",
                      "Cash-flow forecast con machine learning",
                      "Analisi marginalità per cliente/prodotto/commessa",
                      "Dashboard finanziaria unificata con drill-down operativo"
                    ].map((feature, i) => (
                      <div key={i} className="flex items-start gap-3">
                        <svg className="h-6 w-6 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                        <span className="text-slate-300">{feature}</span>
                      </div>
                    ))}
                  </div>

                  <div className="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 px-4 py-2 text-sm font-semibold text-emerald-300">
                    <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                    100% sincronizzazione automatica
                  </div>
                </div>

                <div className="space-y-4">
                  <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                    <div className="text-sm font-semibold text-emerald-300 uppercase tracking-wider mb-2">Controlli Automatici</div>
                    <ul className="space-y-2 text-sm text-slate-300">
                      <li>• Verifica congruenza ordine-DDT-fattura-pagamento</li>
                      <li>• Alert scadenze pagamenti clienti/fornitori</li>
                      <li>• Monitoraggio esposizione finanziaria per cliente</li>
                      <li>• Controllo margini commessa real-time</li>
                    </ul>
                  </div>

                  <div className="rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 p-6">
                    <div className="text-sm font-semibold text-emerald-300 uppercase tracking-wider mb-2">Risultati Tipici</div>
                    <div className="space-y-3">
                      <div className="flex items-center justify-between">
                        <span className="text-sm text-slate-300">Chiusura mensile</span>
                        <span className="text-emerald-400 font-bold">10→2 giorni</span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-sm text-slate-300">Errori riconciliazione</span>
                        <span className="text-emerald-400 font-bold">-95%</span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-sm text-slate-300">Visibilità cash-flow</span>
                        <span className="text-emerald-400 font-bold">Real-time</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* CTA Finale */}
          <div className="mt-16 text-center">
            <div className="inline-block rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 backdrop-blur p-10">
              <h3 className="text-2xl font-bold text-white mb-4">Scegli il Modulo Giusto per Te</h3>
              <p className="text-slate-300/90 mb-6 max-w-2xl">
                Puoi partire da un singolo modulo e scalare progressivamente, oppure implementare
                l'ecosistema completo per massimizzare i benefici.
              </p>
              <a
                href="#contatti"
                className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-4 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
              >
                Richiedi Consulenza Gratuita
                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M5 12h14M13 5l7 7-7 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

export default Servizi;
