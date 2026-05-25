<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create test tenant ────────────────────────────────
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'test-local'],
            [
                'name'                => 'Test Business (Local)',
                'status'              => 'active',
                'plan'                => 'starter',
                'subscription_status' => 'trialing',
                'trial_ends_at'       => now()->addDays(30), // 30 days for testing
            ]
        );

        // ── Create test user ──────────────────────────────────
        $user = User::where('email', 'test@ichatup.local')->first();
        if (!$user) {
            $user = new User();
            $user->email = 'test@ichatup.local';
        }
        $user->name = 'Test User';
        $user->password = Hash::make('password');
        $user->onboarded = true;
        $user->email_verified_at = now();
        $user->tenant_id = $tenant->id;
        $user->save();

        $this->command->info("✅ Test account created:");
        $this->command->info("   Email:    test@ichatup.local");
        $this->command->info("   Password: password");
        $this->command->info("   Trial:    30 days (expires " . now()->addDays(30)->format('d M Y') . ")");

        // ── Seed sample leads ─────────────────────────────────
        $sampleLeads = [
            [
                'tenant_id'       => $tenant->id,
                'phone'           => '919876543210',
                'captured_name'   => 'Rahul Sharma',
                'captured_email'  => 'rahul@example.com',
                'intent'          => 'buying_intent',
                'mood'            => 'interested',
                'lead_score'      => 85,
                'capture_stage'   => 'ready_to_buy',
                'human_required'  => false,
                'summary'         => 'High-value lead interested in premium plan. Asked about pricing and features multiple times.',
                'last_activity_at' => now()->subMinutes(5),
            ],
            [
                'tenant_id'       => $tenant->id,
                'phone'           => '919123456789',
                'captured_name'   => 'Priya Patel',
                'captured_email'  => null,
                'intent'          => 'pricing_inquiry',
                'mood'            => 'curious',
                'lead_score'      => 62,
                'capture_stage'   => 'interested',
                'human_required'  => false,
                'summary'         => 'Inquired about monthly pricing. Email not yet captured.',
                'last_activity_at' => now()->subMinutes(45),
            ],
            [
                'tenant_id'       => $tenant->id,
                'phone'           => '918765432109',
                'captured_name'   => 'Amit Kumar',
                'captured_email'  => 'amit.k@gmail.com',
                'intent'          => 'complaint',
                'mood'            => 'frustrated',
                'lead_score'      => 30,
                'capture_stage'   => 'human_required',
                'human_required'  => true,
                'summary'         => 'Frustrated customer. Requires human attention immediately.',
                'last_activity_at' => now()->subHours(2),
            ],
            [
                'tenant_id'       => $tenant->id,
                'phone'           => '917654321098',
                'captured_name'   => 'Sneha Reddy',
                'captured_email'  => 'sneha@business.in',
                'intent'          => 'service_inquiry',
                'mood'            => 'neutral',
                'lead_score'      => 50,
                'capture_stage'   => 'engaged',
                'human_required'  => false,
                'summary'         => 'Asked about WhatsApp automation for her boutique business.',
                'last_activity_at' => now()->subHours(5),
            ],
        ];

        foreach ($sampleLeads as $leadData) {
            Lead::firstOrCreate(
                ['phone' => $leadData['phone'], 'tenant_id' => $tenant->id],
                $leadData
            );
        }

        // ── Seed sample messages for first lead ───────────────
        $sampleMessages = [
            ['phone' => '919876543210', 'role' => 'user',      'content' => 'Hi, I want to know about your WhatsApp AI tool'],
            ['phone' => '919876543210', 'role' => 'assistant', 'content' => 'Hi Rahul! Welcome to iChatUp 👋 We help businesses automate their WhatsApp replies using AI. What would you like to know?'],
            ['phone' => '919876543210', 'role' => 'user',      'content' => 'What is the pricing?'],
            ['phone' => '919876543210', 'role' => 'assistant', 'content' => 'Our Starter plan is ₹2,499/month and includes unlimited AI replies, lead tracking, and analytics. Shall I share more details?'],
            ['phone' => '919876543210', 'role' => 'user',      'content' => 'Yes please, I am interested'],
        ];

        foreach ($sampleMessages as $msg) {
            Message::firstOrCreate(
                ['phone' => $msg['phone'], 'content' => $msg['content'], 'role' => $msg['role']],
                ['tenant_id' => $tenant->id, 'jid' => $msg['phone'] . '@s.whatsapp.net']
            );
        }

        $this->command->info("✅ Seeded " . count($sampleLeads) . " sample leads and " . count($sampleMessages) . " messages");
        $this->command->info("");
        $this->command->info("🚀 Start local server: php artisan serve");
        $this->command->info("   Visit: http://127.0.0.1:8000/login");
    }
}
