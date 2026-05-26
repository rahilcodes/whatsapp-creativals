<?php

namespace App\Models;

use App\Models\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BotSetting extends Model
{
    use HasTenant;

    protected $fillable = ['key', 'value'];

    private static array $defaults = [
        'bot_enabled'             => '1',
        'working_hours_start'     => '00:00',
        'working_hours_end'       => '23:59',
        'delay_min'               => '3',
        'delay_max'               => '15',
        'per_user_cooldown'       => '30',
        'max_replies_per_minute'  => '20',
        'memory_limit'            => '10',
        'system_prompt'           => "You are a friendly, professional business assistant. Keep your replies concise (1–4 lines), warm, and helpful. Never reveal you are an AI unless directly asked. Always stay on topic and refer to the business information provided.",
        'human_trigger_keywords'  => 'call,urgent,complaint,manager,refund,legal,police,emergency',
        'outside_hours_message'   => "Hi! 👋 Our team is currently offline. We'll get back to you as soon as possible! 😊",
    ];

    // ── Static helper: get setting by key ─────────────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();
        if ($row) {
            return $row->value;
        }
        return self::$defaults[$key] ?? $default;
    }

    // ── Static helper: set setting ────────────────────────────
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    // ── Get all settings as key → value map ──────────────────
    public static function allAsMap(): array
    {
        $dbSettings = static::all()->pluck('value', 'key')->toArray();
        return array_merge(self::$defaults, $dbSettings);
    }
}
