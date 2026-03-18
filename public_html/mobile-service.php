<?php
$serviceName = 'Mobile Service';
$serviceNameEs = 'Servicio Movil';
$serviceSlug = 'mobile-service';
$serviceIcon = '&#x1F69A;';
$serviceDescription = 'Mobile tire and auto service in Portland, OR. We come to your home, office, or roadside location. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Servicio movil de llantas y auto en Portland, OR. Vamos a su casa, oficina o ubicacion en la carretera. Servicio bilingue ingles y espanol.';
$serviceBody = '<p>Oregon Tires Auto Care brings professional tire and auto service directly to you anywhere in the Portland metro area. Whether you are at home, at work, or stranded on the road, our fully equipped mobile unit can handle tire installation, tire repair, flat fixes, and basic maintenance on-site.</p><p>Our mobile service saves you time and hassle — no need to drive to the shop or wait for a tow truck. We carry a full selection of new and quality used tires on our mobile unit, along with professional mounting and balancing equipment. Same-day service is available for most requests.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care lleva servicio profesional de llantas y auto directamente a usted en cualquier lugar del area metropolitana de Portland. Ya sea que este en casa, en el trabajo o varado en la carretera, nuestra unidad movil completamente equipada puede manejar instalacion de llantas, reparacion de llantas, arreglo de ponchadas y mantenimiento basico en el lugar.</p><p>Nuestro servicio movil le ahorra tiempo y molestias — no necesita manejar al taller ni esperar una grua. Llevamos una seleccion completa de llantas nuevas y usadas de calidad en nuestra unidad movil, junto con equipo profesional de montaje y balanceo. Servicio el mismo dia esta disponible para la mayoria de las solicitudes.</p>';
$faqItems = [
    ['q' => 'What areas do you serve with mobile service?', 'a' => 'We serve the entire Portland metro area including Beaverton, Gresham, Milwaukie, Tigard, Lake Oswego, and surrounding communities. Contact us to confirm coverage for your location.', 'qEs' => 'Que areas cubren con el servicio movil?', 'aEs' => 'Cubrimos toda el area metropolitana de Portland incluyendo Beaverton, Gresham, Milwaukie, Tigard, Lake Oswego y comunidades cercanas. Contactenos para confirmar cobertura en su ubicacion.'],
    ['q' => 'Is there an extra charge for mobile service?', 'a' => 'A small trip fee may apply depending on your location. We will provide the total cost upfront before dispatching our mobile unit — no surprises.', 'qEs' => 'Hay un cargo extra por el servicio movil?', 'aEs' => 'Una pequena tarifa de viaje puede aplicar dependiendo de su ubicacion. Le daremos el costo total por adelantado antes de enviar nuestra unidad movil — sin sorpresas.'],
    ['q' => 'How quickly can you arrive?', 'a' => 'For same-day requests, we typically arrive within 1-2 hours depending on availability and distance. You can also schedule a specific time that works best for you.', 'qEs' => 'Que tan rapido pueden llegar?', 'aEs' => 'Para solicitudes del mismo dia, tipicamente llegamos en 1-2 horas dependiendo de la disponibilidad y distancia. Tambien puede programar un horario especifico que le convenga.'],
];
$relatedServices = [
    ['name' => 'Roadside Assistance', 'nameEs' => 'Asistencia en Carretera', 'slug' => 'roadside-assistance'],
    ['name' => 'Tire Repair', 'nameEs' => 'Reparacion de Llantas', 'slug' => 'tire-repair'],
    ['name' => 'Tire Installation', 'nameEs' => 'Instalacion de Llantas', 'slug' => 'tire-installation'],
];

// Service Coverage section (injected before CTA)
$customSectionsBeforeCTA = '
  <!-- ═══ Service Coverage ═══ -->
  <section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
        <span data-t="coverage_title">Service Coverage Area</span>
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-6">
          <h3 class="font-semibold text-green-800 dark:text-green-400 mb-3">
            <span data-t="coverage_range">Coverage Range</span>
          </h3>
          <ul class="space-y-2 text-gray-700 dark:text-gray-300 text-sm">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span data-t="coverage_15mi">Service within 15 miles of our shop</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span data-t="coverage_trip_fee">A trip fee may apply based on distance</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span data-t="coverage_confirm">We confirm availability and total cost before dispatching</span>
            </li>
          </ul>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-3">
            <span data-t="coverage_areas">Areas We Serve</span>
          </h3>
          <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>SE Portland</span><span>Clackamas</span>
            <span>Happy Valley</span><span>Milwaukie</span>
            <span>Lents</span><span>Woodstock</span>
            <span>Foster-Powell</span><span>Mt. Scott</span>
          </div>
        </div>
      </div>
    </div>
  </section>';

// Extra translation keys
$customTranslations = "
      coverage_title: 'Área de Cobertura de Servicio',
      coverage_range: 'Rango de Cobertura',
      coverage_15mi: 'Servicio dentro de 15 millas de nuestro taller',
      coverage_trip_fee: 'Se puede aplicar una tarifa de viaje según la distancia',
      coverage_confirm: 'Confirmamos disponibilidad y costo total antes de enviar',
      coverage_areas: 'Áreas que Servimos',";

require __DIR__ . '/templates/service-detail.php';
