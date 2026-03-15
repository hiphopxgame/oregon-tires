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
require __DIR__ . '/templates/service-detail.php';
