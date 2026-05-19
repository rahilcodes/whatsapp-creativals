<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = null;

        // If accessed via API/Webhook with a header
        if ($request->hasHeader('X-Tenant-ID')) {
            $tenantId = $request->header('X-Tenant-ID');
        } 
        // If accessed via Dashboard / Web session
        elseif (\Illuminate\Support\Facades\Auth::check()) {
            $tenantId = \Illuminate\Support\Facades\Auth::user()->tenant_id;
        }

        // Only enforce tenant id if we have one. If we don't have one, 
        // it means the user is not logged in or it's a global route.
        if ($tenantId) {
            \Illuminate\Support\Facades\Log::info('TenantMiddleware: Resolved tenant ID ' . $tenantId . ' for user ' . (\Illuminate\Support\Facades\Auth::id() ?? 'guest'));
            app()->instance('tenant_id', $tenantId);
        } else {
            \Illuminate\Support\Facades\Log::info('TenantMiddleware: Fallback to tenant ID 1 for user ' . (\Illuminate\Support\Facades\Auth::id() ?? 'guest'));
            app()->instance('tenant_id', 1);
        }

        return $next($request);
    }
}
