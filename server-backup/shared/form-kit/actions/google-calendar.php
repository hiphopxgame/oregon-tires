<?php
declare(strict_types=1);

/**
 * GoogleCalendarAction — Google Calendar integration for Form Kit
 *
 * Reusable action plugin that creates Google Calendar events from form
 * submissions. Uses service account auth via JWT (openssl_sign) with
 * zero external dependencies.
 *
 * Usage:
 *   require_once $formKitPath . '/actions/google-calendar.php';
 *   GoogleCalendarAction::register([
 *       'credentials_path' => '/path/to/service-account.json',
 *       'calendar_id'      => 'primary',
 *       'send_invites'     => true,
 *       'timezone'         => 'America/Los_Angeles',
 *       'default_duration' => 60,
 *       'event_title'      => '{name} — {subject}',
 *   ]);
 */
class GoogleCalendarAction
{
    private static array $calConfig = [];
    private static ?string $accessToken = null;
    private static int $tokenExpiry = 0;

    private const TOKEN_URL   = 'https://oauth2.googleapis.com/token';
    private const CALENDAR_API = 'https://www.googleapis.com/calendar/v3';
    private const SCOPE       = 'https://www.googleapis.com/auth/calendar';

    // ── Registration ─────────────────────────────────────────────────────

    /**
     * Register as a Form Kit action.
     *
     * @param array $config Calendar-specific configuration
     */
    public static function register(array $config = []): void
    {
        self::$calConfig = array_merge([
            'credentials_path' => $_ENV['GOOGLE_CALENDAR_CREDENTIALS'] ?? '',
            'calendar_id'      => $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary',
            'send_invites'     => true,
            'timezone'         => 'America/Los_Angeles',
            'default_duration' => 60,
            'event_title'      => '{name} — {subject}',
            'event_color_id'   => null,
            'service_colors'   => [],
        ], $config);

        FormManager::registerAction('google_calendar', [self::class, 'handleSubmission']);
    }

    /**
     * Get current config (for testing/inspection).
     */
    public static function getCalConfig(): array
    {
        return self::$calConfig;
    }

    // ── Action Handler ───────────────────────────────────────────────────

    /**
     * Handle a form submission by creating a Google Calendar event.
     *
     * @param array $submission Submission data from FormSubmission::create()
     * @param array $config     FormManager config
     * @return array Action result
     */
    public static function handleSubmission(array $submission, array $config): array
    {
        $calConfig = self::$calConfig;

        // Skip if not configured
        if (empty($calConfig['credentials_path']) || !file_exists($calConfig['credentials_path'])) {
            return ['success' => false, 'error' => 'Google Calendar credentials not configured'];
        }

        try {
            $event = self::buildEventFromSubmission($submission, $calConfig);
            $result = self::createEvent($event, $calConfig);

            return [
                'success'       => true,
                'event_id'      => $result['id'] ?? null,
                'calendar_link' => $result['htmlLink'] ?? null,
            ];
        } catch (\Throwable $e) {
            error_log('GoogleCalendarAction error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Event Building ───────────────────────────────────────────────────

    /**
     * Build a Google Calendar event from a form submission.
     *
     * @param array $submission Form submission data
     * @param array $calConfig  Calendar config
     * @return array Google Calendar event resource
     */
    public static function buildEventFromSubmission(array $submission, array $calConfig): array
    {
        $title = self::interpolateTemplate(
            $calConfig['event_title'] ?? '{name} — {subject}',
            $submission
        );

        // Build description from submission fields
        $descParts = [];
        if (!empty($submission['name'])) {
            $descParts[] = "Name: {$submission['name']}";
        }
        if (!empty($submission['email'])) {
            $descParts[] = "Email: {$submission['email']}";
        }
        if (!empty($submission['phone'])) {
            $descParts[] = "Phone: {$submission['phone']}";
        }
        if (!empty($submission['message'])) {
            $descParts[] = "\n{$submission['message']}";
        }
        if (!empty($submission['form_data']) && is_array($submission['form_data'])) {
            $descParts[] = "\n--- Additional Info ---";
            foreach ($submission['form_data'] as $key => $val) {
                if (is_string($val) || is_numeric($val)) {
                    $descParts[] = ucfirst(str_replace('_', ' ', (string) $key)) . ": {$val}";
                }
            }
        }

        $timezone = $calConfig['timezone'] ?? 'America/Los_Angeles';
        $duration = (int) ($calConfig['default_duration'] ?? 60);

        // Determine start time from submission data
        $startDt = self::resolveStartTime($submission, $timezone);
        $endDt = $startDt->modify("+{$duration} minutes");

        $event = [
            'summary'     => $title,
            'description' => implode("\n", $descParts),
            'start'       => [
                'dateTime' => $startDt->format('c'),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $endDt->format('c'),
                'timeZone' => $timezone,
            ],
            'source' => [
                'title' => 'Form Kit Submission #' . ($submission['id'] ?? ''),
                'url'   => '',
            ],
        ];

        // Add attendee (customer) if send_invites enabled
        if (!empty($calConfig['send_invites']) && !empty($submission['email'])) {
            $event['attendees'] = [
                [
                    'email'       => $submission['email'],
                    'displayName' => $submission['name'] ?? '',
                ],
            ];
        }

        // Set color if configured
        if (!empty($calConfig['event_color_id'])) {
            $event['colorId'] = (string) $calConfig['event_color_id'];
        }

        // Add Form Kit metadata as extended properties
        $event['extendedProperties'] = [
            'private' => [
                'form_kit_site_key'      => $submission['site_key'] ?? '',
                'form_kit_submission_id'  => (string) ($submission['id'] ?? ''),
                'form_kit_form_type'      => $submission['form_type'] ?? '',
            ],
        ];

        return $event;
    }

    /**
     * Build a Google Calendar event from an appointment (Oregon Tires pattern).
     *
     * @param array $appointment Appointment row from database
     * @param array $calConfig   Calendar config overrides
     * @return array Google Calendar event resource
     */
    public static function buildEventFromAppointment(array $appointment, array $calConfig = []): array
    {
        $calConfig = array_merge(self::$calConfig, $calConfig);
        $timezone  = $calConfig['timezone'] ?? 'America/Los_Angeles';
        $duration  = (int) ($calConfig['default_duration'] ?? 60);

        $service = ucwords(str_replace('-', ' ', $appointment['service'] ?? ''));
        $name    = trim(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? ''));
        $title   = "{$service} — {$name}";

        // Build description
        $descParts = ["Service: {$service}"];
        if (!empty($appointment['reference_number'])) {
            $descParts[] = "Ref: {$appointment['reference_number']}";
        }
        $descParts[] = "Customer: {$name}";
        if (!empty($appointment['email'])) {
            $descParts[] = "Email: {$appointment['email']}";
        }
        if (!empty($appointment['phone'])) {
            $descParts[] = "Phone: {$appointment['phone']}";
        }

        // Vehicle info
        $vehicleParts = array_filter([
            $appointment['vehicle_year'] ?? '',
            $appointment['vehicle_make'] ?? '',
            $appointment['vehicle_model'] ?? '',
        ]);
        if (!empty($vehicleParts)) {
            $descParts[] = "Vehicle: " . implode(' ', $vehicleParts);
        }

        if (!empty($appointment['notes'])) {
            $descParts[] = "\nNotes: {$appointment['notes']}";
        }

        // Start time from preferred_date + preferred_time
        $dateStr = $appointment['preferred_date'] ?? date('Y-m-d');
        $timeStr = $appointment['preferred_time'] ?? '09:00';
        $startDt = new \DateTimeImmutable("{$dateStr} {$timeStr}", new \DateTimeZone($timezone));
        $endDt   = $startDt->modify("+{$duration} minutes");

        $event = [
            'summary'     => $title,
            'description' => implode("\n", $descParts),
            'start'       => [
                'dateTime' => $startDt->format('c'),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $endDt->format('c'),
                'timeZone' => $timezone,
            ],
        ];

        // Add attendee
        if (!empty($calConfig['send_invites']) && !empty($appointment['email'])) {
            $event['attendees'] = [
                [
                    'email'       => $appointment['email'],
                    'displayName' => $name,
                ],
            ];
        }

        // Per-service color
        $serviceColors = $calConfig['service_colors'] ?? [];
        $rawService = $appointment['service'] ?? '';
        if (!empty($serviceColors[$rawService])) {
            $event['colorId'] = (string) $serviceColors[$rawService];
        }

        // Extended properties
        $event['extendedProperties'] = [
            'private' => [
                'appointment_id'  => (string) ($appointment['id'] ?? ''),
                'reference_number' => $appointment['reference_number'] ?? '',
                'service'          => $rawService,
            ],
        ];

        return $event;
    }

    // ── Calendar API ─────────────────────────────────────────────────────

    /**
     * Create a calendar event.
     *
     * @param array $event     Google Calendar event resource
     * @param array $calConfig Calendar config (needs credentials_path, calendar_id)
     * @return array Created event data from API
     */
    public static function createEvent(array $event, array $calConfig = []): array
    {
        $calConfig = array_merge(self::$calConfig, $calConfig);
        $calendarId = urlencode($calConfig['calendar_id'] ?? 'primary');
        $sendUpdates = !empty($calConfig['send_invites']) ? 'all' : 'none';

        $url = self::CALENDAR_API . "/calendars/{$calendarId}/events?sendUpdates={$sendUpdates}";

        return self::apiRequest('POST', $url, $event, $calConfig);
    }

    /**
     * Update an existing calendar event.
     *
     * @param string $eventId   Google Calendar event ID
     * @param array  $event     Updated event data
     * @param array  $calConfig Calendar config
     * @return array Updated event data from API
     */
    public static function updateEvent(string $eventId, array $event, array $calConfig = []): array
    {
        $calConfig = array_merge(self::$calConfig, $calConfig);
        $calendarId = urlencode($calConfig['calendar_id'] ?? 'primary');
        $sendUpdates = !empty($calConfig['send_invites']) ? 'all' : 'none';

        $url = self::CALENDAR_API . "/calendars/{$calendarId}/events/{$eventId}?sendUpdates={$sendUpdates}";

        return self::apiRequest('PUT', $url, $event, $calConfig);
    }

    /**
     * Delete a calendar event.
     *
     * @param string $eventId   Google Calendar event ID
     * @param array  $calConfig Calendar config
     * @return bool True if deleted
     */
    public static function deleteEvent(string $eventId, array $calConfig = []): bool
    {
        $calConfig = array_merge(self::$calConfig, $calConfig);
        $calendarId = urlencode($calConfig['calendar_id'] ?? 'primary');
        $sendUpdates = !empty($calConfig['send_invites']) ? 'all' : 'none';

        $url = self::CALENDAR_API . "/calendars/{$calendarId}/events/{$eventId}?sendUpdates={$sendUpdates}";

        try {
            self::apiRequest('DELETE', $url, null, $calConfig);
            return true;
        } catch (\Throwable $e) {
            // 410 Gone or 404 Not Found = already deleted
            if (str_contains($e->getMessage(), '410') || str_contains($e->getMessage(), '404')) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Mark an event as cancelled (update status instead of delete).
     *
     * @param string $eventId   Google Calendar event ID
     * @param array  $calConfig Calendar config
     * @return array Updated event data
     */
    public static function cancelEvent(string $eventId, array $calConfig = []): array
    {
        return self::updateEvent($eventId, ['status' => 'cancelled'], $calConfig);
    }

    // ── JWT Auth ─────────────────────────────────────────────────────────

    /**
     * Get a valid access token, refreshing if needed.
     *
     * @param array $calConfig Config with credentials_path
     * @return string Access token
     * @throws \RuntimeException If auth fails
     */
    public static function getAccessToken(array $calConfig = []): string
    {
        $calConfig = array_merge(self::$calConfig, $calConfig);

        // Return cached token if still valid (5-minute buffer)
        if (self::$accessToken !== null && time() < (self::$tokenExpiry - 300)) {
            return self::$accessToken;
        }

        $credentialsPath = $calConfig['credentials_path'] ?? '';
        if (empty($credentialsPath) || !file_exists($credentialsPath)) {
            throw new \RuntimeException('Google service account credentials file not found: ' . $credentialsPath);
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \RuntimeException('Invalid service account credentials: missing client_email or private_key');
        }

        $now = time();
        $jwt = self::createJwt(
            $credentials['client_email'],
            $credentials['private_key'],
            self::SCOPE,
            $now,
            $now + 3600
        );

        // Exchange JWT for access token
        $tokenData = self::exchangeJwtForToken($jwt);

        self::$accessToken = $tokenData['access_token'];
        self::$tokenExpiry = $now + (int) ($tokenData['expires_in'] ?? 3600);

        return self::$accessToken;
    }

    /**
     * Create a signed JWT for Google service account auth.
     *
     * @param string $email      Service account email
     * @param string $privateKey PEM-encoded private key
     * @param string $scope      API scope
     * @param int    $iat        Issued at timestamp
     * @param int    $exp        Expiration timestamp
     * @return string Signed JWT
     */
    public static function createJwt(
        string $email,
        string $privateKey,
        string $scope,
        int $iat,
        int $exp
    ): string {
        $header = self::base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $claims = self::base64UrlEncode(json_encode([
            'iss'   => $email,
            'scope' => $scope,
            'aud'   => self::TOKEN_URL,
            'iat'   => $iat,
            'exp'   => $exp,
        ]));

        $signingInput = "{$header}.{$claims}";

        $signature = '';
        $key = openssl_pkey_get_private($privateKey);
        if ($key === false) {
            throw new \RuntimeException('Failed to parse private key: ' . openssl_error_string());
        }

        $signed = openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);
        if (!$signed) {
            throw new \RuntimeException('Failed to sign JWT: ' . openssl_error_string());
        }

        return $signingInput . '.' . self::base64UrlEncode($signature);
    }

    /**
     * Exchange a JWT for an access token.
     *
     * @param string $jwt Signed JWT
     * @return array Token response with access_token, token_type, expires_in
     */
    public static function exchangeJwtForToken(string $jwt): array
    {
        $postData = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('Token exchange failed: ' . $curlError);
        }

        $data = json_decode($response, true);
        if ($httpCode !== 200 || empty($data['access_token'])) {
            $errorMsg = $data['error_description'] ?? $data['error'] ?? 'Unknown error';
            throw new \RuntimeException("Token exchange failed (HTTP {$httpCode}): {$errorMsg}");
        }

        return $data;
    }

    // ── HTTP ─────────────────────────────────────────────────────────────

    /**
     * Make an authenticated API request to Google Calendar.
     *
     * @param string     $method    HTTP method
     * @param string     $url       Full API URL
     * @param array|null $body      Request body (JSON-encoded)
     * @param array      $calConfig Calendar config
     * @return array Response data
     */
    public static function apiRequest(string $method, string $url, ?array $body, array $calConfig = []): array
    {
        $token = self::getAccessToken($calConfig);

        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
        ]);

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("Google Calendar API request failed: {$curlError}");
        }

        // DELETE returns 204 No Content
        if ($method === 'DELETE' && $httpCode === 204) {
            return ['deleted' => true];
        }

        $data = json_decode($response, true) ?? [];

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            throw new \RuntimeException("Google Calendar API error ({$httpCode}): {$errorMsg}");
        }

        return $data;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Resolve start time from submission data.
     * Looks for date/time fields in form_data, falls back to created_at.
     */
    private static function resolveStartTime(array $submission, string $timezone): \DateTimeImmutable
    {
        $tz = new \DateTimeZone($timezone);
        $formData = $submission['form_data'] ?? [];

        // Check for date/time fields
        $date = $formData['preferred_date'] ?? $formData['date'] ?? $formData['event_date'] ?? null;
        $time = $formData['preferred_time'] ?? $formData['time'] ?? $formData['event_time'] ?? null;

        if ($date !== null) {
            $dateStr = $date . ($time ? ' ' . $time : ' 09:00');
            try {
                return new \DateTimeImmutable($dateStr, $tz);
            } catch (\Exception $e) {
                // Fall through to default
            }
        }

        // Default: use created_at or now
        $createdAt = $submission['created_at'] ?? date('Y-m-d H:i:s');
        return new \DateTimeImmutable($createdAt, $tz);
    }

    /**
     * Interpolate template variables like {name}, {subject}, {email}.
     */
    private static function interpolateTemplate(string $template, array $data): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($data) {
            $key = $matches[1];
            return (string) ($data[$key] ?? $matches[0]);
        }, $template);
    }

    /**
     * Base64 URL-safe encoding (no padding).
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Generate iCalendar (.ics) content from an event array.
     *
     * @param array $event Google Calendar event resource
     * @return string RFC 5545 iCalendar content
     */
    public static function generateIcsContent(array $event): string
    {
        $uid = bin2hex(random_bytes(16)) . '@formkit';
        $now = gmdate('Ymd\THis\Z');

        $startDt = new \DateTimeImmutable($event['start']['dateTime']);
        $endDt   = new \DateTimeImmutable($event['end']['dateTime']);
        $dtStart = $startDt->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
        $dtEnd   = $endDt->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');

        $summary     = self::icsEscape($event['summary'] ?? '');
        $description = self::icsEscape($event['description'] ?? '');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//FormKit//GoogleCalendarAction//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:REQUEST',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$now}",
            "DTSTART:{$dtStart}",
            "DTEND:{$dtEnd}",
            "SUMMARY:{$summary}",
            "DESCRIPTION:{$description}",
            'STATUS:CONFIRMED',
        ];

        if (!empty($event['attendees'])) {
            foreach ($event['attendees'] as $attendee) {
                $email = $attendee['email'] ?? '';
                $name  = $attendee['displayName'] ?? '';
                if ($email) {
                    $lines[] = "ATTENDEE;CN={$name};RSVP=TRUE:mailto:{$email}";
                }
            }
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';
        $lines[] = '';

        return implode("\r\n", $lines);
    }

    /**
     * Generate a Google Calendar "Add to Calendar" URL.
     *
     * @param array $event Google Calendar event resource
     * @return string Google Calendar URL
     */
    public static function generateGoogleCalendarUrl(array $event): string
    {
        $startDt = new \DateTimeImmutable($event['start']['dateTime']);
        $endDt   = new \DateTimeImmutable($event['end']['dateTime']);

        $start = $startDt->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
        $end   = $endDt->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');

        $params = [
            'action'  => 'TEMPLATE',
            'text'    => $event['summary'] ?? '',
            'dates'   => "{$start}/{$end}",
            'details' => $event['description'] ?? '',
        ];

        if (!empty($event['location'])) {
            $params['location'] = $event['location'];
        }

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    /**
     * Escape a string for iCalendar format.
     */
    private static function icsEscape(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace("\r", '', $text);
        return $text;
    }

    /**
     * Reset state (for testing).
     */
    public static function reset(): void
    {
        self::$calConfig = [];
        self::$accessToken = null;
        self::$tokenExpiry = 0;
    }
}
