<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->string('plan_support_name')->nullable()->after('plan_automator_price');
            $table->integer('plan_support_price')->nullable()->after('plan_support_name'); // in paise/cents
        });
    }

    public function down(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->dropColumn(['plan_support_name', 'plan_support_price']);
        });
    }
};
