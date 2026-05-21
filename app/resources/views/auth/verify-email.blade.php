<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-1">Verify your email</h2>
        <p class="text-slate-500 text-sm">We've sent a 6-digit verification code to <span class="text-emerald-400 font-medium">{{ Auth::user()->email }}</span>.</p>
    </div>

    <!-- Session Status -->
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-xl border border-emerald-500/25 bg-emerald-500/10 text-emerald-400 text-sm font-medium">
            A new verification code has been sent to your email.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.verify-otp') }}" class="space-y-6">
        @csrf

        <!-- OTP Code -->
        <div>
            <label for="otp">Verification Code</label>
            <input id="otp" type="text" name="otp" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="off" placeholder="123456" 
                   style="text-align:center;letter-spacing:0.5em;font-size:1.5rem;font-weight:700;padding:0.5rem 1rem !important;" />
            @error('otp') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary">
            Verify & Create Workspace →
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-white/5 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" style="color:#10b981;font-size:0.8125rem;font-weight:500;background:none;border:none;cursor:pointer;padding:0;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                Resend Code
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="color:#64748b;font-size:0.8125rem;font-weight:500;background:none;border:none;cursor:pointer;padding:0;transition:color 0.2s;" onmouseover="this.style.color='#cbd5e1'" onmouseout="this.style.color='#64748b'">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
