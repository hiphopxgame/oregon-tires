<?php
$serviceName = 'Roadside Assistance';
$serviceNameEs = 'Asistencia en Carretera';
$serviceSlug = 'roadside-assistance';
$serviceIcon = '&#x1F6A8;';
$serviceDescription = 'Emergency roadside assistance in Portland, OR. Flat tire change, jump start, lockout help, and towing coordination. Bilingual English & Spanish service.';
$serviceDescriptionEs = 'Asistencia en carretera de emergencia en Portland, OR. Cambio de llanta ponchada, arranque con cables, ayuda con cerraduras y coordinacion de grua. Servicio bilingue.';
$serviceBody = '<p>Stranded on the road? Oregon Tires Auto Care provides emergency roadside assistance throughout the Portland metro area. Whether you have a flat tire, a dead battery, or you\'re locked out of your vehicle, our experienced team is ready to help you get back on the road quickly and safely.</p><p>We offer flat tire changes, jump starts, lockout assistance, and can coordinate towing to our shop or a location of your choice. Our bilingual team is available during business hours to ensure clear communication and fast response.</p>';
$serviceBodyEs = '<p>¿Varado en la carretera? Oregon Tires Auto Care ofrece asistencia en carretera de emergencia en toda el area metropolitana de Portland. Ya sea que tenga una llanta ponchada, una bateria muerta o este bloqueado fuera de su vehiculo, nuestro equipo experimentado esta listo para ayudarlo a volver a la carretera de manera rapida y segura.</p><p>Ofrecemos cambio de llantas ponchadas, arranque con cables, asistencia con cerraduras y coordinamos servicio de grua a nuestro taller o al lugar de su eleccion. Nuestro equipo bilingue esta disponible durante el horario de atencion.</p>';
$faqItems = [
    ['q' => 'What hours is roadside assistance available?', 'a' => 'Our roadside assistance is available during business hours, Monday through Saturday, 7AM to 7PM.', 'qEs' => 'En que horario esta disponible la asistencia en carretera?', 'aEs' => 'Nuestra asistencia en carretera esta disponible durante el horario de atencion, de lunes a sabado, de 7AM a 7PM.'],
    ['q' => 'What area do you cover?', 'a' => 'We cover the Portland metro area including SE Portland, Clackamas, Happy Valley, Milwaukie, and surrounding neighborhoods.', 'qEs' => 'Que area cubren?', 'aEs' => 'Cubrimos el area metropolitana de Portland incluyendo SE Portland, Clackamas, Happy Valley, Milwaukie y vecindarios cercanos.'],
    ['q' => 'What\'s included in roadside assistance?', 'a' => 'Our roadside service includes flat tire changes, battery jump starts, lockout assistance, and towing coordination. We can also perform basic roadside tire repairs when possible.', 'qEs' => 'Que incluye la asistencia en carretera?', 'aEs' => 'Nuestro servicio en carretera incluye cambio de llantas ponchadas, arranque de bateria con cables, asistencia con cerraduras y coordinacion de grua. Tambien podemos realizar reparaciones basicas de llantas cuando sea posible.'],
];
$relatedServices = [
    ['name' => 'Tire Repair', 'slug' => 'tire-repair'],
    ['name' => 'Mobile Service', 'slug' => 'mobile-service'],
    ['name' => 'Tire Installation', 'slug' => 'tire-installation'],
];
require __DIR__ . '/templates/service-detail.php';
