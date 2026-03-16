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
    $lang = in_array($lang, ['en', 'es'], true) ? $lang : 'en';
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['member_lang'] = $lang;
    }
    return $lang;
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

        // Conversations
        'conversations'        => ['en' => 'Conversations', 'es' => 'Conversaciones'],
        'new_message'          => ['en' => 'New Message', 'es' => 'Nuevo Mensaje'],
        'send_message'         => ['en' => 'Send Message', 'es' => 'Enviar Mensaje'],
        'reply'                => ['en' => 'Reply', 'es' => 'Responder'],
        'subject'              => ['en' => 'Subject', 'es' => 'Asunto'],
        'message_body'         => ['en' => 'Message', 'es' => 'Mensaje'],
        'no_conversations'     => ['en' => 'No conversations yet. Start one using the button above.', 'es' => 'Aún no hay conversaciones. Inicie una usando el botón de arriba.'],
        'unread'               => ['en' => 'unread', 'es' => 'sin leer'],
        'status_open'          => ['en' => 'Open', 'es' => 'Abierto'],
        'status_waiting'       => ['en' => 'Waiting for Reply', 'es' => 'Esperando Respuesta'],
        'status_resolved'      => ['en' => 'Resolved', 'es' => 'Resuelto'],
        'status_closed'        => ['en' => 'Closed', 'es' => 'Cerrado'],
        'previous_submissions' => ['en' => 'Previous Contact Submissions', 'es' => 'Envíos de Contacto Anteriores'],
        'type_your_message'    => ['en' => 'Type your message...', 'es' => 'Escriba su mensaje...'],
        'message_sent'         => ['en' => 'Message sent!', 'es' => '¡Mensaje enviado!'],

        // Employee tabs
        'my_schedule'       => ['en' => 'My Schedule', 'es' => 'Mi Horario'],
        'assigned_work'     => ['en' => 'Assigned Work', 'es' => 'Trabajo Asignado'],
        'schedule_subtitle' => ['en' => 'Your weekly work schedule', 'es' => 'Su horario de trabajo semanal'],
        'no_schedule'       => ['en' => 'No schedule found. Contact your manager.', 'es' => 'No se encontró horario. Contacte a su gerente.'],
        'assigned_subtitle' => ['en' => 'Repair orders assigned to you', 'es' => 'Órdenes de reparación asignadas a usted'],
        'no_assigned_work'  => ['en' => 'No work currently assigned to you.', 'es' => 'No tiene trabajo asignado actualmente.'],
        'day_sunday'        => ['en' => 'Sunday', 'es' => 'Domingo'],
        'day_monday'        => ['en' => 'Monday', 'es' => 'Lunes'],
        'day_tuesday'       => ['en' => 'Tuesday', 'es' => 'Martes'],
        'day_wednesday'     => ['en' => 'Wednesday', 'es' => 'Miércoles'],
        'day_thursday'      => ['en' => 'Thursday', 'es' => 'Jueves'],
        'day_friday'        => ['en' => 'Friday', 'es' => 'Viernes'],
        'day_saturday'      => ['en' => 'Saturday', 'es' => 'Sábado'],
        'off'               => ['en' => 'Off', 'es' => 'Libre'],
        'today_override'    => ['en' => 'Schedule Override', 'es' => 'Excepción de Horario'],
        'ro_number'         => ['en' => 'RO #', 'es' => 'OT #'],
        'status'            => ['en' => 'Status', 'es' => 'Estado'],

        // Admin tab
        'admin_panel'       => ['en' => 'Admin Panel', 'es' => 'Panel Admin'],
        'admin_desc'        => ['en' => 'Full shop management dashboard', 'es' => 'Panel completo de gestión del taller'],
        'open_admin'        => ['en' => 'Open Admin Panel', 'es' => 'Abrir Panel Admin'],
        'admin_features'    => ['en' => 'From the admin panel you can manage:', 'es' => 'Desde el panel admin puede gestionar:'],

        // Universal dashboard tabs
        'profile'           => ['en' => 'Profile', 'es' => 'Perfil'],
        'settings'          => ['en' => 'Settings', 'es' => "Configuraci\u{00f3}n"],
        'activity'          => ['en' => 'Activity', 'es' => 'Actividad'],
        'security'          => ['en' => 'Security', 'es' => 'Seguridad'],
        'change_password'   => ['en' => 'Change Password', 'es' => "Cambiar Contrase\u{00f1}a"],

        // Loyalty
        'valued_customer'   => ['en' => 'Valued Customer', 'es' => 'Cliente Valioso'],
        'regular_customer'  => ['en' => 'Regular Customer', 'es' => 'Cliente Frecuente'],
        'loyal_customer'    => ['en' => 'Loyal Customer', 'es' => 'Cliente Leal'],
        'visits_count'      => ['en' => '%d visits', 'es' => '%d visitas'],
        'your_technician'   => ['en' => 'Your technician', 'es' => "Su t\u{00e9}cnico"],
        'view_inspection'   => ['en' => 'View Inspection', 'es' => "Ver Inspecci\u{00f3}n"],
        'photos'            => ['en' => 'photos', 'es' => 'fotos'],
        'welcome_back'      => ['en' => 'Welcome back!', 'es' => "\u{00a1}Bienvenido de nuevo!"],
        'visit_number'      => ['en' => 'This is your visit #%d', 'es' => 'Esta es su visita #%d'],

        // ── Auth Pages (Login / Register / Forgot / Reset) ───────────
        // Nav tabs
        'sign_in'               => ['en' => 'Sign In', 'es' => "Iniciar Sesi\u{00f3}n"],
        'create_account'        => ['en' => 'Create Account', 'es' => 'Crear Cuenta'],
        'reset_password_tab'    => ['en' => 'Reset Password', 'es' => "Restablecer Contrase\u{00f1}a"],

        // Login page
        'welcome_back_sub'     => ['en' => 'Welcome back', 'es' => 'Bienvenido de nuevo'],
        'new_here'              => ['en' => 'New here?', 'es' => "\u{00bf}Nuevo aqu\u{00ed}?"],
        'create_free_account'   => ['en' => 'Create your free account', 'es' => 'Crea tu cuenta gratis'],
        'email_label'           => ['en' => 'Email', 'es' => "Correo electr\u{00f3}nico"],
        'password_label'        => ['en' => 'Password', 'es' => "Contrase\u{00f1}a"],
        'forgot_link'           => ['en' => 'Forgot?', 'es' => "\u{00bf}Olvidaste?"],
        'remember_device'       => ['en' => 'Remember this device for 30 days', 'es' => "Recordar este dispositivo por 30 d\u{00ed}as"],
        'remember_device_30_days' => ['en' => 'Remember this device for 30 days', 'es' => "Recordar este dispositivo por 30 d\u{00ed}as"],
        'sign_in_btn'           => ['en' => 'Sign In', 'es' => "Iniciar Sesi\u{00f3}n"],
        'encrypted_badge'       => ['en' => '256-bit encrypted · Your data stays private', 'es' => "Cifrado 256-bit \u{00b7} Tu informaci\u{00f3}n es privada"],
        'or_continue_with'      => ['en' => 'or continue with', 'es' => "o contin\u{00fa}a con"],
        'or_connect_wallet'     => ['en' => 'or connect wallet', 'es' => 'o conecta billetera'],
        'create_an_account'     => ['en' => 'Create an account', 'es' => 'Crear una cuenta'],
        'email_verified_success'=> ['en' => 'Email verified successfully. You can now sign in.', 'es' => "Correo verificado. Ya puede iniciar sesi\u{00f3}n."],
        'account_created_check_email' => ['en' => 'Account created! Please check your email to verify your address before signing in.', 'es' => "\u{00a1}Cuenta creada! Revise su correo para verificar su direcci\u{00f3}n antes de iniciar sesi\u{00f3}n."],
        'too_many_requests'     => ['en' => 'Too many requests.', 'es' => 'Demasiadas solicitudes.'],
        'try_again_later'       => ['en' => 'Please try again later.', 'es' => "Int\u{00e9}ntelo m\u{00e1}s tarde."],
        'session_expiring'      => ['en' => 'Your session will expire in', 'es' => "Su sesi\u{00f3}n expirar\u{00e1} en"],
        'extend_session'        => ['en' => 'Extend session', 'es' => "Extender sesi\u{00f3}n"],

        // Register page
        'join_site'             => ['en' => 'Join', 'es' => "\u{00da}nase a"],
        'sign_up_with'          => ['en' => 'Sign up with', 'es' => "Reg\u{00ed}strate con"],
        'or_divider'            => ['en' => 'or', 'es' => 'o'],
        'username_label'        => ['en' => 'Username', 'es' => 'Nombre de usuario'],
        'display_name_label'    => ['en' => 'Display Name', 'es' => 'Nombre para mostrar'],
        'password_placeholder'  => ['en' => 'At least 8 characters', 'es' => 'Al menos 8 caracteres'],
        'confirm_password'      => ['en' => 'Confirm Password', 'es' => "Confirmar Contrase\u{00f1}a"],
        'repeat_password'       => ['en' => 'Repeat your password', 'es' => "Repita su contrase\u{00f1}a"],
        'create_account_btn'    => ['en' => 'Create Account', 'es' => 'Crear Cuenta'],
        'already_have_account'  => ['en' => 'Already have an account? Sign in', 'es' => "\u{00bf}Ya tiene cuenta? Inicie sesi\u{00f3}n"],

        // Forgot password page
        'reset_password_title'  => ['en' => 'Reset Password', 'es' => "Restablecer Contrase\u{00f1}a"],
        'enter_email_reset'     => ['en' => 'Enter your email to receive a reset link', 'es' => 'Ingrese su correo para recibir un enlace'],
        'email_address'         => ['en' => 'Email Address', 'es' => "Correo Electr\u{00f3}nico"],
        'send_reset_link'       => ['en' => 'Send Reset Link', 'es' => 'Enviar Enlace'],
        'back_to_sign_in'       => ['en' => 'Back to Sign In', 'es' => "Volver a Iniciar Sesi\u{00f3}n"],

        // Reset password page
        'set_new_password'      => ['en' => 'Set New Password', 'es' => "Establecer Nueva Contrase\u{00f1}a"],
        'choose_strong_password'=> ['en' => 'Choose a strong password', 'es' => "Elija una contrase\u{00f1}a segura"],
        'new_password'          => ['en' => 'New Password', 'es' => "Nueva Contrase\u{00f1}a"],
        'reset_password_btn'    => ['en' => 'Reset Password', 'es' => "Restablecer Contrase\u{00f1}a"],
    ];

    return $strings[$key][$lang] ?? $strings[$key]['en'] ?? $key;
}
