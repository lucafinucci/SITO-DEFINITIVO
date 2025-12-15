import { modules } from '../data/modules.js';

export default function Solutions() {
  return (
    <div className="container" style={{ padding: '28px 0' }}>
      <h1>Soluzioni AI</h1>
      <p className="muted">Sette moduli integrabili e scalabili, più servizi su misura per PMI, PA e integratori ERP.</p>
      <div className="grid" style={{ marginTop: 16 }}>
        {modules.map((m) => (
          <article key={m.key} id={m.link.split('#')[1]} className="card">
            <h3>{m.emoji} {m.title}</h3>
            <p style={{ margin: '8px 0 12px' }}>{m.desc}</p>
            <p className="muted"><strong>Output:</strong> {m.output}</p>
          </article>
        ))}
      </div>
    </div>
  );
}
