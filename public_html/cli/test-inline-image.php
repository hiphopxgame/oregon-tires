<?php
/**
 * Oregon Tires — Inline Image Email Test
 * Tests embedded (CID) image support in sendMail().
 *
 * Usage: php cli/test-inline-image.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Oregon Tires — Inline Image Email Test\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$logoPath = realpath(__DIR__ . '/../assets/logo.png');

if ($logoPath === false) {
    echo "❌ Logo file not found at assets/logo.png\n";
    exit(1);
}

echo "Logo path: {$logoPath}\n";
echo "Recipient: tyronenorris@gmail.com\n";
echo "SMTP Host: " . ($_ENV['SMTP_HOST'] ?? 'not set') . "\n";
echo "SMTP From: " . ($_ENV['SMTP_FROM'] ?? 'not set') . "\n\n";

$cid = 'oregon-tires-logo';
$sentAt = date('Y-m-d H:i:s T');

$htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f0fdf4;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;">
<tr><td align="center" style="padding:30px 15px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

  <!-- HEADER with EMBEDDED logo -->
  <tr>
    <td style="background:linear-gradient(135deg,#15803d 0%,#166534 50%,#1a1a2e 100%);padding:0;">
      <div style="height:4px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:32px 30px 24px;">
            <img src="cid:{$cid}" alt="Oregon Tires Auto Care" width="140" style="display:block;max-width:140px;height:auto;margin-bottom:16px;">
            <p style="color:#86efac;font-size:13px;margin:0;letter-spacing:2px;text-transform:uppercase;font-weight:600;">Inline Image Test</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- BODY -->
  <tr>
    <td style="padding:32px 36px;">
      <h2 style="color:#15803d;margin:0 0 16px;font-size:22px;">Embedded Image Test</h2>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">
        This email tests <strong>CID-based inline image embedding</strong>. The Oregon Tires logo in the header above is embedded directly in the email — not loaded from an external URL.
      </p>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">
        If you can see the logo, the inline image feature is working correctly.
      </p>
      <p style="color:#6b7280;font-size:13px;margin:0;">Sent: {$sentAt}</p>
    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td style="background-color:#1a1a2e;padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:24px 30px;">
            <p style="color:#d4a843;font-size:14px;font-weight:700;margin:0 0 6px;">Oregon Tires Auto Care</p>
            <p style="color:#9ca3af;font-size:12px;margin:0;">8536 SE 82nd Ave, Portland, OR 97266</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;

$embeddedImages = [
    [
        'path' => $logoPath,
        'cid'  => $cid,
        'name' => 'logo.png',
    ],
];

echo "Sending test email with embedded logo...\n";

$result = sendMail(
    'tyronenorris@gmail.com',
    'Oregon Tires — Inline Image Test (' . date('H:i:s') . ')',
    $htmlBody,
    'Oregon Tires Inline Image Test - The logo should appear embedded in the email header. Sent: ' . $sentAt,
    '',
    [],
    $embeddedImages
);

if ($result['success']) {
    echo "\n✅ Email sent successfully!\n";
    echo "Check tyronenorris@gmail.com for the test email.\n";
    echo "The logo should appear embedded in the email header.\n";
} else {
    echo "\n❌ Failed to send email.\n";
    echo "Error: " . ($result['error'] ?? 'unknown') . "\n";
    exit(1);
}
