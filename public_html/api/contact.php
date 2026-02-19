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

    // ─── Email notification to shop owner ───────────────────────────────────
    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $subject = "New Contact Message from {$h($firstName)} {$h($lastName)}";

    $htmlBody = <<<HTML
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: #1a1a2e; color: #ffffff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
            <h2 style="margin: 0;">New Contact Message</h2>
        </div>
        <div style="background: #ffffff; padding: 24px; border: 1px solid #e0e0e0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 12px; font-weight: bold; color: #555; width: 120px;">Name:</td>
                    <td style="padding: 8px 12px;">{$h($firstName)} {$h($lastName)}</td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Email:</td>
                    <td style="padding: 8px 12px;"><a href="mailto:{$h($email)}">{$h($email)}</a></td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Phone:</td>
                    <td style="padding: 8px 12px;"><a href="tel:{$h($phone)}">{$h($phone)}</a></td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Language:</td>
                    <td style="padding: 8px 12px;">{$h($language)}</td>
                </tr>
            </table>
            <div style="margin-top: 16px; padding: 16px; background: #f5f5f5; border-left: 4px solid #1a1a2e; border-radius: 4px;">
                <strong style="color: #555;">Message:</strong>
                <p style="margin: 8px 0 0; color: #333; line-height: 1.6;">{$h($message)}</p>
            </div>
        </div>
        <div style="background: #f0f0f0; padding: 12px; text-align: center; font-size: 12px; color: #888; border-radius: 0 0 8px 8px;">
            Oregon Tires Auto Care &mdash; Contact Form Submission #{$messageId}
        </div>
    </div>
    HTML;

    $mailResult = notifyOwner($subject, $htmlBody, $email);

    // Log the email attempt
    $logDesc = $mailResult['success']
        ? "Contact form notification sent for message #{$messageId}"
        : "Contact form notification FAILED for message #{$messageId}: " . ($mailResult['error'] ?? 'unknown');
    logEmail('contact_form', $logDesc);

    jsonSuccess(['message_id' => $messageId]);

} catch (\Throwable $e) {
    error_log("Oregon Tires contact.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
