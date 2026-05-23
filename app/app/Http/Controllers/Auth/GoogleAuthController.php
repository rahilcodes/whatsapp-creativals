<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect('/login')->withErrors(['email' => 'Failed to login with Google: ' . $e->getMessage()]);
        }

        // IMPORTANT: Use withoutGlobalScopes() here to bypass the HasTenant scope.
        // During OAuth callback, app('tenant_id') falls back to 1 (guest), so a
        // scoped query would miss users who belong to tenant 2, 3, etc.
        // We search globally by google_id OR email across ALL tenants.
        $user = User::withoutGlobalScopes()
                    ->where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if ($user) {
            // Update google_id or verify email if missing
            $needsSave = false;
            if (! $user->google_id) {
                $user->google_id = $googleUser->id;
                $needsSave = true;
            }
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
                $needsSave = true;
            }
            if ($needsSave) {
                $user->saveQuietly();
            }
        } else {
            // New user — create a fresh Tenant workspace for them
            $tenant = Tenant::create([
                'name'   => $googleUser->name . "'s Workspace",
                'slug'   => \Illuminate\Support\Str::slug($googleUser->name) . '-' . uniqid(),
                'status' => 'active',
            ]);

            // Create the User record. We use a raw DB insert to fully bypass
            // the HasTenant scope and any auto-filling of tenant_id = 1.
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'name'              => $googleUser->name,
                'email'             => $googleUser->email,
                'google_id'         => $googleUser->id,
                'password'          => null,
                'tenant_id'         => $tenant->id,
                'email_verified_at' => now(), // Auto verify Google user
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Retrieve the newly inserted user (without scope)
            $user = User::withoutGlobalScopes()
                        ->where('email', $googleUser->email)
                        ->first();

            // Spin up the WhatsApp session for the new tenant
            app(BotService::class)->startSession($tenant->id);
        }

        // Log the user in
        Auth::login($user, true);

        if ($user->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        // Redirect to dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }
}
