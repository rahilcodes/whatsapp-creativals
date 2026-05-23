<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default tenant and user
        $this->tenant = Tenant::create([
            'name' => "Billing Test Tenant",
            'slug' => 'billing-test',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'onboarded' => true,
            'email_verified_at' => now(),
        ]);

        // Inject the tenant context manually
        app()->instance('tenant_id', $this->tenant->id);
    }

    public function test_trialing_user_can_access_dashboard(): void
    {
        // Tenant is trialing by default (status is 'trialing' and trial_ends_at is in future)
        $this->tenant->subscription_status = 'trialing';
        $this->tenant->trial_ends_at = now()->addDays(2);
        $this->tenant->save();

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertOk();
    }

    public function test_expired_trial_user_is_redirected_to_billing(): void
    {
        $this->tenant->subscription_status = 'trialing';
        $this->tenant->trial_ends_at = now()->subMinutes(1);
        $this->tenant->save();

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertRedirect('/billing');

        // Confirm database status was updated to expired
        $this->tenant->refresh();
        $this->assertEquals('expired', $this->tenant->subscription_status);
    }

    public function test_expired_subscription_user_is_redirected_to_billing(): void
    {
        $this->tenant->subscription_status = 'expired';
        $this->tenant->save();

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertRedirect('/billing');
    }

    public function test_billing_index_page_loads(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/billing');

        $response->assertOk();
        $response->assertViewHas('tenant');
    }

    public function test_can_initiate_subscription_in_test_mode(): void
    {
        // When Razorpay key is not configured, it runs in Test Mode
        $response = $this
            ->actingAs($this->user)
            ->postJson('/billing/subscribe', [
                'plan' => 'starter'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_test' => true,
            'amount' => 124900,
        ]);
        $response->assertJsonStructure(['subscription_id', 'key_id']);
    }

    public function test_can_initiate_onboarding_support_addon_in_test_mode(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->postJson('/billing/support-addon');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_test' => true,
            'amount' => 249900,
        ]);
        $response->assertJsonStructure(['order_id', 'key_id']);
    }

    public function test_can_verify_payment_for_subscription_in_test_mode(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->postJson('/billing/verify', [
                'type' => 'subscription',
                'plan' => 'starter',
                'razorpay_subscription_id' => 'sub_mock_12345',
                'is_test' => true
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->tenant->refresh();
        $this->assertEquals('starter', $this->tenant->plan);
        $this->assertEquals('active', $this->tenant->subscription_status);
        $this->assertEquals('sub_mock_12345', $this->tenant->razorpay_subscription_id);
    }

    public function test_can_verify_payment_for_support_addon_in_test_mode(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->postJson('/billing/verify', [
                'type' => 'addon',
                'is_test' => true
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->tenant->refresh();
        $this->assertTrue((bool)$this->tenant->has_support_addon);
    }

    public function test_webhook_handles_subscription_activated(): void
    {
        $this->tenant->razorpay_subscription_id = 'sub_rzp_99999';
        $this->tenant->subscription_status = 'trialing';
        $this->tenant->save();

        $response = $this->postJson('/webhooks/razorpay', [
            'event' => 'subscription.activated',
            'payload' => [
                'subscription' => [
                    'entity' => [
                        'id' => 'sub_rzp_99999',
                        'status' => 'active'
                    ]
                ]
            ]
        ]);

        $response->assertOk();
        $response->assertSee('Webhook Handled');

        $this->tenant->refresh();
        $this->assertEquals('active', $this->tenant->subscription_status);
    }

    public function test_webhook_handles_subscription_cancelled(): void
    {
        $this->tenant->razorpay_subscription_id = 'sub_rzp_88888';
        $this->tenant->subscription_status = 'active';
        $this->tenant->save();

        $response = $this->postJson('/webhooks/razorpay', [
            'event' => 'subscription.cancelled',
            'payload' => [
                'subscription' => [
                    'entity' => [
                        'id' => 'sub_rzp_88888',
                        'status' => 'cancelled'
                    ]
                ]
            ]
        ]);

        $response->assertOk();
        $response->assertSee('Webhook Handled');

        $this->tenant->refresh();
        $this->assertEquals('cancelled', $this->tenant->subscription_status);
    }

    public function test_webhook_handles_support_addon_payment_captured(): void
    {
        $this->tenant->has_support_addon = false;
        $this->tenant->save();

        $response = $this->postJson('/webhooks/razorpay', [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_abc123',
                        'receipt' => 'receipt_support_' . $this->tenant->id,
                        'order_id' => 'order_xyz789'
                    ]
                ]
            ]
        ]);

        $response->assertOk();
        $response->assertSee('Webhook Handled');

        $this->tenant->refresh();
        $this->assertTrue((bool)$this->tenant->has_support_addon);
    }
}
