<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerAdminMiddleware
{
    /**
     * Ensure the currently authenticated user is a reseller admin,
     * AND that the active reseller (resolved from domain) matches their account.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Must be logged in and be a reseller_admin
        if (!$user || $user->role !== 'reseller_admin') {
            abort(403, 'Access denied. Reseller admin privileges required.');
        }

        // Safe check — BrandResolutionMiddleware only binds when a reseller is found
        // (binding null breaks Laravel's IoC container due to isset() internals)
        if (!app()->bound('active_reseller')) {
            abort(403, 'No reseller portal configured for this domain. Please check your domain settings.');
        }

        $activeReseller = app('active_reseller');

        // Ensure the reseller admin only manages their own brand
        if ($user->reseller_id !== $activeReseller->id) {
            abort(403, 'Access denied. You do not manage this reseller brand.');
        }

        return $next($request);
    }
}

