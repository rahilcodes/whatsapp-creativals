@php
    $reseller     = app()->has('active_reseller') ? app('active_reseller') : null;
    $appName      = $reseller?->name ?? 'iChatUp';
    $brandPrimary = $reseller?->primary_color ?? '#10b981';
    $brandSidebar = $reseller?->sidebar_color ?? '#080f1e';
    $logoUrl      = $reseller?->logo_path ? Storage::url($reseller->logo_path) : null;
    $faviconUrl   = $reseller?->favicon_path ? Storage::url($reseller->favicon_path) : asset('favicon.png');
@endphp
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin') — {{ $appName }}</title>
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
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '{{ $brandPrimary }}',
                            600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        :root {
            --brand-primary: {{ $brandPrimary }};
            --brand-sidebar: {{ $brandSidebar }};
        }
        body { font-family: 'Inter', sans-serif; background: #070d1a; }
        .sidebar { background: {{ $brandSidebar }}; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.08); color: #fff; font-weight: 600; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; }
        input, select, textarea { 
            background: rgba(255,255,255,0.05) !important; 
            border: 1px solid rgba(255,255,255,0.1) !important; 
            color: #e2e8f0 !important; border-radius: 8px;
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; 
            border-color: var(--brand-primary) !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 15%, transparent);
        }
        .pulse-dot { animation: pulse-dot 1.8s infinite; }
        @keyframes pulse-dot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.6; transform:scale(1.3); } }
    </style>
    @stack('styles')
</head>
<body class="text-slate-100 antialiased">
<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside class="sidebar w-60 flex-shrink-0 flex flex-col">
        {{-- Brand Identity --}}
        <div class="p-5 flex items-center gap-3 border-b" style="border-color: rgba(255,255,255,0.06);">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto object-contain" />
            @else
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-sm"
                     style="background: {{ $brandPrimary }}22; border: 1px solid {{ $brandPrimary }}44; color: {{ $brandPrimary }}">
                    {{ strtoupper(substr($appName, 0, 1)) }}
                </div>
                <div>
                    <div class="font-bold text-white text-sm">{{ $appName }}</div>
                    <div class="text-[9px] text-slate-500 uppercase tracking-wider font-semibold">Admin Portal</div>
                </div>
            @endif
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('reseller.dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 {{ request()->routeIs('reseller.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('reseller.clients') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 {{ request()->routeIs('reseller.clients') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Clients
            </a>
            <a href="{{ route('reseller.branding') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 {{ request()->routeIs('reseller.branding') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Branding
            </a>
            <a href="{{ route('reseller.gateway') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 {{ request()->routeIs('reseller.gateway') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Payments & Mail
            </a>
        </nav>

        {{-- Footer --}}
        <div class="p-4 border-t" style="border-color:rgba(255,255,255,0.05);">
            <div class="text-[10px] text-slate-600 text-center">
                Powered by {{ $reseller ? 'iChatUp Engine' : 'iChatUp' }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-xs text-slate-500 hover:text-white transition-colors py-1 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        {{-- Top Bar --}}
        <header class="h-14 flex-shrink-0 flex items-center justify-between px-6 border-b" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05);">
            <div>
                <h1 class="text-sm font-semibold text-white">@yield('title', 'Dashboard')</h1>
                <p class="text-[11px] text-slate-500">@yield('subtitle', '')</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-sm"
                         style="background: {{ $brandPrimary }}22; color: {{ $brandPrimary }}; border: 1px solid {{ $brandPrimary }}33">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </header>

        {{-- Page --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
