export default function Contact(){
  return (
    <div className="container" style={{ padding:'28px 0' }}>
      <h1>Contatti & Demo</h1>
      <p className="muted">Compila il form o scrivici per prenotare una demo personalizzata.</p>
      <form className="card" onSubmit={(e)=>{e.preventDefault(); alert('Grazie! Ti contatteremo a breve.')}}>
        <div style={{ display:'grid', gap:12 }}>
          <input required placeholder="Nome e Cognome" style={inputStyle}/>
          <input required type="email" placeholder="Email" style={inputStyle}/>
          <input placeholder="Azienda" style={inputStyle}/>
          <select style={inputStyle} defaultValue="">
            <option value="" disabled>Modulo di interesse</option>
            <option>Document Intelligence</option>
            <option>Production</option>
            <option>Finance</option>
            <option>Assistant</option>
            <option>Connector</option>
            <option>Strategic Planner</option>
            <option>Assessment</option>
          </select>
          <textarea rows="5" placeholder="Raccontaci l'esigenza" style={inputStyle}/>
          <label style={{ display:'flex', alignItems:'flex-start', gap:8, color:'white', fontSize:14, lineHeight:1.4 }}>
            <input type="checkbox" required style={{ marginTop:4 }}/>
            <span>Ho letto e accetto la <a href="/privacy-policy.html" target="_blank" rel="noopener noreferrer" style={{ color:'#67e8f9' }}>Privacy Policy</a>.</span>
          </label>
          <button className="cta">Invia richiesta</button>
        </div>
      </form>
    </div>
  )
}

const inputStyle = {
  padding:'12px 14px', borderRadius:12, border:'1px solid rgba(255,255,255,.2)',
  background:'rgba(255,255,255,.06)', color:'white', outline:'none'
}
