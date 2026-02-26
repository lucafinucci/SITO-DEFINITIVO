import { useState } from 'react';

export default function Contact() {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        company: '',
        need: '',
        message: ''
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        const subject = `Richiesta Contatto da ${formData.name} - ${formData.company}`;
        const body = `Nome: ${formData.name}%0D%0AAzienda: ${formData.company}%0D%0AEmail: ${formData.email}%0D%0ATelefono: ${formData.phone}%0D%0AEsigenza: ${formData.need}%0D%0A%0D%0AMessaggio:%0D%0A${formData.message}`;
        window.location.href = `mailto:info@finch-ai.it?subject=${subject}&body=${body}`;
    };

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <section id="contatti" className="py-24 relative overflow-hidden transition-colors duration-300">
            {/* Background glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/5 rounded-full blur-[120px] pointer-events-none" />

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative">
                <div className="grid lg:grid-cols-2 gap-16 items-start">
                    <div className="space-y-8">
                        <div className="text-left">
                            <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-300 mb-6 font-mono">
                                Inizia la Trasformazione
                            </span>
                            <h2 className="text-4xl sm:text-5xl font-extrabold dark:text-white text-slate-900 mb-6">
                                Scopri come Finch-AI può <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-500 to-blue-600">ottimizzare i tuoi processi in 10 minuti.</span>
                            </h2>
                            <p className="text-xl text-slate-600 dark:text-slate-300/90 leading-relaxed max-w-lg">
                                Risposta garantita entro 24h lavorative.
                            </p>
                        </div>

                        <div className="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/40 backdrop-blur">
                            <h3 className="text-2xl font-bold dark:text-white text-slate-900 mb-2">Contatto diretto</h3>
                            <p className="text-slate-600 dark:text-slate-400 mb-8 font-medium">Parla con un esperto Finch-AI. Risposta in 24h lavorative. Meno campi, più sostanza.</p>

                            <div className="space-y-8">
                                {[
                                    { value: "10 min", label: "Setup demo personalizzata" },
                                    { value: "4-8 sett", label: "Deployment completo" },
                                    { value: "ROI 6 mesi", label: "Return on Investment medio" }
                                ].map((stat, i) => (
                                    <div key={i} className="flex items-center gap-4">
                                        <div className="text-xl font-bold text-cyan-600 dark:text-cyan-400 min-w-[100px]">{stat.value}</div>
                                        <div className="text-sm text-slate-500 dark:text-slate-300 font-medium">{stat.label}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="mx-auto w-full">
                        <div className="rounded-3xl border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-900/60 backdrop-blur p-8 shadow-2xl shadow-slate-200/50 dark:shadow-none">
                            <form onSubmit={handleSubmit} className="space-y-5">
                                <div className="space-y-2">
                                    <label htmlFor="name" className="text-sm font-medium text-slate-600 dark:text-slate-300">Nome e cognome *</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        required
                                        value={formData.name}
                                        onChange={handleChange}
                                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all shadow-inner"
                                        placeholder="Mario Rossi"
                                    />
                                </div>

                                <div className="grid sm:grid-cols-2 gap-5">
                                    <div className="space-y-2">
                                        <label htmlFor="email" className="text-sm font-medium text-slate-600 dark:text-slate-300">Email *</label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            required
                                            value={formData.email}
                                            onChange={handleChange}
                                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all shadow-inner"
                                            placeholder="nome@azienda.it"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label htmlFor="phone" className="text-sm font-medium text-slate-600 dark:text-slate-300">Telefono</label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            value={formData.phone}
                                            onChange={handleChange}
                                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all shadow-inner"
                                            placeholder="+39 333 1234567"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label htmlFor="company" className="text-sm font-medium text-slate-600 dark:text-slate-300">Azienda</label>
                                    <input
                                        type="text"
                                        id="company"
                                        name="company"
                                        value={formData.company}
                                        onChange={handleChange}
                                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all shadow-inner"
                                        placeholder="Ragione sociale"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label htmlFor="need" className="text-sm font-medium text-slate-600 dark:text-slate-300">Esigenza (es. documenti, KPI, integrazioni)</label>
                                    <input
                                        type="text"
                                        id="need"
                                        name="need"
                                        value={formData.need}
                                        onChange={handleChange}
                                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all shadow-inner"
                                        placeholder="Automazione DDT, dashboard KPI, integrazione ERP..."
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label htmlFor="message" className="text-sm font-medium text-slate-600 dark:text-slate-300">Messaggio *</label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="3"
                                        required
                                        value={formData.message}
                                        onChange={handleChange}
                                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all resize-none shadow-inner"
                                        placeholder="Descrivi il caso d'uso o cosa vuoi ottenere"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    className="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-4 font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:scale-[1.02] hover:shadow-cyan-500/40"
                                >
                                    Invia il messaggio
                                </button>
                                <p className="text-[10px] text-center text-slate-400 dark:text-slate-500 mt-4 leading-relaxed">
                                    Accetto il trattamento dei dati secondo la Privacy Policy.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
