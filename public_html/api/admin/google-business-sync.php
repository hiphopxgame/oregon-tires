<?php
/**
 * Oregon Tires — Admin Google Business Profile Sync
 * GET  — fetch current sync status, insights, Q&A
 * POST — trigger sync (hours, insights, Q&A)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/google-business.php';

try {
    $staff = requirePermission('marketing');
    requireMethod('GET', 'POST');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        session_write_close();

        $section = $_GET['section'] ?? 'overview';

        if ($section === 'insights') {
            $days = max(7, min(90, (int) ($_GET['days'] ?? 30)));
            $stmt = $db->prepare(
                'SELECT * FROM oretir_gbp_insights
                 WHERE metric_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 ORDER BY metric_date ASC'
            );
            $stmt->execute([$days]);
            jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

        if ($section === 'qna') {
            $stmt = $db->query('SELECT * FROM oretir_gbp_qna ORDER BY status ASC, asked_at DESC LIMIT 100');
            jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

        if ($section === 'posts') {
            $stmt = $db->query("SELECT * FROM oretir_gbp_posts WHERE status != 'deleted' ORDER BY created_at DESC LIMIT 50");
            jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

        // Overview
        $configured = !empty($_ENV['GOOGLE_GBP_ACCOUNT_ID']) && !empty($_ENV['GOOGLE_GBP_LOCATION_ID']);

        $postCountStmt = $db->query("SELECT COUNT(*) FROM oretir_gbp_posts WHERE status = 'published'");
        $publishedPosts = (int) $postCountStmt->fetchColumn();

        $unansweredStmt = $db->query("SELECT COUNT(*) FROM oretir_gbp_qna WHERE status = 'unanswered'");
        $unansweredQna = (int) $unansweredStmt->fetchColumn();

        $latestInsightStmt = $db->query('SELECT MAX(metric_date) FROM oretir_gbp_insights');
        $latestInsight = $latestInsightStmt->fetchColumn();

        // Weekly totals
        $weekStmt = $db->query(
            'SELECT SUM(views_search) AS search, SUM(views_maps) AS maps,
                    SUM(clicks_website) AS website, SUM(clicks_directions) AS directions,
                    SUM(clicks_phone) AS phone
             FROM oretir_gbp_insights
             WHERE metric_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
        );
        $weekTotals = $weekStmt->fetch(\PDO::FETCH_ASSOC);

        jsonSuccess([
            'configured' => $configured,
            'published_posts' => $publishedPosts,
            'unanswered_qna' => $unansweredQna,
            'latest_insight_date' => $latestInsight,
            'week_totals' => $weekTotals,
        ]);
    }

    // POST: trigger sync
    verifyCsrf();
    $data = getJsonBody();
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'sync_hours':
            $result = syncBusinessHours($db);
            jsonSuccess($result);
            break;

        case 'fetch_insights':
            $result = fetchGbpInsights($db);
            jsonSuccess($result);
            break;

        case 'fetch_qna':
            $result = fetchGbpQnA($db);
            jsonSuccess($result);
            break;

        case 'answer_question':
            $qId = (int) ($data['question_id'] ?? 0);
            $answer = sanitize((string) ($data['answer'] ?? ''), 2000);
            if ($qId <= 0 || $answer === '') jsonError('Question ID and answer required.');

            $db->prepare(
                "UPDATE oretir_gbp_qna SET answer_text = ?, status = 'answered', answered_at = NOW(), updated_at = NOW() WHERE id = ?"
            )->execute([$answer, $qId]);
            jsonSuccess(['answered' => true]);
            break;

        case 'sync_all':
            $results = [];
            $results['hours'] = syncBusinessHours($db);
            $results['insights'] = fetchGbpInsights($db);
            $results['qna'] = fetchGbpQnA($db);
            jsonSuccess($results);
            break;

        default:
            jsonError('Invalid action', 400);
    }

} catch (\Throwable $e) {
    error_log("Admin google-business-sync error: " . $e->getMessage());
    jsonError('Server error', 500);
}
