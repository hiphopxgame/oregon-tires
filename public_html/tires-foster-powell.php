<?php
$areaName = 'Foster-Powell';
$areaNameEs = 'Foster-Powell';
$areaSlug = 'foster-powell';
$areaZip = '97206';
$areaDescription = 'Tire installation, oil changes, brake service, and auto repair for the Foster-Powell neighborhood in SE Portland. Easily accessible from SE Foster Road and SE Powell Boulevard.';
$areaDescriptionEs = 'Instalación de llantas, cambios de aceite, servicio de frenos y reparación de autos para el vecindario de Foster-Powell en SE Portland. Fácilmente accesible desde SE Foster Road y SE Powell Boulevard.';
$landmarks = [
  ['name' => 'SE Foster Road', 'nameEs' => 'SE Foster Road', 'distance' => '5 minutes'],
  ['name' => 'SE Powell Boulevard', 'nameEs' => 'SE Powell Boulevard', 'distance' => '6 minutes'],
  ['name' => 'Creston Park', 'nameEs' => 'Creston Park', 'distance' => '8 minutes'],
];
$testimonial = [
  'text' => 'I live in Foster-Powell and this is the best tire shop nearby. Quick service and they always explain everything clearly.',
  'textEs' => 'Vivo en Foster-Powell y esta es la mejor tienda de llantas cercana. Servicio rápido y siempre explican todo claramente.',
  'name' => 'David M.',
  'detail' => 'Foster-Powell resident',
  'detailEs' => 'Residente de Foster-Powell',
];
$nearbyAreas = [
  ['name' => 'SE Portland', 'slug' => 'se-portland'],
  ['name' => 'Lents', 'slug' => 'lents'],
  ['name' => 'Woodstock', 'slug' => 'woodstock'],
  ['name' => 'Mt. Scott', 'slug' => 'mt-scott'],
];
require __DIR__ . '/templates/service-area.php';
