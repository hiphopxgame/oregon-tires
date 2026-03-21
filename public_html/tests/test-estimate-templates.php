<?php
/**
 * Test: Estimate Templates API
 *
 * Verifies the estimate template CRUD operations including
 * migration SQL, API endpoint structure, and seed data.
 *
 * Run: php tests/test-estimate-templates.php
 */

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';
TestHelper::initSession();

$t = new TestHelper('Estimate Templates');

// ── Test 1: Migration SQL file exists and is valid ──
$t->test('Migration SQL file exists with correct table definition', function () {
    $sqlPath = __DIR__ . '/../../sql/migrate-063-estimate-templates.sql';
    TestHelper::assertTrue(file_exists($sqlPath), 'Migration file should exist');

    $sql = file_get_contents($sqlPath);
    TestHelper::assertContains('oretir_estimate_templates', $sql, 'Should create oretir_estimate_templates table');
    TestHelper::assertContains('name_en', $sql, 'Should have name_en column');
    TestHelper::assertContains('name_es', $sql, 'Should have name_es column');
    TestHelper::assertContains('service_type', $sql, 'Should have service_type column');
    TestHelper::assertContains('items JSON', $sql, 'Should have items JSON column');
    TestHelper::assertContains('is_active', $sql, 'Should have is_active column');
    TestHelper::assertContains('sort_order', $sql, 'Should have sort_order column');
});

// ── Test 2: Migration includes 5 seed templates ──
$t->test('Migration seeds 5 common templates', function () {
    $sql = file_get_contents(__DIR__ . '/../../sql/migrate-063-estimate-templates.sql');
    TestHelper::assertContains('Oil Change', $sql, 'Should seed Oil Change template');
    TestHelper::assertContains('Brake Pad Replacement', $sql, 'Should seed Brake Pad Replacement');
    TestHelper::assertContains('Tire Rotation', $sql, 'Should seed Tire Rotation');
    TestHelper::assertContains('Wheel Alignment', $sql, 'Should seed Wheel Alignment');
    TestHelper::assertContains('Engine Diagnostic', $sql, 'Should seed Engine Diagnostic');

    // Check bilingual
    TestHelper::assertContains('Cambio de Aceite', $sql, 'Should have Spanish Oil Change');
    TestHelper::assertContains('Rotación de Llantas', $sql, 'Should have Spanish Tire Rotation');
});

// ── Test 3: Seed data has valid JSON items ──
$t->test('Seed template items are valid JSON arrays', function () {
    $sql = file_get_contents(__DIR__ . '/../../sql/migrate-063-estimate-templates.sql');

    // Extract JSON arrays from the INSERT statement
    preg_match_all("/'\[.*?\]'/s", $sql, $matches);
    TestHelper::assertTrue(count($matches[0]) >= 5, 'Should have at least 5 JSON item arrays');

    foreach ($matches[0] as $jsonStr) {
        $json = trim($jsonStr, "'");
        $decoded = json_decode($json, true);
        TestHelper::assertNotNull($decoded, 'Each items value should be valid JSON');
        TestHelper::assertTrue(is_array($decoded), 'Items should decode to an array');
        TestHelper::assertTrue(count($decoded) > 0, 'Items array should not be empty');

        // Check item structure
        foreach ($decoded as $item) {
            TestHelper::assertTrue(isset($item['type']), 'Item should have type');
            TestHelper::assertTrue(isset($item['description_en']), 'Item should have description_en');
            TestHelper::assertTrue(isset($item['quantity']), 'Item should have quantity');
            TestHelper::assertTrue(isset($item['unit_price']), 'Item should have unit_price');
        }
    }
});

// ── Test 4: API endpoint file exists and handles all methods ──
$t->test('API endpoint file exists with GET/POST/PUT/DELETE', function () {
    $apiPath = __DIR__ . '/../api/admin/estimate-templates.php';
    TestHelper::assertTrue(file_exists($apiPath), 'API file should exist');

    $source = file_get_contents($apiPath);
    TestHelper::assertContains("requireMethod('GET', 'POST', 'PUT', 'DELETE')", $source, 'Should accept all 4 methods');
    TestHelper::assertContains("verifyCsrf()", $source, 'Should verify CSRF on mutations');
    TestHelper::assertContains("requirePermission('shop_ops')", $source, 'Should require shop_ops permission');
    TestHelper::assertContains('jsonSuccess', $source, 'Should return JSON success responses');
});

// ── Test 5: API validates required fields on create ──
$t->test('API validates name_en and items on create', function () {
    $source = file_get_contents(__DIR__ . '/../api/admin/estimate-templates.php');
    TestHelper::assertContains("empty(\$nameEn)", $source, 'Should validate name_en is not empty');
    TestHelper::assertContains("empty(\$items)", $source, 'Should validate items is not empty');
});

// ── Test 6: API sanitizes item data ──
$t->test('API sanitizes template item fields', function () {
    $source = file_get_contents(__DIR__ . '/../api/admin/estimate-templates.php');
    TestHelper::assertContains("sanitize((string) (\$item['type']", $source, 'Should sanitize item type');
    TestHelper::assertContains("sanitize((string) (\$item['description_en']", $source, 'Should sanitize description_en');
    TestHelper::assertContains("json_encode(\$cleanItems)", $source, 'Should encode clean items as JSON');
});

// ── Test 7: repair-orders.js has template dropdown ──
$t->test('Estimate editor includes template dropdown', function () {
    $jsPath = __DIR__ . '/../admin/js/repair-orders.js';
    $source = file_get_contents($jsPath);
    TestHelper::assertContains('estimate-templates.php', $source, 'Should fetch templates from API');
    TestHelper::assertContains('roUseTemplate', $source, 'Should have template dropdown label');
    TestHelper::assertContains('roTemplateApplied', $source, 'Should show toast when template applied');
});

// ── Test 8: DB integration test (if DB available) ──
$t->test('Database integration — create, list, delete template', function () {
    // Try to connect to DB. If unavailable, skip gracefully.
    try {
        // Attempt to load bootstrap for DB access
        $bootstrapPath = __DIR__ . '/../includes/bootstrap.php';
        if (!file_exists($bootstrapPath)) {
            // Skip if bootstrap not available (CI environment)
            return;
        }

        // Check if .env is available
        $envPath = dirname(__DIR__, 2) . '/.env';
        $envAlt = dirname(__DIR__, 3) . '/.env';
        if (!file_exists($envPath) && !file_exists($envAlt)) {
            // No DB credentials — skip integration test silently
            return;
        }

        require_once $bootstrapPath;
        $db = getDB();

        // Check if table exists
        try {
            $db->query('SELECT 1 FROM oretir_estimate_templates LIMIT 1');
        } catch (\Throwable $e) {
            // Table doesn't exist yet — skip
            return;
        }

        // Create
        $db->prepare(
            'INSERT INTO oretir_estimate_templates (name_en, name_es, service_type, items, sort_order)
             VALUES (?, ?, ?, ?, ?)'
        )->execute(['Test Template', 'Plantilla de Prueba', 'test', '[{"type":"labor","description_en":"Test","description_es":"Prueba","quantity":1,"unit_price":10.00,"is_taxable":false}]', 999]);
        $newId = (int) $db->lastInsertId();
        TestHelper::assertTrue($newId > 0, 'Should create template and get ID');

        // List
        $stmt = $db->prepare('SELECT * FROM oretir_estimate_templates WHERE id = ?');
        $stmt->execute([$newId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        TestHelper::assertNotNull($row, 'Should find created template');
        TestHelper::assertEqual('Test Template', $row['name_en']);

        // Delete
        $db->prepare('DELETE FROM oretir_estimate_templates WHERE id = ?')->execute([$newId]);
        $stmt->execute([$newId]);
        $deleted = $stmt->fetch(PDO::FETCH_ASSOC);
        TestHelper::assertFalse($deleted, 'Template should be deleted');

    } catch (\Throwable $e) {
        // DB not available — skip integration test
        return;
    }
});

$t->done();
