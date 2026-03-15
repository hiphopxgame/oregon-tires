<?php
$serviceName = 'Oil Change';
$serviceNameEs = 'Cambio de Aceite';
$serviceSlug = 'oil-change';
$serviceIcon = '&#x1F6E2;';
$serviceDescription = 'Conventional and synthetic oil changes in Portland, OR. Includes filter replacement and multi-point inspection. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Cambios de aceite convencional y sintetico en Portland, OR. Incluye reemplazo de filtro e inspeccion multipunto. Servicio bilingue.';
$serviceBody = '<p>Oregon Tires Auto Care provides full oil change services including conventional, synthetic blend, and full synthetic options. Every oil change includes a new oil filter, fluid top-offs, and a complimentary multi-point vehicle inspection to catch potential issues early.</p><p>Regular oil changes are the single most important maintenance you can do for your engine. Our technicians use quality oils and filters to keep your engine running smoothly and extend its lifespan. We service all makes and models.</p>';
$serviceBodyEs = '<p>Oregon Tires Auto Care ofrece servicios completos de cambio de aceite incluyendo convencional, mezcla sintetica y sintetico completo. Cada cambio de aceite incluye un filtro nuevo, relleno de fluidos y una inspeccion multipunto cortesia para detectar problemas potenciales temprano.</p><p>Los cambios de aceite regulares son el mantenimiento mas importante que puede hacer para su motor. Nuestros tecnicos usan aceites y filtros de calidad para mantener su motor funcionando sin problemas y extender su vida util. Atendemos todas las marcas y modelos.</p>';
$faqItems = [
    ['q' => 'How often should I change my oil?', 'a' => 'Most vehicles need an oil change every 3,000-5,000 miles for conventional oil, or every 7,500-10,000 miles for full synthetic.', 'qEs' => 'Con que frecuencia debo cambiar el aceite?', 'aEs' => 'La mayoria de los vehiculos necesitan un cambio de aceite cada 3,000-5,000 millas para aceite convencional, o cada 7,500-10,000 millas para sintetico completo.'],
    ['q' => 'What is the difference between synthetic and conventional oil?', 'a' => 'Synthetic oil is engineered for better performance, lasts longer between changes, and provides superior protection in extreme temperatures. Conventional oil is more affordable but requires more frequent changes.', 'qEs' => 'Cual es la diferencia entre aceite sintetico y convencional?', 'aEs' => 'El aceite sintetico esta disenado para mejor rendimiento, dura mas entre cambios y proporciona proteccion superior en temperaturas extremas. El aceite convencional es mas economico pero requiere cambios mas frecuentes.'],
    ['q' => 'How long does an oil change take?', 'a' => 'A standard oil change takes about 20-30 minutes. No appointment needed for most oil changes.', 'qEs' => 'Cuanto tiempo toma un cambio de aceite?', 'aEs' => 'Un cambio de aceite estandar toma aproximadamente 20-30 minutos. No se necesita cita para la mayoria de los cambios de aceite.'],
];
$relatedServices = [
    ['name' => 'Engine Diagnostics', 'nameEs' => 'Diagnostico de Motor', 'slug' => 'engine-diagnostics'],
    ['name' => 'Brake Service', 'nameEs' => 'Servicio de Frenos', 'slug' => 'brake-service'],
    ['name' => 'Tire Installation', 'nameEs' => 'Instalacion de Llantas', 'slug' => 'tire-installation'],
];
require __DIR__ . '/templates/service-detail.php';
