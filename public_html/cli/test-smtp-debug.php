<?php
/**
 * SMTP Debug Test — sends a simple test email with full SMTP debug output
 */
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;

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

    // FULL DEBUG OUTPUT
    $mail->SMTPDebug  = 3;
    $mail->Debugoutput = function (string $str, int $level) {
        echo "[SMTP-{$level}] " . trim($str) . "\n";
    };

    $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress('onevsmany@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Oregon Tires — SMTP Test ' . date('H:i:s');
    $mail->Body    = '<h2>SMTP Test</h2><p>If you see this, email delivery works.</p><p>Sent: ' . date('Y-m-d H:i:s T') . '</p>';
    $mail->AltBody = 'SMTP Test — If you see this, email delivery works. Sent: ' . date('Y-m-d H:i:s T');

    echo "━━━ Attempting to send test email to onevsmany@gmail.com ━━━\n\n";

    $mail->send();

    echo "\n━━━ PHPMailer reports: SUCCESS ━━━\n";
} catch (\Throwable $e) {
    echo "\n━━━ PHPMailer reports: FAILED ━━━\n";
    echo "Error: " . $e->getMessage() . "\n";
}
