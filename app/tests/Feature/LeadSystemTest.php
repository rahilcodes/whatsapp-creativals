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
}
