<?php
session_start();

// Connessione diretta senza include
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=finch_ai_clienti;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Errore DB: " . $e->getMessage());
}

$error = '';
$success = '';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    $success = 'Logout effettuato con successo';
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT id, email, password_hash, nome, cognome, ruolo FROM utenti WHERE email = :email AND attivo = 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['cliente_id'] = $user['id'];
            $_SESSION['cliente_email'] = $user['email'];
            $_SESSION['cliente_nome_completo'] = $user['nome'] . ' ' . $user['cognome'];
            $_SESSION['ruolo'] = $user['ruolo'];

            // Redirect
            if ($user['ruolo'] === 'admin') {
                header('Location: admin/gestione-servizi.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Email o password non validi';
        }
    } else {
        $error = 'Inserisci email e password';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Login Semplice - Test</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #0b1220;
            color: #e5e7eb;
            padding: 40px;
            max-width: 400px;
            margin: 0 auto;
        }
        .card {
            background: #1f2937;
            padding: 30px;
            border-radius: 12px;
        }
        h1 {
            color: #22d3ee;
            margin-top: 0;
        }
        label {
            display: block;
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            background: #0f172a;
            border: 1px solid #374151;
            color: #e5e7eb;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #22d3ee;
            color: #0b1220;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #06b6d4;
        }
        .error {
            background: #7f1d1d;
            color: #fecaca;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .success {
            background: #14532d;
            color: #86efac;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .info {
            background: #1e3a8a;
            color: #93c5fd;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔑 Login Test</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>
                Email
                <input type="email" name="email" required autofocus
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <button type="submit">Accedi</button>
        </form>

        <div class="info">
            <strong>Credenziali di default:</strong><br>
            Admin: admin@finch-ai.it / admin123<br>
            Cliente: cliente@test.it / cliente123
        </div>

        <div class="info" style="margin-top: 10px;">
            <a href="test-login-debug.php" style="color: #22d3ee;">→ Test completo sistema</a>
        </div>
    </div>
</body>
</html>
