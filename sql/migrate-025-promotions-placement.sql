-- Migration 025: Add placement support to promotions (exit-intent popup)
-- Run: mysql -u hiphopwo_rld_player -p hiphopwo_oregon_tires < sql/migrate-025-promotions-placement.sql

ALTER TABLE oretir_promotions
  ADD COLUMN placement VARCHAR(30) NOT NULL DEFAULT 'banner' AFTER id,
  ADD COLUMN subtitle_en VARCHAR(255) DEFAULT NULL AFTER body_es,
  ADD COLUMN subtitle_es VARCHAR(255) DEFAULT NULL AFTER subtitle_en,
  ADD COLUMN placeholder_en VARCHAR(100) DEFAULT NULL AFTER subtitle_es,
  ADD COLUMN placeholder_es VARCHAR(100) DEFAULT NULL AFTER placeholder_en,
  ADD COLUMN success_msg_en VARCHAR(255) DEFAULT NULL AFTER placeholder_es,
  ADD COLUMN success_msg_es VARCHAR(255) DEFAULT NULL AFTER success_msg_en,
  ADD COLUMN error_msg_en VARCHAR(255) DEFAULT NULL AFTER success_msg_es,
  ADD COLUMN error_msg_es VARCHAR(255) DEFAULT NULL AFTER error_msg_en,
  ADD COLUMN nospam_en VARCHAR(255) DEFAULT NULL AFTER error_msg_es,
  ADD COLUMN nospam_es VARCHAR(255) DEFAULT NULL AFTER nospam_en,
  ADD COLUMN popup_icon VARCHAR(20) DEFAULT NULL AFTER nospam_es;

ALTER TABLE oretir_promotions
  ADD INDEX idx_placement_active_dates (placement, is_active, starts_at, ends_at);

-- Seed the current hardcoded exit-intent popup as the first exit-intent promotion
INSERT INTO oretir_promotions (
  placement, title_en, title_es, body_en, body_es,
  subtitle_en, subtitle_es,
  cta_text_en, cta_text_es, cta_url,
  placeholder_en, placeholder_es,
  success_msg_en, success_msg_es,
  error_msg_en, error_msg_es,
  nospam_en, nospam_es,
  popup_icon, is_active, sort_order
) VALUES (
  'exit_intent',
  'Before You Go…', 'Antes de Irte…',
  'Enter your email and we''ll send you a coupon for a complimentary inspection on your next visit.',
  'Ingresa tu correo y te enviaremos un cupón para una inspección gratuita en tu próxima visita.',
  'Get a FREE 21-Point Vehicle Health Check',
  'Obtén una Inspección de 21 Puntos GRATIS',
  'Send My Free Coupon', 'Enviar Mi Cupón Gratis', '/api/subscribe.php',
  'your@email.com', 'tu@correo.com',
  'Check your email! Your coupon is on its way.',
  '¡Revisa tu correo! Tu cupón está en camino.',
  'Something went wrong. Please try again.',
  'Algo salió mal. Intenta de nuevo.',
  'No spam. Unsubscribe anytime.',
  'Sin spam. Cancela cuando quieras.',
  '🔍', 1, 0
);
