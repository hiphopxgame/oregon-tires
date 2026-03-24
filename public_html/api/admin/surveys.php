<?php
/**
 * Oregon Tires — Admin Survey CRUD
 * GET    — list surveys + questions
 * POST   — create/update survey + questions
 * DELETE — deactivate survey
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requirePermission('marketing');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        session_write_close();

        $surveyId = (int) ($_GET['id'] ?? 0);

        if ($surveyId > 0) {
            // Single survey with questions
            $stmt = $db->prepare('SELECT * FROM oretir_surveys WHERE id = ?');
            $stmt->execute([$surveyId]);
            $survey = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$survey) jsonError('Survey not found', 404);

            $qStmt = $db->prepare(
                'SELECT * FROM oretir_survey_questions WHERE survey_id = ? ORDER BY sort_order ASC'
            );
            $qStmt->execute([$surveyId]);
            $survey['questions'] = $qStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Response count
            $rStmt = $db->prepare('SELECT COUNT(*) FROM oretir_survey_responses WHERE survey_id = ? AND completed_at IS NOT NULL');
            $rStmt->execute([$surveyId]);
            $survey['response_count'] = (int) $rStmt->fetchColumn();

            jsonSuccess($survey);
        }

        // List all surveys
        $stmt = $db->query(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM oretir_survey_questions WHERE survey_id = s.id) AS question_count,
                    (SELECT COUNT(*) FROM oretir_survey_responses WHERE survey_id = s.id AND completed_at IS NOT NULL) AS response_count
             FROM oretir_surveys s
             ORDER BY s.created_at DESC'
        );
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    verifyCsrf();
    $data = getJsonBody();

    if ($method === 'POST') {
        $titleEn = sanitize((string) ($data['title_en'] ?? ''), 300);
        if ($titleEn === '') jsonError('Survey title is required.');

        $stmt = $db->prepare(
            'INSERT INTO oretir_surveys (title_en, title_es, description_en, description_es, trigger_event, delay_hours, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $titleEn,
            sanitize((string) ($data['title_es'] ?? ''), 300),
            sanitize((string) ($data['description_en'] ?? ''), 2000),
            sanitize((string) ($data['description_es'] ?? ''), 2000),
            sanitize((string) ($data['trigger_event'] ?? 'ro_completed'), 50),
            max(1, (int) ($data['delay_hours'] ?? 24)),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        $surveyId = (int) $db->lastInsertId();

        // Add questions
        if (!empty($data['questions']) && is_array($data['questions'])) {
            saveQuestions($db, $surveyId, $data['questions']);
        }

        jsonSuccess(['id' => $surveyId], 201);
    }

    if ($method === 'PUT') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing survey id.');

        $titleEn = sanitize((string) ($data['title_en'] ?? ''), 300);
        if ($titleEn === '') jsonError('Survey title is required.');

        $stmt = $db->prepare(
            'UPDATE oretir_surveys SET title_en = ?, title_es = ?, description_en = ?, description_es = ?, trigger_event = ?, delay_hours = ?, is_active = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            $titleEn,
            sanitize((string) ($data['title_es'] ?? ''), 300),
            sanitize((string) ($data['description_en'] ?? ''), 2000),
            sanitize((string) ($data['description_es'] ?? ''), 2000),
            sanitize((string) ($data['trigger_event'] ?? 'ro_completed'), 50),
            max(1, (int) ($data['delay_hours'] ?? 24)),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);

        // Replace questions
        if (isset($data['questions']) && is_array($data['questions'])) {
            $db->prepare('DELETE FROM oretir_survey_questions WHERE survey_id = ?')->execute([$id]);
            saveQuestions($db, $id, $data['questions']);
        }

        jsonSuccess(['updated' => true]);
    }

    if ($method === 'DELETE') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing survey id.');

        $db->prepare('UPDATE oretir_surveys SET is_active = 0, updated_at = NOW() WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log("Admin surveys error: " . $e->getMessage());
    jsonError('Server error', 500);
}

function saveQuestions(PDO $db, int $surveyId, array $questions): void
{
    $stmt = $db->prepare(
        'INSERT INTO oretir_survey_questions (survey_id, question_en, question_es, question_type, options_json, sort_order, is_required)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($questions as $i => $q) {
        $type = $q['question_type'] ?? 'rating';
        $validTypes = ['rating', 'nps', 'text', 'multiple_choice'];
        if (!in_array($type, $validTypes, true)) $type = 'rating';

        $optionsJson = null;
        if ($type === 'multiple_choice' && !empty($q['options'])) {
            $optionsJson = json_encode($q['options']);
        }

        $stmt->execute([
            $surveyId,
            sanitize((string) ($q['question_en'] ?? ''), 500),
            sanitize((string) ($q['question_es'] ?? ''), 500),
            $type,
            $optionsJson,
            (int) ($q['sort_order'] ?? $i + 1),
            isset($q['is_required']) ? ((int) $q['is_required']) : 1,
        ]);
    }
}
