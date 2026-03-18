<?php
$serviceName = 'Roadside Assistance';
$serviceNameEs = 'Asistencia en Carretera';
$serviceSlug = 'roadside-assistance';
$serviceIcon = '&#x1F6A8;';
$serviceDescription = 'Emergency roadside assistance in Portland, OR. Flat tire change, jump start, lockout help, and towing coordination. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Asistencia en carretera de emergencia en Portland, OR. Cambio de llanta ponchada, arranque con cables, ayuda con cerraduras y coordinacion de grua. Servicio bilingue.';
$serviceBody = '<p>Stranded on the road? Oregon Tires Auto Care provides emergency roadside assistance throughout the Portland metro area. Whether you have a flat tire, a dead battery, or you\'re locked out of your vehicle, our experienced team is ready to help you get back on the road quickly and safely.</p><p>We offer flat tire changes, jump starts, lockout assistance, and can coordinate towing to our shop or a location of your choice. Our bilingual team is available during business hours to ensure clear communication and fast response.</p>';
$serviceBodyEs = '<p>¿Varado en la carretera? Oregon Tires Auto Care ofrece asistencia en carretera de emergencia en toda el area metropolitana de Portland. Ya sea que tenga una llanta ponchada, una bateria muerta o este bloqueado fuera de su vehiculo, nuestro equipo experimentado esta listo para ayudarlo a volver a la carretera de manera rapida y segura.</p><p>Ofrecemos cambio de llantas ponchadas, arranque con cables, asistencia con cerraduras y coordinamos servicio de grua a nuestro taller o al lugar de su eleccion. Nuestro equipo bilingue esta disponible durante el horario de atencion.</p>';
$faqItems = [
    ['q' => 'What hours is roadside assistance available?', 'a' => 'Our roadside assistance is available during business hours, Monday through Saturday, 7AM to 7PM.', 'qEs' => 'En que horario esta disponible la asistencia en carretera?', 'aEs' => 'Nuestra asistencia en carretera esta disponible durante el horario de atencion, de lunes a sabado, de 7AM a 7PM.'],
    ['q' => 'What area do you cover?', 'a' => 'We cover the Portland metro area including SE Portland, Clackamas, Happy Valley, Milwaukie, and surrounding neighborhoods.', 'qEs' => 'Que area cubren?', 'aEs' => 'Cubrimos el area metropolitana de Portland incluyendo SE Portland, Clackamas, Happy Valley, Milwaukie y vecindarios cercanos.'],
    ['q' => 'What\'s included in roadside assistance?', 'a' => 'Our roadside service includes flat tire changes, battery jump starts, lockout assistance, and towing coordination. We can also perform basic roadside tire repairs when possible.', 'qEs' => 'Que incluye la asistencia en carretera?', 'aEs' => 'Nuestro servicio en carretera incluye cambio de llantas ponchadas, arranque de bateria con cables, asistencia con cerraduras y coordinacion de grua. Tambien podemos realizar reparaciones basicas de llantas cuando sea posible.'],
];
$relatedServices = [
    ['name' => 'Tire Repair', 'nameEs' => 'Reparacion de Llantas', 'slug' => 'tire-repair'],
    ['name' => 'Mobile Service', 'nameEs' => 'Servicio Movil', 'slug' => 'mobile-service'],
    ['name' => 'Tire Installation', 'nameEs' => 'Instalacion de Llantas', 'slug' => 'tire-installation'],
];

// Pricing Estimator section (injected before CTA)
$customSectionsBeforeCTA = '
  <!-- ═══ Pricing Estimator ═══ -->
  <section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">
        <span data-t="estimator_title">Get an Estimate</span>
      </h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6 text-center">
        <span data-t="estimator_subtitle">Select your service and location for an instant estimate.</span>
      </p>
      <div id="roadside-estimator" data-price-base="75" data-price-zone="15"></div>
    </div>
  </section>';

// Extra scripts for the estimator
$customScripts = '
<script src="/assets/js/roadside-estimator.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    RoadsideEstimator.init("roadside-estimator");
  });
</script>';

// Extra translation keys
$customTranslations = "
      estimator_title: 'Obtener Estimado',
      estimator_subtitle: 'Seleccione su servicio y ubicación para un estimado inmediato.',";

require __DIR__ . '/templates/service-detail.php';
