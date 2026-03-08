<?php
$areaName = 'Woodstock';
$areaNameEs = 'Woodstock';
$areaSlug = 'woodstock';
$areaZip = '97206';
$areaDescription = 'Tire installation, oil changes, brake service, and auto repair for the Woodstock neighborhood in SE Portland. Just minutes from Woodstock Boulevard and Reed College.';
$areaDescriptionEs = 'Instalación de llantas, cambios de aceite, servicio de frenos y reparación de autos para el vecindario de Woodstock en SE Portland. A solo minutos de Woodstock Boulevard y Reed College.';
$landmarks = [
  ['name' => 'Woodstock Boulevard', 'nameEs' => 'Woodstock Boulevard', 'distance' => '7 minutes'],
  ['name' => 'Reed College', 'nameEs' => 'Reed College', 'distance' => '8 minutes'],
  ['name' => 'Eastmoreland Golf Course', 'nameEs' => 'Eastmoreland Golf Course', 'distance' => '10 minutes'],
];
$testimonial = [
  'text' => 'Honest mechanics and fair prices. I\'ve been coming here from Woodstock for over a year now. Highly recommend!',
  'textEs' => 'Mecánicos honestos y precios justos. He venido aquí desde Woodstock por más de un año. ¡Muy recomendado!',
  'name' => 'Sarah K.',
  'detail' => 'Woodstock resident',
  'detailEs' => 'Residente de Woodstock',
];
$nearbyAreas = [
  ['name' => 'SE Portland', 'slug' => 'se-portland'],
  ['name' => 'Milwaukie', 'slug' => 'milwaukie'],
  ['name' => 'Mt. Scott', 'slug' => 'mt-scott'],
  ['name' => 'Lents', 'slug' => 'lents'],
];
require __DIR__ . '/templates/service-area.php';
