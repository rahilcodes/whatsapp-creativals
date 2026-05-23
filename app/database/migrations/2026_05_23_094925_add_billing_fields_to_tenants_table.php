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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('plan')->default('starter'); // starter, automator
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('razorpay_customer_id')->nullable();
            $table->string('razorpay_subscription_id')->nullable();
            $table->string('subscription_status')->default('trialing'); // trialing, active, past_due, cancelled, expired
            $table->boolean('has_support_addon')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'plan',
                'trial_ends_at',
                'razorpay_customer_id',
                'razorpay_subscription_id',
                'subscription_status',
                'has_support_addon'
            ]);
        });
    }
};
