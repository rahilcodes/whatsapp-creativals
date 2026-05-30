<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrandResolutionMiddleware
{
    /**
     * Resolve which reseller brand is active based on the incoming domain.
     * This runs on EVERY request and dynamically swaps mail, payment config
     * so the entire platform operates under the reseller's identity.
     *
     * NOTE: We intentionally do NOT bind null when no reseller is found.
     * Laravel's container uses isset() internally, which returns false for null,
     * causing a BindingResolutionException when retrieved. Callers should use
     * app()->bound('active_reseller') to check existence safely.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // e.g. "panel.besurebot.com"

        $reseller = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('resellers')) {
                $reseller = Reseller::where('domain', $host)->where('status', 'active')->first();
            }
        } catch (\Throwable $e) {
            // Ignore database connection or schema errors in early bootstraps/testing
        }

        if ($reseller && $reseller->isLicenseValid()) {
            // Bind to app container so any blade/controller can access it
            app()->instance('active_reseller', $reseller);

            Log::info("BrandResolution: Resolved reseller [{$reseller->name}] for domain [{$host}]");

            // ── Swap SMTP dynamically ──────────────────────────
            if ($reseller->smtp_host) {
                config([
                    'mail.mailers.smtp.host'    => $reseller->smtp_host,
                    'mail.mailers.smtp.port'    => $reseller->smtp_port ?? 587,
                    'mail.mailers.smtp.username' => $reseller->smtp_username,
                    'mail.mailers.smtp.password' => $reseller->smtp_password,
                    'mail.from.address'          => $reseller->smtp_from_address ?? $reseller->support_email,
                    'mail.from.name'             => $reseller->smtp_from_name ?? $reseller->name,
                ]);
            }

            // ── Swap Stripe dynamically ────────────────────────
            if ($reseller->stripe_secret) {
                config([
                    'cashier.key'    => $reseller->stripe_key,
                    'cashier.secret' => $reseller->stripe_secret,
                ]);
            }
        }
        // If no reseller found: do nothing. Callers use app()->bound('active_reseller')
        // to check. No null binding — it breaks Laravel's IoC container retrieval.

        return $next($request);
    }
}
