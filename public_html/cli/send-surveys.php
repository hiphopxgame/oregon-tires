#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Send Customer Satisfaction Surveys
 * Cron: 0 11 * * *
 * Finds completed appointments where delay_hours have elapsed and survey_sent=0.
 * Creates survey response tokens and sends survey emails.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';
require_once __DIR__ . '/../includes/survey.php';

echo "[" . date('Y-m-d H:i:s') . "] Sending customer satisfaction surveys...\n";

try {
    $db = getDB();

    // Get active surveys
    $surveyStmt = $db->query(
        "SELECT * FROM oretir_surveys WHERE is_active = 1 AND trigger_event = 'ro_completed'"
    );
    $surveys = $surveyStmt->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($surveys)) {
        echo "No active surveys configured.\n";
        exit(0);
    }

    $sent = 0;
    $failed = 0;
    $skipped = 0;

    foreach ($surveys as $survey) {
        $delayHours = (int) $survey['delay_hours'];

        // Find completed appointments ready for survey
        $stmt = $db->prepare(
            "SELECT a.id, a.reference_number, a.first_name, a.last_name, a.email, a.service,
                    a.preferred_date, a.language, a.customer_id
             FROM oretir_appointments a
             WHERE a.status = 'completed'
               AND (a.survey_sent IS NULL OR a.survey_sent = 0)
               AND a.email IS NOT NULL AND a.email != ''
               AND a.customer_id IS NOT NULL
               AND a.updated_at <= DATE_SUB(NOW(), INTERVAL ? HOUR)
             ORDER BY a.updated_at ASC
             LIMIT 50"
        );
        $stmt->execute([$delayHours]);
        $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($appointments)) {
            echo "  Survey '{$survey['title_en']}': no appointments ready.\n";
            continue;
        }

        $count = count($appointments);
        echo "  Survey '{$survey['title_en']}': {$count} appointments to process.\n";

        foreach ($appointments as $appt) {
            try {
                // Check if survey response already exists for this appointment
                $existsStmt = $db->prepare(
                    'SELECT id FROM oretir_survey_responses WHERE survey_id = ? AND appointment_id = ? LIMIT 1'
                );
                $existsStmt->execute([$survey['id'], $appt['id']]);
                if ($existsStmt->fetch()) {
                    $db->prepare('UPDATE oretir_appointments SET survey_sent = 1 WHERE id = ?')->execute([$appt['id']]);
                    $skipped++;
                    continue;
                }

                // Create survey response with token
                $token = createSurveyResponse($db, (int) $survey['id'], (int) $appt['id'], (int) $appt['customer_id']);
                $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
                $surveyUrl = $baseUrl . '/survey/' . $token;

                $customerName = trim($appt['first_name'] . ' ' . $appt['last_name']);
                $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));
                $customerLang = ($appt['language'] ?? 'english') === 'spanish' ? 'es' : 'en';

                // Send survey email
                $result = sendSurveyEmail(
                    $appt['email'],
                    $customerName,
                    $serviceDisplay,
                    $surveyUrl,
                    $customerLang
                );

                if ($result['success'] ?? false) {
                    $db->prepare('UPDATE oretir_appointments SET survey_sent = 1 WHERE id = ?')
                       ->execute([$appt['id']]);
                    echo "    ✓ Survey sent to {$appt['email']} ({$appt['reference_number']})\n";
                    $sent++;
                } else {
                    echo "    ✗ Email failed for {$appt['email']}: " . ($result['error'] ?? 'unknown') . "\n";
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                error_log("send-surveys: Error for appointment #{$appt['id']}: " . $e->getMessage());
                echo "    ✗ Error for #{$appt['id']}: {$e->getMessage()}\n";
            }
        }
    }

    echo "\nDone: {$sent} sent / {$failed} failed / {$skipped} skipped.\n";
    exit(0);

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("send-surveys error: " . $e->getMessage());
    exit(1);
}
