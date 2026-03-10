-- Migration 026: Seed FAQ entries (idempotent — skips if rows already exist)
INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'What are your hours of operation?' AS question_en,
  '¿Cuál es su horario de atención?' AS question_es,
  'We are open Monday through Saturday, 7:00 AM to 7:00 PM. We are closed on Sundays. Walk-ins are welcome during business hours.' AS answer_en,
  'Estamos abiertos de lunes a sábado, de 7:00 AM a 7:00 PM. Cerramos los domingos. Se aceptan visitas sin cita durante el horario de atención.' AS answer_es,
  1 AS is_active, 1 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'What are your hours of operation?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'Do you speak Spanish?' AS question_en,
  '¿Hablan español?' AS question_es,
  'Yes! Our entire team is fully bilingual in English and Spanish. We can assist you in whichever language you prefer, from scheduling to explaining repairs.' AS answer_en,
  '¡Sí! Todo nuestro equipo es completamente bilingüe en inglés y español. Podemos atenderle en el idioma que prefiera, desde programar citas hasta explicar reparaciones.' AS answer_es,
  1 AS is_active, 2 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'Do you speak Spanish?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'Do I need an appointment?' AS question_en,
  '¿Necesito una cita?' AS question_es,
  'Appointments are recommended but not required. Walk-ins are always welcome and we offer same-day service for most jobs. Booking online helps us prepare for your visit and reduce wait times.' AS answer_en,
  'Las citas son recomendadas pero no obligatorias. Siempre aceptamos visitas sin cita y ofrecemos servicio el mismo día para la mayoría de los trabajos. Reservar en línea nos ayuda a prepararnos para su visita y reducir tiempos de espera.' AS answer_es,
  1 AS is_active, 3 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'Do I need an appointment?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'How long does a tire installation take?' AS question_en,
  '¿Cuánto tiempo toma una instalación de llantas?' AS question_es,
  'A standard 4-tire installation typically takes 45 minutes to 1 hour. This includes mounting, balancing, and a complimentary safety inspection.' AS answer_en,
  'Una instalación estándar de 4 llantas generalmente toma de 45 minutos a 1 hora. Esto incluye montaje, balanceo y una inspección de seguridad de cortesía.' AS answer_es,
  1 AS is_active, 4 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'How long does a tire installation take?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'Do you offer free estimates?' AS question_en,
  '¿Ofrecen presupuestos gratis?' AS question_es,
  'Yes, we provide free estimates on all services. Bring your vehicle in or book online and we will inspect it and give you an honest quote before any work begins.' AS answer_en,
  'Sí, ofrecemos presupuestos gratis en todos los servicios. Traiga su vehículo o reserve en línea y lo inspeccionaremos y le daremos una cotización honesta antes de comenzar cualquier trabajo.' AS answer_es,
  1 AS is_active, 5 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'Do you offer free estimates?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'What payment methods do you accept?' AS question_en,
  '¿Qué métodos de pago aceptan?' AS question_es,
  'We accept cash, all major credit and debit cards (Visa, Mastercard, American Express, Discover), Apple Pay, and Google Pay.' AS answer_en,
  'Aceptamos efectivo, todas las tarjetas de crédito y débito principales (Visa, Mastercard, American Express, Discover), Apple Pay y Google Pay.' AS answer_es,
  1 AS is_active, 6 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'What payment methods do you accept?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'Do you have a warranty on services?' AS question_en,
  '¿Tienen garantía en los servicios?' AS question_es,
  'Yes, all our services come with a 12-month / 12,000-mile warranty. If something is not right, bring it back and we will make it right at no extra cost.' AS answer_en,
  'Sí, todos nuestros servicios incluyen una garantía de 12 meses / 12,000 millas. Si algo no está bien, tráigalo de vuelta y lo corregiremos sin costo adicional.' AS answer_es,
  1 AS is_active, 7 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'Do you have a warranty on services?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'How do I know if I need new tires?' AS question_en,
  '¿Cómo sé si necesito llantas nuevas?' AS question_es,
  'Signs you need new tires include: tread depth below 2/32 of an inch, visible cracks or bulges in the sidewall, uneven tread wear, vibration while driving, or if your tires are over 6 years old. We offer free tire inspections to help you decide.' AS answer_en,
  'Señales de que necesita llantas nuevas incluyen: profundidad del dibujo menor a 2/32 de pulgada, grietas o protuberancias visibles en la pared lateral, desgaste desigual, vibración al conducir, o si sus llantas tienen más de 6 años. Ofrecemos inspecciones de llantas gratis para ayudarle a decidir.' AS answer_es,
  1 AS is_active, 8 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'How do I know if I need new tires?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'How often should I get an oil change?' AS question_en,
  '¿Con qué frecuencia debo cambiar el aceite?' AS question_es,
  'Most vehicles need an oil change every 3,000 to 5,000 miles for conventional oil, or every 5,000 to 7,500 miles for synthetic oil. Check your owner''s manual for your specific vehicle''s recommendation, or ask our team.' AS answer_en,
  'La mayoría de los vehículos necesitan un cambio de aceite cada 3,000 a 5,000 millas para aceite convencional, o cada 5,000 a 7,500 millas para aceite sintético. Consulte el manual de su vehículo para la recomendación específica, o pregunte a nuestro equipo.' AS answer_es,
  1 AS is_active, 9 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'How often should I get an oil change?');

INSERT INTO oretir_faq (question_en, question_es, answer_en, answer_es, is_active, sort_order)
SELECT * FROM (SELECT
  'Do you offer fleet services?' AS question_en,
  '¿Ofrecen servicios para flotas?' AS question_es,
  'Yes, we offer fleet maintenance programs for businesses of all sizes. This includes priority scheduling, volume pricing, detailed service records, and dedicated account management. Contact us to set up a fleet account.' AS answer_en,
  'Sí, ofrecemos programas de mantenimiento para flotas de empresas de todos los tamaños. Esto incluye programación prioritaria, precios por volumen, registros detallados de servicio y gestión de cuenta dedicada. Contáctenos para configurar una cuenta de flota.' AS answer_es,
  1 AS is_active, 10 AS sort_order
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM oretir_faq WHERE question_en = 'Do you offer fleet services?');
