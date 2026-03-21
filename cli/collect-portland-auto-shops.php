<?php
/**
 * Oregon Tires — Portland Auto Industry Directory Collector
 *
 * Uses Google Places API (Text Search + Place Details) to build a comprehensive
 * JSON database of every auto-related business in the Portland metro area.
 *
 * Usage: php cli/collect-portland-auto-shops.php
 *
 * Requires: GOOGLE_PLACES_API_KEY in .env
 * Output:   _data/portland-auto-directory.json
 */

declare(strict_types=1);

// Load bootstrap — handle both local (public_html/) and server (flat) layouts
$bootstrapPaths = [
    __DIR__ . '/../public_html/includes/bootstrap.php',  // Local dev
    __DIR__ . '/../includes/bootstrap.php',               // Server (flat)
];
$loaded = false;
foreach ($bootstrapPaths as $bp) {
    if (file_exists($bp)) { require_once $bp; $loaded = true; break; }
}
if (!$loaded) {
    // Fallback: load .env manually
    $envPaths = [
        dirname(__DIR__, 2) . '/.env.oregon-tires',
        dirname(__DIR__) . '/.env',
    ];
    foreach ($envPaths as $ep) {
        if (file_exists($ep)) {
            foreach (file($ep, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim(trim($v), '"\'');
            }
            break;
        }
    }
}

$apiKey = $_ENV['GOOGLE_PLACES_API_KEY'] ?? '';
if (!$apiKey) {
    echo "ERROR: GOOGLE_PLACES_API_KEY not set in .env\n";
    exit(1);
}

$outputFile = __DIR__ . '/../_data/portland-auto-directory.json';

// ─── Search Queries ──────────────────────────────────────────────────────────
$searches = [
    // Tire shops
    ['query' => 'tire shops Portland OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire installation Portland Oregon', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire shops Beaverton OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire shops Gresham OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire shops Tigard OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire shops Clackamas OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire shops Milwaukie OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],
    ['query' => 'tire shops Happy Valley OR', 'category' => 'auto_repair', 'subcategory' => 'tires'],

    // General auto repair
    ['query' => 'auto repair shops Portland Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto mechanics SE Portland OR', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto mechanics NE Portland OR', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Beaverton Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Gresham Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Tigard Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Lake Oswego Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Milwaukie Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Clackamas Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],
    ['query' => 'auto repair Happy Valley Oregon', 'category' => 'auto_repair', 'subcategory' => 'general_mechanic'],

    // Brake / specialty repair
    ['query' => 'brake repair Portland Oregon', 'category' => 'auto_repair', 'subcategory' => 'brakes'],
    ['query' => 'transmission repair Portland Oregon', 'category' => 'specialty', 'subcategory' => 'transmission'],
    ['query' => 'muffler exhaust shop Portland Oregon', 'category' => 'specialty', 'subcategory' => 'muffler'],

    // Auto parts stores
    ['query' => 'auto parts stores Portland Oregon', 'category' => 'parts_store', 'subcategory' => 'parts'],
    ['query' => 'AutoZone Portland Oregon', 'category' => 'parts_store', 'subcategory' => 'parts'],
    ['query' => 'O\'Reilly Auto Parts Portland Oregon', 'category' => 'parts_store', 'subcategory' => 'parts'],
    ['query' => 'NAPA Auto Parts Portland Oregon', 'category' => 'parts_store', 'subcategory' => 'parts'],
    ['query' => 'auto parts stores Beaverton Tigard Oregon', 'category' => 'parts_store', 'subcategory' => 'parts'],

    // Dealership service centers
    ['query' => 'Toyota service center Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'Honda service center Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'Ford service center Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'Chevrolet service center Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'Subaru service center Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'Nissan Hyundai Kia service Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'BMW Mercedes Audi service Portland Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],
    ['query' => 'car dealership service department Beaverton Oregon', 'category' => 'dealership', 'subcategory' => 'dealership_service'],

    // Body shops / collision
    ['query' => 'auto body shop Portland Oregon', 'category' => 'specialty', 'subcategory' => 'body_shop'],
    ['query' => 'collision repair Portland Oregon', 'category' => 'specialty', 'subcategory' => 'body_shop'],
    ['query' => 'body shop Beaverton Tigard Oregon', 'category' => 'specialty', 'subcategory' => 'body_shop'],

    // Detailing
    ['query' => 'auto detailing Portland Oregon', 'category' => 'specialty', 'subcategory' => 'detailing'],

    // Tint / audio / upholstery
    ['query' => 'car window tint Portland Oregon', 'category' => 'specialty', 'subcategory' => 'tint'],
    ['query' => 'car audio installation Portland Oregon', 'category' => 'specialty', 'subcategory' => 'audio'],
    ['query' => 'auto upholstery Portland Oregon', 'category' => 'specialty', 'subcategory' => 'upholstery'],

    // Towing
    ['query' => 'towing company Portland Oregon', 'category' => 'specialty', 'subcategory' => 'towing'],

    // Oil change / quick lube
    ['query' => 'oil change Portland Oregon', 'category' => 'specialty', 'subcategory' => 'oil_change'],
    ['query' => 'Jiffy Lube Portland Oregon', 'category' => 'specialty', 'subcategory' => 'oil_change'],
    ['query' => 'Valvoline Portland Oregon', 'category' => 'specialty', 'subcategory' => 'oil_change'],
];

// ─── Known chains for tagging ────────────────────────────────────────────────
$chainPatterns = [
    'Les Schwab' => 'Les Schwab Tires',
    'Discount Tire' => 'Discount Tire',
    'Firestone' => 'Firestone Complete Auto Care',
    'Goodyear' => 'Goodyear Auto Service',
    'Big O Tires' => 'Big O Tires',
    'Midas' => 'Midas',
    'Meineke' => 'Meineke Car Care',
    'Pep Boys' => 'Pep Boys',
    'Jiffy Lube' => 'Jiffy Lube',
    'Valvoline' => 'Valvoline Instant Oil Change',
    'AutoZone' => 'AutoZone',
    'O\'Reilly' => 'O\'Reilly Auto Parts',
    'NAPA' => 'NAPA Auto Parts',
    'Advance Auto' => 'Advance Auto Parts',
    'Caliber Collision' => 'Caliber Collision',
    'Maaco' => 'Maaco',
    'Safelite' => 'Safelite AutoGlass',
    'Brake Masters' => 'Brake Masters',
    'Grease Monkey' => 'Grease Monkey',
    'Christian Brothers' => 'Christian Brothers Automotive',
    'AAMCO' => 'AAMCO Transmissions',
];

// ─── Neighborhood mapping by zip ─────────────────────────────────────────────
$zipToNeighborhood = [
    '97201' => 'SW Portland', '97202' => 'SE Portland', '97203' => 'N Portland',
    '97204' => 'Downtown Portland', '97205' => 'NW Portland', '97206' => 'SE Portland',
    '97209' => 'NW Portland', '97210' => 'NW Portland', '97211' => 'NE Portland',
    '97212' => 'NE Portland', '97213' => 'NE Portland', '97214' => 'SE Portland',
    '97215' => 'SE Portland', '97216' => 'SE Portland', '97217' => 'N Portland',
    '97218' => 'NE Portland', '97219' => 'SW Portland', '97220' => 'E Portland',
    '97221' => 'SW Portland', '97222' => 'Milwaukie', '97223' => 'Tigard',
    '97224' => 'Tigard', '97225' => 'SW Portland', '97227' => 'NE Portland',
    '97229' => 'NW Portland', '97230' => 'E Portland', '97231' => 'NW Portland',
    '97232' => 'NE Portland', '97233' => 'E Portland', '97236' => 'SE Portland',
    '97239' => 'SW Portland', '97266' => 'SE Portland', '97267' => 'Milwaukie',
    '97005' => 'Beaverton', '97006' => 'Beaverton', '97007' => 'Beaverton',
    '97008' => 'Beaverton', '97015' => 'Clackamas', '97034' => 'Lake Oswego',
    '97035' => 'Lake Oswego', '97030' => 'Gresham', '97080' => 'Gresham',
    '97086' => 'Happy Valley', '97222' => 'Milwaukie', '97268' => 'Milwaukie',
];

// ─── API Helpers ─────────────────────────────────────────────────────────────
function placesTextSearch(string $query, string $apiKey): array
{
    $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?'
         . http_build_query(['query' => $query, 'key' => $apiKey]);

    $allResults = [];
    $pageToken = null;
    $pages = 0;

    do {
        $fetchUrl = $pageToken ? $url . '&pagetoken=' . $pageToken : $url;

        // Google requires a short delay between page token requests
        if ($pageToken) {
            usleep(2000000); // 2 seconds
        }

        $response = @file_get_contents($fetchUrl);
        if (!$response) break;

        $data = json_decode($response, true);
        if (!$data || ($data['status'] ?? '') !== 'OK') break;

        $allResults = array_merge($allResults, $data['results'] ?? []);
        $pageToken = $data['next_page_token'] ?? null;
        $pages++;

    } while ($pageToken && $pages < 3); // Max 3 pages (60 results) per query

    return $allResults;
}

function placeDetails(string $placeId, string $apiKey): ?array
{
    $fields = 'name,formatted_address,formatted_phone_number,website,rating,user_ratings_total,opening_hours,types,business_status';
    $url = 'https://maps.googleapis.com/maps/api/place/details/json?'
         . http_build_query(['place_id' => $placeId, 'fields' => $fields, 'key' => $apiKey]);

    $response = @file_get_contents($url);
    if (!$response) return null;

    $data = json_decode($response, true);
    return ($data['status'] ?? '') === 'OK' ? ($data['result'] ?? null) : null;
}

function detectChain(string $name, array $chainPatterns): ?string
{
    $lower = strtolower($name);
    foreach ($chainPatterns as $pattern => $chainName) {
        if (stripos($lower, strtolower($pattern)) !== false) {
            return $chainName;
        }
    }
    return null;
}

function parseAddress(string $formatted): array
{
    // "1234 SE 82nd Ave, Portland, OR 97266, USA"
    $parts = array_map('trim', explode(',', $formatted));
    $address = $parts[0] ?? '';
    $city = $parts[1] ?? 'Portland';
    $stateZip = $parts[2] ?? 'OR';

    preg_match('/([A-Z]{2})\s+(\d{5})/', $stateZip, $m);
    $state = $m[1] ?? 'OR';
    $zip = $m[2] ?? '';

    // Remove country
    return ['address' => $address, 'city' => $city, 'state' => $state, 'zip' => $zip];
}

function parseHours(array $openingHours): array
{
    $result = [];
    $periods = $openingHours['weekday_text'] ?? [];
    foreach ($periods as $day) {
        // "Monday: 8:00 AM – 6:00 PM"
        $parts = explode(': ', $day, 2);
        if (count($parts) === 2) {
            $dayName = strtolower(substr($parts[0], 0, 3));
            $result[$dayName] = $parts[1];
        }
    }

    // Consolidate to mon_fri, sat, sun
    $mf = $result['mon'] ?? 'Unknown';
    $sat = $result['sat'] ?? 'Closed';
    $sun = $result['sun'] ?? 'Closed';

    return ['mon_fri' => $mf, 'sat' => $sat, 'sun' => $sun];
}

function inferServices(string $name, array $types, string $subcategory): array
{
    $services = [];
    $lower = strtolower($name);
    $typeStr = implode(' ', $types);

    if (str_contains($lower, 'tire') || $subcategory === 'tires') {
        $services = array_merge($services, ['tire installation', 'tire repair', 'tire balancing', 'flat repair']);
    }
    if (str_contains($lower, 'brake') || $subcategory === 'brakes') {
        $services[] = 'brake service';
    }
    if (str_contains($lower, 'oil') || str_contains($lower, 'lube') || $subcategory === 'oil_change') {
        $services[] = 'oil change';
    }
    if (str_contains($lower, 'body') || str_contains($lower, 'collision') || $subcategory === 'body_shop') {
        $services = array_merge($services, ['collision repair', 'auto body', 'paint']);
    }
    if (str_contains($lower, 'detail') || $subcategory === 'detailing') {
        $services = array_merge($services, ['detailing', 'wash', 'wax', 'interior cleaning']);
    }
    if (str_contains($lower, 'tow') || $subcategory === 'towing') {
        $services = array_merge($services, ['towing', 'roadside assistance']);
    }
    if (str_contains($lower, 'tint') || $subcategory === 'tint') {
        $services[] = 'window tinting';
    }
    if (str_contains($lower, 'audio') || $subcategory === 'audio') {
        $services = array_merge($services, ['car audio', 'speaker installation', 'stereo']);
    }
    if (str_contains($lower, 'transmission') || $subcategory === 'transmission') {
        $services[] = 'transmission repair';
    }
    if (str_contains($lower, 'muffler') || str_contains($lower, 'exhaust') || $subcategory === 'muffler') {
        $services = array_merge($services, ['exhaust repair', 'muffler service']);
    }
    if ($subcategory === 'general_mechanic') {
        $services = array_merge($services, ['general repair', 'diagnostics', 'maintenance']);
    }
    if (str_contains($typeStr, 'car_repair')) {
        if (!in_array('general repair', $services)) $services[] = 'general repair';
    }
    if ($subcategory === 'parts') {
        $services = array_merge($services, ['auto parts', 'batteries', 'accessories']);
    }
    if ($subcategory === 'dealership_service') {
        $services = array_merge($services, ['factory service', 'warranty repair', 'OEM parts']);
    }

    return array_unique($services);
}

// ─── Main Collection Loop ────────────────────────────────────────────────────
$allPlaces = []; // keyed by place_id to deduplicate
$total = count($searches);

echo "Starting Portland Auto Directory collection...\n";
echo "API Key: " . substr($apiKey, 0, 8) . "...\n";
echo "Queries: {$total}\n\n";

foreach ($searches as $i => $search) {
    $num = $i + 1;
    echo "[{$num}/{$total}] Searching: {$search['query']}... ";

    $results = placesTextSearch($search['query'], $apiKey);
    $newCount = 0;

    foreach ($results as $place) {
        $placeId = $place['place_id'] ?? '';
        if (!$placeId || isset($allPlaces[$placeId])) continue;

        // Filter out non-Portland metro (rough lat/lng bounds)
        $lat = $place['geometry']['location']['lat'] ?? 0;
        $lng = $place['geometry']['location']['lng'] ?? 0;
        if ($lat < 45.2 || $lat > 45.7 || $lng < -123.0 || $lng > -122.3) continue;

        // Skip permanently closed
        if (($place['business_status'] ?? '') === 'CLOSED_PERMANENTLY') continue;

        $addr = parseAddress($place['formatted_address'] ?? '');
        $chainName = detectChain($place['name'] ?? '', $chainPatterns);
        $neighborhood = $zipToNeighborhood[$addr['zip']] ?? $addr['city'];

        $allPlaces[$placeId] = [
            'name' => $place['name'] ?? '',
            'category' => $search['category'],
            'subcategory' => $search['subcategory'],
            'address' => $addr['address'],
            'city' => $addr['city'],
            'state' => $addr['state'],
            'zip' => $addr['zip'],
            'neighborhood' => $neighborhood,
            'phone' => null, // filled by details API
            'website' => null,
            'google_rating' => $place['rating'] ?? null,
            'google_review_count' => $place['user_ratings_total'] ?? null,
            'google_place_id' => $placeId,
            'hours' => null,
            'services' => inferServices($place['name'] ?? '', $place['types'] ?? [], $search['subcategory']),
            'brands' => [],
            'chain' => $chainName !== null,
            'chain_name' => $chainName,
            'bilingual' => false,
            'languages' => ['en'],
            'year_established' => null,
            'price_range' => null,
            'notes' => null,
            'lat' => $lat,
            'lng' => $lng,
        ];
        $newCount++;
    }

    echo "found " . count($results) . " results, {$newCount} new\n";

    // Rate limit: ~50ms between queries
    usleep(100000);
}

echo "\n─── Text Search Complete ───\n";
echo "Total unique businesses: " . count($allPlaces) . "\n\n";

// ─── Fetch Details for Top Businesses ────────────────────────────────────────
// Details API costs more, so we fetch details for all businesses but with rate limiting
$detailCount = 0;
$totalPlaces = count($allPlaces);

echo "Fetching details for {$totalPlaces} businesses...\n";

foreach ($allPlaces as $placeId => &$business) {
    $detailCount++;

    if ($detailCount % 25 === 0) {
        echo "  [{$detailCount}/{$totalPlaces}] ...\n";
    }

    $details = placeDetails($placeId, $apiKey);
    if ($details) {
        $business['phone'] = $details['formatted_phone_number'] ?? null;
        $business['website'] = $details['website'] ?? null;

        // Update rating from details (more accurate)
        if (isset($details['rating'])) $business['google_rating'] = (float) $details['rating'];
        if (isset($details['user_ratings_total'])) $business['google_review_count'] = (int) $details['user_ratings_total'];

        // Parse hours
        if (!empty($details['opening_hours'])) {
            $business['hours'] = parseHours($details['opening_hours']);
        }

        // Enrich services from types
        $types = $details['types'] ?? [];
        $business['services'] = array_values(array_unique(
            array_merge($business['services'], inferServices($business['name'], $types, $business['subcategory']))
        ));
    }

    // Rate limit: 100ms between detail requests
    usleep(100000);
}
unset($business);

// ─── Sort and Output ─────────────────────────────────────────────────────────
$output = array_values($allPlaces);

// Sort by category, then name
usort($output, function ($a, $b) {
    $catOrder = ['auto_repair' => 0, 'parts_store' => 1, 'dealership' => 2, 'specialty' => 3];
    $ca = $catOrder[$a['category']] ?? 99;
    $cb = $catOrder[$b['category']] ?? 99;
    if ($ca !== $cb) return $ca - $cb;
    return strcmp($a['name'], $b['name']);
});

// Write JSON
$json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents($outputFile, $json);

// ─── Summary ─────────────────────────────────────────────────────────────────
$categories = [];
$cities = [];
foreach ($output as $b) {
    $categories[$b['category']] = ($categories[$b['category']] ?? 0) + 1;
    $cities[$b['city']] = ($cities[$b['city']] ?? 0) + 1;
}

echo "\n═══ COLLECTION COMPLETE ═══\n";
echo "Total businesses: " . count($output) . "\n";
echo "Output: {$outputFile}\n\n";
echo "By category:\n";
foreach ($categories as $cat => $count) {
    echo "  {$cat}: {$count}\n";
}
echo "\nBy city:\n";
arsort($cities);
foreach (array_slice($cities, 0, 10) as $city => $count) {
    echo "  {$city}: {$count}\n";
}
echo "\nChain vs Independent:\n";
$chains = count(array_filter($output, fn($b) => $b['chain']));
echo "  Chains: {$chains}\n";
echo "  Independent: " . (count($output) - $chains) . "\n";
