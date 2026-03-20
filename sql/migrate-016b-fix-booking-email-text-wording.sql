-- Migration 016: Remove "text" references from booking confirmation email
-- The site doesn't send SMS, so stop promising texts
-- Date: 2026-03-03

UPDATE oretir_site_settings
SET value_en = REPLACE(value_en, 'call or text you shortly', 'contact you shortly'),
    value_es = REPLACE(value_es, 'Le llamaremos o enviaremos un mensaje de texto pronto', 'Nos comunicaremos con usted pronto')
WHERE setting_key = 'email_tpl_booking_body';
