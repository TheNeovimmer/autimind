<?php
namespace Tests;

use App\Services\ChatbotService;

class ChatbotServiceTest extends TestRunner
{
    private ChatbotService $service;

    protected function setUp(): void
    {
        $this->service = new ChatbotService();
    }

    public function testGetResponseReturnsString()
    {
        $response = $this->service->getResponse('hello', 1);
        $this->assertNotNull($response);
        $this->assertNotEmpty($response);
    }

    public function testGetResponseMatchesKeywordHello()
    {
        $response = $this->service->getResponse('hello', 1);
        $this->assertMatchesRegex('/hello|Hi|Hello|AutiMind/i', $response);
    }

    public function testGetResponseMatchesKeywordAutism()
    {
        $response = $this->service->getResponse('tell me about autism', 1);
        $this->assertMatchesRegex('/autism|spectrum|ASD|developmental/i', $response);
    }

    public function testGetResponseMatchesKeywordScreening()
    {
        $response = $this->service->getResponse('take a screening quiz', 1);
        $this->assertMatchesRegex('/quiz|screening|assessment|test/i', $response);
    }

    public function testGetResponseMatchesKeywordAppointment()
    {
        $response = $this->service->getResponse('book an appointment', 1);
        $this->assertMatchesRegex('/appointment|booking|schedule|specialist/i', $response);
    }

    public function testGetResponseMatchesKeywordPricing()
    {
        $response = $this->service->getResponse('how much does it cost', 1);
        $this->assertMatchesRegex('/plan|pricing|subscription|Standard|Premium|Family/i', $response);
    }

    public function testGetResponseReturnsFallbackForUnknown()
    {
        $response = $this->service->getResponse('xyznonexistentkeyword12345', 1);
        $this->assertMatchesRegex('/not sure|rephrase|ask me about/i', $response);
    }

    public function testGetResponseSavesMessageToHistory()
    {
        $userFixtureId = self::insertFixture('users', [
            'role' => 'parent', 'name' => 'Chatbot Tester',
            'email' => 'chatbot_test_' . time() . '@test.com',
            'password' => 'password',
        ]);

        $this->service->getResponse('hello', $userFixtureId);

        $history = $this->service->getHistory($userFixtureId);
        $this->assertNotEmpty($history);
        $this->assertGreaterThanOrEqual(2, count($history));

        $foundUser = false;
        $foundBot = false;
        foreach ($history as $entry) {
            if ($entry['sender'] === 'user') $foundUser = true;
            if ($entry['sender'] === 'bot') $foundBot = true;
        }
        $this->assertTrue($foundUser, 'Should have user message in history');
        $this->assertTrue($foundBot, 'Should have bot message in history');

        $db = self::getDb();
        $db->prepare('DELETE FROM chat_history WHERE user_id = ?')->execute([$userFixtureId]);
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userFixtureId]);
    }

    public function testGetHistoryReturnsEmptyForNewUser()
    {
        $history = $this->service->getHistory(999999);
        $this->assertIsArray($history);
        $this->assertEmpty($history);
    }
}
