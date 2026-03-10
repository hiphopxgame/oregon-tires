<?php
/**
 * Oregon Tires — Google Places API Review Fetcher
 * Fetches reviews from multiple Google API endpoints/languages to maximize coverage.
 * Rate-limited to 1 fetch per hour via site_settings.
 *
 * Strategy: Google caps at 5 reviews per request. We make 4 calls to get up to 20 unique:
 *   1. Places API (New) — English, most relevant
 *   2. Places API (New) — Spanish, most relevant (different reviewers)
 *   3. Legacy Places API — most relevant
 *   4. Legacy Places API — newest sort
 */

declare(strict_types=1);

/**
 * Fetch Google reviews from all available sources and upsert into testimonials.
 */
function fetchGoogleReviews(PDO $db): array
{
    $apiKey = $_ENV['GOOGLE_PLACES_API_KEY'] ?? '';
    if ($apiKey === '') {
        return ['error' => 'GOOGLE_PLACES_API_KEY not configured', 'imported' => 0];
    }

    // Rate limit: 1 fetch per hour
    $stmt = $db->prepare(
        "SELECT value_en FROM oretir_site_settings WHERE setting_key = 'google_reviews_last_fetched'"
    );
    $stmt->execute();
    $lastFetched = $stmt->fetchColumn();

    if ($lastFetched && strtotime($lastFetched) > time() - 3600) {
        $minutesLeft = ceil((strtotime($lastFetched) + 3600 - time()) / 60);
        return ['error' => "Rate limited. Try again in {$minutesLeft} minutes.", 'imported' => 0];
    }

    $placeId = 'ChIJLSxZDQyflVQRWXEi9LpJGxs';
    $allReviews = [];
    $ratingValue = null;
    $reviewCount = null;

    // ─── Source 1: Places API (New) — English ──────────────────
    $data = callPlacesApiNew($apiKey, $placeId, 'en');
    if ($data) {
        $ratingValue = $data['rating'] ?? $ratingValue;
        $reviewCount = $data['userRatingCount'] ?? $reviewCount;
        foreach ($data['reviews'] ?? [] as $r) {
            $allReviews[] = normalizeNewApiReview($r);
        }
    }

    // ─── Source 2: Places API (New) — Spanish ──────────────────
    $data = callPlacesApiNew($apiKey, $placeId, 'es');
    if ($data) {
        foreach ($data['reviews'] ?? [] as $r) {
            $allReviews[] = normalizeNewApiReview($r);
        }
    }

    // ─── Source 3: Legacy Places API — most relevant ───────────
    $data = callPlacesApiLegacy($apiKey, $placeId, 'most_relevant');
    if ($data) {
        $ratingValue = $ratingValue ?? ($data['result']['rating'] ?? null);
        foreach ($data['result']['reviews'] ?? [] as $r) {
            $allReviews[] = normalizeLegacyApiReview($r);
        }
    }

    // ─── Source 4: Legacy Places API — newest ──────────────────
    $data = callPlacesApiLegacy($apiKey, $placeId, 'newest');
    if ($data) {
        foreach ($data['result']['reviews'] ?? [] as $r) {
            $allReviews[] = normalizeLegacyApiReview($r);
        }
    }

    // Update aggregate stats
    if ($ratingValue !== null) {
        upsertSetting($db, 'rating_value', (string) $ratingValue);
    }
    if ($reviewCount !== null) {
        upsertSetting($db, 'review_count', (string) $reviewCount);
    }

    // Upsert all reviews (dedup via google_review_id unique index)
    $imported = 0;
    $total = 0;

    $upsertStmt = $db->prepare(
        "INSERT INTO oretir_testimonials
            (source, google_review_id, customer_name, author_photo_url, google_published_at,
             rating, review_text_en, is_active, show_on_homepage, sort_order)
         VALUES ('google', ?, ?, ?, ?, ?, ?, 1, 1, 99)
         ON DUPLICATE KEY UPDATE
            customer_name = VALUES(customer_name),
            author_photo_url = VALUES(author_photo_url),
            rating = VALUES(rating),
            review_text_en = VALUES(review_text_en),
            updated_at = NOW()"
    );

    foreach ($allReviews as $review) {
        $total++;
        $upsertStmt->execute([
            $review['dedup_id'],
            sanitize($review['author_name'], 200),
            $review['photo_url'] ? sanitize($review['photo_url'], 500) : null,
            $review['published_at'],
            (int) $review['rating'],
            sanitize($review['text'], 2000),
        ]);

        if ($upsertStmt->rowCount() === 1) {
            $imported++;
        }
    }

    upsertSetting($db, 'google_reviews_last_fetched', date('Y-m-d H:i:s'));

    return [
        'imported' => $imported,
        'total' => $total,
    ];
}

/**
 * Call Google Places API (New) with optional language.
 */
function callPlacesApiNew(string $apiKey, string $placeId, string $lang = 'en'): ?array
{
    $url = "https://places.googleapis.com/v1/places/{$placeId}";
    $headers = [
        'X-Goog-Api-Key: ' . $apiKey,
        'X-Goog-FieldMask: reviews,rating,userRatingCount',
    ];
    if ($lang !== 'en') {
        $headers[] = 'Accept-Language: ' . $lang;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("Places API (New, {$lang}) error: HTTP {$httpCode}");
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

/**
 * Call Legacy Google Places API.
 */
function callPlacesApiLegacy(string $apiKey, string $placeId, string $sort = 'most_relevant'): ?array
{
    $url = "https://maps.googleapis.com/maps/api/place/details/json?"
        . http_build_query([
            'place_id' => $placeId,
            'fields' => 'reviews,rating,user_ratings_total',
            'reviews_sort' => $sort,
            'key' => $apiKey,
        ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("Places API (Legacy, {$sort}) error: HTTP {$httpCode}");
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) && ($data['status'] ?? '') === 'OK' ? $data : null;
}

/**
 * Normalize a review from Places API (New) format.
 */
function normalizeNewApiReview(array $r): array
{
    $authorName = $r['authorAttribution']['displayName'] ?? 'Google User';
    $text = $r['text']['text'] ?? '';
    $rating = $r['rating'] ?? 5;
    $photoUrl = $r['authorAttribution']['photoUri'] ?? null;
    $publishTime = $r['publishTime'] ?? null;

    $publishedAt = null;
    if ($publishTime) {
        $publishedAt = (new DateTime($publishTime))->format('Y-m-d H:i:s');
    }

    return [
        'dedup_id' => hash('sha256', $authorName . '|' . $rating),
        'author_name' => $authorName,
        'photo_url' => $photoUrl,
        'published_at' => $publishedAt,
        'rating' => $rating,
        'text' => $text,
    ];
}

/**
 * Normalize a review from Legacy Places API format.
 */
function normalizeLegacyApiReview(array $r): array
{
    $authorName = $r['author_name'] ?? 'Google User';
    $text = $r['text'] ?? '';
    $rating = $r['rating'] ?? 5;
    $photoUrl = $r['profile_photo_url'] ?? null;
    $time = $r['time'] ?? null;

    $publishedAt = null;
    if ($time) {
        $publishedAt = date('Y-m-d H:i:s', (int) $time);
    }

    return [
        'dedup_id' => hash('sha256', $authorName . '|' . $rating),
        'author_name' => $authorName,
        'photo_url' => $photoUrl,
        'published_at' => $publishedAt,
        'rating' => $rating,
        'text' => $text,
    ];
}

/**
 * Get Google review aggregate stats from site_settings.
 */
function getGoogleReviewStats(PDO $db): array
{
    $keys = ['rating_value', 'review_count', 'google_reviews_last_fetched'];
    $placeholders = implode(',', array_fill(0, count($keys), '?'));

    $stmt = $db->prepare(
        "SELECT setting_key, value_en FROM oretir_site_settings WHERE setting_key IN ({$placeholders})"
    );
    $stmt->execute($keys);

    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['value_en'];
    }

    return [
        'rating_value' => $settings['rating_value'] ?? '4.8',
        'review_count' => $settings['review_count'] ?? '150',
        'last_fetched' => $settings['google_reviews_last_fetched'] ?? null,
    ];
}

/**
 * Upsert a site setting.
 */
function upsertSetting(PDO $db, string $key, string $value): void
{
    $stmt = $db->prepare(
        "INSERT INTO oretir_site_settings (setting_key, value_en) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE value_en = VALUES(value_en)"
    );
    $stmt->execute([$key, $value]);
}
