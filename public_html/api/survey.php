<?php
/**
 * Oregon Tires — Public Survey API
 * GET  ?token=abc123 — Load survey questions by response token
 * POST              — Submit survey answers
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET', 'POST');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $token = $_GET['token'] ?? '';
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            jsonError('Invalid survey link.', 400);
        }

        // Load response + survey + questions
        $stmt = $db->prepare(
            'SELECT sr.id AS response_id, sr.survey_id, sr.completed_at,
                    s.title_en, s.title_es, s.description_en, s.description_es
             FROM oretir_survey_responses sr
             JOIN oretir_surveys s ON sr.survey_id = s.id
             WHERE sr.token = ?'
        );
        $stmt->execute([$token]);
        $response = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$response) {
            jsonError('Invalid or expired survey link.', 404);
        }

        if ($response['completed_at']) {
            jsonSuccess([
                'completed' => true,
                'title_en' => $response['title_en'],
                'title_es' => $response['title_es'],
            ]);
            return;
        }

        // Load questions
        $qStmt = $db->prepare(
            'SELECT id, question_en, question_es, question_type, options_json, sort_order, is_required
             FROM oretir_survey_questions
             WHERE survey_id = ?
             ORDER BY sort_order ASC'
        );
        $qStmt->execute([$response['survey_id']]);
        $questions = $qStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode options_json
        foreach ($questions as &$q) {
            if ($q['options_json']) {
                $q['options'] = json_decode($q['options_json'], true);
            } else {
                $q['options'] = null;
            }
            unset($q['options_json']);
        }

        // Mark as started
        $db->prepare('UPDATE oretir_survey_responses SET started_at = COALESCE(started_at, NOW()) WHERE id = ?')
           ->execute([$response['response_id']]);

        jsonSuccess([
            'completed' => false,
            'response_id' => (int) $response['response_id'],
            'title_en' => $response['title_en'],
            'title_es' => $response['title_es'],
            'description_en' => $response['description_en'],
            'description_es' => $response['description_es'],
            'questions' => $questions,
        ]);
    }

    // POST: Submit answers
    checkRateLimit('survey_submit', 10, 3600);

    $data = getJsonBody();
    $token = $data['token'] ?? '';

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        jsonError('Invalid survey link.', 400);
    }

    $stmt = $db->prepare(
        'SELECT sr.id AS response_id, sr.survey_id, sr.completed_at
         FROM oretir_survey_responses sr
         WHERE sr.token = ?'
    );
    $stmt->execute([$token]);
    $response = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$response) {
        jsonError('Invalid survey link.', 404);
    }

    if ($response['completed_at']) {
        jsonError('This survey has already been completed.', 409);
    }

    $answers = $data['answers'] ?? [];
    if (!is_array($answers) || empty($answers)) {
        jsonError('No answers provided.');
    }

    // Validate question IDs belong to this survey
    $qStmt = $db->prepare(
        'SELECT id, question_type, is_required FROM oretir_survey_questions WHERE survey_id = ?'
    );
    $qStmt->execute([$response['survey_id']]);
    $validQuestions = [];
    foreach ($qStmt->fetchAll(\PDO::FETCH_ASSOC) as $q) {
        $validQuestions[(int) $q['id']] = $q;
    }

    // Check required questions
    foreach ($validQuestions as $qId => $q) {
        if ((int) $q['is_required'] && !isset($answers[$qId]) && !isset($answers[(string) $qId])) {
            jsonError('Please answer all required questions.');
        }
    }

    // Insert answers
    $ansStmt = $db->prepare(
        'INSERT INTO oretir_survey_answers (response_id, question_id, rating_value, text_value) VALUES (?, ?, ?, ?)'
    );

    foreach ($answers as $questionId => $answer) {
        $qId = (int) $questionId;
        if (!isset($validQuestions[$qId])) continue;

        $type = $validQuestions[$qId]['question_type'];
        $ratingValue = null;
        $textValue = null;

        if ($type === 'rating' || $type === 'nps') {
            $ratingValue = max(0, min($type === 'nps' ? 10 : 5, (int) $answer));
        } elseif ($type === 'text') {
            $textValue = sanitize((string) $answer, 2000);
        } elseif ($type === 'multiple_choice') {
            $textValue = sanitize((string) $answer, 500);
        }

        $ansStmt->execute([$response['response_id'], $qId, $ratingValue, $textValue]);
    }

    // Mark complete
    $db->prepare('UPDATE oretir_survey_responses SET completed_at = NOW() WHERE id = ?')
       ->execute([$response['response_id']]);

    jsonSuccess(['message' => 'Thank you for your feedback!']);

} catch (\Throwable $e) {
    error_log("Survey API error: " . $e->getMessage());
    jsonError('Server error', 500);
}
