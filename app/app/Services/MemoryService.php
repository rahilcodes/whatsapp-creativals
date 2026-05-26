<?php

namespace App\Services;

use App\Models\Message;
use App\Models\UserMemory;
use App\Models\BusinessMemory;
use App\Models\BotSetting;

class MemoryService
{
    // ── Short-term: last N messages for this phone ────────────
    public function getShortTermMemory(string $phone, int $limit = null): array
    {
        $limit = $limit ?? (int) BotSetting::get('memory_limit', '10');

        return Message::where('phone', $phone)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();
    }

    // ── Long-term: AI-generated user summary ─────────────────
    public function getLongTermMemory(string $phone): ?string
    {
        return UserMemory::where('phone', $phone)->first()?->summary;
    }

    // ── Business context formatted for AI prompt ─────────────
    public function getBusinessMemory(): string
    {
        return BusinessMemory::forPrompt();
    }

    // ── Save a message to the messages table ─────────────────
    public function saveMessage(
        string $phone,
        string $jid,
        string $role,
        string $content,
        ?string $waMessageId = null,
        ?string $imagePath = null
    ): Message {
        return Message::create([
            'wa_message_id' => $waMessageId,
            'jid'           => $jid,
            'phone'         => $phone,
            'role'          => $role,
            'content'       => $content,
            'image_path'    => $imagePath,
        ]);
    }

    // ── Update (or create) user long-term memory summary ─────
    public function updateUserSummary(string $phone, string $summary): void
    {
        UserMemory::updateOrCreate(
            ['phone' => $phone],
            ['summary' => $summary, 'last_activity_at' => now()]
        );
    }

    // ── Touch last activity timestamp ─────────────────────────
    public function touchActivity(string $phone): void
    {
        UserMemory::updateOrCreate(
            ['phone' => $phone],
            ['last_activity_at' => now()]
        );
    }

    // ── Clear all memory for a phone ─────────────────────────
    public function clearMemory(string $phone): void
    {
        Message::where('phone', $phone)->delete();
        UserMemory::where('phone', $phone)->delete();
    }

    // ── Build full context array for AI ──────────────────────
    public function buildContext(string $phone): array
    {
        return [
            'short_term'      => $this->getShortTermMemory($phone),
            'long_term'       => $this->getLongTermMemory($phone),
            'business_memory' => $this->getBusinessMemory(),
        ];
    }
}
