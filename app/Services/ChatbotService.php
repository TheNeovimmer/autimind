<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Env;

class ChatbotService
{
    public function getResponse(string $message, int $userId): string
    {
        $this->saveMessage($userId, $message, 'user');

        $response = $this->callOpenRouter($message);

        $this->saveMessage($userId, $response, 'bot');
        return $response;
    }

    private function callOpenRouter(string $message): string
    {
        $apiKey = Env::get('OPENROUTER_API_KEY', '');
        $model = Env::get('OPENROUTER_MODEL', 'google/gemma-4-31b-it:free');

        if (empty($apiKey)) {
            $keywordResponse = $this->findMatch($message);
            return $keywordResponse ?: "I'm not sure I understand. Could you rephrase that? You can ask me about autism, screening, progress tracking, appointments, messaging, or our pricing plans.";
        }

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: AutiMind',
                'X-Title: AutiMind',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are AutiMind AI, a helpful assistant specialized in autism spectrum disorder support, child development, and parenting resources. Provide accurate, compassionate, and practical responses. Keep responses concise and supportive.'],
                    ['role' => 'user', 'content' => $message],
                ],
            ]),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'AutiMind/1.0',
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$result) {
            $keywordResponse = $this->findMatch($message);
            return $keywordResponse ?: "I'm having trouble connecting right now. Please try again in a moment.";
        }

        $data = json_decode($result, true);

        if (isset($data['error'])) {
            $keywordResponse = $this->findMatch($message);
            return $keywordResponse ?: "I'm having trouble connecting right now. Please try again in a moment.";
        }

        return $data['choices'][0]['message']['content'] ?? "I'm not sure how to respond to that. Could you rephrase?";
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
