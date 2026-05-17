<?php

namespace App\Services;

use App\Models\BotSetting;
use App\Models\Message;
use App\Models\FlaggedConversation;
use App\Models\ActivityLog;

class SafetyService
{
    // ── Is the bot globally enabled? ──────────────────────────
    public function isBotEnabled(): bool
    {
        return BotSetting::get('bot_enabled', '1') === '1';
    }

    // ── Is current time within configured working hours? ─────
    public function isWithinWorkingHours(): bool
    {
        $start = BotSetting::get('working_hours_start', '09:00');
        $end   = BotSetting::get('working_hours_end', '21:00');
        $now   = now()->format('H:i');
        return $now >= $start && $now <= $end;
    }

    // ── Per-user + global rate limit (DB-based, no Redis) ────
    public function isRateLimited(string $phone): bool
    {
        $cooldown = (int) BotSetting::get('per_user_cooldown', '5');
        $maxGlobal = (int) BotSetting::get('max_replies_per_minute', '20');

        // Per-user: check last assistant message for this phone
        $lastUserReply = Message::where('phone', $phone)
            ->where('role', 'assistant')
            ->orderByDesc('created_at')
            ->first();

        if ($lastUserReply && $lastUserReply->created_at->diffInSeconds(now()) < $cooldown) {
            return true; // Too soon for this user
        }

        // Global: count assistant messages sent in last 60 seconds
        $globalCount = Message::where('role', 'assistant')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->count();

        if ($globalCount >= $maxGlobal) {
            ActivityLog::record('rate_limit', 'Global rate limit reached', $phone);
            return true;
        }

        return false;
    }

    // ── Does the message require human takeover? ─────────────
    public function requiresHumanTakeover(string $text): bool
    {
        $keywordsRaw = BotSetting::get('human_trigger_keywords', 'call,urgent,complaint,manager,refund,legal,police,emergency');
        $keywords    = array_map('trim', explode(',', strtolower($keywordsRaw)));
        $textLower   = strtolower($text);

        foreach ($keywords as $kw) {
            if ($kw && str_contains($textLower, $kw)) {
                return true;
            }
        }
        return false;
    }

    // ── Has human takeover been toggled for this phone? ──────
    public function hasHumanTakeover(string $phone): bool
    {
        return FlaggedConversation::isHumanTakeover($phone);
    }

    // ── Is message ID a duplicate? ───────────────────────────
    public function isDuplicate(string $messageId): bool
    {
        return Message::isDuplicate($messageId);
    }

    // ── Get outside-hours fallback message ───────────────────
    public function outsideHoursMessage(): string
    {
        return BotSetting::get(
            'outside_hours_message',
            "Hi! 👋 We're currently offline. We'll reply as soon as we're back!"
        );
    }
}
