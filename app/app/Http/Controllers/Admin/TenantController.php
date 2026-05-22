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
        return redirect()->route('admin.dashboard');
    }
}
