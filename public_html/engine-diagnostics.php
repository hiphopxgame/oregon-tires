<?php
$serviceName = 'Engine Diagnostics';
$serviceNameEs = 'Diagnostico de Motor';
$serviceSlug = 'engine-diagnostics';
$serviceIcon = '&#x1F50D;';
$serviceDescription = 'Advanced engine diagnostic services in Portland, OR. Check engine light diagnosis, OBD-II scanning, and performance testing. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Servicios avanzados de diagnostico de motor en Portland, OR. Diagnostico de luz de motor, escaneo OBD-II y pruebas de rendimiento. Servicio bilingue.';
$serviceBody = '<p>Oregon Tires Auto Care provides full computer diagnostic services using professional OBD-II scanning equipment. We identify the causes behind check engine lights, engine misfires, sensor failures, emissions issues, and overall performance problems.</p><p>After the diagnostic scan, we provide a detailed report with clear explanations of what we found and our repair recommendations with upfront pricing. We never pressure you into repairs — just honest information so you can make the best decision for your vehicle.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece servicios completos de diagnostico por computadora usando equipo profesional de escaneo OBD-II. Identificamos las causas detras de las luces de motor, fallas de encendido, fallas de sensores, problemas de emisiones y problemas generales de rendimiento.</p><p>Despues del escaneo diagnostico, proporcionamos un informe detallado con explicaciones claras de lo que encontramos y nuestras recomendaciones de reparacion con precios transparentes. Nunca lo presionamos para hacer reparaciones — solo informacion honesta para que pueda tomar la mejor decision para su vehiculo.</p>';
$faqItems = [
    ['q' => 'What does the check engine light mean?', 'a' => 'The check engine light can indicate many things, from a loose gas cap to a serious engine issue. A diagnostic scan reads the specific error codes stored in your vehicle\'s computer to pinpoint the exact problem.', 'qEs' => 'Que significa la luz de check engine?', 'aEs' => 'La luz de check engine puede indicar muchas cosas, desde una tapa de gasolina suelta hasta un problema serio del motor. Un escaneo diagnostico lee los codigos de error especificos almacenados en la computadora de su vehiculo para identificar el problema exacto.'],
    ['q' => 'How long does a diagnostic take?', 'a' => 'A standard diagnostic scan takes 30-60 minutes. More complex issues that require additional testing may take longer. We\'ll give you a time estimate upfront.', 'qEs' => 'Cuanto tiempo toma un diagnostico?', 'aEs' => 'Un escaneo diagnostico estandar toma 30-60 minutos. Problemas mas complejos que requieren pruebas adicionales pueden tomar mas tiempo. Le daremos una estimacion de tiempo por adelantado.'],
    ['q' => 'Do you fix what you find?', 'a' => 'Yes! Once we diagnose the issue, we provide a detailed estimate for the repair. With your approval, our technicians can perform the repair right away in most cases.', 'qEs' => 'Reparan lo que encuentran?', 'aEs' => 'Si! Una vez que diagnosticamos el problema, proporcionamos un estimado detallado para la reparacion. Con su aprobacion, nuestros tecnicos pueden realizar la reparacion de inmediato en la mayoria de los casos.'],
];
$relatedServices = [
    ['name' => 'Oil Change', 'nameEs' => 'Cambio de Aceite', 'slug' => 'oil-change'],
    ['name' => 'Brake Service', 'nameEs' => 'Servicio de Frenos', 'slug' => 'brake-service'],
    ['name' => 'Suspension Repair', 'nameEs' => 'Reparacion de Suspension', 'slug' => 'suspension-repair'],
];
require __DIR__ . '/templates/service-detail.php';
