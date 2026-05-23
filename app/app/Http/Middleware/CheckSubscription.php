<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Super admins have unrestricted access
            if ($user->is_super_admin) {
                return $next($request);
            }

            $tenant = \App\Models\Tenant::find($user->tenant_id);

            if ($tenant) {
                $status = $tenant->subscription_status;
                $trialExpired = ($status === 'trialing' && $tenant->trial_ends_at && now()->gt($tenant->trial_ends_at));

                // If trial is expired or subscription is inactive, redirect to billing
                if ($status === 'expired' || $status === 'cancelled' || $status === 'past_due' || $trialExpired) {
                    // Update status in DB if trial just ended
                    if ($trialExpired && $status === 'trialing') {
                        $tenant->subscription_status = 'expired';
                        $tenant->save();
                    }

                    // Avoid redirect loops for billing routes and logout
                    if (!$request->routeIs('billing.*') && !$request->is('logout') && !$request->is('billing') && !$request->is('billing/*')) {
                        return redirect()->route('billing.index');
                    }
                }
            }
        }

        return $next($request);
    }
}
