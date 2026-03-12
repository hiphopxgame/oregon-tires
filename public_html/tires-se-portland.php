<?php
$areaName = 'SE Portland';
$areaNameEs = 'SE Portland';
$areaSlug = 'tires-se-portland';
$areaSlugEs = 'llantas-se-portland';
$areaZip = '97266';
$areaDescription = 'Professional tire installation, brake service, oil changes and auto repair in SE Portland. Bilingual English & Spanish service on 82nd Ave. Since 2008.';
$areaDescriptionEs = 'Instalaci&oacute;n profesional de llantas, servicio de frenos, cambios de aceite y reparaci&oacute;n automotriz en SE Portland. Servicio biling&uuml;e en la Ave 82nd. Desde 2008.';
$landmarks = [
  ['name' => 'Johnson Creek MAX Station', 'nameEs' => 'Estaci\u00f3n MAX Johnson Creek', 'distance' => '5 min drive'],
  ['name' => 'Eastport Plaza', 'nameEs' => 'Eastport Plaza', 'distance' => '3 min drive'],
  ['name' => 'SE 82nd Avenue corridor', 'nameEs' => 'Corredor SE 82nd Avenue', 'distance' => 'On 82nd Ave'],
];
$testimonial = [
  'name' => 'Maria Rodriguez',
  'text' => 'Excellent service! They installed my new tires quickly and the price was very fair. The staff speaks Spanish which made communication easy.',
  'textEs' => '\u00a1Excelente servicio! Instalaron mis llantas nuevas r\u00e1pidamente y el precio fue muy justo. El personal habla espa\u00f1ol, lo que facilit\u00f3 la comunicaci\u00f3n.',
  'detail' => 'SE Portland resident',
  'detailEs' => 'Residente de SE Portland',
];
$nearbyAreas = [
  ['name' => 'Tires in Clackamas', 'slug' => 'tires-clackamas'],
  ['name' => 'Tires in Happy Valley', 'slug' => 'tires-happy-valley'],
  ['name' => 'Tires in Milwaukie', 'slug' => 'tires-milwaukie'],
  ['name' => 'Tires in Lents', 'slug' => 'tires-lents'],
];
require __DIR__ . '/templates/service-area.php';
