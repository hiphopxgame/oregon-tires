<?php
$areaName = 'SE Portland';
$areaNameEs = 'SE Portland';
$areaSlug = 'tires-se-portland';
$areaSlugEs = 'llantas-se-portland';
$areaZip = '97266';
$areaDescription = 'Professional tire installation, brake service, oil changes and auto repair in SE Portland. Bilingual English & Spanish service on 82nd Ave. Since 2008.';
$areaDescriptionEs = 'Instalaci&oacute;n profesional de llantas, servicio de frenos, cambios de aceite y reparaci&oacute;n automotriz en SE Portland. Servicio biling&uuml;e en la Ave 82nd. Desde 2008.';
$landmarks = [
  ['name' => 'Johnson Creek MAX Station', 'distance' => '5 min drive'],
  ['name' => 'Eastport Plaza', 'distance' => '3 min drive'],
  ['name' => 'SE 82nd Avenue corridor', 'distance' => 'On 82nd Ave'],
];
$testimonial = ['name' => 'Maria Rodriguez', 'text' => 'Excellent service! They installed my new tires quickly and the price was very fair. The staff speaks Spanish which made communication easy.'];
$nearbyAreas = [
  ['name' => 'Tires in Clackamas', 'slug' => 'tires-clackamas'],
  ['name' => 'Tires in Happy Valley', 'slug' => 'tires-happy-valley'],
  ['name' => 'Tires in Milwaukie', 'slug' => 'tires-milwaukie'],
  ['name' => 'Tires in Lents', 'slug' => 'tires-lents'],
];
require __DIR__ . '/templates/service-area.php';
