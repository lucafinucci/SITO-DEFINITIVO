export default function Technology(){
  const items = [
    'Machine Learning & Computer Vision (OCR, analisi immagini)',
    'NLP (chatbot multicanale, text understanding)',
    'Predictive & Prescriptive Analytics (forecast e ottimizzazione)',
    'Business Intelligence & Data Visualization',
    'Data Integration (API, RAG, Knowledge Graph)',
    'Cloud & Edge Computing (Azure, Docker, PostgreSQL)'
  ]
  return (
    <div className="container" style={{ padding:'28px 0' }}>
      <h1>Tecnologia</h1>
      <div className="grid" style={{ marginTop:16 }}>
        {items.map((t,i)=>(
          <div className="card" key={i}><p>{t}</p></div>
        ))}
      </div>
    </div>
  )
}
