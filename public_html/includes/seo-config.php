<?php
declare(strict_types=1);

/**
 * Oregon Tires — Centralized Business & SEO Configuration
 * Single source of truth for business info, services, service areas, and SEO data.
 */

function getBusinessConfig(): array {
    return [
        'name' => 'Oregon Tires Auto Care',
        'nameEs' => 'Oregon Tires Auto Care',
        'url' => 'https://oregon.tires',
        'phone' => '(503) 367-9714',
        'phoneRaw' => '5033679714',
        'email' => 'oregontirespdx@gmail.com',
        'address' => [
            'street' => '8536 SE 82nd Ave',
            'city' => 'Portland',
            'state' => 'OR',
            'zip' => '97266',
            'country' => 'US',
        ],
        'geo' => [
            'lat' => 45.46123,
            'lng' => -122.57895,
        ],
        'hours' => [
            ['days' => 'Mo-Sa', 'open' => '07:00', 'close' => '19:00'],
        ],
        'hoursDisplay' => 'Mon-Sat 7AM-7PM',
        'hoursDisplayEs' => 'Lun-Sáb 7AM-7PM',
        'foundingDate' => '2008',
        'priceRange' => '$$',
        'languages' => ['en', 'es'],
        'social' => [
            'facebook' => 'https://www.facebook.com/61571913202998/',
            'instagram' => 'https://www.instagram.com/oregontires',
        ],
        'googlePlaceId' => 'ChIJLSxZDQyflVQRWXEi9LpJGxs',
        'gaId' => 'G-CHYMTNB6LH',
        'services' => (function() {
            try {
                $db = getDB();
                return $db->query(
                    'SELECT name_en AS name, name_es AS nameEs, slug
                     FROM oretir_services
                     WHERE is_active = 1 AND has_detail_page = 1
                     ORDER BY sort_order ASC'
                )->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                // Fallback if table doesn't exist yet
                return [
                    ['name' => 'Tire Installation', 'nameEs' => 'Instalación de Llantas', 'slug' => 'tire-installation'],
                    ['name' => 'Tire Repair', 'nameEs' => 'Reparación de Llantas', 'slug' => 'tire-repair'],
                    ['name' => 'Wheel Alignment', 'nameEs' => 'Alineación de Ruedas', 'slug' => 'wheel-alignment'],
                    ['name' => 'Brake Service', 'nameEs' => 'Servicio de Frenos', 'slug' => 'brake-service'],
                    ['name' => 'Oil Change', 'nameEs' => 'Cambio de Aceite', 'slug' => 'oil-change'],
                    ['name' => 'Engine Diagnostics', 'nameEs' => 'Diagnóstico de Motor', 'slug' => 'engine-diagnostics'],
                    ['name' => 'Suspension Repair', 'nameEs' => 'Reparación de Suspensión', 'slug' => 'suspension-repair'],
                    ['name' => 'Roadside Assistance', 'nameEs' => 'Asistencia en Carretera', 'slug' => 'roadside-assistance'],
                ];
            }
        })(),
        'serviceAreas' => [
            ['name' => 'SE Portland', 'nameEs' => 'SE Portland', 'slug' => 'tires-se-portland'],
            ['name' => 'Clackamas', 'nameEs' => 'Clackamas', 'slug' => 'tires-clackamas'],
            ['name' => 'Happy Valley', 'nameEs' => 'Happy Valley', 'slug' => 'tires-happy-valley'],
            ['name' => 'Milwaukie', 'nameEs' => 'Milwaukie', 'slug' => 'tires-milwaukie'],
            ['name' => 'Lents', 'nameEs' => 'Lents', 'slug' => 'tires-lents'],
            ['name' => 'Woodstock', 'nameEs' => 'Woodstock', 'slug' => 'tires-woodstock'],
            ['name' => 'Foster-Powell', 'nameEs' => 'Foster-Powell', 'slug' => 'tires-foster-powell'],
            ['name' => 'Mt. Scott', 'nameEs' => 'Mt. Scott', 'slug' => 'tires-mt-scott'],
        ],
        'pages' => [
            'financing' => [
                'title' => 'Financing Options',
                'titleEs' => 'Opciones de Financiamiento',
                'description' => 'Flexible payment options for tire and auto repair services at Oregon Tires Auto Care in Portland, OR.',
                'descriptionEs' => 'Opciones de pago flexibles para servicios de llantas y reparación automotriz en Oregon Tires Auto Care en Portland, OR.',
                'slug' => 'financing',
            ],
        ],
        // TODO: Pull from DB when review system is built
        'rating' => '4.8',
        'reviewCount' => '150',
        'verification' => [
            'google' => $_ENV['GOOGLE_SITE_VERIFICATION'] ?? '',
            'bing' => $_ENV['BING_SITE_VERIFICATION'] ?? '',
        ],
    ];
}
