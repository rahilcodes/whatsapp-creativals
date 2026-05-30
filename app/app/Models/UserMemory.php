<?php

namespace App\Models;

use App\Models\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;

class UserMemory extends Model
{
    use HasTenant;

    protected $table = 'user_memories';

    protected $fillable = ['phone', 'summary', 'active_state', 'last_activity_at'];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'active_state'     => 'array',
    ];

    public static function forPhone(string $phone): static
    {
        return static::firstOrCreate(['phone' => $phone], [
            'last_activity_at' => now(),
        ]);
    }
}
