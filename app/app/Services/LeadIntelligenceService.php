<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeadIntelligenceService
{
    private string $apiKey;
    private string $model = 'gpt-4o-mini';
    private ?string $geminiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY', ''));
        $this->geminiKey = env('GEMINI_API_KEY', '');
    }

    /**
     * Run unified AI analysis to extract lead insights.
     */
    public function analyzeEngagement(string $phone, string $userMessage, array $shortTermHistory): array
    {
        $defaultAnalysis = [
            'name' => null,
            'intent' => 'inquiry',
            'mood' => 'neutral',
            'summary' => 'Customer sent a message regarding services.',
            'human_required' => false,
        ];

        if (empty($this->apiKey) && empty($this->geminiKey)) {
            return $defaultAnalysis;
        }

        // Prepare context history
        $historyText = '';
        foreach ($shortTermHistory as $msg) {
            $historyText .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n";
        }
        $historyText .= "User: " . $userMessage . "\n";

        $prompt = "You are a lead intelligence parser. Analyze the conversation history below and extract lead metrics.

RECENT CONVERSATION HISTORY:
{$historyText}

Respond ONLY with a valid JSON object. Do not include markdown wraps (like ```json). Output exactly this JSON structure:
{
  \"name\": \"<extracted name of the customer if mentioned, else null>\",
  \"intent\": \"<one of [greeting, inquiry, pricing_inquiry, service_inquiry, buying_intent, objection, complaint, casual]>\",
  \"mood\": \"<one of [neutral, curious, interested, hesitant, confused, frustrated, urgent]>\",
  \"summary\": \"<2-3 sentence brief factual summary of what the customer wants/needs>\",
  \"human_required\": <true/false, set true if customer is highly frustrated, explicitly asks for human support, or wants a custom quote>
}";

        $messages = [
            ['role' => 'system', 'content' => 'You are a high-speed data parsing core. Output only raw JSON.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        $rawJson = null;
        $quotaExhausted = false;

        if (!empty($this->apiKey)) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->withoutVerifying()
                    ->timeout(10)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'       => $this->model,
                        'messages'    => $messages,
                        'max_tokens'  => 180,
                        'temperature' => 0.1,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if ($response->status() === 429) {
                    $quotaExhausted = true;
                } else if ($response->successful()) {
                    $rawJson = trim($response->json('choices.0.message.content') ?? '');
                }
            } catch (\Throwable $e) {
                Log::warning('LeadIntelligence OpenAI call failed: ' . $e->getMessage());
            }
        }

        if (($quotaExhausted || empty($this->apiKey)) && !empty($this->geminiKey)) {
            try {
                $rawJson = $this->callGeminiFallback($messages);
            } catch (\Throwable $e) {
                Log::warning('LeadIntelligence Gemini fallback failed: ' . $e->getMessage());
            }
        }

        if (empty($rawJson)) {
            return $defaultAnalysis;
        }

        try {
            $parsed = json_decode($rawJson, true);
            if (is_array($parsed)) {
                return array_merge($defaultAnalysis, $parsed);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to parse lead intelligence JSON: ' . $rawJson);
        }

        return $defaultAnalysis;
    }

    /**
     * Compute a dynamic Lead Score (0 - 100).
     */
    public function calculateLeadScore(string $intent, string $mood, int $messageCount): int
    {
        $score = 0;

        // 1. Intent weighting
        $intentWeights = [
            'buying_intent'    => 40,
            'pricing_inquiry'  => 25,
            'service_inquiry'  => 20,
            'inquiry'          => 10,
            'objection'        => 15,
            'complaint'        => 5,
            'greeting'         => 2,
            'casual'           => 0,
        ];
        $score += $intentWeights[$intent] ?? 10;

        // 2. Emotional/Mood urgency weighting
        $moodWeights = [
            'interested' => 25,
            'urgent'     => 20,
            'curious'    => 15,
            'hesitant'   => 5,
            'neutral'    => 5,
            'confused'   => 2,
            'frustrated' => 0,
        ];
        $score += $moodWeights[$mood] ?? 5;

        // 3. Conversation depth weighting
        if ($messageCount >= 6) {
            $score += 25;
        } elseif ($messageCount >= 3) {
            $score += 15;
        } else {
            $score += 5;
        }

        // Cap within 0-100 boundaries
        return max(0, min(100, $score));
    }

    /**
     * Call Gemini fallback.
     */
    private function callGeminiFallback(array $messages): ?string
    {
        try {
            $contents = [];
            $systemText = '';
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $systemText = $msg['content'];
                    continue;
                }
                $contents[] = [
                    'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            }

            $payload = [
                'systemInstruction' => ['parts' => [['text' => $systemText]]],
                'contents'          => $contents,
                'generationConfig'  => [
                    'maxOutputTokens' => 180,
                    'temperature'     => 0.1,
                ],
            ];

            $response = Http::withoutVerifying()
                ->timeout(12)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->geminiKey,
                    $payload
                );

            if ($response->successful()) {
                return trim($response->json('candidates.0.content.parts.0.text') ?? '');
            }
        } catch (\Throwable $e) {
            Log::error('Gemini fallback exception in LeadIntelligence: ' . $e->getMessage());
        }
        return null;
    }
}
