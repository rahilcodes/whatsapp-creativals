<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed defaults
        $defaults = [
            ['key' => 'bot_enabled',             'value' => '1'],
            ['key' => 'working_hours_start',      'value' => '09:00'],
            ['key' => 'working_hours_end',        'value' => '21:00'],
            ['key' => 'delay_min',                'value' => '3'],
            ['key' => 'delay_max',                'value' => '15'],
            ['key' => 'per_user_cooldown',        'value' => '30'],
            ['key' => 'max_replies_per_minute',   'value' => '20'],
            ['key' => 'memory_limit',             'value' => '10'],
            ['key' => 'system_prompt',            'value' => "You are a friendly, professional business assistant. Keep your replies concise (1–4 lines), warm, and helpful. Never reveal you are an AI unless directly asked. Always stay on topic and refer to the business information provided."],
            ['key' => 'human_trigger_keywords',   'value' => 'call,urgent,complaint,manager,refund,legal,police,emergency'],
            ['key' => 'outside_hours_message',    'value' => "Hi! 👋 Our team is currently offline. We're available from 9 AM to 9 PM. We'll get back to you as soon as we're back! 😊"],
        ];

        DB::table('bot_settings')->insert(array_map(fn($r) => array_merge($r, [
            'created_at' => now(), 'updated_at' => now(),
        ]), $defaults));
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_settings');
    }
};
