-- Prezzi personalizzati per cliente/servizio
CREATE TABLE IF NOT EXISTS clienti_prezzi_personalizzati (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  servizio_id INT NOT NULL,
  prezzo_mensile DECIMAL(10,2) NOT NULL,
  costo_per_pagina DECIMAL(10,4) NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_cliente_servizio (cliente_id, servizio_id),
  CONSTRAINT fk_prezzi_cliente FOREIGN KEY (cliente_id) REFERENCES utenti(id) ON DELETE CASCADE,
  CONSTRAINT fk_prezzi_servizio FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
);
