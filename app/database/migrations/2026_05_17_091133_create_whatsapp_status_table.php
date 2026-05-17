<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_status', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('disconnected'); // disconnected|connecting|connected
            $table->text('qr_code')->nullable();               // base64 PNG
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
        });

        // Single-row status record
        DB::table('whatsapp_status')->insert([
            'status'     => 'disconnected',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_status');
    }
};
