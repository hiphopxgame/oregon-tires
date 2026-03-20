// Oregon Tires Admin — Feature Categories & Data
// 17 categories, 113 features. Freelancer value: ~$52,400 ($50/hr).
// Base prices are internal scope estimates; displayed values use FREELANCER_RATE.
// Source of truth: synced with project-value report (March 2026).

var CLIENT_PRICE = 5000;
var FREELANCER_RATE = 1/3;

// Features the client originally requested.
// Mapped from their requirements document (Operations + Sales + Marketing).
// Everything NOT in this list was added by the developer as bonus value.
var CLIENT_SCOPE_IDS = [
  // Website Foundation — bilingual website, SSL, SEO, social links
  '1-1','1-2','1-4','1-5','1-7',
  // Public Pages — homepage, contact, about, reviews, gallery, services, areas, blog, promotions, financing, tire quote, feedback
  '2-1','2-2','2-4','2-5','2-7','2-8','2-11','2-12','2-13','2-14','2-15','2-16',
  // Regional SEO — local search visibility
  '3-1',
  // Booking — scheduling with time blocks, cancel/reschedule, confirmation emails, business hours, multi-bay
  '4-1','4-4','4-5','4-7','4-8','4-9',
  // Shop Management — basic RO lifecycle
  '5-1',
  // Customer Management — CRM database, vehicle records, language pref
  '6-1','6-2','6-5',
  // Employee Portal — schedule management, assigned work
  '8-1','8-2',
  // Auth & Security — access control, password reset, CSRF, admin setup
  '9-1','9-3','9-4','9-5',
  // Admin Panel — dashboard with analytics, appointments, customers, employees, content, site settings, business hours, feedback
  '10-1','10-2','10-3','10-4','10-5','10-8','10-10','10-11',
  // Customer Engagement — roadside estimator, tire quotes, waitlist
  '11-4','11-5','11-6',
  // Communications — bilingual email system, email logging
  '12-1','12-5',
  // Automation — appointment reminders, review requests
  '14-1','14-2'
];

var FEATURE_CATS = {
  '1':  { en: 'Website Foundation', es: 'Fundación del Sitio Web' },
  '2':  { en: 'Public Pages', es: 'Páginas Públicas' },
  '3':  { en: 'Regional SEO Pages', es: 'Páginas SEO Regionales' },
  '4':  { en: 'Booking & Appointments', es: 'Reservas y Citas' },
  '5':  { en: 'Shop Management — Repair Orders', es: 'Gestión del Taller — Órdenes de Reparación' },
  '6':  { en: 'Customer Management', es: 'Gestión de Clientes' },
  '7':  { en: 'Customer Portal — Member Dashboard', es: 'Portal del Cliente — Panel de Miembros' },
  '8':  { en: 'Employee Portal', es: 'Portal de Empleados' },
  '9':  { en: 'Authentication & Security', es: 'Autenticación y Seguridad' },
  '10': { en: 'Admin Panel', es: 'Panel de Administración' },
  '11': { en: 'Customer Engagement', es: 'Participación del Cliente' },
  '12': { en: 'Communications', es: 'Comunicaciones' },
  '13': { en: 'Push Notifications & PWA', es: 'Notificaciones Push y PWA' },
  '14': { en: 'Automation & Cron', es: 'Automatización y Cron' },
  '15': { en: 'Integrations', es: 'Integraciones' },
  '16': { en: 'Data & Admin Tools', es: 'Datos y Herramientas Admin' },
  '17': { en: 'Performance & Infrastructure', es: 'Rendimiento e Infraestructura' }
};

var FEATURE_DATA = [
  // 1. Website Foundation ($9,700)
  { id:'1-1', cat:'1', status:'complete', price:3000, name_en:'Custom responsive website (Tailwind CSS v4)', name_es:'Sitio web responsivo personalizado (Tailwind CSS v4)', desc_en:'Mobile-first layout, component-based design.', desc_es:'Diseño mobile-first basado en componentes.' },
  { id:'1-2', cat:'1', status:'complete', price:2500, name_en:'Bilingual system (EN/ES)', name_es:'Sistema bilingüe (EN/ES)', desc_en:'Full inline translation system with data-t attributes.', desc_es:'Sistema completo de traducción en línea con atributos data-t.' },
  { id:'1-3', cat:'1', status:'complete', price:800, name_en:'Dark mode support', name_es:'Soporte de modo oscuro', desc_en:'Tailwind v4 class-based dark theme toggle.', desc_es:'Alternancia de tema oscuro basada en clases Tailwind v4.' },
  { id:'1-4', cat:'1', status:'complete', price:500, name_en:'SSL + security headers + .htaccess hardening', name_es:'SSL + encabezados de seguridad + endurecimiento .htaccess', desc_en:'HTTPS redirect, XSS/clickjack protection, file blocking.', desc_es:'Redirección HTTPS, protección XSS/clickjack, bloqueo de archivos.' },
  { id:'1-5', cat:'1', status:'complete', price:1500, name_en:'SEO foundation (meta tags, OG, JSON-LD schema)', name_es:'Fundación SEO (meta tags, OG, esquema JSON-LD)', desc_en:'Per-page config, Organization schema, canonical URLs.', desc_es:'Configuración por página, esquema Organization, URLs canónicas.' },
  { id:'1-6', cat:'1', status:'complete', price:1000, name_en:'Clean URL routing (.php stripping + path-based)', name_es:'Enrutamiento de URLs limpias (eliminación .php + basado en rutas)', desc_en:'301 redirects, friendly URLs throughout.', desc_es:'Redirecciones 301, URLs amigables en todo el sitio.' },
  { id:'1-7', cat:'1', status:'complete', price:400, name_en:'Social media links + footer integration', name_es:'Enlaces de redes sociales + integración en pie de página', desc_en:'Dynamic social links across all pages.', desc_es:'Enlaces sociales dinámicos en todas las páginas.' },

  // 2. Public Pages ($15,400)
  { id:'2-1', cat:'2', status:'complete', price:2000, name_en:'Homepage with hero + dynamic background', name_es:'Página principal con hero + fondo dinámico', desc_en:'Hero section, 7 service cards, CTAs, bilingual.', desc_es:'Sección hero, 7 tarjetas de servicio, CTAs, bilingüe.' },
  { id:'2-2', cat:'2', status:'complete', price:800, name_en:'Contact page + form', name_es:'Página de contacto + formulario', desc_en:'Bilingual, validates, stores to DB.', desc_es:'Bilingüe, valida, almacena en BD.' },
  { id:'2-3', cat:'2', status:'complete', price:600, name_en:'FAQ page (dynamic)', name_es:'Página de preguntas frecuentes (dinámica)', desc_en:'Pulls from DB, bilingual, admin-managed.', desc_es:'Extrae de BD, bilingüe, gestionado por admin.' },
  { id:'2-4', cat:'2', status:'complete', price:500, name_en:'Why Us / About page', name_es:'Página Por Qué Nosotros / Acerca de', desc_en:'Value proposition, trust signals.', desc_es:'Propuesta de valor, señales de confianza.' },
  { id:'2-5', cat:'2', status:'complete', price:1000, name_en:'Google Reviews display page', name_es:'Página de reseñas de Google', desc_en:'Live Google reviews via Places API.', desc_es:'Reseñas de Google en vivo vía Places API.' },
  { id:'2-6', cat:'2', status:'complete', price:400, name_en:'Service Guarantee page', name_es:'Página de Garantía de Servicio', desc_en:'Static content page.', desc_es:'Página de contenido estático.' },
  { id:'2-7', cat:'2', status:'complete', price:1500, name_en:'Blog listing + single post pages', name_es:'Listado de blog + páginas de publicación', desc_en:'CMS-driven, SEO-optimized.', desc_es:'Impulsado por CMS, optimizado para SEO.' },
  { id:'2-8', cat:'2', status:'complete', price:800, name_en:'Promotions page', name_es:'Página de promociones', desc_en:'Dynamic from DB, image + placement targeting.', desc_es:'Dinámico desde BD, imagen + segmentación de ubicación.' },
  { id:'2-9', cat:'2', status:'complete', price:1500, name_en:'Care Plan info + enrollment page', name_es:'Página de plan de cuidado + inscripción', desc_en:'PayPal subscription integration.', desc_es:'Integración de suscripción PayPal.' },
  { id:'2-10', cat:'2', status:'complete', price:1200, name_en:'Checkout page', name_es:'Página de pago', desc_en:'Card + PayPal payment flow.', desc_es:'Flujo de pago con tarjeta + PayPal.' },
  { id:'2-11', cat:'2', status:'complete', price:400, name_en:'Financing page', name_es:'Página de financiamiento', desc_en:'Informational + lead capture.', desc_es:'Informativa + captura de prospectos.' },
  { id:'2-12', cat:'2', status:'complete', price:800, name_en:'Tire Quote request page', name_es:'Página de solicitud de cotización de llantas', desc_en:'Form + DB storage + admin management.', desc_es:'Formulario + almacenamiento BD + gestión admin.' },
  { id:'2-13', cat:'2', status:'complete', price:800, name_en:'Photo gallery page', name_es:'Página de galería de fotos', desc_en:'Bilingual captions, video support, lightbox.', desc_es:'Subtítulos bilingües, soporte de video, lightbox.' },
  { id:'2-14', cat:'2', status:'complete', price:1500, name_en:'10 service pages (tire, brake, oil, etc.)', name_es:'10 páginas de servicio (llantas, frenos, aceite, etc.)', desc_en:'SEO-optimized service descriptions.', desc_es:'Descripciones de servicio optimizadas para SEO.' },
  { id:'2-15', cat:'2', status:'complete', price:500, name_en:'Service Areas overview page', name_es:'Página de resumen de áreas de servicio', desc_en:'Regional targeting.', desc_es:'Segmentación regional.' },
  { id:'2-16', cat:'2', status:'complete', price:600, name_en:'Feedback submission page', name_es:'Página de envío de comentarios', desc_en:'Customer feedback form + DB storage.', desc_es:'Formulario de comentarios + almacenamiento BD.' },
  { id:'2-17', cat:'2', status:'complete', price:500, name_en:'System status page', name_es:'Página de estado del sistema', desc_en:'Platform health + uptime display.', desc_es:'Salud de la plataforma + visualización de tiempo activo.' },

  // 3. Regional SEO Pages ($2,400)
  { id:'3-1', cat:'3', status:'complete', price:2400, name_en:'8 regional SEO pages (Portland neighborhoods)', name_es:'8 páginas SEO regionales (vecindarios de Portland)', desc_en:'SE Portland, Woodstock, Lents, Happy Valley, etc.', desc_es:'SE Portland, Woodstock, Lents, Happy Valley, etc.' },

  // 4. Booking & Appointments ($13,300)
  { id:'4-1', cat:'4', status:'complete', price:3500, name_en:'Online booking form with time slot availability', name_es:'Formulario de reserva en línea con disponibilidad de horarios', desc_en:'Date picker, slot API, auto-create customer + vehicle.', desc_es:'Selector de fecha, API de horarios, creación automática de cliente + vehículo.' },
  { id:'4-2', cat:'4', status:'complete', price:2000, name_en:'VIN decode in booking (NHTSA API)', name_es:'Decodificación VIN en reserva (API NHTSA)', desc_en:'Permanent DB cache, auto-fill vehicle info.', desc_es:'Caché permanente en BD, auto-llenar info del vehículo.' },
  { id:'4-3', cat:'4', status:'complete', price:1500, name_en:'License plate lookup in booking', name_es:'Búsqueda de placa en reserva', desc_en:'Plate-to-vehicle with DB cache.', desc_es:'Placa a vehículo con caché en BD.' },
  { id:'4-4', cat:'4', status:'complete', price:1500, name_en:'Appointment cancel + reschedule (token-based)', name_es:'Cancelar + reprogramar cita (basado en token)', desc_en:'Email links, bilingual confirmation.', desc_es:'Enlaces por email, confirmación bilingüe.' },
  { id:'4-5', cat:'4', status:'complete', price:800, name_en:'SMS opt-in + booking confirmation emails', name_es:'Opt-in SMS + emails de confirmación de reserva', desc_en:'Opt-in checkbox, bilingual confirmation with calendar link.', desc_es:'Casilla de opt-in, confirmación bilingüe con enlace de calendario.' },
  { id:'4-6', cat:'4', status:'complete', price:500, name_en:'Calendar event (.ics) download', name_es:'Descarga de evento de calendario (.ics)', desc_en:'Downloadable calendar event for any booking.', desc_es:'Evento de calendario descargable para cualquier reserva.' },
  { id:'4-7', cat:'4', status:'complete', price:1500, name_en:'Configurable business hours + holiday calendar', name_es:'Horario configurable + calendario de días festivos', desc_en:'Admin-editable hours, holiday closures, slot blocking.', desc_es:'Horas editables por admin, cierres por festivos, bloqueo de horarios.' },
  { id:'4-8', cat:'4', status:'complete', price:1200, name_en:'Multi-bay capacity + schedule-aware slots', name_es:'Capacidad multi-bahía + horarios inteligentes', desc_en:'Per-slot bay limits, employee schedule integration.', desc_es:'Límites de bahía por horario, integración de horarios de empleados.' },
  { id:'4-9', cat:'4', status:'complete', price:800, name_en:'Service-specific intake fields', name_es:'Campos de recepción por servicio', desc_en:'Tire preference (new/used), tire count, service type selection.', desc_es:'Preferencia de llanta (nueva/usada), cantidad, selección de tipo de servicio.' },

  // 5. Shop Management — Repair Orders ($20,000)
  { id:'5-1', cat:'5', status:'complete', price:5000, name_en:'Repair Order (RO) lifecycle (10 stages)', name_es:'Ciclo de vida de Orden de Reparación (10 etapas)', desc_en:'Intake to invoiced, full status tracking.', desc_es:'Desde recepción hasta facturado, seguimiento completo de estado.' },
  { id:'5-2', cat:'5', status:'complete', price:3000, name_en:'Kanban board (drag-and-drop)', name_es:'Tablero Kanban (arrastrar y soltar)', desc_en:'Visual RO management, time-in-status display.', desc_es:'Gestión visual de órdenes, visualización de tiempo en estado.' },
  { id:'5-3', cat:'5', status:'complete', price:3500, name_en:'Digital Vehicle Inspection (DVI)', name_es:'Inspección Digital de Vehículos (DVI)', desc_en:'35 items across 12 categories, traffic light system, photo capture.', desc_es:'35 ítems en 12 categorías, sistema semafórico, captura de fotos.' },
  { id:'5-4', cat:'5', status:'complete', price:2500, name_en:'Estimate builder + approval system', name_es:'Constructor de cotizaciones + sistema de aprobación', desc_en:'Auto-generate from inspection, per-item approve/decline, 8 statuses.', desc_es:'Auto-generar desde inspección, aprobar/rechazar por partida, 8 estados.' },
  { id:'5-5', cat:'5', status:'complete', price:2000, name_en:'Invoice generation from completed ROs', name_es:'Generación de facturas desde órdenes completadas', desc_en:'Token-based customer view, bilingual.', desc_es:'Vista de cliente basada en token, bilingüe.' },
  { id:'5-6', cat:'5', status:'complete', price:2000, name_en:'Labor hours tracking per RO', name_es:'Seguimiento de horas de trabajo por orden', desc_en:'Technician time tracking, admin UI.', desc_es:'Seguimiento de tiempo de técnicos, interfaz admin.' },
  { id:'5-7', cat:'5', status:'complete', price:1200, name_en:'Visit tracking (check-in/out)', name_es:'Rastreo de visitas (entrada/salida)', desc_en:'Customer visit log with timestamps.', desc_es:'Registro de visitas del cliente con marcas de tiempo.' },
  { id:'5-8', cat:'5', status:'complete', price:800, name_en:'Print-optimized reports', name_es:'Reportes optimizados para impresión', desc_en:'Inspection + estimate + invoice print layouts.', desc_es:'Diseños de impresión para inspección + cotización + factura.' },

  // 6. Customer Management ($6,000)
  { id:'6-1', cat:'6', status:'complete', price:2000, name_en:'Customer database with search', name_es:'Base de datos de clientes con búsqueda', desc_en:'Persistent records, email unique, admin CRUD.', desc_es:'Registros persistentes, email único, CRUD admin.' },
  { id:'6-2', cat:'6', status:'complete', price:1500, name_en:'Vehicle records per customer', name_es:'Registros de vehículos por cliente', desc_en:'VIN, year/make/model, tire sizes, member linking.', desc_es:'VIN, año/marca/modelo, tamaños de llantas, vinculación de miembros.' },
  { id:'6-3', cat:'6', status:'complete', price:1000, name_en:'Tire fitment lookup (API + cache)', name_es:'Búsqueda de compatibilidad de llantas (API + caché)', desc_en:'90-day DB cache, year/make/model lookup.', desc_es:'Caché de BD de 90 días, búsqueda por año/marca/modelo.' },
  { id:'6-4', cat:'6', status:'complete', price:1000, name_en:'Smart account linking', name_es:'Vinculación inteligente de cuenta', desc_en:'Auto-links booking customers to member accounts.', desc_es:'Vincula automáticamente clientes de reservas a cuentas de miembros.' },
  { id:'6-5', cat:'6', status:'complete', price:500, name_en:'Customer language preference tracking', name_es:'Seguimiento de preferencia de idioma del cliente', desc_en:'Per-customer EN/ES preference for communications.', desc_es:'Preferencia EN/ES por cliente para comunicaciones.' },

  // 7. Customer Portal — Member Dashboard ($7,500)
  { id:'7-1', cat:'7', status:'complete', price:2000, name_en:'Member registration + login (bilingual)', name_es:'Registro + inicio de sesión de miembros (bilingüe)', desc_en:'Custom auth UI, email verification.', desc_es:'Interfaz de autenticación personalizada, verificación de email.' },
  { id:'7-2', cat:'7', status:'complete', price:1500, name_en:'My Appointments (view/reschedule/cancel)', name_es:'Mis Citas (ver/reprogramar/cancelar)', desc_en:'Member booking history + actions.', desc_es:'Historial de reservas del miembro + acciones.' },
  { id:'7-3', cat:'7', status:'complete', price:1500, name_en:'My Vehicles + My Estimates + My Invoices', name_es:'Mis Vehículos + Mis Cotizaciones + Mis Facturas', desc_en:'Customer self-service portal.', desc_es:'Portal de autoservicio del cliente.' },
  { id:'7-4', cat:'7', status:'complete', price:1500, name_en:'My Messages (customer-to-shop)', name_es:'Mis Mensajes (cliente a taller)', desc_en:'Two-way conversation threads.', desc_es:'Hilos de conversación bidireccionales.' },
  { id:'7-5', cat:'7', status:'complete', price:1000, name_en:'My Care Plan (subscription status)', name_es:'Mi Plan de Cuidado (estado de suscripción)', desc_en:'Billing status, plan details.', desc_es:'Estado de facturación, detalles del plan.' },

  // 8. Employee Portal ($7,700)
  { id:'8-1', cat:'8', status:'complete', price:1500, name_en:'Employee schedule management', name_es:'Gestión de horarios de empleados', desc_en:'Admin sets schedules, employees view theirs.', desc_es:'Admin configura horarios, empleados ven los suyos.' },
  { id:'8-2', cat:'8', status:'complete', price:1500, name_en:'My Assigned Work (employee RO view)', name_es:'Mi Trabajo Asignado (vista de órdenes del empleado)', desc_en:'Employee sees their assigned repair orders.', desc_es:'Empleado ve sus órdenes de reparación asignadas.' },
  { id:'8-3', cat:'8', status:'complete', price:1500, name_en:'My Customers (employee view)', name_es:'Mis Clientes (vista del empleado)', desc_en:'Employee customer relationships.', desc_es:'Relaciones de clientes del empleado.' },
  { id:'8-4', cat:'8', status:'complete', price:1000, name_en:'Skills & certifications tracking', name_es:'Seguimiento de habilidades y certificaciones', desc_en:'Employee qualifications, searchable by admin.', desc_es:'Calificaciones del empleado, búsqueda por admin.' },
  { id:'8-5', cat:'8', status:'complete', price:1000, name_en:'Schedule overrides + daily capacity', name_es:'Excepciones de horario + capacidad diaria', desc_en:'Per-date exceptions, per-employee bay limits.', desc_es:'Excepciones por fecha, límites de bahía por empleado.' },
  { id:'8-6', cat:'8', status:'complete', price:1200, name_en:'Job assignment + notification system', name_es:'Asignación de trabajo + sistema de notificación', desc_en:'Assign tech to appointment, auto-notify via email.', desc_es:'Asignar técnico a cita, notificación automática por email.' },

  // 9. Authentication & Security ($6,000)
  { id:'9-1', cat:'9', status:'complete', price:2000, name_en:'Role-based access control (admin/employee/member)', name_es:'Control de acceso basado en roles (admin/empleado/miembro)', desc_en:'Tab visibility, API authorization.', desc_es:'Visibilidad de pestañas, autorización de API.' },
  { id:'9-2', cat:'9', status:'complete', price:1500, name_en:'Google OAuth login', name_es:'Inicio de sesión con Google OAuth', desc_en:'Login with Google, link/unlink account.', desc_es:'Iniciar sesión con Google, vincular/desvincular cuenta.' },
  { id:'9-3', cat:'9', status:'complete', price:1000, name_en:'Password reset flow (token-based, bilingual)', name_es:'Flujo de restablecimiento de contraseña (basado en token, bilingüe)', desc_en:'Email-based reset with secure tokens.', desc_es:'Restablecimiento por email con tokens seguros.' },
  { id:'9-4', cat:'9', status:'complete', price:1000, name_en:'CSRF protection + session management', name_es:'Protección CSRF + gestión de sesiones', desc_en:'Token validation, session regeneration.', desc_es:'Validación de tokens, regeneración de sesiones.' },
  { id:'9-5', cat:'9', status:'complete', price:500, name_en:'Admin setup email system (invite tokens)', name_es:'Sistema de email de configuración de admin (tokens de invitación)', desc_en:'Onboard new admin users via email.', desc_es:'Incorporar nuevos usuarios admin por email.' },

  // 10. Admin Panel ($15,900)
  { id:'10-1', cat:'10', status:'complete', price:2500, name_en:'Admin dashboard with analytics charts', name_es:'Panel admin con gráficos analíticos', desc_en:'Chart.js — revenue, appointments, traffic, conversion funnel.', desc_es:'Chart.js — ingresos, citas, tráfico, embudo de conversión.' },
  { id:'10-2', cat:'10', status:'complete', price:1500, name_en:'Appointment management tab', name_es:'Pestaña de gestión de citas', desc_en:'Calendar + list view, status management.', desc_es:'Vista calendario + lista, gestión de estado.' },
  { id:'10-3', cat:'10', status:'complete', price:1500, name_en:'Customer + Vehicle management tabs', name_es:'Pestañas de gestión de clientes + vehículos', desc_en:'CRUD, search, vehicle history.', desc_es:'CRUD, búsqueda, historial de vehículos.' },
  { id:'10-4', cat:'10', status:'complete', price:1500, name_en:'Employee management + skills tracking', name_es:'Gestión de empleados + seguimiento de habilidades', desc_en:'CRUD, certifications, schedule config.', desc_es:'CRUD, certificaciones, configuración de horarios.' },
  { id:'10-5', cat:'10', status:'complete', price:2000, name_en:'Content management (Blog, FAQ, Promotions, Testimonials)', name_es:'Gestión de contenido (Blog, FAQ, Promociones, Testimonios)', desc_en:'4 content types, bilingual, image upload.', desc_es:'4 tipos de contenido, bilingüe, carga de imágenes.' },
  { id:'10-6', cat:'10', status:'complete', price:800, name_en:'Gallery management (bilingual captions)', name_es:'Gestión de galería (subtítulos bilingües)', desc_en:'Image upload, ordering, video support, bilingual.', desc_es:'Carga de imágenes, ordenamiento, soporte de video, bilingüe.' },
  { id:'10-7', cat:'10', status:'complete', price:700, name_en:'Subscriber management', name_es:'Gestión de suscriptores', desc_en:'Newsletter list, export.', desc_es:'Lista de boletín, exportación.' },
  { id:'10-8', cat:'10', status:'complete', price:1500, name_en:'Site settings editor + email template config', name_es:'Editor de configuración del sitio + config de plantillas de email', desc_en:'Editable site content, business hours, email templates.', desc_es:'Contenido editable del sitio, horario comercial, plantillas de email.' },
  { id:'10-9', cat:'10', status:'complete', price:2500, name_en:'Resource planner (multi-date scheduling)', name_es:'Planificador de recursos (programación multi-fecha)', desc_en:'Employee grid, skill gaps, hourly breakdown, recommendations.', desc_es:'Cuadrícula de empleados, brechas de habilidades, desglose por hora, recomendaciones.' },
  { id:'10-10', cat:'10', status:'complete', price:800, name_en:'Business hours + holiday configuration', name_es:'Configuración de horario comercial + festivos', desc_en:'Admin UI for hours, holidays, slot capacity.', desc_es:'Interfaz admin para horarios, festivos, capacidad de slots.' },
  { id:'10-11', cat:'10', status:'complete', price:600, name_en:'Feedback management tab', name_es:'Pestaña de gestión de comentarios', desc_en:'View + respond to customer feedback submissions.', desc_es:'Ver + responder a envíos de comentarios de clientes.' },

  // 11. Customer Engagement ($9,700)
  { id:'11-1', cat:'11', status:'complete', price:2500, name_en:'Care plan subscriptions (PayPal recurring)', name_es:'Suscripciones de plan de cuidado (PayPal recurrente)', desc_en:'3-tier plans, enrollment, webhook billing.', desc_es:'Planes de 3 niveles, inscripción, facturación por webhook.' },
  { id:'11-2', cat:'11', status:'complete', price:2000, name_en:'Loyalty points program', name_es:'Programa de puntos de lealtad', desc_en:'Points ledger, redeemable rewards catalog.', desc_es:'Registro de puntos, catálogo de recompensas canjeables.' },
  { id:'11-3', cat:'11', status:'complete', price:1500, name_en:'Customer referral program', name_es:'Programa de referidos de clientes', desc_en:'Referral codes, tracking, bonus points.', desc_es:'Códigos de referido, seguimiento, puntos de bonificación.' },
  { id:'11-4', cat:'11', status:'complete', price:1500, name_en:'Walk-in waitlist queue', name_es:'Cola de espera para walk-in', desc_en:'Join/check queue, admin management.', desc_es:'Unirse/verificar cola, gestión admin.' },
  { id:'11-5', cat:'11', status:'complete', price:1000, name_en:'Tire quote request system', name_es:'Sistema de solicitud de cotización de llantas', desc_en:'Submit request, admin responds.', desc_es:'Enviar solicitud, admin responde.' },
  { id:'11-6', cat:'11', status:'complete', price:1200, name_en:'Roadside assistance estimator', name_es:'Estimador de asistencia en carretera', desc_en:'Service-specific cost estimation tool.', desc_es:'Herramienta de estimación de costos por servicio.' },

  // 12. Communications ($11,800)
  { id:'12-1', cat:'12', status:'complete', price:2000, name_en:'Bilingual email system (PHPMailer)', name_es:'Sistema de email bilingüe (PHPMailer)', desc_en:'6+ template types, branded HTML emails.', desc_es:'6+ tipos de plantilla, emails HTML con marca.' },
  { id:'12-2', cat:'12', status:'complete', price:2500, name_en:'In-app messaging (admin-to-customer)', name_es:'Mensajería en la app (admin a cliente)', desc_en:'Conversation threads, real-time notification bell.', desc_es:'Hilos de conversación, campana de notificación en tiempo real.' },
  { id:'12-3', cat:'12', status:'complete', price:2500, name_en:'Inbound email integration (IMAP)', name_es:'Integración de email entrante (IMAP)', desc_en:'Auto-fetch emails into conversations, Message-ID threading.', desc_es:'Auto-obtener emails en conversaciones, encadenamiento por Message-ID.' },
  { id:'12-4', cat:'12', status:'complete', price:1500, name_en:'SMS notifications (Twilio)', name_es:'Notificaciones SMS (Twilio)', desc_en:'Appointment reminders, inspection/estimate/ready alerts.', desc_es:'Recordatorios de citas, alertas de inspección/cotización/listo.' },
  { id:'12-5', cat:'12', status:'complete', price:1000, name_en:'Email audit trail + logging', name_es:'Registro y auditoría de emails', desc_en:'Full email log with status tracking.', desc_es:'Registro completo de emails con seguimiento de estado.' },
  { id:'12-6', cat:'12', status:'complete', price:1500, name_en:'Email template variable system', name_es:'Sistema de variables de plantillas de email', desc_en:'Dynamic template vars, admin reference.', desc_es:'Variables dinámicas de plantilla, referencia admin.' },
  { id:'12-7', cat:'12', status:'complete', price:800, name_en:'Estimate expiry reminder emails', name_es:'Emails de recordatorio de vencimiento de cotización', desc_en:'Automated follow-up for pending estimates.', desc_es:'Seguimiento automatizado para cotizaciones pendientes.' },

  // 13. Push Notifications & PWA ($9,000)
  { id:'13-1', cat:'13', status:'complete', price:2000, name_en:'Progressive Web App (PWA)', name_es:'Aplicación Web Progresiva (PWA)', desc_en:'Installable, manifest, service worker caching.', desc_es:'Instalable, manifiesto, caché de service worker.' },
  { id:'13-2', cat:'13', status:'complete', price:2500, name_en:'Web Push notifications (VAPID)', name_es:'Notificaciones Web Push (VAPID)', desc_en:'Browser push subscriptions, language prefs.', desc_es:'Suscripciones push del navegador, preferencias de idioma.' },
  { id:'13-3', cat:'13', status:'complete', price:1500, name_en:'Notification queue (bilingual, targeted)', name_es:'Cola de notificaciones (bilingüe, dirigida)', desc_en:'Subscription/customer/broadcast targeting, retry logic.', desc_es:'Segmentación por suscripción/cliente/transmisión, lógica de reintento.' },
  { id:'13-4', cat:'13', status:'complete', price:2000, name_en:'Offline booking form (IndexedDB + Background Sync)', name_es:'Formulario de reserva offline (IndexedDB + Background Sync)', desc_en:'Queue submissions offline, replay when online.', desc_es:'Encolar envíos offline, reproducir cuando haya conexión.' },
  { id:'13-5', cat:'13', status:'complete', price:1000, name_en:'Admin push broadcast (rate-limited)', name_es:'Transmisión push admin (con límite de tasa)', desc_en:'Send to opted-in subscribers, 5/day limit.', desc_es:'Enviar a suscriptores opt-in, límite de 5/día.' },

  // 14. Automation & Cron ($7,000)
  { id:'14-1', cat:'14', status:'complete', price:1000, name_en:'Appointment reminder emails (next-day)', name_es:'Emails de recordatorio de citas (día siguiente)', desc_en:'Daily 6PM cron, email + SMS + push.', desc_es:'Cron diario 6PM, email + SMS + push.' },
  { id:'14-2', cat:'14', status:'complete', price:1000, name_en:'Review request emails (post-service)', name_es:'Emails de solicitud de reseña (post-servicio)', desc_en:'Daily 10AM cron, Google review prompts.', desc_es:'Cron diario 10AM, solicitudes de reseña Google.' },
  { id:'14-3', cat:'14', status:'complete', price:1000, name_en:'Google Reviews auto-fetch + cache', name_es:'Auto-obtención de reseñas Google + caché', desc_en:'Daily 6AM cron, Places API.', desc_es:'Cron diario 6AM, Places API.' },
  { id:'14-4', cat:'14', status:'complete', price:800, name_en:'Push notification queue processor', name_es:'Procesador de cola de notificaciones push', desc_en:'Every 5 min, processes queued notifications.', desc_es:'Cada 5 min, procesa notificaciones en cola.' },
  { id:'14-5', cat:'14', status:'complete', price:1000, name_en:'Service reminder automation', name_es:'Automatización de recordatorios de servicio', desc_en:'Weekly Mon 9AM, due date tracking.', desc_es:'Semanal lunes 9AM, seguimiento de fechas de vencimiento.' },
  { id:'14-6', cat:'14', status:'complete', price:1200, name_en:'Estimate expiry reminder emails', name_es:'Emails de recordatorio de vencimiento de cotización', desc_en:'Automated follow-up for pending estimates.', desc_es:'Seguimiento automatizado para cotizaciones pendientes.' },
  { id:'14-7', cat:'14', status:'complete', price:1000, name_en:'Inbound email fetch (IMAP polling)', name_es:'Obtención de email entrante (sondeo IMAP)', desc_en:'Every 2 min, auto-thread into conversations.', desc_es:'Cada 2 min, auto-encadenar en conversaciones.' },

  // 15. Integrations ($7,300)
  { id:'15-1', cat:'15', status:'complete', price:2000, name_en:'PayPal payments + webhooks', name_es:'Pagos PayPal + webhooks', desc_en:'Checkout, subscriptions, IPN handling.', desc_es:'Checkout, suscripciones, manejo de IPN.' },
  { id:'15-2', cat:'15', status:'complete', price:1000, name_en:'Google OAuth integration', name_es:'Integración Google OAuth', desc_en:'API integration for identity.', desc_es:'Integración de API para identidad.' },
  { id:'15-3', cat:'15', status:'complete', price:1500, name_en:'Google Places API (reviews)', name_es:'API Google Places (reseñas)', desc_en:'Fetch + cache business reviews.', desc_es:'Obtener + cachear reseñas del negocio.' },
  { id:'15-4', cat:'15', status:'complete', price:1500, name_en:'NHTSA vPIC API (VIN decode)', name_es:'API NHTSA vPIC (decodificación VIN)', desc_en:'Permanent cache, vehicle info auto-fill.', desc_es:'Caché permanente, auto-llenado de info del vehículo.' },
  { id:'15-5', cat:'15', status:'complete', price:800, name_en:'Cloudflare CDN integration', name_es:'Integración CDN Cloudflare', desc_en:'Edge caching, DDoS protection, content negotiation.', desc_es:'Caché en borde, protección DDoS, negociación de contenido.' },
  { id:'15-6', cat:'15', status:'complete', price:500, name_en:'API versioning system', name_es:'Sistema de versionamiento de API', desc_en:'v1 alias via .htaccess, X-API-Version header.', desc_es:'Alias v1 vía .htaccess, encabezado X-API-Version.' },

  // 16. Data & Admin Tools ($5,500)
  { id:'16-1', cat:'16', status:'complete', price:800, name_en:'Data export (CSV)', name_es:'Exportación de datos (CSV)', desc_en:'Customers, appointments, ROs.', desc_es:'Clientes, citas, órdenes de reparación.' },
  { id:'16-2', cat:'16', status:'complete', price:700, name_en:'Rate limiting (per-IP/user)', name_es:'Limitación de tasa (por IP/usuario)', desc_en:'Protects public APIs from abuse.', desc_es:'Protege APIs públicas contra abuso.' },
  { id:'16-3', cat:'16', status:'complete', price:1500, name_en:'Global search (Ctrl+K)', name_es:'Búsqueda global (Ctrl+K)', desc_en:'Search across all admin data.', desc_es:'Buscar en todos los datos admin.' },
  { id:'16-4', cat:'16', status:'complete', price:1500, name_en:'Advanced reporting dashboard', name_es:'Panel de reportes avanzados', desc_en:'Employee productivity, revenue trends, conversion funnel.', desc_es:'Productividad de empleados, tendencias de ingresos, embudo de conversión.' },
  { id:'16-5', cat:'16', status:'complete', price:1000, name_en:'Enhanced analytics (multi-chart)', name_es:'Analítica mejorada (multi-gráfico)', desc_en:'Stacked bars, trend lines, period comparison.', desc_es:'Barras apiladas, líneas de tendencia, comparación de períodos.' },

  // 17. Performance & Infrastructure ($3,000)
  { id:'17-1', cat:'17', status:'complete', price:1000, name_en:'Image optimization pipeline (AVIF + WebP)', name_es:'Pipeline de optimización de imágenes (AVIF + WebP)', desc_en:'Responsive picture tags, content negotiation.', desc_es:'Etiquetas picture responsivas, negociación de contenido.' },
  { id:'17-2', cat:'17', status:'complete', price:1000, name_en:'Error tracking (engine-kit, DB fallback)', name_es:'Seguimiento de errores (engine-kit, respaldo BD)', desc_en:'3-tier: Sentry, DB, error_log.', desc_es:'3 niveles: Sentry, BD, error_log.' },
  { id:'17-3', cat:'17', status:'complete', price:1000, name_en:'Health check endpoint + deploy system', name_es:'Endpoint de verificación de salud + sistema de despliegue', desc_en:'Automated deploys, health verification.', desc_es:'Despliegues automatizados, verificación de salud.' }
];

// Category icons for overview display
var FEATURE_ICONS = {
  '1': '\uD83C\uDFD7\uFE0F', '2': '\uD83D\uDCC4', '3': '\uD83D\uDCCD', '4': '\uD83D\uDCC5',
  '5': '\uD83D\uDD27', '6': '\uD83D\uDC65', '7': '\uD83D\uDC64', '8': '\uD83D\uDC77',
  '9': '\uD83D\uDD12', '10': '\u2699\uFE0F', '11': '\u2B50', '12': '\uD83D\uDCE7',
  '13': '\uD83D\uDD14', '14': '\u23F0', '15': '\uD83D\uDD17', '16': '\uD83D\uDCCA',
  '17': '\u26A1'
};

// Links to live pages for specific features
var FEATURE_LINKS = {
  '2-1': '/', '2-2': '/contact', '2-3': '/faq', '2-5': '/reviews',
  '2-7': '/blog', '2-8': '/promotions', '2-9': '/care-plan', '2-10': '/checkout',
  '2-11': '/financing', '2-13': '/gallery', '2-14': '/tire-installation',
  '2-16': '/feedback', '2-17': '/status', '3-1': '/tires-se-portland',
  '4-1': '/book-appointment/', '7-1': '/members', '11-6': '/roadside-assistance'
};

var COMING_SOON_DATA = [
  { id:'CS1', status:'scaffold_ready', emoji:'\uD83D\uDCF1', price:1500,
    name_en:'SMS Notifications (Twilio)', name_es:'Notificaciones SMS (Twilio)',
    desc_en:'Automatic bilingual text message reminders before appointments. Reduces no-shows.',
    desc_es:'Recordatorios automáticos bilingües por mensaje de texto antes de citas. Reduce ausencias.',
    note_en:'Scaffold ready \u2014 needs Twilio credentials', note_es:'Estructura lista \u2014 necesita credenciales Twilio' },
  { id:'CS2', status:'scaffold_ready', emoji:'\uD83D\uDCAC', price:1500,
    name_en:'WhatsApp Integration', name_es:'Integración WhatsApp',
    desc_en:'Send appointment confirmations and status updates via WhatsApp. Popular with Spanish-speaking customers.',
    desc_es:'Envío de confirmaciones y actualizaciones de estado por WhatsApp. Popular entre clientes hispanohablantes.',
    note_en:'Scaffold ready \u2014 needs Twilio WhatsApp credentials', note_es:'Estructura lista \u2014 necesita credenciales WhatsApp de Twilio' },
  { id:'CS3', status:'planned', emoji:'\uD83D\uDCB3', price:2000,
    name_en:'Stripe Payment Integration', name_es:'Integración de Pagos Stripe',
    desc_en:'Accept credit/debit cards directly on the website alongside PayPal. Apple Pay and Google Pay support.',
    desc_es:'Aceptar tarjetas de crédito/débito directamente en el sitio junto con PayPal. Soporte Apple Pay y Google Pay.' },
  { id:'CS4', status:'planned', emoji:'\uD83D\uDCE6', price:5000,
    name_en:'Inventory Management', name_es:'Gestión de Inventario',
    desc_en:'Track tire stock, parts, and supplies. Low-stock alerts. Link inventory to repair orders.',
    desc_es:'Rastrear stock de llantas, partes y suministros. Alertas de bajo stock. Vincular a órdenes de reparación.' },
  { id:'CS5', status:'planned', emoji:'\uD83D\uDED2', price:8000,
    name_en:'Online Tire Ordering', name_es:'Pedidos de Llantas en Línea',
    desc_en:'Customer-facing tire catalog with fitment lookup, pricing, and online ordering with installation scheduling.',
    desc_es:'Catálogo de llantas con búsqueda de compatibilidad, precios y pedidos en línea con programación de instalación.' },
  { id:'CS6', status:'planned', emoji:'\uD83D\uDE9A', price:8000,
    name_en:'Fleet Management Portal', name_es:'Portal de Gestión de Flotillas',
    desc_en:'Dedicated dashboard for fleet customers: manage multiple vehicles, schedule bulk services, track spending.',
    desc_es:'Panel dedicado para clientes de flotilla: gestionar múltiples vehículos, servicios masivos, rastrear gastos.' },
  { id:'CS7', status:'planned', emoji:'\uD83D\uDCE7', price:2500,
    name_en:'Automated Follow-Up Campaigns', name_es:'Campañas de Seguimiento Automatizadas',
    desc_en:'Marketing email sequences based on service history: tire rotation, oil change, brake inspection reminders.',
    desc_es:'Secuencias de email marketing basadas en historial: rotación de llantas, cambio de aceite, inspecciones de frenos.' },
  { id:'CS8', status:'planned', emoji:'\uD83D\uDD27', price:2000,
    name_en:'Tire Recommendation Engine', name_es:'Motor de Recomendación de Llantas',
    desc_en:'When a customer vehicle is on file, proactively show compatible tire options with pricing during booking or on dashboard.',
    desc_es:'Cuando un vehículo está en archivo, mostrar opciones de llantas compatibles con precios durante la reserva o en el panel.' },
  { id:'CS9', status:'planned', emoji:'\uD83C\uDF1F', price:1000,
    name_en:'Seasonal Promotion Automation', name_es:'Automatización de Promociones Estacionales',
    desc_en:'Automatically activate/deactivate promotions by date range. Schedule winter tire deals, summer A/C checks in advance.',
    desc_es:'Activar/desactivar promociones automáticamente por rango de fechas. Programar ofertas de invierno y verano.' },
  { id:'CS10', status:'planned', emoji:'\uD83E\uDD16', price:1500,
    name_en:'Auto-Assignment by Skills', name_es:'Auto-Asignación por Habilidades',
    desc_en:'Automatically assign appointments to technicians based on their skills and current availability.',
    desc_es:'Asignar automáticamente citas a técnicos basado en sus habilidades y disponibilidad actual.' },
  { id:'CS11', status:'planned', emoji:'\uD83D\uDECB\uFE0F', price:500,
    name_en:'Waiting Room Amenities Section', name_es:'Sección de Amenidades de Sala de Espera',
    desc_en:'Homepage section showcasing waiting room features: WiFi, coffee, TV, comfortable seating.',
    desc_es:'Sección en página principal mostrando características de sala de espera: WiFi, café, TV, asientos cómodos.' },
  { id:'CS12', status:'future', emoji:'\uD83D\uDCCD', price:15000,
    name_en:'Multi-Location Support', name_es:'Soporte Multi-Ubicación',
    desc_en:'Support for multiple shop locations with location-specific scheduling, inventory, and reporting.',
    desc_es:'Soporte para múltiples ubicaciones con horarios, inventario e informes por sucursal.' }
];

// Market comparison data
var MARKET_COMPARISONS = [
  { en: 'Agency quote for equivalent custom platform', es: 'Cotización de agencia para plataforma personalizada equivalente', value: '$150,000 \u2013 $250,000' },
  { en: 'Off-the-shelf shop management SaaS (annual)', es: 'SaaS de gestión de taller estándar (anual)', value: '$6,000 \u2013 $18,000/yr' },
  { en: 'Custom WordPress + plugins', es: 'WordPress personalizado + plugins', value: '$40,000 \u2013 $80,000' }
];

// SaaS replacement costs
var SAAS_REPLACEMENTS = [
  { en: 'Shop management software (Tekmetric, ShopBoss)', es: 'Software de gestión de taller (Tekmetric, ShopBoss)', cost: '$300\u2013500/mo' },
  { en: 'Appointment booking (Calendly/Acuity)', es: 'Reservas de citas (Calendly/Acuity)', cost: '$50/mo' },
  { en: 'Email marketing (Mailchimp)', es: 'Email marketing (Mailchimp)', cost: '$50/mo' },
  { en: 'Push notifications (OneSignal)', es: 'Notificaciones push (OneSignal)', cost: '$50/mo' },
  { en: 'Customer portal (custom)', es: 'Portal del cliente (personalizado)', cost: 'N/A' },
  { en: 'Loyalty/referral program (Smile.io)', es: 'Programa de lealtad/referidos (Smile.io)', cost: '$100/mo' },
  { en: 'Website + hosting', es: 'Sitio web + hosting', cost: '$100/mo' }
];

// Domain consultation & transfer
var DOMAIN_CONSULTATION = {
  domain: 'oregon.tires',
  en: 'Premium domain consultation + selection',
  es: 'Consulta y selección de dominio premium',
  desc_en: 'Professional domain strategy session. Selected oregon.tires \u2014 a perfect-match .tires TLD for an Oregon tire shop. Domain ownership transfers to client with payment.',
  desc_es: 'Sesión profesional de estrategia de dominio. Se seleccionó oregon.tires \u2014 un TLD .tires perfectamente alineado para un taller de llantas en Oregon. La propiedad del dominio se transfiere al cliente con el pago.',
  value: 1500
};

// Ongoing service offerings
var SERVICE_OFFERINGS = [
  { en: 'Managed Hosting', es: 'Hosting Administrado', price: '$50/mo', desc_en: 'Server management, SSL, backups, uptime monitoring, security patches, Cloudflare CDN', desc_es: 'Gestión de servidor, SSL, respaldos, monitoreo de uptime, parches de seguridad, CDN Cloudflare' },
  { en: 'Marketing & Management', es: 'Marketing y Gestión', price: 'Starting at $500/mo', desc_en: 'SEO optimization, content updates, blog posts, social media, Google Business management, analytics reporting, platform enhancements', desc_es: 'Optimización SEO, actualizaciones de contenido, publicaciones de blog, redes sociales, gestión de Google Business, reportes de analítica, mejoras a la plataforma' }
];

// Key differentiators
var KEY_DIFFERENTIATORS = [
  { en: 'Fully bilingual (EN/ES) \u2014 every page, email, notification, admin panel', es: 'Totalmente bilingüe (EN/ES) \u2014 cada página, email, notificación, panel admin' },
  { en: 'Integrated DVI \u2192 Estimate \u2192 Approval \u2192 Invoice pipeline', es: 'Pipeline integrado DVI \u2192 Cotización \u2192 Aprobación \u2192 Factura' },
  { en: 'Token-based customer portals \u2014 customers view inspections/estimates without login', es: 'Portales de cliente basados en token \u2014 clientes ven inspecciones/cotizaciones sin iniciar sesión' },
  { en: 'Offline-capable PWA with Web Push \u2014 works without internet, syncs when back online', es: 'PWA con capacidad offline y Web Push \u2014 funciona sin internet, sincroniza al volver' },
  { en: 'Smart account linking \u2014 booking customers auto-linked to member accounts', es: 'Vinculación inteligente de cuentas \u2014 clientes de reservas vinculados automáticamente' },
  { en: 'Inbound email threading \u2014 customer email replies appear in messaging inbox', es: 'Encadenamiento de email entrante \u2014 respuestas de clientes aparecen en la bandeja de mensajes' },
  { en: 'Custom loyalty + referral + care plan programs \u2014 all integrated, not third-party', es: 'Programas propios de lealtad + referidos + planes de cuidado \u2014 todo integrado, no terceros' },
  { en: 'Resource planner + schedule-aware booking \u2014 capacity planning built into scheduling', es: 'Planificador de recursos + reservas inteligentes \u2014 planificación de capacidad integrada' }
];
