<?php
/**
 * SETUP COMPLETO DATABASE - ESEGUI DA BROWSER
 * Apri: http://localhost/SITO/SETUP_COMPLETO.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

// Prova connessione con mysqli invece di PDO
$conn = null;
$connesso = false;

echo "<!DOCTYPE html><html><head><meta charset='utf-8'>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;}";
echo ".ok{color:#0f0;}.err{color:#f00;}.info{color:#0ff;}</style></head><body>";
echo "<pre>";

echo "===========================================\n";
echo "   SETUP COMPLETO DATABASE FINCH-AI\n";
echo "===========================================\n\n";

// Tenta connessione
$hosts = ['127.0.0.1', 'localhost'];
foreach ($hosts as $host) {
    echo "[INFO] Tento connessione a: {$host}...\n";

    $conn = @mysqli_connect($host, 'root', '');

    if ($conn) {
        echo "<span class='ok'>[OK] Connesso a {$host}</span>\n\n";
        $connesso = true;
        break;
    } else {
        echo "<span class='err'>[ERRORE] {$host}: " . mysqli_connect_error() . "</span>\n";
    }
}

if (!$connesso) {
    echo "\n<span class='err'>[FATALE] Impossibile connettersi a MySQL</span>\n";
    echo "\nVerifica che:\n";
    echo "1. MySQL sia avviato in XAMPP\n";
    echo "2. La porta 3306 sia aperta\n";
    echo "</pre></body></html>";
    exit(1);
}

// DROP DATABASE
echo "[1/4] Eliminazione database esistente...\n";
if (mysqli_query($conn, "DROP DATABASE IF EXISTS finch_ai_clienti")) {
    echo "<span class='ok'>[OK] Database eliminato</span>\n\n";
} else {
    echo "<span class='err'>[ERRORE] " . mysqli_error($conn) . "</span>\n\n";
}

// CREATE DATABASE
echo "[2/4] Creazione nuovo database...\n";
if (mysqli_query($conn, "CREATE DATABASE finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo "<span class='ok'>[OK] Database creato</span>\n\n";
} else {
    echo "<span class='err'>[ERRORE] " . mysqli_error($conn) . "</span>\n\n";
    exit(1);
}

// SELECT DATABASE
mysqli_select_db($conn, 'finch_ai_clienti');

// CREATE TABLES
echo "[3/4] Creazione tabelle...\n";

$tables = [
    "utenti" => "CREATE TABLE utenti (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "sessioni" => "CREATE TABLE sessioni (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "servizi" => "CREATE TABLE servizi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descrizione TEXT,
        codice VARCHAR(50) UNIQUE NOT NULL,
        prezzo_mensile DECIMAL(10, 2),
        attivo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_codice (codice)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "utenti_servizi" => "CREATE TABLE utenti_servizi (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "fatture" => "CREATE TABLE fatture (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "scadenze" => "CREATE TABLE scadenze (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "access_logs" => "CREATE TABLE access_logs (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $name => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "<span class='ok'>[OK] Tabella '{$name}' creata</span>\n";
    } else {
        echo "<span class='err'>[ERRORE] Tabella '{$name}': " . mysqli_error($conn) . "</span>\n";
    }
}

// INSERT DATA
echo "\n[4/4] Inserimento dati iniziali...\n";

// Utente demo
if (mysqli_query($conn, "INSERT INTO utenti (email, password_hash, nome, cognome, azienda, attivo) VALUES
    ('demo@finch-ai.it', '\$2y\$10\$sy1aBPONuwKREhutPj7BFeX4jMCdRpMOAYHrFTjEn3fI3bERIpJ4q', 'Demo', 'User', 'Demo Company', TRUE)")) {
    echo "<span class='ok'>[OK] Utente demo creato</span>\n";
} else {
    echo "<span class='err'>[ERRORE] Utente demo: " . mysqli_error($conn) . "</span>\n";
}

// Servizio
if (mysqli_query($conn, "INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
    ('Document Intelligence', 'Analisi automatica documenti con AI', 'DOC_INTEL', 299.00, TRUE)")) {
    echo "<span class='ok'>[OK] Servizio creato</span>\n";
} else {
    echo "<span class='err'>[ERRORE] Servizio: " . mysqli_error($conn) . "</span>\n";
}

// Assegnazione servizio
if (mysqli_query($conn, "INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
    (1, 1, CURDATE(), 'attivo')")) {
    echo "<span class='ok'>[OK] Servizio assegnato</span>\n";
} else {
    echo "<span class='err'>[ERRORE] Assegnazione: " . mysqli_error($conn) . "</span>\n";
}

mysqli_close($conn);

echo "\n===========================================\n";
echo "<span class='ok'>   SETUP COMPLETATO CON SUCCESSO!</span>\n";
echo "===========================================\n\n";
echo "<span class='info'>Credenziali di accesso:</span>\n";
echo "Email: demo@finch-ai.it\n";
echo "Password: Demo123!\n\n";
echo "Vai al login: <a href='/SITO/area-clienti/login.php' style='color:#0ff;'>CLICCA QUI</a>\n";
echo "</pre></body></html>";
?>
