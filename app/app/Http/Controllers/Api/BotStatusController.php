<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BotSetting;
use App\Models\WhatsappStatus;
use App\Services\BotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotStatusController extends Controller
{
    public function __construct(private BotService $bot) {}

    // ── GET /api/bot-status ───────────────────────────────────
    public function index(): JsonResponse
    {
        $wa      = WhatsappStatus::current();
        $nodeUp  = $this->bot->isNodeRunning();

        return response()->json([
            'wa_status'       => $wa->status,
            'wa_connected'    => $wa->isConnected(),
            'qr_available'    => !empty($wa->qr_code),
            'bot_enabled'     => BotSetting::get('bot_enabled', '1') === '1',
            'node_running'    => $nodeUp,
            'last_connected'  => $wa->last_connected_at?->toIso8601String(),
            'working_hours'   => [
                'start' => BotSetting::get('working_hours_start', '00:00'),
                'end'   => BotSetting::get('working_hours_end', '23:59'),
            ],
        ]);
    }

    // ── GET /api/bot/health ───────────────────────────────────
    public function health(): JsonResponse
    {
        $wa = WhatsappStatus::current();
        $nodeHealth = $this->bot->getSessionHealth();

        // Calculate message success rate for last 100 messages
        $lastMessages = \App\Models\Message::where('role', 'assistant')
            ->latest()
            ->take(100)
            ->get();
            
        $total = $lastMessages->count();
        $success = $lastMessages->where('delivery_status', 'sent')->count();
        $successRate = $total > 0 ? round(($success / $total) * 100) : 100;

        return response()->json([
            'health_score'    => $wa->health_score ?? 100,
            'session_state'   => $wa->session_state ?? 'idle',
            'reconnect_count' => $wa->reconnect_count ?? 0,
            'ban_risk'        => $wa->isBanRisk(),
            'uptime_seconds'  => $nodeHealth['uptime_seconds'] ?? 0,
            'last_seen'       => $wa->updated_at?->diffForHumans() ?? 'Never',
            'success_rate'    => $successRate,
        ]);
    }

    // ── GET /api/activity ─────────────────────────────────────
    public function activity(): JsonResponse
    {
        $logs = ActivityLog::latest()->take(50)->get()->map(function($log) {
            return [
                'id' => $log->id,
                'type' => $log->type,
                'description' => $log->description,
                'phone' => $log->phone,
                'time' => $log->created_at->diffForHumans(),
            ];
        });
        return response()->json(['logs' => $logs]);
    }


    // ── POST /api/bot/toggle ──────────────────────────────────
    public function toggle(Request $request): JsonResponse
    {
        $current = BotSetting::get('bot_enabled', '1');
        $new     = $current === '1' ? '0' : '1';
        BotSetting::set('bot_enabled', $new);

        $label = $new === '1' ? 'enabled' : 'disabled';
        ActivityLog::record('status_change', "Bot {$label} from dashboard");

        return response()->json(['bot_enabled' => $new === '1']);
    }

    // ── POST /api/bot/reconnect ───────────────────────────────
    public function reconnect(): JsonResponse
    {
        $success = $this->bot->reconnect();
        return response()->json(['success' => $success]);
    }

    // ── POST /api/bot/pause ───────────────────────────────────
    public function pause(): JsonResponse
    {
        BotSetting::set('bot_enabled', '0');
        ActivityLog::record('status_change', 'Bot paused from dashboard');
        return response()->json(['paused' => true]);
    }

    // ── POST /api/bot/resume ──────────────────────────────────
    public function resume(): JsonResponse
    {
        BotSetting::set('bot_enabled', '1');
        ActivityLog::record('status_change', 'Bot resumed from dashboard');
        return response()->json(['resumed' => true]);
    }

    // ── POST /api/bot/start — Start session for current tenant ─
    public function startEngine(): JsonResponse
    {
        $tenantId = app()->has('tenant_id') ? (int) app('tenant_id') : 1;

        // Always call startSession for this tenant specifically.
        // Node.js will create a new isolated session for this tenantId.
        $success = $this->bot->startSession($tenantId);

        ActivityLog::record('status_change', "WhatsApp engine start requested for Tenant {$tenantId}");

        return response()->json(['status' => $success ? 'starting' : 'error', 'tenant_id' => $tenantId]);
    }
}
