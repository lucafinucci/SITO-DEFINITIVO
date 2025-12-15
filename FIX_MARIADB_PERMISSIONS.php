<?php
/**
 * Fix permessi MariaDB - Soluzione definitiva
 * Questo script si connette via socket/named pipe invece di TCP
 */

echo "\n================================================\n";
echo "FIX PERMESSI MARIADB\n";
echo "================================================\n\n";

// Prova diversi metodi di connessione
$connectionMethods = [
    [
        'name' => 'Socket (named pipe)',
        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;charset=utf8mb4',
    ],
    [
        'name' => 'Socket Windows',
        'dsn' => 'mysql:unix_socket=C:/xampp/mysql/mysql.sock;charset=utf8mb4',
    ],
    [
        'name' => 'Localhost con forza TCP',
        'dsn' => 'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
        'options' => [PDO::MYSQL_ATTR_LOCAL_INFILE => true]
    ],
    [
        'name' => 'IP diretto',
        'dsn' => 'mysql:host=127.0.0.1;charset=utf8mb4',
    ],
];

$pdo = null;
$successMethod = null;

foreach ($connectionMethods as $method) {
    echo "Tentativo: {$method['name']}...\n";

    try {
        $options = isset($method['options']) ? $method['options'] : [];
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;

        $pdo = new PDO($method['dsn'], 'root', '', $options);
        $successMethod = $method['name'];
        echo "✓ Connessione riuscita con: {$method['name']}!\n\n";
        break;
    } catch (PDOException $e) {
        echo "  ✗ Fallito: {$e->getMessage()}\n";
    }
}

if (!$pdo) {
    echo "\n❌ ERRORE CRITICO\n";
    echo "Nessun metodo di connessione funziona.\n\n";
    echo "ULTIMA SOLUZIONE:\n";
    echo "Dobbiamo accedere direttamente al file my.ini di MySQL\n";
    echo "e commentare la riga 'skip-grant-tables'\n\n";

    echo "OPPURE usa questo workaround:\n";
    echo "1. Ferma MySQL da XAMPP\n";
    echo "2. Apri C:\\xampp\\mysql\\bin\\my.ini\n";
    echo "3. Cerca la sezione [mysqld]\n";
    echo "4. Aggiungi questa riga:\n";
    echo "   skip-grant-tables\n";
    echo "5. Riavvia MySQL\n";
    echo "6. Riesegui questo script\n\n";

    exit(1);
}

echo "Connessione stabilita tramite: $successMethod\n";
echo "================================================\n\n";

try {
    echo "Step 1: Creazione permessi per root@localhost...\n";

    // Crea/aggiorna permessi per root
    $commands = [
        "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING '' WITH GRANT OPTION",
        "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING '' WITH GRANT OPTION",
        "GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' IDENTIFIED VIA mysql_native_password USING '' WITH GRANT OPTION",
        "FLUSH PRIVILEGES"
    ];

    foreach ($commands as $cmd) {
        try {
            $pdo->exec($cmd);
            echo "✓ Eseguito: " . substr($cmd, 0, 50) . "...\n";
        } catch (PDOException $e) {
            // Ignora errori se l'utente esiste già
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "  ⚠ Warning: {$e->getMessage()}\n";
            }
        }
    }

    echo "\n✓ Permessi aggiornati!\n\n";

    echo "Step 2: Test nuova connessione localhost...\n";

    // Prova a connettersi di nuovo
    try {
        $testPdo = new PDO(
            "mysql:host=127.0.0.1;port=3306;charset=utf8mb4",
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        echo "✓ Test connessione 127.0.0.1 RIUSCITO!\n\n";

        // Ora importa il database
        echo "Step 3: Importazione database...\n";

        $sqlFile = __DIR__ . '/database/ESEGUI_IN_PHPMYADMIN.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("File SQL non trovato: $sqlFile");
        }

        $sql = file_get_contents($sqlFile);
        $testPdo->exec($sql);

        echo "✓ Database importato!\n\n";

        // Verifica
        $testPdo->exec("USE finch_ai_clienti");
        $stmt = $testPdo->query("SELECT COUNT(*) as cnt FROM utenti");
        $count = $stmt->fetch()['cnt'];

        echo "Step 4: Verifica...\n";
        echo "✓ Utenti trovati: $count\n\n";

        echo "================================================\n";
        echo "SUCCESS! Tutto configurato!\n";
        echo "================================================\n\n";

        echo "CREDENZIALI DI TEST:\n";
        echo "Email:    demo@finch-ai.it\n";
        echo "Password: Demo123!\n\n";

        echo "Vai su: http://localhost/area-clienti/login.php\n\n";

    } catch (PDOException $e) {
        echo "✗ Test connessione fallito: {$e->getMessage()}\n\n";
        echo "I permessi sono stati aggiornati, ma serve riavviare MySQL.\n";
        echo "RIAVVIA MySQL dal Control Panel XAMPP e riprova!\n\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERRORE:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
