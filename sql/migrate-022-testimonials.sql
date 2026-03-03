-- Migration 022: Testimonials management table
CREATE TABLE oretir_testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(200) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    review_text_en TEXT NOT NULL,
    review_text_es TEXT NOT NULL DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
