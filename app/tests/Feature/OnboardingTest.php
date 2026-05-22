<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\BusinessMemory;
use App\Models\BotSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default tenant and user
        $this->tenant = Tenant::create([
            'name' => "Test Tenant's Workspace",
            'slug' => 'test-tenant-slug',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'onboarded' => false,
            'email_verified_at' => now(), // verified email but not onboarded
        ]);

        // Inject the tenant context manually for requests
        app()->instance('tenant_id', $this->tenant->id);
    }

    public function test_non_onboarded_user_is_redirected_to_onboarding_page(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertRedirect('/onboarding');
    }

    public function test_onboarding_page_loads_for_non_onboarded_user(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/onboarding');

        $response->assertOk();
    }

    public function test_can_submit_onboarding_for_business(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/onboarding', [
                'account_type' => 'business',
                'business_name' => 'Fabulous Pizza',
                'category' => 'Food & Beverage (Restaurants, Cafes)',
                'subcategory' => 'Cafe & Coffee Shop',
                'business_description' => 'We make artisanal sourdough pizzas.',
                'ai_name' => 'Pizza Bot',
                'ai_tone' => 'warm',
                'greeting_message' => 'Welcome to Fabulous Pizza!',
            ]);

        $response->assertJson(['success' => true]);

        $this->user->refresh();
        $this->tenant->refresh();

        $this->assertTrue((bool)$this->user->onboarded);
        $this->assertEquals('business', $this->tenant->account_type);
        $this->assertEquals('Fabulous Pizza', $this->tenant->name);
        $this->assertEquals('Food & Beverage (Restaurants, Cafes)', $this->tenant->business_category);

        // Check template memories seeded
        $memories = BusinessMemory::where('tenant_id', $this->tenant->id)->get();
        $this->assertCount(4, $memories);

        // Check system prompt is stored
        $systemPrompt = BotSetting::get('system_prompt');
        $this->assertStringContainsString('Pizza Bot', $systemPrompt);
        $this->assertStringContainsString('Fabulous Pizza', $systemPrompt);
    }

    public function test_can_submit_onboarding_for_personal(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/onboarding', [
                'account_type' => 'personal',
                'personal_name' => 'Alex Dev',
                'personal_role' => 'Learn & Experiment',
                'ai_name' => 'Sandbox AI',
                'ai_tone' => 'casual',
                'greeting_message' => 'Hey, Sandbox active!',
            ]);

        $response->assertJson(['success' => true]);

        $this->user->refresh();
        $this->tenant->refresh();

        $this->assertTrue((bool)$this->user->onboarded);
        $this->assertEquals('Alex Dev', $this->user->name);
        $this->assertEquals('personal', $this->tenant->account_type);

        $memories = BusinessMemory::where('tenant_id', $this->tenant->id)->get();
        $this->assertCount(4, $memories);
    }

    public function test_can_skip_whatsapp_connection_and_explore(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/onboarding/skip');

        $response->assertRedirect('/dashboard');

        $this->user->refresh();
        $this->assertTrue((bool)$this->user->onboarded);
    }

    public function test_onboarded_user_can_access_dashboard_directly(): void
    {
        $this->user->onboarded = true;
        $this->user->save();

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertOk();
    }
}
