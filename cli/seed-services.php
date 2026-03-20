<?php
/**
 * Oregon Tires — Seed services from existing hardcoded data
 * Run: php cli/seed-services.php
 *
 * Idempotent: skips services that already exist (by slug).
 */

declare(strict_types=1);

// Server: cli/ is sibling to includes/; Local: cli/ is sibling to public_html/includes/
$bootstrapPath = __DIR__ . '/../includes/bootstrap.php';
if (!file_exists($bootstrapPath)) {
    $bootstrapPath = __DIR__ . '/../public_html/includes/bootstrap.php';
}
require_once $bootstrapPath;

$db = getDB();

echo "Seeding oretir_services...\n";

$services = [
    [
        'slug' => 'tire-installation',
        'name_en' => 'Tire Installation',
        'name_es' => 'Instalacion de Llantas',
        'description_en' => 'Professional tire installation in Portland, OR. Mounting, balancing, and TPMS reset included. Bilingual English & Spanish service.',
        'description_es' => 'Instalacion profesional de llantas en Portland, OR. Montaje, balanceo y reinicio de TPMS incluidos. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care offers professional tire installation for all vehicle types. Every installation includes mounting, computer-aided balancing, TPMS sensor reset, and a torque-to-spec finish. We work with tires you purchase from us or bring in yourself.</p><p>We carry a wide selection of new and quality used tires from trusted brands. Whether you need all-season, winter, or performance tires, our technicians will help you find the right fit for your vehicle and driving needs.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece instalacion profesional de llantas para todo tipo de vehiculo. Cada instalacion incluye montaje, balanceo por computadora, reinicio del sensor TPMS y torque a especificacion. Trabajamos con llantas compradas con nosotros o que usted traiga.</p><p>Tenemos una amplia seleccion de llantas nuevas y usadas de calidad. Ya sea que necesite llantas para toda temporada, invierno o alto rendimiento, nuestros tecnicos le ayudaran a encontrar la opcion correcta.</p>',
        'icon' => '&#x1F527;',
        'color_hex' => '#3B82F6',
        'color_bg' => 'bg-blue-100',
        'color_text' => 'text-blue-800',
        'color_dark_bg' => 'dark:bg-blue-900/30',
        'color_dark_text' => 'dark:text-blue-300',
        'color_dot' => 'bg-blue-500',
        'category' => 'tires',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 1,
        'duration_estimate' => '45-90',
        'faqs' => [
            ['q' => 'How long does tire installation take?', 'a' => 'Most tire installations take 30-45 minutes for a full set of four tires.', 'qEs' => 'Cuanto tiempo toma la instalacion de llantas?', 'aEs' => 'La mayoria de las instalaciones toman 30-45 minutos para un juego completo de cuatro llantas.'],
            ['q' => 'Can I bring my own tires?', 'a' => 'Yes! We install tires purchased elsewhere. Just bring them in and we\'ll mount, balance, and install them.', 'qEs' => 'Puedo traer mis propias llantas?', 'aEs' => 'Si! Instalamos llantas compradas en otro lugar. Solo traigalas y las montaremos, balancearemos e instalaremos.'],
            ['q' => 'Do you offer tire disposal?', 'a' => 'Yes, we properly recycle old tires for a small environmental fee.', 'qEs' => 'Ofrecen desecho de llantas?', 'aEs' => 'Si, reciclamos adecuadamente las llantas viejas por una pequena tarifa ambiental.'],
        ],
        'related' => ['tire-repair', 'wheel-alignment', 'brake-service'],
    ],
    [
        'slug' => 'tire-repair',
        'name_en' => 'Tire Repair',
        'name_es' => 'Reparacion de Llantas',
        'description_en' => 'Fast tire repair and patching in Portland, OR. Flat tire fix, puncture repair, and tire inspection. Same-day service available. Bilingual English & Spanish service.',
        'description_es' => 'Reparacion rapida de llantas y parches en Portland, OR. Reparacion de llantas ponchadas, reparacion de pinchazos e inspeccion de llantas. Servicio el mismo dia disponible. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care offers professional tire repair for punctures and slow leaks. We assess every tire for repairability per industry standards, then perform a plug/patch combination from the inside of the tire for a lasting, safe repair.</p><p>If a tire is unrepairable due to sidewall damage, large punctures, or excessive wear, we will honestly let you know and recommend affordable replacement options from our selection of new and quality used tires. Most tire repairs are completed in under 30 minutes.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece reparacion profesional de llantas para pinchazos y fugas lentas. Evaluamos cada llanta para determinar si es reparable segun los estandares de la industria, luego realizamos una combinacion de tapon/parche desde el interior de la llanta para una reparacion duradera y segura.</p><p>Si una llanta no es reparable debido a dano en la pared lateral, pinchazos grandes o desgaste excesivo, se lo informaremos honestamente y recomendaremos opciones de reemplazo accesibles de nuestra seleccion de llantas nuevas y usadas de calidad. La mayoria de las reparaciones de llantas se completan en menos de 30 minutos.</p>',
        'icon' => '&#x1F6DE;',
        'color_hex' => '#60A5FA',
        'color_bg' => 'bg-blue-100',
        'color_text' => 'text-blue-700',
        'color_dark_bg' => 'dark:bg-blue-900/30',
        'color_dark_text' => 'dark:text-blue-300',
        'color_dot' => 'bg-blue-400',
        'category' => 'tires',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 2,
        'duration_estimate' => '20-40',
        'faqs' => [
            ['q' => 'Can all flat tires be repaired?', 'a' => 'Not all flats can be repaired. Sidewall damage, punctures larger than 1/4 inch, or damage near the tire bead cannot be safely repaired. We assess every tire honestly and only repair when it is safe to do so.', 'qEs' => 'Se pueden reparar todas las llantas ponchadas?', 'aEs' => 'No todas las llantas ponchadas se pueden reparar. Danos en la pared lateral, pinchazos mayores de 1/4 de pulgada o danos cerca del talon de la llanta no se pueden reparar de manera segura. Evaluamos cada llanta honestamente y solo reparamos cuando es seguro hacerlo.'],
            ['q' => 'How long does a tire repair take?', 'a' => 'Most tire repairs take 15-30 minutes. We remove the tire from the wheel, inspect it from the inside, apply a combination plug/patch, and remount and balance the tire.', 'qEs' => 'Cuanto tiempo toma reparar una llanta?', 'aEs' => 'La mayoria de las reparaciones de llantas toman 15-30 minutos. Removemos la llanta de la rueda, la inspeccionamos desde el interior, aplicamos un tapon/parche combinado, y remontamos y balanceamos la llanta.'],
            ['q' => 'What is the difference between a patch and a plug?', 'a' => 'A plug fills the puncture hole from the outside, while a patch seals it from the inside. We use a combination plug/patch for the safest and most durable repair — this is the industry-standard method.', 'qEs' => 'Cual es la diferencia entre un parche y un tapon?', 'aEs' => 'Un tapon llena el agujero del pinchazo desde el exterior, mientras que un parche lo sella desde el interior. Usamos una combinacion de tapon/parche para la reparacion mas segura y duradera — este es el metodo estandar de la industria.'],
        ],
        'related' => ['tire-installation', 'wheel-alignment', 'brake-service'],
    ],
    [
        'slug' => 'wheel-alignment',
        'name_en' => 'Wheel Alignment',
        'name_es' => 'Alineacion de Ruedas',
        'description_en' => 'Computerized wheel alignment for all vehicles in Portland, OR. Corrects camber, caster, and toe for even tire wear and straight driving. Bilingual service.',
        'description_es' => 'Alineacion de ruedas computarizada para todos los vehiculos en Portland, OR. Corrige camber, caster y convergencia para desgaste uniforme y conduccion recta. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care offers precision 4-wheel alignment using state-of-the-art computerized equipment. We correct camber, caster, and toe angles to manufacturer specifications, ensuring even tire wear and straight, predictable handling.</p><p>Proper alignment extends the life of your tires, improves fuel efficiency, and makes your vehicle safer to drive. We recommend an alignment check every 12 months, after hitting a pothole or curb, or whenever you install new tires.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece alineacion de precision de 4 ruedas usando equipo computarizado de ultima generacion. Corregimos los angulos de camber, caster y convergencia a las especificaciones del fabricante, asegurando un desgaste uniforme de las llantas y un manejo recto y predecible.</p><p>Una alineacion adecuada extiende la vida de sus llantas, mejora la eficiencia de combustible y hace que su vehiculo sea mas seguro de conducir. Recomendamos una revision de alineacion cada 12 meses, despues de golpear un bache o banqueta, o cuando instale llantas nuevas.</p>',
        'icon' => '&#x2699;',
        'color_hex' => '#8B5CF6',
        'color_bg' => 'bg-purple-100',
        'color_text' => 'text-purple-800',
        'color_dark_bg' => 'dark:bg-purple-900/30',
        'color_dark_text' => 'dark:text-purple-300',
        'color_dot' => 'bg-purple-500',
        'category' => 'tires',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 3,
        'duration_estimate' => '45-60',
        'faqs' => [
            ['q' => 'What are signs I need a wheel alignment?', 'a' => 'Common signs include your vehicle pulling to one side, uneven tire wear, a crooked steering wheel when driving straight, or vibration in the steering wheel.', 'qEs' => 'Cuales son las senales de que necesito una alineacion?', 'aEs' => 'Las senales comunes incluyen que su vehiculo se desvie hacia un lado, desgaste desigual de las llantas, un volante torcido al conducir recto, o vibracion en el volante.'],
            ['q' => 'How often should I get an alignment?', 'a' => 'We recommend a wheel alignment every 12 months or 12,000 miles, whichever comes first. Also get one after installing new tires or hitting a significant pothole.', 'qEs' => 'Con que frecuencia debo hacer una alineacion?', 'aEs' => 'Recomendamos una alineacion cada 12 meses o 12,000 millas, lo que ocurra primero. Tambien haga una despues de instalar llantas nuevas o golpear un bache significativo.'],
            ['q' => 'How long does a wheel alignment take?', 'a' => 'A standard 4-wheel alignment takes about 45-60 minutes depending on your vehicle and the adjustments needed.', 'qEs' => 'Cuanto tiempo toma una alineacion?', 'aEs' => 'Una alineacion estandar de 4 ruedas toma aproximadamente 45-60 minutos dependiendo de su vehiculo y los ajustes necesarios.'],
        ],
        'related' => ['tire-installation', 'suspension-repair', 'tire-repair'],
    ],
    [
        'slug' => 'brake-service',
        'name_en' => 'Brake Service',
        'name_es' => 'Servicio de Frenos',
        'description_en' => 'Complete brake service in Portland, OR. Pad replacement, rotor resurfacing, and brake fluid flush. Bilingual English & Spanish service.',
        'description_es' => 'Servicio completo de frenos en Portland, OR. Reemplazo de pastillas, rectificacion de rotores y cambio de liquido de frenos. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care provides comprehensive brake inspection and repair services for all vehicle types. Our services include brake pad and shoe replacement, rotor resurfacing or replacement, caliper service, brake fluid flush, and ABS diagnostics.</p><p>Your brakes are your vehicle\'s most critical safety system. Our experienced technicians use quality parts and follow manufacturer specifications to ensure your brakes perform reliably. We provide a detailed inspection report and honest recommendations with every service.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece servicios completos de inspeccion y reparacion de frenos para todo tipo de vehiculo. Nuestros servicios incluyen reemplazo de pastillas y zapatas, rectificacion o reemplazo de rotores, servicio de calibradores, cambio de liquido de frenos y diagnostico de ABS.</p><p>Sus frenos son el sistema de seguridad mas critico de su vehiculo. Nuestros tecnicos experimentados usan piezas de calidad y siguen las especificaciones del fabricante para asegurar que sus frenos funcionen de manera confiable. Proporcionamos un informe detallado de inspeccion y recomendaciones honestas con cada servicio.</p>',
        'icon' => '&#x1F6D1;',
        'color_hex' => '#EF4444',
        'color_bg' => 'bg-red-100',
        'color_text' => 'text-red-800',
        'color_dark_bg' => 'dark:bg-red-900/30',
        'color_dark_text' => 'dark:text-red-300',
        'color_dot' => 'bg-red-500',
        'category' => 'maintenance',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 4,
        'duration_estimate' => '60-120',
        'faqs' => [
            ['q' => 'What are the signs my brakes need service?', 'a' => 'Common signs include squealing or grinding noises, a pulsating brake pedal, the vehicle pulling to one side when braking, or a soft/spongy brake pedal feel.', 'qEs' => 'Cuales son las senales de que mis frenos necesitan servicio?', 'aEs' => 'Las senales comunes incluyen ruidos de chirrido o rechinido, un pedal de freno pulsante, el vehiculo se desvia hacia un lado al frenar, o un pedal de freno suave/esponjoso.'],
            ['q' => 'How long do brake pads last?', 'a' => 'Brake pads typically last between 25,000-50,000 miles depending on driving habits, vehicle type, and pad quality. City driving wears pads faster than highway driving.', 'qEs' => 'Cuanto duran las pastillas de freno?', 'aEs' => 'Las pastillas de freno tipicamente duran entre 25,000-50,000 millas dependiendo de los habitos de manejo, tipo de vehiculo y calidad de las pastillas. El manejo en ciudad desgasta las pastillas mas rapido que en carretera.'],
            ['q' => 'How long does brake service take?', 'a' => 'A standard brake pad replacement takes 1-2 hours. More complex repairs involving rotors or calipers may take 2-3 hours.', 'qEs' => 'Cuanto tiempo toma el servicio de frenos?', 'aEs' => 'Un reemplazo estandar de pastillas de freno toma 1-2 horas. Reparaciones mas complejas que involucran rotores o calibradores pueden tomar 2-3 horas.'],
        ],
        'related' => ['wheel-alignment', 'engine-diagnostics', 'suspension-repair'],
    ],
    [
        'slug' => 'oil-change',
        'name_en' => 'Oil Change',
        'name_es' => 'Cambio de Aceite',
        'description_en' => 'Conventional and synthetic oil changes in Portland, OR. Includes filter replacement and multi-point inspection. Bilingual English & Spanish service.',
        'description_es' => 'Cambios de aceite convencional y sintetico en Portland, OR. Incluye reemplazo de filtro e inspeccion multipunto. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care provides full oil change services including conventional, synthetic blend, and full synthetic options. Every oil change includes a new oil filter, fluid top-offs, and a complimentary multi-point vehicle inspection to catch potential issues early.</p><p>Regular oil changes are the single most important maintenance you can do for your engine. Our technicians use quality oils and filters to keep your engine running smoothly and extend its lifespan. We service all makes and models.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece servicios completos de cambio de aceite incluyendo convencional, mezcla sintetica y sintetico completo. Cada cambio de aceite incluye un filtro nuevo, relleno de fluidos y una inspeccion multipunto cortesia para detectar problemas potenciales temprano.</p><p>Los cambios de aceite regulares son el mantenimiento mas importante que puede hacer para su motor. Nuestros tecnicos usan aceites y filtros de calidad para mantener su motor funcionando sin problemas y extender su vida util. Atendemos todas las marcas y modelos.</p>',
        'icon' => '&#x1F6E2;',
        'color_hex' => '#F97316',
        'color_bg' => 'bg-orange-100',
        'color_text' => 'text-orange-800',
        'color_dark_bg' => 'dark:bg-orange-900/30',
        'color_dark_text' => 'dark:text-orange-300',
        'color_dot' => 'bg-orange-500',
        'category' => 'maintenance',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 5,
        'duration_estimate' => '20-30',
        'faqs' => [
            ['q' => 'How often should I change my oil?', 'a' => 'Most vehicles need an oil change every 3,000-5,000 miles for conventional oil, or every 7,500-10,000 miles for full synthetic.', 'qEs' => 'Con que frecuencia debo cambiar el aceite?', 'aEs' => 'La mayoria de los vehiculos necesitan un cambio de aceite cada 3,000-5,000 millas para aceite convencional, o cada 7,500-10,000 millas para sintetico completo.'],
            ['q' => 'What is the difference between synthetic and conventional oil?', 'a' => 'Synthetic oil is engineered for better performance, lasts longer between changes, and provides superior protection in extreme temperatures. Conventional oil is more affordable but requires more frequent changes.', 'qEs' => 'Cual es la diferencia entre aceite sintetico y convencional?', 'aEs' => 'El aceite sintetico esta disenado para mejor rendimiento, dura mas entre cambios y proporciona proteccion superior en temperaturas extremas. El aceite convencional es mas economico pero requiere cambios mas frecuentes.'],
            ['q' => 'How long does an oil change take?', 'a' => 'A standard oil change takes about 20-30 minutes. No appointment needed for most oil changes.', 'qEs' => 'Cuanto tiempo toma un cambio de aceite?', 'aEs' => 'Un cambio de aceite estandar toma aproximadamente 20-30 minutos. No se necesita cita para la mayoria de los cambios de aceite.'],
        ],
        'related' => ['engine-diagnostics', 'brake-service', 'tire-installation'],
    ],
    [
        'slug' => 'engine-diagnostics',
        'name_en' => 'Engine Diagnostics',
        'name_es' => 'Diagnostico de Motor',
        'description_en' => 'Advanced engine diagnostic services in Portland, OR. Check engine light diagnosis, OBD-II scanning, and performance testing. Bilingual English & Spanish service.',
        'description_es' => 'Servicios avanzados de diagnostico de motor en Portland, OR. Diagnostico de luz de motor, escaneo OBD-II y pruebas de rendimiento. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care provides full computer diagnostic services using professional OBD-II scanning equipment. We identify the causes behind check engine lights, engine misfires, sensor failures, emissions issues, and overall performance problems.</p><p>After the diagnostic scan, we provide a detailed report with clear explanations of what we found and our repair recommendations with upfront pricing. We never pressure you into repairs — just honest information so you can make the best decision for your vehicle.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece servicios completos de diagnostico por computadora usando equipo profesional de escaneo OBD-II. Identificamos las causas detras de las luces de motor, fallas de encendido, fallas de sensores, problemas de emisiones y problemas generales de rendimiento.</p><p>Despues del escaneo diagnostico, proporcionamos un informe detallado con explicaciones claras de lo que encontramos y nuestras recomendaciones de reparacion con precios transparentes. Nunca lo presionamos para hacer reparaciones — solo informacion honesta para que pueda tomar la mejor decision para su vehiculo.</p>',
        'icon' => '&#x1F50D;',
        'color_hex' => '#06B6D4',
        'color_bg' => 'bg-cyan-100',
        'color_text' => 'text-cyan-800',
        'color_dark_bg' => 'dark:bg-cyan-900/30',
        'color_dark_text' => 'dark:text-cyan-300',
        'color_dot' => 'bg-cyan-500',
        'category' => 'specialized',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 6,
        'duration_estimate' => '30-60',
        'faqs' => [
            ['q' => 'What does the check engine light mean?', 'a' => 'The check engine light can indicate many things, from a loose gas cap to a serious engine issue. A diagnostic scan reads the specific error codes stored in your vehicle\'s computer to pinpoint the exact problem.', 'qEs' => 'Que significa la luz de check engine?', 'aEs' => 'La luz de check engine puede indicar muchas cosas, desde una tapa de gasolina suelta hasta un problema serio del motor. Un escaneo diagnostico lee los codigos de error especificos almacenados en la computadora de su vehiculo para identificar el problema exacto.'],
            ['q' => 'How long does a diagnostic take?', 'a' => 'A standard diagnostic scan takes 30-60 minutes. More complex issues that require additional testing may take longer. We\'ll give you a time estimate upfront.', 'qEs' => 'Cuanto tiempo toma un diagnostico?', 'aEs' => 'Un escaneo diagnostico estandar toma 30-60 minutos. Problemas mas complejos que requieren pruebas adicionales pueden tomar mas tiempo. Le daremos una estimacion de tiempo por adelantado.'],
            ['q' => 'Do you fix what you find?', 'a' => 'Yes! Once we diagnose the issue, we provide a detailed estimate for the repair. With your approval, our technicians can perform the repair right away in most cases.', 'qEs' => 'Reparan lo que encuentran?', 'aEs' => 'Si! Una vez que diagnosticamos el problema, proporcionamos un estimado detallado para la reparacion. Con su aprobacion, nuestros tecnicos pueden realizar la reparacion de inmediato en la mayoria de los casos.'],
        ],
        'related' => ['oil-change', 'brake-service', 'suspension-repair'],
    ],
    [
        'slug' => 'suspension-repair',
        'name_en' => 'Suspension Repair',
        'name_es' => 'Reparacion de Suspension',
        'description_en' => 'Professional suspension repair in Portland, OR. Shocks, struts, control arms, ball joints, and steering components. Bilingual English & Spanish service.',
        'description_es' => 'Reparacion profesional de suspension en Portland, OR. Amortiguadores, puntales, brazos de control, rotulas y componentes de direccion. Servicio bilingue.',
        'body_en' => '<p>Oregon Tires Auto Care provides complete suspension diagnosis and repair for all vehicle types. Our services cover shocks, struts, control arms, ball joints, tie rod ends, sway bar links, and steering rack service to restore your vehicle\'s smooth ride and handling.</p><p>A worn suspension affects your vehicle\'s ride comfort, handling, braking distance, and tire wear. Our technicians thoroughly inspect your suspension system, explain what we find in plain language, and recommend only the repairs you actually need.</p>',
        'body_es' => '<p>Oregon Tires Auto Care ofrece diagnostico y reparacion completa de suspension para todo tipo de vehiculo. Nuestros servicios cubren amortiguadores, puntales, brazos de control, rotulas, terminales de barra, enlaces de barra estabilizadora y servicio de cremallera de direccion para restaurar la conduccion suave y el manejo de su vehiculo.</p><p>Una suspension desgastada afecta la comodidad de conduccion, el manejo, la distancia de frenado y el desgaste de las llantas. Nuestros tecnicos inspeccionan minuciosamente su sistema de suspension, explican lo que encuentran en lenguaje sencillo y recomiendan solo las reparaciones que realmente necesita.</p>',
        'icon' => '&#x1F699;',
        'color_hex' => '#8B5CF6',
        'color_bg' => 'bg-purple-100',
        'color_text' => 'text-purple-800',
        'color_dark_bg' => 'dark:bg-purple-900/30',
        'color_dark_text' => 'dark:text-purple-300',
        'color_dot' => 'bg-purple-500',
        'category' => 'specialized',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 7,
        'duration_estimate' => '120-240',
        'faqs' => [
            ['q' => 'What are signs of suspension problems?', 'a' => 'Common signs include excessive bouncing over bumps, the front end nosediving when braking, uneven tire wear, clunking or knocking noises, and the vehicle drifting or pulling during turns.', 'qEs' => 'Cuales son las senales de problemas de suspension?', 'aEs' => 'Las senales comunes incluyen rebote excesivo en los baches, la parte delantera se hunde al frenar, desgaste desigual de las llantas, ruidos de golpeteo y el vehiculo se desvia o jala durante las curvas.'],
            ['q' => 'How long does suspension repair take?', 'a' => 'Suspension repair typically takes 2-4 hours depending on the components being replaced. More extensive work involving multiple components may take a full day.', 'qEs' => 'Cuanto tiempo toma la reparacion de suspension?', 'aEs' => 'La reparacion de suspension tipicamente toma 2-4 horas dependiendo de los componentes a reemplazar. Trabajos mas extensos que involucran multiples componentes pueden tomar un dia completo.'],
            ['q' => 'What is the difference between shocks and struts?', 'a' => 'Shocks and struts both dampen road vibrations, but they have different designs. Struts are a structural part of the suspension and include a coil spring, while shocks are standalone components. Your vehicle uses one or the other — not both on the same wheel.', 'qEs' => 'Cual es la diferencia entre amortiguadores y puntales?', 'aEs' => 'Los amortiguadores y puntales ambos amortiguan las vibraciones del camino, pero tienen disenos diferentes. Los puntales son una parte estructural de la suspension e incluyen un resorte helicoidal, mientras que los amortiguadores son componentes independientes. Su vehiculo usa uno u otro — no ambos en la misma rueda.'],
        ],
        'related' => ['wheel-alignment', 'brake-service', 'engine-diagnostics'],
    ],
    [
        'slug' => 'roadside-assistance',
        'name_en' => 'Roadside Assistance',
        'name_es' => 'Asistencia en Carretera',
        'description_en' => 'Emergency roadside assistance in Portland, OR. Flat tire change, jump start, lockout help, and towing coordination. Bilingual English & Spanish service.',
        'description_es' => 'Asistencia en carretera de emergencia en Portland, OR. Cambio de llanta ponchada, arranque con cables, ayuda con cerraduras y coordinacion de grua. Servicio bilingue.',
        'body_en' => '<p>Stranded on the road? Oregon Tires Auto Care provides emergency roadside assistance throughout the Portland metro area. Whether you have a flat tire, a dead battery, or you\'re locked out of your vehicle, our experienced team is ready to help you get back on the road quickly and safely.</p><p>We offer flat tire changes, jump starts, lockout assistance, and can coordinate towing to our shop or a location of your choice. Our bilingual team is available during business hours to ensure clear communication and fast response.</p>',
        'body_es' => '<p>¿Varado en la carretera? Oregon Tires Auto Care ofrece asistencia en carretera de emergencia en toda el area metropolitana de Portland. Ya sea que tenga una llanta ponchada, una bateria muerta o este bloqueado fuera de su vehiculo, nuestro equipo experimentado esta listo para ayudarlo a volver a la carretera de manera rapida y segura.</p><p>Ofrecemos cambio de llantas ponchadas, arranque con cables, asistencia con cerraduras y coordinamos servicio de grua a nuestro taller o al lugar de su eleccion. Nuestro equipo bilingue esta disponible durante el horario de atencion.</p>',
        'icon' => '&#x1F6A8;',
        'color_hex' => '#F43F5E',
        'color_bg' => 'bg-rose-100',
        'color_text' => 'text-rose-800',
        'color_dark_bg' => 'dark:bg-rose-900/30',
        'color_dark_text' => 'dark:text-rose-300',
        'color_dot' => 'bg-rose-500',
        'category' => 'specialized',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 8,
        'duration_estimate' => '30-60',
        'custom_sections_html' => '
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
  </section>',
        'custom_scripts_html' => '
<script src="/assets/js/roadside-estimator.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    RoadsideEstimator.init("roadside-estimator");
  });
</script>',
        'custom_translations' => "
      estimator_title: 'Obtener Estimado',
      estimator_subtitle: 'Seleccione su servicio y ubicación para un estimado inmediato.',",
        'faqs' => [
            ['q' => 'What hours is roadside assistance available?', 'a' => 'Our roadside assistance is available during business hours, Monday through Saturday, 7AM to 7PM.', 'qEs' => 'En que horario esta disponible la asistencia en carretera?', 'aEs' => 'Nuestra asistencia en carretera esta disponible durante el horario de atencion, de lunes a sabado, de 7AM a 7PM.'],
            ['q' => 'What area do you cover?', 'a' => 'We cover the Portland metro area including SE Portland, Clackamas, Happy Valley, Milwaukie, and surrounding neighborhoods.', 'qEs' => 'Que area cubren?', 'aEs' => 'Cubrimos el area metropolitana de Portland incluyendo SE Portland, Clackamas, Happy Valley, Milwaukie y vecindarios cercanos.'],
            ['q' => 'What\'s included in roadside assistance?', 'a' => 'Our roadside service includes flat tire changes, battery jump starts, lockout assistance, and towing coordination. We can also perform basic roadside tire repairs when possible.', 'qEs' => 'Que incluye la asistencia en carretera?', 'aEs' => 'Nuestro servicio en carretera incluye cambio de llantas ponchadas, arranque de bateria con cables, asistencia con cerraduras y coordinacion de grua. Tambien podemos realizar reparaciones basicas de llantas cuando sea posible.'],
        ],
        'related' => ['tire-repair', 'mobile-service', 'tire-installation'],
    ],
    [
        'slug' => 'mobile-service',
        'name_en' => 'Mobile Service',
        'name_es' => 'Servicio Movil',
        'description_en' => 'Mobile tire and auto service in Portland, OR. We come to your home, office, or roadside location. Bilingual English & Spanish service.',
        'description_es' => 'Servicio movil de llantas y auto en Portland, OR. Vamos a su casa, oficina o ubicacion en la carretera. Servicio bilingue ingles y espanol.',
        'body_en' => '<p>Oregon Tires Auto Care brings professional tire and auto service directly to you anywhere in the Portland metro area. Whether you are at home, at work, or stranded on the road, our fully equipped mobile unit can handle tire installation, tire repair, flat fixes, and basic maintenance on-site.</p><p>Our mobile service saves you time and hassle — no need to drive to the shop or wait for a tow truck. We carry a full selection of new and quality used tires on our mobile unit, along with professional mounting and balancing equipment. Same-day service is available for most requests.</p>',
        'body_es' => '<p>Oregon Tires Auto Care lleva servicio profesional de llantas y auto directamente a usted en cualquier lugar del area metropolitana de Portland. Ya sea que este en casa, en el trabajo o varado en la carretera, nuestra unidad movil completamente equipada puede manejar instalacion de llantas, reparacion de llantas, arreglo de ponchadas y mantenimiento basico en el lugar.</p><p>Nuestro servicio movil le ahorra tiempo y molestias — no necesita manejar al taller ni esperar una grua. Llevamos una seleccion completa de llantas nuevas y usadas de calidad en nuestra unidad movil, junto con equipo profesional de montaje y balanceo. Servicio el mismo dia esta disponible para la mayoria de las solicitudes.</p>',
        'icon' => '&#x1F69A;',
        'color_hex' => '#EAB308',
        'color_bg' => 'bg-yellow-100',
        'color_text' => 'text-yellow-800',
        'color_dark_bg' => 'dark:bg-yellow-900/30',
        'color_dark_text' => 'dark:text-yellow-300',
        'color_dot' => 'bg-yellow-500',
        'category' => 'specialized',
        'is_bookable' => 1,
        'has_detail_page' => 1,
        'sort_order' => 9,
        'duration_estimate' => '60-120',
        'custom_sections_html' => '
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
  </section>',
        'custom_translations' => "
      coverage_title: 'Área de Cobertura de Servicio',
      coverage_range: 'Rango de Cobertura',
      coverage_15mi: 'Servicio dentro de 15 millas de nuestro taller',
      coverage_trip_fee: 'Se puede aplicar una tarifa de viaje según la distancia',
      coverage_confirm: 'Confirmamos disponibilidad y costo total antes de enviar',
      coverage_areas: 'Áreas que Servimos',",
        'faqs' => [
            ['q' => 'What areas do you serve with mobile service?', 'a' => 'We serve the entire Portland metro area including Beaverton, Gresham, Milwaukie, Tigard, Lake Oswego, and surrounding communities. Contact us to confirm coverage for your location.', 'qEs' => 'Que areas cubren con el servicio movil?', 'aEs' => 'Cubrimos toda el area metropolitana de Portland incluyendo Beaverton, Gresham, Milwaukie, Tigard, Lake Oswego y comunidades cercanas. Contactenos para confirmar cobertura en su ubicacion.'],
            ['q' => 'Is there an extra charge for mobile service?', 'a' => 'A small trip fee may apply depending on your location. We will provide the total cost upfront before dispatching our mobile unit — no surprises.', 'qEs' => 'Hay un cargo extra por el servicio movil?', 'aEs' => 'Una pequena tarifa de viaje puede aplicar dependiendo de su ubicacion. Le daremos el costo total por adelantado antes de enviar nuestra unidad movil — sin sorpresas.'],
            ['q' => 'How quickly can you arrive?', 'a' => 'For same-day requests, we typically arrive within 1-2 hours depending on availability and distance. You can also schedule a specific time that works best for you.', 'qEs' => 'Que tan rapido pueden llegar?', 'aEs' => 'Para solicitudes del mismo dia, tipicamente llegamos en 1-2 horas dependiendo de la disponibilidad y distancia. Tambien puede programar un horario especifico que le convenga.'],
        ],
        'related' => ['roadside-assistance', 'tire-repair', 'tire-installation'],
    ],
    // Bookable-only services (no detail page)
    [
        'slug' => 'tuneup',
        'name_en' => 'Tune-Up',
        'name_es' => 'Afinacion',
        'description_en' => 'Engine tune-up service.',
        'description_es' => 'Servicio de afinacion de motor.',
        'body_en' => '',
        'body_es' => '',
        'icon' => '&#x2699;',
        'color_hex' => '#10B981',
        'color_bg' => 'bg-emerald-100',
        'color_text' => 'text-emerald-800',
        'color_dark_bg' => 'dark:bg-emerald-900/30',
        'color_dark_text' => 'dark:text-emerald-300',
        'color_dot' => 'bg-emerald-500',
        'category' => 'maintenance',
        'is_bookable' => 1,
        'has_detail_page' => 0,
        'sort_order' => 10,
        'duration_estimate' => '30-60',
        'faqs' => [],
        'related' => [],
    ],
    [
        'slug' => 'mechanical-inspection',
        'name_en' => 'Mechanical Inspection',
        'name_es' => 'Inspeccion Mecanica',
        'description_en' => 'Full vehicle mechanical inspection.',
        'description_es' => 'Inspeccion mecanica completa del vehiculo.',
        'body_en' => '',
        'body_es' => '',
        'icon' => '&#x1F50D;',
        'color_hex' => '#06B6D4',
        'color_bg' => 'bg-cyan-100',
        'color_text' => 'text-cyan-800',
        'color_dark_bg' => 'dark:bg-cyan-900/30',
        'color_dark_text' => 'dark:text-cyan-300',
        'color_dot' => 'bg-cyan-500',
        'category' => 'specialized',
        'is_bookable' => 1,
        'has_detail_page' => 0,
        'sort_order' => 11,
        'duration_estimate' => '30-60',
        'faqs' => [],
        'related' => [],
    ],
    [
        'slug' => 'other',
        'name_en' => 'Other',
        'name_es' => 'Otro',
        'description_en' => 'Other services.',
        'description_es' => 'Otros servicios.',
        'body_en' => '',
        'body_es' => '',
        'icon' => '&#x2753;',
        'color_hex' => '#9CA3AF',
        'color_bg' => 'bg-gray-100',
        'color_text' => 'text-gray-700',
        'color_dark_bg' => 'dark:bg-gray-700',
        'color_dark_text' => 'dark:text-gray-300',
        'color_dot' => 'bg-gray-400',
        'category' => 'specialized',
        'is_bookable' => 1,
        'has_detail_page' => 0,
        'sort_order' => 99,
        'duration_estimate' => '',
        'faqs' => [],
        'related' => [],
    ],
];

// Insert services
$insertSvc = $db->prepare(
    'INSERT INTO oretir_services
        (slug, name_en, name_es, description_en, description_es, body_en, body_es,
         icon, color_hex, color_bg, color_text, color_dark_bg, color_dark_text, color_dot,
         category, is_bookable, has_detail_page, sort_order, duration_estimate,
         custom_sections_html, custom_scripts_html, custom_translations)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$insertFaq = $db->prepare(
    'INSERT INTO oretir_service_faqs (service_id, question_en, question_es, answer_en, answer_es, sort_order)
     VALUES (?, ?, ?, ?, ?, ?)'
);

$slugToId = [];
$serviceRelated = [];

foreach ($services as $svc) {
    // Check if already exists
    $check = $db->prepare('SELECT id FROM oretir_services WHERE slug = ?');
    $check->execute([$svc['slug']]);
    $existing = $check->fetch(\PDO::FETCH_ASSOC);

    if ($existing) {
        echo "  SKIP: {$svc['slug']} (already exists)\n";
        $slugToId[$svc['slug']] = (int) $existing['id'];
        continue;
    }

    $insertSvc->execute([
        $svc['slug'], $svc['name_en'], $svc['name_es'],
        $svc['description_en'], $svc['description_es'],
        $svc['body_en'], $svc['body_es'],
        $svc['icon'], $svc['color_hex'], $svc['color_bg'], $svc['color_text'],
        $svc['color_dark_bg'], $svc['color_dark_text'], $svc['color_dot'],
        $svc['category'], $svc['is_bookable'], $svc['has_detail_page'],
        $svc['sort_order'], $svc['duration_estimate'],
        $svc['custom_sections_html'] ?? null,
        $svc['custom_scripts_html'] ?? null,
        $svc['custom_translations'] ?? null,
    ]);

    $id = (int) $db->lastInsertId();
    $slugToId[$svc['slug']] = $id;
    echo "  INSERT: {$svc['slug']} (id={$id})\n";

    // Insert FAQs
    $faqOrder = 0;
    foreach ($svc['faqs'] as $faq) {
        $insertFaq->execute([
            $id, $faq['q'], $faq['qEs'] ?? '', $faq['a'], $faq['aEs'] ?? '', $faqOrder++
        ]);
    }
    if ($faqOrder > 0) echo "    → {$faqOrder} FAQs\n";

    // Store related for second pass
    if (!empty($svc['related'])) {
        $serviceRelated[$svc['slug']] = $svc['related'];
    }
}

// Second pass: insert related services
echo "\nLinking related services...\n";
$insertRel = $db->prepare(
    'INSERT IGNORE INTO oretir_service_related (service_id, related_service_id, sort_order) VALUES (?, ?, ?)'
);

foreach ($serviceRelated as $slug => $relatedSlugs) {
    if (!isset($slugToId[$slug])) continue;
    $serviceId = $slugToId[$slug];
    $order = 0;
    foreach ($relatedSlugs as $relSlug) {
        if (isset($slugToId[$relSlug])) {
            $insertRel->execute([$serviceId, $slugToId[$relSlug], $order++]);
        }
    }
    echo "  {$slug} → " . implode(', ', $relatedSlugs) . "\n";
}

echo "\nDone! Seeded " . count($services) . " services.\n";
