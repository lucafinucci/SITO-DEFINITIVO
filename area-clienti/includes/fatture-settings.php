<?php

function ensureFattureSettingsTable(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fatture_impostazioni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invio_modalita ENUM('manuale', 'automatico') NOT NULL DEFAULT 'manuale',
            mostra_cliente_solo_inviate TINYINT(1) NOT NULL DEFAULT 1,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function getFattureSettings(PDO $pdo) {
    ensureFattureSettingsTable($pdo);

    $defaults = [
        'invio_modalita' => 'manuale',
        'mostra_cliente_solo_inviate' => 1
    ];

    $stmt = $pdo->prepare('SELECT invio_modalita, mostra_cliente_solo_inviate FROM fatture_impostazioni ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return $defaults;
    }

    return [
        'invio_modalita' => $row['invio_modalita'] ?: $defaults['invio_modalita'],
        'mostra_cliente_solo_inviate' => (int)$row['mostra_cliente_solo_inviate']
    ];
}
