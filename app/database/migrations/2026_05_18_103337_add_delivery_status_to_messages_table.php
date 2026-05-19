<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('delivery_status')->default('sent')->after('is_from_bot'); // pending | sent | failed
            $table->integer('send_attempts')->default(0)->after('delivery_status');
            $table->string('failed_reason')->nullable()->after('send_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'send_attempts', 'failed_reason']);
        });
    }
};
