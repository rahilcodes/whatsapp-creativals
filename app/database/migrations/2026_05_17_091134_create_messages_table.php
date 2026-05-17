<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('wa_message_id')->nullable()->unique(); // WhatsApp msg ID (dedup)
            $table->string('jid');                                  // WhatsApp JID
            $table->string('phone', 20)->index();                   // E.164 phone
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->boolean('flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
