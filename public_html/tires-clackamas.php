<?php
$areaName = 'Clackamas';
$areaNameEs = 'Clackamas';
$areaSlug = 'tires-clackamas';
$areaSlugEs = 'llantas-clackamas';
$areaZip = '97015';
$areaDescription = 'Affordable tire and auto care services near Clackamas Town Center. Bilingual English & Spanish. New and used tires, brakes, oil changes. Since 2008.';
$areaDescriptionEs = 'Servicios automotrices y de llantas cerca de Clackamas Town Center. Servicio biling&uuml;e. Llantas nuevas y usadas, frenos, cambios de aceite. Desde 2008.';
$landmarks = [
  ['name' => 'Clackamas Town Center', 'nameEs' => 'Clackamas Town Center', 'distance' => '8 min drive'],
  ['name' => 'Clackamas Promenade', 'nameEs' => 'Clackamas Promenade', 'distance' => '10 min drive'],
  ['name' => 'I-205 / Sunnyside Rd exit', 'nameEs' => 'Salida I-205 / Sunnyside Rd', 'distance' => '7 min drive'],
];
$testimonial = [
  'name' => 'David Miller',
  'text' => 'Oregon Tires always takes care of my family\'s vehicles. Honest, reliable service. Worth the drive from Clackamas every time.',
  'textEs' => 'Oregon Tires siempre cuida los veh\u00edculos de mi familia. Servicio honesto y confiable. Vale la pena el viaje desde Clackamas.',
  'detail' => 'Clackamas resident',
  'detailEs' => 'Residente de Clackamas',
];
$nearbyAreas = [
  ['name' => 'Tires in SE Portland', 'slug' => 'tires-se-portland'],
  ['name' => 'Tires in Happy Valley', 'slug' => 'tires-happy-valley'],
  ['name' => 'Tires in Milwaukie', 'slug' => 'tires-milwaukie'],
];
require __DIR__ . '/templates/service-area.php';
