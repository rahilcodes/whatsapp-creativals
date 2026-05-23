<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    private string $keyId;
    private string $keySecret;
    private bool $isConfigured;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id', '');
        $this->keySecret = config('services.razorpay.key_secret', '');
        $this->isConfigured = !empty($this->keyId) && !empty($this->keySecret);
    }

    /**
     * Render the Billing dashboard portal.
     */
    public function index()
    {
        $tenant = auth()->user()->tenant;
        
        // Calculate trial days/hours remaining
        $trialRemaining = '';
        if ($tenant->subscription_status === 'trialing' && $tenant->trial_ends_at) {
            $hours = now()->diffInHours($tenant->trial_ends_at, false);
            if ($hours > 0) {
                $days = ceil($hours / 24);
                $trialRemaining = $days > 1 ? "{$days} days" : "{$hours} hours";
            }
        }

        return view('billing.index', compact('tenant', 'trialRemaining'));
    }

    /**
     * Create a Razorpay Subscription (Starter ₹2,499/mo or Automator ₹5,999/mo).
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => 'required|in:starter,automator'
        ]);

        $tenant = auth()->user()->tenant;
        $plan = $request->input('plan');

        // Select Plan IDs (slashed pricing amounts represented as recurring subscriptions)
        // Defaults: Starter = plan_starter_test (₹2,499/mo), Automator = plan_automator_test (₹5,999/mo)
        $planIdEnv = $plan === 'starter' 
            ? config('services.razorpay.plan_starter', 'plan_starter_test') 
            : config('services.razorpay.plan_automator', 'plan_automator_test');

        if (!$this->isConfigured) {
            // Local Test Mode: Return simulated Razorpay subscription details
            $mockSubId = 'sub_mock_' . strtolower(\Illuminate\Support\Str::random(10));
            return response()->json([
                'success' => true,
                'is_test' => true,
                'subscription_id' => $mockSubId,
                'key_id' => 'rzp_test_mockkey',
                'amount' => $plan === 'starter' ? 124900 : 299900,
                'name' => $plan === 'starter' ? 'iChatUp Starter Autopilot' : 'iChatUp Automator Pro',
            ]);
        }

        try {
            // Create subscription via Razorpay API
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(10)
                ->post('https://api.razorpay.com/v1/subscriptions', [
                    'plan_id' => $planIdEnv,
                    'total_count' => 120, // 10 years
                    'quantity' => 1,
                    'customer_notify' => 1,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'is_test' => false,
                    'subscription_id' => $response->json('id'),
                    'key_id' => $this->keyId,
                ]);
            }

            Log::error('Razorpay Subscription creation error response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription with Razorpay: ' . ($response->json('error.description') ?? 'Unknown error')
            ], 400);

        } catch (\Throwable $e) {
            Log::error('Razorpay Subscription exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Billing server connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a Razorpay Order for Onboarding Support Add-on (₹2,499 one-time).
     */
    public function purchaseSupportAddon(Request $request): JsonResponse
    {
        $tenant = auth()->user()->tenant;

        if (!$this->isConfigured) {
            // Local Test Mode: Return simulated Order details
            $mockOrderId = 'order_mock_' . strtolower(\Illuminate\Support\Str::random(10));
            return response()->json([
                'success' => true,
                'is_test' => true,
                'order_id' => $mockOrderId,
                'key_id' => 'rzp_test_mockkey',
                'amount' => 249900,
                'name' => 'iChatUp Setup Support Onboarding',
            ]);
        }

        try {
            // Create a one-time order
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(10)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => 249900, // ₹2,499 in paise
                    'currency' => 'INR',
                    'receipt' => 'receipt_support_' . $tenant->id,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'is_test' => false,
                    'order_id' => $response->json('id'),
                    'key_id' => $this->keyId,
                    'amount' => 249900
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create support order: ' . ($response->json('error.description') ?? 'Unknown error')
            ], 400);

        } catch (\Throwable $e) {
            Log::error('Razorpay Order exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment signature/status returned from front-end checkout callback.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $tenant = auth()->user()->tenant;

        $type = $request->input('type'); // subscription or addon
        $isTest = $request->boolean('is_test', false);

        if ($isTest || !$this->isConfigured) {
            // Verify in test mode (automatic success)
            if ($type === 'subscription') {
                $tenant->plan = $request->input('plan', 'starter');
                $tenant->subscription_status = 'active';
                $tenant->razorpay_subscription_id = $request->input('razorpay_subscription_id', 'sub_mock_test');
                $tenant->save();
                
                \App\Models\AdminLog::log('test_subscription_activate', "Simulated subscription active for: {$tenant->name} (Plan: {$tenant->plan})");
            } else {
                $tenant->has_support_addon = true;
                $tenant->save();
                
                \App\Models\AdminLog::log('test_addon_purchase', "Simulated support addon purchased for: {$tenant->name}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Test payment completed successfully!'
            ]);
        }

        // Live signature verification (standard Razorpay signature verification)
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        if ($type === 'subscription') {
            $subscriptionId = $request->input('razorpay_subscription_id');
            $expectedSignature = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $this->keySecret);
            
            if ($signature === $expectedSignature) {
                $tenant->plan = $request->input('plan', 'starter');
                $tenant->subscription_status = 'active';
                $tenant->razorpay_subscription_id = $subscriptionId;
                $tenant->save();

                \App\Models\AdminLog::log('subscription_activate', "Razorpay subscription verified for: {$tenant->name} (Plan: {$tenant->plan})");

                return response()->json(['success' => true]);
            }
        } else {
            $orderId = $request->input('razorpay_order_id');
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
            
            if ($signature === $expectedSignature) {
                $tenant->has_support_addon = true;
                $tenant->save();

                \App\Models\AdminLog::log('addon_purchase', "Razorpay support addon verified for: {$tenant->name}");

                return response()->json(['success' => true]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment signature verification failed.'
        ], 400);
    }
}
