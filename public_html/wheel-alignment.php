<?php
$serviceName = 'Wheel Alignment';
$serviceNameEs = 'Alineacion de Ruedas';
$serviceSlug = 'wheel-alignment';
$serviceIcon = '&#x2699;';
$serviceDescription = 'Computerized wheel alignment for all vehicles in Portland, OR. Corrects camber, caster, and toe for even tire wear and straight driving. Bilingual service.';
$serviceDescriptionEs = 'Alineacion de ruedas computarizada para todos los vehiculos en Portland, OR. Corrige camber, caster y convergencia para desgaste uniforme y conduccion recta. Servicio bilingue.';
$serviceBody = '<p>Oregon Tires Auto Care offers precision 4-wheel alignment using state-of-the-art computerized equipment. We correct camber, caster, and toe angles to manufacturer specifications, ensuring even tire wear and straight, predictable handling.</p><p>Proper alignment extends the life of your tires, improves fuel efficiency, and makes your vehicle safer to drive. We recommend an alignment check every 12 months, after hitting a pothole or curb, or whenever you install new tires.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece alineacion de precision de 4 ruedas usando equipo computarizado de ultima generacion. Corregimos los angulos de camber, caster y convergencia a las especificaciones del fabricante, asegurando un desgaste uniforme de las llantas y un manejo recto y predecible.</p><p>Una alineacion adecuada extiende la vida de sus llantas, mejora la eficiencia de combustible y hace que su vehiculo sea mas seguro de conducir. Recomendamos una revision de alineacion cada 12 meses, despues de golpear un bache o banqueta, o cuando instale llantas nuevas.</p>';
$faqItems = [
    ['q' => 'What are signs I need a wheel alignment?', 'a' => 'Common signs include your vehicle pulling to one side, uneven tire wear, a crooked steering wheel when driving straight, or vibration in the steering wheel.', 'qEs' => 'Cuales son las senales de que necesito una alineacion?', 'aEs' => 'Las senales comunes incluyen que su vehiculo se desvie hacia un lado, desgaste desigual de las llantas, un volante torcido al conducir recto, o vibracion en el volante.'],
    ['q' => 'How often should I get an alignment?', 'a' => 'We recommend a wheel alignment every 12 months or 12,000 miles, whichever comes first. Also get one after installing new tires or hitting a significant pothole.', 'qEs' => 'Con que frecuencia debo hacer una alineacion?', 'aEs' => 'Recomendamos una alineacion cada 12 meses o 12,000 millas, lo que ocurra primero. Tambien haga una despues de instalar llantas nuevas o golpear un bache significativo.'],
    ['q' => 'How long does a wheel alignment take?', 'a' => 'A standard 4-wheel alignment takes about 45-60 minutes depending on your vehicle and the adjustments needed.', 'qEs' => 'Cuanto tiempo toma una alineacion?', 'aEs' => 'Una alineacion estandar de 4 ruedas toma aproximadamente 45-60 minutos dependiendo de su vehiculo y los ajustes necesarios.'],
];
$relatedServices = [
    ['name' => 'Tire Installation', 'slug' => 'tire-installation'],
    ['name' => 'Suspension Repair', 'slug' => 'suspension-repair'],
    ['name' => 'Tire Repair', 'slug' => 'tire-repair'],
];
require __DIR__ . '/templates/service-detail.php';
