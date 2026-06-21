<?php
namespace Tests;

use App\Services\InsightService;

class InsightServiceTest extends TestRunner
{
    private InsightService $service;

    protected function setUp(): void
    {
        $this->service = new InsightService();
    }

    public function testGetInsightsReturnsDefaultWhenNoData()
    {
        $insights = $this->service->getInsights(99999);
        $this->assertIsArray($insights);
        $this->assertNotEmpty($insights);

        $types = array_column($insights, 'type');
        $this->assertContains('strength', $types);
    }

    public function testGetInsightsIncludesStrength()
    {
        $insights = $this->service->getInsights(99999);
        $strengths = array_filter($insights, fn($i) => $i['type'] === 'strength');
        $this->assertNotEmpty($strengths);
        $strength = reset($strengths);
        $this->assertArrayHasKey('title', $strength);
        $this->assertArrayHasKey('description', $strength);
        $this->assertArrayHasKey('icon', $strength);
    }

    public function testGetInsightsRecommendationWithNoQuiz()
    {
        $insights = $this->service->getInsights(99999);
        $recs = array_filter($insights, fn($i) => $i['type'] === 'recommendation');
        if (!empty($recs)) {
            $rec = reset($recs);
            $this->assertMatchesRegex('/screening|quiz|assessment/i', $rec['title'] . ' ' . $rec['description']);
        }
    }

    public function testGetInsightsForChildWithProgress()
    {
        $db = self::getDb();
        $childId = self::insertFixture('children', [
            'parent_id' => 1, 'name' => 'Insight Child', 'age' => 4,
        ]);

        $activityId = self::insertFixture('activities', [
            'title' => 'Test Puzzle', 'category' => 'puzzles',
            'difficulty' => 'easy',
        ]);

        $stmt = $db->prepare('INSERT INTO child_progress (child_id, activity_id, score) VALUES (?, ?, ?)');
        $stmt->execute([$childId, $activityId, 90]);

        $insights = $this->service->getInsights($childId);
        $this->assertNotEmpty($insights);

        $strengths = array_filter($insights, fn($i) => $i['type'] === 'strength');
        $this->assertNotEmpty($strengths);

        $db->prepare('DELETE FROM child_progress WHERE child_id = ?')->execute([$childId]);
        $db->prepare('DELETE FROM activities WHERE id = ?')->execute([$activityId]);
        $db->prepare('DELETE FROM children WHERE id = ?')->execute([$childId]);
    }
}
