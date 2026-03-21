<?php
/**
 * Send RO Process Guide email to all active admins.
 * Run: php cli/send-ro-guide-email.php
 */

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/mail.php';

$pdo = getDB();

$admins = $pdo->query("SELECT email, display_name, language FROM oretir_admins WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

$subjectEn = 'How to Use the Repair Order System — Step-by-Step Guide';
$subjectEs = 'Cómo Usar el Sistema de Órdenes de Reparación — Guía Paso a Paso';

$bodyEn = <<<'HTML'
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 600px; margin: 0 auto; color: #1f2937;">

<h2 style="color: #15803d; margin-bottom: 4px;">Repair Order System — Step-by-Step Guide</h2>
<p style="color: #6b7280; font-size: 14px; margin-top: 0;">Everything you need to know to manage a job from start to finish.</p>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 20px 0;">

<h3 style="color: #1f2937;">The 10-Step Process</h3>
<p style="font-size: 14px; color: #6b7280;">Every repair order follows this flow. You don't need to memorize it — the system guides you through each step with a single button.</p>

<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin: 16px 0;">
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; width: 60px; text-align: center;">Step 1</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🔍 Intake & Inspection</strong><br><span style="color: #6b7280;">Vehicle arrives. Click <strong>"Start Inspection"</strong> to create the digital vehicle inspection (DVI). Document the condition, take photos, mark items green/yellow/red.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 2</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>⚙️ Diagnosis</strong><br><span style="color: #6b7280;">Review inspection findings. The system automatically moves to this step after inspection.</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 3</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>📋 Create Estimate</strong><br><span style="color: #6b7280;">Click <strong>"Create Estimate"</strong>. The system builds it from your inspection's red/yellow items. Edit prices and add line items as needed.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 4</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>📧 Send to Customer</strong><br><span style="color: #6b7280;">Click <strong>"Send to Customer"</strong>. The customer receives an email with a link to view and approve/decline each item.</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 5</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>✅ Customer Approval</strong><br><span style="color: #6b7280;">Wait for customer to approve. If they call to approve by phone, click <strong>"Mark Approved"</strong>.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 6</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🚀 Start Work & Clock In</strong><br><span style="color: #6b7280;">Click <strong>"Start Work & Clock In"</strong>. This does 3 things automatically:<br>• Checks in the customer (visit tracking starts)<br>• Clocks in the technician (labor tracking starts)<br>• Starts the work timer</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 7</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🔧 Work In Progress</strong><br><span style="color: #6b7280;">The tech is working. You can see the live timer in Labor Tracking. When done, click <strong>"Mark Ready"</strong>. This clocks out the tech and sends the customer a "Your vehicle is ready!" notification.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 8</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🚗 Ready for Pickup</strong><br><span style="color: #6b7280;">Customer has been notified. When they arrive to pick up, click <strong>"Complete & Check Out"</strong>. This checks the customer out of the shop and records total time in building.</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 9</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>💰 Invoiced</strong><br><span style="color: #6b7280;">Invoice is automatically generated from the approved estimate and emailed to the customer. No extra clicks needed.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Step 10</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🏁 Done</strong><br><span style="color: #6b7280;">Order is complete. All time data (wait time, service duration, total time in building) is saved for reporting.</span></td>
</tr>
</table>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 20px 0;">

<h3 style="color: #1f2937;">Where to Start</h3>
<ol style="font-size: 14px; color: #4b5563; line-height: 1.8;">
  <li>Go to the <strong>Appointments</strong> tab — every appointment now shows which step its repair order is on</li>
  <li>Click the <strong>step badge</strong> (e.g. "🔍 2/10 Create estimate") to open the repair order</li>
  <li>Follow the <strong>colored action bar</strong> at the top — it tells you exactly what to do next</li>
  <li>Click the <strong>white button</strong> — the system handles everything and moves to the next step</li>
  <li>Repeat until the order says <strong>"✅ Complete and invoiced"</strong></li>
</ol>

<h3 style="color: #1f2937;">Special Situations</h3>
<ul style="font-size: 14px; color: #4b5563; line-height: 1.8;">
  <li><strong>Waiting for parts?</strong> — The system has an "On Hold" and "Waiting Parts" status. Use these and click "Resume" when ready.</li>
  <li><strong>Need to skip a step?</strong> — Open "Vehicle & Order Details" at the bottom and use the manual status override.</li>
  <li><strong>Adding notes?</strong> — Click the "Notes" section to add technician or admin notes at any time.</li>
  <li><strong>Labor tracking?</strong> — Scroll down in the RO detail to see the Labor Tracking section. You can clock in/out additional techs there.</li>
</ul>

<div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 12px; padding: 16px; margin: 20px 0; text-align: center;">
  <p style="font-size: 16px; font-weight: bold; color: #15803d; margin: 0 0 8px;">One button. Every step. That's it.</p>
  <p style="font-size: 13px; color: #6b7280; margin: 0;">Open the repair order → click the button → repeat.</p>
</div>

</div>
HTML;

$bodyEs = <<<'HTML'
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 600px; margin: 0 auto; color: #1f2937;">

<h2 style="color: #15803d; margin-bottom: 4px;">Sistema de Órdenes de Reparación — Guía Paso a Paso</h2>
<p style="color: #6b7280; font-size: 14px; margin-top: 0;">Todo lo que necesita saber para manejar un trabajo de principio a fin.</p>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 20px 0;">

<h3 style="color: #1f2937;">El Proceso de 10 Pasos</h3>
<p style="font-size: 14px; color: #6b7280;">Cada orden de reparación sigue este flujo. No necesita memorizarlo — el sistema lo guía en cada paso con un solo botón.</p>

<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin: 16px 0;">
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; width: 60px; text-align: center;">Paso 1</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🔍 Recepción e Inspección</strong><br><span style="color: #6b7280;">El vehículo llega. Haga clic en <strong>"Iniciar Inspección"</strong> para crear la inspección digital del vehículo. Documente la condición, tome fotos, marque los elementos en verde/amarillo/rojo.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 2</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>⚙️ Diagnóstico</strong><br><span style="color: #6b7280;">Revise los hallazgos de la inspección. El sistema avanza automáticamente a este paso.</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 3</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>📋 Crear Presupuesto</strong><br><span style="color: #6b7280;">Haga clic en <strong>"Crear Presupuesto"</strong>. El sistema lo construye a partir de los elementos rojos/amarillos de la inspección. Edite precios y agregue elementos según sea necesario.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 4</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>📧 Enviar al Cliente</strong><br><span style="color: #6b7280;">Haga clic en <strong>"Enviar al Cliente"</strong>. El cliente recibe un correo con un enlace para ver y aprobar/rechazar cada elemento.</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 5</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>✅ Aprobación del Cliente</strong><br><span style="color: #6b7280;">Espere la aprobación. Si el cliente aprueba por teléfono, haga clic en <strong>"Marcar Aprobado"</strong>.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 6</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🚀 Iniciar Trabajo</strong><br><span style="color: #6b7280;">Haga clic en <strong>"Iniciar Trabajo"</strong>. Esto hace 3 cosas automáticamente:<br>• Registra la llegada del cliente<br>• Registra la entrada del técnico (comienza el registro de horas)<br>• Inicia el temporizador de trabajo</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 7</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🔧 Trabajo en Progreso</strong><br><span style="color: #6b7280;">El técnico está trabajando. Puede ver el temporizador en vivo. Cuando termine, haga clic en <strong>"Marcar Listo"</strong>. Esto registra la salida del técnico y envía al cliente la notificación "¡Su vehículo está listo!"</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 8</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🚗 Listo para Recoger</strong><br><span style="color: #6b7280;">El cliente ha sido notificado. Cuando llegue a recoger, haga clic en <strong>"Completar y Facturar"</strong>. Esto registra la salida del cliente y el tiempo total en el taller.</span></td>
</tr>
<tr style="background: #f0fdf4;">
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 9</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>💰 Facturado</strong><br><span style="color: #6b7280;">La factura se genera automáticamente del presupuesto aprobado y se envía al cliente por correo. No se necesitan clics adicionales.</span></td>
</tr>
<tr>
  <td style="padding: 10px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center;">Paso 10</td>
  <td style="padding: 10px; border: 1px solid #e5e7eb;"><strong>🏁 Terminado</strong><br><span style="color: #6b7280;">La orden está completa. Todos los datos de tiempo (espera, duración del servicio, tiempo total en el taller) se guardan para reportes.</span></td>
</tr>
</table>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 20px 0;">

<h3 style="color: #1f2937;">Cómo Empezar</h3>
<ol style="font-size: 14px; color: #4b5563; line-height: 1.8;">
  <li>Vaya a la pestaña <strong>Citas</strong> — cada cita muestra en qué paso está su orden de reparación</li>
  <li>Haga clic en la <strong>insignia del paso</strong> (ej. "🔍 2/10 Crear presupuesto") para abrir la orden</li>
  <li>Siga la <strong>barra de acción de color</strong> en la parte superior — le dice exactamente qué hacer</li>
  <li>Haga clic en el <strong>botón blanco</strong> — el sistema maneja todo y avanza al siguiente paso</li>
  <li>Repita hasta que la orden diga <strong>"✅ Completa y facturada"</strong></li>
</ol>

<h3 style="color: #1f2937;">Situaciones Especiales</h3>
<ul style="font-size: 14px; color: #4b5563; line-height: 1.8;">
  <li><strong>¿Esperando piezas?</strong> — El sistema tiene estados "En Espera" y "Esperando Piezas". Úselos y haga clic en "Reanudar" cuando esté listo.</li>
  <li><strong>¿Necesita saltar un paso?</strong> — Abra "Detalles del Vehículo y Orden" abajo y use el control manual de estado.</li>
  <li><strong>¿Agregar notas?</strong> — Haga clic en la sección "Notas" para agregar notas técnicas o administrativas en cualquier momento.</li>
  <li><strong>¿Registro de horas?</strong> — Desplácese hacia abajo en el detalle de la orden para ver el Registro de Horas. Puede registrar entrada/salida de técnicos adicionales ahí.</li>
</ul>

<div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 12px; padding: 16px; margin: 20px 0; text-align: center;">
  <p style="font-size: 16px; font-weight: bold; color: #15803d; margin: 0 0 8px;">Un botón. Cada paso. Eso es todo.</p>
  <p style="font-size: 13px; color: #6b7280; margin: 0;">Abra la orden de reparación → haga clic en el botón → repita.</p>
</div>

</div>
HTML;

echo "Sending RO process guide to " . count($admins) . " admins...\n\n";

$sent = 0;
$failed = 0;

foreach ($admins as $admin) {
    $name = $admin['display_name'] ?: 'Team';
    $lang = $admin['language'] ?: 'both';

    // Determine which version to send
    if ($lang === 'es') {
        $subject = $subjectEs;
        $body = '<p style="font-size: 15px; color: #1f2937;">Hola <strong>' . htmlspecialchars($name) . '</strong>,</p>' . $bodyEs;
    } elseif ($lang === 'en') {
        $subject = $subjectEn;
        $body = '<p style="font-size: 15px; color: #1f2937;">Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>' . $bodyEn;
    } else {
        // Both — send bilingual
        $subject = $subjectEn . ' / ' . $subjectEs;
        $body = '<p style="font-size: 15px; color: #1f2937;">Hi / Hola <strong>' . htmlspecialchars($name) . '</strong>,</p>'
              . $bodyEn
              . '<hr style="border: none; border-top: 3px solid #15803d; margin: 30px 0;">'
              . '<p style="font-size: 13px; color: #6b7280; text-align: center; font-weight: bold;">VERSIÓN EN ESPAÑOL / SPANISH VERSION BELOW</p>'
              . $bodyEs;
    }

    try {
        $result = sendMail($admin['email'], $subject, $body);
        if ($result['success']) {
            echo "  \u2713 Sent to {$admin['email']} ({$name}) [{$lang}]\n";
            $sent++;
            logEmail('ro_guide', "RO process guide sent to {$admin['email']}");
        } else {
            echo "  \u2717 Failed for {$admin['email']}: " . ($result['error'] ?? 'Unknown error') . "\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "  \u2717 Failed for {$admin['email']}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\nDone: {$sent} sent, {$failed} failed.\n";
