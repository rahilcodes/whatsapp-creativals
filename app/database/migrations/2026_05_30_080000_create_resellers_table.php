<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. "BeSureBot"
            $table->string('domain')->unique();          // e.g. "panel.besurebot.com"
            $table->string('slug')->unique();            // e.g. "besurebot" (for internal reference)
            $table->string('support_email')->nullable(); // e.g. "support@besurebot.com"
            $table->string('contact_name')->nullable();  // Reseller owner name

            // Branding
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('primary_color')->default('#10b981');   // Brand accent
            $table->string('sidebar_color')->default('#080f1e');   // Sidebar bg

            // Stripe payment gateway credentials (reseller gets paid directly)
            $table->string('stripe_key')->nullable();
            $table->string('stripe_secret')->nullable();
            $table->string('stripe_webhook_secret')->nullable();

            // SMTP mail credentials (so emails come from their domain, not ours)
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('smtp_from_address')->nullable();
            $table->string('smtp_from_name')->nullable();

            // Subscription / licensing (how you charge the reseller)
            $table->string('license_type')->default('monthly'); // monthly, yearly, lifetime
            $table->integer('license_fee')->default(0);         // Amount you charge them
            $table->date('license_expires_at')->nullable();

            // Limits / Quotas the reseller can give their clients
            $table->integer('max_clients')->default(50);        // Max number of clients
            $table->string('status')->default('active');        // active, suspended, trial

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resellers');
    }
};
