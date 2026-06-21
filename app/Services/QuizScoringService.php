<?php
namespace App\Services;
use App\Core\Database;
use App\Models\QuizAttempt;
class QuizScoringService
{
    public function calculateScore(array $answers): array
    {
        $totalScore = 0;
        $invertCategories = ['social_communication', 'developmental'];
        $maximumWeight = 5;
        $categoryScores = [
            'social_communication' => 0, 'behavior' => 0, 'sensory' => 0, 'developmental' => 0,
        ];
        $categoryMax = [
            'social_communication' => 15, 'behavior' => 15, 'sensory' => 10, 'developmental' => 10,
        ];
        $db = Database::getInstance();
        foreach ($answers as $questionId => $optionId) {
            $stmt = $db->prepare('
                SELECT qo.weight, qq.category
                FROM quiz_options qo
                JOIN quiz_questions qq ON qo.question_id = qq.id
                WHERE qo.id = ?
            ');
            $stmt->execute([$optionId]);
            $result = $stmt->fetch();
            if ($result) {
                $weight = (int) $result['weight'];
                $category = $result['category'];
                if (in_array($category, $invertCategories)) {
                    $weight = $maximumWeight - $weight;
                }
                $totalScore += $weight;
                if (isset($categoryScores[$category])) {
                    $categoryScores[$category] += $weight;
                }
            }
        }
        $categoryPercentages = [];
        foreach ($categoryScores as $cat => $score) {
            $categoryPercentages[$cat] = $categoryMax[$cat] > 0 ? round(($score / $categoryMax[$cat]) * 100, 1) : 0;
        }
        $riskLevel = $this->mapRiskLevel($totalScore);
        return [
            'total_score' => $totalScore, 'max_score' => 50, 'risk_level' => $riskLevel,
            'category_scores' => $categoryScores, 'category_percentages' => $categoryPercentages, 'category_max' => $categoryMax,
        ];
    }
    public function mapRiskLevel(int $score): string
    {
        if ($score <= 15) return 'low';
        if ($score <= 30) return 'moderate';
        return 'high';
    }
    public function saveAttempt(int $attemptId, array $answers): array
    {
        $result = $this->calculateScore($answers);
        $db = Database::getInstance();
        foreach ($answers as $questionId => $optionId) {
            $stmt = $db->prepare('INSERT INTO quiz_answers (attempt_id, question_id, option_id) VALUES (?, ?, ?)');
            $stmt->execute([$attemptId, (int)$questionId, (int)$optionId]);
        }
        QuizAttempt::complete($attemptId, $result['total_score'], $result['risk_level']);
        return $result;
    }
}
