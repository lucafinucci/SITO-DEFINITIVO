/* Header editoriale condiviso dalle pagine articolo del blog.
   Usa il design system di finch-design.css (.section/.wrap/.eyebrow/.h2/.lead). */
export default function ArticleHero({ category, title, description, meta }) {
  return (
    <section className="section article-hero" style={{ paddingBottom: 0 }}>
      <div className="wrap" style={{ maxWidth: 820, textAlign: "center" }}>
        {category && (
          <div
            className="eyebrow center"
            style={{ justifyContent: "center", marginBottom: 22 }}
          >
            {category}
          </div>
        )}
        <h1 className="h2" style={{ marginInline: "auto", maxWidth: "18ch" }}>
          {title}
        </h1>
        {description && (
          <p
            className="lead"
            style={{ marginInline: "auto", marginTop: 22, maxWidth: "52ch" }}
          >
            {description}
          </p>
        )}
        {meta && (
          <p
            style={{
              fontFamily: "var(--mono)",
              fontSize: 12,
              letterSpacing: ".05em",
              color: "var(--fmuted)",
              marginTop: 26,
            }}
          >
            {meta}
          </p>
        )}
      </div>
    </section>
  );
}
