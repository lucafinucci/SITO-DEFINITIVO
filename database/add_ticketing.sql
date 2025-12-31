-- ===============================================
-- Sistema Ticket Supporto
-- ===============================================

CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    oggetto VARCHAR(200) NOT NULL,
    priorita ENUM('normale', 'urgente') DEFAULT 'normale',
    stato ENUM('aperto', 'in_corso', 'chiuso') DEFAULT 'aperto',
    assigned_admin_id INT NULL,
    assigned_at TIMESTAMP NULL,
    ultimo_messaggio_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_admin_id) REFERENCES utenti(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_assigned (assigned_admin_id),
    INDEX idx_stato (stato),
    INDEX idx_priorita (priorita),
    INDEX idx_updated (updated_at),
    INDEX idx_ultimo_messaggio (ultimo_messaggio_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_ticket_messaggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    mittente_tipo ENUM('cliente', 'admin') NOT NULL,
    mittente_id INT NULL,
    messaggio TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (mittente_id) REFERENCES utenti(id) ON DELETE SET NULL,
    INDEX idx_ticket (ticket_id),
    INDEX idx_mittente (mittente_tipo),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrazione opzionale per istanze esistenti:
-- ALTER TABLE support_tickets MODIFY COLUMN priorita ENUM('normale', 'urgente') DEFAULT 'normale';
-- ALTER TABLE support_tickets MODIFY COLUMN stato ENUM('aperto', 'in_corso', 'chiuso') DEFAULT 'aperto';
-- UPDATE support_tickets SET priorita = 'normale' WHERE priorita IN ('bassa', 'media');
-- UPDATE support_tickets SET priorita = 'urgente' WHERE priorita = 'alta';
-- UPDATE support_tickets SET stato = 'in_corso' WHERE stato = 'in_lavorazione';
-- ALTER TABLE support_tickets ADD COLUMN assigned_admin_id INT NULL;
-- ALTER TABLE support_tickets ADD COLUMN assigned_at TIMESTAMP NULL;
-- ALTER TABLE support_tickets ADD FOREIGN KEY (assigned_admin_id) REFERENCES utenti(id) ON DELETE SET NULL;
