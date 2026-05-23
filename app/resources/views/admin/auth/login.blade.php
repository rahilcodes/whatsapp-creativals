<x-admin-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-1">System Administration</h2>
        <p class="text-slate-500 text-sm">Sign in to control & monitor WhatsApp AI servers</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 p-3 rounded-xl text-sm text-indigo-400" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
        @csrf

        <!-- Email -->
        <div>
            <label for="email">Administrator Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@example.com" />
            @error('email') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            @error('password') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <!-- Remember Me -->
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0;">
            <input id="remember_me" type="checkbox" name="remember" style="width:auto;padding:0;" />
            <span style="font-size:0.8125rem;color:#64748b;">Keep admin session active</span>
        </label>

        <!-- Submit -->
        <button type="submit" class="btn-primary" style="margin-top:0.5rem;">
            Authenticate Administrator
        </button>
    </form>
</x-admin-guest-layout>
