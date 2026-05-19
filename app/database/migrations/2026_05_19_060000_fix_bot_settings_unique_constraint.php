<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            try {
                $table->dropUnique(['key']);
            } catch (\Throwable $e) {
                // In case it's SQLite and we need to ignore index not existing
            }
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            try {
                $table->dropUnique(['tenant_id', 'key']);
            } catch (\Throwable $e) {
            }
            $table->unique('key');
        });
    }
};
