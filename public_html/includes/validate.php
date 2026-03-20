<?php
/**
 * Oregon Tires — Input Validation & Sanitization
 */

declare(strict_types=1);

/**
 * Get JSON body from POST/PUT request.
 */
function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        jsonError('Invalid JSON body.', 400);
    }

    return $data;
}

/**
 * Sanitize a string: trim, strip tags, limit length.
 */
function sanitize(string $value, int $maxLength = 500): string
{
    $value = trim($value);
    $value = strip_tags($value);
    $value = mb_substr($value, 0, $maxLength, 'UTF-8');
    return $value;
}

/**
 * Validate an email address.
 */
function isValidEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate a US phone number (must be exactly 10 digits).
 * Strips all non-digit characters first, then strips leading '1' country code.
 */
function isValidPhone(string $phone): bool
{
    $digits = preg_replace('/\D/', '', $phone);
    // Strip leading US country code
    if (strlen($digits) === 11 && $digits[0] === '1') {
        $digits = substr($digits, 1);
    }
    return strlen($digits) === 10;
}

/**
 * Format a phone number as (XXX) XXX-XXXX.
 * Returns original string if not a valid 10-digit number.
 */
function formatPhone(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 11 && $digits[0] === '1') {
        $digits = substr($digits, 1);
    }
    if (strlen($digits) !== 10) {
        return $phone;
    }
    return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
}

/**
 * Validate a date string (Y-m-d format, not in the past, not Sunday).
 */
function isValidAppointmentDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        return false;
    }

    $today = new DateTime('today');
    if ($d < $today) {
        return false;
    }

    // No Sundays
    if ((int) $d->format('w') === 0) {
        return false;
    }

    return true;
}

/**
 * Validate time slot (accepts both "07:00" 24h and "7:00 AM" 12h formats).
 * Valid range: 07:00 - 18:00 on the hour or half-hour.
 */
function isValidTimeSlot(string $time): bool
{
    // Accept 24-hour format (HH:MM) — 15-minute intervals
    if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) {
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($min % 15 !== 0) return false;
        if ($h < 7 || $h > 18) return false;
        if ($h === 18 && $min > 0) return false;
        return true;
    }

    // Also accept 12-hour format (H:MM AM/PM) for backwards compatibility
    $validSlots = [];
    for ($h = 7; $h <= 18; $h++) {
        $ampm = $h >= 12 ? 'PM' : 'AM';
        $hour12 = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
        foreach ([0, 15, 30, 45] as $min) {
            if ($h === 18 && $min > 0) break;
            $validSlots[] = "{$hour12}:" . str_pad((string) $min, 2, '0', STR_PAD_LEFT) . " {$ampm}";
        }
    }
    return in_array($time, $validSlots, true);
}

/**
 * Format a 24-hour time string (HH:MM) for display as 12-hour with AM/PM.
 */
function formatTimeDisplay(string $time): string
{
    $parts = explode(':', $time);
    $hour = (int) ($parts[0] ?? 0);
    $min = $parts[1] ?? '00';
    $suffix = $hour >= 12 ? 'PM' : 'AM';
    $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
    return $displayHour . ':' . $min . ' ' . $suffix;
}

/**
 * Validate service type.
 * Accepts frontend values (tire-installation, brake-service, etc.)
 * and legacy backend values (tires, brakes, etc.).
 */
function isValidService(string $service): bool
{
    $validServices = [
        // Frontend values (book-appointment form)
        'tire-installation', 'tire-repair', 'wheel-alignment',
        'oil-change', 'brake-service', 'tuneup',
        'mechanical-inspection', 'mobile-service', 'roadside-assistance', 'other',
        // Legacy backend values
        'tires', 'brakes', 'alignment', 'suspension',
        'diagnostics', 'ac-service', 'general-repair',
    ];
    return in_array($service, $validServices, true);
}

/**
 * Require specific fields from data array.
 * Returns array of missing field names, or empty if all present.
 */
function requireFields(array $data, array $fields): array
{
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Validate an uploaded image file.
 */
function validateImageUpload(array $file, int $maxSizeMB = 5): string|true
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed with error code ' . $file['error'];
    }

    $maxBytes = $maxSizeMB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return "File too large. Maximum size: {$maxSizeMB}MB.";
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowedMimes, true)) {
        return 'Invalid file type. Allowed: JPEG, PNG, WebP, GIF.';
    }

    return true;
}

/**
 * Parse and validate services from booking data.
 * Accepts `$data['services']` (array) or `$data['service']` (string).
 * Returns a validated array of 1-5 unique service slugs.
 *
 * @return array Validated service slugs
 */
function parseServices(array $data): array
{
    $raw = [];

    if (!empty($data['services']) && is_array($data['services'])) {
        $raw = $data['services'];
    } elseif (!empty($data['service']) && is_string($data['service'])) {
        $raw = [$data['service']];
    }

    // Sanitize and validate each slug
    $valid = [];
    foreach ($raw as $svc) {
        $slug = sanitize((string) $svc, 50);
        if ($slug !== '' && isValidService($slug) && !in_array($slug, $valid, true)) {
            $valid[] = $slug;
        }
    }

    // Enforce max 5 services
    return array_slice($valid, 0, 5);
}

/**
 * Validate a VIN (Vehicle Identification Number).
 * 17 alphanumeric characters, no I, O, or Q.
 */
function isValidVin(string $vin): bool
{
    return (bool) preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin);
}
