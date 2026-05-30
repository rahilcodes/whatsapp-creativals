@extends('layouts.app')
@section('title', 'Reseller Hub')
@section('subtitle', 'Onboard and manage whitelabel partners')

@section('content')
<div x-data="resellerHub()" x-init="init()" class="space-y-6">

    {{-- ── Header Stats ─────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Total Resellers</span>
                <div class="text-3xl font-bold text-white mt-1" x-text="resellers.length">0</div>
                <div class="text-[11px] text-slate-500 mt-1">Whitelabel partners onboarded</div>
            </div>
            <div class="p-3.5 rounded-xl bg-violet-500/10 text-violet-400 border border-violet-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Active Partners</span>
                <div class="text-3xl font-bold text-white mt-1" x-text="resellers.filter(r => r.status === 'active').length">0</div>
                <div class="text-[11px] text-slate-500 mt-1">Currently serving clients</div>
            </div>
            <div class="p-3.5 rounded-xl bg-brand-500/10 text-brand-400 border border-brand-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Total Clients</span>
                <div class="text-3xl font-bold text-white mt-1" x-text="resellers.reduce((sum, r) => sum + r.clients_count, 0)">0</div>
                <div class="text-[11px] text-slate-500 mt-1">End users across all resellers</div>
            </div>
            <div class="p-3.5 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Resellers Table ───────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-white">Reseller Partners</h3>
                <p class="text-xs text-slate-500">Manage whitelabel brands and their portal domains</p>
            </div>
            <button @click="openOnboardWizard()"
                    class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white font-bold rounded-lg transition-all text-xs flex items-center gap-1.5 shadow-md shadow-violet-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Onboard New Reseller</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/40 text-slate-400 font-semibold text-left">
                    <tr>
                        <th class="px-6 py-3">Brand Details</th>
                        <th class="px-6 py-3">Domain</th>
                        <th class="px-6 py-3 text-center">Clients</th>
                        <th class="px-6 py-3 text-center">Msgs Today</th>
                        <th class="px-6 py-3">License</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    <template x-for="reseller in resellers" :key="reseller.id">
                        <tr class="hover:bg-slate-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Brand Color Badge --}}
                                    <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs font-bold text-white"
                                         :style="'background:' + reseller.primary_color + '22; border: 1px solid ' + reseller.primary_color + '44;color:' + reseller.primary_color">
                                        <span x-text="reseller.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-white" x-text="reseller.name"></div>
                                        <div class="text-[10px] text-slate-500" x-text="'slug: ' + reseller.slug"></div>
                                        <div class="text-[10px] text-slate-500" x-text="reseller.contact_name"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a :href="'https://' + reseller.domain" target="_blank"
                                   class="font-mono text-brand-400 hover:text-brand-300 transition-colors underline-offset-2 hover:underline text-[11px]"
                                   x-text="reseller.domain"></a>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-mono font-bold text-white" x-text="reseller.clients_count"></span>
                                <span class="text-slate-600 text-[10px]" x-text="' / ' + reseller.max_clients"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono text-[10px]" x-text="reseller.messages_today"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[11px]">
                                    <span class="capitalize font-medium" 
                                          :class="reseller.license_valid ? 'text-brand-400' : 'text-red-400'"
                                          x-text="reseller.license_type"></span>
                                    <div class="text-slate-600 font-mono" x-text="'₹' + reseller.license_fee + '/mo'"></div>
                                    <div x-show="reseller.license_expires_at" class="text-[10px] text-slate-600" x-text="'Expires: ' + reseller.license_expires_at"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider"
                                      :class="reseller.status === 'active' ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'"
                                      x-text="reseller.status"></span>
                                <div x-show="!reseller.license_valid" class="text-[9px] text-amber-400 mt-0.5">⚠ License Expired</div>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5">
                                {{-- View Details --}}
                                <button @click="viewReseller(reseller)"
                                        class="p-1 px-2 rounded bg-slate-800 border border-slate-700 hover:border-slate-600 text-[11px] text-slate-300 inline-flex items-center gap-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Details
                                </button>
                                {{-- Suspend/Activate --}}
                                <button @click="toggleStatus(reseller.id)"
                                        class="p-1 px-2 rounded border text-[11px] inline-flex items-center gap-1 transition-all"
                                        :class="reseller.status === 'active'
                                            ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
                                            : 'bg-brand-500/10 border-brand-500/30 text-brand-400 hover:bg-brand-500/20'">
                                    <span x-text="reseller.status === 'active' ? 'Suspend' : 'Activate'"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && resellers.length === 0">
                        <td colspan="7" class="px-6 py-16 text-center text-slate-500">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                <p class="text-sm">No resellers onboarded yet</p>
                                <p class="text-xs text-slate-600">Click "Onboard New Reseller" to add your first whitelabel partner</p>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="loading">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs">Loading reseller data...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Reseller Details Modal ────────────────────────────── --}}
    <div x-show="showModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         style="display: none;" x-transition>
        <div @click.away="showModal = false" class="card w-full max-w-lg overflow-hidden shadow-2xl border border-slate-800 max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-sm"
                         :style="'background:' + (selectedReseller?.primary_color ?? '#10b981') + '22; border: 1px solid ' + (selectedReseller?.primary_color ?? '#10b981') + '44; color:' + (selectedReseller?.primary_color ?? '#10b981')">
                        <span x-text="selectedReseller?.name?.charAt(0)?.toUpperCase()"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm" x-text="selectedReseller?.name"></h3>
                        <p class="text-[11px] text-slate-500" x-text="selectedReseller?.domain"></p>
                    </div>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg bg-slate-800 border border-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-300">
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase tracking-wider">Clients Used</span>
                        <div class="mt-1 font-bold text-white text-lg" x-text="(selectedReseller?.clients_count ?? 0) + ' / ' + (selectedReseller?.max_clients ?? 0)"></div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase tracking-wider">License</span>
                        <div class="mt-1 font-bold text-white capitalize" x-text="selectedReseller?.license_type"></div>
                        <div class="text-[10px] text-slate-500" x-text="selectedReseller?.license_valid ? 'Valid' : 'EXPIRED'"></div>
                    </div>
                </div>
                <div class="divide-y divide-slate-800 border-y border-slate-800 text-xs">
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">Contact</span><span class="font-medium text-white" x-text="selectedReseller?.contact_name"></span></div>
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">Support Email</span><span class="font-mono text-slate-300" x-text="selectedReseller?.support_email"></span></div>
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">License Fee</span><span class="text-white font-bold" x-text="'₹' + (selectedReseller?.license_fee ?? 0) + '/mo'"></span></div>
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">Messages Today</span><span class="font-mono text-slate-300" x-text="selectedReseller?.messages_today"></span></div>
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">Total Messages</span><span class="font-mono text-slate-300" x-text="selectedReseller?.total_messages"></span></div>
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">Onboarded</span><span class="text-slate-300" x-text="selectedReseller?.created_at"></span></div>
                    <div class="py-2.5 flex justify-between"><span class="text-slate-400">Expires</span><span class="text-slate-300" x-text="selectedReseller?.license_expires_at ?? 'Lifetime'"></span></div>
                </div>
                <div class="p-3 rounded-lg border border-slate-800 bg-slate-900/40">
                    <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider mb-2">Brand Color</p>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full border border-slate-700" :style="'background:' + selectedReseller?.primary_color"></div>
                        <span class="font-mono text-slate-300" x-text="selectedReseller?.primary_color"></span>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-slate-800 bg-slate-900/30 flex justify-between">
                <button @click="toggleStatus(selectedReseller.id); showModal = false;"
                        class="px-4 py-2 rounded-lg border text-xs font-semibold transition-all"
                        :class="selectedReseller?.status === 'active'
                            ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
                            : 'bg-brand-500/10 border-brand-500/30 text-brand-400 hover:bg-brand-500/20'"
                        x-text="selectedReseller?.status === 'active' ? 'Suspend Partner' : 'Reactivate Partner'">
                </button>
                <button @click="showModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white rounded-lg text-xs font-semibold transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ── Onboard Wizard Modal ──────────────────────────────── --}}
    <div x-show="showWizard"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         style="display: none;" x-transition>
        <div @click.away="showWizard = false" class="card w-full max-w-2xl overflow-hidden shadow-2xl border border-slate-800 max-h-[95vh] flex flex-col">
            <div class="p-6 border-b border-slate-800">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-violet-500/10 text-violet-400 border border-violet-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    Onboard New Reseller / Whitelabel Partner
                </h3>
                <p class="text-xs text-slate-500 mt-1">Create a new reseller brand and their admin portal login.</p>
            </div>
            <div class="p-6 overflow-y-auto space-y-5">
                {{-- Flash error --}}
                <div x-show="wizardError" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs" x-text="wizardError"></div>
                <div x-show="wizardSuccess" class="p-3 rounded-lg bg-brand-500/10 border border-brand-500/30 text-brand-400 text-xs" x-text="wizardSuccess"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Brand Name *</label>
                        <input x-model="form.name" type="text" placeholder="BeSureBot" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Slug / Identifier *</label>
                        <input x-model="form.slug" type="text" placeholder="besurebot" class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
                    </div>
                    <div class="col-span-2 space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Custom Portal Domain *</label>
                        <input x-model="form.domain" type="text" placeholder="panel.besurebot.com" class="w-full text-xs px-3 py-2 rounded-lg font-mono" />
                        <p class="text-[10px] text-slate-600">The reseller must point this domain to your server IP via DNS.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Contact Person Name *</label>
                        <input x-model="form.contact_name" type="text" placeholder="John Smith" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Support Email *</label>
                        <input x-model="form.support_email" type="email" placeholder="support@besurebot.com" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                </div>

                <hr class="border-slate-800" />
                <h4 class="text-xs font-semibold text-slate-300">Admin Account (Reseller Login)</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Admin Name *</label>
                        <input x-model="form.admin_name" type="text" placeholder="BeSureBot Owner" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Admin Email *</label>
                        <input x-model="form.admin_email" type="email" placeholder="admin@besurebot.com" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="col-span-2 space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Admin Password *</label>
                        <input x-model="form.admin_password" type="password" placeholder="Minimum 8 characters" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                </div>

                <hr class="border-slate-800" />
                <h4 class="text-xs font-semibold text-slate-300">Licensing & Quota</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">License Type</label>
                        <select x-model="form.license_type" class="w-full text-xs px-3 py-2 rounded-lg">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="lifetime">Lifetime</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Fee (₹/mo)</label>
                        <input x-model="form.license_fee" type="number" placeholder="5000" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Max Clients</label>
                        <input x-model="form.max_clients" type="number" placeholder="50" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">License Expires</label>
                        <input x-model="form.license_expires_at" type="date" class="w-full text-xs px-3 py-2 rounded-lg" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Brand Color</label>
                        <div class="flex items-center gap-2">
                            <input x-model="form.primary_color" type="color" class="w-10 h-9 rounded-lg border border-slate-700 cursor-pointer bg-transparent" />
                            <input x-model="form.primary_color" type="text" class="flex-1 text-xs px-3 py-2 rounded-lg font-mono" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-slate-800 bg-slate-900/30 flex items-center justify-between">
                <button @click="showWizard = false" class="px-4 py-2 text-slate-400 hover:text-white text-xs font-semibold transition-colors">
                    Cancel
                </button>
                <button @click="submitOnboard()"
                        :disabled="submitting"
                        class="px-5 py-2 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white font-bold rounded-lg transition-all text-xs flex items-center gap-2">
                    <div x-show="submitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span x-text="submitting ? 'Creating...' : 'Onboard Reseller'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function resellerHub() {
    return {
        resellers: [],
        loading: true,
        showModal: false,
        showWizard: false,
        selectedReseller: null,
        submitting: false,
        wizardError: '',
        wizardSuccess: '',
        form: {
            name: '', slug: '', domain: '', contact_name: '', support_email: '',
            admin_name: '', admin_email: '', admin_password: '',
            license_type: 'monthly', license_fee: 0, max_clients: 50,
            license_expires_at: '', primary_color: '#10b981', sidebar_color: '#080f1e',
        },

        init() {
            this.loadResellers();
        },

        loadResellers() {
            this.loading = true;
            fetch('/admin/resellers/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    this.resellers = data.resellers || [];
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
        },

        viewReseller(reseller) {
            this.selectedReseller = reseller;
            this.showModal = true;
        },

        openOnboardWizard() {
            this.wizardError = '';
            this.wizardSuccess = '';
            this.submitting = false;
            this.showWizard = true;
        },

        toggleStatus(id) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch(`/admin/resellers/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const r = this.resellers.find(r => r.id === id);
                    if (r) r.status = data.status;
                }
            });
        },

        submitOnboard() {
            this.wizardError = '';
            this.wizardSuccess = '';
            this.submitting = true;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch('/admin/resellers/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify(this.form),
            })
            .then(r => r.json())
            .then(data => {
                this.submitting = false;
                if (data.success) {
                    this.wizardSuccess = data.message;
                    this.loadResellers();
                    setTimeout(() => { this.showWizard = false; }, 2500);
                } else {
                    this.wizardError = data.message || 'Something went wrong.';
                }
            })
            .catch(() => {
                this.submitting = false;
                this.wizardError = 'Network error. Please try again.';
            });
        },
    };
}
</script>
@endpush
