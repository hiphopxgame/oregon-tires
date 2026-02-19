-- Migration 003: Add booking confirmation email template
-- Run on live DB: hiphopwo_oregon_tires
-- Date: 2026-02-18

SET NAMES utf8mb4;

-- ─── Booking confirmation email template ─────────────────────────────────────
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  ('email_tpl_booking_subject',  'Appointment Requested — Oregon Tires Auto Care', 'Cita Solicitada — Oregon Tires Auto Care'),
  ('email_tpl_booking_greeting', 'Thank you, {{name}}!', '¡Gracias, {{name}}!'),
  ('email_tpl_booking_body',     'We''ve received your appointment request for <strong>{{service}}</strong> on <strong>{{date}}</strong> at <strong>{{time}}</strong>.{{vehicle_line}}<br><br>We will call or text you shortly to confirm your appointment.', 'Hemos recibido su solicitud de cita para <strong>{{service}}</strong> el <strong>{{date}}</strong> a las <strong>{{time}}</strong>.{{vehicle_line}}<br><br>Le llamaremos o enviaremos un mensaje de texto pronto para confirmar su cita.'),
  ('email_tpl_booking_button',   'Visit Our Website', 'Visitar Nuestro Sitio'),
  ('email_tpl_booking_footer',   'If you need to reschedule, please call us at <strong>(503) 367-9714</strong>.<br>Mon–Sat 7:00 AM – 7:00 PM', 'Si necesita reprogramar, llámenos al <strong>(503) 367-9714</strong>.<br>Lun–Sáb 7:00 AM – 7:00 PM')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
