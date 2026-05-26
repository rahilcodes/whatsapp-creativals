<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrController extends Controller
{
    private function checkSecret(Request $request): bool
    {
        return $request->header('X-Bot-Secret') === config('services.bot.secret');
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

    // ── GET /api/qr — Dashboard + Onboarding polls this (web route, session-scoped) ─
    public function show(): JsonResponse
    {
        $status = WhatsappStatus::current();
        return response()->json([
            'qr'            => $status->qr_code,
            'status'        => $status->status,
            'session_state' => $status->session_state,
        ]);
    }

    // ── GET /api/qr/stream — Server-Sent Events real-time push ────────
    // Replaces polling: browser opens one connection and receives instant
    // updates whenever the QR or status changes (checks DB every 1 second).
    public function stream(): StreamedResponse
    {
        return response()->stream(function () {
            $lastHash  = null;
            $startTime = microtime(true);
            $maxDuration = 90; // Reconnect every 90 seconds to keep connection healthy

            // Send an initial heartbeat immediately so the browser knows the connection is live
            echo ": heartbeat\n\n";
            if (ob_get_level()) { ob_flush(); }
            flush();

            while (true) {
                // Max connection duration reached — tell client to reconnect
                if (microtime(true) - $startTime > $maxDuration) {
                    echo "event: reconnect\ndata: {}\n\n";
                    if (ob_get_level()) { ob_flush(); }
                    flush();
                    break;
                }

                // Client disconnected — stop the loop
                if (connection_aborted()) break;

                sleep(1);

                try {
                    $status = WhatsappStatus::current();
                    $hash   = md5(($status->qr_code ?? '') . $status->status);

                    if ($hash !== $lastHash) {
                        $lastHash = $hash;
                        $payload  = json_encode([
                            'qr'     => $status->qr_code,
                            'status' => $status->status,
                        ]);
                        echo "data: {$payload}\n\n";
                        if (ob_get_level()) { ob_flush(); }
                        flush();
                    }
                } catch (\Throwable) {
                    break;
                }
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',   // Disable nginx buffering
            'Connection'        => 'keep-alive',
        ]);
    }

    // ── POST /api/qr/clear — Clear auth session + force fresh QR ─────
    public function clearSession(): JsonResponse
    {
        // Clear the QR and reset status so dashboard shows Generate QR button
        WhatsappStatus::updateQr(null);

        return response()->json(['ok' => true, 'message' => 'Session cleared — click Generate QR to reconnect']);
    }
}
