<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\BotSetting;
use App\Models\SystemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $normalUser;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard tenant context
        $this->tenant = Tenant::create([
            'name' => 'Acme Organization',
            'slug' => 'acme-org',
            'status' => 'active',
        ]);

        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => true,
            'onboarded' => true,
        ]);

        // Create a normal non-admin user
        $this->normalUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_super_admin' => false,
            'onboarded' => true,
        ]);

        app()->instance('tenant_id', $this->tenant->id);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_can_access_admin_login_page(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_super_admin_is_redirected_away_from_admin_login(): void
    {
        $response = $this
            ->actingAs($this->superAdmin)
            ->get('/admin/login');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_non_super_admin_cannot_login_via_admin_portal(): void
    {
        $this->normalUser->password = bcrypt('password');
        $this->normalUser->save();

        $response = $this->post('/admin/login', [
            'email' => $this->normalUser->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_cannot_login_via_standard_portal(): void
    {
        $this->superAdmin->password = bcrypt('password');
        $this->superAdmin->save();

        $response = $this->post('/login', [
            'email' => $this->superAdmin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_non_super_admin_cannot_access_dashboard(): void
    {
        $response = $this
            ->actingAs($this->normalUser)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $response = $this
            ->actingAs($this->superAdmin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('globalAiEnabled');
    }

    public function test_super_admin_can_retrieve_stats(): void
    {
        $response = $this
            ->actingAs($this->superAdmin)
            ->get('/admin/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'stats' => [
                'total_tenants',
                'active_connections',
                'messages_today',
                'ai_replies_today',
                'chart',
            ]
        ]);
    }

    public function test_super_admin_can_retrieve_tenants(): void
    {
        $response = $this
            ->actingAs($this->superAdmin)
            ->get('/admin/tenants/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tenants' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'status',
                    'whatsapp_status',
                    'health_score',
                    'ai_enabled',
                    'messages_today',
                    'last_active',
                ]
            ]
        ]);
    }

    public function test_super_admin_can_toggle_global_ai(): void
    {
        // Default is '1' (enabled)
        $this->assertEquals('1', SystemStatus::get('global_ai_enabled'));

        $response = $this
            ->actingAs($this->superAdmin)
            ->post('/admin/toggle-ai');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'global_ai_enabled' => false
        ]);

        $this->assertEquals('0', SystemStatus::get('global_ai_enabled'));

        // Toggle back
        $response = $this
            ->actingAs($this->superAdmin)
            ->post('/admin/toggle-ai');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'global_ai_enabled' => true
        ]);

        $this->assertEquals('1', SystemStatus::get('global_ai_enabled'));
    }

    public function test_super_admin_can_toggle_tenant_ai(): void
    {
        // Seed initial bot_enabled key for the tenant
        BotSetting::set('bot_enabled', '1');

        $response = $this
            ->actingAs($this->superAdmin)
            ->post('/admin/toggle-ai', [
                'tenant_id' => $this->tenant->id
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ai_enabled' => false
        ]);

        // Verify key updated
        $dbValue = \DB::table('bot_settings')
            ->where('tenant_id', $this->tenant->id)
            ->where('key', 'bot_enabled')
            ->value('value');
        $this->assertEquals('0', $dbValue);
    }

    public function test_super_admin_can_pause_tenant(): void
    {
        $this->assertEquals('active', $this->tenant->status);

        $response = $this
            ->actingAs($this->superAdmin)
            ->post('/admin/pause-tenant', [
                'tenant_id' => $this->tenant->id
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'suspended'
        ]);

        $this->tenant->refresh();
        $this->assertEquals('suspended', $this->tenant->status);
    }

    public function test_super_admin_can_retrieve_health_check(): void
    {
        $response = $this
            ->actingAs($this->superAdmin)
            ->get('/admin/system-health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'health' => [
                'laravel_status',
                'db_latency',
                'node_status',
                'node_uptime',
                'node_latency',
                'global_ai_enabled',
            ]
        ]);
    }

    public function test_super_admin_can_impersonate_tenant(): void
    {
        $tenantB = Tenant::create([
            'name' => 'Client Tenant B',
            'slug' => 'client-b',
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'tenant_id' => $tenantB->id,
            'is_super_admin' => false,
            'onboarded' => true,
        ]);

        $response = $this
            ->actingAs($this->superAdmin)
            ->post(route('admin.impersonate', $tenantB));

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($tenantUser->id, auth()->id());
        $this->assertEquals($this->superAdmin->id, session('impersonator_id'));
    }

    public function test_impersonated_tenant_user_has_exit_banner_on_pages(): void
    {
        $tenantB = Tenant::create([
            'name' => 'Client Tenant B',
            'slug' => 'client-b',
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'tenant_id' => $tenantB->id,
            'is_super_admin' => false,
            'onboarded' => true,
        ]);

        // Access dashboard page while impersonating
        $response = $this
            ->actingAs($tenantUser)
            ->withSession(['impersonator_id' => $this->superAdmin->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Impersonation Mode: Logged in as');
        $response->assertSee(e($tenantUser->name));
        $response->assertSee('Return to Admin Panel');
    }

    public function test_super_admin_can_stop_impersonating(): void
    {
        $tenantB = Tenant::create([
            'name' => 'Client Tenant B',
            'slug' => 'client-b',
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'tenant_id' => $tenantB->id,
            'is_super_admin' => false,
            'onboarded' => true,
        ]);

        $response = $this
            ->actingAs($tenantUser)
            ->withSession(['impersonator_id' => $this->superAdmin->id])
            ->post(route('admin.impersonate.stop'));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertEquals($this->superAdmin->id, auth()->id());
        $this->assertFalse(session()->has('impersonator_id'));
    }

    public function test_super_admin_can_reconnect_tenant_whatsapp_bot(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'http://127.0.0.1:3000/reconnect' => \Illuminate\Support\Facades\Http::response(['success' => true], 200),
        ]);

        $response = $this
            ->actingAs($this->superAdmin)
            ->post(route('admin.reconnect-tenant'), [
                'tenant_id' => $this->tenant->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => "WhatsApp socket reconnect initiated for {$this->tenant->name}."
        ]);
    }

    public function test_super_admin_can_manually_provision_tenant_without_precomplete(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'http://127.0.0.1:3000/start' => \Illuminate\Support\Facades\Http::response(['success' => true], 200),
        ]);

        $newTenantData = [
            'tenant_name' => 'Provisioned Tenant',
            'slug' => 'provisioned-tenant',
            'account_type' => 'business',
            'user_name' => 'Admin User',
            'email' => 'admin@provisioned.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'pre_complete_onboarding' => 0,
        ];

        $response = $this
            ->actingAs($this->superAdmin)
            ->post(route('admin.tenants.store'), $newTenantData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => "Tenant 'Provisioned Tenant' and administrator account provisioned successfully!"
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Provisioned Tenant',
            'slug' => 'provisioned-tenant',
        ]);

        $tenant = Tenant::where('slug', 'provisioned-tenant')->first();
        $this->assertNotNull($tenant);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@provisioned.com',
            'onboarded' => 0,
        ]);
    }

    public function test_super_admin_can_manually_provision_tenant_with_precomplete(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'http://127.0.0.1:3000/start' => \Illuminate\Support\Facades\Http::response(['success' => true], 200),
        ]);

        $newTenantData = [
            'tenant_name' => 'Precompleted Tenant',
            'slug' => 'precompleted-tenant',
            'account_type' => 'business',
            'user_name' => 'Pre Admin User',
            'email' => 'preadmin@precompleted.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'pre_complete_onboarding' => 1,
        ];

        $response = $this
            ->actingAs($this->superAdmin)
            ->post(route('admin.tenants.store'), $newTenantData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => "Tenant 'Precompleted Tenant' and administrator account provisioned successfully!"
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Precompleted Tenant',
            'slug' => 'precompleted-tenant',
        ]);

        $tenant = Tenant::where('slug', 'precompleted-tenant')->first();
        $this->assertNotNull($tenant);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'name' => 'Pre Admin User',
            'email' => 'preadmin@precompleted.com',
            'onboarded' => 1,
        ]);

        // Verify seeded memories and settings inside the tenant database
        $this->assertDatabaseHas('business_memories', [
            'tenant_id' => $tenant->id,
            'category' => 'services',
            'key' => 'Bespoke Services',
            'value' => "We provide local custom solutions matching your exact business needs.",
        ]);

        $this->assertDatabaseHas('bot_settings', [
            'tenant_id' => $tenant->id,
            'key' => 'bot_enabled',
            'value' => '1',
        ]);
        
        $this->assertDatabaseHas('bot_settings', [
            'tenant_id' => $tenant->id,
            'key' => 'system_prompt',
        ]);
    }
}

