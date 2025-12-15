export default function Services(){
  return (
    <div className="container" style={{ padding:'28px 0' }}>
      <h1>Servizi personalizzati</h1>
      <div className="grid" style={{ marginTop:16 }}>
        {[
          { t:'Modelli predittivi e prescrittivi', d:'Costruiti sui tuoi dati e processi.' },
          { t:'Dashboard integrate multi-sorgente', d:'KPI end-to-end con tempi reali.' },
          { t:'Pianificazione e schedulazione', d:'Algoritmi per ridurre sprechi e ritardi.' },
          { t:'AI Strategy & Digital Transformation', d:'Roadmap, ROI, governance e change management.' },
          { t:'Integrazione & RAG', d:'API, knowledge graph e connector ERP.' },
        ].map((x,i)=>(
          <div className="card" key={i}>
            <h3>{x.t}</h3>
            <p>{x.d}</p>
          </div>
        ))}
      </div>
    </div>
  )
}
