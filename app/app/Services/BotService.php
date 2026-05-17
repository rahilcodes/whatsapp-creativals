<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BotSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotService
{
    private string $botUrl;
    private string $secret;

    public function __construct()
    {
        $this->botUrl = rtrim(env('BOT_URL', 'http://127.0.0.1:3000'), '/');
        $this->secret = env('SHARED_SECRET', 'whatsapp_ai_secret_2026');
    }

    private function headers(): array
    {
        return [
            'Content-Type'  => 'application/json',
            'X-Bot-Secret'  => $this->secret,
        ];
    }

    // ── Send a WhatsApp reply via the Node.js bot ─────────────
    public function sendReply(string $jid, string $text, bool $instant = false): bool
    {
        $delayMin = $instant ? 0 : (int) BotSetting::get('delay_min', '3');
        $delayMax = $instant ? 0 : (int) BotSetting::get('delay_max', '15');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(($delayMax + 5))
                ->post("{$this->botUrl}/send", [
                    'jid'       => $jid,
                    'text'      => $text,
                    'delay_min' => $delayMin,
                    'delay_max' => $delayMax,
                ]);

            if ($response->successful()) {
                ActivityLog::record('message_sent', "Reply sent to {$jid}", null, [
                    'jid'     => $jid,
                    'preview' => substr($text, 0, 60),
                ]);
                return true;
            }

            Log::warning('Bot send failed', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('BotService::sendReply exception', ['error' => $e->getMessage()]);
            ActivityLog::record('error', 'Failed to send reply: ' . $e->getMessage());
            return false;
        }
    }

    // ── Trigger a reconnect on the Node.js bot ───────────────
    public function reconnect(): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->botUrl}/reconnect");

            ActivityLog::record('status_change', 'Reconnect triggered from dashboard');
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('BotService::reconnect exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ── Get Node bot connection status ───────────────────────
    public function getNodeStatus(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(4)
                ->get("{$this->botUrl}/status");

            if ($response->successful()) {
                return $response->json() ?? ['status' => 'unknown'];
            }
            return ['status' => 'unreachable'];
        } catch (\Throwable $e) {
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }
    }

    // ── Health check (is Node process running?) ──────────────
    public function isNodeRunning(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->botUrl}/health");
            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
