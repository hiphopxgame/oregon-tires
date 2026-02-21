<?php
/**
 * Oregon Tires — Contact Form Endpoint
 * POST /api/contact.php
 *
 * Accepts contact form submissions, validates input,
 * stores in database, and emails the shop owner.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

try {
    requireMethod('POST');

    // Rate limit: 3 per hour per IP
    checkRateLimit('contact', 3, 3600);

    // Parse & validate body
    $data = getJsonBody();

    $missing = requireFields($data, ['first_name', 'last_name', 'email', 'phone', 'message']);
    if (!empty($missing)) {
        jsonError('Missing required fields: ' . implode(', ', $missing));
    }

    // Sanitize
    $firstName = sanitize((string) $data['first_name'], 100);
    $lastName  = sanitize((string) $data['last_name'], 100);
    $email     = sanitize((string) $data['email'], 254);
    $phone     = sanitize((string) $data['phone'], 30);
    $message   = sanitize((string) $data['message'], 2000);
    $language  = sanitize((string) ($data['language'] ?? 'english'), 20);

    // Validate name lengths
    if (mb_strlen($firstName) < 1 || mb_strlen($firstName) > 100) {
        jsonError('First name must be between 1 and 100 characters.');
    }
    if (mb_strlen($lastName) < 1 || mb_strlen($lastName) > 100) {
        jsonError('Last name must be between 1 and 100 characters.');
    }

    // Validate email
    if (!isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    // Validate phone
    if (!isValidPhone($phone)) {
        jsonError('Please provide a valid phone number.');
    }

    // Validate message length
    if (mb_strlen($message) < 10) {
        jsonError('Message must be at least 10 characters.');
    }
    if (mb_strlen($message) > 2000) {
        jsonError('Message must not exceed 2000 characters.');
    }

    // Validate language
    if (!in_array($language, ['english', 'spanish'], true)) {
        $language = 'english';
    }

    // ─── Insert into database ───────────────────────────────────────────────
    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO oretir_contact_messages (first_name, last_name, email, phone, message, status, language, created_at)
         VALUES (:first_name, :last_name, :email, :phone, :message, :status, :language, NOW())'
    );
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name'  => $lastName,
        ':email'      => $email,
        ':phone'      => $phone,
        ':message'    => $message,
        ':status'     => 'new',
        ':language'   => $language,
    ]);

    $messageId = (int) $db->lastInsertId();

    // ─── Email notification to shop owner (branded template) ────────────────
    $mailResult = sendContactNotificationEmail(
        "{$firstName} {$lastName}",
        $email,
        $message
    );

    jsonSuccess(['message_id' => $messageId]);

} catch (\Throwable $e) {
    error_log("Oregon Tires contact.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
