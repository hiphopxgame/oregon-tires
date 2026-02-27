-- Migration 017: Seasonal Promotions System
-- Creates oretir_promotions table for time-bound promotional banners

CREATE TABLE IF NOT EXISTS oretir_promotions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_es VARCHAR(255) NOT NULL DEFAULT '',
    body_en TEXT,
    body_es TEXT,
    cta_text_en VARCHAR(100) DEFAULT 'Book Now',
    cta_text_es VARCHAR(100) DEFAULT 'Reserve Ahora',
    cta_url VARCHAR(500) DEFAULT '/book-appointment/',
    bg_color VARCHAR(20) DEFAULT '#f59e0b',
    text_color VARCHAR(20) DEFAULT '#000000',
    badge_text VARCHAR(50) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    starts_at DATETIME DEFAULT NULL,
    ends_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_dates (is_active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed a sample promotion
INSERT INTO oretir_promotions (title_en, title_es, body_en, body_es, cta_text_en, cta_text_es, badge_text, starts_at, ends_at) VALUES
('Spring Tire Sale — Save 15% on All Tire Installations!',
 '¡Venta de Llantas de Primavera — Ahorre 15% en Todas las Instalaciones!',
 'Get ready for spring with new tires. 15% off installation on all tires through March 31st.',
 'Prepárese para la primavera con llantas nuevas. 15% de descuento en instalación hasta el 31 de marzo.',
 'Book Now', 'Reserve Ahora', 'SPRING SALE',
 '2026-03-01 00:00:00', '2026-03-31 23:59:59');
