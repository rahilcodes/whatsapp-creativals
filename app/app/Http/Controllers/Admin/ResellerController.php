<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Message;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Handles all reseller CRUD operations from the Super Admin panel.
 */
class ResellerController extends Controller
{
    public function __construct()
    {
        app()->forgetInstance('tenant_id');
    }

    /**
     * GET /admin/resellers
     * Renders the resellers management page.
     */
    public function index()
    {
        app()->forgetInstance('tenant_id');
        return view('admin.resellers');
    }

    /**
     * GET /admin/resellers/list (JSON)
     * Returns all resellers with stats for the data table.
     */
    public function list(): JsonResponse
    {
        app()->forgetInstance('tenant_id');

        $resellers = Reseller::withCount('tenants')->get()->map(function ($reseller) {
            // Count total messages across all clients of this reseller
            $totalMessages = Message::withoutGlobalScope('tenant')
                ->whereIn('tenant_id', $reseller->tenants()->pluck('id'))
                ->count();

            $messagesToday = Message::withoutGlobalScope('tenant')
                ->whereIn('tenant_id', $reseller->tenants()->pluck('id'))
                ->whereDate('created_at', today())
                ->count();

            return [
                'id'                   => $reseller->id,
                'name'                 => $reseller->name,
                'slug'                 => $reseller->slug,
                'domain'               => $reseller->domain,
                'status'               => $reseller->status,
                'support_email'        => $reseller->support_email,
                'contact_name'         => $reseller->contact_name,
                'primary_color'        => $reseller->primary_color,
                'license_type'         => $reseller->license_type,
                'license_fee'          => $reseller->license_fee,
                'license_expires_at'   => $reseller->license_expires_at?->format('Y-m-d'),
                'license_valid'        => $reseller->isLicenseValid(),
                'max_clients'          => $reseller->max_clients,
                'clients_count'        => $reseller->tenants_count,
                'slots_remaining'      => $reseller->remainingClientSlots(),
                'total_messages'       => $totalMessages,
                'messages_today'       => $messagesToday,
                'created_at'           => $reseller->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'success'   => true,
            'resellers' => $resellers,
        ]);
    }

    /**
     * POST /admin/resellers/create
     * Onboard a new reseller brand and create their admin user account.
     */
    public function store(Request $request): JsonResponse
    {
        app()->forgetInstance('tenant_id');

        $request->validate([
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:80|unique:resellers,slug|regex:/^[a-z0-9\-]+$/',
            'domain'          => 'required|string|max:255|unique:resellers,domain',
            'support_email'   => 'required|email|max:255',
            'contact_name'    => 'required|string|max:255',
            'admin_email'     => 'required|email|max:255|unique:users,email',
            'admin_name'      => 'required|string|max:255',
            'admin_password'  => 'required|string|min:8',
            'license_type'    => 'required|in:monthly,yearly,lifetime',
            'license_fee'     => 'required|integer|min:0',
            'license_expires_at' => 'nullable|date|after:today',
            'max_clients'     => 'required|integer|min:1|max:10000',
            'primary_color'   => 'nullable|string|max:20',
            'sidebar_color'   => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create the Reseller brand record
            $reseller = Reseller::create([
                'name'               => $request->name,
                'slug'               => Str::slug($request->slug),
                'domain'             => strtolower($request->domain),
                'support_email'      => $request->support_email,
                'contact_name'       => $request->contact_name,
                'license_type'       => $request->license_type,
                'license_fee'        => $request->license_fee,
                'license_expires_at' => $request->license_expires_at,
                'max_clients'        => $request->max_clients,
                'primary_color'      => $request->primary_color ?? '#10b981',
                'sidebar_color'      => $request->sidebar_color ?? '#080f1e',
                'status'             => 'active',
            ]);

            // 2. Create the Reseller's admin user account
            $adminUser = User::forceCreate([
                'name'              => $request->admin_name,
                'email'             => $request->admin_email,
                'password'          => Hash::make($request->admin_password),
                'role'              => 'reseller_admin',
                'reseller_id'       => $reseller->id,
                'email_verified_at' => now(),
                // No tenant_id — reseller admins are not client tenants
            ]);

            DB::commit();

            AdminLog::log('provision_reseller', "Onboarded new reseller: {$reseller->name} (domain: {$reseller->domain}, admin: {$adminUser->email})");

            return response()->json([
                'success' => true,
                'message' => "Reseller '{$reseller->name}' and admin account created successfully! They can now login at {$reseller->domain}",
                'reseller_id' => $reseller->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reseller: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /admin/resellers/{id}/toggle-status
     * Suspend or reactivate a reseller (affects ALL their clients).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        app()->forgetInstance('tenant_id');

        $reseller = Reseller::findOrFail($id);
        $newStatus = $reseller->status === 'suspended' ? 'active' : 'suspended';
        $reseller->status = $newStatus;
        $reseller->save();

        AdminLog::log('toggle_reseller_status', "Reseller '{$reseller->name}' status changed to: {$newStatus}");

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
            'message' => "Reseller '{$reseller->name}' is now {$newStatus}.",
        ]);
    }

    /**
     * POST /admin/resellers/{id}/update
     * Update reseller metadata like name, domain, license details, branding colors.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        app()->forgetInstance('tenant_id');

        $reseller = Reseller::findOrFail($id);

        $request->validate([
            'name'               => 'required|string|max:255',
            'domain'             => 'required|string|max:255|unique:resellers,domain,' . $id,
            'support_email'      => 'required|email|max:255',
            'license_expires_at' => 'nullable|date',
            'max_clients'        => 'required|integer|min:1',
            'primary_color'      => 'nullable|string|max:20',
            'sidebar_color'      => 'nullable|string|max:20',
        ]);

        $reseller->update($request->only([
            'name', 'domain', 'support_email', 'contact_name',
            'license_type', 'license_fee', 'license_expires_at',
            'max_clients', 'primary_color', 'sidebar_color',
        ]));

        AdminLog::log('update_reseller', "Updated reseller: {$reseller->name} (ID: {$id})");

        return response()->json([
            'success' => true,
            'message' => "Reseller '{$reseller->name}' updated successfully.",
        ]);
    }
}
