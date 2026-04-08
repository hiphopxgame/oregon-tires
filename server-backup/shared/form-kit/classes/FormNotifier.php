<?php
declare(strict_types=1);

/**
 * FormNotifier — Email notification system for Form Kit
 *
 * Handles admin notification emails and auto-reply emails after
 * form submissions. Supports three mail delivery mechanisms:
 *   1. Site's mail helper (require_once a sendMail function)
 *   2. PHPMailer with SMTP config
 *   3. PHP built-in mail() as fallback
 *
 * Email failures are logged but never throw — they should not
 * break the form submission flow.
 */
class FormNotifier
{
    /**
     * Send admin notification email about a new submission.
     *
     * Builds an HTML email with dark theme and sends it to the
     * configured recipient_email. Sets Reply-To to the submitter's
     * email so admins can reply directly.
     *
     * @param array $submission Submission data (name, email, phone, subject, message, site_key, created_at)
     * @param array $config     Config overrides (recipient_email, subject_prefix, mail_helper_path, etc.)
     * @return bool True if sent successfully, false otherwise
     */
    public static function notifyAdmin(array $submission, array $config = []): bool
    {
        $recipient = $config['recipient_email'] ?? '';
        if ($recipient === '') {
            error_log('[FormNotifier] notifyAdmin: No recipient_email configured');
            return false;
        }

        $subjectPrefix = $config['subject_prefix'] ?? '[Contact]';
        $submitterName = $submission['name'] ?? 'Unknown';
        $submitterSubject = $submission['subject'] ?? '';
        $subject = $subjectPrefix . ' ' . ($submitterSubject !== '' ? $submitterSubject : 'New message from ' . $submitterName);

        $htmlBody = self::buildNotificationHtml($submission);

        return self::sendMail($recipient, $subject, $htmlBody, [
            'from'             => $config['mail_from'] ?? '',
            'from_name'        => $config['mail_from_name'] ?? '',
            'reply_to'         => $submission['email'] ?? '',
            'smtp_config'      => $config['smtp_config'] ?? null,
            'mail_helper_path' => $config['mail_helper_path'] ?? null,
        ]);
    }

    /**
     * Send auto-reply email to the form submitter.
     *
     * Uses the configured auto_reply_subject and auto_reply_body.
     * Only called if config['auto_reply'] is true.
     *
     * @param array $submission Submission data
     * @param array $config     Config with auto_reply_subject and auto_reply_body
     * @return bool True if sent successfully, false otherwise
     */
    public static function sendAutoReply(array $submission, array $config = []): bool
    {
        $to = $submission['email'] ?? '';
        if ($to === '') {
            error_log('[FormNotifier] sendAutoReply: No submitter email');
            return false;
        }

        $subject = $config['auto_reply_subject'] ?? 'Thank you for contacting us';
        $body = $config['auto_reply_body'] ?? '';

        if ($body === '') {
            error_log('[FormNotifier] sendAutoReply: No auto_reply_body configured');
            return false;
        }

        // Replace placeholders in body
        $body = str_replace(
            ['{{name}}', '{{email}}', '{{site_key}}'],
            [
                htmlspecialchars($submission['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($submission['email'] ?? '', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($submission['site_key'] ?? '', ENT_QUOTES, 'UTF-8'),
            ],
            $body
        );

        // Wrap plain text body in simple HTML if not already HTML
        if (stripos($body, '<html') === false && stripos($body, '<body') === false) {
            $body = '<html><body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">'
                  . nl2br($body)
                  . '</body></html>';
        }

        return self::sendMail($to, $subject, $body, [
            'from'             => $config['mail_from'] ?? '',
            'from_name'        => $config['mail_from_name'] ?? '',
            'smtp_config'      => $config['smtp_config'] ?? null,
            'mail_helper_path' => $config['mail_helper_path'] ?? null,
        ]);
    }

    /**
     * Low-level mail delivery.
     *
     * Attempts delivery in this order:
     * 1. If mail_helper_path is set: require_once and call sendMail() from it
     * 2. If smtp_config is set: use PHPMailer directly with SMTP
     * 3. Fallback: PHP's built-in mail() function
     *
     * @param string $to       Recipient email
     * @param string $subject  Email subject
     * @param string $htmlBody HTML email body
     * @param array  $options  Options: from, from_name, reply_to, smtp_config, mail_helper_path
     * @return bool True if sent, false otherwise
     */
    public static function sendMail(string $to, string $subject, string $htmlBody, array $options = []): bool
    {
        $mailHelperPath = $options['mail_helper_path'] ?? null;
        $smtpConfig = $options['smtp_config'] ?? null;
        $from = $options['from'] ?? '';
        $fromName = $options['from_name'] ?? '';
        $replyTo = $options['reply_to'] ?? '';

        // Strategy 1: Site's mail helper function
        if ($mailHelperPath !== null && file_exists($mailHelperPath)) {
            try {
                require_once $mailHelperPath;
                if (function_exists('sendMail')) {
                    // Detect signature: some helpers accept (to, subj, html, textBody, replyTo)
                    // while others accept (to, subj, html, options[])
                    $ref = new \ReflectionFunction('sendMail');
                    $params = $ref->getParameters();
                    $fourthIsArray = isset($params[3]) && $params[3]->hasType() && $params[3]->getType()->getName() === 'array';

                    if ($fourthIsArray) {
                        $result = sendMail($to, $subject, $htmlBody, [
                            'from'      => $from,
                            'from_name' => $fromName,
                            'reply_to'  => $replyTo,
                        ]);
                    } else {
                        // Oregon Tires style: (to, subject, htmlBody, textBody, replyTo)
                        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
                        $result = sendMail($to, $subject, $htmlBody, $textBody, $replyTo);
                    }

                    // Handle both bool and array returns
                    if (is_array($result)) {
                        return !empty($result['success']);
                    }
                    return (bool) $result;
                }
                // Try sendBrandedTemplateEmail if available
                if (function_exists('sendBrandedTemplateEmail')) {
                    $result = sendBrandedTemplateEmail($to, $subject, $htmlBody, [
                        'from'      => $from,
                        'from_name' => $fromName,
                        'reply_to'  => $replyTo,
                    ]);
                    if (is_array($result)) {
                        return !empty($result['success']);
                    }
                    return (bool) $result;
                }
                error_log('[FormNotifier] sendMail: mail helper loaded but no sendMail() or sendBrandedTemplateEmail() found');
            } catch (\Throwable $e) {
                error_log('[FormNotifier] sendMail: mail_helper error: ' . $e->getMessage());
                return false;
            }
        }

        // Strategy 2: PHPMailer with SMTP config
        if ($smtpConfig !== null && is_array($smtpConfig)) {
            try {
                if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                    // Try common autoloader paths
                    $vendorAutoload = dirname(FORM_KIT_PATH) . '/vendor/autoload.php';
                    if (file_exists($vendorAutoload)) {
                        require_once $vendorAutoload;
                    }
                }

                if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = $smtpConfig['host'] ?? '';
                    $mail->Port = (int) ($smtpConfig['port'] ?? 465);
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtpConfig['username'] ?? '';
                    $mail->Password = $smtpConfig['password'] ?? '';

                    // Set encryption
                    $encryption = $smtpConfig['encryption'] ?? 'ssl';
                    if ($encryption === 'ssl' || (int) ($smtpConfig['port'] ?? 465) === 465) {
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    }

                    $mail->setFrom($from ?: $smtpConfig['username'] ?? '', $fromName);
                    $mail->addAddress($to);

                    if ($replyTo !== '') {
                        $mail->addReplyTo($replyTo);
                    }

                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $htmlBody;
                    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

                    $mail->send();
                    return true;
                }

                error_log('[FormNotifier] sendMail: PHPMailer class not found, falling back to mail()');
            } catch (\Throwable $e) {
                error_log('[FormNotifier] sendMail: PHPMailer error: ' . $e->getMessage());
                return false;
            }
        }

        // Strategy 3: PHP built-in mail()
        try {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            if ($from !== '') {
                $headers .= "From: " . ($fromName !== '' ? "{$fromName} <{$from}>" : $from) . "\r\n";
            }
            if ($replyTo !== '') {
                $headers .= "Reply-To: {$replyTo}\r\n";
            }

            return mail($to, $subject, $htmlBody, $headers);
        } catch (\Throwable $e) {
            error_log('[FormNotifier] sendMail: mail() error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build the admin notification HTML email body.
     *
     * Dark themed email with table layout for email client compatibility.
     * Header gradient #111827 to #1f2937 with emerald #10b981 accents.
     *
     * @param array $submission Submission data
     * @return string HTML email body
     */
    private static function buildNotificationHtml(array $submission): string
    {
        $name = htmlspecialchars($submission['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($submission['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($submission['phone'] ?? '', ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($submission['subject'] ?? '', ENT_QUOTES, 'UTF-8');
        $message = nl2br(htmlspecialchars($submission['message'] ?? '', ENT_QUOTES, 'UTF-8'));
        $siteKey = htmlspecialchars($submission['site_key'] ?? '', ENT_QUOTES, 'UTF-8');
        $formType = htmlspecialchars($submission['form_type'] ?? 'contact', ENT_QUOTES, 'UTF-8');
        $timestamp = htmlspecialchars($submission['created_at'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');

        $rows = '';

        // Name
        $rows .= self::buildFieldRow('Name', $name);

        // Email (mailto link)
        if ($email !== '') {
            $rows .= self::buildFieldRow('Email', '<a href="mailto:' . $email . '" style="color: #10b981; text-decoration: none;">' . $email . '</a>');
        }

        // Phone (tel link)
        if ($phone !== '') {
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            $rows .= self::buildFieldRow('Phone', '<a href="tel:' . $cleanPhone . '" style="color: #10b981; text-decoration: none;">' . $phone . '</a>');
        }

        // Subject
        if ($subject !== '') {
            $rows .= self::buildFieldRow('Subject', $subject);
        }

        // Message
        $rows .= self::buildFieldRow('Message', $message);

        // Form Data (extra fields)
        if (!empty($submission['form_data']) && is_array($submission['form_data'])) {
            foreach ($submission['form_data'] as $key => $value) {
                $fieldName = htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $key)), ENT_QUOTES, 'UTF-8');
                $fieldValue = is_array($value) ? htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                $rows .= self::buildFieldRow($fieldName, $fieldValue);
            }
        }

        // Timestamp
        $rows .= self::buildFieldRow('Timestamp', $timestamp);

        // Site
        $rows .= self::buildFieldRow('Site', $siteKey);

        // Form Type
        $rows .= self::buildFieldRow('Form Type', $formType);

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin: 0; padding: 0; background-color: #0f172a; font-family: Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #0f172a;">
<tr><td align="center" style="padding: 24px 16px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

<!-- Header -->
<tr><td style="background: linear-gradient(135deg, #111827, #1f2937); border-radius: 12px 12px 0 0; padding: 32px 24px; text-align: center;">
  <h1 style="margin: 0; color: #10b981; font-size: 22px; font-weight: 700;">New Form Submission</h1>
  <p style="margin: 8px 0 0; color: #9ca3af; font-size: 14px;">' . $siteKey . ' &mdash; ' . $formType . '</p>
</td></tr>

<!-- Body -->
<tr><td style="background-color: #1f2937; padding: 24px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
' . $rows . '
</table>
</td></tr>

<!-- Footer -->
<tr><td style="background-color: #111827; border-radius: 0 0 12px 12px; padding: 16px 24px; text-align: center;">
  <p style="margin: 0; color: #6b7280; font-size: 12px;">Sent by Form Kit v' . (defined('FORM_KIT_VERSION') ? FORM_KIT_VERSION : '1.0.0') . '</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }

    /**
     * Build a single field row for the notification email table.
     *
     * @param string $label Field label
     * @param string $value Field value (may contain HTML)
     * @return string HTML table row
     */
    private static function buildFieldRow(string $label, string $value): string
    {
        return '<tr>
  <td style="padding: 12px 0; border-bottom: 1px solid #374151; vertical-align: top;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td width="120" style="color: #9ca3af; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; vertical-align: top; padding-right: 12px;">' . $label . '</td>
      <td style="color: #e5e7eb; font-size: 15px; line-height: 1.5;">' . $value . '</td>
    </tr>
    </table>
  </td>
</tr>';
    }
}
