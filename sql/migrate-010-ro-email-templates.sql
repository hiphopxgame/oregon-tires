-- Migration 010: RO Email Templates — Inspection, Estimate, Approval, Vehicle Ready
-- Run in cPanel phpMyAdmin or MySQL CLI
-- Database: hiphopwo_oregon_tires

INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  -- Inspection Report Email
  ('email_tpl_inspection_subject',  'Vehicle Inspection Report — {{ro_number}}', 'Reporte de Inspección del Vehículo — {{ro_number}}'),
  ('email_tpl_inspection_greeting', 'Hello {{name}},', 'Hola {{name}},'),
  ('email_tpl_inspection_body',     'We''ve completed a digital vehicle inspection on your <strong>{{vehicle}}</strong>. Our technician has documented the condition of key components with photos and ratings. Click below to view your full inspection report.', 'Hemos completado una inspección digital de su <strong>{{vehicle}}</strong>. Nuestro técnico ha documentado el estado de los componentes clave con fotos y calificaciones. Haga clic abajo para ver su reporte completo de inspección.'),
  ('email_tpl_inspection_button',   'View Inspection Report', 'Ver Reporte de Inspección'),
  ('email_tpl_inspection_footer',   'This report is available for 90 days. If you have questions, call us at (503) 367-9714.', 'Este reporte está disponible por 90 días. Si tiene preguntas, llámenos al (503) 367-9714.'),

  -- Estimate for Approval Email
  ('email_tpl_estimate_subject',    'Estimate Ready for Approval — {{ro_number}}', 'Presupuesto Listo para Aprobación — {{ro_number}}'),
  ('email_tpl_estimate_greeting',   'Hello {{name}},', 'Hola {{name}},'),
  ('email_tpl_estimate_body',       'We''ve prepared an estimate for your <strong>{{vehicle}}</strong> totaling <strong>{{total}}</strong>. You can review each recommended service and approve the ones you''d like us to perform. Click below to review and approve your estimate.', 'Hemos preparado un presupuesto para su <strong>{{vehicle}}</strong> con un total de <strong>{{total}}</strong>. Puede revisar cada servicio recomendado y aprobar los que desee que realicemos. Haga clic abajo para revisar y aprobar su presupuesto.'),
  ('email_tpl_estimate_button',     'Review & Approve Estimate', 'Revisar y Aprobar Presupuesto'),
  ('email_tpl_estimate_footer',     'This estimate is valid for 30 days. Questions? Call us at (503) 367-9714.', 'Este presupuesto es válido por 30 días. ¿Preguntas? Llámenos al (503) 367-9714.'),

  -- Approval Confirmation Email
  ('email_tpl_approval_subject',    'Estimate Approved — Work Starting on {{vehicle}}', 'Presupuesto Aprobado — Trabajo Comenzando en {{vehicle}}'),
  ('email_tpl_approval_greeting',   'Thank you, {{name}}!', '¡Gracias, {{name}}!'),
  ('email_tpl_approval_body',       'You''ve approved services for your <strong>{{vehicle}}</strong>. Our team will begin work shortly. We''ll notify you as soon as your vehicle is ready for pickup.', 'Ha aprobado los servicios para su <strong>{{vehicle}}</strong>. Nuestro equipo comenzará el trabajo pronto. Le notificaremos tan pronto como su vehículo esté listo para recoger.'),
  ('email_tpl_approval_button',     'View Approved Services', 'Ver Servicios Aprobados'),
  ('email_tpl_approval_footer',     'Estimated completion: {{date}}. Questions? Call us at (503) 367-9714.', 'Finalización estimada: {{date}}. ¿Preguntas? Llámenos al (503) 367-9714.'),

  -- Vehicle Ready for Pickup Email
  ('email_tpl_vehicle_ready_subject',  'Your Vehicle is Ready! — {{ro_number}}', '¡Su Vehículo Está Listo! — {{ro_number}}'),
  ('email_tpl_vehicle_ready_greeting', 'Great news, {{name}}!', '¡Buenas noticias, {{name}}!'),
  ('email_tpl_vehicle_ready_body',     'Your <strong>{{vehicle}}</strong> is ready for pickup at Oregon Tires Auto Care. All approved services have been completed. We''re open Mon-Sat 7AM-7PM.', 'Su <strong>{{vehicle}}</strong> está listo para recoger en Oregon Tires Auto Care. Todos los servicios aprobados han sido completados. Estamos abiertos Lun-Sab 7AM-7PM.'),
  ('email_tpl_vehicle_ready_button',   'Get Directions', 'Obtener Direcciones'),
  ('email_tpl_vehicle_ready_footer',   'Thank you for choosing Oregon Tires Auto Care! We appreciate your business.', '¡Gracias por elegir Oregon Tires Auto Care! Apreciamos su negocio.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
