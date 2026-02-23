<?php
/**
 * Oregon Tires — VIN Decode Service
 * Uses NHTSA vPIC API (free, no auth required)
 * Results cached permanently in oretir_vin_cache
 */

declare(strict_types=1);

/**
 * Decode a VIN using NHTSA vPIC API with permanent DB caching.
 *
 * @param string $vin 17-character VIN
 * @param PDO|null $db Optional PDO connection (uses getDB() if null)
 * @return array{success: bool, data?: array, error?: string}
 */
function decodeVin(string $vin, ?PDO $db = null): array
{
    $vin = strtoupper(trim($vin));

    if (!isValidVinFormat($vin)) {
        return ['success' => false, 'error' => 'Invalid VIN format. Must be 17 alphanumeric characters (no I, O, or Q).'];
    }

    $db = $db ?? getDB();

    // Check cache first
    $cached = getVinFromCache($vin, $db);
    if ($cached !== null) {
        return ['success' => true, 'data' => $cached, 'cached' => true];
    }

    // Call NHTSA vPIC API
    try {
        $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/' . urlencode($vin) . '?format=json';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'OregonTires/1.0',
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            return ['success' => false, 'error' => 'Unable to reach VIN decode service. Please try again.'];
        }

        $json = json_decode($response, true);
        if (!$json || empty($json['Results'][0])) {
            return ['success' => false, 'error' => 'Invalid response from VIN decode service.'];
        }

        $result = $json['Results'][0];

        // Check for errors from NHTSA
        $errorCode = (int) ($result['ErrorCode'] ?? 0);
        $errorText = $result['ErrorText'] ?? '';
        if ($errorCode >= 400) {
            return ['success' => false, 'error' => 'VIN not found or invalid: ' . $errorText];
        }

        // Extract key fields
        $decoded = extractVinFields($result);

        // Validate we got meaningful data
        if (empty($decoded['make']) && empty($decoded['model'])) {
            return ['success' => false, 'error' => 'VIN could not be decoded. Please verify and try again.'];
        }

        // Cache permanently
        cacheVinResult($vin, $response, $decoded, $db);

        return ['success' => true, 'data' => $decoded, 'cached' => false];

    } catch (\Throwable $e) {
        error_log("Oregon Tires VIN decode error for {$vin}: " . $e->getMessage());
        return ['success' => false, 'error' => 'VIN decode service error. Please try again later.'];
    }
}

/**
 * Validate VIN format (17 chars, no I/O/Q).
 */
function isValidVinFormat(string $vin): bool
{
    return (bool) preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin);
}

/**
 * Extract relevant fields from NHTSA vPIC response.
 */
function extractVinFields(array $result): array
{
    return [
        'year'          => trim($result['ModelYear'] ?? ''),
        'make'          => trim($result['Make'] ?? ''),
        'model'         => trim($result['Model'] ?? ''),
        'trim_level'    => trim($result['Trim'] ?? ''),
        'engine'        => buildEngineString($result),
        'transmission'  => trim($result['TransmissionStyle'] ?? ''),
        'drive_type'    => trim($result['DriveType'] ?? ''),
        'body_class'    => trim($result['BodyClass'] ?? ''),
        'doors'         => trim($result['Doors'] ?? ''),
        'fuel_type'     => trim($result['FuelTypePrimary'] ?? ''),
        'plant_country' => trim($result['PlantCountry'] ?? ''),
        'vehicle_type'  => trim($result['VehicleType'] ?? ''),
    ];
}

/**
 * Build a human-readable engine string from NHTSA data.
 */
function buildEngineString(array $result): string
{
    $parts = array_filter([
        trim($result['DisplacementL'] ?? '') ? trim($result['DisplacementL']) . 'L' : '',
        trim($result['EngineCylinders'] ?? '') ? trim($result['EngineCylinders']) . '-cyl' : '',
        trim($result['FuelTypePrimary'] ?? ''),
        trim($result['EngineHP'] ?? '') ? trim($result['EngineHP']) . 'hp' : '',
    ]);
    return implode(' ', $parts) ?: trim($result['EngineModel'] ?? '');
}

/**
 * Get decoded VIN data from cache.
 */
function getVinFromCache(string $vin, PDO $db): ?array
{
    $stmt = $db->prepare('SELECT * FROM oretir_vin_cache WHERE vin = ? LIMIT 1');
    $stmt->execute([$vin]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    return [
        'year'          => $row['year'] ?? '',
        'make'          => $row['make'] ?? '',
        'model'         => $row['model'] ?? '',
        'trim_level'    => $row['trim_level'] ?? '',
        'engine'        => $row['engine'] ?? '',
        'transmission'  => $row['transmission'] ?? '',
        'drive_type'    => $row['drive_type'] ?? '',
        'body_class'    => $row['body_class'] ?? '',
        'doors'         => $row['doors'] ?? '',
        'fuel_type'     => $row['fuel_type'] ?? '',
        'plant_country' => $row['plant_country'] ?? '',
        'vehicle_type'  => $row['vehicle_type'] ?? '',
    ];
}

/**
 * Cache VIN decode result permanently.
 */
function cacheVinResult(string $vin, string $rawJson, array $decoded, PDO $db): void
{
    try {
        $stmt = $db->prepare(
            'INSERT INTO oretir_vin_cache
                (vin, raw_json, year, make, model, trim_level, engine, transmission,
                 drive_type, body_class, doors, fuel_type, plant_country, vehicle_type, cached_at)
             VALUES
                (:vin, :raw_json, :year, :make, :model, :trim_level, :engine, :transmission,
                 :drive_type, :body_class, :doors, :fuel_type, :plant_country, :vehicle_type, NOW())
             ON DUPLICATE KEY UPDATE vin = vin'
        );
        $stmt->execute([
            ':vin'           => $vin,
            ':raw_json'      => $rawJson,
            ':year'          => $decoded['year'] ?: null,
            ':make'          => $decoded['make'] ?: null,
            ':model'         => $decoded['model'] ?: null,
            ':trim_level'    => $decoded['trim_level'] ?: null,
            ':engine'        => $decoded['engine'] ?: null,
            ':transmission'  => $decoded['transmission'] ?: null,
            ':drive_type'    => $decoded['drive_type'] ?: null,
            ':body_class'    => $decoded['body_class'] ?: null,
            ':doors'         => $decoded['doors'] ?: null,
            ':fuel_type'     => $decoded['fuel_type'] ?: null,
            ':plant_country' => $decoded['plant_country'] ?: null,
            ':vehicle_type'  => $decoded['vehicle_type'] ?: null,
        ]);
    } catch (\Throwable $e) {
        error_log("Oregon Tires VIN cache error for {$vin}: " . $e->getMessage());
    }
}

/**
 * Find or create a customer record from booking data.
 *
 * @return int|null Customer ID or null on failure
 */
function findOrCreateCustomer(string $email, string $firstName, string $lastName, string $phone, string $language, ?PDO $db = null): ?int
{
    $db = $db ?? getDB();

    try {
        $stmt = $db->prepare('SELECT id FROM oretir_customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $db->prepare(
                'UPDATE oretir_customers SET first_name = ?, last_name = ?, phone = ?, language = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$firstName, $lastName, $phone, $language, $existing['id']]);
            return (int) $existing['id'];
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_customers (first_name, last_name, email, phone, language, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$firstName, $lastName, $email, $phone, $language]);
        return (int) $db->lastInsertId();

    } catch (\Throwable $e) {
        error_log("Oregon Tires findOrCreateCustomer error: " . $e->getMessage());
        return null;
    }
}

/**
 * Find or create a vehicle record from booking data.
 *
 * @return int|null Vehicle ID or null on failure
 */
function findOrCreateVehicle(int $customerId, ?string $year, ?string $make, ?string $model, ?string $vin = null, ?PDO $db = null): ?int
{
    $db = $db ?? getDB();

    if (empty($year) && empty($make) && empty($model) && empty($vin)) {
        return null;
    }

    try {
        if (!empty($vin)) {
            $stmt = $db->prepare('SELECT id FROM oretir_vehicles WHERE vin = ? AND customer_id = ? LIMIT 1');
            $stmt->execute([$vin, $customerId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                return (int) $existing['id'];
            }
        }

        if (!empty($year) && !empty($make) && !empty($model)) {
            $stmt = $db->prepare(
                'SELECT id FROM oretir_vehicles WHERE customer_id = ? AND year = ? AND make = ? AND model = ? LIMIT 1'
            );
            $stmt->execute([$customerId, $year, $make, $model]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                if (!empty($vin)) {
                    $db->prepare('UPDATE oretir_vehicles SET vin = ?, updated_at = NOW() WHERE id = ?')
                       ->execute([$vin, $existing['id']]);
                }
                return (int) $existing['id'];
            }
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_vehicles (customer_id, vin, year, make, model, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$customerId, $vin ?: null, $year ?: null, $make ?: null, $model ?: null]);
        return (int) $db->lastInsertId();

    } catch (\Throwable $e) {
        error_log("Oregon Tires findOrCreateVehicle error: " . $e->getMessage());
        return null;
    }
}

/**
 * Generate a unique RO number (RO-XXXXXXXX format).
 */
function generateRoNumber(?PDO $db = null): string
{
    $db = $db ?? getDB();
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $maxAttempts = 10;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        $bytes = random_bytes(8);
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[ord($bytes[$i]) % strlen($chars)];
        }
        $candidate = 'RO-' . $code;

        $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_repair_orders WHERE ro_number = ?');
        $stmt->execute([$candidate]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $candidate;
        }
    }

    throw new \RuntimeException('Failed to generate unique RO number after ' . $maxAttempts . ' attempts');
}

/**
 * Generate a unique estimate number (ES-XXXXXXXX format).
 */
function generateEstimateNumber(?PDO $db = null): string
{
    $db = $db ?? getDB();
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $maxAttempts = 10;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        $bytes = random_bytes(8);
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[ord($bytes[$i]) % strlen($chars)];
        }
        $candidate = 'ES-' . $code;

        $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_estimates WHERE estimate_number = ?');
        $stmt->execute([$candidate]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $candidate;
        }
    }

    throw new \RuntimeException('Failed to generate unique estimate number after ' . $maxAttempts . ' attempts');
}
