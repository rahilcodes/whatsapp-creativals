<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->boolean('show_billing')->default(true)->after('sidebar_color');
            $table->string('plan_starter_name')->nullable()->after('show_billing');
            $table->integer('plan_starter_price')->nullable()->after('plan_starter_name'); // in cents/paise
            $table->string('plan_automator_name')->nullable()->after('plan_starter_price');
            $table->integer('plan_automator_price')->nullable()->after('plan_automator_name'); // in cents/paise
            $table->string('billing_currency')->default('INR')->after('plan_automator_price');
        });
    }

    public function down(): void
    {
        Schema::table('resellers', function (Blueprint $table) {
            $table->dropColumn([
                'show_billing',
                'plan_starter_name',
                'plan_starter_price',
                'plan_automator_name',
                'plan_automator_price',
                'billing_currency',
            ]);
        });
    }
};
