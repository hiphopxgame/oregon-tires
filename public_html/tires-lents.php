<?php
$areaName = 'Lents';
$areaNameEs = 'Lents';
$areaSlug = 'lents';
$areaZip = '97266';
$areaDescription = 'Affordable tire installation, oil changes, brake service, and auto repair for the Lents neighborhood in SE Portland. Conveniently located near Foster Road and the Lents Town Center.';
$areaDescriptionEs = 'Instalación de llantas, cambios de aceite, servicio de frenos y reparación de autos a precios accesibles para el vecindario de Lents en SE Portland. Ubicados convenientemente cerca de Foster Road y el Lents Town Center.';
$landmarks = [
  ['name' => 'Lents Town Center', 'nameEs' => 'Lents Town Center', 'distance' => '5 minutes'],
  ['name' => 'Foster Road', 'nameEs' => 'Foster Road', 'distance' => '5 minutes'],
  ['name' => 'Lents Park', 'nameEs' => 'Lents Park', 'distance' => '7 minutes'],
];
$testimonial = [
  'text' => 'Great prices and fast service. I always bring my car here from Lents — it\'s just a quick drive down Foster.',
  'textEs' => 'Buenos precios y servicio rápido. Siempre traigo mi carro desde Lents — es un viaje rápido por Foster.',
  'name' => 'Miguel R.',
  'detail' => 'Lents resident',
  'detailEs' => 'Residente de Lents',
];
$nearbyAreas = [
  ['name' => 'SE Portland', 'slug' => 'se-portland'],
  ['name' => 'Foster-Powell', 'slug' => 'foster-powell'],
  ['name' => 'Mt. Scott', 'slug' => 'mt-scott'],
  ['name' => 'Woodstock', 'slug' => 'woodstock'],
];
require __DIR__ . '/templates/service-area.php';
