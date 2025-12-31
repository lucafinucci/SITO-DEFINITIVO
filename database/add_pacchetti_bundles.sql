-- Pacchetti (bundle di servizi)
CREATE TABLE IF NOT EXISTS pacchetti (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  descrizione TEXT NULL,
  prezzo_mensile DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  attivo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pacchetti_servizi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pacchetto_id INT NOT NULL,
  servizio_id INT NOT NULL,
  UNIQUE KEY uniq_pacchetto_servizio (pacchetto_id, servizio_id),
  FOREIGN KEY (pacchetto_id) REFERENCES pacchetti(id) ON DELETE CASCADE,
  FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clienti_pacchetti (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  pacchetto_id INT NOT NULL,
  data_inizio DATE NULL,
  data_fine DATE NULL,
  attivo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  FOREIGN KEY (pacchetto_id) REFERENCES pacchetti(id) ON DELETE CASCADE,
  INDEX idx_cliente (cliente_id),
  INDEX idx_pacchetto (pacchetto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
