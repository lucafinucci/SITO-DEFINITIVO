import Layout from "./Layout";
import SEO from "./SEO";

/**
 * Shared layout for legal pages (Privacy / Cookie policy).
 * Content is passed in already localized: a title, intro and an array of
 * sections. Each section has a heading and a list of blocks, where a block is
 * either a string (paragraph) or an array of strings (bullet list).
 */
export default function LegalPage({ seo, canonical, lastUpdated, title, intro, sections }) {
  return (
    <Layout>
      <SEO
        title={seo.title}
        description={seo.description}
        keywords={seo.keywords}
        canonical={canonical}
      />
      <section className="relative mx-auto max-w-3xl px-5 pt-28 pb-24 sm:pt-32">
        <header className="mb-10 border-b border-[var(--line)] pb-8">
          <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">{title}</h1>
          {lastUpdated && (
            <p className="mt-3 text-sm text-[var(--muted,#7a8794)]">{lastUpdated}</p>
          )}
          {intro && <p className="mt-5 text-base leading-relaxed opacity-90">{intro}</p>}
        </header>

        <div className="space-y-10">
          {sections.map((sec, i) => (
            <div key={i}>
              <h2 className="mb-3 text-xl font-semibold">{sec.heading}</h2>
              <div className="space-y-3">
                {sec.blocks.map((block, j) =>
                  Array.isArray(block) ? (
                    <ul key={j} className="list-disc space-y-2 pl-5 leading-relaxed opacity-90">
                      {block.map((li, k) => (
                        <li key={k}>{li}</li>
                      ))}
                    </ul>
                  ) : (
                    <p key={j} className="leading-relaxed opacity-90">
                      {block}
                    </p>
                  )
                )}
              </div>
            </div>
          ))}
        </div>
      </section>
    </Layout>
  );
}
