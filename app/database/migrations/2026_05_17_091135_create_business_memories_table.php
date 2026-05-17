<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_memories', function (Blueprint $table) {
            $table->id();
            $table->string('category');  // menu|services|pricing|faqs|hours|contact
            $table->string('key');
            $table->text('value');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Seed demo business data
        $demos = [
            ['category' => 'services',  'key' => 'Main Service',      'value' => 'We offer premium catering services for corporate and personal events.'],
            ['category' => 'services',  'key' => 'Delivery',          'value' => 'We deliver within 30 km radius. Minimum order ₹2000.'],
            ['category' => 'pricing',   'key' => 'Basic Package',      'value' => '₹500 per person — includes starter, main course, dessert, and drinks.'],
            ['category' => 'pricing',   'key' => 'Premium Package',    'value' => '₹1000 per person — includes 5-course meal with live counter.'],
            ['category' => 'faqs',      'key' => 'Advance booking',    'value' => 'We require at least 48 hours advance notice for all orders.'],
            ['category' => 'faqs',      'key' => 'Payment',            'value' => 'We accept UPI, bank transfer, and cash. 50% advance required.'],
            ['category' => 'hours',     'key' => 'Business Hours',     'value' => 'Monday to Saturday, 9 AM – 9 PM. Closed on Sundays.'],
            ['category' => 'contact',   'key' => 'WhatsApp',           'value' => 'This is our official WhatsApp. We reply within 30 minutes.'],
        ];

        DB::table('business_memories')->insert(array_map(fn($r) => array_merge($r, [
            'active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]), $demos));
    }

    public function down(): void
    {
        Schema::dropIfExists('business_memories');
    }
};
