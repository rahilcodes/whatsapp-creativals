<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BotSetting extends Model
{
    protected $fillable = ['key', 'value'];

    // ── Static helper: get setting by key ─────────────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();
        return $row?->value ?? $default;
    }

    // ── Static helper: set setting ────────────────────────────
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    // ── Get all settings as key → value map ──────────────────
    public static function allAsMap(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
