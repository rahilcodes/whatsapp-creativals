<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_status', function (Blueprint $table) {
            $table->string('session_state')->default('idle')->after('status');
            $table->integer('health_score')->default(100)->after('session_state');
            $table->boolean('ban_risk')->default(false)->after('health_score');
            $table->integer('reconnect_count')->default(0)->after('ban_risk');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_status', function (Blueprint $table) {
            $table->dropColumn([
                'session_state',
                'health_score',
                'ban_risk',
                'reconnect_count',
            ]);
        });
    }
};
