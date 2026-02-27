<?php
/**
 * Oregon Tires — Care Plan Enrollment
 * POST /api/care-plan-enroll.php
 *
 * Creates a care plan enrollment record and initiates PayPal payment.
 * Falls back to "pending" status if PayPal is not configured.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('POST');

    // Rate limit: 5 enrollments per hour per IP
    checkRateLimit('care_plan_enroll', 5, 3600);

    $data = getJsonBody();

    $planType = sanitize((string)($data['plan_type'] ?? ''), 20);
    if (!in_array($planType, ['basic', 'standard', 'premium'], true)) {
        jsonError('Invalid plan type', 400);
    }

    $name  = sanitize((string)($data['name'] ?? ''), 200);
    $email = sanitize((string)($data['email'] ?? ''), 254);
    $phone = sanitize((string)($data['phone'] ?? ''), 30);

    if (!$name || !$email) {
        jsonError('Name and email are required', 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonError('Invalid email address', 400);
    }

    // Plan pricing (matches care-plan.php display)
    $plans = [
        'basic'    => ['price' => 19.00, 'name' => 'Basic Care Plan'],
        'standard' => ['price' => 29.00, 'name' => 'Standard Care Plan'],
        'premium'  => ['price' => 49.00, 'name' => 'Premium Care Plan'],
    ];
    $plan = $plans[$planType];

    $db = getDB();

    // Check for existing active or pending subscription
    $existingStmt = $db->prepare(
        'SELECT id FROM oretir_care_plans WHERE customer_email = ? AND status IN (?, ?) LIMIT 1'
    );
    $existingStmt->execute([$email, 'active', 'pending']);
    if ($existingStmt->fetch()) {
        jsonError('You already have an active care plan. Contact us to make changes.', 409);
    }

    // Link to member if logged in
    startSecureSession();
    $memberId = !empty($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;

    // PayPal configuration
    $paypalClientId = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
    $paypalSecret   = $_ENV['PAYPAL_SECRET'] ?? '';
    $paypalMode     = $_ENV['PAYPAL_MODE'] ?? 'sandbox';
    $paypalBase     = $paypalMode === 'live'
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com';

    if (!$paypalClientId || !$paypalSecret) {
        // PayPal not configured — save as pending, return instructions
        $stmt = $db->prepare(
            'INSERT INTO oretir_care_plans (plan_type, status, monthly_price, customer_name, customer_email, customer_phone, member_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$planType, 'pending', $plan['price'], $name, $email, $phone, $memberId]);

        jsonSuccess([
            'enrollment_id' => (int)$db->lastInsertId(),
            'status'        => 'pending',
            'message'       => 'Your enrollment has been received. We will contact you to complete payment setup.',
        ]);
    }

    // Get PayPal access token
    $tokenCh = curl_init($paypalBase . '/v1/oauth2/token');
    curl_setopt_array($tokenCh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_USERPWD        => $paypalClientId . ':' . $paypalSecret,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $tokenResp     = curl_exec($tokenCh);
    $tokenHttpCode = curl_getinfo($tokenCh, CURLINFO_HTTP_CODE);
    curl_close($tokenCh);

    if ($tokenHttpCode !== 200) {
        error_log('PayPal token error: ' . $tokenResp);
        // Fallback to pending
        $stmt = $db->prepare(
            'INSERT INTO oretir_care_plans (plan_type, status, monthly_price, customer_name, customer_email, customer_phone, member_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$planType, 'pending', $plan['price'], $name, $email, $phone, $memberId]);
        jsonSuccess([
            'enrollment_id' => (int)$db->lastInsertId(),
            'status'        => 'pending',
            'message'       => 'Enrollment saved. Payment setup will be completed shortly.',
        ]);
    }

    $tokenData   = json_decode($tokenResp, true);
    $accessToken = $tokenData['access_token'] ?? '';

    $appUrl = $_ENV['APP_URL'] ?? 'https://oregon.tires';

    // Save enrollment record first
    $stmt = $db->prepare(
        'INSERT INTO oretir_care_plans (plan_type, status, monthly_price, customer_name, customer_email, customer_phone, member_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$planType, 'pending', $plan['price'], $name, $email, $phone, $memberId]);
    $enrollmentId = (int)$db->lastInsertId();

    // Create a PayPal checkout order for the first month
    $orderPayload = [
        'intent'         => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => 'careplan_' . $enrollmentId,
            'description'  => $plan['name'] . ' — First Month',
            'amount'       => [
                'currency_code' => 'USD',
                'value'         => number_format($plan['price'], 2, '.', ''),
            ],
        ]],
        'application_context' => [
            'brand_name'  => 'Oregon Tires Auto Care',
            'return_url'  => $appUrl . '/care-plan?enrolled=true&plan=' . $planType,
            'cancel_url'  => $appUrl . '/care-plan?cancelled=true',
            'user_action' => 'PAY_NOW',
        ],
    ];

    $orderCh = curl_init($paypalBase . '/v2/checkout/orders');
    curl_setopt_array($orderCh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($orderPayload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $orderResp     = curl_exec($orderCh);
    $orderHttpCode = curl_getinfo($orderCh, CURLINFO_HTTP_CODE);
    curl_close($orderCh);

    if ($orderHttpCode >= 200 && $orderHttpCode < 300) {
        $orderData     = json_decode($orderResp, true);
        $paypalOrderId = $orderData['id'] ?? '';

        // Save PayPal order ID
        $db->prepare('UPDATE oretir_care_plans SET paypal_subscription_id = ? WHERE id = ?')
           ->execute([$paypalOrderId, $enrollmentId]);

        // Find approval URL
        $approvalUrl = '';
        foreach (($orderData['links'] ?? []) as $link) {
            if ($link['rel'] === 'approve') {
                $approvalUrl = $link['href'];
                break;
            }
        }

        jsonSuccess([
            'enrollment_id'   => $enrollmentId,
            'status'          => 'pending',
            'approval_url'    => $approvalUrl,
            'paypal_order_id' => $paypalOrderId,
        ]);
    } else {
        error_log('PayPal order creation error: ' . $orderResp);
        jsonSuccess([
            'enrollment_id' => $enrollmentId,
            'status'        => 'pending',
            'message'       => 'Enrollment saved. We will contact you to complete payment.',
        ]);
    }

} catch (\Throwable $e) {
    error_log('care-plan-enroll.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
