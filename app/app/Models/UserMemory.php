<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMemory extends Model
{
    protected $table = 'user_memories';

    protected $fillable = ['phone', 'summary', 'last_activity_at'];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public static function forPhone(string $phone): static
    {
        return static::firstOrCreate(['phone' => $phone], [
            'last_activity_at' => now(),
        ]);
    }
}
