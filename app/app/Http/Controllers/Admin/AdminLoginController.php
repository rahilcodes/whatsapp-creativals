<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->is_super_admin) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }
        return view('admin.auth.login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            if ($user->is_super_admin) {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard');
            }
            
            // Log out standard user since they used admin portal
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            throw ValidationException::withMessages([
                'email' => 'This portal is restricted to system administrators.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
}
