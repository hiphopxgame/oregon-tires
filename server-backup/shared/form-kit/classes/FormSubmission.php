<?php
declare(strict_types=1);

/**
 * FormSubmission — Form submission CRUD for Form Kit
 *
 * Handles creating, listing, reading, and managing form submissions.
 * Includes built-in honeypot detection, rate limiting, input sanitization,
 * and IP privacy hashing. Executes registered actions after successful
 * submissions (e.g., admin notification, auto-reply).
 */
class FormSubmission
{
    /**
     * Create a new form submission.
     *
     * Flow:
     * 1. Check honeypot field (if filled, return fake success to trap bots)
     * 2. Rate limit check via FormRateLimiter
     * 3. Validate required fields (name, email, message for contact type)
     * 4. Sanitize all input with htmlspecialchars
     * 5. Hash IP with SHA-256 for privacy
     * 6. INSERT into form_submissions table
     * 7. Execute registered actions (notify is built-in)
     * 8. Return success with submission_id and action_results
     *
     * @param string $siteKey Site identifier
     * @param array  $data    Submission data (name, email, phone, subject, message, form_type, form_data, _hp_email)
     * @return array{success: bool, submission_id?: int, message?: string, action_results?: array, error?: string}
     */
    public static function create(string $siteKey, array $data): array
    {
        $config = FormManager::getConfig();
        $honeypotField = $config['honeypot_field'] ?? '_hp_email';

        // 1. Honeypot check — if filled, return fake success (bots fill hidden fields)
        if (!empty($data[$honeypotField] ?? '')) {
            return [
                'success' => true,
                'message' => $config['success_message'] ?? 'Thank you for your message.',
            ];
        }

        // 2. Rate limit check
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $hashedIp = hash('sha256', $ip);
        $rateLimitMax = (int) ($config['rate_limit_max'] ?? 5);
        $rateLimitWindow = (int) ($config['rate_limit_window'] ?? 3600);

        try {
            FormRateLimiter::checkOrFail(
                $siteKey,
                'form_submit',
                $hashedIp,
                $rateLimitMax,
                $rateLimitWindow
            );
        } catch (\RuntimeException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        // 3. Validate required fields
        $formType = trim($data['form_type'] ?? $config['form_type'] ?? 'contact');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $subject = trim($data['subject'] ?? '');
        $message = trim($data['message'] ?? '');
        $formData = $data['form_data'] ?? null;

        if ($name === '') {
            return ['success' => false, 'error' => 'Name is required'];
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'A valid email address is required'];
        }
        if ($formType === 'contact' && $message === '') {
            return ['success' => false, 'error' => 'Message is required'];
        }

        // 4. Sanitize all input
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $formType = htmlspecialchars($formType, ENT_QUOTES, 'UTF-8');

        // Sanitize form_data recursively if present
        $formDataJson = null;
        if ($formData !== null) {
            if (is_array($formData)) {
                $formData = self::sanitizeArray($formData);
                $formDataJson = json_encode($formData);
            } elseif (is_string($formData)) {
                $formDataJson = htmlspecialchars($formData, ENT_QUOTES, 'UTF-8');
            }
        }

        // 5. IP is already hashed above; capture user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (mb_strlen($userAgent) > 512) {
            $userAgent = mb_substr($userAgent, 0, 512);
        }

        // 6. INSERT into form_submissions
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `{$table}`
                    (site_key, form_type, name, email, phone, subject, message, form_data, ip_hash, user_agent, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([
                $siteKey,
                $formType,
                $name,
                $email,
                $phone ?: null,
                $subject ?: null,
                $message,
                $formDataJson,
                $hashedIp,
                $userAgent ?: null,
            ]);
            $submissionId = (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[FormSubmission] create: INSERT error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to save submission'];
        }

        // Record the rate limit entry after successful submission
        FormRateLimiter::record($siteKey, 'form_submit', $hashedIp);

        // Build submission array for actions
        $submission = [
            'id'         => $submissionId,
            'site_key'   => $siteKey,
            'form_type'  => $formType,
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone ?: null,
            'subject'    => $subject ?: null,
            'message'    => $message,
            'form_data'  => $formData,
            'ip_hash'    => $hashedIp,
            'status'     => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // 7. Execute registered actions
        $actionResults = [];

        // Built-in: admin notification
        if (!empty($config['recipient_email'])) {
            $actionResults['notify_admin'] = FormNotifier::notifyAdmin($submission, $config);
        }

        // Built-in: auto-reply
        if (!empty($config['auto_reply']) && !empty($config['auto_reply_subject'])) {
            $actionResults['auto_reply'] = FormNotifier::sendAutoReply($submission, $config);
        }

        // Custom registered actions
        foreach (FormManager::getActions() as $actionName => $handler) {
            try {
                $actionResults[$actionName] = $handler($submission, $config);
            } catch (\Throwable $e) {
                error_log("[FormSubmission] create: action '{$actionName}' error: " . $e->getMessage());
                $actionResults[$actionName] = false;
            }
        }

        // Store action results on the submission record
        if (!empty($actionResults)) {
            try {
                $updateStmt = $pdo->prepare("
                    UPDATE `{$table}` SET action_results = ? WHERE id = ?
                ");
                $updateStmt->execute([json_encode($actionResults), $submissionId]);
            } catch (\Throwable $e) {
                error_log('[FormSubmission] create: action_results UPDATE error: ' . $e->getMessage());
            }
        }

        // 8. Return success
        return [
            'success'        => true,
            'submission_id'  => $submissionId,
            'message'        => $config['success_message'] ?? 'Thank you for your message. We will get back to you soon.',
            'action_results' => $actionResults,
        ];
    }

    /**
     * List form submissions with pagination and filtering.
     *
     * @param string $siteKey Site identifier
     * @param array  $filters Filters: status, form_type, search (name/email/subject), limit (max 200), offset
     * @return array List of submission rows ordered by created_at DESC
     */
    public static function list(string $siteKey, array $filters = []): array
    {
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        $sql = "SELECT * FROM `{$table}` WHERE site_key = ?";
        $params = [$siteKey];

        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        // Filter by form_type
        if (!empty($filters['form_type'])) {
            $sql .= " AND form_type = ?";
            $params[] = $filters['form_type'];
        }

        // Search across name, email, subject
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY created_at DESC";

        // Pagination
        $limit = min((int) ($filters['limit'] ?? 50), 200);
        $offset = max((int) ($filters['offset'] ?? 0), 0);
        if ($limit < 1) {
            $limit = 50;
        }
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            // Decode JSON columns
            foreach ($rows as &$row) {
                $row['form_data'] = $row['form_data'] !== null ? json_decode($row['form_data'], true) : null;
                $row['action_results'] = $row['action_results'] !== null ? json_decode($row['action_results'], true) : null;
            }
            unset($row);

            return $rows;
        } catch (\Throwable $e) {
            error_log('[FormSubmission] list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single submission by ID.
     *
     * @param int $id Submission ID
     * @return array|null Full submission row with decoded JSON columns, or null if not found
     */
    public static function get(int $id): ?array
    {
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        try {
            $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            // Decode JSON columns
            $row['form_data'] = $row['form_data'] !== null ? json_decode($row['form_data'], true) : null;
            $row['action_results'] = $row['action_results'] !== null ? json_decode($row['action_results'], true) : null;

            return $row;
        } catch (\Throwable $e) {
            error_log('[FormSubmission] get: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Count submissions for a site with optional filtering.
     *
     * @param string $siteKey Site identifier
     * @param array  $filters Filters: status, form_type, search
     * @return int
     */
    public static function count(string $siteKey, array $filters = []): int
    {
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE site_key = ?";
        $params = [$siteKey];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['form_type'])) {
            $sql .= " AND form_type = ?";
            $params[] = $filters['form_type'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[FormSubmission] count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark a single submission as read.
     *
     * @param int $id Submission ID
     * @return bool True if updated, false if not found or error
     */
    public static function markRead(int $id): bool
    {
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        try {
            $stmt = $pdo->prepare("UPDATE `{$table}` SET status = 'read' WHERE id = ? AND status = 'new'");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[FormSubmission] markRead: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all new submissions as read for a site.
     *
     * @param string $siteKey Site identifier
     * @return int Number of submissions marked as read
     */
    public static function markAllRead(string $siteKey): int
    {
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        try {
            $stmt = $pdo->prepare("UPDATE `{$table}` SET status = 'read' WHERE site_key = ? AND status = 'new'");
            $stmt->execute([$siteKey]);
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            error_log('[FormSubmission] markAllRead: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Hard delete a submission.
     *
     * @param int $id Submission ID
     * @return bool True if deleted, false if not found or error
     */
    public static function delete(int $id): bool
    {
        $pdo = FormManager::getPdo();
        $table = FormManager::submissionsTable();

        try {
            $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[FormSubmission] delete: ' . $e->getMessage());
            return false;
        }
    }

    // ── Private Helpers ─────────────────────────────────────────────────

    /**
     * Recursively sanitize an array of values with htmlspecialchars.
     *
     * @param array $data Input array
     * @return array Sanitized array
     */
    private static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $safeKey = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
            if (is_array($value)) {
                $sanitized[$safeKey] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$safeKey] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$safeKey] = $value;
            }
        }
        return $sanitized;
    }
}
