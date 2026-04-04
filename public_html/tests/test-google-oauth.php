#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Google OAuth Configuration Test
 * Verifies OAuth redirect URI is properly registered with Google.
 * Run: php tests/test-google-oauth.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../includes/bootstrap.php';

$t = new TestHelper('Google OAuth Configuration');

$clientId    = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

// ── Test 1: Client ID configured ──
$t->test('GOOGLE_CLIENT_ID is set', function () use ($clientId) {
    TestHelper::assertTrue($clientId !== '', 'GOOGLE_CLIENT_ID should not be empty');
});

// ── Test 2: Client Secret configured ──
$t->test('GOOGLE_CLIENT_SECRET is set', function () {
    TestHelper::assertTrue(($_ENV['GOOGLE_CLIENT_SECRET'] ?? '') !== '', 'GOOGLE_CLIENT_SECRET should not be empty');
});

// ── Test 3: Redirect URI configured ──
$t->test('GOOGLE_REDIRECT_URI is set', function () use ($redirectUri) {
    TestHelper::assertTrue($redirectUri !== '', 'GOOGLE_REDIRECT_URI should not be empty');
});

// ── Test 4: Redirect URI uses HTTPS ──
$t->test('Redirect URI uses HTTPS', function () use ($redirectUri) {
    TestHelper::assertTrue(str_starts_with($redirectUri, 'https://'), 'Must use HTTPS');
});

// ── Test 5: Redirect URI points to correct domain ──
$t->test('Redirect URI points to oregon.tires', function () use ($redirectUri) {
    TestHelper::assertContains('oregon.tires', $redirectUri);
});

// ── Test 6: Callback file exists on server ──
$t->test('google-callback.php file exists', function () {
    $path = __DIR__ . '/../api/auth/google-callback.php';
    TestHelper::assertTrue(file_exists($path), 'Callback file should exist at api/auth/google-callback.php');
});

// ── Test 7: Admin OAuth initiator exists ──
$t->test('admin/google.php file exists', function () {
    $path = __DIR__ . '/../api/admin/google.php';
    TestHelper::assertTrue(file_exists($path), 'Admin OAuth initiator should exist');
});

// ── Test 8: Member OAuth initiator exists ──
$t->test('auth/google.php file exists', function () {
    $path = __DIR__ . '/../api/auth/google.php';
    TestHelper::assertTrue(file_exists($path), 'Member OAuth initiator should exist');
});

// ── Test 9: Redirect URI is registered with Google (live check) ──
$t->test('Redirect URI accepted by Google (no redirect_uri_mismatch)', function () use ($clientId, $redirectUri) {
    if (empty($clientId) || empty($redirectUri)) {
        throw new \RuntimeException('Skipped: OAuth not configured');
    }

    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => 'test',
        'access_type'   => 'online',
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $location = '';
    if (preg_match('/^location:\s*(.+)$/mi', $response, $m)) {
        $location = trim($m[1]);
    }
    curl_close($ch);

    // Google returns 302 to consent screen if redirect_uri is valid,
    // or 302 to error page with "redirect_uri_mismatch" if invalid.
    // The error text may be base64-encoded in the URL, so also check for the signin/oauth/error path.
    $hasError = str_contains($location, 'redirect_uri_mismatch')
             || str_contains($location, 'signin/oauth/error');
    if ($hasError) {
        throw new \RuntimeException(
            "redirect_uri_mismatch: URI '{$redirectUri}' is NOT registered in Google Cloud Console. "
            . "Add it at: https://console.cloud.google.com/apis/credentials"
        );
    }

    // Should redirect to consent/login page (not error page)
    TestHelper::assertTrue($httpCode === 302 || $httpCode === 303, "Expected redirect, got HTTP {$httpCode}");
    TestHelper::assertFalse($hasError, 'Should not have redirect_uri_mismatch error');
});

$t->done();
