<?php
/**
 * Oregon Tires — Google Business Profile Management
 * Uses service account for GBP API (My Business API).
 * Requires the service account to be added as a manager on the business profile.
 */

declare(strict_types=1);

/**
 * Get an authenticated Google My Business client.
 * Reuses the same service account JSON from Google Calendar.
 */
function getGbpClient(): ?\Google\Client
{
    static $client = null;
    if ($client !== null) return $client;

    $jsonPath = $_ENV['GOOGLE_SERVICE_ACCOUNT_JSON'] ?? '';
    if ($jsonPath === '') return null;

    if ($jsonPath[0] !== '/') {
        $jsonPath = dirname(__DIR__) . '/' . $jsonPath;
    }

    if (!file_exists($jsonPath)) {
        error_log("GBP: service account JSON not found at {$jsonPath}");
        return null;
    }

    try {
        $client = new \Google\Client();
        $client->setAuthConfig($jsonPath);
        $client->addScope('https://www.googleapis.com/auth/business.manage');
        return $client;
    } catch (\Throwable $e) {
        error_log("GBP: auth failed — " . $e->getMessage());
        return null;
    }
}

/**
 * Make an authenticated request to the GBP API.
 */
function gbpApiRequest(string $method, string $url, ?array $body = null): ?array
{
    $client = getGbpClient();
    if (!$client) return null;

    try {
        $httpClient = $client->authorize();
        $options = ['headers' => ['Content-Type' => 'application/json']];
        if ($body !== null) {
            $options['body'] = json_encode($body);
        }

        $response = $httpClient->request($method, $url, $options);
        $data = json_decode((string) $response->getBody(), true);
        return is_array($data) ? $data : [];
    } catch (\Throwable $e) {
        error_log("GBP API error ({$method} {$url}): " . $e->getMessage());
        return null;
    }
}

/**
 * Publish a GBP post (local post / update).
 */
function publishGbpPost(PDO $db, int $postId): array
{
    $accountId = $_ENV['GOOGLE_GBP_ACCOUNT_ID'] ?? '';
    $locationId = $_ENV['GOOGLE_GBP_LOCATION_ID'] ?? '';

    if (!$accountId || !$locationId) {
        return ['success' => false, 'error' => 'GBP account/location not configured'];
    }

    $stmt = $db->prepare('SELECT * FROM oretir_gbp_posts WHERE id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$post) {
        return ['success' => false, 'error' => 'Post not found'];
    }

    $body = [
        'languageCode' => 'en',
        'summary' => $post['body_en'] ?? '',
        'topicType' => strtoupper($post['post_type'] === 'update' ? 'STANDARD' : ($post['post_type'] === 'offer' ? 'OFFER' : 'EVENT')),
    ];

    if ($post['cta_type'] && $post['cta_url']) {
        $body['callToAction'] = [
            'actionType' => $post['cta_type'],
            'url' => $post['cta_url'],
        ];
    }

    if ($post['post_type'] === 'offer' && $post['offer_start'] && $post['offer_end']) {
        $body['offer'] = [
            'couponCode' => '',
            'redeemOnlineUrl' => $post['cta_url'] ?? '',
        ];
        $body['event'] = [
            'title' => $post['title_en'] ?? '',
            'schedule' => [
                'startDate' => dateToGbp($post['offer_start']),
                'endDate' => dateToGbp($post['offer_end']),
            ],
        ];
    }

    if ($post['post_type'] === 'event' && $post['event_start'] && $post['event_end']) {
        $body['event'] = [
            'title' => $post['title_en'] ?? '',
            'schedule' => [
                'startDate' => dateToGbp(substr($post['event_start'], 0, 10)),
                'startTime' => timeToGbp($post['event_start']),
                'endDate' => dateToGbp(substr($post['event_end'], 0, 10)),
                'endTime' => timeToGbp($post['event_end']),
            ],
        ];
    }

    $url = "https://mybusiness.googleapis.com/v4/accounts/{$accountId}/locations/{$locationId}/localPosts";

    $result = gbpApiRequest('POST', $url, $body);

    if ($result === null) {
        $db->prepare("UPDATE oretir_gbp_posts SET status = 'failed', publish_error = 'API request failed' WHERE id = ?")
           ->execute([$postId]);
        return ['success' => false, 'error' => 'API request failed'];
    }

    $googlePostId = $result['name'] ?? null;
    $db->prepare(
        "UPDATE oretir_gbp_posts SET google_post_id = ?, status = 'published', published_at = NOW(), publish_error = NULL WHERE id = ?"
    )->execute([$googlePostId, $postId]);

    return ['success' => true, 'google_post_id' => $googlePostId];
}

/**
 * Fetch GBP insights and store in DB.
 */
function fetchGbpInsights(PDO $db): array
{
    $accountId = $_ENV['GOOGLE_GBP_ACCOUNT_ID'] ?? '';
    $locationId = $_ENV['GOOGLE_GBP_LOCATION_ID'] ?? '';

    if (!$accountId || !$locationId) {
        return ['success' => false, 'error' => 'GBP not configured'];
    }

    $endDate = new \DateTime('yesterday');
    $startDate = (clone $endDate)->modify('-7 days');

    $url = "https://mybusiness.googleapis.com/v4/accounts/{$accountId}/locations/{$locationId}:reportInsights";

    $body = [
        'locationNames' => ["accounts/{$accountId}/locations/{$locationId}"],
        'basicRequest' => [
            'metricRequests' => [
                ['metric' => 'QUERIES_DIRECT'],
                ['metric' => 'QUERIES_INDIRECT'],
                ['metric' => 'VIEWS_MAPS'],
                ['metric' => 'VIEWS_SEARCH'],
                ['metric' => 'ACTIONS_WEBSITE'],
                ['metric' => 'ACTIONS_DRIVING_DIRECTIONS'],
                ['metric' => 'ACTIONS_PHONE'],
                ['metric' => 'PHOTOS_VIEWS_MERCHANT'],
            ],
            'timeRange' => [
                'startTime' => $startDate->format('Y-m-d\TH:i:s\Z'),
                'endTime' => $endDate->format('Y-m-d\TH:i:s\Z'),
            ],
        ],
    ];

    $result = gbpApiRequest('POST', $url, $body);

    if ($result === null) {
        return ['success' => false, 'error' => 'Insights API failed'];
    }

    // Parse and store insights by date
    $stored = 0;
    $metrics = $result['locationMetrics'][0]['metricValues'] ?? [];
    $metricMap = [];

    foreach ($metrics as $m) {
        $name = $m['metric'] ?? '';
        foreach ($m['dimensionalValues'] ?? [] as $dv) {
            $date = substr($dv['timeDimension']['timeRange']['startTime'] ?? '', 0, 10);
            if (!$date) continue;
            $metricMap[$date][$name] = (int) ($dv['value'] ?? 0);
        }
    }

    $upsert = $db->prepare(
        'INSERT INTO oretir_gbp_insights (metric_date, views_search, views_maps, clicks_website, clicks_directions, clicks_phone, photo_views)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            views_search = VALUES(views_search), views_maps = VALUES(views_maps),
            clicks_website = VALUES(clicks_website), clicks_directions = VALUES(clicks_directions),
            clicks_phone = VALUES(clicks_phone), photo_views = VALUES(photo_views),
            updated_at = NOW()'
    );

    foreach ($metricMap as $date => $vals) {
        $upsert->execute([
            $date,
            $vals['VIEWS_SEARCH'] ?? 0,
            $vals['VIEWS_MAPS'] ?? 0,
            $vals['ACTIONS_WEBSITE'] ?? 0,
            $vals['ACTIONS_DRIVING_DIRECTIONS'] ?? 0,
            $vals['ACTIONS_PHONE'] ?? 0,
            $vals['PHOTOS_VIEWS_MERCHANT'] ?? 0,
        ]);
        $stored++;
    }

    return ['success' => true, 'stored' => $stored];
}

/**
 * Fetch GBP Q&A and upsert into DB.
 */
function fetchGbpQnA(PDO $db): array
{
    $accountId = $_ENV['GOOGLE_GBP_ACCOUNT_ID'] ?? '';
    $locationId = $_ENV['GOOGLE_GBP_LOCATION_ID'] ?? '';

    if (!$accountId || !$locationId) {
        return ['success' => false, 'error' => 'GBP not configured'];
    }

    $url = "https://mybusiness.googleapis.com/v4/accounts/{$accountId}/locations/{$locationId}/questions";
    $result = gbpApiRequest('GET', $url);

    if ($result === null) {
        return ['success' => false, 'error' => 'Q&A API failed'];
    }

    $upsert = $db->prepare(
        'INSERT INTO oretir_gbp_qna (google_question_id, question_text, answer_text, author_name, status, asked_at)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            question_text = VALUES(question_text),
            answer_text = VALUES(answer_text),
            status = VALUES(status),
            updated_at = NOW()'
    );

    $stored = 0;
    foreach ($result['questions'] ?? [] as $q) {
        $qId = $q['name'] ?? null;
        $questionText = $q['text'] ?? '';
        $authorName = $q['author']['displayName'] ?? 'Unknown';
        $askedAt = $q['createTime'] ?? null;
        if ($askedAt) $askedAt = (new \DateTime($askedAt))->format('Y-m-d H:i:s');

        // Check for top answer
        $answerText = null;
        $status = 'unanswered';
        if (!empty($q['topAnswers'])) {
            $answerText = $q['topAnswers'][0]['text'] ?? null;
            if ($answerText) $status = 'answered';
        }

        $upsert->execute([$qId, $questionText, $answerText, $authorName, $status, $askedAt]);
        $stored++;
    }

    return ['success' => true, 'stored' => $stored];
}

/**
 * Sync local business hours to GBP.
 */
function syncBusinessHours(PDO $db): array
{
    $accountId = $_ENV['GOOGLE_GBP_ACCOUNT_ID'] ?? '';
    $locationId = $_ENV['GOOGLE_GBP_LOCATION_ID'] ?? '';

    if (!$accountId || !$locationId) {
        return ['success' => false, 'error' => 'GBP not configured'];
    }

    // Fetch hours from site_settings or business_hours table
    $stmt = $db->query('SELECT * FROM oretir_business_hours ORDER BY day_of_week ASC');
    $hours = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($hours)) {
        return ['success' => false, 'error' => 'No business hours configured'];
    }

    $dayNames = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
    $periods = [];

    foreach ($hours as $h) {
        if (empty($h['is_closed'])) {
            $periods[] = [
                'openDay' => $dayNames[(int) $h['day_of_week']] ?? 'MONDAY',
                'openTime' => substr($h['open_time'], 0, 5),
                'closeDay' => $dayNames[(int) $h['day_of_week']] ?? 'MONDAY',
                'closeTime' => substr($h['close_time'], 0, 5),
            ];
        }
    }

    $url = "https://mybusiness.googleapis.com/v4/accounts/{$accountId}/locations/{$locationId}";
    $body = [
        'regularHours' => ['periods' => $periods],
    ];

    $result = gbpApiRequest('PATCH', $url . '?updateMask=regularHours', $body);

    return $result !== null
        ? ['success' => true]
        : ['success' => false, 'error' => 'Hours sync failed'];
}

/**
 * Convert a date string to GBP date format.
 */
function dateToGbp(string $date): array
{
    $dt = new \DateTime($date);
    return [
        'year' => (int) $dt->format('Y'),
        'month' => (int) $dt->format('n'),
        'day' => (int) $dt->format('j'),
    ];
}

/**
 * Convert a datetime string to GBP time format.
 */
function timeToGbp(string $datetime): array
{
    $dt = new \DateTime($datetime);
    return [
        'hours' => (int) $dt->format('G'),
        'minutes' => (int) $dt->format('i'),
    ];
}
