<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'bot_settings',
            'whatsapp_status',
            'messages',
            'user_memories',
            'business_memories',
            'flagged_conversations',
            'activity_logs'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Add tenant_id as a nullable foreign key for now so we can seed existing data
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'bot_settings',
            'whatsapp_status',
            'messages',
            'user_memories',
            'business_memories',
            'flagged_conversations',
            'activity_logs'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
