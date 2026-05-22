<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemStatus extends Model
{
    protected $table = 'system_status';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): ?string
    {
        try {
            $status = static::where('key', $key)->first();
            return $status ? $status->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
