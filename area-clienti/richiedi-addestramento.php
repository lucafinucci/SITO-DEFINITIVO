<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';

$clienteId = $_SESSION['cliente_id'];
$clienteEmail = $_SESSION['cliente_email'];

$requestedServizioId = (int)($_GET['servizio_id'] ?? $_POST['servizio_id'] ?? 0);
$requestedRichiestaId = (int)($_GET['richiesta_id'] ?? $_POST['richiesta_id'] ?? 0);
$servizioId = 0;

if ($requestedServizioId > 0) {
    $stmt = $pdo->prepare('
        SELECT 1
        FROM utenti_servizi
        WHERE user_id = :user_id AND servizio_id = :servizio_id AND stato = "attivo"
        LIMIT 1
    ');
    $stmt->execute([
        'user_id' => $clienteId,
        'servizio_id' => $requestedServizioId
    ]);
    if ($stmt->fetchColumn()) {
        $servizioId = $requestedServizioId;
    }
}

if ($servizioId <= 0) {
    // Preferisci Document Intelligence se presente/attivo
    $stmt = $pdo->prepare('
        SELECT s.id
        FROM utenti_servizi us
        JOIN servizi s ON us.servizio_id = s.id
        WHERE us.user_id = :user_id AND us.stato = "attivo" AND s.codice = "DOC-INT"
        ORDER BY us.data_attivazione DESC
        LIMIT 1
    ');
    $stmt->execute(['user_id' => $clienteId]);
    $servizioId = (int)($stmt->fetchColumn() ?: 0);
}

if ($servizioId <= 0) {
    // Fallback: primo servizio attivo dell'utente
    $stmt = $pdo->prepare('
        SELECT s.id
        FROM utenti_servizi us
        JOIN servizi s ON us.servizio_id = s.id
        WHERE us.user_id = :user_id AND us.stato = "attivo"
        ORDER BY us.data_attivazione DESC
        LIMIT 1
    ');
    $stmt->execute(['user_id' => $clienteId]);
    $servizioId = (int)($stmt->fetchColumn() ?: 0);
}

$returnUrl = $servizioId > 0
    ? ('/area-clienti/servizio-dettaglio.php?id=' . $servizioId . '&upload=success')
    : '/area-clienti/dashboard.php?upload=success';

// URL di ritorno dopo invio richiesta (sempre servizio-dettaglio)

// Recupera info utente
$stmt = $pdo->prepare('SELECT nome, cognome, azienda FROM utenti WHERE id = :id');
$stmt->execute(['id' => $clienteId]);
$utente = $stmt->fetch();

// Se c'è una richiesta in corso, consenti "ripresa" senza reinserire tutto
$resumeRichiesta = null;
if ($requestedRichiestaId > 0) {
    $stmt = $pdo->prepare('
        SELECT id, tipo_modello, descrizione, num_documenti_stimati, stato
        FROM richieste_addestramento
        WHERE id = :id AND user_id = :user_id AND stato IN ("in_attesa", "in_lavorazione")
        LIMIT 1
    ');
    $stmt->execute(['id' => $requestedRichiestaId, 'user_id' => $clienteId]);
    $resumeRichiesta = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

  // Pre-compila il tipo di modello se passato via URL
  $tipoPrecompilato = '';
  if (isset($_GET['tipo'])) {
    $tipoMappato = [
        'DDT & Fatture' => 'fatture',
        'Logistica' => 'bolle',
        'Contratti' => 'contratti',
        'Procurement' => 'ordini'
    ];
  $tipoPrecompilato = $tipoMappato[$_GET['tipo']] ?? '';
  }

  if ($resumeRichiesta) {
      $tipoPrecompilato = $resumeRichiesta['tipo_modello'] ?? $tipoPrecompilato;
  }

$success = $error = '';

// Gestione invio richiesta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $tipoModello = $_POST['tipo_modello'] ?? '';
        $descrizione = trim($_POST['descrizione'] ?? '');
        $numDocumenti = (int)($_POST['num_documenti'] ?? 0);

        if (empty($tipoModello) || empty($descrizione) || $numDocumenti < 1) {
            $error = 'Compila tutti i campi obbligatori';
        } else {
            // Salva richiesta nel database
            try {
                $stmt = $pdo->prepare('
                    INSERT INTO richieste_addestramento
                    (user_id, tipo_modello, descrizione, num_documenti_stimati, stato)
                    VALUES (:user_id, :tipo_modello, :descrizione, :num_documenti, "in_attesa")
                ');
                $stmt->execute([
                    'user_id' => $clienteId,
                    'tipo_modello' => $tipoModello,
                    'descrizione' => $descrizione,
                    'num_documenti' => $numDocumenti
                ]);

                $richiestaId = $pdo->lastInsertId();

                // Invia email di notifica (opzionale)
                $to = 'ai-training@finch-ai.it';
                $subject = "Nuova Richiesta Addestramento - {$utente['azienda']}";
                $message = "
Nuova richiesta di addestramento modello AI

Cliente: {$utente['nome']} {$utente['cognome']}
Azienda: {$utente['azienda']}
Email: {$clienteEmail}

Tipo Modello: {$tipoModello}
Descrizione: {$descrizione}
Documenti Stimati: {$numDocumenti}

Richiesta ID: {$richiestaId}
Data: " . date('d/m/Y H:i');

                $headers = "From: noreply@finch-ai.it\r\n";
                $headers .= "Reply-To: {$clienteEmail}\r\n";

                $mailSent = mail($to, $subject, $message, $headers);

                if (!$mailSent) {
                    ErrorHandler::logError('Failed to send training request email', [
                        'richiesta_id' => $richiestaId,
                        'user_id' => $clienteId,
                        'email_to' => $to
                    ]);
                } else {
                    ErrorHandler::logAccess('Training request email sent', [
                        'richiesta_id' => $richiestaId,
                        'user_id' => $clienteId
                    ]);
                }

                $success = 'Richiesta inviata con successo! Ti contatteremo a breve.';

                // Redirect alla pagina di origine
                header('Location: ' . $returnUrl);
                exit;
            } catch (PDOException $e) {
                ErrorHandler::logError('Errore salvataggio richiesta addestramento: ' . $e->getMessage());
                $error = 'Errore nel salvataggio della richiesta. Riprova.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Richiedi Addestramento Modello - Finch-AI</title>
  <link rel="stylesheet" href="/area-clienti/css/style.css">
  <style>
    .upload-zone {
      border: 2px dashed var(--border);
      border-radius: 12px;
      padding: 40px 20px;
      text-align: center;
      background: #0f172a;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .upload-zone:hover {
      border-color: var(--accent1);
      background: rgba(34, 211, 238, 0.05);
    }
    .upload-zone.dragover {
      border-color: var(--accent1);
      background: rgba(34, 211, 238, 0.1);
    }
    .file-list {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .file-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      background: #0f172a;
      border: 1px solid var(--border);
      border-radius: 8px;
    }
    .file-item .name {
      flex: 1;
      font-size: 14px;
    }
    .file-item .size {
      color: var(--muted);
      font-size: 12px;
      margin: 0 12px;
    }
    .file-item .remove {
      color: #ef4444;
      cursor: pointer;
      font-size: 18px;
      padding: 4px 8px;
    }
    .file-item .remove:hover {
      color: #dc2626;
    }
    .progress-bar {
      width: 100%;
      height: 4px;
      background: var(--border);
      border-radius: 2px;
      overflow: hidden;
      margin-top: 8px;
    }
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--accent1), var(--accent2));
      width: 0%;
      transition: width 0.3s ease;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">

  <div style="margin-bottom: 20px;">
    <a href="/area-clienti/servizio-dettaglio.php?id=1" style="color: var(--accent1);">
      ← Torna a Document Intelligence
    </a>
  </div>

  <?php if ($success): ?>
    <div class="alert success" style="margin-bottom: 20px;"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert error" style="margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <section class="card">
    <h1>🤖 Richiedi Addestramento Modello AI</h1>
    <p class="muted">Carica i tuoi documenti per addestrare un modello personalizzato di Document Intelligence</p>

    <form method="post" id="training-form" enctype="multipart/form-data" style="margin-top: 30px;">
      <?php echo Security::csrfField(); ?>
      <?php if ($servizioId > 0): ?>
        <input type="hidden" name="servizio_id" value="<?= (int)$servizioId ?>">
      <?php endif; ?>
      <?php if (!empty($resumeRichiesta['id'])): ?>
        <input type="hidden" name="richiesta_id" value="<?= (int)$resumeRichiesta['id'] ?>">
        <div class="alert" style="margin-bottom: 20px;">
          Stai aggiungendo documenti alla richiesta in corso (#<?= (int)$resumeRichiesta['id'] ?>).
        </div>
      <?php endif; ?>

      <!-- Tipo Modello -->
      <div style="margin-bottom: 24px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">
          Tipo di Modello <span style="color: #ef4444;">*</span>
        </label>
        <?php if (!empty($resumeRichiesta['id'])): ?>
          <input type="hidden" name="tipo_modello" value="<?= htmlspecialchars($tipoPrecompilato) ?>">
        <?php endif; ?>
        <select name="tipo_modello" required <?= !empty($resumeRichiesta['id']) ? 'disabled' : '' ?> style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid var(--border); border-radius: 8px; color: var(--text);">
          <option value="">Seleziona tipo di modello...</option>
          <option value="fatture" <?= $tipoPrecompilato === 'fatture' ? 'selected' : '' ?>>Fatture Elettroniche</option>
          <option value="ddt" <?= $tipoPrecompilato === 'ddt' ? 'selected' : '' ?>>DDT (Documenti di Trasporto)</option>
          <option value="contratti" <?= $tipoPrecompilato === 'contratti' ? 'selected' : '' ?>>Contratti</option>
          <option value="bolle" <?= $tipoPrecompilato === 'bolle' ? 'selected' : '' ?>>Bolle di Consegna</option>
          <option value="preventivi" <?= $tipoPrecompilato === 'preventivi' ? 'selected' : '' ?>>Preventivi</option>
          <option value="ordini" <?= $tipoPrecompilato === 'ordini' ? 'selected' : '' ?>>Ordini di Acquisto</option>
          <option value="custom" <?= $tipoPrecompilato === 'custom' ? 'selected' : '' ?>>Personalizzato (specificare sotto)</option>
        </select>
      </div>

      <!-- Descrizione -->
      <div style="margin-bottom: 24px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">
          Descrizione e Requisiti <span style="color: #ef4444;">*</span>
        </label>
        <textarea
          name="descrizione"
          required
          rows="5"
          placeholder="Descrivi il tipo di documenti e i dati che vuoi estrarre automaticamente..."
          style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid var(--border); border-radius: 8px; color: var(--text); resize: vertical; font-family: inherit;"
          <?= !empty($resumeRichiesta['id']) ? 'readonly' : '' ?>
        ><?= !empty($resumeRichiesta['id']) ? htmlspecialchars((string)$resumeRichiesta['descrizione']) : '' ?></textarea>
        <p class="muted small" style="margin-top: 4px;">
          Es: "Fatture fornitori con estrazione di: codice fornitore, data, importo, IVA, codici articolo"
        </p>
      </div>

      <!-- Numero Documenti -->
      <div style="margin-bottom: 24px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">
          Numero di Documenti da Caricare <span style="color: #ef4444;">*</span>
        </label>
        <input
          type="number"
          name="num_documenti"
          min="1"
          max="10000"
          required
          placeholder="Es: 50"
          style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid var(--border); border-radius: 8px; color: var(--text);"
          value="<?= !empty($resumeRichiesta['id']) ? (int)$resumeRichiesta['num_documenti_stimati'] : '' ?>"
          <?= !empty($resumeRichiesta['id']) ? 'readonly' : '' ?>
        >
        <p class="muted small" style="margin-top: 4px;">
          Consigliati: minimo 30 documenti per un addestramento accurato
        </p>
      </div>

      <!-- Upload Zone -->
      <div style="margin-bottom: 24px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">
          Carica Documenti di Esempio
        </label>

        <div class="upload-zone" id="upload-zone">
          <div style="font-size: 48px; margin-bottom: 12px;">📄</div>
          <p style="font-size: 16px; margin-bottom: 8px;">
            Trascina i file qui o <span style="color: var(--accent1); text-decoration: underline;">clicca per sfogliare</span>
          </p>
          <p class="muted small">
            Formati supportati: PDF, PNG, JPG, JPEG (max 10MB per file)
          </p>
        </div>

        <input
          type="file"
          id="file-input"
          name="files[]"
          multiple
          accept=".pdf,.png,.jpg,.jpeg"
          style="display: none;"
        >

        <div class="file-list" id="file-list"></div>
        <div id="upload-progress" style="display: none; margin-top: 16px;">
          <p class="small" style="margin-bottom: 8px;">Caricamento in corso...</p>
          <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
          </div>
        </div>
      </div>

      <!-- Note Aggiuntive -->
      <div style="margin-bottom: 30px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">
          Note Aggiuntive (opzionale)
        </label>
        <textarea
          name="note"
          rows="3"
          placeholder="Informazioni aggiuntive, casi particolari, layout speciali..."
          style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid var(--border); border-radius: 8px; color: var(--text); resize: vertical; font-family: inherit;"
        ></textarea>
      </div>

      <!-- Info Box -->
      <div style="padding: 16px; background: rgba(34, 211, 238, 0.1); border: 1px solid rgba(34, 211, 238, 0.3); border-radius: 12px; margin-bottom: 24px;">
        <h4 style="margin: 0 0 8px 0; color: var(--accent1);">ℹ️ Cosa succede dopo?</h4>
        <ol class="muted small" style="margin: 0; padding-left: 20px; line-height: 1.6;">
          <li>Il nostro team analizzerà la tua richiesta entro 24 ore</li>
          <li>Ti invieremo un preventivo e una timeline di addestramento</li>
          <li>Una volta approvato, inizieremo l'addestramento del modello</li>
          <li>Riceverai notifiche sullo stato di avanzamento</li>
          <li>Il modello sarà disponibile nella tua area clienti</li>
        </ol>
      </div>

      <!-- Submit -->
      <div style="display: flex; gap: 12px;">
        <button type="submit" name="submit_request" class="btn primary" id="submit-btn">
          Invia Richiesta
        </button>
        <a href="/area-clienti/servizio-dettaglio.php?id=1" class="btn ghost">
          Annulla
        </a>
      </div>
    </form>
  </section>

</main>

<script>
// Gestione Upload File
const uploadZone = document.getElementById('upload-zone');
const fileInput = document.getElementById('file-input');
const fileList = document.getElementById('file-list');
const uploadProgress = document.getElementById('upload-progress');
const progressFill = document.getElementById('progress-fill');
const submitBtn = document.getElementById('submit-btn');

let selectedFiles = [];

// Click per aprire file browser
uploadZone.addEventListener('click', () => {
  fileInput.click();
});

// Selezione file
fileInput.addEventListener('change', (e) => {
  handleFiles(e.target.files);
});

// Drag & Drop
uploadZone.addEventListener('dragover', (e) => {
  e.preventDefault();
  uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
  uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
  e.preventDefault();
  uploadZone.classList.remove('dragover');
  handleFiles(e.dataTransfer.files);
});

// Gestione file
function handleFiles(files) {
  for (let file of files) {
    // Verifica tipo file
    const validTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'];
    if (!validTypes.includes(file.type)) {
      alert(`File ${file.name} non supportato. Usa PDF, PNG o JPG.`);
      continue;
    }

    // Verifica dimensione (10MB)
    if (file.size > 10 * 1024 * 1024) {
      alert(`File ${file.name} troppo grande (max 10MB).`);
      continue;
    }

    // Aggiungi a lista
    if (!selectedFiles.find(f => f.name === file.name)) {
      selectedFiles.push(file);
      addFileToList(file);
    }
  }

  updateSubmitButton();
}

// Aggiungi file alla lista visuale
function addFileToList(file) {
  const fileItem = document.createElement('div');
  fileItem.className = 'file-item';
  fileItem.innerHTML = `
    <span class="name">📄 ${file.name}</span>
    <span class="size">${formatFileSize(file.size)}</span>
    <span class="remove" data-name="${file.name}">✕</span>
  `;

  fileItem.querySelector('.remove').addEventListener('click', () => {
    selectedFiles = selectedFiles.filter(f => f.name !== file.name);
    fileItem.remove();
    updateSubmitButton();
  });

  fileList.appendChild(fileItem);
}

// Formatta dimensione file
function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Aggiorna stato pulsante
function updateSubmitButton() {
  if (selectedFiles.length > 0) {
    submitBtn.textContent = `Invia Richiesta (${selectedFiles.length} file)`;
  } else {
    submitBtn.textContent = 'Invia Richiesta';
  }
}

// Submit form con upload
document.getElementById('training-form').addEventListener('submit', async (e) => {
  if (selectedFiles.length === 0) {
    // Invio senza file (solo richiesta)
    return true;
  }

  e.preventDefault();

  // Mostra progress bar
  uploadProgress.style.display = 'block';
  submitBtn.disabled = true;

  // Crea FormData
  const formData = new FormData(e.target);

  // Rimuovi input file originale e aggiungi file selezionati
  formData.delete('files[]');
  selectedFiles.forEach(file => {
    formData.append('files[]', file);
  });

  try {
    // Upload con progress
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const percent = (e.loaded / e.total) * 100;
        progressFill.style.width = percent + '%';
      }
    });

    xhr.addEventListener('load', () => {
      console.log('XHR Status:', xhr.status);
      console.log('XHR Response:', xhr.responseText);

      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          console.log('Parsed response:', response);

          if (response.success) {
            // VERSIONE AGGIORNATA 2024-12-16 v4 - Sempre redirect a servizio-dettaglio
            console.log('Upload completato! Redirect URL:', response.redirect_url);
            const redirectUrl = response.redirect_url || <?php echo json_encode($returnUrl, JSON_UNESCAPED_SLASHES); ?>;
            console.log('Redirecting to:', redirectUrl);
            window.location.href = redirectUrl;
          } else {
            console.error('API returned error:', response.error);
            alert('Errore: ' + response.error);
            uploadProgress.style.display = 'none';
            submitBtn.disabled = false;
          }
        } catch (e) {
          console.error('JSON parse error:', e);
          console.error('Response was:', xhr.responseText);
          alert('Errore nel parsing della risposta');
          uploadProgress.style.display = 'none';
          submitBtn.disabled = false;
        }
      } else {
        console.error('HTTP error:', xhr.status);
        alert('Errore di caricamento. Riprova.');
        uploadProgress.style.display = 'none';
        submitBtn.disabled = false;
      }
    });

    xhr.addEventListener('error', () => {
      alert('Errore di connessione. Riprova.');
      uploadProgress.style.display = 'none';
      submitBtn.disabled = false;
    });

    xhr.open('POST', '/area-clienti/api/upload-training.php', true);
    xhr.send(formData);

  } catch (error) {
    console.error('Errore upload:', error);
    alert('Errore durante il caricamento. Riprova.');
    uploadProgress.style.display = 'none';
    submitBtn.disabled = false;
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
