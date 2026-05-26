<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use App\Models\Tenant;
use App\Services\LeadCaptureService;
use App\Services\LeadIntelligenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantAUser;
    private User $tenantBUser;
    private Tenant $tenantA;
    private Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up Tenant A
        $this->tenantA = Tenant::create([
            'name' => 'Tenant A Corp',
            'slug' => 'tenant-a',
            'status' => 'active',
        ]);
        $this->tenantAUser = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'is_super_admin' => false,
            'onboarded' => true,
        ]);

        // Set up Tenant B
        $this->tenantB = Tenant::create([
            'name' => 'Tenant B LLC',
            'slug' => 'tenant-b',
            'status' => 'active',
        ]);
        $this->tenantBUser = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'is_super_admin' => false,
            'onboarded' => true,
        ]);
    }

    public function test_guest_cannot_access_leads_dashboard(): void
    {
        $response = $this->get('/leads');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_tenant_can_access_leads_dashboard(): void
    {
        app()->instance('tenant_id', $this->tenantA->id);

        Lead::create([
            'phone' => '1234567890',
            'captured_name' => 'Test Lead',
            'lead_score' => 85,
            'capture_stage' => 'interested',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->tenantAUser)->get('/leads');
        $response->assertStatus(200);
        $response->assertSee('Test Lead');
        $response->assertSee('85');
    }

    public function test_leads_multi_tenancy_isolation(): void
    {
        // Tenant A Lead
        app()->instance('tenant_id', $this->tenantA->id);
        Lead::create([
            'phone' => '1111111111',
            'captured_name' => 'Lead A',
            'lead_score' => 10,
        ]);

        // Tenant B Lead
        app()->instance('tenant_id', $this->tenantB->id);
        Lead::create([
            'phone' => '2222222222',
            'captured_name' => 'Lead B',
            'lead_score' => 20,
        ]);

        // Accessing as Tenant A — should see Lead A but NOT Lead B
        $responseA = $this->actingAs($this->tenantAUser)->get('/leads');
        $responseA->assertSee('Lead A');
        $responseA->assertDontSee('Lead B');

        // Accessing as Tenant B — should see Lead B but NOT Lead A
        $responseB = $this->actingAs($this->tenantBUser)->get('/leads');
        $responseB->assertSee('Lead B');
        $responseB->assertDontSee('Lead A');
    }

    public function test_lead_details_slide_over_json_endpoint(): void
    {
        app()->instance('tenant_id', $this->tenantA->id);

        $lead = Lead::create([
            'phone' => '1234567890',
            'captured_name' => 'John Doe',
            'summary' => 'Interested in buying real estate.',
        ]);

        Message::create([
            'phone' => '1234567890',
            'jid' => '1234567890@s.whatsapp.net',
            'role' => 'user',
            'content' => 'Hello, I want to buy a house.',
        ]);

        $response = $this->actingAs($this->tenantAUser)->get("/leads/{$lead->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('lead.captured_name', 'John Doe');
        $response->assertJsonPath('messages.0.content', 'Hello, I want to buy a house.');
    }

    public function test_lead_capture_regex_parsing(): void
    {
        $capture = new LeadCaptureService();

        // Email extraction
        $this->assertEquals('hello@example.com', $capture->extractEmail('My email is hello@example.com, please email me.'));
        $this->assertNull($capture->extractEmail('No email here.'));

        // Phone extraction
        $this->assertEquals('+917997001700', $capture->extractPhone('Reach me at +91 7997001700 or call.'));
        $this->assertEquals('9876543210', $capture->extractPhone('Call me 98765 43210.'));
    }

    public function test_lead_intelligence_scoring_formula(): void
    {
        $intel = new LeadIntelligenceService();

        // High intent, high score
        $scoreHigh = $intel->calculateLeadScore('buying_intent', 'urgent', 6);
        $this->assertEquals(85, $scoreHigh); // 40 + 20 + 25 = 85

        // Low intent, low score
        $scoreLow = $intel->calculateLeadScore('greeting', 'neutral', 1);
        $this->assertEquals(12, $scoreLow); // 2 + 5 + 5 = 12
    }

    public function test_background_job_stores_business_type(): void
    {
        // Set onboarding category to food & beverage
        $this->tenantA->business_category = 'Food & Beverage (Restaurants, Cafes)';
        $this->tenantA->save();

        // Dispatch ProcessLeadEngagement directly
        $job = new \App\Jobs\ProcessLeadEngagement($this->tenantA->id, '1234567890', 'Hello, I want to order food');
        
        // Mock LeadIntelligenceService to return dummy details to avoid external HTTP requests
        $intelMock = $this->createMock(LeadIntelligenceService::class);
        $intelMock->method('analyzeEngagement')->willReturn([
            'name' => 'Alice Pizza',
            'intent' => 'buying_intent',
            'mood' => 'interested',
            'summary' => 'Wants to order food.',
            'human_required' => false,
        ]);
        $intelMock->method('calculateLeadScore')->willReturn(80);

        $capture = new LeadCaptureService();

        // Run the job handler with mocked intelligence service
        $job->handle($capture, $intelMock);

        // Assert that the lead record was created with the correct business_type
        $lead = Lead::where('phone', '1234567890')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('restaurant', $lead->business_type);
        $this->assertEquals('Alice Pizza', $lead->captured_name);
        $this->assertEquals('ready_to_buy', $lead->capture_stage);
    }

    public function test_background_job_extracts_llm_credentials_and_protects_overwrite(): void
    {
        app()->instance('tenant_id', $this->tenantA->id);

        // Pre-create lead with a captured name to test overwrite protection
        $existingLead = Lead::create([
            'phone' => '1234567890',
            'captured_name' => 'Original Name',
            'captured_email' => null,
            'captured_phone' => null,
        ]);

        $job = new \App\Jobs\ProcessLeadEngagement($this->tenantA->id, '1234567890', 'My email is test@domain.com and call me at +91 9999999999');

        $intelMock = $this->createMock(LeadIntelligenceService::class);
        $intelMock->method('analyzeEngagement')->willReturn([
            'name' => 'New Decoded Name', // should NOT overwrite Original Name
            'email' => 'test@domain.com', // should be saved
            'phone' => '+91 99999 99999', // should be cleaned and saved
            'intent' => 'service_inquiry',
            'mood' => 'interested',
            'summary' => 'Shared details.',
            'human_required' => false,
        ]);
        $intelMock->method('calculateLeadScore')->willReturn(60);

        $capture = new LeadCaptureService();
        $job->handle($capture, $intelMock);

        $lead = Lead::where('phone', '1234567890')->first();
        $this->assertNotNull($lead);
        // Assert name was NOT overwritten due to protection
        $this->assertEquals('Original Name', $lead->captured_name);
        // Assert email was saved from insights
        $this->assertEquals('test@domain.com', $lead->captured_email);
        // Assert phone was saved and cleaned
        $this->assertEquals('+919999999999', $lead->captured_phone);
    }

    public function test_media_cleanup_command(): void
    {
        app()->instance('tenant_id', $this->tenantA->id);

        // Define directory & files
        $dir = storage_path("app/public/receipts/{$this->tenantA->id}");
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $oldFilePath = "{$dir}/receipt_old.jpg";
        $newFilePath = "{$dir}/receipt_new.jpg";

        file_put_contents($oldFilePath, 'old_data');
        file_put_contents($newFilePath, 'new_data');

        $this->assertFileExists($oldFilePath);
        $this->assertFileExists($newFilePath);

        // Create messages in DB
        $oldMsg = Message::create([
            'phone' => '1234567890',
            'jid' => '1234567890@s.whatsapp.net',
            'role' => 'user',
            'content' => '[IMAGE_RECEIPT]',
            'image_path' => "storage/receipts/{$this->tenantA->id}/receipt_old.jpg",
        ]);
        // Set created_at back to 4 months ago
        $oldMsg->created_at = now()->subMonths(4);
        $oldMsg->save();

        $newMsg = Message::create([
            'phone' => '1234567890',
            'jid' => '1234567890@s.whatsapp.net',
            'role' => 'user',
            'content' => '[IMAGE_RECEIPT]',
            'image_path' => "storage/receipts/{$this->tenantA->id}/receipt_new.jpg",
        ]);

        // Run Artisan command
        $this->artisan('media:cleanup')
             ->expectsOutputToContain('cleanup finished')
             ->assertExitCode(0);

        // Assert old file deleted, db record cleared
        $this->assertFileDoesNotExist($oldFilePath);
        $this->assertNull($oldMsg->fresh()->image_path);

        // Assert new file preserved, db record untouched
        $this->assertFileExists($newFilePath);
        $this->assertEquals("storage/receipts/{$this->tenantA->id}/receipt_new.jpg", $newMsg->fresh()->image_path);

        // Cleanup files
        if (file_exists($newFilePath)) {
            unlink($newFilePath);
        }
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }

    public function test_google_sheets_sync_observer_dispatches_job(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        app()->instance('tenant_id', $this->tenantA->id);

        $lead = Lead::create([
            'phone' => '9999999999',
            'captured_name' => 'Sheets Sync Lead',
            'lead_score' => 70,
        ]);

        // Assert job was dispatched
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SyncLeadToGoogleSheet::class);

        // Run the handler manually to verify mock standby/fallback behavior works beautifully
        $sheetsService = app(\App\Services\GoogleSheetsService::class);
        $job = new \App\Jobs\SyncLeadToGoogleSheet($lead);
        
        // This will verify that the mock standby logic runs cleanly
        $job->handle($sheetsService);
        
        $this->assertTrue(true); // Verifies no exceptions were thrown
    }
}
