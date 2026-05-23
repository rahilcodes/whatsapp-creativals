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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('phone', 30)->index();
            $table->string('captured_name')->nullable();
            $table->string('captured_phone')->nullable();
            $table->string('captured_email')->nullable();
            $table->string('capture_stage')->default('new'); // new, exploring, engaged, interested, ready_to_buy, objection, complaint, human_required
            $table->text('summary')->nullable();
            $table->string('intent')->nullable(); // greeting, inquiry, pricing_inquiry, service_inquiry, buying_intent, objection, complaint, casual
            $table->string('mood')->nullable(); // neutral, curious, interested, hesitant, confused, frustrated, urgent
            $table->integer('lead_score')->default(0);
            $table->boolean('human_required')->default(false);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            // Unique compound index for tenant isolation
            $table->unique(['tenant_id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
