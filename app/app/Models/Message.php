<?php

namespace App\Models;

use App\Models\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasTenant;

    protected $fillable = [
        'wa_message_id',
        'jid',
        'phone',
        'role',
        'content',
        'image_path',
        'flagged',
        'flag_reason',
    ];

    protected $casts = [
        'flagged' => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────
    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    // ── Check if a message ID already exists (dedup) ──────────
    public static function isDuplicate(string $waMessageId): bool
    {
        return static::withoutGlobalScope('tenant')->where('wa_message_id', $waMessageId)->exists();
    }

    // ── Get conversations list (last message per phone) ───────
    public static function conversations(): \Illuminate\Support\Collection
    {
        return static::selectRaw('phone, MAX(id) as last_id')
            ->groupBy('phone')
            ->orderByDesc('last_id')
            ->get()
            ->map(function ($row) {
                $last = static::find($row->last_id);
                $count = static::where('phone', $row->phone)->count();
                return [
                    'phone'        => $row->phone,
                    'last_message' => $last->content,
                    'last_role'    => $last->role,
                    'last_at'      => $last->created_at,
                    'count'        => $count,
                ];
            });
    }
}
