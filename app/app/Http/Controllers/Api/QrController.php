<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrController extends Controller
{
    private function checkSecret(Request $request): bool
    {
        return $request->header('X-Bot-Secret') === env('SHARED_SECRET', 'whatsapp_ai_secret_2026');
    }

    // ── POST /api/qr — Node pushes QR here ───────────────────
    // This is called by the Node.js bot. It sends X-Tenant-ID in the header.
    // We MUST scope the update to that specific tenant row only.
    public function store(Request $request): JsonResponse
    {
        if (!$this->checkSecret($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Resolve tenant from the X-Tenant-ID header that Node sends
        $tenantId = (int) ($request->header('X-Tenant-ID') ?? 1);

        // Set tenant context so HasTenant global scope applies correctly
        app()->instance('tenant_id', $tenantId);

        $qr = $request->input('qr'); // null = clear QR (connected)

        // Use the model's own updateQr() which correctly scopes to current tenant
        WhatsappStatus::updateQr($qr);

        return response()->json(['ok' => true]);
    }

    // ── GET /api/qr — Dashboard polls this (web route, session-scoped) ─
    public function show(): JsonResponse
    {
        $status = WhatsappStatus::current();
        return response()->json([
            'qr'     => $status->qr_code,
            'status' => $status->status,
        ]);
    }
}
