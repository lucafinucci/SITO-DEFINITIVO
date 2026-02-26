import React from 'react';

export default function DocAI() {
    return (
        <section id="docai" className="py-24 transition-colors duration-300 dark:bg-slate-900/50 bg-[#FDFBF7]">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                {/* HERO */}
                <div className="text-center mb-16 animate-fade-in">
                    <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold tracking-wider uppercase bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 mb-6">
                        <i className="ph ph-globe text-base"></i> SaaS Multi-tenant · Document Intelligence
                    </div>
                    <h2 className="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-slate-900 dark:text-white mb-6 leading-tight">
                        DDT da gestire ogni giorno.<br />
                        <span className="text-green-600 dark:text-green-400 relative inline-block">
                            FinCh-Ai li legge per te.
                            <span className="absolute bottom-1 left-0 right-0 h-3 bg-green-100 dark:bg-green-900/20 -z-10 rounded-lg"></span>
                        </span>
                    </h2>
                    <p className="text-lg md:text-xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto mb-10">
                        Scannerizzati, caricati, ricevuti via email — ogni DDT va aperto, letto, trascritto. <em className="font-serif italic text-slate-900 dark:text-slate-200">A mano.</em><br />
                        Document Intelligence riconosce, estrae e verifica i dati automaticamente.
                    </p>

                    <div className="flex flex-wrap justify-center gap-4">
                        {[
                            { num: "-75%", label: "Tempo risparmiato", sub: "sull'inserimento dati" },
                            { num: "90%", label: "Velocità in più", sub: "nella lavorazione DDT" },
                            { num: "24/7", label: "Elaborazione", sub: "automatica continua" }
                        ].map((stat, i) => (
                            <div key={i} className="flex items-center gap-4 px-6 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">
                                <span className="text-3xl font-serif font-bold text-green-600 dark:text-green-400">{stat.num}</span>
                                <span className="text-left text-xs text-slate-500 dark:text-slate-400 leading-tight">
                                    <strong className="text-slate-900 dark:text-slate-200">{stat.label}</strong><br />{stat.sub}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* BEFORE / AFTER */}
                <div className="grid lg:grid-cols-[1fr,auto,1fr] gap-0 items-stretch mb-24">
                    <div className="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl p-8 shadow-sm relative overflow-hidden">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-slate-300 dark:bg-slate-600"></div>
                        <div className="text-[10px] font-bold tracking-widest uppercase text-slate-400 mb-4 flex items-center gap-2">
                            <i className="ph ph-x-circle text-red-500"></i> Prima
                        </div>
                        <h3 className="text-xl font-serif font-bold text-slate-900 dark:text-white mb-4">Ore perse ogni giorno a copiare dati a mano</h3>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mb-6">Ogni DDT va aperto, letto, trascritto campo per campo nel gestionale. Errori frequenti, colli di bottiglia.</p>

                        <div className="bg-[#F9F7F2] dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-xl p-4 space-y-3">
                            {[
                                { icon: "ph-printer", text: "Scannerizza il DDT cartaceo", time: "2 min" },
                                { icon: "ph-file-pdf", text: "Apri il PDF, cerca i dati rilevanti", time: "2 min" },
                                { icon: "ph-keyboard", text: "Trascrivi nel gestionale", time: "4 min" },
                                { icon: "ph-magnifying-glass", text: "Ricontrolla per errori", time: "2 min" },
                                { icon: "ph-folder", text: "Archivia il documento", time: "1 min" }
                            ].map((step, i) => (
                                <div key={i} className="flex items-center justify-between py-2 border-b border-slate-200/50 dark:border-slate-700/50 last:border-0 text-sm text-slate-600 dark:text-slate-400">
                                    <div className="flex items-center gap-3">
                                        <i className={`ph ${step.icon} text-lg`}></i>
                                        <span>{step.text}</span>
                                    </div>
                                    <span className="font-mono text-[10px] px-2 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">{step.time}</span>
                                </div>
                            ))}
                            <div className="flex items-center justify-between pt-4 font-bold text-slate-900 dark:text-white">
                                <div className="flex items-center gap-3">
                                    <i className="ph ph-clock text-lg"></i>
                                    <span>Totale per DDT</span>
                                </div>
                                <span className="font-mono text-red-600 dark:text-red-400">~11 min</span>
                            </div>
                        </div>
                    </div>

                    <div className="flex lg:flex-col items-center justify-center p-8 gap-4 opacity-50">
                        <div className="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center shadow-lg animate-pulse lg:rotate-0 rotate-90">
                            <i className="ph ph-arrow-right text-2xl"></i>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-800/80 border-2 border-green-500/30 dark:border-green-400/30 rounded-2xl p-8 shadow-xl relative overflow-hidden">
                        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-400"></div>
                        <div className="text-[10px] font-bold tracking-widest uppercase text-green-600 dark:text-green-400 mb-4 flex items-center gap-2">
                            <i className="ph ph-check-circle"></i> Dopo — con FinCh-Ai
                        </div>
                        <h3 className="text-xl font-serif font-bold text-slate-900 dark:text-white mb-4">Dati estratti in automatico, pronti da verificare</h3>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mb-6">Scannerizzi, carichi o invii via email — il DDT viene elaborato automaticamente. Tu verifichi solo il risultato.</p>

                        <div className="bg-green-50/50 dark:bg-green-950/20 border border-green-100 dark:border-green-900/30 rounded-xl p-4">
                            <div className="flex flex-wrap items-center gap-2 mb-6">
                                {[
                                    { label: "In Elaborazione", icon: "ph-download-simple", color: "bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400" },
                                    { label: "Da Verificare", icon: "ph-eye", color: "bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400" },
                                    { label: "Verificato", icon: "ph-check-circle", color: "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400" },
                                    { label: "Trasferito", icon: "ph-upload-simple", color: "bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400" }
                                ].map((pill, i) => (
                                    <React.Fragment key={i}>
                                        <span className={`px-3 py-1 rounded-full text-[10px] font-bold flex items-center gap-1.5 ${pill.color}`}>
                                            <i className={`ph ${pill.icon}`}></i> {pill.label}
                                        </span>
                                        {i < 3 && <i className="ph ph-caret-right text-slate-300 dark:text-slate-700 text-xs"></i>}
                                    </React.Fragment>
                                ))}
                            </div>

                            <div className="grid grid-cols-2 gap-3 mb-4">
                                {[
                                    { label: "Fornitore", val: "Rossi S.r.l." },
                                    { label: "Nr. DDT", val: "2024/00847" },
                                    { label: "Data", val: "15/01/2025" },
                                    { label: "Articoli", val: "12 righe" }
                                ].map((f, i) => (
                                    <div key={i} className="bg-white dark:bg-slate-800 p-3 rounded-lg border border-green-100 dark:border-green-900/30">
                                        <div className="text-[9px] uppercase tracking-wider text-green-600 dark:text-green-400 font-bold mb-1">{f.label}</div>
                                        <div className="font-mono text-xs text-slate-900 dark:text-slate-100">{f.val}</div>
                                    </div>
                                ))}
                            </div>

                            <div className="bg-green-100 dark:bg-green-900/40 p-3 rounded-lg text-xs text-green-800 dark:text-green-300 flex items-center gap-2">
                                <i className="ph ph-shield-check text-lg"></i>
                                <span><strong>Confidence 97%</strong> · Dati estratti in 8 secondi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* HOW IT WORKS */}
                <div className="mb-24">
                    <div className="text-center mb-12">
                        <h2 className="text-3xl font-serif font-bold dark:text-white text-slate-900 mb-3">Come Funziona</h2>
                        <p className="text-slate-500 dark:text-slate-400">Dal documento al gestionale in quattro passaggi automatici</p>
                    </div>

                    <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
                        <div className="hidden lg:block absolute top-12 left-24 right-24 h-0.5 bg-gradient-to-r from-green-500 via-emerald-400 to-green-500 opacity-20 z-0"></div>
                        {[
                            { num: 1, icon: "ph-tray-arrow-down", title: "Ricezione", desc: "Scansione, upload manuale o ricezione via email. Il sistema accetta qualsiasi canale.", badge: "Scan + Upload + Email", color: "bg-green-600" },
                            { num: 2, icon: "ph-cpu", title: "Riconoscimento AI", desc: "L'AI estrae automaticamente campi, tabelle e dati con modelli addestrati sui tuoi documenti.", badge: "Document AI", color: "bg-orange-500" },
                            { num: 3, icon: "ph-check-square", title: "Verifica", desc: "L'operatore rivede i dati estratti, corregge se necessario, e conferma.", badge: "Human-in-the-loop", color: "bg-emerald-500" },
                            { num: 4, icon: "ph-rocket-launch", title: "Trasferimento", desc: "I dati verificati vengono trasferiti al gestionale via webhook o API.", badge: "API + Webhook", color: "bg-purple-500" }
                        ].map((step, i) => (
                            <div key={i} className="relative z-10 bg-white dark:bg-slate-800 p-8 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:-translate-y-1 transition-all duration-300 text-center group">
                                <div className={`w-10 h-10 ${step.color} text-white rounded-full flex items-center justify-center font-serif text-lg mx-auto mb-6`}>{step.num}</div>
                                <i className={`ph ${step.icon} text-4xl mb-4 group-hover:scale-110 transition-transform`}></i>
                                <h3 className="font-bold mb-3 dark:text-white">{step.title}</h3>
                                <p className="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-6">{step.desc}</p>
                                <span className="inline-block px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-900 text-[10px] font-bold text-slate-600 dark:text-slate-400 tracking-wider uppercase">{step.badge}</span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* FEATURES */}
                <div className="mb-24">
                    <div className="text-center mb-12">
                        <h2 className="text-3xl font-serif font-bold dark:text-white text-slate-900 mb-3">Vantaggi per il Business</h2>
                        <p className="text-slate-500 dark:text-slate-400">Efficienza e precisione per il tuo processo logistico</p>
                    </div>

                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {[
                            { icon: "ph-check-circle", title: "Dati Precisi al 100%", desc: "Elimina gli errori di trascrizione manuale. L'AI garantisce un'estrazione dati accurata e verificata.", color: "bg-green-100 dark:bg-green-900/20 text-green-600" },
                            { icon: "ph-clock-countdown", title: "Efficienza Operativa", desc: "Riduci i tempi di lavorazione fino all'80%. Libera il tuo personale da compiti ripetitivi.", color: "bg-blue-100 dark:bg-blue-900/20 text-blue-600" },
                            { icon: "ph-sliders", title: "Flessibilità Totale", desc: "Non solo DDT. Il sistema impara e si adatta a qualsiasi documento necessario alla tua azienda.", color: "bg-orange-100 dark:bg-orange-900/20 text-orange-600" },
                            { icon: "ph-plugs-connected", title: "Integrazione Invisibile", desc: "Collegamento rapido con il tuo gestionale (ERP) per un flusso dati automatico.", color: "bg-purple-100 dark:bg-purple-900/20 text-purple-600" },
                            { icon: "ph-gauge", title: "Tutto Sotto Controllo", desc: "Dashboard intuitiva per monitorare lo stato dei documenti e performance del team.", color: "bg-red-100 dark:bg-red-900/20 text-red-600" },
                            { icon: "ph-shield-check", title: "Privacy e Sicurezza Top", desc: "Dati protetti da crittografia enterprise e server ospitati esclusivamente in Europa (GDPR).", color: "bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600" }
                        ].map((f, i) => (
                            <div key={i} className="bg-white dark:bg-slate-800 p-8 rounded-2xl border border-slate-100 dark:border-slate-700 hover:shadow-lg transition-all group">
                                <div className={`w-14 h-14 rounded-2xl ${f.color} flex items-center justify-center text-3xl mb-6 group-hover:rotate-6 transition-transform`}>
                                    <i className={`ph ${f.icon}`}></i>
                                </div>
                                <h3 className="text-xl font-bold mb-3 dark:text-white">{f.title}</h3>
                                <p className="text-slate-500 dark:text-slate-400 leading-relaxed text-sm">{f.desc}</p>
                            </div>
                        ))}
                    </div>
                </div>

                {/* PRICING */}
                <div className="mb-24">
                    <div className="text-center mb-12">
                        <h2 className="text-3xl font-serif font-bold dark:text-white text-slate-900 mb-3">Piani di Abbonamento</h2>
                        <p className="text-slate-500 dark:text-slate-400">Trasparente, basato sul volume · Scegli il piano adatto a te</p>
                    </div>

                    <div className="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        {[
                            { tier: "Demo", price: "€0", period: "Prova limitata", features: ["20 pagine incluse", "€0,15/pagina extra", "1 layout", "No pooling Email"] },
                            { tier: "Basic", price: "€49", period: "€49/mese + IVA 22%", features: ["400 pagine incluse", "€0,15/pagina extra", "2 layout", "No pooling Email"] },
                            { tier: "Business", price: "€129", period: "€129/mese + IVA 22%", featured: true, features: ["1.500 pagine incluse", "€0,12/pagina extra", "5 layout", "No pooling Email"] },
                            { tier: "Professional", price: "€249", period: "€249/mese + IVA 22%", features: ["4.000 pagine incluse", "€0,09/pagina extra", "10 layout", "Si pooling Email"] },
                            { tier: "Enterprise", contact: true, features: ["12.000 pagine incluse", "€0,07/pagina extra", "Illimitati layout", "Si pooling Email"] }
                        ].map((p, i) => (
                            <div key={i} className={`relative bg-white dark:bg-slate-800 rounded-2xl p-6 border transition-all text-center ${p.featured ? 'border-green-500 dark:border-green-400 shadow-xl scale-105 z-10' : 'border-slate-100 dark:border-slate-700 shadow-sm'}`}>
                                {p.featured && (
                                    <span className="absolute -top-3 left-1/2 -translate-x-1/2 bg-green-500 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">Popolare</span>
                                )}
                                <div className="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">{p.tier}</div>
                                {p.contact ? (
                                    <a href="#contatti" className="block w-full py-2.5 bg-green-600 text-white font-bold rounded-lg text-sm hover:brightness-110 mb-2">Contattaci</a>
                                ) : (
                                    <div className="text-3xl font-serif font-bold text-green-600 dark:text-green-400 mb-1">{p.price}</div>
                                )}
                                <div className="text-[10px] text-slate-500 dark:text-slate-400 mb-8">{p.period || "Contattaci per un preventivo"}</div>

                                <ul className="space-y-4 text-left">
                                    {p.features.map((feat, k) => (
                                        <li key={k} className="flex items-center gap-2 text-[11px] text-slate-600 dark:text-slate-400">
                                            {feat.includes("No") ? <i className="ph ph-x text-slate-300 dark:text-slate-600 text-sm"></i> : <i className="ph ph-check text-green-500 text-sm"></i>}
                                            {feat}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                </div>

                {/* CTA */}
                <div className="bg-slate-900 rounded-3xl p-12 text-center text-white relative overflow-hidden shadow-2xl">
                    <div className="absolute top-0 right-0 w-96 h-96 bg-green-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div className="relative z-10">
                        <h2 className="text-3xl md:text-4xl font-serif font-bold mb-4">Smetti di ricopiare. Inizia ad automatizzare.</h2>
                        <p className="text-slate-400 text-lg mb-10 max-w-2xl mx-auto">Prova Document Intelligence gratis — automatizza il tuo primo mese senza costi.</p>

                        <div className="flex flex-wrap justify-center gap-6 mb-12">
                            {[
                                { icon: "ph-map-pin", text: "Prodotto Italiano" },
                                { icon: "ph-lock-key", text: "GDPR Compliant" },
                                { icon: "ph-lightning", text: "Attivazione Rapida" },
                                { icon: "ph-link-simple", text: "Integrazione API" }
                            ].map((b, i) => (
                                <div key={i} className="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-full text-sm text-slate-300">
                                    <i className={`ph ${b.icon} text-lg`}></i>
                                    {b.text}
                                </div>
                            ))}
                        </div>

                        <a href="mailto:info@finch-ai.it" className="inline-flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl font-bold hover:scale-105 transition-transform shadow-lg shadow-green-500/20">
                            Richiedi una Demo <i className="ph ph-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        </section>
    );
}
