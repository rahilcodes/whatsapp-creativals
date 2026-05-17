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
            'bot_enabled'     => BotSetting::get('bot_enabled') === '1',
            'node_running'    => $nodeUp,
            'last_connected'  => $wa->last_connected_at?->toIso8601String(),
            'working_hours'   => [
                'start' => BotSetting::get('working_hours_start', '09:00'),
                'end'   => BotSetting::get('working_hours_end', '21:00'),
            ],
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

    // ── POST /api/bot/start — spawn Node.js in background ────
    public function startEngine(): JsonResponse
    {
        // Already running? Force a reconnect so it clears old session and makes a new QR
        if ($this->bot->isNodeRunning()) {
            $this->bot->reconnect();
            return response()->json(['status' => 'already_running', 'message' => 'Triggered reconnect']);
        }

        // Resolve absolute path to the bot directory (one level up from /app)
        $botDir  = realpath(base_path('../bot'));
        $logFile = $botDir . DIRECTORY_SEPARATOR . 'bot.log';

        if (!$botDir || !is_dir($botDir)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bot directory not found at: ' . base_path('../bot'),
            ], 500);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // WScript.Shell: the only reliable way to detach from PHP/web-server context.
            // VBS strings use "" (doubled) for literal quotes — not backslash escapes.
            $botEsc = str_replace('/', '\\', $botDir);

            $vbs  = "Set ws = CreateObject(\"WScript.Shell\")\r\n";
            $vbs .= "ws.CurrentDirectory = \"{$botEsc}\"\r\n";
            $vbs .= "ws.Run \"node src\\index.js\", 0, False\r\n";

            $vbsPath = sys_get_temp_dir() . '\\start_wa_bot.vbs';
            file_put_contents($vbsPath, $vbs);
            exec("wscript.exe \"{$vbsPath}\"");
        } else {
            $cmd = "cd \"{$botDir}\" && nohup node src/index.js >> \"{$logFile}\" 2>&1 &";
            exec($cmd);
        }

        ActivityLog::record('status_change', 'WhatsApp engine started from dashboard');

        return response()->json(['status' => 'starting']);
    }
}
