<?php
/**
 * Phase 7 Agent Tests — WebAuthn, Anomaly Detection, Session Fingerprint, Mobile
 *
 * Agents:
 * - Agent 13: WebAuthn/Passkey Support (5 tests)
 * - Agent 14: Account Takeover Prevention/Anomaly Detection (4 tests)
 * - Agent 15: Session Fingerprint Rotation (2 tests)
 * - Agent 16: Native Mobile App Integration (4 tests)
 */

$tests_passed = 0;
$tests_failed = 0;

function assert_file_exists($path) {
    global $tests_passed, $tests_failed;
    if (file_exists($path)) {
        echo "✓ assert_file_exists: $path\n";
        $tests_passed++;
        return true;
    } else {
        echo "✗ assert_file_exists FAILED: $path\n";
        $tests_failed++;
        return false;
    }
}

function assert_contains($content, $needle) {
    global $tests_passed, $tests_failed;

    // Support regex patterns (contain .* or other regex syntax)
    if (strpos($needle, '.*') !== false || strpos($needle, '|') !== false || strpos($needle, '(') !== false) {
        if (preg_match('/' . $needle . '/i', $content)) {
            echo "✓ assert_contains (regex): /$needle/i\n";
            $tests_passed++;
            return true;
        } else {
            echo "✗ assert_contains (regex) FAILED: /$needle/i\n";
            $tests_failed++;
            return false;
        }
    }

    // Literal string match
    if (strpos($content, $needle) !== false) {
        echo "✓ assert_contains: \"$needle\"\n";
        $tests_passed++;
        return true;
    } else {
        echo "✗ assert_contains FAILED: \"$needle\"\n";
        $tests_failed++;
        return false;
    }
}

echo "======================================\n";
echo "Phase 7 Agent Tests\n";
echo "======================================\n\n";

// ============================================================================
// Agent 13: WebAuthn/Passkey Support (5 tests)
// ============================================================================
echo "[Agent 13] WebAuthn/Passkey Support\n";
echo "---\n";

// 13.1 WebAuthn migration
$migration_13_1 = file_get_contents(__DIR__ . '/../migrations/005_webauthn.php');
assert_contains($migration_13_1, 'CREATE TABLE');
assert_contains($migration_13_1, 'webauthn_credentials');
assert_contains($migration_13_1, 'credential_id');

// 13.2 WebAuthn API — register begin
assert_file_exists(__DIR__ . '/../api/member/webauthn-register-begin.php');
$api_13_2 = file_get_contents(__DIR__ . '/../api/member/webauthn-register-begin.php');
assert_contains($api_13_2, 'PublicKeyCredentialCreationOptions');

// 13.3 WebAuthn API — register complete
assert_file_exists(__DIR__ . '/../api/member/webauthn-register-complete.php');
$api_13_3 = file_get_contents(__DIR__ . '/../api/member/webauthn-register-complete.php');
assert_contains($api_13_3, 'credential_id');
assert_contains($api_13_3, 'webauthn_credentials');

echo "\n";

// ============================================================================
// Agent 14: Account Takeover Prevention / Anomaly Detection (4 tests)
// ============================================================================
echo "[Agent 14] Account Takeover Prevention / Anomaly Detection\n";
echo "---\n";

// 14.1 Anomaly detection migration
$migration_14_1 = file_get_contents(__DIR__ . '/../migrations/005_anomaly_detection.php');
assert_contains($migration_14_1, 'CREATE TABLE');
assert_contains($migration_14_1, 'login_anomalies');
assert_contains($migration_14_1, 'is_suspicious');

// 14.2 Anomaly detection API — check
assert_file_exists(__DIR__ . '/../api/member/anomaly-check.php');
$api_14_2 = file_get_contents(__DIR__ . '/../api/member/anomaly-check.php');
assert_contains($api_14_2, 'suspicious');
assert_contains($api_14_2, 'require_additional_verification');

// 14.3 Anomaly detection API — report
assert_file_exists(__DIR__ . '/../api/member/report-suspicious-activity.php');
$api_14_3 = file_get_contents(__DIR__ . '/../api/member/report-suspicious-activity.php');
assert_contains($api_14_3, 'member_id');
assert_contains($api_14_3, 'activity_type');

// 14.4 Admin anomaly dashboard template
assert_file_exists(__DIR__ . '/../templates/member/anomaly-dashboard.php');

echo "\n";

// ============================================================================
// Agent 15: Session Fingerprint Rotation (2 tests)
// ============================================================================
echo "[Agent 15] Session Fingerprint Rotation\n";
echo "---\n";

// 15.1 Fingerprint rotation migration
$migration_15_1 = file_get_contents(__DIR__ . '/../migrations/005_fingerprint_rotation.php');
assert_contains($migration_15_1, 'CREATE TABLE');
assert_contains($migration_15_1, 'fingerprint_rotation');
assert_contains($migration_15_1, 'previous_fingerprint');

// 15.2 Fingerprint rotation API
assert_file_exists(__DIR__ . '/../api/member/rotate-fingerprint.php');
$api_15_2 = file_get_contents(__DIR__ . '/../api/member/rotate-fingerprint.php');
assert_contains($api_15_2, 'fingerprint');

echo "\n";

// ============================================================================
// Agent 16: Native Mobile App Integration (4 tests)
// ============================================================================
echo "[Agent 16] Native Mobile App Integration\n";
echo "---\n";

// 16.1 Mobile device table migration
$migration_16_1 = file_get_contents(__DIR__ . '/../migrations/005_mobile_devices.php');
assert_contains($migration_16_1, 'CREATE TABLE');
assert_contains($migration_16_1, 'mobile_devices');
assert_contains($migration_16_1, 'device_token');

// 16.2 Mobile API — register device
assert_file_exists(__DIR__ . '/../api/member/mobile-register-device.php');
$api_16_2 = file_get_contents(__DIR__ . '/../api/member/mobile-register-device.php');
assert_contains($api_16_2, 'device_token');

// 16.3 Mobile API — app auth endpoint
assert_file_exists(__DIR__ . '/../api/member/mobile-auth.php');
$api_16_3 = file_get_contents(__DIR__ . '/../api/member/mobile-auth.php');
assert_contains($api_16_3, 'Bearer.*token');

// 16.4 Mobile API — push notification
assert_file_exists(__DIR__ . '/../api/member/mobile-notify.php');

echo "\n";

// ============================================================================
// Results
// ============================================================================
echo "======================================\n";
echo "Phase 7 Test Results\n";
echo "======================================\n";
echo "Passed: $tests_passed\n";
echo "Failed: $tests_failed\n";
echo "Total:  " . ($tests_passed + $tests_failed) . "\n";

if ($tests_failed > 0) {
    exit(1);
}
exit(0);
