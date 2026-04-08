<?php
declare(strict_types=1);

/**
 * CommerceNotifications — Email notifications for commerce events.
 *
 * Sends order confirmations, receipts, status change notifications.
 * Uses PHPMailer via the shared mail.php helper when available,
 * falls back to PHP's built-in mail() function.
 */
class CommerceNotifications
{
    /**
     * Send order confirmation to customer.
     */
    public static function sendOrderConfirmation(PDO $pdo, string $orderRef, array $config = []): bool
    {
        $order = CommerceOrder::get($pdo, $orderRef);
        if (!$order || empty($order['customer_email'])) return false;

        $siteName = $config['site_name'] ?? 'Our Store';
        $subject = ($config['subject_prefix'] ?? '[Order]') . " Confirmation - {$orderRef}";

        $itemRows = '';
        foreach ($order['line_items'] as $item) {
            $desc = htmlspecialchars($item['description']);
            $qty = (int)$item['quantity'];
            $amt = number_format((float)$item['amount'], 2);
            $itemRows .= "<tr><td style='padding:8px;border-bottom:1px solid #333;color:#ccc;'>{$desc}</td>"
                . "<td style='padding:8px;border-bottom:1px solid #333;color:#ccc;text-align:center;'>{$qty}</td>"
                . "<td style='padding:8px;border-bottom:1px solid #333;color:#ccc;text-align:right;'>\${$amt}</td></tr>";
        }

        $total = number_format((float)$order['total'], 2);
        $name = htmlspecialchars($order['customer_name'] ?? 'Customer');
        $ref = htmlspecialchars($orderRef);

        $body = <<<HTML
<div style="max-width:600px;margin:0 auto;background:#0A0A0A;color:#fff;font-family:Arial,sans-serif;padding:32px;border-radius:12px;">
    <h2 style="color:var(--site-primary,#D4AF37);margin:0 0 8px;">Order Confirmed</h2>
    <p style="color:#9CA3AF;margin:0 0 24px;">Thank you, {$name}!</p>

    <div style="background:#111827;border-radius:8px;padding:16px;margin-bottom:24px;">
        <p style="color:#9CA3AF;margin:0 0 8px;font-size:13px;">Order Reference</p>
        <p style="color:#fff;margin:0;font-size:18px;font-weight:600;">{$ref}</p>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:2px solid #333;">
                <th style="padding:8px;text-align:left;color:#9CA3AF;font-size:13px;">Item</th>
                <th style="padding:8px;text-align:center;color:#9CA3AF;font-size:13px;">Qty</th>
                <th style="padding:8px;text-align:right;color:#9CA3AF;font-size:13px;">Amount</th>
            </tr>
        </thead>
        <tbody>{$itemRows}</tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="padding:12px 8px;text-align:right;font-weight:600;color:#fff;">Total</td>
                <td style="padding:12px 8px;text-align:right;font-weight:600;color:var(--site-primary,#D4AF37);font-size:18px;">\${$total}</td>
            </tr>
        </tfoot>
    </table>

    <p style="color:#9CA3AF;margin:24px 0 0;font-size:13px;">
        If you have any questions, reply to this email or contact us at {$siteName}.
    </p>
</div>
HTML;

        return self::send($order['customer_email'], $subject, $body, $config);
    }

    /**
     * Send notification to business owner about new order.
     */
    public static function sendOwnerNotification(PDO $pdo, string $orderRef, string $ownerEmail, array $config = []): bool
    {
        $order = CommerceOrder::get($pdo, $orderRef);
        if (!$order) return false;

        $subject = "New Order: {$orderRef}";
        $name = htmlspecialchars($order['customer_name'] ?? 'Unknown');
        $email = htmlspecialchars($order['customer_email'] ?? 'N/A');
        $phone = htmlspecialchars($order['customer_phone'] ?? 'N/A');
        $total = number_format((float)$order['total'], 2);
        $ref = htmlspecialchars($orderRef);

        $itemList = '';
        foreach ($order['line_items'] as $item) {
            $desc = htmlspecialchars($item['description']);
            $qty = (int)$item['quantity'];
            $amt = number_format((float)$item['amount'], 2);
            $itemList .= "<li style='color:#ccc;margin:4px 0;'>{$desc} x{$qty} — \${$amt}</li>";
        }

        $body = <<<HTML
<div style="max-width:600px;margin:0 auto;background:#0A0A0A;color:#fff;font-family:Arial,sans-serif;padding:32px;border-radius:12px;">
    <h2 style="color:#D4AF37;margin:0 0 16px;">New Order Received</h2>

    <div style="background:#111827;border-radius:8px;padding:16px;margin-bottom:16px;">
        <p style="margin:4px 0;"><strong>Ref:</strong> {$ref}</p>
        <p style="margin:4px 0;"><strong>Customer:</strong> {$name}</p>
        <p style="margin:4px 0;"><strong>Email:</strong> {$email}</p>
        <p style="margin:4px 0;"><strong>Phone:</strong> {$phone}</p>
        <p style="margin:4px 0;"><strong>Total:</strong> <span style="color:#D4AF37;">\${$total}</span></p>
    </div>

    <h3 style="color:#9CA3AF;margin:0 0 8px;">Items</h3>
    <ul style="padding-left:20px;margin:0 0 16px;">{$itemList}</ul>
</div>
HTML;

        return self::send($ownerEmail, $subject, $body, $config);
    }

    /**
     * Send status update notification to customer.
     */
    public static function sendStatusUpdate(PDO $pdo, string $orderRef, string $newStatus, array $config = []): bool
    {
        $order = CommerceOrder::get($pdo, $orderRef);
        if (!$order || empty($order['customer_email'])) return false;

        $siteName = $config['site_name'] ?? 'Our Store';
        $name = htmlspecialchars($order['customer_name'] ?? 'Customer');
        $ref = htmlspecialchars($orderRef);
        $total = number_format((float)$order['total'], 2);

        $titles = [
            'cancelled' => 'Order Cancelled',
            'refunded'  => 'Refund Processed',
            'processing' => 'Order Being Processed',
        ];
        $descriptions = [
            'cancelled' => 'Your order has been cancelled.',
            'refunded'  => "A refund of \${$total} has been processed for your order. Please allow 5-10 business days for the refund to appear.",
            'processing' => 'Your order is now being processed.',
        ];

        $title = $titles[$newStatus] ?? 'Order Update';
        $description = $descriptions[$newStatus] ?? "Your order status has been updated to: {$newStatus}";
        $subject = ($config['subject_prefix'] ?? '[Order]') . " {$title} - {$orderRef}";

        $body = <<<HTML
<div style="max-width:600px;margin:0 auto;background:#0A0A0A;color:#fff;font-family:Arial,sans-serif;padding:32px;border-radius:12px;">
    <h2 style="color:#D4AF37;margin:0 0 8px;">{$title}</h2>
    <p style="color:#9CA3AF;margin:0 0 24px;">Hi {$name},</p>
    <div style="background:#111827;border-radius:8px;padding:16px;margin-bottom:24px;">
        <p style="color:#9CA3AF;margin:0 0 8px;font-size:13px;">Order Reference</p>
        <p style="color:#fff;margin:0;font-size:18px;font-weight:600;">{$ref}</p>
    </div>
    <p style="color:#ccc;margin:0 0 24px;">{$description}</p>
    <p style="color:#9CA3AF;margin:0;font-size:13px;">
        If you have any questions, contact us at {$siteName}.
    </p>
</div>
HTML;

        return self::send($order['customer_email'], $subject, $body, $config);
    }

    /**
     * Send an email using PHPMailer (if available) or fallback to mail().
     */
    private static function send(string $to, string $subject, string $htmlBody, array $config = []): bool
    {
        // Try PHPMailer via shared mail helper
        $mailPath = $config['mail_helper_path'] ?? null;
        if ($mailPath && file_exists($mailPath)) {
            try {
                require_once $mailPath;
                if (function_exists('sendMail')) {
                    return sendMail($to, $subject, $htmlBody);
                }
            } catch (\Throwable $e) {
                error_log("[CommerceNotifications] PHPMailer send failed: {$e->getMessage()}");
            }
        }

        // Fallback: PHP mail()
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . ($config['from_email'] ?? 'noreply@hiphop.world'),
        ];

        return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }
}
