<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Block iChatUp master super-admins from normal login form
        if ($user && $user->is_super_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Administrators must log in via the dedicated Admin Portal.',
            ]);
        }

        $request->session()->regenerate();

        // Reseller admins → go to their panel (relative path stays on current domain)
        if ($user && $user->role === 'reseller_admin') {
            return redirect('/reseller-admin');
        }

        // Start bot session for tenant users
        if ($user && $user->tenant_id) {
            app(\App\Services\BotService::class)->startSession($user->tenant_id);
        }

        // Failsafe: never redirect back to an API URL
        $intended = redirect()->getIntendedUrl();
        if ($intended && (str_contains($intended, '/api/') || str_contains($intended, '/api-'))) {
            $request->session()->forget('url.intended');
            return redirect('/dashboard'); // relative — stays on current domain
        }

        // Use relative path so browser stays on panel.besurebot.com (not ichatup.com)
        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
