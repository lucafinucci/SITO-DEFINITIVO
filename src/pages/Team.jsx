import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { getTeamMembers, getMediaUrl, extractTextFromRichText } from '../lib/cms';
import { Linkedin, Mail, Loader2, Users } from 'lucide-react';

export default function Team() {
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedMember, setSelectedMember] = useState(null);

  useEffect(() => {
    async function fetchTeam() {
      try {
        setLoading(true);
        const response = await getTeamMembers();
        setMembers(response.docs);
      } catch (err) {
        setError('Impossibile caricare il team. Riprova più tardi.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchTeam();
  }, []);

  // Render bio preview or full bio
  function renderBio(member, full = false) {
    if (full && member.bio) {
      return extractTextFromRichText(member.bio, 1000);
    }
    return member.shortBio || extractTextFromRichText(member.bio, 100);
  }

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
              <Link to="/use-cases" className="text-sm text-slate-400 hover:text-white transition-colors">
                Use Cases
              </Link>
              <Link to="/team" className="text-sm text-cyan-300 font-medium">
                Team
              </Link>
            </nav>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="pt-32 pb-16 px-4">
        <div className="mx-auto max-w-6xl text-center">
          <span className="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-6">
            <Users className="h-4 w-4" />
            Il Nostro Team
          </span>
          <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6">
            <span className="bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 to-blue-500">
              Le Persone dietro Finch-AI
            </span>
          </h1>
          <p className="text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto leading-relaxed">
            Un team di esperti in AI, data science e ingegneria del software, uniti dalla passione
            per l'innovazione e l'eccellenza operativa.
          </p>
        </div>
      </section>

      {/* Team Grid */}
      <section className="pb-24 px-4">
        <div className="mx-auto max-w-6xl">
          {loading ? (
            <div className="flex items-center justify-center py-20">
              <Loader2 className="h-8 w-8 text-cyan-400 animate-spin" />
              <span className="ml-3 text-slate-400">Caricamento team...</span>
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
          ) : members.length === 0 ? (
            <div className="text-center py-20">
              <p className="text-slate-400">Il team sta arrivando...</p>
            </div>
          ) : (
            <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
              {members.map((member) => (
                <div
                  key={member.id}
                  className="group relative overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur transition-all hover:border-cyan-500/50 hover:shadow-[0_0_30px_rgba(34,211,238,0.2)]"
                >
                  {/* Photo */}
                  <div className="aspect-square overflow-hidden">
                    {member.photo ? (
                      <img
                        src={getMediaUrl(member.photo, 'card')}
                        alt={member.photo.alt || member.name}
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                      />
                    ) : (
                      <div className="h-full w-full bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                        <span className="text-6xl">👤</span>
                      </div>
                    )}
                  </div>

                  {/* Content */}
                  <div className="p-6">
                    <h2 className="text-xl font-bold text-white mb-1 group-hover:text-cyan-300 transition-colors">
                      {member.name}
                    </h2>
                    <p className="text-sm text-cyan-400 mb-4">{member.role}</p>
                    <p className="text-sm text-slate-400 mb-4 line-clamp-3">
                      {renderBio(member)}
                    </p>

                    {/* Social Links */}
                    <div className="flex items-center gap-3 pt-4 border-t border-slate-700/50">
                      {member.linkedin && (
                        <a
                          href={member.linkedin}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center justify-center h-10 w-10 rounded-lg border border-slate-700/60 bg-slate-800/50 text-slate-400 hover:border-[#0077B5] hover:text-[#0077B5] transition-colors"
                          aria-label={`LinkedIn di ${member.name}`}
                        >
                          <Linkedin className="h-5 w-5" />
                        </a>
                      )}
                      {member.email && (
                        <a
                          href={`mailto:${member.email}`}
                          className="flex items-center justify-center h-10 w-10 rounded-lg border border-slate-700/60 bg-slate-800/50 text-slate-400 hover:border-cyan-500 hover:text-cyan-400 transition-colors"
                          aria-label={`Email di ${member.name}`}
                        >
                          <Mail className="h-5 w-5" />
                        </a>
                      )}
                      <button
                        onClick={() => setSelectedMember(member)}
                        className="ml-auto text-sm text-cyan-400 hover:text-cyan-300 transition-colors"
                      >
                        Leggi bio →
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Bio Modal */}
      {selectedMember && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
          onClick={() => setSelectedMember(null)}
        >
          <div
            className="relative max-w-2xl w-full max-h-[80vh] overflow-auto rounded-2xl border border-slate-700 bg-slate-900 p-6 sm:p-8"
            onClick={(e) => e.stopPropagation()}
          >
            <button
              onClick={() => setSelectedMember(null)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors"
            >
              <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <div className="flex flex-col sm:flex-row gap-6 mb-6">
              {selectedMember.photo && (
                <img
                  src={getMediaUrl(selectedMember.photo, 'card')}
                  alt={selectedMember.name}
                  className="w-32 h-32 rounded-xl object-cover"
                />
              )}
              <div>
                <h2 className="text-2xl font-bold text-white">{selectedMember.name}</h2>
                <p className="text-cyan-400">{selectedMember.role}</p>
                <div className="flex items-center gap-3 mt-4">
                  {selectedMember.linkedin && (
                    <a
                      href={selectedMember.linkedin}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-[#0077B5] hover:opacity-80 transition-opacity"
                    >
                      <Linkedin className="h-5 w-5" />
                    </a>
                  )}
                  {selectedMember.email && (
                    <a
                      href={`mailto:${selectedMember.email}`}
                      className="text-cyan-400 hover:text-cyan-300 transition-colors"
                    >
                      <Mail className="h-5 w-5" />
                    </a>
                  )}
                </div>
              </div>
            </div>

            <div className="text-slate-300 leading-relaxed">
              {renderBio(selectedMember, true)}
            </div>
          </div>
        </div>
      )}

      {/* CTA Section */}
      <section className="pb-24 px-4">
        <div className="mx-auto max-w-4xl">
          <div className="rounded-3xl border border-cyan-500/30 bg-gradient-to-br from-slate-900/80 to-slate-900/40 p-8 sm:p-12 text-center">
            <h2 className="text-2xl sm:text-3xl font-bold text-white mb-4">
              Vuoi unirti al team?
            </h2>
            <p className="text-slate-300 mb-6">
              Siamo sempre alla ricerca di talenti appassionati di AI e innovazione.
            </p>
            <a
              href="mailto:careers@finch-ai.it"
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
            >
              <Mail className="h-5 w-5" />
              Inviaci il tuo CV
            </a>
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
