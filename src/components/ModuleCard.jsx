export default function ModuleCard({ emoji, title, desc, output, href }) {
  return (
    <a href={href} className="card">
      <div className="badge">{output}</div>
      <h3>{emoji} {title}</h3>
      <p>{desc}</p>
    </a>
  );
}
