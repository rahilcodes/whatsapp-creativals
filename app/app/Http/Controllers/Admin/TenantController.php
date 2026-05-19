<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of all tenants for the super admin.
     */
    public function index()
    {
        $tenants = Tenant::with(['users', 'whatsappStatus'])
            ->withCount('messages')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.tenants', compact('tenants'));
    }
}
