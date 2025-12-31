<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Ricrea Database</title>";
echo "<style>body{font-family:system-ui;padding:40px;background:#0b1220;color:#e5e7eb;max-width:800px;margin:0 auto;}";
echo "h1{color:#22d3ee;}.ok{color:#10b981;}.err{color:#ef4444;background:#1f2937;padding:15px;border-radius:8px;margin:10px 0;}</style></head><body>";

echo "<h1>RICREAZIONE DATABASE</h1><hr>";

// Connessione
$hosts = ['127.0.0.1', 'localhost'];
$pdo = null;

foreach ($hosts as $host) {
    try {
        echo "Connessione a {$host}...<br>";
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "<p class='ok'>✅ CONNESSO a {$host}</p>";
        break;
    } catch (PDOException $e) {
        echo "<p class='err'>❌ {$host}: {$e->getMessage()}</p>";
    }
}

if (!$pdo) {
    die("<p class='err'>IMPOSSIBILE CONNETTERSI. Verifica che MySQL sia avviato.</p></body></html>");
}

try {
    // DROP DATABASE
    echo "<br>Elimino database esistente...<br>";
    $pdo->exec("DROP DATABASE IF EXISTS finch_ai_clienti");
    echo "<p class='ok'>✅ Database eliminato</p>";

    // CREATE DATABASE
    echo "Creo nuovo database...<br>";
    $pdo->exec("CREATE DATABASE finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p class='ok'>✅ Database creato</p>";

    // USE DATABASE
    $pdo->exec("USE finch_ai_clienti");

    // CREATE TABLES
    echo "<br>Creo tabelle...<br>";

    $pdo->exec("CREATE TABLE utenti (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        nome VARCHAR(100) NOT NULL,
        cognome VARCHAR(100) NOT NULL,
        azienda VARCHAR(255) NOT NULL,
        telefono VARCHAR(20),
        ruolo ENUM('admin', 'cliente', 'viewer') DEFAULT 'cliente',
        mfa_secret VARCHAR(32) DEFAULT NULL,
        mfa_enabled BOOLEAN DEFAULT FALSE,
        attivo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_email (email),
        INDEX idx_azienda (azienda)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE sessioni (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        revoked BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
        INDEX idx_token (token_hash),
        INDEX idx_user (user_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE servizi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descrizione TEXT,
        codice VARCHAR(50) UNIQUE NOT NULL,
        prezzo_mensile DECIMAL(10, 2),
        attivo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_codice (codice)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE utenti_servizi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        servizio_id INT NOT NULL,
        data_attivazione DATE NOT NULL,
        data_disattivazione DATE NULL,
        stato ENUM('attivo', 'sospeso', 'disattivato') DEFAULT 'attivo',
        note TEXT,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
        FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
        INDEX idx_user_servizio (user_id, servizio_id),
        INDEX idx_stato (stato)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE fatture (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        numero_fattura VARCHAR(50) NOT NULL UNIQUE,
        data_emissione DATE NOT NULL,
        data_scadenza DATE NOT NULL,
        importo_netto DECIMAL(10, 2) NOT NULL,
        iva DECIMAL(10, 2) NOT NULL,
        importo_totale DECIMAL(10, 2) NOT NULL,
        stato ENUM('emessa', 'pagata', 'scaduta', 'annullata') DEFAULT 'emessa',
        file_path VARCHAR(500),
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
        INDEX idx_numero (numero_fattura),
        INDEX idx_user (user_id),
        INDEX idx_data (data_emissione),
        INDEX idx_stato (stato)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE scadenze (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        tipo VARCHAR(100) NOT NULL,
        descrizione TEXT NOT NULL,
        data_scadenza DATE NOT NULL,
        urgente BOOLEAN DEFAULT FALSE,
        completata BOOLEAN DEFAULT FALSE,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_data (data_scadenza),
        INDEX idx_urgente (urgente)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE access_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        email_tentativo VARCHAR(255),
        ip_address VARCHAR(45),
        user_agent TEXT,
        successo BOOLEAN DEFAULT FALSE,
        motivo_fallimento VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL,
        INDEX idx_user (user_id),
        INDEX idx_ip (ip_address),
        INDEX idx_data (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "<p class='ok'>✅ 7 tabelle create</p>";

    // INSERT UTENTE DEMO
    echo "<br>Creo utente demo...<br>";
    $pdo->exec("INSERT INTO utenti (email, password_hash, nome, cognome, azienda, attivo) VALUES
        ('demo@finch-ai.it', '\$2y\$10\$sy1aBPONuwKREhutPj7BFeX4jMCdRpMOAYHrFTjEn3fI3bERIpJ4q', 'Demo', 'User', 'Demo Company', TRUE)");
    echo "<p class='ok'>✅ Utente demo creato</p>";

    // INSERT SERVIZIO
    echo "Creo servizio Document Intelligence...<br>";
    $pdo->exec("INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
        ('Document Intelligence', 'Analisi automatica documenti con AI', 'DOC_INTEL', 299.00, TRUE)");
    echo "<p class='ok'>✅ Servizio creato</p>";

    // ASSEGNA SERVIZIO A DEMO
    $pdo->exec("INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
        (1, 1, CURDATE(), 'attivo')");
    echo "<p class='ok'>✅ Servizio assegnato all'utente demo</p>";

    echo "<br><h2 style='color:#10b981;'>✅ FATTO!</h2>";
    echo "<p><strong>Credenziali:</strong><br>Email: demo@finch-ai.it<br>Password: Demo123!</p>";
    echo "<p><a href='/SITO/area-clienti/login.php' style='background:#22d3ee;color:#0b1220;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:600;'>VAI AL LOGIN</a></p>";

} catch (PDOException $e) {
    echo "<p class='err'>❌ ERRORE: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
