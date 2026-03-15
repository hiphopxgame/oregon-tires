<?php
$serviceName = 'Suspension Repair';
$serviceNameEs = 'Reparacion de Suspension';
$serviceSlug = 'suspension-repair';
$serviceIcon = '&#x1F699;';
$serviceDescription = 'Professional suspension repair in Portland, OR. Shocks, struts, control arms, ball joints, and steering components. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Reparacion profesional de suspension en Portland, OR. Amortiguadores, puntales, brazos de control, rotulas y componentes de direccion. Servicio bilingue.';
$serviceBody = '<p>Oregon Tires Auto Care provides complete suspension diagnosis and repair for all vehicle types. Our services cover shocks, struts, control arms, ball joints, tie rod ends, sway bar links, and steering rack service to restore your vehicle\'s smooth ride and handling.</p><p>A worn suspension affects your vehicle\'s ride comfort, handling, braking distance, and tire wear. Our technicians thoroughly inspect your suspension system, explain what we find in plain language, and recommend only the repairs you actually need.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece diagnostico y reparacion completa de suspension para todo tipo de vehiculo. Nuestros servicios cubren amortiguadores, puntales, brazos de control, rotulas, terminales de barra, enlaces de barra estabilizadora y servicio de cremallera de direccion para restaurar la conduccion suave y el manejo de su vehiculo.</p><p>Una suspension desgastada afecta la comodidad de conduccion, el manejo, la distancia de frenado y el desgaste de las llantas. Nuestros tecnicos inspeccionan minuciosamente su sistema de suspension, explican lo que encuentran en lenguaje sencillo y recomiendan solo las reparaciones que realmente necesita.</p>';
$faqItems = [
    ['q' => 'What are signs of suspension problems?', 'a' => 'Common signs include excessive bouncing over bumps, the front end nosediving when braking, uneven tire wear, clunking or knocking noises, and the vehicle drifting or pulling during turns.', 'qEs' => 'Cuales son las senales de problemas de suspension?', 'aEs' => 'Las senales comunes incluyen rebote excesivo en los baches, la parte delantera se hunde al frenar, desgaste desigual de las llantas, ruidos de golpeteo y el vehiculo se desvía o jala durante las curvas.'],
    ['q' => 'How long does suspension repair take?', 'a' => 'Suspension repair typically takes 2-4 hours depending on the components being replaced. More extensive work involving multiple components may take a full day.', 'qEs' => 'Cuanto tiempo toma la reparacion de suspension?', 'aEs' => 'La reparacion de suspension tipicamente toma 2-4 horas dependiendo de los componentes a reemplazar. Trabajos mas extensos que involucran multiples componentes pueden tomar un dia completo.'],
    ['q' => 'What is the difference between shocks and struts?', 'a' => 'Shocks and struts both dampen road vibrations, but they have different designs. Struts are a structural part of the suspension and include a coil spring, while shocks are standalone components. Your vehicle uses one or the other — not both on the same wheel.', 'qEs' => 'Cual es la diferencia entre amortiguadores y puntales?', 'aEs' => 'Los amortiguadores y puntales ambos amortiguan las vibraciones del camino, pero tienen disenos diferentes. Los puntales son una parte estructural de la suspension e incluyen un resorte helicoidal, mientras que los amortiguadores son componentes independientes. Su vehiculo usa uno u otro — no ambos en la misma rueda.'],
];
$relatedServices = [
    ['name' => 'Wheel Alignment', 'nameEs' => 'Alineacion de Ruedas', 'slug' => 'wheel-alignment'],
    ['name' => 'Brake Service', 'nameEs' => 'Servicio de Frenos', 'slug' => 'brake-service'],
    ['name' => 'Engine Diagnostics', 'nameEs' => 'Diagnostico de Motor', 'slug' => 'engine-diagnostics'],
];
require __DIR__ . '/templates/service-detail.php';
