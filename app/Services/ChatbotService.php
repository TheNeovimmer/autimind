<?php
namespace App\Services;
use App\Core\Database;
class ChatbotService
{
    public function getResponse(string $message, int $userId): string
    {
        $this->saveMessage($userId, $message, 'user');
        $response = $this->findMatch($message);
        if (!$response) {
            $response = "I'm not sure I understand. Could you rephrase that? You can ask me about autism, screening, progress tracking, appointments, messaging, or our pricing plans.";
        }
        $this->saveMessage($userId, $response, 'bot');
        return $response;
    }
    private function findMatch(string $message): ?string
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM chatbot_responses WHERE is_active = 1');
        $responses = $stmt ? $stmt->fetchAll() : [];
        $messageLower = mb_strtolower($message);
        $bestMatch = null;
        $bestScore = 0;
        foreach ($responses as $response) {
            $keywords = json_decode($response['keywords'], true);
            if (!is_array($keywords)) continue;
            $score = 0;
            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower(trim($keyword));
                if (str_contains($messageLower, $keyword)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $response['response_text'];
            }
        }
        return $bestMatch;
    }
    private function saveMessage(int $userId, string $message, string $sender): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO chat_history (user_id, message, sender) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $message, $sender]);
    }
    public function getHistory(int $userId, int $limit = 50): array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
