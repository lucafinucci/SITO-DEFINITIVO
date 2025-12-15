<?php
/**
 * Script di fix connessione e inizializzazione database
 * Usa 127.0.0.1 invece di localhost per evitare problemi DNS/host
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Inizializzazione DB Finch-AI</title>";
echo "<style>body{font-family:monospace;max-width:900px;margin:30px auto;padding:20px;background:#0b1220;color:#e2e8f0;}";
echo ".success{color:#10b981;padding:10px;background:rgba(16,185,129,0.1);border-left:4px solid #10b981;margin:10px 0;}";
echo ".error{color:#f87171;padding:10px;background:rgba(248,113,113,0.1);border-left:4px solid #f87171;margin:10px 0;}";
echo ".info{color:#22d3ee;padding:10px;background:rgba(34,211,238,0.1);border-left:4px solid #22d3ee;margin:10px 0;}";
echo "pre{background:#1f2937;padding:15px;border-radius:8px;overflow-x:auto;} h1{color:#22d3ee;}</style>";
echo "</head><body>";

echo "<h1>🚀 Inizializzazione Database Finch-AI Area Clienti</h1>";

try {
    // Step 1: Connessione usando 127.0.0.1
    echo "<div class='info'><strong>📡 Step 1: Connessione al server MySQL...</strong></div>";

    $pdo = new PDO(
        "mysql:host=127.0.0.1;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "<div class='success'>✓ Connessione MySQL riuscita (host: 127.0.0.1)</div>";

    // Step 2: Crea database
    echo "<div class='info'><strong>📁 Step 2: Creazione database...</strong></div>";

    $pdo->exec("CREATE DATABASE IF NOT EXISTS finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<div class='success'>✓ Database 'finch_ai_clienti' creato/verificato</div>";

    // Step 3: Seleziona database
    $pdo->exec("USE finch_ai_clienti");

    // Step 4: Leggi e esegui schema
    echo "<div class='info'><strong>📋 Step 3: Creazione tabelle...</strong></div>";

    $schemaPath = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("File schema.sql non trovato in: $schemaPath");
    }

    $schema = file_get_contents($schemaPath);
    $pdo->exec($schema);

    echo "<div class='success'>✓ Tabelle create con successo</div>";

    // Step 5: Genera password hashate
    echo "<div class='info'><strong>🔐 Step 4: Generazione password hashate...</strong></div>";

    $passwordAdmin = 'Admin123!';
    $passwordDemo = 'Demo123!';
    $passwordCliente = 'Cliente123!';

    $hashAdmin = password_hash($passwordAdmin, PASSWORD_DEFAULT);
    $hashDemo = password_hash($passwordDemo, PASSWORD_DEFAULT);
    $hashCliente = password_hash($passwordCliente, PASSWORD_DEFAULT);

    echo "<pre style='font-size:11px;'>";
    echo "Admin:   $passwordAdmin\n";
    echo "Demo:    $passwordDemo\n";
    echo "Cliente: $passwordCliente\n";
    echo "</pre>";

    // Step 6: Inserisci utenti
    echo "<div class='info'><strong>👥 Step 5: Creazione utenti demo...</strong></div>";

    $pdo->exec("
        INSERT INTO utenti (email, password_hash, nome, cognome, azienda, telefono, ruolo, mfa_enabled, attivo) VALUES
        ('admin@finch-ai.it', '$hashAdmin', 'Mario', 'Rossi', 'Finch-AI Srl', '+39 02 1234567', 'admin', FALSE, TRUE),
        ('demo@finch-ai.it', '$hashDemo', 'Luigi', 'Verdi', 'Azienda Demo Srl', '+39 06 7654321', 'cliente', FALSE, TRUE),
        ('cliente@example.com', '$hashCliente', 'Paolo', 'Bianchi', 'Example Corp', '+39 011 9876543', 'cliente', FALSE, TRUE)
    ");

    echo "<div class='success'>✓ 3 utenti creati</div>";

    // Step 7: Inserisci servizi
    echo "<div class='info'><strong>🔧 Step 6: Creazione servizi...</strong></div>";

    $pdo->exec("
        INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
        ('Document Intelligence', 'OCR e validazione documenti automatica con AI', 'DOC-INT', 1500.00, TRUE),
        ('Production Analytics', 'Dashboard KPI e monitoraggio real-time', 'PROD-ANA', 1200.00, TRUE),
        ('Financial Control', 'Integrazione ERP e forecast economico', 'FIN-CTR', 1800.00, TRUE),
        ('Supply Chain Optimizer', 'Ottimizzazione logistica e inventario', 'SUP-OPT', 2000.00, TRUE),
        ('Quality Assurance AI', 'Controllo qualità automatizzato', 'QA-AI', 1600.00, TRUE)
    ");

    echo "<div class='success'>✓ 5 servizi creati</div>";

    // Step 8: Assegna servizi
    echo "<div class='info'><strong>🔗 Step 7: Assegnazione servizi...</strong></div>";

    $pdo->exec("
        INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES
        (1, 1, '2024-01-01', 'attivo'),
        (1, 2, '2024-01-01', 'attivo'),
        (1, 3, '2024-01-15', 'attivo'),
        (2, 1, '2024-01-01', 'attivo'),
        (2, 2, '2024-01-01', 'attivo'),
        (2, 3, '2024-02-15', 'attivo'),
        (3, 1, '2024-03-01', 'attivo'),
        (3, 4, '2024-03-15', 'attivo')
    ");

    echo "<div class='success'>✓ Servizi assegnati</div>";

    // Step 9: Inserisci fatture
    echo "<div class='info'><strong>📄 Step 8: Creazione fatture...</strong></div>";

    $pdo->exec("
        INSERT INTO fatture (user_id, numero_fattura, data_emissione, data_scadenza, importo_netto, iva, importo_totale, stato, file_path) VALUES
        (1, 'FT-2024-001', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-001.pdf'),
        (1, 'FT-2024-002', '2024-02-15', '2024-03-16', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-002.pdf'),
        (1, 'FT-2024-010', '2024-10-15', '2024-11-14', 4100.00, 902.00, 5002.00, 'emessa', '/fatture/2024/FT-2024-010.pdf'),
        (2, 'FT-2024-004', '2024-01-15', '2024-02-14', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-004.pdf'),
        (2, 'FT-2024-005', '2024-02-15', '2024-03-16', 4100.00, 902.00, 5002.00, 'pagata', '/fatture/2024/FT-2024-005.pdf'),
        (2, 'FT-2024-011', '2024-10-15', '2024-11-14', 4500.00, 990.00, 5490.00, 'emessa', '/fatture/2024/FT-2024-011.pdf'),
        (3, 'FT-2024-007', '2024-03-15', '2024-04-14', 3500.00, 770.00, 4270.00, 'pagata', '/fatture/2024/FT-2024-007.pdf'),
        (3, 'FT-2024-012', '2024-10-15', '2024-11-14', 3500.00, 770.00, 4270.00, 'emessa', '/fatture/2024/FT-2024-012.pdf')
    ");

    echo "<div class='success'>✓ 8 fatture create</div>";

    // Step 10: Inserisci scadenze
    echo "<div class='info'><strong>📅 Step 9: Creazione scadenze...</strong></div>";

    $pdo->exec("
        INSERT INTO scadenze (user_id, tipo, descrizione, data_scadenza, urgente, completata) VALUES
        (1, 'Pagamento', 'Fattura FT-2024-010', '2024-12-14', TRUE, FALSE),
        (1, 'Rinnovo', 'Rinnovo annuale servizi', '2024-12-31', FALSE, FALSE),
        (2, 'Pagamento', 'Fattura FT-2024-011', '2024-12-14', TRUE, FALSE),
        (2, 'Contratto', 'Rinnovo contratto servizi', '2024-12-31', FALSE, FALSE),
        (3, 'Pagamento', 'Fattura FT-2024-012', '2024-12-14', TRUE, FALSE)
    ");

    echo "<div class='success'>✓ 5 scadenze create</div>";

    // Riepilogo finale
    echo "<div style='background:rgba(16,185,129,0.15);border:2px solid #10b981;border-radius:12px;padding:20px;margin-top:30px;'>";
    echo "<h2 style='color:#10b981;margin-top:0;'>✅ Inizializzazione Completata!</h2>";

    echo "<h3 style='color:#22d3ee;'>📊 Credenziali di accesso:</h3>";
    echo "<pre style='background:#1f2937;padding:15px;border-radius:8px;'>";
    echo "<strong style='color:#22d3ee;'>Admin:</strong>\n";
    echo "Email:    admin@finch-ai.it\n";
    echo "Password: $passwordAdmin\n\n";

    echo "<strong style='color:#22d3ee;'>Cliente Demo:</strong>\n";
    echo "Email:    demo@finch-ai.it\n";
    echo "Password: $passwordDemo\n\n";

    echo "<strong style='color:#22d3ee;'>Cliente Example:</strong>\n";
    echo "Email:    cliente@example.com\n";
    echo "Password: $passwordCliente\n";
    echo "</pre>";

    echo "<h3 style='color:#22d3ee;'>🎯 Prossimi passi:</h3>";
    echo "<ol style='line-height:1.8;'>";
    echo "<li>✅ Database configurato correttamente</li>";
    echo "<li>Testare login: <a href='/area-clienti/login.php' style='color:#22d3ee;font-weight:bold;'>/area-clienti/login.php</a></li>";
    echo "<li>Dashboard API: <a href='/public/area-clienti.html' style='color:#22d3ee;font-weight:bold;'>/public/area-clienti.html</a></li>";
    echo "<li><strong style='color:#f87171;'>ELIMINARE questo file (fix-and-init-db.php) per sicurezza!</strong></li>";
    echo "</ol>";

    echo "<h3 style='color:#22d3ee;'>⚙️ Configurazione completata:</h3>";
    echo "<ul style='line-height:1.8;'>";
    echo "<li>✓ Host MySQL: <code>127.0.0.1</code> (invece di localhost)</li>";
    echo "<li>✓ Database: <code>finch_ai_clienti</code></li>";
    echo "<li>✓ Utente: <code>root</code></li>";
    echo "<li>✓ Password: <code>(vuota)</code></li>";
    echo "</ul>";

    echo "</div>";

    // Verifica finale
    $stmt = $pdo->query("SELECT COUNT(*) FROM utenti");
    $userCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM servizi");
    $serviceCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM fatture");
    $fattureCount = $stmt->fetchColumn();

    echo "<div style='background:rgba(34,211,238,0.1);border:1px solid #22d3ee;border-radius:8px;padding:15px;margin-top:20px;'>";
    echo "<strong>📊 Statistiche database:</strong>";
    echo "<ul>";
    echo "<li>Utenti: $userCount</li>";
    echo "<li>Servizi: $serviceCount</li>";
    echo "<li>Fatture: $fattureCount</li>";
    echo "</ul>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Errore Database</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<h3>Possibili soluzioni:</h3>";
    echo "<ul>";
    echo "<li>Verificare che MySQL sia avviato nel pannello XAMPP</li>";
    echo "<li>Controllare che non ci siano altri processi sulla porta 3306</li>";
    echo "<li>Provare a riavviare MySQL dal Control Panel XAMPP</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Errore Generale</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}

echo "</body></html>";
