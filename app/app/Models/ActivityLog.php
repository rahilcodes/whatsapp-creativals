<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['type', 'phone', 'description', 'meta'];

    protected $casts = ['meta' => 'array'];

    public static function record(string $type, string $description, ?string $phone = null, array $meta = []): void
    {
        static::create([
            'type'        => $type,
            'phone'       => $phone,
            'description' => $description,
            'meta'        => empty($meta) ? null : $meta,
        ]);
    }

    public static function recent(int $limit = 30): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderByDesc('created_at')->limit($limit)->get();
    }
}
