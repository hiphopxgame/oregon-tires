<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

/**
 * MemberMail — Email helper for verification and password reset
 *
 * Uses PHPMailer with SMTP configuration from .env
 */
class MemberMail
{
    /**
     * Send email verification link.
     */
    public static function sendVerification(string $email, string $token, string $siteName, string $siteUrl): bool
    {
        $verifyUrl = rtrim($siteUrl, '/') . '/api/member/verify-email.php?token=' . urlencode($token);

        $subject = "Verify your email — {$siteName}";

        $html = self::buildEmail($siteName, 'Verify Your Email', [
            'We need to confirm your email address to complete your registration.',
            'Click the button below to verify your email:',
        ], 'Verify Email', $verifyUrl, [
            'This link expires in 30 minutes.',
            'If you did not create an account, you can ignore this email.',
        ]);

        $text = "Verify your email for {$siteName}\n\n"
            . "Click here to verify: {$verifyUrl}\n\n"
            . "This link expires in 30 minutes.\n"
            . "If you did not create an account, you can ignore this email.";

        return self::sendEmail($email, $subject, $html, $text);
    }

    /**
     * Send password reset link.
     */
    public static function sendPasswordReset(string $email, string $token, string $siteName, string $siteUrl): bool
    {
        $resetUrl = rtrim($siteUrl, '/') . '/reset-password/' . urlencode($token);

        $subject = "Reset your password — {$siteName}";

        $html = self::buildEmail($siteName, 'Reset Your Password', [
            'We received a request to reset your password.',
            'Click the button below to set a new password:',
        ], 'Reset Password', $resetUrl, [
            'This link expires in 30 minutes.',
            'If you did not request a password reset, you can ignore this email. Your password will remain unchanged.',
        ]);

        $text = "Reset your password for {$siteName}\n\n"
            . "Click here to reset: {$resetUrl}\n\n"
            . "This link expires in 30 minutes.\n"
            . "If you did not request this, you can ignore this email.";

        return self::sendEmail($email, $subject, $html, $text);
    }

    /**
     * Build a styled HTML email.
     */
    private static function buildEmail(
        string $siteName,
        string $heading,
        array $bodyLines,
        string $ctaText,
        string $ctaUrl,
        array $footerLines
    ): string {
        $bodyHtml = '';
        foreach ($bodyLines as $line) {
            $bodyHtml .= '<p style="margin:0 0 16px 0;color:#cbd5e1;font-size:16px;line-height:1.6;">'
                . htmlspecialchars($line) . '</p>';
        }

        $footerHtml = '';
        foreach ($footerLines as $line) {
            $footerHtml .= '<p style="margin:0 0 8px 0;color:#64748b;font-size:13px;line-height:1.5;">'
                . htmlspecialchars($line) . '</p>';
        }

        $safeCtaUrl = htmlspecialchars($ctaUrl);
        $safeSiteName = htmlspecialchars($siteName);
        $safeHeading = htmlspecialchars($heading);
        $safeCtaText = htmlspecialchars($ctaText);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;padding:40px 20px;">
<tr><td align="center">
<table width="480" cellpadding="0" cellspacing="0" style="max-width:480px;width:100%;background-color:#1e293b;border-radius:12px;border:1px solid #334155;">
<!-- Header -->
<tr><td style="padding:32px 32px 0 32px;text-align:center;">
<h2 style="margin:0 0 8px 0;color:#f1f5f9;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:1px;">{$safeSiteName}</h2>
<h1 style="margin:0 0 24px 0;color:#f1f5f9;font-size:24px;font-weight:700;">{$safeHeading}</h1>
</td></tr>
<!-- Body -->
<tr><td style="padding:0 32px;">
{$bodyHtml}
<!-- CTA Button -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 24px 0;">
<tr><td align="center">
<a href="{$safeCtaUrl}" style="display:inline-block;padding:14px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:600;">{$safeCtaText}</a>
</td></tr>
</table>
<p style="margin:0 0 16px 0;color:#64748b;font-size:12px;word-break:break-all;">
Or copy this link: {$safeCtaUrl}
</p>
</td></tr>
<!-- Footer -->
<tr><td style="padding:16px 32px 32px 32px;border-top:1px solid #334155;">
{$footerHtml}
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * Configure PHPMailer and send an email.
     */
    private static function sendEmail(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        try {
            $mail = new PHPMailer(true);

            // SMTP config: kit-level override (smtp_config) takes precedence over $_ENV
            $smtp = MemberAuth::getConfig('smtp_config') ?? [];
            $host     = $smtp['host']     ?? ($_ENV['SMTP_HOST'] ?? '');
            $port     = (int) ($smtp['port'] ?? ($_ENV['SMTP_PORT'] ?? 465));
            $user     = $smtp['username'] ?? ($smtp['user'] ?? ($_ENV['SMTP_USER'] ?? ''));
            $password = $smtp['password'] ?? ($_ENV['SMTP_PASSWORD'] ?? '');
            $from     = $smtp['from']     ?? ($_ENV['SMTP_FROM'] ?? $user);
            $fromName = $smtp['from_name'] ?? ($_ENV['SMTP_FROM_NAME'] ?? '');

            $mail->isSMTP();
            $mail->Host     = $host;
            $mail->Port     = $port;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $password;

            // Encryption: use PHPMailer constants, never bare strings
            if ($port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($port === 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Debug level from env
            $mail->SMTPDebug = (int) ($_ENV['SMTP_DEBUG'] ?? 0);
            $mail->Debugoutput = function ($str, $level) {
                error_log("PHPMailer [{$level}]: {$str}");
            };

            // Sender
            $mail->setFrom($from, $fromName);

            // Recipient
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('[MemberMail] sendEmail: failed: ' . $e->getMessage());
            return false;
        }
    }
}
