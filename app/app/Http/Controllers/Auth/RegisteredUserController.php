<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Services\BotService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ]);

        // Create a new Tenant for this new user
        $tenant = Tenant::create([
            'name' => $request->name . "'s Workspace",
            'slug' => \Illuminate\Support\Str::slug($request->name) . '-' . uniqid(),
            'status' => 'active',
        ]);

        $otpCode = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        // Explicitly link tenant and save quietly to avoid HasTenant event overriding
        $user->tenant_id = $tenant->id;
        $user->saveQuietly();

        // Spin up the WhatsApp session for the new tenant
        app(BotService::class)->startSession($tenant->id);

        event(new Registered($user));

        // Send OTP Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otpCode));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Email Send Failed: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect(route('verification.notice'));
    }
}
