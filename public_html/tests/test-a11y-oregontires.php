<?php
/**
 * test-a11y-oregontires.php
 * Accessibility compliance tests for Oregon Tires Auto Care website
 * Tests specific to booking flow, CTA buttons, and bilingual interface
 */

require_once __DIR__ . '/TestHelper.php';
$test = new TestHelper('a11y: Oregon Tires Auto Care');

// Read template files
$bookingForm = @file_get_contents(__DIR__ . '/../templates/booking-form.php') ?: '';
$layout = @file_get_contents(__DIR__ . '/../templates/layout.php') ?: '';
$index = @file_get_contents(__DIR__ . '/../index.php') ?: '';
$allContent = $bookingForm . "\n" . $layout . "\n" . $index;

// ─────────────────────────────────────────────────────────────────────
// Test 1: Booking form label associations
// ─────────────────────────────────────────────────────────────────────

$test->test('Booking form inputs have id attributes', function () use ($bookingForm) {
    if (empty($bookingForm)) {
        throw new Exception('Could not read booking form template');
    }
    if (!preg_match('/type="(text|email|tel|date)"\s+id=/i', $bookingForm) &&
        !preg_match('/id=\s*"[^"]*"\s+type="(text|email|tel|date)"/i', $bookingForm)) {
        throw new Exception('Booking form inputs missing id attributes');
    }
});

$test->test('Booking form labels have for attributes', function () use ($bookingForm) {
    if (empty($bookingForm)) {
        throw new Exception('Could not read booking form template');
    }
    if (!preg_match('/<label[^>]*for=/', $bookingForm)) {
        throw new Exception('Booking form labels missing for attributes');
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 2: CTA buttons have aria-labels
// ─────────────────────────────────────────────────────────────────────

$test->test('CTA buttons have aria-label or text content', function () use ($allContent) {
    $ctaPatterns = [
        '/Book Appointment|Schedule Now|Get Started/i',
        '/<button[^>]*(class="[^"]*cta|id="[^"]*cta)[^>]*>/i',
    ];

    $hasCtaWithLabel = false;
    foreach ($ctaPatterns as $pattern) {
        if (preg_match($pattern, $allContent)) {
            // Check if button/link has text or aria-label nearby
            if (preg_match('/(aria-label|>.*Book|>.*Schedule|>.*Get Started)/i', $allContent)) {
                $hasCtaWithLabel = true;
                break;
            }
        }
    }

    if (!$hasCtaWithLabel && preg_match('/<button/i', $allContent)) {
        throw new Exception('CTA buttons exist but lack proper labels');
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 3: Bilingual language toggle has aria-label
// ─────────────────────────────────────────────────────────────────────

$test->test('Language toggle has aria-label or descriptive text', function () use ($layout) {
    if (empty($layout)) {
        throw new Exception('Could not read layout template');
    }

    if (!preg_match('/lang|language|toggle|español|english/i', $layout)) {
        // Bilingual toggle might not be implemented - skip
        return;
    }

    if (!preg_match('/(aria-label|title=|data-label)/i', $layout)) {
        throw new Exception('Language toggle lacks descriptive label');
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 4: Service card images have non-empty alt text
// ─────────────────────────────────────────────────────────────────────

$test->test('Service card images have alt text', function () use ($allContent) {
    preg_match_all('/<img[^>]*class="[^"]*service[^>]*>/i', $allContent, $serviceImages);

    if (empty($serviceImages[0])) {
        // No service images found - skip
        return;
    }

    $missingAlt = 0;
    foreach ($serviceImages[0] as $img) {
        if (!preg_match('/\balt\s*=\s*["\'](?![\s"\']*$)/i', $img)) {
            $missingAlt++;
        }
    }

    if ($missingAlt > 0) {
        throw new Exception("{$missingAlt} service card images missing alt text");
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 5: HTML lang attribute present
// ─────────────────────────────────────────────────────────────────────

$test->test('<html> tag has lang attribute', function () use ($allContent) {
    if (!preg_match('/<html[^>]*\blang\s*=\s*["\']?[a-z]{2}/i', $allContent)) {
        throw new Exception('Missing or invalid lang attribute on <html> tag');
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 6: Status messages have aria-live
// ─────────────────────────────────────────────────────────────────────

$test->test('Booking status messages have aria-live', function () use ($allContent) {
    if (!preg_match(/(status|confirmation|error|success)/i, $allContent)) {
        // No obvious status elements - skip
        return;
    }

    if (!preg_match(/aria-live\s*=\s*["\']?(polite|assertive)/i, $allContent)) {
        // Might use JS-based updates - just warn
        throw new Exception('No aria-live attributes found for status updates (check JS updates)');
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 7: Phone button has href="tel:"
// ─────────────────────────────────────────────────────────────────────

$test->test('Phone numbers use tel: links', function () use ($allContent) {
    if (!preg_match(/phone|call|\(\d{3}\)/i, $allContent)) {
        // No phone numbers visible - skip
        return;
    }

    if (!preg_match(/href="tel:/i, $allContent)) {
        throw new Exception('Phone number exists but not clickable via tel: link');
    }
});

// ─────────────────────────────────────────────────────────────────────
// Test 8: Heading hierarchy starts with H1
// ─────────────────────────────────────────────────────────────────────

$test->test('Page has H1 heading', function () use ($allContent) {
    if (!preg_match('/<h1/i', $allContent)) {
        throw new Exception('Page missing H1 heading');
    }
});

$test->done();
