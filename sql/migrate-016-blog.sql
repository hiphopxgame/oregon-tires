-- Blog system tables
-- Migration 016: Blog posts, categories, and post-category relationships

CREATE TABLE IF NOT EXISTS oretir_blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title_en VARCHAR(255) NOT NULL,
    title_es VARCHAR(255) NOT NULL DEFAULT '',
    excerpt_en TEXT,
    excerpt_es TEXT,
    body_en LONGTEXT NOT NULL,
    body_es LONGTEXT NOT NULL DEFAULT '',
    featured_image VARCHAR(500) DEFAULT NULL,
    author VARCHAR(100) DEFAULT 'Oregon Tires',
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_published (status, published_at),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_blog_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name_en VARCHAR(100) NOT NULL,
    name_es VARCHAR(100) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_blog_post_categories (
    post_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, category_id),
    FOREIGN KEY (post_id) REFERENCES oretir_blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES oretir_blog_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed categories
INSERT INTO oretir_blog_categories (slug, name_en, name_es) VALUES
('tires', 'Tires', 'Llantas'),
('maintenance', 'Maintenance', 'Mantenimiento'),
('safety', 'Safety', 'Seguridad'),
('seasonal', 'Seasonal Tips', 'Consejos de Temporada')
ON DUPLICATE KEY UPDATE name_en = VALUES(name_en);
