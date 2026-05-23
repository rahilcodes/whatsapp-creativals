<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BotSetting;
use App\Models\Message;
use App\Models\WhatsappStatus;
use App\Services\BotService;

class DashboardController extends Controller
{
    public function __construct(private BotService $bot) {}

    public function index()
    {
        if (\Illuminate\Support\Facades\Auth::user()->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        \Illuminate\Support\Facades\Log::info('DashboardController: user_id=' . (\Illuminate\Support\Facades\Auth::id() ?? 'null') . ', tenant_id=' . app('tenant_id'));

        $waStatus    = WhatsappStatus::current();
        $botEnabled  = BotSetting::get('bot_enabled') === '1';
        $nodeRunning = $this->bot->isNodeRunning();
        $logs        = ActivityLog::recent(25);
        $totalMsgs   = Message::count();
        $totalChats  = Message::distinct('phone')->count('phone');
        $todayMsgs   = Message::whereDate('created_at', today())->count();
        $settings    = BotSetting::allAsMap();

        return view('dashboard.index', compact(
            'waStatus', 'botEnabled', 'nodeRunning',
            'logs', 'totalMsgs', 'totalChats', 'todayMsgs', 'settings'
        ));
    }
}
