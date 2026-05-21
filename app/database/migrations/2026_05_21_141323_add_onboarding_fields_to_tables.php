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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('onboarded')->default(false)->after('email_verified_at');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('account_type')->nullable()->after('slug');
            $table->string('business_category')->nullable()->after('account_type');
            $table->string('business_subcategory')->nullable()->after('business_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarded');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'business_category', 'business_subcategory']);
        });
    }
};
