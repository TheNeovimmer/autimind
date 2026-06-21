<?php
namespace Tests;

use App\Services\QuizScoringService;

class QuizScoringServiceTest extends TestRunner
{
    private QuizScoringService $service;

    protected function setUp(): void
    {
        $this->service = new QuizScoringService();
    }

    public function testCalculateScoreReturnsCorrectStructure()
    {
        $answers = [1 => 1, 2 => 7, 3 => 13];
        $result = $this->service->calculateScore($answers);
        $this->assertArrayHasKey('total_score', $result);
        $this->assertArrayHasKey('max_score', $result);
        $this->assertArrayHasKey('risk_level', $result);
        $this->assertArrayHasKey('category_scores', $result);
        $this->assertArrayHasKey('category_percentages', $result);
        $this->assertArrayHasKey('category_max', $result);
        $this->assertEquals(50, $result['max_score']);
    }

    public function testCalculateScoreInvertsSocialCommunicationWeights()
    {
        $answers = [
            1 => 6, 2 => 12, 3 => 18,
        ];
        $result = $this->service->calculateScore($answers);
        $this->assertEquals(15, $result['category_scores']['social_communication']);
        $this->assertEquals(100.0, $result['category_percentages']['social_communication']);
    }

    public function testCalculateScoreInvertsDevelopmentalWeights()
    {
        $answers = [9 => 54, 10 => 60];
        $result = $this->service->calculateScore($answers);
        $this->assertEquals(10, $result['category_scores']['developmental']);
        $this->assertEquals(100.0, $result['category_percentages']['developmental']);
    }

    public function testCalculateScoreDoesNotInvertBehaviorWeights()
    {
        $answers = [4 => 19, 5 => 25, 6 => 31];
        $result = $this->service->calculateScore($answers);
        $this->assertEquals(15, $result['category_scores']['behavior']);
        $this->assertEquals(100.0, $result['category_percentages']['behavior']);
    }

    public function testCalculateScoreDoesNotInvertSensoryWeights()
    {
        $answers = [7 => 37, 8 => 43];
        $result = $this->service->calculateScore($answers);
        $this->assertEquals(10, $result['category_scores']['sensory']);
        $this->assertEquals(100.0, $result['category_percentages']['sensory']);
    }

    public function testCalculateScoreWithAllPositiveAnswers()
    {
        $answers = [1 => 1, 2 => 7, 3 => 13, 4 => 24, 5 => 30, 6 => 36, 7 => 42, 8 => 48, 9 => 49, 10 => 55];

        $result = $this->service->calculateScore($answers);
        $this->assertEquals(0, $result['category_scores']['social_communication']);
        $this->assertEquals(0, $result['category_scores']['behavior']);
        $this->assertEquals(0, $result['category_scores']['sensory']);
        $this->assertEquals(0, $result['category_scores']['developmental']);
        $this->assertEquals(0, $result['total_score']);
        $this->assertEquals('low', $result['risk_level']);
    }

    public function testCalculateScoreWithAllNegativeAnswers()
    {
        $answers = [1 => 6, 2 => 12, 3 => 18, 4 => 19, 5 => 25, 6 => 31, 7 => 37, 8 => 43, 9 => 54, 10 => 60];

        $result = $this->service->calculateScore($answers);
        $this->assertEquals(15, $result['category_scores']['social_communication']);
        $this->assertEquals(15, $result['category_scores']['behavior']);
        $this->assertEquals(10, $result['category_scores']['sensory']);
        $this->assertEquals(10, $result['category_scores']['developmental']);
        $this->assertEquals(50, $result['total_score']);
        $this->assertEquals('high', $result['risk_level']);
    }

    public function testCalculateScoreWithMixedAnswers()
    {
        $answers = [
            1 => 1, 2 => 12, 3 => 13, 4 => 24, 5 => 25, 6 => 36,
            7 => 37, 8 => 43, 9 => 49, 10 => 60,
        ];
        $result = $this->service->calculateScore($answers);
        $this->assertEquals(0 + 5 + 0, $result['category_scores']['social_communication']);
        $this->assertEquals(0 + 5 + 0, $result['category_scores']['behavior']);
        $this->assertEquals(5 + 5, $result['category_scores']['sensory']);
        $this->assertEquals(0 + 5, $result['category_scores']['developmental']);
    }

    public function testMapRiskLevelLow()
    {
        $this->assertEquals('low', $this->service->mapRiskLevel(0));
        $this->assertEquals('low', $this->service->mapRiskLevel(15));
    }

    public function testMapRiskLevelModerate()
    {
        $this->assertEquals('moderate', $this->service->mapRiskLevel(16));
        $this->assertEquals('moderate', $this->service->mapRiskLevel(30));
    }

    public function testMapRiskLevelHigh()
    {
        $this->assertEquals('high', $this->service->mapRiskLevel(31));
        $this->assertEquals('high', $this->service->mapRiskLevel(50));
    }

    public function testSaveAttemptStoresAnswersAndCompletes()
    {
        $db = self::getDb();
        $childId = self::insertFixture('children', [
            'parent_id' => 1, 'name' => 'Test Quiz Child', 'age' => 5,
        ]);

        $attemptId = \App\Models\QuizAttempt::create($childId);
        $this->assertGreaterThan(0, $attemptId);

        $answers = [1 => 1, 2 => 7, 3 => 13, 4 => 24, 5 => 30, 6 => 36, 7 => 42, 8 => 48, 9 => 49, 10 => 55];
        $result = $this->service->saveAttempt($attemptId, $answers);

        $this->assertArrayHasKey('total_score', $result);
        $this->assertArrayHasKey('risk_level', $result);

        $stmt = $db->prepare('SELECT COUNT(*) FROM quiz_answers WHERE attempt_id = ?');
        $stmt->execute([$attemptId]);
        $this->assertEquals(10, (int)$stmt->fetchColumn());

        $attempt = \App\Models\QuizAttempt::findById($attemptId);
        $this->assertEquals('completed', $attempt['status']);
        $this->assertNotNull($attempt['completed_at']);

        \App\Models\QuizAttempt::delete($attemptId);
        $db->prepare('DELETE FROM children WHERE id = ?')->execute([$childId]);
    }
}
