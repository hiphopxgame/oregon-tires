<?php
/**
 * Oregon Tires — Customer Feedback Endpoint
 * POST /api/feedback.php
 *
 * Receives private feedback from customers who rated 1-3 stars.
 * Stores in oretir_contact_messages and notifies the shop owner.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

try {
    requireMethod('POST');

    // Rate limit: 3 submissions per hour per IP
    checkRateLimit('feedback', 3, 3600);

    // Parse JSON body
    $data = getJsonBody();

    // ─── Validate required fields ───────────────────────────────────────────

    if (empty($data['email'])) {
        jsonError('Email is required.');
    }
    if (empty($data['message'])) {
        jsonError('Message is required.');
    }

    // Sanitize inputs
    $email   = sanitize((string) $data['email'],   254);
    $message = sanitize((string) $data['message'], 2000);
    $name    = sanitize((string) ($data['name']   ?? ''), 200);
    $ref     = sanitize((string) ($data['ref']    ?? ''), 50);
    $rating  = isset($data['rating']) ? (int) $data['rating'] : 0;

    // Validate email
    if (!isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    // Validate message length
    if (mb_strlen($message) < 5) {
        jsonError('Message must be at least 5 characters.');
    }
    if (mb_strlen($message) > 2000) {
        jsonError('Message must not exceed 2000 characters.');
    }

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        jsonError('Rating must be between 1 and 5.');
    }

    // ─── Split name into first/last ─────────────────────────────────────────
    $nameParts = explode(' ', trim($name), 2);
    $firstName = $nameParts[0] ?? 'Customer';
    $lastName  = $nameParts[1] ?? '';

    // ─── Prefix message with rating context for storage ─────────────────────
    $refContext  = $ref !== '' ? " (Ref: {$ref})" : '';
    $storedMessage = "[Feedback {$rating}/5{$refContext}] {$message}";

    // ─── Insert into contact_messages ───────────────────────────────────────
    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO oretir_contact_messages
             (first_name, last_name, email, phone, message, status, language, created_at)
         VALUES
             (:first_name, :last_name, :email, :phone, :message, :status, :language, NOW())'
    );
    $stmt->execute([
        ':first_name' => $firstName ?: 'Customer',
        ':last_name'  => $lastName,
        ':email'      => $email,
        ':phone'      => '',
        ':message'    => $storedMessage,
        ':status'     => 'new',
        ':language'   => 'english',
    ]);

    $messageId = (int) $db->lastInsertId();

    // ─── Look up appointment context if ref provided ─────────────────────────
    $appointmentContext = '';
    if ($ref !== '') {
        $apptStmt = $db->prepare(
            'SELECT first_name, last_name, service, preferred_date, preferred_time
               FROM oretir_appointments
              WHERE reference_number = :ref
              LIMIT 1'
        );
        $apptStmt->execute([':ref' => $ref]);
        $appt = $apptStmt->fetch();

        if ($appt) {
            $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
            $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));
            $appointmentContext = <<<HTML
            <tr style="background:#f0fdf4;">
              <td colspan="2" style="padding:8px 12px;font-weight:bold;color:#15803d;">Appointment Context</td>
            </tr>
            <tr>
              <td style="padding:8px 12px;font-weight:bold;color:#555;width:140px;">Reference:</td>
              <td style="padding:8px 12px;">{$h($ref)}</td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:8px 12px;font-weight:bold;color:#555;">Service:</td>
              <td style="padding:8px 12px;">{$h($serviceDisplay)}</td>
            </tr>
            <tr>
              <td style="padding:8px 12px;font-weight:bold;color:#555;">Date:</td>
              <td style="padding:8px 12px;">{$h($appt['preferred_date'])}</td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:8px 12px;font-weight:bold;color:#555;">Time:</td>
              <td style="padding:8px 12px;">{$h($appt['preferred_time'])}</td>
            </tr>
HTML;
        }
    }

    // ─── Send notification email to shop owner ───────────────────────────────
    $contactAddr = $_ENV['CONTACT_EMAIL'] ?? $_ENV['SMTP_FROM'] ?? '';
    if (!empty($contactAddr)) {
        $baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $adminUrl = $baseUrl . '/admin/';

        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        // Build star display for email (plain text stars)
        $starsDisplay = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

        $displayName = trim($name) ?: $email;

        $htmlBody = <<<HTML
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
            <div style="background:linear-gradient(135deg,#15803d,#166534);color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0;">
                <h2 style="margin:0;">Customer Feedback Received</h2>
                <p style="margin:8px 0 0;opacity:0.9;font-size:14px;">Rating: {$h($starsDisplay)} ({$rating}/5)</p>
            </div>
            <div style="background:#fff;padding:24px;border:1px solid #e0e0e0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="background:#f0f9f0;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;width:140px;">From:</td>
                        <td style="padding:8px 12px;">{$h($displayName)}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Email:</td>
                        <td style="padding:8px 12px;"><a href="mailto:{$h($email)}">{$h($email)}</a></td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Rating:</td>
                        <td style="padding:8px 12px;font-size:18px;">{$h($starsDisplay)} <span style="font-size:14px;color:#6b7280;">({$rating}/5)</span></td>
                    </tr>
                    {$appointmentContext}
                </table>
                <div style="margin-top:16px;padding:16px;background:#fef2f2;border-left:4px solid #dc2626;border-radius:4px;">
                    <strong style="color:#dc2626;">Feedback:</strong>
                    <p style="margin:8px 0 0;color:#333;line-height:1.6;white-space:pre-wrap;">{$h($message)}</p>
                </div>
                <div style="margin-top:20px;text-align:center;">
                    <a href="{$h($adminUrl)}" style="display:inline-block;padding:12px 28px;background:#15803d;color:#fff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:14px;">
                        View in Admin Panel
                    </a>
                </div>
            </div>
            <div style="background:#15803d;padding:12px;text-align:center;font-size:12px;color:#fff;border-radius:0 0 8px 8px;">
                Oregon Tires Auto Care — Feedback #ID{$messageId}
            </div>
        </div>
        HTML;

        $textBody  = "CUSTOMER FEEDBACK — Oregon Tires Auto Care\n";
        $textBody .= "==========================================\n\n";
        $textBody .= "From: {$displayName}\n";
        $textBody .= "Email: {$email}\n";
        $textBody .= "Rating: {$rating}/5\n";
        if ($ref !== '') {
            $textBody .= "Reference: {$ref}\n";
        }
        $textBody .= "\nFeedback:\n{$message}\n\n";
        $textBody .= "==========================================\n";
        $textBody .= "View in admin: {$adminUrl}";

        $emailSubject = "\u{1F6A8} Low Rating Feedback ({$rating}/5) from {$displayName}";

        sendMail($contactAddr, $emailSubject, $htmlBody, $textBody, $email);
        logEmail('customer_feedback', "Feedback #{$messageId} from {$email} (rating: {$rating}/5)");
    }

    jsonSuccess(['message_id' => $messageId]);

} catch (\Throwable $e) {
    error_log('Oregon Tires feedback.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
