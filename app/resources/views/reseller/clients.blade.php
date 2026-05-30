@extends('layouts.reseller')
@section('title', 'Clients')
@section('subtitle', 'Manage all client accounts on your platform')

@section('content')
<div x-data="clientsPanel()" x-init="init()" class="space-y-6">

    {{-- ── Top Actions ────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div class="text-xs text-slate-500">
            <span class="text-white font-bold" x-text="clients.length"></span> clients |
            <span class="text-brand-400 font-bold" x-text="clients.filter(c => c.status === 'active').length"></span> active
        </div>
        <button @click="openCreate()"
                class="px-4 py-2 text-xs font-bold rounded-lg text-white transition-all hover:opacity-90 flex items-center gap-1.5 shadow-lg"
                style="background: {{ $reseller->primary_color }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Client
        </button>
    </div>

    {{-- ── Clients Table ───────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/40 text-slate-400 font-semibold text-left">
                    <tr>
                        <th class="px-6 py-3">Business</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Subscription</th>
                        <th class="px-6 py-3 text-center">Msgs Today</th>
                        <th class="px-6 py-3">Joined</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    <template x-for="client in clients" :key="client.id">
                        <tr class="hover:bg-slate-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-white" x-text="client.name"></div>
                                <div class="text-[10px] text-slate-500 font-mono" x-text="'slug: ' + client.slug"></div>
                            </td>
                            <td class="px-6 py-4 font-mono text-[11px]" x-text="client.email"></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider"
                                      :class="{
                                          'bg-brand-500/10 text-brand-400 border border-brand-500/20': client.subscription === 'active',
                                          'bg-amber-500/10 text-amber-400 border border-amber-500/20': client.subscription === 'trialing',
                                          'bg-red-500/10 text-red-400 border border-red-500/20': ['expired','cancelled','past_due'].includes(client.subscription)
                                      }"
                                      x-text="client.subscription"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-mono font-bold text-white" x-text="client.messages_today"></span>
                            </td>
                            <td class="px-6 py-4 text-slate-400" x-text="client.created_at"></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase"
                                      :class="client.status === 'active' ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'"
                                      x-text="client.status"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="toggleClient(client.id)"
                                        class="p-1 px-2 rounded border text-[11px] inline-flex items-center gap-1 transition-all"
                                        :class="client.status === 'active'
                                            ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
                                            : 'bg-brand-500/10 border-brand-500/30 text-brand-400 hover:bg-brand-500/20'"
                                        x-text="client.status === 'active' ? 'Suspend' : 'Activate'"></button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && clients.length === 0">
                        <td colspan="7" class="px-6 py-16 text-center text-slate-500">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-sm">No clients yet</p>
                                <p class="text-xs text-slate-600">Click "Add Client" to create your first client account</p>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="loading">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-t-transparent rounded-full animate-spin" style="border-color: {{ $reseller->primary_color }};border-top-color:transparent"></div>
                                <span class="text-xs">Loading clients...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Create Client Modal ─────────────────────────────────── --}}
    <div x-show="showCreate"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         style="display:none;" x-transition>
        <div @click.away="showCreate = false" class="card w-full max-w-md overflow-hidden shadow-2xl border border-slate-800">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-white">Add New Client</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Create a new account on your platform</p>
                </div>
                <button @click="showCreate = false" class="text-slate-400 hover:text-white p-1 rounded-lg bg-slate-800 border border-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div x-show="createError" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs" x-text="createError"></div>
                <div x-show="createSuccess" class="p-3 rounded-lg bg-brand-500/10 border border-brand-500/30 text-brand-400 text-xs" x-text="createSuccess"></div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Business Name *</label>
                    <input x-model="createForm.business_name" type="text" placeholder="e.g. Rahil Fashion Store" class="w-full text-xs px-3 py-2 rounded-lg" />
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Owner Name *</label>
                    <input x-model="createForm.owner_name" type="text" placeholder="e.g. Rahil Arora" class="w-full text-xs px-3 py-2 rounded-lg" />
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Email Address *</label>
                    <input x-model="createForm.email" type="email" placeholder="owner@business.com" class="w-full text-xs px-3 py-2 rounded-lg" />
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Password *</label>
                    <input x-model="createForm.password" type="password" placeholder="Min 8 characters" class="w-full text-xs px-3 py-2 rounded-lg" />
                </div>
            </div>
            <div class="p-6 border-t border-slate-800 bg-slate-900/30 flex items-center justify-between">
                <button @click="showCreate = false" class="px-4 py-2 text-slate-400 hover:text-white text-xs font-semibold">Cancel</button>
                <button @click="submitCreate()"
                        :disabled="submitting"
                        class="px-5 py-2 text-xs font-bold rounded-lg text-white transition-all hover:opacity-90 disabled:opacity-50 flex items-center gap-2"
                        style="background: {{ $reseller->primary_color }}">
                    <div x-show="submitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span x-text="submitting ? 'Creating...' : 'Create Client'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function clientsPanel() {
    return {
        clients: [], loading: true,
        showCreate: false, submitting: false,
        createError: '', createSuccess: '',
        createForm: { business_name: '', owner_name: '', email: '', password: '' },

        init() { this.loadClients(); },

        loadClients() {
            this.loading = true;
            fetch('/reseller-admin/clients/data', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => { this.clients = data.clients || []; this.loading = false; });
        },

        openCreate() {
            this.createError = ''; this.createSuccess = '';
            this.createForm = { business_name: '', owner_name: '', email: '', password: '' };
            this.showCreate = true;
        },

        submitCreate() {
            this.createError = ''; this.createSuccess = ''; this.submitting = true;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch('/reseller-admin/clients/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(this.createForm),
            }).then(r => r.json()).then(data => {
                this.submitting = false;
                if (data.success) {
                    this.createSuccess = data.message;
                    this.loadClients();
                    setTimeout(() => { this.showCreate = false; }, 2000);
                } else {
                    this.createError = data.message || 'Something went wrong.';
                }
            }).catch(() => { this.submitting = false; this.createError = 'Network error.'; });
        },

        toggleClient(id) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch(`/reseller-admin/clients/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    const c = this.clients.find(c => c.id === id);
                    if (c) c.status = data.status;
                }
            });
        },
    };
}
</script>
@endpush
