<?php
$serviceName = 'Tire Repair';
$serviceNameEs = 'Reparacion de Llantas';
$serviceSlug = 'tire-repair';
$serviceIcon = '&#x1F6DE;';
$serviceDescription = 'Fast tire repair and patching in Portland, OR. Flat tire fix, puncture repair, and tire inspection. Same-day service starting at $15. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Reparacion rapida de llantas y parches en Portland, OR. Reparacion de llantas ponchadas, reparacion de pinchazos e inspeccion de llantas. Servicio el mismo dia desde $15. Servicio bilingue.';
$startingPrice = '$15+';
$serviceBody = '<p>Oregon Tires Auto Care offers professional tire repair for punctures and slow leaks. We assess every tire for repairability per industry standards, then perform a plug/patch combination from the inside of the tire for a lasting, safe repair.</p><p>If a tire is unrepairable due to sidewall damage, large punctures, or excessive wear, we will honestly let you know and recommend affordable replacement options from our selection of new and quality used tires. Most tire repairs are completed in under 30 minutes.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece reparacion profesional de llantas para pinchazos y fugas lentas. Evaluamos cada llanta para determinar si es reparable segun los estandares de la industria, luego realizamos una combinacion de tapon/parche desde el interior de la llanta para una reparacion duradera y segura.</p><p>Si una llanta no es reparable debido a dano en la pared lateral, pinchazos grandes o desgaste excesivo, se lo informaremos honestamente y recomendaremos opciones de reemplazo accesibles de nuestra seleccion de llantas nuevas y usadas de calidad. La mayoria de las reparaciones de llantas se completan en menos de 30 minutos.</p>';
$faqItems = [
    ['q' => 'Can all flat tires be repaired?', 'a' => 'Not all flats can be repaired. Sidewall damage, punctures larger than 1/4 inch, or damage near the tire bead cannot be safely repaired. We assess every tire honestly and only repair when it is safe to do so.', 'qEs' => 'Se pueden reparar todas las llantas ponchadas?', 'aEs' => 'No todas las llantas ponchadas se pueden reparar. Danos en la pared lateral, pinchazos mayores de 1/4 de pulgada o danos cerca del talon de la llanta no se pueden reparar de manera segura. Evaluamos cada llanta honestamente y solo reparamos cuando es seguro hacerlo.'],
    ['q' => 'How long does a tire repair take?', 'a' => 'Most tire repairs take 15-30 minutes. We remove the tire from the wheel, inspect it from the inside, apply a combination plug/patch, and remount and balance the tire.', 'qEs' => 'Cuanto tiempo toma reparar una llanta?', 'aEs' => 'La mayoria de las reparaciones de llantas toman 15-30 minutos. Removemos la llanta de la rueda, la inspeccionamos desde el interior, aplicamos un tapon/parche combinado, y remontamos y balanceamos la llanta.'],
    ['q' => 'What is the difference between a patch and a plug?', 'a' => 'A plug fills the puncture hole from the outside, while a patch seals it from the inside. We use a combination plug/patch for the safest and most durable repair — this is the industry-standard method.', 'qEs' => 'Cual es la diferencia entre un parche y un tapon?', 'aEs' => 'Un tapon llena el agujero del pinchazo desde el exterior, mientras que un parche lo sella desde el interior. Usamos una combinacion de tapon/parche para la reparacion mas segura y duradera — este es el metodo estandar de la industria.'],
];
$relatedServices = [
    ['name' => 'Tire Installation', 'slug' => 'tire-installation', 'price' => '$20+'],
    ['name' => 'Wheel Alignment', 'slug' => 'wheel-alignment', 'price' => '$75+'],
    ['name' => 'Brake Service', 'slug' => 'brake-service', 'price' => '$100+'],
];
require __DIR__ . '/templates/service-detail.php';
