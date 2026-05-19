<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert a default tenant
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Default Tenant',
            'slug' => 'default',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
            DB::table($tableName)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No safe down migration, we leave the tenant_id as is (it will be dropped in the other migration)
    }
};
