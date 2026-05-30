@php
    $reseller = app()->bound('active_reseller') ? app('active_reseller') : null;
    $appName  = $reseller?->name ?? 'iChatUp';
@endphp

<x-guest-layout>
<div class="mb-8">
    <h2 class="text-2xl font-bold text-white mb-1">Welcome back</h2>
    <p class="text-slate-500 text-sm">Sign in to your {{ $appName }} workspace</p>
</div>

<!-- Session Status -->
@if (session('status'))
    <div class="mb-4 p-3 rounded-xl text-sm text-emerald-400" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <!-- Email -->
    <div>
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com" />
        @error('email') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <!-- Password -->
    <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.375rem;">
            <label for="password" style="margin-bottom:0;">Password</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size:0.75rem;color:var(--brand, #10b981);text-decoration:none;">Forgot password?</a>
            @endif
        </div>
        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
        @error('password') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <!-- Remember Me -->
    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0;">
        <input id="remember_me" type="checkbox" name="remember" style="width:auto;padding:0;" />
        <span style="font-size:0.8125rem;color:#64748b;">Keep me signed in</span>
    </label>

    <!-- Submit -->
    <button type="submit" class="btn-primary" style="margin-top:0.5rem;">
        Sign In to Dashboard
    </button>

    {{-- Google OAuth and self-register are only shown on the main iChatUp domain.
         On reseller domains (panel.besurebot.com etc.) these are intentionally hidden:
         - Google OAuth is tied to ichatup.com's OAuth credentials
         - Client registration is managed by the reseller admin, not self-service --}}
    @if(!$reseller)
        <!-- Google -->
        <div style="display:flex;align-items:center;gap:0.75rem;margin:0.5rem 0;">
            <div style="flex:1;height:1px;background:rgba(255,255,255,0.08);"></div>
            <span style="font-size:0.75rem;color:#475569;">or</span>
            <div style="flex:1;height:1px;background:rgba(255,255,255,0.08);"></div>
        </div>

        <a href="{{ route('google.redirect') }}"
           style="display:flex;align-items:center;justify-content:center;gap:0.625rem;padding:0.75rem 1.5rem;border-radius:0.75rem;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.03);color:#cbd5e1;text-decoration:none;font-size:0.875rem;font-weight:500;transition:all 0.2s;"
           onmouseover="this.style.borderColor='rgba(255,255,255,0.2)'"
           onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
            <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Continue with Google
        </a>

        <p style="text-align:center;margin-top:1.5rem;font-size:0.8125rem;color:#475569;">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:#10b981;text-decoration:none;font-weight:500;"> Create one free →</a>
        </p>
    @endif
</form>
</x-guest-layout>
