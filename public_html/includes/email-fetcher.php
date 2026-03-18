<?php
/**
 * Oregon Tires — IMAP Email Fetcher
 *
 * Connects to IMAP mailbox, fetches unseen emails, threads them into
 * the conversations system, and marks them as read.
 *
 * Uses webklex/php-imap (pure PHP, no ext-imap required).
 */

declare(strict_types=1);

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

class EmailFetcher
{
    private PDO $db;
    private ?object $client = null;

    /** Max emails to process per run (flood protection) */
    private const MAX_PER_RUN = 50;

    /** Max attachment size in bytes (10 MB) */
    private const MAX_ATTACHMENT_SIZE = 10 * 1024 * 1024;

    /** Allowed attachment MIME types */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain', 'text/csv',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Connect to IMAP server using env vars.
     */
    public function connect(): bool
    {
        $host       = $_ENV['IMAP_HOST'] ?? '';
        $port       = (int) ($_ENV['IMAP_PORT'] ?? 993);
        $user       = $_ENV['IMAP_USER'] ?? '';
        $password   = $_ENV['IMAP_PASSWORD'] ?? '';
        $encryption = $_ENV['IMAP_ENCRYPTION'] ?? 'ssl';

        if (!$host || !$user || !$password) {
            error_log('EmailFetcher: Missing IMAP configuration in .env');
            return false;
        }

        try {
            $cm = new ClientManager();
            $this->client = $cm->make([
                'host'          => $host,
                'port'          => $port,
                'encryption'    => $encryption,
                'validate_cert' => true,
                'username'      => $user,
                'password'      => $password,
                'protocol'      => 'imap',
            ]);
            $this->client->connect();
            return true;
        } catch (\Throwable $e) {
            error_log('EmailFetcher: IMAP connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch and process new (unseen) emails.
     *
     * @return int Number of emails processed
     */
    public function fetchNewEmails(): int
    {
        if (!$this->client) {
            error_log('EmailFetcher: Not connected');
            return 0;
        }

        $processed = 0;

        try {
            $folder = $this->client->getFolder('INBOX');
            if (!$folder) {
                error_log('EmailFetcher: Cannot open INBOX');
                return 0;
            }

            $messages = $folder->messages()->unseen()->limit(self::MAX_PER_RUN)->get();

            foreach ($messages as $message) {
                try {
                    if ($this->processEmail($message)) {
                        $processed++;
                    }
                    // Mark as seen regardless (prevents re-processing of unparseable emails)
                    $message->setFlag('Seen');
                } catch (\Throwable $e) {
                    error_log('EmailFetcher: Error processing email: ' . $e->getMessage());
                    // Mark as seen to avoid infinite retry
                    try { $message->setFlag('Seen'); } catch (\Throwable $ignore) {}
                }
            }
        } catch (\Throwable $e) {
            error_log('EmailFetcher: fetchNewEmails error: ' . $e->getMessage());
        }

        return $processed;
    }

    /**
     * Process a single email message.
     */
    private function processEmail(Message $message): bool
    {
        $messageId = trim((string) $message->getMessageId());
        if (!$messageId) {
            $messageId = '<generated-' . bin2hex(random_bytes(16)) . '@oregon.tires>';
        }

        // Dedup: skip if already processed
        $stmt = $this->db->prepare(
            'SELECT id FROM oretir_email_message_ids WHERE message_id_header = ? LIMIT 1'
        );
        $stmt->execute([$messageId]);
        if ($stmt->fetch()) {
            return false; // Already processed
        }

        $fromAddress = '';
        $fromName = '';
        $from = $message->getFrom();
        if ($from && count($from) > 0) {
            $first = $from->first();
            $fromAddress = strtolower(trim($first->mail ?? ''));
            $fromName = trim($first->personal ?? '');
        }

        // Skip emails from ourselves (avoid loops)
        $ourEmail = strtolower(trim($_ENV['IMAP_USER'] ?? ''));
        if ($fromAddress === $ourEmail) {
            return false;
        }

        $subject = trim((string) $message->getSubject()) ?: '(No Subject)';
        $inReplyTo = trim((string) $message->getInReplyTo());
        $references = trim((string) $message->getReferences());

        // Get email body — prefer HTML, fall back to text
        $htmlBody = (string) $message->getHTMLBody();
        $textBody = (string) $message->getTextBody();

        $body = '';
        if ($htmlBody) {
            $body = $this->sanitizeEmailBody($htmlBody);
        } elseif ($textBody) {
            $body = nl2br(htmlspecialchars($textBody, ENT_QUOTES, 'UTF-8'));
        }

        if (trim(strip_tags($body)) === '') {
            $body = '<em>(Empty email body)</em>';
        }

        // Save attachments
        $attachments = $this->saveAttachments($message, 0); // temp conv_id, updated below

        // Find or create conversation
        $conversationId = $this->mapToConversation(
            $fromAddress,
            $fromName,
            $subject,
            $inReplyTo,
            $references,
            $messageId
        );

        if (!$conversationId) {
            error_log("EmailFetcher: Could not map email to conversation. From: {$fromAddress}, Subject: {$subject}");
            return false;
        }

        // Move attachments to correct directory if we saved any with temp conv_id
        if (!empty($attachments)) {
            $attachments = $this->moveAttachments($attachments, $conversationId);
        }

        // Insert the message into conversation
        $attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;
        $senderName = $fromName ?: $fromAddress;

        $this->db->prepare(
            'INSERT INTO oretir_conversation_messages
             (conversation_id, sender_type, sender_name, body, is_read, source, attachments_json, created_at)
             VALUES (?, ?, ?, ?, 0, ?, ?, NOW())'
        )->execute([
            $conversationId,
            'customer',
            $senderName,
            $body,
            'email',
            $attachmentsJson,
        ]);

        $messageDbId = (int) $this->db->lastInsertId();

        // Update conversation last_message_at and status
        $this->db->prepare(
            'UPDATE oretir_conversations SET last_message_at = NOW(), status = ? WHERE id = ?'
        )->execute(['open', $conversationId]);

        // Record email Message-ID for threading
        $this->db->prepare(
            'INSERT INTO oretir_email_message_ids
             (message_id_header, conversation_id, conversation_message_id, direction, in_reply_to, from_email, subject, has_attachments)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $messageId,
            $conversationId,
            $messageDbId,
            'inbound',
            $inReplyTo ?: null,
            $fromAddress,
            substr($subject, 0, 255),
            !empty($attachments) ? 1 : 0,
        ]);

        return true;
    }

    /**
     * Map an inbound email to an existing or new conversation.
     *
     * Threading priority:
     * 1. In-Reply-To header matches a known outbound Message-ID
     * 2. References header contains any known Message-ID
     * 3. Subject matches existing open conversation from same sender
     * 4. Create new conversation
     */
    private function mapToConversation(
        string $fromEmail,
        string $fromName,
        string $subject,
        string $inReplyTo,
        string $references,
        string $messageId
    ): ?int {
        // 1. Check In-Reply-To against known outbound Message-IDs
        if ($inReplyTo) {
            $stmt = $this->db->prepare(
                'SELECT conversation_id FROM oretir_email_message_ids WHERE message_id_header = ? LIMIT 1'
            );
            $stmt->execute([$inReplyTo]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return (int) $row['conversation_id'];
            }
        }

        // 2. Check References header for any known Message-ID
        if ($references) {
            // References is a space-separated list of Message-IDs
            $refIds = preg_split('/\s+/', $references);
            if ($refIds) {
                $placeholders = implode(',', array_fill(0, count($refIds), '?'));
                $stmt = $this->db->prepare(
                    "SELECT conversation_id FROM oretir_email_message_ids WHERE message_id_header IN ({$placeholders}) LIMIT 1"
                );
                $stmt->execute($refIds);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    return (int) $row['conversation_id'];
                }
            }
        }

        // 3. Subject match: strip Re:/Fwd: prefixes and look for open conversations from same sender
        $cleanSubject = $this->cleanSubject($subject);
        if ($cleanSubject && $fromEmail) {
            $stmt = $this->db->prepare(
                "SELECT c.id FROM oretir_conversations c
                 JOIN oretir_customers cust ON c.customer_id = cust.id
                 WHERE cust.email = ?
                   AND c.status IN ('open', 'waiting_reply')
                   AND (c.subject = ? OR c.subject = ?)
                 ORDER BY c.last_message_at DESC
                 LIMIT 1"
            );
            $stmt->execute([$fromEmail, $cleanSubject, $subject]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return (int) $row['id'];
            }
        }

        // 4. Create new conversation
        return $this->createConversationFromEmail($fromEmail, $fromName, $subject, $messageId);
    }

    /**
     * Create a new conversation from an inbound email.
     */
    private function createConversationFromEmail(
        string $fromEmail,
        string $fromName,
        string $subject,
        string $messageId
    ): ?int {
        // Find or create customer
        $nameParts = $this->parseEmailName($fromName, $fromEmail);

        require_once __DIR__ . '/vin-decode.php';
        $customerId = findOrCreateCustomer(
            $fromEmail,
            $nameParts['first'],
            $nameParts['last'],
            '', // no phone from email
            'english', // default language
            $this->db
        );

        if (!$customerId) {
            error_log("EmailFetcher: Could not find/create customer for {$fromEmail}");
            return null;
        }

        $cleanSubject = $this->cleanSubject($subject) ?: $subject;

        $this->db->prepare(
            'INSERT INTO oretir_conversations
             (customer_id, subject, status, source, email_thread_id, last_message_at, created_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        )->execute([
            $customerId,
            substr($cleanSubject, 0, 255),
            'open',
            'email',
            $messageId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Parse a display name into first/last name.
     */
    private function parseEmailName(string $displayName, string $email): array
    {
        if ($displayName) {
            $parts = preg_split('/\s+/', trim($displayName), 2);
            return [
                'first' => $parts[0] ?? '',
                'last'  => $parts[1] ?? '',
            ];
        }

        // Fall back to email local part
        $local = explode('@', $email)[0] ?? 'Unknown';
        return ['first' => ucfirst($local), 'last' => ''];
    }

    /**
     * Strip Re:/Fwd:/etc. prefixes from subject lines.
     */
    private function cleanSubject(string $subject): string
    {
        return trim(preg_replace('/^(Re|Fwd|Fw|RE|FWD|FW)\s*:\s*/i', '', $subject));
    }

    /**
     * Sanitize HTML email body — strip dangerous elements, keep safe formatting.
     */
    public function sanitizeEmailBody(string $html): string
    {
        // Remove script, style, iframe, object, embed tags entirely
        $html = preg_replace('/<(script|style|iframe|object|embed|form|input|button|select|textarea)[^>]*>.*?<\/\1>/is', '', $html);
        $html = preg_replace('/<(script|style|iframe|object|embed|form|input|button|select|textarea)[^>]*\/?>/is', '', $html);

        // Remove event handlers (on*)
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $html);

        // Remove javascript: URLs
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $html);

        // Strip <html>, <head>, <body> wrappers but keep inner content
        $html = preg_replace('/<\/?(html|head|body|meta|title|link)[^>]*>/i', '', $html);

        // Trim excessive whitespace
        $html = trim($html);

        // Limit length (50KB max for stored body)
        if (strlen($html) > 50000) {
            $html = substr($html, 0, 50000) . '<p><em>[Email truncated — original was too long]</em></p>';
        }

        return $html;
    }

    /**
     * Save email attachments to disk.
     *
     * @return array Array of attachment metadata [{name, size, mime, path}]
     */
    private function saveAttachments(Message $message, int $conversationId): array
    {
        $saved = [];
        $attachments = $message->getAttachments();

        if (!$attachments || count($attachments) === 0) {
            return $saved;
        }

        // Use a temp directory, will be moved once we have the conversation ID
        $dir = rtrim($_ENV['UPLOAD_PATH'] ?? dirname(__DIR__) . '/uploads') . '/email-attachments/tmp-' . bin2hex(random_bytes(8));

        foreach ($attachments as $attachment) {
            $size = $attachment->getSize() ?? 0;
            $mime = strtolower($attachment->getMimeType() ?? '');
            $name = $attachment->getName() ?? 'attachment';

            // Skip oversized files
            if ($size > self::MAX_ATTACHMENT_SIZE) {
                $saved[] = [
                    'name' => $name,
                    'size' => $size,
                    'mime' => $mime,
                    'path' => null,
                    'skipped' => 'File too large (max 10MB)',
                ];
                continue;
            }

            // Skip disallowed MIME types
            if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
                continue;
            }

            // Sanitize filename
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
            $safeName = substr($safeName, 0, 200);

            // Ensure unique filename
            $finalName = time() . '_' . $safeName;

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filePath = $dir . '/' . $finalName;
            $content = $attachment->getContent();
            if ($content !== false) {
                file_put_contents($filePath, $content);

                $saved[] = [
                    'name' => $name,
                    'size' => $size,
                    'mime' => $mime,
                    'path' => $filePath,
                    'temp_dir' => $dir,
                ];
            }
        }

        return $saved;
    }

    /**
     * Move attachments from temp directory to final conversation directory.
     */
    private function moveAttachments(array $attachments, int $conversationId): array
    {
        $finalDir = rtrim($_ENV['UPLOAD_PATH'] ?? dirname(__DIR__) . '/uploads') . '/email-attachments/' . $conversationId;

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $result = [];
        foreach ($attachments as $att) {
            if (!empty($att['skipped'])) {
                $result[] = $att;
                continue;
            }

            if (!empty($att['path']) && file_exists($att['path'])) {
                $basename = basename($att['path']);
                $newPath = $finalDir . '/' . $basename;
                rename($att['path'], $newPath);

                $result[] = [
                    'name' => $att['name'],
                    'size' => $att['size'],
                    'mime' => $att['mime'],
                    'path' => 'uploads/email-attachments/' . $conversationId . '/' . $basename,
                ];
            }
        }

        // Clean up temp directory
        foreach ($attachments as $att) {
            if (!empty($att['temp_dir']) && is_dir($att['temp_dir'])) {
                @rmdir($att['temp_dir']); // Only succeeds if empty
            }
        }

        return $result;
    }

    /**
     * Disconnect from IMAP.
     */
    public function disconnect(): void
    {
        if ($this->client) {
            try {
                $this->client->disconnect();
            } catch (\Throwable $e) {
                // Silent
            }
            $this->client = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
