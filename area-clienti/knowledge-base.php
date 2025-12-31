<?php
require __DIR__ . '/includes/auth.php';
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ e Guide - Area Clienti</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .kb-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 16px;
      margin-top: 16px;
    }
    .kb-card {
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 16px;
    }
    .kb-card h4 {
      margin: 0 0 8px 0;
    }
    .kb-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .kb-list li {
      padding: 10px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: #0b1220;
    }
    .kb-list p {
      margin: 6px 0 0 0;
      color: var(--muted);
      font-size: 13px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
  <section class="card">
    <div class="card-header">
      <div>
        <h3 style="margin: 0 0 8px 0;">FAQ e Guide</h3>
        <p class="muted">Risposte rapide e procedure operative per l area clienti.</p>
      </div>
      <a class="btn ghost small" href="/area-clienti/dashboard.php">Torna alla Dashboard</a>
    </div>

    <div class="kb-grid">
      <div class="kb-card">
        <h4>Accesso e sicurezza</h4>
        <ul class="kb-list">
          <li>
            <strong>Come cambio la password?</strong>
            <p>Vai su Profilo, aggiorna la password e salva. La modifica e immediata.</p>
          </li>
          <li>
            <strong>Come attivo la 2FA?</strong>
            <p>Apri Sicurezza 2FA e segui la procedura guidata con app Authenticator.</p>
          </li>
          <li>
            <strong>Problemi di accesso?</strong>
            <p>Se non riesci ad accedere, apri un ticket con il dettaglio dell errore.</p>
          </li>
        </ul>
      </div>

      <div class="kb-card">
        <h4>Fatture e pagamenti</h4>
        <ul class="kb-list">
          <li>
            <strong>Dove trovo le fatture?</strong>
            <p>Dashboard > Fatture. Puoi visualizzare e scaricare i PDF.</p>
          </li>
          <li>
            <strong>Come pago una fattura?</strong>
            <p>Apri la fattura e usa il pulsante Paga Ora per completare il pagamento.</p>
          </li>
          <li>
            <strong>Ricevo la ricevuta?</strong>
            <p>Alla conferma del pagamento viene inviata anche una email di riepilogo.</p>
          </li>
        </ul>
      </div>

      <div class="kb-card">
        <h4>Servizi e utilizzo</h4>
        <ul class="kb-list">
          <li>
            <strong>Come vedo i servizi attivi?</strong>
            <p>Nella Dashboard trovi l elenco dei servizi con stato e dettaglio.</p>
          </li>
          <li>
            <strong>Limiti e quote</strong>
            <p>Se hai una quota mensile, la vedi nel dettaglio del servizio.</p>
          </li>
          <li>
            <strong>Richiesta addestramento</strong>
            <p>Usa la pagina Richiedi addestramento e monitora lo stato dal pannello.</p>
          </li>
        </ul>
      </div>

      <div class="kb-card">
        <h4>Supporto</h4>
        <ul class="kb-list">
          <li>
            <strong>Come apro un ticket?</strong>
            <p>Dashboard > Supporto e Ticket. Compila il modulo e invia.</p>
          </li>
          <li>
            <strong>Tempi di risposta</strong>
            <p>Ticket normali entro 24-48 ore. Urgenti entro 4 ore lavorative.</p>
          </li>
          <li>
            <strong>Posso allegare file?</strong>
            <p>Non ancora. Se serve, indica nel ticket e ti forniamo un link sicuro.</p>
          </li>
        </ul>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
