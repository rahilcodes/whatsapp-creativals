@extends('layouts.reseller')
@section('title', 'Payments & Mail')
@section('subtitle', 'Configure Stripe payments and email delivery settings')

@section('content')
<div x-data="gatewayPanel()" class="space-y-6 max-w-2xl">

    <div x-show="success" class="p-3 rounded-lg bg-brand-500/10 border border-brand-500/30 text-brand-400 text-xs" x-text="success"></div>
    <div x-show="error"   class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs" x-text="error"></div>

    {{-- ── Stripe Payment Gateway ──────────────────────────────── --}}
    <div class="card p-6 space-y-4">
        <div class="flex items-center gap-3 mb-1">
            <div class="p-2 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Stripe Payment Gateway</h3>
                <p class="text-[11px] text-slate-500">Your clients pay through your Stripe account directly</p>
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Stripe Publishable Key</label>
            <input x-model="form.stripe_key" type="text" placeholder="pk_live_..." class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Stripe Secret Key</label>
            <input x-model="form.stripe_secret" type="password" placeholder="sk_live_..." class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
            <p class="text-[10px] text-slate-600">⚠ Never share your secret key. It is stored encrypted.</p>
        </div>
        <div class="p-3 rounded-lg bg-indigo-500/5 border border-indigo-500/20 text-[11px] text-indigo-300">
            💡 When configured, all subscription payments from your clients will go directly to your Stripe account — not ours.
        </div>
    </div>

    {{-- ── SMTP Mail Configuration ─────────────────────────────── --}}
    <div class="card p-6 space-y-4">
        <div class="flex items-center gap-3 mb-1">
            <div class="p-2 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Email (SMTP) Configuration</h3>
                <p class="text-[11px] text-slate-500">Emails to your clients send from your domain</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">SMTP Host</label>
                <input x-model="form.smtp_host" type="text" placeholder="smtp.gmail.com" class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
            </div>
            <div class="space-y-1">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">SMTP Port</label>
                <input x-model="form.smtp_port" type="number" placeholder="587" class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
            </div>
            <div class="space-y-1">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">SMTP Username</label>
                <input x-model="form.smtp_username" type="text" placeholder="you@besurebot.com" class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
            </div>
            <div class="space-y-1">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">SMTP Password</label>
                <input x-model="form.smtp_password" type="password" placeholder="App password" class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
            </div>
            <div class="space-y-1">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">From Email</label>
                <input x-model="form.smtp_from_address" type="email" placeholder="support@besurebot.com" class="w-full text-xs px-3 py-2 rounded-lg" />
            </div>
            <div class="space-y-1">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">From Name</label>
                <input x-model="form.smtp_from_name" type="text" placeholder="BeSureBot Support" class="w-full text-xs px-3 py-2 rounded-lg" />
            </div>
        </div>

        <div class="p-3 rounded-lg bg-sky-500/5 border border-sky-500/20 text-[11px] text-sky-300">
            💡 All OTP emails, subscription notifications, and alerts will arrive from your configured sender address.
        </div>
    </div>

    <div class="flex justify-end">
        <button @click="saveGateway()"
                :disabled="saving"
                class="px-6 py-2.5 text-sm font-bold rounded-lg text-white transition-all hover:opacity-90 disabled:opacity-50 flex items-center gap-2 shadow-lg"
                style="background: {{ $reseller->primary_color }}">
            <div x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span x-text="saving ? 'Saving...' : 'Save Settings'"></span>
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
function gatewayPanel() {
    return {
        saving: false, success: '', error: '',
        form: {
            stripe_key:        '{{ $reseller->stripe_key ?? "" }}',
            stripe_secret:     '',
            smtp_host:         '{{ $reseller->smtp_host ?? "" }}',
            smtp_port:         '{{ $reseller->smtp_port ?? 587 }}',
            smtp_username:     '{{ $reseller->smtp_username ?? "" }}',
            smtp_password:     '',
            smtp_from_address: '{{ $reseller->smtp_from_address ?? "" }}',
            smtp_from_name:    '{{ $reseller->smtp_from_name ?? "" }}',
        },

        saveGateway() {
            this.saving = true; this.success = ''; this.error = '';
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch('/reseller-admin/gateway', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(this.form),
            }).then(r => r.json()).then(data => {
                this.saving = false;
                if (data.success) { this.success = data.message; }
                else              { this.error   = data.message || 'Failed to save.'; }
            }).catch(() => { this.saving = false; this.error = 'Network error.'; });
        },
    };
}
</script>
@endpush
