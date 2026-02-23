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
 * Validate a phone number (allows digits, spaces, dashes, parens, plus).
 */
function isValidPhone(string $phone): bool
{
    $digits = preg_replace('/\D/', '', $phone);
    return strlen($digits) >= 7 && strlen($digits) <= 15;
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
    // Accept 24-hour format (HH:MM) from frontend
    if (preg_match('/^(\d{1,2}):([03]0)$/', $time, $m)) {
        $h = (int) $m[1];
        return $h >= 7 && $h <= 18 && !($h === 18 && $m[2] === '30');
    }

    // Also accept 12-hour format (H:MM AM/PM) for backwards compatibility
    $validSlots = [];
    for ($h = 7; $h <= 18; $h++) {
        $ampm = $h >= 12 ? 'PM' : 'AM';
        $hour12 = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
        $validSlots[] = "{$hour12}:00 {$ampm}";
        if ($h < 18) {
            $validSlots[] = "{$hour12}:30 {$ampm}";
        }
    }
    return in_array($time, $validSlots, true);
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
        'mechanical-inspection', 'mobile-service', 'other',
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
 * Validate a VIN (Vehicle Identification Number).
 * 17 alphanumeric characters, no I, O, or Q.
 */
function isValidVin(string $vin): bool
{
    return (bool) preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin);
}
