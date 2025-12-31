-- Quote utilizzo servizi (documenti/mese)
CREATE TABLE IF NOT EXISTS servizi_quote (
  servizio_id INT NOT NULL PRIMARY KEY,
  quota_documenti_mese INT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clienti_quote (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  servizio_id INT NOT NULL,
  quota_documenti_mese INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_cliente_servizio (cliente_id, servizio_id),
  FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servizi_quota_uso (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  servizio_id INT NOT NULL,
  periodo CHAR(7) NOT NULL,
  documenti_usati INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_cliente_servizio_periodo (cliente_id, servizio_id, periodo),
  FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
  INDEX idx_periodo (periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
