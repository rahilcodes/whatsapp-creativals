<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Message;
use App\Models\BotSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Reseller Admin Panel — all actions a reseller admin can take
 * to manage their own brand, clients, and settings.
 */
class ResellerAdminController extends Controller
{
    private function reseller(): \App\Models\Reseller
    {
        // BrandResolutionMiddleware already validated this is bound before we reach here
        // (via ResellerAdminMiddleware), but we add a safety fallback just in case.
        if (!app()->bound('active_reseller')) {
            abort(403, 'No active reseller resolved for this domain.');
        }
        return app('active_reseller');
    }

    // ── Dashboard ──────────────────────────────────────────────

    /**
     * GET /reseller-admin
     * Main reseller admin dashboard.
     */
    public function dashboard()
    {
        $reseller = $this->reseller();

        $clientCount   = $reseller->tenants()->count();
        $activeClients = $reseller->tenants()->where('status', 'active')->count();
        $tenantIds     = $reseller->tenants()->pluck('id');

        $messagesToday = Message::withoutGlobalScope('tenant')
            ->whereIn('tenant_id', $tenantIds)
            ->whereDate('created_at', today())
            ->count();

        $totalMessages = Message::withoutGlobalScope('tenant')
            ->whereIn('tenant_id', $tenantIds)
            ->count();

        return view('reseller.dashboard', compact(
            'reseller', 'clientCount', 'activeClients', 'messagesToday', 'totalMessages'
        ));
    }

    // ── Client Management ──────────────────────────────────────

    /**
     * GET /reseller-admin/clients
     * List all clients belonging to this reseller.
     */
    public function clients()
    {
        $reseller = $this->reseller();
        return view('reseller.clients', compact('reseller'));
    }

    /**
     * GET /reseller-admin/clients/data (JSON)
     */
    public function clientsData(): JsonResponse
    {
        $reseller  = $this->reseller();
        $tenantIds = $reseller->tenants()->pluck('id');

        $tenants = Tenant::whereIn('id', $tenantIds)->get()->map(function ($tenant) {
            $messagesToday = Message::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->whereDate('created_at', today())
                ->count();

            $user = User::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->first();

            return [
                'id'              => $tenant->id,
                'name'            => $tenant->name,
                'slug'            => $tenant->slug,
                'status'          => $tenant->status,
                'email'           => $user?->email ?? 'N/A',
                'subscription'    => $tenant->subscription_status,
                'messages_today'  => $messagesToday,
                'created_at'      => $tenant->created_at->format('M d, Y'),
            ];
        });

        return response()->json(['success' => true, 'clients' => $tenants]);
    }

    /**
     * POST /reseller-admin/clients/create
     * Create a new client account under this reseller.
     */
    public function storeClient(Request $request): JsonResponse
    {
        $reseller = $this->reseller();

        // Check slot availability
        if ($reseller->remainingClientSlots() <= 0) {
            return response()->json([
                'success' => false,
                'message' => "You have reached the maximum client limit ({$reseller->max_clients}). Please contact support to increase your quota.",
            ], 422);
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8',
            'owner_name'    => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $slug = Str::slug($request->business_name) . '-' . Str::random(5);

            $tenant = Tenant::create([
                'name'        => $request->business_name,
                'slug'        => $slug,
                'status'      => 'active',
                'reseller_id' => $reseller->id,
                // Give trial access
                'subscription_status' => 'trialing',
                'trial_ends_at'       => now()->addDays(14),
            ]);

            $user = User::forceCreate([
                'name'              => $request->owner_name,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'tenant_id'         => $tenant->id,
                'reseller_id'       => $reseller->id,
                'role'              => 'client',
                'onboarded'         => false,
                'email_verified_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Client '{$tenant->name}' created successfully! They can now log in at your portal.",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /reseller-admin/clients/{id}/toggle
     * Suspend or reactivate a client account.
     */
    public function toggleClient(int $id): JsonResponse
    {
        $reseller = $this->reseller();
        $tenant   = Tenant::where('id', $id)->where('reseller_id', $reseller->id)->firstOrFail();

        $newStatus    = $tenant->status === 'suspended' ? 'active' : 'suspended';
        $tenant->status = $newStatus;
        $tenant->save();

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
            'message' => "Client '{$tenant->name}' is now {$newStatus}.",
        ]);
    }

    // ── Branding Settings ──────────────────────────────────────

    /**
     * GET /reseller-admin/branding
     * Show branding configuration form.
     */
    public function branding()
    {
        $reseller = $this->reseller();
        return view('reseller.branding', compact('reseller'));
    }

    /**
     * POST /reseller-admin/branding
     * Save branding settings: colors, logo, favicon.
     */
    public function updateBranding(Request $request): JsonResponse
    {
        $reseller = $this->reseller();

        $request->validate([
            'primary_color'  => 'nullable|string|max:20',
            'sidebar_color'  => 'nullable|string|max:20',
            'logo'           => 'nullable|image|max:2048',
            'favicon'        => 'nullable|image|max:512',
        ]);

        $updates = [];

        if ($request->primary_color) $updates['primary_color'] = $request->primary_color;
        if ($request->sidebar_color) $updates['sidebar_color'] = $request->sidebar_color;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("resellers/{$reseller->slug}/branding", 'public');
            $updates['logo_path'] = $path;
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store("resellers/{$reseller->slug}/branding", 'public');
            $updates['favicon_path'] = $path;
        }

        $reseller->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Branding settings updated successfully.',
        ]);
    }

    // ── Gateway Settings ───────────────────────────────────────

    /**
     * GET /reseller-admin/gateway
     * Show gateway & SMTP configuration form.
     */
    public function gateway()
    {
        $reseller = $this->reseller();
        return view('reseller.gateway', compact('reseller'));
    }

    /**
     * POST /reseller-admin/gateway
     * Save Stripe and SMTP settings.
     */
    public function updateGateway(Request $request): JsonResponse
    {
        $reseller = $this->reseller();

        $request->validate([
            'stripe_key'        => 'nullable|string|max:255',
            'stripe_secret'     => 'nullable|string|max:255',
            'smtp_host'         => 'nullable|string|max:255',
            'smtp_port'         => 'nullable|integer',
            'smtp_username'     => 'nullable|string|max:255',
            'smtp_password'     => 'nullable|string|max:255',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name'    => 'nullable|string|max:255',
        ]);

        $reseller->update($request->only([
            'stripe_key', 'stripe_secret',
            'smtp_host', 'smtp_port', 'smtp_username',
            'smtp_password', 'smtp_from_address', 'smtp_from_name',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Payment and mail settings saved successfully.',
        ]);
    }
}
