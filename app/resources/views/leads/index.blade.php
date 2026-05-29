@extends('layouts.app')
@section('title', 'Leads Dashboard')
@section('subtitle', 'Manage and monitor captured customer leads and intelligence metrics')

@section('content')
<div x-data="leadsPanel()" x-init="init()" class="space-y-6">

    {{-- Filter Navigation Bar + Sync Button --}}
    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('leads.index', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'all' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                All Leads
            </a>
            <a href="{{ route('leads.index', ['filter' => 'high_score']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'high_score' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                High Score
            </a>
            <a href="{{ route('leads.index', ['filter' => 'missing_contact']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'missing_contact' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                Missing Info
            </a>
            <a href="{{ route('leads.index', ['filter' => 'human_required']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'human_required' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                Human Required
            </a>
            <a href="{{ route('leads.index', ['filter' => 'recent']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'recent' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                Recent
            </a>
        </div>
        {{-- Sync Status + Manual Sync Button --}}
        <div class="flex items-center gap-3">
            <span class="text-[10px] text-slate-500 font-mono" x-show="syncedAt" x-text="'Synced ' + syncedAgo"></span>
            <button @click="syncLeads(true)"
                    :disabled="syncing"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-700 text-xs text-slate-400 hover:border-brand-500 hover:text-brand-400 transition-all disabled:opacity-50">
                <svg :class="syncing ? 'animate-spin' : ''" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                <span x-text="syncing ? 'Syncing...' : 'Sync'"></span>
            </button>
            {{-- Live indicator dot --}}
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[10px] text-slate-500">Live</span>
            </div>
        </div>
    </div>

    {{-- Main Leads Table Card --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-800" style="background:rgba(255,255,255,0.01);">
                        <th class="px-6 py-4 font-semibold text-slate-400">Captured Name</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">WhatsApp</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">Intent</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">Mood</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">Lead Score</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">Capture Stage</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">Human Check</th>
                        <th class="px-6 py-4 font-semibold text-slate-400">Last Activity</th>
                    </tr>
                </thead>
                {{-- Polled leads table body (replaced every 30s by Alpine) --}}
                <tbody class="divide-y divide-slate-800/40" id="leads-tbody">
                    @forelse ($leads as $lead)
                    <tr class="hover:bg-slate-900/30 cursor-pointer transition-colors" onclick="window.location.href = '{{ route('chats.show', $lead->phone) }}'">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-200 block truncate max-w-[150px]">
                                {{ $lead->captured_name ?? 'N/A' }}
                            </span>
                            @if($lead->business_type)
                                <span class="text-[9px] text-brand-400 font-mono tracking-wider block capitalize">{{ str_replace('_', ' ', $lead->business_type) }}</span>
                            @endif
                            @if(empty($lead->captured_email))
                                <span class="text-[10px] text-red-500 block">Email missing</span>
                            @else
                                <span class="text-[10px] text-slate-500 block truncate max-w-[150px]">{{ $lead->captured_email }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-slate-400">
                            +{{ $lead->phone }}
                            @if($lead->captured_phone && $lead->captured_phone !== $lead->phone)
                                <span class="text-[9px] text-slate-500 block">Alt: +{{ $lead->captured_phone }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-[10px] font-medium tracking-wide uppercase
                                @if($lead->intent === 'buying_intent') bg-emerald-500/10 text-emerald-400
                                @elseif(in_array($lead->intent, ['pricing_inquiry', 'service_inquiry'])) bg-blue-500/10 text-blue-400
                                @elseif($lead->intent === 'objection') bg-amber-500/10 text-amber-400
                                @elseif($lead->intent === 'complaint') bg-red-500/10 text-red-400
                                @else bg-slate-800 text-slate-400 @endif">
                                {{ str_replace('_', ' ', $lead->intent ?? 'inquiry') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 capitalize text-slate-300">
                            <span class="
                                @if($lead->mood === 'urgent') text-red-400 font-medium
                                @elseif($lead->mood === 'interested') text-emerald-400
                                @elseif($lead->mood === 'curious') text-blue-400
                                @elseif($lead->mood === 'frustrated') text-red-500 font-semibold
                                @else text-slate-400 @endif">
                                {{ $lead->mood ?? 'neutral' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-12 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full 
                                        @if($lead->lead_score >= 70) bg-emerald-500
                                        @elseif($lead->lead_score >= 40) bg-amber-500
                                        @else bg-slate-600 @endif" 
                                         style="width: {{ $lead->lead_score }}%"></div>
                                </div>
                                <span class="font-bold text-slate-300 font-mono">{{ $lead->lead_score }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border capitalize
                                @if($lead->capture_stage === 'ready_to_buy') border-emerald-500/20 bg-emerald-500/5 text-emerald-400
                                @elseif($lead->capture_stage === 'interested') border-blue-500/20 bg-blue-500/5 text-blue-400
                                @elseif($lead->capture_stage === 'engaged') border-slate-700 bg-slate-800/40 text-slate-300
                                @elseif($lead->capture_stage === 'objection') border-amber-500/20 bg-amber-500/5 text-amber-400
                                @elseif(in_array($lead->capture_stage, ['complaint', 'human_required'])) border-red-500/20 bg-red-500/5 text-red-400
                                @else border-slate-800 text-slate-500 @endif">
                                {{ str_replace('_', ' ', $lead->capture_stage) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($lead->human_required)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20 uppercase tracking-wider">Required</span>
                            @else
                                <span class="text-slate-600 text-[10px] uppercase">Automated</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500 font-mono">
                            {{ $lead->last_activity_at ? $lead->last_activity_at->diffForHumans() : 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-20 text-slate-500">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0110.089 21c-2.243 0-4.352-.64-6.136-1.753a3.375 3.375 0 00-1.753-4.257 4.135 4.135 0 014.122-2.404c1.21.034 2.353.385 3.324.973M15 11.25a3 3 0 11-6 0 3 3 0 016 0zm6.375 8.25a8.961 8.961 0 01-2.28 5.98M9 3.75h.008v.008H9V3.75z"/></svg>
                                <p class="text-sm font-medium">No leads match this filter</p>
                                <p class="text-[11px] text-slate-600">Leads populate automatically as customer messages arrive</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        @if($leads->hasPages())
        <div class="px-6 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
            {{ $leads->links() }}
        </div>
        @endif

        {{-- Total count footer --}}
        <div class="px-6 py-3 border-t border-slate-800/60 flex items-center justify-between">
            <span class="text-[10px] text-slate-600" x-text="totalLeads > 0 ? totalLeads + ' leads total' : ''"></span>
            <span class="text-[10px] text-slate-600">Auto-refreshes every 30s</span>
        </div>
    </div>

    {{-- ── Side Panel Details Drawer (Slide-over) ──────────────── --}}
    <div x-show="drawerOpen" 
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 w-[450px] shadow-2xl z-50 flex flex-col"
         style="background:#080f1e; border-left:1px solid rgba(255,255,255,0.06); display: none;">
        
        {{-- Drawer Header --}}
        <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-white text-sm">Lead Profile Details</h3>
                <span class="text-[10px] font-mono text-slate-500" x-text="loading ? 'Retrieving records...' : '+' + lead.phone"></span>
            </div>
            <button @click="drawerOpen = false" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold border border-slate-800 px-3 py-1 rounded-lg">
                Close Panel
            </button>
        </div>

        {{-- Drawer Content --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <template x-if="loading">
                <div class="flex flex-col items-center justify-center py-20 text-slate-600 space-y-2">
                    <div class="w-6 h-6 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs">Loading insights...</p>
                </div>
            </template>

            <template x-if="!loading && lead.id">
                <div class="space-y-6">
                    {{-- Summary Box --}}
                    <div class="p-4 rounded-xl border border-slate-800" style="background:rgba(255,255,255,0.015);">
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Lead Intelligence Summary</h4>
                        <p class="text-xs text-slate-300 leading-relaxed font-sans" x-text="lead.summary || 'No summary available.'"></p>
                    </div>

                    {{-- Profiling Fields Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] text-slate-500 uppercase block">Extracted Name</span>
                            <span class="text-xs font-medium text-slate-200" x-text="lead.captured_name || 'Not provided'"></span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] text-slate-500 uppercase block">Alternate Phone</span>
                            <span class="text-xs font-medium text-slate-200" x-text="lead.captured_phone ? '+' + lead.captured_phone : 'Not provided'"></span>
                        </div>
                        <div class="space-y-1 mt-2">
                            <span class="text-[10px] text-slate-500 uppercase block">Extracted Email</span>
                            <span class="text-xs font-medium text-slate-200" x-text="lead.captured_email || 'Not provided'"></span>
                        </div>
                        <div class="space-y-1 mt-2">
                            <span class="text-[10px] text-slate-500 uppercase block">Current Stage</span>
                            <span class="text-xs font-medium text-slate-200 capitalize" x-text="lead.capture_stage ? lead.capture_stage.replace('_', ' ') : 'new'"></span>
                        </div>
                        <div class="space-y-1 mt-2">
                            <span class="text-[10px] text-slate-500 uppercase block">Last Intent</span>
                            <span class="text-xs font-medium text-slate-200 capitalize" x-text="lead.intent ? lead.intent.replace('_', ' ') : 'inquiry'"></span>
                        </div>
                        <div class="space-y-1 mt-2">
                            <span class="text-[10px] text-slate-500 uppercase block">Last Emotion</span>
                            <span class="text-xs font-medium text-slate-200 capitalize" x-text="lead.mood || 'neutral'"></span>
                        </div>
                        <div class="space-y-1 mt-2 col-span-2">
                            <span class="text-[10px] text-slate-500 uppercase block">Business Niche</span>
                            <span class="text-xs font-medium text-slate-200 capitalize" x-text="lead.business_type ? lead.business_type.replace('_', ' ') : 'generic'"></span>
                        </div>
                    </div>

                    <div class="border-t border-slate-800/80 pt-5">
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">Conversation Timeline</h4>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                            <template x-for="msg in messages" :key="msg.id">
                                <div class="flex flex-col space-y-1" :class="msg.role === 'user' ? 'items-start' : 'items-end'">
                                    <div class="px-3.5 py-2.5 rounded-xl text-xs max-w-[85%] leading-relaxed" 
                                         :class="msg.role === 'user' ? 'bg-slate-800 text-slate-200' : 'bg-brand-500/10 border border-brand-500/10 text-brand-400'">
                                        <span x-text="msg.content"></span>
                                    </div>
                                    <span class="text-[8px] text-slate-600 font-mono" x-text="new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function leadsPanel() {
    return {
        // ── Drawer state ──────────────────────────────────────
        drawerOpen: false,
        leadId: null,
        lead: {},
        messages: [],
        loading: false,

        // ── Sync state ────────────────────────────────────────
        syncing: false,
        syncedAt: null,
        syncedAgo: '',
        totalLeads: 0,
        pollInterval: null,
        agoInterval: null,
        currentFilter: new URLSearchParams(window.location.search).get('filter') || 'all',

        init() {
            // Kick off first sync immediately, then every 30s
            this.syncLeads(false);
            this.pollInterval = setInterval(() => this.syncLeads(false), 30000);

            // Update "synced X seconds ago" every 5s
            this.agoInterval = setInterval(() => this.updateAgo(), 5000);
        },

        updateAgo() {
            if (!this.syncedAt) return;
            const secs = Math.floor((Date.now() - this.syncedAt) / 1000);
            if (secs < 60) this.syncedAgo = secs + 's ago';
            else this.syncedAgo = Math.floor(secs / 60) + 'm ago';
        },

        syncLeads(manual = false) {
            if (this.syncing) return;
            this.syncing = true;

            const url = `/leads?filter=${this.currentFilter}&json=1`;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    this.totalLeads = data.total || 0;
                    this.syncedAt   = Date.now();
                    this.syncedAgo  = 'just now';
                    this.renderLeads(data.leads || []);
                })
                .catch(e => console.error('Leads sync error:', e))
                .finally(() => { this.syncing = false; });
        },

        renderLeads(leads) {
            const tbody = document.getElementById('leads-tbody');
            if (!tbody) return;

            // Track existing phone numbers to detect new leads
            const existingPhones = new Set(
                [...tbody.querySelectorAll('tr[data-phone]')].map(r => r.dataset.phone)
            );

            if (leads.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-20 text-slate-500">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0110.089 21c-2.243 0-4.352-.64-6.136-1.753a3.375 3.375 0 00-1.753-4.257 4.135 4.135 0 014.122-2.404c1.21.034 2.353.385 3.324.973M15 11.25a3 3 0 11-6 0 3 3 0 016 0zm6.375 8.25a8.961 8.961 0 01-2.28 5.98M9 3.75h.008v.008H9V3.75z"/></svg>
                                <p class="text-sm font-medium">No leads yet</p>
                                <p class="text-[11px] text-slate-600">Leads populate automatically as customer messages arrive via WhatsApp</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = leads.map(lead => {
                const isNew = !existingPhones.has(String(lead.phone));
                const score = lead.lead_score || 0;
                const scoreColor = score >= 70 ? 'bg-emerald-500' : score >= 40 ? 'bg-amber-500' : 'bg-slate-600';
                const intentColor = lead.intent === 'buying_intent' ? 'bg-emerald-500/10 text-emerald-400'
                    : ['pricing_inquiry','service_inquiry'].includes(lead.intent) ? 'bg-blue-500/10 text-blue-400'
                    : lead.intent === 'objection' ? 'bg-amber-500/10 text-amber-400'
                    : lead.intent === 'complaint' ? 'bg-red-500/10 text-red-400'
                    : 'bg-slate-800 text-slate-400';
                const moodColor = lead.mood === 'urgent' ? 'text-red-400 font-medium'
                    : lead.mood === 'interested' ? 'text-emerald-400'
                    : lead.mood === 'curious' ? 'text-blue-400'
                    : lead.mood === 'frustrated' ? 'text-red-500 font-semibold'
                    : 'text-slate-400';
                const stageColor = lead.capture_stage === 'ready_to_buy' ? 'border-emerald-500/20 bg-emerald-500/5 text-emerald-400'
                    : lead.capture_stage === 'interested' ? 'border-blue-500/20 bg-blue-500/5 text-blue-400'
                    : lead.capture_stage === 'engaged' ? 'border-slate-700 bg-slate-800/40 text-slate-300'
                    : lead.capture_stage === 'objection' ? 'border-amber-500/20 bg-amber-500/5 text-amber-400'
                    : ['complaint','human_required'].includes(lead.capture_stage) ? 'border-red-500/20 bg-red-500/5 text-red-400'
                    : 'border-slate-800 text-slate-500';
                const lastActivity = lead.last_activity_at
                    ? this.timeAgo(new Date(lead.last_activity_at)) : 'N/A';

                return `<tr data-phone="${lead.phone}" 
                            data-id="${lead.id}"
                            class="hover:bg-slate-900/30 cursor-pointer transition-colors ${isNew ? 'animate-pulse-once bg-emerald-900/10' : ''}"
                            onclick="window.location.href = '/chats/${lead.phone}'">
                    <td class="px-6 py-4">
                        <span class="font-medium text-slate-200 block truncate max-w-[150px]">${lead.captured_name || 'N/A'}</span>
                        ${lead.business_type ? `<span class="text-[9px] text-brand-400 font-mono tracking-wider block capitalize">${lead.business_type.replace(/_/g,' ')}</span>` : ''}
                        ${!lead.captured_email ? '<span class="text-[10px] text-red-500 block">Email missing</span>' : `<span class="text-[10px] text-slate-500 block truncate max-w-[150px]">${lead.captured_email}</span>`}
                    </td>
                    <td class="px-6 py-4 font-mono text-slate-400">+${lead.phone}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 rounded text-[10px] font-medium tracking-wide uppercase ${intentColor}">${(lead.intent || 'inquiry').replace(/_/g,' ')}</span></td>
                    <td class="px-6 py-4 capitalize"><span class="${moodColor}">${lead.mood || 'neutral'}</span></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-12 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full ${scoreColor}" style="width:${score}%"></div>
                            </div>
                            <span class="font-bold text-slate-300 font-mono">${score}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border capitalize ${stageColor}">${(lead.capture_stage || 'new').replace(/_/g,' ')}</span></td>
                    <td class="px-6 py-4">${lead.human_required ? '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20 uppercase tracking-wider">Required</span>' : '<span class="text-slate-600 text-[10px] uppercase">Automated</span>'}</td>
                    <td class="px-6 py-4 text-slate-500 font-mono">${lastActivity}</td>
                </tr>`;
            }).join('');
        },

        timeAgo(date) {
            const secs = Math.floor((Date.now() - date) / 1000);
            if (secs < 60) return secs + 's ago';
            if (secs < 3600) return Math.floor(secs/60) + 'm ago';
            if (secs < 86400) return Math.floor(secs/3600) + 'h ago';
            return Math.floor(secs/86400) + 'd ago';
        },

        openLead(id) {
            this.loading  = true;
            this.drawerOpen = true;
            this.leadId   = id;
            fetch(`/leads/${id}`)
                .then(r => r.json())
                .then(data => {
                    this.lead     = data.lead;
                    this.messages = data.messages;
                    this.loading  = false;
                })
                .catch(() => { this.loading = false; });
        }
    };
}
</script>
@endpush
