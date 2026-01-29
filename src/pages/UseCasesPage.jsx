import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { getUseCases, getMediaUrl, getIndustryLabel } from '../lib/cms';
import { Briefcase, ArrowRight, Loader2, Filter } from 'lucide-react';

const industries = [
  { value: null, label: 'Tutti i settori' },
  { value: 'manufacturing', label: 'Manufacturing' },
  { value: 'logistics', label: 'Logistica' },
  { value: 'finance', label: 'Finanza' },
  { value: 'retail', label: 'Retail' },
  { value: 'services', label: 'Servizi' },
  { value: 'other', label: 'Altro' },
];

export default function UseCasesPage() {
  const [cases, setCases] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedIndustry, setSelectedIndustry] = useState(null);

  useEffect(() => {
    async function fetchCases() {
      try {
        setLoading(true);
        const response = await getUseCases({ industry: selectedIndustry });
        setCases(response.docs);
      } catch (err) {
        setError('Impossibile caricare i case study. Riprova più tardi.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchCases();
  }, [selectedIndustry]);

  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-white">
      {/* Header */}
      <header className="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/50 bg-slate-900/80 backdrop-blur-xl">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-20 items-center justify-between">
            <Link to="/" className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-lg">
                <img
                  src="/assets/images/LOGO.png"
                  alt="Finch-AI"
                  className="h-8 w-auto object-contain"
                />
              </div>
              <span className="text-xl font-bold text-white">Finch-AI</span>
            </Link>
            <nav className="flex items-center gap-6">
              <Link to="/" className="text-sm text-slate-400 hover:text-white transition-colors">
                Home
              </Link>
              <Link to="/blog" className="text-sm text-slate-400 hover:text-white transition-colors">
                Blog
              </Link>
              <Link to="/use-cases" className="text-sm text-cyan-300 font-medium">
                Use Cases
              </Link>
              <Link to="/team" className="text-sm text-slate-400 hover:text-white transition-colors">
                Team
              </Link>
            </nav>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="pt-32 pb-12 px-4">
        <div className="mx-auto max-w-6xl text-center">
          <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
            <Briefcase className="h-4 w-4" />
            Case Studies
          </span>
          <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
            <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">
              Storie di Successo
            </span>
          </h1>
          <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
            Scopri come abbiamo aiutato le aziende a trasformare le loro operazioni con l'intelligenza artificiale
          </p>
        </div>
      </section>

      {/* Filter */}
      <section className="pb-8 px-4">
        <div className="mx-auto max-w-6xl">
          <div className="flex items-center gap-2 flex-wrap">
            <Filter className="h-4 w-4 text-slate-400" />
            {industries.map((industry) => (
              <button
                key={industry.value || 'all'}
                onClick={() => setSelectedIndustry(industry.value)}
                className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${
                  selectedIndustry === industry.value
                    ? 'bg-cyan-500 text-white'
                    : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'
                }`}
              >
                {industry.label}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Cases Grid */}
      <section className="pb-24 px-4">
        <div className="mx-auto max-w-6xl">
          {loading ? (
            <div className="flex items-center justify-center py-20">
              <Loader2 className="h-8 w-8 text-cyan-400 animate-spin" />
              <span className="ml-3 text-slate-400">Caricamento case study...</span>
            </div>
          ) : error ? (
            <div className="text-center py-20">
              <p className="text-red-400">{error}</p>
              <button
                onClick={() => window.location.reload()}
                className="mt-4 px-4 py-2 rounded-lg bg-cyan-500/20 text-cyan-300 hover:bg-cyan-500/30 transition-colors"
              >
                Riprova
              </button>
            </div>
          ) : cases.length === 0 ? (
            <div className="text-center py-20">
              <p className="text-slate-400">Nessun case study trovato per questo filtro.</p>
              <button
                onClick={() => setSelectedIndustry(null)}
                className="mt-4 text-cyan-400 hover:text-cyan-300"
              >
                Mostra tutti
              </button>
            </div>
          ) : (
            <div className="grid gap-8 lg:grid-cols-2">
              {cases.map((useCase) => (
                <article
                  key={useCase.id}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur transition-all hover:border-cyan-500/50 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                >
                  <div className="flex flex-col md:flex-row">
                    {/* Image */}
                    <div className="md:w-2/5 aspect-video md:aspect-auto overflow-hidden">
                      {useCase.featuredImage ? (
                        <img
                          src={getMediaUrl(useCase.featuredImage, 'card')}
                          alt={useCase.featuredImage.alt || useCase.title}
                          className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                      ) : (
                        <div className="h-full w-full min-h-[200px] bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                          <Briefcase className="h-12 w-12 text-slate-700" />
                        </div>
                      )}
                    </div>

                    {/* Content */}
                    <div className="md:w-3/5 p-6">
                      {/* Industry Badge */}
                      <span className="inline-flex items-center rounded-full bg-blue-500/10 border border-blue-500/30 px-3 py-1 text-xs font-medium text-blue-300 mb-3">
                        {getIndustryLabel(useCase.industry)}
                      </span>

                      <h2 className="text-xl font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors">
                        {useCase.title}
                      </h2>

                      <p className="text-sm text-slate-400 mb-4">
                        Cliente: <span className="text-slate-300">{useCase.clientName}</span>
                      </p>

                      {/* Results Preview */}
                      {useCase.results && useCase.results.length > 0 && (
                        <div className="flex flex-wrap gap-4 mb-4">
                          {useCase.results.slice(0, 2).map((result, i) => (
                            <div key={i} className="text-center">
                              <div className="text-2xl font-bold text-cyan-400">{result.value}</div>
                              <div className="text-xs text-slate-400">{result.metric}</div>
                            </div>
                          ))}
                        </div>
                      )}

                      <Link
                        to={`/use-cases/${useCase.slug}`}
                        className="inline-flex items-center gap-2 text-sm font-semibold text-cyan-400 hover:text-cyan-300 transition-colors"
                      >
                        Leggi il case study
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </div>
                  </div>
                </article>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* CTA Section */}
      <section className="pb-24 px-4">
        <div className="mx-auto max-w-4xl">
          <div className="rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 p-8 sm:p-12 text-center">
            <h2 className="text-2xl sm:text-3xl font-bold text-white mb-4">
              Vuoi essere il prossimo success story?
            </h2>
            <p className="text-slate-300 mb-6">
              Contattaci per scoprire come Finch-AI può trasformare la tua azienda.
            </p>
            <Link
              to="/#contatti"
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
            >
              Richiedi una demo
              <ArrowRight className="h-5 w-5" />
            </Link>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-slate-800/50 py-8">
        <div className="mx-auto max-w-7xl px-4 text-center text-sm text-slate-500">
          © {new Date().getFullYear()} Finch-AI S.r.l. Tutti i diritti riservati.
        </div>
      </footer>
    </div>
  );
}
