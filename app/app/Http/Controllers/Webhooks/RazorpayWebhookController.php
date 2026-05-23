<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    /**
     * Handle incoming Razorpay Webhook request.
     */
    public function handle(Request $request): Response
    {
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = config('services.razorpay.webhook_secret', '');

        // Verify webhook signature if secret is configured
        if (!empty($webhookSecret)) {
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if ($signature !== $expectedSignature) {
                Log::warning('Razorpay Webhook: Signature verification failed.');
                return response('Invalid signature', 400);
            }
        }

        $event = $request->input('event');
        Log::info("Razorpay Webhook Event Received: {$event}");

        switch ($event) {
            case 'subscription.charged':
            case 'subscription.activated':
                $this->handleSubscriptionCharged($request);
                break;

            case 'subscription.cancelled':
            case 'subscription.halted':
                $this->handleSubscriptionCancelled($request);
                break;

            case 'order.paid':
            case 'payment.captured':
                $this->handlePaymentCaptured($request);
                break;
        }

        return response('Webhook Handled', 200);
    }

    /**
     * Handle successful subscription payment/activation.
     */
    protected function handleSubscriptionCharged(Request $request): void
    {
        $payload = $request->input('payload.subscription.entity');
        if (!$payload) return;

        $subscriptionId = $payload['id'];
        $status = $payload['status']; // active, halted, etc.

        // Resolve tenant bypassing multi-tenant global scoping
        $tenant = Tenant::withoutGlobalScope('tenant')
            ->where('razorpay_subscription_id', $subscriptionId)
            ->first();

        if ($tenant) {
            $tenant->subscription_status = 'active';
            $tenant->save();

            \App\Models\AdminLog::log('webhook_subscription_active', "Webhook updated subscription to active for: {$tenant->name} (ID: {$tenant->id})");
            Log::info("Razorpay Webhook: Subscription {$subscriptionId} set to active for tenant {$tenant->id}.");
        } else {
            Log::warning("Razorpay Webhook: Tenant not found for Subscription ID {$subscriptionId}.");
        }
    }

    /**
     * Handle subscription cancellation or failure.
     */
    protected function handleSubscriptionCancelled(Request $request): void
    {
        $payload = $request->input('payload.subscription.entity');
        if (!$payload) return;

        $subscriptionId = $payload['id'];

        $tenant = Tenant::withoutGlobalScope('tenant')
            ->where('razorpay_subscription_id', $subscriptionId)
            ->first();

        if ($tenant) {
            $tenant->subscription_status = 'cancelled';
            $tenant->save();

            \App\Models\AdminLog::log('webhook_subscription_cancelled', "Webhook updated subscription to cancelled for: {$tenant->name} (ID: {$tenant->id})");
            Log::info("Razorpay Webhook: Subscription {$subscriptionId} set to cancelled for tenant {$tenant->id}.");
        }
    }

    /**
     * Handle lead setup support addon payment capture.
     */
    protected function handlePaymentCaptured(Request $request): void
    {
        $payload = $request->input('payload.payment.entity');
        if (!$payload) return;

        // Verify receipt or order ID
        $receipt = $payload['receipt'] ?? null;
        $orderId = $payload['order_id'] ?? null;

        $tenant = null;

        // Try matching by receipt
        if ($receipt && str_starts_with($receipt, 'receipt_support_')) {
            $tenantId = (int) str_replace('receipt_support_', '', $receipt);
            $tenant = Tenant::withoutGlobalScope('tenant')->find($tenantId);
        }

        if ($tenant) {
            $tenant->has_support_addon = true;
            $tenant->save();

            \App\Models\AdminLog::log('webhook_addon_verified', "Webhook verified onboarding support addon payment for: {$tenant->name}");
            Log::info("Razorpay Webhook: Support onboarding addon flagged for tenant {$tenant->id}.");
        }
    }
}
