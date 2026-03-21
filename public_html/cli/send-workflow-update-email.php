<?php
/**
 * Send Workflow Overhaul announcement email to all active admins.
 * Introduces the new 12-status RO flow, check-in step, smart automation,
 * manager gate, and Job Board.
 *
 * Run: php cli/send-workflow-update-email.php
 */

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/mail.php';

$pdo = getDB();

$admins = $pdo->query("SELECT email, display_name, language FROM oretir_admins WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

if (empty($admins)) {
    echo "No active admins found.\n";
    exit(0);
}

$subjectEn = 'New Workflow Update — Smarter Repair Orders, Job Board & Live Timers';
$subjectEs = 'Actualización del Flujo de Trabajo — Órdenes Más Inteligentes, Tablero de Trabajo y Temporizadores en Vivo';

$bodyEn = <<<'HTML'
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 620px; margin: 0 auto; color: #1f2937;">

<div style="background: linear-gradient(135deg, #15803d, #0d9488); border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: center;">
  <h1 style="color: #ffffff; font-size: 22px; margin: 0 0 8px;">Repair Order Workflow Update</h1>
  <p style="color: #bbf7d0; font-size: 14px; margin: 0;">Smarter automation, live timers, and a real-time Job Board</p>
</div>

<p style="font-size: 15px; line-height: 1.6;">We've overhauled how repair orders flow through the shop. The system is now smarter &mdash; each status change automatically triggers the right side effects (timers, notifications, labor tracking) so you can focus on the work, not clicking buttons.</p>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 24px 0;">

<h2 style="color: #15803d; font-size: 18px;">What's New</h2>

<!-- 1. Check-In Step -->
<div style="background: #f0fdf4; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #06b6d4;">
  <h3 style="margin: 0 0 8px; color: #0e7490; font-size: 15px;">1. New "Check In" Step</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">When a customer arrives, click <strong>"Check In Customer"</strong> in the repair order. This:</p>
  <ul style="font-size: 13px; color: #4b5563; margin: 8px 0 0; padding-left: 20px; line-height: 1.7;">
    <li>Records the arrival time for visit tracking</li>
    <li>Shows the vehicle on the Job Board</li>
    <li>Prepares the order for diagnosis</li>
  </ul>
  <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0;"><strong>New flow:</strong> Intake &rarr; <span style="background:#cffafe;padding:2px 6px;border-radius:4px;font-weight:bold;color:#0e7490;">Check In</span> &rarr; Diagnosis &rarr; ...</p>
</div>

<!-- 2. Smart Automation -->
<div style="background: #fefce8; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #eab308;">
  <h3 style="margin: 0 0 8px; color: #854d0e; font-size: 15px;">2. Smart Automation on Every Status Change</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">The system now auto-handles side effects so you don't have to:</p>
  <table style="width:100%; font-size:13px; margin-top:10px; border-collapse:collapse;">
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Start Diagnosis"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Auto clocks in the assigned technician &amp; starts the repair timer</td></tr>
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Waiting Parts" / "On Hold"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Auto clocks out all active labor (timer pauses)</td></tr>
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Parts Arrived" / "Resume"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Auto clocks the tech back in (timer resumes)</td></tr>
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Mark Ready"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Clocks out all labor, notifies customer by email &amp; SMS</td></tr>
    <tr><td style="padding:6px 8px;"><strong>Create / send estimate</strong></td><td style="padding:6px 8px; color:#6b7280;">Auto-advances the RO status (no manual status change needed)</td></tr>
  </table>
</div>

<!-- 3. Manager Gate -->
<div style="background: #fef2f2; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #dc2626;">
  <h3 style="margin: 0 0 8px; color: #991b1b; font-size: 15px;">3. Manager Gate Before Invoicing</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;"><strong>Important change:</strong> "Completed" no longer auto-generates the invoice. Instead:</p>
  <ol style="font-size: 13px; color: #4b5563; margin: 8px 0 0; padding-left: 20px; line-height: 1.8;">
    <li>Click <strong>"Mark Complete"</strong> &mdash; manager reviews the finished work</li>
    <li>Click <strong>"Invoice &amp; Check Out"</strong> &mdash; generates the invoice, emails it, and checks out the customer</li>
  </ol>
  <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0;">This gives management a chance to review before anything goes to the customer.</p>
</div>

<!-- 4. Live Timers -->
<div style="background: #f0f9ff; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #3b82f6;">
  <h3 style="margin: 0 0 8px; color: #1d4ed8; font-size: 15px;">4. Live Timers in Every RO</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">When you open a repair order, you'll see two live timers below the status bar:</p>
  <div style="display:flex; gap:24px; margin-top:10px;">
    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#22c55e;margin-right:4px;"></span><strong style="font-size:13px; color:#15803d;">VISIT</strong><br><span style="font-size:12px; color:#6b7280;">Time since check-in</span></div>
    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f97316;margin-right:4px;"></span><strong style="font-size:13px; color:#ea580c;">REPAIR</strong><br><span style="font-size:12px; color:#6b7280;">Active labor time</span></div>
  </div>
</div>

<!-- 5. Job Board -->
<div style="background: #faf5ff; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #8b5cf6;">
  <h3 style="margin: 0 0 8px; color: #6d28d9; font-size: 15px;">5. New Job Board (Labor Tab)</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">The Labor Tracking tab is now a <strong>real-time Job Board</strong> showing every active repair order at a glance:</p>
  <ul style="font-size: 13px; color: #4b5563; margin: 8px 0 0; padding-left: 20px; line-height: 1.7;">
    <li>Mini status stepper on each card &mdash; see progress instantly</li>
    <li>Live VISIT and REPAIR timers per vehicle</li>
    <li>Assigned tech + quick-action button ("Start Diagnosis", "Mark Ready", etc.)</li>
    <li>Filter by status: All Active, Checked In, In Progress, Waiting, Ready</li>
    <li>Click any card to open the full RO detail</li>
    <li>Switch to <strong>Reports</strong> tab for employee hours summaries</li>
  </ul>
</div>

<!-- 6. Kanban Update -->
<div style="background: #f8fafc; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #64748b;">
  <h3 style="margin: 0 0 8px; color: #475569; font-size: 15px;">6. Updated Kanban Board</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">The Kanban view now has a <strong>Check In</strong> column (cyan) between Intake and Diagnosis. Drag-and-drop still works across all 12 columns.</p>
</div>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 24px 0;">

<h2 style="color: #15803d; font-size: 18px;">The New 12-Step Flow</h2>

<div style="font-size: 13px; color: #4b5563; line-height: 2.2; padding: 0 8px;">
  <span style="background:#dbeafe;padding:3px 8px;border-radius:4px;">Intake</span>
  &rarr; <span style="background:#cffafe;padding:3px 8px;border-radius:4px;font-weight:bold;">Check In</span>
  &rarr; <span style="background:#ede9fe;padding:3px 8px;border-radius:4px;">Diagnosis</span>
  &rarr; <span style="background:#fef3c7;padding:3px 8px;border-radius:4px;">Estimate</span>
  &rarr; <span style="background:#ffedd5;padding:3px 8px;border-radius:4px;">Approval</span>
  &rarr; <span style="background:#dcfce7;padding:3px 8px;border-radius:4px;">Approved</span>
  &rarr; <span style="background:#e0e7ff;padding:3px 8px;border-radius:4px;">In Progress</span>
  &rarr; <span style="background:#d1fae5;padding:3px 8px;border-radius:4px;">Ready</span>
  &rarr; <span style="background:#f3f4f6;padding:3px 8px;border-radius:4px;">Completed</span>
  &rarr; <span style="background:#ccfbf1;padding:3px 8px;border-radius:4px;">Invoiced</span>
</div>
<p style="font-size: 12px; color: #9ca3af; margin-top: 4px; padding: 0 8px;">(+ On Hold and Waiting Parts branch off from In Progress and rejoin when resumed)</p>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 24px 0;">

<h2 style="color: #15803d; font-size: 18px;">Quick Start</h2>
<ol style="font-size: 14px; color: #4b5563; line-height: 1.8;">
  <li>Open a Repair Order (or create one from an appointment)</li>
  <li>Follow the <strong>colored action bar</strong> at the top &mdash; it tells you the next step</li>
  <li>Click the button &mdash; the system handles timers, notifications, and status changes</li>
  <li>Check the <strong>Labor Tracking</strong> tab for the Job Board view of all active work</li>
</ol>

<div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 12px; padding: 16px; margin: 20px 0; text-align: center;">
  <p style="font-size: 16px; font-weight: bold; color: #15803d; margin: 0 0 8px;">One button per step. Smart automation does the rest.</p>
  <p style="font-size: 13px; color: #6b7280; margin: 0;">No need to manually clock in/out, change statuses, or send notifications &mdash; it all happens automatically.</p>
</div>

<p style="font-size: 13px; color: #9ca3af; text-align: center;">Questions? Reply to this email or reach out anytime.</p>

</div>
HTML;

$bodyEs = <<<'HTML'
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 620px; margin: 0 auto; color: #1f2937;">

<div style="background: linear-gradient(135deg, #15803d, #0d9488); border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: center;">
  <h1 style="color: #ffffff; font-size: 22px; margin: 0 0 8px;">Actualizaci&oacute;n del Flujo de Trabajo</h1>
  <p style="color: #bbf7d0; font-size: 14px; margin: 0;">Automatizaci&oacute;n inteligente, temporizadores en vivo y un Tablero de Trabajo en tiempo real</p>
</div>

<p style="font-size: 15px; line-height: 1.6;">Hemos renovado c&oacute;mo fluyen las &oacute;rdenes de reparaci&oacute;n en el taller. El sistema es ahora m&aacute;s inteligente &mdash; cada cambio de estado activa autom&aacute;ticamente los efectos correctos (temporizadores, notificaciones, registro de horas) para que pueda enfocarse en el trabajo, no en hacer clics.</p>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 24px 0;">

<h2 style="color: #15803d; font-size: 18px;">&iquest;Qu&eacute; Hay de Nuevo?</h2>

<!-- 1. Registro -->
<div style="background: #f0fdf4; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #06b6d4;">
  <h3 style="margin: 0 0 8px; color: #0e7490; font-size: 15px;">1. Nuevo Paso "Registro"</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">Cuando llega un cliente, haga clic en <strong>"Registrar Cliente"</strong> en la orden de reparaci&oacute;n. Esto:</p>
  <ul style="font-size: 13px; color: #4b5563; margin: 8px 0 0; padding-left: 20px; line-height: 1.7;">
    <li>Registra la hora de llegada para el seguimiento de visitas</li>
    <li>Muestra el veh&iacute;culo en el Tablero de Trabajo</li>
    <li>Prepara la orden para el diagn&oacute;stico</li>
  </ul>
  <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0;"><strong>Nuevo flujo:</strong> Recepci&oacute;n &rarr; <span style="background:#cffafe;padding:2px 6px;border-radius:4px;font-weight:bold;color:#0e7490;">Registro</span> &rarr; Diagn&oacute;stico &rarr; ...</p>
</div>

<!-- 2. Automatizacion -->
<div style="background: #fefce8; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #eab308;">
  <h3 style="margin: 0 0 8px; color: #854d0e; font-size: 15px;">2. Automatizaci&oacute;n Inteligente en Cada Cambio de Estado</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">El sistema ahora maneja los efectos secundarios autom&aacute;ticamente:</p>
  <table style="width:100%; font-size:13px; margin-top:10px; border-collapse:collapse;">
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Iniciar Diagn&oacute;stico"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Registra autom&aacute;ticamente la entrada del t&eacute;cnico e inicia el temporizador</td></tr>
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Esperando Piezas" / "En Espera"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Registra la salida de todo el personal activo (temporizador se pausa)</td></tr>
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Piezas Llegaron" / "Reanudar"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Registra la entrada del t&eacute;cnico de nuevo (temporizador se reanuda)</td></tr>
    <tr><td style="padding:6px 8px; border-bottom:1px solid #fef3c7;"><strong>"Marcar Listo"</strong></td><td style="padding:6px 8px; border-bottom:1px solid #fef3c7; color:#6b7280;">Registra salida del personal, notifica al cliente por correo y SMS</td></tr>
    <tr><td style="padding:6px 8px;"><strong>Crear / enviar presupuesto</strong></td><td style="padding:6px 8px; color:#6b7280;">Avanza autom&aacute;ticamente el estado de la orden</td></tr>
  </table>
</div>

<!-- 3. Aprobacion del Gerente -->
<div style="background: #fef2f2; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #dc2626;">
  <h3 style="margin: 0 0 8px; color: #991b1b; font-size: 15px;">3. Aprobaci&oacute;n del Gerente Antes de Facturar</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;"><strong>Cambio importante:</strong> "Completado" ya no genera la factura autom&aacute;ticamente. En su lugar:</p>
  <ol style="font-size: 13px; color: #4b5563; margin: 8px 0 0; padding-left: 20px; line-height: 1.8;">
    <li>Haga clic en <strong>"Marcar Completo"</strong> &mdash; el gerente revisa el trabajo terminado</li>
    <li>Haga clic en <strong>"Facturar y Despachar"</strong> &mdash; genera la factura, la env&iacute;a por correo y registra la salida del cliente</li>
  </ol>
  <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0;">Esto da a la gerencia la oportunidad de revisar antes de enviar algo al cliente.</p>
</div>

<!-- 4. Temporizadores -->
<div style="background: #f0f9ff; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #3b82f6;">
  <h3 style="margin: 0 0 8px; color: #1d4ed8; font-size: 15px;">4. Temporizadores en Vivo en Cada Orden</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">Al abrir una orden de reparaci&oacute;n, ver&aacute; dos temporizadores en vivo debajo de la barra de estado:</p>
  <div style="display:flex; gap:24px; margin-top:10px;">
    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#22c55e;margin-right:4px;"></span><strong style="font-size:13px; color:#15803d;">VISITA</strong><br><span style="font-size:12px; color:#6b7280;">Tiempo desde el registro</span></div>
    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f97316;margin-right:4px;"></span><strong style="font-size:13px; color:#ea580c;">REPARACI&Oacute;N</strong><br><span style="font-size:12px; color:#6b7280;">Tiempo activo de trabajo</span></div>
  </div>
</div>

<!-- 5. Tablero de Trabajo -->
<div style="background: #faf5ff; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #8b5cf6;">
  <h3 style="margin: 0 0 8px; color: #6d28d9; font-size: 15px;">5. Nuevo Tablero de Trabajo (Pesta&ntilde;a de Horas)</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">La pesta&ntilde;a de Registro de Horas ahora es un <strong>Tablero de Trabajo en tiempo real</strong> que muestra cada orden activa:</p>
  <ul style="font-size: 13px; color: #4b5563; margin: 8px 0 0; padding-left: 20px; line-height: 1.7;">
    <li>Mini barra de progreso en cada tarjeta &mdash; vea el avance instant&aacute;neamente</li>
    <li>Temporizadores de VISITA y REPARACI&Oacute;N en vivo por veh&iacute;culo</li>
    <li>T&eacute;cnico asignado + bot&oacute;n de acci&oacute;n r&aacute;pida</li>
    <li>Filtrar por estado: Todas Activas, Registradas, En Progreso, En Espera, Listas</li>
    <li>Haga clic en cualquier tarjeta para abrir el detalle completo</li>
    <li>Cambie a <strong>Reportes</strong> para ver res&uacute;menes de horas por empleado</li>
  </ul>
</div>

<!-- 6. Kanban -->
<div style="background: #f8fafc; border-radius: 10px; padding: 16px; margin: 16px 0; border-left: 4px solid #64748b;">
  <h3 style="margin: 0 0 8px; color: #475569; font-size: 15px;">6. Tablero Kanban Actualizado</h3>
  <p style="font-size: 14px; color: #4b5563; margin: 0;">El tablero Kanban ahora tiene una columna <strong>Registro</strong> (cian) entre Recepci&oacute;n y Diagn&oacute;stico. Arrastrar y soltar funciona en las 12 columnas.</p>
</div>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 24px 0;">

<h2 style="color: #15803d; font-size: 18px;">El Nuevo Flujo de 12 Pasos</h2>

<div style="font-size: 13px; color: #4b5563; line-height: 2.2; padding: 0 8px;">
  <span style="background:#dbeafe;padding:3px 8px;border-radius:4px;">Recepci&oacute;n</span>
  &rarr; <span style="background:#cffafe;padding:3px 8px;border-radius:4px;font-weight:bold;">Registro</span>
  &rarr; <span style="background:#ede9fe;padding:3px 8px;border-radius:4px;">Diagn&oacute;stico</span>
  &rarr; <span style="background:#fef3c7;padding:3px 8px;border-radius:4px;">Presupuesto</span>
  &rarr; <span style="background:#ffedd5;padding:3px 8px;border-radius:4px;">Aprobaci&oacute;n</span>
  &rarr; <span style="background:#dcfce7;padding:3px 8px;border-radius:4px;">Aprobado</span>
  &rarr; <span style="background:#e0e7ff;padding:3px 8px;border-radius:4px;">En Progreso</span>
  &rarr; <span style="background:#d1fae5;padding:3px 8px;border-radius:4px;">Listo</span>
  &rarr; <span style="background:#f3f4f6;padding:3px 8px;border-radius:4px;">Completado</span>
  &rarr; <span style="background:#ccfbf1;padding:3px 8px;border-radius:4px;">Facturado</span>
</div>
<p style="font-size: 12px; color: #9ca3af; margin-top: 4px; padding: 0 8px;">(+ En Espera y Esperando Piezas se ramifican desde En Progreso y se reintegran al reanudar)</p>

<hr style="border: none; border-top: 2px solid #dcfce7; margin: 24px 0;">

<h2 style="color: #15803d; font-size: 18px;">C&oacute;mo Empezar</h2>
<ol style="font-size: 14px; color: #4b5563; line-height: 1.8;">
  <li>Abra una Orden de Reparaci&oacute;n (o cree una desde una cita)</li>
  <li>Siga la <strong>barra de acci&oacute;n de color</strong> en la parte superior &mdash; le dice el siguiente paso</li>
  <li>Haga clic en el bot&oacute;n &mdash; el sistema maneja temporizadores, notificaciones y cambios de estado</li>
  <li>Revise la pesta&ntilde;a <strong>Registro de Horas</strong> para ver el Tablero de Trabajo con todo el trabajo activo</li>
</ol>

<div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 12px; padding: 16px; margin: 20px 0; text-align: center;">
  <p style="font-size: 16px; font-weight: bold; color: #15803d; margin: 0 0 8px;">Un bot&oacute;n por paso. La automatizaci&oacute;n hace el resto.</p>
  <p style="font-size: 13px; color: #6b7280; margin: 0;">No necesita registrar entrada/salida manualmente, cambiar estados o enviar notificaciones &mdash; todo sucede autom&aacute;ticamente.</p>
</div>

<p style="font-size: 13px; color: #9ca3af; text-align: center;">&iquest;Preguntas? Responda a este correo o comun&iacute;quese en cualquier momento.</p>

</div>
HTML;

echo "Sending workflow update email to " . count($admins) . " admins...\n\n";

$sent = 0;
$failed = 0;

foreach ($admins as $admin) {
    $name = $admin['display_name'] ?: 'Team';
    $lang = $admin['language'] ?: 'both';

    if ($lang === 'es') {
        $subject = $subjectEs;
        $body = '<p style="font-size: 15px; color: #1f2937;">Hola <strong>' . htmlspecialchars($name) . '</strong>,</p>' . $bodyEs;
    } elseif ($lang === 'en') {
        $subject = $subjectEn;
        $body = '<p style="font-size: 15px; color: #1f2937;">Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>' . $bodyEn;
    } else {
        $subject = $subjectEn . ' / ' . $subjectEs;
        $body = '<p style="font-size: 15px; color: #1f2937;">Hi / Hola <strong>' . htmlspecialchars($name) . '</strong>,</p>'
              . $bodyEn
              . '<hr style="border: none; border-top: 3px solid #15803d; margin: 30px 0;">'
              . '<p style="font-size: 13px; color: #6b7280; text-align: center; font-weight: bold;">VERSI&Oacute;N EN ESPA&Ntilde;OL ABAJO</p>'
              . $bodyEs;
    }

    try {
        $result = sendMail($admin['email'], $subject, $body);
        if ($result['success']) {
            echo "  \xE2\x9C\x93 Sent to {$admin['email']} ({$name}) [{$lang}]\n";
            $sent++;
            logEmail('workflow_update', "Workflow update email sent to {$admin['email']}");
        } else {
            echo "  \xE2\x9C\x97 Failed for {$admin['email']}: " . ($result['error'] ?? 'Unknown error') . "\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "  \xE2\x9C\x97 Failed for {$admin['email']}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\nDone: {$sent} sent, {$failed} failed.\n";
