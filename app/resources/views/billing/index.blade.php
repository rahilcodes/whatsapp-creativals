@extends('layouts.app')
@section('title', 'Subscription & Billing')
@section('subtitle', 'Manage your plans and integrations')

@section('content')
<div class="space-y-6" x-data="billingPortal()">
    
    {{-- Status Banner --}}
    <div class="card p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>Account Status:</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-slate-900 border border-slate-700"
                      :class="{
                          'text-brand-400 border-brand-500/30 bg-brand-500/5': '{{ $tenant->subscription_status }}' === 'active',
                          'text-amber-400 border-amber-500/30 bg-amber-500/5': '{{ $tenant->subscription_status }}' === 'trialing',
                          'text-red-400 border-red-500/30 bg-red-500/5': '{{ $tenant->subscription_status }}' === 'expired' || '{{ $tenant->subscription_status }}' === 'cancelled'
                      }">
                    {{ $tenant->subscription_status }}
                </span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                @if($tenant->subscription_status === 'trialing')
                    Your free trial is active. You have <strong class="text-slate-300">{{ $trialRemaining }}</strong> left.
                @elseif($tenant->subscription_status === 'active')
                    You are subscribed to the <strong class="text-slate-300 capitalize">{{ $tenant->plan }} Plan</strong>.
                @else
                    Your access has expired. Please select a plan below to resume autopilot services.
                @endif
            </p>
        </div>
        
        @if($tenant->has_support_addon)
        <div class="flex items-center gap-2 px-4 py-3 rounded-lg border border-indigo-500/30 bg-indigo-500/5">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
            </svg>
            <div class="text-xs">
                <span class="font-bold text-white block">Onboarding Support Active</span>
                <span class="text-slate-400">Dedicated Manager Support: <strong class="text-white">+91 7997001700</strong></span>
            </div>
        </div>
        @endif
    </div>

    {{-- Main Pricing Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
        
        {{-- Plan 1: Starter --}}
        <div class="card p-6 flex flex-col justify-between relative border border-slate-800"
             :class="{ 'border-brand-500/30 ring-1 ring-brand-500/10': '{{ $tenant->plan }}' === 'starter' && '{{ $tenant->subscription_status }}' === 'active' }">
            @if($tenant->plan === 'starter' && $tenant->subscription_status === 'active')
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] bg-brand-500/15 border border-brand-500/30 text-brand-400 font-bold uppercase tracking-wider">Current Plan</span>
            @endif
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest block">Starter Autopilot</span>
                <h3 class="text-lg font-bold text-white mt-1">Starter Plan</h3>
                <p class="text-xs text-slate-500 mt-2">Perfect for automated Q&A and general inquiries.</p>
                
                {{-- Price Display --}}
                <div class="mt-6 mb-6 flex items-baseline gap-2">
                    <span class="text-slate-500 line-through text-sm">₹2,499</span>
                    <span class="text-2xl font-black text-white">₹1,249</span>
                    <span class="text-xs text-slate-500">/ month</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">50% OFF</span>
                </div>
                
                <hr class="border-slate-800 my-4" />
                
                {{-- Features --}}
                <ul class="space-y-3 text-xs text-slate-400">
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span><strong>Unlimited AI messages</strong></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span>1 Connected WhatsApp number</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span>Full Business Memory (FAQ engine)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span>Live chat handoff & takeover control</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-slate-600 font-bold">✕</span>
                        <span class="text-slate-600 line-through">Google Sheets read/write integration</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8">
                <button @click="checkoutPlan('starter')"
                        :disabled="isProcessing"
                        class="w-full py-3 bg-slate-900 border border-slate-700 hover:bg-slate-800 hover:border-slate-600 text-white font-bold rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 disabled:opacity-50">
                    <span>Subscribe to Starter</span>
                </button>
            </div>
        </div>

        {{-- Plan 2: Automator --}}
        <div class="card p-6 flex flex-col justify-between relative border border-slate-800"
             :class="{ 'border-brand-500/40 ring-1 ring-brand-500/20': '{{ $tenant->plan }}' === 'automator' && '{{ $tenant->subscription_status }}' === 'active' }">
            @if($tenant->plan === 'automator' && $tenant->subscription_status === 'active')
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] bg-brand-500/15 border border-brand-500/30 text-brand-400 font-bold uppercase tracking-wider">Current Plan</span>
            @endif
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest block">Automator Pro</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-brand-500 text-slate-950 uppercase tracking-wider">Recommended</span>
                </div>
                <h3 class="text-lg font-bold text-white mt-1">Automator Plan</h3>
                <p class="text-xs text-slate-500 mt-2">For business looking to sync memory and push leads to Sheets.</p>
                
                {{-- Price Display --}}
                <div class="mt-6 mb-6 flex items-baseline gap-2">
                    <span class="text-slate-500 line-through text-sm">₹5,999</span>
                    <span class="text-2xl font-black text-white">₹2,999</span>
                    <span class="text-xs text-slate-500">/ month</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">50% OFF</span>
                </div>
                
                <hr class="border-slate-800 my-4" />
                
                {{-- Features --}}
                <ul class="space-y-3 text-xs text-slate-400">
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span><strong>Google Sheets Read/Write Integration</strong></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span>Sync Business memory templates from Sheets</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span>Auto-append booking leads to Sheets</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span><strong>Unlimited AI messages</strong></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-brand-400 font-bold">✓</span>
                        <span>Everything in Starter Plan included</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8">
                <button @click="checkoutPlan('automator')"
                        :disabled="isProcessing"
                        class="w-full py-3 bg-brand-500 hover:bg-brand-400 text-slate-950 font-bold rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 disabled:opacity-50">
                    <span>Subscribe to Automator</span>
                </button>
            </div>
        </div>
        
    </div>

    {{-- Support Add-on Section --}}
    @if(!$tenant->has_support_addon)
    <div class="card p-6 border border-slate-800 bg-slate-950/40 max-w-3xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5 flex-1">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase tracking-wider inline-block">Support Add-on</span>
            <h4 class="font-bold text-white text-sm">Need Help Setting Up? Onboarding Support</h4>
            <p class="text-xs text-slate-500 leading-relaxed max-w-xl">
                Let our dedicated team handle the onboarding setup for you. We will share sheets templates, configure your triggers, customize your prompt parameters, and test everything live to verify it functions perfectly.
            </p>
        </div>
        <div class="text-left md:text-right flex flex-col md:items-end justify-center min-w-[200px]">
            <span class="text-lg font-black text-white block">₹2,499 <span class="text-[10px] text-slate-500 font-normal">one-time</span></span>
            <button @click="purchaseSupportAddon()"
                    :disabled="isProcessing"
                    class="mt-3 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg text-xs transition-colors disabled:opacity-50">
                <span>Get Setup Support</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Alert Notification Toast --}}
    <div x-show="toast.show" 
         x-transition
         class="fixed bottom-5 right-5 z-50 p-4 rounded-xl border text-xs font-semibold shadow-2xl flex items-center gap-2.5 max-w-sm"
         :class="toast.type === 'success' ? 'bg-brand-500/10 border-brand-500/30 text-brand-400' : 'bg-red-500/10 border-red-500/30 text-red-400'"
         style="display: none;">
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    function billingPortal() {
        return {
            isProcessing: false,
            toast: {
                show: false,
                message: '',
                type: 'success'
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 4000);
            },

            async checkoutPlan(planName) {
                this.isProcessing = true;
                try {
                    let res = await fetch('{{ route('billing.subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ plan: planName })
                    });
                    
                    let data = await res.json();
                    
                    if (!data.success) {
                        this.showToast(data.message || 'Failed to start billing flow', 'error');
                        this.isProcessing = false;
                        return;
                    }

                    if (data.is_test) {
                        // Test Mode / local dev bypass
                        this.showToast('Test mode payment initiated...', 'success');
                        this.verifyTestPayment('subscription', planName, data.subscription_id);
                    } else {
                        // Launch live Razorpay subscriptions checkout overlay
                        const options = {
                            key: data.key_id,
                            subscription_id: data.subscription_id,
                            name: 'iChatUp',
                            description: planName.toUpperCase() + ' Plan Subscription',
                            handler: (response) => {
                                this.verifyLivePayment('subscription', planName, {
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_subscription_id: response.razorpay_subscription_id,
                                    razorpay_signature: response.razorpay_signature
                                });
                            },
                            modal: {
                                ondismiss: () => { this.isProcessing = false; }
                            },
                            theme: { color: '#10b981' }
                        };
                        const rzp = new Razorpay(options);
                        rzp.open();
                    }

                } catch (e) {
                    this.showToast('Network error starting checkout', 'error');
                    this.isProcessing = false;
                }
            },

            async purchaseSupportAddon() {
                this.isProcessing = true;
                try {
                    let res = await fetch('{{ route('billing.support-addon') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    let data = await res.json();
                    
                    if (!data.success) {
                        this.showToast(data.message || 'Failed to initiate onboarding addon', 'error');
                        this.isProcessing = false;
                        return;
                    }

                    if (data.is_test) {
                        this.showToast('Test mode payment initiated...', 'success');
                        this.verifyTestPayment('addon', '', '', data.order_id);
                    } else {
                        // Launch live Razorpay standard order checkout overlay
                        const options = {
                            key: data.key_id,
                            amount: data.amount,
                            currency: 'INR',
                            name: 'iChatUp Onboarding Support',
                            description: 'Setup Support & Linking assistance',
                            order_id: data.order_id,
                            handler: (response) => {
                                this.verifyLivePayment('addon', '', {
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                });
                            },
                            modal: {
                                ondismiss: () => { this.isProcessing = false; }
                            },
                            theme: { color: '#6366f1' }
                        };
                        const rzp = new Razorpay(options);
                        rzp.open();
                    }

                } catch (e) {
                    this.showToast('Network error starting checkout', 'error');
                    this.isProcessing = false;
                }
            },

            async verifyTestPayment(type, planName, subId = '', orderId = '') {
                try {
                    let res = await fetch('{{ route('billing.verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: type,
                            plan: planName,
                            razorpay_subscription_id: subId,
                            razorpay_order_id: orderId,
                            is_test: true
                        })
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.showToast('Subscription active! Loading dashboard...', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        this.showToast(data.message || 'Payment verification failed', 'error');
                        this.isProcessing = false;
                    }
                } catch (e) {
                    this.showToast('Verification request failed', 'error');
                    this.isProcessing = false;
                }
            },

            async verifyLivePayment(type, planName, payload) {
                try {
                    let res = await fetch('{{ route('billing.verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: type,
                            plan: planName,
                            ...payload
                        })
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.showToast('Payment verified successfully! Reloading...', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        this.showToast(data.message || 'Verification failed', 'error');
                        this.isProcessing = false;
                    }
                } catch (e) {
                    this.showToast('Verification connection error', 'error');
                    this.isProcessing = false;
                }
            }
        };
    }
</script>
@endpush
