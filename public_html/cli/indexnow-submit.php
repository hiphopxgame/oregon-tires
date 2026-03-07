#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Oregon Tires — IndexNow URL Submission
 * Usage: php cli/indexnow-submit.php https://oregon.tires/faq https://oregon.tires/reviews
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

// Load environment for INDEXNOW_KEY
$envPaths = [
    [dirname(__DIR__, 3), '.env.oregon-tires'],
    [dirname(__DIR__), '.env'],
];
foreach ($envPaths as [$dir, $file]) {
    $path = $dir . '/' . $file;
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim(trim($v), '"\'');
            }
        }
        break;
    }
}

$key = $_ENV['INDEXNOW_KEY'] ?? '';
if (empty($key)) {
    echo "Error: INDEXNOW_KEY not set in .env\n";
    echo "Generate one: php -r \"echo bin2hex(random_bytes(16)) . PHP_EOL;\"\n";
    echo "Add to .env: INDEXNOW_KEY=your_key\n";
    echo "Create key file: echo 'your_key' > public_html/your_key.txt\n";
    exit(1);
}

$urls = array_slice($argv, 1);
if (empty($urls)) {
    echo "Usage: php cli/indexnow-submit.php <url1> [url2] [url3] ...\n";
    echo "Example: php cli/indexnow-submit.php https://oregon.tires/faq\n";
    exit(1);
}

$host = 'oregon.tires';
$payload = json_encode([
    'host' => $host,
    'key' => $key,
    'keyLocation' => "https://{$host}/{$key}.txt",
    'urlList' => $urls,
]);

$ch = curl_init('https://api.indexnow.org/indexnow');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "IndexNow Response: HTTP {$httpCode}\n";
if ($httpCode >= 200 && $httpCode < 300) {
    echo "Success! " . count($urls) . " URL(s) submitted.\n";
} else {
    echo "Failed. Response: {$response}\n";
}

foreach ($urls as $url) {
    echo "  - {$url}\n";
}
