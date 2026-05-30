@php
    $reseller    = app()->has('active_reseller') ? app('active_reseller') : null;
    $appName     = $reseller?->name ?? 'iChatUp';
    $brandPrimary = $reseller?->primary_color ?? '#10b981';
    $brandSidebar = $reseller?->sidebar_color ?? '#080f1e';
    $faviconUrl  = $reseller?->favicon_path ? Storage::url($reseller->favicon_path) : asset('favicon.png');
@endphp
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', $appName) — {{ $appName }}</title>
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '{{ $brandPrimary }}',
                            600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        },
                    },
                },
            },
        };
    </script>
    {{-- Dynamic brand CSS variables for reseller theming --}}
    <style>
        :root {
            --brand-primary: {{ $brandPrimary }};
            --brand-sidebar: {{ $brandSidebar }};
        }
        body { font-family: 'Inter', sans-serif; background: #070d1a; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.07); }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background: rgba(16,185,129,0.1); color: #34d399; }
        .sidebar-link.active { background: rgba(16,185,129,0.15); color: #10b981; border-left: 3px solid #10b981; }
        .pulse-dot { animation: pulse-glow 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse-glow {
            0%,100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50%      { opacity: .7; box-shadow: 0 0 0 6px rgba(16,185,129,0); }
        }
        .card { background: #0d1627; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; }
        .btn-primary { background: linear-gradient(135deg,#059669,#10b981); transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(16,185,129,0.3); }
        .btn-danger { background: linear-gradient(135deg,#dc2626,#ef4444); transition: all 0.2s; }
        .btn-danger:hover  { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(239,68,68,0.3); }
        .status-connected    { color: #10b981; }
        .status-disconnected { color: #ef4444; }
        .status-connecting   { color: #f59e0b; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
        input, textarea, select {
            background: #0a1120 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #e2e8f0 !important;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #10b981 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15) !important;
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-200 min-h-screen flex">

{{-- ── SIDEBAR ─────────────────────────────────────────────── --}}
<aside class="w-64 min-h-screen flex flex-col fixed left-0 top-0 z-30" style="background:#080f1e;border-right:1px solid rgba(255,255,255,0.05);">
    {{-- Logo --}}
    <div class="px-6 py-5 border-b" style="border-color:rgba(255,255,255,0.05);">
        <div class="flex items-center gap-1.5">
            @if($reseller?->logo_path)
                <img src="{{ Storage::url($reseller->logo_path) }}" alt="{{ $appName }}" class="w-9 h-9 object-contain rounded-xl" />
            @elseif($reseller)
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-sm flex-shrink-0"
                     style="background: linear-gradient(135deg, {{ $brandPrimary }}, #047857);">
                    {{ strtoupper(substr($appName, 0, 1)) }}
                </div>
            @else
                <img src="{{ asset('ichatup_logo.png') }}" alt="iChatUp Logo" class="w-9 h-9 object-contain rounded-xl" />
            @endif
            <div>
                <div class="text-sm font-bold text-white">{{ $appName }}</div>
                <div class="text-xs text-slate-500">AI Business Bot</div>
            </div>
        </div>
    </div>

    {{-- Live Status Badge --}}
    @if(Auth::check() && !Auth::user()->is_super_admin)
    <div class="px-4 py-3 mx-3 mt-3 rounded-lg" style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.15);">
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400">WhatsApp</span>
            <div id="sidebar-status" class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                <span class="text-xs text-slate-400">Loading...</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Navigation --}}
    <nav class="flex-1 px-3 mt-4 space-y-1">
        @php
            $navItems = [];
            if (Auth::check() && Auth::user()->is_super_admin) {
                $navItems[] = [
                    'route' => 'admin.dashboard',
                    'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'label' => 'Admin Panel'
                ];
                $navItems[] = [
                    'route' => 'admin.resellers.index',
                    'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'label' => 'Reseller Hub'
                ];
            } else {
                $showBilling = !$reseller || (bool)$reseller->show_billing;
                $navItems = [
                    ['route' => 'dashboard',      'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                    ['route' => 'leads.index',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'label' => 'Leads'],
                    ['route' => 'chats.index',    'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'Chats'],
                    ['route' => 'integrations.index', 'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z', 'label' => 'Integrations'],
                    ['route' => 'settings.index', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'AI Settings'],
                    ['route' => 'business.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Business Memory'],
                ];

                if ($showBilling) {
                    $navItems[] = ['route' => 'billing.index',  'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z', 'label' => 'Subscription'];
                }
            }
        @endphp

        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Bot Toggle at Bottom --}}
    @if(Auth::check() && !Auth::user()->is_super_admin)
    <div class="p-4 border-t" style="border-color:rgba(255,255,255,0.05);">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-slate-400">AI Replies</span>
            <button id="bot-toggle-btn" onclick="toggleBot()"
                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none"
                style="background:#374151;">
                <span id="bot-toggle-thumb" class="inline-block h-3.5 w-3.5 rounded-full bg-white transition-transform translate-x-1"></span>
            </button>
        </div>
        <button onclick="reconnectWA(event)"
            class="w-full flex items-center justify-center text-xs py-2 rounded-lg text-slate-400 border border-slate-700 hover:border-brand-500 hover:text-brand-400 transition-all mb-3">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            <span>Reconnect WhatsApp</span>
        </button>
    </div>
    @endif

    {{-- User Profile + Logout --}}
    <div class="p-4 border-t" style="border-color:rgba(255,255,255,0.05);">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                 style="background:linear-gradient(135deg,#059669,#10b981);">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold text-slate-300 truncate">{{ Auth::user()->name }}</div>
                <div class="text-[10px] text-slate-600 truncate">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 text-xs py-2 rounded-lg text-slate-400 border border-slate-700 hover:border-red-500 hover:text-red-400 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Sign Out
            </button>
        </form>
    </div>
</aside>

{{-- ── MAIN CONTENT ────────────────────────────────────────── --}}
<main class="ml-64 flex-1 min-h-screen">
    @if(session()->has('impersonator_id'))
    <!-- Impersonation Banner -->
    <div class="bg-indigo-600 text-white px-8 py-3 flex items-center justify-between text-xs font-semibold border-b border-indigo-700 bg-gradient-to-r from-indigo-600 to-emerald-600 shadow-md">
        <div class="flex items-center gap-2.5">
            <svg class="w-4.5 h-4.5 text-white animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>Impersonation Mode: Logged in as <strong class="text-white font-bold">{{ Auth::user()->name }}</strong> (Tenant: {{ Auth::user()->tenant->name ?? 'Client' }})</span>
        </div>
        <form method="POST" action="{{ route('admin.impersonate.stop') }}" class="m-0 inline-block">
            @csrf
            <button type="submit" class="bg-slate-950/40 hover:bg-slate-950/60 text-white px-3 py-1.5 rounded-lg transition-all border border-white/10 text-[11px] font-bold shadow-sm">
                Return to Admin Panel
            </button>
        </form>
    </div>
    @elseif(Auth::check() && !Auth::user()->is_super_admin)
        @if(Auth::user()->tenant->has_support_addon)
            <!-- Dedicated Support Manager Banner -->
            <div class="bg-indigo-600 text-white px-8 py-3 flex items-center justify-between text-xs font-semibold border-b border-indigo-700 bg-gradient-to-r from-indigo-600 to-indigo-700 shadow-md">
                <div class="flex items-center gap-2">
                    <svg class="w-4.5 h-4.5 text-white animate-bounce" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                    </svg>
                    <span>Dedicated Support Active — Your Setup Manager: <strong class="text-white font-bold">+91 7997001700</strong></span>
                </div>
                <span class="bg-slate-950/40 text-white px-3 py-1.5 rounded-lg border border-white/10 text-[10px] uppercase font-bold tracking-wider">Call or WhatsApp</span>
            </div>
        @elseif(Auth::user()->tenant->subscription_status === 'trialing')
            @php
                $tenant      = Auth::user()->tenant;
                $hoursLeft   = $tenant->trialHoursLeft();
                $daysLeft    = $tenant->trialDaysLeft();
                $isLastDay   = $hoursLeft > 0 && $hoursLeft <= 24;
                $isLast3Days = $hoursLeft > 0 && $hoursLeft <= 72;
                $isActive    = $tenant->isTrialActive();
            @endphp
            @if($isActive)
                {{-- Trial Active Banner --}}
                <div class="text-white px-8 py-2.5 flex items-center justify-between text-xs font-semibold border-b
                    {{ $isLastDay ? 'bg-red-600 border-red-700 bg-gradient-to-r from-red-600 to-red-700' : 'bg-amber-600 border-amber-700 bg-gradient-to-r from-amber-500 to-amber-600' }}
                    shadow-md">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 {{ $isLastDay ? 'animate-pulse' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        @if($isLastDay)
                            <span>⚠️ <strong>Last {{ $hoursLeft }} hours</strong> of your free trial — upgrade now to keep AI replies running!</span>
                        @elseif($isLast3Days)
                            <span>Trial ending soon — <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }} left</strong> on your free trial.</span>
                        @else
                            <span>Trial Mode — <strong>{{ $daysLeft }} days left</strong> on your free trial.</span>
                        @endif
                    </div>
                    <a href="{{ route('billing.index') }}" class="bg-slate-950/40 hover:bg-slate-950/60 text-white px-3 py-1.5 rounded-lg transition-all border border-white/10 text-[11px] font-bold shadow-sm whitespace-nowrap">
                        Upgrade Plan →
                    </a>
                </div>
            @else
                {{-- Trial Expired Banner --}}
                <div class="bg-red-700 text-white px-8 py-2.5 flex items-center justify-between text-xs font-semibold border-b border-red-800 shadow-md animate-pulse">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                        <span>🔴 Your free trial has <strong>expired</strong>. AI autoreplies are paused. Subscribe to re-enable.</span>
                    </div>
                    <a href="{{ route('billing.index') }}" class="bg-white text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-all text-[11px] font-bold shadow-sm whitespace-nowrap">
                        Subscribe Now →
                    </a>
                </div>
            @endif
        @endif
    @endif
    {{-- Top Bar --}}
    <header class="sticky top-0 z-20 px-8 py-4 flex items-center justify-between"
            style="background:rgba(7,13,26,0.8);backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,0.05);">
        <div>
            <h1 class="text-lg font-semibold text-white">@yield('title', 'Dashboard')</h1>
            <p class="text-xs text-slate-500">@yield('subtitle', 'WhatsApp AI Assistant')</p>
        </div>
        <div class="flex items-center gap-3">
            @if(Auth::check() && !Auth::user()->is_super_admin)
            <div id="wa-status-badge" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium"
                 style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                <span class="text-slate-400">Checking...</span>
            </div>
            <div class="text-xs text-slate-500" id="last-updated">--</div>
            @endif
            {{-- User Avatar Dropdown --}}
            <div class="relative pl-3 border-l border-slate-700" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                         style="background:linear-gradient(135deg,#059669,#10b981);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-medium text-slate-300">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-slate-500">{{ Auth::user()->email }}</div>
                    </div>
                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.away="open = false"
                     class="absolute right-0 top-10 w-48 rounded-xl py-1 z-50"
                     style="background:#0d1627;border:1px solid rgba(255,255,255,0.08);box-shadow:0 20px 40px rgba(0,0,0,0.5);"
                     x-transition>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-xs text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile Settings
                    </a>
                    <div style="height:1px;background:rgba(255,255,255,0.06);margin:0.25rem 0;"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2.5 text-xs text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mx-8 mt-4 px-4 py-3 rounded-lg text-sm font-medium fade-in"
             style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#34d399;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mx-8 mt-4 px-4 py-3 rounded-lg text-sm font-medium fade-in"
             style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;">
            ✕ {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mx-8 mt-4 px-5 py-3.5 rounded-lg text-xs font-semibold fade-in space-y-1.5"
             style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.15);color:#f87171;">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="p-8 fade-in">
        @yield('content')
    </div>
</main>

{{-- ── GLOBAL SCRIPTS ───────────────────────────────────────── --}}
@if(Auth::check() && !Auth::user()->is_super_admin)
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Poll bot status every 5s ──────────────────────────────
    async function pollStatus() {
        try {
            const r = await fetch('/api/bot-status');
            const d = await r.json();
            updateStatusUI(d);
        } catch(e) { /* Node offline */ }
    }

    function updateStatusUI(d) {
        const connected  = d.wa_connected;
        const status     = d.wa_status || 'disconnected';
        const botEnabled = d.bot_enabled;

        // Header badge
        const badge = document.getElementById('wa-status-badge');
        const colors = { connected:'#10b981', disconnected:'#ef4444', connecting:'#f59e0b' };
        const labels = { connected:'Connected ✓', disconnected:'Disconnected', connecting:'Connecting...' };
        if (badge) {
            badge.querySelector('div').style.background = colors[status] || '#6b7280';
            badge.querySelector('span').textContent     = labels[status] || status;
            if (connected) badge.querySelector('div').classList.add('pulse-dot');
        }

        // Sidebar status
        const ss = document.getElementById('sidebar-status');
        if (ss) {
            ss.innerHTML = `<div class="w-2 h-2 rounded-full pulse-dot" style="background:${colors[status] || '#6b7280'}"></div>
                            <span class="text-xs" style="color:${colors[status] || '#6b7280'}">${labels[status] || status}</span>`;
        }

        // Bot toggle
        const btn   = document.getElementById('bot-toggle-btn');
        const thumb = document.getElementById('bot-toggle-thumb');
        if (btn && thumb) {
            btn.style.background   = botEnabled ? '#059669' : '#374151';
            thumb.style.transform  = botEnabled ? 'translateX(18px)' : 'translateX(2px)';
        }

        document.getElementById('last-updated').textContent = 'Updated ' + new Date().toLocaleTimeString();
    }

    async function toggleBot() {
        const r = await fetch('/api/bot/toggle', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
        const d = await r.json();
        pollStatus();
    }

    async function reconnectWA(event) {
        const btn = event.currentTarget || event.target;
        const iconSpan = btn.querySelector('svg');
        const textSpan = btn.querySelector('span');
        
        iconSpan.classList.add('animate-spin');
        textSpan.textContent = 'Reconnecting...';
        btn.disabled = true;
        
        await fetch('/api/bot/reconnect', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
        
        setTimeout(() => { 
            iconSpan.classList.remove('animate-spin');
            textSpan.textContent = 'Reconnect WhatsApp'; 
            btn.disabled = false; 
            pollStatus(); 
        }, 3000);
    }

    pollStatus();
    setInterval(pollStatus, 5000);
</script>
@endif
@stack('scripts')
</body>
</html>
