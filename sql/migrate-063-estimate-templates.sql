-- Migration 063: Estimate line item templates
-- Pre-built templates for common jobs to speed up estimate creation

CREATE TABLE IF NOT EXISTS oretir_estimate_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(200) NOT NULL,
  name_es VARCHAR(200) DEFAULT '',
  service_type VARCHAR(100) DEFAULT NULL,
  items JSON NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed 5 common templates
INSERT INTO oretir_estimate_templates (name_en, name_es, service_type, items, sort_order) VALUES
(
  'Oil Change',
  'Cambio de Aceite',
  'oil_change',
  '[{"type":"parts","description_en":"Conventional Motor Oil (5 qt)","description_es":"Aceite de Motor Convencional (5 qt)","quantity":1,"unit_price":29.99,"is_taxable":true},{"type":"parts","description_en":"Oil Filter","description_es":"Filtro de Aceite","quantity":1,"unit_price":8.99,"is_taxable":true},{"type":"labor","description_en":"Oil Change Labor","description_es":"Mano de Obra Cambio de Aceite","quantity":1,"unit_price":25.00,"is_taxable":false}]',
  1
),
(
  'Brake Pad Replacement',
  'Reemplazo de Pastillas de Freno',
  'brake_service',
  '[{"type":"parts","description_en":"Brake Pads (Front Set)","description_es":"Pastillas de Freno (Juego Delantero)","quantity":1,"unit_price":45.00,"is_taxable":true},{"type":"labor","description_en":"Brake Pad Installation","description_es":"Instalación de Pastillas de Freno","quantity":2,"unit_price":50.00,"is_taxable":false},{"type":"parts","description_en":"Brake Hardware Kit","description_es":"Kit de Herrajes de Freno","quantity":1,"unit_price":12.99,"is_taxable":true}]',
  2
),
(
  'Tire Rotation',
  'Rotación de Llantas',
  'tire_rotation',
  '[{"type":"labor","description_en":"Tire Rotation (4 tires)","description_es":"Rotación de Llantas (4 llantas)","quantity":1,"unit_price":35.00,"is_taxable":false},{"type":"labor","description_en":"Tire Pressure Check & Adjust","description_es":"Revisión y Ajuste de Presión de Llantas","quantity":1,"unit_price":0.00,"is_taxable":false}]',
  3
),
(
  'Wheel Alignment',
  'Alineación de Ruedas',
  'wheel_alignment',
  '[{"type":"labor","description_en":"4-Wheel Alignment","description_es":"Alineación de 4 Ruedas","quantity":1,"unit_price":89.99,"is_taxable":false},{"type":"labor","description_en":"Steering & Suspension Check","description_es":"Revisión de Dirección y Suspensión","quantity":1,"unit_price":0.00,"is_taxable":false}]',
  4
),
(
  'Engine Diagnostic',
  'Diagnóstico de Motor',
  'engine_diagnostics',
  '[{"type":"labor","description_en":"Computer Diagnostic Scan","description_es":"Escaneo de Diagnóstico por Computadora","quantity":1,"unit_price":75.00,"is_taxable":false},{"type":"labor","description_en":"Visual Inspection & Report","description_es":"Inspección Visual e Informe","quantity":1,"unit_price":25.00,"is_taxable":false}]',
  5
);
