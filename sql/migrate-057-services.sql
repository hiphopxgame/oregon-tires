-- Migration 057: DB-driven services
-- Replaces hardcoded service pages with admin-managed services

CREATE TABLE IF NOT EXISTS oretir_services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  name_en VARCHAR(200) NOT NULL,
  name_es VARCHAR(200) NOT NULL DEFAULT '',
  description_en TEXT NOT NULL,
  description_es TEXT NOT NULL,
  body_en MEDIUMTEXT NOT NULL,
  body_es MEDIUMTEXT NOT NULL,
  icon VARCHAR(20) NOT NULL DEFAULT '',
  color_hex VARCHAR(7) NOT NULL DEFAULT '#10B981',
  color_bg VARCHAR(50) NOT NULL DEFAULT 'bg-green-100',
  color_text VARCHAR(50) NOT NULL DEFAULT 'text-green-800',
  color_dark_bg VARCHAR(50) NOT NULL DEFAULT 'dark:bg-green-900/30',
  color_dark_text VARCHAR(50) NOT NULL DEFAULT 'dark:text-green-300',
  color_dot VARCHAR(50) NOT NULL DEFAULT 'bg-green-500',
  price_display_en VARCHAR(100) NOT NULL DEFAULT '',
  price_display_es VARCHAR(100) NOT NULL DEFAULT '',
  category ENUM('tires','maintenance','specialized') NOT NULL DEFAULT 'maintenance',
  is_bookable TINYINT(1) NOT NULL DEFAULT 1,
  has_detail_page TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  image_url VARCHAR(500) NOT NULL DEFAULT '',
  custom_sections_html MEDIUMTEXT,
  custom_scripts_html TEXT,
  custom_translations TEXT,
  duration_estimate VARCHAR(20) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active_sort (is_active, sort_order),
  INDEX idx_bookable (is_bookable, is_active),
  INDEX idx_category (category, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_service_faqs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id INT UNSIGNED NOT NULL,
  question_en VARCHAR(500) NOT NULL,
  question_es VARCHAR(500) NOT NULL DEFAULT '',
  answer_en TEXT NOT NULL,
  answer_es TEXT NOT NULL DEFAULT '',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES oretir_services(id) ON DELETE CASCADE,
  INDEX idx_service_sort (service_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_service_related (
  service_id INT UNSIGNED NOT NULL,
  related_service_id INT UNSIGNED NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (service_id, related_service_id),
  FOREIGN KEY (service_id) REFERENCES oretir_services(id) ON DELETE CASCADE,
  FOREIGN KEY (related_service_id) REFERENCES oretir_services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
