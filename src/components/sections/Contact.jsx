import { useState } from 'react';

export default function Contact() {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        company: '',
        message: ''
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        const subject = `Richiesta Contatto da ${formData.name} - ${formData.company}`;
        const body = `Nome: ${formData.name}%0D%0AAzienda: ${formData.company}%0D%0AEmail: ${formData.email}%0D%0A%0D%0AMessaggio:%0D%0A${formData.message}`;
        window.location.href = `mailto:info@finch-ai.it?subject=${subject}&body=${body}`;
    };

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <section id="contatti" className="py-24 relative overflow-hidden">
            {/* Background glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/5 rounded-full blur-[120px] pointer-events-none" />

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative">
                <div className="grid lg:grid-cols-2 gap-16 items-start">
                    <div className="space-y-8">
                        <div className="text-left">
                            <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
                                Contattaci
                            </span>
                            <h2 className="text-4xl sm:text-5xl font-extrabold text-white mb-6">
                                Inizia la <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-blue-500">Trasformazione</span>
                            </h2>
                            <p className="text-xl text-slate-300/90 leading-relaxed max-w-lg">
                                Scopri come Finch-AI può ottimizzare i tuoi processi in 10 minuti di demo. Parla con i nostri esperti.
                            </p>
                        </div>

                        <div className="space-y-6">
                            <div className="flex items-start gap-4">
                                <div className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-3 text-cyan-400">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 className="text-lg font-bold text-white mb-1">Email</h4>
                                    <a href="mailto:info@finch-ai.it" className="text-slate-300 hover:text-cyan-400 transition-colors">info@finch-ai.it</a>
                                    <p className="text-sm text-slate-500 mt-1">Risposta entro 24h</p>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-3 text-cyan-400">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 className="text-lg font-bold text-white mb-1">Orari</h4>
                                    <p className="text-slate-300">Lun - Ven: 9:00 - 18:00</p>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="rounded-xl border border-cyan-500/30 bg-cyan-500/10 p-3 text-cyan-400">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 className="text-lg font-bold text-white mb-1">Sede</h4>
                                    <p className="text-slate-300">Milano, Italia</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mx-auto w-full">
                        <div className="rounded-3xl border border-slate-700/60 bg-slate-900/60 backdrop-blur p-8 shadow-2xl">
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid sm:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label htmlFor="name" className="text-sm font-medium text-slate-300">Nome e Cognome</label>
                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            required
                                            value={formData.name}
                                            onChange={handleChange}
                                            className="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-all"
                                            placeholder="Mario Rossi"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label htmlFor="company" className="text-sm font-medium text-slate-300">Azienda</label>
                                        <input
                                            type="text"
                                            id="company"
                                            name="company"
                                            required
                                            value={formData.company}
                                            onChange={handleChange}
                                            className="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-all"
                                            placeholder="Nome Azienda S.r.l."
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label htmlFor="email" className="text-sm font-medium text-slate-300">Email Aziendale</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        required
                                        value={formData.email}
                                        onChange={handleChange}
                                        className="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-all"
                                        placeholder="mario.rossi@azienda.it"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label htmlFor="message" className="text-sm font-medium text-slate-300">Come possiamo aiutarti?</label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="4"
                                        required
                                        value={formData.message}
                                        onChange={handleChange}
                                        className="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-all resize-none"
                                        placeholder="Descrivi brevemente le tue esigenze..."
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    className="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-4 font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:scale-[1.02] hover:shadow-cyan-500/40"
                                >
                                    Richiedi Contatto
                                </button>
                                <p className="text-xs text-center text-slate-500 mt-4">
                                    Cliccando su "Richiedi Contatto" accetti la nostra Privacy Policy.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
