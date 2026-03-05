<?php
$serviceName = 'Brake Service';
$serviceNameEs = 'Servicio de Frenos';
$serviceSlug = 'brake-service';
$serviceIcon = '&#x1F6D1;';
$serviceDescription = 'Complete brake service in Portland, OR. Pad replacement, rotor resurfacing, and brake fluid flush. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Servicio completo de frenos en Portland, OR. Reemplazo de pastillas, rectificacion de rotores y cambio de liquido de frenos. Servicio bilingue.';
$serviceBody = '<p>Oregon Tires Auto Care provides comprehensive brake inspection and repair services for all vehicle types. Our services include brake pad and shoe replacement, rotor resurfacing or replacement, caliper service, brake fluid flush, and ABS diagnostics.</p><p>Your brakes are your vehicle\'s most critical safety system. Our experienced technicians use quality parts and follow manufacturer specifications to ensure your brakes perform reliably. We provide a detailed inspection report and honest recommendations with every service.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece servicios completos de inspeccion y reparacion de frenos para todo tipo de vehiculo. Nuestros servicios incluyen reemplazo de pastillas y zapatas, rectificacion o reemplazo de rotores, servicio de calibradores, cambio de liquido de frenos y diagnostico de ABS.</p><p>Sus frenos son el sistema de seguridad mas critico de su vehiculo. Nuestros tecnicos experimentados usan piezas de calidad y siguen las especificaciones del fabricante para asegurar que sus frenos funcionen de manera confiable. Proporcionamos un informe detallado de inspeccion y recomendaciones honestas con cada servicio.</p>';
$faqItems = [
    ['q' => 'What are the signs my brakes need service?', 'a' => 'Common signs include squealing or grinding noises, a pulsating brake pedal, the vehicle pulling to one side when braking, or a soft/spongy brake pedal feel.', 'qEs' => 'Cuales son las senales de que mis frenos necesitan servicio?', 'aEs' => 'Las senales comunes incluyen ruidos de chirrido o rechinido, un pedal de freno pulsante, el vehiculo se desvía hacia un lado al frenar, o un pedal de freno suave/esponjoso.'],
    ['q' => 'How long do brake pads last?', 'a' => 'Brake pads typically last between 25,000-50,000 miles depending on driving habits, vehicle type, and pad quality. City driving wears pads faster than highway driving.', 'qEs' => 'Cuanto duran las pastillas de freno?', 'aEs' => 'Las pastillas de freno tipicamente duran entre 25,000-50,000 millas dependiendo de los habitos de manejo, tipo de vehiculo y calidad de las pastillas. El manejo en ciudad desgasta las pastillas mas rapido que en carretera.'],
    ['q' => 'How long does brake service take?', 'a' => 'A standard brake pad replacement takes 1-2 hours. More complex repairs involving rotors or calipers may take 2-3 hours.', 'qEs' => 'Cuanto tiempo toma el servicio de frenos?', 'aEs' => 'Un reemplazo estandar de pastillas de freno toma 1-2 horas. Reparaciones mas complejas que involucran rotores o calibradores pueden tomar 2-3 horas.'],
];
$relatedServices = [
    ['name' => 'Wheel Alignment', 'slug' => 'wheel-alignment'],
    ['name' => 'Engine Diagnostics', 'slug' => 'engine-diagnostics'],
    ['name' => 'Suspension Repair', 'slug' => 'suspension-repair'],
];
require __DIR__ . '/templates/service-detail.php';
