@extends('layouts.app')
@section('title', 'Leads Dashboard')
@section('subtitle', 'Manage and monitor captured customer leads and intelligence metrics')

@section('content')
<div x-data="{ 
    drawerOpen: false, 
    leadId: null, 
    lead: {}, 
    messages: [], 
    loading: false,
    openLead(id) {
        this.loading = true;
        this.drawerOpen = true;
        this.leadId = id;
        fetch(`/leads/${id}`)
            .then(res => res.json())
            .then(data => {
                this.lead = data.lead;
                this.messages = data.messages;
                this.loading = false;
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
    }
}" class="space-y-6">

    {{-- Filter Navigation Bar --}}
    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <div class="flex gap-2">
            <a href="{{ route('leads.index', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'all' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                All Leads
            </a>
            <a href="{{ route('leads.index', ['filter' => 'high_score']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'high_score' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                High Score Leads
            </a>
            <a href="{{ route('leads.index', ['filter' => 'missing_contact']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'missing_contact' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                Missing Contact Info
            </a>
            <a href="{{ route('leads.index', ['filter' => 'human_required']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'human_required' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                Human Required
            </a>
            <a href="{{ route('leads.index', ['filter' => 'recent']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-semibold border sidebar-link transition-all {{ $filter === 'recent' ? 'border-brand-500 bg-brand-500/10 text-brand-400' : 'border-slate-800 text-slate-400 hover:border-slate-700' }}">
                Recent Activity
            </a>
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
                <tbody class="divide-y divide-slate-800/40">
                    @forelse ($leads as $lead)
                    <tr class="hover:bg-slate-900/30 cursor-pointer transition-colors" @click="openLead({{ $lead->id }})">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-200 block truncate max-w-[150px]">
                                {{ $lead->captured_name ?? 'N/A' }}
                            </span>
                            @if(empty($lead->captured_email))
                                <span class="text-[10px] text-red-500">Email missing</span>
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
