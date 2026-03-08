<?php
$areaName = 'Mt. Scott';
$areaNameEs = 'Mt. Scott';
$areaSlug = 'mt-scott';
$areaZip = '97266';
$areaDescription = 'Tire installation, oil changes, brake service, and auto repair for the Mt. Scott-Arleta neighborhood in SE Portland. Conveniently located near SE 72nd Avenue and Mt. Scott Park.';
$areaDescriptionEs = 'Instalación de llantas, cambios de aceite, servicio de frenos y reparación de autos para el vecindario de Mt. Scott-Arleta en SE Portland. Ubicados convenientemente cerca de SE 72nd Avenue y Mt. Scott Park.';
$landmarks = [
  ['name' => 'Mt. Scott Park', 'nameEs' => 'Mt. Scott Park', 'distance' => '5 minutes'],
  ['name' => 'SE 72nd Avenue', 'nameEs' => 'SE 72nd Avenue', 'distance' => '4 minutes'],
  ['name' => 'Mt. Scott Community Center', 'nameEs' => 'Centro Comunitario Mt. Scott', 'distance' => '6 minutes'],
];
$testimonial = [
  'text' => 'Best tire shop in the Mt. Scott area. Fair prices, bilingual staff, and they got me in and out quickly.',
  'textEs' => 'La mejor tienda de llantas en el área de Mt. Scott. Precios justos, personal bilingüe y me atendieron rápidamente.',
  'name' => 'Ana G.',
  'detail' => 'Mt. Scott resident',
  'detailEs' => 'Residente de Mt. Scott',
];
$nearbyAreas = [
  ['name' => 'SE Portland', 'slug' => 'se-portland'],
  ['name' => 'Lents', 'slug' => 'lents'],
  ['name' => 'Happy Valley', 'slug' => 'happy-valley'],
  ['name' => 'Foster-Powell', 'slug' => 'foster-powell'],
];
require __DIR__ . '/templates/service-area.php';
