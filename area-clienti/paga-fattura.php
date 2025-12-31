<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

// Recupera fattura da URL
$fatturaId = (int)($_GET['id'] ?? 0);

if (!$fatturaId) {
    header('Location: dashboard.php');
    exit;
}

// Recupera fattura
$stmt = $pdo->prepare('
    SELECT f.*, u.azienda, u.email
    FROM fatture f
    JOIN utenti u ON f.cliente_id = u.id
    WHERE f.id = :id
');
$stmt->execute(['id' => $fatturaId]);
$fattura = $stmt->fetch();

if (!$fattura) {
    header('Location: dashboard.php');
    exit;
}

// Verifica che la fattura appartenga al cliente loggato (se non admin)
$stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
$stmt->execute(['id' => $_SESSION['cliente_id']]);
$user = $stmt->fetch();

if ($user['ruolo'] !== 'admin' && $fattura['cliente_id'] != $_SESSION['cliente_id']) {
    header('Location: dashboard.php');
    exit;
}

// Verifica che la fattura non sia già pagata
if ($fattura['stato'] === 'pagata') {
    $_SESSION['message'] = 'Questa fattura è già stata pagata.';
    header('Location: dashboard.php');
    exit;
}

// Recupera configurazione gateway
$stmt = $pdo->prepare('SELECT * FROM payment_gateways_config WHERE attivo = TRUE');
$stmt->execute();
$gateways = $stmt->fetchAll(PDO::FETCH_GROUP);

$stripeEnabled = isset($gateways['stripe']);
$paypalEnabled = isset($gateways['paypal']);

if (!$stripeEnabled && !$paypalEnabled) {
    $_SESSION['message'] = 'Nessun metodo di pagamento disponibile al momento.';
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Fattura - Finch-AI</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .invoice-summary {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .invoice-number {
            font-size: 24px;
            font-weight: 700;
            color: #8b5cf6;
        }

        .invoice-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .invoice-status.scaduta {
            background: #fee;
            color: #c00;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
        }

        .invoice-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }

        .total-label {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
        }

        .total-amount {
            font-size: 32px;
            font-weight: 700;
            color: #8b5cf6;
        }

        .payment-methods {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .payment-methods h2 {
            margin-bottom: 20px;
            color: #212529;
        }

        .gateway-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .gateway-option {
            flex: 1;
            padding: 20px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .gateway-option:hover {
            border-color: #8b5cf6;
            background: #f8f4ff;
        }

        .gateway-option.selected {
            border-color: #8b5cf6;
            background: #f0e7ff;
        }

        .gateway-logo {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .gateway-name {
            font-weight: 600;
            color: #212529;
        }

        .payment-form {
            display: none;
        }

        .payment-form.active {
            display: block;
        }

        #stripe-payment-form #card-element {
            padding: 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        #card-errors {
            color: #c00;
            margin-top: 10px;
            font-size: 14px;
        }

        .paypal-buttons {
            margin-top: 20px;
        }

        .submit-payment {
            width: 100%;
            padding: 15px;
            background: #8b5cf6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-payment:hover {
            background: #7c3aed;
        }

        .submit-payment:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            text-align: center;
            color: white;
        }

        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .security-notice {
            background: #e7f5ff;
            border-left: 4px solid #339af0;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 14px;
            color: #1864ab;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #8b5cf6;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/layout-start.php'; ?>
    <div class="payment-container">
        <a href="dashboard.php" class="back-link">← Torna alla Dashboard</a>

        <!-- Riepilogo Fattura -->
        <div class="invoice-summary">
            <div class="invoice-header">
                <div class="invoice-number">Fattura <?= htmlspecialchars($fattura['numero_fattura']) ?></div>
                <div class="invoice-status <?= $fattura['stato'] ?>">
                    <?= ucfirst($fattura['stato']) ?>
                </div>
            </div>

            <div class="invoice-details">
                <div class="detail-item">
                    <div class="detail-label">Azienda</div>
                    <div class="detail-value"><?= htmlspecialchars($fattura['azienda']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?= htmlspecialchars($fattura['email']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Data Emissione</div>
                    <div class="detail-value"><?= date('d/m/Y', strtotime($fattura['data_emissione'])) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Data Scadenza</div>
                    <div class="detail-value"><?= date('d/m/Y', strtotime($fattura['data_scadenza'])) ?></div>
                </div>
            </div>

            <div class="invoice-total">
                <div class="total-label">Totale da Pagare</div>
                <div class="total-amount">€<?= number_format($fattura['totale'], 2, ',', '.') ?></div>
            </div>
        </div>

        <!-- Metodi di Pagamento -->
        <div class="payment-methods">
            <h2>Seleziona Metodo di Pagamento</h2>

            <div class="gateway-selector">
                <?php if ($stripeEnabled): ?>
                <div class="gateway-option" data-gateway="stripe" onclick="selectGateway('stripe')">
                    <div class="gateway-logo">💳</div>
                    <div class="gateway-name">Carta di Credito</div>
                    <div style="font-size: 12px; color: #6c757d; margin-top: 5px;">Powered by Stripe</div>
                </div>
                <?php endif; ?>

                <?php if ($paypalEnabled): ?>
                <div class="gateway-option" data-gateway="paypal" onclick="selectGateway('paypal')">
                    <div class="gateway-logo">🅿️</div>
                    <div class="gateway-name">PayPal</div>
                    <div style="font-size: 12px; color: #6c757d; margin-top: 5px;">Paga con PayPal</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form Stripe -->
            <?php if ($stripeEnabled): ?>
            <div id="stripe-payment-form" class="payment-form">
                <h3>Pagamento con Carta di Credito</h3>
                <div id="card-element"></div>
                <div id="card-errors" role="alert"></div>
                <button type="button" id="submit-stripe" class="submit-payment">
                    Paga €<?= number_format($fattura['totale'], 2, ',', '.') ?>
                </button>
            </div>
            <?php endif; ?>

            <!-- Form PayPal -->
            <?php if ($paypalEnabled): ?>
            <div id="paypal-payment-form" class="payment-form">
                <h3>Pagamento con PayPal</h3>
                <div id="paypal-button-container" class="paypal-buttons"></div>
            </div>
            <?php endif; ?>

            <div class="security-notice">
                🔒 <strong>Pagamento Sicuro</strong> - I tuoi dati di pagamento sono protetti con crittografia SSL.
                Non memorizziamo i dettagli della tua carta di credito.
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <div>Elaborazione pagamento in corso...</div>
        </div>
    </div>

    <!-- Stripe JS -->
    <?php if ($stripeEnabled): ?>
    <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>

    <!-- PayPal JS -->
    <?php if ($paypalEnabled): ?>
    <?php
    $paypalClientId = $gateways['paypal'][0]['api_key'] ?? '';
    $paypalMode = $gateways['paypal'][0]['mode'] ?? 'sandbox';
    ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?= $paypalClientId ?>&currency=EUR"></script>
    <?php endif; ?>

    <script>
        const fatturaId = <?= $fatturaId ?>;
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
        let selectedGateway = null;

        function selectGateway(gateway) {
            selectedGateway = gateway;

            // Aggiorna UI
            document.querySelectorAll('.gateway-option').forEach(el => {
                el.classList.remove('selected');
            });
            document.querySelector(`[data-gateway="${gateway}"]`).classList.add('selected');

            // Mostra form corrispondente
            document.querySelectorAll('.payment-form').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(`${gateway}-payment-form`).classList.add('active');
        }

        function showLoading() {
            document.getElementById('loading-overlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.remove('active');
        }

        function showError(message) {
            alert('Errore: ' + message);
        }

        function showSuccess() {
            alert('Pagamento completato con successo! Verrai reindirizzato alla dashboard.');
            window.location.href = 'dashboard.php';
        }

        <?php if ($stripeEnabled): ?>
        // === STRIPE ===
        const stripe = Stripe('<?= $gateways['stripe'][0]['api_publishable_key'] ?? '' ?>');
        const elements = stripe.elements();
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#212529',
                    '::placeholder': {
                        color: '#6c757d',
                    },
                },
            },
        });
        cardElement.mount('#card-element');

        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        document.getElementById('submit-stripe').addEventListener('click', async function() {
            showLoading();

            try {
                // Crea Payment Intent
                const response = await fetch('api/payment-checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        fattura_id: fatturaId,
                        gateway: 'stripe'
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Errore durante l\'inizializzazione del pagamento');
                }

                // Conferma pagamento
                const {error, paymentIntent} = await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: cardElement,
                    }
                });

                if (error) {
                    throw new Error(error.message);
                }

                if (paymentIntent.status === 'succeeded') {
                    showSuccess();
                }

            } catch (error) {
                hideLoading();
                showError(error.message);
            }
        });
        <?php endif; ?>

        <?php if ($paypalEnabled): ?>
        // === PAYPAL ===
        paypal.Buttons({
            createOrder: async function() {
                showLoading();

                try {
                    const response = await fetch('api/payment-checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        },
                        body: JSON.stringify({
                            fattura_id: fatturaId,
                            gateway: 'paypal'
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error || 'Errore durante l\'inizializzazione del pagamento');
                    }

                    hideLoading();
                    return data.orderId;

                } catch (error) {
                    hideLoading();
                    showError(error.message);
                    throw error;
                }
            },
            onApprove: async function(data) {
                showLoading();

                try {
                    // Il webhook gestirà l'aggiornamento della fattura
                    // Qui confermiamo solo il successo
                    setTimeout(() => {
                        showSuccess();
                    }, 2000);

                } catch (error) {
                    hideLoading();
                    showError(error.message);
                }
            },
            onError: function(err) {
                hideLoading();
                showError('Si è verificato un errore con PayPal');
            }
        }).render('#paypal-button-container');
        <?php endif; ?>

        // Auto-seleziona primo gateway disponibile
        <?php if ($stripeEnabled): ?>
        selectGateway('stripe');
        <?php elseif ($paypalEnabled): ?>
        selectGateway('paypal');
        <?php endif; ?>
    </script>
<?php include __DIR__ . '/includes/layout-end.php'; ?>
</body>
</html>
