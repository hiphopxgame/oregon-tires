<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'dkim':
        // Try to find DKIM key from cPanel
        $home = dirname(__DIR__);
        $dkimPaths = [
            "$home/.dkim/default.pub",
            "/var/cpanel/domain_keys/oregon.tires",
            "$home/etc/oregon.tires/dkim.public",
        ];
        foreach ($dkimPaths as $p) {
            if (file_exists($p)) {
                echo "Found DKIM at: $p\n";
                echo file_get_contents($p) . "\n";
            }
        }
        // Try OpenDKIM
        exec("cat /etc/opendkim/keys/oregon.tires/default.txt 2>&1", $out);
        if (!empty($out)) echo "OpenDKIM:\n" . implode("\n", $out) . "\n";

        // Try cPanel DKIM via command
        exec("/usr/local/cpanel/bin/dkim_keys_installed 2>&1", $out2);
        if (!empty($out2)) echo "cPanel DKIM:\n" . implode("\n", $out2) . "\n";

        // Check if there's a DKIM record in cPanel's zone
        exec("cat /var/named/oregon.tires.db 2>&1", $out3);
        if (!empty($out3)) echo "Zone:\n" . implode("\n", array_slice($out3, 0, 5)) . "\n";

        if (empty(array_filter([$out, $out2, $out3]))) {
            echo "No DKIM key found via standard paths.\n";
            echo "You may need to enable DKIM in cPanel → Email → Email Deliverability\n";
        }
        break;

    case 'test-smtp-bluehost':
        // Test sending via BlueHost's mail server (mail.oregon.tires / localhost)
        require_once __DIR__ . '/vendor/autoload.php';

        // Load env
        $envFile = dirname(__DIR__) . '/.env.oregon-tires';
        $dotenv = Dotenv\Dotenv::createImmutable(dirname($envFile), basename($envFile));
        $dotenv->load();

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'localhost'; // BlueHost local SMTP
            $mail->Port = 25;
            $mail->SMTPAuth = false; // Local delivery, no auth needed
            $mail->SMTPAutoTLS = false;

            $mail->setFrom('contact@oregon.tires', 'Oregon Tires Auto Care');
            $mail->addAddress($_ENV['CONTACT_EMAIL'] ?? 'oregontirespdx@gmail.com');
            $mail->Subject = 'Oregon Tires — BlueHost Email Test (localhost)';
            $mail->isHTML(true);
            $mail->Body = '<h2>BlueHost Local SMTP Test</h2><p>If you receive this, BlueHost localhost SMTP works.</p><p>Sent: ' . date('Y-m-d H:i:s T') . '</p>';
            $mail->AltBody = 'BlueHost Local SMTP Test. Sent: ' . date('Y-m-d H:i:s T');

            $mail->send();
            echo "localhost:25 — SUCCESS\n";
        } catch (\Throwable $e) {
            echo "localhost:25 — FAILED: " . $e->getMessage() . "\n";
        }

        // Test via mail.oregon.tires with SSL
        $mail2 = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail2->isSMTP();
            $mail2->Host = 'mail.oregon.tires';
            $mail2->Port = 465;
            $mail2->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail2->SMTPAuth = true;
            $mail2->Username = $_ENV['SMTP_USER'] ?? 'contact@oregon.tires';
            $mail2->Password = $_ENV['SMTP_PASSWORD'] ?? '';

            $mail2->setFrom('contact@oregon.tires', 'Oregon Tires Auto Care');
            $mail2->addAddress($_ENV['CONTACT_EMAIL'] ?? 'oregontirespdx@gmail.com');
            $mail2->Subject = 'Oregon Tires — BlueHost Email Test (mail.oregon.tires)';
            $mail2->isHTML(true);
            $mail2->Body = '<h2>BlueHost mail.oregon.tires SMTP Test</h2><p>If you receive this, BlueHost authenticated SMTP works.</p><p>Sent: ' . date('Y-m-d H:i:s T') . '</p>';
            $mail2->AltBody = 'BlueHost mail.oregon.tires SMTP Test. Sent: ' . date('Y-m-d H:i:s T');

            $mail2->send();
            echo "mail.oregon.tires:465 — SUCCESS\n";
        } catch (\Throwable $e) {
            echo "mail.oregon.tires:465 — FAILED: " . $e->getMessage() . "\n";
        }
        break;

    case 'cleanup':
        @unlink(__FILE__);
        echo "Removed.";
        break;

    default:
        echo "Actions: dkim, test-smtp-bluehost, cleanup";
}
