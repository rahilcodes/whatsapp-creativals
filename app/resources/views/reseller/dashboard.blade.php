@extends('layouts.reseller')
@section('title', 'Dashboard')
@section('subtitle', 'Overview of your brand performance')

@section('content')
<div class="space-y-6">

    {{-- ── Welcome Banner ────────────────────────────────────── --}}
    <div class="card p-5 flex items-center gap-4"
         style="background: linear-gradient(135deg, {{ $reseller->primary_color }}10, rgba(255,255,255,0.02));">
        <div class="p-3 rounded-xl" style="background: {{ $reseller->primary_color }}20; border: 1px solid {{ $reseller->primary_color }}30;">
            <svg class="w-6 h-6" style="color:{{ $reseller->primary_color }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="flex-1">
            <h2 class="font-bold text-white">Welcome back, {{ $reseller->name }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">Your whitelabel platform is live at <span class="font-mono">{{ $reseller->domain }}</span></p>
        </div>
        <a href="{{ route('reseller.clients') }}"
           class="px-4 py-2 text-xs font-bold rounded-lg text-white transition-all hover:opacity-90"
           style="background: {{ $reseller->primary_color }};">
            Manage Clients
        </a>
    </div>

    {{-- ── Stats Grid ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Clients --}}
        <div class="card p-5 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-semibold tracking-wider">Total Clients</span>
                <div class="text-3xl font-bold text-white mt-1">{{ $clientCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Registered accounts</div>
            </div>
            <div class="p-3 rounded-xl border" style="background: {{ $reseller->primary_color }}10; border-color: {{ $reseller->primary_color }}30; color: {{ $reseller->primary_color }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        {{-- Active Clients --}}
        <div class="card p-5 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-semibold tracking-wider">Active</span>
                <div class="text-3xl font-bold text-white mt-1">{{ $activeClients }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Actively using platform</div>
            </div>
            <div class="p-3 rounded-xl bg-brand-500/10 text-brand-400 border border-brand-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        {{-- Messages Today --}}
        <div class="card p-5 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-semibold tracking-wider">Msgs Today</span>
                <div class="text-3xl font-bold text-white mt-1">{{ number_format($messagesToday) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Messages across all clients</div>
            </div>
            <div class="p-3 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
        </div>
        {{-- Slot Usage --}}
        <div class="card p-5 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase text-slate-400 font-semibold tracking-wider">Slot Usage</span>
                <div class="text-3xl font-bold text-white mt-1">
                    {{ $clientCount }}<span class="text-slate-500 text-base font-normal"> / {{ $reseller->max_clients }}</span>
                </div>
                <div class="text-[11px] mt-1 {{ $reseller->remainingClientSlots() <= 5 ? 'text-amber-400' : 'text-slate-500' }}">
                    {{ $reseller->remainingClientSlots() }} slots remaining
                </div>
            </div>
            <div class="p-3 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Slot Usage Bar ─────────────────────────────────────── --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-white">Client Slot Capacity</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $clientCount }} of {{ $reseller->max_clients }} seats filled</p>
            </div>
            <span class="text-xs font-bold text-white">{{ $reseller->max_clients > 0 ? round(($clientCount / $reseller->max_clients) * 100) : 0 }}%</span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
            @php $pct = $reseller->max_clients > 0 ? min(100, round(($clientCount / $reseller->max_clients) * 100)) : 0; @endphp
            <div class="h-2 rounded-full transition-all"
                 style="width: {{ $pct }}%; background: {{ $reseller->primary_color }};"></div>
        </div>
    </div>

    {{-- ── Quick Actions ───────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4">
        <a href="{{ route('reseller.clients') }}"
           class="card p-5 flex flex-col gap-3 hover:border-slate-600 transition-all cursor-pointer group">
            <div class="p-2.5 rounded-lg w-fit" style="background: {{ $reseller->primary_color }}15; color: {{ $reseller->primary_color }}; border: 1px solid {{ $reseller->primary_color }}25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-white group-hover:text-brand-400 transition-colors">Add New Client</div>
                <div class="text-xs text-slate-500">Register a client account</div>
            </div>
        </a>
        <a href="{{ route('reseller.branding') }}"
           class="card p-5 flex flex-col gap-3 hover:border-slate-600 transition-all cursor-pointer group">
            <div class="p-2.5 rounded-lg w-fit bg-violet-500/10 text-violet-400 border border-violet-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-white group-hover:text-violet-400 transition-colors">Brand Settings</div>
                <div class="text-xs text-slate-500">Logo, colors, and identity</div>
            </div>
        </a>
        <a href="{{ route('reseller.gateway') }}"
           class="card p-5 flex flex-col gap-3 hover:border-slate-600 transition-all cursor-pointer group">
            <div class="p-2.5 rounded-lg w-fit bg-amber-500/10 text-amber-400 border border-amber-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-white group-hover:text-amber-400 transition-colors">Payments & Mail</div>
                <div class="text-xs text-slate-500">Configure Stripe and SMTP</div>
            </div>
        </a>
    </div>

</div>
@endsection
