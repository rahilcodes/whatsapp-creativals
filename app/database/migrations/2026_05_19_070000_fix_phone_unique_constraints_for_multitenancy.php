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
        Schema::table('flagged_conversations', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->unique(['tenant_id', 'phone']);
        });

        Schema::table('user_memories', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->unique(['tenant_id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_memories', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'phone']);
            $table->unique(['phone']);
        });

        Schema::table('flagged_conversations', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'phone']);
            $table->unique(['phone']);
        });
    }
};
