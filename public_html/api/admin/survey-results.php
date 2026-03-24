<?php
/**
 * Oregon Tires — Admin Survey Results / Analytics
 * GET — NPS score, averages, trends, per-question breakdown
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/survey.php';

try {
    $staff = requirePermission('marketing');
    requireMethod('GET');
    session_write_close();
    $db = getDB();

    $surveyId = (int) ($_GET['survey_id'] ?? 0);
    $days = max(7, min(365, (int) ($_GET['days'] ?? 30)));

    $where = 'sr.completed_at IS NOT NULL';
    $params = [];

    if ($surveyId > 0) {
        $where .= ' AND sr.survey_id = ?';
        $params[] = $surveyId;
    }

    $where .= ' AND sr.completed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)';
    $params[] = $days;

    // Total responses
    $countStmt = $db->prepare("SELECT COUNT(*) FROM oretir_survey_responses sr WHERE {$where}");
    $countStmt->execute($params);
    $totalResponses = (int) $countStmt->fetchColumn();

    // NPS scores
    $npsStmt = $db->prepare(
        "SELECT sa.rating_value
         FROM oretir_survey_answers sa
         JOIN oretir_survey_responses sr ON sa.response_id = sr.id
         JOIN oretir_survey_questions sq ON sa.question_id = sq.id
         WHERE {$where} AND sq.question_type = 'nps' AND sa.rating_value IS NOT NULL"
    );
    $npsStmt->execute($params);
    $npsScores = $npsStmt->fetchAll(\PDO::FETCH_COLUMN);
    $npsData = calculateNPS($npsScores);

    // Average satisfaction (rating questions)
    $avgStmt = $db->prepare(
        "SELECT AVG(sa.rating_value)
         FROM oretir_survey_answers sa
         JOIN oretir_survey_responses sr ON sa.response_id = sr.id
         JOIN oretir_survey_questions sq ON sa.question_id = sq.id
         WHERE {$where} AND sq.question_type = 'rating' AND sa.rating_value IS NOT NULL"
    );
    $avgStmt->execute($params);
    $avgSatisfaction = $avgStmt->fetchColumn();
    $avgSatisfaction = $avgSatisfaction !== false ? round((float) $avgSatisfaction, 2) : null;

    // Per-question breakdown
    $qStmt = $db->prepare(
        "SELECT sq.id, sq.question_en, sq.question_es, sq.question_type,
                AVG(sa.rating_value) AS avg_rating,
                COUNT(sa.id) AS answer_count,
                MIN(sa.rating_value) AS min_rating,
                MAX(sa.rating_value) AS max_rating
         FROM oretir_survey_questions sq
         LEFT JOIN oretir_survey_answers sa ON sq.id = sa.question_id
         LEFT JOIN oretir_survey_responses sr ON sa.response_id = sr.id AND {$where}
         " . ($surveyId > 0 ? 'WHERE sq.survey_id = ?' : '') . "
         GROUP BY sq.id
         ORDER BY sq.sort_order ASC"
    );
    $qParams = $surveyId > 0 ? array_merge($params, [$surveyId]) : $params;
    $qStmt->execute($qParams);
    $questionBreakdown = $qStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Trend: daily response count over period
    $trendStmt = $db->prepare(
        "SELECT DATE(sr.completed_at) AS date, COUNT(*) AS count
         FROM oretir_survey_responses sr
         WHERE {$where}
         GROUP BY DATE(sr.completed_at)
         ORDER BY date ASC"
    );
    $trendStmt->execute($params);
    $trend = $trendStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Recent text responses
    $textStmt = $db->prepare(
        "SELECT sa.text_value, sq.question_en, sr.completed_at,
                CONCAT(c.first_name, ' ', c.last_name) AS customer_name
         FROM oretir_survey_answers sa
         JOIN oretir_survey_responses sr ON sa.response_id = sr.id
         JOIN oretir_survey_questions sq ON sa.question_id = sq.id
         LEFT JOIN oretir_customers c ON sr.customer_id = c.id
         WHERE {$where} AND sq.question_type = 'text' AND sa.text_value IS NOT NULL AND sa.text_value != ''
         ORDER BY sr.completed_at DESC
         LIMIT 20"
    );
    $textStmt->execute($params);
    $textResponses = $textStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Low scores (3 or below on satisfaction, 6 or below on NPS) — alerts
    $alertStmt = $db->prepare(
        "SELECT sr.id AS response_id, sr.completed_at,
                CONCAT(c.first_name, ' ', c.last_name) AS customer_name, c.email,
                GROUP_CONCAT(CONCAT(sq.question_en, ': ', sa.rating_value) SEPARATOR ' | ') AS low_scores
         FROM oretir_survey_responses sr
         JOIN oretir_survey_answers sa ON sr.id = sa.response_id
         JOIN oretir_survey_questions sq ON sa.question_id = sq.id
         LEFT JOIN oretir_customers c ON sr.customer_id = c.id
         WHERE {$where}
           AND ((sq.question_type = 'rating' AND sa.rating_value <= 3)
                OR (sq.question_type = 'nps' AND sa.rating_value <= 6))
         GROUP BY sr.id
         ORDER BY sr.completed_at DESC
         LIMIT 10"
    );
    $alertStmt->execute($params);
    $lowScoreAlerts = $alertStmt->fetchAll(\PDO::FETCH_ASSOC);

    jsonSuccess([
        'total_responses' => $totalResponses,
        'nps' => $npsData,
        'avg_satisfaction' => $avgSatisfaction,
        'question_breakdown' => $questionBreakdown,
        'trend' => $trend,
        'text_responses' => $textResponses,
        'low_score_alerts' => $lowScoreAlerts,
        'period_days' => $days,
    ]);

} catch (\Throwable $e) {
    error_log("Admin survey-results error: " . $e->getMessage());
    jsonError('Server error', 500);
}
