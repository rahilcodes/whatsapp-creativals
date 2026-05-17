<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flagged_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique()->index();
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'resolved', 'taken_over'])->default('pending');
            $table->text('last_message')->nullable();
            $table->boolean('human_takeover')->default(false); // Human has taken over
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flagged_conversations');
    }
};
