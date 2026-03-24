-- Migration 069: Customer satisfaction surveys

CREATE TABLE IF NOT EXISTS oretir_surveys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(300) NOT NULL,
    title_es VARCHAR(300) DEFAULT NULL,
    description_en TEXT DEFAULT NULL,
    description_es TEXT DEFAULT NULL,
    trigger_event VARCHAR(50) NOT NULL DEFAULT 'ro_completed',
    delay_hours INT UNSIGNED NOT NULL DEFAULT 24,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_trigger (trigger_event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_survey_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id INT UNSIGNED NOT NULL,
    question_en VARCHAR(500) NOT NULL,
    question_es VARCHAR(500) DEFAULT NULL,
    question_type ENUM('rating','nps','text','multiple_choice') NOT NULL DEFAULT 'rating',
    options_json JSON DEFAULT NULL COMMENT 'Options for multiple_choice type',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_survey (survey_id),
    INDEX idx_sort (sort_order),
    CONSTRAINT fk_sq_survey FOREIGN KEY (survey_id) REFERENCES oretir_surveys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_survey_responses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id INT UNSIGNED NOT NULL,
    appointment_id INT UNSIGNED DEFAULT NULL,
    customer_id INT UNSIGNED DEFAULT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    started_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_survey (survey_id),
    INDEX idx_appointment (appointment_id),
    INDEX idx_customer (customer_id),
    INDEX idx_token (token),
    INDEX idx_completed (completed_at),
    CONSTRAINT fk_sr_survey FOREIGN KEY (survey_id) REFERENCES oretir_surveys(id) ON DELETE CASCADE,
    CONSTRAINT fk_sr_appointment FOREIGN KEY (appointment_id) REFERENCES oretir_appointments(id) ON DELETE SET NULL,
    CONSTRAINT fk_sr_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_survey_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    rating_value INT DEFAULT NULL COMMENT 'Numeric answer (1-5 for rating, 0-10 for NPS)',
    text_value TEXT DEFAULT NULL COMMENT 'Text answer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_response (response_id),
    INDEX idx_question (question_id),
    CONSTRAINT fk_sa_response FOREIGN KEY (response_id) REFERENCES oretir_survey_responses(id) ON DELETE CASCADE,
    CONSTRAINT fk_sa_question FOREIGN KEY (question_id) REFERENCES oretir_survey_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track survey sent status on appointments
ALTER TABLE oretir_appointments
    ADD COLUMN survey_sent TINYINT(1) DEFAULT 0 AFTER review_request_sent;
