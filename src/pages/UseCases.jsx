export default function UseCases(){
  const cases = [
    { seg:'PMI manifatturiere', roi:'−70% tempo DDT, +25% efficienza pianificazione' },
    { seg:'Logistica e servizi', roi:'−30% tempi ciclo, +15% puntualità consegne' },
    { seg:'Pubblica Amministrazione', roi:'Digitalizzazione documenti e KPI trasparenti' },
    { seg:"Software house & integratori ERP", roi:"Connector e RAG pronti all'uso" },
  ]
  return (
    <div className="container" style={{ padding:'28px 0' }}>
      <h1>Casi d&apos;uso e settori</h1>
      <div className="grid" style={{ marginTop:16 }}>
        {cases.map((c,i)=>(
          <div className="card" key={i}>
            <h3>{c.seg}</h3>
            <p className="muted">{c.roi}</p>
          </div>
        ))}
      </div>
    </div>
  )
}
