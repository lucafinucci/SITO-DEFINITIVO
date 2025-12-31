<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Fix Login Area Clienti</h1>";
echo "<p>Ripristino del sistema di login dopo modifiche multi-utente...</p><hr>";

// Connessione DB
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=finch_ai_clienti;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Connesso al database</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>✗ Errore connessione: " . $e->getMessage() . "</p>");
}

// 1. Verifica se esiste la tabella aziende
echo "<h2>1. Verifica tabella aziende</h2>";
try {
    $result = $pdo->query("SHOW TABLES LIKE 'aziende'");
    if ($result->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Tabella aziende esiste</p>";

        // Conta aziende
        $count = $pdo->query("SELECT COUNT(*) FROM aziende")->fetchColumn();
        echo "<p>Aziende presenti: $count</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Tabella aziende NON esiste - la creo...</p>";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS aziende (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL UNIQUE,
                partita_iva VARCHAR(50) NULL,
                codice_fiscale VARCHAR(50) NULL,
                indirizzo VARCHAR(255) NULL,
                citta VARCHAR(100) NULL,
                cap VARCHAR(10) NULL,
                provincia VARCHAR(2) NULL,
                nazione VARCHAR(100) DEFAULT 'Italia',
                telefono VARCHAR(20) NULL,
                pec VARCHAR(255) NULL,
                codice_sdi VARCHAR(20) NULL,
                webapp_url VARCHAR(255) NULL,
                cliente_dal DATE NULL,
                note TEXT NULL,
                attivo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_nome (nome),
                INDEX idx_attivo (attivo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✓ Tabella aziende creata</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore: " . $e->getMessage() . "</p>";
}

// 2. Verifica colonna azienda_id nella tabella utenti
echo "<h2>2. Verifica colonna azienda_id in utenti</h2>";
try {
    $result = $pdo->query("SHOW COLUMNS FROM utenti LIKE 'azienda_id'");
    if ($result->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Colonna azienda_id esiste</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Colonna azienda_id NON esiste - la aggiungo...</p>";

        $pdo->exec("
            ALTER TABLE utenti
            ADD COLUMN azienda_id INT NULL AFTER azienda,
            ADD INDEX idx_azienda_id (azienda_id)
        ");

        // Aggiungi foreign key solo se la tabella aziende esiste
        try {
            $pdo->exec("
                ALTER TABLE utenti
                ADD CONSTRAINT fk_utenti_azienda
                FOREIGN KEY (azienda_id) REFERENCES aziende(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE
            ");
        } catch (PDOException $e) {
            // Ignora se già esiste
        }

        echo "<p style='color: green;'>✓ Colonna azienda_id aggiunta</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore: " . $e->getMessage() . "</p>";
}

// 3. Migra aziende esistenti dal campo azienda
echo "<h2>3. Migrazione aziende esistenti</h2>";
try {
    // Prendi tutte le aziende uniche dal campo azienda
    $stmt = $pdo->query("
        SELECT DISTINCT azienda
        FROM utenti
        WHERE azienda IS NOT NULL
        AND azienda != ''
        AND ruolo != 'admin'
    ");
    $aziende = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($aziende)) {
        echo "<p>Trovate " . count($aziende) . " aziende da migrare...</p>";

        foreach ($aziende as $nomeAzienda) {
            // Inserisci azienda se non esiste
            $pdo->exec("
                INSERT INTO aziende (nome, created_at)
                VALUES (" . $pdo->quote($nomeAzienda) . ", NOW())
                ON DUPLICATE KEY UPDATE nome = nome
            ");
            echo "<p style='color: green;'>✓ Azienda: $nomeAzienda</p>";
        }

        // Aggiorna azienda_id negli utenti
        $pdo->exec("
            UPDATE utenti u
            INNER JOIN aziende a ON u.azienda = a.nome
            SET u.azienda_id = a.id
            WHERE u.ruolo != 'admin'
        ");

        echo "<p style='color: green;'>✓ Utenti collegati alle aziende</p>";
    } else {
        echo "<p>Nessuna azienda da migrare</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore: " . $e->getMessage() . "</p>";
}

// 4. Crea azienda di test e utenti
echo "<h2>4. Creazione utenti di test</h2>";
try {
    // Crea azienda Finch-AI se non esiste
    $pdo->exec("
        INSERT INTO aziende (nome, webapp_url, attivo, created_at)
        VALUES ('Finch-AI', 'https://app.finch-ai.it', TRUE, NOW())
        ON DUPLICATE KEY UPDATE nome = nome
    ");

    $aziendiFinchId = $pdo->query("SELECT id FROM aziende WHERE nome = 'Finch-AI'")->fetchColumn();
    echo "<p style='color: green;'>✓ Azienda Finch-AI (ID: $aziendiFinchId)</p>";

    // Crea azienda Test Cliente se non esiste
    $pdo->exec("
        INSERT INTO aziende (nome, attivo, created_at)
        VALUES ('Test SRL', TRUE, NOW())
        ON DUPLICATE KEY UPDATE nome = nome
    ");

    $aziendaTestId = $pdo->query("SELECT id FROM aziende WHERE nome = 'Test SRL'")->fetchColumn();
    echo "<p style='color: green;'>✓ Azienda Test SRL (ID: $aziendaTestId)</p>";

    // Crea utente admin
    $adminEmail = 'admin@finch-ai.it';
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO utenti (email, password_hash, nome, cognome, azienda, azienda_id, ruolo, attivo)
        VALUES (?, ?, 'Admin', 'Finch-AI', 'Finch-AI', NULL, 'admin', TRUE)
        ON DUPLICATE KEY UPDATE
            password_hash = VALUES(password_hash),
            ruolo = 'admin',
            attivo = TRUE
    ");
    $stmt->execute([$adminEmail, $adminPass]);
    echo "<p style='color: green;'>✓ Utente ADMIN: $adminEmail / admin123</p>";

    // Crea utente cliente
    $clienteEmail = 'cliente@test.it';
    $clientePass = password_hash('cliente123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO utenti (email, password_hash, nome, cognome, azienda, azienda_id, ruolo, attivo)
        VALUES (?, ?, 'Mario', 'Rossi', 'Test SRL', ?, 'cliente', TRUE)
        ON DUPLICATE KEY UPDATE
            password_hash = VALUES(password_hash),
            azienda_id = VALUES(azienda_id),
            ruolo = 'cliente',
            attivo = TRUE
    ");
    $stmt->execute([$clienteEmail, $clientePass, $aziendaTestId]);
    echo "<p style='color: green;'>✓ Utente CLIENTE: $clienteEmail / cliente123</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore: " . $e->getMessage() . "</p>";
}

// 5. Verifica finale
echo "<h2>5. Verifica Finale</h2>";
try {
    $stmt = $pdo->query("
        SELECT u.id, u.email, u.nome, u.cognome, u.ruolo, u.attivo,
               u.azienda_id, a.nome as azienda_nome
        FROM utenti u
        LEFT JOIN aziende a ON u.azienda_id = a.id
        ORDER BY u.ruolo, u.id
    ");
    $utenti = $stmt->fetchAll();

    if (empty($utenti)) {
        echo "<p style='color: red;'>⚠ PROBLEMA: Nessun utente nel database!</p>";
    } else {
        echo "<p style='color: green;'>✓ Trovati " . count($utenti) . " utenti</p>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #1f2937; color: #22d3ee;'>";
        echo "<th>ID</th><th>Email</th><th>Nome</th><th>Ruolo</th><th>Azienda</th><th>Attivo</th>";
        echo "</tr>";

        foreach ($utenti as $user) {
            $bgColor = $user['ruolo'] === 'admin' ? '#7f1d1d' : '#1e3a8a';
            echo "<tr style='background: $bgColor; color: white;'>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['nome']} {$user['cognome']}</td>";
            echo "<td><strong>{$user['ruolo']}</strong></td>";
            echo "<td>" . ($user['azienda_nome'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['attivo'] ? '✓' : '✗') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Errore: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>✅ Completato!</h2>";
echo "<div style='background: #1f2937; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #22d3ee;'>Credenziali di Accesso:</h3>";
echo "<p style='color: white;'><strong>Admin:</strong> admin@finch-ai.it / admin123</p>";
echo "<p style='color: white;'><strong>Cliente:</strong> cliente@test.it / cliente123</p>";
echo "</div>";

echo "<p><a href='area-clienti/login.php' style='color: #22d3ee; font-size: 18px;'>→ Vai al Login</a></p>";
echo "<p><a href='area-clienti/login-simple-test.php' style='color: #22d3ee;'>→ Login Semplificato (Test)</a></p>";
?>
<style>
body {
    font-family: system-ui, -apple-system, sans-serif;
    background: #0b1220;
    color: #e5e7eb;
    padding: 40px;
    max-width: 900px;
    margin: 0 auto;
}
h1, h2 { color: #22d3ee; }
p { line-height: 1.6; }
</style>
