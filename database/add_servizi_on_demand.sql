-- Servizi on-demand e acquisti una-tantum
CREATE TABLE IF NOT EXISTS servizi_on_demand (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  descrizione TEXT NULL,
  prezzo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  attivo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clienti_acquisti_onetime (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  servizio_id INT NOT NULL,
  quantita DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  prezzo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  totale DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  data_acquisto DATE NOT NULL,
  stato ENUM('da_fatturare','fatturato','annullato') NOT NULL DEFAULT 'da_fatturare',
  fattura_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  FOREIGN KEY (servizio_id) REFERENCES servizi_on_demand(id) ON DELETE CASCADE,
  INDEX idx_cliente (cliente_id),
  INDEX idx_data (data_acquisto),
  INDEX idx_stato (stato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
