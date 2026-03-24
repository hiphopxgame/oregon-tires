<?php
/**
 * Oregon Tires — Survey Helpers
 */

declare(strict_types=1);

/**
 * Generate a unique survey response token.
 */
function generateSurveyToken(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Calculate Net Promoter Score from survey answers.
 * NPS = %Promoters (9-10) - %Detractors (0-6)
 *
 * @param array $npsScores Array of integer scores (0-10)
 * @return array{score: int, promoters: int, passives: int, detractors: int, total: int}
 */
function calculateNPS(array $npsScores): array
{
    $total = count($npsScores);
    if ($total === 0) {
        return ['score' => 0, 'promoters' => 0, 'passives' => 0, 'detractors' => 0, 'total' => 0];
    }

    $promoters = 0;
    $passives = 0;
    $detractors = 0;

    foreach ($npsScores as $score) {
        $score = (int) $score;
        if ($score >= 9) {
            $promoters++;
        } elseif ($score >= 7) {
            $passives++;
        } else {
            $detractors++;
        }
    }

    $nps = (int) round(($promoters / $total * 100) - ($detractors / $total * 100));

    return [
        'score' => $nps,
        'promoters' => $promoters,
        'passives' => $passives,
        'detractors' => $detractors,
        'total' => $total,
    ];
}

/**
 * Get the average satisfaction score for a survey.
 */
function getAverageSatisfaction(PDO $db, int $surveyId): ?float
{
    $stmt = $db->prepare(
        'SELECT AVG(sa.rating_value)
         FROM oretir_survey_answers sa
         JOIN oretir_survey_responses sr ON sa.response_id = sr.id
         JOIN oretir_survey_questions sq ON sa.question_id = sq.id
         WHERE sr.survey_id = ? AND sq.question_type = ? AND sa.rating_value IS NOT NULL AND sr.completed_at IS NOT NULL'
    );
    $stmt->execute([$surveyId, 'rating']);
    $avg = $stmt->fetchColumn();
    return $avg !== false ? round((float) $avg, 2) : null;
}

/**
 * Create a survey response record and return the token.
 */
function createSurveyResponse(PDO $db, int $surveyId, int $appointmentId, int $customerId): string
{
    $token = generateSurveyToken();
    $db->prepare(
        'INSERT INTO oretir_survey_responses (survey_id, appointment_id, customer_id, token, created_at)
         VALUES (?, ?, ?, ?, NOW())'
    )->execute([$surveyId, $appointmentId, $customerId, $token]);
    return $token;
}
