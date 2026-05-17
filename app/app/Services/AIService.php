<?php

namespace App\Services;

use App\Models\BotSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $model = 'gpt-4o-mini';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY', ''));
    }

    // ── Main entry: generate a reply for an incoming message ─
    public function generateReply(string $phone, string $userMessage, array $context): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API key not configured');
            return null;
        }

        $systemPrompt = $this->buildSystemPrompt(
            $context['business_memory'],
            $context['long_term']
        );

        $messages = $this->buildMessages($systemPrompt, $context['short_term'], $userMessage);

        $reply = $this->callOpenAI($messages);
        if ($reply === null) return null;

        return $this->postCheck($reply);
    }

    // ── Build the system prompt ───────────────────────────────
    private function buildSystemPrompt(string $businessMemory, ?string $userSummary): string
    {
        $basePrompt = BotSetting::get('system_prompt',
            'You are a friendly, professional business assistant. Keep replies concise (1–4 lines), warm, and helpful.'
        );

        $prompt = $basePrompt . "\n\n";

        $prompt .= "=== BUSINESS INFORMATION ===\n";
        $prompt .= $businessMemory . "\n\n";

        if ($userSummary) {
            $prompt .= "=== WHAT WE KNOW ABOUT THIS USER ===\n";
            $prompt .= $userSummary . "\n\n";
        }

        $prompt .= "=== RULES ===\n";
        $prompt .= "- Reply in the SAME language the user writes in\n";
        $prompt .= "- Keep replies SHORT (1–4 lines max)\n";
        $prompt .= "- Be warm, natural, and human-like — avoid sounding robotic\n";
        $prompt .= "- Never reveal you are an AI unless directly asked\n";
        $prompt .= "- If you don't know something, say you'll check and get back to them\n";
        $prompt .= "- Do NOT use markdown formatting in your reply\n";
        $prompt .= "- Use emojis occasionally to feel friendly 😊\n";

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

    // ── Call OpenAI API ───────────────────────────────────────
    private function callOpenAI(array $messages): ?string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => 200,
                    'temperature' => 0.75,
                    'top_p'       => 0.9,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return trim($response->json('choices.0.message.content') ?? '');
        } catch (\Throwable $e) {
            Log::error('OpenAI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Post-check: validate & flag if needed ─────────────────
    private function postCheck(string $reply): array
    {
        $flagHuman  = false;
        $flagReason = null;

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

        return $result;
    }
}
