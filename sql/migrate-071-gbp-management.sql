-- Migration 071: Google Business Profile management

CREATE TABLE IF NOT EXISTS oretir_gbp_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    google_post_id VARCHAR(255) DEFAULT NULL,
    post_type ENUM('update','offer','event') NOT NULL DEFAULT 'update',
    title_en VARCHAR(300) DEFAULT NULL,
    title_es VARCHAR(300) DEFAULT NULL,
    body_en TEXT DEFAULT NULL,
    body_es TEXT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    cta_type VARCHAR(50) DEFAULT NULL COMMENT 'LEARN_MORE, BOOK, ORDER, SHOP, SIGN_UP, CALL',
    cta_url VARCHAR(500) DEFAULT NULL,
    offer_start DATE DEFAULT NULL,
    offer_end DATE DEFAULT NULL,
    event_start DATETIME DEFAULT NULL,
    event_end DATETIME DEFAULT NULL,
    status ENUM('draft','published','failed','expired','deleted') NOT NULL DEFAULT 'draft',
    publish_error TEXT DEFAULT NULL,
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (post_type),
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_gbp_insights (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metric_date DATE NOT NULL UNIQUE,
    views_search INT UNSIGNED DEFAULT 0,
    views_maps INT UNSIGNED DEFAULT 0,
    clicks_website INT UNSIGNED DEFAULT 0,
    clicks_directions INT UNSIGNED DEFAULT 0,
    clicks_phone INT UNSIGNED DEFAULT 0,
    photo_views INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_gbp_qna (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    google_question_id VARCHAR(255) DEFAULT NULL UNIQUE,
    question_text TEXT NOT NULL,
    answer_text TEXT DEFAULT NULL,
    author_name VARCHAR(200) DEFAULT NULL,
    status ENUM('unanswered','answered','ignored') NOT NULL DEFAULT 'unanswered',
    asked_at TIMESTAMP NULL DEFAULT NULL,
    answered_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
