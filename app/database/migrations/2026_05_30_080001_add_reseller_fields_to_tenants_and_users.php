<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Link tenants to a reseller (nullable = direct iChatUp client)
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('reseller_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('resellers')
                  ->onDelete('set null');
        });

        // Add reseller role to users table
        Schema::table('users', function (Blueprint $table) {
            // 'client' = normal tenant user, 'reseller_admin' = owns a reseller brand
            $table->string('role')->default('client')->after('email');
            $table->foreignId('reseller_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('resellers')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn(['role', 'reseller_id']);
        });
    }
};
