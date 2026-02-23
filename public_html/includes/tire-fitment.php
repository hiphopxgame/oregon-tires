<?php
/**
 * Oregon Tires — Tire Fitment Service
 * Looks up tire fitment data by year/make/model/trim.
 * Uses NHTSA API for vehicle verification + common tire size database.
 * Cache TTL: 90 days.
 */

declare(strict_types=1);

/**
 * Look up tire fitment by year/make/model/trim.
 *
 * @return array{success: bool, data?: array, error?: string}
 */
function lookupTireFitment(string $year, string $make, string $model, string $trim = '', ?PDO $db = null): array
{
    $year  = trim($year);
    $make  = trim($make);
    $model = trim($model);
    $trim  = trim($trim);

    if (empty($year) || empty($make) || empty($model)) {
        return ['success' => false, 'error' => 'Year, make, and model are required.'];
    }

    $db = $db ?? getDB();
    $lookupKey = buildFitmentLookupKey($year, $make, $model, $trim);

    // Check cache (90-day TTL)
    $cached = getFitmentFromCache($lookupKey, $db);
    if ($cached !== null) {
        return ['success' => true, 'data' => $cached, 'cached' => true];
    }

    try {
        $fitmentData = fetchFitmentData($year, $make, $model, $trim);

        if ($fitmentData !== null) {
            cacheFitmentResult($lookupKey, $fitmentData, $db);
            return ['success' => true, 'data' => $fitmentData, 'cached' => false];
        }

        return [
            'success' => true,
            'data' => [
                'tire_sizes'   => [],
                'rim_diameter' => '',
                'bolt_pattern' => '',
                'source'       => 'not_found',
                'message'      => 'Fitment data not found for this vehicle. Please enter tire size manually.',
            ],
            'cached' => false,
        ];

    } catch (\Throwable $e) {
        error_log("Oregon Tires tire fitment error for {$lookupKey}: " . $e->getMessage());
        return ['success' => false, 'error' => 'Tire fitment lookup failed. Please enter tire size manually.'];
    }
}

/**
 * Build a normalized lookup key for caching.
 */
function buildFitmentLookupKey(string $year, string $make, string $model, string $trim): string
{
    return strtolower(implode('|', [
        $year,
        preg_replace('/\s+/', ' ', $make),
        preg_replace('/\s+/', ' ', $model),
        preg_replace('/\s+/', ' ', $trim),
    ]));
}

/**
 * Fetch fitment data using NHTSA vehicle verification + common tire database.
 */
function fetchFitmentData(string $year, string $make, string $model, string $trim): ?array
{
    // Verify vehicle exists via NHTSA
    $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformakeyear/make/'
         . urlencode($make) . '/modelyear/' . urlencode($year) . '?format=json';

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'OregonTires/1.0',
        ],
    ]);

    $response = @file_get_contents($url, false, $ctx);
    if ($response === false) {
        return null;
    }

    $json = json_decode($response, true);
    if (!$json || empty($json['Results'])) {
        return null;
    }

    $modelFound = false;
    foreach ($json['Results'] as $r) {
        if (stripos($r['Model_Name'] ?? '', $model) !== false) {
            $modelFound = true;
            break;
        }
    }

    if (!$modelFound) {
        return null;
    }

    $tireSizes = getCommonTireSizes($year, $make, $model, $trim);

    return [
        'tire_sizes'       => $tireSizes['sizes'] ?? [],
        'rim_diameter'     => $tireSizes['rim_diameter'] ?? '',
        'bolt_pattern'     => $tireSizes['bolt_pattern'] ?? '',
        'source'           => !empty($tireSizes['sizes']) ? 'database' : 'manual_required',
        'vehicle_confirmed' => true,
    ];
}

/**
 * Common tire sizes for popular vehicles (expandable).
 */
function getCommonTireSizes(string $year, string $make, string $model, string $trim): array
{
    $makeNorm  = strtolower(trim($make));
    $modelNorm = strtolower(trim($model));
    $yearInt   = (int) $year;

    $db = [
        'toyota' => [
            'camry'      => ['sizes' => ['215/55R17', '235/45R18'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2018, 2026]],
            'corolla'    => ['sizes' => ['205/55R16', '225/40R18'], 'rim' => '16', 'bolt' => '5x114.3', 'years' => [2019, 2026]],
            'rav4'       => ['sizes' => ['225/65R17', '235/55R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2019, 2026]],
            'tacoma'     => ['sizes' => ['245/75R16', '265/70R16', '265/65R17'], 'rim' => '16', 'bolt' => '6x139.7', 'years' => [2016, 2026]],
            'highlander' => ['sizes' => ['235/65R18', '235/55R20'], 'rim' => '18', 'bolt' => '5x114.3', 'years' => [2020, 2026]],
        ],
        'honda' => [
            'civic'  => ['sizes' => ['215/55R16', '235/40R18'], 'rim' => '16', 'bolt' => '5x114.3', 'years' => [2016, 2026]],
            'accord' => ['sizes' => ['225/50R17', '235/40R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2018, 2026]],
            'cr-v'   => ['sizes' => ['225/65R17', '235/60R18'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2017, 2026]],
            'pilot'  => ['sizes' => ['245/60R18', '245/50R20'], 'rim' => '18', 'bolt' => '5x120', 'years' => [2016, 2026]],
        ],
        'ford' => [
            'f-150'    => ['sizes' => ['265/70R17', '275/65R18', '275/55R20'], 'rim' => '17', 'bolt' => '6x135', 'years' => [2015, 2026]],
            'escape'   => ['sizes' => ['225/65R17', '225/55R19'], 'rim' => '17', 'bolt' => '5x108', 'years' => [2020, 2026]],
            'explorer' => ['sizes' => ['255/65R18', '255/55R20'], 'rim' => '18', 'bolt' => '5x114.3', 'years' => [2020, 2026]],
        ],
        'chevrolet' => [
            'silverado' => ['sizes' => ['265/70R17', '275/60R20', '275/55R22'], 'rim' => '17', 'bolt' => '6x139.7', 'years' => [2019, 2026]],
            'equinox'   => ['sizes' => ['225/65R17', '225/55R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2018, 2026]],
            'malibu'    => ['sizes' => ['225/55R17', '245/45R18'], 'rim' => '17', 'bolt' => '5x115', 'years' => [2016, 2026]],
        ],
        'nissan' => [
            'altima' => ['sizes' => ['215/55R17', '235/40R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2019, 2026]],
            'rogue'  => ['sizes' => ['225/65R17', '225/55R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2021, 2026]],
            'sentra' => ['sizes' => ['205/55R16', '215/45R18'], 'rim' => '16', 'bolt' => '5x114.3', 'years' => [2020, 2026]],
        ],
        'hyundai' => [
            'elantra' => ['sizes' => ['205/55R16', '225/40R18'], 'rim' => '16', 'bolt' => '5x114.3', 'years' => [2021, 2026]],
            'tucson'  => ['sizes' => ['225/60R17', '235/55R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2022, 2026]],
            'sonata'  => ['sizes' => ['215/55R17', '245/40R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2020, 2026]],
        ],
        'kia' => [
            'forte'    => ['sizes' => ['205/55R16', '225/40R18'], 'rim' => '16', 'bolt' => '5x114.3', 'years' => [2019, 2026]],
            'sportage' => ['sizes' => ['225/60R17', '235/55R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2023, 2026]],
            'sorento'  => ['sizes' => ['235/65R17', '235/55R19'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2021, 2026]],
        ],
        'subaru' => [
            'outback'   => ['sizes' => ['225/65R17', '225/60R18'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2020, 2026]],
            'forester'  => ['sizes' => ['225/60R17', '225/55R18'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2019, 2026]],
            'crosstrek' => ['sizes' => ['225/60R17', '225/55R18'], 'rim' => '17', 'bolt' => '5x114.3', 'years' => [2018, 2026]],
        ],
    ];

    if (isset($db[$makeNorm][$modelNorm])) {
        $entry = $db[$makeNorm][$modelNorm];
        if ($yearInt >= $entry['years'][0] && $yearInt <= $entry['years'][1]) {
            return [
                'sizes'        => $entry['sizes'],
                'rim_diameter' => $entry['rim'],
                'bolt_pattern' => $entry['bolt'],
            ];
        }
    }

    return ['sizes' => [], 'rim_diameter' => '', 'bolt_pattern' => ''];
}

/**
 * Get fitment data from cache (90-day TTL).
 */
function getFitmentFromCache(string $lookupKey, PDO $db): ?array
{
    $stmt = $db->prepare(
        'SELECT * FROM oretir_tire_fitment_cache
         WHERE lookup_key = ? AND cached_at > DATE_SUB(NOW(), INTERVAL 90 DAY)
         LIMIT 1'
    );
    $stmt->execute([$lookupKey]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    return [
        'tire_sizes'   => !empty($row['tire_sizes']) ? explode(', ', $row['tire_sizes']) : [],
        'rim_diameter' => $row['rim_diameter'] ?? '',
        'bolt_pattern' => $row['bolt_pattern'] ?? '',
        'source'       => 'cache',
    ];
}

/**
 * Cache fitment result.
 */
function cacheFitmentResult(string $lookupKey, array $data, PDO $db): void
{
    try {
        $tireSizesStr = implode(', ', $data['tire_sizes'] ?? []);
        $rawJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        $stmt = $db->prepare(
            'INSERT INTO oretir_tire_fitment_cache (lookup_key, raw_json, tire_sizes, rim_diameter, bolt_pattern, cached_at)
             VALUES (?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE raw_json = VALUES(raw_json), tire_sizes = VALUES(tire_sizes),
                rim_diameter = VALUES(rim_diameter), bolt_pattern = VALUES(bolt_pattern), cached_at = NOW()'
        );
        $stmt->execute([
            $lookupKey,
            $rawJson,
            $tireSizesStr ?: null,
            $data['rim_diameter'] ?? null,
            $data['bolt_pattern'] ?? null,
        ]);
    } catch (\Throwable $e) {
        error_log("Oregon Tires fitment cache error for {$lookupKey}: " . $e->getMessage());
    }
}
