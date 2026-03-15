<?php
$serviceName = 'Tire Installation';
$serviceNameEs = 'Instalacion de Llantas';
$serviceSlug = 'tire-installation';
$serviceIcon = '&#x1F527;';
$serviceDescription = 'Professional tire installation in Portland, OR. Mounting, balancing, and TPMS reset included. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Instalacion profesional de llantas en Portland, OR. Montaje, balanceo y reinicio de TPMS incluidos. Servicio bilingue.';
$serviceBody = '<p>Oregon Tires Auto Care offers professional tire installation for all vehicle types. Every installation includes mounting, computer-aided balancing, TPMS sensor reset, and a torque-to-spec finish. We work with tires you purchase from us or bring in yourself.</p><p>We carry a wide selection of new and quality used tires from trusted brands. Whether you need all-season, winter, or performance tires, our technicians will help you find the right fit for your vehicle and driving needs.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece instalacion profesional de llantas para todo tipo de vehiculo. Cada instalacion incluye montaje, balanceo por computadora, reinicio del sensor TPMS y torque a especificacion. Trabajamos con llantas compradas con nosotros o que usted traiga.</p><p>Tenemos una amplia seleccion de llantas nuevas y usadas de calidad. Ya sea que necesite llantas para toda temporada, invierno o alto rendimiento, nuestros tecnicos le ayudaran a encontrar la opcion correcta.</p>';
$faqItems = [
    ['q' => 'How long does tire installation take?', 'a' => 'Most tire installations take 30-45 minutes for a full set of four tires.', 'qEs' => 'Cuanto tiempo toma la instalacion de llantas?', 'aEs' => 'La mayoria de las instalaciones toman 30-45 minutos para un juego completo de cuatro llantas.'],
    ['q' => 'Can I bring my own tires?', 'a' => 'Yes! We install tires purchased elsewhere. Just bring them in and we\'ll mount, balance, and install them.', 'qEs' => 'Puedo traer mis propias llantas?', 'aEs' => 'Si! Instalamos llantas compradas en otro lugar. Solo traigalas y las montaremos, balancearemos e instalaremos.'],
    ['q' => 'Do you offer tire disposal?', 'a' => 'Yes, we properly recycle old tires for a small environmental fee.', 'qEs' => 'Ofrecen desecho de llantas?', 'aEs' => 'Si, reciclamos adecuadamente las llantas viejas por una pequena tarifa ambiental.'],
];
$relatedServices = [
    ['name' => 'Tire Repair', 'nameEs' => 'Reparacion de Llantas', 'slug' => 'tire-repair'],
    ['name' => 'Wheel Alignment', 'nameEs' => 'Alineacion de Ruedas', 'slug' => 'wheel-alignment'],
    ['name' => 'Brake Service', 'nameEs' => 'Servicio de Frenos', 'slug' => 'brake-service'],
];
require __DIR__ . '/templates/service-detail.php';
