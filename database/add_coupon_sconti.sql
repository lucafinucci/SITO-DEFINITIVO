-- Coupon e sconti temporanei
CREATE TABLE IF NOT EXISTS coupon (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codice VARCHAR(50) NOT NULL UNIQUE,
  tipo ENUM('percentuale','fisso') NOT NULL DEFAULT 'percentuale',
  valore DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  data_inizio DATE NULL,
  data_fine DATE NULL,
  max_usi INT NULL,
  usi INT NOT NULL DEFAULT 0,
  attivo TINYINT(1) NOT NULL DEFAULT 1,
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clienti_sconti (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  servizio_id INT NULL,
  tipo ENUM('percentuale','fisso') NOT NULL DEFAULT 'percentuale',
  valore DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  data_inizio DATE NULL,
  data_fine DATE NULL,
  note TEXT NULL,
  attivo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE SET NULL,
  INDEX idx_cliente (cliente_id),
  INDEX idx_servizio (servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clienti_coupon (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  coupon_id INT NOT NULL,
  usato TINYINT(1) NOT NULL DEFAULT 0,
  assegnato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usato_il TIMESTAMP NULL,
  UNIQUE KEY uniq_cliente_coupon (cliente_id, coupon_id),
  FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  FOREIGN KEY (coupon_id) REFERENCES coupon(id) ON DELETE CASCADE,
  INDEX idx_cliente (cliente_id),
  INDEX idx_coupon (coupon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
