<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Database Connection</h1>";

try {
    require __DIR__ . '/includes/db.php';
    echo "✓ Database connection OK<br>";

    // Test query utenti
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM utenti");
    $count = $stmt->fetchColumn();
    echo "✓ Utenti count: $count<br>";

    // Test query aziende
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM aziende");
    $count = $stmt->fetchColumn();
    echo "✓ Aziende count: $count<br>";

    // Test query aziende_servizi
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM aziende_servizi");
    $count = $stmt->fetchColumn();
    echo "✓ Aziende_servizi count: $count<br>";

    // Test query aziende_prezzi_personalizzati
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM aziende_prezzi_personalizzati");
    $count = $stmt->fetchColumn();
    echo "✓ Aziende_prezzi_personalizzati count: $count<br>";

    // Test la query della dashboard
    $clienteId = 1; // ID admin
    $stmt = $pdo->prepare('
        SELECT s.id, s.nome, s.descrizione, s.codice, s.prezzo_mensile, s.costo_per_pagina,
               app.prezzo_mensile AS prezzo_personalizzato,
               app.costo_per_pagina AS costo_per_pagina_personalizzato,
               COALESCE(app.prezzo_mensile, s.prezzo_mensile) AS prezzo_finale,
               COALESCE(app.costo_per_pagina, s.costo_per_pagina) AS costo_per_pagina_finale,
               ase.data_attivazione, ase.stato
        FROM utenti u
        INNER JOIN aziende_servizi ase ON u.azienda_id = ase.azienda_id
        JOIN servizi s ON ase.servizio_id = s.id
        LEFT JOIN aziende_prezzi_personalizzati app
          ON app.azienda_id = u.azienda_id AND app.servizio_id = s.id
        WHERE u.id = :user_id AND ase.stato = "attivo"
        ORDER BY ase.data_attivazione DESC
    ');
    $stmt->execute(['user_id' => $clienteId]);
    $servizi = $stmt->fetchAll();
    echo "✓ Servizi attivi query OK - Found " . count($servizi) . " servizi<br>";

    echo "<br><h2>Tutto OK!</h2>";

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
