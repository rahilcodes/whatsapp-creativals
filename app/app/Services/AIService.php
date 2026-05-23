<?php

namespace App\Services;

use App\Models\BotSetting;
use App\Models\ActivityLog;
use App\Models\UserMemory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $model        = 'gpt-4o-mini';
    private ?string $geminiKey;

    public function __construct()
    {
        $this->apiKey   = config('services.openai.key', env('OPENAI_API_KEY', ''));
        $this->geminiKey = env('GEMINI_API_KEY', '');
    }

    // ── Main entry: generate a reply for an incoming message ─
    public function generateReply(string $phone, string $userMessage, array $context): ?array
    {
        // 1. Run the Decision Engine to analyze intent, emotion, state, and objective
        $decision = $this->runDecisionEngine($userMessage, $context);

        // 2. Build the upgraded System Prompt incorporating the Decision Engine state
        $systemPrompt = $this->buildSystemPrompt(
            $context['business_memory'],
            $context['long_term'],
            $decision
        );

        $messages = $this->buildMessages($systemPrompt, $context['short_term'], $userMessage);

        // 3. Generate response using OpenAI (fallback to Gemini)
        $reply = null;
        $quotaExhausted = false;

        if (!empty($this->apiKey)) {
            [$reply, $quotaExhausted] = $this->callOpenAI($messages);
        }

        if ($quotaExhausted && !empty($this->geminiKey)) {
            Log::warning('OpenAI quota exhausted — falling back to Gemini for reply generation');
            $reply = $this->callGemini($messages);
        }

        if (empty($this->apiKey) && empty($this->geminiKey)) {
            Log::error('No AI API key configured (OPENAI_API_KEY or GEMINI_API_KEY)');
            return null;
        }

        if ($reply === null) return null;

        // Perform post-checks (uncertainty detection, length limits, human flags)
        $checkedResult = $this->postCheck($reply);

        // 4. Update the user summary & dynamic preferences/objections/state asynchronously or inline
        $this->updateMemoryDetails($phone, $userMessage, $checkedResult['reply'], $decision, $context['short_term']);

        return $checkedResult;
    }

    // ── Run the Decision Engine ────────────────────────────────
    private function runDecisionEngine(string $userMessage, array $context): array
    {
        // Default values in case API fails
        $defaultDecision = [
            'intent' => 'inquiry',
            'emotion' => 'neutral',
            'state' => 'exploring',
            'objective' => 'inform',
            'strategy' => 'educate + guide',
        ];

        if (empty($this->apiKey) && empty($this->geminiKey)) {
            return $defaultDecision;
        }

        $historyText = '';
        foreach ($context['short_term'] as $msg) {
            $historyText .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n";
        }

        $businessContext = substr($context['business_memory'], 0, 1000); // truncated snippet for speed

        $analysisPrompt = "You are the behavioral analysis core of a high-performance sales agent. Your task is to output a single valid JSON object analyzing the latest user message.

BUSINESS MEMORY HIGHLIGHTS:
{$businessContext}

RECENT CHAT HISTORY:
{$historyText}
User: {$userMessage}

Respond ONLY with a JSON object containing these exact fields:
1. \"intent\": one of [\"greeting\", \"inquiry\", \"pricing_inquiry\", \"service_inquiry\", \"buying_intent\", \"objection\", \"complaint\", \"casual\"]
2. \"emotion\": one of [\"neutral\", \"curious\", \"interested\", \"hesitant\", \"confused\", \"frustrated\", \"urgent\"]
3. \"state\": one of [\"new\", \"exploring\", \"engaged\", \"interested\", \"ready_to_buy\", \"objection\", \"complaint\", \"human_required\"] (determine the state of this customer's lifecycle)
4. \"objective\": one of [\"inform\", \"qualify\", \"build_trust\", \"overcome_objection\", \"convert\", \"escalate\"]
5. \"strategy\": a short phrase on how the bot should behave (e.g., \"psychological reassurance\", \"soft closing step\", \"value positioning\")

Do not include any markup, markdown blocks (like ```json), or conversational filler. Output ONLY raw JSON.";

        $messages = [
            ['role' => 'system', 'content' => 'You are a high-speed behavioral parsing unit. Output only valid raw JSON.'],
            ['role' => 'user', 'content' => $analysisPrompt]
        ];

        $rawJson = null;
        $quotaExhausted = false;

        if (!empty($this->apiKey)) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->withoutVerifying()
                    ->timeout(8)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'       => $this->model,
                        'messages'    => $messages,
                        'max_tokens'  => 120,
                        'temperature' => 0.2,
                        'response_format' => ['type' => 'json_object'],
                    ]);
                if ($response->status() === 429) {
                    $quotaExhausted = true;
                } else if ($response->successful()) {
                    $rawJson = trim($response->json('choices.0.message.content') ?? '');
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI decision engine call failed: ' . $e->getMessage());
            }
        }

        if (($quotaExhausted || empty($this->apiKey)) && !empty($this->geminiKey)) {
            try {
                $rawJson = $this->callGemini($messages);
            } catch (\Throwable $e) {
                Log::warning('Gemini decision engine fallback failed: ' . $e->getMessage());
            }
        }

        if (empty($rawJson)) {
            return $defaultDecision;
        }

        try {
            $parsed = json_decode($rawJson, true);
            if (is_array($parsed) && isset($parsed['intent'])) {
                return array_merge($defaultDecision, $parsed);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to parse decision engine JSON: ' . $rawJson);
        }

        return $defaultDecision;
    }

    // ── Build the system prompt ───────────────────────────────
    private function buildSystemPrompt(string $businessMemory, ?string $userSummary, array $decision): string
    {
        $basePrompt = BotSetting::get('system_prompt',
            'You are a friendly, professional business assistant. Keep replies concise, warm, and helpful.'
        );

        $prompt = $basePrompt . "\n\n";

        $prompt .= "=== BUSINESS INFORMATION & GUIDELINES ===\n";
        $prompt .= $businessMemory . "\n\n";

        if ($userSummary) {
            $prompt .= "=== WHAT WE KNOW ABOUT THIS USER ===\n";
            $prompt .= $userSummary . "\n\n";
        }

        $prompt .= "=== REAL-TIME DECISION ANALYSIS ===\n";
        $prompt .= "- **Detected User Intent**: " . strtoupper($decision['intent']) . "\n";
        $prompt .= "- **Detected User Emotion**: " . strtoupper($decision['emotion']) . "\n";
        $prompt .= "- **Current Lifecycle State**: " . strtoupper($decision['state']) . "\n";
        $prompt .= "- **Bot Objective for this reply**: " . strtoupper($decision['objective']) . "\n";
        $prompt .= "- **Target Strategy**: " . strtoupper($decision['strategy']) . "\n\n";

        $prompt .= "=== RESPONSE STRATEGY DIRECTIVES ===\n";
        $prompt .= "1. **Adopt target strategy**: Follow the strategy of '" . $decision['strategy'] . "' to steer the user toward '" . $decision['objective'] . "'.\n";
        $prompt .= "2. **Handle objections psychologically**: If intent is OBJECTION or state is OBJECTION, acknowledge their concern, validate it, re-position the value proposition (referring to business memories), and close with a low-pressure question.\n";
        $prompt .= "3. **Persuasion & Conversion**: Use natural closes (e.g. assumption close, suggesting next step naturally, referencing client success or social proof implicitly like 'Most businesses we work with prefer...').\n";
        $prompt .= "4. **Wow Moments**: Craft replies that feel customized. Do not just regurgitate facts. Connect their stated constraints or problems to a solution. (e.g., 'Since you mentioned [concern], I recommend...').\n\n";

        $prompt .= "=== CONSTANT RULES ===\n";
        $prompt .= "- Reply in the SAME language the user writes in.\n";
        $prompt .= "- Keep replies SHORT (1–4 lines max). Do NOT write paragraphs.\n";
        $prompt .= "- Match user's language, be extremely warm, natural, and human-like — avoid sounding robotic.\n";
        $prompt .= "- Never reveal you are an AI. Act as a human executive.\n";
        $prompt .= "- If you don't know something, say you'll check and get back to them.\n";
        $prompt .= "- Do NOT use markdown formatting (no bold **, no headers #, no bullet formatting).\n";
        $prompt .= "- Use emojis occasionally to feel friendly 😊.\n";

        return $prompt;
    }

    // ── Build OpenAI messages array ───────────────────────────
    private function buildMessages(string $systemPrompt, array $shortTermMemory, string $userMessage): array
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Add conversation history
        foreach ($shortTermMemory as $msg) {
            $messages[] = [
                'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        // Add the latest user message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    // ── Call OpenAI API — returns [reply|null, isQuotaError] ────
    private function callOpenAI(array $messages): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => 250,
                    'temperature' => 0.75,
                    'top_p'       => 0.9,
                ]);

            if ($response->status() === 429) {
                Log::warning('OpenAI quota/rate limit hit (429) — will try fallback', [
                    'body' => substr($response->body(), 0, 300),
                ]);
                return [null, true]; // [reply, quotaExhausted]
            }

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return [null, false];
            }

            $text = trim($response->json('choices.0.message.content') ?? '');
            return [$text ?: null, false];

        } catch (\Throwable $e) {
            Log::error('OpenAI exception', ['error' => $e->getMessage()]);
            return [null, false];
        }
    }

    // ── Call Google Gemini (free tier fallback) ─────────────────
    private function callGemini(array $messages): ?string
    {
        try {
            // Convert OpenAI message format to Gemini format
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
                    'maxOutputTokens' => 250,
                    'temperature'     => 0.75,
                ],
            ];

            $response = Http::withoutVerifying()
                ->timeout(20)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->geminiKey,
                    $payload
                );

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return null;
            }

            return trim($response->json('candidates.0.content.parts.0.text') ?? '');

        } catch (\Throwable $e) {
            Log::error('Gemini exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Post-check: validate & flag if needed ─────────────────
    private function postCheck(string $reply): array
    {
        $flagHuman  = false;
        $flagReason = null;

        // Strip markdown formatting if any was generated
        $reply = str_replace(['**', '##', '# ', '### '], '', $reply);

        // Too long → truncate at sentence boundary
        if (strlen($reply) > 500) {
            $reply      = $this->truncateAtSentence($reply, 450) . '..';
        }

        // Uncertain phrases → flag for human
        $uncertainPhrases = [
            "i don't know", "i'm not sure", "i cannot", "i can't help",
            "i do not know", "not sure", "unable to", "i apologize, but i",
        ];
        $lowerReply = strtolower($reply);
        foreach ($uncertainPhrases as $phrase) {
            if (str_contains($lowerReply, $phrase)) {
                $flagHuman  = true;
                $flagReason = 'ai_uncertain';
                break;
            }
        }

        // Empty reply → flag
        if (empty(trim($reply))) {
            $flagHuman  = true;
            $flagReason = 'empty_reply';
        }

        return [
            'reply'       => $reply,
            'flag_human'  => $flagHuman,
            'flag_reason' => $flagReason,
        ];
    }

    // ── Truncate at last sentence boundary ───────────────────
    private function truncateAtSentence(string $text, int $maxLen): string
    {
        if (strlen($text) <= $maxLen) return $text;
        $truncated = substr($text, 0, $maxLen);
        $lastPeriod = max(strrpos($truncated, '.'), strrpos($truncated, '!'), strrpos($truncated, '?'));
        return $lastPeriod > 0 ? substr($truncated, 0, $lastPeriod + 1) : $truncated;
    }

    // ── Update memory details ────────────────────────────────
    private function updateMemoryDetails(string $phone, string $userMessage, string $botReply, array $decision, array $shortTerm): void
    {
        if (empty($this->apiKey) && empty($this->geminiKey)) return;

        // Reconstruct conversation log for summary input
        $historyText = '';
        foreach ($shortTerm as $msg) {
            $historyText .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n";
        }
        $historyText .= "User: " . $userMessage . "\n";
        $historyText .= "Assistant: " . $botReply . "\n";

        // Query LLM to generate an upgraded long-term summary incorporating preferences, objections, and conversation state
        $summaryPrompt = "You are a customer intelligence unit. Based on the transaction history below, update the short customer profile summary.
Include:
- What they need or are exploring
- Their emotional state & attitude
- Any specific objections they raised (e.g. price, timing, safety)
- Preferences they mentioned (e.g. contact hours, plan choice)
- Current sales lifecycle state: " . strtoupper($decision['state']) . "

Keep the summary extremely concise (2-4 sentences max).

RECENT HISTORY LOG:
{$historyText}

Old Summary:
" . (UserMemory::where('phone', $phone)->first()?->summary ?? 'None yet.') . "

Generate the updated summary:";

        $messages = [
            ['role' => 'system', 'content' => 'You are a customer database updates engine. Be concise and factual.'],
            ['role' => 'user', 'content' => $summaryPrompt]
        ];

        $updatedSummary = null;
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
                        'temperature' => 0.4,
                    ]);
                if ($response->status() === 429) {
                    $quotaExhausted = true;
                } else if ($response->successful()) {
                    $updatedSummary = trim($response->json('choices.0.message.content') ?? '');
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to generate memory update: ' . $e->getMessage());
            }
        }

        if (($quotaExhausted || empty($this->apiKey)) && !empty($this->geminiKey)) {
            try {
                $updatedSummary = $this->callGemini($messages);
            } catch (\Throwable $e) {
                Log::warning('Failed Gemini fallback memory update: ' . $e->getMessage());
            }
        }

        if (!empty($updatedSummary)) {
            UserMemory::updateOrCreate(
                ['phone' => $phone],
                [
                    'summary' => $updatedSummary,
                    'last_activity_at' => now()
                ]
            );
        }
    }

    // ── Generate a user summary from conversation history ────
    public function generateUserSummary(string $phone, array $messages): ?string
    {
        if (empty($messages) || empty($this->apiKey)) return null;

        $history = implode("\n", array_map(
            fn($m) => ucfirst($m['role']) . ': ' . $m['content'],
            $messages
        ));

        $prompt = "Summarize what this customer has asked about and what we know about them in 2–3 sentences. Be factual and brief.\n\nConversation:\n{$history}";

        $result = $this->callOpenAI([
            ['role' => 'system', 'content' => 'You are a helpful assistant that creates brief customer summaries.'],
            ['role' => 'user',   'content' => $prompt],
        ]);

        return is_array($result) ? $result[0] : $result;
    }
}

