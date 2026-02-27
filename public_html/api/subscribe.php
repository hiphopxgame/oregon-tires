<?php
/**
 * Oregon Tires — Email Subscriber API
 * Captures email for newsletter/deals mailing list.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// Only accept POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        jsonError('Invalid JSON input', 400);
    }

    $email = trim($input['email'] ?? '');
    $language = in_array($input['language'] ?? '', ['en', 'es'], true) ? $input['language'] : 'en';
    $source = substr(trim($input['source'] ?? 'website'), 0, 50);

    // Validate email
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonError('Please enter a valid email address.', 422);
    }

    // Rate limiting (5 subscribe attempts per hour per IP)
    if (function_exists('checkRateLimit')) {
        checkRateLimit('subscribe', 5, 3600);
    }

    $pdo = getDB();

    // Check if already subscribed
    $stmt = $pdo->prepare('SELECT id, unsubscribed_at FROM oretir_subscribers WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['unsubscribed_at']) {
            // Re-subscribe
            $stmt = $pdo->prepare('UPDATE oretir_subscribers SET unsubscribed_at = NULL, language = ?, source = ?, subscribed_at = NOW() WHERE id = ?');
            $stmt->execute([$language, $source, $existing['id']]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed.']);
        }
    } else {
        // New subscriber
        $stmt = $pdo->prepare('INSERT INTO oretir_subscribers (email, language, source, subscribed_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$email, $language, $source]);

        // Send welcome email with coupon (new subscribers only)
        try {
            require_once __DIR__ . '/../includes/mail.php';
            sendSubscriberWelcomeEmail($email, $language);
        } catch (\Throwable $mailErr) {
            error_log('subscribe.php: welcome email failed for ' . $email . ': ' . $mailErr->getMessage());
            // Don't fail the subscription if email fails
        }

        echo json_encode(['success' => true, 'message' => 'Successfully subscribed!']);
    }

} catch (\Throwable $e) {
    error_log('subscribe.php error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
