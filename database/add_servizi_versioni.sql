-- Versioning servizi
CREATE TABLE IF NOT EXISTS servizi_versioni (
  id INT AUTO_INCREMENT PRIMARY KEY,
  servizio_id INT NOT NULL,
  action VARCHAR(20) NOT NULL DEFAULT 'update',
  changed_fields TEXT NULL,
  old_data LONGTEXT NULL,
  new_data LONGTEXT NULL,
  changed_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES utenti(id) ON DELETE SET NULL,
  INDEX idx_servizio (servizio_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
