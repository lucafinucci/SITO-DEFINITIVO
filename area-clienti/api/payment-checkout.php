<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/payment-gateways.php';

header('Content-Type: application/json; charset=utf-8');

// Verifica CSRF
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF non valido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $fatturaId = (int)($input['fattura_id'] ?? 0);
    $gateway = $input['gateway'] ?? 'stripe';

    if (!$fatturaId) {
        throw new Exception('ID fattura mancante');
    }

    if (!in_array($gateway, ['stripe', 'paypal'], true)) {
        throw new Exception('Gateway non valido');
    }

    // Recupera fattura
    $stmt = $pdo->prepare('
        SELECT f.*, u.email, u.azienda
        FROM fatture f
        JOIN utenti u ON f.cliente_id = u.id
        WHERE f.id = :id
    ');
    $stmt->execute(['id' => $fatturaId]);
    $fattura = $stmt->fetch();

    if (!$fattura) {
        throw new Exception('Fattura non trovata');
    }

    // Verifica che la fattura appartenga al cliente loggato (se non admin)
    $stmt = $pdo->prepare('SELECT ruolo FROM utenti WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['cliente_id']]);
    $user = $stmt->fetch();

    if ($user['ruolo'] !== 'admin' && $fattura['cliente_id'] != $_SESSION['cliente_id']) {
        http_response_code(403);
        throw new Exception('Non autorizzato');
    }

    // Verifica che la fattura non sia già pagata
    if ($fattura['stato'] === 'pagata') {
        throw new Exception('Fattura già pagata');
    }

    $importo = (float)$fattura['totale'];
    $metadata = [
        'fattura_id' => $fatturaId,
        'numero_fattura' => $fattura['numero_fattura'],
        'cliente_email' => $fattura['email'],
        'azienda' => $fattura['azienda']
    ];

    // Inizializza gateway
    if ($gateway === 'stripe') {
        $paymentGateway = new StripeGateway($pdo);
        $paymentIntent = $paymentGateway->creaPaymentIntent($fatturaId, $importo, $metadata);

        echo json_encode([
            'success' => true,
            'gateway' => 'stripe',
            'clientSecret' => $paymentIntent['client_secret'],
            'paymentIntentId' => $paymentIntent['id'],
            'publicKey' => $paymentGateway->getPublicKey(),
            'testMode' => $paymentGateway->isTestMode()
        ]);

    } elseif ($gateway === 'paypal') {
        $paymentGateway = new PayPalGateway($pdo);
        $order = $paymentGateway->creaOrdine($fatturaId, $importo, $metadata);

        // Trova link di approvazione
        $approveLink = null;
        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'approve') {
                $approveLink = $link['href'];
                break;
            }
        }

        echo json_encode([
            'success' => true,
            'gateway' => 'paypal',
            'orderId' => $order['id'],
            'approveLink' => $approveLink,
            'testMode' => $paymentGateway->isTestMode()
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
