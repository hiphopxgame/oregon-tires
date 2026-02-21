<?php
/**
 * Oregon Tires — Email Account Verification
 * Tests both SENDING from contact@oregon.tires and RECEIVING at contact@oregon.tires
 *
 * Usage: php cli/test-email-account.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Oregon Tires — Email Account Test\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "SMTP Host:     " . $_ENV['SMTP_HOST'] . "\n";
echo "SMTP Port:     " . $_ENV['SMTP_PORT'] . "\n";
echo "SMTP User:     " . $_ENV['SMTP_USER'] . "\n";
echo "From Address:  " . $_ENV['SMTP_FROM'] . "\n";
echo "From Name:     " . $_ENV['SMTP_FROM_NAME'] . "\n";
echo "Contact Email: " . ($_ENV['CONTACT_EMAIL'] ?? 'not set') . "\n\n";

$testId = date('His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

// Test 1: Send from contact@oregon.tires to the owner
echo "── Test 1: Send to owner ({$_ENV['CONTACT_EMAIL']}) ──\n";
$result1 = sendTest($_ENV['CONTACT_EMAIL'] ?? '', "Test #{$testId} — Owner Delivery", 1);
echo $result1 ? "  ✅ SENT successfully\n\n" : "  ❌ FAILED\n\n";

// Test 2: Send from contact@oregon.tires to itself (verify mailbox works)
echo "── Test 2: Send to contact@oregon.tires (self) ──\n";
$result2 = sendTest($_ENV['SMTP_FROM'], "Test #{$testId} — Self Delivery", 2);
echo $result2 ? "  ✅ SENT successfully\n\n" : "  ❌ FAILED\n\n";

// Test 3: Send to onevsmany (super admin)
echo "── Test 3: Send to onevsmany@gmail.com ──\n";
$result3 = sendTest('onevsmany@gmail.com', "Test #{$testId} — Admin Delivery", 3);
echo $result3 ? "  ✅ SENT successfully\n\n" : "  ❌ FAILED\n\n";

echo "━━━ Summary ━━━\n";
echo "Owner delivery:  " . ($result1 ? 'PASS' : 'FAIL') . "\n";
echo "Self delivery:   " . ($result2 ? 'PASS' : 'FAIL') . "\n";
echo "Admin delivery:  " . ($result3 ? 'PASS' : 'FAIL') . "\n";
echo "\nCheck all inboxes (and spam folders) for test emails with ID: {$testId}\n";

function sendTest(string $to, string $subject, int $debugLevel): bool
{
    if (empty($to)) {
        echo "  ⚠ No address provided, skipping\n";
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->Port       = (int) $_ENV['SMTP_PORT'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        $mail->SMTPDebug  = ($debugLevel === 1) ? 2 : 0;
        $mail->Debugoutput = function (string $str, int $level) {
            echo "  [SMTP-{$level}] " . trim($str) . "\n";
        };

        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Oregon Tires — ' . $subject;
        $mail->Body    = '<div style="font-family:sans-serif;max-width:500px;margin:0 auto;padding:20px">'
            . '<h2 style="color:#15803d">Oregon Tires Email Test</h2>'
            . '<p>This is a test email to verify the <strong>contact@oregon.tires</strong> email account is functioning correctly.</p>'
            . '<p><strong>Sent:</strong> ' . date('Y-m-d H:i:s T') . '</p>'
            . '<p><strong>To:</strong> ' . htmlspecialchars($to) . '</p>'
            . '<p style="color:#999;font-size:12px">Test ID: ' . htmlspecialchars($subject) . '</p>'
            . '</div>';
        $mail->AltBody = "Oregon Tires Email Test\nSent: " . date('Y-m-d H:i:s T') . "\nTo: {$to}";

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        echo "  Error: " . $e->getMessage() . "\n";
        return false;
    }
}
