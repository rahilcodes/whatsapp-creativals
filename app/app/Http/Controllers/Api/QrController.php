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
    public function store(Request $request): JsonResponse
    {
        if (!$this->checkSecret($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $qr = $request->input('qr'); // null = clear QR (connected)
        WhatsappStatus::query()->update([
            'qr_code'    => $qr,
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── GET /api/qr — Dashboard polls this ───────────────────
    public function show(): JsonResponse
    {
        $status = WhatsappStatus::current();
        return response()->json([
            'qr'     => $status->qr_code,
            'status' => $status->status,
        ]);
    }
}
