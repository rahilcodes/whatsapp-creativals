<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Message;
use App\Models\BotSetting;
use App\Models\ActivityLog;
use App\Models\SystemStatus;
use App\Models\AdminLog;
use App\Models\User;
use App\Services\BotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
                'plan' => $tenant->plan,
                'subscription_status' => $tenant->subscription_status,
                'has_support_addon' => (bool) $tenant->has_support_addon,
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

    /**
     * Impersonate a tenant by logging in as their first user.
     */
    public function impersonate(Tenant $tenant)
    {
        $this->disableTenantScope();

        $tenantUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$tenantUser) {
            return redirect()->back()->with('error', "No user accounts found for tenant: {$tenant->name}");
        }

        $adminId = Auth::id();
        session(['impersonator_id' => $adminId]);

        Auth::login($tenantUser);

        AdminLog::log('impersonate_tenant', "Super Admin (ID: {$adminId}) started impersonation of Tenant: {$tenant->name} (User ID: {$tenantUser->id})");

        return redirect()->route('dashboard')->with('success', "Logged in as client: {$tenantUser->name}");
    }

    /**
     * Stop impersonating and log back in as the super admin.
     */
    public function stopImpersonate()
    {
        if (!session()->has('impersonator_id')) {
            abort(403, 'No active impersonation session found.');
        }

        $adminId = session('impersonator_id');
        
        $adminUser = User::withoutGlobalScope('tenant')
            ->where('is_super_admin', true)
            ->findOrFail($adminId);

        Auth::login($adminUser);
        session()->forget('impersonator_id');

        AdminLog::log('stop_impersonate_tenant', "Super Admin (ID: {$adminId}) ended impersonation");

        return redirect()->route('admin.dashboard')->with('success', 'Returned to Founder Admin Dashboard');
    }

    /**
     * Reconnect the WhatsApp client socket connection for a specific tenant.
     */
    public function reconnectTenant(Request $request): JsonResponse
    {
        $this->disableTenantScope();

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id'
        ]);

        $tenantId = $request->input('tenant_id');
        $tenant = Tenant::findOrFail($tenantId);

        // Temporarily bind target tenant ID for BotService context resolving
        app()->instance('tenant_id', $tenantId);

        $success = app(BotService::class)->reconnect();

        // Restore bypassed state
        $this->disableTenantScope();

        if ($success) {
            AdminLog::log('reconnect_tenant_socket', "WhatsApp socket reconnect triggered for tenant: {$tenant->name} (ID: {$tenantId})");
            return response()->json([
                'success' => true,
                'message' => "WhatsApp socket reconnect initiated for {$tenant->name}."
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to initiate WhatsApp socket reconnect for {$tenant->name}."
        ], 500);
    }

    /**
     * Manually provision a new tenant.
     */
    public function storeTenant(Request $request): JsonResponse
    {
        $this->disableTenantScope();

        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug|regex:/^[a-z0-9\-]+$/i',
            'account_type' => 'required|in:business,personal',
            'user_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'pre_complete_onboarding' => 'boolean',
        ]);

        $tenantName = $request->input('tenant_name');
        $slug = Str::slug($request->input('slug'));
        $accountType = $request->input('account_type');

        try {
            DB::beginTransaction();

            // 1. Create Tenant
            $tenant = Tenant::create([
                'name' => $tenantName,
                'slug' => $slug,
                'status' => 'active',
                'account_type' => $accountType,
            ]);

            // 2. Create User
            $user = User::create([
                'name' => $request->input('user_name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            $user->tenant_id = $tenant->id;
            $user->email_verified_at = now(); // Pre-verify email by default

            // 3. Pre-complete onboarding if requested
            if ($request->input('pre_complete_onboarding')) {
                $user->onboarded = true;
                
                // Seed templates and system prompt for the new tenant
                // Set app tenant ID context
                app()->instance('tenant_id', $tenant->id);

                if ($accountType === 'business') {
                    $tenant->business_category = 'Other / Custom Niche';
                    $tenant->save();
                    
                    $this->seedBusinessTemplates($tenant->id, 'Other / Custom Niche', $tenant->name);
                } else {
                    $tenant->business_category = 'Personal Brand';
                    $tenant->save();
                    
                    $this->seedPersonalTemplates($tenant->id, 'Personal Brand', $user->name);
                }

                $prompt = "You are a specialized AI assistant named 'Assistant' representing '{$tenant->name}' ({$accountType}).\n"
                    . "Tone: Polite, helpful, and concise.\n\n"
                    . "Instructions:\n"
                    . "- Keep all responses brief and concise (1 to 4 lines).\n"
                    . "- Never explicitly state you are an AI unless directly questioned.\n"
                    . "- Refer to provided memory to answer questions accurately.\n"
                    . "- If you do not know an answer, politely say you will check and follow up.";

                BotSetting::set('system_prompt', $prompt);
                BotSetting::set('bot_enabled', '1');
            } else {
                $user->onboarded = false;
            }

            $user->saveQuietly();

            DB::commit();

            // 4. Start WhatsApp Session asynchronously via BotService
            app(BotService::class)->startSession($tenant->id);

            AdminLog::log('provision_tenant', "Manually provisioned new tenant: {$tenant->name} (Slug: {$tenant->slug}) with admin user: {$user->email}");

            // Restore bypassed state
            $this->disableTenantScope();

            return response()->json([
                'success' => true,
                'message' => "Tenant '{$tenant->name}' and administrator account provisioned successfully!"
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->disableTenantScope();
            return response()->json([
                'success' => false,
                'message' => "Provisioning failed: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate seed template logic here to avoid coupling with OnboardingController's internal private methods
     */
    private function seedBusinessTemplates(int $tenantId, string $category, string $businessName): void
    {
        $templates = [
            'Other / Custom Niche' => [
                ['category' => 'services', 'key' => 'Bespoke Services', 'value' => "We provide local custom solutions matching your exact business needs."],
                ['category' => 'pricing', 'key' => 'Custom Estimates', 'value' => 'Quotes are calculated based on requirements. Drop us a message for a custom estimate.'],
                ['category' => 'faqs', 'key' => 'General Inquiries', 'value' => 'We reply to all inquiries on the same day. Please supply specifications in your text.'],
                ['category' => 'contact', 'key' => 'Reach Out', 'value' => 'Message this official WhatsApp for customer assistance.'],
            ]
        ];

        $list = $templates[$category];

        foreach ($list as $item) {
            \App\Models\BusinessMemory::create([
                'tenant_id' => $tenantId,
                'category'  => $item['category'],
                'key'       => $item['key'],
                'value'     => $item['value'],
                'active'    => true
            ]);
        }
    }

    private function seedPersonalTemplates(int $tenantId, string $role, string $name): void
    {
        $list = [
            ['category' => 'services', 'key' => 'Who I Am',        'value' => "{$name} is a {$role}. This AI assistant represents them personally."],
            ['category' => 'faqs',     'key' => 'Inquiries',        'value' => 'For collaboration, press, or business inquiries, please share your details and I will pass them along to ' . $name . '.'],
            ['category' => 'hours',    'key' => 'Response Times',   'value' => 'I am available 24/7. For time-sensitive matters, leave your contact details and you will hear back within 24 hours.'],
            ['category' => 'contact',  'key' => 'Booking & Calls',  'value' => 'Interested in working with ' . $name . '? Send a brief introduction and what you need, and we will connect you shortly.'],
        ];

        foreach ($list as $item) {
            \App\Models\BusinessMemory::create([
                'tenant_id' => $tenantId,
                'category'  => $item['category'],
                'key'       => $item['key'],
                'value'     => $item['value'],
                'active'    => true
            ]);
        }
    }
}
