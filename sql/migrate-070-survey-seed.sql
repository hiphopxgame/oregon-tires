-- Migration 070: Seed default post-service survey

INSERT INTO oretir_surveys (title_en, title_es, description_en, description_es, trigger_event, delay_hours, is_active) VALUES
    ('Post-Service Satisfaction Survey',
     'Encuesta de Satisfacción Post-Servicio',
     'We value your feedback! Please take a moment to tell us about your experience.',
     '¡Valoramos sus comentarios! Por favor, tómese un momento para contarnos sobre su experiencia.',
     'ro_completed', 24, 1);

SET @survey_id = LAST_INSERT_ID();

INSERT INTO oretir_survey_questions (survey_id, question_en, question_es, question_type, sort_order, is_required) VALUES
    (@survey_id, 'How would you rate your overall experience?', '¿Cómo calificaría su experiencia general?', 'rating', 1, 1),
    (@survey_id, 'How likely are you to recommend Oregon Tires to a friend or colleague?', '¿Qué tan probable es que recomiende Oregon Tires a un amigo o colega?', 'nps', 2, 1),
    (@survey_id, 'How would you rate the quality of service?', '¿Cómo calificaría la calidad del servicio?', 'rating', 3, 1),
    (@survey_id, 'Any additional comments or suggestions?', '¿Algún comentario o sugerencia adicional?', 'text', 4, 0);

-- Seed email template for survey
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
    ('email_template_survey_subject_en', 'How was your experience at Oregon Tires?', 'How was your experience at Oregon Tires?'),
    ('email_template_survey_subject_es', '¿Cómo fue su experiencia en Oregon Tires?', '¿Cómo fue su experiencia en Oregon Tires?'),
    ('email_template_survey_greeting_en', 'Hi {name},', 'Hi {name},'),
    ('email_template_survey_greeting_es', 'Hola {name},', 'Hola {name},'),
    ('email_template_survey_body_en', 'Thank you for choosing Oregon Tires for your recent {service} service. We would love to hear about your experience! Please take a moment to complete our brief survey.', 'Thank you for choosing Oregon Tires for your recent {service} service.'),
    ('email_template_survey_body_es', 'Gracias por elegir Oregon Tires para su reciente servicio de {service}. ¡Nos encantaría conocer su experiencia! Por favor, tómese un momento para completar nuestra breve encuesta.', 'Gracias por elegir Oregon Tires para su reciente servicio de {service}.'),
    ('email_template_survey_button_en', 'Take Survey', 'Take Survey'),
    ('email_template_survey_button_es', 'Completar Encuesta', 'Completar Encuesta'),
    ('email_template_survey_footer_en', 'Your feedback helps us improve our service.', 'Your feedback helps us improve our service.'),
    ('email_template_survey_footer_es', 'Sus comentarios nos ayudan a mejorar nuestro servicio.', 'Sus comentarios nos ayudan a mejorar nuestro servicio.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
