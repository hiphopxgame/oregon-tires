<?php
/**
 * Oregon Tires — License Plate Lookup Endpoint
 * GET /api/plate-lookup.php?plate=ABC1234&state=OR
 *
 * Looks up a license plate via Auto.dev API, caches result,
 * chains into VIN decode for full vehicle data.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/vin-decode.php';

try {
    requireMethod('GET');
    checkRateLimit('plate_lookup', 10, 3600);

    $plate = strtoupper(preg_replace('/[^A-Z0-9]/', '', sanitize((string) ($_GET['plate'] ?? ''), 20)));
    $state = strtoupper(sanitize((string) ($_GET['state'] ?? ''), 5));
    $lang  = sanitize((string) ($_GET['lang'] ?? 'en'), 2);

    if (strlen($plate) < 2 || strlen($plate) > 8) {
        $msg = $lang === 'es'
            ? 'Ingrese un número de placa válido (2-8 caracteres).'
            : 'Please enter a valid license plate (2-8 characters).';
        if (isHtmxRequest()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Vary: HX-Request');
            echo '<p class="text-xs mt-1 text-red-600 dark:text-red-400">' . htmlspecialchars($msg) . '</p>';
            exit;
        }
        jsonError($msg);
    }

    if (!preg_match('/^[A-Z]{2}$/', $state)) {
        $msg = $lang === 'es' ? 'Seleccione un estado válido.' : 'Please select a valid state.';
        if (isHtmxRequest()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Vary: HX-Request');
            echo '<p class="text-xs mt-1 text-red-600 dark:text-red-400">' . htmlspecialchars($msg) . '</p>';
            exit;
        }
        jsonError($msg);
    }

    $db = getDB();
    $vin = null;

    // Check cache first
    $cacheStmt = $db->prepare('SELECT vin FROM oretir_plate_cache WHERE license_plate = ? AND state = ? LIMIT 1');
    $cacheStmt->execute([$plate, $state]);
    $cached = $cacheStmt->fetch(PDO::FETCH_ASSOC);

    if ($cached && !empty($cached['vin'])) {
        $vin = $cached['vin'];
    } else {
        // Call Auto.dev API
        $apiKey = $_ENV['AUTODEV_API_KEY'] ?? '';
        if (empty($apiKey)) {
            error_log('Oregon Tires plate-lookup: AUTODEV_API_KEY not configured');
            $msg = $lang === 'es'
                ? 'Servicio de búsqueda de placas no disponible.'
                : 'Plate lookup service unavailable.';
            if (isHtmxRequest()) {
                header('Content-Type: text/html; charset=utf-8');
                header('Vary: HX-Request');
                echo '<p class="text-xs mt-1 text-red-600 dark:text-red-400">' . htmlspecialchars($msg) . '</p>';
                exit;
            }
            jsonError($msg, 503);
        }

        $url = 'https://auto.dev/api/vin/' . urlencode($plate) . '?apikey=' . urlencode($apiKey) . '&state=' . urlencode($state);

        $ctx = stream_context_create([
            'http' => [
                'timeout'    => 10,
                'user_agent' => 'OregonTires/1.0',
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            $msg = $lang === 'es'
                ? 'No se pudo conectar al servicio de búsqueda. Intente de nuevo.'
                : 'Unable to reach plate lookup service. Please try again.';
            if (isHtmxRequest()) {
                header('Content-Type: text/html; charset=utf-8');
                header('Vary: HX-Request');
                echo '<p class="text-xs mt-1 text-red-600 dark:text-red-400">' . htmlspecialchars($msg) . '</p>';
                exit;
            }
            jsonError($msg);
        }

        $json = json_decode($response, true);

        // Extract VIN from response
        $vin = $json['vin'] ?? null;

        // Cache the result (even if VIN is null, to avoid re-hitting API)
        try {
            $db->prepare(
                'INSERT INTO oretir_plate_cache (license_plate, state, vin, raw_json, cached_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE vin = VALUES(vin), raw_json = VALUES(raw_json), cached_at = NOW()'
            )->execute([$plate, $state, $vin, $response]);
        } catch (\Throwable $e) {
            error_log("Oregon Tires plate cache error: " . $e->getMessage());
        }
    }

    if (empty($vin)) {
        $msg = $lang === 'es'
            ? 'No se encontró un vehículo con esa placa. Intente con el VIN.'
            : 'No vehicle found for that plate. Try entering your VIN instead.';
        if (isHtmxRequest()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Vary: HX-Request');
            $plateSuccess = false;
            $plateError = $msg;
            require __DIR__ . '/../templates/partials/booking-plate-result.php';
            exit;
        }
        jsonError($msg, 404);
    }

    // Chain into VIN decode
    $result = decodeVin($vin, $db);

    if (isHtmxRequest()) {
        header('Content-Type: text/html; charset=utf-8');
        header('Vary: HX-Request');
        $plateSuccess = true;
        $plateVin = $vin;
        $vinSuccess = $result['success'];
        $vinData = $result['data'] ?? null;
        $vinError = $result['error'] ?? null;
        require __DIR__ . '/../templates/partials/booking-plate-result.php';
        exit;
    }

    if ($result['success']) {
        jsonSuccess(array_merge($result['data'], ['vin' => $vin]));
    } else {
        jsonError($result['error'] ?? 'VIN decode failed.', 422);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/plate-lookup.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
