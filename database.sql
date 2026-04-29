-- Codexx3 SMM Panel v3 - Base de datos completa
-- Compatible con MySQL / Railway

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  balance DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS providers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  api_url VARCHAR(255) NOT NULL,
  api_key VARCHAR(255) NOT NULL,
  balance DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_providers_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NULL,
  provider_service_id VARCHAR(80) NULL,
  category VARCHAR(120) NOT NULL DEFAULT 'General',
  name VARCHAR(190) NOT NULL,
  description TEXT NULL,
  rate DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  cost_rate DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  min INT UNSIGNED NOT NULL DEFAULT 1,
  max INT UNSIGNED NOT NULL DEFAULT 10000,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_services_active (active),
  INDEX idx_services_category (category),
  INDEX idx_services_provider (provider_id),
  CONSTRAINT fk_services_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NULL,
  link TEXT NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  charge DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  profit DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  status ENUM('pending','processing','inprogress','completed','partial','canceled','failed') NOT NULL DEFAULT 'pending',
  provider_order_id VARCHAR(120) NULL,
  provider_response TEXT NULL,
  remains INT UNSIGNED NULL,
  start_count INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_status (status),
  INDEX idx_orders_service (service_id),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_orders_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS deposits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  amount DECIMAL(12,4) NOT NULL,
  method VARCHAR(120) NOT NULL DEFAULT 'manual',
  note TEXT NULL,
  status ENUM('pending','completed','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_deposits_user (user_id),
  INDEX idx_deposits_status (status),
  CONSTRAINT fk_deposits_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,
  type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  value DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  max_uses INT UNSIGNED NULL,
  used_count INT UNSIGNED NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  expires_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  details TEXT NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_user (user_id),
  INDEX idx_logs_action (action),
  CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario admin de prueba
-- Email: admin@codexx3.com
-- Password: admin123
INSERT INTO users (email, password, balance, is_admin, status)
VALUES ('admin@codexx3.com', '$2y$10$0aKT4cYuxbqG8KbcIYzIxOxHgONMqczBteGSjYgZvGp5jUjLjMCzG', 100.0000, 1, 'active')
ON DUPLICATE KEY UPDATE is_admin=1;

-- Datos de ejemplo seguros: debes cambiar api_url y api_key por tu proveedor real.
INSERT INTO providers (name, api_url, api_key, active)
VALUES ('Proveedor Demo', 'https://example.com/api/v2', 'CAMBIA_ESTA_API_KEY', 0)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO services (provider_id, provider_service_id, category, name, description, rate, cost_rate, min, max, active)
VALUES
(NULL, NULL, 'Instagram', 'Instagram Likes Demo', 'Servicio demo. Configura proveedor antes de vender.', 1.5000, 0.8000, 10, 10000, 1),
(NULL, NULL, 'TikTok', 'TikTok Views Demo', 'Servicio demo. Configura proveedor antes de vender.', 0.9000, 0.4000, 100, 100000, 1)
ON DUPLICATE KEY UPDATE name=name;
