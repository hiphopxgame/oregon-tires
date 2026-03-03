<?php
/**
 * Oregon Tires — Member Dashboard Bilingual Translations (EN/ES)
 *
 * Shared by all member tab endpoints for consistent bilingual support.
 */

declare(strict_types=1);

/**
 * Detect preferred language from query param, session, cookie, or default.
 */
function getMemberLang(): string
{
    $lang = $_GET['lang'] ?? $_SESSION['member_lang'] ?? $_COOKIE['lang'] ?? 'en';
    return in_array($lang, ['en', 'es'], true) ? $lang : 'en';
}

/**
 * Get a translated string by key.
 */
function memberT(string $key, string $lang = ''): string
{
    if (!$lang) {
        $lang = getMemberLang();
    }

    static $strings = [
        // Shared
        'sign_in_required'  => ['en' => 'Please sign in to continue.', 'es' => 'Inicie sesión para continuar.'],
        'error_loading'     => ['en' => 'Error loading data. Please try again.', 'es' => 'Error al cargar datos. Inténtelo de nuevo.'],
        'book_now'          => ['en' => 'Book one now →', 'es' => 'Reserve ahora →'],
        'book_appointment'  => ['en' => 'Book an Appointment', 'es' => 'Reservar una Cita'],

        // Appointments
        'my_appointments'   => ['en' => 'My Appointments', 'es' => 'Mis Citas'],
        'appt_subtitle'     => ['en' => 'View and manage your service appointments', 'es' => 'Vea y gestione sus citas de servicio'],
        'no_appointments'   => ['en' => 'No appointments scheduled yet.', 'es' => 'Aún no hay citas programadas.'],
        'ref'               => ['en' => 'Ref', 'es' => 'Ref'],
        'service'           => ['en' => 'Service', 'es' => 'Servicio'],
        'date_time'         => ['en' => 'Date & Time', 'es' => 'Fecha y Hora'],

        // Vehicles
        'my_vehicles'       => ['en' => 'My Vehicles', 'es' => 'Mis Vehículos'],
        'vehicles_subtitle' => ['en' => 'Vehicles associated with your account', 'es' => 'Vehículos asociados a su cuenta'],
        'no_vehicles'       => ['en' => 'No vehicles on file yet. They\'ll be added when you book an appointment.', 'es' => 'Aún no hay vehículos registrados. Se agregarán al reservar una cita.'],
        'license'           => ['en' => 'License', 'es' => 'Placa'],
        'tire_size'         => ['en' => 'Tire Size', 'es' => 'Tamaño de Llanta'],

        // Estimates
        'estimates_reports' => ['en' => 'Estimates & Reports', 'es' => 'Estimados e Informes'],
        'estimates_subtitle'=> ['en' => 'Your inspection reports and service estimates', 'es' => 'Sus informes de inspección y estimados de servicio'],
        'no_estimates'      => ['en' => 'No estimates or reports yet. They\'ll appear here once we inspect your vehicle.', 'es' => 'Aún no hay estimados. Aparecerán aquí después de inspeccionar su vehículo.'],
        'estimate'          => ['en' => 'Estimate', 'es' => 'Estimado'],
        'total'             => ['en' => 'Total', 'es' => 'Total'],

        // Messages
        'messages'          => ['en' => 'Messages', 'es' => 'Mensajes'],
        'messages_subtitle' => ['en' => 'Your contact submissions', 'es' => 'Sus envíos de contacto'],
        'no_messages'       => ['en' => 'No messages yet. Your contact form submissions will appear here.', 'es' => 'Aún no hay mensajes. Sus envíos del formulario de contacto aparecerán aquí.'],
        'no_subject'        => ['en' => 'No Subject', 'es' => 'Sin Asunto'],
        'showing_of'        => ['en' => 'Showing %d to %d of %d messages', 'es' => 'Mostrando %d a %d de %d mensajes'],

        // Care Plan
        'care_plan'         => ['en' => 'Care Plan', 'es' => 'Plan de Cuidado'],
        'care_subtitle'     => ['en' => 'Your monthly auto care membership', 'es' => 'Su membresía mensual de cuidado automotriz'],
        'no_plan'           => ['en' => 'You do not have an active Care Plan', 'es' => 'No tiene un Plan de Cuidado activo'],
        'plan_cta'          => ['en' => 'Save on oil changes, tire rotations, and all services with a monthly plan.', 'es' => 'Ahorre en cambios de aceite, rotación de llantas y todos los servicios con un plan mensual.'],
        'view_plans'        => ['en' => 'View Plans & Enroll', 'es' => 'Ver Planes e Inscribirse'],
        'current_period'    => ['en' => 'Current period', 'es' => 'Período actual'],
        'your_benefits'     => ['en' => 'Your Benefits', 'es' => 'Sus Beneficios'],
        'enrollment_pending'=> ['en' => 'Your enrollment is being processed. We will contact you to complete payment setup.', 'es' => 'Su inscripción está siendo procesada. Lo contactaremos para completar la configuración del pago.'],

        // Care Plan features
        'oil_change_1'      => ['en' => '1 oil change per year', 'es' => '1 cambio de aceite al año'],
        'oil_change_2'      => ['en' => '2 oil changes per year', 'es' => '2 cambios de aceite al año'],
        'oil_change_unlim'  => ['en' => 'Unlimited oil changes', 'es' => 'Cambios de aceite ilimitados'],
        'discount_5'        => ['en' => '5% off all services', 'es' => '5% de descuento en todos los servicios'],
        'discount_10'       => ['en' => '10% off all services', 'es' => '10% de descuento en todos los servicios'],
        'discount_15'       => ['en' => '15% off all services', 'es' => '15% de descuento en todos los servicios'],
        'free_tire_rotation'=> ['en' => 'Free tire rotations', 'es' => 'Rotación de llantas gratis'],
        'priority_sched'    => ['en' => 'Priority scheduling', 'es' => 'Programación prioritaria'],
        'free_inspections'  => ['en' => 'Free multi-point inspections', 'es' => 'Inspecciones multipunto gratis'],
        'roadside_assist'   => ['en' => 'Roadside assistance', 'es' => 'Asistencia en carretera'],
        'free_alignment'    => ['en' => 'Free alignment check', 'es' => 'Revisión de alineación gratis'],

        // Customers (admin-only tab)
        'customer_directory'=> ['en' => 'Customer Directory', 'es' => 'Directorio de Clientes'],
        'customer_subtitle' => ['en' => 'View customer history and vehicle records', 'es' => 'Vea historial de clientes y registros de vehículos'],
        'search_customers'  => ['en' => 'Search by name, email, phone...', 'es' => 'Buscar por nombre, email, teléfono...'],
        'no_customers'      => ['en' => 'No customers found.', 'es' => 'No se encontraron clientes.'],
        'visits'            => ['en' => 'Visits', 'es' => 'Visitas'],
        'last_visit'        => ['en' => 'Last Visit', 'es' => 'Última Visita'],
        'services'          => ['en' => 'Services', 'es' => 'Servicios'],
        'vehicles'          => ['en' => 'Vehicles', 'es' => 'Vehículos'],
        'repair_orders'     => ['en' => 'Repair Orders', 'es' => 'Órdenes de Reparación'],
        'view_details'      => ['en' => 'View Details', 'es' => 'Ver Detalles'],
        'returning'         => ['en' => 'Returning', 'es' => 'Recurrente'],
        'customer'          => ['en' => 'Customer', 'es' => 'Cliente'],
        'contact'           => ['en' => 'Contact', 'es' => 'Contacto'],
        'vehicle'           => ['en' => 'Vehicle', 'es' => 'Vehículo'],
        'active_ros'        => ['en' => 'Active ROs', 'es' => 'ROs Activas'],
        'total_approved'    => ['en' => 'Total Approved', 'es' => 'Total Aprobado'],
        'pending_estimates'  => ['en' => 'Pending Estimates', 'es' => 'Estimados Pendientes'],
        'first_visit'       => ['en' => 'First Visit', 'es' => 'Primera Visita'],
        'language_pref'     => ['en' => 'Language', 'es' => 'Idioma'],
        'notes'             => ['en' => 'Notes', 'es' => 'Notas'],
        'never'             => ['en' => 'Never', 'es' => 'Nunca'],
        'phone'             => ['en' => 'Phone', 'es' => 'Teléfono'],
        'email'             => ['en' => 'Email', 'es' => 'Correo'],
    ];

    return $strings[$key][$lang] ?? $strings[$key]['en'] ?? $key;
}
