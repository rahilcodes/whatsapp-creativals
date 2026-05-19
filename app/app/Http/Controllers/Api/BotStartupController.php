<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BotStartupController extends Controller
{
    private function checkSecret(Request $request): bool
    {
        return $request->header('X-Bot-Secret') === env('SHARED_SECRET', 'whatsapp_ai_secret_2026');
    }

    // ── GET /api/tenants/active ───────────────────────────────
    // Called by Node.js on startup to know which tenant IDs to start
    public function getActiveTenants(Request $request): JsonResponse
    {
        if (!$this->checkSecret($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get all distinct tenant IDs that have real users
        $tenantIds = User::withoutGlobalScopes()
            ->whereNotNull('tenant_id')
            ->pluck('tenant_id')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return response()->json(['tenant_ids' => $tenantIds]);
    }

    // ── POST /api/bot/startup-reset ───────────────────────────
    // Called by Node.js on startup — resets ALL tenant statuses to disconnected
    // so stale "connected" states from a previous run don't persist
    public function startupReset(Request $request): JsonResponse
    {
        if (!$this->checkSecret($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('whatsapp_status')->update([
            'status'        => 'disconnected',
            'session_state' => 'idle',
            'qr_code'       => null,
            'updated_at'    => now(),
        ]);

        return response()->json(['ok' => true, 'reset' => true]);
    }
}
