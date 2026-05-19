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

    // ── Resolve the current tenant ID from the container ────────
    private function tenantId(): int
    {
        return (int) (app()->has('tenant_id') ? app('tenant_id') : 1);
    }

    private function headers(): array
    {
        return [
            'Content-Type'  => 'application/json',
            'X-Bot-Secret'  => $this->secret,
            'X-Tenant-ID'   => (string) $this->tenantId(),
        ];
    }

    // ── Send a WhatsApp reply via the Node.js bot (Enqueue) ───
    public function sendReply(string $jid, string $text, bool $instant = false, ?int $messageId = null): bool
    {
        $delayMin = $instant ? 0 : (int) BotSetting::get('delay_min', '3');
        $delayMax = $instant ? 0 : (int) BotSetting::get('delay_max', '15');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(5)
                ->post("{$this->botUrl}/send", [
                    'jid'        => $jid,
                    'text'       => $text,
                    'message_id' => $messageId,
                    'delay_min'  => $delayMin,
                    'delay_max'  => $delayMax,
                    'tenant_id'  => $this->tenantId(),
                ]);

            if ($response->successful()) {
                $queueId = $response->json('queue_id');
                ActivityLog::record('message_sent', "Reply enqueued for {$jid}", null, [
                    'jid'      => $jid,
                    'queue_id' => $queueId,
                    'preview'  => substr($text, 0, 60),
                ]);
                
                if ($messageId) {
                    \App\Models\Message::where('id', $messageId)->update(['delivery_status' => 'pending']);
                }
                
                return true;
            }

            Log::warning('Bot enqueue failed', ['status' => $response->status(), 'body' => $response->body()]);
            
            if ($messageId) {
                \App\Models\Message::where('id', $messageId)->update([
                    'delivery_status' => 'failed',
                    'failed_reason' => 'Enqueue failed (HTTP ' . $response->status() . ')'
                ]);
            }
            
            return false;
        } catch (\Throwable $e) {
            Log::error('BotService::sendReply exception', ['error' => $e->getMessage()]);
            ActivityLog::record('error', 'Failed to enqueue reply: ' . $e->getMessage());
            
            if ($messageId) {
                \App\Models\Message::where('id', $messageId)->update([
                    'delivery_status' => 'failed',
                    'failed_reason' => substr($e->getMessage(), 0, 255)
                ]);
            }
            
            return false;
        }
    }

    // ── Trigger a reconnect on the Node.js bot ───────────────
    public function reconnect(): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->botUrl}/reconnect", [
                    'tenant_id' => $this->tenantId(),
                ]);

            ActivityLog::record('status_change', 'Reconnect triggered from dashboard');
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('BotService::reconnect exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ── Start a new session for a specific tenant ─────────────
    public function startSession(int $tenantId): bool
    {
        try {
            // Provide explicit headers since this might be called outside an active tenant session
            $headers = $this->headers();
            $headers['X-Tenant-ID'] = (string) $tenantId;

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post("{$this->botUrl}/start", [
                    'tenant_id' => $tenantId,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('BotService::startSession exception', ['error' => $e->getMessage(), 'tenant_id' => $tenantId]);
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

    // ── Get detailed session health ───────────────────────────
    public function getSessionHealth(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(4)
                ->get("{$this->botUrl}/health/detail");

            if ($response->successful()) {
                return $response->json() ?? [];
            }
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
