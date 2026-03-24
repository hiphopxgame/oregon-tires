/**
 * Oregon Tires — Admin Dashboard Help Guide System
 *
 * Non-intrusive, opt-in contextual help for every admin tab.
 * Toggle via the "?" button in the header. Persists preference in localStorage.
 * Bilingual (EN/ES), auto-updates on tab switch and language toggle.
 */

(function () {
'use strict';

var STORAGE_KEY = 'adminHelpVisible';
var BANNER_ID_PREFIX = 'admin-help-banner-';

// ─── Help Content ────────────────────────────────────────────────────────────

var HELP = {
  overview: {
    en: {
      title: 'Dashboard Overview',
      desc: 'Your at-a-glance business summary with key metrics and upcoming schedule.',
      tips: [
        'Cards at the top show today\'s appointments, pending confirmations, active repair orders, and unread messages',
        'The upcoming schedule lists the next 14 days of bookings — click any appointment to view details',
        'Employee cards show who is working today and their assigned task count',
        'Charts update automatically — use the date filters to adjust the reporting period'
      ]
    },
    es: {
      title: 'Resumen del Panel',
      desc: 'Resumen general de su negocio con métricas clave y calendario próximo.',
      tips: [
        'Las tarjetas superiores muestran las citas de hoy, confirmaciones pendientes, órdenes activas y mensajes sin leer',
        'El calendario muestra las reservas de los próximos 14 días — haga clic en cualquier cita para ver detalles',
        'Las tarjetas de empleados muestran quién trabaja hoy y su cantidad de tareas asignadas',
        'Los gráficos se actualizan automáticamente — use los filtros de fecha para ajustar el período'
      ]
    }
  },

  appointments: {
    en: {
      title: 'Appointments',
      desc: 'Manage customer bookings, confirmations, and scheduling.',
      tips: [
        'New appointments appear with "new" status — click to confirm, reschedule, or cancel',
        'Use the date picker to filter appointments by day or range',
        'Click an appointment row to see full details including vehicle info and customer notes',
        'Confirmed appointments can be converted to Repair Orders from the detail view',
        'SMS and email reminders are sent automatically the day before each appointment'
      ]
    },
    es: {
      title: 'Citas',
      desc: 'Gestione reservas de clientes, confirmaciones y programación.',
      tips: [
        'Las nuevas citas aparecen con estado "nueva" — haga clic para confirmar, reprogramar o cancelar',
        'Use el selector de fecha para filtrar citas por día o rango',
        'Haga clic en una fila de cita para ver todos los detalles incluyendo vehículo y notas',
        'Las citas confirmadas se pueden convertir en Órdenes de Reparación desde la vista de detalle',
        'Los recordatorios por SMS y correo se envían automáticamente el día anterior'
      ]
    }
  },

  customers: {
    en: {
      title: 'Customers',
      desc: 'Customer directory with vehicle history and contact information.',
      tips: [
        'Search by name, email, or phone number to quickly find a customer',
        'Each customer profile shows their vehicles, appointment history, and estimates',
        'Customers are automatically created when they book an appointment or have a vehicle serviced',
        'Click a customer to view or edit their details and linked vehicles'
      ]
    },
    es: {
      title: 'Clientes',
      desc: 'Directorio de clientes con historial de vehículos e información de contacto.',
      tips: [
        'Busque por nombre, correo o teléfono para encontrar rápidamente un cliente',
        'Cada perfil de cliente muestra sus vehículos, historial de citas y estimados',
        'Los clientes se crean automáticamente cuando reservan una cita o reciben servicio',
        'Haga clic en un cliente para ver o editar sus detalles y vehículos vinculados'
      ]
    }
  },

  repairorders: {
    en: {
      title: 'Repair Orders',
      desc: 'Full repair order lifecycle — from intake to invoice. Includes inspections, estimates, and a kanban board.',
      tips: [
        'Toggle between Table View and Kanban Board using the buttons at the top',
        'Kanban: drag and drop cards between columns to change RO status',
        'Create a new RO from an existing appointment or as a walk-in',
        'Each RO can have inspections (green/yellow/red traffic light ratings) and estimates',
        'Status flow: Intake → Diagnosis → Estimate → Approval → In Progress → Ready → Completed → Invoiced',
        'Click the inspection or estimate buttons inside an RO to create digital vehicle inspections with photos'
      ]
    },
    es: {
      title: 'Órdenes de Reparación',
      desc: 'Ciclo completo de órdenes — desde la recepción hasta la factura. Incluye inspecciones, estimados y tablero kanban.',
      tips: [
        'Alterne entre Vista de Tabla y Tablero Kanban usando los botones superiores',
        'Kanban: arrastre y suelte tarjetas entre columnas para cambiar el estado de la orden',
        'Cree una nueva orden desde una cita existente o como cliente sin cita',
        'Cada orden puede tener inspecciones (calificaciones verde/amarillo/rojo) y estimados',
        'Flujo: Recepción → Diagnóstico → Estimado → Aprobación → En Proceso → Listo → Completado → Facturado',
        'Haga clic en los botones de inspección o estimado dentro de una orden para crear inspecciones digitales con fotos'
      ]
    }
  },

  invoices: {
    en: {
      title: 'Invoices',
      desc: 'Digital invoices generated from completed repair orders.',
      tips: [
        'Invoices are generated automatically from completed ROs — click "Generate Invoice" on a completed order',
        'Each invoice has a unique token for customer viewing — share the link for contactless payment',
        'Filter invoices by status: draft, sent, paid, or overdue',
        'Invoice totals are calculated from the RO estimate items (parts, labor, fees, discounts)'
      ]
    },
    es: {
      title: 'Facturas',
      desc: 'Facturas digitales generadas desde órdenes de reparación completadas.',
      tips: [
        'Las facturas se generan automáticamente desde órdenes completadas — haga clic en "Generar Factura"',
        'Cada factura tiene un enlace único para que el cliente pueda verla sin iniciar sesión',
        'Filtre facturas por estado: borrador, enviada, pagada o vencida',
        'Los totales se calculan desde los ítems del estimado (partes, mano de obra, cargos, descuentos)'
      ]
    }
  },

  walkinqueue: {
    en: {
      title: 'Walk-In Queue',
      desc: 'Manage walk-in customers waiting for service.',
      tips: [
        'Add walk-in customers to the queue with their vehicle info and service needed',
        'Queue position updates in real-time — drag to reorder priority',
        'Convert a queued customer to a repair order when a bay opens up',
        'Estimated wait times are calculated based on current queue depth'
      ]
    },
    es: {
      title: 'Cola de Espera',
      desc: 'Gestione clientes sin cita que esperan servicio.',
      tips: [
        'Agregue clientes sin cita a la cola con su vehículo y servicio necesario',
        'La posición en la cola se actualiza en tiempo real — arrastre para reordenar',
        'Convierta un cliente en cola a una orden de reparación cuando haya espacio',
        'Los tiempos de espera estimados se calculan según la profundidad de la cola'
      ]
    }
  },

  waitlist: {
    en: {
      title: 'On Hold',
      desc: 'Repair orders that are paused or waiting for parts/approval.',
      tips: [
        'ROs with "on_hold" or "waiting_parts" status appear here automatically',
        'Track the reason each order is on hold and expected resolution date',
        'Move orders back to active status when parts arrive or approval is received'
      ]
    },
    es: {
      title: 'En Espera',
      desc: 'Órdenes de reparación pausadas o esperando partes/aprobación.',
      tips: [
        'Las órdenes con estado "en espera" o "esperando partes" aparecen aquí automáticamente',
        'Rastree la razón de cada pausa y la fecha esperada de resolución',
        'Mueva las órdenes al estado activo cuando lleguen las partes o se reciba aprobación'
      ]
    }
  },

  tirequotes: {
    en: {
      title: 'Tire Quotes',
      desc: 'Customer tire quote requests submitted through the website.',
      tips: [
        'New quote requests include the customer\'s vehicle and desired tire size',
        'Respond with pricing and availability — the customer receives an email notification',
        'Convert accepted quotes directly into appointments or repair orders'
      ]
    },
    es: {
      title: 'Cotizaciones de Llantas',
      desc: 'Solicitudes de cotización de llantas enviadas desde el sitio web.',
      tips: [
        'Las nuevas solicitudes incluyen el vehículo del cliente y el tamaño de llanta deseado',
        'Responda con precios y disponibilidad — el cliente recibe una notificación por correo',
        'Convierta cotizaciones aceptadas directamente en citas u órdenes de reparación'
      ]
    }
  },

  services: {
    en: {
      title: 'Services',
      desc: 'Manage the services your shop offers, displayed on the website.',
      tips: [
        'Add, edit, or reorder services that appear on the public website',
        'Each service can have bilingual names, descriptions, pricing, and images',
        'Services marked as featured appear prominently on the homepage'
      ]
    },
    es: {
      title: 'Servicios',
      desc: 'Gestione los servicios que ofrece su taller, mostrados en el sitio web.',
      tips: [
        'Agregue, edite o reordene los servicios que aparecen en el sitio público',
        'Cada servicio puede tener nombres bilingües, descripciones, precios e imágenes',
        'Los servicios destacados aparecen de forma prominente en la página principal'
      ]
    }
  },

  visits: {
    en: {
      title: 'Visit Tracking',
      desc: 'Track customer visits and vehicle check-in history.',
      tips: [
        'Log each customer visit with vehicle, service type, and technician',
        'Visit history builds a complete service record per vehicle',
        'Use visit data to identify returning customers and service patterns'
      ]
    },
    es: {
      title: 'Seguimiento de Visitas',
      desc: 'Rastree las visitas de clientes y el historial de check-in de vehículos.',
      tips: [
        'Registre cada visita con vehículo, tipo de servicio y técnico',
        'El historial de visitas construye un registro completo por vehículo',
        'Use los datos de visitas para identificar clientes recurrentes y patrones de servicio'
      ]
    }
  },

  resourceplanner: {
    en: {
      title: 'Resource Planner',
      desc: 'Plan technician capacity, skill coverage, and workload distribution.',
      tips: [
        'Select date ranges to see technician availability and scheduled workload',
        'The skills matrix shows which technicians are certified for which services',
        'Heatmap view highlights overbooked days and understaffed time slots',
        'Recommendations suggest optimal technician assignments based on skills and availability'
      ]
    },
    es: {
      title: 'Planificador de Recursos',
      desc: 'Planifique la capacidad de técnicos, cobertura de habilidades y distribución de carga.',
      tips: [
        'Seleccione rangos de fechas para ver disponibilidad y carga de trabajo programada',
        'La matriz de habilidades muestra qué técnicos están certificados para cada servicio',
        'La vista de mapa de calor resalta días sobrevendidos y horarios con poco personal',
        'Las recomendaciones sugieren asignaciones óptimas basadas en habilidades y disponibilidad'
      ]
    }
  },

  employees: {
    en: {
      title: 'Employees',
      desc: 'Manage technicians, staff accounts, schedules, and skills.',
      tips: [
        'Add employees with their role, skills, and contact information',
        'Set weekly schedules for each technician — these appear in the Resource Planner',
        'Admin accounts can be created here for staff who need dashboard access',
        'Employee skills and certifications are used for smart work assignment'
      ]
    },
    es: {
      title: 'Empleados',
      desc: 'Gestione técnicos, cuentas de personal, horarios y habilidades.',
      tips: [
        'Agregue empleados con su rol, habilidades e información de contacto',
        'Configure horarios semanales para cada técnico — estos aparecen en el Planificador',
        'Cree cuentas de administrador aquí para el personal que necesita acceso al panel',
        'Las habilidades y certificaciones se usan para asignación inteligente de trabajo'
      ]
    }
  },

  labor: {
    en: {
      title: 'Labor Tracking',
      desc: 'Technician hours and labor summary across all repair orders.',
      tips: [
        'This tab shows a summary of hours worked by each technician across all ROs',
        'Active clocks (pulsing green dot) indicate a technician currently clocked in',
        'To clock in/out a technician, open the specific Repair Order and use the Labor section',
        'Track billable vs. non-billable hours for accurate invoicing'
      ]
    },
    es: {
      title: 'Seguimiento de Mano de Obra',
      desc: 'Horas de técnicos y resumen de trabajo en todas las órdenes de reparación.',
      tips: [
        'Esta pestaña muestra un resumen de horas trabajadas por cada técnico en todas las órdenes',
        'Los relojes activos (punto verde pulsante) indican un técnico actualmente registrado',
        'Para registrar entrada/salida de un técnico, abra la Orden de Reparación específica',
        'Rastree horas facturables vs. no facturables para una facturación precisa'
      ]
    }
  },

  messages: {
    en: {
      title: 'Messages',
      desc: 'Contact form submissions, customer conversations, and email logs.',
      tips: [
        'Contact Messages: form submissions from the website — click to read and reply',
        'Conversations: two-way messaging threads with customers (email and web)',
        'Email Logs: audit trail of all emails sent by the system',
        'Unread message count appears as a badge on the Messages tab',
        'Inbound emails are automatically threaded into existing conversations'
      ]
    },
    es: {
      title: 'Mensajes',
      desc: 'Formularios de contacto, conversaciones con clientes y registro de correos.',
      tips: [
        'Mensajes de Contacto: envíos del formulario del sitio web — haga clic para leer y responder',
        'Conversaciones: hilos de mensajes bidireccionales con clientes (correo y web)',
        'Registro de Correos: historial de auditoría de todos los correos enviados por el sistema',
        'El conteo de mensajes sin leer aparece como insignia en la pestaña de Mensajes',
        'Los correos entrantes se enlazan automáticamente en conversaciones existentes'
      ]
    }
  },

  myschedule: {
    en: {
      title: 'My Schedule',
      desc: 'Your personal work schedule and upcoming shifts.',
      tips: [
        'View your assigned schedule for the current and upcoming weeks',
        'Your shifts are set by the admin in the Employees tab'
      ]
    },
    es: {
      title: 'Mi Horario',
      desc: 'Su horario de trabajo personal y turnos próximos.',
      tips: [
        'Vea su horario asignado para la semana actual y las próximas',
        'Sus turnos son configurados por el administrador en la pestaña de Empleados'
      ]
    }
  },

  mywork: {
    en: {
      title: 'My Work',
      desc: 'Repair orders assigned to you as a technician.',
      tips: [
        'See all ROs where you are the assigned technician',
        'Update status and add labor time directly from this view'
      ]
    },
    es: {
      title: 'Mi Trabajo',
      desc: 'Órdenes de reparación asignadas a usted como técnico.',
      tips: [
        'Vea todas las órdenes donde usted es el técnico asignado',
        'Actualice el estado y agregue tiempo de trabajo directamente desde esta vista'
      ]
    }
  },

  blog: {
    en: {
      title: 'Blog',
      desc: 'Create and manage bilingual blog posts for the website.',
      tips: [
        'Write posts in English and Spanish — both versions appear based on the visitor\'s language',
        'Use the rich text editor for formatting, images, and links',
        'Published posts appear on the /blog page and are included in the sitemap for SEO',
        'Set a featured image and meta description for better search engine visibility'
      ]
    },
    es: {
      title: 'Blog',
      desc: 'Cree y administre publicaciones bilingües para el sitio web.',
      tips: [
        'Escriba publicaciones en inglés y español — ambas versiones aparecen según el idioma del visitante',
        'Use el editor de texto enriquecido para formato, imágenes y enlaces',
        'Las publicaciones aparecen en la página /blog y se incluyen en el mapa del sitio para SEO',
        'Configure una imagen destacada y meta descripción para mejor visibilidad en buscadores'
      ]
    }
  },

  promotions: {
    en: {
      title: 'Promotions',
      desc: 'Create promotional offers displayed on the website.',
      tips: [
        'Add promotions with bilingual title, description, image, and expiration date',
        'Target promotions to specific pages or show them site-wide',
        'Active promotions appear on the /promotions page and optionally on the homepage',
        'Expired promotions are automatically hidden from the public site'
      ]
    },
    es: {
      title: 'Promociones',
      desc: 'Cree ofertas promocionales que se muestran en el sitio web.',
      tips: [
        'Agregue promociones con título bilingüe, descripción, imagen y fecha de vencimiento',
        'Dirija promociones a páginas específicas o muéstrelas en todo el sitio',
        'Las promociones activas aparecen en la página /promotions y opcionalmente en la principal',
        'Las promociones vencidas se ocultan automáticamente del sitio público'
      ]
    }
  },

  faq: {
    en: {
      title: 'FAQ',
      desc: 'Manage frequently asked questions displayed on the website.',
      tips: [
        'Add questions and answers in both English and Spanish',
        'Drag to reorder — the display order on the public /faq page matches this list',
        'FAQs are automatically structured as JSON-LD for Google rich results'
      ]
    },
    es: {
      title: 'Preguntas Frecuentes',
      desc: 'Administre las preguntas frecuentes que se muestran en el sitio web.',
      tips: [
        'Agregue preguntas y respuestas en inglés y español',
        'Arrastre para reordenar — el orden en la página /faq coincide con esta lista',
        'Las preguntas se estructuran automáticamente como JSON-LD para resultados enriquecidos de Google'
      ]
    }
  },

  reviews: {
    en: {
      title: 'Reviews',
      desc: 'Google Business reviews cached and displayed on the website.',
      tips: [
        'Reviews are fetched automatically from Google every morning at 6 AM',
        'They appear on the /reviews page and as testimonials on the homepage',
        'The review cache refreshes daily — use the Refresh button for an immediate update'
      ]
    },
    es: {
      title: 'Reseñas',
      desc: 'Reseñas de Google Business almacenadas y mostradas en el sitio web.',
      tips: [
        'Las reseñas se obtienen automáticamente de Google cada mañana a las 6 AM',
        'Aparecen en la página /reviews y como testimonios en la página principal',
        'La caché se actualiza diariamente — use el botón Actualizar para una actualización inmediata'
      ]
    }
  },

  gallery: {
    en: {
      title: 'Gallery',
      desc: 'Manage shop photos and service card images for the website.',
      tips: [
        'Upload photos of your shop, team, and completed work',
        'Add bilingual captions for each image (English and Spanish)',
        'Service Images tab: manage the photos shown on each service card on the homepage',
        'Images are automatically optimized in WebP and AVIF formats for fast loading'
      ]
    },
    es: {
      title: 'Galería',
      desc: 'Administre fotos del taller e imágenes de servicios para el sitio web.',
      tips: [
        'Suba fotos de su taller, equipo y trabajo completado',
        'Agregue descripciones bilingües para cada imagen (inglés y español)',
        'Pestaña de Imágenes de Servicios: administre las fotos de cada tarjeta de servicio',
        'Las imágenes se optimizan automáticamente en formatos WebP y AVIF para carga rápida'
      ]
    }
  },

  subscribers: {
    en: {
      title: 'Subscribers',
      desc: 'Email newsletter subscribers from the website signup form.',
      tips: [
        'New subscribers receive an automatic welcome email',
        'Export the subscriber list for use in external email marketing tools',
        'Track subscription dates and engagement status'
      ]
    },
    es: {
      title: 'Suscriptores',
      desc: 'Suscriptores del boletín por correo desde el formulario del sitio web.',
      tips: [
        'Los nuevos suscriptores reciben un correo de bienvenida automático',
        'Exporte la lista de suscriptores para herramientas de marketing externas',
        'Rastree fechas de suscripción y estado de participación'
      ]
    }
  },

  loyalty: {
    en: {
      title: 'Loyalty & Rewards',
      desc: 'Customer loyalty points program and redeemable rewards catalog.',
      tips: [
        'Award points to customers for completed services, referrals, and special promotions',
        'Set up redeemable rewards (discounts, free services) in the Rewards Catalog',
        'Points are tracked per customer and visible in their member dashboard',
        'Manually adjust points (add or deduct) from this admin view'
      ]
    },
    es: {
      title: 'Lealtad y Recompensas',
      desc: 'Programa de puntos de lealtad y catálogo de recompensas canjeables.',
      tips: [
        'Otorgue puntos a clientes por servicios completados, referidos y promociones especiales',
        'Configure recompensas canjeables (descuentos, servicios gratis) en el Catálogo',
        'Los puntos se rastrean por cliente y son visibles en su panel de miembro',
        'Ajuste puntos manualmente (agregar o deducir) desde esta vista de administrador'
      ]
    }
  },

  referrals: {
    en: {
      title: 'Referrals',
      desc: 'Customer referral tracking — codes, status, and bonus points.',
      tips: [
        'Each member gets a unique referral code to share with friends',
        'When a referred customer books their first appointment, the referral is tracked',
        'Mark referrals as complete and award bonus loyalty points to the referrer',
        'Filter by status: pending, completed, or expired'
      ]
    },
    es: {
      title: 'Referidos',
      desc: 'Seguimiento de referidos de clientes — códigos, estado y puntos de bonificación.',
      tips: [
        'Cada miembro recibe un código de referido único para compartir con amigos',
        'Cuando un cliente referido reserva su primera cita, el referido se registra',
        'Marque referidos como completados y otorgue puntos de bonificación al referente',
        'Filtre por estado: pendiente, completado o expirado'
      ]
    }
  },

  reminders: {
    en: {
      title: 'Service Reminders',
      desc: 'Automated service due date reminders per vehicle.',
      tips: [
        'Set up reminders for oil changes, tire rotations, inspections, and other recurring services',
        'Reminders are linked to specific vehicles and sent automatically when the service is due',
        'A weekly cron job (Monday 9 AM) sends reminder emails to customers',
        'Track which reminders have been sent and which are upcoming'
      ]
    },
    es: {
      title: 'Recordatorios de Servicio',
      desc: 'Recordatorios automáticos de fecha de servicio por vehículo.',
      tips: [
        'Configure recordatorios para cambios de aceite, rotación de llantas, inspecciones y otros servicios',
        'Los recordatorios están vinculados a vehículos específicos y se envían automáticamente',
        'Un trabajo programado semanal (lunes 9 AM) envía correos de recordatorio',
        'Rastree qué recordatorios se han enviado y cuáles están próximos'
      ]
    }
  },

  analytics: {
    en: {
      title: 'Analytics',
      desc: 'Business analytics dashboard with charts and key performance indicators.',
      tips: [
        'Track revenue trends, appointment volume, and customer acquisition over time',
        'Charts include: appointments by status, revenue by service type, top customers',
        'Use date filters to compare different time periods',
        'Data is pulled in real-time from your appointments, repair orders, and invoice records'
      ]
    },
    es: {
      title: 'Analíticas',
      desc: 'Panel de analíticas del negocio con gráficos e indicadores clave.',
      tips: [
        'Rastree tendencias de ingresos, volumen de citas y adquisición de clientes',
        'Los gráficos incluyen: citas por estado, ingresos por tipo de servicio, mejores clientes',
        'Use filtros de fecha para comparar diferentes períodos',
        'Los datos se obtienen en tiempo real de sus citas, órdenes de reparación y facturas'
      ]
    }
  },

  sitecontent: {
    en: {
      title: 'Site Content',
      desc: 'Edit website content, business info, and email templates.',
      tips: [
        'Update your shop\'s address, phone number, business hours, and social media links',
        'Edit bilingual email templates used for appointment confirmations, reminders, and estimates',
        'Changes take effect immediately on the live website',
        'Template variables (like {customer_name}) are automatically replaced when emails are sent'
      ]
    },
    es: {
      title: 'Contenido del Sitio',
      desc: 'Edite el contenido del sitio web, información del negocio y plantillas de correo.',
      tips: [
        'Actualice la dirección, teléfono, horario y redes sociales de su taller',
        'Edite plantillas de correo bilingües para confirmaciones, recordatorios y estimados',
        'Los cambios se aplican inmediatamente en el sitio web en vivo',
        'Las variables de plantilla (como {customer_name}) se reemplazan automáticamente al enviar'
      ]
    }
  },

  docs: {
    en: {
      title: 'Documentation',
      desc: 'Platform documentation and technical reference.',
      tips: [
        'Browse the full feature list, API documentation, and setup guides',
        'This section is for reference — it documents how the platform was built and how features work'
      ]
    },
    es: {
      title: 'Documentación',
      desc: 'Documentación de la plataforma y referencia técnica.',
      tips: [
        'Explore la lista completa de funciones, documentación de API y guías de configuración',
        'Esta sección es de referencia — documenta cómo se construyó la plataforma y cómo funcionan las funciones'
      ]
    }
  },

  health: {
    en: {
      title: 'System Health',
      desc: 'Monitor uptime, SSL certificates, backups, and automated feature tests.',
      tips: [
        'Uptime is checked every 5 minutes — the chart shows daily availability percentage',
        'SSL certificate expiry is checked daily — you\'ll see warnings when it\'s close to expiring',
        'Database backups run automatically at 6 AM daily and are retained for 30 days',
        'Feature tests verify that key API endpoints (booking, services, FAQ, blog) are responding',
        'A daily health report email is sent to the admin contact address every morning',
        'Cron job status shows whether scheduled tasks (reminders, reviews, push) are running on time'
      ]
    },
    es: {
      title: 'Estado del Sistema',
      desc: 'Monitoree disponibilidad, certificados SSL, respaldos y pruebas automatizadas.',
      tips: [
        'La disponibilidad se verifica cada 5 minutos — el gráfico muestra el porcentaje diario',
        'La expiración del certificado SSL se verifica diariamente — verá alertas cuando esté cerca de expirar',
        'Los respaldos de base de datos se ejecutan automáticamente a las 6 AM y se retienen por 30 días',
        'Las pruebas de funciones verifican que los endpoints clave (reservas, servicios, FAQ, blog) respondan',
        'Un reporte diario de salud se envía por correo al administrador cada mañana',
        'El estado de tareas programadas muestra si los trabajos (recordatorios, reseñas, push) están funcionando'
      ]
    }
  }
};

// ─── State ───────────────────────────────────────────────────────────────────

function isHelpVisible() {
  return localStorage.getItem(STORAGE_KEY) === 'true';
}

function setHelpVisible(visible) {
  localStorage.setItem(STORAGE_KEY, visible ? 'true' : 'false');
  updateToggleButton(visible);
}

function getLang() {
  return (typeof currentLang !== 'undefined' && currentLang) || 'en';
}

function getActiveTabId() {
  var el = document.querySelector('.tab-content:not(.hidden)');
  if (el && el.id && el.id.startsWith('tab-')) return el.id.replace('tab-', '');
  return 'overview';
}

// ─── Toggle Button ───────────────────────────────────────────────────────────

function updateToggleButton(active) {
  var btn = document.getElementById('admin-help-toggle');
  if (!btn) return;
  if (active) {
    btn.classList.add('bg-white/20', 'rounded-lg');
    btn.setAttribute('aria-pressed', 'true');
    btn.title = getLang() === 'es' ? 'Desactivar guía' : 'Turn off guide';
  } else {
    btn.classList.remove('bg-white/20', 'rounded-lg');
    btn.setAttribute('aria-pressed', 'false');
    btn.title = getLang() === 'es' ? 'Activar guía' : 'Turn on guide';
  }
}

// ─── Banner Rendering ────────────────────────────────────────────────────────

function removeAllBanners() {
  var banners = document.querySelectorAll('[id^="' + BANNER_ID_PREFIX + '"]');
  for (var i = 0; i < banners.length; i++) {
    banners[i].remove();
  }
}

function renderHelpBanner(tabId) {
  // Remove any existing banners first
  removeAllBanners();

  if (!isHelpVisible()) return;

  var content = HELP[tabId];
  if (!content) return;

  var lang = getLang();
  var data = content[lang] || content.en;
  if (!data) return;

  var tabEl = document.getElementById('tab-' + tabId);
  if (!tabEl) return;

  var bannerId = BANNER_ID_PREFIX + tabId;

  // Build the banner
  var banner = document.createElement('div');
  banner.id = bannerId;
  banner.className = 'bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800 rounded-xl p-4 mb-5 fade-in';
  banner.setAttribute('role', 'complementary');
  banner.setAttribute('aria-label', lang === 'es' ? 'Guía de esta sección' : 'Section guide');

  // Header row: icon + title + dismiss
  var header = document.createElement('div');
  header.className = 'flex items-start justify-between gap-3 mb-2';

  var titleWrap = document.createElement('div');
  titleWrap.className = 'flex items-center gap-2';

  var icon = document.createElement('span');
  icon.className = 'text-sky-500 dark:text-sky-400 text-lg flex-shrink-0';
  icon.textContent = '\uD83D\uDCA1'; // 💡
  titleWrap.appendChild(icon);

  var title = document.createElement('h3');
  title.className = 'text-sm font-semibold text-sky-800 dark:text-sky-300';
  title.textContent = data.title;
  titleWrap.appendChild(title);

  header.appendChild(titleWrap);

  // Dismiss button
  var dismiss = document.createElement('button');
  dismiss.className = 'text-sky-400 hover:text-sky-600 dark:text-sky-500 dark:hover:text-sky-300 transition-colors flex-shrink-0 p-0.5';
  dismiss.setAttribute('aria-label', lang === 'es' ? 'Cerrar guía' : 'Close guide');
  dismiss.title = lang === 'es' ? 'Cerrar guía' : 'Close guide';
  var xSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  xSvg.setAttribute('width', '16');
  xSvg.setAttribute('height', '16');
  xSvg.setAttribute('viewBox', '0 0 24 24');
  xSvg.setAttribute('fill', 'none');
  xSvg.setAttribute('stroke', 'currentColor');
  xSvg.setAttribute('stroke-width', '2');
  xSvg.setAttribute('stroke-linecap', 'round');
  var line1 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
  line1.setAttribute('x1', '18'); line1.setAttribute('y1', '6');
  line1.setAttribute('x2', '6');  line1.setAttribute('y2', '18');
  var line2 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
  line2.setAttribute('x1', '6');  line2.setAttribute('y1', '6');
  line2.setAttribute('x2', '18'); line2.setAttribute('y2', '18');
  xSvg.appendChild(line1);
  xSvg.appendChild(line2);
  dismiss.appendChild(xSvg);
  dismiss.addEventListener('click', function () {
    toggleAdminHelp();
  });
  header.appendChild(dismiss);

  banner.appendChild(header);

  // Description
  var desc = document.createElement('p');
  desc.className = 'text-sm text-sky-700 dark:text-sky-400 mb-3 ml-7';
  desc.textContent = data.desc;
  banner.appendChild(desc);

  // Tips list
  if (data.tips && data.tips.length > 0) {
    var ul = document.createElement('ul');
    ul.className = 'text-sm text-gray-600 dark:text-gray-400 space-y-1.5 ml-7 list-none';

    for (var i = 0; i < data.tips.length; i++) {
      var li = document.createElement('li');
      li.className = 'flex items-start gap-2';

      var bullet = document.createElement('span');
      bullet.className = 'text-sky-400 dark:text-sky-600 mt-0.5 flex-shrink-0';
      bullet.textContent = '›';
      li.appendChild(bullet);

      var text = document.createElement('span');
      text.textContent = data.tips[i];
      li.appendChild(text);

      ul.appendChild(li);
    }
    banner.appendChild(ul);
  }

  // Insert at the top of the tab content
  if (tabEl.firstChild) {
    tabEl.insertBefore(banner, tabEl.firstChild);
  } else {
    tabEl.appendChild(banner);
  }
}

// ─── Public API ──────────────────────────────────────────────────────────────

window.toggleAdminHelp = function () {
  var nowVisible = !isHelpVisible();
  setHelpVisible(nowVisible);

  if (nowVisible) {
    renderHelpBanner(getActiveTabId());
    if (typeof showToast === 'function') {
      showToast(getLang() === 'es' ? 'Guía activada — verá instrucciones en cada pestaña' : 'Guide enabled — you\'ll see tips on every tab');
    }
  } else {
    removeAllBanners();
  }
};

// ─── Hook into switchTab ─────────────────────────────────────────────────────

var _origSwitchTab = window.switchTab;
if (typeof _origSwitchTab === 'function') {
  window.switchTab = function (tab) {
    _origSwitchTab(tab);
    if (isHelpVisible()) {
      // Small delay to let the tab become visible
      setTimeout(function () { renderHelpBanner(tab); }, 50);
    }
  };
}

// ─── Hook into language toggle ───────────────────────────────────────────────

var _origToggleLang = window.toggleAdminLanguage;
if (typeof _origToggleLang === 'function') {
  window.toggleAdminLanguage = function () {
    _origToggleLang();
    if (isHelpVisible()) {
      setTimeout(function () { renderHelpBanner(getActiveTabId()); }, 100);
    }
    updateToggleButton(isHelpVisible());
  };
}

// ─── Init on load ────────────────────────────────────────────────────────────

function initHelp() {
  var visible = isHelpVisible();
  updateToggleButton(visible);
  if (visible) {
    renderHelpBanner(getActiveTabId());
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initHelp);
} else {
  initHelp();
}

})();
