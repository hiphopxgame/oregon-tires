-- Migration 007: Add appointment reminder email templates
-- Run on live DB: hiphopwo_oregon_tires
-- Date: 2026-02-19

SET NAMES utf8mb4;

-- ─── Appointment Reminder email template (bilingual) ─────────────────────────
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  ('email_tpl_reminder_subject', 'Reminder: Your Appointment Tomorrow — Oregon Tires', 'Recordatorio: Tu Cita Mañana — Oregon Tires'),
  ('email_tpl_reminder_greeting', 'Hello, {{name}}!', '¡Hola, {{name}}!'),
  ('email_tpl_reminder_body', 'This is a friendly reminder that your appointment for <strong>{{service}}</strong> is scheduled for <strong>{{date}}</strong> at <strong>{{time}}</strong>.<br><br>📍 <strong>8536 SE 82nd Ave, Portland, OR 97266</strong><br>📞 (503) 367-9714', 'Este es un recordatorio amistoso de que tu cita para <strong>{{service}}</strong> está programada para el <strong>{{date}}</strong> a las <strong>{{time}}</strong>.<br><br>📍 <strong>8536 SE 82nd Ave, Portland, OR 97266</strong><br>📞 (503) 367-9714'),
  ('email_tpl_reminder_button', 'View Our Location', 'Ver Nuestra Ubicación'),
  ('email_tpl_reminder_footer', 'If you need to reschedule or cancel, please call us at <strong>(503) 367-9714</strong>.', 'Si necesitas reprogramar o cancelar, por favor llámanos al <strong>(503) 367-9714</strong>.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
