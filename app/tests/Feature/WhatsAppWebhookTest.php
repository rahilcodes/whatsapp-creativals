<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\BotSetting;
use App\Models\FlaggedConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a default tenant with ID = 1 to resolve webhook correctly
        $this->tenant = Tenant::create([
            'id' => 1,
            'name' => 'Test Corp',
            'slug' => 'test-corp',
            'status' => 'active',
        ]);
        
        // Ensure the global status is active
        app()->instance('tenant_id', $this->tenant->id);
    }

    /**
     * Test unauthorized request fails.
     */
    public function test_webhook_unauthorized_without_secret(): void
    {
        $response = $this->postJson('/api/whatsapp/message', [
            'phone' => '919876543210',
            'jid' => '919876543210@s.whatsapp.net',
            'message' => 'hello',
            'message_id' => 'MSG123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test normal user message that does not trigger human takeover.
     */
    public function test_webhook_normal_message_bypasses_admin_notification(): void
    {
        // Mock Node.js bot /send endpoint and OpenAI completions API
        Http::fake([
            '*/send' => Http::response(['queue_id' => 'q123'], 200),
            '*api.openai.com*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'name' => null,
                                'email' => null,
                                'phone' => null,
                                'intent' => 'casual',
                                'mood' => 'neutral',
                                'summary' => 'The customer sent a thumbs up.',
                                'human_required' => false,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Mock OpenAI or use acknowledgement bypass (simple acknowledgment like "👍")
        $response = $this->postJson('/api/whatsapp/message', [
            'phone' => '919876543210',
            'jid' => '919876543210@s.whatsapp.net',
            'message' => '👍',
            'message_id' => 'MSG123',
        ], [
            'X-Bot-Secret' => 'whatsapp_ai_secret_2026',
            'X-Tenant-ID' => (string) $this->tenant->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'ok']);

        // Assert exactly 1 message was enqueued via BotService (for the user, not the admin)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/send') &&
                   $request['jid'] === '919876543210@s.whatsapp.net';
        });

        // Assert no hot lead alerts were sent to any admin
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/send') &&
                   str_contains($request['text'], 'HOT LEAD ALERT');
        });
        
        // Assert conversation is not flagged for human takeover
        $this->assertFalse(FlaggedConversation::isHumanTakeover('919876543210'));
    }

    /**
     * Test admin notification is sent when user message matches human takeover keyword.
     */
    public function test_webhook_sends_admin_notifications_on_human_takeover_trigger(): void
    {
        // Set the admin phone numbers
        BotSetting::set('admin_phones', '917997001700,919573142847');
        BotSetting::set('human_trigger_keywords', 'call,urgent,complaint,manager,refund');

        // Mock Node.js bot /send endpoint and OpenAI completions API
        Http::fake([
            '*/send' => Http::response(['queue_id' => 'q123'], 200),
            '*api.openai.com*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'name' => null,
                                'email' => null,
                                'phone' => null,
                                'intent' => 'casual',
                                'mood' => 'neutral',
                                'summary' => 'Keyword triggered hot lead.',
                                'human_required' => true,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Post a message containing a human trigger keyword ("manager")
        $response = $this->postJson('/api/whatsapp/message', [
            'phone' => '919876543210',
            'jid' => '919876543210@s.whatsapp.net',
            'message' => 'I want to speak to the manager urgently',
            'message_id' => 'MSG_URGENT_123',
        ], [
            'X-Bot-Secret' => 'whatsapp_ai_secret_2026',
            'X-Tenant-ID' => (string) $this->tenant->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'flagged_human']);

        // Assert the conversation was flagged in DB as pending review
        $this->assertDatabaseHas('flagged_conversations', [
            'phone' => '919876543210',
            'reason' => 'keyword_trigger',
            'status' => 'pending'
        ]);

        // Assert that two HTTP requests were sent to the bot (one for each admin)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/send') &&
                   $request['jid'] === '917997001700@s.whatsapp.net' &&
                   str_contains($request['text'], 'HOT LEAD ALERT');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/send') &&
                   $request['jid'] === '919573142847@s.whatsapp.net' &&
                   str_contains($request['text'], 'HOT LEAD ALERT');
        });

        // Total 2 requests (one to each admin)
        Http::assertSentCount(2);
    }
}
