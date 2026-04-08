<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/mail.php';

$date = date('j \d\e F, Y');
$dateEn = date('F j, Y');

// Spanish email to oregontirespdx@gmail.com
$subjectEs = 'Migración de Servidor Completada — Oregon Tires Auto Care';
$bodyEs = <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
  <div style="background: #15803d; padding: 20px; text-align: center;">
    <h1 style="color: #fff; margin: 0; font-size: 22px;">Oregon Tires Auto Care</h1>
  </div>
  <div style="padding: 24px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <h2 style="color: #15803d; margin-top: 0;">Migración de Servidor Exitosa</h2>
    <p>Estimado equipo de Oregon Tires,</p>
    <p>Les informamos que la migración del servidor web de <strong>MochaHost</strong> a <strong>BlueHost</strong> se completó exitosamente el día de hoy, {$date}.</p>

    <h3 style="color: #15803d;">¿Qué se migró?</h3>
    <ul>
      <li><strong>Sitio web completo</strong> — todas las páginas, panel de administración, portal de miembros y APIs</li>
      <li><strong>Base de datos</strong> — 79 tablas con todos los datos (clientes, citas, órdenes de reparación, etc.)</li>
      <li><strong>Correo electrónico</strong> — configurado y funcionando en el nuevo servidor</li>
      <li><strong>Tareas automatizadas</strong> — 9 trabajos programados (recordatorios de citas, notificaciones push, respaldos, etc.)</li>
      <li><strong>Respaldos automáticos</strong> — respaldo diario de la base de datos a las 3:00 AM con retención de 30 días</li>
      <li><strong>DNS</strong> — todos los registros actualizados (A, MX, SPF, DKIM, DMARC)</li>
    </ul>

    <h3 style="color: #15803d;">Información del Nuevo Servidor</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>Proveedor:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">BlueHost</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>PHP:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">8.3.30</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>SSL:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">Let's Encrypt (renovación automática)</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>Sitio web:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">https://oregon.tires</td></tr>
      <tr><td style="padding: 6px;"><strong>Estado:</strong></td><td style="padding: 6px; color: #15803d; font-weight: bold;">En línea y funcionando</td></tr>
    </table>

    <h3 style="color: #15803d;">¿Qué deben hacer ustedes?</h3>
    <p><strong>Nada.</strong> Todo está funcionando normalmente. El sitio web, correo electrónico, citas en línea y el panel de administración están operando sin interrupciones.</p>
    <p>Si notan cualquier problema, por favor no duden en comunicarse con nosotros.</p>

    <p style="margin-top: 24px;">Atentamente,<br><strong>Equipo de Desarrollo — 1vsM Network</strong></p>
  </div>
  <div style="background: #f3f4f6; padding: 12px; text-align: center; font-size: 12px; color: #6b7280;">
    Oregon Tires Auto Care — 8536 SE 82nd Ave, Portland, OR 97266 — (503) 367-9714
  </div>
</div>
HTML;

// English email to tyronenorris@gmail.com
$subjectEn = 'Server Migration Complete — Oregon Tires Auto Care';
$bodyEn = <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
  <div style="background: #15803d; padding: 20px; text-align: center;">
    <h1 style="color: #fff; margin: 0; font-size: 22px;">Oregon Tires Auto Care</h1>
  </div>
  <div style="padding: 24px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <h2 style="color: #15803d; margin-top: 0;">Server Migration Successful</h2>
    <p>Hi Tyrone,</p>
    <p>The server migration for Oregon Tires from <strong>MochaHost</strong> to <strong>BlueHost</strong> was completed successfully today, {$dateEn}.</p>

    <h3 style="color: #15803d;">What Was Migrated</h3>
    <ul>
      <li><strong>Full website</strong> — all pages, admin panel, member portal, and 134 API endpoints</li>
      <li><strong>Database</strong> — 79 tables with all data (customers, appointments, repair orders, etc.)</li>
      <li><strong>Email</strong> — configured and working on the new server (SMTP, IMAP, DKIM, SPF)</li>
      <li><strong>Automated tasks</strong> — 9 cron jobs (appointment reminders, push notifications, backups, etc.)</li>
      <li><strong>Daily backups</strong> — automated database backup at 3:00 AM with 30-day retention</li>
      <li><strong>DNS</strong> — all records updated and nameservers switched from MochaHost to Porkbun</li>
      <li><strong>4 shared kits</strong> — member-kit, form-kit, commerce-kit, engine-kit</li>
    </ul>

    <h3 style="color: #15803d;">New Server Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>Provider:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">BlueHost</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>PHP:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">8.3.30</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>SSL:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">Let's Encrypt (auto-renewing, 29 days remaining)</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>IP:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">129.121.65.201</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>SSH:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">ssh oregontires (pending shell access enablement)</td></tr>
      <tr><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;"><strong>cPanel User:</strong></td><td style="padding: 6px; border-bottom: 1px solid #e5e7eb;">avadpnmy</td></tr>
      <tr><td style="padding: 6px;"><strong>Status:</strong></td><td style="padding: 6px; color: #15803d; font-weight: bold;">Online & Healthy</td></tr>
    </table>

    <h3 style="color: #15803d;">Changes Made During Migration</h3>
    <ul>
      <li><code>bootstrap.php</code> — path resolution updated for BlueHost directory structure</li>
      <li><code>.htaccess</code> — removed <code>RemoteIPHeader</code> (not allowed on BlueHost shared hosting)</li>
      <li><code>.user.ini</code> — OPcache timestamps enabled for SFTP deploys</li>
      <li><code>deploy.sh</code> — SSH host updated to <code>oregontires</code></li>
      <li><code>health-monitor.php</code> — backup path updated</li>
      <li>Nameservers switched from MochaHost to Porkbun</li>
    </ul>

    <h3 style="color: #15803d;">Next Steps</h3>
    <ul>
      <li>MochaHost can be cancelled — nothing depends on it</li>
      <li>BlueHost SSH shell access still needs to be enabled (currently SFTP only)</li>
      <li>DKIM key was updated to BlueHost's key in Porkbun DNS</li>
    </ul>

    <p style="margin-top: 24px;">Best,<br><strong>1vsM Development Team</strong></p>
  </div>
  <div style="background: #f3f4f6; padding: 12px; text-align: center; font-size: 12px; color: #6b7280;">
    Oregon Tires Auto Care — 8536 SE 82nd Ave, Portland, OR 97266 — (503) 367-9714
  </div>
</div>
HTML;

// Plain text versions
$plainEs = strip_tags(str_replace(['<li>', '<br>', '</h2>', '</h3>', '</p>'], ["\n- ", "\n", "\n", "\n", "\n"], $bodyEs));
$plainEn = strip_tags(str_replace(['<li>', '<br>', '</h2>', '</h3>', '</p>'], ["\n- ", "\n", "\n", "\n", "\n"], $bodyEn));

// Send Spanish to oregontirespdx
$result1 = sendMail('oregontirespdx@gmail.com', $subjectEs, $bodyEs, $plainEs);
echo "oregontirespdx@gmail.com (ES): " . ($result1 ? 'SENT' : 'FAILED') . "\n";

// Send English to tyronenorris
$result2 = sendMail('tyronenorris@gmail.com', $subjectEn, $bodyEn, $plainEn);
echo "tyronenorris@gmail.com (EN): " . ($result2 ? 'SENT' : 'FAILED') . "\n";
