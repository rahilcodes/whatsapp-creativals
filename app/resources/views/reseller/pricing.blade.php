@extends('layouts.reseller')
@section('title', 'Plans & Pricing')
@section('subtitle', 'Configure packages and subscription settings for your clients')

@section('content')
<div x-data="pricingPanel()" class="space-y-6 max-w-2xl">

    {{-- Flash messages --}}
    <div x-show="success" class="p-3 rounded-lg bg-brand-500/10 border border-brand-500/30 text-brand-400 text-xs" x-text="success" style="display: none;"></div>
    <div x-show="error" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs" x-text="error" style="display: none;"></div>

    {{-- ── Billing & Plans Settings ────────────────────────────────── --}}
    <div class="card p-6 space-y-5">
        <h3 class="text-sm font-semibold text-white">Billing & Subscription Settings</h3>
        
        <div class="space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" x-model="showBilling" class="rounded bg-slate-900 border-slate-700 text-brand-500 focus:ring-brand-500 w-4 h-4" />
                <span class="text-xs text-slate-300 font-medium">Show Subscription & Billing dashboard to your clients</span>
            </label>
            
            <div x-show="showBilling" class="space-y-4 pt-4 border-t border-slate-800">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider block">Billing Currency</label>
                        <select x-model="billingCurrency" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none">
                            <option value="INR">INR (₹)</option>
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    {{-- Starter Plan Customization --}}
                    <div class="space-y-3 p-4 rounded-xl border border-slate-800 bg-slate-950/20">
                        <h4 class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-2">Starter Plan</h4>
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase text-slate-500 font-semibold tracking-wider block">Custom Plan Name</label>
                            <input x-model="planStarterName" type="text" placeholder="Starter" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase text-slate-500 font-semibold tracking-wider block">Monthly Price (e.g. 1249)</label>
                            <input x-model="planStarterPrice" type="number" placeholder="1249" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none" />
                        </div>
                    </div>

                    {{-- Automator Plan Customization --}}
                    <div class="space-y-3 p-4 rounded-xl border border-slate-800 bg-slate-950/20">
                        <h4 class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-2">Automator Plan</h4>
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase text-slate-500 font-semibold tracking-wider block">Custom Plan Name</label>
                            <input x-model="planAutomatorName" type="text" placeholder="Automator" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase text-slate-500 font-semibold tracking-wider block">Monthly Price (e.g. 2999)</label>
                            <input x-model="planAutomatorPrice" type="number" placeholder="2999" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none" />
                        </div>
                    </div>
                </div>

                {{-- Onboarding Support Addon Customization --}}
                <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/20 space-y-3">
                    <h4 class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-2">Onboarding Support Addon</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase text-slate-500 font-semibold tracking-wider block">Custom Addon Name</label>
                            <input x-model="planSupportName" type="text" placeholder="Onboarding Support" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase text-slate-500 font-semibold tracking-wider block">One-time Price (e.g. 2499)</label>
                            <input x-model="planSupportPrice" type="number" placeholder="2499" class="w-full text-xs px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-200 focus:outline-none" />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Save Button ─────────────────────────────────────────── --}}
    <div class="flex justify-end">
        <button @click="savePricing()"
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
function pricingPanel() {
    return {
        showBilling: {{ $reseller->show_billing ? 'true' : 'false' }},
        billingCurrency: '{{ $reseller->billing_currency ?? "INR" }}',
        planStarterName: '{{ $reseller->plan_starter_name ?? "" }}',
        planStarterPrice: '{{ $reseller->plan_starter_price !== null ? $reseller->plan_starter_price / 100 : "" }}',
        planAutomatorName: '{{ $reseller->plan_automator_name ?? "" }}',
        planAutomatorPrice: '{{ $reseller->plan_automator_price !== null ? $reseller->plan_automator_price / 100 : "" }}',
        planSupportName: '{{ $reseller->plan_support_name ?? "" }}',
        planSupportPrice: '{{ $reseller->plan_support_price !== null ? $reseller->plan_support_price / 100 : "" }}',
        saving: false,
        success: '',
        error: '',

        savePricing() {
            this.saving = true; this.success = ''; this.error = '';
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const form  = new FormData();
            form.append('_token', token);
            form.append('show_billing', this.showBilling ? 1 : 0);
            form.append('billing_currency', this.billingCurrency || 'INR');
            form.append('plan_starter_name', this.planStarterName || '');
            form.append('plan_starter_price', this.planStarterPrice !== null && this.planStarterPrice !== undefined ? this.planStarterPrice : '');
            form.append('plan_automator_name', this.planAutomatorName || '');
            form.append('plan_automator_price', this.planAutomatorPrice !== null && this.planAutomatorPrice !== undefined ? this.planAutomatorPrice : '');
            form.append('plan_support_name', this.planSupportName || '');
            form.append('plan_support_price', this.planSupportPrice !== null && this.planSupportPrice !== undefined ? this.planSupportPrice : '');

            fetch('/reseller-admin/pricing', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                body: form,
            }).then(r => r.json()).then(data => {
                this.saving = false;
                if (data.success) {
                    this.success = data.message;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.error = data.message || 'Save failed.';
                }
            }).catch(() => { this.saving = false; this.error = 'Network error.'; });
        },
    };
}
</script>
@endpush
