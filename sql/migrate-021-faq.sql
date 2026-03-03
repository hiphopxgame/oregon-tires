-- Migration 021: FAQ management table
CREATE TABLE oretir_faq (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_en VARCHAR(500) NOT NULL,
    question_es VARCHAR(500) NOT NULL DEFAULT '',
    answer_en TEXT NOT NULL,
    answer_es TEXT NOT NULL DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
