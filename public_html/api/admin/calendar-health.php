<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');
    $admin = requireAdmin();
    $db = getDB();

    $credentialsPath = $_ENV['GOOGLE_CALENDAR_CREDENTIALS'] ?? '';
    $calendarId      = $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary';

    // ─── Check 1: Credentials file exists ────────────────────────────────
    if (empty($credentialsPath) || !file_exists($credentialsPath)) {
        jsonSuccess([
            'configured'         => false,
            'credentials_valid'  => false,
            'service_account'    => null,
            'calendar_id'        => $calendarId,
            'calendar_accessible' => false,
            'failed_syncs_24h'   => 0,
            'total_synced'       => 0,
            'error'              => 'Credentials file not found: ' . ($credentialsPath ?: '(not set)'),
        ]);
    }

    // ─── Load credentials to read service account email ──────────────────
    $credentials = json_decode(file_get_contents($credentialsPath), true);
    $serviceAccount = $credentials['client_email'] ?? null;

    if (empty($serviceAccount) || empty($credentials['private_key'])) {
        jsonSuccess([
            'configured'         => true,
            'credentials_valid'  => false,
            'service_account'    => $serviceAccount,
            'calendar_id'        => $calendarId,
            'calendar_accessible' => false,
            'failed_syncs_24h'   => 0,
            'total_synced'       => 0,
            'error'              => 'Invalid credentials: missing client_email or private_key',
        ]);
    }

    // ─── Load Form Kit + GoogleCalendarAction ────────────────────────────
    $formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../---form-kit';
    require_once $formKitPath . '/loader.php';
    require_once $formKitPath . '/actions/google-calendar.php';

    FormManager::init($db, ['site_key' => 'oregon.tires']);
    GoogleCalendarAction::register([
        'credentials_path' => $credentialsPath,
        'calendar_id'      => $calendarId,
        'send_invites'     => true,
        'timezone'         => 'America/Los_Angeles',
    ]);

    // ─── Check 2: Can get access token ──────────────────────────────────
    $credentialsValid = false;
    $tokenError = null;
    try {
        GoogleCalendarAction::getAccessToken();
        $credentialsValid = true;
    } catch (\Throwable $e) {
        $tokenError = $e->getMessage();
    }

    // ─── Check 3: Can list 1 event from calendar ────────────────────────
    $calendarAccessible = false;
    $calendarError = null;
    if ($credentialsValid) {
        try {
            $encodedCalId = urlencode($calendarId);
            $url = "https://www.googleapis.com/calendar/v3/calendars/{$encodedCalId}/events?maxResults=1";
            GoogleCalendarAction::apiRequest('GET', $url, null);
            $calendarAccessible = true;
        } catch (\Throwable $e) {
            $calendarError = $e->getMessage();
        }
    }

    // ─── Check 4: Failed syncs in last 24h ──────────────────────────────
    $failedStmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_appointments
         WHERE calendar_sync_status = 'failed'
           AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
    );
    $failedStmt->execute();
    $failedSyncs = (int) $failedStmt->fetchColumn();

    // ─── Check 5: Total synced (have google_event_id) ────────────────────
    $syncedStmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_appointments WHERE google_event_id IS NOT NULL AND google_event_id != ''"
    );
    $syncedStmt->execute();
    $totalSynced = (int) $syncedStmt->fetchColumn();

    $result = [
        'configured'          => true,
        'credentials_valid'   => $credentialsValid,
        'service_account'     => $serviceAccount,
        'calendar_id'         => $calendarId,
        'calendar_accessible' => $calendarAccessible,
        'failed_syncs_24h'    => $failedSyncs,
        'total_synced'        => $totalSynced,
    ];

    if ($tokenError) {
        $result['token_error'] = $tokenError;
    }
    if ($calendarError) {
        $result['calendar_error'] = $calendarError;
    }

    jsonSuccess($result);

} catch (\Throwable $e) {
    error_log('calendar-health.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
