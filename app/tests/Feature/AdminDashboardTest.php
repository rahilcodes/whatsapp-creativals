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
        $response->assertRedirect('/login');
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
}
