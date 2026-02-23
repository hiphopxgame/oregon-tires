<?php
/**
 * Oregon Tires — Calendar Test Sync
 * POST /api/admin/calendar-test-sync.php
 *
 * Creates a test event on Google Calendar, verifies it, then deletes it.
 * Used to validate that the calendar integration is working end-to-end.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('POST');
    $admin = requireAdmin();
    verifyCsrf();
    $db = getDB();

    $startTime = microtime(true);
    $steps = [];

    // ─── Step 1: Load credentials ───────────────────────────────────────
    $credentialsPath = $_ENV['GOOGLE_CALENDAR_CREDENTIALS'] ?? '';
    if (empty($credentialsPath) || !file_exists($credentialsPath)) {
        jsonError('Google Calendar credentials not configured.', 400);
    }

    $calendarId  = $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary';
    $formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../---form-kit';

    try {
        require_once $formKitPath . '/loader.php';
        require_once $formKitPath . '/actions/google-calendar.php';

        FormManager::init($db, ['site_key' => 'oregon.tires']);
        GoogleCalendarAction::register([
            'credentials_path' => $credentialsPath,
            'calendar_id'      => $calendarId,
            'send_invites'     => false,
            'timezone'         => 'America/Los_Angeles',
        ]);
        $steps[] = ['step' => 'load_credentials', 'success' => true];
    } catch (\Throwable $e) {
        jsonSuccess([
            'success'       => false,
            'failed_step'   => 'load_credentials',
            'error'         => $e->getMessage(),
            'steps'         => $steps,
            'latency_ms'    => (int) round((microtime(true) - $startTime) * 1000),
        ]);
    }

    // ─── Step 2: Get access token ───────────────────────────────────────
    try {
        GoogleCalendarAction::getAccessToken();
        $steps[] = ['step' => 'get_access_token', 'success' => true];
    } catch (\Throwable $e) {
        $steps[] = ['step' => 'get_access_token', 'success' => false, 'error' => $e->getMessage()];
        jsonSuccess([
            'success'       => false,
            'failed_step'   => 'get_access_token',
            'error'         => $e->getMessage(),
            'steps'         => $steps,
            'latency_ms'    => (int) round((microtime(true) - $startTime) * 1000),
        ]);
    }

    // ─── Step 3: Create test event ──────────────────────────────────────
    $timezone = 'America/Los_Angeles';
    $now = new \DateTimeImmutable('+15 minutes', new \DateTimeZone($timezone));
    $end = $now->modify('+15 minutes');

    $testEvent = [
        'summary'     => 'Test Event — Oregon Tires',
        'description' => 'Automated sync test. This event will be deleted automatically.',
        'start'       => [
            'dateTime' => $now->format('c'),
            'timeZone' => $timezone,
        ],
        'end' => [
            'dateTime' => $end->format('c'),
            'timeZone' => $timezone,
        ],
    ];

    $eventCreated = false;
    $eventId = null;
    try {
        $createResult = GoogleCalendarAction::createEvent($testEvent);
        $eventId = $createResult['id'] ?? null;
        $eventCreated = !empty($eventId);
        $steps[] = ['step' => 'create_event', 'success' => $eventCreated, 'event_id' => $eventId];
    } catch (\Throwable $e) {
        $steps[] = ['step' => 'create_event', 'success' => false, 'error' => $e->getMessage()];
        jsonSuccess([
            'success'       => false,
            'failed_step'   => 'create_event',
            'error'         => $e->getMessage(),
            'event_created' => false,
            'event_deleted' => false,
            'steps'         => $steps,
            'latency_ms'    => (int) round((microtime(true) - $startTime) * 1000),
        ]);
    }

    if (!$eventCreated) {
        jsonSuccess([
            'success'       => false,
            'failed_step'   => 'create_event',
            'error'         => 'No event ID returned from API.',
            'event_created' => false,
            'event_deleted' => false,
            'steps'         => $steps,
            'latency_ms'    => (int) round((microtime(true) - $startTime) * 1000),
        ]);
    }

    // ─── Step 4: Delete test event ──────────────────────────────────────
    $eventDeleted = false;
    try {
        $eventDeleted = GoogleCalendarAction::deleteEvent($eventId);
        $steps[] = ['step' => 'delete_event', 'success' => $eventDeleted];
    } catch (\Throwable $e) {
        $steps[] = ['step' => 'delete_event', 'success' => false, 'error' => $e->getMessage()];
        jsonSuccess([
            'success'       => false,
            'failed_step'   => 'delete_event',
            'error'         => $e->getMessage(),
            'event_created' => true,
            'event_deleted' => false,
            'steps'         => $steps,
            'latency_ms'    => (int) round((microtime(true) - $startTime) * 1000),
        ]);
    }

    // ─── All steps passed ───────────────────────────────────────────────
    $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

    jsonSuccess([
        'success'       => true,
        'message'       => 'Calendar sync test passed',
        'event_created' => true,
        'event_deleted' => $eventDeleted,
        'steps'         => $steps,
        'latency_ms'    => $latencyMs,
    ]);

} catch (\Throwable $e) {
    error_log('calendar-test-sync.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
