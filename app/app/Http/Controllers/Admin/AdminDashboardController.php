<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Message;
use App\Models\BotSetting;
use App\Models\ActivityLog;
use App\Models\SystemStatus;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // Bypass multi-tenant global scoping by removing tenant_id from application container
        app()->forgetInstance('tenant_id');
    }

    private function disableTenantScope(): void
    {
        app()->forgetInstance('tenant_id');
    }

    /**
     * Render the admin dashboard view.
     */
    public function dashboard()
    {
        $this->disableTenantScope();
        
        $globalAiEnabled = SystemStatus::get('global_ai_enabled', '1') === '1';
        
        return view('admin.dashboard', compact('globalAiEnabled'));
    }

    /**
     * Get aggregate statistics for the admin dashboard.
     */
    public function stats(): JsonResponse
    {
        $this->disableTenantScope();

        $totalTenants = Tenant::count();
        
        $activeConnections = DB::table('whatsapp_status')
            ->where('status', 'connected')
            ->count();

        $messagesToday = Message::withoutGlobalScope('tenant')
            ->whereDate('created_at', today())
            ->count();

        $aiRepliesToday = Message::withoutGlobalScope('tenant')
            ->where('role', 'assistant')
            ->whereDate('created_at', today())
            ->count();

        // 24 hours message chart aggregation
        $now = now();
        $chartData = [];
        for ($i = 23; $i >= 0; $i--) {
            $hourStart = $now->copy()->subHours($i)->startOfHour();
            $hourEnd = $now->copy()->subHours($i)->endOfHour();
            
            $count = Message::withoutGlobalScope('tenant')
                ->whereBetween('created_at', [$hourStart, $hourEnd])
                ->count();

            $chartData[] = [
                'label' => $hourStart->format('H:00'),
                'count' => $count,
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_connections' => $activeConnections,
                'messages_today' => $messagesToday,
                'ai_replies_today' => $aiRepliesToday,
                'chart' => $chartData,
            ]
        ]);
    }

    /**
     * Get the list of all tenants with active statuses.
     */
    public function tenants(): JsonResponse
    {
        $this->disableTenantScope();

        $tenants = Tenant::with(['whatsappStatus'])->get()->map(function ($tenant) {
            $aiSetting = BotSetting::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('key', 'bot_enabled')
                ->first();
                
            $aiEnabled = $aiSetting ? ($aiSetting->value === '1') : true;

            $messagesToday = Message::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->whereDate('created_at', today())
                ->count();

            $lastMessage = Message::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->latest()
                ->first();
                
            $lastActive = $lastMessage ? $lastMessage->created_at->diffForHumans() : 'N/A';

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status,
                'whatsapp_status' => $tenant->whatsappStatus ? $tenant->whatsappStatus->status : 'disconnected',
                'health_score' => $tenant->whatsappStatus ? ($tenant->whatsappStatus->health_score ?? 100) : 100,
                'ai_enabled' => $aiEnabled,
                'messages_today' => $messagesToday,
                'last_active' => $lastActive,
            ];
        });

        return response()->json([
            'success' => true,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Toggle AI status globally or for a specific tenant.
     */
    public function toggleAi(Request $request): JsonResponse
    {
        $this->disableTenantScope();

        $tenantId = $request->input('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);
            
            $aiSetting = BotSetting::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('key', 'bot_enabled')
                ->first();
                
            $newValue = ($aiSetting && $aiSetting->value === '1') ? '0' : '1';
            
            BotSetting::withoutGlobalScope('tenant')->updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => 'bot_enabled'],
                ['value' => $newValue]
            );

            AdminLog::log('toggle_tenant_ai', "Toggled AI for tenant: {$tenant->name} (ID: {$tenantId}) to " . ($newValue === '1' ? 'ON' : 'OFF'));

            return response()->json([
                'success' => true,
                'message' => "AI status toggled for tenant {$tenant->name}",
                'ai_enabled' => $newValue === '1',
            ]);
        } else {
            // Global toggle
            $current = SystemStatus::get('global_ai_enabled', '1');
            $newValue = $current === '1' ? '0' : '1';
            
            SystemStatus::set('global_ai_enabled', $newValue);

            AdminLog::log('toggle_global_ai', "Toggled global AI engine status to " . ($newValue === '1' ? 'ON' : 'OFF'));

            return response()->json([
                'success' => true,
                'message' => "Global AI engine toggled " . ($newValue === '1' ? 'ON' : 'OFF'),
                'global_ai_enabled' => $newValue === '1',
            ]);
        }
    }

    /**
     * Pause or resume a specific tenant.
     */
    public function pauseTenant(Request $request): JsonResponse
    {
        $this->disableTenantScope();

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id'
        ]);

        $tenant = Tenant::findOrFail($request->input('tenant_id'));
        $newStatus = $tenant->status === 'suspended' ? 'active' : 'suspended';
        
        $tenant->status = $newStatus;
        $tenant->save();

        AdminLog::log('pause_tenant', "Suspended status for tenant {$tenant->name} (ID: {$tenant->id}) toggled to: {$newStatus}");

        return response()->json([
            'success' => true,
            'message' => "Tenant status changed to {$newStatus}",
            'status' => $newStatus,
        ]);
    }

    /**
     * Get live activity logs.
     */
    public function activity(): JsonResponse
    {
        $this->disableTenantScope();

        $logs = ActivityLog::withoutGlobalScope('tenant')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(function ($log) {
                // Find tenant without scoping
                $tenant = Tenant::find($log->tenant_id);
                
                return [
                    'id' => $log->id,
                    'tenant_id' => $log->tenant_id,
                    'tenant_name' => $tenant ? $tenant->name : 'System',
                    'type' => $log->type,
                    'phone' => $log->phone,
                    'description' => $log->description,
                    'time' => $log->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Check system health and responsiveness.
     */
    public function systemHealth(): JsonResponse
    {
        $this->disableTenantScope();

        // 1. Laravel DB latency
        $dbStart = microtime(true);
        DB::select('SELECT 1');
        $dbLatency = round((microtime(true) - $dbStart) * 1000, 1);

        // 2. Node.js bot status
        $nodeOnline = false;
        $nodeUptime = 'N/A';
        $nodeLatency = 'N/A';
        
        $nodeStart = microtime(true);
        try {
            // Node server runs on port 3000 locally
            $response = Http::timeout(1.0)->get('http://127.0.0.1:3000/health');
            if ($response->successful()) {
                $nodeOnline = true;
                $uptimeSec = $response->json('uptime') ?? 0;
                $nodeUptime = $uptimeSec > 3600 
                    ? round($uptimeSec / 3600, 1) . ' hours' 
                    : round($uptimeSec) . ' seconds';
                    
                $nodeLatency = round((microtime(true) - $nodeStart) * 1000, 1) . ' ms';
            }
        } catch (\Throwable $e) {
            // offline
        }

        $globalAiEnabled = SystemStatus::get('global_ai_enabled', '1') === '1';

        return response()->json([
            'success' => true,
            'health' => [
                'laravel_status' => 'working',
                'db_latency' => $dbLatency . ' ms',
                'node_status' => $nodeOnline ? 'online' : 'offline',
                'node_uptime' => $nodeUptime,
                'node_latency' => $nodeLatency,
                'global_ai_enabled' => $globalAiEnabled,
            ]
        ]);
    }
}
