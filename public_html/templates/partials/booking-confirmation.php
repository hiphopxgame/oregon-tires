<?php
/**
 * HTMX partial: Booking confirmation (replaces the form).
 *
 * Expected variables:
 *   $bookingData     — array (service, preferredDate, preferredTime, firstName, lastName, phone, email, vehicleYear, vehicleMake, vehicleModel)
 *   $bookingResponse — array (reference_number, account_created, payment?)
 *   $lang            — 'en' | 'es'
 */

$labels = [
    'en' => [
        'title' => 'Appointment Requested!',
        'subtitle' => 'We will contact you to confirm your appointment. A confirmation email has been sent to your inbox. Thank you!',
        'service' => 'Service',
        'date' => 'Date',
        'time' => 'Time',
        'vehicle' => 'Vehicle',
        'name' => 'Name',
        'phone' => 'Phone',
        'email' => 'Email',
        'reference' => 'Reference #',
        'backToHome' => 'Back to Home',
        'bookAnother' => 'Book Another',
        'accountCreated' => "We've created an account for you! Check your email to set a password and track your appointments online.",
    ],
    'es' => [
        'title' => '¡Cita Solicitada!',
        'subtitle' => 'Nos comunicaremos con usted para confirmar su cita. Se ha enviado un correo de confirmación a su bandeja de entrada. ¡Gracias!',
        'service' => 'Servicio',
        'date' => 'Fecha',
        'time' => 'Hora',
        'vehicle' => 'Vehículo',
        'name' => 'Nombre',
        'phone' => 'Teléfono',
        'email' => 'Correo',
        'reference' => 'Referencia #',
        'backToHome' => 'Ir al Inicio',
        'bookAnother' => 'Programar Otra',
        'accountCreated' => 'Hemos creado una cuenta para usted. Revise su correo para establecer su contrasena y ver sus citas en linea.',
    ],
];

$serviceNames = [
    'en' => [
        'tire-installation' => 'Tire Installation', 'tire-repair' => 'Tire Repair',
        'wheel-alignment' => 'Wheel Alignment', 'oil-change' => 'Oil Change',
        'brake-service' => 'Brake Service', 'tuneup' => 'Tuneup',
        'mechanical-inspection' => 'Mechanical Inspection', 'mobile-service' => 'Mobile Service',
        'roadside-assistance' => 'Roadside Assistance', 'other' => 'Other',
    ],
    'es' => [
        'tire-installation' => 'Instalación de Llantas', 'tire-repair' => 'Reparación de Llantas',
        'wheel-alignment' => 'Alineación de Ruedas', 'oil-change' => 'Cambio de Aceite',
        'brake-service' => 'Servicio de Frenos', 'tuneup' => 'Afinación',
        'mechanical-inspection' => 'Inspección Mecánica', 'mobile-service' => 'Servicio Móvil',
        'roadside-assistance' => 'Asistencia en Carretera', 'other' => 'Otro',
    ],
];

$l = $labels[$lang] ?? $labels['en'];
$sn = $serviceNames[$lang] ?? $serviceNames['en'];

// Format date
$dateObj = new DateTime($bookingData['preferredDate']);
$displayDate = $lang === 'es'
    ? $dateObj->format('d/m/Y')
    : $dateObj->format('m/d/Y');

// Format time
$timeParts = explode(':', $bookingData['preferredTime']);
$hour = (int) $timeParts[0];
$min = $timeParts[1] ?? '00';
$suffix = $hour >= 12 ? 'PM' : 'AM';
$displayHour = $hour > 12 ? $hour - 12 : ($hour ?: 12);
$displayTime = $displayHour . ':' . $min . ' ' . $suffix;

// Build detail rows
$details = [
    [$l['reference'], $bookingResponse['reference_number'] ?? ''],
    [$l['service'], $sn[$bookingData['service']] ?? ucwords(str_replace('-', ' ', $bookingData['service']))],
    [$l['date'], $displayDate],
    [$l['time'], $displayTime],
    [$l['name'], htmlspecialchars($bookingData['firstName'] . ' ' . $bookingData['lastName'], ENT_QUOTES, 'UTF-8')],
    [$l['phone'], htmlspecialchars($bookingData['phone'], ENT_QUOTES, 'UTF-8')],
    [$l['email'], htmlspecialchars($bookingData['email'], ENT_QUOTES, 'UTF-8')],
];

// Insert vehicle row if present
$vehicleParts = array_filter([$bookingData['vehicleYear'] ?? '', $bookingData['vehicleMake'] ?? '', $bookingData['vehicleModel'] ?? '']);
if ($vehicleParts) {
    array_splice($details, 4, 0, [[$l['vehicle'], htmlspecialchars(implode(' ', $vehicleParts), ENT_QUOTES, 'UTF-8')]]);
}
?>
<div class="mt-8 bg-white rounded-xl shadow-lg p-8 text-center fade-in dark:bg-gray-700" role="alert" aria-live="assertive" id="booking-confirmation-result">
    <div class="text-5xl mb-4">&#x2705;</div>
    <h2 class="text-2xl font-bold text-brand dark:text-green-400 mb-2" data-t="confirmationTitle"><?= htmlspecialchars($l['title'], ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="text-gray-600 dark:text-gray-300 mb-6" data-t="confirmationSubtitle"><?= htmlspecialchars($l['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>

    <div class="text-left max-w-md mx-auto space-y-2 mb-6">
        <?php foreach ($details as [$label, $value]): ?>
        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-600">
            <span class="font-medium text-gray-700 dark:text-gray-300"><?= $label ?>:</span>
            <span class="text-gray-900 dark:text-gray-100"><?= $value ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($bookingResponse['account_created'])): ?>
    <div class="p-4 rounded-lg border mb-6" style="background:#f0fdf4;border-color:#86efac;">
        <p class="text-sm" style="margin:0;color:#166534;"><?= htmlspecialchars($l['accountCreated'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <?php endif; ?>

    <div class="flex justify-center gap-4 flex-wrap">
        <a href="/" class="bg-brand text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition" data-t="backToHome"><?= htmlspecialchars($l['backToHome'], ENT_QUOTES, 'UTF-8') ?></a>
        <a href="/book-appointment/" class="border-2 border-brand dark:border-green-400 text-brand dark:text-green-400 px-6 py-2 rounded-lg font-semibold hover:bg-brand/5 dark:hover:bg-green-400/10 transition" data-t="bookAnother"><?= htmlspecialchars($l['bookAnother'], ENT_QUOTES, 'UTF-8') ?></a>
    </div>
</div>
