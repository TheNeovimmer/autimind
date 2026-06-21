<?php
namespace App\Services;
use App\Core\Database;
class InsightService
{
    public function getInsights(int $childId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT cp.score, a.category, a.title
            FROM child_progress cp
            JOIN activities a ON cp.activity_id = a.id
            WHERE cp.child_id = ?
            ORDER BY cp.score DESC
            LIMIT 5
        ');
        $stmt->execute([$childId]);
        $progressData = $stmt->fetchAll();
        $strength = $this->generateStrengthInsight($progressData);
        $stmt = $db->prepare('
            SELECT total_score, risk_level
            FROM quiz_attempts
            WHERE child_id = ? AND status = "completed"
            ORDER BY completed_at DESC
            LIMIT 1
        ');
        $stmt->execute([$childId]);
        $latestQuiz = $stmt->fetch();
        $recommendation = $this->generateRecommendationInsight($latestQuiz);
        return array_filter([$strength, $recommendation]);
    }
    private function generateStrengthInsight(array $progressData): ?array
    {
        if (empty($progressData)) {
            return [
                'type' => 'strength', 'title' => 'Getting Started',
                'description' => 'Complete some activities with your child to unlock personalized insights about their strengths and progress.',
                'icon' => 'fa-star',
            ];
        }
        $bestCategory = $progressData[0]['category'] ?? 'activities';
        $categoryLabels = [
            'games' => 'interactive games', 'puzzles' => 'puzzle solving',
            'stories' => 'story comprehension', 'video' => 'visual learning', 'coloring' => 'creative activities',
        ];
        return [
            'type' => 'strength',
            'title' => 'Strength: ' . ($categoryLabels[$bestCategory] ?? $bestCategory),
            'description' => 'Your child shows strong engagement in ' . ($categoryLabels[$bestCategory] ?? $bestCategory) . '. Continue to encourage this area of interest.',
            'icon' => 'fa-trophy',
        ];
    }
    private function generateRecommendationInsight(array|false $latestQuiz): ?array
    {
        if (!$latestQuiz) {
            return [
                'type' => 'recommendation', 'title' => 'Complete a Screening',
                'description' => 'Take our developmental screening quiz to get personalized recommendations for your child\'s needs.',
                'icon' => 'fa-lightbulb',
            ];
        }
        $riskLabels = [
            'low' => 'maintain current supportive strategies and continue monitoring development',
            'moderate' => 'consult with a specialist for a comprehensive evaluation',
            'high' => 'seek professional guidance as soon as possible for early intervention',
        ];
        return [
            'type' => 'recommendation',
            'title' => 'Recommendation',
            'description' => 'Based on the latest screening, we recommend you ' . ($riskLabels[$latestQuiz['risk_level']] ?? 'continue monitoring your child\'s development'),
            'icon' => 'fa-lightbulb',
        ];
    }
}
