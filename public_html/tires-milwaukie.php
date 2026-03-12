<?php
$areaName = 'Milwaukie';
$areaNameEs = 'Milwaukie';
$areaSlug = 'tires-milwaukie';
$areaSlugEs = 'llantas-milwaukie';
$areaZip = '97222';
$areaDescription = 'Quality tire and auto care services near Milwaukie, OR. New and used tires, brakes, oil changes, alignment. Bilingual English & Spanish. Since 2008.';
$areaDescriptionEs = 'Servicios de llantas y automotrices de calidad cerca de Milwaukie, OR. Llantas nuevas y usadas, frenos, cambios de aceite, alineaci&oacute;n. Biling&uuml;e. Desde 2008.';
$landmarks = [
  ['name' => 'downtown Milwaukie', 'nameEs' => 'centro de Milwaukie', 'distance' => '10 min drive'],
  ['name' => 'Milwaukie/Main St MAX Station', 'nameEs' => 'Estaci\u00f3n MAX Milwaukie/Main St', 'distance' => '12 min drive'],
  ['name' => 'McLoughlin Blvd corridor', 'nameEs' => 'Corredor McLoughlin Blvd', 'distance' => '8 min drive'],
];
$testimonial = [
  'name' => 'Carlos Ramirez',
  'text' => 'Excellent customer service. They repaired my tire quickly and with warranty. Great value for Milwaukie area drivers.',
  'textEs' => 'Excelente servicio al cliente. Repararon mi llanta r\u00e1pidamente y con garant\u00eda. Gran valor para los conductores del \u00e1rea de Milwaukie.',
  'detail' => 'Milwaukie area driver',
  'detailEs' => 'Conductor del \u00e1rea de Milwaukie',
];
$nearbyAreas = [
  ['name' => 'Tires in SE Portland', 'slug' => 'tires-se-portland'],
  ['name' => 'Tires in Clackamas', 'slug' => 'tires-clackamas'],
  ['name' => 'Tires in Woodstock', 'slug' => 'tires-woodstock'],
];
require __DIR__ . '/templates/service-area.php';
