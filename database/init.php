<?php
/**
 * Script di inizializzazione database
 * Esegui questo file UNA VOLTA per creare il database e popolarlo
 *
 * ISTRUZIONI:
 * 1. Configurare le credenziali in api/config/database.php
 * 2. Creare il database MySQL dal pannello Aruba
 * 3. Visitare: http://tuosito.it/database/init.php
 * 4. ELIMINARE questo file dopo l'inizializzazione per sicurezza
 */

// Impedisci esecuzione in produzione (decommentare dopo prima esecuzione)
// die('Script di inizializzazione disabilitato per sicurezza');

require_once __DIR__ . '/../public/api/config/database.php';

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inizializzazione Database - Finch-AI</title>
    <style>
        body {
            font-family: monospace;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #0b1220;
            color: #e2e8f0;
        }
        .success { color: #10b981; }
        .error { color: #f87171; }
        .info { color: #22d3ee; }
        pre { background: #1f2937; padding: 15px; border-radius: 8px; overflow-x: auto; }
        h1 { color: #22d3ee; }
        .step { margin: 20px 0; padding: 15px; background: rgba(34, 211, 238, 0.1); border-left: 4px solid #22d3ee; }
    </style>
</head>
<body>
    <h1>🚀 Inizializzazione Database Finch-AI</h1>

<?php

try {
    echo "<div class='step'><strong class='info'>📡 Connessione al database...</strong><br>";

    $pdo = getDB();

    echo "<span class='success'>✓ Connesso a: " . DB_NAME . "@" . DB_HOST . "</span></div>";

    // Leggi e esegui schema.sql
    echo "<div class='step'><strong class='info'>📋 Creazione tabelle...</strong><br>";

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);

    echo "<span class='success'>✓ Tabelle create con successo</span></div>";

    // Genera password hashate
    echo "<div class='step'><strong class='info'>🔐 Generazione password hashate...</strong><br>";

    $passwordAdmin = 'Admin123!';
    $passwordDemo = 'Demo123!';
    $passwordCliente = 'Cliente123!';

    $hashAdmin = password_hash($passwordAdmin, PASSWORD_DEFAULT);
    $hashDemo = password_hash($passwordDemo, PASSWORD_DEFAULT);
    $hashCliente = password_hash($passwordCliente, PASSWORD_DEFAULT);

    echo "<pre>";
    echo "Admin:   {$passwordAdmin} => {$hashAdmin}\n";
    echo "Demo:    {$passwordDemo} => {$hashDemo}\n";
    echo "Cliente: {$passwordCliente} => {$hashCliente}\n";
    echo "</pre>";

    echo "<span class='success'>✓ Password generate</span></div>";

    // Inserisci dati demo
    echo "<div class='step'><strong class='info'>👥 Inserimento utenti demo...</strong><br>";

    $pdo->exec("
        INSERT INTO utenti (email, password_hash, nome, cognome, azienda, telefono, ruolo, mfa_enabled, attivo) VALUES
        ('admin@finch-ai.it', '{$hashAdmin}', 'Mario', 'Rossi', 'Finch-AI Srl', '+39 02 1234567', 'admin', FALSE, TRUE),
        ('demo@finch-ai.it', '{$hashDemo}', 'Luigi', 'Verdi', 'Azienda Demo Srl', '+39 06 7654321', 'cliente', FALSE, TRUE),
        ('cliente@example.com', '{$hashCliente}', 'Paolo', 'Bianchi', 'Example Corp', '+39 011 9876543', 'cliente', FALSE, TRUE)
    ");

    echo "<span class='success'>✓ 3 utenti creati</span></div>";

    // Inserisci servizi
    echo "<div class='step'><strong class='info'>🔧 Inserimento servizi...</strong><br>";

    $pdo->exec("
        INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES
        ('Document Intelligence', 'OCR e validazione documenti automatica con AI', 'DOC-INT', 1500.00, TRUE),
        ('Production Analytics', 'Dashboard KPI e monitoraggio real-time', 'PROD-ANA', 1200.00, TRUE),
        ('Financial Control', 'Integrazione ERP e forecast economico', 'FIN-CTR', 1800.00, TRUE),
        ('Supply Chain Optimizer', 'Ottimizzazione logistica e inventario', 'SUP-OPT', 2000.00, TRUE),
        ('Quality Assurance AI', 'Controllo qualità automatizzato', 'QA-AI', 1600.00, TRUE)
    ");

    echo "<span class='success'>✓ 5 servizi creati</span></div>";

    // Assegna servizi agli utenti
    echo "<div class='step'><strong class='info'>🔗 Assegnazione servizi agli utenti...</strong><br>";

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

    echo "<span class='success'>✓ Servizi assegnati</span></div>";

    // Inserisci fatture
    echo "<div class='step'><strong class='info'>📄 Inserimento fatture...</strong><br>";

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

    echo "<span class='success'>✓ 8 fatture create</span></div>";

    // Inserisci scadenze
    echo "<div class='step'><strong class='info'>📅 Inserimento scadenze...</strong><br>";

    $pdo->exec("
        INSERT INTO scadenze (user_id, tipo, descrizione, data_scadenza, urgente, completata) VALUES
        (1, 'Pagamento', 'Fattura FT-2024-010', '2024-12-14', TRUE, FALSE),
        (1, 'Rinnovo', 'Rinnovo annuale servizi', '2024-12-31', FALSE, FALSE),
        (2, 'Pagamento', 'Fattura FT-2024-011', '2024-12-14', TRUE, FALSE),
        (2, 'Contratto', 'Rinnovo contratto servizi', '2024-12-31', FALSE, FALSE),
        (3, 'Pagamento', 'Fattura FT-2024-012', '2024-12-14', TRUE, FALSE)
    ");

    echo "<span class='success'>✓ 5 scadenze create</span></div>";

    // Riepilogo
    echo "<div class='step' style='background: rgba(16, 185, 129, 0.1); border-color: #10b981;'>";
    echo "<h2 class='success'>✅ Inizializzazione completata!</h2>";
    echo "<h3>📊 Credenziali di accesso:</h3>";
    echo "<pre>";
    echo "<strong>Admin:</strong>\n";
    echo "Email:    admin@finch-ai.it\n";
    echo "Password: {$passwordAdmin}\n\n";

    echo "<strong>Cliente Demo:</strong>\n";
    echo "Email:    demo@finch-ai.it\n";
    echo "Password: {$passwordDemo}\n\n";

    echo "<strong>Cliente Example:</strong>\n";
    echo "Email:    cliente@example.com\n";
    echo "Password: {$passwordCliente}\n";
    echo "</pre>";

    echo "<h3>🎯 Prossimi passi:</h3>";
    echo "<ol>";
    echo "<li><strong class='error'>ELIMINARE questo file (init.php) per sicurezza!</strong></li>";
    echo "<li>Configurare JWT_SECRET in config/database.php</li>";
    echo "<li>Testare login su /area-clienti.html</li>";
    echo "<li>Creare cartella /fatture/ per PDF reali</li>";
    echo "<li>Configurare HTTPS su Aruba</li>";
    echo "</ol>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Errore durante l'inizializzazione</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Verifica le credenziali in api/config/database.php</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Errore</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}

?>

<div style="margin-top: 40px; padding: 20px; background: rgba(248, 113, 113, 0.1); border: 2px solid #f87171; border-radius: 8px;">
    <h3 class="error">⚠️ IMPORTANTE - SICUREZZA</h3>
    <p><strong>Dopo aver eseguito questo script:</strong></p>
    <ul>
        <li>ELIMINARE questo file <code>init.php</code></li>
        <li>Modificare JWT_SECRET in <code>api/config/database.php</code></li>
        <li>Abilitare HTTPS sul server Aruba</li>
        <li>Cambiare le password demo degli utenti</li>
    </ul>
</div>

</body>
</html>
