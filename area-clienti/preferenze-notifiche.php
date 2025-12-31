<?php
require 'includes/auth.php';
require 'includes/db.php';

$clienteId = $_SESSION['cliente_id'];

// Recupera preferenze attuali
$stmt = $pdo->prepare('
    SELECT np.*, u.email, u.telefono
    FROM notifiche_preferenze np
    LEFT JOIN utenti u ON np.utente_id = u.id
    WHERE np.utente_id = :cliente_id
');
$stmt->execute(['cliente_id' => $clienteId]);
$preferenze = $stmt->fetch();

// Se non esistono preferenze, crea default
if (!$preferenze) {
    $stmt = $pdo->prepare('
        INSERT INTO notifiche_preferenze (utente_id) VALUES (:cliente_id)
    ');
    $stmt->execute(['cliente_id' => $clienteId]);

    // Ricarica
    $stmt = $pdo->prepare('
        SELECT np.*, u.email, u.telefono
        FROM notifiche_preferenze np
        LEFT JOIN utenti u ON np.utente_id = u.id
        WHERE np.utente_id = :cliente_id
    ');
    $stmt->execute(['cliente_id' => $clienteId]);
    $preferenze = $stmt->fetch();
}

// Gestione salvataggio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('
            UPDATE notifiche_preferenze
            SET
                telefono_sms = :telefono_sms,
                email_enabled = :email_enabled,
                sms_enabled = :sms_enabled,
                browser_enabled = :browser_enabled,
                servizio_attivato_enabled = :servizio_attivato_enabled,
                servizio_attivato_canale = :servizio_attivato_canale,
                servizio_disattivato_enabled = :servizio_disattivato_enabled,
                servizio_disattivato_canale = :servizio_disattivato_canale,
                fattura_emessa_canale = :fattura_emessa_canale,
                fattura_scadenza_canale = :fattura_scadenza_canale,
                pagamento_confermato_canale = :pagamento_confermato_canale,
                aggiornamento_sistema_canale = :aggiornamento_sistema_canale
            WHERE utente_id = :cliente_id
        ');

        $stmt->execute([
            'cliente_id' => $clienteId,
            'telefono_sms' => $_POST['telefono_sms'] ?? null,
            'email_enabled' => isset($_POST['email_enabled']) ? 1 : 0,
            'sms_enabled' => isset($_POST['sms_enabled']) ? 1 : 0,
            'browser_enabled' => isset($_POST['browser_enabled']) ? 1 : 0,
            'servizio_attivato_enabled' => isset($_POST['servizio_attivato_enabled']) ? 1 : 0,
            'servizio_attivato_canale' => $_POST['servizio_attivato_canale'] ?? 'email',
            'servizio_disattivato_enabled' => isset($_POST['servizio_disattivato_enabled']) ? 1 : 0,
            'servizio_disattivato_canale' => $_POST['servizio_disattivato_canale'] ?? 'email',
            'fattura_emessa_canale' => $_POST['fattura_emessa_canale'] ?? 'email',
            'fattura_scadenza_canale' => $_POST['fattura_scadenza_canale'] ?? 'entrambi',
            'pagamento_confermato_canale' => $_POST['pagamento_confermato_canale'] ?? 'email',
            'aggiornamento_sistema_canale' => $_POST['aggiornamento_sistema_canale'] ?? 'email'
        ]);

        $successMessage = 'Preferenze salvate con successo!';

        // Ricarica preferenze
        $stmt = $pdo->prepare('
            SELECT np.*, u.email, u.telefono
            FROM notifiche_preferenze np
            LEFT JOIN utenti u ON np.utente_id = u.id
            WHERE np.utente_id = :cliente_id
        ');
        $stmt->execute(['cliente_id' => $clienteId]);
        $preferenze = $stmt->fetch();

    } catch (Exception $e) {
        $errorMessage = 'Errore nel salvataggio: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferenze Notifiche - Finch-AI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .toggle-section {
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 0;
        }

        .toggle-section:last-child {
            border-bottom: none;
        }

        .toggle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .toggle-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #374151;
        }

        .toggle-icon {
            font-size: 24px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #8b5cf6;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        .channel-options {
            display: flex;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .channel-radio {
            flex: 1;
            min-width: 120px;
        }

        .channel-radio input[type="radio"] {
            display: none;
        }

        .channel-radio label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            color: #6b7280;
        }

        .channel-radio input:checked + label {
            border-color: #8b5cf6;
            background: #f3f4f6;
            color: #8b5cf6;
            font-weight: 600;
        }

        .channel-radio label:hover {
            border-color: #8b5cf6;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-group input[type="text"],
        .input-group input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #8b5cf6;
        }

        .input-hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 6px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #6b7280;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .info-box strong {
            color: #1e40af;
        }

        .info-box p {
            color: #1e40af;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Preferenze Notifiche</h1>
            <p>Scegli come e quando ricevere le notifiche sui tuoi servizi</p>
        </div>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                ✓ <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                ✗ <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Canali Globali -->
            <div class="card">
                <div class="card-title">
                    <span>📡</span>
                    Canali di Notifica
                </div>
                <div class="card-subtitle">
                    Abilita i canali attraverso cui vuoi ricevere le notifiche
                </div>

                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">🌐</span>
                            <span>Notifiche Browser</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="browser_enabled" <?php echo $preferenze['browser_enabled'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <p class="input-hint">Ricevi notifiche in tempo reale quando sei nell'area clienti</p>
                </div>

                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">📧</span>
                            <span>Notifiche Email</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_enabled" <?php echo $preferenze['email_enabled'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <p class="input-hint">Email di notifica verranno inviate a: <strong><?php echo htmlspecialchars($preferenze['email']); ?></strong></p>
                </div>

                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">📱</span>
                            <span>Notifiche SMS</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_enabled" <?php echo $preferenze['sms_enabled'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="input-group">
                        <label>Numero di Telefono</label>
                        <input
                            type="tel"
                            name="telefono_sms"
                            placeholder="+39 123 456 7890"
                            value="<?php echo htmlspecialchars($preferenze['telefono_sms'] ?? ''); ?>"
                        >
                        <p class="input-hint">Formato: +39 seguito dal numero. Gli SMS sono soggetti a costi del gestore.</p>
                    </div>
                </div>
            </div>

            <!-- Preferenze per Tipo di Notifica -->
            <div class="card">
                <div class="card-title">
                    <span>⚙️</span>
                    Preferenze per Tipo di Evento
                </div>
                <div class="card-subtitle">
                    Personalizza come ricevere ciascun tipo di notifica
                </div>

                <!-- Servizi -->
                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">✅</span>
                            <span>Attivazione Servizi</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="servizio_attivato_enabled" <?php echo $preferenze['servizio_attivato_enabled'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="channel-options">
                        <div class="channel-radio">
                            <input type="radio" id="sa_email" name="servizio_attivato_canale" value="email" <?php echo $preferenze['servizio_attivato_canale'] === 'email' ? 'checked' : ''; ?>>
                            <label for="sa_email">📧 Solo Email</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="sa_sms" name="servizio_attivato_canale" value="sms" <?php echo $preferenze['servizio_attivato_canale'] === 'sms' ? 'checked' : ''; ?>>
                            <label for="sa_sms">📱 Solo SMS</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="sa_both" name="servizio_attivato_canale" value="entrambi" <?php echo $preferenze['servizio_attivato_canale'] === 'entrambi' ? 'checked' : ''; ?>>
                            <label for="sa_both">📧+📱 Entrambi</label>
                        </div>
                    </div>
                </div>

                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">❌</span>
                            <span>Disattivazione Servizi</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="servizio_disattivato_enabled" <?php echo $preferenze['servizio_disattivato_enabled'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="channel-options">
                        <div class="channel-radio">
                            <input type="radio" id="sd_email" name="servizio_disattivato_canale" value="email" <?php echo $preferenze['servizio_disattivato_canale'] === 'email' ? 'checked' : ''; ?>>
                            <label for="sd_email">📧 Solo Email</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="sd_sms" name="servizio_disattivato_canale" value="sms" <?php echo $preferenze['servizio_disattivato_canale'] === 'sms' ? 'checked' : ''; ?>>
                            <label for="sd_sms">📱 Solo SMS</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="sd_both" name="servizio_disattivato_canale" value="entrambi" <?php echo $preferenze['servizio_disattivato_canale'] === 'entrambi' ? 'checked' : ''; ?>>
                            <label for="sd_both">📧+📱 Entrambi</label>
                        </div>
                    </div>
                </div>

                <!-- Fatturazione -->
                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">📄</span>
                            <span>Nuove Fatture</span>
                        </div>
                    </div>
                    <div class="channel-options">
                        <div class="channel-radio">
                            <input type="radio" id="fe_email" name="fattura_emessa_canale" value="email" <?php echo $preferenze['fattura_emessa_canale'] === 'email' ? 'checked' : ''; ?>>
                            <label for="fe_email">📧 Solo Email</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="fe_sms" name="fattura_emessa_canale" value="sms" <?php echo $preferenze['fattura_emessa_canale'] === 'sms' ? 'checked' : ''; ?>>
                            <label for="fe_sms">📱 Solo SMS</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="fe_both" name="fattura_emessa_canale" value="entrambi" <?php echo $preferenze['fattura_emessa_canale'] === 'entrambi' ? 'checked' : ''; ?>>
                            <label for="fe_both">📧+📱 Entrambi</label>
                        </div>
                    </div>
                </div>

                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">⏰</span>
                            <span>Promemoria Scadenze</span>
                        </div>
                    </div>
                    <div class="info-box">
                        <p><strong>Consigliato: Entrambi</strong> - Le scadenze sono importanti, ti consigliamo di abilitare sia email che SMS per non perdere i promemoria.</p>
                    </div>
                    <div class="channel-options">
                        <div class="channel-radio">
                            <input type="radio" id="fs_email" name="fattura_scadenza_canale" value="email" <?php echo $preferenze['fattura_scadenza_canale'] === 'email' ? 'checked' : ''; ?>>
                            <label for="fs_email">📧 Solo Email</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="fs_sms" name="fattura_scadenza_canale" value="sms" <?php echo $preferenze['fattura_scadenza_canale'] === 'sms' ? 'checked' : ''; ?>>
                            <label for="fs_sms">📱 Solo SMS</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="fs_both" name="fattura_scadenza_canale" value="entrambi" <?php echo $preferenze['fattura_scadenza_canale'] === 'entrambi' ? 'checked' : ''; ?>>
                            <label for="fs_both">📧+📱 Entrambi</label>
                        </div>
                    </div>
                </div>

                <!-- Pagamenti -->
                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">💳</span>
                            <span>Conferme Pagamento</span>
                        </div>
                    </div>
                    <div class="channel-options">
                        <div class="channel-radio">
                            <input type="radio" id="pc_email" name="pagamento_confermato_canale" value="email" <?php echo $preferenze['pagamento_confermato_canale'] === 'email' ? 'checked' : ''; ?>>
                            <label for="pc_email">📧 Solo Email</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="pc_sms" name="pagamento_confermato_canale" value="sms" <?php echo $preferenze['pagamento_confermato_canale'] === 'sms' ? 'checked' : ''; ?>>
                            <label for="pc_sms">📱 Solo SMS</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="pc_both" name="pagamento_confermato_canale" value="entrambi" <?php echo $preferenze['pagamento_confermato_canale'] === 'entrambi' ? 'checked' : ''; ?>>
                            <label for="pc_both">📧+📱 Entrambi</label>
                        </div>
                    </div>
                </div>

                <!-- Aggiornamenti -->
                <div class="toggle-section">
                    <div class="toggle-header">
                        <div class="toggle-label">
                            <span class="toggle-icon">🔄</span>
                            <span>Aggiornamenti Sistema</span>
                        </div>
                    </div>
                    <div class="channel-options">
                        <div class="channel-radio">
                            <input type="radio" id="as_email" name="aggiornamento_sistema_canale" value="email" <?php echo $preferenze['aggiornamento_sistema_canale'] === 'email' ? 'checked' : ''; ?>>
                            <label for="as_email">📧 Solo Email</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="as_sms" name="aggiornamento_sistema_canale" value="sms" <?php echo $preferenze['aggiornamento_sistema_canale'] === 'sms' ? 'checked' : ''; ?>>
                            <label for="as_sms">📱 Solo SMS</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="as_both" name="aggiornamento_sistema_canale" value="entrambi" <?php echo $preferenze['aggiornamento_sistema_canale'] === 'entrambi' ? 'checked' : ''; ?>>
                            <label for="as_both">📧+📱 Entrambi</label>
                        </div>
                        <div class="channel-radio">
                            <input type="radio" id="as_none" name="aggiornamento_sistema_canale" value="nessuno" <?php echo $preferenze['aggiornamento_sistema_canale'] === 'nessuno' ? 'checked' : ''; ?>>
                            <label for="as_none">🚫 Nessuno</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="dashboard.php" class="btn btn-secondary">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva Preferenze</button>
            </div>
        </form>
    </div>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
