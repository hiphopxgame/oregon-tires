#!/usr/bin/env php
<?php
/**
 * TDD: Test getAggregateRating() — dynamic rating from DB with fallback.
 * Run: php tests/test-aggregate-rating.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/seo-config.php';

$t = new TestHelper('Aggregate Rating');

// ── Test 1: Function exists ──
$t->test('getAggregateRating() function exists', function () {
    TestHelper::assertTrue(function_exists('getAggregateRating'), 'getAggregateRating should be defined');
});

// ── Test 2: Returns array with expected keys ──
$t->test('returns array with ratingValue and reviewCount keys', function () {
    $rating = getAggregateRating();
    TestHelper::assertTrue(is_array($rating), 'should return array');
    TestHelper::assertTrue(array_key_exists('ratingValue', $rating), 'should have ratingValue key');
    TestHelper::assertTrue(array_key_exists('reviewCount', $rating), 'should have reviewCount key');
});

// ── Test 3: ratingValue is a numeric string ──
$t->test('ratingValue is a valid numeric string', function () {
    $rating = getAggregateRating();
    TestHelper::assertTrue(is_string($rating['ratingValue']), 'ratingValue should be string');
    TestHelper::assertTrue(is_numeric($rating['ratingValue']), 'ratingValue should be numeric');
    $val = (float) $rating['ratingValue'];
    TestHelper::assertTrue($val >= 1.0 && $val <= 5.0, 'ratingValue should be between 1.0 and 5.0');
});

// ── Test 4: reviewCount is a numeric string ──
$t->test('reviewCount is a valid numeric string', function () {
    $rating = getAggregateRating();
    TestHelper::assertTrue(is_string($rating['reviewCount']), 'reviewCount should be string');
    TestHelper::assertTrue(is_numeric($rating['reviewCount']), 'reviewCount should be numeric');
    TestHelper::assertTrue((int) $rating['reviewCount'] >= 1, 'reviewCount should be at least 1');
});

// ── Test 5: getBusinessConfig() uses dynamic rating ──
$t->test('getBusinessConfig() rating matches getAggregateRating()', function () {
    $config = getBusinessConfig();
    $rating = getAggregateRating();
    TestHelper::assertEqual($rating['ratingValue'], $config['rating'], 'config rating should match');
    TestHelper::assertEqual($rating['reviewCount'], $config['reviewCount'], 'config reviewCount should match');
});

// ── Test 6: Fallback values are reasonable ──
$t->test('always returns non-empty values (fallback works)', function () {
    $rating = getAggregateRating();
    TestHelper::assertTrue(strlen($rating['ratingValue']) > 0, 'ratingValue should not be empty');
    TestHelper::assertTrue(strlen($rating['reviewCount']) > 0, 'reviewCount should not be empty');
});

$t->done();
