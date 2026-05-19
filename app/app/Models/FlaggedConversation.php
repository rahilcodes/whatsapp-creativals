<?php

namespace App\Models;

use App\Models\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;

class FlaggedConversation extends Model
{
    use HasTenant;

    protected $fillable = ['phone', 'reason', 'status', 'last_message', 'human_takeover'];

    protected $casts = ['human_takeover' => 'boolean'];

    public static function flagPhone(string $phone, string $reason, string $lastMessage = ''): void
    {
        static::updateOrCreate(['phone' => $phone], [
            'reason'         => $reason,
            'status'         => 'pending',
            'last_message'   => $lastMessage,
            'human_takeover' => false,
        ]);
    }

    public static function isHumanTakeover(string $phone): bool
    {
        $row = static::where('phone', $phone)->first();
        return $row?->human_takeover === true;
    }

    public static function isFlagged(string $phone): bool
    {
        return static::where('phone', $phone)
            ->whereIn('status', ['pending'])
            ->where('human_takeover', false)
            ->exists();
    }
}
